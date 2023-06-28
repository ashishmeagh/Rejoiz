<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    protected $table    = 'countries_all';

    protected $fillable = ['sortname',
    					   'name',
    					   'phonecode',
    					   'is_active'
    					   ];
}
