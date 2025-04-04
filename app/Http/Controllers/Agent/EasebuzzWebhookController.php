<?php

namespace App\Http\Controllers\Agent;

use DB;
use Log;
use Hash;
use Validator;
use App\Easebuzz\DynamicQr as EasebuzzDynamicQr;
use App\Easebuzz\StaticQr;

use App\Models\Apiresponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EasebuzzWebhookController extends Controller
{
    public function __construct()
    {
        $this->money_provider_id = 316;
        $this->provider_commission_type = 3;
    }

    public function webhookPostRequest(Request $request)
    {
        $callback_data = $request->all();
        $event = "";
        if (isset($callback_data['event'])) {
            $event = $callback_data['event'];
        }
        Apiresponse::insertGetId(['message' => json_encode($callback_data), 'api_type' => 4, 'response_type' => 'easebuzzCallback'.$event]);

        if (isset($callback_data['event'])) {
            if ($callback_data['event'] == 'TRANSACTION_CREDIT') {
                $easebuzz = new StaticQr();
                $return_data = $easebuzz->webhookTransactionCreated($callback_data);
            } elseif ($callback_data['event'] == 'ORDER_CREATED') {
                $easebuzz = new EasebuzzDynamicQr();
                $return_data = [];
                //$return_data = $easebuzz->webhookOrderCreated($callback_data);
            } elseif ($callback_data['event'] == 'ORDER_STATUS_UPDATE') {
                $easebuzz = new EasebuzzDynamicQr();
                $return_data = $easebuzz->webhookStatusUpdate($callback_data);
            } else {
                // nothing to do
                $return_data = [];
            }

            return $return_data;
        }
    }
}
