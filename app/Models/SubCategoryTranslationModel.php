<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategoryTranslationModel extends Model
{
    protected $table = 'subcategory_translation';
    protected $fillable = ['subcategory_id',
						   'subcategory_name',
						   'subcategory_slug',
						   'locale'
						  ]; 
}
