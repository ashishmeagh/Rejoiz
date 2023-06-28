<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailerQuotesProductModel extends Model
{
    protected $table    = 'retailer_transaction_detail';

    protected $fillable = ['retailer_quotes_id',
                           'product_id',
    					             'transaction_id',
                           'sku_no',
    					             'qty',
                           'retail_price',
    					             'unit_wholsale_price',
                           'wholesale_price',
    					             'product_discount',
                           'shipping_charge',
                           'color',
                           'size_id',
                           'shipping_discount',
    					             'description'
                         ];

   	function product_details()
    {
        return $this->belongsTo('App\Models\ProductsModel','product_id','id');
    }

    
}
