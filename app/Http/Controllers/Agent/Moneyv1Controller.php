<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Apiresponse;
use App\Models\Bankitdmtbank;
use App\Models\Company;
use App\Paysprint\Apicredentials as PaysprintApicredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Validator;
use DB;
use Hash;
use Helpers;
use App\Models\Api;
use App\Models\Masterbank;
use App\Models\User;
use App\Models\Accountvalidate;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Mreport;
use App\Models\Beneficiary;
use App\Models\Paysprintdmtbank;

// library here
use App\Library\BasicLibrary;
use App\Library\LocationRestrictionsLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\DmtLibrary;

//dmt service
use App\Pay2all\Dmt as Pay2all;
use App\Paysprint\Dmt as PaysprintDmt;
use App\Bankit\Dmt as BankitDmt;
use App\IServeU\Dmt as iServeUDmt;

class Moneyv1Controller extends Controller
{
    public function __construct()
    {
        $this->vendor_id = 2;
        $this->money_provider_id = 316;
        $this->verification_provider_id = 315;

        $apis = Api::where('vender_id', $this->vendor_id)->first();
        $this->api_id = $apis->id;

        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->company_dmt_provider = $companies->dmt_provider;
        $this->paySprint = 1;
        $this->bankIt = 2;
        $this->iServerU = 3;
        $provider_commission_type = 1;
        if ($this->company_dmt_provider == $this->bankIt) {
            $provider_commission_type = 2;
            $this->api_id = 2;
        }
        if ($this->company_dmt_provider == $this->iServerU) {
            $provider_commission_type = 3;
            $this->api_id = 3;
        }
        $this->provider_commission_type = $provider_commission_type;
    }


