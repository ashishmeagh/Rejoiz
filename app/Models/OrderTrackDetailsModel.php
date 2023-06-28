<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTrackDetailsModel extends Model
{
    protected $table = 'order_tracking_details';


    protected $fillable = ['company_id','order_id','order_no','tracking_no'];
}
