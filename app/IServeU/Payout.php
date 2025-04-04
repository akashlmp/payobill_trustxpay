<?php

namespace App\IServeU {

    use App\Models\Apiresponse;
    use App\Models\UserAepsPayoutAccount;
    use Helpers;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class Payout
    {

        public function __construct()
        {
            $mode = env('ISERVEU_PAYOUT_MODE', 'LIVE');
            $this->dmt_mode = $mode;
            if ($mode == "TEST") {
                $this->base_url = "";
                $this->client_id = "";
                $this->client_secret = "";
                $this->encrypt_key = "=";
                $this->pass_key = "";
            } else {
                $this->base_url = "";
                $this->client_id = "";
                $this->client_secret = "";
                $this->encrypt_key = "";
                $this->pass_key = "";
            }
            $this->api_id = 3;
        }

        public function addBankAccount($request, $user_id, $merchantcode)
        {
            $id = $request['id'] ?? NULL;
            $save_data = [
                'user_id' => $user_id,
                'merchant_code' => $merchantcode,
                'bank_name' => isset($request['bank_name']) ? $request['bank_name'] : null,
                'account_no' => isset($request['account_no']) ? $request['account_no'] : null,
                'ifsc' => isset($request['ifsc']) ? $request['ifsc'] : null,
                'name' => isset($request['account_holder_name']) ? $request['account_holder_name'] : null,
                'account_type' => isset($request['account_type']) ? $request['account_type'] : null,
                'bene_phone_number' => isset($request['bene_phone_number']) ? $request['bene_phone_number'] : null,
                'document_verified' =>  0,
                'provider_api_from' => 3,
                'status' => 1,
            ];
            if ($id) {
                $checkAccount = UserAepsPayoutAccount::where('id', $id)->first();
                if ($checkAccount) {
                    $checkAccount->update($save_data);
                } else {
                    UserAepsPayoutAccount::create($save_data)->first();
                }
            } else {
                UserAepsPayoutAccount::create($save_data)->first();
            }
        }

        public function generateHeaderSecrate()
        {
            $data = [
                "client_id" => $this->client_id,
                "client_secret" => $this->client_secret,
                "epoch" => (string) time()
            ];
            $data = json_encode($data);
            return encryptPayoutResponse($data, $this->encrypt_key);
        }

        public function generatePayoutData($data)
        {
            return encryptPayoutResponse($data, $this->encrypt_key);
        }

        public function generateAEPSData($data)
        {
            return encryptPayoutResponse($data, "MWYztgF/ZcR+XyRwZEn1NpdyYGUnQMuIMDgI6ZWlaNg=");
        }

        public function doTransaction($parameters)
        {
            $header_secrate = $this->generateHeaderSecrate();
            // echo $header_secrate . "<br>";
            $parameters = json_encode($parameters);
            $payoutData = $this->generatePayoutData($parameters);
            //pre($payoutData);
            $method = 'POST';
            $url = $this->base_url . '/w1w2-payout/w1/cashtransfer';
            $header = [
                "accept: application/json",
                "Content-Type: application/json",
                "header_secrets: $header_secrate",
                "pass_key: $this->pass_key"
            ];

            $paramData = [
                "RequestData" => $payoutData
            ];
            $paramData = json_encode($paramData);
            $request_message = $url . '?' . json_encode($parameters);
            $responseCurl = $this->sendCurlPost($url, $header, $paramData, $method);
            $response = json_decode($responseCurl, true);
            Apiresponse::insertGetId(['message' => $responseCurl, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);
            if (isset($response['status']) && ($response['status'] == -1 || $response['status'] == 0)) {
                return ['status' => 'failure', 'transaction_id' => "", 'message' => $response['statusDesc'], 'request_message' => $request_message, 'api_response' => $response];
            }
            if (isset($response['ResponseData'])) {
                if ($response['ResponseData'] == 'Invalid Request') {
                    return ['status' => 'failure', 'transaction_id' => "", 'message' => "Invalid Request", 'request_message' => $request_message, 'api_response' => $responseCurl];
                }
                $jsonResponse = decryptPayoutResponse($response['ResponseData'], $this->encrypt_key);
                Apiresponse::insertGetId(['message' => $jsonResponse, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);
                $res = json_decode($jsonResponse, true);
                // pre($res);
                if (isset($res['subStatus']) && isset($res['status'])) {
                    $transactionId = NULL;
                    if (isset($res['transactionId'])) {
                        $transactionId = $res['transactionId'];
                    }
                    if ($res['status'] == "SUCCESS" && $res['subStatus'] == 0) {
                        return ['status' => 'success', 'message' => "Payout Successfully received.", 'request_message' => $request_message, 'payid' => $res['rrn'] ?? null,  'transaction_id' => $transactionId, 'api_response' => $jsonResponse];
                    } elseif ($res['status'] == "INPROGRESS" && $res['subStatus'] == 1) {
                        return ['status' => 'pending', 'message' => "Please Wait while your transaction is processing.", 'payid' => $res['rrn'] ?? null, 'request_message' => $request_message,  'transaction_id' => $transactionId, 'api_response' => $jsonResponse];
                    } else {
                        $msg = "Your transaction is failed please try again or later";
                        if (isset($res['statusDesc']) && $res['statusDesc'] != '') {
                            $msg = $res['statusDesc'];
                        }
                        return ['status' => 'failure', 'transaction_id' => $transactionId, 'message' => $msg, 'request_message' => $request_message, 'api_response' => $jsonResponse];
                    }
                } else {
                    return ['status' => 'failure', 'message' => 'Something went wrong', 'request_message' => $request_message, 'api_response' => $jsonResponse];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Something went wrong', 'request_message' => $request_message, 'api_response' => $response];
            }
        }

        public function transactionStatusCheck($ref_id, $transaction_id, $sdate,$edate)
        {
            $header_secrate = $this->generateHeaderSecrate();
            $parameters = array(
                "$1" => "Cashout_addbank_status",
                "$4" => $sdate,
                "$5" => $edate,
                "$6" => $ref_id,
                "$10" => $transaction_id
            );
            $parameters = json_encode($parameters);
            $payoutData = $this->generatePayoutData($parameters);
            //pre($payoutData);
            $method = 'POST';
            $url = $this->base_url . '/payout/statuscheck/txnreport';
            $header = [
                "accept: application/json",
                "Content-Type: application/json",
                "header_secrets: $header_secrate",
                "pass_key: $this->pass_key"
            ];

            $paramData = [
                "RequestData" => $payoutData
            ];
            $paramData = json_encode($paramData);
            $request_message = $url . '?' . json_encode($parameters);
            $responseCurl = $this->sendCurlPost($url, $header, $paramData, $method);
            $response = json_decode($responseCurl, true);
            Apiresponse::insertGetId(['message' => $responseCurl, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);
            if (isset($response['ResponseData'])) {
                if ($response['ResponseData'] == 'Invalid Request') {
                    return ['status' => 'failure', 'transaction_id' => "", 'message' => "Invalid Request", 'request_message' => $request_message, 'api_response' => $responseCurl];
                }
                $jsonResponse = decryptPayoutResponse($response['ResponseData'], $this->encrypt_key);
                Apiresponse::insertGetId(['message' => $jsonResponse, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);
                $res = json_decode($jsonResponse, true);
                if (isset($res['status'])) {
                    if ($res['status'] == 200 || $res['status'] == 1) {
                        return ['status' => 'success', 'message' => $res['message'], 'data' => $res['results']];
                    } else {
                        return ['status' => 'failure', 'message' => $res['message']];
                    }
                } else {
                    return ['status' => 'failure', 'message' => 'Something went wrong'];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Something went wrong', 'request_message' => $request_message, 'api_response' => $response];
            }
            // Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'transactionStatusCheck']);
            // $res = json_decode($response, true);
            // $res = [
            //     "status" => 200,
            //     "message" => "Success",
            //     "length" => 1,
            //     "results" => [
            //       [
            //         "transactionId" => 386107533434880,
            //         "subStatus" => 0,
            //         "status" => "FAILED",
            //         "statusDesc" => "CO00 - Transaction is Successful.",
            //         "beneName" => "kiran",
            //         "beneAccountNo" => "33171402473",
            //         "beneifsc" => "SBIN0001083",
            //         "benePhoneNo" => 7381279922,
            //         "beneBankName" => "State Bank of India",
            //         "clientreferenceid" => "10824456780909",
            //         "latlong" => "22.8031731,88.7874172",
            //         "pincode" => 751024,
            //         "custName" => "Vijay Nayak",
            //         "custMobNo" => 9821361027,
            //         "rrn" => "308214158501",
            //         "paramA" => "",
            //         "paramB" => "",
            //         "dateTime" => "03-23-2023 02:28:08",
            //         "txnAmount" => 100,
            //         "txnType" => "IMPS"
            //       ]
            //     ]
            //   ];
        }

        public function aepsTransactionStatusCheck($ref_id, $sdate,$edate)
        {
            echo "Enc Key<br>";
            echo env('ISU_TOKEN_KEY');
            echo "<br>";
            $token = Helpers::generateIserveuToken();
            echo "Token:<br> ". $token;
            echo "<br>";
            echo "<br>";
            $parameters = array(
                "$1" => "UAeps_txn_status_api",
                "$4" => $sdate,
                "$5" => $edate,
                "$6" => $ref_id
            );
            $parameters = json_encode($parameters);
            $payoutData = $this->generateAEPSData($parameters);
            echo "Data:<br> ". $payoutData;
            echo "<br>";
           // pre($payoutData);
            $method = 'POST';
            $url = 'https://apiprodgateway.txninfra.com/productionV2/statuscheckv2/txnreportv2';
            $header = [
                "accept: application/json",
                "Content-Type: application/json",
                "token: $token",
                "pass_key: $this->pass_key"
            ];
            $paramData = [
                "RequestData" => $payoutData
            ];
            $paramData = $parameters;// json_encode($paramData);
            $request_message = $url . '?' . json_encode($parameters);
            $responseCurl = $this->sendCurlPost($url, $header, $paramData, $method);
            $response = json_decode($responseCurl, true);
            pre($response);
            Apiresponse::insertGetId(['message' => $responseCurl, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);
            if (isset($response['ResponseData'])) {
                if ($response['ResponseData'] == 'Invalid Request') {
                    return ['status' => 'failure', 'transaction_id' => "", 'message' => "Invalid Request", 'request_message' => $request_message, 'api_response' => $responseCurl];
                }
                $jsonResponse = decryptPayoutResponse($response['ResponseData'], $this->encrypt_key);
                Apiresponse::insertGetId(['message' => $jsonResponse, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'doTransaction']);
                $res = json_decode($jsonResponse, true);
                if (isset($res['status'])) {
                    if ($res['status'] == 200 || $res['status'] == 1) {
                        return ['status' => 'success', 'message' => $res['message'], 'data' => $res['results']];
                    } else {
                        return ['status' => 'failure', 'message' => $res['message']];
                    }
                } else {
                    return ['status' => 'failure', 'message' => 'Something went wrong'];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Something went wrong', 'request_message' => $request_message, 'api_response' => $response];
            }
            // Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'transactionStatusCheck']);
            // $res = json_decode($response, true);
            // $res = [
            //     "status" => 200,
            //     "message" => "Success",
            //     "length" => 1,
            //     "results" => [
            //       [
            //         "transactionId" => 386107533434880,
            //         "subStatus" => 0,
            //         "status" => "FAILED",
            //         "statusDesc" => "CO00 - Transaction is Successful.",
            //         "beneName" => "kiran",
            //         "beneAccountNo" => "33171402473",
            //         "beneifsc" => "SBIN0001083",
            //         "benePhoneNo" => 7381279922,
            //         "beneBankName" => "State Bank of India",
            //         "clientreferenceid" => "10824456780909",
            //         "latlong" => "22.8031731,88.7874172",
            //         "pincode" => 751024,
            //         "custName" => "Vijay Nayak",
            //         "custMobNo" => 9821361027,
            //         "rrn" => "308214158501",
            //         "paramA" => "",
            //         "paramB" => "",
            //         "dateTime" => "03-23-2023 02:28:08",
            //         "txnAmount" => 100,
            //         "txnType" => "IMPS"
            //       ]
            //     ]
            //   ];
        }
        public function sendCurlPost($url, $header, $api_request_parameters, $method)
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
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $api_request_parameters,
                CURLOPT_HTTPHEADER => $header,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }
    }
}
