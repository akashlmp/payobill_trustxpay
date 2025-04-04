<?php

namespace App\Easebuzz;

use App\Models\Balance;
use Helpers;
use App\Models\User;
use App\Models\Report;
use App\Models\Apiresponse;
use App\Models\Api;
use App\Jobs\DynamicQRPayinCallback;
use http\Env\Response;
use App\Library\GetcommissionLibrary;

class DynamicQr
{
    public function __construct()
    {
        $this->api_id = 4;
        $this->provider_id = 592;
        // $this->base_url = 'https://wire.easebuzz.in';
        $this->base_url = 'https://stoplight.io/mocks/easebuzz/neobanking/141702012';
    }

    public function doTransaction($parameters, $authorization)
    {
        $url = $this->base_url . '/api/v1/insta-collect/order/create/';

        $header = [
            'Accept: application/json',
            'Content-Type: application/json',
            'WIRE-API-KEY: ' . $parameters['key'],
            'Authorization: ' . $authorization
        ];

        $json_data = json_encode($parameters);

        $responseCurl = $this->sendCurlPost($url, $header, $json_data);

        Apiresponse::insertGetId(['message' => $responseCurl, 'api_type' => $this->api_id, 'request_message' => $url . '?' . json_encode($parameters), 'response_type' => 'dynamicQr']);

        $response_array = json_decode($responseCurl, true);

        if (isset($response_array['data']['transaction_order']['payment_link']) && !empty($response_array['data']['transaction_order']['payment_link'])) {
            return ['status' => 'pending', 'payment_link' => $response_array['data']['transaction_order']['payment_link'], 'message' => 'QR code generated successfully.', 'provider_order_id' => $response_array['data']['transaction_order']['id'] ?? null, 'api_response' => $responseCurl];
        } else {
            return ['status' => 'failure', 'message' => $response_array['message'] ?? 'Something went wrong. Please try again.', 'provider_order_id' => $response_array['data']['transaction_order']['id'] ?? null, 'api_response' => $responseCurl];
        }
    }

    public function webhookOrderCreated($callback_data)
    {
        if (
            isset($callback_data['data']['status']) &&
            isset($callback_data['data']['id']) && !empty($callback_data['data']['id']) &&
            isset($callback_data['data']['Authorization']) && !empty($callback_data['data']['Authorization'])
        ) {
            $report = Report::where('provider_order_id', $callback_data['data']['id'])
                ->first();

            if (!empty($report)) {
                // nothing to do incase already created
            } else {
                $insert_id = Report::insertGetId([
                    'user_id' => $user->id,
                    'provider_id' => $this->provider_id,
                    'provider_api_from' => $this->api_id,
                    'number' => $input['customer_phone'],
                    'amount' => $input['amount'],
                    'api_id' => $this->api_id,
                    'ip_address' => request()->ip(),
                    'created_at' => $input['created_at'],
                    'updated_at' => $input['created_at'],
                    'txnid' => $response['transaction_id'],
                    'reference_id' => $input['reference_id'],
                    'merchant_reference_id' => $input['merchant_reference_id'],
                    'opening_balance' => $opening_balance,
                    'total_balance' => $total_balance,
                    'charges_percentage' => $charges_percentage,
                    'mode' => $input['mode'],
                    'description' => $description,
                    'credit_by' => $credit_by,
                    'status_id' => $input['status_id'],
                    'wallet_type' => $wallet_type,
                    'decrementAmount' => $decrementAmount,
                ]);
            }
        }
    }

    public function webhookStatusUpdate($callback_data)
    {
        if (
            isset($callback_data['data']['status']) &&
            isset($callback_data['data']['transactions'][0]['status']) &&
            isset($callback_data['data']['transactions'][0]['id']) && !empty($callback_data['data']['transactions'][0]['id']) &&
            isset($callback_data['data']['transactions'][0]['amount']) && !empty($callback_data['data']['transactions'][0]['amount']) &&
            isset($callback_data['data']['id']) && !empty($callback_data['data']['id']) &&
            isset($callback_data['data']['merchant_request_number']) && !empty($callback_data['data']['merchant_request_number']) &&
            isset($callback_data['data']['Authorization']) && !empty($callback_data['data']['Authorization'])
        ) {
            $report = Report::where('provider_order_id', $callback_data['data']['id'])
                ->where('reference_id', $callback_data['data']['merchant_request_number'])
                ->first();

            if (empty($report)) {
                return response()->json(['message' => 'resource not found.'], 406);
            }

            if ($callback_data['data']['transactions'][0]['amount'] != $report['amount']) {
                return response()->json(['message' => 'amount not match.'], 406);
            }

            $status = $this->getStatus($callback_data['data']['transactions'][0]['status']);
            $userdetails = User::find($report->user_id);
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

            list($master_id, $wire_key, $salt_key) = getCredentials($userdetails->credentials_id);
            $authentication = hash('sha512',
                $wire_key.'|'.
                $report['reference_id'].'|'.
                number_format((float)$report['amount'], 2, '.', '').'|'.
                '|'. // per_transaction_amount
                $callback_data['data']['transactions'][0]['status'].'|'.
                '|'. // udf1
                '|'. // udf2
                '|'. // udf3
                '|'. // udf4
                '|'. // udf5
                $salt_key
            );

            if ($authentication !== $callback_data['data']['Authorization']) {
                return response()->json(['message' => 'Authorization not match.'], 406);
            }

            if (!empty($report) && $report['status_id'] != 4) {
                $report->txnid = $callback_data['data']['transactions'][0]['id'];

                // pending transaction
                if (in_array($callback_data['data']['transactions'][0]['status'], ['unsettled', 'received', 'timed_out'])) {
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
                } elseif ($callback_data['data']['transactions'][0]['status'] == 'failure') {
                    $report->failure_reason = $callback_data['data']['transactions'][0]['message'] ?? $callback_data['data']['message'] ?? 'Something went wrong. Try again.';
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
                } elseif ($callback_data['data']['transactions'][0]['status'] == 'pending') {
                    $report->status_id = 3;
                } elseif (in_array($callback_data['data']['transactions'][0]['status'], ['refunded', 'partially_refunded'])) {
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

                $callback_url = User::where('id', $report->user_id)
                    ->value('callback_url');

                if (!empty($callback_url)) {
                    DynamicQRPayinCallback::dispatch($report['reference_id']);
                }
            }
        }
    }

    public function getStatus($status)
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
                return "";
        }
    }

    public function sendCurlPost($url, $header, $api_request_parameters, $method = 'POST')
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
