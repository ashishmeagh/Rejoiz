<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesManagerModel extends Model
{
     protected $table    = 'sales_manager';

    protected $fillable = ['user_id','area_id','category_id','description'];

    public function get_user_data()
    {
    	return $this->belongsTo('App\Models\UserModel','user_id','id');
    }  

    public function area_details()
    {
     	return $this->belongsTo('App\Models\RepAreaModel','area_id','id');
   	}

    public function areas_details()
    {
        return $this->hasMany('App\Models\RepAreaModel','id','area_id');
    }

    public function rep_details()
    {
        return $this->hasMany('App\Models\RepresentativeModel','sales_manager_id','user_id');
    }
}