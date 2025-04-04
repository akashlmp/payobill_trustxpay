<?php

namespace App\library {
    use App\Models\User;
    use Helpers;
    use App\Models\Api;
    use App\Models\Company;
    use App\Models\Sitesetting;
    use App\Models\Smstemplate;
    use App\Models\MerchantUsers;
    use Mail;

    class SmsLibrary {
        public function __construct()   {
            $this->whatsapp_app_key="";
            $this->whatsapp_auth_key="";
            $this->device_id="";

            $this->company_id = Helpers::company_id()->id;
            $dt = Helpers::company_id();
            $this->company_id = $dt->id;

            $company = Company::where('id', $this->company_id)->first();
            if ($company) {
                $this->sender_id = $company->sender_id;
            }else{
                $this->sender_id = "";
            }

            $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
            if ($sitesettings){
                $this->brand_name = $sitesettings->brand_name;
                $this->sms = $sitesettings->sms;
                $this->sms_key = $sitesettings->sms_key;
                $this->whatsapp = $sitesettings->whatsapp;
                $this->whatsapp_key = $sitesettings->whatsapp_key;
                $this->send_mail = $sitesettings->send_mail;
                $this->mail_from = $sitesettings->mail_from;
            }else{
                $this->brand_name = "";
                $this->sms = "";
                $this->sms_key = "";
                $this->whatsapp = "";
                $this->whatsapp_key = "";
                $this->send_mail = "";
                $this->mail_from = "";
            }
        }


        function send_sms ($mobile, $message, $template_id,$whatsappArr=[]){
            if ($this->sms  == 1){
                $this->send_sms_api($mobile, $message, $template_id);
            }

            if ($this->whatsapp  == 1){
                $this->send_whatsapp_api($mobile, $message, $template_id,$whatsappArr);
            }

            if ($this->send_mail  == 1){

                if(isMerchant())
                {
                    $this->merchant_send_mail_api($mobile, $message, $template_id);
                }
                else{

                    $this->send_mail_api($mobile, $message, $template_id);
                }
            }
        }

        function merchant_send_sms ($mobile, $message, $template_id,$whatsappArr=[]){
            if ($this->sms  == 1){
                $this->send_sms_api($mobile, $message, $template_id);
            }

            if ($this->whatsapp  == 1){
                $this->send_whatsapp_api($mobile, $message, $template_id,$whatsappArr);
            }

            if ($this->send_mail  == 1){

                $this->merchant_send_mail_api($mobile, $message, $template_id);

            }
        }

        function send_sms_api ($mobile, $message, $template_id){
            $smstemplates = Smstemplate::find($template_id);

            if ($smstemplates && $smstemplates->sms == 1){
                $message = urlencode($message);
                // $url = "http://sms.sms21.co.in/api/sendhttp.php?authkey=$this->sms_key&mobiles=91$mobile&message=$message&sender=$this->sender_id&route=4&country=91&DLT_TE_ID=$smstemplates->template_id";


                $url = "http://web.adcruxmedia.in/vb/apikey.php?apikey=$this->sms_key&senderid=$smstemplates->sms_sender_id&templateid=$smstemplates->template_id&number=$mobile&message=$message";


                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $data = curl_exec($curl);
                curl_close($curl);
            }

        }

        function send_whatsapp_api ($mobile, $message, $template_id,$whatsappArr){
            // dd($whatsappArr);
            $smstemplates = Smstemplate::find($template_id);

            if ($smstemplates->whatsapp == 1){

                $url="https://web.wabridge.com/api/createmessage";

                $parameters["app-key"]= $this->whatsapp_app_key;
                $parameters["auth-key"]=  $this->whatsapp_auth_key;
                $parameters["destination_number"]= "91".$mobile;
                $parameters["template_id"]= $smstemplates->whatsapp_template_id;
                $parameters["device_id"]= $this->device_id;
                $parameters["variables"]= $whatsappArr;
                $parameters["media"]= '';
                $parameters["message"]= $smstemplates->whatsapp_template_msg;

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($parameters),
                    CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                return $response;
            }
        }

        function send_mail_api ($mobile, $message, $template_id){
            $smstemplates = Smstemplate::find($template_id);
            if ($smstemplates->send_mail == 1){


                $userdetails = User::where('mobile', $mobile)->first();

                if ($userdetails){
                    $email = $userdetails->email;
                    $name = $userdetails->name.' '.$userdetails->last_name;
                    $companies = Company::find($userdetails->company_id);
                    $data = [
                        'company_logo' => $companies->company_logo,
                        'support_number' => $companies->support_number,
                        'company_name' => $companies->company_name,
                        'name' => $name,
                        'email' => $email,
                        'content' => $message
                    ];
                    Mail::send('mail.send_mail', $data, function ($m) use ($userdetails, $smstemplates) {
                        $m->to($userdetails['email'], $userdetails['name'])->subject($smstemplates->template_name);
                        $m->from($this->mail_from, $userdetails->company->company_name);
                    });
                }
            }
        }

        function merchant_send_mail_api ($mobile, $message, $template_id){
            $smstemplates = Smstemplate::find($template_id);
            if ($smstemplates->send_mail == 1){

                $userdetails = MerchantUsers::where('mobile_number', $mobile)->first();

                if ($userdetails){
                    $email = $userdetails->email;
                    $name = $userdetails->first_name.' '.$userdetails->last_name;
                    $companies = Company::find(1);

                    $data = [
                        'company_logo' => $companies->company_logo,
                        'support_number' => $companies->support_number,
                        'company_name' => $companies->company_name,
                        'name' => $name,
                        'email' => $email,
                        'content' => $message
                    ];
                    Mail::send('mail.send_mail', $data, function ($m) use ($userdetails, $smstemplates, $companies) {
                        $m->to($userdetails['email'], $userdetails['first_name'])->subject($smstemplates->template_name);
                        $m->from($this->mail_from, $companies->company_name);
                    });
                }
            }
        }


        function send_whatsapp ($mobile, $message){
            $text_message = urlencode($message);
            $url = "https://whatsbot.tech/api/send_sms?api_token=$this->whatsapp_key&mobile=91$mobile&message=$text_message";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Cookie: JSESSIONID=A51039E2B6D039D04A2299C7C94CA44D",
                    "content-type: application/x-www-form-urlencoded"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }

        function send_whatsapp_image ($mobile, $message, $image_url){
            $text_message = urlencode($message);
            $url = "https://whatsbot.tech/api/send_img?api_token=$this->whatsapp_key&mobile=91$mobile&img_url=$image_url&img_caption=$text_message";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Cookie: JSESSIONID=A51039E2B6D039D04A2299C7C94CA44D"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;

        }

    }

}
