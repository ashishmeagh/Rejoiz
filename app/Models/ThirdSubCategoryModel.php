<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Dimsav\Translatable\Translatable;

class ThirdSubCategoryModel extends Model
{
    use Translatable;
    protected $table = 'third_sub_category';

    /* Translatable Config */
    public $translationModel      = 'App\Models\ThirdSubCategoryTranslationModel';
   
    public $translationForeignKey = 'third_sub_category_id';
   
    public $translatedAttributes  = ['third_sub_category_name','third_sub_category_slug','locale'];
   
    protected $fillable = ['category_id','sub_category_id','is_active'];

    public function delete()
    {
        $this->translations()->delete();
        return parent::delete();
    }

    public function third_subcategory_details()
    {
        return $this->hasMany('App\Models\FourthSubCategoryModel','third_sub_category_id','id');
    }
}
