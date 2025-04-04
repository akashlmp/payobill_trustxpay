<?php

namespace App\Console\Commands;

use App\Models\MerchantPayouts;
use App\Models\MerchantTransactions;
use App\Models\MerchantUsers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IServeUPayoutStatusCheckCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iserveu-payout-status:cron';

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
        info("iServeU Payout Other Cron Job running at " . now());
        $date = date("Y-m-d H:i:s");
        $pendingReports = MerchantTransactions::where(['status_id' => 3, 'provider_id' => 584])
            ->where('created_at', '<', $date)
            ->orderBy('cron_order', 'asc')
            ->limit(10)->get()->toArray();
        // pre($pendingReports);
        if (count($pendingReports) > 0) {
            foreach ($pendingReports as $key => $reportDetails) {
                MerchantTransactions::where('id', $reportDetails['id'])->increment('cron_order', 1);
                $userdetails = MerchantUsers::find($reportDetails['merchant_id']);
                $ref_id = $reportDetails['reference_id'];
                $transaction_date = date("Y-m-d", strtotime($reportDetails['created_at']));
                $tran_id = $reportDetails['transaction_id'];
                try {
                    $payout = new \App\IServeU\Payout();
                    $response = $payout->transactionStatusCheck($ref_id, $tran_id, $transaction_date,$transaction_date);
                    // pre($response);
                    if ($response['status'] == 'success' && count($response['data']) > 0) {
                        $results = $response['data'][0];
                        $statusDesc = $results['statusDesc'];
                        if ($results['status'] == 'SUCCESS' && ($results['statusCode'] == 0 || $results['statusCode'] == 1)) {
                            $rrn = $results['rrn'];
                            MerchantTransactions::where('id', $reportDetails['id'])->update([
                                'utr' => $rrn,
                                'status_id' => 1,
                                'failure_reason' => $statusDesc
                            ]);
                            MerchantPayouts::where('transaction_id', $tran_id)->update(['status' => 1,'utr' => $rrn]);
                        } elseif (($results['status'] == 'FAILED' || $results['status'] == 'REFUNDED') && ($results['statusCode'] == 0 || $results['statusCode'] == -1 || $results['statusCode'] == 1)) {
                            if ($reportDetails['status_id'] != 2) {
                                MerchantPayouts::where('transaction_id', $tran_id)->update(['status' => 2]);
                                $decrementAmount = $reportDetails['decrementAmount'];
                                $userdetails->wallet = $userdetails->wallet +  $decrementAmount;
                                $userdetails->save();
                                MerchantTransactions::where('id', $reportDetails['id'])->update(['total_balance' => $userdetails->wallet, 'profit' => 0, 'status_id' => 2, 'failure_reason' => $statusDesc]);
                            }
                        } elseif ($results['status'] == 'INPROGRESS' && ($results['statusCode'] == 1)) {
                            Log::info("iServerU Payout status in progress", ['transaction_id' => $tran_id]);
                        } else {
                            Log::info("iServerU NO STATUS", ['data' => $results]);
                        }
                    } else {
                        Log::info("iServerU cron no response or data received");
                    }
                } catch (\Exception $e) {
                    Log::error("iServerU cron error received", ["error" => $e->getMessage()]);
                }
            }
        }
    }
}
