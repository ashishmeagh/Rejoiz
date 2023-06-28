<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryDivisionModel extends Model
{
    protected $table = "category_division";


    protected $fillable = ['cat_division_name','is_active'];
}
