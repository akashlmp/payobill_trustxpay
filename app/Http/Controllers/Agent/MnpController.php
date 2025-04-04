<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Apiresponse;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Validator;
use Helpers;
use App\Models\Balance;
use App\Models\User;
use App\Models\Report;

class MnpController extends Controller
{
    public function __construct()
    {
        $this->service_id = env('SERVICE_ID', '29');
        $this->base_url = '';

        $this->corporate_no='';
        $this->md5_key='';
        $this->mnp_password='';
        $this->mnp_charges='';
        if (env('APP_ENV') == 'local') {
            $this->mnp_provider_id = 587;
        } else {
            $this->mnp_provider_id = 589;
        }
        // $this->mnp_provider_id=589;

    }

    public function welcome()
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $user_id = Auth::id();
        if (Auth::User()->role_id == 8) {
            $providerList = Provider::where('service_id', $this->service_id)->get();
            $data = array('page_title' => 'MNP', 'providerList' => $providerList);
            return view('agent.mnp.index')->with($data);
        } else {
            return redirect()->back();
        }
    }

    public function storeMnp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }


        $user_id = Auth::id();
        $userdetails = User::find($user_id);

        $balance = Balance::where('user_id', $user_id)->first();

        if($balance->user_balance<1)
        {
            return Response()->json(['status' => 'error', 'message' => 'Insufficient Balance', 'errors' => $validator->getMessageBag()->toArray()]);

        }

        //mnp detail api call
        $data = $this->getMnpDetail($request->all());

        return Response()->json(['status' => 'success', 'message' => 'Success', 'data' => $data]);

    }

    public function getMnpDetail($params)
    {

        $user_id = Auth::id();
        $userdetails = User::find($user_id);
        $mode = env('MNP_MODE', 'LIVE');

        if($mode!='LIVE')
        {
            $this->base_url='';

        }
        else{
            $this->base_url=$this->base_url.'MNPApi';
        }
        $url = $this->base_url;

        $transactionUniqId = Helpers::generateRandomNumber(6);

        $md5String=md5($this->corporate_no.$params['mobile_number'].$transactionUniqId.$this->md5_key);

        $parameters = array(
            'CorporateNo' => $this->corporate_no,
            'MobileNo' => $params['mobile_number'],
            'SystemReferenceNo' => $transactionUniqId,
            'APIChecksum' => $md5String
        );

        $method = 'POST';
        $header = ["Accept:application/json"];



        $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
        $res = json_decode($response);

        Log::info($response);
        //deduct charges from user wallet
        if(empty($res->Error))
        {

            Balance::where('user_id', $user_id)->decrement('user_balance', $this->mnp_charges);
            $balance = Balance::where('user_id', $user_id)->first();

            $mobileNumber = $params['mobile_number'];
            $provider_id=$this->mnp_provider_id;
            $amount=$this->mnp_charges;
            $api_id = 0;
            $client_id = '';

            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $description = "MNP Detail - $mobileNumber";

            $request_ip = request()->ip();
            $opening_balance=$userdetails->balance->user_balance;
            $user_balance =  $balance->total_balance;


            $state_id = 0;
            $latitude = '';
            $longitude = '';


            $insert_id = Report::insertGetId([
                'number' => $mobileNumber,
                'provider_id' => $provider_id,
                'amount' => $amount,
                'api_id' => $api_id,
                'status_id' => 3,
                'client_id' => $client_id,
                'created_at' => $ctime,
                'user_id' => $user_id,
                'profit' => 0,
                'mode' => $params['mode'],
                'ip_address' => $request_ip,
                'description' => $description,
                'opening_balance' => $opening_balance,
                'total_balance' => $user_balance,
                'wallet_type' => 1,
                'state_id' => $state_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

        }
        $api_request_parameters = $api_request_parameters = json_encode($parameters);;
        $request_message = $url . '?' . $api_request_parameters;
        Apiresponse::insertGetId(['message' => $response, 'api_type' => 0,  'request_message' => $request_message]);


        return $res;

    }

    public function getMnpBalance(Request $request)
    {
        $url = $this->base_url."api/Balance";
        $transactionUniqId = Helpers::generateRandomNumber(6);


        $parameters = array(
            'CorporateNumber' => $this->corporate_no,
            'Password' => $this->mnp_password
        );

        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
        $res = json_decode($response);

        Log::info($response);
        return $res;
        // return Response()->json(['status' => 'success', 'message' => 'Success', 'data' => $res]);

    }
}
