<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMultipleImagesModel extends Model
{
    protected $table = 'product_multiple_images';

    protected $fillable = [
    					  'product_id',
						  'product_detail_id',
						  'product_image',
						  'sku'
						 
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
