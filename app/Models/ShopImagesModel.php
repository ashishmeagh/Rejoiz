<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopImagesModel extends Model
{
    protected $table    = 'store_images';

    protected $fillable = ['id',
    					   'maker_id',
    					   'store_cover_image',
    					   'store_profile_image'
    					  ];
}
