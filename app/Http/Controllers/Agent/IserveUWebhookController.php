<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Library\Commission_increment;
use App\Library\GetcommissionLibrary;
use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Paysprintbank;
use App\Models\Provider;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IserveUWebhookController extends Controller
{

    public function __construct()
    {
        $this->api_id = 3;
    }

    public function iserveuAepsWebhook(Request $request)
    {
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 3,'response_type'=>"iServeU AEPS"]);
        try {

            $data = $request->all();
            $txnId = $data['txnId'] ?? '';
            $tMode = "API";
            if(isset($data['param_b']) && $data['param_b']=="WEB"){
                $tMode = "WEB";
            }
            if (!$txnId) {
                return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
            }
            $checkExist = Report::where("txnid", $txnId)->first();
            if ($checkExist) {
                return response()->json(['status' => 1, 'statusDesc' => 'Duplicate transaction']);
            }


            $username = $data['username'] ?? '';
            $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
            if(!$userdetails){
                Log::error('Failure User not onboarded :'.$username,['data'=>$data]);
                return response()->json(['status' => 1, 'statusDesc' => 'Failure User not onboarded']);
            }
            if (isset($data['status']) && $data['status'] == 'SUCCESS') {
                $txnType = $data['txnType'] ?? '';

                if ($txnType == 'AEPS_BALANCE_ENQUIRY') {

                    return self::balance_enquiry_middle($data,$tMode);
                } elseif ($txnType == 'AEPS_MINI_STATEMENT') {

                    return self::mini_statement_middle($data,$tMode);
                } elseif ($txnType == 'AEPS_CASH_WITHDRAWAL') {

                    return self::cash_withdrawal_middle($data,$tMode);
                } elseif ($txnType == 'AADHAAR_PAY') {

                    return self::aadhar_pay_middle($data,$tMode);
                } else {
                    return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
                }
            } else {
                $txnType = $data['txnType'] ?? '';

                if ($txnType == 'AEPS_BALANCE_ENQUIRY') {

                    return self::decline_balance_enquiry_middle($data,$tMode);
                } elseif ($txnType == 'AEPS_MINI_STATEMENT') {

                    return self::decline_mini_statement_middle($data,$tMode);
                } elseif ($txnType == 'AEPS_CASH_WITHDRAWAL') {

                    return self::decline_cash_withdrawal_middle($data,$tMode);
                } elseif ($txnType == 'AADHAAR_PAY') {

                    return self::decline_aadhar_pay_middle($data,$tMode);
                } else {
                    return response()->json(['status' => 2, 'statusDesc' => 'Failure']);
                }
                // return response()->json(['status' => 1, 'statusDesc' => $data['statusDesc'] ?? 'Failure']);
            }
        } catch (\Exception $e) {
            Log::error('ISERVEU AEPS Webhook post request error : ' . $e->getMessage());
            return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
        }
    }

    public function balance_enquiry_middle($data,$tMode)
    {
        $username = $data['username'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $mobile_number = $userdetails->mobile;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 318;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name  $mobile_number";
        $retailer = 0;

        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => 0,
            'api_id' => $this->api_id,
            'status_id' => 1,
            'created_at' => $ctime,
            'user_id' => $userdetails->id,
            'profit' => $retailer,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'txnid' => $txnId,
            'row_data' => json_encode($data)
        ]);
        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);

        return response()->json(['status' => 0, 'statusDesc' => 'success']);
    }

    public function mini_statement_middle($data,$tMode)
    {
        $username = $data['username'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $user_id = $userdetails->id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 320;
        $mobile_number = $userdetails->mobile;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name  $mobile_number";
        $scheme_id = $userdetails->scheme_id;
        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount = 10, 1);
        $retailer = $commission['retailer'];
        $distributor = $commission['distributor'];
        $sdistributor = $commission['sdistributor'];
        $sales_team = $commission['sales_team'];
        $referral = $commission['referral'];
        $tds = 0;
        if ($retailer) {
            $tds = ($retailer * 5) / 100;
        }

        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => 0,
            'api_id' => $this->api_id,
            'status_id' => 1,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'row_data' => json_encode($data)
        ]);

        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);

        $increment_amount = $retailer - $tds;
        Balance::where('user_id', $user_id)->increment('aeps_balance', $increment_amount);
        $balance = Balance::where('user_id', $user_id)->first();
        $user_balance = $balance->aeps_balance;
        $library = new Commission_increment();
        $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
        Report::where('id', $insert_id)->update(['status_id' => 1, 'profit' => $retailer, 'txnid' => $txnId, 'total_balance' => $user_balance, 'tds' => $tds]);

        return response()->json(['status' => 0, 'statusDesc' => 'success']);
    }

    public function cash_withdrawal_middle($data,$tMode)
    {
        $amount = $data['txnAmount'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $username = $data['username'] ?? '';

        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $user_id = $userdetails->id;
        $mobile_number = $userdetails->mobile;
        $scheme_id = $userdetails->scheme_id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 319;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name $mobile_number";

        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, 3);
        $retailer = $commission['retailer'];
        $distributor = $commission['distributor'];
        $sdistributor = $commission['sdistributor'];
        $sales_team = $commission['sales_team'];
        $referral = $commission['referral'];
        $tds = 0;
        if ($retailer) {
            $tds = ($retailer * 5) / 100;
        }

        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => $amount,
            'api_id' => $this->api_id,
            'status_id' => 1,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'tds' => $tds,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'row_data' => json_encode($data)
        ]);

        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);

        $increment_amount = ($amount + $retailer) - $tds;
        Balance::where('user_id', $user_id)->increment('aeps_balance', $increment_amount);
        $balance = Balance::where('user_id', $user_id)->first();
        $user_balance = $balance->aeps_balance;
        Report::where('id', $insert_id)->update(['status_id' => 6, 'profit' => $retailer, 'txnid' => $txnId, 'total_balance' => $user_balance]);
        $library = new Commission_increment();
        $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);

        // get wise commission
        $library = new GetcommissionLibrary();
        $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
        $apiCommission = $apiComms['apiCommission'];
        $commissionType = $apiComms['commissionType'];
        $library = new Commission_increment();
        $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $distributor, $sdistributor, $sales_team, $referral, $apiCommission, $insert_id, $commissionType);

        return response()->json(['status' => 0, 'statusDesc' => 'success']);
    }

    public function aadhar_pay_middle($data,$tMode)
    {
        $amount = $data['txnAmount'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $username = $data['username'] ?? '';

        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $user_id = $userdetails->id;
        $mobile_number = $userdetails->mobile;
        $scheme_id = $userdetails->scheme_id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 321;

        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name $mobile_number";

        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, 3);
        $retailer = $commission['retailer'];
        $distributor = $commission['distributor'];
        $sdistributor = $commission['sdistributor'];
        $sales_team = $commission['sales_team'];
        $referral = $commission['referral'];


        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => $amount,
            'api_id' => $this->api_id,
            'status_id' => 1,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'row_data' => json_encode($data),
        ]);

        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);

        $gst = ($retailer * 18) / 100;
        $totalChargeAmount = $retailer + $gst;
        $sum_amount = $amount - $totalChargeAmount;
        Balance::where('user_id', $user_id)->increment('aeps_balance', $sum_amount);
        $balance = Balance::where('user_id', $user_id)->first();
        $total_balance = $balance->aeps_balance;

        Report::where('id', $insert_id)->update([
            'status_id' => 1,
            'txnid' => $txnId,
            'profit' => '-' . $retailer,
            'total_balance' => $total_balance,
            'gst' => $gst,
        ]);

        $library = new Commission_increment();
        $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
        // get wise commission
        $library = new GetcommissionLibrary();
        $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
        $apiCommission = $apiComms['apiCommission'];
        $commissionType = $apiComms['commissionType'];
        $library = new Commission_increment();
        $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $distributor, $sdistributor, $sales_team, $referral, $apiCommission, $insert_id, $commissionType);

        return response()->json(['status' => 0, 'statusDesc' => 'success']);
    }
    // Decline status functions
    public function decline_balance_enquiry_middle($data,$tMode)
    {
        $username = $data['username'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $failure_reason = $data['statusDesc'] ?? '';
        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $mobile_number = $userdetails->mobile;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 318;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name  $mobile_number";
        $retailer = 0;

        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => 0,
            'api_id' => $this->api_id,
            'status_id' => 2,
            'created_at' => $ctime,
            'user_id' => $userdetails->id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'txnid' => $txnId,
            'row_data' => json_encode($data),
            'failure_reason'=>$failure_reason
        ]);
        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);

        return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
    }

    public function decline_mini_statement_middle($data,$tMode)
    {
        $username = $data['username'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $failure_reason = $data['statusDesc'] ?? '';
        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $user_id = $userdetails->id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 320;
        $mobile_number = $userdetails->mobile;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name  $mobile_number";
        $scheme_id = $userdetails->scheme_id;
        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount = 10, 3);
        $retailer = $commission['retailer'];
        $distributor = $commission['distributor'];
        $sdistributor = $commission['sdistributor'];
        $sales_team = $commission['sales_team'];
        $referral = $commission['referral'];
        $tds = 0;
        if ($retailer) {
            $tds = ($retailer * 5) / 100;
        }

        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => 0,
            'api_id' => $this->api_id,
            'status_id' => 2,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'row_data' => json_encode($data),
            'failure_reason'=>$failure_reason
        ]);

        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);
        Report::where('id', $insert_id)->update(['txnid' => $txnId]);
        return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
    }

    public function decline_cash_withdrawal_middle($data,$tMode)
    {
        $amount = $data['txnAmount'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $username = $data['username'] ?? '';
        $failure_reason = $data['statusDesc'] ?? '';
        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $user_id = $userdetails->id;
        $mobile_number = $userdetails->mobile;
        $scheme_id = $userdetails->scheme_id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 319;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name $mobile_number";

        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, 3);
        $retailer = $commission['retailer'];
        $distributor = $commission['distributor'];
        $sdistributor = $commission['sdistributor'];
        $sales_team = $commission['sales_team'];
        $referral = $commission['referral'];
        $tds = 0;
        if ($retailer) {
            $tds = ($retailer * 5) / 100;
        }

        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => $amount,
            'api_id' => $this->api_id,
            'status_id' => 2,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'tds' => 0,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'row_data' => json_encode($data),
            'failure_reason'=>$failure_reason
        ]);

        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);
        Report::where('id', $insert_id)->update(['txnid' => $txnId]);
        return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
    }

    public function decline_aadhar_pay_middle($data,$tMode)
    {
        $amount = $data['txnAmount'] ?? '';
        $txnId = $data['txnId'] ?? '';
        $username = $data['username'] ?? '';
        $failure_reason = $data['statusDesc'] ?? '';
        $userdetails = User::where('cms_agent_id', $username)->where('iserveu_onboard_status', 1)->first();
        $user_id = $userdetails->id;
        $mobile_number = $userdetails->mobile;
        $scheme_id = $userdetails->scheme_id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 321;

        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name $mobile_number";

        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, 3);
        $retailer = $commission['retailer'];
        $distributor = $commission['distributor'];
        $sdistributor = $commission['sdistributor'];
        $sales_team = $commission['sales_team'];
        $referral = $commission['referral'];


        $insert_id = Report::insertGetId([
            'number' => $mobile_number,
            'provider_id' => $provider_id,
            'amount' => $amount,
            'api_id' => $this->api_id,
            'status_id' => 2,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $tMode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'provider_api_from' => 3,
            'row_data' => json_encode($data),
            'failure_reason'=>$failure_reason
        ]);

        Apiresponse::insertGetId(['message' => json_encode($data), 'api_type' => 3, 'report_id' => $insert_id]);
        Report::where('id', $insert_id)->update(['txnid' => $txnId]);
        return response()->json(['status' => 1, 'statusDesc' => 'Failure']);
    }
}
