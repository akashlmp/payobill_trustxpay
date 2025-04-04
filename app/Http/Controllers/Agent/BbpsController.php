<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BbpsController extends Controller
{
    public function welcome()
    {

        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $id = Auth::id();
        $token = \Helpers::customEncrypt($id);
        $url = env('BBPS_URL') ."/agent/welcome/$token";
        return redirect($url);
        $params['page_title'] = 'BBPS Recharge & Bill Payment';
        $params['categories'] = DB::table('categories_bbps')->where('is_active', 1)->pluck('name', 'id')->toArray();
        $params['postpaid_provider'] = Provider::where('category_id',22)->pluck('provider_name', 'operator_id')->toArray();
        $params['prepaid_provider'] = Provider::where('category_id',23)->pluck('provider_name', 'operator_id')->toArray();
        $params['circles'] = config('bbps.circles');
        return view('agent.bbps.index', $params);
    }

    public function getServiceDetail(Request $request)
    {
        dd($request->all());
    }
}
