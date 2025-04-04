<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apicommreport;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Api;
use App\Models\Report;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\User;
use Helpers;
use App\Models\Loginlog;
use App\Models\Sitesetting;
use App\Models\Service;
use App\Models\Company;
use App\Library\SmsLibrary;
use App\Library\MemberLibrary;
use App\Library\BasicLibrary;
use Carbon\Carbon;


class DashboardController extends Controller
{


    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 : $sitesettings->backend_template_id;
        $api = Api::where('vender_id', 10)->first();
        $this->key = (empty($api)) ? '' : 'Bearer ' . $api->api_key;
        // get company details
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
    }

    function dashboard()
    {
        if (Auth::User()->role_id <= 7) {
            $data = array(
                'page_title' => 'Dashboard',
                'urls' => url('admin/top-seller'),
            );
            if ($this->backend_template_id == 1) {
                return view('admin.dashboard')->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.dashboard')->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.dashboard')->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.dashboard')->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect('agent/dashboard');
        }
    }


    function dashboard_data_api(Request $request)
    {
        self::send_balance_alert();
        if (Auth::User()->role_id == 1) {
            $balace = $this->getApiBalance();
            $api_balance = $balace['normal_balance'];
            $aeps_api_balance = $balace['aeps_balance'];
        } else {
            $api_balance = 0;
            $aeps_api_balance = 0;
        }
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $normal_sale = Report::whereIn('user_id', $my_down_member)->whereIn('status_id', [1, 3, 8])->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $aeps_sale = Report::whereIn('user_id', $my_down_member)->whereIn('status_id', [6])->whereIn('provider_id', [319, 320, 321])->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        if (Auth::User()->role_id == 8 || Auth::User()->role_id == 9 || Auth::User()->role_id == 10) {
            $today_profit = Report::where('user_id', Auth::id())->whereIn('status_id', [1])->whereDate('created_at', '=', date('Y-m-d'))->sum('profit');
        } elseif (Auth::User()->role_id == 1) {
            $provider_id = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15])->get(['id']);
            $user_profit = Report::whereIn('user_id', $my_down_member)->whereIn('provider_id', $provider_id)->whereIn('status_id', [1])->whereDate('created_at', '=', date('Y-m-d'))->sum('profit');
            $distributor_profit = Report::whereIn('user_id', $my_down_member)->whereIn('provider_id', $provider_id)->whereIn('status_id', [1])->whereDate('created_at', '=', date('Y-m-d'))->sum('distributor_comm');
            $super_profit = Report::whereIn('user_id', $my_down_member)->whereIn('provider_id', $provider_id)->whereIn('status_id', [1])->whereDate('created_at', '=', date('Y-m-d'))->sum('super_distributor_comm');
            $api_profit = Report::whereIn('user_id', $my_down_member)->whereIn('provider_id', $provider_id)->whereIn('status_id', [1])->whereDate('created_at', '=', date('Y-m-d'))->sum('api_comm');
            $today_profit = $api_profit - $user_profit - $distributor_profit - $super_profit;
            $records = Apicommreport::query();
            $records = $records->where('status_id', 1)
                ->groupBy('api_id')
                ->selectRaw('*, sum(amount) as amount, sum(apiCharge) as apiCharge, sum(apiCommission) as apiCommission, sum(retailerCharge) as retailerCharge, sum(retailerComm) as retailerComm, sum(totalProfit) as totalProfit')
                ->whereDate('created_at', '=', date('Y-m-d'))
                ->whereNotIn('api_id', [0])
                ->first();
            if ($records) {
                $today_profit = number_format($records->totalProfit, 2);
            } else {
                $today_profit = 0;
            }
        } else {
            $today_profit = Report::where('user_id', Auth::id())->whereIn('status_id', [6])->whereDate('created_at', '=', date('Y-m-d'))->sum('profit');
        }
        $sales = array(
            'today_sale' => number_format($normal_sale, 2),
            'aeps_sale' => number_format($aeps_sale, 2),
            'today_profit' => number_format($today_profit, 2),
        );
        $balaces = array(
            'api_balance' => $api_balance,
            'aeps_api_balance' => $aeps_api_balance,
        );
        return Response()->json([
            'status' => 'success',
            'balance' => $balaces,
            'sales' => $sales,
        ]);
    }


    function getApiBalance()
    {
        return ['normal_balance' => 0, 'aeps_balance' => 0];
        $api = Api::where('company_id', Auth::User()->company_id)->where('vender_id', 10)->first();
        $key = 'Bearer ' . $api->api_key;
        $url = "";
        $api_request_parameters = array();
        $method = 'GET';
        $header = ["Accept:application/json", "Authorization:" . $key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $normal_balance = (empty($res->balance)) ? 0 : $res->balance;
        $aeps_balance = (empty($res->aeps_balance)) ? 0 : $res->aeps_balance;
        return ['normal_balance' => $normal_balance, 'aeps_balance' => $aeps_balance];
    }

    function dashboard_chart_api(Request $request)
    {
        $datefrom = date('Y-m-d', time());
        $dateto = date('Y-m-d', time());
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        $reports = Report::whereIn('user_id', $my_down_member)
            ->where('status_id', 1)
            ->whereDate('created_at', '>=', $datefrom)
            ->whereDate('created_at', '<=', $dateto)
            ->groupBy('provider_id')
            ->selectRaw('provider_id, sum(amount) as op_amount, sum(profit) as op_profit')
            ->orderBy('provider_id', 'DESC')
            ->get();

        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["amount"] = $value->op_amount;
            $product["provider_name"] = $value->provider->provider_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'provider' => $response]);
    }

    function dashboard_details_api()
    {

        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $today_success = Report::whereIn('user_id', $my_down_member)->where('status_id', 1)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_failure = Report::whereIn('user_id', $my_down_member)->whereIn('status_id', [2, 5])->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_pending = Report::whereIn('user_id', $my_down_member)->where('status_id', 3)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_refunded = Report::whereIn('user_id', $my_down_member)->where('status_id', 4)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_credit = Report::where('user_id', Auth::id())->where('status_id', 6)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_debit = Report::where('user_id', Auth::id())->where('status_id', 7)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');

        $normal_distributed_balance = Balance::whereIn('user_id', $my_down_member)->whereNotIn('user_id', [Auth::id()])->sum('user_balance');
        $aeps_distributed_balance = Balance::whereIn('user_id', $my_down_member)->whereNotIn('user_id', [Auth::id()])->sum('aeps_balance');
        $total_members = User::whereIn('id', $my_down_member)->whereNotIn('id', [Auth::id()])->count();
        $total_suspended_users = User::whereIn('id', $my_down_member)->whereNotIn('active', [1])->count();
        $balances = array(
            'normal_distributed_balance' => number_format($normal_distributed_balance, 2),
            'aeps_distributed_balance' => number_format($aeps_distributed_balance, 2),
            'my_balances' => number_format(Auth::User()->balance->user_balance, 2),
            'dashboard_total_members' => $total_members,
            'dashboard_total_suspended_users' => $total_suspended_users,

        );

        $total_row = Report::whereIn('user_id', $my_down_member)->whereIn('status_id', [1, 2, 3, 5])->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        if (empty($today_success && $total_row)) {
            $percentage = array(
                'success_percentage' => 0,
                'failure_percentage' => 0,
                'pending_percentage' => 0,
            );
        } else {
            $percentage = array(
                'success_percentage' => number_format(100 * $today_success / $total_row, 2) . '%',
                'failure_percentage' => number_format(100 * $today_failure / $total_row, 2) . '%',
                'pending_percentage' => number_format(100 * $today_pending / $total_row, 2) . '%',
            );
        }

        $sales_overview = array(
            'today_success' => number_format($today_success, 2),
            'today_failure' => number_format($today_failure, 2),
            'today_pending' => number_format($today_pending, 2),
            'today_refunded' => number_format($today_refunded, 2),
            'today_credit' => number_format($today_credit, 2),
            'today_debit' => number_format($today_debit, 2),
        );
        return Response()->json([
            'status' => 'success',
            'sales_overview' => $sales_overview,
            'percentage' => $percentage,
            'balances' => $balances,
        ]);
    }

    function activity_logs(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
        }
        $loginlogs = Loginlog::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'desc')
            ->get();
        $data = array(
            'page_title' => 'Activity Logs',
            'fromdate' => $fromdate,
            'todate' => $todate,
        );
        return view('admin.activity_logs', compact('loginlogs'))->with($data);
    }

    function send_balance_alert()
    {
        $company_id = Auth::User()->company_id;
        $sitesettings = Sitesetting::where('company_id', $company_id)->first();
        if ($sitesettings) {
            $alert_amount = $sitesettings->alert_amount;
        } else {
            $alert_amount = 500;
        }
        $balances = Balance::where('balance_alert', 1)->get();
        foreach ($balances as $value) {
            $user_id = $value->user_id;
            $user_balance = $value->user_balance;
            if ($alert_amount >= $user_balance) {
                Balance::where('user_id', $user_id)->update(['balance_alert' => 0]);
                $userdetails = User::find($user_id);
                // $message = "Dear $userdetails->name $userdetails->last_name your balance is low : $user_balance kindly refill your wallet $this->brand_name";
                $message = "Dear user, your balance is low: ₹$user_balance. Kindly load your trustxpay wallet to continue business transactions. For more info: trustxpay.org PAOBIL";
                $template_id = 12;
                $whatsappArr = [$user_balance];
                $library = new SmsLibrary();
                $library->send_sms($userdetails->mobile, $message, $template_id, $whatsappArr);
            }
        }
    }

    function top_seller(Request $request)
    {
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

        $totalRecords = 10;
        $totalRecordswithFilter = 10;

        // Fetch records
        $start = Carbon::now()->startOfMonth();
        $fromdate = $start->format('Y-m-d');
        $todate = date('Y-m-d', time());
        $records = Report::whereIn('user_id', $my_down_member)
            ->whereIn('status_id', [1, 6])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->groupBy('user_id')
            ->selectRaw('user_id, sum(amount) as total_amount, sum(profit) as total_profit')
            ->orderBy('total_amount', 'DESC')
            ->paginate(10);

        $data_arr = array();
        $i = 1;
        foreach ($records as $value) {
            $data_arr[] = array(
                "sr_no" => $i++,
                "username" => $value->user->name . ' ' . $value->user->last_name,
                "total_amount" => number_format($value->total_amount, 2),
                "total_profit" => number_format($value->total_profit, 2),
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


    function getServiceWiseSales()
    {
        $library = new BasicLibrary();
        $companyActiveService = $library->getCompanyActiveService(Auth::id());
        $userActiveService = $library->getUserActiveService(Auth::id());
        $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->get();
        $results = '<div class="row row-sm">';
        foreach ($services as $value) {
            $toDaySale = Self::getServiceWiseTodaySales($value->id);
            $todaySuccess = $toDaySale['todaySuccess'];
            $todayFailure = $toDaySale['todayFailure'];
            $results .= '<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            <div class="card overflow-hidden project-card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="my-auto">
                                            <!--<svg enable-background="new 0 0 512 512" class="mr-4 ht-60 wd-60 my-auto warning" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
												<path d="m259.2 317.72h-6.398c-8.174 0-14.824-6.65-14.824-14.824 1e-3 -8.172 6.65-14.822 14.824-14.822h6.398c8.174 0 14.825 6.65 14.825 14.824h29.776c0-20.548-13.972-37.885-32.911-43.035v-33.74h-29.777v33.739c-18.94 5.15-32.911 22.487-32.911 43.036 0 24.593 20.007 44.601 44.601 44.601h6.398c8.174 0 14.825 6.65 14.825 14.824s-6.65 14.824-14.825 14.824h-6.398c-8.174 0-14.824-6.65-14.824-14.824h-29.777c0 20.548 13.972 37.885 32.911 43.036v33.739h29.777v-33.74c18.94-5.15 32.911-22.487 32.911-43.035 0-24.594-20.008-44.603-44.601-44.603z"/>
                                                <path d="m502.7 432.52c-7.232-60.067-26.092-111.6-57.66-157.56-27.316-39.764-65.182-76.476-115.59-112.06v-46.29l37.89-98.425-21.667-0.017c-6.068-4e-3 -8.259-1.601-13.059-5.101-6.255-4.559-14.821-10.802-30.576-10.814h-0.046c-15.726 0-24.292 6.222-30.546 10.767-4.799 3.487-6.994 5.081-13.041 5.081h-0.027c-6.07-5e-3 -8.261-1.602-13.063-5.101-6.255-4.559-14.821-10.801-30.577-10.814h-0.047c-15.725 0-24.293 6.222-30.548 10.766-4.8 3.487-6.995 5.081-13.044 5.081h-0.027l-21.484-0.017 36.932 98.721v46.117c-51.158 36.047-89.636 72.709-117.47 111.92-33.021 46.517-52.561 98.116-59.74 157.74l-9.304 77.285h512l-9.304-77.284zm-301.06-395.47c4.8-3.487 6.995-5.081 13.045-5.081h0.026c6.07 4e-3 8.261 1.602 13.062 5.101 6.255 4.559 14.821 10.802 30.578 10.814h0.047c15.725 0 24.292-6.222 30.546-10.767 4.799-3.487 6.993-5.081 13.041-5.081h0.026c6.068 5e-3 8.259 1.602 13.059 5.101 2.869 2.09 6.223 4.536 10.535 6.572l-21.349 55.455h-92.526l-20.762-55.5c4.376-2.041 7.773-4.508 10.672-6.614zm98.029 91.89v26.799h-83.375v-26.799h83.375zm-266.09 351.08 5.292-43.947c6.571-54.574 24.383-101.7 54.458-144.07 26.645-37.537 62.54-71.458 112.73-106.5h103.78c101.84 71.198 150.75 146.35 163.29 250.56l5.291 43.948h-444.85z"/>
											</svg>-->
											<img src="' . $this->cdnLink . '' . $value->service_image . '" style="width: 80px;">
                                        </div>

                                        <div class="project-content" style="margin-left: 10%;">
                                            <h6>' . $value->service_name . '</h6>
                                            <ul>
                                                <li>
                                                    <strong class="text-success">Success</strong>
                                                    <span>₹ ' . $todaySuccess . '</span>
                                                </li>
                                                <li>
                                                    <strong class="text-danger">Failure</strong>
                                                    <span>₹ ' . $todayFailure . '</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
        }
        // $results = '</div>';
        return $results;
    }

    function getServiceWiseTodaySales($service_id)
    {
        $provider_id = Provider::where('service_id', $service_id)->get(['id']);
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        $todayQuery = Report::query();
        if (Auth::user()->role_id == 1) {
        } else {
            $todayQuery->whereIn('user_id', $my_down_member);
        }
        if ($service_id == 19) {
            $todayQuery->whereIn('provider_id', [319, 320, 321])
                ->where('status_id', 6);
        } else {
            $todayQuery->whereIn('provider_id', $provider_id)
                ->where('status_id', 1);
        }
        $todaySuccess = $todayQuery->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');

        $failureQuery = Report::query();
        if (Auth::user()->role_id == 1) {
        } else {
            $failureQuery->whereIn('user_id', $my_down_member);
        }
        $todayFailure = $failureQuery->where('status_id', 2)->whereDate('created_at', '=', date('Y-m-d'))->whereIn('provider_id', $provider_id)->sum('amount');

        return [
            'todaySuccess' => number_format($todaySuccess, 2),
            'todayFailure' => number_format($todayFailure, 2)
        ];
    }
}
