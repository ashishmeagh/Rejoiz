<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepresentativeModel extends Model
{
    protected $table    = 'representative';

    protected $fillable = ['user_id','description','sales_manager_id','area_id','category_id'];

    public function get_user_details()
    {
    	return $this->belongsTo('App\Models\UserModel','user_id','id');
    }

    public function get_area_details()
    {
       return $this->belongsTo('App\Models\RepAreaModel','area_id','id');
    }

    public function get_rep_vendor()
    {
       return $this->hasMany('App\Models\VendorRepresentativeMappingModel','representative_id','user_id');   
    }

    public function sales_manager_details()
    {
        return $this->belongsTo('App\Models\SalesManagerModel','sales_manager_id','user_id');
    }

}
