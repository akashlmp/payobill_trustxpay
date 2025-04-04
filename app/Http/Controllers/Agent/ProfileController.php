<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\Member;
use App\Models\Tableotp;
use App\Models\Profile;
use App\Models\Company;
use Validator;
use Hash;
use Helpers;
use App\Library\SmsLibrary;
use App\Library\BasicLibrary;
use Carbon\Carbon;
use Str;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
    }

    function my_profile()
    {
        $data = array('page_title' => 'My Profile');
        $roles = Role::get();
        $circles = State::where('status_id', 1)->get();
        $district = District::get();
        return view('agent.my_profile', compact('roles', 'circles', 'district'))->with($data);
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
        $userdetail = User::find(Auth::id());
        $current_password = $userdetail->password;

        if (Hash::check($request->new_password, $userdetail->transaction_password)) {
            return Response()->json(['status' => 'failure', 'message' => 'Login password and transaction password can not same']);
        }
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

    function trans_change_password(Request $request)
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
        $userdetail = User::find(Auth::id());
        if (Hash::check($request->new_password, $userdetail->password)) {
            return Response()->json(['status' => 'failure', 'message' => 'Login password and transaction password can not same']);
        }

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
                $userdetail->transaction_password = Hash::make($new_password);
                $userdetail->save();
                return Response()->json(['status' => 'success', 'message' => 'Transaction password successfully changed']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
            }
        }
    }


    function update_profile(Request $request)
    {
        // dd($request->all());
        $rules = array(
            'shop_name' => 'required',
            'office_address' => 'required',
            'pay_name' => 'required',
            'bank_name' => 'required',
            'ifsc_code' => 'required',
            'bank_account_no' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        $pay_name = $request->pay_name;
        $bank_name = $request->bank_name;
        $ifsc_code = $request->ifsc_code;
        $bank_account_no = $request->bank_account_no;
        $user_id = Auth::id();
        Member::where('user_id', $user_id)->update([
            'shop_name' => $shop_name,
            'office_address' => $office_address,
            'pay_name' => $pay_name,
            'bank_name' => $bank_name,
            'ifsc_code' => $ifsc_code,
            'bank_account_no' => $bank_account_no,
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
        $shop_photo = $request->shop_photo;
        $path = "shop_photo";
        try {
            $image_url = Helpers::upload_s3_image($shop_photo, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['shop_photo' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Shop Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $gst_regisration_photo = $request->gst_regisration_photo;
        $path = "gst_regisration_photo";
        try {
            $image_url = Helpers::upload_s3_image($gst_regisration_photo, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['gst_regisration_photo' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'GST Registration Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $pancard_photo = $request->pancard_photo;
        $path = "pancard_photo";
        try {
            $image_url = Helpers::upload_s3_image($pancard_photo, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['pancard_photo' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Pancard Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $cancel_cheque = $request->cancel_cheque;
        $path = "cancel_cheque";
        try {
            $image_url = Helpers::upload_s3_image($cancel_cheque, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['cancel_cheque' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Cancel Cheque Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $address_proof = $request->address_proof;
        $path = "address_proof";
        try {
            $image_url = Helpers::upload_s3_image($address_proof, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['address_proof' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Address Proof Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $aadhar_front = $request->aadhar_front;
        $path = "aadhar_front";
        try {
            $image_url = Helpers::upload_s3_image($aadhar_front, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['aadhar_front' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Aadhar Front Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $aadhar_back = $request->aadhar_back;
        $path = "aadhar_back";
        try {
            $image_url = Helpers::upload_s3_image($aadhar_back, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['aadhar_back' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Aadhar Back Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
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
        $agreement_form = $request->agreement_form;
        $path = "agreement_form";
        try {
            $image_url = Helpers::upload_s3_image($agreement_form, $path);
        } catch (\Exception $e) {
            \Session::flash('failure', $e->getMessage());
            return redirect()->back();
        }
        if ($image_url) {
            Member::where('user_id', Auth::id())->update(['agreement_form' => $image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Agreement/ASM Form Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', "Something went wrong, try again later");
            return redirect()->back();
        }
    }


    function get_distric_by_state(Request $request)
    {
        if ($request->state_id) {
            $state_id = $request->state_id;
            $districts = District::where('state_id', $state_id)->get();
            $response = array();
            foreach ($districts as $value) {
                $product = array();
                $product["district_id"] = $value->id;
                $product["district_name"] = $value->district_name;
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'districts' => $response]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'select state']);
        }
    }

    function update_verify_profile(Request $request)
    {
        $rules = array(
            'permanent_address' => 'required',
            'permanent_state' => 'required',
            'permanent_district' => 'required',
            'permanent_city' => 'required',
            'permanent_pin_code' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $permanent_address = $request->permanent_address;
        $permanent_state = $request->permanent_state;
        $permanent_district = $request->permanent_district;
        $permanent_city = $request->permanent_city;
        $permanent_pin_code = $request->permanent_pin_code;
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        Member::where('user_id', Auth::id())->update([
            'permanent_address' => $permanent_address,
            'permanent_city' => $permanent_city,
            'permanent_state' => $permanent_state,
            'permanent_district' => $permanent_district,
            'permanent_pin_code' => $permanent_pin_code,
            'shop_name' => $shop_name,
            'office_address' => $office_address,
        ]);
        return Response()->json(['status' => 'success', 'message' => 'Profile details update successfully']);
    }

    function verify_mobile(Request $request)
    {
        $otp = mt_rand(100000, 999999);
        $mobile_number = Auth::User()->mobile;
        $request_ip = request()->ip();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $tableotp = Tableotp::where('mobile_number', $mobile_number)->first();
        if ($tableotp) {
            Tableotp::where('mobile_number', $mobile_number)->update([
                'otp' => $otp,
                'ip_address' => $request_ip,
                'status_id' => 3,
            ]);
        } else {
            Tableotp::insertGetId([
                'mobile_number' => $mobile_number,
                'ip_address' => $request_ip,
                'created_at' => $ctime,
                'otp' => $otp,
                'status_id' => 3,
            ]);
        }
        $message = "Dear partnter your profile activation otp is : $otp";
        $library = new SmsLibrary();
        $library->send_sms($mobile_number, $message);
        return response()->json([
            'status' => 'success',
            'mobile_number' => $mobile_number,
            'message' => 'Successfully',
        ]);
    }


    function verify_mobile_otp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|exists:tableotps,mobile_number',
            'otp' => 'required|exists:tableotps,otp',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $tableotp = Tableotp::where('mobile_number', $mobile_number)->where('otp', $otp)->where('status_id', 3)->first();
        if ($tableotp) {
            Tableotp::where('id', $tableotp->id)->update(['status_id' => 1]);
            User::where('mobile', $tableotp->mobile_number)->update(['mobile_verified' => 1]);
            return Response()->json(['status' => 'success', 'message' => 'your profile is activated now please wait few moments']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'invalid otp']);
        }
    }

    function view_kyc()
    {

        $user_id = Auth::id();
        $userdetails = User::where('id', $user_id)->first();

        if ($userdetails->member->shop_photo) {
            $shop_photo = $userdetails->member->shop_photo;
        } else {
            $shop_photo = null;
        }
        if ($userdetails->member->gst_regisration_photo) {
            $gst_regisration_photo = $userdetails->member->gst_regisration_photo;
        } else {
            $gst_regisration_photo = null;
        }

        if ($userdetails->member->pancard_photo) {
            $pancard_photo = $userdetails->member->pancard_photo;
        } else {
            $pancard_photo = null;
        }

        if ($userdetails->member->cancel_cheque) {
            $cancel_cheque = $userdetails->member->cancel_cheque;
        } else {
            $cancel_cheque = null;
        }

        if ($userdetails->member->address_proof) {
            $address_proof = $userdetails->member->address_proof;
        } else {
            $address_proof = null;
        }

        if ($userdetails->member->profile_photo) {
            $profile_photo = $userdetails->member->profile_photo;
        } else {
            $profile_photo = null;
            // $profile_photo = url('assets/img/profile-pic.jpg');
        }

        if ($userdetails->member->aadhar_front) {
            $aadhar_front = $userdetails->member->aadhar_front;
        } else {
            $aadhar_front = null;
        }

        if ($userdetails->member->aadhar_back) {
            $aadhar_back = $userdetails->member->aadhar_back;
        } else {
            $aadhar_back = null;
        }
        if ($userdetails->member->agreement_form) {
            $agreement_form = $userdetails->member->agreement_form;
        } else {
            $agreement_form = null;
        }

        $details = array(
            'shop_photo' => $shop_photo,
            'gst_regisration_photo' => $gst_regisration_photo,
            'pancard_photo' => $pancard_photo,
            'cancel_cheque' => $cancel_cheque,
            'address_proof' => $address_proof,
            'profile_photo' => $profile_photo,
            'name' => $userdetails->name . ' ' . $userdetails->last_name,
            'role_type' => $userdetails->role->role_title,
            'website_name' => $userdetails->company->company_name,
            'email' => $userdetails->email,
            'mobile' => $userdetails->mobile,
            'joining_date' => "$userdetails->created_at",
            'kyc_status' => $userdetails->member->kyc_status,
            'user_id' => $userdetails->id,
            'kyc_remark' => $userdetails->member->kyc_remark,
            'aadhar_front' => $aadhar_front,
            'aadhar_back' => $aadhar_back,
            'agreement_form' => $agreement_form
        );
        $page_title = $userdetails->name . ' Kyc';
        $data = array('page_title' => $page_title);
        return view('agent.view_kyc')->with($data)->with($details);
    }

    function my_settings()
    {
        $data = array('page_title' => 'Settings');
        return view('agent.my_settings')->with($data);
    }

    function save_settings(Request $request)
    {
        $rules = array(
            'day_book' => 'required',
            'daily_statement' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $day_book = $request->day_book;
        $daily_statement = $request->daily_statement;
        Profile::where('user_id', Auth::id())->update(['day_book' => $day_book, 'monthly_statement' => $daily_statement]);
        return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
    }

    function transaction_pin()
    {
        if (Auth::User()->company->transaction_pin == 1) {
            $data = array('page_title' => 'Transaction Pin');
            return view('agent.transaction_pin')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function latlongSecurity()
    {
        $data = array('page_title' => 'Transaction Pin');
        return view('agent.latlongSecurity')->with($data);
    }

    function regenerate_keys(Request $request)
    {
        // dd("regenerate_keys");
        $api_key = Str::random(36);
        $secrete_key = Str::random(16);

        User::where('id', Auth::user()->id)->update(['api_key' => $api_key, 'secrete_key' => $secrete_key]);
        return Response()->json(['status' => 'success', 'message' => 'Regenerated successfully..!']);
    }
}
