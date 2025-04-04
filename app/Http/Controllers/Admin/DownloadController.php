<?php

namespace App\Http\Controllers\Admin;

use File;
use Hash;
use \Crypt;
use Helpers;
use Validator;
use ZipArchive;
use App\Models\Api;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\Member;
use App\Models\Report;
use App\Models\Status;
use http\Env\Response;
use App\Models\Service;
use App\Models\Loadcash;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\Aepsreport;
use App\Models\Beneficiary;
use App\Models\Sitesetting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Library\BasicLibrary;
use App\Models\Apicommreport;
use App\Models\MerchantUsers;
use App\Library\MemberLibrary;
use App\Models\Agentonboarding;
use App\Models\Commissionreport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\MerchantTransactions;
use Illuminate\Support\Facades\Auth;
use App\Models\MerchantApicommreport;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{


    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $this->cdn_link = $companies->cdn_link;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }


    function download_file(Request $request)
    {
        /*  $currentTime = date('H', time());
          if ($currentTime > 17 && $currentTime < 22) {
              return Response()->json(['status' => 'failure', 'message' => 'From 6PM to 10PM, you cannot download any data.']);
          }*/
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
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $password = $request->password;
        $optional1 = $request->download_optional1;
        $optional2 = $request->download_optional2;
        $optional3 = $request->download_optional3;
        $optional4 = $request->download_optional4;
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
                return Self::DownloadAllTransactionReport($fromdate, $todate, $optional1, $optional2, $optional3, $optional4);
            } elseif ($menu_name == 'Pending Report') {
                return Self::DownloadPendingReport($fromdate, $todate);
            } elseif ($menu_name == 'Api Profit Loss Report') {
                return Self::downloadApiProfitLossReport($fromdate, $todate);
            } elseif ($menu_name == 'Admin Profit Report') {
                return Self::downloadMerchantCommisionReport($fromdate, $todate);
            } elseif ($menu_name == 'Debit Report') {
                return Self::downloadDebitReport($fromdate, $todate);
            } elseif ($menu_name == 'Credit Report') {
                return Self::downloadCreditReport($fromdate, $todate);
            } elseif ($menu_name == 'Download User Ledger Report') {
                $child_id = Crypt::decrypt($optional2);
                return Self::DownloadUserLedgerReport($fromdate, $todate, $optional1, $child_id);
            } elseif ($menu_name == 'Purchase Balance') {
                return Self::downloadPurchaseBalance($fromdate, $todate);
            } elseif ($menu_name == 'Move To Bank History') {
                return Self::DownloadBankHistoryReport($fromdate, $todate, $optional1);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password does not match']);
        }

    }


    function DownloadBankingReport($fromdate, $todate, $status_id, $services)
    {
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
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

            /*$payFrom ='N/A';
            if($value->provider_api_from == 1){
                $payFrom ='Paysprint';
            }else if($value->provider_api_from == 2){
                $payFrom ='BankIt';
            }*/
            $payFrom = providerType($value->provider_api_from);

            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $payFrom,
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
        $col = ['ID', 'Date', 'User', 'Provider', 'Payment Platform', 'Mobile', 'Account Number', 'Beneficiary Name', 'Bank Name', 'IFSC', 'Amount', 'Charges', 'UTR', 'Status', 'Payment Type', 'Customer Charge', 'Gst', 'TDS', 'Net Commission', 'Balance', 'Failure Reason'];
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $aepsreports = Aepsreport::where('report_id', $value->id)->first();
            $aadhar_number = (empty($aepsreports)) ? '' : $aepsreports->aadhar_number;
            /*$payFrom ='N/A';
            if($value->provider_api_from == 1){
                $payFrom ='Paysprint';
            }else if($value->provider_api_from == 2){
                $payFrom ='BankIt';
            }*/
            $payFrom = providerType($value->provider_api_from);
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $payFrom,
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
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Payment Platform', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Aadhar Number', 'Status', 'Failure Reason'];
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
            /*$payFrom ='N/A';
            if($value->provider_api_from == 1){
                $payFrom ='Paysprint';
            }else if($value->provider_api_from == 2){
                $payFrom ='BankIt';
            }*/
            $payFrom = providerType($value->provider_api_from);
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $payFrom,
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
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Payment Platform', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'TDS', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadAllTransactionReport($fromdate, $todate, $statusId, $childId, $providerId, $apiId)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        if ($statusId == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $statusId)->get(['id']);
        }
        if ($childId == 0) {
            $child_id = User::whereIn('id', $my_down_member)->get(['id']);
        } else {
            $child_id = User::whereIn('id', $my_down_member)->where('id', $childId)->get(['id']);
        }
        if ($providerId == 0) {
            $provider_id = Provider::get(['id']);
        } else {
            $provider_id = Provider::where('id', $providerId)->get(['id']);
        }
        if ($apiId == 0) {
            $api_id = Api::get(['id']);
        } else {
            $api_id = Api::where('id', $apiId)->get(['id']);
        }
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('user_id', $child_id)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('api_id', $api_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
            /* $payFrom ='N/A';
             if($value->provider_api_from == 1){
                 $payFrom ='Paysprint';
             }else if($value->provider_api_from == 2){
                 $payFrom ='BankIt';
             }*/
            $payFrom = providerType($value->provider_api_from);
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $payFrom,
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
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Payment Platform', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'TDS', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadPendingReport($fromdate, $todate)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', [3])
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
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
                $value->status->status,
                $value->failure_reason,
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/pending-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function downloadApiProfitLossReport($fromdate, $todate)
    {
        if (Auth::User()->role_id == 1) {
            $user_id = Auth::id();
            $reports = Apicommreport::where('status_id', 1)
                ->groupBy('api_id')
                ->selectRaw('*, sum(amount) as amount, sum(apiCharge) as apiCharge, sum(apiCommission) as apiCommission, sum(retailerCharge) as retailerCharge, sum(retailerComm) as retailerComm, sum(totalProfit) as totalProfit')
                ->orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereNotIn('api_id', [0])
                ->get();
            $arr = array();
            foreach ($reports as $value) {
                $data = array(
                    $value->api->api_name,
                    $value->amount,
                    $value->apiCharge,
                    $value->apiCommission,
                    $value->retailerCharge,
                    $value->retailerComm,
                    $value->totalProfit,
                    $value->failure_reason,
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/api-profit-loss-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['Api Name', 'Amount', 'Api Charges', 'Api Commission', 'Our Charges', 'Our Commission', 'Net Profit', 'Failure Reason'];
            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function downloadMerchantCommisionReport($fromdate, $todate)
    {
        if (Auth::User()->role_id == 1) {
            $user_id = Auth::id();
            $reports = MerchantApicommreport::where('status_id', 1)
                ->groupBy('api_id')
                ->selectRaw('*, sum(amount) as amount, sum(apiCharge) as apiCharge, sum(apiCommission) as apiCommission, sum(retailerCharge) as retailerCharge, sum(retailerComm) as retailerComm, sum(totalProfit) as totalProfit')
                ->orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereNotIn('api_id', [0])
                ->get();
            $arr = array();
            foreach ($reports as $value) {
                $data = array(
                    $value->api->api_name,
                    $value->amount,
                    $value->apiCharge,
                    $value->apiCommission,
                    $value->retailerCharge,
                    $value->retailerComm,
                    $value->totalProfit,
                    $value->failure_reason,
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/merchant-commision-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['Api Name', 'Amount', 'Api Charges', 'Api Commission', 'Our Charges', 'Our Commission', 'Net Profit', 'Failure Reason'];
            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function downloadDebitReport($fromdate, $todate)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', [Auth::id()])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 7)
            ->whereNotIn('remark', ['profit'])
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $users = User::find($value->credit_by);
            $transfer_to = ($users) ? $users->name . ' ' . $users->last_name : '';
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $transfer_to,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->amount,
                $value->total_balance,
                $value->status->status,
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/debit_report_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Transfer To', 'Provider', 'Number', 'Txn Id', 'Amount', 'Balance', 'Status', 'Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function downloadCreditReport($fromdate, $todate)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', [Auth::id()])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 6)
            ->whereNotIn('remark', ['profit'])
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $users = User::find($value->credit_by);
            $transfer_to = ($users) ? $users->name . ' ' . $users->last_name : '';
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $transfer_to,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->amount,
                $value->total_balance,
                $value->status->status,
                $value->failure_reason,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/credit_report_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Transfer By', 'Provider', 'Number', 'Txn Id', 'Amount', 'Balance', 'Status', 'Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function member_download(Request $request)
    {
        $rules = array(
            'menu_name' => 'required',
            'password' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $this->delete_all_file();
        $menu_name = $request->menu_name;
        $password = $request->password;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            $roles = Role::where('role_title', $menu_name)->first();
            if ($roles) {
                $role_id = Auth::User()->role_id;
                $company_id = Auth::User()->company_id;
                $user_id = Auth::id();
                $library = new MemberLibrary();
                $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
                $users = User::where('role_id', $roles->id)->whereIn('id', $my_down_member)->get();
                $arr = array();
                foreach ($users as $value) {
                    $parent_details = User::where('id', $value->parent_id)->first();
                    $parent_name = ($parent_details) ? $parent_details->name . ' ' . $parent_details->last_name : '';

                    $permanents = State::find($value->member->state_id);
                    $state_name = ($permanents) ? $permanents->name : '';
                    $data = array(
                        $value->id,
                        $value->created_at,
                        $value->name,
                        $value->last_name,
                        $value->fullname ? $value->fullname : "-",
                        $value->mobile,
                        $value->email,
                        $value->member->shop_name,
                        $value->role->role_title,
                        $value->cms_agent_id ? $value->cms_agent_id : "-",
                        number_format($value->balance->user_balance, 2),
                        number_format($value->balance->aeps_balance, 2),
                        $parent_name,
                        $value->member->address,
                        $value->member->city,
                        $state_name,
                        $value->member->pin_code,
                        $value->member->office_address,
                        ($value->profile->recharge == 1) ? 'Active' : 'De Active',
                        ($value->profile->money == 1) ? 'Active' : 'De Active',
                        ($value->profile->aeps == 1) ? 'Active' : 'De Active',
                        ($value->profile->payout == 1) ? 'Active' : 'De Active',
                        ($value->profile->pancard == 1) ? 'Active' : 'De Active',
                        ($value->profile->giftcard == 1) ? 'Active' : 'De Active'
                    );
                    array_push($arr, $data);
                }
                $delimiter = ",";
                $filename = 'download/' . $menu_name . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
                $fp = fopen($filename, 'w+');
                $col = ['User Id', 'Joining Date', 'First Name', 'Last Name', 'Full Name', 'Mobile', 'Email Id', 'Shop Name', 'Member Type', 'Retailer ID / Distributor ID', 'Normal Balance', 'Aeps Balance', 'Parent Name', 'Address', 'City', 'State', 'Pincode', 'Office Address', 'Recharge', 'Money', 'Aeps', 'Payout', 'Pancard', 'Giftcard'];

                fputcsv($fp, $col, $delimiter);
                foreach ($arr as $line) {
                    fputcsv($fp, $line, $delimiter);
                }
                fclose($fp);
                $path = url('') . '/' . $filename;
                return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);

            } else {
                return Response()->json(['status' => 'failure', 'message' => "sorry you can't download this file at this time"]);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'password does not match']);
        }
    }

    function payment_request_view(Request $request)
    {
        $rules = array(
            'menu_name' => 'required',
            'password' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $user_id = Auth::id();
        $menu_name = $request->menu_name;
        $password = $request->password;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $status_id = $request->status_id;
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            $loadcash = Loadcash::where('parent_id', Auth::id())->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)->where('status_id', $status_id)->orderBy('id', 'DESC')->get();
            $arr = array();
            foreach ($loadcash as $value) {

                $data = array(
                    $value->id,
                    $value->user->name . ' ' . $value->user->last_name,
                    $value->created_at,
                    $value->payment_date,
                    $value->bankdetail->bank_name,
                    $value->paymentmethod->payment_type,
                    number_format($value->amount, 2),
                    $value->bankref,
                    ($value->payment_type == 1) ? 'Auto' : 'Manaul',
                    $value->status->status,
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/' . $menu_name . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['Id', 'User', 'Request Date', 'Payment Date', 'Bank', 'Method', 'Amount', 'UTR', 'Payment Type', 'Status'];

            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'password does not match']);
        }
    }

    function agent_onboarding_download(Request $request)
    {
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'password' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = Auth::id();
            $menu_name = $request->menu_name;
            $password = $request->password;
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (Hash::check($password, $current_password)) {
                $loadcash = Agentonboarding::orderBy('id', 'DESC')->get();
                $arr = array();
                foreach ($loadcash as $value) {

                    $data = array(
                        $value->id,
                        $value->created_at,
                        $value->user->name . ' ' . $value->user->last_name,
                        $value->first_name,
                        $value->last_name,
                        $value->mobile_number,
                        $value->email,
                        $value->aadhar_number,
                        $value->pan_number,
                        $value->company,
                        $value->pin_code,
                        $value->address,
                        $value->bank_account_number,
                        $value->ifsc,
                        $value->state->name,
                        $value->district->district_name,
                        $value->city,
                        $value->status->status,
                    );
                    array_push($arr, $data);
                }
                $delimiter = ",";
                $filename = 'download/outlet_list_' . mt_rand(10, 99) . '.csv';
                $fp = fopen($filename, 'w+');
                $col = ['ID', 'Date Time', 'User Name', 'First Name', 'Last Name', 'Mobile Number', 'Email', 'Aadhar Number', 'Pan Number', 'Shop Name', 'Pin Code', 'Address', 'Account Number', 'IFSC Code', 'State Name', 'District Name', 'City', 'Status'];

                fputcsv($fp, $col, $delimiter);
                foreach ($arr as $line) {
                    fputcsv($fp, $line, $delimiter);
                }
                fclose($fp);
                $path = url('') . '/' . $filename;
                return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'password does not match']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function DownloadUserLedgerReport($fromdate, $todate, $wallet_type, $child_id)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('user_id', $child_id)
            ->where('wallet_type', $wallet_type)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                ($value->txnid!="") ? $value->txnid : "N/A",
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $value->status->status,
                $value->failure_reason,
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function downloadPurchaseBalance($fromdate, $todate)
    {
        if (Auth::User()->role_id == 1) {
            $reports = Purchase::orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->get();
            $arr = array();
            foreach ($reports as $value) {
                $data = array(
                    $value->id,
                    $value->created_at,
                    $value->user->name,
                    $value->api->api_name,
                    $value->masterbank->bank_name,
                    number_format($value->amount, 2),
                    $value->utr,
                    $value->purchase_type,
                    $value->status->status
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/purchase_balance_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['ID', 'Date', 'User Name', 'Api Name', 'Bank Name', 'Amount', 'UTR', 'Purchase Type', 'Status'];
            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function delete_all_file()
    {
        $destinationPath = 'download';
        File::cleanDirectory($destinationPath);
    }

    public function bankitUserExport(Request $request)
    {
        try {
            $type = $request->type ? $request->type : 'aeps';
            $document = [];
            $users = DB::table('users as u')
                ->join('members as m', 'u.id', '=', 'm.user_id')
                ->leftJoin('states as s', 'm.state_id', '=', 's.id')
                ->leftJoin('districts as d', 'm.district_id', '=', 'd.id')
                ->where('u.role_id', '=', 8)
                ->where('u.status_id', '=', 1)
                ->where('m.kyc_status', '=', 1);
            if ($type == 'aeps') {

            } else {

            }
            $users = $users->select('u.id', 'u.cms_agent_id', 'u.name', 'u.last_name', 'u.fullname', 'u.gender',
                'u.mobile', 'u.email', 'u.dob', 'm.pan_number', 'm.pin_code', 'd.district_name', 's.name as state',
                'm.city', 'm.address', 'm.pancard_photo', 'm.aadhar_front', 'm.aadhar_back')
                ->get()->toArray();

            $header = ['AGENT ID', 'FIRSTNAME', 'LASTNAME', 'FULL NAME', 'GENDER', 'COMPANYNAME', 'MOBILENO',
                'TELEPHONENO', 'ALTERNATEMOBILE', 'EMAILID', 'DOB(mm/dd/yyyy)', 'IDCARDNUMBER', 'PINCODE', 'DISTRICT', 'STATE',
                'CITY', 'ADDRESS', 'AREA', 'PINCODE', 'DISTRICT', 'STATE', 'CITY', 'ADDRESS', 'AREA', 'PINCODE', 'DISTRICT', 'STATE',
                'CITY', 'ADDRESS', 'AREA', 'API ID'];

            $csv_file_name = "Bankit_user_onboarding_" . time() . ".csv";
            $csv_file_path = public_path('/download/' . $csv_file_name);
            $file = fopen($csv_file_path, 'w');
            ob_start();
            fputcsv($file, $header);
            foreach ($users as $user) {
                $mobile_no = $user->mobile;
                $data = [];
                $data[] = $user->cms_agent_id ?? ' ';
                $data[] = $user->name ?? ' ';
                // $data[] = $user->middle_name ?? ' ';
                $data[] = $user->last_name ?? ' ';
                $data[] = $user->fullname ?? ' ';
                $data[] = $user->gender ?? ' ';
                $data[] = 'Trustxpay';
                $data[] = $user->mobile ?? ' ';
                $data[] = ' ';
                $data[] = $user->mobile ?? ' ';
                $data[] = $user->email ?? ' ';
                $data[] = $user->dob ? date('m/d/Y', strtotime($user->dob)) : ' ';
                $data[] = $user->pan_number ?? ' ';
                $data[] = $user->pin_code ?? ' ';
                $data[] = $user->district_name ?? ' ';
                $data[] = $user->state ?? ' ';
                $data[] = $user->city ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->pin_code ?? '';
                $data[] = $user->district_name ?? ' ';
                $data[] = $user->state ?? ' ';
                $data[] = $user->city ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->pin_code ?? ' ';
                $data[] = $user->district_name ?? ' ';
                $data[] = $user->state ?? ' ';
                $data[] = $user->city ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = 1;
                fputcsv($file, $data);

                $rand = Str::random(8);
                if ($user->pancard_photo && Storage::disk('s3')->exists($user->pancard_photo)) {
                    $pan_url = $this->cdn_link . $user->pancard_photo;
                    $rename_pan = $mobile_no . "_POI_$rand.jpg";
                    $pan_path = public_path('/download/' . $rename_pan);
                    $pan_content = file_get_contents($pan_url);
                    file_put_contents($pan_path, $pan_content);
                    $document[] = ['name' => $rename_pan, 'path' => $pan_path];
                }
                if ($user->aadhar_front && Storage::disk('s3')->exists($user->aadhar_front)) {
                    $aadhar_front_url = $this->cdn_link . $user->aadhar_front;
                    $rename_aadhar_front = $mobile_no . "_POA_Front_$rand.jpg";
                    $aadhar_front_path = public_path('/download/' . $rename_aadhar_front);
                    $aadhar_front_content = file_get_contents($aadhar_front_url);
                    file_put_contents($aadhar_front_path, $aadhar_front_content);
                    $document[] = ['name' => $rename_aadhar_front, 'path' => $aadhar_front_path];
                }
                if ($user->aadhar_back && Storage::disk('s3')->exists($user->aadhar_back)) {
                    $aadhar_back_url = $this->cdn_link . $user->aadhar_back;
                    $rename_aadhar_back = $mobile_no . "_POA_Back_$rand.jpg";
                    $aadhar_back_path = public_path('/download/' . $rename_aadhar_back);
                    $aadhar_back_content = file_get_contents($aadhar_back_url);
                    file_put_contents($aadhar_back_path, $aadhar_back_content);
                    $document[] = ['name' => $rename_aadhar_back, 'path' => $aadhar_back_path];
                }
            }
            fclose($file);
            $zip = new  \ZipArchive();
            $zip_file_name = "Bankit_user_" . time() . ".zip";
            $zip_file_path = public_path('/download/' . $zip_file_name);
            if ($zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($csv_file_path, $csv_file_name);
                foreach ($document as $data) {
                    $zip->addFile($data['path'], $data['name']);
                }
                $zip->close();
                @unlink($csv_file_path);
                foreach ($document as $data) {
                    @unlink($data['path']);
                }
            } else {
                return redirect()->back()->with('error', 'Failed to create zip file');
            }

            return response()->download($zip_file_path)->deleteFileAfterSend(true);
            exit();
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', $e->getMessage());
        }

    }


    function DownloadBankHistoryReport($fromdate, $todate, $status_id)
    {

        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $provider_id = 584;
        $provider_id = Provider::where('id', $provider_id)->where('service_id', 19)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
            /*$payFrom ='N/A';
            if($value->provider_api_from == 1){
                $payFrom ='Paysprint';
            }else if($value->provider_api_from == 2){
                $payFrom ='BankIt';
            }*/
            $payFrom = providerType($value->provider_api_from);
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $payFrom,
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
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/move_to_bank_history' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Payment Platform', 'Number', 'Txn Id', 'Opening Balance', 'Amount', 'Profit', 'TDS', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    public function bankitUserExportNew(Request $request)
    {
        try {
            $type = $request->type ? $request->type : 'aeps';
            $document = [];
            $users = DB::table('users as u')
                ->join('members as m', 'u.id', '=', 'm.user_id')
                ->leftJoin('states as s', 'm.state_id', '=', 's.id')
                ->leftJoin('districts as d', 'm.district_id', '=', 'd.id')
                ->where('u.role_id', '=', 8)
                ->where('u.status_id', '=', 1)
                ->where('m.kyc_status', '=', 1);
            $users = $users->select('u.id', 'u.cms_agent_id', 'u.name', 'u.last_name', 'u.fullname', 'u.gender',
                'u.mobile', 'u.email', 'u.dob', 'm.pan_number', 'm.pin_code', 'd.district_name', 's.name as state',
                'm.city', 'm.address', 'm.pancard_photo', 'm.aadhar_front', 'm.aadhar_back')
                ->get()->toArray();

            $header = ['AGENT ID', 'FIRSTNAME', 'LASTNAME', 'FULL NAME', 'GENDER', 'COMPANYNAME', 'MOBILENO',
                'TELEPHONENO', 'ALTERNATEMOBILE', 'EMAILID', 'DOB(mm/dd/yyyy)', 'IDCARDNUMBER', 'PINCODE', 'DISTRICT', 'STATE',
                'CITY', 'ADDRESS', 'AREA', 'PINCODE', 'DISTRICT', 'STATE', 'CITY', 'ADDRESS', 'AREA', 'PINCODE', 'DISTRICT', 'STATE',
                'CITY', 'ADDRESS', 'AREA', 'API ID'];

            $csv_file_name = "Bankit_user_onboarding_" . time() . ".csv";
            $csv_file_path = public_path('/download/' . $csv_file_name);
            $file = fopen($csv_file_path, 'w');
            ob_start();
            fputcsv($file, $header);
            foreach ($users as $user) {
                $mobile_no = $user->mobile;
                $data = [];
                $data[] = $user->cms_agent_id ?? ' ';
                $data[] = $user->name ?? ' ';
                // $data[] = $user->middle_name ?? ' ';
                $data[] = $user->last_name ?? ' ';
                $data[] = $user->fullname ?? ' ';
                $data[] = $user->gender ?? ' ';
                $data[] = 'Trustxpay';
                $data[] = $user->mobile ?? ' ';
                $data[] = ' ';
                $data[] = $user->mobile ?? ' ';
                $data[] = $user->email ?? ' ';
                $data[] = $user->dob ? date('m/d/Y', strtotime($user->dob)) : ' ';
                $data[] = $user->pan_number ?? ' ';
                $data[] = $user->pin_code ?? ' ';
                $data[] = $user->district_name ?? ' ';
                $data[] = $user->state ?? ' ';
                $data[] = $user->city ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->pin_code ?? '';
                $data[] = $user->district_name ?? ' ';
                $data[] = $user->state ?? ' ';
                $data[] = $user->city ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->pin_code ?? ' ';
                $data[] = $user->district_name ?? ' ';
                $data[] = $user->state ?? ' ';
                $data[] = $user->city ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = $user->address ?? ' ';
                $data[] = 1;
                fputcsv($file, $data);

                $rand = Str::random(8);
                if ($user->pancard_photo && Storage::disk('s3')->exists($user->pancard_photo)) {
                    $pan_url = $this->cdn_link . $user->pancard_photo;
                    $rename_pan = $mobile_no . "_POI_$rand.jpg";
                    $pan_path = public_path('/download/' . $rename_pan);
                    $pan_content = file_get_contents($pan_url);
                    file_put_contents($pan_path, $pan_content);
                    $document[] = ['name' => $rename_pan, 'path' => $pan_path];
                }
                if ($user->aadhar_front && Storage::disk('s3')->exists($user->aadhar_front)) {
                    $aadhar_front_url = $this->cdn_link . $user->aadhar_front;
                    $rename_aadhar_front = $mobile_no . "_POA_Front_$rand.jpg";
                    $aadhar_front_path = public_path('/download/' . $rename_aadhar_front);
                    $aadhar_front_content = file_get_contents($aadhar_front_url);
                    file_put_contents($aadhar_front_path, $aadhar_front_content);
                    $document[] = ['name' => $rename_aadhar_front, 'path' => $aadhar_front_path];
                }
                if ($user->aadhar_back && Storage::disk('s3')->exists($user->aadhar_back)) {
                    $aadhar_back_url = $this->cdn_link . $user->aadhar_back;
                    $rename_aadhar_back = $mobile_no . "_POA_Back_$rand.jpg";
                    $aadhar_back_path = public_path('/download/' . $rename_aadhar_back);
                    $aadhar_back_content = file_get_contents($aadhar_back_url);
                    file_put_contents($aadhar_back_path, $aadhar_back_content);
                    $document[] = ['name' => $rename_aadhar_back, 'path' => $aadhar_back_path];
                }
            }
            fclose($file);
            $zip = new  \ZipArchive();
            $zip_file_name = "Bankit_user_" . time() . ".zip";
            $zip_file_path = public_path('/download/' . $zip_file_name);
            if ($zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($csv_file_path, $csv_file_name);
                foreach ($document as $data) {
                    $zip->addFile($data['path'], $data['name']);
                }
                $zip->close();
                @unlink($csv_file_path);
                foreach ($document as $data) {
                    @unlink($data['path']);
                }
            } else {
                return redirect()->back()->with('error', 'Failed to create zip file');
            }
            return ['status' => 'success', 'data' => ['file_name' => $zip_file_name, 'file_path' => asset("download/$zip_file_name")]];
        } catch (\Exception $e) {
            Log::error($e);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

    }

    public function downloadBankitZipFile(Request $request)
    {
        $file_name = $request->file_name ?? null;
        if ($file_name) {
            $zip_file_path = public_path('/download/' . $file_name);
            return response()->download($zip_file_path)->deleteFileAfterSend(true);
        }
        return redirect()->back()->with('error', 'File not found.');

    }


    public function iServeUUserExport(Request $request)
    {
        try {
            $users = DB::table('users as u')
                ->join('members as m', 'u.id', '=', 'm.user_id')
                ->leftJoin('states as s', 'm.state_id', '=', 's.id')
                ->leftJoin('districts as d', 'm.district_id', '=', 'd.id')
                ->where('u.role_id', '=', 8)
                ->where('u.status_id', '=', 1)
                ->where('u.iserveu_onboard_status',0)
                ->where('m.kyc_status', '=', 1);
            $users = $users->select('u.id', 'u.cms_agent_id', 'u.name', 'u.last_name', 'u.fullname', 'u.gender',
                'u.mobile', 'u.email', 'u.dob', 'm.pan_number', 'm.pin_code', 'd.district_name', 's.name as state',
                'm.city', 'm.address', 'm.office_address', 'm.pancard_photo', 'm.aadhar_front', 'm.aadhar_back', 'm.shop_name')
                ->get()->toArray();

            $fileName = "iserveu_user_onboarding_" . time() . ".csv";

            $header = ['BCagentid', 'BC agent Name', 'Last Name', 'Company Name', 'Address', 'Area', 'District', 'City',
                'State', 'Pincode', 'Mobile No', 'Shope name', 'Shope Address', 'Shope State', 'Shope City', 'Shope District',
                'Shope Area', 'Pin Code', 'Pan Card', 'Email', 'API Username'];

            $response = new StreamedResponse(function () use ($header, $users) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $header);
                foreach ($users as $user) {
                    $data = [];
                    $data[] = $user->cms_agent_id ?? ' ';
                    $data[] = $user->name ?? ' ';
                    $data[] = $user->last_name ?? ' ';
                    $data[] = 'Trustxpay';
                    $data[] = $user->address ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->state ?? ' ';
                    $data[] = $user->pin_code ?? '';
                    $data[] = $user->mobile ?? ' ';
                    $data[] = $user->shop_name ?? ' ';
                    $data[] = $user->office_address ?? ' ';
                    $data[] = $user->state ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->pin_code ?? '';
                    $data[] = $user->pan_number ?? '';
                    $data[] = $user->email ?? ' ';
                    $data[] = 'p';
                    fputcsv($file, $data);
                }
                fclose($file);
            });
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            return $response;
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', $e->getMessage());
        }

    }

    public function MerchantUserExport(Request $request)
    {
        try {

            $users = DB::table('merchant as u')->where('status', 1)->select('*')->get()->toArray();

            $fileName = "merchant_user_" . time() . ".csv";

            $header = ['Name', 'Email', 'Mobile', 'ADDRESS', 'STATE', 'CITY', 'PINCODE'];

            $response = new StreamedResponse(function () use ($header, $users) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $header);
                foreach ($users as $user) {
                    $data = [];
                    $data[] = $user->first_name.' '.$user->last_name;
                    $data[] = $user->email ?? ' ';
                    $data[] = $user->mobile_number ?? ' ';
                    $data[] = $user->address ?? ' ';
                    $data[] = $user->state ?? ' ';
                    $data[] = $user->city ?? ' ';
                    $data[] = $user->pincode ?? '';
                    fputcsv($file, $data);
                }
                fclose($file);
            });
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            return $response;
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', $e->getMessage());
        }

    }
    function merchant_download_file(Request $request)
    {
        // dd($request->all());
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
        $optional1 = $request->download_optional1;
        $optional2 = $request->download_optional2;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            return Self::DownloadMerchantTransactions($fromdate, $todate, $optional1,$optional2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password does not match']);
        }
    }
    function DownloadMerchantTransactions($fromdate, $todate, $statusId, $childId)
    {
       // $user_id = Auth::id();
        if ($statusId == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $statusId)->get(['id']);
        }
        if ($childId == 0) {
            $childId = MerchantUsers::get(['id']);
        } else {
            $childId = MerchantUsers::where('id', $childId)->get(['id']);
        }
        $reports = MerchantTransactions::whereIn('merchant_id', $childId)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $data = array(
                $value->created_at,
                $value->merchant->first_name . ' ' . $value->merchant->last_name,
                $value->provider->provider_name,
                $value->account_number,
                ($value->transaction_id ? $value->transaction_id : "N/A"),
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
        $filename = 'download/merchant_transaction-report_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Date', 'Merchant', 'Provider', 'Account Number', 'Txn Id', 'Opening Balance', 'Amount', 'Charge', 'Gst', 'Closing Balance', 'Ip Address', 'Status', 'Failure Reason'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }
}
