<?php

namespace App\Paysprint {

    use App\Models\User;
    use App\Models\Report;
    use App\Models\Balance;
    use App\Models\Commissionreport;
    use App\Models\Traceurl;
    use App\Library\Commission_increment;
    use App\Models\Providerlimit;
    use App\Models\Provider;
    use App\Models\Dispute;
    use App\Models\Disputechat;
    use App\Models\Apicommreport;
    use Helpers;
    use Str;
    use App\Models\Sitesetting;
    use App\Library\BasicLibrary;
    use App\Library\SmsLibrary;
    use App\Library\GetcommissionLibrary;
    use http\Env\Response;
    use App\Paysprint\Refund as PaysprintDmtRefund;
    use App\Paysprint\Dmt as PaysprintDmt;
    use Illuminate\Support\Facades\DB;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class Refund
    {

        public function __construct()
        {
            $mode = env('DMT_MODE', 'LIVE');
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

            $this->company_id = Helpers::company_id()->id;
            $companies = Helpers::company_id();
            $this->company_id = $companies->id;
            $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
            $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        }


        function update_transaction($insert_id, $mode, $otp)
        {
            DB::beginTransaction();
            $reports = Report::where('id', $insert_id)->first();
            if ($reports) {
                $user_id = $reports->user_id;
                $profit = $reports->profit;
                $amount = $reports->amount;
                $provider_id = $reports->provider_id;
                $number = $reports->number;
                $status_id = $oldstatus_id =  $reports->status_id;
                $client_id = $reports->client_id;
                $call_back_url = $reports->user->member->call_back_url;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $api_id = 1;
                $txnid = $reports->txnid;
                if ($status_id == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 4, 'txnid' => $txnid]);
                    $balanace = Balance::where('user_id', $user_id)->first();
                    $opening_balance = $balanace->user_balance;
                    $finalamount = $reports->decrementAmount;
                    Balance::where('user_id', $user_id)->increment('user_balance', $finalamount);
                    $balanace = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balanace->user_balance;
                    $provider_name = $reports->provider->provider_name;
                    $description = "$provider_name  $number";
                    Report::insertGetId([
                        'number' => $number,
                        'provider_id' => $provider_id,
                        'amount' => $finalamount,
                        'api_id' => $api_id,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' =>  0,
                        'txnid' => 'Refund Id ' . $insert_id,
                        'mode' => $mode,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'wallet_type' => 1,
                        'provider_api_from'=>1
                    ]);

                    Self::return_parent_commission($insert_id, $mode);
                    Self::solve_dispte($insert_id, $txnid);

                    $library = new PaysprintDmt();
                    $result = $library->dmtRefund($insert_id, $reports->payid, $otp);
                    if ($result['status'] == 1) {
                        DB::commit();
                        return Response()->json(['status' => 'success', 'message' => 'Transaction Refund Successfully']);
                    } else {
                        DB::rollback();
                        return Response()->json(['status' => 'failure', 'message' => $result['message']]);
                    }
                } else {
                    Report::where('id', $insert_id)->update(['status_id' => 4, 'txnid' => $txnid]);
                    Commissionreport::where('report_id', $insert_id)->update(['status_id' => 2]);
                    $balanace = Balance::where('user_id', $user_id)->first();
                    $opening_balance = $balanace->user_balance;
                    $finalamount = $reports->decrementAmount;
                    Balance::where('user_id', $user_id)->increment('user_balance', $finalamount);
                    $balanace = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balanace->user_balance;
                    $provider_name = $reports->provider->provider_name;
                    $description = "$provider_name  $number";
                    Report::insertGetId([
                        'number' => $number,
                        'provider_id' => $provider_id,
                        'amount' => $finalamount,
                        'api_id' => $api_id,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' =>   0,
                        'txnid' => 'Refund Id ' . $insert_id,
                        'mode' => $mode,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'wallet_type' => 1,
                        'provider_api_from'=>1
                    ]);

                    Self::solve_dispte($insert_id, $txnid);
                    $library = new PaysprintDmt();
                    $result = $library->dmtRefund($insert_id, $reports->payid, $otp);
                    if ($result['status'] == 1) {
                        DB::commit();
                        return Response()->json(['status' => 'success', 'message' => 'Transaction Refund Successfully']);
                    } else {
                        DB::rollback();
                        return Response()->json(['status' => 'failure', 'message' => $result['message']]);
                    }
                }
            } else {
                DB::rollback();
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        }

