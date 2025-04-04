<?php

namespace App\Http\Controllers\Merchant\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MerchantUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Library\SmsLibrary;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::guard('merchant')->check()) {
            return redirect()->route('merchant.transaction');
        }
        return view('merchant.auth.login');
    }

    public function login_now(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:merchant,mobile_number|digits:10',
            'password' => 'required',
            'company_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $username = $request->username;
        $password = $request->password;
        $company_id = $request->company_id;
        $request_ip = request()->ip();
        $companies = Company::find($company_id);
        if ($companies) {
            $userDetails = MerchantUsers::where('mobile_number', $username)->first();
            if ($userDetails) {
                if (Hash::check(trim($password), $userDetails->password) && Auth::guard('merchant')->attempt(['mobile_number' => $username, 'password' => $password])) {
                    return redirect()->route('merchant.transaction');
                } else {
                    return back()->withErrors(['Invalid username or password']);
                }
            } else {
                return back()->withErrors(['Invalid username or password']);
            }
        } else {
            return back()->withErrors(['Company not found']);
        }
    }

    function forgot_password()
    {
        return view('merchant.auth.forgot_password');
    }

    function forgot_password_otp(Request $request)
    {

        $rules = array(
            'mobile_number' => 'required|exists:merchant,mobile_number|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $request_ip = request()->ip();
        $mobile = $request->mobile_number;
        $userDetails = MerchantUsers::where('mobile_number', $mobile)->first();
        if ($userDetails) {

            $otp = mt_rand(100000, 999999);
            MerchantUsers::where('mobile_number', $mobile)->update(['otp' => bcrypt($otp)]);
            // $message = "Dear $userDetails->name, your forgot password verification code is $otp $this->brand_name";

            $message = "Dear user, your trustxpay reset password code is $otp. Please use this code to reset your password. For more info: trustxpay.org PAYOBL";
            $template_id = 19;
            $whatsappArr=[$otp];
            $library = new SmsLibrary();
            $sms=$library->merchant_send_sms($mobile, $message, $template_id,$whatsappArr);
            $mobile = substr($mobile, 0, 1) . '*******' . substr($mobile, -2);
            $alertMessage = "Enter the 6-digit OTP sent on +91 $mobile";
            return Response()->json(['status' => 'success', 'message' => $alertMessage]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid mobile number']);
        }
    }

    function confirm_forgot_password(Request $request)
    {
        // dd("confirm_forgot_password");
        $rules = array(
            'mobile_number' => 'required|exists:merchant,mobile_number|digits:10',
            'otp' => 'required',
            'new_password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/|same:confirm_password',
            'confirm_password' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile = $request->mobile_number;
        $otp = $request->otp;
        $request_ip = request()->ip();
        $userDetails = MerchantUsers::where('mobile_number', $mobile)->first();
        if ($userDetails) {
            if (Hash::check(trim($otp), $userDetails->otp)) {
                $password = bin2hex(random_bytes(8));
                $user_id = $userDetails->id;
                $mm = MerchantUsers::find($user_id);

                $mm->password = bcrypt($request->new_password);
                $mm->save();

                return Response()->json(['status' => 'success', 'message' => "Dear $userDetails->first_name  your new password successfully sent to your regsiter mobile number"]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
        }
    }

    public function logout()
    {
        Auth::guard('merchant')->logout();
        return redirect()->route('merchant.login');
    }


}
