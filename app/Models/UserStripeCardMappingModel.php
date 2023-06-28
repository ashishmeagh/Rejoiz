<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStripeCardMappingModel extends Model
{
    protected $table = "user_and_stripe_card_mapping_details";

    protected $fillable = [
    	                    'from_stripe_key_id',
                            'to_stripe_key_id',
    	                    'user_id',
    	                    'admin_id',
    	                    'card_id',
    	                    'stripe_card_id',
    	                    'stripe_customer_id',
                            'fingerprint'
    	                  ];
}
