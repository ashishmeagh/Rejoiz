<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPagesModel extends Model
{
    protected $table    = 'catalog_pages';

    protected $fillable = ['catalog_id','sequence','page_type','is_active'];


    public function getCatalogImageData()
    {
    	return $this->hasMany('App\Models\CatalogImageModel','catalog_page_id','id');
    } 

    public function getCatalogData()
    {
    	return $this->belongsTo('App\Models\CatlogsModel','catalog_id','id');
    }
}
