<?php

namespace App\Paysprint {

    use Helpers;
    use App\Models\Api;
    use App\Models\Masterbank;
    use App\Models\Beneficiary;
    use App\Models\Apiresponse;
    use App\Models\Paysprintremitter;
    use App\Models\Paysprintdmtbank;
    use http\Env\Response;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;
    use Illuminate\Support\Facades\Log;

    class Dmt
    {

        public function __construct()
        {
            $mode = env('DMT_MODE', 'LIVE');
            //$mode = 'UAT';
            $this->dmt_mode = $mode;
            $library = new PaysprintApicredentials();
            $response = $library->getCredentials($mode);
            $this->base_url = $response['base_url'];
            $this->partner_id = $response['partner_id'];
            $this->api_key = $response['api_key'];
            $this->jwt_header = $response['jwt_header'];
            $this->authorised_key = $response['authorised_key'];
            $this->key = $response['key'];
            $this->iv = $response['iv'];
            $this->api_id = $response['api_id'];
            $this->bank3_flag = 'no';
            $this->bank4_flag = 'no';
            $this->pincode = "201301";
            $this->dob = "1995-12-16";
            $this->gst_state = "09";
            $this->address = "Noida";
            $this->pipe = 'bank1';
            $this->stateresp = '465220179';
        }


