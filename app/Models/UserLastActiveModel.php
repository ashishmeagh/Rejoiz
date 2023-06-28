<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLastActiveModel extends Model
{
    protected $table = 'user_last_active_time';
    
    protected $fillable = ['user_id','last_active_time'];
}
