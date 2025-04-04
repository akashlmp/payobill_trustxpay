<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use App\Library\SmsLibrary;
use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Loadcash;
use App\Models\MerchantTransactions;
use App\Models\MerchantUsers;
use App\Models\Paymentmethod;
use App\Models\Report;
use App\Models\User;
use App\Models\WhiteListBank;
use Helpers;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;
use Illuminate\Http\Request;

class AxisController extends Controller
{
    public function __construct()
    {
        $this->secretKey = '';
        $this->secretKeyEasyPay = '';
        $this->provider_id = 326;
    }

    public function asValidate(Request $request)
    {
        $rules = array(
            'UTR' => 'required',
            'Bene_acc_no' => 'required',
            'Req_type' => 'required',
            'Req_date' => 'required',
            'Txn_amnt' => 'required',
            'Sndr_acnt' => 'required',
            'check_sum_token' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['Stts_flg' => 'F', "Err_cd" => "000", 'message' => $validator->messages()->first()], 422);
        }
        try {
            $utr = $request->UTR;
            $beneAccNo = $request->Bene_acc_no;
            $sndrAcnt = $request->Sndr_acnt;
            $checkSumKey = $request->check_sum_token;
            $data = $utr . $beneAccNo;
            // Generate encrypt for the data
            $actualAESString = Helpers::decryptAES($checkSumKey, $this->secretKey);
            Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 1, 'request_message' => 'asValidate', 'response_type' => 'asValidate']);
            if ($data == $actualAESString) {
                $checkExist = User::where('account_number', $beneAccNo)->first();
                $checkExistMerchant = MerchantUsers::where('account_number', $beneAccNo)->first();
                $cleanedNumber = ltrim($sndrAcnt, '0');
                $checkTransaction = Loadcash::where('bankref', $request->Tran_id)->first();
                $checkUTR = Loadcash::where('utr', $request->UTR)->first();
                if (!empty($checkExist)) {
                    $checkBankExist = WhiteListBank::where(DB::raw('CAST(account_number AS UNSIGNED)'), $cleanedNumber)->where('status', 1)->where('type', 1)->first();
                    if (!empty($checkBankExist)) {
                        return response()->json(["Stts_flg" => "S", "Err_cd" => "000", "message" => "Success"], 200);
                    } else {
                        return response()->json(["Stts_flg" => "F", "Err_cd" => "003", "message" => "Sender Account number not white listed in system."], 404);
                    }
                } else if (!empty($checkExistMerchant)) {
                    $checkBankExistMerchant = WhiteListBank::where(DB::raw('CAST(account_number AS UNSIGNED)'), $cleanedNumber)->where('status', 1)->where('type', 2)->first();
                    if (!empty($checkBankExistMerchant)) {
                        return response()->json(["Stts_flg" => "S", "Err_cd" => "000", "message" => "Success"], 200);
                    } else {
                        return response()->json(["Stts_flg" => "F", "Err_cd" => "003", "message" => "Sender Account number not white listed in system."], 404);
                    }
                } else {
                    return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "In-valid VAN number"], 422);
                }
            } else {
                return response()->json(["Stts_flg" => "F", "Err_cd" => "001", "message" => "Authentication failed.In-valid checksum value"], 500);
            }
        } catch (\Exception $exception) {
            Log::info('asValidation Error:=>');
            Log::info($exception);
            return response()->json(['Stts_flg' => 'F', "Err_cd" => "001", "message" => $exception->getMessage()], 500);
        }
    }

    public function asTransaction(Request $request)
    {
        $rules = array(
            'UTR' => 'required',
            'Bene_acc_no' => 'required',
            'Req_type' => 'required',
            'Req_date' => 'required',
            'Txn_amnt' => 'required',
            'Sndr_acnt' => 'required',
            'check_sum_token' => 'required',
            'Pmode' => 'required',
            'Sndr_nm' => 'required',
            'Sndr_ifsc' => 'required',
            'Corp_code' => 'required',
            'Tran_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['Stts_flg' => 'F', 'message' => $validator->messages()->first()]);
        }
        //dd(Helpers::encryptAES('2806202PAYO00000000001M',$this->secretKey));
        try {
            Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 1, 'request_message' => 'asTransaction', 'response_type' => 'asTransaction']);
            $checkSumKey = $request->check_sum_token;
            $data = $request->UTR . $request->Bene_acc_no;
            // Generate encrypt for the data
            $actualAESString = Helpers::decryptAES($checkSumKey, $this->secretKey);
            if ($data == $actualAESString) {
                //Check $beneAccNo
                $checkExistUsers = User::where('account_number', $request->Bene_acc_no)->first();
                $checkExistMerchants = MerchantUsers::where('account_number', $request->Bene_acc_no)->first();
                $cleanedNumber = ltrim($request->Sndr_acnt, '0');
                if (!empty($checkExistUsers)) {
                    $checkBankExist = WhiteListBank::where(DB::raw('CAST(account_number AS UNSIGNED)'), $cleanedNumber)->where(['status' => 1, 'type' => 1])->first();
                    //$checkTransaction = Loadcash::where('bankref', $request->Tran_id)->first();
                    $checkUTR = Loadcash::where('utr', $request->UTR)->where('merchant_id', 0)->first();
                    if (!empty($checkBankExist)) {
                        if (empty($checkUTR)) {
                            $methods = Paymentmethod::where('status_id', 1)->whereRaw('payment_type LIKE "%' . $request->Pmode . '%"')->first();
                            $methodsID = '2'; //DEfault NEFT id
                            if (!empty($methods)) {
                                $methodsID = $methods->id;
                            }

                            $now = new \DateTime();
                            $ctime = $now->format('Y-m-d H:i:s');
                            $amount = $request->Txn_amnt;
                            $bankref = $request->Tran_id . '_' . time();
                            Loadcash::insertGetId([
                                'user_id' => $checkExistUsers->id,
                                'payment_date' => $request->Req_date,
                                'paymentmethod_id' => $methodsID,
                                'bankdetail_id' => 1,
                                'amount' => $request->Txn_amnt,
                                'bankref' => $bankref,
                                'parent_id' => 1,
                                'created_at' => $ctime,
                                'payment_type' => 1,
                                'ip_address' => request()->ip(),
                                'status_id' => 1,
                                'utr' => $request->UTR,
                                'added_from' => 1,
                                'api_json' => Json::encode($request->all()),
                            ]);
                            // user balance
                            $child_opening_balance = $checkExistUsers->balance->user_balance;

                            Balance::where('user_id', $checkExistUsers->id)->increment('user_balance', $request->Txn_amnt);
                            Balance::where('user_id', $checkExistUsers->id)->update(['balance_alert' => 1]);
                            $childbalance = Balance::where('user_id', $checkExistUsers->id)->first();
                            $child_balance = $childbalance->user_balance;
                            $provider_id = $this->provider_id;
                            $request_ip = request()->ip();
                            $description = "Top up from Axis bank to  $checkExistUsers->name $checkExistUsers->last_name";

                            $insert_id = Report::insertGetId([
                                'number' => $checkExistUsers->name . ' ' . $checkExistUsers->last_name,
                                'provider_id' => $provider_id,
                                'amount' => $amount,
                                'api_id' => 0,
                                'status_id' => 6,
                                'created_at' => $ctime,
                                'user_id' => $checkExistUsers->id,
                                'profit' => 0,
                                'mode' => "WEB",
                                'txnid' => $bankref,
                                'ip_address' => $request_ip,
                                'description' => $description,
                                'opening_balance' => $child_opening_balance,
                                'total_balance' => $child_balance,
                                'credit_by' => $checkExistUsers->id,
                                'wallet_type' => 1
                            ]);
                            $amount = number_format($amount, 2);
                            $child_balance = number_format($child_balance, 2);
                            $message = "Dear User, Your Wallet is Credited With Amount $amount. Your Current balance is $child_balance. For more info: trustxpay.org PAOBIL";
                            $template_id = 5;
                            $whatsappArr = [$amount, $child_balance];
                            $library = new SmsLibrary();
                            $library->send_sms($checkExistUsers->mobile, $message, $template_id, $whatsappArr);
                            return response()->json(["Stts_flg" => "S", "Err_cd" => "000", "message" => "Success"], 200);
                        } else {
                            return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "Duplicate UTR"], 422);
                        }
                    } else {
                        return response()->json(["Stts_flg" => "F", "Err_cd" => "003", "message" => "Bank Account not white listed in system."], 422);
                    }
                } else if (!empty($checkExistMerchants)) {
                    $checkBankExist = WhiteListBank::where(DB::raw('CAST(account_number AS UNSIGNED)'), $cleanedNumber)->where(['status' => 1, 'type' => 2])->first();
                    //$checkTransaction = Loadcash::where('bankref', $request->Tran_id)->first();
                    $checkUTR = Loadcash::where('utr', $request->UTR)->where('user_id', 0)->first();
                    if (!empty($checkBankExist)) {
                        if (empty($checkUTR)) {
                            $methods = Paymentmethod::where('status_id', 1)->whereRaw('payment_type LIKE "%' . $request->Pmode . '%"')->first();
                            $methodsID = '2'; //DEfault NEFT id
                            if (!empty($methods)) {
                                $methodsID = $methods->id;
                            }

                            $now = new \DateTime();
                            $ctime = $now->format('Y-m-d H:i:s');
                            $amount = $request->Txn_amnt;
                            $bankref = $request->Tran_id . '_' . time();
                            Loadcash::insertGetId([
                                'user_id' => 0,
                                'merchant_id' => $checkExistMerchants->id,
                                'payment_date' => $request->Req_date,
                                'paymentmethod_id' => $methodsID,
                                'bankdetail_id' => 1,
                                'amount' => $request->Txn_amnt,
                                'bankref' => $bankref,
                                'parent_id' => 1,
                                'created_at' => $ctime,
                                'payment_type' => 1,
                                'ip_address' => request()->ip(),
                                'status_id' => 1,
                                'utr' => $request->UTR,
                                'added_from' => 1,
                                'api_json' => Json::encode($request->all()),
                            ]);
                            // MerchantUsers balance
                            $child_opening_balance = $checkExistMerchants->wallet;

                            MerchantUsers::where('id', $checkExistMerchants->id)->increment('wallet', $request->Txn_amnt);
                            $childbalance = MerchantUsers::where('id', $checkExistMerchants->id)->first();
                            $child_balance = $childbalance->wallet;
                            $provider_id = $this->provider_id;
                            $request_ip = request()->ip();
                            $description = "Top up from Axis bank to  $checkExistMerchants->name $checkExistMerchants->last_name";

                            $insert_id = MerchantTransactions::insertGetId([
                                'merchant_id' => $checkExistMerchants->id,
                                'provider_id' => $provider_id,
                                'account_number' => $request->Sndr_acnt,
                                'reference_id' => $bankref,
                                'transaction_id' => $request->Tran_id,
                                'utr' => $request->UTR,
                                'opening_balance' => $child_opening_balance,
                                'profit' => 0,
                                'total_balance' => $child_balance,
                                'amount' => $amount,
                                'status_id' => 6,
                                'created_at' => $ctime,
                                'mode' => "WEB",
                                'ip_address' => $request_ip,
                                'description' => $description
                            ]);
                            $amount = number_format($amount, 2);
                            $child_balance = number_format($child_balance, 2);
                            $message = "Dear User, Your Wallet is Credited With Amount $amount. Your Current balance is $child_balance. For more info: trustxpay.org PAOBIL";
                            $template_id = 5;
                            $whatsappArr = [$amount, $child_balance];
                            $library = new SmsLibrary();
                            $library->send_sms($checkExistMerchants->mobile, $message, $template_id, $whatsappArr);
                            return response()->json(["Stts_flg" => "S", "Err_cd" => "000", "message" => "Success"], 200);
                        } else {
                            return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "Duplicate UTR"], 422);
                        }
                    } else {
                        return response()->json(["Stts_flg" => "F", "Err_cd" => "003", "message" => "Bank Account not white listed in system."], 422);
                    }
                } else {
                    return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "In-valid VAN number"], 422);
                }
            } else {
                return response()->json(["Stts_flg" => "F", "Err_cd" => "001", "message" => "Authentication failed.In-valid checksum value"], 500);
            }

        } catch (\Exception $exception) {
            Log::info('asTransaction Error:=>');
            Log::info($exception);
            return response()->json(['Stts_flg' => 'F', "Err_cd" => "001", "message" => 'Oops something went wrong.'], 500);
        }
    }

    public function cdmAsTransaction(Request $request)
    {
        //dd(Helpers::encryptAES('140123SWRO2035046430001879414',$this->secretKey));
        $rules = array(
            'UTR' => 'required',
            'CDMCardNo' => 'required',
            'Agent_Id' => 'required',
            'Txn_amnt' => 'required',
            'Req_dt_time' => 'required',
            'txn_nmbr' => 'required',
            'Corp_code' => 'required',
            'pmode' => 'required',
            'check_sum_token' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(["txnid" => $request->UTR, "status" => "1", 'message' => $validator->messages()->first()], 422);
        }
        try {
            Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 1, 'request_message' => 'cdmAsTransaction', 'response_type' => 'cdmAsTransaction']);
            $checkSumKey = $request->check_sum_token;
            $data = $request->UTR . $request->CDMCardNo;
            // Generate encrypt for the data
            $actualAESString = Helpers::decryptAES($checkSumKey, $this->secretKey);
            if ($data == $actualAESString) {
                $checkExistUsers = User::where('cdm_card_number', $request->CDMCardNo)->first();
                $checkUTR = Loadcash::where('utr', $request->UTR)->first();
                if (!empty($checkExistUsers)) {
                    if (empty($checkUTR)) {
                        $methods = Paymentmethod::where('payment_type', 'CDM')->first();
                        $methodsID = $methods->id;
                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        $bankref = $request->txn_nmbr . '_' . time();
                        Loadcash::insertGetId([
                            'user_id' => $checkExistUsers->id,
                            'payment_date' => date('Y-m-d', strtotime($request->Req_dt_time)),
                            'paymentmethod_id' => $methodsID,
                            'bankdetail_id' => 1,
                            'amount' => $request->Txn_amnt,
                            'bankref' => $request->txn_nmbr . '_' . time(),
                            'txn_number' => $request->txn_nmbr,
                            'parent_id' => 1,
                            'created_at' => $ctime,
                            'payment_type' => 1,
                            'ip_address' => request()->ip(),
                            'status_id' => 1,
                            'utr' => $request->UTR,
                            'added_from' => 2,
                            'api_json' => Json::encode($request->all()),
                        ]);


                        $provider_id = $this->provider_id;
                        $request_ip = request()->ip();
                        $description = "Transfer axis cdm to $checkExistUsers->name $checkExistUsers->last_name";

                        // child update
                        $child_opening_balance = $checkExistUsers->balance->user_balance;
                        Balance::where('user_id', $checkExistUsers->id)->increment('user_balance', $request->Txn_amnt);
                        Balance::where('user_id', $checkExistUsers->id)->update(['balance_alert' => 1]);
                        $childbalance = Balance::where('user_id', $checkExistUsers->id)->first();
                        $child_balance = $childbalance->user_balance;

                        $insert_id = Report::insertGetId([
                            'number' => $checkExistUsers->name . ' ' . $checkExistUsers->last_name,
                            'provider_id' => $provider_id,
                            'amount' => $request->Txn_amnt,
                            'api_id' => 0,
                            'status_id' => 6,
                            'created_at' => $ctime,
                            'user_id' => $checkExistUsers->id,
                            'profit' => 0,
                            'mode' => "WEB",
                            'txnid' => $bankref,
                            'ip_address' => $request_ip,
                            'description' => $description,
                            'opening_balance' => $child_opening_balance,
                            'total_balance' => $child_balance,
                            'credit_by' => $checkExistUsers->id,
                            'wallet_type' => 1
                        ]);

                        $amount = number_format($request->Txn_amnt, 2);
                        $child_balance = number_format($child_balance, 2);
                        $message = "Dear User, Your Wallet is Credited With Amount $amount. Your Current balance is $child_balance. For more info: trustxpay.org PAOBIL";
                        $template_id = 5;
                        $whatsappArr = [$amount, $child_balance];
                        $library = new SmsLibrary();
                        $library->send_sms($checkExistUsers->mobile, $message, $template_id, $whatsappArr);

                        return response()->json(["txnid" => $request->UTR, "status" => "0", "message" => "Account credited"], 200);
                    } else {
                        return response()->json(["txnid" => $request->UTR, "status" => "1", "message" => "Duplicate UTR."], 422);
                    }
                } else {
                    return response()->json(["txnid" => $request->UTR, "status" => "2", "message" => "In-valid VAN number or Card not assigned."], 422);
                }
            } else {
                return response()->json(["txnid" => $request->UTR, "status" => "4", "message" => "Authentication failed.In-valid checksum value"], 400);
            }

        } catch (\Exception $exception) {
            Log::info('cdmAsTransaction Error:=>');
            Log::info($exception);
            return response()->json(["txnid" => $request->UTR, "status" => "4", "message" => $exception->getMessage()], 500);
        }
    }

    public function easyPayValidate(Request $request)
    {
        $rules = array(
            'agent_id' => 'required',
            'check_sum_token' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['Stts_flg' => 'F', "Err_cd" => "000", 'message' => $validator->messages()->first()], 422);
        }
        try {
            $req_id = $request->Req_id;
            $corpCode = $request->Corp_code;
            $reqDtTime = $request->Req_dt_time;
            $reqDtls = $request->Req_dtls;
            $agent_id = $request->agent_id;
            $checkSumKey = $request->check_sum_token;
            $data = $agent_id;

            Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 1, 'request_message' => 'easypay-validate', 'response_type' => 'easyPayValidate']);
            // Generate encrypt for the data
            $actualAESString = Helpers::decryptAES($checkSumKey, $this->secretKeyEasyPay);
            if ($data == $actualAESString) {
                $checkExist = User::where('cms_agent_id', $agent_id)->first();
                if (!empty($checkExist)) {
                    $data = [
                        'name' => $checkExist->name,
                        'email' => $checkExist->email,
                        'mobile' => $checkExist->mobile,
                        'Req_id' => $req_id,
                        'Corp_code' => $corpCode,
                        'Req_dt_time' => $reqDtTime,
                        'Req_dtls' => $agent_id,
                    ];
                    return response()->json(["Stts_flg" => "S", "Err_cd" => "000", "message" => "Success", 'data' => $data], 200);
                } else {
                    return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "In-valid agent id"], 422);
                }
            } else {
                return response()->json(["Stts_flg" => "F", "Err_cd" => "001", "message" => "Authentication failed.In-valid checksum value"], 500);
            }
        } catch (\Exception $exception) {
            Log::info('easyPayValidate Error:=>');
            Log::info($exception);
            return response()->json(['Stts_flg' => 'F', "Err_cd" => "001", "message" => $exception->getMessage()], 500);
        }
    }

    public function easyPayTransaction(Request $request)
    {
        $rules = array(
            'agent_id' => 'required',
            'Req_dt_time' => 'required',
            'Txn_amnt' => 'required',
            'txn_nmbr' => 'required',
            'check_sum_token' => 'required',
            'pmode' => 'required',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['Stts_flg' => 'F', 'message' => $validator->messages()->first()], 422);
        }
        try {
            Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 1, 'request_message' => 'easypay-transaction', 'response_type' => 'easyPayTransaction']);

            $checkSumKey = $request->check_sum_token;
            $data = $request->agent_id;
            // Generate encrypt for the data
            $actualAESString = Helpers::decryptAES($checkSumKey, $this->secretKeyEasyPay);
            if ($data == $actualAESString) {
                //Check $beneAccNo
                $checkExistUsers = User::where('cms_agent_id', $request->agent_id)->first();
                $checkTransaction = Loadcash::where('bankref', $request->txn_nmbr)->first();
                if (!empty($checkExistUsers)) {
                    if (empty($checkTransaction)) {
                        //Paymode 1=Cash, 2=Transfer, 3=Cheque & 4=CDM
                        $methodsID = '2'; //DEfault NEFT id
                        if ($request->pmode == 1) {
                            $methodsID = 1;
                        } else if ($request->pmode == 2) {
                            $methodsID = 2;
                        } else if ($request->pmode == 3) {
                            $methodsID = 5;
                        } else if ($request->pmode == 4) {
                            $methodsID = 6;
                        }

                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        $bankref = $request->txn_nmbr;
                        Loadcash::insertGetId([
                            'user_id' => $checkExistUsers->id,
                            'payment_date' => $request->Req_dt_time,
                            'paymentmethod_id' => $methodsID,
                            'bankdetail_id' => 1,
                            'amount' => $request->Txn_amnt,
                            'bankref' => $bankref,
                            'txn_number' => $request->txn_nmbr,
                            'parent_id' => 1,
                            'created_at' => $ctime,
                            'payment_type' => 1,
                            'ip_address' => request()->ip(),
                            'status_id' => 1,
                            'utr' => $request->UTR,
                            'api_json' => Json::encode($request->all()),
                            'added_from' => 3
                        ]);

                        $provider_id = $this->provider_id;
                        $request_ip = request()->ip();
                        $description = "Transfer easyPay  to $checkExistUsers->name $checkExistUsers->last_name";

                        // child update
                        $child_opening_balance = $checkExistUsers->balance->user_balance;
                        Balance::where('user_id', $checkExistUsers->id)->increment('user_balance', $request->Txn_amnt);
                        Balance::where('user_id', $checkExistUsers->id)->update(['balance_alert' => 1]);
                        $childbalance = Balance::where('user_id', $checkExistUsers->id)->first();
                        $child_balance = $childbalance->user_balance;

                        $insert_id = Report::insertGetId([
                            'number' => $checkExistUsers->name . ' ' . $checkExistUsers->last_name,
                            'provider_id' => $provider_id,
                            'amount' => $request->Txn_amnt,
                            'api_id' => 0,
                            'status_id' => 6,
                            'created_at' => $ctime,
                            'user_id' => $checkExistUsers->id,
                            'profit' => 0,
                            'mode' => "WEB",
                            'txnid' => $bankref,
                            'ip_address' => $request_ip,
                            'description' => $description,
                            'opening_balance' => $child_opening_balance,
                            'total_balance' => $child_balance,
                            'credit_by' => $checkExistUsers->id,
                            'wallet_type' => 1
                        ]);

                        $amount = number_format($request->Txn_amnt, 2);
                        $child_balance = number_format($child_balance, 2);
                        $message = "Dear User, Your Wallet is Credited With Amount $amount. Your Current balance is $child_balance. For more info: trustxpay.org PAOBIL";
                        $template_id = 5;
                        $whatsappArr = [$amount, $child_balance];
                        $library = new SmsLibrary();
                        $library->send_sms($checkExistUsers->mobile, $message, $template_id, $whatsappArr);

                        $data = [
                            'agent_id' => $request->agent_id,
                            'Sta_cd' => 1,
                            'Corp_code' => ($request->Corp_code) ? $request->Corp_code : '',
                            'Status_desc' => 'Success',
                            'txn_nmbr' => $request->txn_nmbr,
                            'Req_id' => $request->Req_id,
                            'Stts_flg' => 'Y',
                            'txn_id' => $request->Corp_code,
                            'client_id' => $request->Corp_code
                        ];
                        return response()->json(["Stts_flg" => "S", "Err_cd" => "000", "message" => "Success"], 200);
                    } else {
                        return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "Duplicate Transaction id."], 422);
                    }

                } else {
                    return response()->json(["Stts_flg" => "F", "Err_cd" => "002", "message" => "In-valid agent id."], 422);
                }
            } else {
                return response()->json(["Stts_flg" => "F", "Err_cd" => "001", "message" => "Authentication failed.In-valid checksum value"], 500);
            }

        } catch (\Exception $exception) {
            Log::info('easyPayTransaction Error:=>');
            Log::info($exception);
            return response()->json(['Stts_flg' => 'F', "Err_cd" => "001", "message" => $exception->getMessage()], 500);
        }
    }
}
