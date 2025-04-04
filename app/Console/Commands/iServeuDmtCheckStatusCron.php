<?php

namespace App\Console\Commands;

use App\IServeU\Dmt as iServeUDmt;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\IServeU\Apicredentials as IServeApicredentials;

class iServeuDmtCheckStatusCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iserveu-dmt-transfer-status:cron';

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
        info("iServeU DMT check status running at " . now());
        $date = date("Y-m-d H:i:s");
        $money_provider_id = 316;
        $mode = 'LIVE';
        $library = new IServeApicredentials();
        $response = $library->getCredentials($mode);
        $apiUrl = 'https://api-prod.txninfra.com/apiV1/dmt-api_prod/statuscheck/txnreport';
        $clientId = $response['clientId'];
        $clientSecret = $response['clientSecret'];
        $header = array(
            'Content-Type: application/json',
            'accept: application/json',
            'client_id:' . $clientId,
            'client_secret:' . $clientSecret,
        );
        $pendingReports = Report::where(['status_id' => 3, 'provider_id' => $money_provider_id])
            ->where('created_at', '<', $date)
            ->where('api_id', 3)
            ->whereNotNull('reference_id')
            ->orderBy('cron_order', 'asc')
            ->limit(10)->get()->toArray();
        if (!empty($pendingReports) && count($pendingReports) > 0) {
            foreach ($pendingReports as $key => $reportDetails) {
                Report::where('id', $reportDetails['id'])->increment('cron_order', 1);
                try {
                    if (!empty($reportDetails['reference_id'])) {
                        $rowData = array(
                            "$1" => 'DMT_txn_status_api_lite_common',
                            "$4" => date('Y-m-d', strtotime($reportDetails['created_at'])),
                            "$5" => date('Y-m-d', strtotime($reportDetails['created_at'])),
                            "$6" => $reportDetails['reference_id'],
                        );
                        $library = new iServeUDmt();
                        $responseData = self::sendCurlPost($apiUrl, $header, json_encode($rowData));
                        $responseData = json_decode($responseData,true);
                        if ($responseData['status'] == 200 && isset($responseData['results']) && count($responseData['results']) > 0) {
                            $library->callBackAPi($responseData['results'], $money_provider_id);
                        }else{
                            Log::info("iServeU DMT cron empty response");
                        }
                    }else{
                        Log::info("iServeU DMT cron empty reference id");

                    }
                } catch (\Exception $e) {
                    Log::error("iServeU DMT cron no error received", ["error" => $e->getMessage()]);
                }
            }
        }else{
            Log::info("iServeU DMT cron no records received from reports".$date);
        }
    }

    public function sendCurlPost($url, $header, $api_request_parameters)
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
