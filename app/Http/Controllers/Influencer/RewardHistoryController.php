<?php

namespace App\Http\Controllers\Influencer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\InfluencerService;

use App\Models\UserModel;
use App\Models\InfluencerRewardsModel;
use App\Models\CustomerQuotesModel;

use DB;
use Sentinel;

class RewardHistoryController extends Controller
{
    public function __construct(UserModel $UserModel,
    							InfluencerRewardsModel $InfluencerRewardsModel,
    							CustomerQuotesModel $CustomerQuotesModel,
    							InfluencerService $InfluencerService)
    {	
    	$this->UserModel              = $UserModel;
    	$this->InfluencerRewardsModel = $InfluencerRewardsModel;
    	$this->CustomerQuotesModel    = $CustomerQuotesModel;
    	$this->InfluencerService      = $InfluencerService;

    	$this->arr_view_data          = [];

       	$this->module_title           = 'Rewards History';
       	$this->module_view_folder     = 'influencer.rewards_history';
       	$this->influencer_panel_slug  = config('app.project.influencer_panel_slug');
       	$this->module_url_path        = url($this->influencer_panel_slug.'/rewards_history');
        $this->customer_orders_path   = url($this->influencer_panel_slug.'/customer_orders');
    }

    public function index()
    {
    	$this->arr_view_data['module_title']          = 'Manage '.$this->module_title;
        $this->arr_view_data['influencer_panel_slug'] = $this->influencer_panel_slug;
        $this->arr_view_data['page_title']      	  = 'Manage '.$this->module_title;
        $this->arr_view_data['module_url_path']       = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_influencer_rewards_history_listing(Request $request)
    {
    	$form_data = $request->all();

    	/*Get Logged in user id*/
    	$user            = Sentinel::check();
        $loggedIn_userId = 0;

        if($user){
            $loggedIn_userId = $user->id;
        } 

    	$obj_data = $this->InfluencerService->get_influencer_rewards_history_listing($form_data,$loggedIn_userId);


        //Calculate total by Harshada on date 09 Sep 2020
         $total_reward_amt = $total_order_amt= $total_used_amt = $total_carry_forward_amt = 0;        
         $total_reward_amt =array_reduce($obj_data->get()->toArray(), function(&$res, $item) {
              return $res + $item->reward_amount;
          }, 0);

         $total_order_amt =array_reduce($obj_data->get()->toArray(), function(&$res, $item) {
              return $res + $item->total_order_amount;
          }, 0);

         $total_used_amt =array_reduce($obj_data->get()->toArray(), function(&$res, $item) {
              return $res + $item->used_order_amount;
          }, 0);

         $total_carry_forward_amt =array_reduce($obj_data->get()->toArray(), function(&$res, $item) {
              return $res + $item->carry_forward_amount;
          }, 0);


    	$current_context = $this;
    	$json_result     = \Datatables::of($obj_data);

    	$json_result  = $json_result->editColumn('id',function($data) use ($current_context)
                        {
                            if(isset($data->id) && $data->id != '')
                            {
                               return  $id = base64_encode($data->id);
                            }
                        })
                        ->editColumn('created_at',function($data) use ($current_context)
                        {
                            return us_date_format($data->created_at);

                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {   
                            $view_href   =  $this->module_url_path.'/details/'.base64_encode($data->id);
                            
                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Rewards History" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>';
                            
                            return $build_view_action;
                        });

        $build_result = $json_result->make(true)->getData();
        $build_result->total_reward_amt = $total_reward_amt;
        $build_result->total_order_amt = $total_order_amt;
        $build_result->total_used_amt = $total_used_amt;
        $build_result->total_carry_forward_amt = $total_carry_forward_amt;
        return response()->json($build_result);
    }

    public function details($enc_id)
    {
    	$arr_data = $arr_order_ids = $arr_order_data = [];
    	if(isset($enc_id))
    	{

    		$id = base64_decode($enc_id);

    		$obj_data = $this->InfluencerRewardsModel
    						 ->with(['influencer_details'])
    						 ->where('id',$id)
    						 ->first();

    		if($obj_data){
    			$arr_data  = $obj_data->toArray();
    			$order_ids = isset($arr_data['order_ids'])?$arr_data['order_ids']:0;

    			if($order_ids!=0){
    				$arr_order_ids  = explode(', ', $order_ids);
    				$arr_order_data = $this->CustomerQuotesModel
    										->whereIn('id',$arr_order_ids)
    										->get()
    										->toArray();
    			}
    		}
    	}

    	$this->arr_view_data['arr_data']               = $arr_data;
    	$this->arr_view_data['arr_order_data']         = $arr_order_data;

    	$this->arr_view_data['module_title']           = $this->module_title;
        $this->arr_view_data['influencer_panel_slug']  = $this->influencer_panel_slug;
        $this->arr_view_data['page_title']             = $this->module_title.' Details';
        $this->arr_view_data['module_url_path']        = $this->module_url_path;
        $this->arr_view_data['customer_orders_path']   = $this->customer_orders_path;

        return view($this->module_view_folder.'.details',$this->arr_view_data);
    }
}
