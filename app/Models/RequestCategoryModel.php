<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestCategoryModel extends Model
{
   protected $table = 'brands';

   protected $fillable = ['user_id','brand_name','is_active','brand_image'];
}
