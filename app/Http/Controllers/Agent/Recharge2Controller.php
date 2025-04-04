<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Provider;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Validator;

use App\Models\Bbpsparam;

use DB;

use Hash;
use \Crypt;
use Helpers;

use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use Illuminate\Support\Facades\Http;

class Recharge2Controller extends Controller
{
    public function __construct()
    {
        $this->service_id = env('SERVICE_ID', '28');
        $this->base_url = '';
        $mode = env('PAY_ONE_MODE', 'LIVE');

        //Live credentials
        $userID = '';
        $api_token = '';
        if ($mode != 'LIVE') {
            //Test` credentials
            $userID = '';
            $api_token = '';
        }
        $this->api_userid = $userID;
        $this->api_token = $api_token;
    }

    function welcome()
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $user_id = Auth::id();
        if (Auth::User()->role_id == 8) {
            $providerList = Provider::where('service_id', $this->service_id)->get();
            $data = array('page_title' => 'Recharge2', 'providerList' => $providerList);
            return view('agent.recharge-2.index')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function providerList(Request $request)
    {
        $type = ($request->type) ? $request->type : 1;
        $providerList = Provider::where('service_id', $this->service_id)->where('type', $type)->select('provider_code', 'provider_name')->get();
        return Response()->json(['status' => 'success', 'message' => 'successful..!', 'provider_list' => $providerList]);
    }

    function storeRecharge(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'amount' => 'required|numeric',
            'service_provider' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        return $this->doTransaction($request->all());
    }

    function doTransaction($params)
    {
        try {
            $request_ip = request()->ip();
            $user_id = Auth::id();
            $userdetails = User::find($user_id);
            $providers = Provider::where(['provider_code' => $params['service_provider'], 'service_id' => $this->service_id])
                ->where('status_id', 1)->first();

            $provider_id = $providers->id;
            $library = new BasicLibrary();
            $activeService = $library->getActiveService($provider_id, $user_id);
            $serviceStatus = $activeService['status_id'];
            $amount = $params['amount'];
            $mobileNumber = $params['mobile_number'];
            $api_id = 0;
            $client_id = '';
            $state_id = 0;
            $latitude = '';
            $longitude = '';
            if ($serviceStatus == 1) {
                $opening_balance = $userdetails->balance->user_balance;
                $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
                if ($opening_balance >= $sumamount && $sumamount >= 4) {
                    //get commission
                    $scheme_id = $userdetails->scheme_id;
                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                    $retailer = $commission['retailer'];
                    $d = $commission['distributor'];
                    $sd = $commission['sdistributor'];
                    $st = $commission['sales_team'];
                    $rf = $commission['referral'];
                    $tds = ($retailer * 5) / 100;
                    $deduct_amount = ($amount - $retailer) + $tds;

                    Balance::where('user_id', $user_id)->decrement('user_balance', $deduct_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;

                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $description = "$providers->provider_name  $mobileNumber";
                    $insert_id = Report::insertGetId([
                        'number' => $mobileNumber,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $api_id,
                        'status_id' => 3,
                        'client_id' => $client_id,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => $retailer,
                        'mode' => $params['mode'],
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'wallet_type' => 1,
                        'state_id' => $state_id,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'tds' => $tds
                    ]);
                    $api_type = 1;
                    //API Call will start here
                    $url = $this->base_url;
                    $transactionUniqId = Helpers::generateRandomNumber(6);
                    $parameters = array(
                        'userid' => $this->api_userid,
                        'token' => $this->api_token,
                        'opcode' => $params['service_provider'],
                        'number' => $params['mobile_number'],
                        'amount' => $params['amount'],
                        'transid' => $transactionUniqId
                    );
                    $method = 'POST';
                    $header = ["Accept:application/json"];
                    $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
                    $res = json_decode($response);
                    Log::info($response);
                    if (isset($res->status) && !empty($res->status)) {
                        $status = $res->status;
                        $client_id = $res->transid;
                        $txnid = $res->referenceid;

                        $api_request_parameters = json_encode($parameters);
                        $request_message = $url . '?' . $api_request_parameters;
                        Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $request_message]);
                        $print_url = url('agent/transaction-receipt') . '/' . Crypt::encrypt($insert_id);
                        $mobile_anchor = url('agent/mobile-receipt') . '/' . Crypt::encrypt($insert_id);
                        $row_data = $response;
                        // if success
                        if ($status == 'SUCCESS') {
                            Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid, 'row_data' => $row_data]);
                            $transaction_details = array(
                                'status' => 'success',
                                'operator_ref' => $txnid,
                                'provider_name' => $providers->provider_name,
                                'payid' => $insert_id,
                                'client_id' => $client_id,
                                'number' => $mobileNumber,
                                'profit' => $retailer,
                                'amount' => $amount,
                                'date' => "$ctime",
                                'print_url' => $print_url,
                                'mobile_anchor' => $mobile_anchor,
                            );
                            $message = "Dear $userdetails->name, Recharge Success Number: $mobileNumber Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                            $library = new Commission_increment();
                            $library->parent_recharge_commission($user_id, $mobileNumber, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                            return Response()->json(['status' => 'success', 'message' => $message, 'transaction_details' => $transaction_details]);
                        } elseif ($status == 'FAIL') {
                            Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            Report::where('id', $insert_id)->update(['status_id' => 2, 'failure_reason' => $res->message, 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance, 'row_data' => $row_data, 'tds' => 0]);
                            $message = "Dear  $userdetails->name, Transaction Failed, Number : $mobileNumber  Operator : $providers->provider_name And Amount Rs $amount Please check Detail or Try After some time, Thanks.";
                            $message .= " " . $res->message;
                            $transaction_details = array(
                                'status' => 'failure',
                                'operator_ref' => $txnid,
                                'provider_name' => $providers->provider_name,
                                'payid' => $insert_id,
                                'number' => $mobileNumber,
                                'profit' => $retailer,
                                'amount' => $amount,
                                'date' => "$ctime",
                                'print_url' => $print_url,
                                'mobile_anchor' => $mobile_anchor,
                            );
                            return Response()->json(['status' => 'failure', 'message' => $message, 'transaction_details' => $transaction_details]);
                        } else {
                            Report::where('id', $insert_id)->update(['row_data' => $row_data]);
                            $message = "Dear $userdetails->name, Recharge Submitted Number: $mobileNumber Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                            $transaction_details = array(
                                'status' => 'pending',
                                'operator_ref' => $txnid,
                                'provider_name' => $providers->provider_name,
                                'payid' => $insert_id,
                                'number' => $mobileNumber,
                                'profit' => $retailer,
                                'amount' => $amount,
                                'date' => "$ctime",
                                'print_url' => $print_url,
                                'mobile_anchor' => $mobile_anchor,
                            );
                            return Response()->json(['status' => 'success', 'message' => $message, 'transaction_details' => $transaction_details]);
                        }
                    } else {
                        Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        return Response()->json(['status' => 'failure', 'message' => 'Something went wrong please try again.', 'operator_ref' => 'Something went wrong.', 'payid' => '']);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Your Balance is low kinldy refill your wallet', 'operator_ref' => 'Your Balance is low kinldy refill your wallet', 'payid' => '']);
                }
            } else {
                //Need to change message
                $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
                return Response()->json(['status' => 'failure', 'message' => $message, 'operator_ref' => $userdetails->company->server_message, 'payid' => '']);
            }
        } catch (\Exception $exception) {
            Log::info($exception);
            return Response()->json(['status' => 'failure', 'message' => $exception->getMessage()]);
        }
    }

    public function webhookCallback(Request $request)
    {
        $status = $request['status'];
        $clientID = $request['yourtransid'];
        $txnids = $request['txnid'];
        $opids = $request['opid'];
        $mobileNumber = $request['number'];
        $amount = $request['amount'];
        $message = $request['message'];
        $reportData = Report::where('txnid', $txnids)->first();
        if (!empty($reportData)) {
            $providers = Provider::where('provider_name', $reportData->provider_name)->first();
            $provider_id = 0;
            $user_id = $reportData->user_id;
            $userdetails = User::where('id', $user_id)->first();
            $api_id = 0;
            if (!empty($providers)) {
                $provider_id = $providers->id;
            }
            $insert_id = $reportData->id;
            if ($status == 'SUCCESS') {
                Report::where('id', $reportData->id)->update(['status_id' => 1, 'txnid' => $txnids]);
                $library = new Commission_increment();
                $library->parent_recharge_commission($user_id, $mobileNumber, $insert_id, $provider_id, $amount, $api_id, 0, 0, 0, 0, 0);
            } elseif ($status == 'FAIL') {
                //get commission
                $scheme_id = $userdetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $deduct_amount = $amount - $retailer;
                Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                Report::where('id', $insert_id)->update(['status_id' => 2, 'failure_reason' => $message, 'txnid' => $txnids, 'profit' => 0, 'total_balance' => $user_balance]);
            }
        }
    }

    function recharge2Balance(Request $request)
    {


        try {
            $url = 'https://status.rechargeexchange.com/API.asmx/BalanceNew?userid=' . $this->api_userid . '&token=' . $this->api_token;
            $dataArr = array(
                "userid" => $this->api_userid,
                "token" => $this->api_userid,

            );
            // $response = Http::withHeaders(["content-type" => "application/json"])->post($url, $dataArr)->json();
            $method = 'GET';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_get($url);
            $res = json_decode($response);
            return $res;
        } catch (\Exception $e) {
            Log::info($e);
            return Response()->json(['status' => 'failure', 'message' => $e->getMessage()]);
        }
    }

}
