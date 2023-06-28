<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSizesModel extends Model
{
    protected $table    = 'product_sizes';

    protected $fillable = ['id',
    			 'product_id',
                 'sku_no',
                 'size_id',
                 'size_inventory'
    		];
}
