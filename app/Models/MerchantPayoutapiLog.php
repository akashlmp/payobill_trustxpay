<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantPayoutapiLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'merchant_payoutapi_logs';

    public function getData($id)
    {
        return static::where('merchant_id', $id)
            ->first();
    }

    public function findData($id)
    {
        return static::find($id);
    }

    public function storeData($input, $return_array = null)
    {
        // log into database
        $api_log = [
            'merchant_user_id' => $input['merchant_user_id'] ?? 0,
            'merchant_reference_id' => $input['merchant_reference_id'] ?? null,
            'reference_id' => $input['reference_id'] ?? null,
            'type' => $input['type'],
            'mode' => $input['mode'],
            'url' => \Request::url(),
            'header' => json_encode(\Request::header()),
            'body' => json_encode(\Request::all()),
            'ip' => \Request::ip(),
        ];

        if (!empty($return_array)) {
            if (is_array($return_array)) {
                $api_log['response'] = json_encode($return_array);
            } else {
                $api_log['response'] = $return_array;
            }
        }

        return self::create($api_log);
    }
}
