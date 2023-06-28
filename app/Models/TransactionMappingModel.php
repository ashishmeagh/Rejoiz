<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionMappingModel extends Model
{
    protected $table = 'transaction_mapping';
    protected $fillable = [
    						'user_id',
    						'order_id',
    						'order_no',
    						'transaction_id',
    						'transaction_status',
    						'payment_type',
    						'amount'
    					  ];
}
