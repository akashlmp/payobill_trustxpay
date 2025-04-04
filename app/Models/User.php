<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{

    use Notifiable,HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'mobile', 'profile_id', 'balance_id', 'password_changed_at','transaction_password','cms_agent_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function member()
    {
        return $this->hasOne('App\Models\Member');
    }

    public function balance()
    {
        return $this->belongsTo('App\Models\Balance');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status');
    }

    public function profile()
    {
        return $this->belongsTo('App\Models\Profile');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    // public function getFullNameAttribute()
    // {
    //     return ucfirst($this->fullname);
    // }

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
}
