<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateZipCodeModel extends Model
{
    protected $table = "state_zip_code";
    protected $fillable = ['area_id','state_id','zip_code'];
}
