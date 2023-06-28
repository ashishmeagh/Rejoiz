<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerImageModel extends Model
{
   protected $table    = 'banner_images';

    protected $fillable = ['id',
    					   'banner_image',
    					   // 'banner_image_small',
    					   'type'
    					  ];
}
