<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtractedProductsImages extends Model
{   
    protected $table    = 'extracted_products_images';

    protected $fillable = ['sku_no','product_image','product_image_path','maker_id','is_process','product_source_image','product_dest_image_path'];



    
}
