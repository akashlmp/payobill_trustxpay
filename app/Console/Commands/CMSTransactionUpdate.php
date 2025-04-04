<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Models\Balance;
use Illuminate\Support\Facades\Log;

class CMSTransactionUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cms-trans-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Transaction update after 24 hours if any pending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $api_id = 2;

        $date = date("Y-m-d H:i:s", strtotime("-24 hour"));
        $apiurl = "";
        $agentAuthId = '';
        $agentAuthPassword = '';
        $service_id = 25;
        $result = Report::select('id', 'txnid')
            ->where('trans_type', 1)
            ->where("status_id", 3)
            ->whereNotNull('txnid')
            ->where('created_at', '<', $date)
            ->where('api_id', $api_id)->get()->toArray();
        if (count($result) > 0) {
            foreach ($result as $r) {
                $txnid = $r['txnid'];
                $number = $r['mobileNo'];
                $rowData = array(
                    "clientTransactionId" => $txnid
                );
                try {
                    $response = Http::withoutVerifying()
                        ->withBasicAuth($agentAuthId, $agentAuthPassword)
                        ->withHeaders(["content-type" => "application/json"])
                        ->post($apiurl, $rowData)->json();
                    if ($response) {
                        $amount = $response['Amount'];
                        $insert_id = $r['id'];
                        $userDetails = User::where('cms_agent_id', $response['Agent_Id'])->first();
                        $user_id = $userDetails->id;
                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        $providers = Provider::where('paysprint_biller_id', $response['accountNo'])->where('service_id', $service_id)->first();
                        if ($providers) {
                            $provider_id = $providers->id;
                        } else {
                            $provider_id = Provider::insertGetId([
                                'provider_name' => $response['bankName'],
                                'service_id' => $service_id,
                                'api_id' => $api_id,
                                'paysprint_biller_id' => $response['accountNo'],
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
                        if (isset($response['status']) && $response['status'] == 0) {
                            if ($r['status_id'] != 1) {
                                Report::where('txnid', $txnid)->where('api_id', $this->api_id)->update([
                                    'status_id' => 1,
                                    'row_data' => json_encode($response)
                                ]);
                                $library = new Commission_increment();
                                $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                            }
                        } else {
                            if ($r['status_id'] != 2) {
                                Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                                $balance = Balance::where('user_id', $user_id)->first();
                                $user_balance = $balance->user_balance;
                                Report::where('id', $insert_id)->update(['status_id' => 2, 'row_data' => json_encode($response), 'failure_reason' => $response['status'], 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance, 'tds' => 0]);
                            }
                            Log::info("CMS cron Transaction failed", ['data' => $response]);
                        }
                    } else {
                        Log::info("CMS cron no response received");
                    }
                } catch (\Exception $e) {
                    Log::error("CMS cron no error received", ["error" => $e->getMessage()]);
                }
            }
        }
    }
}
