<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepAreaModel extends Model
{
    
    protected $table = 'rep_area';

    protected $fillable = ['area_name','status','state_id','category_id','area_type','created_at','updated_at','deleted_at'];

    public function category_details()
    {
    	return $this->belongsTo('App\Models\CategoryTranslationModel','category_id','id');
    }


    public function cat_division_details()
    {
       return $this->belongsTo('App\Models\CategoryDivisionModel','category_id','id');
    }

    
    
}
