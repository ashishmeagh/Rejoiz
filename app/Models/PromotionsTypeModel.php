<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionsTypeModel extends Model
{
    protected $table    = 'promotions_type';

    protected $fillable = ['promotion_type_name','is_active'];
}
