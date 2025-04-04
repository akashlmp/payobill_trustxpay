<?php

namespace App\Bankit {

    use App\Library\Commission_increment;
    use App\Models\AepsPayoutRequest;
    use App\Models\Cmsorder;
    use App\Models\User;
    use App\Models\Provider;
    use App\Models\Balance;
    use App\Models\Report;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    // library here
    use App\Library\GetcommissionLibrary;


    class Cms
    {

        public function __construct()
        {
            $mode = 'LIVE'; // LIVE or TEST
            $this->base_url = "";
            $this->agentAuthId = '';
            $this->agentAuthPassword = '';
            $this->apiId = "";
            $this->api_id = 2;
            $this->service_id = 25;
        }


        function generateUrl($insert_id)
        {
            $retailerId = \Auth::user()->cms_agent_id;
            $apiurl = $this->base_url . 'AEPS/v1/generatetoken';

            $rowData = array(
                "agentAuthId" => $this->agentAuthId,
                "agentAuthPassword" => $this->agentAuthPassword,
                "retailerId" => $retailerId,
                "apiId" => $this->apiId
            );

            try {
                $res = Http::withoutVerifying()->withHeaders(["content-type" => "application/json"])->post($apiurl, $rowData)->json();
                if (isset($res['errorCode']) && $res['errorCode'] == "00") {
                    $redirectionUrl = "";
                    if (isset($res['data']['token']) && $res['data']['token'] != '') {
                        $redirectionUrl = $this->base_url . "AEPS/cmslogin?token=" . $res['data']['token'];
                    }
                    return ['status' => 'success', 'message' => $res['errorMsg'], 'redirectionUrl' => $redirectionUrl];
                } else {
                    return ['status' => 'failure', 'message' => $res['errorMsg']];
                }
            } catch (\Exception $e) {
                $response = [];
                $response['status'] = "failure";
                $response['message'] = $e->getMessage();
                return $response;
            }
        }

        function CmsBalanceInquiry($param)
        {
            $agentId  = $param['agentId'];
            $amount = $param['amount'];
            $userDetails = User::where('cms_agent_id', $agentId)->first();
            if ($userDetails) {
                $opening_balance = $userDetails->balance->user_balance;
                $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
                if ($opening_balance >= $sumamount && $sumamount >= 1) {
                    return ['status' => "00", "message" => "Success"];
                } else {
                    return ['status' => 401, "message" => "Insufficient funds"];
                }
            } else {
                return ['status' => 401, "message" => "Agent not found"];
            }
        }

        function CmsBalanceDebit($param)
        {
            if ($param['status'] == "Pending") {
                $agentId  = $param['agentId'];
                $amount = $param['amount'];
                $userDetails = User::where('cms_agent_id', $agentId)->first();
                if ($userDetails) {
                    $txnid = $param['txnId'];
                    $checkReport = Report::where('txnid', $txnid)->where('api_id', $this->api_id)->first();
                    if (!$checkReport) {
                        $user_id = $userDetails->id;
                        $opening_balance = $userDetails->balance->user_balance;
                        $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
                        if ($opening_balance >= $sumamount && $sumamount >= 1) {
                            $now = new \DateTime();
                            $ctime = $now->format('Y-m-d H:i:s');
                            $providers = Provider::where('paysprint_biller_id', $param['accountNo'])->where('service_id', $this->service_id)->first();
                            if ($providers) {
                                $provider_id = $providers->id;
                            } else {
                                $provider_id = Provider::insertGetId([
                                    'provider_name' => $param['bankName'],
                                    'service_id' => $this->service_id,
                                    'api_id' => $this->api_id,
                                    'paysprint_biller_id' => $param['accountNo'],
                                    'created_at' => $ctime,
                                    'status_id' => 1,
                                ]);
                            }
                            $scheme_id = $userDetails->scheme_id;
                            $library = new GetcommissionLibrary();
                            $commission = $library->get_commission($scheme_id, $provider_id, $amount, 2);
                            $retailer = $commission['retailer'];
                            $tds = 0;
                            if ($retailer) {
                                $tds = ($retailer * 5) / 100;
                            }
                            $decrementAmount = ($amount - $retailer) + $tds;
                            Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            $description = 'CMS: ' . $param['bankName'];
                            $insert_id = Report::insertGetId([
                                'number' => $param['mobileNo'],
                                'provider_id' => $provider_id,
                                'amount' => $amount,
                                'api_id' => $this->api_id,
                                'status_id' => 3,
                                'created_at' => $ctime,
                                'user_id' => $user_id,
                                'profit' => $retailer,
                                'mode' => 'WEB',
                                'description' => $description,
                                'opening_balance' => $opening_balance,
                                'total_balance' => $user_balance,
                                'credit_by' => $user_id,
                                'wallet_type' => 1,
                                'txnid' => $param['txnId'],
                                'provider_api_from' => 2,
                                'tds' => $tds,
                                'row_data' => json_encode($param)
                                //'latitude' => $cmsorders->latitude,
                                //'longitude' => $cmsorders->longitude,
                            ]);
                            //Cmsorder::where('id', $refid)->update(['report_id' => $insert_id]);
                            return ['status' => "00", "message" => "Success"];
                        } else {
                            return ['status' => 401, "message" => "Insufficient funds"];
                        }
                    } else {
                        return ['status' => 401, "message" => "Duplicate Transaction"];
                    }
                } else {
                    return ['status' => 401, "message" => "Agent not found"];
                }
            } else {
                return ['status' => 401, "message" => $param['status']];
            }
        }

