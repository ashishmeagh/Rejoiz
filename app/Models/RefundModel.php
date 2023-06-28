<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundModel extends Model
{
    protected $table    = 'refund';

    protected $fillable = ['order_id',
    					   'order_no',
    					   'transaction_id',
    					   'amount',
    					   'balance_transaction',
    					   'status',
    					   'paid_by',
    					   'received_by'
    					   ];
}
