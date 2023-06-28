<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSettings extends Model
{
   protected $table    = 'shop_settings';

    protected $fillable = ['id',
    					   'maker_id',
    					   'first_order_minimum',
    					   're_order_minimum',
    					   'schedule_orders',
    					   'handling_and_packaging',
    					   'sell_to_online_retaile',
    					   'shop_lead_time',
    					   'vacation_mode_start',
    					   'vacation_mode_end',
                           'shop_story',
                           'split_order_free_shipping'
						  ];
}
