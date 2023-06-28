<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeRetailerMappingModel extends Model
{
    protected $table    = 'promo_code_retailer_mapping';

    protected $fillable = ['retailer_id','promo_code_id'];
}
