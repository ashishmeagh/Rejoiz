<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Dimsav\Translatable\Translatable;

class SubCategoryModel extends Model
{
    use Translatable;
    protected $table = 'subcategory';

    /* Translatable Config */
    public $translationModel      = 'App\Models\SubCategoryTranslationModel';
   
    public $translationForeignKey = 'subcategory_id';
   
    public $translatedAttributes  = ['subcategory_name','subcategory_slug','locale'];
   
    protected $fillable = ['category_id','is_active'];

    public function delete()
    {
        $this->translations()->delete();
        return parent::delete();
    }

    public function second_subcategory_details()
    {
        return $this->hasMany('App\Models\ThirdSubCategoryModel','sub_category_id','id');
    }
}
