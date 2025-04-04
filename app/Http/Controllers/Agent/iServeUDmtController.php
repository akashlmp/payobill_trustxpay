<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\IServeU\Dmt as IServeUDmt;
use App\Library\BasicLibrary;
use App\Library\Commission_increment;
use App\Library\DmtLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\LocationRestrictionsLibrary;
use App\Models\Accountvalidate;
use App\Models\Api;
use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Beneficiary;
use App\Models\Company;
use App\Models\Masterbank;
use App\Models\Mreport;
use App\Models\Provider;
use App\Models\Report;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Validator;
use DB;
use Hash;
use Helpers;

class iServeUDmtController extends Controller
{
    public $usernameDMT;

    public function __construct()
    {
        $this->mode = env('iServeUDmt_MODE', 'LIVE');
        $this->vendor_id = 2;
        $this->money_provider_id = 316;
        $this->verification_provider_id = 315;

        $apis = Api::where('vender_id', $this->vendor_id)->first();
        $this->api_id = $apis->id;

        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->company_dmt_provider = 3;// $companies->dmt_provider;
        $this->paySprint = 1;
        $this->bankIt = 2;
        $this->iServerU = 3;
        $provider_commission_type = 1;

        if ($this->company_dmt_provider == $this->iServerU) {
            $provider_commission_type = 3;
            $this->api_id = 3;
        }
        $this->provider_commission_type = $provider_commission_type;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $usernameDMT = 'upitestret';
            if ($this->mode == 'LIVE') {
                $usernameDMT = Auth::user()->cms_agent_id;
            }
            $this->usernameDMT = $usernameDMT;
            return $next($request);
        });
    }

    function bank_list_iServeU()
    {
        $masterbank = Masterbank::where('status_id', 1)->select('id', 'bank_name', 'ifsc')->get();
        $response = array();
        foreach ($masterbank as $value) {
            $product = array();
            $product["bank_id"] = $value->id;
            $product["bank_name"] = $value->bank_name;
            $product["ifsc_code"] = $value->ifsc;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'successful..!', 'bank_list' => $response]);
    }

    function index()
    {
        $data = array('page_title' => 'Money Transfer');
        $page = 'iServeU.iServeU_route_one';
        $banks = Masterbank::where('status_id', 1)->select('id', 'bank_name')->get();
        return view('agent.dmt.' . $page, compact('banks'))->with($data);
    }

    function getAllBeneficiaryBankIt(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $library = new IServeUDmt();
        return $library->getAllBeneficiary($mobile_number);
    }

    function getCustomer(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $longitude = $request->longitude;
        $latitude = $request->latitude;
        if ($this->vendor_id == 2) {
            $library = new iServeUDmt();
            return $library->getCustomer($mobile_number, $latitude, $longitude, $this->usernameDMT);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function accountVerify(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'beneficiary_name' => 'required',
            'address' => 'required',
            'pincode' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'otp' => 'required',
            'bank_id' => 'required'
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
        $beneName = $request->beneficiary_name;
        $address = $request->address;
        $pincode = $request->pincode;
        $otp = $request->otp;
        $externalRefNumber = $request->externalRefNumber;
        $user_id = Auth::id();
        $client_id = '';
        $mode = 'WEB';
        if (isset($request->mode)) {
            $mode = $request->mode;
        }
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode, $beneName, $address, $pincode, $otp, $externalRefNumber);
    }

    function accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode, $beneName, $address, $pincode, $otp, $externalRefNumber)
    {
        $request_ip = request()->ip();
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->verification_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        $bank = Masterbank::where('id', $bank_id)->first();
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $accountvalidates = Accountvalidate::where('account_number', $account_number)
                ->where('ifsc_code', $ifsc_code)
                ->where('api_id', $this->api_id)
                ->first();

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
                $response = ['status_id' => 2, 'message' => 'Accounts Data not fetch from system', 'name' => ''];
                if ($this->company_dmt_provider == $this->iServerU && !empty($accountvalidates)) {
                    $response = ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $accountvalidates->beneficiary_name];
                }
                $isApiCharge = false;
                if ($this->vendor_id == 2) {
                    if ($this->company_dmt_provider == $this->iServerU && empty($accountvalidates)) {
                        $banks = Masterbank::where('id', $bank_id)->first();
                        $usersData = Auth::user();
                        $library = new IServeUDmt();
                        $data = [
                            "externalRefNumber" => $externalRefNumber,
                            "accountNumber" => $account_number,
                            'beneBankName' => $bank->bank_name,
                            'beneMobileNumber' => $mobile_number,
                            'beneName' => $beneName,
                            'bankCode' => $banks->bank_code,
                            'beneIfscCode' => $ifsc_code,
                            'transactionMode' => 'IMPS',
                            'customerName' => $usersData->name,
                            'customerMobileNumber' => $usersData->mobile,
                            'pincode' => $pincode,
                            'address' => $address,
                            'username' => $this->usernameDMT,
                            'latLong' => $latitude . ',' . $longitude,
                            'customerIP' => request()->ip(),
                            'otp' => $otp,
                            'isHoldTransaction' => false
                        ];
                        $response = $library->accountVerify($insert_id, $this->api_id, json_encode($data));
                        $isApiCharge = true;
                    } elseif ($this->company_dmt_provider == $this->iServerU && !empty($accountvalidates)) {
                        $response = ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $accountvalidates->beneficiary_name];
                    }
                } else if (empty($accountvalidates)) {
                    Log::info("empty accountvalidates");
                    Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
                }
                $status_id = $response['status_id'];
                Log::info("accountVerify Controller =>" . $status_id);
                Log::info($response);
                if ($status_id == 1) {
                    $name = $response['name'];
                    $insertDataId = '';
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $name]);
                    if (empty($accountvalidates)) {
                        $insertDataId = Accountvalidate::insertGetId([
                            'account_number' => $account_number,
                            'ifsc_code' => $ifsc_code,
                            'beneficiary_name' => $name,
                            'created_at' => $ctime,
                            'status_id' => 1,
                            'api_id' => $this->api_id,
                        ]);
                    } else {
                        $insertDataId = $accountvalidates->id;
                    }

                    $beneficiary = Beneficiary::where('account_number', $account_number)->where('api_id', $this->api_id)->first();
                    $data = array(
                        'benficiary_id' => $insertDataId,
                        'account_number' => $account_number,
                        'ifsc' => $ifsc_code,
                        'bank_name' => $bank->bank_name,
                        'name' => $name,
                        'remiter_number' => $mobile_number,
                        'remiter_name' => $this->usernameDMT,
                        'status_id' => 1,
                        'api_id' => $this->api_id,
                        'address' => $address,
                        'pincode' => $pincode,
                    );
                    if ($beneficiary) {
                        $beneficiary_id = $beneficiary->id;
                        Beneficiary::where('id', $beneficiary_id)->update($data);
                    } else {
                        Beneficiary::insert($data);
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
        if ($this->vendor_id == 2) {
            $library = new IServeUDmt();
            return $library->getIfscCode($bank_id);
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
        if ($this->vendor_id == 2) {
            Beneficiary::where('benficiary_id', $beneficiary_id)
                ->where('remiter_number', $mobile_number)
                ->where('api_id', $this->api_id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Deleted successfuly']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function viewAccountTransfer(Request $request)
    {

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
            /* 'ovdData' => 'required',*/
            'is_transfer_otp' => 'required'
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
        //$ovdData = $request->ovdData;
        $user_id = Auth::id();
        $mode = 'WEB';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        $payment_mode = ($channel_id == 2) ? 'IMPS' : 'NEFT';
        if ($request->is_transfer_otp == 1) {
            $library = new IServeUDmt();
            $latLong = $latitude . "," . $longitude;
            $externalRefNumber = 'PAYO' . Helpers::generateRandomNumber(8);
            return $library->senderSendOtp(Auth::user()->mobile, 2, $latLong, $this->usernameDMT, '', $externalRefNumber, $amount);
        } else {
            $otp = $request->transfer_otp;
            $externalRefNumber = $request->externalRefNumber;
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
            return self::transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode, $otp, $externalRefNumber);
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
            'is_transfer_otp' => 'required'
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
        $ovdData = $request->ovdData;

        $user_id = Auth::id();
        $mode = 'APP';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }

        if (Auth::User()->role_id == 10) {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry you cannot access this URL']);
        }
        if ($request->is_transfer_otp == 1) {
            $library = new IServeUDmt();
            $latLong = $latitude . "," . $longitude;
            $externalRefNumber = 'PAYO' . Helpers::generateRandomNumber(8);
            return $library->senderSendOtp(Auth::user()->mobile, 2, $latLong, $this->usernameDMT, $ovdData, $externalRefNumber, $amount);
        } else {
            $externalRefNumber = $request->externalRefNumber;
            $otp = $request->transfer_otp;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            return self::transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode, $otp, $externalRefNumber);
        }
    }

    function transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode, $otp, $externalRefNumber)
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
                /*$splitAmount = new DmtLibrary();
                $partsAmount = $splitAmount->splitAmount($amount, $provider_id);
                foreach ($partsAmount as $amounts) {
                    sleep(5);
                    self::trnNew($mobile_number, $user_id, $beneficiary_id, $amounts, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude, $otp);
                }*/
                sleep(5);
                self::trnNew($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude, $otp, $externalRefNumber);
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

    function trnNew($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude, $otp, $externalRefNumber)
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
                'gst' => $calculate['gst'],
                'reference_id' => $externalRefNumber
            ]);
            $response = self::callApi($amount, $insert_id, $mobile_number, $channel_id, $api_id, $latitude, $longitude, $beneficiaryId, $otp, $externalRefNumber);

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
                    return ['status' => 'failure', 'message' => $message, 'utr' => '', 'payid' => $insert_id];
                } else {
                    Report::where('id', $insert_id)->update(['payid' => $payid, 'txnid' => $txnid]);
                    return ['status' => 'pending', 'message' => 'Transaction process!', 'utr' => '', 'payid' => $insert_id];
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong in transaction.']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Your Balance Is Low Please Refill Your Wallet']);
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

    function callApi($amount, $insert_id, $mobile_number, $channel_id, $api_id, $latitude, $longitude, $beneficiaryId, $otp, $externalRefNumber)
    {
        try {
            if ($this->vendor_id == 2) {
                if ($this->company_dmt_provider == $this->iServerU) {
                    $library = new IServeUDmt();
                    $usersData = Auth::user();
                    $beneficiaryData = Beneficiary::where('id', $beneficiaryId)->first();
                    $banks = Masterbank::where('bank_name', $beneficiaryData->bank_name)->first();
                    $txntype = ($channel_id == 2) ? 'IMPS' : 'NEFT';
                    $parameters = [
                        "externalRefNumber" => $externalRefNumber,
                        "requestedAmount" => (int)$amount,
                        "accountNumber" => $beneficiaryData->account_number,
                        "beneBankName" => $beneficiaryData->bank_name,
                        "beneMobileNumber" => $mobile_number,
                        "beneName" => $beneficiaryData->name,
                        "bankCode" => $banks->bank_code,
                        "beneIfscCode" => $beneficiaryData->ifsc,
                        "transactionMode" => $txntype,
                        'customerName' => $usersData->name,
                        'customerMobileNumber' => $usersData->mobile,
                        "pincode" => $beneficiaryData->pincode,
                        "address" => $beneficiaryData->address,
                        "isHoldTransaction" => false,
                        "username" => $this->usernameDMT,
                        "latLong" => $latitude . ',' . $longitude,
                        "customerIP" => request()->ip(),
                        "otp" => $otp
                    ];
                    Log::info(json_encode($parameters));
                    return $library->transferNow($insert_id, $channel_id, $api_id, json_encode($parameters));
                }
            } else {
                return ['status_id' => 2, 'txnid' => '', 'payid' => '', 'message' => ''];
            }
        } catch (\Exception $exception) {
            return ['status_id' => 2, 'txnid' => '', 'payid' => '', 'message' => $exception->getMessage()];
        }

    }

    function confirmSender(Request $request)
    {
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'pincode' => 'required',
            'state' => 'required',
            'address' => 'required',
            'mobile_number' => 'required|digits:10',
            'is_otp_send' => 'required',
            'ovdData' => 'required'
        );
        if ($request->is_otp_send == 2) {
            $rules['otp'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $pincode = $request->pincode;
        $address = $request->address;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $ovdData = $request->ovdData;
        $ovdType = 'Aadhaar Card';
        if ($this->vendor_id == 2) {
            $library = new IServeUDmt();
            $latlong = $latitude . ',' . $longitude;
            if ($request->is_otp_send == 1) {
                return $library->senderSendOtp($mobile_number, 1, $latlong, $this->usernameDMT, $ovdData);
            } else {
                $parameters = '{
                    "mobileNumber": "' . $mobile_number . '",
                    "name": "' . $first_name . ' ' . $last_name . '",
                    "address":"' . $address . '",
                    "pincode":"' . $pincode . '",
                    "ovdType":"' . $ovdType . '",
                    "ovdData":"' . $ovdData . '",
                    "otp":"' . $otp . '",
                    "username":"' . $this->usernameDMT . '",
                    "latlong":"' . $latlong . '",
                    "publicIP":"' . $request->ip() . '"}';
                return $library->processCustomer($parameters);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function resendOtp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $ovdData = $request->ovdData;

        if ($this->vendor_id == 2) {
            $library = new IServeUDmt();
            $latlong = $latitude . ',' . $longitude;
            return $library->senderSendOtp($mobile_number, 1, $latlong, $this->usernameDMT, $ovdData);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function sendOtp(Request $request)
    {
        $mobile_number = Auth::user()->mobile;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $externalRefNumber = 'PAYO' . Helpers::generateRandomNumber(8);
        if ($this->vendor_id == 2) {
            $library = new IServeUDmt();
            $latlong = $latitude . ',' . $longitude;
            return $library->senderSendOtp($mobile_number, 2, $latlong, $this->usernameDMT, '', $externalRefNumber);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }
}
