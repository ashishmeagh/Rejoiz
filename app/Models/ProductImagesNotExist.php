<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImagesNotExist extends Model
{
    protected $table = 'product_images_not_exists';

    protected $fillable = ['sku_no'];
}
