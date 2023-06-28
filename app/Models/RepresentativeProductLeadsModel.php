<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class RepresentativeProductLeadsModel extends Model
{
    protected $table    = 'representative_product_leads';

    protected $fillable = ['representative_leads_id','product_id','sku','retail_price','wholesale_price',
							'qty','comission','description','maker_id','order_no','product_discount','shipping_charges','shipping_charges_discount','product_discount','shipping_charges','shipping_charges_discount','product_shipping_charge','unit_wholsale_price'];

	public $appends = ['sku_images'];							

   	function product_details()
    {
        return $this->belongsTo('App\Models\ProductsModel','product_id','id');
    }

     public function shop_settings()
    {
       return $this->belongsTo('App\Models\ShopSettings','maker_id', 'maker_id');
    }	

    public function maker_details()
    {
       return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
    }


    //get product min qty of the perticular sku

    public function get_product_min_qty()
    {
       return $this->belongsTo('App\Models\ProductDetailsModel','sku','sku');
    }

    
    public function  getSkuImagesAttribute()
    {
        $arr_sku_image   = [];
        $obj_sku_image   = app(\App\Models\ProductDetailsModel::class);
        $obj_sku_image   = $obj_sku_image->where('sku','=',$this->sku)->first(['sku','image']);
        if($obj_sku_image)
        {
            $arr_sku_image = $obj_sku_image->toArray();
        }

        return $arr_sku_image;        
    }
	

}
