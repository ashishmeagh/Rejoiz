<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDetailsModel extends Model
{
    protected $table = 'products_details';

    protected $fillable = [
    					  'product_id',
						  'image',
						  'image_thumb',
						  'sku',
						  'color',
						  'weight',
						  'length',
						  'width',
						  'height',
						  'lifestyle_image',
						  'packaging_image',
						  'option_type',
						  'option',
						  'sku_product_description'
						];

	public function productDetails()
    {
        return $this->belongsTo('App\Models\ProductsModel','product_id','id');
    }

    public function inventory_details()
    {
    	return $this->belongsTo('App\Models\ProductInventoryModel','sku','sku_no');
    }					
}
