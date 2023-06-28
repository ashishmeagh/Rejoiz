<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogImageModel extends Model
{   
    protected $table    = 'catlogs_images';

    protected $fillable = ['catalog_id','image','is_active','sku','product_id','catalog_page_id'];


    public function catalog_details()
    {
    	return $this->belongsTo('App\Models\CatlogsModel','catalog_id','id');
    }

    public function productDeta()
    {
      return $this->belongsTo('App\Models\ProductDetailsModel','sku','sku');	
    }

    
}
