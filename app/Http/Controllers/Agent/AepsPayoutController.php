<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Models\AepsPayoutRequest;
use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\PaysprintAepsBank;
use App\Models\PaysprintPayoutBank;
use App\Models\Provider;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAepsPayoutAccount;
use App\Paysprint\Payout as PaysprintAepsPayout;
use App\IServeU\Payout as IserveUPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Helpers;
use App\Models\Status;


class AepsPayoutController extends Controller
{
    protected $payout;
    protected $provider_id;

    public function __construct()
    {
        $this->company = Helpers::company_id();
        $this->payout = new PaysprintAepsPayout();
        $this->payout_iserveu = new IserveUPayout();
        if (env('AEPS_MODE', 'LIVE') == 'TEST') {
            $this->provider_id = 584;
        } else {
            $this->provider_id = 584;
        }
    }


    public function testIserveUPayout()
    {
        $provider_api_from = 3;
        $id = Auth::id();
        $params['page_title'] = 'Payout Request';
        $params['verified_accounts'] = UserAepsPayoutAccount::where(['status' => 1, 'user_id' => $id, 'provider_api_from' => 3])
            ->pluck('bank_name', 'id')->toArray();

        $params['accounts'] = UserAepsPayoutAccount::where('user_id', $id)->where('provider_api_from', $provider_api_from)->get();
        $params['aeps_banks'] = PaysprintPayoutBank::where('status', 1)->pluck('bank_name', 'bank_id')->toArray();
        $params['payouts'] = AepsPayoutRequest::where('user_id', $id)->get();
        return view('agent.payout.index_iserveu', $params);
    }

    public function index()
    {
        $provider_api_from = $this->company->payout_provider;
        $id = Auth::id();
        $params['page_title'] = 'Payout Request';
        if ($provider_api_from == 3) {
            $verified_accounts = UserAepsPayoutAccount::where(['status' => 1, 'user_id' => $id, 'provider_api_from' => 3])
                ->pluck('bank_name', 'id')->toArray();
        } else {
            $verified_accounts = UserAepsPayoutAccount::where(['status' => 1, 'user_id' => $id, 'provider_api_from' => $provider_api_from, 'document_verified' => 1])
                ->pluck('bank_name', 'bene_id')->toArray();
        }


        $urls = url('agent/payout-request-api');
        $data = array(
            'page_title' => 'Payouts',
            'urls' => $urls,
        );
        $accounts = UserAepsPayoutAccount::where('user_id', $id)->where('provider_api_from', $provider_api_from)->get();
        $aeps_banks = PaysprintPayoutBank::where('status', 1)->pluck('bank_name', 'bank_id')->toArray();
        // $payouts = AepsPayoutRequest::where('user_id', $id)->get();

        //return view('agent.payout.index_iserveu',compact('verified_accounts','accounts','aeps_banks'))->with($data);
        if ($this->company->payout_provider == 3) {
            return view('agent.payout.index_iserveu',compact('verified_accounts','accounts','aeps_banks'))->with($data);
        } else {
            return view('agent.payout.index', compact('verified_accounts','accounts','aeps_banks'))->with($data);
        }
    }

