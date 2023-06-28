<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdSubCategoryTranslationModel extends Model
{
    protected $table = 'third_sub_category_translation';
    
    protected $fillable = ['third_sub_category_id',
						   'third_sub_category_name',
						   'third_sub_category_slug',
						   'locale'
						  ]; 
}
//This is third level category translation model
