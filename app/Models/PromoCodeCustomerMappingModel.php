<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeCustomerMappingModel extends Model
{
    protected $table    = 'promo_code_customer_mapping';

    protected $fillable = [
    						'customer_quotes_id',
    						'customer_id',
    						'promo_code_id'
    					  ];
}
