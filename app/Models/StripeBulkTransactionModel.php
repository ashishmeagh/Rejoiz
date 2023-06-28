<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeBulkTransactionModel extends Model
{
    protected $table = 'stripe_bulk_transactions';

    protected $fillable = ['bulk_transaction_key','paid_by','received_by','bulk_transfer_id','bulk_transaction_id','bulk_destination_payment','total_amount','payment_status','payment_date','updated_date'];
}


