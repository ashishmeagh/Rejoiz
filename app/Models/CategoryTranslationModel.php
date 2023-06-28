<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslationModel extends Model
{
    protected $table = 'category_translation';
    protected $fillable = ['category_id',
						   'category_name',
						   'category_slug',
						]; 
}
