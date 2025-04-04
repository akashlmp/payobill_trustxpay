<?php

namespace App\Http\Controllers\Api;

use Helpers;
use App\Traits\Authenticate;
use App\Models\MerchantUsers;
use App\Models\MerchantPayouts;
use App\Models\MerchantPayoutapiLog;
use App\Models\MerchantApiCommissions;
use App\Models\MerchantTransactions;
use App\Http\Controllers\Controller;
use App\Library\MerchantcommissionLibrary;
use App\Library\MerchantCommissionIncrement;
use App\IServeU\Payout as IserveUPayout;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayoutRetailorController extends Controller
{
    use Authenticate;

    protected $payout, $pincode, $api_id, $provider_id;

    public function __construct()
    {
        $this->api_log = new MerchantPayoutapiLog;
        $this->merchant_transaction = new MerchantTransactions;
        $this->merchant_payout = new MerchantPayouts;
        $this->merchant_api_commissions = new MerchantApiCommissions;
        $this->payout_iserveu = new IserveUPayout;
        $this->provider_id = 584;
        $this->api_id = 3;
        $this->pincode = 201301;
    }

    public function store(Request $request)
    {
        $input = $request->only(['ben_name', 'ben_account_number', 'ben_ifsc', 'ben_phone_number', 'ben_bank_name', 'amount', 'ip_address', 'merchant_reference_id', 'transfer_type', 'signature']);

        $return = $this->keyAuthentication(request: $request);
        if($return->getData()->success==false){
            $input['mode'] = 1; // 1=live
            $input['type'] = 1;
            $this->api_log->storeData($input,json_encode($return->getData(),true));
            return $return;
        }

        $api_key = $request->bearerToken();
        $user = User::where('api_key', $api_key)
        ->first();

        // pre($return->getData());

        $validator = Validator::make($input, [
            'ben_name' => 'required|min:2|max:100',
            'ben_account_number' => 'required|string|min:10|max:20',
            'ben_ifsc' => 'required|min:2|max:100',
            'ben_phone_number' => 'required|min:5|max:20',
            'ben_bank_name' => 'required|min:2|max:100',
            'amount' => 'required|regex:/^\d+(\.\d{1,9})?$/',
            'ip_address' => 'required|ip',
            'merchant_reference_id' => 'required|min:3|max:100|alpha_dash:ascii',
            'transfer_type' => 'required|in:IMPS,NEFT',
            'signature' => 'required|min:2|max:100'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'success' => false,
                'message' => 'Validation error, please check errors parameter.',
                'errors' => $errors
            ]);
        }

        $signature = hash('sha256', $input['ben_account_number'].$input['amount'].$input['ben_ifsc'].$input['merchant_reference_id'].$user->secrete_key);

        if ($signature !== $input['signature']) {
            return response()->json([
                'success' => false,
                'message' => 'Signature not verified.'
            ]);
        }

        if (isset($input['merchant_reference_id']) && !empty($input['merchant_reference_id'])) {
            $order_id_exists = Report::where('merchant_reference_id', $input['merchant_reference_id'])
                ->where('merchant_id', $user->id)
                ->exists();

            if ($order_id_exists) {
                $errors = [];
                $errors['merchant_reference_id'][0] = 'Duplicate merchant_reference_id, field must be unique.';

                return response()->json([
                    'success' => false,
                    'message' => 'Validation error, please check errors parameter.',
                    'errors' => $errors
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $input['reference_id'] = Helpers::generateReferenceID();
            $input['created_at'] = date('Y-m-d H:i:s');
            $transfer_type = 0;
            if($input['transfer_type']=="IMPS"){
                $transfer_type = 1;
            }elseif($input['transfer_type']=="NEFT"){
                $transfer_type = 2;
            }
            $library = new MerchantcommissionLibrary;
            $commission = $library->get_commission($user->id, $this->provider_id, $input['amount'], 3,$transfer_type);

            $retailer = $commission['retailer'];
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $gst = ($retailer * 18) / 100;

            // merchant wallet balance check
            $decrementAmount = $input['amount'] + $retailer + $gst;
            if ($user->wallet < $decrementAmount) {
                $errors = [];
                $errors['amount'][0] = 'Merchant wallet balance is low.';
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error, please check errors parameter.',
                    'errors' => $errors
                ]);
            }

            $parameters = [
                'beneName' => $input['ben_name'],
                'beneAccountNo' =>(string) $input['ben_account_number'],
                'beneifsc' => $input['ben_ifsc'],
                'benePhoneNo' => (int) $input['ben_phone_number'],
                'beneBankName' => $input['ben_bank_name'],
                'clientReferenceNo' => $input['reference_id'],
                'amount' => (float) $input['amount'],
                'fundTransferType' => $input['transfer_type'],
                'pincode' => (int) $this->pincode,
                'custName' => $user->first_name,
                'custMobNo' => $user->mobile_number,
                'custIpAddress' => $input['ip_address'],
                'latlong' => $user->latitude.','.$user->longitude,
                'paramA' => 'OTHER',
            ];

            $response = $this->payout_iserveu->doTransaction($parameters);

            $input['message'] = $response['message'] ?? 'Payout Successfully received.';
            $input['api_response'] = $response['api_response'] ?? '{}';
            $input['transaction_id'] = $response['transaction_id'] ?? null;
            $input['utr'] = $response['payid'] ?? null;
            $input['mode'] = 1; // 1=live
            $input['type'] = 1; // 1=api

            if (isset($response['status']) && $response['status'] == 'success') {
                $input['status_id'] = 1;
                $input['status'] = 1;
            } elseif (isset($response['status']) && $response['status'] == 'pending') {
                $input['status_id'] = 3;
                $input['status'] = 0;
            } else {
                $retailer = 0;
                $decrementAmount = 0;
                $gst = 0;
                $input['status_id'] = 2;
                $input['status'] = 2;
            }

            $transaction_data = [
                'merchant_id' => $user->id,
                'provider_id' => $this->provider_id,
                'account_number' => $input['ben_account_number'],
                'merchant_reference_id' => $input['merchant_reference_id'],
                'reference_id' => $input['reference_id'],
                'transaction_id' => $input['transaction_id'],
                'utr' => $input['utr'],
                'opening_balance' => $user->wallet,
                'amount' => $input['amount'],
                'profit' => $retailer,
                'total_balance' => $user->wallet - $decrementAmount,
                'tds' => 0,
                'decrementAmount' => $decrementAmount,
                'gst' => $gst,
                'description' => 'Payout to ' . $input['ben_account_number'],
                'mode' => 'API',
                'status_id' => $input['status_id'],
                'failure_reason' => $input['message'],
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'ip_address' => $input['ip_address'],
                'created_at' => $input['created_at'],
            ];

            $transaction = $this->merchant_transaction->storeData($transaction_data);

            // update user wallet for success/pending response
            if (isset($response['status']) && in_array($response['status'], ['success', 'pending'])) {
                $user->wallet = $transaction_data['total_balance'];
                $user->save();
            }

            $payout_data = [
                'merchant_id' => $user->id,
                'merchant_reference_id' => $input['merchant_reference_id'],
                'reference_id' => $input['reference_id'],
                'transaction_id' => $input['transaction_id'],
                'utr' => $input['utr'],
                'bank_name' => $input['ben_bank_name'],
                'account_number'=>$input['ben_account_number'],
                'ifsc' => $input['ben_ifsc'],
                'bene_name' => $input['ben_name'],
                'bene_phone_number' => $input['ben_phone_number'],
                'mode' => $input['transfer_type'],
                'amount' => $input['amount'],
                'status' => $input['status'],
                'created_at' => $input['created_at'],
            ];

            $payout = $this->merchant_payout->storeData($payout_data);

            if (isset($response['status']) && in_array($response['status'], ['success', 'pending'])) {
                $library = new MerchantcommissionLibrary();
                $apiComms = $library->getApiCommission(3, $this->provider_id, $input['amount'],$user->id,$transfer_type);
                $apiCommission = $apiComms['apiCommission'];
                $commissionType = $apiComms['commissionType'];
                $library = new MerchantCommissionIncrement();
                $library->updateApiComm($user->id, $this->provider_id, $this->api_id, $input['amount'], $retailer, $d, $sd, $st, $rf, $apiCommission, $transaction->id, $commissionType);
            }

            DB::commit();
            return response()->json($this->getResponseArray($input));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Server error, please contact trustxpay support',
            ]);
        }

    }

    public function status(Request $request)
    {
        $input = $request->only(['reference_id', 'merchant_reference_id']);

        $api_key = $request->bearerToken();

        // if api_key is not included in request
        if (empty($api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found.'
            ]);
        }

        $user = DB::table('merchant')
            ->select('id', 'status', 'is_ip_whiltelist')
            ->where('api_key', $api_key)
            ->first();

        // if invalid api_key provided
        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key provided.'
            ]);
        }

        $input['merchant_id'] = $user->id;

        // if merchant disabled
        if ($user->status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant account disabled.'
            ]);
        }

        // if merchant server not whitelisted
        if ($user->is_ip_whiltelist == 1) {
            $ips = json_decode($user->server_ip, true);
            if (empty($ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP(' . \Request::ip() . ') not added to whitelist.'
                ]);
            }
            if (!in_array(\Request::ip(), $ips)) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP(' . \Request::ip() . ') not whitelisted.'
                ]);
            }
        }

        $validator = Validator::make($input, [
            'merchant_reference_id' => 'required_without:reference_id|min:3|max:100',
            'reference_id' => 'required_without:merchant_reference_id|min:10|max:100',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'success' => false,
                'message' => 'Validation error, please check errors paramter.',
                'errors' => $errors
            ]);
        }

        $transaction = $this->merchant_transaction->getApiData($input);

        if (empty($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid details provided.'
            ]);
        }

        $payout = $this->merchant_payout->getApiData($input);

        if (empty($payout)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid details provided.'
            ]);
        }

        $transaction = $transaction->toArray();
        $transaction['ben_name'] = $payout['ben_name'];
        $transaction['ben_ifsc'] = $payout['ben_ifsc'];
        $transaction['ben_phone_number'] = $payout['ben_phone_number'];
        $transaction['ben_bank_name'] = $payout['ben_bank_name'];

        return response()->json($this->getResponseArray($transaction));
    }

    public function getResponseArray($input)
    {
        $status = \DB::table('statuses')
            ->where('id', $input['status_id'])
            ->value('status');

        $return_array = [
            'success' => true,
            'status' => Str::upper($status),
            'message' => $input['message'],
            'transaction_id' => $input['reference_id'],
            'merchant_reference_id' => $input['merchant_reference_id'],
            'ben_name' => $input['ben_name'],
            'ben_account_number' => $input['ben_account_number'],
            'ben_ifsc' => $input['ben_ifsc'],
            'ben_phone_number' => (int) $input['ben_phone_number'],
            'ben_bank_name' => $input['ben_bank_name'],
            'transfer_type' => $input['transfer_type'],
            'amount' =>  (float) $input['amount'],
            'timestamp' =>  $input['created_at'],
        ];

        if (isset($input['utr']) && !empty($input['utr'])) {
            $return_array['utr'] = $input['utr'];
        }

        $this->api_log->storeData($input, $return_array);

        return $return_array;
    }

    public function sendCurlPost($input)
    {
        $transaction_id = 'TRN'.Str::upper(Str::random(4)).time().Str::upper(Str::random(2));

        if ($input['ben_account_number'] == '12345678901234') {
            return [
                'status' => 'success',
                'message' => 'Payout Successfully received.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } elseif ($input['ben_account_number'] == '11112222333344') {
            return [
                'status' => 'pending',
                'message' => 'Payout pending.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } elseif ($input['ben_account_number'] == '10002000300040') {
            return [
                'status' => 'pending',
                'message' => 'Payout pending.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } elseif ($input['ben_account_number'] == '50004000300021') {
            return [
                'status' => 'failed',
                'message' => 'Payout request failed.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => 'Payout request failed.',
                'api_response' => '{}',
                'transaction_id' => $transaction_id,
                'payid' => time(),
            ];
        }
    }

    public function sendCurlPostt($url, $header, $parameters, $method = 'POST')
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