    function welcome()
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->money_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        $banks = array();
        $data = array('page_title' => 'Money Transfer');
        $page = 'route_one';
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            if ($this->vendor_id == 1) {
                $banks = Masterbank::where('status_id', 1)->select('id', 'bank_name')->get();
            } elseif ($this->vendor_id == 2) {
                if ($this->company_dmt_provider == $this->bankIt) {
                    $page = 'bankit_route_one';
                    $banks = Bankitdmtbank::select('id', 'bank_name')->get();
                } else if ($this->company_dmt_provider == $this->iServerU) {
                    $page = 'iServeU.iServeU_route_one';
                    $banks = Masterbank::where('status_id', 1)->select('id', 'bank_name')->get();
                } else {
                    $banks = Paysprintdmtbank::where('status_id', 1)->select('id', 'bank_name')->get();
                }
            }
            return view('agent.dmt.' . $page, compact('banks'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function getCustomer(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10'
        );
        if ($this->company_dmt_provider == $this->paySprint) {
            $rules['name'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $lat = ($request->latitude) ? $request->latitude : "";
        $long = ($request->longitude) ? $request->longitude : '';
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getCustomer($mobile_number);
        } elseif ($this->vendor_id == 2) {
            if ($this->company_dmt_provider == $this->bankIt) {
                $library = new BankitDmt();
                return $library->getCustomer($mobile_number);
            } else {
                $name = $request->name;
                $library = new PaysprintDmt();
                return $library->getCustomer($mobile_number, $name, $lat, $long);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function addSender(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'otp' => 'required',
            'kyc_id' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $kyc_id = $request->kyc_id;
        $otp = $request->otp;
        $ad1 = $request->ad1;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        if ($this->vendor_id == 2) {
            $library = new PaysprintDmt();
            return $library->confirmSender($mobile_number, $kyc_id, $otp, $ad1, $latitude, $longitude);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function confirmSender(Request $request)
    {
        $rules = array(
            'first_name' => '' . ($request->ad2 == 1) ? 'required' : 'nullable' . '',
            'last_name' => '' . ($request->ad2 == 1) ? 'required' : 'nullable' . '',
            'pincode' => '' . ($request->ad2 == 1) ? 'required' : 'nullable' . '',
            'state' => '' . ($request->ad2 == 1) ? 'required' : 'nullable' . '',
            'address' => '' . ($request->ad2 == 1) ? 'required' : 'nullable' . '',
            'mobile_number' => 'required|digits:10',
            // 'otp' => 'required',
            'aadhaar_number' => 'required|digits:12',
            'BiometricDataPid' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $aadhaar_number = $request->aadhaar_number;
        $pincode = $request->pincode;
        $state = $request->state;
        $address = $request->address;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->confirmSender($mobile_number, $otp, $ad1, $ad2);
        } elseif ($this->vendor_id == 2) {
            $mode = env('DMT_MODE', 'LIVE');
            $libraryCredentials = new PaysprintApicredentials();
            $response = $libraryCredentials->getCredentials($mode);
            Log::info("PID Moneyv1", ['data' => $request->BiometricDataPid]);
            $library = new PaysprintDmt();
            $dataPid = Helpers::encryptPidData($request->BiometricDataPid, $response['key'], $response['iv']);
            // $dataPid = trim($request->BiometricDataPid);
            $dataEKyc = $library->remitterEkyc($mobile_number, $latitude, $longitude, $aadhaar_number, $dataPid);
            if ($dataEKyc['status'] == 'success' && isset($dataEKyc['data']) && !empty($dataEKyc['data'])) {
                $ekycId = $dataEKyc['data']->ekyc_id;
                return Response()->json(['status' => 'success', 'message' => $dataEKyc['message'], 'kyc_id' => $ekycId, 'ad1' => $dataEKyc['data']->stateresp]);
                //return $library->confirmSender($mobile_number, $first_name, $last_name, $pincode, $state, $address, $otp, $ad1, $ad2, $ekycId, $latitude, $longitude);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $dataEKyc['message'], 'ad1' => '', 'ad2' => '']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function senderResendOtp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'first_name' => 'required',
            'last_name' => 'required',
            'pincode' => 'required',
            'state' => 'required',
            'address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $pincode = $request->pincode;
        $state = $request->state;
        $address = $request->address;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->senderResendOtp($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function getAllBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'sender_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $sender_name = $request->sender_name;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getAllBeneficiary($mobile_number, $sender_name);
        } elseif ($this->vendor_id == 2) {
            $library = new PaysprintDmt();
            return $library->getAllBeneficiary($mobile_number, $sender_name);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function searchByAccount(Request $request)
    {
        $rules = array(
            'account_number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $account_number = $request->account_number;
        $beneficiaries = Beneficiary::where('account_number', $account_number)->where('api_id', $this->api_id)->get();
        $response = array();
        foreach ($beneficiaries as $value) {
            $product = array();
            $product["remiter_number"] = $value->remiter_number;
            $product["account_number"] = $value->account_number;
            $product["ifsc"] = $value->ifsc;
            $product["bank_name"] = $value->bank_name;
            $product["name"] = $value->name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'beneficiaries' => $response]);
    }

    function getIfscCode(Request $request)
    {
        $rules = array(
            'bank_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $bank_id = $request->bank_id;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getIfscCode($bank_id);
        } elseif ($this->vendor_id == 2) {
            if ($this->company_dmt_provider == $this->bankIt) {
                $library = new BankitDmt();
            } else {
                $library = new PaysprintDmt();
            }
            return $library->getIfscCode($bank_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function addBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            //'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'beneficiary_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $beneficiary_name = $request->beneficiary_name;
        $verify_status = $request->verify_status;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name);
        } elseif ($this->vendor_id == 2) {
            $library = new PaysprintDmt();
            return $library->addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name, $verify_status);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function confirmBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->confirmBeneficiary($mobile_number, $otp, $ad1, $ad2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }


    function deleteBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->deleteBeneficiary($mobile_number, $beneficiary_id);
        } elseif ($this->vendor_id == 2) {
            $library = new PaysprintDmt();
            return $library->deleteBeneficiary($mobile_number, $beneficiary_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function confirmDeleteBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        $otp = $request->otp;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->confirmDeleteBeneficiary($mobile_number, $ad1, $ad2, $otp);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function accountVerifyWeb(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            // 'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = '';
        $mode = "WEB";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function accountVerifyApp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            // 'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = '';
        $mode = "APP";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode)
    {
        $request_ip = request()->ip();
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->verification_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $accountvalidates = Accountvalidate::where('account_number', $account_number)->where('api_id', $this->api_id)->first();
            /*if (!empty($accountvalidates)) {
                $data = array('beneficiary_name' => $accountvalidates->beneficiary_name);
                return Response()->json(['status' => 'success', 'message' => 'verifyed form our database', 'data' => $data]);
            }*/
            $provider_id = $this->verification_provider_id;
            $amount = 3;
            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount, $this->provider_commission_type);
            $retailer = ($commission['retailer'] <= 1) ? 3 : $commission['retailer'];
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $amount = $retailer;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 1) {
                $providers = Provider::find($provider_id);
                Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $description = "$providers->provider_name  $account_number";
                $wallet_type = 1;
                $insert_id = Report::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'provider_api_from' => $this->provider_commission_type,
                    'amount' => $amount,
                    'api_id' => $this->api_id,
                    'status_id' => 3,
                    'client_id' => $client_id,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'wallet_type' => $wallet_type,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                $response = ['status_id' => 2, 'message' => '', 'name' => ''];
                if ($this->company_dmt_provider == $this->paySprint) {
                    $responseData = Apiresponse::whereRaw("request_message LIKE '%registerbeneficiary/benenameverify%' AND `request_message` LIKE '%\"accno\":\"" . $account_number . "\"%'")->orderBy('id', 'DESC')->first();
                    $existingResponse = ['status_id' => 3, 'message' => '', 'name' => ''];
                    if (!empty($accountvalidates) && !empty($responseData)) {
                        $responseMessage = json_decode($responseData->message);
                        $existingResponse = ['status_id' => 3, 'message' => '', 'name' => ''];
                        if ($responseMessage->status == true && $responseMessage->response_code == 1) {
                            $existingResponse = ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $responseMessage->benename];
                        } elseif ($responseMessage->status == false) {
                            $existingResponse = ['status_id' => 2, 'message' => $responseMessage->message, 'name' => ''];
                        }
                    }
                    $response = $existingResponse;
                } else if ($this->company_dmt_provider == $this->bankIt) {
                    $responseData = Apiresponse::whereRaw("`message` LIKE '%SUCCESS%' AND request_message LIKE '%V1.1/transact/IMPS/accountverification%' AND `request_message` LIKE '%\"udf1\":\"" . $account_number . "\"%'")->orderBy('id', 'DESC')->first();
                    $existingResponse = ['status_id' => 2, 'message' => '', 'name' => ''];
                    if (!empty($responseData) && !empty($accountvalidates)) {
                        $responseMessage = json_decode($responseData->message);
                        if (strtolower($responseMessage->errorMsg) == 'success' && $responseMessage->errorCode == "00") {
                            $existingResponse = ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $responseMessage->data->name];
                        } else {
                            $existingResponse = ['status_id' => 2, 'message' => $responseMessage->errorMsg, 'name' => ''];
                        }
                    }
                    $response = $existingResponse;
                }
                $isApiCharge = false;
                if ($this->vendor_id == 1 && empty($accountvalidates)) {
                    $library = new Pay2all();
                    $response = $library->accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $this->api_id);
                } elseif ($this->vendor_id == 2) {
                    if ($this->company_dmt_provider != $this->bankIt && empty($accountvalidates)) {
                        $library = new PaysprintDmt();
                        $response = $library->accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $this->api_id);
                        $isApiCharge = true;
                    } else if ($this->company_dmt_provider == $this->bankIt && empty($accountvalidates)) {
                        $clientRefId = '10240' . Helpers::generateRandomNumber(15);
                        $library = new BankitDmt();
                        $response = $library->accountVerify($mobile_number, $clientRefId, $insert_id, $this->api_id, $account_number, $ifsc_code);
                        $isApiCharge = true;
                    }
                } else if (empty($accountvalidates)) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
                }
                $status_id = $response['status_id'];
                Log::info("accountVerify Controller =>" . $status_id);
                if ($status_id == 1) {
                    $name = $response['name'];
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $name]);
                    if (empty($accountvalidates)) {
                        Accountvalidate::insertGetId([
                            'account_number' => $account_number,
                            'ifsc_code' => $ifsc_code,
                            'beneficiary_name' => $name,
                            'created_at' => $ctime,
                            'status_id' => 1,
                            'api_id' => $this->api_id,
                        ]);
                    }
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $this->api_id, $amount, $d, $sd, $st, $rf);
                    //get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    if (!$isApiCharge) {
                        $commissionType = 'commission_admin';
                        $apiCommission = 0;
                    }
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                    $data = array('beneficiary_name' => $name);
                    return Response()->json(['status' => 'success', 'message' => 'Verified form vendor database', 'data' => $data]);
                } elseif ($status_id == 2) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'failure_reason' => $response['message'], 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => $response['message']]);
                } else {
                    $data = array('beneficiary_name' => '');
                    return Response()->json(['status' => 'pending', 'message' => 'Transaction in process', 'data' => $data]);
                }

            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund.']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }

    function viewAccountTransfer(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'beneficiary_name' => 'required',
            'bank_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        //$max = (empty($providers)) ? 25000 : $providers->max_amount;
        $max = (empty($providers)) ? 5000 : $providers->max_amount;
        $placeholder = "Enter Amount: Min:$min and Max:$max";
        $data = array(
            'mobile_number' => $request->mobile_number,
            'beneficiary_id' => $request->beneficiary_id,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'beneficiary_name' => $request->beneficiary_name,
            'bank_name' => $request->bank_name,
            'placeholder' => $placeholder,
        );
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
    }

    function transferNowWeb(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        //$max = (empty($providers)) ? 25000 : $providers->max_amount;
        $max = (empty($providers)) ? 5000 : $providers->max_amount;
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'channel_id' => 'required',
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'latitude' => 'required',
            'longitude' => 'required',
            'is_transfer_otp' => 'required',
        );
        if ($request->is_transfer_otp == 2) {
            $rules['transfer_otp'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        $account_number = $request->account_number;
        $ifsc_code = $request->ifsc_code;
        $channel_id = $request->channel_id;
        $amount = str_replace(',','',$request->amount);
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'WEB';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        $payment_mode = ($channel_id == 2) ? 'IMPS' : 'NEFT';
        if ($request->is_transfer_otp == 1) {
            $library = new PaysprintDmt();
            $reqPass = [
                'mobile' => $mobile_number,
                'txntype' => $payment_mode,
                'amount' => $amount,
                'referenceid' => date('d') . time(),
                'bene_id' => $beneficiary_id,
                'pincode' => '110015',
                'address' => 'New Delhi',
                'dob' => '01-01-1994',
                'gst_state' => '1221',
                'lat' => $latitude,
                'long' => $latitude,
            ];
            return $library->transferSendOtp($reqPass);
        } else {
            $otp = $request->transfer_otp;
            DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
            if (Auth::User()->role_id == 10) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry you cannot access this URL']);
            }
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            return Self::transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode, $otp);
        }
    }

