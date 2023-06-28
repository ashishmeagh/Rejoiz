<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsSubCategoriesModel extends Model
{
    protected $table = 'product_sub_categories';
    
    protected $fillable = ['product_id',
    					   'category_id',
    					   'sub_category_id',
                           'third_sub_category_id',
                           'fourth_sub_category_id'
						  ];

	public function SubcategoryDetails()
    {
        return $this->belongsTo('App\Models\SubCategoryModel','sub_category_id','id');
    }

    public function categoryDetails()
    {
        return $this->belongsTo('App\Models\CategoryModel','category_id','id');
    }
    
    public function ThirdSubcategoryDetails()
    {
        return $this->belongsTo('App\Models\ThirdSubCategoryModel','third_sub_category_id','id');
    }

    public function FourthSubcategoryDetails()
    {
        return $this->belongsTo('App\Models\FourthSubCategoryModel','fourth_sub_category_id','id');
    }

    public function productDetails()
    {
        return $this->belongsTo('App\Models\ProductsModel','product_id','id');
    }				  

}
