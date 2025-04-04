<?php

namespace App\Bankit {

    use App\library\Commission_increment;
    use App\library\GetcommissionLibrary;
    use App\Models\Balance;
    use App\Models\Report;
    use App\Models\User;
    use Helpers;
    use App\Models\Masterbank;
    use App\Models\Beneficiary;
    use App\Models\Apiresponse;
    use App\Models\Bankitremitter;
    use App\Models\Bankitdmtbank;
    use App\Bankit\Apicredentials as BankitApicredentials;
    use Illuminate\Support\Facades\Log;

    class Dmt
    {

        public function __construct()
        {
            $mode = env('BANKIT_MODE', 'LIVE');
            // $mode = 'UAT';
            $this->dmt_mode = $mode;
            $library = new BankitApicredentials();
            $response = $library->getCredentials($mode);
            $this->base_url = $response['base_url'];
            $this->api_id = $response['api_id'];
            $this->agentCode = $response['agentCode'];
            $this->agentAuthId = $response['AgentAuthId'];
            $this->agentAuthPassword = $response['AgentAuthPassword'];
            $this->pincode = "201301";
            $this->dob = "1995-12-16";
            $this->gst_state = "09";
            $this->address = "Noida";
            $this->pipe = 'bank1';
            $this->header = array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->agentAuthId . ":" . $this->agentAuthPassword)
            );
        }


        function getCustomer($mobile_number)
        {
            $url = $this->base_url . '/customer/v1/fetch';
            $parameters = '{"customerId":"' . $mobile_number . '","agentCode":"' . $this->agentCode . '"}';
            $response = self::sendCurlPost($url, $parameters);
            // $response = '{"errorMsg":"SUCCESS","errorCode":"00","data":{"customerId":"7777777777","name":"Fintech","kycstatus":0,"monthlyLimit":25000,"walletbal":25000,"dateOfBirth":"1998-02-10"},"pipeList":[{"remainingLimit":10000.0,"pipeCode":"P2","_notAllowedBankCode":"FINO","channelAllowed":"ALL"},{"remainingLimit":25000.00,"pipeCode":"P3","_notAllowedBankCode":"ABCD","channelAllowed":"ALL"}]}';
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'getCustomer']);
            $res = json_decode($response);
            if (isset($res->errorMsg)) {
                if ($res->errorCode == '00' && strtolower($res->errorMsg) == 'success') {
                    $paysprintremitters = Bankitremitter::where('mobile', $mobile_number)->first();
                    if (empty($paysprintremitters)) {
                        Bankitremitter::insert([
                            'mobile' => $mobile_number,
                            'name' => $res->data->name,
                            'dob' => $res->data->dateOfBirth
                        ]);
                    }
                    $data = array('name' => $res->data->name, 'mobile_number' => $mobile_number, 'total_limit' => $res->data->walletbal);
                    return Response(['status' => 'success', 'message' => 'Successfull.', 'data' => $data]);
                } elseif (strtolower($res->errorMsg) != 'success' && $res->errorCode == 'V0002') {
                    $url = $this->base_url . '/generic/otp';
                    $parameters = '{"customerId":"' . $mobile_number . '","agentCode":"' . $this->agentCode . '"}';

                    $response = self::sendCurlPost($url, $parameters);
                    Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'getCustomer']);
                    $data = array('is_otp' => 1);
                    return Response()->json(['status' => 'pending', 'message' => $res->errorMsg, 'data' => $data]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!']);
            }
        }

        function addSender($mobile_number, $first_name, $last_name, $address, $dateOfBirth, $otp)
        {
            $url = $this->base_url . '/customer/create';
            $parameters = array(
                'agentCode' => $this->agentCode,
                'customerId' => $mobile_number,
                'name' => $first_name . ' ' . $last_name,
                'address' => $address,
                'dateOfBirth' => $dateOfBirth,
                'otp' => $otp
            );
            $response = self::sendCurlPost($url, json_encode($parameters));
            //$response = '{"errorMsg":"SUCCESS","errorCode":"00","data":{"customerId":"9558233707"}}';
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($parameters), 'response_type' => 'addSender']);
            $res = json_decode($response);
            if (strtolower($res->errorMsg) == 'success') {
                Bankitremitter::insert([
                    'mobile' => $mobile_number,
                    'name' => $first_name . ' ' . $last_name,
                    'dob' => $dateOfBirth,
                    'address' => $address
                ]);
                return Response()->json(['status' => 'success', 'message' => $res->errorMsg]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
            }
        }

        function confirmSender($mobile_number, $first_name, $last_name, $dateOfBirth, $address, $otp)
        {
            $url = $this->base_url . '/customer/v1/fetch';
            $parameters = '{"customerId":"' . $mobile_number . '","agentCode":"' . $this->agentCode . '"}';
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'confirmSender']);
            $res = json_decode($response);
            if (isset($res->errorMsg)) {
                if ($res->errorCode != "00") {
                    return Response()->json(['status' => 'success', 'message' => $res->errorMsg]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => "Something's gone wrong. We're working to get it fixed as soon as we can."]);
            }
        }

        function senderResendOtp($mobile_number)
        {
            $url = $this->base_url . '/generic/otp';
            $parameters = '{"customerId":"' . $mobile_number . '","agentCode":"' . $this->agentCode . '"}';
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'senderResendOtp']);
            $res = json_decode($response);
            if ($res) {
                if (strtolower($res->errorMsg) == 'success' && $res->errorCode == '00') {
                    return Response()->json(['status' => 'success', 'message' => $res->errorMsg]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!']);
            }
        }

        function getAllBeneficiary($mobile_number, $sender_name)
        {
            $url = $this->base_url . "V1.1/recipient/fetchAll";
            $parameters = '{"customerId":"' . $mobile_number . '","agentCode":"1"}';
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'getAllBeneficiary']);
            $res = json_decode($response);
            if (strtolower($res->errorMsg) == 'success' && $res->errorCode == "00") {
                $beneficiaries = $res->data;
                self::updateBeneficiary($beneficiaries, $mobile_number, $sender_name);
                $beneficiaryList = self::getBeneficiaryList($beneficiaries);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'beneficiaries' => $beneficiaryList]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
            }
        }

        function updateBeneficiary($beneficiaries, $mobile_number, $sender_name)
        {
            foreach ($beneficiaries->recipientList as $value) {
                $beneficiary = Beneficiary::where('account_number', $value->udf1)->where('benficiary_id', $value->recipientId)
                    ->where('api_id', $this->api_id)
                    ->first();
                $data = array(
                    'benficiary_id' => $value->recipientId,
                    'account_number' => $value->udf1,
                    'ifsc' => $value->udf2,
                    'bank_name' => $value->bankName,
                    'name' => $value->recipientName,
                    'remiter_number' => $mobile_number,
                    'remiter_name' => $sender_name,
                    'status_id' => 1,
                    'api_id' => $this->api_id,
                );
                if ($beneficiary) {
                    $beneficiary_id = $beneficiary->id;
                    Beneficiary::where('id', $beneficiary_id)->update($data);
                } else {
                    Beneficiary::insert($data);
                }
            }
        }

        function getBeneficiaryList($beneficiaries)
        {
            $response = array();
            $i = 1;
            foreach ($beneficiaries->recipientList as $value) {
                $product = array();
                $product["id"] = $i++;
                $product["beneficiary_id"] = $value->recipientId;
                $product["bank_name"] = $value->bankName;
                $product["mobile_number"] = '';
                $product["beneficiary_name"] = $value->recipientName;
                $product["ifsc_code"] = $value->udf2;
                $product["account_number"] = $value->udf1;
                $product["is_verify"] = 1;
                $product["status_id"] = 1;
                array_push($response, $product);
            }
            return $response;
        }

        function getIfscCode($bank_id)
        {
            $paysprintdmtbanks = Bankitdmtbank::find($bank_id);
            if ($paysprintdmtbanks) {
                $data = array('ifsc' => $paysprintdmtbanks->ifsc);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        }

        function addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name)
        {
            $paysprintdmtbanks = Bankitdmtbank::find($bank_id);
            $bankCode = (empty($paysprintdmtbanks)) ? '' : $paysprintdmtbanks->bankCode;
            $url = $this->base_url . "/recipient/add";
            $parameters = '{"agentCode":"1","bankName":"' . $bankCode . '","customerId": "' . $mobile_number . '",
            "accountNo":"' . $account_number . '","ifsc": "' . $ifsc_code . '","mobileNo": "' . $mobile_number . '",
            "recipientName":"' . $beneficiary_name . '"}';
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'addBeneficiary']);
            $res = json_decode($response);
            if (isset($res->errorCode)) {
                if ($res->errorCode == "00") {
                    return Response()->json(['status' => 'success', 'message' => $res->errorMsg]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Someting went wrong']);
            }
        }

        function deleteBeneficiary($mobile_number, $beneficiary_id)
        {
            $url = $this->base_url . "/recipient/delete";
            $parameters = '{"customerId": "' . $mobile_number . '","recipientId": "' . $beneficiary_id . '","agentCode":"1"}';
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'deleteBeneficiary']);
            $res = json_decode($response);
            $response_code = $res->errorCode;
            if ($response_code == "00") {
                Beneficiary::where('benficiary_id', $beneficiary_id)
                    ->where('remiter_number', $mobile_number)
                    ->where('api_id', $this->api_id)->delete();
                return Response()->json(['status' => 'success', 'message' => $res->errorMsg]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->errorMsg]);
            }
        }

        function accountVerify($mobile_number, $recipientId, $api_id, $insert_id, $accountNumber, $ifscCode)
        {
            try {
                Log::info("accountVerify in ==");

                $url = $this->base_url . "V1.1/transact/IMPS/accountverification";
                $parameters = '{"agentCode":"1","customerId":"' . $mobile_number . '","amount":"1.0","clientRefId":"' . $recipientId . '",
            "udf1":"' . $accountNumber . '","udf2":"' . $ifscCode . '"}';
                Log::info("accountVerify Parameters ==>" . $url . '?' . $parameters);
                $response = self::sendCurlPost($url, $parameters);

                //$response ='{"errorMsg": "SUCCESS","errorCode": "00","data": {"customerId": "9243000200","name": "AADHARSHILA PVT LTD","clientRefId": "10029012309876543454","txnId":"802417335263"}}';
                Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'accountverification']);
                $res = json_decode($response);

                Log::info("accountVerify Response ==>" . $response);
                if (strtolower($res->errorMsg) == 'success' && $res->errorCode == "00") {
                    Log::info("accountVerify return ==> Success");
                    return ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $res->data->name];
                } elseif (strtolower($res->errorMsg) == 'failure' && $res->errorCode == '02') {
                    Log::info("accountVerify return ==> failure");
                    return ['status_id' => 2, 'message' => $res->Reason, 'name' => ''];
                } else {
                    Log::info("accountVerify return ==> else");
                    return ['status_id' => 3, 'message' => '', 'name' => ''];
                }
            } catch (\Exception $exception) {
                Log::error("accountVerify BANKIT==" . $exception->getMessage());
                Log::error($exception);
                Log::info("accountVerify return ==> Exception");
                return ['status_id' => 2, 'message' => $exception->getMessage(), 'name' => ''];
            }
        }


        function transferNow($amount, $beneficiary_id, $insert_id, $mobile_number, $channel_id, $api_id)
        {
            $clientRefId = '10240' . Helpers::generateRandomNumber(15);
            $txntype = ($channel_id == 2) ? 'IMPS' : 'NEFT';
            $url = $this->base_url . "/transact/" . $txntype . "/v1/remit";
            $parameters = '{"customerId":"' . $mobile_number . '","agentCode":"1","recipientId":"' . $beneficiary_id . '","amount":"' . $amount . '","clientRefId":"' . $clientRefId . '"}';
            $response = self::sendCurlPost($url, $parameters);
            //$response = '{"errorMsg": "SUCCESS","errorCode": "00","data": {"customerId": "9243000200","clientRefId": "10029012309876543454","txnId": "8012345623"}}';
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters]);
            $res = json_decode($response);
            $message = ($res->errorMsg) ? $res->errorMsg : '';
            if (isset($res->errorMsg)) {
                $status = strtolower($res->errorMsg);
                if ($status == 'success') {
                    return ['status_id' => 1, 'txnid' => $res->data->txnId, 'payid' => $res->data->clientRefId, 'message' => $message];
                } else if ($status == 'failure') {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => '', 'message' => ($res->Reason) ? $res->Reason : $message];
                }
            } else {
                return ['status_id' => 3, 'txnid' => '', 'payid' => '', 'message' => $message];
            }
        }

        function getBankList()
        {
            $masterbank = Masterbank::where('status_id', 1)->select('bank_id', 'bank_name', 'ifsc')->get();
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

        function bankFetch()
        {
            $url = $this->base_url . "/generic/bankList";
            $parameters = "{}";
            $response = self::sendCurlPost($url, $parameters);
            $response = json_decode($response);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            if (strtolower($response->errorMsg) == 'success' && $response->errorCode == '00') {
                $bankList = array();
                foreach ($response->data->bankList as $key => $value) {
                    $bdata = Bankitdmtbank::select('id', 'bank_name')->first();
                    if (empty($bdata)) {
                        $bankList[] = [
                            'bank_name' => $value->bankName,
                            'ifsc' => $value->ifsc,
                            'channelsSupported' => $value->channelsSupported,
                            'accVerAvailabe' => $value->accVerAvailabe,
                            'bankCode' => $value->bankCode,
                            'ifscStatus' => $value->ifscStatus,
                            'bank_id' => $key + 1,
                            'created_at' => $ctime,
                            'updated_at' => $ctime
                        ];
                    }
                }
                Bankitdmtbank::insert($bankList);
            }
            return Response()->json(['status' => 'success', 'message' => 'successful..!', 'bank_list' => $response]);
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
            $library = new BankitApicredentials();
            $Jwt = $library->encode($Jwtheader, $payload, $apikey);
            return $Jwt;
        }

        public function sendCurlPost($url, $api_request_parameters)
        {
            $header = $this->header;
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
                CURLOPT_POSTFIELDS => $api_request_parameters,
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }

        function callBackAPi($param, $money_provider_id)
        {
            $provider_commission_type = 2;
            $api_id = 2;
            if ($param['ClientRefID'] != "" && $param['Status'] != '') {
                $clientRefID = $param['ClientRefID'];
                $reportDetails = Report::where('payid', $clientRefID)->where('api_id', $api_id)->where('provider_id', $money_provider_id)->first();
                if ($reportDetails) {
                    $oldStatusId = $reportDetails->status_id;

                    $updatedStatusID = $reportDetails->status_id;
                    if (strtolower($param['Status']) == 'pending') {
                        $updatedStatusID = 3;
                    } else if (strtolower($param['Status']) == 'success') {
                        $updatedStatusID = 1;
                    } else if (strtolower($param['Status']) == 'fail') {
                        $updatedStatusID = 2;
                    }
                    Report::where('id', $reportDetails->id)->update(['status_id' => $updatedStatusID]);
                    $userdetails = User::find($reportDetails->user_id);
                    $user_id = $reportDetails->user_id;
                    if (!$reportDetails && $oldStatusId == 3 && $updatedStatusID != 3) {
                        $amount = $reportDetails->amount;
                        if ($updatedStatusID == 1 && $oldStatusId == 3) {
                            /* Commission && minus balance if report Pending to Success*/
                            $beneficiary = Beneficiary::where('id', $reportDetails->benficiary_id)->first();
                            $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
                            $scheme_id = $userdetails->scheme_id;

                            $library = new GetcommissionLibrary();
                            $commission = $library->get_commission($scheme_id, $money_provider_id, $amount, $provider_commission_type);
                            $retailer = $commission['retailer'];
                            $d = $commission['distributor'];
                            $sd = $commission['sdistributor'];
                            $st = $commission['sales_team'];
                            $rf = $commission['referral'];

                            $library = new Commission_increment();
                            $library->parent_recharge_commission($user_id, $account_number, $reportDetails->id, $money_provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                            // get wise commission
                            $library = new GetcommissionLibrary();
                            $apiComms = $library->getApiCommission($api_id, $money_provider_id, $amount);
                            $apiCommission = $apiComms['apiCommission'];
                            $commissionType = $apiComms['commissionType'];
                            $library = new Commission_increment();
                            $library->updateApiComm($user_id, $money_provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $reportDetails->id, $commissionType);

                            Log::error("DMT Report minus balance  and charge commission === " . $reportDetails->decrementAmount);
                        } else if ($updatedStatusID == 2 && $oldStatusId == 3) {
                            /*revert balance if report pending to Failed*/
                            Balance::where('user_id', $user_id)->increment('user_balance', $reportDetails->decrementAmount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            Report::where('id', $reportDetails->id)->update(['total_balance' => $user_balance]);
                            Log::error("DMT Report revert balance  === " . $reportDetails->decrementAmount);
                        }
                    }
                    return ['status' => "00", "message" => "Success"];
                } else {
                    return ['status' => 401, "message" => "Reports not found"];
                }
            } else {
                return ['status' => 401, "message" => $param['status']];
            }
        }
    }
}
