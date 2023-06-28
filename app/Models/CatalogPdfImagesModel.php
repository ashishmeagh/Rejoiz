<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPdfImagesModel extends Model
{
    protected $table    = 'catalog_pdf_image';

    protected $fillable = ['catalog_id','page_sequence','image','is_active'];



    public function getCatalogData()
    {
    	return $this->belongsTo('App\Models\CatlogsModel','catalog_id','id');
    }
}
