<?php

namespace App\Console\Commands;

use App\library\Commission_increment;
use App\library\GetcommissionLibrary;
use App\Models\Balance;
use App\Models\Beneficiary;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DmtBankItCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bankIt:cron';

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
        info("Cron Job running at " . now());
        echo $date = date("Y-m-d H:i:s", strtotime("-24 hour"));
        echo "\n";
        $apiurl = '';
        $agentAuthId = '';
        $agentAuthPassword = '';
        $agentCode = 'BI001797';
        $money_provider_id = 316;
        $provider_commission_type = 2;
        $api_id = 2;
        $pendingReports = Report::where(['status_id' => 3, 'api_id' => 2, 'provider_id' => '316'])
            ->where('created_at', '<', $date)->get()->toArray();
        if (!empty($pendingReports) && count($pendingReports) > 0) {
            foreach ($pendingReports as $key => $reportDetails) {
                $userdetails = User::find($reportDetails['user_id']);
                $user_id = $reportDetails['user_id'];
                $txnid = $reportDetails['txnid'];
                $payid = $reportDetails['payid'];
                $amount = $reportDetails['amount'];
                //$mobileNumber = $reportDetails['mobileNo'];
                $rowData = array(
                    "ClientRefId" => $payid,
                    "TxnId" => $txnid,
                    "agentCode" => $agentCode,
                    "customerId" => '9243000200'
                );
                try {
                    $response = Http::withoutVerifying()
                        ->withBasicAuth($agentAuthId, $agentAuthPassword)
                        ->withHeaders(["content-type" => "application/json"])
                        ->post($apiurl, $rowData)->json();
                    if ($response) {
                        Log::info($response);
                        $response['errorMsg'] = strtolower($response['errorMsg']);
                        if (isset($response['errorCode']) && $response['errorCode'] == '00' && ($response['errorMsg']) == 'success') {
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
                            $library = new GetcommissionLibrary();
                            $apiComms = $library->getApiCommission($api_id, $money_provider_id, $amount);
                            $apiCommission = $apiComms['apiCommission'];
                            $commissionType = $apiComms['commissionType'];
                            $library = new Commission_increment();
                            $library->updateApiComm($user_id, $money_provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $reportDetails['id'], $commissionType);
                            Report::where('id', $reportDetails['id'])->update(['status_id' => 1, 'txnid' => $txnid, 'payid' => $payid]);

                            Log::error("DMT Report minus balance  and charge commission === " . $reportDetails['decrementAmount']);
                        } else if (isset($response['errorCode']) && $response['errorCode'] == '02' && ($response['errorMsg']) == 'failure') {
                            /*revert balance if report pending to Failed*/
                            Balance::where('user_id', $user_id)->increment('user_balance', $reportDetails['decrementAmount']);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            Report::where('id', $reportDetails['id'])->update(['total_balance' => $user_balance, 'status_id' => 2, 'row_data' => json_encode($response), 'failure_reason' => ($response['Reason']) ? $response['Reason'] : '']);
                            Log::info("BANKIT cron Transaction failed", ['data' => $response]);
                        }
                    } else {
                        Log::info("BANKIT cron no response received");
                    }
                } catch (\Exception $e) {
                    Log::error("BANKIT cron no error received", ["error" => $e->getMessage()]);
                }
            }
        }
    }
}
