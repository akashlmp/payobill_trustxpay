<?php

namespace App\Jobs;

use App\Models\MerchantUsers;
use App\Models\MerchantPayoutapiLog;
use App\Models\MerchantTestTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PendingPayoutToComplete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction_id;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $merchant_transaction = MerchantTestTransaction::where('reference_id', $this->transaction_id)
            ->first();

        if (empty($merchant_transaction)) {
            return;
        }

        if ($merchant_transaction->account_number == '11112222333344') {
            $merchant_transaction->status_id = 1;
            $merchant_transaction->failure_reason = 'Payout Successfully received.';
        } elseif ($merchant_transaction->account_number == '10002000300040') {
            $merchant_transaction->status_id = 2;
            $merchant_transaction->failure_reason = 'Payout request failed.';
        }

        $merchant_transaction->save();

        $merchant = MerchantUsers::select('id', 'secrete_key', 'callback_url')
            ->where('id', $merchant_transaction->merchant_id)
            ->first();

        $request_data = $this->getResponseArray($merchant_transaction, $merchant);

        try {
            $this->curlRequest($merchant->callback_url, $request_data);
        } catch (Exception $e) {
            \Log::info(['callback_error_' . $this->transaction_id => $e->getMessage()]);
        }
    }

    public function getResponseArray($merchant_transaction, $merchant)
    {
        $status = \DB::table('statuses')
            ->where('id', $merchant_transaction->status_id)
            ->value('status');

        $signature = hash('sha256', $merchant_transaction['account_number'].$merchant_transaction['amount'].Str::upper($status).$merchant_transaction['ben_ifsc'].$merchant_transaction['merchant_reference_id'].$merchant->secrete_key);

        $return_array = [
            'success' => true,
            'status' => Str::upper($status),
            'message' => $merchant_transaction['failure_reason'],
            'transaction_id' => $merchant_transaction['reference_id'],
            'merchant_reference_id' => $merchant_transaction['merchant_reference_id'],
            'ben_name' => $merchant_transaction['ben_name'],
            'ben_account_number' => $merchant_transaction['account_number'],
            'ben_ifsc' => $merchant_transaction['ben_ifsc'],
            'ben_phone_number' => (int) $merchant_transaction['ben_phone_number'],
            'ben_bank_name' => $merchant_transaction['ben_bank_name'],
            'transfer_type' => $merchant_transaction['mode'],
            'amount' =>  (int) $merchant_transaction['amount'],
            'signature' =>  $signature,
            'timestamp' =>  $merchant_transaction['created_at'],
        ];

        if (isset($merchant_transaction['utr']) && !empty($merchant_transaction['utr']) && $merchant_transaction->status_id == 1) {
            $return_array['utr'] = $merchant_transaction['utr'];
        }

        $api_log = new MerchantPayoutapiLog;
        $api_log->storeData($merchant_transaction, $return_array);

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
