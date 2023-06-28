<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class RepresentativeLeadsModel extends Model
{
    protected $table    = 'representative_leads';


    protected $fillable = ['representative_id',
                           'maker_id',
                           'retailer_id',
                           'comission',
                           'total_commission_wholesale',
                           'tot_commi_less_wholesale',
                           'is_confirm',
                           'total_retail_price',
                           'total_wholesale_price',
                           'description',
                           'order_no',
                           'transaction_id',
                           'ship_status',
                           'payment_term',
                           'payment_due_date',
                           'sales_manager_id',
                           'total_product_discount',
                           'total_shipping_charges',
                           'total_shipping_discount',
                           'maker_confirmation',
                           'is_split_order',
                           'split_order_id',
                           'rep_commission_status',
                           'maker_commission_status',
                           'admin_commission_status',
                           'sales_manager_commission_status',
                           'promotion_discount',
                           'promo_code',
                           'is_reorder',
                           'order_cancel_status',
                           'refund_status',
                           'is_direct_payment',
                           'admin_commission',
                           'order_rejected_reason',
                           'rep_sales_commission',
                           'stripe_key_id',
                           'received_commission_stripe_key_id',
                           'transfer_commission_stripe_key_id'
                        ];




	  function representative_details()
    {
    	return $this->hasMany('App\Models\RepresentativeModel','id','representative_id');
    }

    function leads_details()
    {
      return $this->hasMany('App\Models\RepresentativeProductLeadsModel','representative_leads_id','id');
    }

    function order_details()
    {
        return $this->hasMany('App\Models\RepresentativeProductLeadsModel','order_no','order_no');
    }

    function maker_order_details()
    {
        return $this->hasMany('App\Models\RepresentativeProductLeadsModel','maker_id','maker_id');
    }

    function representative_user_details()
    {
        return $this->belongsTo('App\Models\UserModel','representative_id','id');
    }

       

    function retailer_user_details()
    {
        return $this->belongsTo('App\Models\UserModel','retailer_id','id');
    }						

    function maker_details()
    {
        return $this->belongsTo('App\Models\MakerModel','maker_id','user_id');
    }

    function maker_data()
    {
        return $this->belongsTo('App\Models\UserModel','maker_id','id');

    }   
	

    function user_details()
    {
        return $this->belongsTo('App\Models\UserModel','maker_id','id');
    }

    function address_details()
    {
        return $this->belongsTo('App\Models\AddressModel','order_no','order_no');
    }

    function transaction_details()
    {
        return $this->belongsTo('App\Models\TransactionsModel','transaction_id','transaction_id');
    }

    function transaction_mapping()
    {
        return $this->belongsTo('App\Models\TransactionMappingModel','order_no','order_no');
    }

    function transaction_mapping_details()
    {
        return $this->belongsTo('App\Models\TransactionMappingModel','id','order_id');
    }

    function sales_manager_details()
    {
        return $this->belongsTo('App\Models\UserModel','sales_manager_id','id');
    }   
    
    function stripe_transaction_detail()
    {
       return $this->belongsTo('App\Models\StripeTransactionModel','id','quote_id');
    }

    function stripe_transaction_data()
    {
       return $this->hasMany('App\Models\StripeTransactionModel','quote_id','id');
    }
}
