<?php

namespace App\library {

    use App\Models\Numberdata;
    use App\Models\Circleprovider;
    use App\Models\Backupapi;
    use App\Models\Provider;
    use App\Models\User;
    use App\Models\Report;
    use App\Models\Providerlimit;
    use App\Models\Service;
    use DB;
    use Auth;
    use App\Library\GetcommissionLibrary;
    use App\Notifications\DatabseNotification;
    use Notification;
    use App\Models\Denomination;
    use App\Models\State;
    use App\Models\District;
    use App\Models\Agentonboarding;
    use App\Models\Api;
    use Helpers;


    class DmtLibrary
    {



        function splitAmount($amount, $provider_id)
        {
            $providers = Provider::find($provider_id);
            $splitAmountBy = ($providers->splitAmountBy == 0) ? 5000 : $providers->splitAmountBy;
            $partsAmount = [];
            while ($amount > $splitAmountBy) {
                $partsAmount[] = $splitAmountBy;
                $amount -= $splitAmountBy;
            }
            if ($amount > 0) {
                $partsAmount[] = $amount;
            }
            return $partsAmount;
        }


        function getTransactionCharges($user_id, $amount, $provider_id,$pcType=0)
        {
            $id = sprintf("%06d", mt_rand(1, 999999));
            $partsAmount = Self::splitAmount($amount, $provider_id);
            foreach ($partsAmount as $amounts) {
                Self::getMySlab($amounts, $id, $user_id, $provider_id,$pcType);
            }
            $list = $this->getslablist($id);
            DB::table('view_charges')->where('myid', $id)->delete();
            return Response()->json([
                'status' => 'success',
                'list' => $list,
            ]);
        }



        function getMySlab($amount, $myid, $user_id, $provider_id,$pcType)
        {
            $userdetails = User::find($user_id);
            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount,$pcType);
            $retailer = $commission['retailer'];
            $final_amount = $amount + $retailer;
            $data = array(
                'amount' => $amount,
                'charges' => $retailer,
                'total_amount' => $final_amount,
                'myid' => $myid
            );
            DB::table('view_charges')->insert($data);
            return true;
        }

        function getslablist($myid)
        {
            $report = DB::table('view_charges')->where('myid', $myid)->orderBy('id', 'ASC')->get();
            $response = array();
            foreach ($report as $value) {
                $product = array();
                $product["amount"] = $value->amount;
                $product["charges"] = $value->charges;
                $product["total_amount"] = $value->total_amount;
                array_push($response, $product);
            }
            return $response;
        }

        function calculateChargesAndCommission($amount, $retailer)
        {
            // Calculate customer charge with a minimum value of 12
            $customer_charge = max(($amount * 1.2) / 100, 12);
            // Calculate the customer charge factor (ccf)
            $ccf = $customer_charge / 1.18;
            // Use the ccf as the commission
            $commission = $ccf - $retailer;
            // Calculate GST and TDS
            $gst = $customer_charge - $ccf;
            $tds = ($commission * 5) / 100;
            // Return the result as an associative array
            return [
                'charges' => $retailer,
                'gst' => $gst,
                'tds' => $tds,
                'commission' => $commission,
                'netCommission' => $commission - $tds,
                'customer_charge' => -$customer_charge,
            ];
        }
    }

}
