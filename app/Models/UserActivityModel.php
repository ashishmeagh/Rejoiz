<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityModel extends Model
{
    protected $table = 'user_activity_log';

    protected $fillable = ['user_id',
                           'order_id',
                           'shipping_address',
                           'billing_address',
                           'payment_mode',
                           'payment_status',
                           'grand_total',
                           'order_place_date'
                          ];
}
