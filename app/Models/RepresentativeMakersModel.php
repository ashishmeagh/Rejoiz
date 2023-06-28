<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class RepresentativeMakersModel extends Model
{
    protected $table    = 'representative_makers';

    protected $fillable = ['representative_id','maker_id','commission','is_confirm','is_lock'];


    public function maker_details()
    {
     	return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
   	}

    public function representative_details()
    {
      return $this->belongsTo('App\Models\UserModel','representative_id','id');
    }

    public function maker_user()
    {
      return $this->belongsTo('App\Models\UserModel','maker_id','id');
    }

   	public function store_details()
    {
      return $this->belongsTo('App\Models\ShopImagesModel','maker_id','maker_id');
    }



}
