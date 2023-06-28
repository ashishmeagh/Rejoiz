<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSalesmanagerMappingModel extends Model
{
    protected $table    = 'vendor_salesmanager_mapping';


    protected $fillable = ['salesmanager_id','vendor_id'];


    public function get_user_details()
    {
    	return $this->belongsTo('App\Models\UserModel','vendor_id','id');
    }

    
    public function get_salesmanager_details()
    {
        return $this->belongsTo('App\Models\SalesManagerModel','salesmanager_id','user_id');
    }

    public function get_maker_details()
    {
        return $this->belongsTo('App\Models\MakerModel','vendor_id','user_id');
    }
    
}
