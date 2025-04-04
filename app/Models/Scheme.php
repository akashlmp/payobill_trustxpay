<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheme extends Model {
    protected $fillable = ['company_id', 'scheme_name', 'status_id', 'user_id'];

    public function status() {
        return $this->belongsTo('App\Models\Status');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
