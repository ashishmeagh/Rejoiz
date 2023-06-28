<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateModel extends Model
{
    protected $table    = 'states';

    protected $fillable = ['name',
    					   'country_id'
    					   ];
}
