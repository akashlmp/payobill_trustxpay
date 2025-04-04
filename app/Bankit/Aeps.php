<?php

namespace App\Bankit;


use App\Library\Commission_increment;
use App\Library\GetcommissionLibrary;
use App\Models\Aepsreport;
use App\Models\Balance;
use App\Models\Provider;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class Aeps
{


    public function __construct()
    {
        $mode = 'LIVE'; // LIVE or TEST
        $this->base_url = "";
        $this->agentAuthId = '';
        $this->agentAuthPassword = '';
        $this->apiId = "";
        $this->api_id = 2;
    }

    public function generateToken($retailerId, $pipe)
    {

        $url = $this->base_url . 'AEPS/generatetoken';

        $rowData = [
            "agentAuthId" => md5($this->agentAuthId),
            "agentAuthPassword" => md5($this->agentAuthPassword),
            "retailerId" => $retailerId,
            "apiId" => $this->apiId,
            'pipe' => $pipe
        ];

        try {
            $res = Http::withoutVerifying()
                ->withBasicAuth($this->agentAuthId, $this->agentAuthPassword)
                ->withHeaders(["content-type" => "application/json"])
                ->post($url, $rowData)
                ->json();

            if (isset($res['errorCode']) && $res['errorCode'] == "00") {
                $redirectionUrl = "";
                if (isset($res['data']['token']) && $res['data']['token'] != '') {
                    $redirectionUrl = $this->base_url . "AEPS/login?token=" . $res['data']['token'];
                }
                return ['status' => 'success', 'message' => $res['errorMsg'], 'redirectionUrl' => $redirectionUrl];
            } else {
                return ['status' => 'failure', 'message' => $res['errorMsg']];
            }
        } catch (\Exception $e) {
            $response = [];
            $response['status'] = "failure";
            $response['message'] = $e->getMessage();
            return $response;
        }
    }

    public function webhookCallback($param)
    {
        if ($param['status'] == 0) {
            if ($param['Service'] == "Cash Withdrawl") {
                $userdetails = User::where('cms_agent_id', $param['Agent_Id'])->where('aeps_onboard_status', 1)->first();
                if (!empty($userdetails) && $userdetails) {

                    $user_id = $userdetails->id;
                    $mobile_number = $param['mobileNo'];
                    $amount = $param['Amount'];
                    $aadhar_number = $param['uId'];
                    $bank_name = $param['bankName'];
                    $bankrrn = $param['rrn'] ?? '';


                    $scheme_id = $userdetails->scheme_id;
                    $opening_balance = $userdetails->balance->aeps_balance;
                    $request_ip = request()->ip();
                    $provider_id = 319;
                    $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $description = "$providers->provider_name $mobile_number";

                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount, 2);
                    $retailer = $commission['retailer'];
                    $distributor = $commission['distributor'];
                    $sdistributor = $commission['sdistributor'];
                    $sales_team = $commission['sales_team'];
                    $referral = $commission['referral'];

                    $mode = "WEB";
                    $client_id = "";
                    $tds = 0;
                    if ($retailer) {
                        $tds = ($retailer * 5) / 100;
                    }
                    $insert_id = Report::insertGetId([
                        'number' => $mobile_number,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $this->api_id,
                        'status_id' => 2,
                        'client_id' => $client_id,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => 0,
                        'mode' => $mode,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $opening_balance,
                        'wallet_type' => 2,
                        'row_data' => json_encode($param->all()),
                        'txnid' => $bankrrn,
                        'tds' => $tds
                    ]);

                    Aepsreport::insertGetId([
                        'aadhar_number' => $aadhar_number,
                        'bank_name' => $bank_name,
                        'created_at' => $ctime,
                        'report_id' => $insert_id,
                    ]);

                    if ($param['txnStatus'] == 'Success') {

                        $increment_amount = ($amount + $retailer) - $tds;
                        Balance::where('user_id', $user_id)->increment('aeps_balance', $increment_amount);

                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->aeps_balance;

                        Report::where('id', $insert_id)->update(['status_id' => 6, 'profit' => $retailer, 'total_balance' => $user_balance]);

                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $mobile_number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
                    }
                    return response()->json(['Status' => 0, 'Description' => 'Success']);
                } else {
                    return response()->json(['Status' => 1, 'Description' => 'Your onboarding process is pending. Please contact to support']);
                }
            } elseif ($param['Service'] == "Balance Enquiry") {

                return response()->json(['Status' => 0, 'Description' => 'Success']);

            } elseif ($param['Service'] == "Mini Statement") {

                return response()->json(['Status' => 0, 'Description' => 'Success']);

            } elseif ($param['Service'] == "Aadhaar Pay") {

                return response()->json(['Status' => 0, 'Description' => 'Success']);
            } else {

                return response()->json(['Status' => 1, 'Description' => 'Service not found.']);
            }
        } else {
            return response()->json(['Status' => 1, 'Description' => 'Failure']);
        }
    }
}
