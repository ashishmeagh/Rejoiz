<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    protected $table    = 'customer';

    protected $fillable = ['user_id',
                           'state',
                           'city',
                           'address2',
                           'shipping_addr',
    					             'shipping_zip_postal_code',
                           'billing_address',
    					             'billing_zip_postal_code',
    					             'years_in_business',
    					             'annual_sales',
    					             'store_description',
                           'store_name',
                           'store_website',
                           'country',
                           'shipping_city',
                           'shipping_state',
                           'shipping_country',
                           'billing_city',
                           'billing_state',
                           'billing_country',
                           'shipping_suit_apt',
                           'billing_suit_apt',
                           'ship_contact_no',
                           'bill_contact_no'
    				              ];


   public function user_details()
   {
      return $this->belongsTo('App\Models\UserModel','user_id','id');
   }


   

}
