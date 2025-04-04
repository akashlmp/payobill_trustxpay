<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use DB;
use Hash;
use Helpers;
use App\Models\Paysprintbank;
use App\Models\User;
use App\Models\Balance;
use App\Models\Provider;
use App\Models\Report;
use App\Models\Aepsreport;

use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;

// paysptint api
use App\Paysprint\Cashdeposit as Cashdeposit;

class CashDepositController extends Controller
{

    public function __construct()
    {


        $this->api_id = 1;
        $this->provider_id = 585;
    }

    public function welcome()
    {
        if (Auth::User()->member->kyc_status != 1) {
            return \redirect('agent/my-profile');
        }
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];

        if ($serviceStatus == 1 && Auth::user()->role_id == 8) {
            $banks = Paysprintbank::where('status_id', 1)->orderBy('bank_name', 'ASC')->get();
            $data = ['page_title' => 'Cash Deposit'];
            return view('agent.cash-deposit.welcome', compact('banks'))->with($data);
        }

        return redirect()->back();
    }

    public function initiateWeb(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|digits:10',
            'adhaar_number' => 'required|digits:12',
            'bank_id' => 'required|exists:paysprintbanks,iinno',
            'BiometricData' => 'required',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'latitude' => 'required',
            'longitude' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $data = $request->only([
            'mobile_number', 'adhaar_number', 'bank_id', 'BiometricData',
            'amount', 'latitude', 'longitude'
        ]);

        $user_id = Auth::id();
        $submerchantid = Auth::user()->paysprint_merchantcode;
        $mode = "WEB";
        $accessmodetype = "SITE";

        return $this->cashDepositMiddle(
            $data['mobile_number'], $data['adhaar_number'], $data['bank_id'],
            $data['BiometricData'], $data['amount'], $data['latitude'],
            $data['longitude'], $mode, $accessmodetype, $user_id, $submerchantid
        );
    }

    public function initiateApp(Request $request)
    {
        $rules = [
            'mobile_number' => 'required|digits:10',
            'adhaar_number' => 'required|digits:12',
            'bank_id' => 'required|exists:paysprintbanks,iinno',
            'BiometricData' => 'required',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'latitude' => 'required',
            'longitude' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $data = $request->only([
            'mobile_number', 'adhaar_number', 'bank_id', 'BiometricData',
            'amount', 'latitude', 'longitude'
        ]);

        $user_id = Auth::id();
        $submerchantid = Auth::user()->paysprint_merchantcode;
        $mode = "APP";
        $accessmodetype = "APP";

        return $this->cashDepositMiddle(
            $data['mobile_number'], $data['adhaar_number'], $data['bank_id'],
            $data['BiometricData'], $data['amount'], $data['latitude'],
            $data['longitude'], $mode, $accessmodetype, $user_id, $submerchantid
        );
    }

    protected function cashDepositMiddle($mobile_number, $adhaar_number, $bank_id, $BiometricData, $amount, $latitude, $longitude, $mode, $accessmodetype, $user_id, $submerchantid)
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::user()->role_id == 8) {
            $ip_address = request()->ip();
            $userDetails = User::find($user_id);
            $opening_balance = $userDetails->balance->aeps_balance;
            $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 10) {
                $provider_id = $this->provider_id;
                $scheme_id = $userDetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $decrementAmount = $amount - $retailer;
                Balance::where('user_id', $user_id)->decrement('aeps_balance', $decrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $aeps_balance = $balance->aeps_balance;
                $aadharNumber = str_repeat("X", strlen($adhaar_number) - 4) . substr($adhaar_number, -4);
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $providers = Provider::find($provider_id);
                $description = "$providers->provider_name  $mobile_number";
                $insert_id = Report::insertGetId([
                    'number' => $aadharNumber,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $this->api_id,
                    'status_id' => 3,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => $retailer,
                    'mode' => $mode,
                    'ip_address' => $ip_address,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $aeps_balance,
                    'wallet_type' => 2,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                $paysprintbanks = Paysprintbank::where('iinno', $bank_id)->first();
                $maskedAadhaar = str_repeat('X', 8) . substr($adhaar_number, -4);
                Aepsreport::insertGetId([
                    'aadhar_number' => $maskedAadhaar,
                    'bank_name' => $paysprintbanks->bank_name ?? '',
                    'created_at' => $ctime,
                    'report_id' => $insert_id,
                ]);
                $library = new Cashdeposit();
                $response = $library->cashDeposit($user_id, $mobile_number, $accessmodetype, $adhaar_number, $latitude, $longitude, $insert_id, $bank_id, $submerchantid, $BiometricData, $amount);
                $status_id = $response['status_id'];
                $message = $response['message'];
                if ($status_id == 1) {
                    $data = $response['data'];
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $data['utr']]);
                    return Response()->json(['status' => 'success', 'message' => 'Transaction Successful..', 'data' => $data]);
                } elseif ($status_id == 2) {
                    Balance::where('user_id', $user_id)->increment('aeps_balance', $decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $aeps_balance = $balance->aeps_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'txnid' => '', 'profit' => 0, 'total_balance' => $aeps_balance]);
                    return Response()->json(['status' => 'failure', 'message' => $message]);
                } else {
                    return Response()->json(['status' => 'pending', 'message' => $message]);
                }
            }

            return response()->json(['status' => 'failure', 'message' => 'Insufficient funds']);
        }

        return response()->json(['status' => 'failure', 'message' => 'Service not active!']);
    }

    function bankList()
    {
        $masterbank = Paysprintbank::where('status_id', 1)->select('iinno', 'bank_name')->get();
        $response = array();
        foreach ($masterbank as $value) {
            $product = array();
            $product["bank_id"] = $value->iinno;
            $product["bank_name"] = $value->bank_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'successful..!', 'bank_list' => $response]);
    }

}
