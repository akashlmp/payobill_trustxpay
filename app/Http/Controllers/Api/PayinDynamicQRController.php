<?php

namespace App\Http\Controllers\Api;

use Helpers;
use App\Traits\Authenticate;
use App\Models\MerchantPayoutapiLog;
use App\Models\Apicommission;
use App\Http\Controllers\Controller;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Easebuzz\DynamicQr;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayinDynamicQRController extends Controller
{
    use Authenticate;

    public function __construct()
    {
        $this->api_log = new MerchantPayoutapiLog;
        $this->merchant_api_commissions = new Apicommission;
        $this->easebuzz_dynamic_qr = new DynamicQr;
        $this->provider_id = 592;
        $this->api_id = 4;
    }

    public function store(Request $request)
    {
        $input = $request->only(['customer_name', 'customer_phone', 'customer_email', 'amount', 'merchant_reference_id', 'signature']);
        $input['mode'] = 1; // 1=live
        $input['type'] = 1; // api

        $return = $this->keyAuthentication($request);

        if($return->getData()->success == false) {
            $this->api_log->storeData($input, json_encode($return->getData(), true));
            return $return;
        }

        $api_key = $request->bearerToken();
        $user = User::where('api_key', $api_key)->first();
        $input['merchant_user_id'] = $user->id;
        list($master_id, $wire_key, $salt_key) = getCredentials($user->credentials_id);

        $validator = Validator::make($input, [
            'customer_name' => 'required|min:2|max:100',
            'customer_phone' => 'required|min:5|max:20',
            'amount' => 'required|regex:/^\d+(\.\d{1,9})?$/',
            'merchant_reference_id' => 'required|min:3|max:100|alpha_dash:ascii',
            'customer_email' => 'required|email',
            'signature' => 'required|min:2|max:100'
        ]);

        if ($validator->fails()) {
            $response_data = [
                'success' => false,
                'message' => 'Validation error, please check errors parameter.',
                'errors' => $validator->errors()
            ];

            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }

        $signature = hash('sha256', $input['customer_name'].number_format((float)$input['amount'], 2, '.', '').$input['customer_email'].$input['merchant_reference_id'].$user->secrete_key);

        if ($signature !== $input['signature']) {
            $response_data = [
                'success' => false,
                'message' => 'Signature not verified.'
            ];

            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }

        $order_id_exists = Report::where('merchant_reference_id', $input['merchant_reference_id'])
            ->where('user_id', $user->id)
            ->exists();

        if ($order_id_exists) {
            $errors = [];
            $errors['merchant_reference_id'][0] = 'Duplicate merchant_reference_id, field must be unique.';

            $response_data = [
                'success' => false,
                'message' => 'Validation error, please check errors parameter.',
                'errors' => $errors
            ];

            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }

        DB::beginTransaction();
        try {
            $input['reference_id'] = Helpers::generateReferenceID();
            $input['created_at'] = date('Y-m-d H:i:s');

            $parameters = [
                'key' => $wire_key,
                'unique_request_number' => $input['reference_id'],
                'customer_name' => $input['customer_name'],
                'customer_phone' => $input['customer_phone'],
                'customer_email' => $input['customer_email'],
                'amount' => $input['amount'],
                'allowed_collection_modes' => ['bank_account', 'upi'],
            ];

            $authentication = hash('sha512',
                $parameters['key'].'|'.
                $parameters['unique_request_number'].'|'.
                $parameters['amount'].'|'.
                '|'. // per_transaction_amount
                '|'. // udf1
                '|'. // udf2
                '|'. // udf3
                '|'. // udf4
                '|'. // udf5
                $salt_key
            );

            $response = $this->easebuzz_dynamic_qr->doTransaction($parameters, $authentication);

            if (isset($response['payment_link']) && !empty($response['payment_link'])) {
                $input['payment_link'] = $response['payment_link'];
            }
            $input['message'] = $response['message'];
            $input['api_response'] = $response['api_response'] ?? '{}';
            $input['provider_order_id'] = $response['provider_order_id'] ?? null;

            if (isset($response['status']) && $response['status'] == 'pending') {
                $input['status_id'] = 3;
            } elseif (isset($response['status']) && $response['status'] == 'success') {
                $input['status_id'] = 1;
            } else {
                $input['status_id'] = 2;
            }

            Report::insert([
                'user_id' => $user->id,
                'provider_id' => $this->provider_id,
                'provider_api_from' => $this->api_id,
                'number' => $input['customer_phone'],
                'amount' => $input['amount'],
                'api_id' => $this->api_id,
                'ip_address' => request()->ip(),
                'created_at' => $input['created_at'],
                'updated_at' => $input['created_at'],
                'provider_order_id' => $response['provider_order_id'],
                'reference_id' => $input['reference_id'],
                'merchant_reference_id' => $input['merchant_reference_id'],
                'opening_balance' => $user->balance->user_balance,
                'total_balance' => $user->balance->user_balance,
                'mode' => "API",
                'description' => $input['customer_name'] . " -Dynamic QR",
                'credit_by' => $user->id,
                'status_id' => $input['status_id'],
                'failure_reason' => $input['message'],
                'wallet_type' => 1,
                'decrementAmount' => 0,
                'profit' => 0,
                'provider_credential_id' => $user->credentials_id
            ]);

            DB::commit();
            return response()->json($this->getResponseArray($input));
        } catch (\Exception $e) {
            DB::rollback();
            \Log::info($e);

            $response_data = [
                'success' => false,
                'message' => 'Server error, please contact trustxpay support',
            ];

            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }
    }

    public function status(Request $request)
    {
        $input = $request->only(['reference_id', 'merchant_reference_id']);
        $input['mode'] = 1; // 1=live
        $input['type'] = 2; // 2=status

        $return = $this->keyAuthentication($request, 0);

        if($return->getData()->success == false){
            $this->api_log->storeData($input, json_encode($return->getData(), true));
            return $return;
        }

        $api_key = $request->bearerToken();
        $user = User::where('api_key', $api_key)->first();
        $input['merchant_user_id'] = $user->id;

        $validator = Validator::make($input, [
            'merchant_reference_id' => 'required_without:reference_id|min:3|max:100',
            'reference_id' => 'required_without:merchant_reference_id|min:10|max:100',
        ]);

        if ($validator->fails()) {
            $response_data = [
                'success' => false,
                'message' => 'Validation error, please check errors parameter.',
                'errors' => $validator->errors()
            ];

            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }

        $report = Report::select('*');
            if (isset($input['reference_id']) && !empty($input['reference_id'])) {
                $report = $report->where('reference_id', $input['reference_id']);
            }
            if (isset($input['merchant_reference_id']) && !empty($input['merchant_reference_id'])) {
                $report = $report->where('merchant_reference_id', $input['merchant_reference_id']);
            }
            $report = $report->first();

        if (empty($report)) {
            $response_data = [
                'success' => false,
                'message' => 'Invalid details provided.'
            ];
            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }

        $merchant_api_log = MerchantPayoutapiLog::where('reference_id', $report['reference_id'])
            ->where('type', 1)
            ->first();

        if (empty($merchant_api_log)) {
            $response_data = [
                'success' => false,
                'message' => 'Invalid details provided.'
            ];
            $this->api_log->storeData($input, $response_data);
            return response()->json($response_data);
        }

        $merchant_api_response = json_decode($merchant_api_log['response'], true);

        $merchant = User::select('secrete_key', 'callback_url')
            ->where('id', $report->user_id)
            ->first();

        $input = [
            'status_id' => $report['status_id'],
            'message' => $report['failure_reason'],
            'merchant_reference_id' => $report['merchant_reference_id'],
            'reference_id' => $report['reference_id'],
            'customer_name' => $merchant_api_response['customer_name'],
            'customer_phone' => $merchant_api_response['customer_phone'],
            'customer_email' => $merchant_api_response['customer_email'],
            'amount' =>  (float) $merchant_api_response['amount'],
            'created_at' =>  $merchant_api_response['timestamp'],
            'type' => $input['type'],
            'mode' => $input['mode'],
        ];

        $return_data = $this->getResponseArray($input);

        return response()->json($return_data);
    }

    public function getResponseArray($input)
    {
        $status = \DB::table('statuses')
            ->where('id', $input['status_id'])
            ->value('status');

        $return_array = [
            'success' => true,
            'status' => Str::upper($status),
            'message' => $input['message'],
            'merchant_reference_id' => $input['merchant_reference_id'],
            'reference_id' => $input['reference_id'],
            'customer_name' => $input['customer_name'],
            'customer_phone' => $input['customer_phone'],
            'customer_email' => $input['customer_email'],
            'amount' =>  (float) $input['amount'],
            'timestamp' =>  $input['created_at'],
        ];

        if (
            isset($return_array['status']) && $return_array['status'] == 'PENDING' &&
            isset($input['payment_link']) && !empty($input['payment_link'])
        ) {
            $return_array['payment_link'] = $input['payment_link'];

            $deeplink = parse_url($input['payment_link']);
            parse_str($deeplink['query'], $deeplink_query);

            if (isset($deeplink_query['upi_string']) && !empty($deeplink_query['upi_string'])) {
                $input['upi_intent'] = $deeplink_query['upi_string'];

                parse_str($input['upi_intent'], $upi_query);

                if (!(isset($upi_query['tn']) && !empty($upi_query['tn']))) {
                    $upi_query['tn'] = 'Payment to ' . $upi_query['pn'] ?? 'Trustxpay';
                }

                if (!(isset($upi_query['tr']) && !empty($upi_query['tr']))) {
                    $upi_query['tr'] = $input['merchant_reference_id'];
                }
                $return_array['upi_intent'] = urldecode(http_build_query($upi_query));
            }
        }

        $this->api_log->storeData($input, $return_array);

        return $return_array;
    }

    public function sendCurlPost($url, $header, $parameters, $method = 'POST')
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
