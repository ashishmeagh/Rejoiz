<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Common\Services\TransactionHistoryService;
use App\Models\RetailerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\StripeTransactionModel;
use App\Models\MakerModel;
use App\Models\RefundModel;
use App\Models\CustomerQuotesModel;
use App\Models\RoleUsersModel;
use App\Models\RoleModel;
use App\Common\Services\InfluencerService;


use DB;
use DateTime;
use Sentinel;
use Session;
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
                                InfluencerService $InfluencerService,
                                RefundModel $RefundModel,
                                StripeTransactionModel $StripeTransactionModel,
                                MakerModel $MakerModel
                               

                               
                            )

    {
        $this->RoleUsersModel            = $RoleUsersModel;
        $this->RoleModel                 = $RoleModel;
        $this->CustomerQuotesModel       = $CustomerQuotesModel;
        $this->UserModel    	           = $UserModel;
        $this->TransactionsModel         = $TransactionsModel;
        $this->TransactionMappingModel   = $TransactionMappingModel;
        $this->TransactionHistoryService = $TransactionHistoryService;
        $this->RetailerQuotesModel       = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel  = $RepresentativeLeadsModel;
        $this->RefundModel               = $RefundModel;
        $this->StripeTransactionModel    = $StripeTransactionModel;
        $this->MakerModel                = $MakerModel;
        $this->InfluencerService         = $InfluencerService;
       
        $this->maker_panel_slug          = config('app.project.maker_panel_slug');
    	  $this->module_url_path           = url($this->maker_panel_slug.'/transactions/');
    	  $this->module_view_folder        = 'maker.transaction_tables'; 

    }

   
    public function vendor_transaction_details(Request $request)
    { 
          
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['page_title']      = 'Vendor Payments';
        
        return view($this->module_view_folder.'.vendor',$this->arr_view_data);
    }

    public function admin_transaction_details(Request $request)
    {
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['page_title']      = 'Admin Payments';
        
        return view($this->module_view_folder.'.admin',$this->arr_view_data);
    }

  
    public function transactions(Request $request)
    {
        
        $search_data = $request->column_filter;
        
        $transaction_details = $this->get_vendor_transactions($search_data);

         //Calculate total by Harshada on date 09 Sep 2020
         $total_amt = 0;        
         $total_amt =array_reduce($transaction_details->get()->toArray(), function(&$res, $item) {
              return $res + $item->amount;
          }, 0);


        $current_context = $this;
        $json_result     = \Datatables::of($transaction_details);

               
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
                                            return '/vendor/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                                                 
                                        } 
                                        $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                                     ->where('order_no',$data->order_no)
                                                                     ->where('id',$data->order_id)
                                                                     ->first();

                                        if ($arr_rep_order_details) 
                                        {
                                            return '/vendor/representative_orders/view/'.base64_encode($arr_rep_order_details['order_no']);                          
                                        } 

                                        $arr_cust_order_details = $this->CustomerQuotesModel
                                                                     ->where('order_no',$data->order_no)
                                                                     ->where('id',$data->order_id)
                                                                     ->first();

                                        if($arr_cust_order_details)
                                        {
                                            return '/vendor/customer_orders/view/'.base64_encode($arr_cust_order_details['id']);
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

                                    return $order_type;
                                })

                                ->editColumn('amount',function($data) use ($current_context)
                                {

                                    return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                                })->make(true);
                               

                $build_result = $json_result->getData();
                $build_result->total_amt = $total_amt;
                return response()->json($build_result);
    }

    /*show vendor transactions details*/
    public function get_vendor_transactions($arr_search_column=false)
    {   
        /*get login user*/
        $loginUserId = 0;
        $user = Sentinel::check();

        if(isset($user))
        {
          $loginUserId = $user->id;
        }

        $user_table           = $this->UserModel->getTable();
        $prefix_user_table    = DB::getTablePrefix().$user_table;

        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl =  DB::getTablePrefix().$representative_leads;

        $retailer_leads            =  $this->RetailerQuotesModel->getTable();
        $prefix_retailer_leads_tbl = DB::getTablePrefix().$retailer_leads;

        $stripe_transaction        = $this->StripeTransactionModel->getTable();
        $prefix_stripe_transaction = DB::getTablePrefix().$stripe_transaction;

        $maker_table           = $this->MakerModel->getTable();
        $prefix_maker_table    = DB::getTablePrefix().$maker_table;

        $role_table            =  $this->RoleModel->getTable();
        $prefix_role_table     =  DB::getTablePrefix().$role_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table =  DB::getTablePrefix().$role_user_table;

        $customer_order_table        = $this->CustomerQuotesModel->getTable();
        $prefix_customer_order_table = DB::getTablePrefix().$customer_order_table;



        $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_retailer_leads_tbl.'.order_no,'.
                          $prefix_retailer_leads_tbl.'.id as order_id'
                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
                       ->leftJoin($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.id','=',$stripe_transaction.'.lead_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','maker')                       

                       ->where($prefix_stripe_transaction.'.lead_id','!=',0)

                       ->where($prefix_stripe_transaction.'.received_by','=',$loginUserId)
                       ->where($prefix_stripe_transaction.'.paid_by','=',1);

                    $lead_obj = $lead_obj->orderBy($prefix_stripe_transaction.'.created_at','DESC');

          $quote_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.'.id as order_id'
                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
                       ->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','maker')                       

                       ->where($prefix_stripe_transaction.'.quote_id','!=',0)

                       ->where($prefix_stripe_transaction.'.received_by','=',$loginUserId)
                       ->where($prefix_stripe_transaction.'.paid_by','=',1); 

                $quote_obj = $quote_obj->orderBy($prefix_stripe_transaction.'.created_at','DESC');       


            $customer_order_obj = DB::table($stripe_transaction)
                                  ->select(DB::raw($stripe_transaction.".*," .
                                      $prefix_customer_order_table.'.order_no,'.
                                      $prefix_customer_order_table.'.id as order_id'
                                    ))

                                  ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                                   ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                                  
                                   ->leftJoin($prefix_customer_order_table,$prefix_customer_order_table.'.id','=',$stripe_transaction.'.customer_order_id')

                                   ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                                   ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                                   ->where($role_table.'.slug','=','maker')                       

                                   ->where($prefix_stripe_transaction.'.customer_order_id','!=',0)
                                    ->where($prefix_customer_order_table.'.order_no','!=',"")
                                   ->where($prefix_stripe_transaction.'.received_by','=',$loginUserId)
                                   ->where($prefix_stripe_transaction.'.paid_by','=',1); 

                    $customer_order_obj = $customer_order_obj->orderBy($prefix_stripe_transaction.'.created_at','DESC');                 
   

                if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
                {
                    $search_term        = $arr_search_column['q_username'];
                    $lead_obj           = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $quote_obj          = $quote_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->having('user_name','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
                {  
                    $search_term        = $arr_search_column['q_order_no'];

                    $lead_obj           = $lead_obj->having('order_no','LIKE',$search_term."%");
                    $quote_obj          = $quote_obj->having('order_no','LIKE',$search_term."%");
                    $customer_order_obj = $customer_order_obj->having('order_no','LIKE',$search_term."%");

                }

                if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
                {
                    $search_term        = $arr_search_column['q_amount'];

                    $lead_obj           = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');

                    $quote_obj          = $quote_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');

                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_status'];

                    $lead_obj         = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');

                    $quote_obj        = $quote_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');

                    $customer_order_obj        = $customer_order_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
                {
                    $search_term        = $arr_search_column['q_transaction_id'];

                    $lead_obj           = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');

                    $quote_obj          = $quote_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transfer_id']) && $arr_search_column['q_transfer_id']!="")
                {
                    $search_term      = $arr_search_column['q_transfer_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                    
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                    
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                }

                

                if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
                {

                    $search_term        = $arr_search_column['q_created_at'];
                    $date               = DateTime::createFromFormat('m-d-Y',$search_term);
                    $date               = $date->format('Y-m-d');
                  
                    $lead_obj           = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');

                    $quote_obj          = $quote_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                } 

                $lead_obj = $lead_obj->union($quote_obj)->orderBy('created_at','DESC'); 
                
                $lead_obj = $lead_obj->union($customer_order_obj)->orderBy('created_at','DESC'); 

            //dd( $lead_obj->toSql(), $lead_obj->getBindings());

        return $lead_obj;        
    }


    public function admin_transactions(Request $request)
    {
        $search_data = $request->column_filter;
        
        $transaction_details = $this->get_admin_transactions($search_data);
       // echo "<pre>";print_r($transaction_details->get()->toArray());die;
        //Calculate total by Harshada on date 09 Sep 2020
       $total_amt = 0;        
       $total_amt =array_reduce($transaction_details->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;
        }, 0);

        $current_context = $this;
        $json_result     = \Datatables::of($transaction_details);

               
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
                                            return '/vendor/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                                                 
                                        } 
                                        $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                                     ->where('order_no',$data->order_no)
                                                                     ->where('id',$data->order_id)
                                                                     ->first();

                                        if ($arr_rep_order_details) 
                                        {
                                            return '/vendor/representative_orders/view/'.base64_encode($arr_rep_order_details['order_no']);                          
                                        } 


                                        $arr_cust_order_details = $this->CustomerQuotesModel
                                                                     ->where('order_no',$data->order_no)
                                                                     ->where('id',$data->order_id)
                                                                     ->first();

                                        if($arr_cust_order_details)
                                        {
                                            return '/vendor/customer_orders/view/'.base64_encode($arr_cust_order_details['id']);
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

                                    return $order_type;
                                })
                                
                                ->editColumn('amount',function($data) use ($current_context)
                                {

                                    return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                                })->make(true);
        
            $build_result = $json_result->getData();
            $build_result->total_amt = $total_amt;
            return response()->json($build_result);
    }


    public function get_admin_transactions($arr_search_column=false)
    {
        /*get login user*/
        $loginUserId = 0;
        $user = Sentinel::check();

        if(isset($user))
        {
          $loginUserId = $user->id;
        }

        $user_table           = $this->UserModel->getTable();
        $prefix_user_table    = DB::getTablePrefix().$user_table;

        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl =  DB::getTablePrefix().$representative_leads;

        $retailer_leads            =  $this->RetailerQuotesModel->getTable();
        $prefix_retailer_leads_tbl = DB::getTablePrefix().$retailer_leads;

        $stripe_transaction        = $this->StripeTransactionModel->getTable();
        $prefix_stripe_transaction = DB::getTablePrefix().$stripe_transaction;

        $maker_table           = $this->MakerModel->getTable();
        $prefix_maker_table    = DB::getTablePrefix().$maker_table;

        $role_table        =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $customer_order_table        = $this->CustomerQuotesModel->getTable();
        $prefix_customer_order_table = DB::getTablePrefix().$customer_order_table;




        $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_retailer_leads_tbl.'.order_no,'.
                          $prefix_retailer_leads_tbl.'.id as order_id'
                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.paid_by')
                      
                       ->leftJoin($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.id','=',$stripe_transaction.'.lead_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.paid_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','maker')                       

                       ->where($prefix_stripe_transaction.'.lead_id','!=',0)

                       ->where($prefix_stripe_transaction.'.received_by','=',1)
                       ->where($prefix_stripe_transaction.'.paid_by','=',$loginUserId);

                $lead_obj = $lead_obj->orderBy($prefix_stripe_transaction.'.created_at','DESC');
            

          $quote_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.'.id as order_id'
                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.paid_by')
                      
                       ->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.paid_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','maker')                       

                       ->where($prefix_stripe_transaction.'.quote_id','!=',0)
       
                       ->where($prefix_stripe_transaction.'.received_by','=',1)

                       ->where($prefix_stripe_transaction.'.paid_by','=',$loginUserId);

              $quote_obj = $quote_obj->orderBy($prefix_stripe_transaction.'.created_at','DESC');

            $customer_order_obj = DB::table($stripe_transaction)
                                  ->select(DB::raw($stripe_transaction.".*," .
                                      $prefix_customer_order_table.'.order_no,'.
                                      $prefix_customer_order_table.'.id as order_id'
                                    ))

                                  ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                                   ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.paid_by')
                                  
                                   ->leftJoin($prefix_customer_order_table,$prefix_customer_order_table.'.id','=',$stripe_transaction.'.customer_order_id')

                                   ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.paid_by')

                                   ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                                   ->where($role_table.'.slug','=','maker')                       
                                   ->where($prefix_customer_order_table.'.order_no','!=',"")
                                   ->where($prefix_stripe_transaction.'.customer_order_id','!=',0)

                                   ->where($prefix_stripe_transaction.'.received_by','=',1)
                                   ->where($prefix_stripe_transaction.'.paid_by','=',$loginUserId);

                        $customer_order_obj = $customer_order_obj->orderBy($prefix_stripe_transaction.'.created_at','DESC');                      

                if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
                {
                    $search_term      = $arr_search_column['q_username'];
                    $lead_obj = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->having('user_name','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
                {  
                    $search_term      = $arr_search_column['q_order_no'];

                    $lead_obj = $lead_obj->having('order_no','LIKE',$search_term."%");
                    $quote_obj = $quote_obj->having('order_no','LIKE',$search_term."%");
                    $customer_order_obj = $customer_order_obj->having('order_no','LIKE',$search_term."%");
                }

                if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
                {
                    $search_term      = $arr_search_column['q_amount'];
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_status'];
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transfer_id']) && $arr_search_column['q_transfer_id']!="")
                {
                    $search_term      = $arr_search_column['q_transfer_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                }

                

                if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
                {

                    $search_term      = $arr_search_column['q_created_at'];
                    $date             = DateTime::createFromFormat('m-d-Y',$search_term);
                    $date             = $date->format('Y-m-d');
                  
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                     $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                } 

                // dd($lead_obj->get());
                $lead_obj = $lead_obj->union($quote_obj)->orderBy('created_at','DESC'); 
                $lead_obj = $lead_obj->union($customer_order_obj)->orderBy('created_at','DESC'); 

        return $lead_obj;        
    }

    public function get_export_admin_transaction(Request $request)
    {
       $search_data = $request->all();

        $transaction_details = $this->get_admin_transactions($search_data);

         $type  = 'csv'; 
        $data = $arr_transaction = $arrayResponseData = [];

        $arr_transaction = $transaction_details->get()->toArray();

        if(count($arr_transaction) <= 0)
        {
            $response['status']      = 'error';
            $response['message']      = 'No data available for export';
        
            return response()->json($response);
        }

        foreach($arr_transaction as $key => $value)
        { 
            $transaction_status = 'Pending';
            if($value->status == 1 || $value->status==null)
            {
              $transaction_status =  'Pending';
            }else if($value->status == 2)
            {
              $transaction_status = 'Paid';
            }else
            {
              $transaction_status = 'Failed';
            }

            /*Order Type*/
            $order_type ='';

            $retailer_order = $this->RetailerQuotesModel
                                   ->where('order_no',$value->order_no)
                                   ->where('id',$value->order_id)
                                   ->first();

            if(isset($retailer_order))
            {
               $order_type = 'Retailer-Order';
            }


            $rep_sales_order = $this->RepresentativeLeadsModel
                                    ->where('order_no',$value->order_no)
                                    ->where('id',$value->order_id)
                                    ->first();            

            if(isset($rep_sales_order))
            { 
               $order_type = 'Rep-Sales-Order';
            }


            $customer_order = $this->CustomerQuotesModel
                                   ->where('order_no',$value->order_no)
                                   ->where('id',$value->order_id)
                                   ->first();                                

            if(isset($customer_order))
            {
               $order_type = 'Customer-Order';
            }           

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Type']            = $order_type;
            $arrayResponseData['Transaction Id']        = $value->transaction_id;
            $arrayResponseData['Tranfer Id']            = $value->transfer_id;      
            $arrayResponseData['Amount ($)']            = num_format($value->amount);      
            $arrayResponseData['Transaction Status']    = $transaction_status;
            $arrayResponseData['Transaction Date']      = $value->created_at;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Admin Transactions', function($excel) use ($data) {        
            $excel->sheet('Admin Transactions', function($sheet) use ($data)
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

    public function get_export_vendor_transaction(Request $request)
    {
      $search_data = $request->all();

      $transaction_details = $this->get_vendor_transactions($search_data);

        $type  = 'csv'; 
        $data = $arr_transaction = $arrayResponseData = [];

        $arr_transaction = $transaction_details->get()->toArray();

        if(count($arr_transaction) <= 0)
        {
            $response['status']      = 'error';
            $response['message']      = 'No data available for export';
        
            return response()->json($response);
        }

        foreach($arr_transaction as $key => $value)
        { 
            $transaction_status = 'Pending';
            if($value->status == 1 || $value->status==null)
            {
              $transaction_status =  'Pending';
            }else if($value->status == 2)
            {
              $transaction_status = 'Paid';
            }else
            {
              $transaction_status = 'Failed';
            }

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Transaction Id']            = $value->transaction_id;
            $arrayResponseData['Tranfer Id']              = $value->transfer_id;      
            $arrayResponseData['Amount ($)']              = $value->amount;      
            $arrayResponseData['Transaction Status']        = $transaction_status;
            $arrayResponseData['Transaction Date']       = $value->created_at;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Vendor Transactions', function($excel) use ($data) {        
            $excel->sheet('Vendor Transactions', function($sheet) use ($data)
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

    public function retailer_refunds(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->maker_panel_slug.'/refund/retailer');
        $this->arr_view_data['page_title'] = 'Retailer Refunds';
        
        return view('maker.refund.retailer_refunds',$this->arr_view_data);
    }

    public function rep_sales_refunds(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->maker_panel_slug.'/refund/rep_sales');
        $this->arr_view_data['page_title'] = 'Rep / Sales Refunds';
        
        return view('maker.refund.rep_sales_refunds',$this->arr_view_data);
    }


    public function customer_refunds(Request $request)
    {
        $this->arr_view_data['module_url_path'] = url($this->maker_panel_slug.'/refund/customer');
        $this->arr_view_data['page_title']      = 'Customer Refunds';
        
        return view('maker.refund.customer_refunds',$this->arr_view_data);
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

        $obj_user = $this->TransactionHistoryService->get_retailer_refund('maker',$arr_search_column);
       /* dd( $obj_user->get()->toArray());*/
        /* ---------------- Filtering Logic ----------------------------------*/  

        
       /* if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('retailer_name','LIKE', '%'.$search_term.'%');
        }  */ 

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->where('dummy_store_name','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($prefix_refund_table.'.amount','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term      = $arr_search_column['q_transaction_status'];
            $obj_user = $obj_user->where($prefix_refund_table.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {


            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_user = $obj_user->where($prefix_refund_table.'.balance_transaction','LIKE', '%'.$search_term.'%');

        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_user = $obj_user->where($prefix_refund_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($prefix_refund_table.'.created_at','LIKE', '%'.$date.'%');
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

                                $retailer_order_details = $this->RetailerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($retailer_order_details) 
                                {

                                    return '/vendor/cancel_orders/view/'.base64_encode($retailer_order_details->id);      
                                }

                                $rep_sales_order_details = $this->RepresentativeLeadsModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();

                                if ($rep_sales_order_details) 
                                {
                                    return '/vendor/representative_orders/view/'.base64_encode($rep_sales_order_details->order_no);      
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

    public function get_export_retailer_refund_transaction(Request $request)
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

        $obj_user = $this->TransactionHistoryService->get_retailer_refund('maker',$arr_search_column);

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        /*if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('retailer_name','LIKE', '%'.$search_term.'%');
        } */

         if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->where('dummy_store_name','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($prefix_refund_table.'.amount','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term      = $arr_search_column['q_transaction_status'];
            $obj_user = $obj_user->where($prefix_refund_table.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {


            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_user = $obj_user->where($prefix_refund_table.'.balance_transaction','LIKE', '%'.$search_term.'%');

        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_user = $obj_user->where($prefix_refund_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($prefix_refund_table.'.created_at','LIKE', '%'.$date.'%');
        }

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
            //$arrayResponseData['Retailer']          = $value->retailer_name; 
            $arrayResponseData['Retailer']          = $value->dummy_store_name; 
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

        $obj_user = $this->TransactionHistoryService->get_rep_sales_refund('maker',$arr_search_column);

        /* ---------------- Filtering Logic ----------------------------------*/  

      /*  
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('retailer_name','LIKE', '%'.$search_term.'%');
        }  */

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->where('dummy_store_name','LIKE', '%'.$search_term.'%');
        }    
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($prefix_refund_table.'.amount','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term      = $arr_search_column['q_transaction_status'];
            $obj_user = $obj_user->where($prefix_refund_table.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {


            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_user = $obj_user->where($prefix_refund_table.'.balance_transaction','LIKE', '%'.$search_term.'%');

        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_user = $obj_user->where($prefix_refund_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($prefix_refund_table.'.created_at','LIKE', '%'.$date.'%');
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

                                $rep_sales_order_details = $this->RepresentativeLeadsModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();

                                if ($rep_sales_order_details) 
                                {

                                    return '/vendor/rep_sales_cancel_orders/view/'.base64_encode($rep_sales_order_details->order_no);      
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

    public function get_export_reps_sales_refund_transactions(Request $request)
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

        $obj_user = $this->TransactionHistoryService->get_rep_sales_refund('maker',$arr_search_column);

        /* ---------------- Filtering Logic ----------------------------------*/  

        
       /* if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('retailer_name','LIKE', '%'.$search_term.'%');
        }   */
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->where('dummy_store_name','LIKE', '%'.$search_term.'%');
        }    
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_user = $obj_user->where($prefix_refund_table.'.amount','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term      = $arr_search_column['q_transaction_status'];
            $obj_user = $obj_user->where($prefix_refund_table.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {


            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_user = $obj_user->where($prefix_refund_table.'.balance_transaction','LIKE', '%'.$search_term.'%');

        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_user = $obj_user->where($prefix_refund_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($prefix_refund_table.'.created_at','LIKE', '%'.$date.'%');
        }

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
            //$arrayResponseData['Retailer']          = $value->retailer_name; 
            $arrayResponseData['Retailer']          = $value->dummy_store_name; 
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

        $obj_user = $this->TransactionHistoryService->get_customer_refund('maker',$arr_search_column);

        // /* ---------------- Filtering Logic ----------------------------------*/  

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
                            if ($data->order_no != "")
                            {

                                $retailer_order_details = $this->CustomerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if ($retailer_order_details) 
                                {
                                   return '/vendor/customer_cancel_orders/view/'.base64_encode($retailer_order_details->id);      
                                }
                            }
                        })

                        ->editColumn('amount',function($data) use ($current_context)
                        {

                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
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
        $build_result->total_amt = $total_amt;
        return response()->json($build_result);
    }

    public function get_export_customer_refund_transaction(Request $request)
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

        $obj_user = $this->TransactionHistoryService->get_customer_refund('maker',$arr_search_column);

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
}
