<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use DB;
use Hash;
use App\Models\Apiresponse;
use Log;
use App\Bankit\Dmt as BankitDmt;

class BankitDmtCallbackController extends Controller
{
    public function __construct()
    {
        $this->money_provider_id = 316;
        $this->provider_commission_type = 2;
    }
    function callBackRequest(Request $request)
    {
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 2]);
        try {
            $library = new BankitDmt();
            return $library->callBackAPi($request,$this->money_provider_id);
        } catch (\Exception $exception) {
            Log::error("DMT callBackRequest ===" . $exception->getMessage());
            return ['status' => 401, 'message' => $exception->getMessage()];
        }
    }
}
