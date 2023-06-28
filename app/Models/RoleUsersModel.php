<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class RoleUsersModel extends Model
{
    //use SoftDeletes;
    protected $table    = 'role_users';

    protected $fillable = ['user_id','role_id'];

    public function role_name()
    {
    	return $this->belongsTo('App\Models\RoleModel','role_id','id');
    }
   
}
