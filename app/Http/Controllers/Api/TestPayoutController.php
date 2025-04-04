<?php

namespace App\Http\Controllers\Api;

use Helpers;
use App\Jobs\PendingPayoutToComplete;
use App\Models\MerchantPayoutapiLog;
use App\Models\MerchantTestTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TestPayoutController extends Controller
{
    protected $merchant_transaction;

    public function __construct()
    {
        $this->api_log = new MerchantPayoutapiLog;
        $this->merchant_transaction = new MerchantTestTransaction;
    }

    public function store(Request $request)
    {
        $input = $request->only(['ben_name', 'ben_account_number', 'ben_ifsc', 'ben_phone_number', 'ben_bank_name', 'amount', 'ip_address', 'merchant_reference_id', 'transfer_type', 'signature']);

        $api_key = $request->bearerToken();

        // if api_key is not included in request
        if (empty($api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found.'
            ]);
        }

        $user = DB::table('merchant')
            ->where('api_key', $api_key)
            ->first();

        // if invalid api_key provided
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key provided.'
            ]);
        }

        // if merchant disabled
        if ($user->status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant account disabled.'
            ]);
        }

        // if merchant server not whitelisted
        if ($user->is_ip_whiltelist == 1) {
            $ips = json_decode($user->server_ip, true);
            if (empty($ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP(' . \Request::ip() . ') not added to whitelist.'
                ]);
            }
            if (!in_array(\Request::ip(), $ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP(' . \Request::ip() . ') not whitelisted.'
                ]);
            }
        }

        $validator = Validator::make($input, [
            'ben_name' => 'required|min:2|max:100',
            'ben_account_number' => 'required|string|min:10|max:14',
            'ben_ifsc' => 'required|min:2|max:100',
            'ben_phone_number' => 'required|min:5|max:20',
            'ben_bank_name' => 'required|min:2|max:100',
            'amount' => 'required|regex:/^\d+(\.\d{1,9})?$/',
            'ip_address' => 'required|ip',
            'merchant_reference_id' => 'required|min:3|max:100|alpha_dash:ascii',
            'transfer_type' => 'required|in:IMPS,NEFT',
            'signature' => 'required|min:2|max:100'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'success' => false,
                'message' => 'Validation error, please check errors paramter.',
                'errors' => $errors
            ]);
        }

        $signature = hash('sha256', $input['ben_account_number'].$input['amount'].$input['ben_ifsc'].$input['merchant_reference_id'].$user->secrete_key);

        if ($signature !== $input['signature']) {
            return response()->json([
                'success' => false,
                'message' => 'Signature not verified.'
            ]);
        }

        if (isset($input['merchant_reference_id']) && !empty($input['merchant_reference_id'])) {
            $order_id_exists = MerchantTestTransaction::where('merchant_reference_id', $input['merchant_reference_id'])
                ->where('merchant_id', $user->id)
                ->exists();

            if ($order_id_exists) {
                $errors = [];
                $errors['merchant_reference_id'][0] = 'Duplicate merchant_reference_id, field must be unique.';

                return response()->json([
                    'success' => false,
                    'message' => 'Validation error, please check errors paramter.',
                    'errors' => $errors
                ]);
            }
        }

        $input['reference_id'] = Helpers::generateReferenceID();
        $input['created_at'] = date('Y-m-d H:i:s');

        $response = $this->sendCurlPost($input);

        $input['message'] = $response['message'] ?? 'Payout Successfully received.';
        $input['api_response'] = $response['api_response'] ?? '{}';
        $input['transaction_id'] = $response['transaction_id'] ?? null;
        $input['utr'] = $response['payid'] ?? null;
        $input['mode'] = 0; // 0=test
        $input['type'] = 1; // 1=api

        if (isset($response['status']) && $response['status'] == 'success') {
            $input['status_id'] = 1;
            $input['status'] = 1;
        } elseif (isset($response['status']) && $response['status'] == 'pending') {
            $input['status_id'] = 3;
            $input['status'] = 0;
        } else {
            $input['status_id'] = 2;
            $input['status'] = 2;
        }

        $transaction_data = [
            'merchant_id' => $user->id,
            'account_number' => $input['ben_account_number'],
            'merchant_reference_id' => $input['merchant_reference_id'],
            'reference_id' => $input['reference_id'],
            'transaction_id' => $input['transaction_id'],
            'ben_name' => $input['ben_name'],
            'ben_ifsc' => $input['ben_ifsc'],
            'ben_phone_number' => $input['ben_phone_number'],
            'ben_bank_name' => $input['ben_bank_name'],
            'utr' => $input['utr'],
            'amount' => $input['amount'],
            'mode' => $input['transfer_type'],
            'status_id' => $input['status_id'],
            'failure_reason' => $input['message'],
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'ip_address' => $user->merchant_ip,
            'created_at' => $input['created_at'],
        ];

        PendingPayoutToComplete::dispatch($input['reference_id'])
                ->delay(now()->addMinutes(1));

        $transaction = $this->merchant_transaction->storeData($transaction_data);

        return response()->json($this->getResponseArray($input, $user));
    }

    public function status(Request $request)
    {
        $input = $request->only(['reference_id', 'merchant_reference_id']);

        $api_key = $request->bearerToken();

        // if api_key is not included in request
        if (empty($api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found.'
            ]);
        }

        $user = DB::table('merchant')
            ->select('id', 'status', 'is_ip_whiltelist','server_ip')
            ->where('api_key', $api_key)
            ->first();

        // if invalid api_key provided
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key provided.'
            ]);
        }

        $input['merchant_id'] = $user->id;

        // if merchant disabled
        if ($user->status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant account disabled.'
            ]);
        }

        // if merchant server not whitelisted
        if ($user->is_ip_whiltelist == 1) {
            $ips = json_decode($user->server_ip, true);
            if (empty($ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP(' . \Request::ip() . ') not added to whitelist.'
                ]);
            }
            if (!in_array(\Request::ip(), $ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP(' . \Request::ip() . ') not whitelisted.'
                ]);
            }
        }

        $validator = Validator::make($input, [
            'merchant_reference_id' => 'required_without:reference_id|min:3|max:100',
            'reference_id' => 'required_without:merchant_reference_id|min:10|max:100',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'success' => false,
                'message' => 'Validation error, please check errors paramter.',
                'errors' => $errors
            ]);
        }

        $transaction = $this->merchant_transaction->getApiData($input);

        if (empty($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid details provided.'
            ]);
        }

        return response()->json($this->getResponseArray($transaction));
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
            'transaction_id' => $input['reference_id'],
            'merchant_reference_id' => $input['merchant_reference_id'],
            'ben_name' => $input['ben_name'],
            'ben_account_number' => $input['ben_account_number'],
            'ben_ifsc' => $input['ben_ifsc'],
            'ben_phone_number' => (int) $input['ben_phone_number'],
            'ben_bank_name' => $input['ben_bank_name'],
            'transfer_type' => $input['transfer_type'],
            'amount' =>  (float) $input['amount'],
            'timestamp' =>  $input['created_at']
        ];

        if (isset($input['utr']) && !empty($input['utr']) && $input['status_id'] == 1) {
            $return_array['utr'] = $input['utr'];
        }

        $this->api_log->storeData($input, $return_array);

        return $return_array;
    }

    public function sendCurlPost($input)
    {
        $transaction_id = 'TRN'.Str::upper(Str::random(4)).time().Str::upper(Str::random(2));

        if ($input['ben_account_number'] == '12345678901234') {
            return [
                'status' => 'success',
                'message' => 'Payout Successfully received.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } elseif ($input['ben_account_number'] == '11112222333344') {
            return [
                'status' => 'pending',
                'message' => 'Payout pending.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } elseif ($input['ben_account_number'] == '10002000300040') {
            return [
                'status' => 'pending',
                'message' => 'Payout pending.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } elseif ($input['ben_account_number'] == '50004000300021') {
            return [
                'status' => 'failed',
                'message' => 'Payout request failed.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => 'Payout request failed.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        }
    }
}
