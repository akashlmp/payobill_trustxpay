<?php

namespace App\Http\Controllers;

use App\IServeU\Dmt as iServeUDmt;
use App\Models\Apicommission;
use App\Models\Commission;
use App\Models\Provider;
use App\Models\State;
use App\Models\Zipcodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Report;
use App\Models\User;
use App\Paysprint\Apicredentials as PaysprintApicredentials;
use Illuminate\Support\Facades\Log;

class ScriptController extends Controller
{
    public function bbps_provider_store()
    {
        $service_id = 32;
        $operators = DB::table('operators')->get();
        foreach ($operators as $op) {
            $provider = new Provider();
            $provider->operator_id = $op->operator_id ?? null;
            $provider->category_id = $op->category_id ?? null;
            $provider->bank_id = $op->bank_id ?? null;
            $provider->provider_name = $op->name ?? null;
            $provider->view_bill = $op->view_bill ?? 0;
            $provider->bbps_enabled = $op->bbps_enabled ?? 0;
            $provider->input = $op->input ?? null;
            $provider->min_amount = $op->minimum_transaction_amount ?? null;
            $provider->service_id = $service_id;
            $provider->api_id = 0;
            $provider->status_id = 1;
            $provider->save();
        }

        echo 'Success';
    }

    public function storeZipcodes()
    {
        $filename = public_path('assets/all_india_pin_code.csv');
        $file = fopen($filename, "r");
        $i = 0;
        while (($data = fgetcsv($file, 200, ",")) !== FALSE) {
            if ($i != 0) {
                // pre($data);
                $zip = $data[1];
                $city = $data[8];
                $state = $data[9];
                $check = Zipcodes::where('zipcode', $zip)->where('city', $city)->where('state', $state)->exists();
                if (!$check) {
                    $new = new Zipcodes();
                    $new->zipcode = $zip;
                    $new->city = $city;
                    $new->state = $state;
                    $new->save();
                }
            }
            $i++;
        }
    }

    public function getZipcodeDetails(Request $request)
    {
        $data = Zipcodes::where('zipcode', $request->zipcode)->first();
        if ($data) {
            return ['status' => 1, 'data' => $data];
        } else {
            return ['status' => 0, 'data' => $data];
        }
    }

    public function storeCMSProvider()
    {
        $filename = public_path('assets/paysprint_cms.csv');
        $file = fopen($filename, "r");
        $i = 0;
        $service_id = 25;
        while (($data = fgetcsv($file, 200, ",")) !== FALSE) {
            if ($i != 0) {
                $provider_name = $data[0];
                $paysprint_biller_id = $data[1];
                $check = Provider::where('provider_name', $provider_name)->where('paysprint_biller_id', $paysprint_biller_id)
                    ->where('service_id', $service_id)->exists();
                if (!$check) {
                    $new = new Provider();
                    $new->provider_name = $provider_name;
                    $new->paysprint_biller_id = $paysprint_biller_id;
                    $new->service_id = $service_id;
                    $new->api_id = 1;
                    $new->save();
                }
            }
            $i++;
        }
    }

    public function storeCMSCommission()
    {
        $filename = public_path('assets/paysprint_cms.csv');
        $file = fopen($filename, "r");
        $i = 0;
        $service_id = 25;
        while (($data = fgetcsv($file, 200, ",")) !== FALSE) {
            // pre($data);
            if ($i != 0) {
                $provider_name = $data[0];
                $paysprint_biller_id = $data[1];
                $r = $data[3];
                $d = $data[4];
                $providers = Provider::where('provider_name', $provider_name)->where('paysprint_biller_id', $paysprint_biller_id)
                    ->where('service_id', $service_id)->first();
                if ($providers) {
                    $scheme_id = 2;
                    $provider_id = $providers->id;
                    $min_amount = 1;
                    $max_amount = 10000000;
                    $provider_commission_type = 1;
                    $type = 0;
                    $st = 0;
                    $sd = 0;
                    $referral = 0;

                    $checkCom = Commission::where('scheme_id', $scheme_id)
                        ->where('service_id', '=', $service_id)
                        ->where('provider_id', '=', $provider_id)
                        ->where('provider_commission_type', '=', $provider_commission_type)
                        ->first();
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    if ($checkCom) {
                        $checkCom->d = $d;
                        $checkCom->r = $r;
                        $checkCom->save();
                    } else {
                        Commission::insertGetId([
                            'provider_id' => $provider_id,
                            'scheme_id' => $scheme_id,
                            'service_id' => $providers->service_id,
                            'min_amount' => $min_amount,
                            'max_amount' => $max_amount,
                            'provider_commission_type' => $provider_commission_type,
                            'st' => $st,
                            'sd' => $sd,
                            'd' => $d,
                            'r' => $r,
                            'referral' => $referral,
                            'user_id' => 1,
                            'type' => $type,
                            'created_at' => $ctime,
                            'status_id' => 1,
                        ]);
                    }
                }
            }
            $i++;
        }
    }

