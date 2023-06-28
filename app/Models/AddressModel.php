<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressModel extends Model
{
    protected $table = 'addresses';

    protected $fillable = ['user_id',
    					   'bill_first_name',
    					   'bill_last_name',
    					   'bill_email',
    					   'bill_mobile_no',
    					   'bill_complete_address',
    					   'bill_city',
                 'bill_country',
    					   'bill_state',
    					   'bill_zip_code',
    					   'ship_first_name',
    					   'ship_last_name',
    					   'ship_email',
    					   'ship_mobile_no',
    					   'ship_complete_address',
    					   'ship_city',
                 'ship_country',
    					   'ship_state',
    					   'ship_zip_code',
    					   'is_as_below',
                 'order_no',
                 'created_at',
                 'updated_at',
                 'bill_street_address',
                 'bill_suit_apt',
                 'ship_street_address',
                 'ship_suit_apt'
						  ];
                          
   public function user_details(){
        return $this->belongsTo('App\Models\UserModel','user_id','id');
   }
   



         
                      
}
