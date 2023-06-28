<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;

class StaticPageTranslationModel extends Model
{
	use Rememberable;
    protected $table='static_pages_translation';
   
    public $timestamps = false;
    protected $fillable = ['static_page_id',
    					   'locale',
    					   'page_title',
    					   'page_slug',
    					   'page_desc',
    					   'meta_keyword',
    					   'meta_url',
    					   'meta_desc',
    					   'image_url'
    					];

}
