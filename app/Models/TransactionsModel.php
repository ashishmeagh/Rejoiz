<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsModel extends Model
{
    protected $table = 'transactions';
    protected $fillable = ['paid_by',
    						'transaction_id',
                            'transaction_status',
    						'received_by',
    						'amount',
    						'paid_on',
    						'payment_response',
                            'customer_token',
    						'card_id',
                            'payment_type',
                            'retailer_type',
                            'stripe_key_id'
						  ];

    function get_retailer_orders()
    {
        return $this->hasMany('App\Models\RetailerQuotesModel','transaction_id','transaction_id');
    }

    function get_customer_orders()
    {
        return $this->hasMany('App\Models\CustomerQuotesModel','transaction_id','transaction_id');
    }

    function get_rep_sales_orders()
    {
        return $this->hasMany('App\Models\RepresentativeLeadsModel','transaction_id','transaction_id');
    }

    function strip_key_details()
    {
        return $this->belongsTo('App\Models\UserStripeAccountDetailsModel','stripe_key_id','id');
    }
}