        function getCustomer($mobile_number, $name, $lat, $long)
        {
            $url = $this->base_url . 'api/v1/service/dmt/kyc/remitter/queryremitter';
            // $parameters = '{"mobile":"' . $mobile_number . '","bank3_flag":"' . $this->bank3_flag . '","bank4_flag":"' . $this->bank4_flag . '"}';
            $parameters = '{"mobile":' . $mobile_number . ',"name":"' . $name . '","lat":"' . $lat . '","long":"' . $long . '","accessmode":"WEB"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            if ($this->dmt_mode != 'LIVE') {
                $response = '{"status": true,"response_code": 1,"message": "Remitter account details fetched.","data": {"limit": "25000.00","mobile": "9971355854"}}';
            }
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'getCustomer']);
            $res = json_decode($response);
            Log::info($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1 && !isset($res->stateresp)) {
                    $paysprintremitters = Paysprintremitter::where('mobile', $mobile_number)->first();
                    if (empty($paysprintremitters)) {
                        Paysprintremitter::insert([
                            'mobile' => $mobile_number,
                            'firstname' => (isset($res->data->fname)) ? $res->data->fname : $name,
                            'lastname' => (isset($res->data->lname)) ? $res->data->lname : '',
                            'address' => $this->address,
                            'pincode' => $this->pincode,
                            'dob' => $this->dob,
                            'gst_state' => $this->gst_state,
                        ]);
                    }
                    $data = array('name' => (isset($res->data->fname)) ? $res->data->fname : $name, 'mobile_number' => $mobile_number, 'total_limit' => $res->data->limit);
                    return Response(['status' => 'success', 'message' => 'Successfull.', 'ad1' => '', 'ad2' => '', 'data' => $data]);
                } elseif ($res->status == false && $res->response_code == 0) {
                    $data = array('is_otp' => 1);
                    return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => $res->stateresp, 'ad2' => 1, 'data' => $data]);
                } elseif ($res->status == true && ($res->response_code == 2 || $res->response_code == 3)) {
                    $data = array('is_otp' => 1);
                    return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => '', 'ad2' => 1, 'data' => $data]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!', 'ad1' => '', 'ad2' => '']);
            }
        }


        function remitterEkyc($mobile_number, $lat, $long, $aadhaar_number, $dataPid)
        {
            $url = $this->base_url . 'api/v1/service/dmt/kyc/remitter/queryremitter/kyc';
            $parameters = '{"mobile":"' . $mobile_number . '","lat":"' . $lat . '","long":"' . $long . '","aadhaar_number":"' . $aadhaar_number . '","accessmode":"WEB","data":"' . $dataPid . '"}';
            \Log::info("==remitterEkyc==", ['data' => $parameters]);
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            \Log::info("==remitterEkyc parameters==" . $url . '?' . $parameters);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'queryremitter-Kyc']);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1) {
                    return ['status' => 'success', 'message' => $res->message, 'data' => $res->data];
                } else {
                    return ['status' => 'failure', 'message' => ($res->message) ? $res->message : 'Kyc not completed please try again.'];
                }
            } else {
                return ['status' => 'failure', 'message' => "Something's gone wrong. We're working to get it fixed as soon as we can."];
            }
        }

        function confirmSender($mobile_number, $kyc_id, $otp, $ad1, $latitude, $longitude)
        {
            $url = $this->base_url . 'api/v1/service/dmt/kyc/remitter/registerremitter';
            $parameters = '{"mobile":"' . $mobile_number . '","otp":"' . $otp . '","lat":"' . $latitude . '","long":"' . $longitude . '","stateresp":"' . $ad1 . '","ekyc_id":"' . $kyc_id . '"}';

            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'confirmSender']);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1) {
                    return Response()->json(['status' => 'success', 'response_code' => $res->response_code, 'message' => $res->message]);
                } else {
                    return Response()->json(['status' => 'failure', 'response_code' => $res->response_code, 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => "Something's gone wrong. We're working to get it fixed as soon as we can."]);
            }
        }

        function senderResendOtp($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2)
        {
            $url = $this->base_url . 'api/v1/service/dmt/remitter/queryremitter/kyc';
            $parameters = '{"mobile":"' . $mobile_number . '","bank3_flag":"' . $this->bank3_flag . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'senderResendOtp']);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->status == false && $res->response_code == 0) {
                    return Response()->json(['status' => 'pending', 'message' => $res->message, 'response_code' => $res->response_code, 'ad1' => '', 'ad2' => '']);
                }
                if ($res->status == true && $res->response_code == 1) {
                    return Response()->json(['status' => 'success', 'message' => $res->message, 'response_code' => $res->response_code, 'ad1' => '', 'ad2' => '']);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!', 'ad1' => '', 'ad2' => '']);
            }
        }

        function getAllBeneficiary($mobile_number, $sender_name)
        {
            //$url = $this->base_url . "api/v1/service/dmt/beneficiary/registerbeneficiary/fetchbeneficiary";
            $url = $this->base_url . "api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/fetchbeneficiary";
            $method = "POST";
            $parameters = '{"mobile":"' . $mobile_number . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'getAllBeneficiary']);
            $res = json_decode($response);
            if (isset($res->status) && $res->status == true && $res->response_code == 1) {
                $beneficiaries = $res->data;
                Self::updateBeneficiary($beneficiaries, $mobile_number, $sender_name);
                $beneficiaryList = Self::getBeneficiaryList($beneficiaries);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'beneficiaries' => $beneficiaryList]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Oops... something went wrong']);
            }
        }

        function updateBeneficiary($beneficiaries, $mobile_number, $sender_name)
        {
            foreach ($beneficiaries as $value) {
                $beneficiary = Beneficiary::where('account_number', $value->accno)->where('benficiary_id', $value->bene_id)->where('api_id', $this->api_id)->first();
                $data = array(
                    'benficiary_id' => $value->bene_id,
                    'account_number' => $value->accno,
                    'ifsc' => $value->ifsc,
                    'bank_name' => $value->bankname,
                    'name' => $value->name,
                    'remiter_number' => $mobile_number,
                    'remiter_name' => $sender_name,
                    'status_id' => 1,
                    'api_id' => $this->api_id,
                );
                if ($beneficiary) {
                    $beneficiary_id = $beneficiary->id;
                    Beneficiary::where('id', $beneficiary_id)->update($data);
                } else {
                    Beneficiary::insert($data);
                }
            }
        }

        function getBeneficiaryList($beneficiaries)
        {
            $response = array();
            $i = 1;
            foreach ($beneficiaries as $value) {
                $product = array();
                $product["id"] = $i++;
                $product["beneficiary_id"] = $value->bene_id;
                $product["bank_name"] = $value->bankname;
                $product["mobile_number"] = '';
                $product["beneficiary_name"] = $value->name;
                $product["ifsc_code"] = $value->ifsc;
                $product["account_number"] = $value->accno;
                $product["is_verify"] = $value->verified;
                $product["status_id"] = 1;
                array_push($response, $product);
            }
            return $response;
        }

        function getIfscCode($bank_id)
        {
            $paysprintdmtbanks = Paysprintdmtbank::find($bank_id);
            if ($paysprintdmtbanks) {
                $data = array('ifsc' => $paysprintdmtbanks->ifsc);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        }

        function addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name, $verify_status)
        {
            $paysprintdmtbanks = Paysprintdmtbank::find($bank_id);
            $bankid = (empty($paysprintdmtbanks)) ? '' : $paysprintdmtbanks->bank_id;
            $url = $this->base_url . "api/v1/service/dmt/kyc/beneficiary/registerbeneficiary";
            $parameters = '{"mobile":"' . $mobile_number . '","benename":"' . $beneficiary_name . '","bankid":"' . $bankid . '","accno":"' . $account_number . '","ifsccode":"' . $ifsc_code . '","verified":"' . $verify_status . '","gst_state":"' . $this->gst_state . '","dob":"' . $this->dob . '","address":"' . $this->address . '","pincode":"' . $this->pincode . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'addBeneficiary']);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1) {
                    return Response()->json(['status' => 'success', 'message' => $res->message]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong', 'ad1' => '', 'ad2' => '']);
            }
        }

        function confirmBeneficiary($mobile_number, $otp, $ad1, $ad2)
        {
            $url = $this->base_url . "api/v1/dmt/add_beneficiary_confirm";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'dmtbeneficiary_id' => $ad1,
                'otp' => $otp,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'success', 'message' => 'Beneficiary Successfully addedd']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function deleteBeneficiary($mobile_number, $beneficiary_id)
        {
            $url = $this->base_url . "api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/deletebeneficiary";
            $parameters = '{"mobile": "' . $mobile_number . '","bene_id": "' . $beneficiary_id . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'deleteBeneficiary']);
            $res = json_decode($response);
            $response_code = $res->response_code;
            if ($response_code == 1) {
                return Response()->json(['status' => 'success', 'message' => $res->message]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }


        function confirmDeleteBeneficiary($mobile_number, $ad1, $ad2, $otp)
        {
            $url = $this->base_url . "api/v1/dmt/delete_beneficiary_confirm";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'vendor_id' => 10,
                'dmtbeneficiary_id' => $ad1,
                'otp' => $otp,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'success', 'message' => $res->message]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $api_id)
        {
            $paysprintdmtbanks = Paysprintdmtbank::find($bank_id);
            $bankid = (empty($paysprintdmtbanks)) ? '' : $paysprintdmtbanks->bank_id;
            $url = 'https://api.verifya2z.com/';
            if ($this->dmt_mode != 'LIVE') {
                $url = "https://uat.paysprint.in/sprintverify-uat/";
            }
            //$url = $url. "api/v1/service/dmt/beneficiary/registerbeneficiary/benenameverify";
            //$parameters = '{"mobile":"' . $mobile_number . '","accno":"' . $account_number . '","bankid":"' . $bankid . '","benename":"' . $benename . '","referenceid":"' . $insert_id . '","pincode":"' . $this->pincode . '","address":"' . $this->address . '","dob":"' . $this->dob . '","gst_state":"' . $this->gst_state . '"}';
            $url = $url . "api/v1/verification/penny_drop_v2";
            $parameters = '{"account_number":"' . $account_number . '","ifsc_code":"' . $ifsc_code . '"}';
            $token = Self::generateTokenVerification();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:TVRJek5EVTJOelUwTnpKRFQxSlFNREF3TURFPQ==";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'benenameverify']);
            $res = json_decode($response);
            if (isset($res->statuscode)) {
                if ($res->status == true && $res->statuscode == 200) {
                    return ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => (isset($res->data->c_name)) ? $res->data->c_name : ''];
                } elseif ($res->status == false) {
                    return ['status_id' => 2, 'message' => $res->message, 'name' => ''];
                } else {
                    return ['status_id' => 3, 'message' => '', 'name' => ''];
                }
            } else {
                return ['status_id' => 3, 'message' => '', 'name' => ''];
            }
        }


        function transferNow($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude, $otp)
        {
            $txntype = ($channel_id == 2) ? 'IMPS' : 'NEFT';
            $url = $this->base_url . "api/v1/service/dmt/kyc/transact/transact";
            //$parameters = '{"mobile":"' . $mobile_number . '","referenceid":"' . $insert_id . '","pipe":"' . $this->pipe . '","pincode":"' . $this->pincode . '","address":"' . $this->address . '","dob":"' . $this->dob . '","gst_state":"' . $this->gst_state . '","bene_id":"' . $beneficiary_id . '","txntype":"' . $txntype . '","amount":"' . $amount . '"}';
            $parameters = '{"mobile":"' . $mobile_number . '","referenceid":"' . $insert_id . '","stateresp":"' . $this->stateresp . '","pincode":"' . $this->pincode . '","address":"' . $this->address . '","dob":"' . $this->dob . '","gst_state":"' . $this->gst_state . '","bene_id":"' . $beneficiary_id . '","txntype":"' . $txntype . '","amount":"' . $amount . '","otp":"' . $otp . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            /*$response = '{"status":true,"response_code":1,"ackno":"109649","utr":"204012199354","txn_status":1,"benename":"NIRAJ CHAUHAN","remarks":" - NIRAJ CHAUHAN","message":"Transaction Successful.","customercharge":"50.00","gst":"7.63","tds":"1.46","netcommission":"37.41","remitter":"8527032627","account_number":"50100201419269","paysprint_share":"3.5","txn_amount":"5000","balance":4054.57}';*/
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters]);
            $res = json_decode($response);
            $message = ($res->message) ? $res->message : '';
            if (isset($res->status)) {
                $status = $res->status;
                if ($status == true) {
                    if ($res->txn_status == 1) {
                        return ['status_id' => 1, 'txnid' => $res->utr, 'payid' => $res->ackno, 'message' => $message];
                    } elseif ($res->txn_status == 0) {
                        return ['status_id' => 2, 'txnid' => '', 'payid' => $res->ackno, 'message' => $message];
                    } else {
                        return ['status_id' => 3, 'txnid' => '', 'payid' => $res->ackno, 'message' => $message];
                    }
                } elseif ($status == false) {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => '', 'message' => $message];
                } else {
                    return ['status_id' => 3, 'txnid' => '', 'payid' => '', 'message' => $message];
                }
            } else {
                return ['status_id' => 3, 'txnid' => '', 'payid' => '', 'message' => $message];
            }
        }

        function transferSendOtp($parameters)
        {
            $parameters = json_encode($parameters);
            $url = $this->base_url . "api/v1/service/dmt/kyc/transact/transact/send_otp";
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            if ($this->dmt_mode != 'LIVE') {
                $response = '{"status":"success","response_code": 1,"message": "OTP has been send to the remitter mobile number.","stateresp": "465220179","is_send_otp":true}';
            }
            Apiresponse::insertGetId(['message' => $response, 'request_message' => $url . '?' . $parameters, 'response_type' => 'transferSendOtp']);
            $res = json_decode($response);
            $message = ($res->message) ? $res->message : '';
            if ($res->status) {
                return Response()->json(['status' => 'success', 'response_code' => $res->response_code, 'stateresp' => $res->stateresp, 'message' => $message, 'is_send_otp' => true]);
            } else {
                return Response()->json(['status' => 'failure', 'response_code' => $res->response_code, 'message' => $message, 'is_send_otp' => false]);

            }

        }

        function getBankList()
        {
            $masterbank = Paysprintdmtbank::where('status_id', 1)->select('id', 'bank_name', 'ifsc')->get();
            $response = array();
            foreach ($masterbank as $value) {
                $product = array();
                $product["bank_id"] = $value->id;
                $product["bank_name"] = $value->bank_name;
                $product["ifsc_code"] = $value->ifsc;
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'message' => 'successful..!', 'bank_list' => $response]);
        }

        public function generateToken()
        {
            $Jwtheader = $this->jwt_header;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $reqid = rand(100000, 999999);
            $timestamp = strtotime($ctime);
            $payload = '{
            "timestamp": "' . $timestamp . '",
            "partnerId": "' . $this->partner_id . '",
            "reqid": "' . $reqid . '"
        }';
            $apikey = $this->api_key;
            $library = new PaysprintApicredentials();
            $Jwt = $library->encode($Jwtheader, $payload, $apikey);
            return $Jwt;
        }

        public function generateTokenVerification()
        {
            $Jwtheader = $this->jwt_header;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $reqid = rand(100000, 999999);
            $timestamp = strtotime($ctime);
            $partnerId = 'CORP00001775';
            if ($this->dmt_mode != 'LIVE') {
                $partnerId = 'CORP00001';
            }
            $payload = '{
            "timestamp": "' . $timestamp . '",
            "partnerId": "' . $partnerId . '",
            "reqid": "' . $reqid . '"}';
            $apikey = 'UTA5U1VEQXdNREF4VFZSSmVrNUVWVEpPZWxVd1RuYzlQUT09';
            $library = new PaysprintApicredentials();
            $Jwt = $library->encode($Jwtheader, $payload, $apikey);
            return $Jwt;
        }

        public function sendCurlPost($url, $header, $api_request_parameters, $method)
        {
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
                CURLOPT_POSTFIELDS => $api_request_parameters,
                CURLOPT_HTTPHEADER => $header,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }

        function getPaySprintCreditBalance()
        {
            $url = $this->base_url . "api/v1/service/balance/balance/mainbalance";

            $method = "POST";
            $parameters = '';
            $token = Self::generateToken();


            $header = array(
                'Content-Type: application/json',
                "Token: $token",
                'accept' => 'application/json',
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $response = Self::sendCurlPost($url, $header, $parameters, $method);

            $res = json_decode($response);
            if ($res->status == true && $res->response_code == 1) {

                return $res;
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function getPaySprintDebitBalance()
        {
            $url = $this->base_url . "api/v1/service/balance/balance/cashbalance";

            $method = "POST";
            $parameters = '';
            $token = Self::generateToken();

            $header = array(
                "Token: $token",
                'accept' => 'application/json',
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $response = Self::sendCurlPost($url, $header, $parameters, $method);

            $res = json_decode($response);
            if ($res->status == true && $res->response_code == 1) {

                return $res;
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function dmtResendOtp($id, $payid)
        {
            $url = $this->base_url . 'api/v1/service/dmt/kyc/refund/refund/resendotp';
            // $parameters = '{"referenceid":12345678926,"ackno":127}';
            $parameters = '{"referenceid":"' . $id . '","ackno":"' . $payid . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            \Log::info("==dmtResendOtp==", ['data' => $response]);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'dmtResendOtp']);
            $res = json_decode($response);
            // return ['status'=>1,"message"=>"Refund Otp Successfully Sent."];
            if ($res->status == true && $res->response_code == 1) {
                return ['status' => 1, 'message' => $res->message];
            } else {
                return ['status' => 'failure', 'message' => $res->message];
            }
        }

        function dmtRefund($id, $payid, $otp)
        {
            $url = $this->base_url . 'api/v1/service/dmt/kyc/refund/refund/';
            //$parameters = '{"referenceid":12345678926,"ackno":127,"otp":"576719"}';
            $parameters = '{"referenceid":"' . $id . '","ackno":"' . $payid . '","otp":"' . $otp . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
            );
            if ($this->dmt_mode != 'LIVE') {
                $header[] = "Authorisedkey:$this->authorised_key";
            }
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            \Log::info("==dmtRefund==", ['data' => $response]);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url . '?' . $parameters, 'response_type' => 'dmtRefund']);
            $res = json_decode($response);
            // return ['status'=>1,"message"=>"Transaction Successfully Refunded."];
            if ($res->status == true && $res->response_code == 1) {
                return ['status' => 1, 'message' => $res->message];
            } else {
                return ['status' => 'failure', 'message' => $res->message];
            }
        }

    }


}
