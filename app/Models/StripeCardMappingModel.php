<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeCardMappingModel extends Model
{
    protected $table = 'stripe_card_mapping';

    protected $fillable = ['vendor_id','card_id','admin_id','stripe_card_id','stripe_key_id'];
}
