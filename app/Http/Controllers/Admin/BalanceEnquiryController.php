<?php

namespace App\Http\Controllers\Admin;

use Helpers;
use App\Models\Api;
use App\Models\User;
use App\Models\State;
use App\Models\Status;
use App\Models\Provider;
use App\Models\Sitesetting;
use Illuminate\Http\Request;
use App\Library\MemberLibrary;
use App\Models\BalanceEnquiry;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class BalanceEnquiryController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 : $sitesettings->backend_template_id;
    }

    function balance_enquiries_report(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['all_transaction_report_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $child_id = $request->child_id;
            $provider_id = $request->provider_id;
            $api_id = $request->api_id;
            $urls = url('admin/report/v1/balance-enquiries-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id . '&child_id=' . $child_id . '&provider_id=' . $provider_id . '&api_id=' . $api_id;;
        } else {
            $status_id = 0;
            $child_id = 0;
            $provider_id = 0;
            $api_id = 0;
            $fromdate = date('Y-m-d', strtotime('-7 days'));
            $todate = date('Y-m-d', time());
            $urls = url('admin/report/v1/balance-enquiries-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id . '&child_id=' . $child_id . '&provider_id=' . $provider_id . '&api_id=' . $api_id;;
        }
        $data = array(
            'page_title' => 'Balance Enquiry Report',
            'report_slug' => 'Balance Enquiry Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls,
            'status_id' => $status_id,
            'child_id' => $child_id,
            'provider_id' => $provider_id,
            'api_id' => $api_id,
        );
        $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7, 11])->select('id', 'status')->get();
        $users = User::whereIn('id', $my_down_member)->where('status_id', 1)->select('id', 'name', 'last_name')->get();
        $providers = Provider::where('status_id', 1)->select('id', 'provider_name')->get();
        if ($this->backend_template_id == 1) {
            return view('admin.report.balance_enquiries_report', compact('apis', 'status', 'users', 'providers'))->with($data);
        }else {
            return redirect()->back();
        }
    }

    function balance_enquiries_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $child_id = $request->get('amp;child_id');
        $status_id = $request->get('amp;status_id');
        $provider_id = $request->get('amp;provider_id');
        $api_id = $request->get('amp;api_id');
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

        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        if ($status_id == 0) {
            $status_id = Status::get('id');
        } else {
            $status_id = Status::where('id', $status_id)->get('id');
        }
        if ($child_id == 0) {
            $child_id = User::whereIn('id', $my_down_member)->get('id');
        } else {
            $child_id = User::whereIn('id', $my_down_member)->where('id', $child_id)->get('id');
        }

        // if ($provider_id == 0) {
        //     $provider_id = Provider::get('id');
        // } else {
        //     $provider_id = Provider::where('id', $provider_id)->get('id');
        // }
        $provider_id = array(318);

        if (Auth::User()->role_id == 1) {
            if ($api_id == 0) {
                $database_api = Api::get(['id'])->toArray();
                $old_id = Status::where('id', 0)->get('id')->toArray();
                $api_id = array_merge($database_api, $old_id);
            } else {
                $api_id = Api::where('id', $api_id)->get('id');
            }
        } else {
            $api_id = Api::get('id');
        }

        $totalRecords = BalanceEnquiry::select('count(*) as allcount')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('user_id', $child_id)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('api_id', $api_id)
            ->count();

        if (!empty($searchValue)) {
            $totalRecordswithFilter = BalanceEnquiry::select('count(*) as allcount')
                ->whereIn('user_id', $child_id)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('api_id', $api_id)
                ->where(function ($query) use ($searchValue) {
                    $query->where('id', 'like', '%' . $searchValue . '%')
                        ->orWhere('number', 'like', '%' . $searchValue . '%')
                        ->orWhere('opening_balance', 'like', '%' . $searchValue . '%')
                        ->orWhere('amount', $searchValue)
                        ->orWhere('profit', $searchValue)                       
                        ->orWhere('txnid', 'like', '%' . $searchValue . '%')
                        ->orWhereHas('user', function ($q) use ($searchValue) {
                            $q->where('name', 'like', '%' . $searchValue . '%');
                        });
                })->count();

            $records = BalanceEnquiry::query();
            if (in_array($columnName, ['user'])) {
                $records = $records->orderBy('id', $columnSortOrder);
            } else {
                $records = $records->orderBy($columnName, $columnSortOrder);
            }
           echo $records = $records->whereIn('user_id', $child_id)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('api_id', $api_id)
                ->select('state_id', 'user_id', 'api_id', 'id', 'created_at', 'provider_id', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id', 'mode', 'failure_reason', 'provider_api_from')
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('id', 'like', '%' . $searchValue . '%')
                        ->orWhere('number', 'like', '%' . $searchValue . '%')
                        ->orWhere('opening_balance', $searchValue)
                        ->orWhere('amount', $searchValue)
                        ->orWhere('profit', $searchValue)                        
                        ->orWhere('txnid', 'like', '%' . $searchValue . '%')
                        ->orWhereHas('user', function ($q) use ($searchValue) {
                            $q->where('name', 'like', '%' . $searchValue . '%');
                        });                    
                })->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecordswithFilter = BalanceEnquiry::select('count(*) as allcount')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('number', 'like', '%' . $searchValue . '%')
                ->whereIn('user_id', $child_id)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('api_id', $api_id)
                ->count();

            $records = BalanceEnquiry::query();
            if (in_array($columnName, ['user', 'provider', 'status', 'state'])) {
                $records = $records->orderBy('id', $columnSortOrder);
            } else {
                $records = $records->orderBy($columnName, $columnSortOrder);
            }
            $records = $records->where('number', 'like', '%' . $searchValue . '%')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('user_id', $child_id)
                ->whereIn('api_id', $api_id)
                ->select('state_id', 'user_id', 'api_id', 'id', 'created_at', 'provider_id', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id', 'mode', 'failure_reason', 'provider_api_from')
                ->orderBy('id', 'DESC')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        }
        $data_arr = array();
        foreach ($records as $value) {
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $states = State::find($value->state_id);
            $state_name = ($states) ? $states->code : 'All Zone';
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = ($apis) ? $apis->api_name : $this->brand_name;
            } else {
                $vendor = $this->brand_name;
            }
            $payFrom = providerType($value->provider_api_from);            
            
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "total_balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '" >' . $value->status->status . '</span>',
                "mode" => $value->mode,
                "provider_api_from" => $payFrom,
                "state" => $state_name,
                "vendor" => $vendor,
                "failure_reason" => $value->failure_reason,
                "view" => '<button class="btn btn-danger btn-sm mr-2" onclick="view_bal_transaction_logs(' . $value->id . ')"><i class="fas fa-history"></i>  Api Logs</button>',
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
}
