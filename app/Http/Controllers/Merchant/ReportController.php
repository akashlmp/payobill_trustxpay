<?php

namespace App\Http\Controllers\Merchant;

use \Crypt;
use Validator;
use App\Models\Api;
use App\Models\State;
use App\Models\Status;
use App\Models\Service;
use App\Models\Provider;
use http\Client\Response;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use App\Library\BasicLibrary;
use App\Models\MerchantPayouts;
use App\Http\Controllers\Controller;
use App\Models\MerchantTransactions;
use Illuminate\Support\Facades\Auth;
use App\Models\MerchantTestTransaction;
use Illuminate\Support\Facades\Redirect;

class ReportController extends Controller
{
    function all_transaction_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $urls = url('merchant/all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $urls = url('merchant/all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        }
        $data = array(
            'page_title' => 'All Transactions',
            'report_slug' => 'All Transactions',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'urls' => $urls
        );
        $status = Status::select('id', 'status')->whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
        return view('merchant.report.all_transaction_report', compact('status'))->with($data);
    }

    function test_transaction_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $urls = url('merchant/test-all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        } else {
            $fromdate = date('Y-m-d', strtotime('-7 days'));
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $urls = url('merchant/test-all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
        }
        $data = array(
            'page_title' => 'Test Transactions',
            'report_slug' => 'Test Transactions',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'urls' => $urls
        );
        $status = Status::select('id', 'status')->whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
        return view('merchant.report.test_transaction_report', compact('status'))->with($data);
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
        $user_id = Auth::guard('merchant')->id();

        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $totalRecords = MerchantTransactions::select('count(*) as allcount')
            ->where('merchant_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->count();

        $totalRecordswithFilter = MerchantTransactions::select('count(*) as allcount')
            ->where('merchant_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('account_number', 'like', '%' . $searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->count();

        // Fetch records

        $records = MerchantTransactions::query()->from('merchant_transactions as r');
        if ($columnName == 'provider') {
            $records = $records->leftJoin('providers as p', 'p.id', '=', 'r.provider_id');
            $records = $records->orderBy('p.provider_name', $columnSortOrder);
        } elseif ($columnName = 'status') {
            $records = $records->orderBy('r.status_id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }

        $records = $records->select( 'r.id', 'r.created_at', 'r.provider_id', 'r.account_number', 'r.transaction_id', 'r.opening_balance', 'r.amount', 'r.profit', 'r.total_balance', 'r.status_id', 'r.tds','r.gst')
            // ->where('r.account_number', 'like', '%' . $searchValue . '%')
            ->where('r.merchant_id', $user_id)
            ->whereDate('r.created_at', '>=', $fromdate)
            ->whereDate('r.created_at', '<=', $todate)
            ->whereIn('r.status_id', $status_id)
            ->orderBy('r.id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $wallet_type = "";
            // $states = State::find($value->state_id);
            $state_name = 'All Zone';
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->account_number,
                "txnid" => $value->transaction_id,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "gst" => number_format($value->gst, 2),
                // "tds" => number_format($value->tds, 2),
                "total_balance" => number_format($value->total_balance, 2),
                // "wallet_type" => $wallet_type,
                // "state_name" => $state_name,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "failure_reason" => $value->failure_reason ?? "NA",
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

    function test_all_transaction_report_api(Request $request)
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
        $user_id = Auth::guard('merchant')->id();

        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $totalRecords = MerchantTestTransaction::select('count(*) as allcount')
            ->where('merchant_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->count();

        $totalRecordswithFilter = MerchantTestTransaction::select('count(*) as allcount')
            ->where('merchant_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('account_number', 'like', '%' . $searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->count();

        // Fetch records

        $records = MerchantTestTransaction::query()->from('merchant_test_transactions as r');
        if ($columnName = 'status') {
            $records = $records->orderBy('r.status_id', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }

        $records = $records->select( 'r.id', 'r.created_at', 'r.account_number', 'r.mode', 'r.utr', 'r.ben_name', 'r.transaction_id', 'r.merchant_reference_id', 'r.ben_phone_number', 'r.ben_bank_name', 'r.amount', 'r.status_id' , 'r.failure_reason')
            ->where('r.account_number', 'like', '%' . $searchValue . '%')
            ->where('r.merchant_id', $user_id)
            ->whereDate('r.created_at', '>=', $fromdate)
            ->whereDate('r.created_at', '<=', $todate)
            ->whereIn('r.status_id', $status_id)
            ->orderBy('r.id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();

        foreach ($records as $value) {
            $failure_reason = "N/A";
            if($value->status->id ==1 || $value->status->id==3){
                $failure_reason = "N/A";
            }else{
                $failure_reason = $value->failure_reason ?? "NA";
            }
            $data_arr[] = array(
                "id" => "",
                "created_at" => "$value->created_at",
                "number" => $value->account_number,
                "name" => $value->ben_name,
                "amount" => number_format($value->amount, 2),
                "merchant_ref_id" => $value->merchant_reference_id,
                "transaction_id" => $value->transaction_id,
                "phone_number" => $value->ben_phone_number,
                "bank_name" => $value->ben_bank_name,
                "mode" => $value->mode,
                "utr" => $value->utr,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "failure_reason" => $failure_reason,
                //"view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
            );
        }
        //pre($data_arr);
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
        $user_id = Auth::guard('merchant')->id();
        $reports = MerchantTransactions::where('id', $id)->where('merchant_id', $user_id)->first();
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

            $details = array(
                'receipt_anchor' => "",
                'mobile_receipt' => "",
                'dispute_anchor' => 'dispute_transaction(' . $reports->id . ')',
                'id' => $reports->id,
                'company' => "",
                'created_at' => "$reports->created_at",
                'user' => $reports->merchant->name,
                'provider' => $reports->provider->provider_name,
                'number' => $reports->account_number,
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

    public function payoutReports(Request $request)
    {
        $urls = url('merchant/payouts-api');
        $data = array(
            'page_title' => 'Payouts',
            'urls' => $urls,
        );
        $params['payouts'] = MerchantPayouts::get();
        return view('merchant.report.payouts')->with($data);

    }

    function payoutReportsApi(Request $request)
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
        $user_id = Auth::guard('merchant')->id();
        $totalRecords = MerchantPayouts::select('count(*) as allcount')->where('merchant_id', $user_id)->count();

        $totalRecordswithFilter = MerchantPayouts::select('count(*) as allcount')
                                ->where('merchant_id', $user_id)
                                ->where(function ($query) use ($searchValue) {
                                    $query->where('transaction_id', 'like', '%' . $searchValue . '%')
                                    ->orWhere('bank_name', 'like', '%' . $searchValue . '%')
                                    ->orWhere('bene_name', 'like', '%' . $searchValue . '%')
                                    ->orWhere('account_number', 'like', '%' . $searchValue . '%')
                                    ->orWhere('ifsc', 'like', '%' . $searchValue . '%')
                                    ->orWhere('mode', 'like', '%' . $searchValue . '%')
                                    ->orWhereHas('merchant', function ($q) use ($searchValue) {
                                        $q->where('first_name', 'like', '%' . $searchValue . '%');
                                    });
                                })->count();



        $records = MerchantPayouts::orderBy('id', 'DESC')
                    ->where('merchant_id', $user_id)
                    ->where(function ($query) use ($searchValue) {
                        $query->where('transaction_id', 'like', '%' . $searchValue . '%')
                        ->orWhere('bank_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bene_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('account_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('ifsc', 'like', '%' . $searchValue . '%')
                        ->orWhere('mode', 'like', '%' . $searchValue . '%')
                        ->orWhereHas('merchant', function ($q) use ($searchValue) {
                            $q->where('first_name', 'like', '%' . $searchValue . '%');
                        });
                    })
                    ->skip($start)
                    ->take($rowperpage)
                    ->get();

                    $data_arr = array();
        foreach ($records as $value) {

            if ($value->status == 1) {
                $status = '<span class="badge badge-success">Success</span>';
            }elseif ($value->status == 2) {
                $status = '<span class="badge badge-danger">Failed</span>';
            }elseif ($value->status == 3) {
                $status = '<span class="badge badge-primary">Refunded</span>';
            } else {
                $status = '<span class="badge badge-warning">Pending</span>';
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user_name" => $value->merchant->first_name,
                "transaction_id" =>$value->transaction_id,
                "bank_name" => $value->bank_name,
                "bene_name" => $value->bene_name,
                "account_no" => $value->account_number,
                "ifsc" => $value->ifsc,
                "amount" => $value->amount,
                "status" => $status,
                "mode" => $value->mode,
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
