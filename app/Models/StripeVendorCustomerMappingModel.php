<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeVendorCustomerMappingModel extends Model
{
    protected $table = 'stripe_vendor_customer_mapping';

    protected $fillable = ['vendor_id','user_id','stripe_customer_id','stripe_key_id'];
}
