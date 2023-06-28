<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeTransactionModel extends Model
{
    protected $table = 'stripe_transactions';

    protected $fillable = ['order_id','paid_by','received_by','transaction_id','amount','bulk_pay_id','status','lead_id','transfer_id','quote_id','destination_payment','customer_order_id'];
}
