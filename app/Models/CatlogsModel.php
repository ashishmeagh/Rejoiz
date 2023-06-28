<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatlogsModel extends Model
{
    protected $table    ='catlogs';

    protected $fillable = ['maker_id','catalog_name','is_active'];


    public function maker_details()
    {
      return $this->belongsTo('App\Models\UserModel','maker_id','id');
    }


    public function catlog_details()
    {
    	return $this->hasMany('App\Models\CatalogImageModel','catalog_id','id');
    }


    public function catalogPageDetails()
    {
        return $this->hasMany('App\Models\CatalogPagesModel','catalog_id','id');
    }

    

   

    
}
