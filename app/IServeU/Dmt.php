<?php

namespace App\IServeU {

    use App\Library\Commission_increment;
    use App\Library\GetcommissionLibrary;
    use App\Models\Balance;
    use App\Models\Report;
    use App\Models\User;
    use App\Models\Masterbank;
    use App\Models\Beneficiary;
    use App\Models\Apiresponse;
    use App\Models\iServeUremitter;
    use App\IServeU\Apicredentials as iServeUApiCredentials;
    use Illuminate\Support\Facades\Log;

    class Dmt
    {

        public function __construct()
        {
            $mode = env('iServeUDmt_MODE', 'LIVE');
            $this->dmt_mode = $mode;
            $library = new iServeUApiCredentials();
            $response = $library->getCredentials($mode);
            $baseUrl = $response['base_url'];
            $this->api_id = $response['api_id'];
            $this->client_id = $response['clientId'];
            $this->client_secret = $response['clientSecret'];
            $this->header = array(
                'Content-Type: application/json',
                'accept: application/json',
                'client_id:' . $this->client_id,
                'client_secret:' . $this->client_secret,
            );
            if ($this->dmt_mode != 'LIVE') {
                $baseUrl .= 'dmt-lite/unified/';
            } else {
                $baseUrl .= 'common/dmt-lite/';
            }
            $this->base_url = $baseUrl;
        }

