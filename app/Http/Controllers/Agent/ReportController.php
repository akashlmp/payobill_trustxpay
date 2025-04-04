<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Report;
use App\Models\Provider;
use App\Models\Beneficiary;
use App\Models\Status;
use App\Models\Service;
use App\Models\State;
use \Crypt;
use App\Library\BasicLibrary;

class ReportController extends Controller
{
    function all_transaction_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $urls = url('agent/report/v1/all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $urls = url('agent/report/v1/all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        }
        $data = array(
            'page_title' => 'All Transaction Report',
            'report_slug' => 'All Transaction Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'urls' => $urls
        );
        $status = Status::select('id', 'status')->whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
        return view('agent.report.all_transaction_report', compact('status'))->with($data);
    }

    function all_transaction_report_api(Request $request)
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
        $user_id = Auth::id();

        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->count();

        // Fetch records

        $records = Report::query()->from('reports as r');
        if ($columnName == 'provider') {
            $records = $records->leftJoin('providers as p', 'p.id', '=', 'r.provider_id');
            $records = $records->orderBy('p.provider_name', $columnSortOrder);
        } elseif ($columnName = 'status') {
            $records = $records->orderBy('r.status_id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->select('r.state_id', 'r.id', 'r.created_at', 'r.provider_id', 'r.number', 'r.txnid', 'r.opening_balance', 'r.amount', 'r.profit', 'r.total_balance', 'r.wallet_type', 'r.status_id', 'r.tds','r.failure_reason')
            ->where('r.number', 'like', '%' . $searchValue . '%')
            ->where('r.user_id', $user_id)
            ->whereDate('r.created_at', '>=', $fromdate)
            ->whereDate('r.created_at', '<=', $todate)
            ->whereIn('r.status_id', $status_id)
            ->orderBy('r.id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            if ($value->wallet_type == 1) {
                $wallet_type = "Normal";
            } elseif ($value->wallet_type == 2) {
                $wallet_type = "Aeps";
            } else {
                $wallet_type = "";
            }

            $failure_reason = "N/A";
            if($value->status_id!=1){
                $failure_reason = $value->failure_reason ?? "N/A";
            }
            $states = State::find($value->state_id);
            $state_name = ($states) ? $states->name : 'All Zone';
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "tds" => number_format($value->tds, 2),
                "total_balance" => number_format($value->total_balance, 2),
                "wallet_type" => $wallet_type,
                "state_name" => $state_name,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "failure_reason" => $failure_reason,
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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


    function welcome(Request $request, $report_slug)
    {
        $library = new \App\Library\BasicLibrary;
        $companyActiveService = $library->getCompanyActiveService(Auth::id());
        $userActiveService = $library->getUserActiveService(Auth::id());
        $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('report_slug', $report_slug)->first();
        if ($services) {
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $status_id = $request->status_id;
                $apiUrl = url('agent/report/v1/search') . '/' . $services->report_slug . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $status_id = 0;
                $apiUrl = url('agent/report/v1/search') . '/' . $services->report_slug . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
            }
            $data = array(
                'page_title' => $services->service_name . ' History',
                'report_slug' => $report_slug,
                'fromdate' => $fromdate,
                'todate' => $todate,
                'status_id' => $status_id,
                'apiUrl' => $apiUrl,
                'searchURL' => url('agent/report/v1/welcome') . '/' . $report_slug,
            );
            $status = Status::select('id', 'status')->whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
            if ($services->servicegroup_id == 4) {
                return view('agent.report.banking_reports', compact('status'))->with($data);
            } elseif ($services->servicegroup_id == 5) {
                return view('agent.report.aeps_report', compact('status'))->with($data);
            } else {
                return view('agent.report.dynamic_reports', compact('status'))->with($data);
            }
        } else {
            return redirect()->back();
        }
    }

    function search_report(Request $request, $report_slug)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $status_id = $request->get('amp;status_id');
        $library = new \App\Library\BasicLibrary;
        $companyActiveService = $library->getCompanyActiveService(Auth::id());
        $userActiveService = $library->getUserActiveService(Auth::id());
        $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('report_slug', $report_slug)->first();
        if (empty($services)) {
            return Response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }

        if ($services->servicegroup_id == 4) {
            Self::bankingReport($request, $fromdate, $todate, $status_id, $services);
        } elseif ($services->servicegroup_id == 5) {
            Self::aepsReport($request, $fromdate, $todate, $status_id, $services);
        } else {
            Self::otherReport($request, $fromdate, $todate, $status_id, $services);
        }
    }

    function otherReport($request, $fromdate, $todate, $status_id, $services)
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
        $user_id = Auth::id();

        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }

        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->count();

        // Fetch records
        $records = Report::query()->from('reports as r');
        if ($columnName == 'provider') {
            $records = $records->leftJoin('providers as p', 'p.id', '=', 'r.provider_id');
            $records = $records->orderBy('p.provider_name', $columnSortOrder);
        } elseif ($columnName == 'status') {
            $records = $records->orderBy('r.status_id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->select('r.state_id', 'r.id', 'r.created_at', 'r.provider_id', 'r.failure_reason', 'r.number', 'r.txnid', 'r.opening_balance', 'r.amount', 'r.profit', 'r.total_balance', 'r.wallet_type', 'r.status_id', 'r.tds')
            ->where('r.number', 'like', '%' . $searchValue . '%')
            ->where('r.user_id', $user_id)
            ->whereDate('r.created_at', '>=', $fromdate)
            ->whereDate('r.created_at', '<=', $todate)
            ->whereIn('r.status_id', $status_id)
            ->whereIn('r.provider_id', $provider_id)
            ->orderBy('r.id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            if ($value->wallet_type == 1) {
                $wallet_type = "Normal";
            } elseif ($value->wallet_type == 2) {
                $wallet_type = "Aeps";
            } else {
                $wallet_type = "";
            }
            $states = State::find($value->state_id);
            $state_name = ($states) ? $states->name : 'All Zone';
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "tds" => number_format($value->tds, 2),
                "total_balance" => number_format($value->total_balance, 2),
                "wallet_type" => $wallet_type,
                "state_name" => $state_name,
                "failure_reason" => $value->failure_reason,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function bankingReport($request, $fromdate, $todate, $status_id, $services)
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
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);

        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }

        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->count();

        // Fetch records
        $records = Report::query()->from('reports as r');
        if ($columnName == 'provider') {
            $records = $records->leftJoin('providers as p', 'p.id', '=', 'r.provider_id');
            $records = $records->orderBy('p.provider_name', $columnSortOrder);
        } elseif ($columnName == 'status') {
            $records = $records->orderBy('r.status_id', $columnSortOrder);
        } elseif ($columnName == 'remiter_number') {
            $records = $records->orderBy('r.number', $columnSortOrder);
        } elseif ($columnName == 'payment_mode') {
            $records = $records->orderBy('r.channel', $columnSortOrder);
        } elseif ($columnName == 'bene_name' || $columnName == 'bank_name' || $columnName == 'ifsc' || $columnName == 'charges') {
            $records = $records->orderBy('r.id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->select('r.id', 'r.created_at', 'r.provider_id', 'r.number', 'r.failure_reason', 'r.txnid', 'r.amount', 'r.row_data', 'r.profit', 'r.total_balance', 'r.status_id', 'r.beneficiary_id', 'r.channel')
            ->where('r.number', 'like', '%' . $searchValue . '%')
            ->where('r.user_id', $user_id)
            ->whereDate('r.created_at', '>=', $fromdate)
            ->whereDate('r.created_at', '<=', $todate)
            ->whereIn('r.provider_id', $provider_id)
            ->whereIn('r.status_id', $status_id)
            ->orderBy('r.id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';

            $charges = empty($value->row_data) ? '' : (json_decode($value->row_data)->charges ?? '');
            $customer_charge = empty($value->row_data) ? '' : (json_decode($value->row_data)->customer_charge ?? '');
            $gst = empty($value->row_data) ? '' : number_format(json_decode($value->row_data)->gst ?? 0, 2);
            $tds = empty($value->row_data) ? '' : number_format(json_decode($value->row_data)->tds ?? 0, 2);
            $netCommission = empty($value->row_data) ? '' : number_format(json_decode($value->row_data)->netCommission ?? 0, 2);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "remiter_number" => $remiter_number,
                "number" => $value->number,
                "bene_name" => $bene_name,
                "bank_name" => $bank_name,
                "ifsc" => $ifsc,
                "amount" => number_format($value->amount, 2),
                "charges" => $charges,
                "txnid" => $value->txnid,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "payment_mode" => $payment_mode,
                "customer_charge" => $customer_charge,
                "gst" => $gst,
                "tds" => $tds,
                "netCommission" => $netCommission,
                "balance" => number_format($value->total_balance, 2),
                "failure_reason" => $value->failure_reason,
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function aepsReport($request, $fromdate, $todate, $status_id, $services)
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
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);

        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }

        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->where('wallet_type', 2)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->where('wallet_type', 2)
            ->count();

        // Fetch records

        $records = Report::query()->from('reports as r');
        if ($columnName == 'provider') {
            $records = $records->leftJoin('providers as p', 'p.id', '=', 'provider_id');
            $records = $records->orderBy('p.provider_name', $columnSortOrder);
        } elseif ($columnName == 'bank_name' || $columnName == 'aadhar_number') {
            $records = $records->leftJoin('aepsreports as ar', 'ar.report_id', '=', 'r.id');
            $records = $records->orderBy('ar.bank_name', $columnSortOrder);
            $records = $records->orderBy('ar.aadhar_number', $columnSortOrder);
        } elseif ($columnName == 'status') {
            $records = $records->orderBy('r.status_id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->select('r.id', 'r.created_at', 'r.provider_id', 'r.number', 'r.txnid', 'r.amount', 'r.status_id', 'r.failure_reason')
            ->where('r.number', 'like', '%' . $searchValue . '%')
            ->where('r.user_id', $user_id)
            ->whereDate('r.created_at', '>=', $fromdate)
            ->whereDate('r.created_at', '<=', $todate)
            ->whereIn('r.provider_id', $provider_id)
            ->whereIn('r.status_id', $status_id)
            ->where('r.wallet_type', 2)
            ->orderBy('r.id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                'bank_name' => (!empty($value->aepsreport->report_id)) ? $value->aepsreport->bank_name : '',
                'aadhar_number' => (!empty($value->aepsreport->report_id)) ? $value->aepsreport->aadhar_number : '',
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "failure_reason" => $value->failure_reason,
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function ledger_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('agent/report/v1/ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('agent/report/v1/ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Ledger Report',
            'report_slug' => 'Ledger Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );

        return view('agent.report.ledger_report')->with($data);
    }

    function ledger_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

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
        $user_id = Auth::id();

        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('wallet_type', 1)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->where('wallet_type', 1)
            ->count();

        // Fetch records

        $records = Report::query();
        if ($columnName == 'debit' || $columnName == 'credit') {
            $records = $records->orderBy('amount', $columnSortOrder);
        }elseif ($columnName == 'status'){
            $records = $records->orderBy('status_id', $columnSortOrder);
        }else{
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
       $records = $records->where('id', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('wallet_type', 1)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {

            if ($value->status_id == 1 || $value->status_id == 3 || $value->status_id == 7 || $value->status_id == 5) {
                $debit = '<span style="color: red;"><i class="fas fa-minus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $debit = 0;
            }

            if ($value->status_id == 2 || $value->status_id == 4 || $value->status_id == 6) {
                $credit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $credit = 0;
            }

            if ($value->profit < 0) {
                $profit = '<span style="color: red;"><i class="fas fa-minus-square"></i>  ' . number_format($value->profit, 2) . '</span>';
            } else {
                $profit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->profit, 2) . '</span>';
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "txnid" => $value->txnid,
                "description" => $value->description,
                "opening_balance" => number_format($value->opening_balance, 2),
                "debit" => $debit,
                "credit" => $credit,
                "profit" => $profit,
                "failure_reason" => $value->failure_reason,
                "total_balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
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

    function view_recharge_details(Request $request)
    {
        $id = $request->id;
        $reports = Report::where('id', $id)->where('user_id', Auth::id())->first();
        if ($reports) {
            $serviceData = Provider::where('id', $reports->provider_id)->first();
            $serviceId = $serviceData->service_id;
            $beneficiary = Beneficiary::find($reports->beneficiary_id);
            $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
            $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $remiter_name = (empty($beneficiary)) ? '' : $beneficiary->remiter_name;
            $moneydetails = array(
                'account_number' => $account_number,
                'ifsc' => $ifsc,
                'bank_name' => $bank_name,
                'name' => $name,
                'remiter_number' => $remiter_number,
                'remiter_name' => $remiter_name,
            );
            if ($reports->provider_id == 314 || $reports->provider_id == 315) {
                $receipt_anchor = url('agent/money-receipt') . '/' . $reports->mreportid;
                $mobile_receipt = url('agent/thermal-printer-receipt') . '/' . $reports->mreportid;
            } else if ($serviceId == 25) {
                $receipt_anchor = url('agent/cms-transaction-receipt') . '/' . Crypt::encrypt($reports->id);
                $mobile_receipt = url('agent/cms-mobile-receipt') . '/' . Crypt::encrypt($reports->id);
            } else {
                $receipt_anchor = url('agent/transaction-receipt') . '/' . Crypt::encrypt($reports->id);
                $mobile_receipt = url('agent/mobile-receipt') . '/' . Crypt::encrypt($reports->id);
            }
            $details = array(
                'receipt_anchor' => $receipt_anchor,
                'mobile_receipt' => $mobile_receipt,
                'dispute_anchor' => 'dispute_transaction(' . $reports->id . ')',
                'id' => $reports->id,
                'company' => $reports->user->company->company_name,
                'created_at' => "$reports->created_at",
                'user' => $reports->user->name,
                'provider' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'opening_balance' => number_format($reports->opening_balance, 2),
                'amount' => number_format($reports->amount, 2),
                'profit' => number_format($reports->profit, 2),
                'total_balance' => number_format($reports->total_balance, 2),
                'mode' => $reports->mode,
                'api_id' => $reports->api_id,
                'client_id' => $reports->client_id,
                'ip_address' => $reports->ip_address,
                'status_id' => $reports->status->status,
                'moneydetails' => $moneydetails,
            );
            return Response()->json([
                'status' => 'success',
                'details' => $details
            ]);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'record not found']);
        }
    }

    public function move_to_bank_history(Request $request)
    {
        // dd("move_to_bank_history");
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $apiUrl = url('agent/report/v1/move-to-bank-history-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $apiUrl = url('agent/report/v1/move-to-bank-history-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        }

        $data = array(
            'page_title' => 'Move To Bank History',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'apiUrl' => $apiUrl,
            'searchURL' => url('agent/report/v1/move-to-bank-history'),
        );
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7,11])->select('id', 'status')->get();


        return view('agent.report.move_to_bank_history', compact('status'))->with($data);

    }

    function move_to_bank_history_api(Request $request)
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

        $user_id = Auth::id();

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }


        $user_id = Auth::id();


        $provider_id=584;
        $provider_id = Provider::where('id',$provider_id)->where('service_id',19)->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where(function ($query) use ($searchValue) {
                $query->where('number', 'like', '%' . $searchValue . '%')
                    ->orWhere('opening_balance', 'like', '%' . $searchValue . '%')
                    ->orWhere('amount', $searchValue)
                    ->orWhere('profit', $searchValue)
                    ->orWhere('tds', $searchValue)
                    ->orWhereHas('user', function ($q) use ($searchValue) {
                        $q->where('name', 'like', '%' . $searchValue . '%');
                    });
            })
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->count();

        $records = Report::where(function ($query) use ($searchValue) {
                $query->where('number', 'like', '%' . $searchValue . '%')
                    ->orWhere('opening_balance', 'like', '%' . $searchValue . '%')
                    ->orWhere('amount', $searchValue)
                    ->orWhere('profit', $searchValue)
                    ->orWhere('tds', $searchValue)
                    ->orWhereHas('user', function ($q) use ($searchValue) {
                        $q->where('name', 'like', '%' . $searchValue . '%');
                    });
            })
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();
        foreach ($records as $value) {
            if ($value->wallet_type == 1) {
                $wallet_type = "Normal";
            } elseif ($value->wallet_type == 2) {
                $wallet_type = "Aeps";
            } else {
                $wallet_type = "";
            }
            $states = State::find($value->state_id);
            $state_name = ($states) ? $states->name : 'All Zone';



            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "tds" => number_format($value->tds, 2),
                "total_balance" => number_format($value->total_balance, 2),
                "wallet_type" => $wallet_type,
                "state_name" => $state_name,
                "failure_reason" => $value->failure_reason,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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
