<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Sleimanx2\Plastic\Searchable;
use App\Models\Search\Searchable;

class ProductsModel extends Model
{
    use Searchable;

    protected $table = 'products';

    protected $fillable = ['user_id',    
                           'category_id',					  
              					   'product_name',
                           'ingrediants',
              					   'description',
              					   'unit_wholsale_price',
              					   'retail_price',
              					   'available_qty',
              					   'is_active',
                           'is_archive',
                           'brand',
                           'is_draft',
                           'is_best_seller',                           
                           'is_tester_available',
    					             'case_size',
                           'product_image',
                           'product_image_thumb',
                           'product_complete_status',
                           'shipping_charges',
                           'shipping_type',
                           'minimum_amount_off',
                           'off_type_amount',
                           'prodduct_dis_type',
                           'product_dis_min_amt',
                           'product_discount',
                           'product_status',
                           'case_quantity',
                           'restock_days'

						  ];

    public function productDetails()
    {
        return $this->hasMany('App\Models\ProductDetailsModel','product_id','id');
    }

     public function inventoryDetails()
    {
        return $this->hasMany('App\Models\ProductInventoryModel','product_id','id');
    }


    public function productImages()
    {
        return $this->belongsTo('App\Models\ProductImagesModel','id','product_id');
    }

    public function productMultipleImages()
    {
        return $this->hasMany('App\Models\ProductMultipleImagesModel','product_id','id');
    }

    public function productSubCategories()
    {
        return $this->hasMany('App\Models\ProductsSubCategoriesModel','product_id','id');
    }   

    public function categoryDetails()
    {
        return $this->belongsTo('App\Models\CategoryModel','category_id','id');
    } 
    public function userDetails()
    {
        return $this->belongsTo('App\Models\UserModel','user_id','id');
    }

    public function brand_details()
    {
       return $this->belongsTo('App\Models\BrandsModel','brand','id');
    }

    public function maker_details()
    {
       return $this->belongsTo('App\Models\MakerModel','user_id','user_id');
    }

    public function shop_settings()
    {
       return $this->belongsTo('App\Models\ShopSettings','user_id', 'maker_id');
    }

    public function vendor_sales_manager_mapping()
    {
      return $this->belongsTo('App\Models\VendorSalesmanagerMappingModel','user_id', 'vendor_id');
    }

}
