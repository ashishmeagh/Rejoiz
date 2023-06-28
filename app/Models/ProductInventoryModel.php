<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInventoryModel extends Model
{
    protected $table = 'product_inventory';

    protected $fillable = [
    					  'product_id',
						  'sku_no',
						  'quantity',
						  'user_id'
						];
}
