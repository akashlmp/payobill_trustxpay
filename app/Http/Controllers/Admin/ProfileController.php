<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\Member;
use Validator;
use Hash;
use Helpers;
use App\Models\Sitesetting;
use App\Models\Company;
use App\Library\SmsLibrary;
use Carbon\Carbon;

class ProfileController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
        } else {
            $this->brand_name = "";
        }
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
    }

    function my_profile()
    {
        $data = array('page_title' => 'My Profile');
        $roles = Role::get();
        $circles = State::where('status_id', 1)->get();
        return view('admin.my_profile', compact('roles', 'circles'))->with($data);
    }

    function change_password(Request $request)
    {
        $rules = array(
            'new_password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirm_password' => 'same:new_password',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $userdetail = User::find(Auth::id());
        $current_password = $userdetail->password;
        if ($request->type == 1) {
            $otp = mt_rand(100000, 999999);
            $message = $otp . " is your OTP. Use this to change your password. Valid for 3 minutes. Don't share this OTP with anyone. For more info: trustxpay.org PYOBIL";
            $template_id = 23;
            $whatsappArr = [$otp];
            $library = new SmsLibrary();
            $mobile = $userdetail->mobile;
            User::where('id', $userdetail->id)->update(['login_otp' => $otp]);
            $sms = $library->send_sms($mobile, $message, $template_id, $whatsappArr);
            $mobile = substr($mobile, 0, 1) . '*******' . substr($mobile, -2);
            $alertMessage = "Enter the 6-digit OTP sent on +91 $mobile";
            return Response()->json(['status' => 'success', 'message' => $alertMessage]);
        } else {
            $password_otp = $request->password_otp;
            if ($password_otp == $userdetail->login_otp) {
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


    function update_profile(Request $request)
    {
        $rules = array(
            'shop_name' => 'required',
            'office_address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        $user_id = Auth::id();
        Member::where('user_id', $user_id)->update([
            'shop_name' => $shop_name,
            'office_address' => $office_address,
        ]);
        return Response()->json(['status' => 'success', 'message' => 'Profile Successfully Updated']);
    }

    function update_profile_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $profile_photo = $request->profile_photo;
        $path = "profile_photo";
        try {
            $image_url = Helpers::upload_s3_image($profile_photo, $path);
            Member::where('user_id', Auth::id())->update(['profile_photo' => $image_url]);
            \Session::flash('success', 'Profile Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function update_shop_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $shop_photo = $request->shop_photo;
        $path = "shop_photo";
        try {
            $image_url = Helpers::upload_s3_image($shop_photo, $path);
            Member::where('user_id', $user_id)->update(['shop_photo' => $image_url]);
            \Session::flash('success', 'Shop Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function update_gst_regisration_photo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gst_regisration_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $gst_regisration_photo = $request->gst_regisration_photo;
        $path = "gst_regisration_photo";
        try {
            $image_url = Helpers::upload_s3_image($gst_regisration_photo, $path);
            Member::where('user_id', $user_id)->update(['gst_regisration_photo' => $image_url]);
            \Session::flash('success', 'GST Registration Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function update_pancard_photo(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'pancard_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $pancard_photo = $request->pancard_photo;
        $path = "pancard_photo";
        try {
            $image_url = Helpers::upload_s3_image($pancard_photo, $path);
            Member::where('user_id', $user_id)->update(['pancard_photo' => $image_url]);
            \Session::flash('success', 'Pancard Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function cancel_cheque_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cancel_cheque' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $cancel_cheque = $request->cancel_cheque;
        $path = "cancel_cheque";
        try {
            $image_url = Helpers::upload_s3_image($cancel_cheque, $path);
            Member::where('user_id', $user_id)->update(['cancel_cheque' => $image_url]);
            \Session::flash('success', 'Cancel Cheque Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function address_proof_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_proof' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $address_proof = $request->address_proof;
        $path = "address_proof";
        try {
            $image_url = Helpers::upload_s3_image($address_proof, $path);
            Member::where('user_id', $user_id)->update(['address_proof' => $image_url]);
            \Session::flash('success', 'Address Proof Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function aadhar_front_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aadhar_front' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $aadhar_front = $request->aadhar_front;
        $path = "aadhar_front";
        try {
            $image_url = Helpers::upload_s3_image($aadhar_front, $path);
            Member::where('user_id', $user_id)->update(['aadhar_front' => $image_url]);
            \Session::flash('success', 'Aadhar Front Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function aadhar_back_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aadhar_back' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $aadhar_back = $request->aadhar_back;
        $path = "aadhar_back";
        try {
            $image_url = Helpers::upload_s3_image($aadhar_back, $path);
            Member::where('user_id', $user_id)->update(['aadhar_back' => $image_url]);
            \Session::flash('success', 'Aadhar Back Photo Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function agreement_form_doc(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agreement_form' => 'required|mimes:pdf,doc,docx,odt|max:50480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $agreement_form = $request->agreement_form;
        $path = "agreement_form";
        try {
            $image_url = Helpers::upload_s3_image($agreement_form, $path);
            Member::where('user_id', $user_id)->update(['agreement_form' => $image_url]);
            \Session::flash('success', 'Agreement/ASM Form Successfully Updated');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
    }

    function transaction_pin()
    {
        if (Auth::User()->company->transaction_pin == 1) {
            $data = array('page_title' => 'Transaction Pin');
            return view('admin.transaction_pin')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function send_transaction_pin_otp(Request $request)
    {
        if (Auth::User()->company->transaction_pin == 1) {
            $user_id = Auth::id();
            $userDetails = User::find($user_id);
            $otp = mt_rand(100000, 999999);
            User::where('id', $userDetails->id)->update(['login_otp' => $otp]);
            // $message = "Dear $userDetails->name, your generate transaction pin one time password is $otp $this->brand_name";
            $message = "$otp is the OTP for trustxpay transaction is $this->brand_name. Valid for 3 minutes. Do not share this OTP with anyone. For more info: trustxpay.org PAOBIL";
            $template_id = 18;
            $whatsappArr = [$otp, $this->brand_name];
            $library = new SmsLibrary();
            $sms = $library->send_sms($mobile, $message, $template_id, $whatsappArr);
            return Response()->json(['status' => 'success', 'message' => 'OTP has been sent to authorised person mobile number']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Kindly contact customer care']);
        }
    }

    function create_transaction_pin(Request $request)
    {
        $rules = array(
            'password' => 'required',
            'transaction_pin' => 'required|digits:6',
            'otp' => 'required|digits:6',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $password = $request->password;
        $transaction_pin = $request->transaction_pin;
        $user_id = Auth::id();
        $userDetails = User::find($user_id);
        if ($request->otp == Auth::User()->login_otp) {
            $current_password = $userDetails->password;
            if (Hash::check($password, $current_password)) {
                User::where('id', $user_id)->update(['transaction_pin' => bcrypt($transaction_pin)]);
                return Response()->json(['status' => 'success', 'message' => 'Transaction pin created successful..!']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid One Time Password']);
        }
    }
}
