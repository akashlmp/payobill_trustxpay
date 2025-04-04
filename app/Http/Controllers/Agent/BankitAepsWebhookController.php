<?php

namespace App\Http\Controllers\Agent;

use App\Bankit\Aeps;
use App\Http\Controllers\Controller;
use App\Models\Apiresponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankitAepsWebhookController extends Controller
{
    public function webhookPostRequest(Request $request)
    {
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 2]);
        try {
            $bankit_aeps = new Aeps();
            return $bankit_aeps->webhookCallback($request);
        } catch (\Exception $e) {
            Log::error('AEPS Webhook post request error : ' . $e->getMessage());
            return response()->json(['Status' => 1, 'Description' => $e->getMessage()]);
        }
    }
}
