<?php

namespace App\Paysprint {

    use App\Models\AepsPayoutRequest;
    use App\Models\Cmsorder;
    use App\Models\User;
    use App\Models\Provider;
    use App\Models\Balance;
    use App\Models\Report;

    // library here
    use App\Library\GetcommissionLibrary;
    use App\Library\Commission_increment;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class Cms
    {

        public function __construct()
        {
            $mode = 'LIVE'; // LIVE or TEST
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
            $this->service_id = 25;
        }


        function generateUrl($mobile_number, $latitude, $longitude, $insert_id)
        {
            $url = $this->base_url . 'api/v1/service/airtelcms/V2/airtel/index';
            $parameters = '{"refid":"' . $insert_id . '","latitude":"' . $latitude . '","longitude":"' . $longitude . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token"
                //"Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response);
            if (isset($res->status) && isset($res->responsecode)) {
                if ($res->status == true && $res->responsecode == 1) {
                    $data = array('redirectionUrl' => $res->redirectionUrl);
                    return Response()->json(['status' => 'success', 'message' => $res->message, 'data' => $data]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'server not responding']);
            }
        }

        function CmsBalanceInquiry($param)
        {
            $refid = $param['refid'];
            $amount = $param['amount'];
            $cmsorders = Cmsorder::where('id', $refid)->where('status_id', 3)->first();
            if ($cmsorders) {
                $user_id = $cmsorders->user_id;
                $userDetails = User::find($user_id);
                $opening_balance = $userDetails->balance->user_balance;
                $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
                if ($opening_balance >= $sumamount && $sumamount >= 1) {
                    return '{"status":200,"message":"Transaction completed successfully"}';
                } else {
                    return '{"status":401,"message":"Insufficient funds."}';
                }
            } else {
                return '{"status":401,"message":"duplicate transaction"}';
            }
        }

        function CmsBalanceDebit($param)
        {
            $refid = $param['refid'];
            $amount = $param['amount'];
            $cmsorders = Cmsorder::where('id', $refid)->where('status_id', 3)->first();
            if ($cmsorders) {
                $user_id = $cmsorders->user_id;
                $userDetails = User::find($user_id);
                $opening_balance = $userDetails->balance->user_balance;
                $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
                if ($opening_balance >= $sumamount && $sumamount >= 1) {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $providers = Provider::where('paysprint_biller_id', $param['biller_id'])->first();
                    if ($providers) {
                        $provider_id = $providers->id;
                    } else {
                        $provider_id = Provider::insertGetId([
                            'provider_name' => $param['biller_name'],
                            'service_id' => $this->service_id,
                            'api_id' => $this->api_id,
                            'paysprint_biller_id' => $param['biller_id'],
                            'created_at' => $ctime,
                            'status_id' => 1,
                        ]);
                    }
                    $scheme_id = $userDetails->scheme_id;
                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount, 1);
                    $retailer = $commission['retailer'];
                    $tds = 0;
                    if ($retailer) {
                        $tds = ($retailer * 5) / 100;
                    }
                    $decrementAmount = ($amount - $retailer) + $tds;
                    Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $description = 'CMS: ' . $param['biller_name'];
                    $insert_id = Report::insertGetId([
                        'number' => $param['mobile_no'],
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
                        'latitude' => $cmsorders->latitude,
                        'longitude' => $cmsorders->longitude,
                        'tds' => $tds,
                        'provider_api_from' => 1
                    ]);
                    Cmsorder::where('id', $refid)->update(['report_id' => $insert_id]);
                    return '{"status":200,"message":"Transaction completed successfully"}';
                } else {
                    return '{"status":401,"message":"Insufficient funds."}';
                }
            } else {
                return '{"status":401,"message":"Duplicate transaction"}';
            }
        }

        function CmsLowBalanceInquiry()
        {
            return '{"status":200,"message":"Transaction completed successfully"}';
        }

        function CmsPosting($param)
        {
            $refid = $param['refid'];
            $status = $param['status'];
            $cmsorders = Cmsorder::where('id', $refid)->first();
            if ($cmsorders) {
                if ($status == 1) {
                    $report_id = $cmsorders->report_id;
                    $reports = Report::find($report_id);
                    if ($reports) {
                        $unique_id = $param['unique_id'];
                        $ackno = $param['ackno'];
                        $expload = explode('-', $unique_id);
                        $number = $expload[1];
                        Report::where('id', $report_id)->update([
                            'number' => $number,
                            'txnid' => $ackno,
                            'status_id' => 1,
                            'payid' => ($param['utr']) ? $param['utr'] : '',
                        ]);
                        Cmsorder::where('id', $refid)->update(['status_id' => 1]);
                        /*commission code start*/
                        // get wise commission
                        $user_id = $reports->user_id;
                        $userDetails = User::find($user_id);
                        $scheme_id = $userDetails->scheme_id;
                        $api_id = $this->api_id;
                        $provider_id = $reports->provider_id;
                        $amount = $reports->amount;
                        $insert_id = $reports->id;
                        // get user commission
                        $library = new GetcommissionLibrary();
                        $commission = $library->get_commission($scheme_id, $provider_id, $amount, $provider_commission_type = 1);
                        $retailer = $commission['retailer'];
                        $d = $commission['distributor'];
                        $sd = $commission['sdistributor'];
                        $st = $commission['sales_team'];
                        $rf = $commission['referral'];

                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $api_id, $amount, $d, $sd, $st, $rf);

                        $library = new GetcommissionLibrary();
                        $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                        $apiCommission = $apiComms['apiCommission'];
                        $commissionType = $apiComms['commissionType'];
                        $library = new Commission_increment();
                        $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                        /* commission code end */
                        return '{"status":200,"message":"Transaction completed successfully"}';
                    } else {
                        return '{"status":401,"message":"invalid refid!"}';
                    }
                } else {
                    return '{"status":401,"message":"invalid status!"}';
                }
            } else {
                return '{"status":401,"message":"invalid refid!"}';
            }
        }

        function payoutSettlement($param)
        {
            $refid = $param['refid'];
            $status = $param['status'];
            $ackno = $param['ackno'];
            $utr = $param['utr'];
            $payout = AepsPayoutRequest::where('ref_id', $refid)->first();
            if ($payout) {
                if ($status == 1) {
                    $report_id = $payout->report_id;
                    $reports = Report::find($report_id);
                    if ($reports) {
                        Report::where('id', $report_id)->update([
                            'txnid' => $ackno,
                            'payid' => $utr,
                            'status_id' => 1,
                        ]);
                        AepsPayoutRequest::where('ref_id', $refid)->update(['status' => 1]);
                        return '{"status":200,"message":"Transaction completed successfully"}';
                    } else {
                        return '{"status":401,"message":"invalid refid!"}';
                    }
                } else {
                    return '{"status":401,"message":"invalid status!"}';
                }
            } else {
                return '{"status":401,"message":"invalid refid!"}';
            }
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


    }
}
