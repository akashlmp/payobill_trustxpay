<?php

namespace App\Paysprint {

    use App\Models\Apiresponse;
    use Helpers;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class Payout
    {

        public function __construct()
        {
            $mode = env('DMT_MODE', 'LIVE');
            $this->dmt_mode = $mode;
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
            $this->bank3_flag = 'no';
            $this->bank4_flag = 'no';
            $this->pincode = "201301";
            $this->dob = "1995-12-16";
            $this->gst_state = "09";
            $this->address = "Noida";
            $this->pipe = 'bank1';
        }

        public function getBankList($merchantId)
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/payout/payout/list';
            $parameters = '{"merchantid":"' . $merchantId . '"}';
            $token = $this->generateToken();

            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }

            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if ($res['response_code'] == 1) {
                    return ['status' => 'success', 'message' => $res['message'], 'data' => $res['data']];
                } else {
                    return ['status' => 'failure', 'message' => $res['message']];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Someting went wrong'];
            }
        }

        public function addBankAccount($merchantId, $bankId, $account_no, $ifsc, $name, $account_type)
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/payout/payout/add';
            $parameters = '{"bankid":' . $bankId . ',"merchant_code":"' . $merchantId . '","account":"' . $account_no . '","ifsc":"' . $ifsc . '","name":"' . $name . '","account_type":"' . $account_type . '" }';
            $token = $this->generateToken();

            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }

            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'addBankAccount']);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if (in_array($res['response_code'], [1, 2])) {
                    return ['status' => 'success', 'message' => isset($res['message']) ? $res['message'] : 'Account Detailed saved successfully'];
                } else {
                    return ['status' => 'failure', 'message' => isset($res['message']) ? $res['message'] : 'Something went wrong'];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Something went wrong'];
            }
        }

        public function uploadDocument($bene_id, $passbook, $doctype, $panImage, $front_aadhar, $back_aadhar)
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/payout/payout/uploaddocument';
            $parameters['doctype'] = $doctype;
            $parameters['bene_id'] = $bene_id;
            $parameters['passbook'] = curl_file_create($passbook->getRealPath(), $passbook->getMimeType(), $passbook->getClientOriginalName());
            if ($doctype == 'PAN') {
                $parameters['panimage'] = curl_file_create($panImage->getRealPath(), $panImage->getMimeType(), $panImage->getClientOriginalName());
            } else {
                $parameters['front_aadhar'] = curl_file_create($front_aadhar->getRealPath(), $front_aadhar->getMimeType(), $front_aadhar->getClientOriginalName());
                $parameters['back_aadhar'] = curl_file_create($back_aadhar->getRealPath(), $back_aadhar->getMimeType(), $back_aadhar->getClientOriginalName());
            }
            $token = $this->generateToken();
            $header = [
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($parameters), 'response_type' => 'uploadDocument']);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if ($res['response_code'] == 1) {
                    return ['status' => 'success', 'message' => $res['message']];
                } else {
                    return ['status' => 'failure', 'message' => $res['message']];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Someting went wrong'];
            }
        }

        public function accountStatusCheck($merchantId, $beneId)
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/payout/Payout/accountstatus';
            $parameters = '{"merchantid":"' . $merchantId . '","beneid":"' . $beneId . '"}';
            $token = $this->generateToken();

            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }

            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'accountStatusCheck']);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if ($res['response_code'] == 1) {
                    if($res['accountstatus']==1){
                        return ['status' => 'success', 'message' => "Account status is Activated"];
                    }elseif($res['accountstatus']==2){
                        return ['status' => 'success', 'message' => "Account status: Document Upload Pending"];
                    }elseif($res['accountstatus']==3){
                        return ['status' => 'success', 'message' => "Account status: Document verification pending at admin end"];
                    }elseif($res['accountstatus']==0){
                        return ['status' => 'success', 'message' => "Account status: Deactivated"];
                    }
                    return ['status' => 'success', 'message' => $res['message']];
                } else {
                    return ['status' => 'failure', 'message' => $res['message']];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Someting went wrong'];
            }
        }

        public function doTransaction($bene_id, $amount, $refid, $mode = 'IMPS')
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/payout/payout/dotransaction';
            $parameters = ['bene_id' => $bene_id, 'amount' => $amount, 'refid' => $refid, 'mode' => $mode,];
            $token = $this->generateToken();
            $header = [
                'Accept: application/json',
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }

            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($parameters), 'response_type' => 'doTransaction']);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if ($res['response_code'] == 1) {
                    return ['status' => 'success', 'message' => $res['message'], 'transaction_id' => $res['ackno'] ?? null, 'api_response' => $response];
                } else {
                    return ['status' => 'failure', 'message' => $res['message'], 'api_response' => $response];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Someting went wrong', 'api_response' => $response];
            }
        }

        public function transactionStatusCheck($ref_id, $transaction_id)
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/payout/payout/status';
            $parameters = '{"refid":"' . $ref_id . '","ackno":' . $transaction_id . '}';
            $token = $this->generateToken();

            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }

            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'transactionStatusCheck']);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if ($res['response_code'] == 1) {
                    return ['status' => 'success', 'message' => $res['message'], 'data' => $res['data']];
                } else {
                    return ['status' => 'failure', 'message' => $res['message']];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Someting went wrong'];
            }
        }

        public function getBankMasterList()
        {
            $method = 'POST';
            $url = $this->base_url . 'api/v1/service/aeps/banklist/index';
            $parameters = '';
            $token = $this->generateToken();

            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            ];
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }

            $response = $this->sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response, true);
            if (isset($res['response_code'])) {
                if ($res['response_code'] == 1) {
                    return ['status' => 'success', 'message' => $res['message'], 'data' => $res['banklist']['data']];
                } else {
                    return ['status' => 'failure', 'message' => $res['message']];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Someting went wrong'];
            }
        }

        public function generateToken()
        {
            $Jwtheader = $this->jwt_header;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $reqid = rand(100000, 999999);
            $timestamp = strtotime($ctime);
            $payload = '{"timestamp": "' . $timestamp . '","partnerId": "' . $this->partner_id . '","reqid": "' . $reqid . '"}';
            $apikey = $this->api_key;
            $library = new PaysprintApicredentials();
            $Jwt = $library->encode($Jwtheader, $payload, $apikey);
            return $Jwt;
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