        function return_parent_commission($insert_id, $mode)
        {
            $reportData = Report::find($insert_id);
            $reports = Report::where('txnid', $insert_id)->where('provider_id', $reportData->provider_id)->where('status_id', 6)->get();
            foreach ($reports as $value) {
                $id = $value->id;
                Report::where('id', $id)->update(['status_id' => 2]);
                $provider_id = $value->provider_id;
                $number = $value->number;
                $profit = $value->profit;
                $user_id = $value->user_id;
                $balanace = Balance::where('user_id', $user_id)->first();
                $opening_balance = $balanace->user_balance;
                Balance::where('user_id', $user_id)->decrement('user_balance', $profit);
                $balanace = Balance::where('user_id', $user_id)->first();
                $user_balance = $balanace->user_balance;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $providers = Provider::find($provider_id);
                $description = "$providers->provider_name  $number";
                Report::insertGetId([
                    'number' => $number,
                    'provider_id' => $provider_id,
                    'amount' => $profit,
                    'api_id' => $value->api_id,
                    'status_id' => 7,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => 0,
                    'txnid' => 'Credit Id ' . $id,
                    'mode' => $mode,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'wallet_type' => 1,
                    'provider_api_from'=>1
                ]);
            }
        }


        function update_transaction_aeps($status_id, $txnid, $insert_id, $mode)
        {
            $reports = Report::where('id', $insert_id)->whereIn('status_id', [1, 2, 3])->first();
            if ($reports) {
                $user_id = $reports->user_id;
                $profit = $reports->profit;
                $amount = $reports->amount;
                $provider_id = $reports->provider_id;
                $number = $reports->number;
                $oldstatus_id = $reports->status_id;
                $client_id = $reports->client_id;
                $call_back_url = $reports->user->member->call_back_url;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $api_id = 0;
                if ($oldstatus_id == 3) {
                    if ($status_id == 2) {
                        Report::where('id', $insert_id)->update(['status_id' => 5, 'txnid' => $txnid]);
                        $balanace = Balance::where('user_id', $user_id)->first();
                        $opening_balance = $balanace->aeps_balance;
                        $finalamount = $amount - $profit;
                        Balance::where('user_id', $user_id)->increment('aeps_balance', $finalamount);
                        $balanace = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balanace->aeps_balance;
                        $provider_name = $reports->provider->provider_name;
                        $description = "$provider_name  $number";
                        Report::insertGetId([
                            'number' => $number,
                            'provider_id' => $provider_id,
                            'amount' => $amount,
                            'api_id' => $api_id,
                            'status_id' => 4,
                            'created_at' => $ctime,
                            'user_id' => $user_id,
                            'profit' => $profit,
                            'txnid' => 'Refund Id ' . $insert_id,
                            'mode' => $mode,
                            'description' => $description,
                            'opening_balance' => $opening_balance,
                            'total_balance' => $user_balance,
                            'wallet_type' => 2,
                        ]);
                        if ($call_back_url) {
                            $txnid = urlencode($txnid);
                            $url = "$call_back_url?payid=$insert_id&status=failure&operator_ref=$txnid&client_id=$client_id&number=$number&provider_id=$provider_id&wallet_type=2";
                            $response = Self::send_to_curl($url);
                            Traceurl::insertGetId([
                                'user_id' => $user_id,
                                'url' => $url,
                                'number' => $number,
                                'response_message' => $response,
                                'created_at' => $ctime
                            ]);
                        }
                        Self::solve_dispte($insert_id, $txnid);
                        return Response()->json(['status' => 'success', 'message' => 'Transaction Refund Successfully']);
                    } elseif ($status_id == 1) {
                        Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid]);
                        if ($call_back_url) {
                            $txnid = urlencode($txnid);
                            $url = "$call_back_url?payid=$insert_id&status=success&operator_ref=$txnid&client_id=$client_id&number=$number&provider_id=$provider_id&wallet_type=2";
                            $response = Self::send_to_curl($url);
                            Traceurl::insertGetId([
                                'user_id' => $user_id,
                                'url' => $url,
                                'number' => $number,
                                'response_message' => $response,
                                'created_at' => $ctime
                            ]);
                        }
                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $profit, $amount, $api_id);
                        Self::solve_dispte($insert_id, $txnid);
                        return Response()->json(['status' => 'success', 'message' => 'Transaction status successfully updpated']);
                    }
                } elseif ($oldstatus_id == 1 && $status_id == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid]);
                    if ($call_back_url) {
                        $txnid = urlencode($txnid);
                        $url = "$call_back_url?payid=$insert_id&status=success&operator_ref=$txnid&client_id=$client_id&number=$number&provider_id=$provider_id&wallet_type=2";
                        $response = $this->send_to_curl($url);
                        Traceurl::insertGetId([
                            'user_id' => $user_id,
                            'url' => $url,
                            'number' => $number,
                            'response_message' => $response,
                            'created_at' => $ctime
                        ]);
                    }
                    $library = new Commission_increment();
                    $commission = $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $profit, $amount, $api_id);
                    Self::solve_dispte($insert_id, $txnid);
                    return Response()->json(['status' => 'success', 'message' => 'Transaction status successfully updpated']);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Sorry ! if u want this feature kindly contact tech team']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        }

        function send_to_curl($url)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $data = curl_exec($curl);
            curl_close($curl);
            return $data;
        }


        function solve_dispte($insert_id, $txnid)
        {
            $dispute = Dispute::where('report_id', $insert_id)->first();
            if ($dispute) {
                $reports = Report::find($insert_id);
                $userdetails = User::find($reports->user_id);
                $providers = Provider::find($reports->provider_id);
                Dispute::where('id', $dispute->id)->update(['status_id' => 1]);
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Disputechat::insertGetId([
                    'user_id' => 1,
                    'dispute_id' => $dispute->id,
                    'message' => $txnid,
                    'created_at' => $ctime,
                    'is_read' => 0,
                ]);
                $status_name = $reports->status->status;
                $message = "Your complaint request for  $providers->provider_name  number $reports->number of Rs.$reports->amount has been resolved status : $status_name Thanks $this->brand_name";
                $template_id = 9;
                // $whatsappMessage="Complaint resolved. {{1}} . Thanks, For more info.";
                $whatsappArr = [$reports->number];
                $library = new SmsLibrary();
                $library->send_sms($userdetails->mobile, $message, $template_id, $whatsappArr);
            }
        }

        function updateApiWiseProfit($status_id, $insert_id)
        {
            $reports = Report::find($insert_id);
            if ($reports) {
                $user_id = $reports->user_id;
                $provider_id = $reports->provider_id;
                $amount = $reports->amount;
                $api_id = $reports->api_id;
                $userDetails = User::find($user_id);
                $scheme_id = $userDetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $d = $commission['distributor'];
                $sd = $commission['sdistributor'];
                $st = $commission['sales_team'];
                $rf = $commission['referral'];
                //get wise commission
                $library = new GetcommissionLibrary();
                $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                $apiCommission = $apiComms['apiCommission'];
                $commissionType = $apiComms['commissionType'];
                if ($status_id == 1) {
                    $library = new Commissionreport();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                } elseif ($status_id == 2) {
                    $apicommreports = Apicommreport::where('report_id', $insert_id)->first();
                    if ($apicommreports) {
                        Apicommreport::where('id', $apicommreports->id)->update(['status_id' => 2]);
                    }
                }
            }
        }
    }
}
