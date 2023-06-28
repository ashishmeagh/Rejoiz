<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSettingModel extends Model
{
    protected $table    = 'general_settings';

    protected $fillable = ['data_id','data_value','type'];
}
