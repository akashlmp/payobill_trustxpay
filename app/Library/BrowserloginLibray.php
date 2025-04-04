<?php

namespace App\library {

    use DB;
    use App\Models\Company;
    use App\Models\Api;
    use App\Models\User;
    use App\Models\Sitesetting;
    use Helpers;
    use App\Library\SmsLibrary;

    class BrowserloginLibray
    {

        public function __construct()
        {
            $this->company_id = Helpers::company_id()->id;
            $companies = Helpers::company_id();
            $this->company_id = $companies->id;
            $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
            $this->brand_name = (empty($sitesettings)) ? '' :  $sitesettings->brand_name;
        }

        function checkingcookie($users, $all)
        {
            if (!isset($_COOKIE['username'])) {
                $otp = mt_rand(100000, 999999);
                $otp = 123456;
                // $message = "Dear $users->name, your login verification code is $otp $this->brand_name";
                $message ="$users->name is your one time password $otp to access your trustxpay account. Do not share the OTP with anyone else. For more info: trustxpay.org";
                $template_id = 1;
                $whatsappArr=[$users->name,$otp];
                $library = new SmsLibrary();
                $library->send_sms($users->mobile, $message, $template_id,$whatsappArr);
                User::where('id', $users->id)->update(['login_otp' => bcrypt($otp)]);
                return 0;

            } else {
                if ($_COOKIE['username'] != $all['username']) {
                    $otp = mt_rand(100000, 999999);
                    $otp = 123456;
                    // $message = "Dear $users->name, your login verification code is $otp $this->brand_name";
                    $message ="$users->name is your one time password $otp to access your trustxpay account. Do not share the OTP with anyone else. For more info: trustxpay.org";
                    $template_id = 1;
                    $whatsappArr=[$users->name,$otp];
                    $library = new SmsLibrary();
                    $library->send_sms($users->mobile, $message, $template_id,$whatsappArr);
                    User::where('id', $users->id)->update(['login_otp' => bcrypt($otp)]);
                    return 0;
                } else {
                    return 1;
                }
            }
        }


    }

}
