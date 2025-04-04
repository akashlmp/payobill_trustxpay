<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MerchantUsers extends Authenticatable
{
    protected $table = 'merchant';

    use Notifiable;

    protected $hidden = [
        'password', 'remember_token',
    ];
}
