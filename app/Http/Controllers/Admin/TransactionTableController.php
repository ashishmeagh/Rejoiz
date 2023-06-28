<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Common\Services\TransactionHistoryService;
use App\Models\RetailerQuotesModel;
use App\Models\RepresentativeLeadsModel;

use App\Models\RoleUsersModel;
use App\Models\RoleModel;
use App\Models\RefundModel;
use App\Models\CustomerQuotesModel;

use App\Common\Services\InfluencerService;
use App\Common\Services\HelperService;

use DB;
use DateTime;
use Sentinel;
use Excel;
  
class TransactionTableController extends Controller
{
   public function __construct( UserModel $UserModel,
    						    TransactionsModel $TransactionsModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                TransactionHistoryService $TransactionHistoryService,
                                TransactionMappingModel $TransactionMappingModel,
                                RoleUsersModel $RoleUsersModel,
                                RoleModel $RoleModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                RefundModel $RefundModel,
                                InfluencerService $InfluencerService,
                                HelperService $HelperService)

    {
        $this->RoleUsersModel            = $RoleUsersModel;
        $this->RoleModel                 = $RoleModel;
        $this->CustomerQuotesModel       = $CustomerQuotesModel;
        $this->RefundModel               = $RefundModel;

        $this->UserModel    	         = $UserModel;
        $this->TransactionsModel         = $TransactionsModel;
        $this->TransactionMappingModel   = $TransactionMappingModel;
        $this->TransactionHistoryService = $TransactionHistoryService;
        $this->RetailerQuotesModel       = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel  = $RepresentativeLeadsModel;

        $this->InfluencerService         = $InfluencerService;
        $this->HelperService             = $HelperService;

        $this->admin_panel_slug          = config('app.project.admin_panel_slug');
    	$this->module_url_path           = url($this->admin_panel_slug.'/transactions/get_transaction_details');
    	$this->module_view_folder        = 'admin.transaction_tables'; 

    }

    public function all_transaction_details(Request $request)
    { 
        
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_transaction');
		$this->arr_view_data['page_title'] = 'All Payments';
       	
       	return view($this->module_view_folder.'.all_transaction',$this->arr_view_data);
    } 


    public function all_commission_transaction(Request $request)
    { 
    
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_transaction');
        $this->arr_view_data['page_title']      = 'All Payments';
        
        return view($this->module_view_folder.'.all_commission_transaction',$this->arr_view_data);
    } 

    public function show_transaction_details(Request $request)
    { 
    
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['page_title'] = 'Customer Payments';
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }
    public function vendor_transaction_details(Request $request)
    { 
    
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_transaction');
        $this->arr_view_data['page_title'] = 'Vendor Payments';
        
        return view($this->module_view_folder.'.vendor',$this->arr_view_data);
    }

    public function reps_transaction_details(Request $request)
    { 

        
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_transaction');
        $this->arr_view_data['page_title'] = 'Representative Payments';
        
        return view($this->module_view_folder.'.reps',$this->arr_view_data);
    }

    public function sales_manager_transaction_details(Request $request)
    { 

        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_transaction');
        $this->arr_view_data['page_title'] = 'Sales Manager Payments';
        
        return view($this->module_view_folder.'.sales_manager',$this->arr_view_data);
    }

    public function admin_transaction_details(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_transaction');
        $this->arr_view_data['page_title'] = 'Admin Payments';
        
        return view($this->module_view_folder.'.admin',$this->arr_view_data);
    }

    public function customer_transaction_details(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_customer_transaction_details');
        $this->arr_view_data['page_title'] = 'Customer Payments';
        
        return view($this->module_view_folder.'.customer',$this->arr_view_data);
    }

    public function influencer_transaction_details(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/transactions/get_influencer_transaction_details');
        $this->arr_view_data['page_title'] = 'Influencer Payments';
        
        return view($this->module_view_folder.'.influencer',$this->arr_view_data);
    }

    public function retailer_refunds(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/refund/retailer');
        $this->arr_view_data['page_title'] = 'Retailer Refunds';
        
        return view('admin.refund.retailer_refunds',$this->arr_view_data);
    }

