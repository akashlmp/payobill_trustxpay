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
use App\Library\BasicLibrary;

use App\Paysprint\Cms as PaysprintCms;
use App\Bankit\Cms as BankitCms;
Use Session;

class AirtelCmsController extends Controller
{
    public function __construct()
    {
        $this->provider_id = 330;
        $this->api_id = 1;
        $this->company = Helpers::company_id();
    }


    function welcome(Request $request)
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            if ($this->company->cms_provider == 2) {
                if (Auth::user()->cms_onboard_status==0) {
                    Session::put('error', "Your onboarding process is pending, please contact to support");
                    return redirect()->back();
                }
                $response = $this->generateBankItUrl();
                if ($response['status'] == "success") {
                    return Redirect::to($response['redirectionUrl']);
                } else {
                    Session::put('error', $response['message']);
                    return redirect()->back();
                }
            } else {
                $data = array('page_title' => 'CMS');
                return view('agent.cms.airtel_cms')->with($data);
            }
        } else {
            return redirect()->back();
        }
    }

    function generateUrl(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $insert_id = Cmsorder::insertGetId([
            'user_id' => Auth::id(),
            'mobile_number' => $mobile_number,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'api_id' => $this->api_id,
            'created_at' => $ctime,
            'status_id' => 3,
        ]);
        $library = new PaysprintCms();
        return $library->generateUrl($mobile_number, $latitude, $longitude, $insert_id);
    }

    function generateBankItUrl()
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $insert_id = Cmsorder::insertGetId([
            'user_id' => Auth::id(),
            'api_id' => 2,
            'created_at' => $ctime,
            'status_id' => 3,
        ]);
        $library = new BankitCms();
        return $library->generateUrl($insert_id);
    }
}