    public function storePayobillCMSCommission()
    {
        $filename = public_path('assets/paysprint_cms.csv');
        $file = fopen($filename, "r");
        $i = 0;
        $service_id = 25;
        while (($data = fgetcsv($file, 200, ",")) !== FALSE) {
            // pre($data);
            if ($i != 0) {
                $provider_name = $data[0];
                $paysprint_biller_id = $data[1];
                $commission = $data[2];
                $providers = Provider::where('provider_name', $provider_name)->where('paysprint_biller_id', $paysprint_biller_id)
                    ->where('service_id', $service_id)->first();
                if ($providers) {
                    $provider_id = $providers->id;
                    $min_amount = 1;
                    $max_amount = 10000000;

                    Apicommission::where('commission_type', "commission")
                        ->where('service_id', '=', $service_id)
                        ->where('provider_id', '=', $provider_id)
                        ->where('api_id', '=', 1)
                        ->delete();

                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Apicommission::insertGetId([
                        'provider_id' => $provider_id,
                        'api_id' => 1,
                        'service_id' => $providers->service_id,
                        'min_amount' => $min_amount,
                        'max_amount' => $max_amount,
                        'commission' => $commission,
                        'user_id' => 1,
                        'type' => 0,
                        'commission_type' => "commission",
                        'created_at' => $ctime,
                    ]);
                }
            }
            $i++;
        }
    }

    public function updateStateData()
    {
        $states = State::get();
        foreach ($states as $s) {
            $s->name = ucwords(strtolower($s->name));
            $s->save();
        }

        $states = Zipcodes::get();
        foreach ($states as $s) {
            $s->state = ucwords(strtolower($s->state));
            $s->save();
        }
    }

    public function check_dmt_status(Request $request)
    {

        $mode = 'LIVE';
        $library = new PaysprintApicredentials();
        $response = $library->getCredentials($mode);
        $apiurl = $response['base_url'] . 'api/v1/service/dmt/transact/transact/querytransact';

        $jwt_header = $response['jwt_header'];
        $api_key = $response['api_key'];
        $partner_id = $response['partner_id'];
        $id = $request->id;
        $reportDetails = Report::where('id', $id)->first()->toArray();
        $token = self::generateToken($jwt_header, $partner_id, $api_key);
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Token: $token"
        );
        $rowData = array(
            "referenceid" => $reportDetails['id']
        );
        try {
            $response = self::sendCurlPost($apiurl, $header, json_encode($rowData));
            pre($response);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function generateToken($Jwtheader, $partner_id, $api_key)
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $reqid = rand(100000, 999999);
        $timestamp = strtotime($ctime);
        $payload = '{
            "timestamp": "' . $timestamp . '",
            "partnerId": "' . $partner_id . '",
            "reqid": "' . $reqid . '"
        }';
        $apikey = $api_key;
        $library = new PaysprintApicredentials();
        $Jwt = $library->encode($Jwtheader, $payload, $apikey);
        return $Jwt;
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
        Log::info($response);
        return $response;
    }

    public function checkTransactionStatus()
    {
        info("iServeU DMT check status running at " . now());
        $date = date("Y-m-d H:i:s");
        $apiUrl = 'https://api-prod.txninfra.com/apiV1/dmt-api_prod/statuscheck/txnreport';
        $pendingReports = Report::where(['status_id' => 3, 'provider_id' => 316])
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
                        $responseData = $library->sendCurlPost($apiUrl, json_encode($rowData));
                        $responseData = json_decode($responseData, true);
                        if ($responseData['status'] == 200 && isset($responseData['results']) && count($responseData['results']) > 0) {
                            $response = $library->callBackAPi($responseData['results'], 316);
                            Log::info("iServeU DMT Success response",['data'=>$response]);
                        } else {
                            Log::info("iServeU DMT cron empty response");
                        }
                    } else {
                        Log::info("iServeU DMT cron empty reference id");

                    }
                } catch (\Exception $e) {
                    Log::error("iServeU DMT cron no error received", ["error" => $e->getMessage()]);
                }
            }
        } else {
            Log::info("iServeU DMT cron no records received from reports" . $date);
        }
    }
}
