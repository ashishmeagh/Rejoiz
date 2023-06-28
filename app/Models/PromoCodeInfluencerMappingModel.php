<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeInfluencerMappingModel extends Model
{
    protected $table = 'promo_code_influencer_mapping';


    protected $fillable = [
    						'id',
    					    'influencer_promo_code_id',
    					    'influencer_id',
    					    'assigned_date',
    					    'expiry_date'
    					  ];
}
