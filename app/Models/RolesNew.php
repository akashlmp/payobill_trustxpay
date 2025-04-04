<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RoleTrait;

class RolesNew extends Model
{
    protected $table = 'roles_new';

    use RoleTrait;

}
