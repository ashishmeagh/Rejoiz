<?php

namespace App\Models;

use \Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Watson\Rememberable\Rememberable;

class StaticPageModel extends Eloquent
{
	use Rememberable;
	use Translatable;
    protected $table = 'static_pages';

    /* Translatable Config */
    public $translationModel 	  = 'App\Models\StaticPageTranslationModel';
    
    public $translationForeignKey = 'static_page_id';

    public $translatedAttributes  = ['locale',
    								'page_title',
    								'page_slug',
    								'page_desc',
    								'meta_keyword',
    								'meta_url',
    								'meta_desc',
    								'image_url'
                                ];

    							
    protected $fillable 		  = ['is_active'];

     public function translation_details()
    {
        return $this->hasMany('App\Models\StaticPageTranslationModel','static_page_id','id');
    }

}


