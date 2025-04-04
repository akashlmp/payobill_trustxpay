<?php

namespace App\Traits;

use App\Models\Transaction;
use App\Models\TransactionSession;
use App\Models\TransactionPspLog;
use App\Models\User;
use App\Transformers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait Authenticate
{
    public function keyAuthentication($request, $is_wallet_check = 1)
    {
        $api_key = $request->bearerToken();

        // if api_key is not included in request
        if (empty($api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found.'
            ]);
        }

        $user = User::where('api_key', $api_key)
            ->first();

        // if invalid api_key provided
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key provided.'
            ]);
        }

        // if merchant disabled
        if ($user->status_id == 0) {
            return response()->json([
                'success' => false,
                'message' => 'User account disabled.'
            ]);
        }

        if ($is_wallet_check == 1) {
            // if provider not assigned
            if (!$user->credentials_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not assigned, please contact admin.'
                ]);
            }

            // if provider not assigned
            if ($user->balance->user_balance <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your wallet balance is low, kindly refill your wallet.'
                ]);
            }
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

        return response()->json([
            'success' => true
        ]);
    }
}
