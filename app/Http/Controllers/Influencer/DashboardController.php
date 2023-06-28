<?php

namespace App\Http\Controllers\Influencer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\GeneralSettingModel;
use App\Models\PromoCodeInfluencerMappingModel;
use App\Models\CustomerQuotesModel;
use App\Models\InfluencerRewardsModel;
use App\Models\RetailerQuotesModel;

use App\Models\InfluencerSettingModel;

use Sentinel;
use Flash;
use Session;

class DashboardController extends Controller
{
    
    public function __construct(UserModel $UserModel,
                                RetailerQuotesModel $RetailerQuotesModel, 
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                GeneralSettingModel $GeneralSettingModel,
                                PromoCodeInfluencerMappingModel $PromoCodeInfluencerMappingModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                InfluencerRewardsModel $InfluencerRewardsModel,
                                InfluencerSettingModel $InfluencerSettingModel)
    {
    	$this->arr_view_data         = [];
    	$this->module_title          = "Dashboard";
    	$this->module_view_folder    = 'influencer.dashboard';
     
      	$this->UserModel             = $UserModel;

        $this->StripeAccountDetailsModel = $StripeAccountDetailsModel;
        $this->GeneralSettingModel = $GeneralSettingModel;

        $this->PromoCodeInfluencerMappingModel = $PromoCodeInfluencerMappingModel;
        $this->CustomerQuotesModel    = $CustomerQuotesModel;

        $this->InfluencerRewardsModel = $InfluencerRewardsModel;

        $this->InfluencerSettingModel = $InfluencerSettingModel;

        $this->RetailerQuotesModel = $RetailerQuotesModel;

      	$this->influencer_panel_slug = config('app.project.influencer_panel_slug');
      	$this->module_url_path       = url($this->influencer_panel_slug.'/dashboard');
    }

    public function index()
    {
        $user            = Sentinel::check();
        $loggedIn_userId = 0;

        if($user){
            $loggedIn_userId = $user->id;
        }
        
        /* Get Influencer Code */
        $influencer_code = $this->UserModel
                                     
                                     ->where('id',$loggedIn_userId) 
                                     ->pluck('influencer_code' )
                                     ->all();

        $influencer_order_count = 0;

         /*Get influencer's order's status count.*/
        $influencer_order_count = get_influencer_order_count($influencer_code);
        
        /*Check whether influencer is connected to admin stripe account or not*/
        $obj_stripe_account_details = $this->StripeAccountDetailsModel
                                            ->where('user_id',$loggedIn_userId)
                                            ->first();

        /*
            is_stripe_connected = true (Influencer stripe is connected to admin stripe)
            is_stripe_connected = false (Influencer stripe is not connected to admin stripe)
        */
        if(isset($obj_stripe_account_details)){
            $this->arr_view_data['is_stripe_connected'] = true;
        }else{
            $this->arr_view_data['is_stripe_connected'] = false;
        }

        /* Get Stripe client id from general setting */
        $general_setting_obj = $this->GeneralSettingModel
                                  ->where('data_id','STRIPE_CLIENT_ID')
                                  ->where('type','admin')
                                  ->first();

        $client_id = isset($general_setting_obj->data_value)?$general_setting_obj->data_value:'';

        $this->arr_view_data['connection_request_link'] =  'https://dashboard.stripe.com/oauth/authorize?response_type=code&client_id='.$client_id.'&scope=read_write&state='.$loggedIn_userId;

        /* Get Total assigned count */
        $total_assigned_count  = $this->PromoCodeInfluencerMappingModel
                                      ->where('influencer_id',$loggedIn_userId)
                                      ->count();

        /* Get Total Used Count */
        $arr_customer_orders = $this->CustomerQuotesModel
                                 ->where('influencer_id',$loggedIn_userId)
                                 ->groupBy('order_no')
                                 ->get()
                                 ->toArray();

        $total_used_count = count($arr_customer_orders);

        /* Total Received Rewards */
        $total_received_rewards = $this->InfluencerRewardsModel
                                     ->where('influencer_id',$loggedIn_userId)
                                     ->where('status',2) //success
                                     ->sum('reward_amount');
        /* Total Pending Rewards */
        $total_pending_rewards = $this->InfluencerRewardsModel
                                     ->where('influencer_id',$loggedIn_userId)
                                     ->where('status','!=',2) //Here we want pending (1) and failed (3)
                                     ->sum('reward_amount');


        /* Get Current Sales Target and Rewards from influencer_settings */
        $current_sales_target = $current_reward_amount = 0;

        $obj_influencer_settings = $this->InfluencerSettingModel->first();

        if($obj_influencer_settings)
        {
            $arr_influencer_settings = $obj_influencer_settings->toArray();
            $current_sales_target    = isset($arr_influencer_settings['sales_target'])?num_format($arr_influencer_settings['sales_target']):0;
            $current_reward_amount  = isset($arr_influencer_settings['reward_amount'])?num_format($arr_influencer_settings['reward_amount']):0;
        }


        $this->arr_view_data['current_sales_target']    = $current_sales_target;
        $this->arr_view_data['current_reward_amount']   = $current_reward_amount;

        $this->arr_view_data['total_assigned_count']    = isset($total_assigned_count)?$total_assigned_count:0;
        $this->arr_view_data['total_used_count']        = isset($total_used_count)?$total_used_count:0;
        $this->arr_view_data['total_received_rewards']  = isset($total_received_rewards)?num_format($total_received_rewards):0;
        $this->arr_view_data['total_pending_rewards']   = isset($total_pending_rewards)?num_format($total_pending_rewards):0;

	    $this->arr_view_data['module_title']      = $this->module_title;
      	$this->arr_view_data['page_title']        = 'Dashboard';
      	$this->arr_view_data['module_url_path']   = $this->module_url_path;

        $this->arr_view_data['influencer_order_count']   = $influencer_order_count;
        

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

}
