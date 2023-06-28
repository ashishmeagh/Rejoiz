<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerSettingModel extends Model
{
    protected $table    ='influencer_settings';
    
	protected $fillable =  [ 
								'id',
	                        	'sales_target',
                    			'reward_amount',
                    			'discount_on_promo_code'
                           ];
}
