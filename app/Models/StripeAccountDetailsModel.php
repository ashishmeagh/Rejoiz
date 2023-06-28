<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeAccountDetailsModel extends Model
{
    protected $table = 'admin_connected_stripe_account_details';

    protected $fillable = ['user_id','stripe_acc_id','stripe_customer_id','user_email_id','vendor_id','admin_stripe_key_id','vendor_stripe_key_id'];
}
