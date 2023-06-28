<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationModel extends Model
{
    protected $table = 'activations';

    protected $fillable = ['user_id','code','completed'];
}