    function payoutRequestApi(Request $request)
    {

        // dd($request->all());
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

        $id = Auth::id();
        $totalRecords = AepsPayoutRequest::where('user_id', $id)->count();

        $totalRecordswithFilter = AepsPayoutRequest::where('user_id', $id)
                                ->where(function ($query) use ($searchValue) {
                                    $query->where('transaction_id', 'like', '%' . $searchValue . '%')
                                    ->orWhere('bank_name', 'like', '%' . $searchValue . '%')
                                    ->orWhere('bene_name', 'like', '%' . $searchValue . '%')
                                    ->orWhere('account_no', 'like', '%' . $searchValue . '%')
                                    ->orWhere('ifsc', 'like', '%' . $searchValue . '%')
                                    ->orWhere('mode', 'like', '%' . $searchValue . '%');

                                })->count();



        $records = AepsPayoutRequest::where('user_id', $id)->orderBy('id', 'DESC')
                    ->where(function ($query) use ($searchValue) {
                        $query->where('transaction_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('bank_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bene_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('account_no', 'like', '%' . $searchValue . '%')
                        ->orWhere('ifsc', 'like', '%' . $searchValue . '%')
                        ->orWhere('mode', 'like', '%' . $searchValue . '%');

                    })
                    ->skip($start)
                    ->take($rowperpage)
                    ->get();

        $data_arr = array();


        foreach ($records as $value) {



            if ($value->status == 0) {
            $status = '<span class="badge badge-danger">Failed</span>';
            }
            elseif ($value->status == 1) {
                $status = '<span class="badge badge-success">Success</span>';
            }elseif ($value->status == 2) {
                $status = '<span class="badge badge-warning">Pending</span>';
            }elseif ($value->status == 3) {
                $status = '<span class="badge badge-primary">Processed</span>';
            } elseif($value->status == 4) {
                $status = '<span class="badge badge-dark">Hold</span>';
            }
            $data_arr[] = array(

                "created_at" => "$value->created_at",
                "bank_name" => $value->bank_name ?? 'NA',
                "account_no" => $value->account_no  ?? 'NA',
                "transaction_id" =>$value->transaction_id ?? 'NA',
                "amount" => $value->amount  ?? 'NA',
                "status" => $status  ?? 'NA',
                "mode" => $value->mode  ?? 'NA',

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

    public function sendPayoutRequest(Request $request)
    {
        /** Validation */
        $validator = Validator::make(
            $request->all(),
            [
                'bank_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'failure', 'message' => $error]);
        }

        DB::beginTransaction();
        try {
            $id = $user_id = Auth::id();
            $userdetails = User::find($id);
            $scheme_id = $userdetails->scheme_id;
            $mode = $payment_mode = $request->payment_mode;
            $amount = $request->amount;
            $bene_id = $request->bank_id;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $provider_id = $this->provider_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount, 1);
            $retailer = $commission['retailer'];
            if ($payment_mode == "NEFT") {
                $retailer = 0;
            }
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $api_id = 1;
            $gst = 0;
            if ($payment_mode != "NEFT") {
                $gst = ($retailer * 18) / 100;
            }
            $ref_id = Helpers::generateReferenceID();
            $balance = Balance::where('user_id', $id)->first();
            $decrementAmount = $amount + $retailer + $gst;
            if (empty($balance) || $balance->aeps_balance < $decrementAmount) {
                return response()->json(['status' => 'failure', 'message' => 'Insufficient balance.']);
            }

            $row_data = [
                'charges' => 0,
                'gst' => $gst,
                'tds' => 0,
                'commission' => 0,
                'netCommission' => 0,
                'customer_charge' => $retailer,
            ];

            $account = UserAepsPayoutAccount::where(['user_id' => $id, 'bene_id' => $request->bank_id])->first();
            $account_number = $account->account_no;
            $data = [
                'user_id' => $id,
                'ref_id' => $ref_id,
                'bene_id' => $bene_id,
                'bank_name' => $account->bank_name,
                'ifsc' => $account->ifsc,
                'account_no' => $account_number,
                'bene_name' => $account->name,
                'mode' => $mode,
                'amount' => $amount,
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 2,
            ];

            $payout = AepsPayoutRequest::create($data);

            $res = $this->payout->doTransaction($bene_id, $amount, $ref_id, $mode);

            if (isset($res['status']) && $res['status'] == 'success') {
                $msg = isset($res['message']) ? $res['message'] : 'Payout Successfully received.';
                $api_res = isset($res['api_response']) ? $res['api_response'] : '{}';
                $transaction_id = isset($res['transaction_id']) ? $res['transaction_id'] : null;
                AepsPayoutRequest::where('id', $payout->id)->update(['transaction_id' => $transaction_id, 'message' => $msg, 'response' => $api_res]);


                $userdetails = User::find($id);
                $opening_balance = $userdetails->balance->aeps_balance;
                $mobile_no = $userdetails->mobile;
                $mode = "WEB";
                $request_ip = request()->ip();

                $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                $date = date('Y-m-d H:i:s');
                $description = $providers->provider_name . " to " . $account_number;

                Balance::where('user_id', $id)->decrement('aeps_balance', $decrementAmount);
                $balance = Balance::where('user_id', $id)->first();
                $total_balance = $balance->aeps_balance;

                $insert_id = Report::insertGetId([
                    'number' => $userdetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => 1,
                    'created_at' => $date,
                    'user_id' => $id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'txnid' => $transaction_id,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $total_balance,
                    'credit_by' => $id,
                    'wallet_type' => 2,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'provider_api_from' => 1,
                    'row_data' => json_encode($row_data),
                    'decrementAmount' => $decrementAmount,
                ]);

                AepsPayoutRequest::where('id', $payout->id)->update(['report_id' => $insert_id, 'status' => 1]);
                if ($payment_mode != "NEFT") {
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                    // get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                }
                DB::commit();
                return response()->json(['status' => 'success', 'message' => $msg]);
            } else {
                $msg = isset($res['message']) ? $res['message'] : 'Unable to process request.';
                $api_res = isset($res['api_response']) ? $res['api_response'] : '{}';
                AepsPayoutRequest::where('id', $payout->id)->update(['status' => 0, 'message' => $msg, 'response' => $api_res]);

                $userdetails = User::find($id);
                $opening_balance = $userdetails->balance->aeps_balance;
                $mobile_no = $userdetails->mobile;
                $mode = "WEB";
                $request_ip = request()->ip();
                $provider_id = $this->provider_id;
                $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                $date = date('Y-m-d H:i:s');
                $description = "$providers->provider_name  $mobile_no";
                $balance = Balance::where('user_id', $id)->first();
                $total_balance = $balance->aeps_balance;

                $insert_id = Report::insertGetId([
                    'number' => $userdetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 0,
                    'created_at' => $date,
                    'user_id' => $id,
                    'profit' => 0,
                    'mode' => $mode,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $total_balance,
                    'credit_by' => $id,
                    'wallet_type' => 2,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'provider_api_from' => 1
                ]);

                AepsPayoutRequest::where('id', $payout->id)->update(['report_id' => $insert_id]);

                DB::commit();
                return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Unable to process request.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'failure', 'message' => 'Unable to send payout request due to ' . $e->getMessage()]);
        }
    }

    public function addAccount(Request $request)
    {
        /** Validation */
        $validator = Validator::make(
            $request->all(),
            [
                'bank_id' => 'required',
                'account_no' => 'required',
                'ifsc' => 'required',
                'account_holder_name' => 'required',
                'account_type' => 'required|in:PRIMARY,RELATIVE',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'failure', 'message' => $error]);
        }
        try {
            $id = Auth::id();
            $merchant_id = Auth::user()->paysprint_merchantcode ?? Auth::user()->cms_agent_id;
            $bank_id = $request->bank_id;
            $account_no = $request->account_no;
            $ifsc = $request->ifsc;
            $name = $request->account_holder_name;
            $account_type = $request->account_type;
            $res = $this->payout->addBankAccount($merchant_id, $bank_id, $account_no, $ifsc, $name, $account_type);
            if (isset($res['status']) && $res['status'] == 'success') {
                return response()->json(['status' => 'success', 'message' => isset($res['message']) ? $res['message'] : 'Account added successfully.']);
            }
            return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Unable to add account.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => $e->getMessage()]);
        }
    }

    public function uploadDocument(Request $request)
    {
        /** Validation */
        $validator = Validator::make(
            $request->all(),
            [
                'bene_id' => 'required',
                'document_type' => 'required|in:PAN,AADHAAR',
                'passbook' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'pan' => 'required_if:document_type,PAN|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'front_aadhar' => 'required_if:document_type,AADHAAR|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'back_aadhar' => 'required_if:document_type,AADHAAR|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'failure', 'message' => $error]);
        }
        try {
            $bene_id = $request->bene_id;
            $passbook = $request->passbook;
            $doctype = $request->document_type;
            $panImage = $request->pan;
            $front_aadhar = isset($request->front_aadhar) ? $request->front_aadhar : null;
            $back_aadhar = isset($request->back_aadhar) ? $request->back_aadhar : null;
            $res = $this->payout->uploadDocument($bene_id, $passbook, $doctype, $panImage, $front_aadhar, $back_aadhar);
            if (isset($res['status']) && $res['status'] == 'success') {
                UserAepsPayoutAccount::where(['user_id' => Auth::id(), 'bene_id' => $bene_id])->update(['document_verified' => 1, 'status' => 1]);
                return response()->json(['status' => 'success', 'message' => isset($res['message']) ? $res['message'] : 'Account Documet upload successfully and verified']);
            }
            return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Unable to process request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => 'Unable to upload document due to ' . $e->getMessage()]);
        }
    }

