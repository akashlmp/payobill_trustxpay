<?php

namespace App\Jobs;

use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Report;
use App\Models\User;
use App\Library\GetcommissionLibrary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EasebuzzDynamicQRStatusUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reference_id, $api_id, $provider_id, $base_url;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($reference_id)
    {
        $this->api_id = 4;
        $this->provider_id = 592;
        // $this->base_url = 'https://wire.easebuzz.in';
        $this->base_url = 'https://stoplight.io/mocks/easebuzz/neobanking/141702012';

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

        $userdetails = User::select('credentials_id', 'secrete_key', 'callback_url', 'scheme_id')
            ->where('id', $report->user_id)
            ->first();

        if (empty($userdetails)) {
            return;
        }

        $credentials = getCredentials($userdetails->credentials_id);

        $response_data = $this->easebuzzStatus($report['provider_order_id'], $credentials);

        if (
            isset($response_data['data']['transaction_order']['transactions'][0]['amount']) &&
            isset($response_data['data']['transaction_order']['transactions'][0]['status']) && !empty($response_data['data']['transaction_order']['transactions'][0]['status'])
        ) {
            // amount mismatch
            if ($response_data['data']['transaction_order']['transactions'][0]['amount'] != $report['amount']) {
                $response_data['data']['transaction_order']['transactions'][0]['status'] = 'failure';
                $response_data['data']['transaction_order']['transactions'][0]['message'] = 'Amount mismatched with transaction.';
            }

            $status = $this->getStatus($response_data['data']['transaction_order']['transactions'][0]['status']);
            $user_id = $report->user_id;
            $amount = $report->amount;
            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $this->provider_id, $amount);
            $charges = $commission['retailer'];
            $increment_amount = $charges;
            $profit = $charges;
            $result = $report;
            $insert_id = $result->id;

            if ($report['status_id'] != 4) {
                $report->txnid = $response_data['data']['transaction_order']['transactions'][0]['id'];

                // success transaction
                if (in_array($response_data['data']['transaction_order']['transactions'][0]['status'], ['unsettled', 'received', 'timed_out'])) {
                    $report->failure_reason = 'Transaction completed.';
                    if ($result->status_id != 1) {
                        Balance::where('user_id', $user_id)->decrement('user_balance', $increment_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;
                        $report->profit = $profit;
                        $report->total_balance = $user_balance;
                        $report->decrementAmount = $increment_amount;
                    }
                    $report->status_id = 1;
                } elseif ($response_data['data']['transaction_order']['transactions'][0]['status'] == 'failure') {
                    $report->failure_reason = $response_data['data']['transaction_order']['transactions'][0]['message'] ?? $response_data['data']['message'] ?? 'Something went wrong. Try again.';
                    if ($status == 2) {
                        if ($result->status_id == 1) {
                            Balance::where('user_id', $user_id)->increment('user_balance', $result->decrementAmount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            $report->total_balance = $user_balance;
                        }
                        $report->profit = 0;
                    }
                    $report->status_id = 2;
                } elseif ($response_data['data']['transaction_order']['transactions'][0]['status'] == 'pending') {
                    $report->status_id = 3;
                } elseif (in_array($response_data['data']['transaction_order']['transactions'][0]['status'], ['refunded', 'partially_refunded'])) {
                    $report->failure_reason = 'Transaction refunded.';
                    if ($result->status_id == 1) {
                        Balance::where('user_id', $user_id)->increment('user_balance', $result->decrementAmount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;
                        $report->profit = 0;
                        $report->total_balance = $user_balance;
                    }
                    $report->status_id = 4;
                }

                $report->update();

                if (!empty($userdetails->callback_url)) {
                    DynamicQRPayinCallback::dispatch($report['reference_id']);
                }
            }
        }
    }

    protected function easebuzzStatus($provider_order_id, $credentials)
    {
        $url = $this->base_url.'/api/v1/insta-collect/order/'.$provider_order_id.'/?key='.$credentials[1];

        $authentication = hash('sha512',
            $credentials[1].'|'.
            $provider_order_id.'|'.
            $credentials[2]
        );

        $headers = [
            'Accept: application/json',
            'Authorization: ' . $authentication,
            'WIRE-API-KEY: ' . $credentials[1]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        Apiresponse::insert(['message' => $response, 'api_type' => $this->api_id, 'request_message' => $url, 'response_type' => 'dynamicQrStatus']);

        return json_decode($response, true);
    }

    protected function getStatus($status)
    {
        switch ($status) {
            case 'unsettled':
            case 'received':
            case 'timed_out';
                return 1;
            case 'refunded':
            case 'partially_refunded':
                return 4;
            case 'failure':
                return 2;
            case 'pending':
                return 3;
            default:
                return '';
        }
    }
}
