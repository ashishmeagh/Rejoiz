<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailerRepresentativeMappingModel extends Model
{
    protected $table    = 'retailer_representative_mapping';

    protected $fillable = ['retailer_id','representative_id','sales_manager_id'];



    public function getRetailerDetails()
    {
       return $this->belongsTo('App\Models\UserModel','retailer_id','id');
    }

    public function retailer_details()
    {
    	return $this->belongsTo('App\Models\RetailerModel','retailer_id','user_id');
    }
}
