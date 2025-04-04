<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantTestTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'merchant_test_transactions';

    public function getData($id)
    {
        return static::where('merchant_id', $id)
            ->first();
    }

    public function findData($id)
    {
        return static::find($id);
    }

    public function status(){
        return $this->belongsTo('App\Models\Status');
    }

    public function storeData($input)
    {
        return self::create($input);
    }

    public function getApiData($input)
    {
        $transaction = static::select(
                DB::raw('0 as mode'), // test
                DB::raw('2 as type'), // status
                'merchant_test_transactions.id',
                'merchant_test_transactions.reference_id',
                'merchant_test_transactions.merchant_reference_id',
                'merchant_test_transactions.status_id',
                'merchant_test_transactions.ben_name',
                'merchant_test_transactions.ben_ifsc',
                'merchant_test_transactions.ben_phone_number',
                'merchant_test_transactions.ben_bank_name',
                'merchant_test_transactions.amount',
                'merchant_test_transactions.created_at',
                'merchant_test_transactions.failure_reason as message',
                'merchant_test_transactions.account_number as ben_account_number',
                'merchant_test_transactions.mode as transfer_type'
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
