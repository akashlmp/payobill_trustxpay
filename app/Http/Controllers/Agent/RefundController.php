<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Apiresponse;
use App\Library\RefundLibrary;
use App\Models\Callbackurl;
use App\Models\Report;
use App\Models\Company;
use App\Models\Sitesetting;
use App\Library\BasicLibrary;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\SmsLibrary;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\User;
use App\Models\Commissionreport;
use App\Models\Api;
use Helpers;

class RefundController extends Controller {

    public function __construct()   {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings){
            $this->brand_name = $sitesettings->brand_name;
        }else{
            $this->brand_name = "";
        }
    }


    function dynamic_response (Request $request, $api_id){
        $callbackurls = Callbackurl::where('api_id', $api_id)->first();
        if ($callbackurls){
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Apiresponse::insertGetId(['message' => $request, 'api_type' => $api_id, 'response_type' => 'call_back', 'ip_address' => request()->ip(), 'created_at' => $ctime]);
            $status_parameter = $callbackurls->status_parameter;
            $success_value = $callbackurls->success_value;
            $failure_value = $callbackurls->failure_value;
            $failure_value_two = $callbackurls->failure_value_two;
            $failure_value_three = $callbackurls->failure_value_three;
            $uniq_id = $callbackurls->uniq_id;
            $operator_ref = $callbackurls->operator_ref;
            $ip_address = $callbackurls->ip_address;

            $id = $request["$uniq_id"];
            $status = $request["$status_parameter"];
            $request_ip =  request()->ip();

            if ($ip_address){
                if ($ip_address == $request_ip){

                }else{
                    return Response()->json(['status' => 'failure', 'message' => 'Invalid ip address']);
                }
            }

            if ($status == $success_value) {
                $status = 1;
                $txnid = $request["$operator_ref"];
            }elseif ($status == $failure_value_two){
                $status = 2;
                $txnid = '';
            }elseif ($status == $failure_value_three){
                $status = 2;
                $txnid = '';
            }elseif ($status == $failure_value){
                $status = 2;
                $txnid = '';
            }else{
                $status = 3;
                $txnid = '';
            }
            if ($id != '' && $status != '') {
                $mode = "Call-back";
                $reports = Report::find($id);

                 if ($reports->status_id == 1){
                        return Response()->json(['status' => 'failure', 'message' => 'Transaction is success!']);
                }

                if ($reports->wallet_type == 1){
                    $library = new RefundLibrary();
                    return   $library->update_transaction($status, $txnid, $id, $mode);
                }elseif ($reports->wallet_type == 2){
                    $library = new RefundLibrary();
                    $library->update_transaction_aeps($status, $txnid, $id, $mode);
                }
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Invalid URL']);
        }
    }

    function merchant_pay2all (Request $request){
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1]);
        $id = $request->client_id;
        $status = $request->status_id;
        if ($status == 1) {
            $status = 1;
            $txnid = $request->utr;
        }elseif ($status == 2){
            $status = 2;
            $txnid = '';
        }elseif ($status == 6){
            $amount = $request->amount;
            $utr = $request->utr;
            $virtual_account_number = $request->virtual_account_number;
            $sender_name = $request->sender_name;
            return $this->update_auto_payemnt($amount, $utr, $virtual_account_number, $sender_name);
        }else{
            $status = 3;
            $txnid = '';
        }
        if ($id != '' && $status != '') {
            $mode = "Call-back";
            $reports = Report::find($id);
            if ($reports->wallet_type == 1){
                $library = new RefundLibrary();
                return   $library->update_transaction($status, $txnid, $id, $mode);
            }elseif ($reports->wallet_type == 2){
                $library = new RefundLibrary();
                $library->update_transaction_aeps($status, $txnid, $id, $mode);
            }
        }
    }

    function update_auto_payemnt ($amount, $utr, $virtual_account_number, $sender_name){
        $request_ip =  request()->ip();
        $host = $_SERVER['HTTP_HOST'];
        $companies = Company::where('company_website', $host)->first();
        if ($companies){
            $icici_code = $companies->icici_code;
            $exploadnumber = explode($icici_code, $virtual_account_number);
            $mobile_number = $exploadnumber[1];
            $userdetails = User::where('mobile', $mobile_number)->first();
            if ($userdetails){
                $reports = Report::where('txnid', $utr)->first();
                if ($reports){
                    return Response()->json(['status' => 'failure', 'message' => 'duplicate utr number']);
                }else{
                    $provider_id = 262;
                    $scheme_id = $userdetails->scheme_id;
                    $library = new GetcommissionLibrary();
                    $commission =  $library->get_commission($scheme_id, $provider_id, $amount);
                    $retailer = $commission['retailer'];

                    $user_id = $userdetails->id;
                    $opening_balance = $userdetails->balance->user_balance;

                    $increament_amount = $amount + $retailer;
                    Balance::where('user_id', $user_id)->increment('user_balance', $increament_amount);
                    Balance::where('user_id', $user_id)->update(['balance_alert' => 1]);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $insert_id = Report::insertGetId([
                        'number' => $userdetails->mobile,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => 0,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => $retailer,
                        'mode' => "WEB",
                        'txnid' => $utr,
                        'ip_address' => $request_ip,
                        'description' => $sender_name,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'credit_by' => $user_id,
                        'wallet_type' => 1,
                    ]);
                    $amount=number_format($amount,2);
                    $user_balance=number_format($user_balance,2);

                    // $message = "Dear $userdetails->name Your Wallet Credited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                    $message = "Dear User, Your Wallet is Credited With Amount $amount. Your Current balance is $user_balance. For more info: trustxpay.org PAOBIL";
                    $template_id = 5;
                    $whatsappArr=[$amount,$user_balance];

                    $library = new SmsLibrary();
                    $library->send_sms($userdetails->mobile, $message, $template_id,$whatsappArr);
                    return Response()->json(['status' => 'success', 'message' => 'balance successfully updated']);
                }
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'user does not exist']);
            }

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Company not found']);
        }
    }
}
