<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeModel extends Model
{
    protected $table = 'promo_code';


    protected $fillable = ['promo_code_name','vendor_id','status','is_active'];

    public function get_promotions_offer_details()
    {
       return $this->hasMany('App\Models\PromotionsOffersModel','promotion_id','id');

    }
}
