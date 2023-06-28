<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionsModel extends Model
{
    protected $table = 'promotions';


    protected $fillable = ['maker_id','title','from_date','to_date','is_active','description','promo_code'];


    // public function get_promotions_type_details()
    // {
    // 	return $this->belongsTo('App\Models\PromotionsTypeModel','promotion_type_id','id');
    // }

    public function get_promotions_offer_details()
    {
       return $this->hasMany('App\Models\PromotionsOffersModel','promotion_id','id');

    }

    public function get_maker_details()
    {
    	return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
    }

    public function get_user_details()
    {
        return $this->belongsTo('App\Models\UserModel','maker_id','id');
    }
     
    public function get_promo_code_details()
    {
        return $this->belongsTo('App\Models\PromoCodeModel','promo_code','id');
    } 


}
