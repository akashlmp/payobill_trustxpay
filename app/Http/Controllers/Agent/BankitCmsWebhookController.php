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
use App\Library\BasicLibrary;
use Log;
use App\Bankit\Cms as BankitCms;

class BankitCmsWebhookController extends Controller
{

    function webhookPreRequest(Request $request)
    {
        Log::info("webhookPreRequest===",['data'=>json_encode($request->all())]);
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 2]);
        $request = $request->all();
        try {
            $library = new BankitCms();
            return $library->CmsBalanceDebit($request);
        } catch (\Exception $e) {
            Log::error("webhookPreRequest===" . $e->getMessage());
            return ['status' => 401, 'message' => $e->getMessage()];
        }
    }
    function webhookPostRequest(Request $request)
    {
        Log::info("webhookPostRequest===",['data'=>json_encode($request->all())]);
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 2]);
        $request = $request->all();
        try {
            $library = new BankitCms();
            return $library->CmsPosting($request);
        } catch (\Exception $e) {
            Log::error("webhookPostRequest===" . $e->getMessage());
            return ['status' => 401, 'message' => $e->getMessage()];
        }
    }
}
