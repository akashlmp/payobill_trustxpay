<?php

namespace App\Easebuzz;

use App\Jobs\StaticQRPayinCallback;
use App\Models\Balance;
use App\Models\MerchantPayoutapiLog;
use Helpers;
use App\Models\Apiresponse;
use App\Models\Api;
use App\Models\Report;
use App\Models\User;
use App\Models\UserStaticQrAccount;
use http\Env\Response;
use App\Library\GetcommissionLibrary;
use App\Library\BasicLibrary;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StaticQr
{
    protected $base_url, $api_id, $provider_id, $api_log;
    public function __construct()
    {
        // $this->base_url = 'https://wire.easebuzz.in';
        $this->base_url = "https://stoplight.io/mocks/easebuzz/neobanking/141702012";
        $this->api_id = 4;
        $this->provider_id = 591;
        $this->api_log = new MerchantPayoutapiLog;
    }

    public function doTransaction($parameters, $credentials_id)
    {
        list($id, $wire_key, $salt_key) =  getCredentials($credentials_id);
        $hashed = $wire_key . '|' . $parameters['label'] . '|' . $salt_key;
        $hashed = hash("sha512", $hashed);
        $method = 'POST';
        $url = $this->base_url . '/api/v1/insta-collect/virtual_accounts/';
        $header = [
            "accept: application/json",
            "Content-Type: application/json",
            "Authorization: $hashed",
            "WIRE-API-KEY: $wire_key"
        ];
        $parameters = json_encode($parameters);

        $request_message = $url . '?' . $parameters;
        $responseCurl = $this->sendCurlPost($url, $header, $parameters, $method);
        $response = json_decode($responseCurl, true);
        Apiresponse::insertGetId(['message' => $responseCurl, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'VirtualAccountStaticQR']);
        if (isset($response) && isset($response['success'])) {
            if ($response['success'] == true) {
                return ['status' => 'success', 'data' => $response['data']];
            } else {
                $message = "Something went wrong. Please try again or later.";
                if (isset($response['message']) && $response['message'] != "") {
                    $message = $response['message'];
                }
                return ['status' => 'failure', 'transaction_id' => "", 'message' => $message, 'request_message' => $request_message, 'api_response' => $responseCurl];
            }
        } else {
            return ['status' => 'failure', 'message' => 'Something went wrong', 'request_message' => $request_message, 'api_response' => $response];
        }
    }

    public function webhookTransactionCreated($request_data)
    {
        $data = $request_data['data'];
        $transaction_id = $data['id'];
        $statusText = $data['status'];
        $virtual_account_id = $data['virtual_account']['id'];
        $resVirtualAccount = UserStaticQrAccount::select('user_id')->where('virtual_account_id', $virtual_account_id)->first();
        // pre($request_data);
        if ($resVirtualAccount) {
            $user_id = $resVirtualAccount['user_id'];
            $userdetails = User::find($user_id);
            list($master_id, $wire_key, $salt_key) =  getCredentials($userdetails->credentials_id);
            $amount = number_format($data['amount'], 2, ".", "");
            $signature = hash('sha512', $wire_key . "|" . $data['unique_transaction_reference'] . "|" . $amount . "|" . $statusText . "|" . $salt_key);
            $Authorization = $data['Authorization'];
            if ($signature != $Authorization) {
                Log::info("Authorization", ['Authorization' => $Authorization, 'signature' => $signature]);
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'message' => "Invalid Transaction"
                ]);
            }

            $description = $data['remitter_full_name'] . " -Static QR- " . $data['payment_mode'];
            $amount = $data['amount'];
            $status = $this->getStatus(status: $statusText);
            $opening_balance = $userdetails->balance->user_balance;

            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $this->provider_id, $amount);
            $charges = $commission['retailer'];
            $increment_amount = $charges;
            $profit = $charges;
            $result = Report::where('txnid', $transaction_id)->where('user_id', $user_id)->first();
            if ($result) {
                $insert_id = $result->id;
                $flag = 0;
                if ($status == 1 && $result->status_id != 1) {
                    Balance::where('user_id', $user_id)->decrement('user_balance', $increment_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $result->status_id = 1;
                    $result->profit = $profit;
                    $result->total_balance = $user_balance;
                    $flag = 1;
                } elseif ($status == 4 && $result->status_id == 1) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $result->decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $result->status_id = $status;
                    $result->profit = 0;
                    $result->total_balance = $user_balance;
                    $flag = 1;
                } elseif ($status == 2) {
                    if ($result->status_id == 1) {
                        Balance::where('user_id', $user_id)->increment('user_balance', $result->decrementAmount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;
                        $result->total_balance = $user_balance;
                    }
                    $result->status_id = $status;
                    $result->profit = 0;
                    $flag = 1;
                }
                if ($userdetails->callback_url && $flag == 1) {
                    if ($opening_balance >= 0) {
                        StaticQRPayinCallback::dispatch($result->reference_id);
                    } else {
                        Log::info("Law balance", ['balance' => $opening_balance, 'reference_id' => $result->reference_id, "Merchant ID" => $userdetails->id]);
                    }
                }
                $result->save();
            } else {
                $reference_id = Helpers::generateReferenceID();
                $created_at = date("Y-m-d H:i:s");
                $insert_id = Report::insertGetId([
                    'number' => $data['remitter_phone_number'],
                    'provider_id' => $this->provider_id,
                    'amount' => $data['amount'],
                    'api_id' => $this->api_id,
                    'status_id' => $status,
                    'created_at' => $created_at,
                    'user_id' => $user_id,
                    'profit' => 0,
                    'mode' => "API",
                    'txnid' => $transaction_id,
                    'description' => $description,
                    'op_ref_no' => $data['unique_transaction_reference'],
                    'reference_id' => $reference_id,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $opening_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 1,
                    'provider_api_from' => $this->api_id,
                    'decrementAmount' => $increment_amount,
                    'provider_credential_id' => $master_id
                ]);

                $input = array();
                $input['reference_id'] = $reference_id;
                $input['merchant_reference_id'] = NULL;
                $input['type'] = 1;
                $input['mode'] = 1;
                $input['merchant_user_id'] = $user_id;

                $statuses = DB::table('statuses')
                    ->where('id', $status)
                    ->value('status');

                $return_array = [
                    'success' => true,
                    'status' => Str::upper($statuses),
                    'message' => "",
                    'merchant_reference_id' => $input['merchant_reference_id'],
                    'reference_id' => $input['reference_id'],
                    'customer_name' => $data['remitter_full_name'],
                    'customer_phone' => $data['remitter_phone_number'],
                    'customer_email' => "",
                    'amount' =>  (float) $amount,
                    'timestamp' =>  $created_at,
                ];

                $this->api_log->storeData($input, $return_array);

                if ($status == 1) {
                    Balance::where('user_id', $user_id)->decrement('user_balance', $increment_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => $status, 'profit' => $profit, 'total_balance' => $user_balance]);
                }

                if ($userdetails->callback_url) {
                    if ($opening_balance >= 0) {
                        StaticQRPayinCallback::dispatch($reference_id);
                    } else {
                        Log::info("Law balance", ['balance' => $opening_balance, 'reference_id' => $reference_id, "Merchant ID" => $userdetails->id]);
                    }
                }
            }
        }

        return ['status' => 200];
    }

    public function getStatus($status)
    {
        switch ($status) {
            case 'unsettled':
            case 'received':
            case 'timed_out';
                return 1;
            case 'refunded':
            case 'partially_refunded':
                return 4;
            case 'failure':
                return 2;
            case 'pending':
                return 3;
            default:
                return "";
        }
    }

    public function changeVirtualAccountStatus($virtual_account_id, $status, $credentials_id)
    {
        list($master_id, $wire_key, $salt_key) =  getCredentials($credentials_id);
        $hashed = $wire_key . '|' . $virtual_account_id . '|' . $salt_key;
        $hashed = hash("sha512", $hashed);
        $method = 'POST';
        $url = $this->base_url . "/api/v1/insta-collect/virtual_accounts/".$virtual_account_id."/update_status/";
        $header = [
            "accept: application/json",
            "Content-Type: application/json",
            "Authorization: $hashed",
            "WIRE-API-KEY: $wire_key",
            "virtual_account_id: $virtual_account_id"
        ];

        if ($status == 1) {
            $status = true;
        } else {
            $status = false;
        }

        $parameters =  ['key' => $wire_key, "is_active" => $status];
        $parameters = json_encode($parameters);

        $request_message = $url . '?' . $parameters;
        $responseCurl = $this->sendCurlPost($url, $header, $parameters, $method);
        $response = json_decode($responseCurl, true);
        Apiresponse::insertGetId(['message' => $responseCurl, 'api_type' => $this->api_id, 'request_message' => $request_message, 'response_type' => 'VirtualAccountStaticQRStatusChange']);
        if (isset($response) && isset($response['success'])) {
            if ($response['success'] == true) {
                return ['status' => 'success', 'data' => ""];
            } else {
                $message = "Something went wrong. Please try again or later.";
                if (isset($response['message']) && $response['message'] != "") {
                    $message = $response['message'];
                }
                return ['status' => 'failure', 'message' => $message];
            }
        } else {
            return ['status' => 'failure', 'message' => 'Something went wrong'];
        }
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