        function CmsPosting($param)
        {
            $agentId  = $param['agentId'];
            $userDetails = User::where('cms_agent_id', $agentId)->first();
            if ($userDetails) {
                $user_id = $userDetails->id;
                $txnid = $param['txnId'];
                $number = $param['mobileNo'];
                $amount = $param['amount'];
                $txnid = $param['txnId'];
                $checkReport = Report::where('txnid', $txnid)->where('api_id', $this->api_id)->first();
                if ($checkReport) {
                    $insert_id = $checkReport->id;
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $providers = Provider::where('paysprint_biller_id', $param['accountNo'])->where('service_id', $this->service_id)->first();
                    if ($providers) {
                        $provider_id = $providers->id;
                    } else {
                        $provider_id = Provider::insertGetId([
                            'provider_name' => $param['bankName'],
                            'service_id' => $this->service_id,
                            'api_id' => $this->api_id,
                            'paysprint_biller_id' => $param['accountNo'],
                            'created_at' => $ctime,
                            'status_id' => 1,
                        ]);
                    }
                    $scheme_id = $userDetails->scheme_id;
                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount, 2);
                    $retailer = $commission['retailer'];
                    $d = $commission['distributor'];
                    $sd = $commission['sdistributor'];
                    $st = $commission['sales_team'];
                    $rf = $commission['referral'];
                    $tds = 0;
                    if ($retailer) {
                        $tds = ($retailer * 5) / 100;
                    }
                    $decrementAmount = ($amount - $retailer) + $tds;
                    if ($param['status'] == "Success") {
                        if ($checkReport->status_id != 1) {
                            Report::where('txnid', $txnid)->where('api_id', $this->api_id)->update([
                                'status_id' => 1,
                                'row_data' => json_encode($param)
                            ]);
                            $library = new Commission_increment();
                            $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $d, $sd, $st, $rf);

                            $library = new GetcommissionLibrary();
                            $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
                            $apiCommission = $apiComms['apiCommission'];
                            $commissionType = $apiComms['commissionType'];
                            $library = new Commission_increment();
                            $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                            /*commission code end*/
                        }
                        //Cmsorder::where('id', $refid)->update(['report_id' => $insert_id]);
                        return ['status' => "00", "message" => "Success"];
                    } else {
                        if ($checkReport->status_id != 2) {
                            Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            Report::where('id', $insert_id)->update(['status_id' => 2, 'row_data' => json_encode($param), 'failure_reason' => $param['status'], 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance, 'tds' => 0]);
                        }
                        return ['status' => $param['status'], "message" => "Transaction failed"];
                    }
                } else {
                    return ['status' => 401, "message" => "Transaction not found"];
                }
            } else {
                return ['status' => 401, "message" => "Agent not found"];
            }
        }
    }
}
