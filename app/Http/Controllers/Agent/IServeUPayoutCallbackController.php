<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use App\Models\MerchantUsers;
use App\Models\MerchantPayouts;
use App\Models\MerchantTransactions;
use App\Models\AepsPayoutRequest;
use App\Models\Balance;
use Illuminate\Http\Request;
use Validator;
use DB;
use Hash;
use App\Jobs\PayoutCallback;
use App\Models\Apiresponse;
use Log;
use App\Bankit\Dmt as BankitDmt;
use App\Models\Report;
use App\Models\User;

class IServeUPayoutCallbackController extends Controller
{
    public function __construct() {}
    function payoutCallBackRequest(Request $request)
    {
        Apiresponse::insertGetId(['message' => json_encode($request->all()), 'api_type' => 3, 'response_type' => "iServeU Payout"]);
        try {
            $param = $request->all();

            // merchant payout api
            if (
                isset($param['txnId']) && !empty($param['txnId']) &&
                isset($param['param_b']) && $param['param_b'] == 'OTHER'
            ) {

                $merchant_transaction = MerchantTransactions::where('transaction_id', $param['txnId'])
                    ->first();
                if (empty($merchant_transaction)) {
                    return '{"status":401,"statusDesc":"invalid transaction id"}';
                }

                $payout_record = MerchantPayouts::where('transaction_id', $param['txnId'])
                    ->first();
                if (empty($payout_record)) {
                    return '{"status":401,"statusDesc":"invalid transaction id"}';
                }

                if (isset($param['Status']) && $param['Status'] == 'SUCCESS') {
                    $merchant_transaction->status_id = 1;
                    $merchant_transaction->failure_reason = $param['statusDesc'] ?? 'Payout success.';
                    $merchant_transaction->utr = $param['rrn'] ?? null;
                    $merchant_transaction->save();

                    $payout_record->status = 1;
                    $payout_record->save();

                    $return_data = [
                        'status' => 0,
                        'statusDesc' => 'success'
                    ];
                } elseif (isset($param['Status']) && $param['Status'] == 'FAILED') {
                    if ($merchant_transaction->status_id == 3) {

                        $merchant = MerchantUsers::select('id', 'wallet')
                            ->where('id', $merchant_transaction->merchant_id)
                            ->first();
                        if (empty($merchant)) {
                            return '{"status":401,"statusDesc":"invalid transaction id"}';
                        }

                        $after_balance = $merchant->wallet + $merchant_transaction->decrementAmount;

                        $merchant->wallet = $after_balance;
                        $merchant->save();

                        $merchant_transaction->status_id = 2;
                        $merchant_transaction->profit = 0;
                        $merchant_transaction->total_balance = $after_balance;
                        $merchant_transaction->failure_reason = $param['statusDesc'] ?? 'Failed.';
                        $merchant_transaction->save();

                        $payout_record->status = 2;
                        $payout_record->save();
                    }

                    $return_data = [
                        'status' => 0,
                        'statusDesc' => 'success'
                    ];
                } elseif (isset($param['Status']) && $param['Status'] == 'INPROGRESS') {
                    return '{"status":-5,"message":"INPROGRESS"}';
                } else {
                    return '{"status":401,"statusDesc":"invalid status id"}';
                }

                PayoutCallback::dispatch($param['txnId']);

                return response()->json($return_data);
            }

            $txnId = $param['txnId'];
            $status = $param['status'];
            $failure_reason = ($param['statusDesc']) ? $param['statusDesc'] : '';
            if (isset($param['txnId']) && !empty($param['txnId'])) {
                $payout = AepsPayoutRequest::where('transaction_id', $txnId)->first();
                if ($payout) {
                    if ($status == 'SUCCESS') {
                        $report_id = $payout->report_id;
                        $reports = Report::find($report_id);
                        if ($reports) {
                            $rrn = $param['rrn'];
                            Report::where('id', $report_id)->update([
                                'payid' => $rrn,
                                'status_id' => 1,
                            ]);
                            AepsPayoutRequest::where('transaction_id', $txnId)->update(['status' => 1]);
                            return '{"status":0,"statusDesc":"success"}';
                        } else {
                            return '{"status":401,"statusDesc":"invalid txnId!"}';
                        }
                    } else if ($status == 'INPROGRESS') {
                        return '{"status":-5,"message":"INPROGRESS"}';
                    } else if ($status == 'FAILED') {
                        AepsPayoutRequest::where('transaction_id', $txnId)->update(['status' => 0]);
                        $report_id = $payout->report_id;
                        $reports = Report::find($report_id);
                        if ($reports) {
                            if ($reports->status_id != 2) {
                                $userdetails = User::find($reports->user_id);
                                $decrementAmount = $reports->decrementAmount;
                                $user_id = $userdetails->id;
                                Balance::where('user_id', $user_id)->increment('aeps_balance', $decrementAmount);
                                $balance = Balance::where('user_id', $user_id)->first();
                                $user_balance = $balance->aeps_balance;
                                Report::where('id', $reports->id)->update(['total_balance' => $user_balance, 'profit' => 0, 'status_id' => 2, 'row_data' => json_encode($param), 'failure_reason' => $failure_reason]);
                            }
                        }
                        return '{"status":1,"statusDesc":"Failure!"}';
                    }
                } else {
                    return '{"status":401,"statusDesc":"invalid transaction id"}';
                }
            } else {
                return '{"status":401,"statusDesc":"transaction id not found"}';
            }
        } catch (\Exception $e) {
            Log::error("Payout callBackRequest ===" . $e->getMessage());
            return ['status' => 401, 'statusDesc' => $e->getMessage()];
        }
    }
}
