<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use DB;
use Hash;
use Helpers;
use App\Models\Cmsorder;
use App\Models\Apiresponse;
use App\Models\User;
use App\Library\BasicLibrary;
use App\Paysprint\Cms as PaysprintCms;
use App\Paysprint\MicroAtm as MicroAtm;

class PaysprintWebhookController extends Controller
{


    function webhookRequest(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1]);
        $event = $request->event;
        if ($event == 'CMS_BALANCE_INQUIRY') {
            $param = $request->param;
            $library = new PaysprintCms();
            return $library->CmsBalanceInquiry($param);
        } elseif ($event == 'CMS_BALANCE_DEBIT') {
            $param = $request->param;
            $library = new PaysprintCms();
            return $library->CmsBalanceDebit($param);
        } elseif ($event == 'CMS_LOW_BALANCE_INQUIRY') {
            $library = new PaysprintCms();
            return $library->CmsLowBalanceInquiry();
        } elseif ($event == 'CMS_POSTING') {
            $param = $request->param;
            $library = new PaysprintCms();
            return $library->CmsPosting($param);
        } elseif ($event == 'PAYOUT_SETTLEMENT') {
            $param = $request->param;
            $library = new PaysprintCms();
            return $library->payoutSettlement($param);
        } elseif ($event == 'MATM') {
            $param = $request->param;
            $library = new MicroAtm();
            return $library->withdrawalTransaction($param);
        }elseif ($event == 'MERCHANT_ONBOARDING') {
            $param = $request->param;
            // $merchant_id = $param->merchant_id;
            $merchant_id = $param['merchant_id'];
            $users = User::where('paysprint_merchantcode', $merchant_id)->first();
            if ($users) {
                return '{"status":200,"message":"Transaction completed successfully"}';
            }else{
                return '{"status":401,"message":"invalid merchant id"}';
            }

        }
    }
}
