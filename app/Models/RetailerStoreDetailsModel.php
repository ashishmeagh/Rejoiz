<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailerStoreDetailsModel extends Model
{
   protected $table    = 'retailer_store_details';

   protected $fillable = [
   	                       'retailer_id',
   	                       'store_logo'
                         ];
}
