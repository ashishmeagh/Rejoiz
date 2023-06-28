<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Dimsav\Translatable\Translatable;



class CategoryModel extends Model
{
	use Translatable;
    protected $table = 'category';

    /* Translatable Config */
    public $translationModel      = 'App\Models\CategoryTranslationModel';
   
    public $translationForeignKey = 'category_id';
   
    public $translatedAttributes  = ['category_name','category_slug','locale'];
   
    protected $fillable = ['is_active','category_image','priority'];
   
   public function delete()
    {
        $this->translations()->delete();
        return parent::delete();
    }

    public function subcategory_details()
    {
        return $this->hasMany('App\Models\SubCategoryModel','category_id','id');
    }
    public function get_cat_name()
    {
        return $this->belongsTo('App\Models\CategoryTranslationModel','id','category_id');
    }
    public function get_sub_cat_name()
    {
        return $this->belongsTo('App\Models\SubCategoryTranslationModel','id','subcategory_id');
    }
}
