<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerPromoCodeModel extends Model
{
    protected $table = 'influencer_promo_code';


    protected $fillable = [
    						'id',
    					    'promo_code_name',
    					    'vendor_id',
    					    'is_active',
    					    'is_assigned'
    					  ];

    public function assigned_promo_code_details()
    {
    	return $this->belongsTo('App\Models\PromoCodeInfluencerMappingModel','id','influencer_promo_code_id');
    }
}
