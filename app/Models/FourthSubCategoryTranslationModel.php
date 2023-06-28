<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FourthSubCategoryTranslationModel extends Model
{
    protected $table    = 'fourth_sub_category_translation';
    
    protected $fillable = ['fourth_sub_category_id',
						   'fourth_sub_category_name',
						   'fourth_sub_category_slug',
						   'locale'
						  ]; 
}
//This is third level category translation model