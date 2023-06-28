<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteModel extends Model
{
    protected $table    = 'favorite';

    protected $fillable = ['retailer_id','maker_id','product_id','type']; 



    public function makerDetails()
    {
        return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
    }

    public function productDetails()
    {
       return $this->belongsTo('App\Models\ProductsModel','product_id','id');	
    }

    public function store_image_details()
    {
        return $this->belongsTo('App\Models\ShopImagesModel','maker_id','maker_id');
    }
}
