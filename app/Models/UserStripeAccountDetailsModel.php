<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStripeAccountDetailsModel extends Model
{
    protected $table = 'stripe_account_details';

    protected $fillable = ['user_id','test_key','secret_key','client_id','is_admin_authorize','is_vendor_authorize','client_id','is_active','account_holder'];
}
