<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Validator;
use DB;
use Hash;
use App\Models\Apiresponse;
use Log;
use App\IServeU\Dmt as iServeUDmt;

class iServeUDmtCallbackController extends Controller
{
    public function __construct()
    {
        $this->money_provider_id = 316;
        $this->provider_commission_type = 3;
    }

    function callBackRequest(Request $request)
    {
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 3, 'response_type' => 'callBackRequestIserveUDMT']);
        try {
            $library = new iServeUDmt();
            if ($request->status == 200 && isset($request->results) && count($request->results) > 0) {
                Log::info("iServeUDmt callBackRequest if" . json_encode($request->all()));
                return $library->callBackAPi($request->results, $this->money_provider_id);
            } else {
                Log::error("iServeUDmt callBackRequest else" . json_encode($request->all()));
                return ['status' => 1, 'statusDesc' => 'Failure'];
            }
        } catch (\Exception $exception) {
            Log::error("iServeUDmt callBackRequest ===" . $exception->getMessage());
            return ['status' => 1, 'message' => $exception->getMessage(), 'statusDesc' => 'Failure'];
        }
    }
}
