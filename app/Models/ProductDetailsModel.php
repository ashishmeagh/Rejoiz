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
						  'shipping_weight',
						  'shipping_length',
						  'shipping_width',
						  'shipping_height',
						  'lifestyle_image',
						  'packaging_image',
						  'option_type',
						  'option',
						  'sku_product_description',
						  'product_min_qty'
						];

	public function productDetails()
    {
        return $this->belongsTo('App\Models\ProductsModel','product_id','id');
    }   

    public function productMultipleImages()
    {
        return $this->hasMany('App\Models\ProductMultipleImagesModel','product_detail_id','id');
    }

    public function inventory_details()
    {
    	return $this->belongsTo('App\Models\ProductInventoryModel','sku','sku_no');
    }					
}