        function getAllBeneficiary($mobile_number)
        {
            $beneficiaryList = self::getBeneficiaryList($mobile_number);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'beneficiaries' => $beneficiaryList]);
        }

        function getCustomer($mobile_number, $lat, $long, $usernameDMT)
        {
            $url = $this->base_url . 'customer/get-customer';
            $latlong = $lat . ',' . $long;
            $parameters = '{"mobileNumber":"' . $mobile_number . '","username":"' . $usernameDMT . '","latlong":"' . $latlong . '","publicIP":"' . request()->ip() . '"}';
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'getCustomer']);
            if ($this->dmt_mode != 'LIVE') {
                $response = '{"name": "Chandan Pradhan","mobileNumber": "8144094480","statusCode": "2","statusDesc": "Customer verified successfully","KYCTypeFlag": true,"status":"SUCCESS"}';
            }
            $res = json_decode($response);
            $total_limit = 0;
            $name = '';
            if (strtolower($res->status) == 'success') {
                $name = (isset($res->name)) ? $res->name : '';
                $remitters = iServeUremitter::where('mobile', $mobile_number)->first();
                if (empty($remitters)) {
                    iServeUremitter::insert([
                        'mobile' => $mobile_number,
                        'name' => $name
                    ]);
                }
                $urlMobile = $this->base_url . 'customer/global-limit';
                $parametersMobile = '{"mobileNumber":"' . $mobile_number . '"}';
                $responseMobile = self::sendCurlPost($urlMobile, $parametersMobile);
                Apiresponse::insertGetId(['message' => $responseMobile, 'api_type' => $this->api_id, 'request_message' => $urlMobile . '?' . $parametersMobile, 'response_type' => 'getCustomerLimit']);
                $resMobile = json_decode($responseMobile);

                if (isset($resMobile->status)) {
                    if (strtolower($resMobile->status) == 'success') {
                        $total_limit = $resMobile->totalPipeLimit;
                    }
                }
            }
            $data = array('name' => $name, 'mobile_number' => $mobile_number, 'total_limit' => $total_limit, 'KYCTypeFlag' => $res->KYCTypeFlag);
            return Response(['status' => 'success', 'message' => 'Successful.', 'data' => $data]);
        }

        function senderSendOtp($mobile_number, $otpType, $latLong, $usernameDMT, $ovdData, $externalRefNumber = '', $requestedAmount = '')
        {
            $url = $this->base_url . 'otp/send';
            $parameters = array(
                'customerMobileNumber' => $mobile_number,
                'username' => $usernameDMT,
                'otpType' => $otpType,
                'ovdType' => 'Aadhaar Card',
                'latLong' => $latLong,
                'publicIP' => request()->ip()
            );
            if ($otpType == 2) {
                $parameters['externalRefNumber'] = $externalRefNumber;
                $parameters['requestedAmount'] = $requestedAmount;
            } else {
                $parameters['ovdData'] = $ovdData;
            }
            $response = self::sendCurlPost($url, json_encode($parameters));
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($parameters), 'response_type' => 'sendOTPREference']);
            $res = json_decode($response);
            $description = (isset($res->statusDesc)) ? $res->statusDesc : 'Oops something went wrong!';
            $statusCode = (isset($res->statusCode)) ? $res->statusCode : '';
            if (isset($res->status) && strtolower($res->status) == 'success') {
                return Response()->json(['status' => 'success', 'message' => $description, 'statusCode' => $statusCode, 'is_send_otp' => true, 'externalRefNumber' => $externalRefNumber]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $description, 'statusCode' => $statusCode, 'is_send_otp' => false]);
            }
        }

        function getBeneficiaryList($mobile_number)
        {
            $beneficiaries = Beneficiary::where('remiter_number', $mobile_number)
                ->where('api_id', $this->api_id)
                ->get()->toArray();

            $response = array();
            $i = 1;
            foreach ($beneficiaries as $value) {
                $product = array();
                $product["id"] = $i++;
                $product["beneficiary_id"] = $value['benficiary_id'];
                $product["bank_name"] = $value['bank_name'];
                $product["mobile_number"] = '';
                $product["beneficiary_name"] = $value['name'];
                $product["ifsc_code"] = $value['ifsc'];
                $product["account_number"] = $value['account_number'];
                $product["is_verify"] = 1;
                $product["status_id"] = 1;
                array_push($response, $product);
            }
            return $response;
        }

        function getIfscCode($bank_id)
        {
            $paysprintdmtbanks = Masterbank::find($bank_id);
            if ($paysprintdmtbanks) {
                $data = array('ifsc' => $paysprintdmtbanks->ifsc);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        }

        function accountVerify($insert_id, $api_id, $requestData)
        {
            try {
                $url = $this->base_url . "transaction/verify-bene";
                $response = self::sendCurlPost($url, $requestData);
                Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $requestData, 'response_type' => 'accountverification']);
                if ($this->dmt_mode != 'LIVE') {
                    $response = '{
                      "status": "SUCCESS",
                      "statusCode": "0",
                      "externalRefNumber": "DLC00000000032",
                      "parentTxnId": "4563379176284160",
                      "status-desc": "Transaction Successful",
                      "gateway-transaction-details": [
                        {
                          "gateWayTxnId": "45633791762841601",
                          "status": "SUCCESS",
                          "txnStausCode": "4",
                          "txnStatusDesc": "Transaction Success",
                          "amount": 1,
                          "rrn": "310116908254",
                          "beneName": "rajkumar",
                          "charges": "0.0",
                          "createdDate": "Wed Mar 06 06:22:31 UTC 2024"
                        }
                      ]
                    }';
                }
                $res = json_decode($response, true);
                $message = '';
                if (isset($res['statusDesc'])) {
                    $message = $res['statusDesc'];
                } else if (isset($res['status-desc'])) {
                    $message = $res['status-desc'];
                }
                if (strtolower($res['status']) == 'success') {
                    return ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $res['gateway-transaction-details'][0]['beneName']];
                } elseif (strtolower($res['status']) == 'failed') {
                    return ['status_id' => 2, 'message' => $message, 'name' => ''];
                } else {
                    Log::info("accountVerify return ==> else");
                    return ['status_id' => 3, 'message' => $message, 'name' => ''];
                }
            } catch (\Exception $exception) {
                return ['status_id' => 2, 'message' => $exception->getMessage(), 'name' => ''];
            }
        }


        function transferNow($insert_id, $channel_id, $api_id, $parameters)
        {

            $url = $this->base_url . "transaction/moneytransfer-wb";
            $response = self::sendCurlPost($url, $parameters);
            Log::info($response);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'moneytransfer-wb']);
            if ($this->dmt_mode != 'LIVE') {
                $response = '{
                      "status": "SUCCESS",
                      "statusDesc": "Transaction Successful",
                      "gatewayTxnStatusList": [
                      {
                        "gateWayTxnId": "13533403753431041",
                        "status": "SUCCESS",
                        "txnStausCode": "4",
                        "txnStatusDesc": "Transaction Success",
                        "amount": 100.0,
                        "rrn": "303716251832",
                        "beneName": "SANGRAM",
                        "charges": "10.0",
                        "createdDate": "Mon Feb 06 11:14:00 UTC 2023"
                      }
                      ],
                      "statusCode": "0",
                      "externalRefNumber": "ABCLEFPPGKPL5678P",
                      "parentTxnId": "1353340375343104"
                    }
                ';
            }
            $res = json_decode($response, true);
            $message = '';
            if (isset($res['statusDesc'])) {
                $message = $res['statusDesc'];
            } else if (isset($res['status-desc'])) {
                $message = $res['status-desc'];
            }
            if (isset($res['status'])) {
                $status = strtolower($res['status']);
                if ($status == 'success') {
                    return ['status_id' => 1, 'txnid' => $res['parentTxnId'], 'payid' => '', 'message' => $message];
                } else if ($status == 'failed') {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => '', 'message' => $message];
                } else if ($status == 'inprogress') {
                    return ['status_id' => 3, 'txnid' => $res['parentTxnId'], 'payid' => '', 'message' => $message];
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

        public function sendCurlPost($url, $parameters)
        {
            try {
                $header = [
                    "accept: application/json",
                    "Content-Type: application/json",
                    "client_id: $this->client_id",
                    "client_secret: $this->client_secret"
                ];
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
                curl_close($curl);
                return $response;
            } catch (\Exception $exception) {
                Log::info($exception);
                return $exception->getMessage();
            }
        }

        function callBackAPi($paramArray, $money_provider_id)
        {

            foreach ($paramArray as $key => $param) {
                $provider_commission_type = 3;
                $api_id = 3;
                if ($param['txnId'] != "" && $param['status'] != '') {
//                    $txnId = $param['txnId'];
                    $txnId = str_replace('#', '', $param['txnId']);
                    $reportDetails = Report::where('txnid', $txnId)->where('api_id', $api_id)->where('provider_id', $money_provider_id)->first();
                    if ($reportDetails) {
                        $oldStatusId = $reportDetails->status_id;
                        $amount = $reportDetails->amount;

                        if (strtolower($param['status']) == 'pending') {
                            Log::error("DMT Report status " . $param['status']);
                        } else if (strtolower($param['status']) == 'success') {
                            Report::where('id', $reportDetails->id)->update(['status_id' => 1, 'payid' => $param['rrn']]);
                            $userdetails = User::find($reportDetails->user_id);
                            $user_id = $reportDetails->user_id;
                            if ($oldStatusId != 1) {
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
                            }
                            Log::error("DMT Report minus balance  and charge commission === " . $reportDetails->decrementAmount);


                        } else if ((strtolower($param['status']) == 'fail') || (strtolower($param['status']) == 'failed') || (strtolower($param['status']) == 'refunded')) {
                            if ($oldStatusId != 2) {
                                Report::where('id', $reportDetails->id)->update(['status_id' => 2]);
                                $userdetails = User::find($reportDetails->user_id);
                                $user_id = $reportDetails->user_id;

                                /*revert balance if report pending to Failed*/
                                Balance::where('user_id', $user_id)->increment('user_balance', $reportDetails->decrementAmount);
                                $balance = Balance::where('user_id', $user_id)->first();
                                $user_balance = $balance->user_balance;
                                Report::where('id', $reportDetails->id)->update(['total_balance' => $user_balance]);
                            }
                            Log::info("DMT Report revert balance  === " . $reportDetails->decrementAmount);
                        }
                    } else {
                        Log::info("DMT Report no data");

                    }
                }
            }
            return ['status' => 0, "statusDesc" => "Success"];
        }

        function processCustomer($parameters)
        {

            $url = $this->base_url . "customer/process-customer-registration";
            $response = self::sendCurlPost($url, $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => 3, 'request_message' => $url . '?' . $parameters, 'response_type' => 'process-customer-registration']);
            if ($this->dmt_mode != 'LIVE') {
                $response = '{
                        "name": "Sangram Keshari pradhan",
                        "mobileNumber": "8658733463",
                        "statusCode": "2",
                        "statusDesc": "Customer verified successfully and Ovd Data Updated",
                        "KYCTypeFlag": true,
                        "status":"SUCCESS"
                        }';
            }
            $res = json_decode($response, true);
            $message = '';
            if (isset($res['statusDesc'])) {
                $message = $res['statusDesc'];
            } else if (isset($res['status-desc'])) {
                $message = $res['status-desc'];
            }
            if (isset($res['status'])) {
                $status = strtolower($res['status']);
                $kyc = '';
                if (isset($res['kyctypeFlag'])) {
                    $kyc = $res['kyctypeFlag'];
                } elseif (isset($res['KYCTypeFlag'])) {
                    $kyc = $res['KYCTypeFlag'];
                }
                return ['status' => $status, 'message' => $message, 'KYCTypeFlag' => $kyc];
            } else {
                return ['status' => 'failed', 'message' => $message];
            }
        }
    }


}
