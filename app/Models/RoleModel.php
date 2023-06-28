<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    protected $table    = 'roles';

    protected $fillable = ['name','slug'];
}
