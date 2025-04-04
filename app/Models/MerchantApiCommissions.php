<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantApiCommissions extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'merchant_api_commissions';

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
}
