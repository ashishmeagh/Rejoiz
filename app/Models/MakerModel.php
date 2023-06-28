<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MakerModel extends Model
{
    protected $table    = 'makers';

    protected $fillable = ['user_id',
    					   'tax_id',
                           'brand_name',
    					   'company_name',
                 'real_company_name',
    					   'website_url',
    					   'primary_category_id',
                 'primary_category_name',
    					   'no_of_stores',
    					   'insta_url',
    					   'description',
                 'is_direct_payment',
                 'listing_sequence_no',
                  'added_by',
                  'admin_commission',
                  'is_get_a_quote'
    					  ];

    public function shop_settings()
    {
       return $this->belongsTo('App\Models\ShopSettings','user_id', 'maker_id');
    }


    public function shop_store_images()
    {
      return $this->belongsTo('App\Models\ShopImagesModel','user_id','maker_id');
    }


    public function user_details()
    {
      return $this->belongsTo('App\Models\UserModel','user_id','id');   
    }

    public function stripe_account_details()
    {
      return $this->belongsTo('App\Models\StripeAccountDetailsModel','user_id','user_id');
    }
}

