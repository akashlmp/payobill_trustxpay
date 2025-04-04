<?php

namespace App\Library;

use App\ModelsUser;
use App\Models\MerchantCommissions;
use App\Models\MerchantApiCommissions;
use App\Models\Apiprovider;
use App\Models\Gatewaycharge;

class MerchantcommissionLibrary
{
    function get_commission($merchant_id, $provider_id, $amount, $type = 3,$transfer_type=0)
    {
        $commission = MerchantCommissions::where('provider_id', $provider_id)
            ->where('merchant_id', $merchant_id)
            ->where('trans_type', $transfer_type)
            ->where('provider_commission_type', $type)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->first();

        if ($commission) {
            if ($commission->type == 1) {
                $retailer = $commission->r;
                $distributor = $commission->d;
                $sdistributor = $commission->sd;
                $sales_team = $commission->st;
                $referral = $commission->referral;
            } else {
                $retailer = ($amount * $commission->r) / 100;
                $distributor = ($amount * $commission->d) / 100;
                $sdistributor = ($amount * $commission->sd) / 100;
                $sales_team = ($amount * $commission->st) / 100;
                $referral = ($amount * $commission->referral) / 100;
            }
        } else {
            $retailer = 0;
            $distributor = 0;
            $sdistributor = 0;
            $sales_team = 0;
            $referral = 0;
        }
        return ['retailer' => $retailer, 'distributor' => $distributor, 'sdistributor' => $sdistributor, 'sales_team' => $sales_team, 'referral' => $referral];
    }

    function recharge_api_commission($api_id, $amount, $provider_id)
    {
        $apiprovider = Apiprovider::where('provider_id', $provider_id)->where('api_id', $api_id)->first();
        if ($apiprovider) {
            if ($apiprovider->type == 1) {
                $api_commission = $apiprovider->api_commission;
            } else {
                $api_commission = ($amount * $apiprovider->api_commission) / 100;
            }
        } else {
            $api_commission = 0;
        }
        return ['api_commission' => $api_commission];
    }

    function getGatewayCharges($methodCode, $amount)
    {
        $gatewaycharges = Gatewaycharge::where('method_code', $methodCode)->first();
        if ($gatewaycharges) {
            if ($gatewaycharges->type == 1) {
                $retailer = $gatewaycharges->commission;
            } else {
                $retailer = ($amount * $gatewaycharges->commission) / 100;
            }
        } else {
            $retailer = 0;
        }
        return ['retailer' => $retailer];
    }

    function getApiCommission($api_id, $provider_id, $amount,$merchant_id=0,$transfer_type)
    {
        $apicommissions = MerchantApiCommissions::where('provider_id', $provider_id)
            // ->where('merchant_id', $merchant_id)
            ->where('trans_type', $transfer_type)
            ->where('provider_commission_type', $api_id)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->first();

        if ($apicommissions) {
            if ($apicommissions->type == 1) {
                $apiCommission = $apicommissions->commission;
                $commissionType = $apicommissions->commission_type;
            } else {
                $apiCommission = ($amount * $apicommissions->commission) / 100;
                $commissionType = $apicommissions->commission_type;
            }
        } else {
            $apiCommission = 0;
            $commissionType = "commission";
        }
        return ['apiCommission' => $apiCommission, 'commissionType' => $commissionType];
    }
}
