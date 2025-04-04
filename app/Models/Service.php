<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];
    public function wallet(){
        return $this->belongsTo('App\Models\Wallet');
    }
}
