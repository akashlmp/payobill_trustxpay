<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantTransactions extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'merchant_transactions';

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

    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }

    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
    public function status(){
        return $this->belongsTo('App\Models\Status');
    }

    public function merchant(){
        return $this->belongsTo('App\Models\MerchantUsers');
    }

    public function getApiData($input)
    {
        $transaction = static::select(
                DB::raw('1 as mode'), // test
                DB::raw('2 as type'), // status
                'merchant_transactions.id',
                'merchant_transactions.reference_id',
                'merchant_transactions.merchant_reference_id',
                'merchant_transactions.status_id',
                'merchant_transactions.amount',
                'merchant_transactions.created_at',
                'merchant_transactions.failure_reason as message',
                'merchant_transactions.account_number as ben_account_number',
                'merchant_transactions.mode as transfer_type'
            )
            ->where('merchant_id', $input['merchant_id']);
            if ((isset($input['merchant_reference_id']) && !empty($input['merchant_reference_id']))) {
                $transaction = $transaction->where('merchant_reference_id', $input['merchant_reference_id']);
            }
            if ((isset($input['reference_id']) && !empty($input['reference_id']))) {
                $transaction = $transaction->where('reference_id', $input['reference_id']);
            }
            $transaction = $transaction->orderBy('id', 'desc')
                ->first();

        return $transaction;
    }
}
