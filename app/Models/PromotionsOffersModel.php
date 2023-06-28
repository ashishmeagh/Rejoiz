<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionsOffersModel extends Model
{
    protected $table    = 'promotions_offer';

    protected $fillable = ['promotion_type_id','promotion_id','minimum_ammount','discount'];


    public function get_prmotion_type()
    {
    	return $this->belongsTo('App\Models\PromotionsTypeModel','promotion_type_id','id');
    }
    
}
