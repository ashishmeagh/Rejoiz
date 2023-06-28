<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsDetailsModel extends Model
{
    protected $table = 'transaction_details';
    protected $fillable = ['transaction_id','product_id','sku_no','maker_id','retail_price','wholesale_price','item_qty'];
}
