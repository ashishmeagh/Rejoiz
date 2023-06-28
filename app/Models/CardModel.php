<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardModel extends Model
{
    protected $table = "card_detail";
    protected $fillable = ['user_id','card_data','stripe_customer_id','stripe_card_id','stripe_key_id','fingerprint'];
}
