<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhiteListBank;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Hash;
use Validator;
use App\Models\Bankdetail;
use App\Models\Paymentmethod;
use App\Models\Loadcash;
use App\Models\Balance;
use App\Models\Report;
use App\Models\User;
use App\Models\Sitesetting;
use DB;
use Helpers;
use \Crypt;
use App\Library\SmsLibrary;
use App\Library\BasicLibrary;
use App\Library\PermissionLibrary;
use App\Library\LocationRestrictionsLibrary;

class PaymentrequestController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->provider_id = 326;
        $this->min_amount = 10;
        $this->max_amount = 1000000;
    }

    function payment_request(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_request_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $bankdetails = Bankdetail::where('company_id', Auth::user()->company_id)->where('status_id', 1)->get();
        $methods = Paymentmethod::where('status_id', 1)->get();
        $data = array('page_title' => 'Payment Request');
        $loadcash = Loadcash::where('user_id', Auth::id())->get();
        return view('admin.balance.payment_request', compact('bankdetails', 'methods', 'loadcash'))->with($data);
    }

    function save_payment_request(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_request_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $rules = array(
            'bankdetail_id' => 'required|digits_between:1,6',
            'paymentmethod_id' => 'required|digits_between:1,6',
            'payment_date' => 'required|string|min:3|max:20',
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
            'bankref' => 'required|unique:loadcashes|alpha_dash|string|min:3|max:20',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $bankdetail_id = $request->bankdetail_id;
        $paymentmethod_id = $request->paymentmethod_id;
        $payment_date = $request->payment_date;
        $amount = $request->amount;
        $bankref = $request->bankref;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $parent_id = Auth::User()->parent_id;
        $request_ip = request()->ip();
        $user_id = Auth::id();
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        DB::beginTransaction();
        try {
            $reports = Report::where('txnid', $bankref)->first();
            if ($reports) {
                return Response()->json(['status' => 'failure', 'message' => 'duplicate utr number']);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Loadcash::insertGetId([
                'user_id' => Auth::id(),
                'payment_date' => $payment_date,
                'paymentmethod_id' => $paymentmethod_id,
                'bankdetail_id' => $bankdetail_id,
                'amount' => $amount,
                'bankref' => $bankref,
                'parent_id' => 1,
                'created_at' => $ctime,
                'payment_type' => 0,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'ip_address' => $request_ip,
                'status_id' => 3,
            ]);

            $library = new BasicLibrary();
            $user_id = array($parent_id);

            $bankdetails = Bankdetail::find($bankdetail_id);
            $parentdetails = User::find($parent_id);
            $username = Auth::User()->name;
            $letter = collect([
                'title' => "Payment Rquest Amount $amount",
                'body' => "Dear $parentdetails->name you received  rs $amount payment request in $bankdetails->bank_name Ref Number is : $bankref request send by $username kindly verify amount with your bank and update ASAP thanks",
            ]);
            $library->send_notification($user_id, $letter);
            DB::commit();
            return Response()->json(['status' => 'success', 'message' => 'payment request successfully submited']);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }


    function payment_request_view(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_request_view_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }

        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $total_amount = Loadcash::where('parent_id', Auth::id())
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', $status_id)
                ->sum('amount');
            $urls = url('admin/payment-request-view-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        } else {
            $fromdate = date('Y-m-d', strtotime('-15 days'));
            $todate = date('Y-m-d', time());
            $status_id = 3;
            $total_amount = Loadcash::where('parent_id', Auth::id())
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', $status_id)
                ->sum('amount');
            $urls = url('admin/payment-request-view-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        }
        $data = array(
            'page_title' => 'Payment Request View',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls,
            'total_amount' => number_format($total_amount, 2),
            'status_id' => $status_id,
        );
        $methods = Paymentmethod::where('status_id', 1)->get();
        $bankdetails = Bankdetail::where('company_id', Auth::User()->company_id)->where('status_id', 1)->get();
        return view('admin.balance.payment_request_view', compact('methods', 'bankdetails'))->with($data);
    }

    function payment_request_view_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $status_id = $request->get('amp;status_id');
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $totalRecords = Loadcash::select('count(*) as allcount')
            ->where('parent_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where(function ($q) use ($status_id) {
                if ($status_id) {
                    $q->where('status_id', $status_id);
                }
            })
            ->orderBy('id', 'DESC')
            ->count();

        $totalRecordswithFilter = Loadcash::select('count(*) as allcount')
            ->where('parent_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where(function ($q) use ($status_id) {
                if ($status_id) {
                    $q->where('status_id', $status_id);
                }
            })
            ->orderBy('id', 'DESC')
            ->where('bankref', 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records

        $records = Loadcash::query();
        if (in_array($columnName, ['bank_name', 'payment_method', 'status', 'action'])) {
            $records = $records->orderBy('id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->where('bankref', 'like', '%' . $searchValue . '%')
            ->where('parent_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where(function ($q) use ($status_id) {
                if ($status_id) {
                    $q->where('status_id', $status_id);
                }
            })
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $UTR = explode('_', $value->bankref);
            $action = "";
            if ($value->status_id == 3) {
                $action = '<button class="btn btn-success btn-sm" onclick="view_request(' . $value->id . ')">Update</button>';
            } else if ($value->status_id == 2) {
                if ($value->added_from != -1) {
                    $action = '<button class="btn btn-primary btn-sm" onclick="re_approve_view_request(' . $value->id . ')">Re-Approved</button>';
                }
            }
            $edit = "-";
            if(hasAdminPermission('admin.view_payment_request')){
                $edit = ($value->status_id == 3 && Auth::User()->role_id == 1) ? '<button class="btn btn-danger btn-sm" onclick="view_edit_request(' . $value->id . ')">Edit</button>' : '';
            }else{
                $action = "-";
            }
            $data_arr[] = array(
                "id" => $value->id,
                "user_id" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "created_at" => "$value->created_at",
                "payment_date" => "$value->payment_date",
                "bank_name" => $value->bankdetail->bank_name,
                "payment_method" => $value->paymentmethod->payment_type,
                "amount" => number_format($value->amount, 2),
                "bankref" => $UTR[0],
                "txn_number" => ($value->txn_number) ? $value->txn_number : '-',
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "payment_type" => ($value->payment_type == 1) ? 'Auto' : 'Manual',
                "action" => $action,
                "edit" => $edit,
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;
    }


    function view_payment_request(Request $request)
    {
        $id = $request->id;
        $loadcash = Loadcash::where('id', $id)->where('status_id', 3)->first();
        if ($loadcash) {
            $edit_details = array(
                'id' => $loadcash->id,
                'paymentmethod_id' => $loadcash->paymentmethod_id,
                'bankdetail_id' => $loadcash->bankdetail_id,
                'bankref' => $loadcash->bankref,
            );
            $details = array(
                'id' => $loadcash->id,
                'user_id' => $loadcash->user->name,
                'payment_date' => $loadcash->payment_date,
                'paymentmethod_id' => $loadcash->paymentmethod->payment_type,
                'bankdetail_id' => $loadcash->bankdetail->bank_name,
                'amount' => number_format($loadcash->amount, 2),
                'bankref' => $loadcash->bankref,
                'status_id' => $loadcash->status_id,
            );
            return Response()->json(['status' => 'success', 'details' => $details, 'edit_details' => $edit_details]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function update_payment_request(Request $request)
    {
        $mt = explode(' ', microtime());
        $dupplicate_transaction = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        $rules = array(
            'id' => 'required',
            'status_id' => 'required',
            'password' => 'required',
            //'dupplicate_transaction' => 'required|unique:check_duplicates',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $id = $request->id;
        $status_id = $request->status_id;
        $password = $request->password;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        if (Hash::check($password, $current_password)) {
            DB::table('check_duplicates')->insert(['dupplicate_transaction' => $dupplicate_transaction]);
            $loadcash = Loadcash::where('id', $id)->where('status_id', 3)->first();
            if ($loadcash) {
                if ($status_id == 1) {
                    $child_id = $loadcash->user_id;
                    $amount = $loadcash->amount;
                    $bankref = $loadcash->bankref;
                    $loadcash_id = $loadcash->id;
                    return $this->transfer_middle($user_id, $child_id, $amount, $bankref, $loadcash_id, $latitude, $longitude);
                } elseif ($status_id == 2) {
                    Loadcash::where('id', $id)->update(['status_id' => 2]);
                    return Response()->json(['status' => 'success', 'message' => 'request successfully rejected']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
        }
    }

    function view_reapprove_payment_request(Request $request)
    {
        $id = $request->id;
        $loadcash = Loadcash::where('id', $id)->first();
        if ($loadcash) {
            $edit_details = array(
                'id' => $loadcash->id,
                'paymentmethod_id' => $loadcash->paymentmethod_id,
                'bankdetail_id' => $loadcash->bankdetail_id,
                'bankref' => $loadcash->bankref,
            );
            $details = array(
                'id' => $loadcash->id,
                'user_id' => $loadcash->user->name,
                'payment_date' => $loadcash->payment_date,
                'paymentmethod_id' => $loadcash->paymentmethod->payment_type,
                'bankdetail_id' => $loadcash->bankdetail->bank_name,
                'amount' => number_format($loadcash->amount, 2),
                'bankref' => $loadcash->bankref,
                'status_id' => $loadcash->status_id,
            );
            return Response()->json(['status' => 'success', 'details' => $details, 'edit_details' => $edit_details]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function update_reapprove_payment_request(Request $request)
    {
        $mt = explode(' ', microtime());
        $dupplicate_transaction = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        $rules = array(
            'id' => 'required',
            'status_id' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'paymentmethod_id' => 'required',
            'amount' => 'required',
            'bankref' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $id = $request->id;
        $status_id = $request->status_id;

        $password = $request->password;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $amount = $request->amount;
        $paymentmethod_id = $request->paymentmethod_id;
        $bankref = $request->bankref;

        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        if (Hash::check($password, $current_password)) {
            $oldLoadcash = Loadcash::where('id', $id)->first();
            DB::table('check_duplicates')->insert(['dupplicate_transaction' => $dupplicate_transaction]);
            $ctime = date('Y-m-d H:i:s');
            $request_ip = request()->ip();
            $loadcash_id = Loadcash::insertGetId([
                'user_id' => $oldLoadcash->user_id,
                'payment_date' => $oldLoadcash->payment_date,
                'paymentmethod_id' => $paymentmethod_id,
                'bankdetail_id' => $oldLoadcash->bankdetail_id,
                'amount' => $amount,
                'bankref' => $bankref,
                'parent_id' => 1,
                'created_at' => $ctime,
                'payment_type' => 0,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'ip_address' => $request_ip,
                'status_id' => 1,
            ]);
            $loadcash = Loadcash::where('id', $loadcash_id)->first();
            if ($loadcash) {
                $child_id = $loadcash->user_id;
                $amount = $loadcash->amount;
                $bankref = $loadcash->bankref;
                $loadcash_id = $loadcash->id;
                $oldLoadcash->added_from = -1;
                $oldLoadcash->save();
                return $this->transfer_middle($user_id, $child_id, $amount, $bankref, $loadcash_id, $latitude, $longitude);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
        }
    }

    function transfer_middle($user_id, $child_id, $amount, $bankref, $loadcash_id, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        if ($userdetails->active == 1) {
            $childdetails = User::find($child_id);
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 4) {
                DB::beginTransaction();
                try {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $provider_id = $this->provider_id;
                    $request_ip = request()->ip();
                    $description = "Tansfer to  $childdetails->name $childdetails->last_name";
                    $insert_id = Report::insertGetId([
                        'number' => $childdetails->name . ' ' . $childdetails->last_name,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => 0,
                        'status_id' => 7,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => 0,
                        'mode' => "WEB",
                        'txnid' => $bankref,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'credit_by' => $child_id,
                        'wallet_type' => 1,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    $message = "Dear $userdetails->name Your Wallet Debited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                    $template_id = 4;
                    $whatsappArr = [$amount, $user_balance];
                    $library = new SmsLibrary();
                    $library->send_sms($userdetails->mobile, $message, $template_id, $whatsappArr);

                    // child update
                    $child_opening_balance = $childdetails->balance->user_balance;
                    Balance::where('user_id', $child_id)->increment('user_balance', $amount);
                    Balance::where('user_id', $child_id)->update(['balance_alert' => 1]);
                    $childbalance = Balance::where('user_id', $child_id)->first();
                    $child_balance = $childbalance->user_balance;

                    $description = "Transfer by $userdetails->name $userdetails->last_name";
                    $insert_id = Report::insertGetId([
                        'number' => $userdetails->name . ' ' . $userdetails->last_name,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => 0,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $child_id,
                        'profit' => 0,
                        'mode' => "WEB",
                        'txnid' => $bankref,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $child_opening_balance,
                        'total_balance' => $child_balance,
                        'credit_by' => $user_id,
                        'wallet_type' => 1,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    Loadcash::where('id', $loadcash_id)->update(['status_id' => 1]);
                    DB::commit();
                    $amount = number_format($amount, 2);
                    $child_balance = number_format($child_balance, 2);
                    // $message = "Dear $childdetails->name Your Wallet Credited With Amount $amount Your Current balance is $child_balance $this->brand_name";
                    $message = "Dear User, Your Wallet is Credited With Amount $amount. Your Current balance is $child_balance. For more info: trustxpay.org PAOBIL";
                    $template_id = 5;
                    $whatsappArr = [$amount, $child_balance];
                    $library = new SmsLibrary();
                    $library->send_sms($childdetails->mobile, $message, $template_id, $whatsappArr);
                    return Response()->json(['status' => 'success', 'message' => 'Balance successfully trasnfered']);
                } catch (\Exception $ex) {
                    DB::rollback();
                    // throw $ex;
                    return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your balance is low kindly refill your wallet']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
        }
    }

    function payment_request_edit_now(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            $rules = array(
                'id' => 'required',
                'paymentmethod_id' => 'required',
                'bankdetail_id' => 'required',
                'bankref' => 'required|unique:loadcashes,bankref,' . $id,
                'bankref',
                'password' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $paymentmethod_id = $request->paymentmethod_id;
            $bankdetail_id = $request->bankdetail_id;
            $bankref = $request->bankref;
            $password = $request->password;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (Hash::check($password, $current_password)) {
                Loadcash::where('id', $id)->update([
                    'paymentmethod_id' => $paymentmethod_id,
                    'bankdetail_id' => $bankdetail_id,
                    'bankref' => $bankref,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function whitelistBankRequest(Request $request)
    {
        $bankDetails = WhiteListBank::where('status', 0)->where('type', 1)->select('*', DB::raw('(Select name from users where id = white_list_banks.user_id) as retailer_name'), DB::raw('(Select mobile from users where id = white_list_banks.user_id) as mobile'))->get();
        $data = array('page_title' => 'Bank Whitelist Request');
        return view('admin.white-list-bank.list', compact('bankDetails'))->with($data);
    }

    function whitelistMerchantBankRequest(Request $request)
    {
        $bankDetails = WhiteListBank::where('status', 0)->where('type', 2)->select('*', DB::raw('(Select first_name from merchant where id = white_list_banks.merchant_id) as retailer_name'), DB::raw('(Select mobile_number from merchant where id = white_list_banks.merchant_id) as mobile'))->get();
        $data = array('page_title' => 'Bank Whitelist Merchant Request');
        return view('admin.white-list-bank.merchant_list', compact('bankDetails'))->with($data);
    }

    function approveRejectBank(Request $request)
    {
        try {
            WhiteListBank::where('id', $request->id)->update(['status' => $request->type, 'approve_reject_by' => Auth::id()]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } catch (\Exception $exception) {
            return Response()->json(['status' => 'failure', 'message' => $exception->getMessage()]);
        }
    }
}
