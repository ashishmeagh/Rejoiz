<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempBagModel extends Model
{
    protected $table = 'temp_bag';
    protected $fillable = [ 
    						'user_id',
    						'ip_address',
    						'user_session_id',
    						'product_id',
    						'product_data',
    						'qty',
    						'total_price',
                            'is_reorder'
						  ];
}
