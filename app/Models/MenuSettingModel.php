<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuSettingModel extends Model
{
    protected $table ='menu_settings';
    
	protected $fillable = [ 'id',
	                        'menu_name',
                    		'menu_status',
                            'deleted_at',
                            'created_at',
                            'updated_at'
                         ];
}
