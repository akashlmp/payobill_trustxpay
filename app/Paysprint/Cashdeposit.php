<?php

namespace App\Paysprint {

    use App\Models\Apiresponse;
    use App\Models\User;
    use App\Models\Paysprintbank;

    // library here
    use App\Library\GetcommissionLibrary;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class Cashdeposit
    {

        public function __construct()
        {
            $mode = 'TEST'; // LIVE or TEST
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
            $this->service_id = 26;
            $this->pipe = 'bank1';
        }


        function cashDeposit($user_id, $mobile_number, $accessmodetype, $adhaar_number, $latitude, $longitude, $insert_id, $bank_id, $submerchantid, $BiometricData, $amount)
        {
            $userDetails = User::find($user_id);
            $now = now();
            $ctime = $now->format('Y-m-d H:i:s');
            $maskedAadhaar = str_repeat('X', 8) . substr($adhaar_number, -4);
            // Prepare body data
            $bodyData = [
                'mobilenumber' => $mobile_number,
                'accessmodetype' => $accessmodetype,
                'adhaarnumber' => $adhaar_number,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'referenceno' => $insert_id,
                'nationalbankidentification' => $bank_id,
                'pipe' => $this->pipe,
                'submerchantid' => $submerchantid,
                'data' => $BiometricData,
                'timestamp' => $ctime,
                'amount' => $amount
            ];
            // Encrypt the body data
            $key = $this->key;
            $iv = $this->iv;
            $ciphertext_raw = openssl_encrypt(json_encode($bodyData), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            $request1 = base64_encode($ciphertext_raw);
            $api_request_parameters = json_encode(['body' => $request1]);
            // Generate token and prepare headers
            $token = $this->generateToken();
            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            ];
            // Send the API request
            $url = $this->base_url . "api/v1/service/cashdeposit/V2/Cashdeposit/index";
            $response = $this->sendCurlPost($url, $header, $api_request_parameters, 'POST');
            // Prepare data for saving request message
            $bodyDataSave = $bodyData;
            $bodyDataSave['data'] = ''; // Clear sensitive data before saving
            $request_message = $url . '?' . json_encode($bodyDataSave);
            // Save the API response
           // $response = '{"response_code":1,"status":true,"message":"Success","ackno":1088,"amount":"100","balanceamount":5000,"bankrrn":713161,"bankiin":"607152","mobile":"","errorcode":0}';
            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'report_id' => $insert_id,
                'request_message' => $request_message,
                'response_type' => 'CD'
            ]);
            $responseData = json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status_id' => 3, 'message' => 'Invalid JSON response'];
            }
            if (!isset($responseData->status)) {
                return ['status_id' => 3, 'message' => 'Something went wrong'];
            }
            
            $message = $responseData->message ?? '';
            if ($responseData->status === true) {
                $paysprintbanks = Paysprintbank::where('iinno', $bank_id)->first();
                switch ($responseData->response_code) {
                    case 1:
                        $data = [
                            'bank_name' => $paysprintbanks->bank_name ?? '',
                            'amount' => $responseData->amount,
                            'total_balance' => $responseData->balanceamount,
                            'utr' => $responseData->bankrrn,
                            'aadhar_number' => $maskedAadhaar,
                            'shop_name' => $userDetails->member->shop_name,
                            'message' => 'Transaction successful.!'
                        ];
                        return ['status_id' => 1, 'message' => $message, 'data' => $data];
                    case 0:
                    case 3:
                    case 4:
                        return ['status_id' => 2, 'message' => $message];
                    default:
                        return ['status_id' => 3, 'message' => $message];
                }
            }
            if ($responseData->status === false) {
                return ['status_id' => 2, 'message' => $message];
            }
            return ['status_id' => 3, 'message' => 'Something went wrong'];
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
