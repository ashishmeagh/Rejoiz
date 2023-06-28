<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailerQuotesModel extends Model
{
    protected $table    = 'retailer_transaction';

    protected $fillable = ['maker_id',
                           'order_no',
                           'retailer_id',
                           'transaction_id',
    					             'customer_token',
                           'qty',
    					             'ship_date',
    					             'description',
    					             'total_retail_price',
    					             'total_wholesale_price',
    					             'ship_status',
                           'shipping_addr',
                           'shipping_addr_zip_code',
                           'billing_addr',
                           'billing_addr_zip_code',
                           'order_cancel_status',
                           'order_cancel_rejected_status',
                           'refund_status',
                           'payment_term',
                           'is_split_order',
                           'split_order_id',
                           'payment_due_date',
                           'promotion_discount',
                           'promo_code',
                           'maker_commission_status',
                           'admin_commission_status',
                           'is_direct_payment',
                           'order_rejected_reason',
                           'admin_commission',
                           'is_payment_status',
                           'stripe_key_id',
                           'received_commission_stripe_key_id',
                           'transfer_commission_stripe_key_id',
                           'payment_proof',
                           'influencer_code',
                        ];

    function quotes_details()
    {
    	return $this->hasMany('App\Models\RetailerQuotesProductModel','retailer_quotes_id','id');
    }

    function user_details()
    {
        return $this->belongsTo('App\Models\UserModel','retailer_id','id');
    }

    function maker_details()
    {
        return $this->belongsTo('App\Models\UserModel','maker_id','id');
    }

    function maker_data()
    {
        return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
    }

    function transaction_details()
    {
        return $this->belongsTo('App\Models\TransactionsModel','transaction_id','transaction_id');
    }

    function transaction_mapping()
    {
        return $this->belongsTo('App\Models\TransactionMappingModel','id','order_id');
    }

    function transaction_mapping_details()
    {
       return $this->belongsTo('App\Models\TransactionMappingModel','order_no','order_no');
    }
    
    public function maker_brand_details()
    {
        return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
    }

    function stripe_transaction_detail()
    {
      return $this->belongsTo('App\Models\StripeTransactionModel','id','lead_id');
    }

    function stripe_transaction_data()
    {
      return $this->hasMany('App\Models\StripeTransactionModel','lead_id','id');
    }

    function address_details()
    {
        return $this->belongsTo('App\Models\AddressModel','order_no','order_no');
    }

   
}
