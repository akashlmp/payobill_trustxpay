<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceEnquiry extends Model
{
    use HasFactory;

    protected $fillable = ['number','provider_id','amount','api_id','status_id','txnid','user_id','profit','client_id','total_balance'];

    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
