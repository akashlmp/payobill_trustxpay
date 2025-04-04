<?php

namespace App\Http\Controllers\Agent;

use App\Bankit\Aeps;
use App\Http\Controllers\Controller;
use App\Models\Sitesetting;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Validator;
use App\Models\Paysprintbank;
use App\Models\Apiresponse;
use Str;
use App\Models\User;
use App\Models\Report;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Api;
use App\Models\Aepsreport;
use App\Models\Paysprintaepsagent;
use App\Models\Traceurl;
use App\Models\Service;
use Crypt;
use Helpers;
use App\Library\Commission_increment;
use App\Library\GetcommissionLibrary;
use App\Library\BasicLibrary;
use Stevebauman\Location\Facades\Location;
use App\Paysprint\Apicredentials as PaysprintApicredentials;

class PaysprintAepsController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $this->aeps_provider = $dt->aeps_provider;
        $mode = 'LIVE'; // LIVE or TEST
        $library = new PaysprintApicredentials();
        $response = $library->getCredentials($mode);
        $this->base_url = $response['base_url'];
        $this->partner_id = $response['partner_id'];
        $this->api_key = $response['api_key'];
        $this->jwt_header = $response['jwt_header'];
        $this->authorised_key = $response['authorised_key'];
        $this->key = $response['key'];
        $this->iv = $response['iv'];
        $this->api_id = $response['api_id'];
        $this->shortCode = "";
        $this->pipe = 'bank2';
        $this->bankit = new Aeps();

        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }

    function agent_onboarding()
    {
        $provider_id = 331;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        $shortCode = Auth::User()->cms_agent_id;
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array(
                'page_title' => 'Agent Onboarding',
                'short_code' => $shortCode,
            );
            return view('agent.aeps.paysprint.agent_onboarding')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function agentOnboardingApp(Request $request)
    {
        $merchant_code = Auth::User()->cms_agent_id;
        $mobile_number = Auth::User()->mobile;
        $email = Auth::User()->email;
        $firm = Auth::User()->member->shop_name;
        if(!Auth::User()->paysprint_merchantcode){
            User::where('id', Auth::id())->update(['paysprint_merchantcode' => $merchant_code]);
        }
        $details = array(
            'merchant_code' => $merchant_code,
            'mobile_number' => $mobile_number,
            'email' => $email,
            'firm' => $firm,
            'partner_id' => $this->partner_id,
            'api_key' => $this->api_key,
        );
        return Response()->json([
            'status' => 'success',
            'message' => 'Successful..!',
            'details' => $details,
        ]);
    }

    function agent_onboarding_save(Request $request)
    {
        \Log::info("agent_onboarding_save", ['data' => $request->all()]);
        $provider_id = 331;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'merchant_code' => 'required',
                'mobile_number' => 'required|digits:10',
                'email' => 'required|email',
                'firm' => 'required',
                'callback' => (Auth::User()->role_id == 10) ? 'required' : '',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $merchant_code = $request->merchant_code;
            $mobile_number = $request->mobile_number;
            $email = $request->email;
            $firm = $request->firm;
            $callback = $request->callback;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            if (Auth::User()->role_id == 10) {
                $paysprintaepsagents = Paysprintaepsagent::where('merchantcode', $merchant_code)->first();
                if (empty($paysprintaepsagents)) {
                    Paysprintaepsagent::insert([
                        'user_id' => Auth::id(),
                        'merchantcode' => $merchant_code,
                        'mobile' => $mobile_number,
                        'email' => $email,
                        'firm' => $firm,
                        'callback' => $callback,
                        'created_at' => $ctime,
                        'status_id' => 3,
                    ]);
                } else {
                    Paysprintaepsagent::where('id', $paysprintaepsagents->id)->update([
                        'callback' => $callback,
                        'mobile' => $mobile_number,
                        'email' => $email,
                        'firm' => $firm,
                    ]);
                }
            } else {
                User::where('id', Auth::id())->update(['paysprint_merchantcode' => $merchant_code]);
            }
            $callback = (Auth::User()->role_id == 10) ? $request->return_url : url('agent/aeps/v2/agent-onboarding');
            $parameters = '{
                    "merchantcode": "' . $merchant_code . '",
                    "mobile": "' . $mobile_number . '",
                    "is_new": "0",
                    "email": "' . $email . '",
                    "firm": "' . $firm . '",
                    "callback": "' . $callback . '"
                }';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                //  "Authorisedkey: $this->authorised_key"
            );
            $url = $this->base_url . "api/v1/service/onboard/onboard/getonboardurl"; // for live
            //$url = $this->base_url . "api/v1/service/onboard/onboardnew/getonboardurl"; // for test
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $parameters,
                CURLOPT_HTTPHEADER => $header,
            ));
            $response = curl_exec($curl);
            \Log::info("agent_onboarding_save_response", ['data' => $response]);
            curl_close($curl);
            if ($response) {
                $res = json_decode($response);
                if ($res->status == true || $res->response_code == 1) {
                    if ($res->onboard_pending == 0) {
                        return Response()->json(['status' => 'failure', 'message' => $res->message]);
                    } else {
                        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'merchantcode' => $merchant_code, 'redirecturl' => $res->redirecturl]);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'server not responding']);
            }
        } else {
            return redirect()->back();
        }
    }

    function paysprint_webhook(Request $request)
    {
        $json = file_get_contents('php://input');
        Apiresponse::insertGetId(['message' => $request, 'api_type' => $this->api_id, 'report_id' => '', 'request_message' => $json, 'response_type' => 'paysprint_callback', 'created_at' => Carbon::now()]);
        if (empty($json)) {
            return '{"status":200,"message":"Transaction completed successfully"}';
        }
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $response = json_decode($json);
        $param = $response->param;
        $merchant_id = $param->merchant_id;
        //$amount = $param->amount;
        Apiresponse::insertGetId(['message' => $json, 'api_type' => $this->api_id, 'report_id' => '']);
        $userDetails = User::where('paysprint_merchantcode', $merchant_id)->first();
        if (empty($userDetails)) {
            $paysprintaepsagents = Paysprintaepsagent::where('merchantcode', $merchant_id)->first();
            if (empty($paysprintaepsagents)) {
                return '{"status":200,"message":"Transaction completed successfully"}';
            }
            $user_id = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->user_id;
            $callback = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->callback;
            $userDetails = User::find($user_id);
        }
        if ($userDetails) {
            $user_id = $userDetails->id;
            $scheme_id = $userDetails->scheme_id;
            $mobile_number = $userDetails->mobile;
            $opening_balance = $userDetails->balance->user_balance;
            $request_ip = request()->ip();
            $provider_id = 334;
            $client_id = "";
            $mode = "WEB";
            $reports = Report::where('user_id', $user_id)->where('txnid', $merchant_id)->first();
            if (empty($reports)) {
                $amount = 5;
                $sumAmount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
                if ($opening_balance >= $sumAmount && $amount >= 1) {
                    $providers = Provider::find($provider_id);
                    $description = "$providers->provider_name  $mobile_number";
                    Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $insert_id = Report::insertGetId([
                        'number' => $mobile_number,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $this->api_id,
                        'status_id' => 7,
                        'client_id' => $client_id,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'txnid' => $merchant_id,
                        'profit' => 0,
                        'mode' => $mode,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'wallet_type' => 1,
                    ]);
                    if (!empty($callback)) {
                        $merchantcode = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->merchantcode;
                        $mobile = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->mobile;
                        $email = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->email;
                        $url = $callback . "?merchant_code=$merchantcode&mobile_number=$mobile&email=$email";
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        $response = curl_exec($curl);
                        curl_close($curl);
                        Traceurl::insertGetId([
                            'user_id' => $user_id,
                            'url' => $url,
                            'number' => $merchantcode,
                            'response_message' => $response,
                            'created_at' => $ctime
                        ]);
                    }
                    return '{"status":200,"message":"Transaction completed successfully"}';
                }
            }
            if (!empty($callback)) {
                $merchantcode = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->merchantcode;
                $mobile = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->mobile;
                $email = (empty($paysprintaepsagents)) ? '' : $paysprintaepsagents->email;
                $url = $callback . "?merchant_code=$merchantcode&mobile_number=$mobile&email=$email";
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($curl);
                curl_close($curl);
                Traceurl::insertGetId([
                    'user_id' => $user_id,
                    'url' => $url,
                    'number' => $merchantcode,
                    'response_message' => $response,
                    'created_at' => $ctime
                ]);
            }
        }
        return '{"status":200,"message":"Transaction completed successfully"}';
    }

    function welcome()
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $data = array('page_title' => 'Aadhaar Enabled Payment System (AePS)');
        $provider_id = 331;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            if ($this->aeps_provider == 2) {
                if (!Auth::user()->cms_agent_id || Auth::user()->aeps_onboard_status == 0) {
                    Session::put('error', "Agent onboarding process is pending. please contact to support");
                    return redirect()->back();
                }
                $response = $this->bankit->generateToken(Auth::user()->cms_agent_id, 'ICICI');
                if ($response['status'] == "success") {
                    return Redirect::to($response['redirectionUrl']);
                } else {
                    Session::put('error', $response['message']);
                    return redirect()->back();
                }
            } elseif ($this->aeps_provider == 3) {
                if (!Auth::user()->cms_agent_id || Auth::user()->iserveu_onboard_status == 0) {
                    Session::put('error', "Agent onboarding process is pending. please contact to support");
                    return redirect()->back();
                }
                $ref_id = Helpers::generateReferenceID();
                $token = Helpers::generateIserveuToken();
                $data['pass_key'] = env('ISU_PASS_KEY');
                $data['token'] = $token;
                $data['api_username'] = env('ISU_API_USERNAME');
                $data['username'] = Auth::user()->cms_agent_id;
                $data['ref_id'] = $ref_id;
                $data['is_receipt'] = 'true';
                $data['callback_url'] = url('iserveu-aeps-callback');
                return view('agent.aeps.iserveu')->with($data);
            } else {
                $banks = Paysprintbank::where('status_id', 1)->OrderBy('bank_name', 'ASC')->get();
                return view('agent.aeps.paysprint.welcome', compact('banks'))->with($data);
            }
        } else {
            return redirect()->back();
        }
    }

    function bankList()
    {
        $masterbank = Paysprintbank::where('status_id', 1)->OrderBy('bank_name', 'ASC')->get();
        $response = array();
        foreach ($masterbank as $value) {
            $product = array();
            $product["bank_id"] = $value->iinno;
            $product["bank_name"] = $value->bank_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'bank_list' => $response]);
    }

    function aeps_initiate_transaction(Request $request)
    {
        $provider_id = 331;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'mobile_number' => 'required|digits:10',
                'aadhar_number' => 'required|digits:12',
                'bank_id' => 'required',
                'BiometricData' => 'required',
                'amount' => '' . ($request->transaction_type == 'CW' || $request->transaction_type == 'APW') ? 'required|regex:/^\d+(\.\d{1,2})?$/' : 'nullable' . '',
                'transaction_type' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $aadhar_number = $request->aadhar_number;
            $bank_id = $request->bank_id;
            $BiometricData = $request->BiometricData;
            $mobile_number = $request->mobile_number;
            $user_id = Auth::id();
            $amount = $request->amount;
            $mode = "WEB";
            $client_id = "";
            $transaction_type = $request->transaction_type;
            $merchant_code = Auth::User()->paysprint_merchantcode;
            $bank_pipe = $this->pipe;
            $MerAuthTxnId = $request->MerAuthTxnId;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            if ($transaction_type == 'BE') {
                // balance enquiry
                return Self::balance_enquiry_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude);
            } elseif ($transaction_type == 'CW') {
                // cash withdrawal
                return Self::cash_withdrawal_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $MerAuthTxnId, $latitude, $longitude);
            } elseif ($transaction_type == 'MS') {
                // mini statement
                return Self::mini_statement_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude);
            } elseif ($transaction_type == 'APW') {
                // aadhaar pay
                return Self::aadhar_pay_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude);
            }
        } else {
            return redirect()->back();
        }
    }

    function initiate_transaction_app(Request $request)
    {
        $provider_id = 331;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'mobile_number' => 'required|digits:10',
                'aadhar_number' => 'required|digits:12',
                'bank_id' => 'required',
                'BiometricData' => 'required',
                'amount' => '' . ($request->transaction_type == 'CW' || $request->transaction_type == 'APW') ? 'required|regex:/^\d+(\.\d{1,2})?$/' : 'nullable' . '',
                'transaction_type' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $aadhar_number = $request->aadhar_number;
            $bank_id = $request->bank_id;
            $BiometricData = $request->BiometricData;
            $mobile_number = $request->mobile_number;
            $user_id = Auth::id();
            $amount = $request->amount;
            $mode = "APP";
            $client_id = "";
            $transaction_type = $request->transaction_type;
            $merchant_code = Auth::User()->paysprint_merchantcode;
            $bank_pipe = $this->pipe;
            $MerAuthTxnId = $request->MerAuthTxnId;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            if ($transaction_type == 'BE') {
                // balance enquiry
                return Self::balance_enquiry_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude);
            } elseif ($transaction_type == 'CW') {
                // cash withdrawal
                return Self::cash_withdrawal_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $MerAuthTxnId, $latitude, $longitude);
            } elseif ($transaction_type == 'MS') {
                // mini statement
                return Self::mini_statement_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude);
            } elseif ($transaction_type == 'APW') {
                // aadhaar pay
                return Self::aadhar_pay_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction type']);
            }
        } else {
            return redirect()->back();
        }
    }

    function initiate_transaction_api(Request $request)
    {
        $provider_id = 331;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'mobile_number' => 'required|digits:10',
                'aadhar_number' => 'required|digits:12',
                'bank_id' => 'required',
                'BiometricData' => 'required',
                'amount' => '' . ($request->transaction_type == 'CW' || $request->transaction_type == 'APW') ? 'required|regex:/^\d+(\.\d{1,2})?$/' : 'nullable' . '',
                'transaction_type' => 'required',
                'merchant_code' => 'required|exists:paysprintaepsagents,merchantcode',
                'client_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $aadhar_number = $request->aadhar_number;
            $bank_id = $request->bank_id;
            $BiometricData = $request->BiometricData;
            $mobile_number = $request->mobile_number;
            $user_id = Auth::id();
            $amount = $request->amount;
            $mode = $request->mode;
            $client_id = $request->client_id;
            $transaction_type = $request->transaction_type;
            $merchant_code = $request->merchant_code;
            $bank_pipe = $this->pipe;
            $MerAuthTxnId = $request->MerAuthTxnId;
            if ($transaction_type == 'BE') {
                // balance enquiry
                return Self::balance_enquiry_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe);
            } elseif ($transaction_type == 'CW') {
                // cash withdrawal
                return Self::cash_withdrawal_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $MerAuthTxnId);
            } elseif ($transaction_type == 'MS') {
                // mini statement
                return Self::mini_statement_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe);
            } elseif ($transaction_type == 'APW') {
                // aadhaar pay
                return Self::aadhar_pay_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe);
            }
        } else {
            return redirect()->back();
        }
    }

    function balance_enquiry_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
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
            'client_id' => $client_id,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => $retailer,
            'mode' => ($userdetails->role_id == 10) ? 'API' : $mode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'provider_api_from' => 1
        ]);
        $masterbanks = Paysprintbank::where('iinno', $bank_id)->first();
        $maskedAadhaar = str_repeat('X', 8) . substr($aadhar_number, -4);
        Aepsreport::insertGetId([
            'aadhar_number' => $maskedAadhaar,
            'bank_name' => $masterbanks->bank_name,
            'created_at' => $ctime,
            'report_id' => $insert_id,
        ]);

        // Base array for common data
        $commonData = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'mobilenumber' => $mobile_number,
            'referenceno' => $insert_id,
            'ipaddress' => $request_ip,
            'adhaarnumber' => $aadhar_number,
            'accessmodetype' => $mode == "APP" ? 'APP' : 'SITE',
            'nationalbankidentification' => $bank_id,
            'requestremarks' => 'balance enquiry',
            'pipe' => $bank_pipe,
            'timestamp' => $ctime,
            'transcationtype' => 'BE',
            'submerchantid' => $merchant_code,
            'is_iris' => false
        ];
        // $datapost_store with specific 'data' value
        $datapost_store = array_merge($commonData, ['data' => '']);
        // $datapost with specific 'data' value (BiometricData)
        $datapost = array_merge($commonData, ['data' => $BiometricData]);
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        $url = $this->base_url . "api/v1/service/aeps/balanceenquiry/index";
        $method = 'POST';
        $token = Self::generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            //"Authorisedkey: $this->authorised_key"
        );
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($datapost_store), 'response_type' => 'BE']);
        if ($response) {
            $res = json_decode($response);
            if ($res->status == true || $res->response_code == 1) {
                if ($res->response_code == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $res->bankrrn]);
                    $bankDetails = Paysprintbank::where('iinno', $res->bankiin)->first();
                    $reports = Report::where('id', $insert_id)->first();
                    $receipt_anchor = url('agent/transaction-receipt') . '/' . Crypt::encrypt($reports->id);
                    $details = array(
                        'bank_name' => $bankDetails->bank_name,
                        'amount' => number_format($res->amount, 2),
                        'total_balance' => number_format($res->balanceamount, 2),
                        'utr' => $res->bankrrn,
                        'aadhar_number' => $maskedAadhaar,
                        'shop_name' => $userdetails->member->shop_name,
                        'shop_address' => $userdetails->member->office_address,
                        'receipt_anchor' => $receipt_anchor
                    );
                    return Response()->json([
                        'status' => 'success',
                        'message' => $res->message,
                        'details' => $details,
                    ]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'server not responding']);
        }
    }

    function cash_withdrawal_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $MerAuthTxnId, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $scheme_id = $userdetails->scheme_id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 319;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name $mobile_number";

        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, 1);
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
            'client_id' => $client_id,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $mode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'tds' => $tds,
            'provider_api_from' => 1
        ]);
        $maskedAadhaar = str_repeat('X', 8) . substr($aadhar_number, -4);
        $masterbanks = Paysprintbank::where('iinno', $bank_id)->first();
        Aepsreport::insertGetId([
            'aadhar_number' => $maskedAadhaar,
            'bank_name' => $masterbanks->bank_name,
            'created_at' => $ctime,
            'report_id' => $insert_id,
        ]);
        // Base array for common data
        $commonData = [
            "latitude" => $latitude,
            "longitude" => $longitude,
            "mobilenumber" => $mobile_number,
            "referenceno" => $insert_id,
            "ipaddress" => $request_ip,
            "adhaarnumber" => $aadhar_number,
            "accessmodetype" => $mode == "APP" ? 'APP' : 'SITE',
            "nationalbankidentification" => $bank_id,
            "requestremarks" => $description,
            "pipe" => $bank_pipe,
            "timestamp" => $ctime,
            "transactiontype" => "CW",
            "submerchantid" => $merchant_code,
            "amount" => $amount,
            "is_iris" => false,
            "MerAuthTxnId" => $MerAuthTxnId
        ];

        // $datapost_store with specific 'data' value as an empty string
        $datapost_store = array_merge($commonData, ["data" => '']);
        // $datapost with specific 'data' value as $BiometricData
        $datapost = array_merge($commonData, ["data" => $BiometricData]);
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        //$url = $this->base_url . "api/v1/service/aeps/cashwithdraw/index"; // old url
        $url = $this->base_url . "api/v1/service/aeps/authcashwithdraw/index";
        $token = Self::generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            // "Authorisedkey: $this->authorised_key"
        );
        $method = 'POST';
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($datapost_store), 'response_type' => 'CW']);
        if ($response) {
            $res = json_decode($response);
            if ($res->status == true || $res->response_code == 1) {
                if ($res->response_code == 1) {
                    $increment_amount = ($amount + $retailer) - $tds;
                    Balance::where('user_id', $user_id)->increment('aeps_balance', $increment_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->aeps_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 6, 'profit' => $retailer, 'txnid' => $res->bankrrn, 'total_balance' => $user_balance]);
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
                    $reports = Report::where('id', $insert_id)->first();
                    $receipt_anchor = url('agent/transaction-receipt') . '/' . Crypt::encrypt($reports->id);
                    $bankDetails = Paysprintbank::where('iinno', $res->bankiin)->first();
                    $balanceamount = $res->balanceamount;
                    if ($balanceamount) {
                        $balanceamount = number_format($res->balanceamount, 2);
                    }

                    // get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $distributor, $sdistributor, $sales_team, $referral, $apiCommission, $insert_id, $commissionType);


                    $details = array(
                        'bank_name' => $bankDetails->bank_name,
                        'amount' => number_format($res->amount, 2),
                        'total_balance' => $balanceamount,
                        'utr' => $res->bankrrn,
                        'aadhar_number' => $maskedAadhaar,
                        'shop_name' => $userdetails->member->shop_name,
                        'shop_address' => $userdetails->member->office_address,
                        'receipt_anchor' => $receipt_anchor
                    );
                    return Response()->json([
                        'status' => 'success',
                        'message' => $res->message,
                        'details' => $details,
                    ]);
                } else {
                    Report::where('id', $insert_id)->update(['txnid' => $res->message]);
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                Report::where('id', $insert_id)->update(['txnid' => $res->message]);
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'server not responding']);
        }
    }

    public function mini_statement_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 320;
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
            'status_id' => 2,
            'client_id' => $client_id,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $mode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'provider_api_from' => 1
        ]);
        $masterbanks = Paysprintbank::where('iinno', $bank_id)->first();
        $maskedAadhaar = str_repeat('X', 8) . substr($aadhar_number, -4);
        Aepsreport::insertGetId([
            'aadhar_number' => $maskedAadhaar,
            'bank_name' => $masterbanks->bank_name,
            'created_at' => $ctime,
            'report_id' => $insert_id,
        ]);
        // Base array for common data
        $commonData = [
            "latitude" => $latitude,
            "longitude" => $longitude,
            "mobilenumber" => $mobile_number,
            "referenceno" => $insert_id,
            "ipaddress" => $request_ip,
            "adhaarnumber" => $aadhar_number,
            "accessmodetype" => $mode == "APP" ? 'APP' : 'SITE',
            "nationalbankidentification" => $bank_id,
            "requestremarks" => $description,
            "pipe" => $bank_pipe,
            "timestamp" => $ctime,
            "transactiontype" => "MS",
            "submerchantid" => $merchant_code,
            "is_iris" => false
        ];
        // $datapost_store with 'data' as an empty string
        $datapost_store = array_merge($commonData, ["data" => '']);

        // $datapost with 'data' set to $BiometricData
        $datapost = array_merge($commonData, ["data" => $BiometricData]);
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        $url = $this->base_url . "api/v1/service/aeps/ministatement/index";
        $token = Self::generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            //"Authorisedkey: $this->authorised_key"
        );
        $method = 'POST';
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($datapost_store), 'response_type' => 'MS']);
        if ($response) {
            $res = json_decode($response);
            if ($res->status == true || $res->response_code == 1) {
                if ($res->response_code == 1) {
                    $increment_amount = $retailer - $tds;
                    Balance::where('user_id', $user_id)->increment('aeps_balance', $increment_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->aeps_balance;
                    //Report::where('id', $insert_id)->update(['status_id' => 6, 'profit' => $retailer, 'txnid' => $res->bankrrn, 'total_balance' => $user_balance]);
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'profit' => $retailer, 'txnid' => $res->bankrrn, 'total_balance' => $user_balance, 'tds' => $tds]);

                    $reports = Report::where('id', $insert_id)->first();
                    $receipt_anchor = url('agent/transaction-receipt') . '/' . Crypt::encrypt($reports->id);
                    $bankDetails = Paysprintbank::where('iinno', $res->bankiin)->first();
                    $balanceamount = $res->balanceamount;
                    if ($balanceamount) {
                        $balanceamount = number_format($res->balanceamount, 2);
                    }
                    $details = array(
                        'bank_name' => $res->bankiin,
                        // 'amount' => number_format($res->amount, 2),
                        'total_balance' => $balanceamount,
                        'utr' => $res->bankrrn,
                        'aadhar_number' => $maskedAadhaar,
                        'shop_name' => $userdetails->member->shop_name,
                        'shop_address' => $userdetails->member->office_address,
                        'receipt_anchor' => $receipt_anchor
                    );
                    $ministatement = $res->ministatement;
                    $response = array();
                    $i = 1;
                    foreach ($ministatement as $value) {
                        $product = array();
                        $product["sr_no"] = $i++;
                        $product["date"] = $value->date;
                        $product["txnType"] = $value->txnType;
                        $product["amount"] = $value->amount;
                        $product["narration"] = $value->narration;
                        array_push($response, $product);
                    }

                    return Response()->json([
                        'status' => 'success',
                        'message' => $res->message,
                        'details' => $details,
                        'ministatement' => $res->ministatement
                    ]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'server not responding']);
        }
    }

    public function aadhar_pay_middle($bank_id, $mobile_number, $aadhar_number, $BiometricData, $amount, $user_id, $client_id, $mode, $merchant_code, $bank_pipe, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $scheme_id = $userdetails->scheme_id;
        $company_id = $userdetails->company_id;
        $opening_balance = $userdetails->balance->aeps_balance;
        $request_ip = request()->ip();
        $provider_id = 321;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $description = "$providers->provider_name $mobile_number";

        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, 1);
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
            'client_id' => $client_id,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'profit' => 0,
            'mode' => $mode,
            'ip_address' => $request_ip,
            'description' => $description,
            'opening_balance' => $opening_balance,
            'total_balance' => $opening_balance,
            'wallet_type' => 2,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'provider_api_from' => 1
        ]);
        $masterbanks = Paysprintbank::where('iinno', $bank_id)->first();
        $maskedAadhaar = str_repeat('X', 8) . substr($aadhar_number, -4);
        Aepsreport::insertGetId([
            'aadhar_number' => $maskedAadhaar,
            'bank_name' => $masterbanks->bank_name,
            'created_at' => $ctime,
            'report_id' => $insert_id,
        ]);

        /*   $currentUserInfo = Location::get($request_ip);
        $latitude = $currentUserInfo->latitude;
        $longitude = $currentUserInfo->longitude;*/
        // Base array for common data
        $commonData = [
            "latitude" => $latitude,
            "longitude" => $longitude,
            "mobilenumber" => $mobile_number,
            "referenceno" => $insert_id,
            "ipaddress" => $request_ip,
            "adhaarnumber" => $aadhar_number,
            "accessmodetype" => $mode == "APP" ? 'APP' : 'SITE',
            "nationalbankidentification" => $bank_id,
            "requestremarks" => $description,
            "pipe" => $bank_pipe,
            "timestamp" => $ctime,
            "transactionType" => "M",
            "submerchantid" => $merchant_code,
            "amount" => $amount,
            "is_iris" => false
        ];

        // $datapost_store with 'data' as an empty string
        $datapost_store = array_merge($commonData, ["data" => '']);

        // $datapost with 'data' set to $BiometricData
        $datapost = array_merge($commonData, ["data" => $BiometricData]);
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        $url = $this->base_url . "api/v1/service/aadharpay/aadharpay/index";
        $token = $this->generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            // "Authorisedkey: $this->authorised_key"
        );
        $method = 'POST';
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($datapost_store), 'response_type' => 'M']);
        if ($response) {
            $res = json_decode($response);
            if ($res->status == true || $res->response_code == 1) {
                if ($res->response_code == 1) {
                    $gst = ($retailer * 18) / 100;
                    $totalChargeAmount = $retailer + $gst;
                    $sum_amount = $amount - $totalChargeAmount;
                    Balance::where('user_id', $user_id)->increment('aeps_balance', $sum_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $total_balance = $balance->aeps_balance;
                    Report::where('id', $insert_id)->update([
                        'status_id' => 1,
                        'txnid' => $res->bankrrn,
                        'profit' => '-' . $retailer,
                        'total_balance' => $total_balance,
                        'gst' => $gst,
                    ]);
                    $reports = Report::where('id', $insert_id)->first();
                    $receipt_anchor = url('agent/transaction-receipt') . '/' . Crypt::encrypt($reports->id);
                    $bankDetails = Paysprintbank::where('iinno', $res->bankiin)->first();
                    $details = array(
                        'bank_name' => $bankDetails->bank_name,
                        'amount' => number_format($res->amount, 2),
                        'total_balance' => number_format($res->balanceamount, 2),
                        'utr' => $res->bankrrn,
                        'aadhar_number' => $maskedAadhaar,
                        'shop_name' => $userdetails->member->shop_name,
                        'shop_address' => $userdetails->member->office_address,
                        'receipt_anchor' => $receipt_anchor
                    );
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
                    // get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $distributor, $sdistributor, $sales_team, $referral, $apiCommission, $insert_id, $commissionType);

                    return Response()->json([
                        'status' => 'success',
                        'message' => $res->message,
                        'details' => $details,
                    ]);
                } else {
                    Report::where('id', $insert_id)->update(['txnid' => $res->message]);
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                Report::where('id', $insert_id)->update(['txnid' => $res->message]);
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'server not responding']);
        }
    }

    public function withdrawal_check_status()
    {
        $datapost = array(
            "reference" => "848",
        );
        $key = $this->key;
        $iv = $this->iv;

        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        $url = $this->base_url . "api/v1/service/aeps/aepsquery/query";
        $token = $this->generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            //"Authorisedkey: $this->authorised_key"
        );
        $method = 'POST';
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($datapost), 'response_type' => 'withdrawal_check_status']);
        return $response;
    }

    public function aadhar_pay_check_status()
    {
        $datapost = array(
            "reference" => "847",
        );
        $key = $this->key;
        $iv = $this->iv;

        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        $url = $this->base_url . "api/v1/service/aadharpay/aadharpayquery/query";
        $token = $this->generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            //"Authorisedkey: $this->authorised_key"
        );
        $method = 'POST';
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($datapost), 'response_type' => 'aadhar_pay_check_status']);
        return $response;
    }

    public function generateToken()
    {
        $Jwtheader = $this->jwt_header;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $reqid = rand(100000, 999999);
        $timestamp = strtotime($ctime);
        $payload = '{
            "timestamp": "' . $timestamp . '",
            "partnerId": "' . $this->partner_id . '",
            "reqid": "' . $reqid . '"
        }';
        $apikey = $this->api_key;
        $library = new PaysprintApicredentials();
        $Jwt = $library->encode($Jwtheader, $payload, $apikey);
        return $Jwt;
    }

    static function paysprint_curl($url, $header, $api_request_parameters, $method)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $api_request_parameters,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function return_url()
    {
        return redirect('agent/aeps/v2/agent-onboarding');
    }

    function twoFactorAuthentication()
    {
        $data = array(
            'page_title' => 'Two Factor Authentication',
        );
        return view('agent.aeps.paysprint.twoFactorAuthentication')->with($data);
    }

    function twoFactorAuthenticationWeb(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required',
            'aadhar_number' => 'required',
            'pipe' => 'required',
            'BiometricData' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $aadhar_number = $request->aadhar_number;
        $BiometricData = $request->BiometricData;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $accessmodetype = "SITE";
        $bank_pipe = $request->pipe;
        return Self::twoFactorAuthenticationMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe);
    }

    function twoFactorAuthenticationApp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required',
            'aadhar_number' => 'required',
            'BiometricData' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $aadhar_number = $request->aadhar_number;
        $BiometricData = $request->BiometricData;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $accessmodetype = "APP";
        $bank_pipe = $this->pipe;
        return Self::twoFactorAuthenticationMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe);
    }

    function twoFactorAuthenticationMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe)
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $userdetails = User::find($user_id);
        $datapost = array(
            'accessmodetype' => $accessmodetype,
            'adhaarnumber' => $aadhar_number,
            'mobilenumber' => $mobile_number,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'referenceno' => mt_rand(1000000, 9999999),
            'submerchantid' => $userdetails->paysprint_merchantcode,
            'timestamp' => $ctime,
            'data' => $BiometricData,
            'ipaddress' => request()->ip(),
        );
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        if ($bank_pipe == 'bank3') {
            $url = $this->base_url . "api/v1/service/aeps/kyc/Twofactorkyc/auth_login"; //UAT
            //$url = "https://api.paysprint.in/api/v1/service/aeps/kyc/Twofactorkyc/auth_login";
        } else {
            $url = $this->base_url . "api/v1/service/aeps/kyc/Twofactorkyc/authentication"; //UAT
            //$url = "https://api.paysprint.in/api/v1/service/aeps/kyc/Twofactorkyc/authentication";
        }
        $method = 'POST';
        $token = Self::generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            // "Authorisedkey: $this->authorised_key"
        );
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        $request_message = $url . '?' . json_encode($datapost);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'twoFactorAuthentication']);
        $res = json_decode($response);
        if ($res) {
            if ($res->status == true && $res->response_code == 1) {
                return Response()->json(['status' => 'success', 'message' => $res->message]);
            } else {
                $message = $res->message;
                if ($message == 'Merchant two-factor registration is pending.') {
                    return Self::twoFactorAuthenticationRegister($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe);
                }
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => "No response received, please try again or later."]);
        }
    }

    function twoFactorAuthenticationRegister($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe)
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $userdetails = User::find($user_id);
        $datapost = array(
            'accessmodetype' => $accessmodetype,
            'adhaarnumber' => $aadhar_number,
            'mobilenumber' => $mobile_number,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'referenceno' => mt_rand(1000000, 9999999),
            'submerchantid' => $userdetails->paysprint_merchantcode,
            'timestamp' => $ctime,
            'data' => $BiometricData,
            'ipaddress' => request()->ip(),
        );
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        if ($bank_pipe == 'bank3') {
            $url = $this->base_url . "api/v1/service/aeps/kyc/Twofactorkyc/register_agent"; //UAT
            //$url = "https://api.paysprint.in/api/v1/service/aeps/kyc/Twofactorkyc/register_agent";
        } else {
            $url = $this->base_url . "api/v1/service/aeps/kyc/Twofactorkyc/registration"; //UAT
            //$url = "https://api.paysprint.in/api/v1/service/aeps/kyc/Twofactorkyc/registration";
        }
        $method = 'POST';
        $token = Self::generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            //"Authorisedkey: $this->authorised_key"
        );
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        $request_message = $url . '?' . json_encode($datapost);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'twoFactorAuthenticationRegistration']);
        $res = json_decode($response);
        if ($res->status == true && $res->response_code == 1) {
            return Self::twoFactorAuthenticationMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }

    function merchantAuthInitiateWeb(Request $request)
    {
        $rules = array(
            'BiometricData' => 'required',
            'aadhar_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = Auth::User()->mobile;
        $aadhar_number = $request->aadhar_number;
        $BiometricData = $request->BiometricData;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $accessmodetype = "SITE";
        $bank_pipe = $this->pipe;
        return Self::merchantAuthInitiateMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe);
    }

    function merchantAuthInitiateApp(Request $request)
    {
        $rules = array(
            'BiometricData' => 'required',
            'aadhar_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = Auth::User()->mobile;
        $aadhar_number = $request->aadhar_number;
        $BiometricData = $request->BiometricData;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $accessmodetype = "APP";
        $bank_pipe = $this->pipe;
        return Self::merchantAuthInitiateMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe);
    }

    function merchantAuthInitiateMiddle($mobile_number, $aadhar_number, $BiometricData, $user_id, $accessmodetype, $latitude, $longitude, $bank_pipe)
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $userdetails = User::find($user_id);
        $datapost = array(
            'accessmodetype' => $accessmodetype,
            'adhaarnumber' => $aadhar_number,
            'mobilenumber' => $mobile_number,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'referenceno' => mt_rand(1000000, 9999999),
            'submerchantid' => $userdetails->paysprint_merchantcode,
            'timestamp' => $ctime,
            'data' => $BiometricData,
            'ipaddress' => request()->ip(),
        );
        $key = $this->key;
        $iv = $this->iv;
        $ciphertext_raw = openssl_encrypt(json_encode($datapost, true), 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        $api_request_parameters = '{"body":"' . $request1 . '"}';
        if ($bank_pipe == 'bank3') {
            $url = "https://api.paysprint.in/api/v1/service/aeps/kyc/Twofactorkyc/agent_authencity";
            //$url = "https://sit.paysprint.in/service-api/api/v1/service/aeps/kyc/Twofactorkyc/agent_authencity"; //UAT
        } else {
            $url = "https://api.paysprint.in/api/v1/service/aeps/kyc/Twofactorkyc/merchant_authencity";
            //$url = "https://sit.paysprint.in/service-api/api/v1/service/aeps/kyc/Twofactorkyc/merchant_authencity"; //UAT
        }
        $method = 'POST';
        $token = Self::generateToken();
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token",
            //"Authorisedkey: $this->authorised_key"
        );
        $response = Self::paysprint_curl($url, $header, $api_request_parameters, $method);
        $request_message = $url . '?' . json_encode($datapost);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'merchantAuthInitiate']);
        $res = json_decode($response);
        if ($res->status == true && $res->response_code == 1) {
            return Response()->json(['status' => 'success', 'message' => $res->message, 'MerAuthTxnId' => $res->MerAuthTxnId]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }

    function iserveuAepsCallback(Request $request)
    {
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 3]);
    }
}
