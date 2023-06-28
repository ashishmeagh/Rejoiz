<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Models\CustomerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use DB;
use Sentinel;
use DateTime;


class TransactionTableController extends Controller
{
  

  public function __construct(UserModel $UserModel,
    						  TransactionsModel $TransactionsModel,
                              CustomerQuotesModel $CustomerQuotesModel,
                              RepresentativeLeadsModel $RepresentativeLeadsModel,
    						  TransactionMappingModel $TransactionMappingModel)

    {
      $this->UserModel    	         = $UserModel;
      $this->TransactionsModel       = $TransactionsModel;
      $this->CustomerQuotesModel       = $CustomerQuotesModel;
      $this->RepresentativeLeadsModel       = $RepresentativeLeadsModel;
      $this->TransactionMappingModel = $TransactionMappingModel;
      $this->customer_panel_slug     = config('app.project.customer_panel_slug');
      $this->module_url_path         = url($this->customer_panel_slug.'/transactions/get_transaction_details');
      $this->module_title            = "Payment Transactions";
      $this->module_view_folder      = 'customer.transaction_tables';

    }

  public function show_transaction_details(Request $request)
    {  
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']    = $this->module_title;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

   public function get_transaction_details(Request $request)
    {
    	$user = Sentinel::check();
     		
    		$loggedIn_userId = 0;
     
     		if($user)
     		{
    		    $loggedIn_userId = $user->id;
     		}   

    	$arr_search_column = $request->input('column_filter');

    	$user_table =  $this->UserModel->getTable();
  		$prefix_user_table = DB::getTablePrefix().$user_table;

  		$transaction_table =  $this->TransactionsModel->getTable();
  		$prefix_transaction_table = DB::getTablePrefix().$transaction_table;

  		$transaction_mapping_table =$this->TransactionMappingModel->getTable();
  		$prefix_transaction_mapping_table = DB::getTablePrefix().$transaction_mapping_table;

  		$obj_user = DB::table($transaction_mapping_table)
					 ->select(DB::raw(
									   $transaction_mapping_table.".amount,".
									   $transaction_mapping_table.".order_no,".
                                        $transaction_mapping_table.".order_id,".
                                       $transaction_mapping_table.".transaction_status,".
                                       $transaction_mapping_table.".transaction_id,".
                                       $transaction_mapping_table.".created_at"
                                      ))
						->where($transaction_mapping_table.'.user_id','=',$loggedIn_userId)
                        ->orderBy($transaction_mapping_table.'.created_at','DESC');
         /* ---------------- Filtering Logic ----------------------------------*/  
 
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
        }	

         if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_user = $obj_user->where($transaction_mapping_table.'.order_no','LIKE', '%'.$search_term.'%');
        }	

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term      = $arr_search_column['q_transaction_status'];
            $obj_user = $obj_user->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_user = $obj_user->where($transaction_mapping_table.'.transaction_id','LIKE', '%'.$search_term.'%');
        }
        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
             $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term    = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
        }   


       //Calculate total by Harshada on date 09 Sep 2020
        
        $total_amt = 0;      
        $total_amt =array_reduce($obj_user->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;
        }, 0);
    	$current_context = $this;
    	$json_result     = \Datatables::of($obj_user);

       
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                $arr_retailer_order_details = $this->CustomerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($arr_retailer_order_details) 
                                {
                                    return '/customer/my_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                                         
                                } 
                                $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                             ->where('order_no',$data->order_no)
                                                             ->where('id',$data->order_id)
                                                             ->first();

                                if ($arr_rep_order_details) 
                                {

                                    return '/customer/my_orders/order_summary/'.base64_encode($arr_rep_order_details['order_no']).'/'.base64_encode($arr_rep_order_details['maker_id']);
                                                         
                                } 
                            }
                        })

     					          ->editColumn('amount',function($data) use ($current_context)
                        {
                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })->make(true);
                       

        $build_result = $json_result->getData();
         $build_result->total_amt = $total_amt;
        return response()->json($build_result);
        
    }
}
