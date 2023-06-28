<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GetQuoteModel extends Model
{
    protected $table = 'get_quote_details';

    protected $fillable = ['product_id',
     'vendor_id', 
     'quantity', 
     'name', 
     'email', 
     'contact_number', 
     'additional_note',
     'no_of_days_to_expected_delivery',
     'expected_delivery_date',
     'influencer_code'
						  ];
    
    public function vendor_details()
    {
        return $this->belongsTo('App\Models\UserModel','vendor_id','id');
    }                      
    
}
