<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantPayouts extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'merchant_payouts';

    public function getData($id)
    {
        return static::where('merchant_id', $id)
            ->first();
    }

    public function findData($id)
    {
        return static::find($id);
    }

    public function storeData($input)
    {
        return self::create($input);
    }

    public function merchant()
    {
        return $this->belongsTo('App\Models\MerchantUsers');
    }
    public function getApiData($input)
    {
        $payout = static::select(
            'merchant_payouts.id',
            'merchant_payouts.reference_id',
            'merchant_payouts.merchant_reference_id',
            'merchant_payouts.bene_name as ben_name',
            'merchant_payouts.ifsc as ben_ifsc',
            'merchant_payouts.bene_phone_number as ben_phone_number',
            'merchant_payouts.bank_name as ben_bank_name',
            'merchant_payouts.amount',
            'merchant_payouts.created_at',
        )
            ->where('merchant_id', $input['merchant_id']);
        if ((isset($input['merchant_reference_id']) && !empty($input['merchant_reference_id']))) {
            $payout = $payout->where('merchant_reference_id', $input['merchant_reference_id']);
        }
        if ((isset($input['reference_id']) && !empty($input['reference_id']))) {
            $payout = $payout->where('reference_id', $input['reference_id']);
        }
        $payout = $payout->orderBy('id', 'desc')
            ->first();

        return $payout;
    }
}
