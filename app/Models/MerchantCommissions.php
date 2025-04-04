<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantCommissions extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'merchant_commissions';

    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
}