    function transferNowApp(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        //$max = (empty($providers)) ? 25000 : $providers->max_amount;
        $max = (empty($providers)) ? 5000 : $providers->max_amount;
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'channel_id' => 'required',
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'latitude' => 'required',
            'longitude' => 'required',
            'is_transfer_otp' => 'required',
        );
        if ($request->is_transfer_otp == 2) {
            $rules['transfer_otp'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        $account_number = $request->account_number;
        $ifsc_code = $request->ifsc_code;
        $channel_id = $request->channel_id;
        $amount = str_replace(',','',$request->amount);
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'APP';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        $payment_mode = ($channel_id == 2) ? 'IMPS' : 'NEFT';
        if ($request->is_transfer_otp == 1) {
            $library = new PaysprintDmt();
            $reqPass = [
                'mobile' => $mobile_number,
                'txntype' => $payment_mode,
                'amount' => $amount,
                'referenceid' => date('d') . time(),
                'bene_id' => $beneficiary_id,
                'pincode' => '110015',
                'address' => 'New Delhi',
                'dob' => '01-01-1994',
                'gst_state' => '1221',
                'lat' => $latitude,
                'long' => $latitude,
            ];
            return $library->transferSendOtp($reqPass);
        } else {
            $otp = $request->transfer_otp;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            return Self::transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode, $otp);
        }
    }

