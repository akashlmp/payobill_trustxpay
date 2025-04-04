<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Report;
use App\Models\MerchantPayoutapiLog;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StaticQRPayinCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reference_id;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($reference_id)
    {
        $this->reference_id = $reference_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $report = Report::where('reference_id', $this->reference_id)
            ->first();

        if (empty($report)) {
            return;
        }

        $merchant_api_log = MerchantPayoutapiLog::where('reference_id', $this->reference_id)
            ->where('type', 1)
            ->first();

        if (empty($merchant_api_log)) {
            return;
        }

        $merchant_api_response = json_decode($merchant_api_log['response'], true);

        $merchant = User::select('secrete_key', 'callback_url')
            ->where('id', $report->user_id)
            ->first();

        $input = [
            'status_id' => $report['status_id'],
            'message' => $report['failure_reason'],
            'merchant_user_id' => $report->user_id,
            'merchant_reference_id' => $report['merchant_reference_id'],
            'reference_id' => $report['reference_id'],
            'customer_name' => $merchant_api_response['customer_name'],
            'customer_phone' => $merchant_api_response['customer_phone'],
            'customer_email' => $merchant_api_response['customer_email'],
            'amount' =>  (float) $merchant_api_response['amount'],
            'timestamp' =>  $merchant_api_response['timestamp'],
            'type' => 3,
            'mode' => 1
        ];
        $input['signature'] = hash('sha256', $input['reference_id'].number_format((float)$input['amount'], 2, '.', '').$merchant['secrete_key']);
        $request_data = $this->getResponseArray($input);

        try {
            $this->curlRequest($merchant->callback_url, $request_data);
        } catch (\Exception $e) {
            Log::info("callback_error_",['callback_error_' . $this->reference_id => $e->getMessage()]);
        }
    }

    public function getResponseArray($input)
    {
        $status = DB::table('statuses')
            ->where('id', $input['status_id'])
            ->value('status');

        $return_array = [
            'success' => true,
            'status' => Str::upper($status),
            'message' => $input['message'],
            'merchant_reference_id' => $input['merchant_reference_id'],
            'reference_id' => $input['reference_id'],
            'customer_name' => $input['customer_name'],
            'customer_phone' => $input['customer_phone'],
            'customer_email' => $input['customer_email'],
            'amount' =>  (float) $input['amount'],
            'timestamp' =>  $input['timestamp'],
            'event' => 2,
            'signature' =>  $input['signature'],
        ];

        if (
            isset($return_array['status']) && $return_array['status'] == 'PENDING' &&
            isset($input['payment_link']) && !empty($input['payment_link'])
        ) {
            $return_array['payment_link'] = $input['payment_link'];
        }

        $api_log = new MerchantPayoutapiLog;
        $api_log->storeData($input, $return_array);

        return $return_array;
    }

    public function curlRequest($url, $json_array)
    {
        if (is_array($json_array)) {
            $json_array = json_encode($json_array);
        }

        $headers = [
            'Content-Type: application/json'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_array);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);

            return 404;
        }

        curl_close($curl);

        return $http_code;
    }
}
