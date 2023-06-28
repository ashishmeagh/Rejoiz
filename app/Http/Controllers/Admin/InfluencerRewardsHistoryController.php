<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\InfluencerService;

use App\Models\UserModel;
use App\Models\InfluencerRewardsModel;
use App\Models\CustomerQuotesModel;

use DB;
use Excel;

class InfluencerRewardsHistoryController extends Controller
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

       	$this->module_title           = 'Influencer Rewards History';
       	$this->module_view_folder     = 'admin.influencer_rewards_history';
       	$this->admin_panel_slug       = config('app.project.admin_panel_slug');
       	$this->module_url_path        = url($this->admin_panel_slug.'/influencer_rewards_history');

       	$this->customer_orders_path = url($this->admin_panel_slug.'/customer_orders');
        $this->influencer_view_path = url($this->admin_panel_slug."/influencer");
    }

    public function index()
    {
    	$this->arr_view_data['module_title']     = 'Manage '.$this->module_title;
        $this->arr_view_data['admin_panel_slug'] = $this->admin_panel_slug;
        $this->arr_view_data['page_title']       = 'Manage '.$this->module_title;
        $this->arr_view_data['module_url_path']  = $this->module_url_path;

        $this->arr_view_data['influencer_view_path'] = $this->influencer_view_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_influencer_rewards_history_listing(Request $request)
    {
    	$form_data = $request->all();

    	$obj_data = $this->InfluencerService->get_influencer_rewards_history_listing($form_data);

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
                        ->editColumn('user_name',function($data) use ($current_context){
                           return isset($data->user_name)?$data->user_name:'N/A';
                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {   
                            $view_href   =  $this->module_url_path.'/details/'.base64_encode($data->id);
                            
                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>';
                            
                            return $build_view_action;
                        });

        $build_result = $json_result->make(true)->getData();

        return response()->json($build_result);
    }

    public function get_export_influencer_reward_history(Request $request)
    {
        $form_data['column_filter'] = $request->all();
        $obj_data = $this->InfluencerService->get_influencer_rewards_history_listing($form_data);

        $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $obj_data->get()->toArray();
        
        if(count($arr_orders) <= 0)
        {
            $response['status']      = 'error';
            $response['message']      = 'No data available for export';
        
            return response()->json($response);
        } 

        foreach($arr_orders as $key => $value)
        { 
            $status = 'Pending';
            if($value->status == 1 || $value->status==null)
            {
              $status =  'Pending';
            }else if($value->status == 2)
            {
              $status = 'Success';
            }else
            {
              $status = 'Failed';
            }

            $arrayResponseData['Influencer Name']           = $value->user_name;
            $arrayResponseData['Date']                      = $value->created_at;
            $arrayResponseData['Reward Amount ($)']         = $value->reward_amount; 
            $arrayResponseData['Total Order Amount ($)']    = $value->total_order_amount; 
            $arrayResponseData['Used Order Amount ($)']     = $value->used_order_amount;      
            $arrayResponseData['Carry Forward Amount ($)']  = $value->carry_forward_amount;
            $arrayResponseData['Status']                    = $status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Influencer Reward History', function($excel) use ($data) {
        
        $excel->sheet('Influencer Reward History', function($sheet) use ($data)
        {
          $sheet->fromArray($data);
          $sheet->freezeFirstRow();  
          $sheet->cells("M2:M20", function($cells) {            
            $cells->setFont(array(              
              'bold'       =>  true
            ));

          });
        });
      })->download($type);

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

    	$this->arr_view_data['arr_data']             = $arr_data;
    	$this->arr_view_data['arr_order_data']       = $arr_order_data;

    	$this->arr_view_data['module_title']         = $this->module_title;
        $this->arr_view_data['admin_panel_slug']     = $this->admin_panel_slug;
        $this->arr_view_data['page_title']           = $this->module_title.' Details';
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['customer_orders_path'] = $this->customer_orders_path;
        $this->arr_view_data['influencer_view_path'] = $this->influencer_view_path;

        return view($this->module_view_folder.'.details',$this->arr_view_data);
    }
}
    