    public function updateBankMaster()
    {
        $res = $this->payout->getBankMasterList();
        if (isset($res['status']) && $res['status'] == 'success') {
            if (isset($res['data']) && !empty($res['data']) && count($res['data']) > 0) {
                foreach ($res['data'] as $bank) {
                    $check_bank = PaysprintAepsBank::where('bank_id', $bank['id'])->count();
                    if ($check_bank == 0) {
                        PaysprintAepsBank::create([
                            'bank_id' => isset($bank['id']) ? $bank['id'] : 0,
                            'bank_name' => isset($bank['bankName']) ? $bank['bankName'] : null,
                            'iinno' => isset($bank['iinno']) ? $bank['iinno'] : null,
                            'status' => isset($bank['activeFlag']) ? $bank['activeFlag'] : 0,
                        ]);
                    }
                }
                return response()->json(['status' => 'success', 'message' => isset($res['message']) ? $res['message'] : 'Bank list updated']);
            }
        }
        return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Something went wrong.']);
    }

    public function updatePayoutAccount()
    {
        $id = Auth::id();
        $merchant_id = Auth::user()->paysprint_merchantcode ?? Auth::user()->cms_agent_id;
        $res = $this->payout->getBankList($merchant_id);
        if (isset($res['status']) && $res['status'] == 'success') {
            if (isset($res['data']) && !empty($res['data']) && count($res['data']) > 0) {
                foreach ($res['data'] as $bank) {
                    $save_data = [
                        'user_id' => $id,
                        'bene_id' => isset($bank['beneid']) ? $bank['beneid'] : null,
                        'merchant_code' => isset($bank['merchantcode']) ? $bank['merchantcode'] : null,
                        'bank_name' => isset($bank['bankname']) ? $bank['bankname'] : null,
                        'account_no' => isset($bank['account']) ? $bank['account'] : null,
                        'ifsc' => isset($bank['ifsc']) ? $bank['ifsc'] : null,
                        'name' => isset($bank['name']) ? $bank['name'] : null,
                        'account_type' => isset($bank['account_type']) ? $bank['account_type'] : null,
                        'document_verified' => isset($bank['verified']) ? $bank['verified'] : 0,
                        'status' => isset($bank['status']) ? $bank['status'] : 2,
                    ];
                    $checkAccount = UserAepsPayoutAccount::where(['user_id' => $id, 'account_no' => $bank['account']])->first();
                    if (!empty($checkAccount) && $checkAccount) {
                        $checkAccount->update($save_data);
                    } else {
                        UserAepsPayoutAccount::create($save_data)->first();
                    }
                }
                return response()->json(['status' => 'success', 'message' => isset($res['message']) ? $res['message'] : 'Bank list updated']);
            }
        }
        return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Something went wrong.']);
    }

