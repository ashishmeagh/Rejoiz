<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPdfModel extends Model
{
    protected $table    = 'catalog_pdf';

    protected $fillable = ['vendor_id','pdf_file','is_active','catalog_name','cover_image'];
}