    function transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode, $otp = '')
    {
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->money_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $retailer = 5;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount + $retailer;
            if ($opening_balance >= $sumamount && $sumamount >= 10) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $provider_id = $this->money_provider_id;
                $mreport_id = Mreport::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $this->api_id,
                    'status_id' => 3,
                    'ip_address' => request()->ip(),
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'customer_number' => $mobile_number,
                    'channel' => $channel_id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'total_balance' => $opening_balance,
                ]);
                //$full_amount = number_format($amount, 2);
                /*$splitAmount = new DmtLibrary();
                $partsAmount = $splitAmount->splitAmount($amount, $provider_id);
                foreach ($partsAmount as $amounts) {
                    sleep(5);
                    Self::trnnew($mobile_number, $user_id, $beneficiary_id, $amounts, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude, $otp);
                }*/
                sleep(5);
                Self::trnnew($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude, $otp);
                $payment_mode = ($channel_id == 2) ? 'IMPS' : 'NEFT';
                $beneficiary = Beneficiary::where('account_number', $account_number)->where('benficiary_id', $beneficiary_id)->first();
                $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
                $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;
                $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
                $beneficiary_name = (empty($beneficiary)) ? '' : $beneficiary->name;
                $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
                $remiter_name = (empty($beneficiary)) ? '' : $beneficiary->remiter_name;
                $url = url('agent/money-receipt/' . $mreport_id);
                $thermal_print = url('agent/thermal-printer-receipt/' . $mreport_id);
                $benedetails = array(
                    'account_number' => $account_number,
                    'ifsc' => $ifsc,
                    'bank_name' => $bank_name,
                    'beneficiary_name' => $beneficiary_name,
                    'remiter_number' => $remiter_number,
                    'remiter_name' => $remiter_name,
                    'payment_mode' => $payment_mode,
                    'full_amount' => $amount,
                    'created_at' => $ctime,
                    'print_url' => $url,
                    'thermal_print' => $thermal_print,
                );
                $reports = Self::getReceipt($mreport_id);
                return Response()->json([
                    'status' => 'success',
                    'benedetails' => $benedetails,
                    'reports' => $reports,
                    'is_send_otp' => false,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund.']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }


    function trnnew($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude, $otp = '')
    {
        $userdetails = User::find($user_id);
        $opening_balance = $userdetails->balance->user_balance;
        $scheme_id = $userdetails->scheme_id;
        $provider_id = $this->money_provider_id;
        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, $this->provider_commission_type);
        $retailer = $commission['retailer'];
        $d = $commission['distributor'];
        $sd = $commission['sdistributor'];
        $st = $commission['sales_team'];
        $rf = $commission['referral'];
        $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount + $retailer;
        if ($opening_balance >= $sumamount && $sumamount >= 10) {

            $dmtLibrary = new DmtLibrary();
            $calculate = $dmtLibrary->calculateChargesAndCommission($amount, $retailer);
            $gst = $calculate['gst'];
            $tds = $calculate['tds'];
            $commission = $calculate['commission'];
            $decrementAmount = $amount + $retailer + $gst + $tds;
            Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
            $balance = Balance::where('user_id', $user_id)->first();
            $user_balance = $balance->user_balance;
            $beneficiary = Beneficiary::where('account_number', $account_number)->where('benficiary_id', $beneficiary_id)->where('api_id', $this->api_id)->first();
            $beneficiaryId = (empty($beneficiary)) ? '' : $beneficiary->id;
            $providers = Provider::find($provider_id);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $description = "$providers->provider_name  $account_number";
            $api_id = $this->api_id;
            $row_data = [
                'charges' => $calculate['charges'],
                'gst' => $calculate['gst'],
                'tds' => $calculate['tds'],
                'commission' => $calculate['commission'],
                'netCommission' => $calculate['netCommission'],
                'customer_charge' => $calculate['customer_charge'],
            ];
            $insert_id = Report::insertGetId([
                'number' => $account_number,
                'provider_id' => $provider_id,
                'provider_api_from' => $this->provider_commission_type,
                'amount' => $amount,
                'api_id' => $api_id,
                'status_id' => 3,
                'created_at' => $ctime,
                'user_id' => $user_id,
                'profit' => $commission,
                'mode' => $mode,
                'ip_address' => request()->ip(),
                'description' => $description,
                'opening_balance' => $opening_balance,
                'total_balance' => $user_balance,
                'wallet_type' => 1,
                'mreportid' => $mreport_id,
                'channel' => $channel_id,
                'beneficiary_id' => $beneficiaryId,
                'decrementAmount' => $decrementAmount,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'row_data' => json_encode($row_data),
                'tds' => $calculate['tds'],
                'gst' => $calculate['gst']
            ]);
            $response = Self::callApi($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude, $otp);
            $status_id = $response['status_id'];
            $txnid = $response['txnid'];
            $payid = $response['payid'];
            $message = $response['message'];
            if ($status_id == 1) {
                Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid, 'payid' => $payid]);
                $library = new Commission_increment();
                $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                // get wise commission
                $library = new GetcommissionLibrary();
                $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                $apiCommission = $apiComms['apiCommission'];
                $commissionType = $apiComms['commissionType'];
                $library = new Commission_increment();
                $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                return ['status' => 'success', 'message' => 'Transaction success!', 'utr' => $txnid, 'payid' => $insert_id];
            } elseif ($status_id == 2) {
                Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                Report::where('id', $insert_id)->update(['status_id' => 2, 'failure_reason' => $message, 'txnid' => $txnid, 'payid' => $payid, 'total_balance' => $user_balance, 'profit' => 0]);
                return ['status' => 'failure', 'message' => 'Transaction failed!', 'utr' => '', 'payid' => $insert_id];
            } else {
                Report::where('id', $insert_id)->update(['payid' => $payid, 'txnid' => $txnid,]);
                return ['status' => 'pending', 'message' => 'Transaction process!', 'utr' => '', 'payid' => $insert_id];
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Your Balance Is Low Please Refill Your Wallet']);
        }
    }

    function callApi($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude, $otp = '')
    {
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->transferNow($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude);
        } elseif ($this->vendor_id == 2) {
            if ($this->company_dmt_provider == $this->bankIt) {
                $library = new BankitDmt();
                return $library->transferNow((int)$amount, $beneficiary_id, $insert_id, $mobile_number, $channel_id, $api_id);
            } else {
                $library = new PaysprintDmt();
                return $library->transferNow((int)$amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude, $otp);
            }
        } else {
            return ['status_id' => 2, 'txnid' => '', 'payid' => '', 'message' => ''];
        }
    }


    function getReceipt($mreport_id)
    {
        $reports = Report::where('mreportid', $mreport_id)->get();
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["report_id"] = $value->id;
            $product["utr_number"] = $value->txnid;
            $product["amount"] = number_format($value->amount, 2);
            $product["charges"] = env('CHARGE_PERCENTAGE', 1.2) . '%';
            $product["status"] = $value->status->status;
            $product["failure_reason"] = $value->failure_reason;
            array_push($response, $product);
        }
        return $response;
    }


    function getTransactionCharges(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        //$max = (empty($providers)) ? 25000 : $providers->max_amount;
        $max = (empty($providers)) ? 5000 : $providers->max_amount;
        $rules = array(
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $amount = str_replace(',','',$request->amount);
        $user_id = Auth::id();
        $provider_id = $this->money_provider_id;
        $library = new DmtLibrary();
        return $library->getTransactionCharges($user_id, $amount, $provider_id, $this->provider_commission_type);
    }

    function bank_list()
    {
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getBankList();
        } elseif ($this->vendor_id == 2) {
            $library = new PaysprintDmt();
            return $library->getBankList();
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function addBankItSender(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'first_name' => 'required',
            'last_name' => 'required',
            'dateOfBirth' => 'required',
            'address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $dateOfBirth = $request->dateOfBirth;
        $address = $request->address;
        $otp = $request->otp;

        if ($this->vendor_id == 2) {
            $library = new BankitDmt();
            return $library->addSender($mobile_number, $first_name, $last_name, $address, $dateOfBirth, $otp);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function senderResendOtpBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;

        if ($this->vendor_id == 2) {
            $library = new BankitDmt();
            return $library->senderResendOtp($mobile_number);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function addBeneficiaryBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            //'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'beneficiary_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $beneficiary_name = $request->beneficiary_name;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name);
        } elseif ($this->vendor_id == 2) {
            $library = new BankitDmt();
            return $library->addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function getAllBeneficiaryBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'sender_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $sender_name = $request->sender_name;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getAllBeneficiary($mobile_number, $sender_name);
        } elseif ($this->vendor_id == 2) {
            $library = new BankitDmt();
            return $library->getAllBeneficiary($mobile_number, $sender_name);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function accountVerifyBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = '';
        $mode = "WEB";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function deleteBeneficiaryBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        if ($this->vendor_id == 2) {
            $library = new BankitDmt();
            return $library->deleteBeneficiary($mobile_number, $beneficiary_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function viewAccountTransferBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'beneficiary_name' => 'required',
            'bank_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        //$max = (empty($providers)) ? 25000 : $providers->max_amount;
        $max = (empty($providers)) ? 5000 : $providers->max_amount;
        $placeholder = "Enter Amount: Min:$min and Max:$max";
        $data = array(
            'mobile_number' => $request->mobile_number,
            'beneficiary_id' => $request->beneficiary_id,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'beneficiary_name' => $request->beneficiary_name,
            'bank_name' => $request->bank_name,
            'placeholder' => $placeholder,
        );
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
    }

    function transferNowWebBankIt(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        //$max = (empty($providers)) ? 25000 : $providers->max_amount;
        $max = (empty($providers)) ? 5000 : $providers->max_amount;
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'channel_id' => 'required',
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        $account_number = $request->account_number;
        $ifsc_code = $request->ifsc_code;
        $channel_id = $request->channel_id;
        $amount = str_replace(',','',$request->amount);
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'WEB';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        if (Auth::User()->role_id == 10) {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry you cannot access this URL']);
        }
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return self::transferNowMiddleBankIt($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode);
    }

    function transferNowMiddleBankIt($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode)
    {
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->money_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $retailer = 5;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount + $retailer;
            if ($opening_balance >= $sumamount && $sumamount >= 10) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $provider_id = $this->money_provider_id;
                $mreport_id = Mreport::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $this->api_id,
                    'status_id' => 3,
                    'ip_address' => request()->ip(),
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'customer_number' => $mobile_number,
                    'channel' => $channel_id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'total_balance' => $opening_balance,
                ]);
                //$full_amount = number_format($amount, 2);
                /*$splitAmount = new DmtLibrary();
                $partsAmount = $splitAmount->splitAmount($amount, $provider_id);
                foreach ($partsAmount as $amounts) {
                    sleep(5);
                    self::trnNewBankIt($mobile_number, $user_id, $beneficiary_id, $amounts, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude);
                }*/
                sleep(5);
                self::trnNewBankIt($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude);
                $payment_mode = ($channel_id == 2) ? 'IMPS' : 'NEFT';
                $beneficiary = Beneficiary::where('account_number', $account_number)->where('benficiary_id', $beneficiary_id)->first();
                $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
                $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;
                $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
                $beneficiary_name = (empty($beneficiary)) ? '' : $beneficiary->name;
                $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
                $remiter_name = (empty($beneficiary)) ? '' : $beneficiary->remiter_name;
                $url = url('agent/money-receipt/' . $mreport_id);
                $thermal_print = url('agent/thermal-printer-receipt/' . $mreport_id);
                $benedetails = array(
                    'account_number' => $account_number,
                    'ifsc' => $ifsc,
                    'bank_name' => $bank_name,
                    'beneficiary_name' => $beneficiary_name,
                    'remiter_number' => $remiter_number,
                    'remiter_name' => $remiter_name,
                    'payment_mode' => $payment_mode,
                    'full_amount' => $amount,
                    'created_at' => $ctime,
                    'print_url' => $url,
                    'thermal_print' => $thermal_print,
                );
                $reports = self::getReceipt($mreport_id);
                return Response()->json([
                    'status' => 'success',
                    'benedetails' => $benedetails,
                    'reports' => $reports,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund.']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }

    function trnNewBankIt($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $opening_balance = $userdetails->balance->user_balance;
        $scheme_id = $userdetails->scheme_id;
        $provider_id = $this->money_provider_id;
        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount, $this->provider_commission_type);
        $retailer = $commission['retailer'];
        $d = $commission['distributor'];
        $sd = $commission['sdistributor'];
        $st = $commission['sales_team'];
        $rf = $commission['referral'];
        $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount + $retailer;
        if ($opening_balance >= $sumamount && $sumamount >= 10) {

            $dmtLibrary = new DmtLibrary();
            $calculate = $dmtLibrary->calculateChargesAndCommission($amount, $retailer);
            $gst = $calculate['gst'];
            $tds = $calculate['tds'];
            $commission = $calculate['commission'];
            $decrementAmount = $amount + $retailer + $gst + $tds;
            Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
            $balance = Balance::where('user_id', $user_id)->first();
            $user_balance = $balance->user_balance;
            $beneficiary = Beneficiary::where('account_number', $account_number)->where('benficiary_id', $beneficiary_id)->where('api_id', $this->api_id)->first();
            $beneficiaryId = (empty($beneficiary)) ? '' : $beneficiary->id;
            $providers = Provider::find($provider_id);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $description = "$providers->provider_name  $account_number";
            $api_id = $this->api_id;
            $row_data = [
                'charges' => $calculate['charges'],
                'gst' => $calculate['gst'],
                'tds' => $calculate['tds'],
                'commission' => $calculate['commission'],
                'netCommission' => $calculate['netCommission'],
                'customer_charge' => $calculate['customer_charge'],
            ];
            $insert_id = Report::insertGetId([
                'number' => $account_number,
                'provider_id' => $provider_id,
                'provider_api_from' => $this->provider_commission_type,
                'amount' => $amount,
                'api_id' => $api_id,
                'status_id' => 3,
                'created_at' => $ctime,
                'user_id' => $user_id,
                'profit' => $commission,
                'mode' => $mode,
                'ip_address' => request()->ip(),
                'description' => $description,
                'opening_balance' => $opening_balance,
                'total_balance' => $user_balance,
                'wallet_type' => 1,
                'mreportid' => $mreport_id,
                'channel' => $channel_id,
                'beneficiary_id' => $beneficiaryId,
                'decrementAmount' => $decrementAmount,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'row_data' => json_encode($row_data),
                'tds' => $calculate['tds'],
                'gst' => $calculate['gst']
            ]);
            $response = self::callApi($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude);
            if ($response && isset($response['status_id'])) {
                $status_id = $response['status_id'];
                $txnid = $response['txnid'];
                $payid = $response['payid'];
                $message = $response['message'];
                if ($status_id == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid, 'payid' => $payid]);
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                    // get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                    return ['status' => 'success', 'message' => 'Transaction success!', 'utr' => $txnid, 'payid' => $insert_id];
                } elseif ($status_id == 2) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'failure_reason' => $message, 'txnid' => $txnid, 'payid' => $payid, 'total_balance' => $user_balance, 'profit' => 0]);
                    return ['status' => 'failure', 'message' => 'Transaction failed!', 'utr' => '', 'payid' => $insert_id];
                } else {
                    Report::where('id', $insert_id)->update(['payid' => $payid, 'txnid' => $txnid,]);
                    return ['status' => 'pending', 'message' => 'Transaction process!', 'utr' => '', 'payid' => $insert_id];
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong in transaction.']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Your Balance Is Low Please Refill Your Wallet']);
        }
    }
}
