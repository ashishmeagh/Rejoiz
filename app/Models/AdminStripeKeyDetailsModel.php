<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminStripeKeyDetailsModel extends Model
{
    protected $table = 'Admin_stripe_key_details';

    protected $fillable = ['id','secret_key','client_id','is_active'];
}
