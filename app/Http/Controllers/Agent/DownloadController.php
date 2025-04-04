<?php

namespace App\Http\Controllers\Agent;

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
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            $services = Service::where('report_slug', $menu_name)->first();
            if (!empty($services)) {
                if ($services->servicegroup_id == 4) {
                    return Self::DownloadBankingReport($fromdate, $todate, $optional1, $services);
                } elseif ($services->servicegroup_id == 5) {
                    return Self::DownloadAepsReport($fromdate, $todate, $optional1, $services);
                } else {
                    return Self::DownloadOtherReport($fromdate, $todate, $optional1, $services);
                }
            } elseif ($menu_name == 'All Transaction Report') {
                return Self::DownloadAllTransactionReport($fromdate, $todate, $optional1);
            } elseif ($menu_name == 'Pending Report') {
                return Self::DownloadPendingReport($fromdate, $todate);
            } elseif ($menu_name == 'Api Profit Loss Report') {
                return Self::downloadApiProfitLossReport($fromdate, $todate);
            } elseif ($menu_name == 'Debit Report') {
                return Self::downloadDebitReport($fromdate, $todate);
            }elseif ($menu_name == 'Credit Report'){
                return Self::downloadCreditReport($fromdate, $todate);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password does not match']);
        }
    }

    function DownloadBankingReport($fromdate, $todate, $status_id, $services)
    {
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            //->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';
            $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;

            $charges = empty($value->row_data) ? '' : (json_decode($value->row_data)->charges ?? '');
            $customer_charge = empty($value->row_data) ? '' : (json_decode($value->row_data)->customer_charge ?? '');
            $gst = empty($value->row_data) ? '' : number_format(json_decode($value->row_data)->gst ?? 0, 2);
            $tds = empty($value->row_data) ? '' : number_format(json_decode($value->row_data)->tds ?? 0, 2);
            $netCommission = empty($value->row_data) ? '' : number_format(json_decode($value->row_data)->netCommission ?? 0, 2);
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $remiter_number,
                $value->number,
                $bene_name,
                $bank_name,
                $ifsc,
                number_format($value->amount, 2),
                $charges,
                $value->txnid,
                $value->status->status,
                $payment_mode,
                $customer_charge,
                $gst,
                $tds,
                $netCommission,
                number_format($value->total_balance, 2),
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['ID', 'Date', 'User', 'Provider', 'Mobile', 'Account Number', 'Beneficiary Name', 'Bank Name', 'IFSC', 'Amount', 'Charges', 'UTR', 'Status', 'Payment Type', 'Customer Charge', 'Gst', 'TDS', 'Net Commission', 'Balance','Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }


    function DownloadAepsReport($fromdate, $todate, $optional1, $services)
    {
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $aepsreports = Aepsreport::where('report_id', $value->id)->first();
            $aadhar_number = (empty($aepsreports)) ? '' : $aepsreports->aadhar_number;
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $aadhar_number,
                $value->status->status,
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Aadhar Number', 'Status','Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }


    function DownloadOtherReport($fromdate, $todate, $status_id, $services)
    {
        $user_id = Auth::id();
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->tds,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $value->status->status,
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'TDS', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status','Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadAllTransactionReport($fromdate, $todate, $statusId)
    {
        $user_id = Auth::id();
        if ($statusId == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $statusId)->get(['id']);
        }
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->tds,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $value->status->status,
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'TDS', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason'];
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
