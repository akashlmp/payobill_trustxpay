<?php

namespace App\Console\Commands;

use App\Models\AepsPayoutRequest;
use App\Models\Balance;
use App\Models\MerchantPayouts;
use App\Models\MerchantTransactions;
use App\Models\MerchantUsers;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IServeUMinePayoutStatusCheckCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iserveu-mine-payout-status:cron';

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
        info("iServeU Payout Mine Cron Job running at " . now());
        $date = date("Y-m-d H:i:s");
        $pendingReports = Report::where(['status_id' => 3, 'provider_id' => 584])
            ->where('created_at', '<', $date)
            ->where('provider_api_from',3)
            ->orderBy('cron_order', 'asc')
            ->limit(10)->get()->toArray();
        // pre($pendingReports);
        if (count($pendingReports) > 0) {
            foreach ($pendingReports as $key => $reportDetails) {
                Report::where('id', $reportDetails['id'])->increment('cron_order', 1);
                $userdetails = User::find($reportDetails['user_id']);
                $ref_id = $reportDetails['reference_id'];
                $transaction_date = date("Y-m-d", strtotime($reportDetails['created_at']));
                $tran_id = $reportDetails['txnid'];
                try {
                    $payout = new \App\IServeU\Payout();
                    $response = $payout->transactionStatusCheck($ref_id, $tran_id, $transaction_date,$transaction_date);
                    // $response = $array = array(
                    //     "status" => "success",
                    //     "message" => "Success",
                    //     "data" => array(
                    //         array(
                    //             "customeridentIfication" => "03650100002600",
                    //             "statusDesc" => "Transaction Success",
                    //             "param_b" => "OTHER",
                    //             "param_c" => null,
                    //             "txnType" => "WALLET1PAYOUT_IMPS",
                    //             "updatedDate" => 1731582344000,
                    //             "userName" => "payomatxapi",
                    //             "retailerUserName" => "payomatxapi",
                    //             "txnDateTime" => 1731582337000,
                    //             "rrn" => "431916037097",
                    //             "createdDate" => 1731582337000,
                    //             "productCode" => "IMPS",
                    //             "clientreferenceid" => "#R141731582416CXWTE",
                    //             "txnAmount" => 100,
                    //             "status" => "FAILED",
                    //             "statusCode" => 0,
                    //             "txnId" => "#859681209738120"
                    //         )
                    //     )
                    // );

                    if ($response['status'] == 'success' && count($response['data']) > 0) {
                        $results = $response['data'][0];
                        $statusDesc = $results['statusDesc'];
                        if ($results['status'] == 'SUCCESS' && ($results['statusCode'] == 0 || $results['statusCode'] == 1)) {
                            $rrn = $results['rrn'];
                            Report::where('id', $reportDetails['id'])->update([
                                'payid' => $rrn,
                                'status_id' => 1,
                                'failure_reason' => $statusDesc
                            ]);
                            AepsPayoutRequest::where('transaction_id', $tran_id)->update(['status' => 1, 'utr' => $rrn]);
                        } elseif (($results['status'] == 'FAILED' || $results['status'] == 'REFUNDED') && ($results['statusCode'] == 0 || $results['statusCode'] == -1 || $results['statusCode'] == 1)) {
                            if ($reportDetails['status_id'] != 2) {
                                AepsPayoutRequest::where('transaction_id', $tran_id)->update(['status' => 0]);
                                $decrementAmount = $reportDetails['decrementAmount'];
                                $user_id = $userdetails->id;
                                Balance::where('user_id', $user_id)->increment('aeps_balance', $decrementAmount);
                                $balance = Balance::where('user_id', $user_id)->first();
                                $user_balance = $balance->aeps_balance;
                                Report::where('id', $reportDetails['id'])->update(['total_balance' => $user_balance, 'profit' => 0, 'status_id' => 2, 'row_data' => "", 'failure_reason' => $statusDesc]);
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
