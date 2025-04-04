<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserStaticQrAccount;
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
use App\Easebuzz\StaticQr;

class VirtualAccountStaticQRController extends Controller
{
    function index(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('agent/virtual-account-static-qr/all-data-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', strtotime('-7 days'));
            $todate = date('Y-m-d', time());
            $urls = url('agent/virtual-account-static-qr/all-data-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Virtual Account (Static QR)',
            'report_slug' => 'Virtual Account (Static QR)',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        return view('agent.all_virtual_account_static_qr')->with($data);
    }

    function all_data_api(Request $request)
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


        $totalRecords = UserStaticQrAccount::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->count();

        $totalRecordswithFilter = UserStaticQrAccount::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where(function ($query) use ($searchValue) {
                $query->where('merchant_reference_id', 'like', '%' . $searchValue . '%')
                    ->orWhere('unique_request_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('virtual_account_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('virtual_account_id', 'like', '%' . $searchValue . '%');
            })
            ->count();

        // Fetch records

        $records = UserStaticQrAccount::query();
        $records->where(function ($query) use ($searchValue) {
            $query->where('merchant_reference_id', 'like', '%' . $searchValue . '%')
                ->orWhere('unique_request_number', 'like', '%' . $searchValue . '%')
                ->orWhere('virtual_account_number', 'like', '%' . $searchValue . '%')
                ->orWhere('virtual_account_id', 'like', '%' . $searchValue . '%');
        });
        $records =  $records->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $statusButton  = "<a href='javascript:;' class='changeStatus btn btn-success btn-sm' data-id=" . $value->id . " data-status='1'>Activate</a>";
            if ($value->is_active == 1) {
                $statusButton  = "<a href='javascript:;' class='changeStatus btn btn-danger btn-sm' data-id=" . $value->id . " data-status='0'>Deactivate</a>";
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => date("d-m-Y h:i A", strtotime($value->created_at)),
                "merchant_reference_id" => $value->merchant_reference_id,
                "upi_qrcode_remote_file_location" => "<a href='" . $value->upi_qrcode_remote_file_location . "' target='_blank'><img width=50 src='" . $value->upi_qrcode_remote_file_location . "'/></a>",
                "upi_qrcode_scanner_remote_file_location" => "<a href='" . $value->upi_qrcode_scanner_remote_file_location . "' target='_blank'>View Pdf<a>",
                "unique_request_number" => $value->unique_request_number,
                "virtual_account_id" => $value->virtual_account_id,
                // "account_number" => $value->account_number,
                "virtual_account_number" => $value->virtual_account_number,
                "is_active" => ($value->is_active == 1) ? "Active" : "Deactivate",
                "auto_deactivate_at" => $value->auto_deactivate_at,
                "action" => $statusButton
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

    public function statusChange(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $result = UserStaticQrAccount::where('id', $id)->first();
        $credentials_id = Auth::user()->credentials_id;
        if ($result) {
            $easebuzz_static_qr = new StaticQr;
            $response = $easebuzz_static_qr->changeVirtualAccountStatus($result->virtual_account_id, $status, $credentials_id);
            if ($response['status'] == "success") {
                if ($status == 1) {
                    UserStaticQrAccount::where('user_id', $result->user_id)->update(['is_active' => 0]);
                }
                $result->is_active = $status;
                $result->save();
                return response()->json([
                    'status' => 'success',
                    'message' => "Status changed successfully"
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => $response['message']
                ]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'record not found']);
        }
    }
}
