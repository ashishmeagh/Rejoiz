<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorRepresentativeMappingModel extends Model
{
    protected $table    = 'vendor_representative_mapping';


    protected $fillable = ['representative_id','vendor_id'];


    public function get_user_details()
    {
    	return $this->belongsTo('App\Models\UserModel','vendor_id','id');
    }

    
    public function get_representative_details()
    {
        return $this->belongsTo('App\Models\RepresentativeModel','representative_id','user_id');
    }


    public function get_promotions()
    {
    	return $this->hasMany('App\Models\PromotionsModel','maker_id','vendor_id');
    }

    public function get_maker_details()
    {
        return $this->belongsTo('App\Models\MakerModel','vendor_id','user_id');
    }
    
}
