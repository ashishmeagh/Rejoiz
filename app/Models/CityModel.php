<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityModel extends Model
{
    protected $table    = 'cities';

    protected $fillable = ['name',
    					   'state_id'
    					   ];
}
