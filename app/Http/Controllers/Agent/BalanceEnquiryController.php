<?php

namespace App\Http\Controllers\Agent;

use App\Models\State;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Models\BalanceEnquiry;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BalanceEnquiryController extends Controller
{
    function balance_enquiries_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $urls = url('agent/report/v1/balance-enquiries-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        } else {
            $fromdate = date('Y-m-d', strtotime('-7 days'));
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $urls = url('agent/report/v1/balance-enquiries-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        }
        $data = array(
            'page_title' => 'Balance Enquiry Report',
            'report_slug' => 'Balance Enquiry Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'urls' => $urls
        );
        $status = Status::select('id', 'status')->whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
        return view('agent.report.balance-enquiries-report', compact('status'))->with($data);
    }

    function balance_enquiries_report_api(Request $request)
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
        $totalRecords = BalanceEnquiry::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->count();

        $totalRecordswithFilter = BalanceEnquiry::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->count();

        // Fetch records

        $records = BalanceEnquiry::query()->from('balance_enquiries as b');
        if ($columnName == 'provider') {
            $records = $records->leftJoin('providers as p', 'p.id', '=', 'b.provider_id');
            $records = $records->orderBy('p.provider_name', $columnSortOrder);
        } elseif ($columnName = 'status') {
            $records = $records->orderBy('b.status_id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->select('b.state_id', 'b.id', 'b.created_at', 'b.provider_id', 'b.number', 'b.txnid', 'b.opening_balance', 'b.amount', 'b.profit', 'b.total_balance', 'b.wallet_type', 'b.status_id','b.failure_reason')
            ->where('b.number', 'like', '%' . $searchValue . '%')
            ->where('b.user_id', $user_id)
            ->whereDate('b.created_at', '>=', $fromdate)
            ->whereDate('b.created_at', '<=', $todate)
            ->whereIn('b.status_id', $status_id)
            ->orderBy('b.id', 'DESC')
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
                "total_balance" => number_format($value->total_balance, 2),
                "wallet_type" => $wallet_type,
                "state_name" => $state_name,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "failure_reason" => $failure_reason,
                //"view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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