    public function accountStatusCheck(Request $request)
    {
        /** Validation */
        $validator = Validator::make(
            $request->all(),
            [
                'bene_id' => 'required'
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'failure', 'message' => $error]);
        }
        try {
            $id = Auth::id();
            $merchant_id = Auth::user()->paysprint_merchantcode ?? Auth::user()->cms_agent_id;
            $bene_id = $request->bene_id;
            $res = $this->payout->accountStatusCheck($merchant_id, $bene_id);
            if (isset($res['status']) && $res['status'] == 'success') {
                return response()->json(['status' => 'success', 'message' => isset($res['message']) ? $res['message'] : 'Account added successfully.']);
            }
            return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Unable to add account.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => $e->getMessage()]);
        }
    }

    public function addAccountIserveU(Request $request)
    {
        /** Validation */
        $validator = Validator::make(
            $request->all(),
            [
                'bank_name' => 'required',
                'account_no' => 'required',
                'ifsc' => 'required',
                'account_holder_name' => 'required',
                'bene_phone_number' => 'required',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'failure', 'message' => $error]);
        }
        try {
            $id = Auth::id();
            $merchant_id = Auth::user()->cms_agent_id;
            $requestData =  $request->all();
            $this->payout_iserveu->addBankAccount($requestData, $id, $merchant_id);
            $message = 'Account added successfully.';
            if ($request->id) {
                $message = 'Account updated successfully.';
            }
            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => $e->getMessage()]);
        }
    }

    public function sendPayoutRequestIserveU(Request $request)
    {
        /** Validation */
        $validator = Validator::make(
            $request->all(),
            [
                'bank_id' => 'required',
                'payment_mode' => 'required',
                'amount' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'failure', 'message' => $error]);
        }

        DB::beginTransaction();
        try {
            $id = $user_id = Auth::id();
            $userdetails = User::find($id);
            $pincode = 201301; // $userdetails->member->pin_code ?? "";
            $scheme_id = $userdetails->scheme_id;
            $payment_mode = $request->payment_mode;
            $latlong = $request->latitude . "," . $request->longitude;
            $amount = $request->amount;
            $bene_id = $request->bank_id;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $provider_id = $this->provider_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount, 1);
            $retailer = $commission['retailer'];
            if ($payment_mode == "NEFT") {
                $retailer = config('common.payout_merchant_neft_charges')[3];
            }
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $api_id = 3;
            $gst = ($retailer * 18) / 100;
            $ref_id = Helpers::generateReferenceID();
            $balance = Balance::where('user_id', $id)->first();
            $decrementAmount = $amount + $retailer + $gst;
            if (empty($balance) || $balance->aeps_balance < $decrementAmount) {
                return response()->json(['status' => 'failure', 'message' => 'Insufficient balance.']);
            }

            $row_data = [
                'charges' => 0,
                'gst' => $gst,
                'tds' => 0,
                'commission' => 0,
                'netCommission' => 0,
                'customer_charge' => $retailer,
            ];

            $account = UserAepsPayoutAccount::where(['user_id' => $id, 'id' => $request->bank_id])->first();
            $account_number = $account->account_no;
            $data = [
                'user_id' => $id,
                'ref_id' => $ref_id,
                'bene_id' => $bene_id,
                'bank_name' => $account->bank_name,
                'ifsc' => $account->ifsc,
                'account_no' => $account_number,
                'bene_name' => $account->name,
                'mode' => $payment_mode,
                'amount' => $amount,
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 2,
                'bene_phone_number' => $account->bene_phone_number
            ];

            $payout = AepsPayoutRequest::create($data);
            $parameters =
                [
                    'beneName' => $account->name,
                    'beneAccountNo' => $account_number,
                    'beneifsc' => $account->ifsc,
                    'benePhoneNo' => (int) $account->bene_phone_number,
                    'beneBankName' => $account->bank_name,
                    'clientReferenceNo' => $ref_id,
                    'amount' => (float) $amount,
                    'fundTransferType' => $payment_mode,
                    'pincode' => $pincode,
                    'custName' => $userdetails->name,
                    'custMobNo' => $userdetails->mobile,
                    'custIpAddress' => $request->ip(),
                    'latlong' => $latlong,
                    'paramA' => "MINE",
                ];

            $res = $this->payout_iserveu->doTransaction($parameters);
            if (isset($res['status']) && ($res['status'] == 'success' || $res['status'] == 'pending')) {
                $msg = isset($res['message']) ? $res['message'] : 'Payout Successfully received.';
                $api_res = isset($res['api_response']) ? $res['api_response'] : '{}';
                $transaction_id = isset($res['transaction_id']) ? $res['transaction_id'] : null;
                $payid = isset($res['payid']) ? $res['payid'] : null;
                $status_id = 1;
                $status = 1;
                if ($res['status'] == 'pending') {
                    $status_id = 3;
                    $status = 2;
                }
                AepsPayoutRequest::where('id', $payout->id)->update(['transaction_id' => $transaction_id, 'utr' => $payid, 'message' => $msg, 'response' => $api_res]);

                $userdetails = User::find($id);
                $opening_balance = $userdetails->balance->aeps_balance;
                $mobile_no = $userdetails->mobile;
                $mode = "WEB";
                if(isset($request->mode) && $request->mode=="API"){
                    $mode = "API";
                }
                $request_ip = request()->ip();

                $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                $date = date('Y-m-d H:i:s');
                $description = $providers->provider_name . " to " . $account_number;

                Balance::where('user_id', $id)->decrement('aeps_balance', $decrementAmount);
                $balance = Balance::where('user_id', $id)->first();
                $total_balance = $balance->aeps_balance;

                $insert_id = Report::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => $status_id,
                    'created_at' => $date,
                    'user_id' => $id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'txnid' => $transaction_id,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $total_balance,
                    'credit_by' => $id,
                    'wallet_type' => 2,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'provider_api_from' => 3,
                    'row_data' => json_encode($row_data),
                    'decrementAmount' => $decrementAmount,
                    'payid' => $payid,
                    'reference_id'=>$ref_id
                ]);
                Apiresponse::insertGetId(['message' => $res['api_response'], 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $res['request_message'], 'response_type' => 'doTransaction']);

                AepsPayoutRequest::where('id', $payout->id)->update(['report_id' => $insert_id, 'status' => $status]);
                if ($payment_mode == "NEFT") {
                    $apiCommission = config('common.payout_admin_neft_charges')[3];;
                    $commissionType = "charges";
                } else {
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                }
                $library = new Commission_increment();
                $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);

                DB::commit();
                return response()->json(['status' => 'success', 'message' => $msg]);
            } else {
                $msg = isset($res['message']) ? $res['message'] : 'Unable to process request.';
                if (isset($res['api_response']) && $res['api_response'] != '') {
                    $api_res = $res['api_response'];
                } else {
                    $api_res = "{}";
                }
                $request_message = isset($res['request_message']) ? $res['request_message'] : '-';
                AepsPayoutRequest::where('id', $payout->id)->update(['status' => 0, 'message' => $msg, 'response' => $api_res]);

                $userdetails = User::find($id);
                $opening_balance = $userdetails->balance->aeps_balance;
                $mobile_no = $userdetails->mobile;
                $mode = "WEB";
                $request_ip = request()->ip();
                $provider_id = $this->provider_id;
                $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                $date = date('Y-m-d H:i:s');
                $description = "$providers->provider_name  $mobile_no";
                $balance = Balance::where('user_id', $id)->first();
                $total_balance = $balance->aeps_balance;
                $transaction_id = isset($res['transaction_id']) ? $res['transaction_id'] : null;
                $insert_id = Report::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => 2,
                    'created_at' => $date,
                    'user_id' => $id,
                    'profit' => 0,
                    'mode' => $mode,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $total_balance,
                    'credit_by' => $id,
                    'wallet_type' => 2,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'provider_api_from' => 3,
                    'txnid' => $transaction_id,
                    'failure_reason' => $msg,
                    'reference_id'=>$ref_id
                ]);
                Apiresponse::insertGetId(['message' => $api_res, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);

                AepsPayoutRequest::where('id', $payout->id)->update(['report_id' => $insert_id]);
                DB::commit();
                return response()->json(['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Unable to process request.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'failure', 'message' => 'Unable to send payout request due to ' . $e->getMessage()]);
        }
    }

    public function deleteAccountIserveU(Request $request)
    {
        $id = $request->id;
        UserAepsPayoutAccount::where("id", $id)->delete();
        return response()->json(['status' => 'success', 'message' => "Bank account deleted successfully."]);
    }

    public function listAccountIserveU(Request $request)
    {
        $provider_api_from = 3;
        $id = Auth::id();
        $params['verified_accounts'] = UserAepsPayoutAccount::where(['status' => 1, 'user_id' => $id, 'provider_api_from' => 3])
            ->pluck('bank_name', 'id')->toArray();
        $params['accounts'] = UserAepsPayoutAccount::where('user_id', $id)->where('provider_api_from', $provider_api_from)->get();
        $params['aeps_banks'] = PaysprintPayoutBank::where('status', 1)->pluck('bank_name', 'bank_id')->toArray();
        return Response()->json(['status' => 'success', 'message' => 'Success..', 'data' => $params]);
    }

    public function listPayoutRequest(Request $request)
    {
        $id = Auth::id();
        $fromdate = ($request->fromdate) ? $request->fromdate : date('Y-m-d', strtotime('-30 days'));
        $todate = ($request->todate) ? $request->todate : date('Y-m-d', time());
        $reports = AepsPayoutRequest::where('user_id', $id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = $value->toArray();
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);

    }

    public function listAccountPaysprint(Request $request)
    {
        $provider_api_from = 1;
        $id = Auth::id();
        $params['verified_accounts'] = UserAepsPayoutAccount::where(['status' => 1, 'user_id' => $id, 'provider_api_from' => $provider_api_from, 'document_verified' => 1])
        ->pluck('bank_name', 'bene_id')->toArray();
        $params['accounts'] = UserAepsPayoutAccount::where('user_id', $id)->where('provider_api_from', $provider_api_from)->get();
        $params['aeps_banks'] = PaysprintPayoutBank::where('status', 1)->pluck('bank_name', 'bank_id')->toArray();
        return Response()->json(['status' => 'success', 'message' => 'Success..', 'data' => $params]);
    }
}
