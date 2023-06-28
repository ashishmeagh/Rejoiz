<?php

namespace App\Http\Controllers\Influencer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\InfluencerService;

use App\Models\UserModel;
use App\Models\InfluencerRewardsModel;

use Sentinel;

class TransactionHistoryController extends Controller
{
    public function __construct(UserModel $UserModel,
    							InfluencerRewardsModel $InfluencerRewardsModel,
    							InfluencerService $InfluencerService)
    {	
    	$this->UserModel              = $UserModel;
    	$this->InfluencerRewardsModel = $InfluencerRewardsModel;
    	$this->InfluencerService      = $InfluencerService;

    	$this->arr_view_data          = [];

       	$this->module_title           = 'Transaction History';
       	$this->module_view_folder     = 'influencer.transaction_history';
       	$this->influencer_panel_slug  = config('app.project.influencer_panel_slug');
       	$this->module_url_path        = url($this->influencer_panel_slug.'/transaction_history');
    }

    public function index()
    {
    	$this->arr_view_data['module_title']          = 'Manage '.$this->module_title;
        $this->arr_view_data['influencer_panel_slug'] = $this->influencer_panel_slug;
        $this->arr_view_data['page_title']      	  = 'Manage '.$this->module_title;
        $this->arr_view_data['module_url_path']       = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_transaction_history_listing(Request $request)
    {
    	$form_data = $request->all();

    	/*Get Logged in user id*/
    	$user            = Sentinel::check();
        $loggedIn_userId = 0;

        if($user){
            $loggedIn_userId = $user->id;
        } 

    	$obj_data = $this->InfluencerService->get_transaction_history_listing($form_data,$loggedIn_userId);


        //Calculate total by Harshada on date 09 Sep 2020
         $total_amt = 0;        
         $total_amt =array_reduce($obj_data->get()->toArray(), function(&$res, $item) {
              return $res + $item->reward_amount;
          }, 0);

         
    	$current_context = $this;
    	$json_result     = \Datatables::of($obj_data);

    	$json_result  = $json_result->editColumn('transaction_id',function($data) use ($current_context)
                        {
                            if(isset($data->transaction_id) && $data->transaction_id != '')
                            {
                               return $data->transaction_id;
                            }
                            else
                            {
                                return '-';
                            }
                        })
                         ->editColumn('transfer_id',function($data) use ($current_context)
                        {
                            if(isset($data->transfer_id) && $data->transfer_id != '')
                            {
                               return $data->transfer_id;
                            }
                            else
                            {
                                return '-';
                            }
                        })
                        ->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        });
                        

        $build_result = $json_result->make(true)->getData();
        $build_result->total_amt = $total_amt;
        return response()->json($build_result);
    }

}
