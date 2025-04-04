<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\State;
use App\Models\District;
use App\Models\MerchantUsers;
use Validator;
use Illuminate\Support\Facades\Auth;
use Hash;
use App\Library\SmsLibrary;
use Carbon\Carbon;
use Str;

class ProfileController extends Controller
{

    function my_profile()
    {
        $data = array('page_title' => 'My Profile');
        $roles = Role::get();
        $circles = State::where('status_id', 1)->get();
        $district = District::get();
        return view('merchant.my_profile', compact('roles', 'circles', 'district'))->with($data);
    }

    function change_password(Request $request)
    {
        $rules = array(
            'new_password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $new_password = $request->new_password;
        $userdetail = MerchantUsers::find(Auth::guard('merchant')->user()->id);
        $current_password = $userdetail->password;

        // if (Hash::check($request->new_password, $userdetail->transaction_password)) {
        //     return Response()->json(['status' => 'failure', 'message' => 'Login password and transaction password can not same']);
        // }
        if ($request->type == 1) {
            $otp = mt_rand(100000, 999999);
            $message = $otp . " is your OTP. Use this to change your password. Valid for 3 minutes. Don't share this OTP with anyone. For more info: trustxpay.org PYOBIL";
            $template_id = 23;
            $whatsappArr = [$otp];
            $library = new SmsLibrary();
            $mobile = $userdetail->mobile_number;
            MerchantUsers::where('id', $userdetail->id)->update(['otp' => $otp]);
            $sms = $library->send_sms($mobile, $message, $template_id, $whatsappArr);
            $mobile = substr($mobile, 0, 1) . '*******' . substr($mobile, -2);
            $alertMessage = "Enter the 6-digit OTP sent on +91 $mobile";
            return Response()->json(['status' => 'success', 'message' => $alertMessage]);
        } else {
            $password_otp = $request->password_otp;
            if ($password_otp == $userdetail->otp) {
                $userdetail->password = Hash::make($new_password);
                $userdetail->password_changed_at = Carbon::now()->toDateTimeString();
                $userdetail->save();
                \Session::flush();
                return Response()->json(['status' => 'success', 'message' => 'Password successfully changed']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
            }
        }
    }


    function my_settings()
    {
        $data = array('page_title' => 'Settings');
        return view('merchant.my_settings')->with($data);
    }

    function save_settings(Request $request)
    {
        $rules = array(
            'api_key' => 'required',
            'secrete_key' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $api_key = $request->api_key;
        $secrete_key = $request->secrete_key;
        MerchantUsers::where('id', Auth::guard('merchant')->user()->id)->update(['api_key' => $api_key, 'secrete_key' => $secrete_key]);
        return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
    }

    function regenerate_keys(Request $request)
    {
        // dd("regenerate_keys");
        $api_key = Str::random(36);
        $secrete_key = Str::random(16);

        MerchantUsers::where('id', Auth::guard('merchant')->user()->id)->update(['api_key' => $api_key, 'secrete_key' => $secrete_key]);
        return Response()->json(['status' => 'success', 'message' => 'Regenerated successfully..!']);
    }
}
