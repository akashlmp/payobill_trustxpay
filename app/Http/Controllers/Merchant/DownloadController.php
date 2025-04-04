<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;
use App\Models\User;
use App\Models\Report;
use App\Models\Provider;
use App\Library\MemberLibrary;
use App\Commissionreport;
use App\Models\Status;
use App\Models\Beneficiary;
use App\Models\MerchantTransactions;
use App\Models\MerchantUsers;
use App\Models\Role;
use App\Models\State;
use App\Models\Service;
use File;

class DownloadController extends Controller
{
    //

    function download_file(Request $request)
    {
        $rules = array(
            'menu_name' => 'required',
            'password' => 'required',
            'fromdate' => 'required',
            'todate' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $this->delete_all_file();
        $menu_name = $request->menu_name;
        $password = $request->password;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $optional1 = $request->optional1;
        $user_id = Auth::guard('merchant')->id();
        $userdetail = MerchantUsers::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            return Self::DownloadAllTransactionReport($fromdate, $todate, $optional1);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password does not match']);
        }
    }

    function DownloadAllTransactionReport($fromdate, $todate, $statusId)
    {
        $user_id = Auth::guard('merchant')->id();
        if ($statusId == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $statusId)->get(['id']);
        }
        $reports = MerchantTransactions::where('merchant_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $data = array(
                $value->created_at,
                $value->merchant->name . ' ' . $value->merchant->last_name,
                $value->provider->provider_name,
                $value->account_number,
                ($value->txnid ? $value->txnid : "N/A"),
                $value->opening_balance,
                $value->amount,
                ($value->profit ? $value->profit : 0),
                ($value->gst ? $value->gst : 0),
                $value->total_balance,
                ($value->ip_address ? $value->ip_address : "-"),
                $value->status->status,
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/transaction-report_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Date', 'User', 'Provider', 'Account Number', 'Txn Id', 'Opening Balance', 'Amount', 'Charge', 'Gst', 'Closing Balance', 'Ip Address', 'Status', 'Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }


    function delete_all_file()
    {
        $destinationPath = 'download';
        File::cleanDirectory($destinationPath);
    }
}
