<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerRewardsModel extends Model
{
    protected $table    ='influencer_rewards';
    
	protected $fillable =  [ 
								'id',
	                        	'influencer_id',
                                'order_ids',
                    			'reward_amount',
                                'admin_settled_sales_target',
                                'admin_settled_reward_amount',
                                'current_order_amount',
                                'total_order_amount',
                                'used_order_amount',
                                'previous_carry_forward_amount',
                                'carry_forward_amount',
                    			'paid_by',
                    			'transfer_id',
                    			'transaction_id',
                    			'destination_payment',
                    			'status',
                                'description'
                           ];

    public function influencer_details(){
        return $this->belongsTo('App\Models\UserModel','influencer_id','id');
    }
}
