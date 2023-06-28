<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProductUpdatedByVendorModel extends Model
{ 

    protected $table = 'product_updated_by_vendor';

    protected $fillable = ['id',    
                           'product_id',					  
              			   'vendor_id',
                           'update_columns',
                           'update_productDetails_columns',
                           'update_subcategories_columns',
                           'update_third_subcategories_columns',
                           'update_fourth_subcategories_columns',
                           'updated_at',
                           'created_at'
				         ];   

}