    public function rep_sales_refunds(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/refund/rep_sales');
        $this->arr_view_data['page_title'] = 'Rep / Sales Refunds';
        
        return view('admin.refund.rep_sales_refunds',$this->arr_view_data);
    }

    public function customer_refunds(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->admin_panel_slug.'/refund/customer');
        $this->arr_view_data['page_title']      = 'Customer Refunds';
        
        return view('admin.refund.customer_refunds',$this->arr_view_data);
    }

    public function transactions(Request $request)
    {
       
        $search_data = $request->column_filter;
        
        $transaction_details = $this->TransactionHistoryService->get_order_list($search_data);

        //dd($transaction_details->get()->toArray());
        $total_amt = 0;      
        $total_amt =array_reduce($transaction_details->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;

        }, 0);
        $total_amt = isset($total_amt)?num_format($total_amt):0.00;

        $current_context = $this;
        $json_result     = \Datatables::of($transaction_details);
        $order_type = '';

               
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                                {
                                    return $formated_date = us_date_format($data->created_at);
                                })
                                ->editColumn('order_no',function($data) use ($current_context)
                                {
                                   return $data->order_no;
                                })

                                

                                ->editColumn('order_link',function($data) use ($current_context)
                                {
                                    
                                    if ($data->order_no != "") 
                                    {

                                        $arr_retailer_order_details = $this->RetailerQuotesModel
                                                                 ->where('order_no',$data->order_no)
                                                                 ->where('id',$data->order_id)
                                                                 ->first();

                                    

                                        if ($arr_retailer_order_details) 
                                        {
                                            return '/admin/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                                                 
                                        } 
                                        
                                        $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                                      ->where('order_no',$data->order_no)
                                                                      ->where('id',$data->order_id)
                                                                      ->first();

                                                                   

                                        if ($arr_rep_order_details) 
                                        {
                                            return '/admin/leads/view/'.base64_encode($arr_rep_order_details['id']);                          
                                        } 

                                        $customer_order_details = $this->CustomerQuotesModel
                                                                       ->where('order_no',$data->order_no)
                                                                       ->where('id',$data->order_id)
                                                                       ->first();

                                                        

                                        if ($customer_order_details) 
                                        {
                                            return '/admin/customer_orders/view/'.base64_encode($customer_order_details['id']);      
                                        }
                                        
                                    }

                                   
                                })

                                ->editColumn('order_type',function($data) use ($current_context)
                                {
                                    $order_type ='';

                                    $retailer_order = $this->RetailerQuotesModel
                                                           ->where('order_no',$data->order_no)
                                                           ->where('id',$data->order_id)
                                                           ->first();

                                                         

                                    if(isset($retailer_order))
                                    {
                                       $order_type = 'Retailer-Order';
                                    }


                                    $rep_sales_order = $this->RepresentativeLeadsModel
                                                            ->where('order_no',$data->order_no)
                                                            ->where('id',$data->order_id)
                                                            ->first();

                                                           

                                    if(isset($rep_sales_order))
                                    { 
                                       $order_type = 'Rep-Sales-Order';
                                    }


                                    $customer_order = $this->CustomerQuotesModel
                                                           ->where('order_no',$data->order_no)
                                                           ->where('id',$data->order_id)
                                                           ->first();

                                                        

                                    if(isset($customer_order))
                                    {
                                       $order_type = 'Customer-Order';
                                    }                                                                      
                                    $this->order_type = $order_type;
                                    return $order_type;
                                })
                                ->editColumn('company_name',function($data) use ($current_context)
                                {
                                  
                                   $vendor_data = $this->HelperService->get_order_company_name($data->order_no,$data->order_id,$this->order_type);
                                //dd($data);

                                   return $vendor_data;
                                })

                                ->editColumn('sender',function($data) use ($current_context)
                                {
                                    $sender = '';

                                    $sender = isset($data->paid_by)?get_role($data->paid_by):'';

                                    return $sender; 
                                })

                                ->editColumn('reciever',function($data) use ($current_context)
                                {
                                    $reciver = "";

                                    $reciever = isset($data->received_by)?get_role($data->received_by):''; 
                                    return  $reciever;
                                })    



                                ->editColumn('amount',function($data) use ($current_context)
                                {

                                    return $amount = '<i class="fa fa-usd" aria-hidden="true"></i> '.num_format($data->amount);
                                })->make(true);
                               

                $build_result = $json_result->getData();
                $build_result->total_amt = $total_amt;

              //echo "<pre>";  print_r($build_result); exit;
                return response()->json($build_result);
    }

    /* Shown Retailer Transaction Details */
    public function get_transaction_details(Request $request)
    {
    	
        $arr_search_column = $request->input('column_filter');

    	$user_table        = $this->UserModel->getTable();
		$prefix_user_table = DB::getTablePrefix().$user_table;

		$transaction_table        = $this->TransactionsModel->getTable();
		$prefix_transaction_table = DB::getTablePrefix().$transaction_table;

		$transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping_table = DB::getTablePrefix().$transaction_mapping_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

       /* $obj_user = DB::table($transaction_mapping_table)
						->select(DB::raw($prefix_user_table.".id as id,".
                                    		"CONCAT(".$prefix_user_table.".first_name,' ',"
                                            .$prefix_user_table.".last_name) as user_name,".
                                            $transaction_mapping_table.".amount,".
                                            $transaction_mapping_table.".transaction_status,".
                                            $transaction_mapping_table.".transaction_id,".
                                            $transaction_mapping_table.".order_no,".
                                            $transaction_mapping_table.".order_id,".
                                            $transaction_mapping_table.".created_at"
                                     ))
						->leftJoin($user_table,$transaction_mapping_table.'.user_id','=',$user_table.'.id')
                       
                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                        ->where($role_table.'.slug','=','retailer')
                        
						->orderBy($transaction_mapping_table.'.created_at','DESC');*/


        $obj_user   = DB::table($transaction_mapping_table)
                        ->select(DB::raw($transaction_mapping_table.".order_no as order_no," .
                          $transaction_mapping_table.".order_id as order_id," .
                          $transaction_mapping_table.'.transaction_id,'.
                          $transaction_mapping_table.'.amount,'.
                          $transaction_mapping_table.'.transaction_status,'.
                          $transaction_mapping_table.'.created_at,'.
                          $transaction_mapping_table.'.user_id as company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"

                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                        ->where($role_table.'.slug','=','retailer')
                        
                        ->orderBy($transaction_mapping_table.'.created_at','DESC');             
                      

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }	
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_user = $obj_user->where($transaction_mapping_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
        }   

       
        $total_amt = 0;      
        $total_amt =array_reduce($obj_user->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;
        }, 0);
         $total_amt = isset($total_amt)?num_format($total_amt):0.00;

    	$current_context = $this;
    	$json_result     = \Datatables::of($obj_user);

       $this->order_type = '';
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                $arr_retailer_order_details = $this->RetailerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($arr_retailer_order_details) 
                                {
                                    $this->order_type = "Retailer-Order";
                                    return '/admin/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                   
                                                         
                                } 
                                $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                             ->where('order_no',$data->order_no)
                                                             ->where('id',$data->order_id)
                                                             ->first();

                                if ($arr_rep_order_details) 
                                {
                                     $this->order_type = "Rep-Sales-Order";
                                    return '/admin/leads/view/'.base64_encode($arr_rep_order_details['id']);
                                                             
                                } 
                            }
                        })

                        ->editColumn('company_name',function($data) use ($current_context)
                        {
                           
                            $company_name = $this->HelperService->get_order_company_name($data->order_no,$data->order_id,$this->order_type);
                            return $company_name;
                        })

                        ->editColumn('amount',function($data) use ($current_context)
                        {

                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })->make(true);
                       

        $build_result = $json_result->getData();
        $build_result->total_amt = $total_amt;

         
        return response()->json($build_result);
        
    }

    /* Shown Customer Transaction Details */
    public function get_customer_transaction_details(Request $request)
    {
        
        $arr_search_column = $request->input('column_filter');

        $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $transaction_table        = $this->TransactionsModel->getTable();
        $prefix_transaction_table = DB::getTablePrefix().$transaction_table;

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping_table = DB::getTablePrefix().$transaction_mapping_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = DB::table($transaction_mapping_table)
                        ->select(DB::raw(
                                            $transaction_mapping_table.".amount,".
                                            $transaction_mapping_table.".transaction_status,".
                                            $transaction_mapping_table.".transaction_id,".
                                            $transaction_mapping_table.".order_no,".
                                            $transaction_mapping_table.".order_id,".
                                            $transaction_mapping_table.".created_at,".

                                            $prefix_user_table.".id as id,".
                                            "CONCAT(".$prefix_user_table.".first_name,' ',"
                                            .$prefix_user_table.".last_name) as user_name"
                                     ))
                        ->leftJoin($user_table,$transaction_mapping_table.'.user_id','=',$user_table.'.id')
                       
                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                        ->where($role_table.'.slug','=','customer')
                        
                        ->orderBy($transaction_mapping_table.'.created_at','DESC');
                      

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_user = $obj_user->where($transaction_mapping_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
        }   
   

        $total_amt = 0;      
        $total_amt =array_reduce($obj_user->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;
        }, 0);
        $total_amt = isset($total_amt)?num_format($total_amt):0.00;
        $current_context = $this;
        $json_result     = \Datatables::of($obj_user);

       
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                $customer_order_details = $this->CustomerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($customer_order_details) 
                                {
                                    return '/admin/customer_orders/view/'.base64_encode($customer_order_details['id']);      
                                }
                            }
                        })
                        ->editColumn('company_name',function($data) use ($current_context)
                        {

                             $company_name = $this->HelperService->get_order_company_name($data->order_no,$data->order_id,"Customer-Order");
                            return $company_name;
                        })

                        ->editColumn('amount',function($data) use ($current_context)
                        {

                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })->make(true);
                       

        $build_result = $json_result->getData();
        $build_result->total_amt = $total_amt;
         
        return response()->json($build_result);
    }

    public function get_influencer_transaction_details(Request $request)
    {
        $form_data = $request->all();

        $obj_data = $this->InfluencerService->get_transaction_history_listing($form_data);       

        $total_amt = 0;      
        $total_amt =array_reduce($obj_data->get()->toArray(), function(&$res, $item) {
            return $res + $item->reward_amount;
        }, 0);
        $total_amt = isset($total_amt)?num_format($total_amt):0.00;
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

    public function get_retailer_refund_details(Request $request)
    {
        $arr_search_column = $request->input('column_filter');

        $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $refund_table        = $this->RefundModel->getTable();
        $prefix_refund_table = DB::getTablePrefix().$refund_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = $this->TransactionHistoryService->get_retailer_refund('admin',$arr_search_column);

        // /* ---------------- Filtering Logic ----------------------------------*/  

        
        // if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        // {
        //     $search_term      = $arr_search_column['q_retailer_name'];
        //     $obj_user = $obj_user->having('retailer_name','LIKE', '%'.$search_term.'%');
        // }   
        // if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        // {
        //     $search_term      = $arr_search_column['q_amount'];
        //     $obj_user = $obj_user->where($prefix_refund_table.'.amount','LIKE', '%'.$search_term.'%');
        // }   

        // if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        // {
        //     $search_term      = $arr_search_column['q_transaction_status'];
        //     $obj_user = $obj_user->where($prefix_refund_table.'.status','LIKE', '%'.$search_term.'%');
        // }
        // if(isset($arr_search_column['q_paid_by']) && $arr_search_column['q_paid_by']!="")
        // {
        //     $search_term      = $arr_search_column['q_paid_by'];
             
        //     $obj_user = $obj_user->having('paid_by','LIKE', '%'.$search_term.'%');
        // }

        // if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        // {


        //     $search_term      = $arr_search_column['q_transaction_id'];

        //     $obj_user = $obj_user->where($prefix_refund_table.'.balance_transaction','LIKE', '%'.$search_term.'%');

        // }

        // if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        // {
        //     $search_term      = $arr_search_column['q_order_no'];

        //     $obj_user = $obj_user->where($prefix_refund_table.'.order_no','LIKE', '%'.$search_term.'%');
        // }

        // if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        // {


        //     $search_term      = $arr_search_column['q_created_at'];
        //     $date             = DateTime::createFromFormat('m-d-Y',$search_term);
        //     $date             = $date->format('Y-m-d');
            
        //     //$search_term  = date('Y-m-d',strtotime($search_term));
        //     $obj_user = $obj_user->where($prefix_refund_table.'.created_at','LIKE', '%'.$date.'%');
        // }   

        $current_context = $this;
        $json_result     = \Datatables::of($obj_user);

       
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                $retailer_order_details = $this->RetailerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($retailer_order_details) 
                                {
                                    return '/admin/cancel_orders/view/'.base64_encode($retailer_order_details->id);      
                                }
                            }
                        })

                        ->editColumn('amount',function($data) use ($current_context)
                        {

                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })
                         ->editColumn('paid_by',function($data) use ($current_context)
                        {

                            $paid_by = "Admin";
                            if($data->paid_by!=null)
                            {
                                $paid_by = $data->paid_by;
                            }
                            return $paid_by;
                        })

                        ->editColumn('retailer_name',function($data) use ($current_context)
                        {
                            if(isset($data->retailer_name) && $data->retailer_name != '')
                            {
                                $retaileName = $data->retailer_name;
                            }
                            elseif(isset($data->retailer) && $data->retailer != '')
                            {
                                $retaileName = $data->retailer;
                            }
                            return $retaileName;
                        })
                        ->make(true);
                       

        $build_result = $json_result->getData();
         
        return response()->json($build_result);
    }

    public function get_export_retailer_refund(Request $request)
    {
        $arr_search_column = $request->all();

         $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $refund_table        = $this->RefundModel->getTable();
        $prefix_refund_table = DB::getTablePrefix().$refund_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = $this->TransactionHistoryService->get_retailer_refund('admin',$arr_search_column);

        $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $obj_user->get()->toArray();

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
              $status = 'Paid';
            }else
            {
              $status = 'Failed';
            }

            $arrayResponseData['Order No']          = $value->order_no;
            $arrayResponseData['Date']              = $value->created_at;
            $arrayResponseData['Retailer']          = $value->retailer_name; 
            $arrayResponseData['Paid By']           = $value->paid_by; 
            $arrayResponseData['Transaction Id']    = $value->balance_transaction;      
            $arrayResponseData['Amount ($)']        = $value->amount;
            $arrayResponseData['Status']              = $status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Retailer Refunds', function($excel) use ($data) {
        
        $excel->sheet('Retailer Refunds', function($sheet) use ($data)
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

    public function get_rep_sales_refund_details(Request $request)
    {
        $arr_search_column = $request->input('column_filter');

        $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $refund_table        = $this->RefundModel->getTable();
        $prefix_refund_table = DB::getTablePrefix().$refund_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = $this->TransactionHistoryService->get_rep_sales_refund('admin',$arr_search_column);

        // /* ---------------- Filtering Logic ----------------------------------*/  

        $current_context = $this;
        $json_result     = \Datatables::of($obj_user);

       
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                $rep_sales_order_details = $this->RepresentativeLeadsModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                                        
                                if ($rep_sales_order_details) 
                                {
                                   return '/admin/rep_sales_cancel_orders/view/'.base64_encode($rep_sales_order_details->id);      
                                }
                            }
                        })

                        ->editColumn('amount',function($data) use ($current_context)
                        {

                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })

                        ->editColumn('paid_by',function($data) use ($current_context)
                        {

                            $paid_by = "Admin";
                            if($data->paid_by!=null)
                            {
                                $paid_by = $data->paid_by;
                            }
                            return $paid_by;
                        })

                        ->editColumn('retailer_name',function($data) use ($current_context)
                        {
                            if(isset($data->retailer_name) && $data->retailer_name != '')
                            {
                                $retaileName = $data->retailer_name;
                            }
                            elseif(isset($data->retailer) && $data->retailer != '')
                            {
                                $retaileName = $data->retailer;
                            }
                            return $retaileName;
                        })
                        ->make(true);
                       

        $build_result = $json_result->getData();
         
        return response()->json($build_result);
    }

    public function get_export_reps_sales_refund(Request $request)
    {
        $arr_search_column = $request->all();

        $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $refund_table        = $this->RefundModel->getTable();
        $prefix_refund_table = DB::getTablePrefix().$refund_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = $this->TransactionHistoryService->get_rep_sales_refund('admin',$arr_search_column);

        $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $obj_user->get()->toArray();

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
              $status = 'Paid';
            }else
            {
              $status = 'Failed';
            }

            $arrayResponseData['Order No']          = $value->order_no;
            $arrayResponseData['Date']              = $value->created_at;
            $arrayResponseData['Rep/Sales Name']              = $value->user_name;
            $arrayResponseData['Retailer']          = $value->retailer_name; 
            $arrayResponseData['Paid By']           = $value->paid_by; 
            $arrayResponseData['Transaction Id']    = $value->balance_transaction;      
            $arrayResponseData['Amount ($)']        = $value->amount;
            $arrayResponseData['Status']              = $status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Reps Sales Refunds', function($excel) use ($data) {
        
        $excel->sheet('Reps Sales Refunds', function($sheet) use ($data)
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

    public function get_customer_refund_details(Request $request)
    {
        $arr_search_column = $request->input('column_filter');

        $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $refund_table        = $this->RefundModel->getTable();
        $prefix_refund_table = DB::getTablePrefix().$refund_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = $this->TransactionHistoryService->get_customer_refund('admin',$arr_search_column);

        // /* ---------------- Filtering Logic ----------------------------------*/  

 

        $current_context = $this;
        $json_result     = \Datatables::of($obj_user);

       
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                // $retailer_order_details = $this->RetailerQuotesModel
                                //                          ->where('order_no',$data->order_no)
                                //                          ->where('id',$data->order_id)
                                //                          ->first();
                                 $retailer_order_details = $this->CustomerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($retailer_order_details) 
                                {
                                    return '/admin/customer_cancel_orders/view/'.base64_encode($retailer_order_details->id);      
                                }
                            }
                        })

                        ->editColumn('amount',function($data) use ($current_context)
                        {

                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })

                         ->editColumn('paid_by',function($data) use ($current_context)
                        {

                            $paid_by = "Admin";
                            if($data->paid_by!=null)
                            {
                                $paid_by = $data->paid_by;
                            }
                            return $paid_by;
                        })

                        ->editColumn('customer_name',function($data) use ($current_context)
                        {
                            if(isset($data->customer_name) && $data->customer_name != '')
                            {
                                $customerName = $data->customer_name;
                            }
                            elseif(isset($data->customer) && $data->customer != '')
                            {
                                $customerName = $data->customer;
                            }
                            return $customerName;
                        })
                        ->make(true);
                       

        $build_result = $json_result->getData();
         
        return response()->json($build_result);
    }

    public function get_export_customer_refund(Request $request)
    {
        $arr_search_column = $request->all();

        $user_table        = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $refund_table        = $this->RefundModel->getTable();
        $prefix_refund_table = DB::getTablePrefix().$refund_table;

        $role_table        = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

        $role_user_table        = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = $this->TransactionHistoryService->get_customer_refund('admin',$arr_search_column);

        $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $obj_user->get()->toArray();

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
              $status = 'Paid';
            }else
            {
              $status = 'Failed';
            }

            $arrayResponseData['Order No']          = $value->order_no;
            $arrayResponseData['Date']              = $value->created_at;
            $arrayResponseData['Customer']          = $value->customer_name; 
            $arrayResponseData['Paid By']           = $value->paid_by; 
            $arrayResponseData['Transaction Id']    = $value->balance_transaction;      
            $arrayResponseData['Amount ($)']        = $value->amount;
            $arrayResponseData['Status']              = $status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Customer Refunds', function($excel) use ($data) {
        
        $excel->sheet('Customer Refunds', function($sheet) use ($data)
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


    //this function for gets all order transaction customer,rep/sales,retailer orders transaction payments.
    public function get_order_transaction (Request $request)
    {

    }
}
