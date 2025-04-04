<?php

namespace App\Console\Commands;

use App\library\Commission_increment;
use App\library\GetcommissionLibrary;
use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Beneficiary;
use App\Models\Report;
use App\Models\User;
use App\Paysprint\Apicredentials as PaysprintApicredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DmtPaySprintCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paSprint-transaction:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        info("Cron Job running at " . now());
        $date = date("Y-m-d H:i:s", strtotime("-24 hour"));
        $mode = 'LIVE';
        // $mode = 'UAT';
        $library = new PaysprintApicredentials();
        $response = $library->getCredentials($mode);
        $apiurl = $response['base_url'].'api/v1/service/dmt/kyc/transact/transact/querytransact';

        $jwt_header = $response['jwt_header'];
        $api_key = $response['api_key'];
        $partner_id = $response['partner_id'];
        $money_provider_id = 316;
        $provider_commission_type = 1;
        $api_id = 1;
        $pendingReports = Report::where(['status_id' => 3, 'api_id' => 1, 'provider_id' => '316'])
            ->where('created_at', '<', $date)
            ->where('provider_api_from',1)
            ->orderBy('cron_order','asc')
            ->limit(2)->get()->toArray();
        if (!empty($pendingReports) && count($pendingReports) > 0) {
            foreach ($pendingReports as $key => $reportDetails) {
                Report::where('id', $reportDetails['id'])->increment('cron_order', 1);

                $userdetails = User::find($reportDetails['user_id']);
                $user_id = $reportDetails['user_id'];
                $amount = $reportDetails['amount'];
                $token = self::generateToken($jwt_header, $partner_id, $api_key);
                $header = array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    "Token: $token"
                );
                $rowData = array(
                    "referenceid" => $reportDetails['id']
                );
                try {
                    $response = self::sendCurlPost($apiurl, $header, json_encode($rowData));
                    if ($response) {
                        Log::info($response);
                        if (isset($response['status'])) {
                            if ($response['txn_status'] == 1) {
                                $txnid = $response['utr'];
                                $payid = $response['ackno'];

                                /*Pending to Success*/
                                /* Commission && minus balance if report Pending to Success*/
                                $beneficiary = Beneficiary::where('id', $reportDetails['benficiary_id'])->first();
                                $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
                                $scheme_id = $userdetails->scheme_id;

                                $library = new GetcommissionLibrary();
                                $commission = $library->get_commission($scheme_id, $money_provider_id, $amount, $provider_commission_type);
                                $retailer = $commission['retailer'];
                                $d = $commission['distributor'];
                                $sd = $commission['sdistributor'];
                                $st = $commission['sales_team'];
                                $rf = $commission['referral'];

                                $library = new Commission_increment();
                                $library->parent_recharge_commission($user_id, $account_number, $reportDetails['id'], $money_provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);

                                // get wise commission
                                Report::where('id', $reportDetails['id'])->update(['status_id' => 1, 'txnid' => $txnid, 'payid' => $payid]);
                                $library = new GetcommissionLibrary();
                                $apiComms = $library->getApiCommission($api_id, $money_provider_id, $amount);
                                $apiCommission = $apiComms['apiCommission'];
                                $commissionType = $apiComms['commissionType'];
                                $library = new Commission_increment();
                                $library->updateApiComm($user_id, $money_provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $reportDetails['id'], $commissionType);
                                Log::error("DMT PaySprint Report minus balance  and charge commission === " . $reportDetails['decrementAmount']);
                            } elseif ($response['txn_status'] == 0) {
                                /*Pending to Failed*/
                                Balance::where('user_id', $user_id)->increment('user_balance', $reportDetails['decrementAmount']);
                                $balance = Balance::where('user_id', $user_id)->first();
                                $user_balance = $balance->user_balance;
                                Report::where('id', $reportDetails['id'])->update(['total_balance' => $user_balance, 'status_id' => 2, 'row_data' => json_encode($response), 'failure_reason' => ($response['Reason']) ? $response['Reason'] : '']);
                                Log::info("PaySprint cron Transaction failed", ['data' => $response]);
                            }elseif ($response['txn_status'] == 5) {
                                /*Pending to Failed*/
                                Report::where('id', $reportDetails['id'])->update(['status_id' => 11, 'failure_reason' => "Transaction Failed Please initiate refund process"]);
                                Log::info("PaySprint cron Transaction failed", ['data' => $response]);
                            }
                        }
                    } else {
                        Log::info("PaySprint cron no response received");
                    }
                } catch (\Exception $e) {
                    Log::error("PaySprint cron no error received", ["error" => $e->getMessage()]);
                }
            }
        }
    }

    public function generateToken($Jwtheader, $partner_id, $api_key)
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $reqid = rand(100000, 999999);
        $timestamp = strtotime($ctime);
        $payload = '{
            "timestamp": "' . $timestamp . '",
            "partnerId": "' . $partner_id . '",
            "reqid": "' . $reqid . '"
        }';
        $apikey = $api_key;
        $library = new PaysprintApicredentials();
        $Jwt = $library->encode($Jwtheader, $payload, $apikey);
        return $Jwt;
    }

    public function sendCurlPost($url,$header,$api_request_parameters)
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
        Log::info($response);
        return $response;
    }
}
