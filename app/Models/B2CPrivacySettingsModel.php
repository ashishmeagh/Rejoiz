<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2CPrivacySettingsModel extends Model
{
    protected $table 	= 'b2c_privacy_settings';

   	protected $fillable = [	
   							'id',
   							'is_b2c_module_on',
   							'is_influencer_module_on'
   						  ];
}
