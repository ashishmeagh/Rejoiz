<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Dimsav\Translatable\Translatable;

class FourthSubCategoryModel extends Model
{
    use Translatable;
    protected $table = 'fourth_sub_category';

    /* Translatable Config */
    public $translationModel      = 'App\Models\FourthSubCategoryTranslationModel';
   
    public $translationForeignKey = 'fourth_sub_category_id';
   
    public $translatedAttributes  = ['fourth_sub_category_name','fourth_sub_category_slug','locale'];
   
    protected $fillable = ['category_id','sub_category_id','is_active'];

    public function delete()
    {
        $this->translations()->delete();
        return parent::delete();
    }
}
