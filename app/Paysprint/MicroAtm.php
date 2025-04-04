<?php

namespace App\Paysprint {

    use App\Models\Apiresponse;
    use App\Models\User;
    use App\Models\Matmtransaction;
    use App\Models\Report;
    use App\Models\Provider;
    use App\Models\Balance;
    use App\Models\Aepsreport;
    use GuzzleHttp\Psr7\Response;
    use Illuminate\Support\Facades\Auth;

    // library here
    use App\Library\Commission_increment;
    use App\Library\GetcommissionLibrary;

    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class MicroAtm
    {

        public function __construct()
        {
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
            $this->service_id = 31;
        }


        function getDetails($insert_id)
        {
            return $data = array(
                'partnerId' => $this->partner_id,
                'apiKey' => $this->api_key,
                'merchantCode' => Auth::User()->paysprint_merchantcode,
                'referenceNumber' => $insert_id,
                'subMerchantId' => Auth::User()->paysprint_merchantcode,
            );
        }


        function withdrawalTransaction($param)
        {
            $txnReferenceNo = $param['txnrefrenceNo'] ?? null;
            if (!$txnReferenceNo) {
                return json_encode(['status' => 401, 'message' => 'Transaction reference number is missing.']);
            }
            $matmTransaction = Matmtransaction::find($txnReferenceNo);

            if (!$matmTransaction) {
                return json_encode(['status' => 401, 'message' => 'Invalid transaction reference number.']);
            }
            Matmtransaction::where('id', $txnReferenceNo)->update([
                'apiresponse' => json_encode($param)
            ]);
            $response = $this->threewayUpdate($txnReferenceNo);
            $status_id = $response['status_id'];
            if ($status_id == 1) {
                if ($param['status'] == true && $param['txnstatus'] == 1) {
                    $user_id = $matmTransaction->user_id;
                    $txnid = $param['bankrrn'];
                    $reports = Report::where('txnid', $txnid)->first();
                    if ($reports) {
                        return json_encode(['status' => 401, 'message' => 'Dupplicate request!']);
                    }
                    $userDetails = User::find($user_id);
                    $opening_balance = $userDetails->balance->aeps_balance;
                    $scheme_id = $userDetails->scheme_id;
                    $amount = $param['amount'];
                    $provider_id = 322;

                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                    $retailer = $commission['retailer'];
                    $d = $commission['distributor'];
                    $sd = $commission['sdistributor'];
                    $st = $commission['sales_team'];
                    $rf = $commission['referral'];
                    $incrementAmount = $amount + $retailer;
                    Balance::where('user_id', $user_id)->increment('aeps_balance', $incrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $total_balance = $balance->aeps_balance;
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $cardnumber = $param['cardnumber'];
                    $description = $param['message'];
                    $api_id = $this->api_id;
                    $insert_id = Report::insertGetId([
                        'number' => $cardnumber,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $api_id,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => $retailer,
                        'mode' => "APP",
                        'txnid' => $txnid,
                        'ip_address' => '',
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $total_balance,
                        'wallet_type' => 2,
                    ]);
                    Aepsreport::insertGetId([
                        'aadhar_number' => $cardnumber,
                        'bank_name' => '',
                        'created_at' => $ctime,
                        'report_id' => $insert_id,
                    ]);
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $cardnumber, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                    // get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                    return json_encode(['status' => 200, 'message' => 'Successful.!']);
                } else {
                    return json_encode(['status' => 401, 'message' => 'Invalid response']);
                }
            } else {
                return json_encode(['status' => 401, 'message' => $response['message']]);
            }
        }

        private function threewayUpdate($txnReferenceNo)
        {
            $dataPost = [
                'reference' => $txnReferenceNo,
                'status' => 'success',
            ];
            $encryptedData = $this->encryptData($dataPost);
            $apiRequestParameters = json_encode(['body' => $encryptedData]);
            $url = $this->base_url . "api/v1/service/matm/threeway/update";
            $token = $this->generateToken();
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                //"Authorisedkey: $this->authorised_key"
            ];
            $response = $this->sendCurlPost($url, $headers, $apiRequestParameters, 'POST');
           // $response = '{"status":true,"message":"Transaction Marked as success","response_code":1}';
            Matmtransaction::where('id', $txnReferenceNo)->update(['threeway_response' => $response]);
            $res = json_decode($response);
            if (isset($res->status)) {
                if ($res->status == true && $res->response_code == 1) {
                    return ['status_id' => 1, 'message' => 'Success'];
                } else {
                    return ['status_id' => 2, 'message' => $res->message];
                }
            } else {
                return ['status_id' => 2, 'message' => 'failed'];
            }
        }

        private function encryptData(array $data)
        {
            $ciphertextRaw = openssl_encrypt(
                json_encode($data),
                'AES-128-CBC',
                $this->key,
                OPENSSL_RAW_DATA,
                $this->iv
            );
            return base64_encode($ciphertextRaw);
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
