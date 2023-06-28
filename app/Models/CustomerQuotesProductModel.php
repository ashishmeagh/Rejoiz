<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerQuotesProductModel extends Model
{
    protected $table    = 'customer_transaction_detail';

    protected $fillable = ['customer_quotes_id',
                           'product_id',
    					             'transaction_id',
                           'sku_no',
    					             'qty',
                           'retail_price',
    					             'unit_retail_price',
                           'wholesale_price',
    					             'product_discount',
                           'shipping_charge',
                           'shipping_discount',
    					             'description'
                         ];

   	function product_details()
    {
        return $this->belongsTo('App\Models\ProductsModel','product_id','id');
    }

    
}
