<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Apiresponse;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use Hash;
use Helpers;
use App\Library\BasicLibrary;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Report;
use App\Models\User;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\ValidationLibrary;
use App\Library\LocationRestrictionsLibrary;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recharge1Controller extends Controller
{
    private $service_id;
    public function __construct()
    {
        $this->service_id = 26;
    }


    function welcome()
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $user_id = Auth::id();
        $params['providers'] = Provider::where('service_id', $this->service_id)->where('status_id', 1)->get();
        if (Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Recharge');
            return view('agent.recharge.index', $params)->with($data);
        } else {
            return redirect()->back();
        }
    }

    function create(Request $request)
    {
        $service_type = $request->service_type;
        if ($service_type == "M") {
            $rules = array(
                'mobile_number' => 'required|digits:10',
                'provider_id' => 'required',
                'amount' => 'required'
            );
        } else {
            $rules = array(
                'mobile_number' => 'required|min:10|max:11',
                'provider_id' => 'required',
                'amount' => 'required'
            );
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $validation = new ValidationLibrary();
        $rules = $validation->rechargeValidation($request);

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $number = $request->mobile_number;
        $amount = $request->amount;
        $provider_id = $request->provider_id;
        $client_id = NULL;
        $user_id = Auth::id();

        $mode = ($request->mode) ? $request->mode : "WEB";
        $latitude = $request->latitude ?? "";
        $longitude = $request->longitude ?? "";
        $request_ip = request()->ip();

        $userdetails = User::find($user_id);
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount) {
                $library = new BasicLibrary();
                $apidetails = $library->get_api($provider_id, $number, $amount, $user_id);
                $api_id = $apidetails['api_id'];
                $state_id = $apidetails['state_id'];

                //get commission
                $scheme_id = $userdetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                //pre( $commission);
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
                $description = "$providers->provider_name  $number";
                $insert_id = Report::insertGetId([
                    'number' => $number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => 3,
                    'client_id' => $client_id,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => $retailer,
                    'mode' => $mode,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'wallet_type' => 1,
                    'state_id' => $state_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'tds'=>$tds
                ]);
                $api_type = 0;
                $result = $this->recharge_api($request, $insert_id);
                // pre($result);
                if ($result) {
                    $txnid = $result['TransactionReference'];
                    $FailureReason = $result['FailureReason'];
                    $print_url = url('agent/transaction-receipt') . '/' . Crypt::encrypt($insert_id);
                    $mobile_anchor = url('agent/mobile-receipt') . '/' . Crypt::encrypt($insert_id);
                    // if success
                    if (isset($result['Status']) && $result['Status'] == 0) {
                        $txnid = $result['TransactionReference'];
                        Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid]);
                        $transaction_details = array(
                            'status' => 'success',
                            'operator_ref' => $txnid,
                            'provider_name' => $providers->provider_name,
                            'payid' => $insert_id,
                            'number' => $number,
                            'profit' => $retailer,
                            'amount' => $amount,
                            'date' => "$ctime",
                            'print_url' => $print_url,
                            'mobile_anchor' => $mobile_anchor,
                        );
                        $message = "Dear $userdetails->name, Recharge Success Number: $number Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);

                        return Response()->json(['status' => 'success', 'message' => $message, 'transaction_details' => $transaction_details]);
                    } // if failure
                    elseif (isset($result['Status']) && $result['Status'] == 2) {
                        Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;
                        Report::where('id', $insert_id)->update(['status_id' => 2,'failure_reason' => $FailureReason, 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance,'tds'=>0]);
                        $message = "Dear  $userdetails->name, Transaction Failed, $FailureReason, Number : $number  Operator : $providers->provider_name And Amount Rs $amount ";
                        $transaction_details = array(
                            'status' => 'failure',
                            'operator_ref' => $txnid,
                            'provider_name' => $providers->provider_name,
                            'payid' => $insert_id,
                            'number' => $number,
                            'profit' => $retailer,
                            'amount' => $amount,
                            'date' => "$ctime",
                            'print_url' => $print_url,
                            'mobile_anchor' => $mobile_anchor,
                        );
                        return Response()->json(['status' => 'failure', 'message' => $message, 'transaction_details' => $transaction_details]);
                    } // if pending
                    else {
                        $message = "Dear $userdetails->name, Recharge Submitted Number: $number Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                        $transaction_details = array(
                            'status' => 'pending',
                            'operator_ref' => $txnid,
                            'provider_name' => $providers->provider_name,
                            'payid' => $insert_id,
                            'number' => $number,
                            'profit' => $retailer,
                            'amount' => $amount,
                            'date' => "$ctime",
                            'print_url' => $print_url,
                            'mobile_anchor' => $mobile_anchor,
                        );
                        return Response()->json(['status' => 'success', 'message' => $message, 'transaction_details' => $transaction_details]);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Something went wrong try again or later', 'operator_ref' => '', 'payid' => '']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your Balance is low kindly refill your wallet', 'operator_ref' => 'Your Balance is low kindly refill your wallet', 'payid' => '']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message, 'operator_ref' => $userdetails->company->server_message, 'payid' => '']);
        }
    }

    public function recharge_api($request, $insert_id)
    {
        $number = $request->mobile_number;
        $amount = $request->amount;
        $provider_id = $request->provider_id;
        $service_type = $request->service_type;
        $is_post_paid = $request->is_post_paid ?? "N";
        $Provider = Provider::where('id', $provider_id)->value('provider_name');
        if (env('RECHARGE_PAYMENT_MODE') == 'TEST') {
            $CorporateNumber = "";
            $shaSecretKey = "";
            $apiurl = "https://corptestapi.justrechargeit.com/api/Recharge";
        } else {
            $CorporateNumber = "";
            $shaSecretKey = "";
            $apiurl = "https://corpapi.justrechargeit.com/api/Recharge";
        }
        $Isspecial = "N";
        if ($Provider == 'BSNL' || $Provider == 'MTNL Delhi' || $Provider == "MTNL Mumbai") {
            $Isspecial = "Y";
        }
        $rowData = array(
            "Amount" => $amount,
            "CorporateNumber" => $CorporateNumber,
            "Isspecial" => $Isspecial,
            "IsPostPaid" => $is_post_paid,
            "Location" => "",
            "NickName" => "",
            "Provider" => $Provider,
            "RechargeNumber" => $number,
            "ServiceType" => $service_type
        );

        $randomString = Str::random(14); // Generates a random string with remaining length to make it 14 characters
        $SystemReference = strtoupper($randomString);
        $rowData['SystemReference'] = $SystemReference;

        $APIChecksum = $CorporateNumber . $number . $amount . $SystemReference . $shaSecretKey;
        $APIChecksum = hash('sha256', $APIChecksum);
        $rowData['APIChecksum'] = $APIChecksum;

        try {
            $response = Http::withHeaders(["content-type" => "application/json"])->post($apiurl, $rowData)->json();
            $api_request_parameters = json_encode($rowData);
            $request_message = $apiurl . '?' . $api_request_parameters;
            Apiresponse::insertGetId(['message' => json_encode($response), 'api_type' => 0, 'report_id' => $insert_id, 'request_message' => $request_message]);
            Report::where('id', $insert_id)->update(['api_id' => 0, 'api_comm' => 0, 'row_data' => $rowData]);
            return $response;
        } catch (\Exception $e) {
            $response = [];
            $response['Status'] = 0;
            $response['FailureReason'] = 'Something went wrong. Please try again or later.';
            $response['TransactionReference'] = "";
            return $response;
        }
    }
    public function getPlans(Request $request)
    {
        // $data = '{"MobileNumber":"7096976424","Provider":"Vi","SystemReference":"B58C6NDJQFRJSB","TransactionReference":"CXXEHTM37341105","Status":0,"Message":"Successful","FailureReason":"","RequestDateTime":"2024-08-02 16:46:25.8422828","Plans":[{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"02-08-2024 16:46:25","planid":"456839242.456839242.456839242.1722584820000.206993.980270","talktime":0,"amount":22,"validity":"1 Day","plan_description":"Add-On Data pack: Get 1GB for 1 day (till 11:59 PM). No Service Validity. No Outgoing SMS","plan_status":"ACTIVE","plan_category_name":"121 Made for you","top_plan":"N","plan_subcategory":"Data","plan_validity":"1 Day"},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"02-08-2024 16:46:25","planid":"456839242.456839242.456839242.1722584820000.206993.980271","talktime":0,"amount":69,"validity":"28 Days","plan_description":"Add-On Data pack: 3GB Data. Validity: 28 Days. No Service Validity","plan_status":"ACTIVE","plan_category_name":"121 Made for you","top_plan":"N","plan_subcategory":"Data","plan_validity":"28 Days"},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"25-07-2024 17:30:08","planid":"HGA1Q036950000124215","talktime":"NA","amount":365,"validity":"28","plan_description":"Get 2 GB/Day + Unlimited Calls + 100 SMS/Day valid for 28 Days. Enjoy Night Data without limits from 12am to 6am. Carry Mon-Fri unused Data into Sat-Sun. Upto 2GB of Backup Data every month at no extra cost.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Hero Unlimited","circleid":15,"plan_validity":"28 Days","cat_seq":3,"plan_seq":1},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"25-07-2024 17:30:08","planid":"HGA1Q115720000124216","talktime":"NA","amount":349,"validity":"28","plan_description":"Get 1.5 GB/Day + Unlimited Calls + 100 SMS/Day valid for 28 Days. Enjoy Night Data without limits from 12am to 6am. Carry Mon-Fri unused Data into Sat-Sun. Upto 2GB of Backup Data every month at no extra cost. You can recharge with this plan and get the same benefits of the earlier Rs. 299 plan.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Hero Unlimited","circleid":15,"plan_validity":"28 Days","cat_seq":3,"plan_seq":2},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"26-07-2024 22:20:06","planid":"HGA1Q092990000127458","talktime":"NA","amount":48,"validity":"3","plan_description":"ADD-ON Data pack Get 6GB (3GB + Extra 3GB) for 3 Days. Limited Period Offer. NO Service Validity. No Outgoing SMS. You can recharge with this pack and get the same benefits of the earlier Rs. 39 pack.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Add On Data","circleid":15,"plan_validity":"3 Days","cat_seq":4,"plan_seq":1},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"26-07-2024 22:20:06","planid":"HGA1Q01A940000127459","talktime":"NA","amount":22,"validity":"1","plan_description":"ADD-ON Data pack 1 GB for 1 Day (Till 11:59 PM). NO Service Validity. No Outgoing SMS. You can recharge with this pack and get the same benefits of the earlier Rs. 19 pack.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Add On Data","circleid":15,"plan_validity":"1 Day","cat_seq":4,"plan_seq":2},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"25-07-2024 18:10:06","planid":"HGA1Q0A1610000127187","talktime":"NA","amount":1599,"validity":"84","plan_description":"84 Days subscription of Netflix (TV + Mobile) Basic (Access to HD Videos on 1 device). Get 2.5GB/Day + Unlimited Calls + 100 SMS/Day valid for 84 Days. Enjoy Night Data without limits from 12am to 6am. Carry Mon-Fri unused Data into Sat-Sun. Upto 2GB of Backup Data every month at no extra cost.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Entertainment","circleid":15,"plan_validity":"84 Days","cat_seq":5,"plan_seq":1},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"25-07-2024 17:30:08","planid":"HGA1Q0D1F80000124255","talktime":"NA","amount":202,"validity":"31","plan_description":"Watch Disney+ Hotstar, Sony LIV, Fancode, Aaj Tak, Manoramax and many more on TV and Mobile. Enjoy 13 OTT Apps + 400 TV channels, subscribe to Vi Movies and TV App. Add-on 5GB data. No Service Validity. Pack validity is 1 month.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Entertainment","circleid":15,"plan_validity":"1 Month","cat_seq":5,"plan_seq":2},{"objectid":"plan","billerid":"VILPRE","biller_category":"Mobile Prepaid","biller_name":"Vi Prepaid","plan_created_on":"25-07-2024 17:30:08","planid":"HGA1Q0EBC20000124271","talktime":"NA","amount":509,"validity":"84","plan_description":"Get 2GB/Day + Unlimited Calls + 100 SMS/Day + 84 Days subscription of Netflix (Mobile) Basic (Access to HD Videos on 1 device). Enjoy Night Data without limits from 12am to 6am. Carry Mon-Fri unused Data into Sat-Sun. Upto 2GB of Backup Data every month at no extra cost.","circle_name":"Mumbai","plan_status":"ACTIVE","plan_category_name":"Entertainment","circleid":15,"plan_validity":"84 Days","cat_seq":5,"plan_seq":3}]}';
        // $data = json_decode($data);
        // // return $data = json_encode($data);
        // return Response()->json(['status' => '0','data' => $data]);
        // pre($data);
        $CorporateNumber = "2024072218273241083539654";
        $shaSecretKey = "07222024wKcA25Q00300OKk";
        $apiurl = "https://spi.justrechargeit.com/jri121/api/RechargePlan";
        $Provider = $request->provider;
        $number = $request->mobile_number;
        $rowData = array(
            "CorporateNumber" => $CorporateNumber,
            "Provider" => $Provider,
            "MobileNumber" => $number
        );
        $randomString = Str::random(14);
        $SystemReference = strtoupper($randomString);
        $rowData['SystemReference'] = $SystemReference;

        $APIChecksum = $CorporateNumber . $number . $SystemReference . $shaSecretKey;
        $APIChecksum = hash('sha256', $APIChecksum);
        $rowData['APIChecksum'] = $APIChecksum;
        if (env('APP_ENV') == 'local') {
            return $response = $this->localGetPlans($rowData, $apiurl);
        } else {
            try {
                $response = Http::withHeaders(["content-type" => "application/json"])->post($apiurl, $rowData)->json();
                return $response;
            } catch (\Exception $e) {
                Log::error("getPlans===" . $e->getMessage());
                $response = [];
                $response['Status'] = 2;
                $response['FailureReason'] = 'Something went wrong. Please try again or later.';
                $response['TransactionReference'] = "";
                return $response;
            }
        }
    }

    public function localGetPlans($rowData, $url)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($rowData));
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                $response = [];
                $response['Status'] = 2;
                $response['FailureReason'] = 'Something went wrong. Please try again or later.';
                $response['TransactionReference'] = "";
                return $response;
            }
            curl_close($ch);
            return json_decode($response, true);
        } catch (\Exception $e) {
            Log::error("localGetPlans===" . $e->getMessage());
            $response = [];
            $response['Status'] = 2;
            $response['FailureReason'] = 'Something went wrong. Please try again or later.';
            $response['TransactionReference'] = "";
            return $response;
        }
    }
}
