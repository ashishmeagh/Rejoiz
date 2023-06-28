<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RepresentativeLeadsModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\RetailerModel;
use App\Models\RefundModel;
use App\Models\SalesManagerModel;
use App\Models\MakerModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;


use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;

class RepSalesCanceledOrderController extends Controller
{
    public function __construct(RepresentativeLeadsModel $RepresentativeLeadsModel,
                                UserModel $UserModel,
                                RetailerModel $RetailerModel,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                TransactionsModel $TransactionsModel,
                                StripePaymentService $StripePaymentService,
                                RefundModel $RefundModel,
                                GeneralService $GeneralService,
                                HelperService $HelperService,
                                MakerModel $MakerModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                SalesManagerModel $SalesManagerModel           
                                )
    {
        $this->BaseModel                = $RepresentativeLeadsModel;  
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel; 
        $this->UserModel                = $UserModel;
        $this->TransactionMappingModel  = $TransactionMappingModel;
        $this->TransactionsModel        = $TransactionsModel;
        $this->StripePaymentService     = $StripePaymentService;
        $this->RetailerModel            = $RetailerModel;
        $this->RefundModel              = $RefundModel;
        $this->GeneralService           = $GeneralService;
        $this->HelperService            = $HelperService;
        $this->SalesManagerModel        = $SalesManagerModel;
        $this->MakerModel               = $MakerModel;
        $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;
        $this->arr_view_data            = [];
        $this->module_title             = "Rep/Sales Cancelled Orders";
        $this->module_view_folder       = 'admin.rep_sales_cancel_orders';
        $this->admin_panel_slug         = config('app.project.admin_panel_slug');
        $this->module_url_path          = url($this->admin_panel_slug.'/rep_sales_cancel_orders');
        $this->module_url               = url('/');
    }

    public function index(Request $request)
    {
        
        //$retailer_id = $request->input('retailer_id',null);
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Rep/Sales Cancelled Orders';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['module_url']       = $this->module_url;
        //$this->arr_view_data['retailer_id']      = $retailer_id;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_my_orders(Request $request)
    { 
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }


        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $user_maker_table        =  $this->UserModel->getTable();
        $prefix_user_maker_table = DB::getTablePrefix().$user_maker_table;        

       
        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

        $transaction_mapping        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

        $sales_manager_table         = $this->SalesManagerModel->getTable();
        $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

        $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
        $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $maker_table        = $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;




        $obj_qutoes = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,".

                              "RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.

                              $prefix_retailer_table.'.store_name,'.
                              $prefix_maker_table.'.company_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".

                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".

                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$prefix_representative_leads_tbl.'.retailer_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftJoin($prefix_sales_manager_table,$prefix_sales_manager_table.'.user_id','=',$prefix_user_table.'.id')
       

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })

                           ->orderBy($prefix_representative_leads_tbl.".id",'DESC')
                           ->groupBy($prefix_representative_leads_tbl.'.id')
                          
                           ->where($representative_leads.'.representative_id','!=','0')

                           ->where($representative_leads.'.order_cancel_status','=',2)
                       
                           ->where($representative_leads.'.total_wholesale_price','>','0');                           
                           


    $sales_man_lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".

                            $prefix_transaction_mapping.'.transaction_status,'.
                            $prefix_transaction_mapping.'.order_id,'.
                            $prefix_retailer_table.'.store_name,'.
                            $prefix_maker_table.'.company_name,'.

                            "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                            
                            " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                            "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
 
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                            ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.sales_manager_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_sales_manager_table,$prefix_sales_manager_table.'.user_id','=',$prefix_user_table.'.id')

                          
                           ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })

                           ->orderBy($prefix_representative_leads_tbl.".id",'DESC')

                           ->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.sales_manager_id','!=','0')

                           ->where($representative_leads.'.order_cancel_status','=',2)
       
                           ->where($representative_leads.'.total_wholesale_price','>','0');
         
       

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');


        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {  
            $search_term  = $arr_search_column['q_order_no'];
            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.order_no','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
        }

       
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having('store_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes = $obj_qutoes->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%');
            
        } 
     

        if(isset($arr_search_column['q_rep_sales_name']) && $arr_search_column['q_rep_sales_name']!="")
        { 
            $search_term  = $arr_search_column['q_rep_sales_name'];
           

            $obj_qutoes = $obj_qutoes->having('representative_user_name','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');
           
        }
   
        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
      
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
        } 

         if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($representative_leads.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term    = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes     = $obj_qutoes->having($representative_leads.'.total_wholesale_price','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having($representative_leads.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_payment_type']) && $arr_search_column['q_payment_type']!="")
        {
            $search_term = $arr_search_column['q_payment_type'];

            $obj_qutoes  = $obj_qutoes->where($representative_leads.'.is_direct_payment','=',$search_term);

            $sales_man_lead_obj  = $sales_man_lead_obj->where($representative_leads.'.is_direct_payment','=',$search_term);
        }  

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {
          $search_term  = $arr_search_column['q_payment_status'];

          if($search_term == 1) 
          {
            $obj_qutoes = $obj_qutoes->whereNotExists(function($query){
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
            });

            $sales_man_lead_obj = $sales_man_lead_obj->whereNotExists(function($query){
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
            });

          }
          else
          {

              $obj_qutoes     = $obj_qutoes->where($prefix_transaction_mapping.'.transaction_status','LIKE','%'.$search_term.'%');

              $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_transaction_mapping.'.transaction_status','LIKE','%'.$search_term.'%');

          }
        }


        if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];
           
            if ($search_term_refund_status == '0')
            {
              
                $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status)
                                           ->where($prefix_transaction_mapping.'.transaction_status','LIKE', '2');


                $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status', $search_term_refund_status)
                                    ->where($prefix_transaction_mapping.'.transaction_status','LIKE', '2');                            

               
            }
            else{
              
                $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

                $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status', $search_term_refund_status);
               
              
            }
        } 


         if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
           
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '>=', $order_from_date);
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

             $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $order_from_date);


            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status', $search_term_refund_status);


        } 
        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status'] == 0)
        {
           
            $obj_qutoes = $obj_qutoes->where($representative_leads.'.maker_confirmation','1');

            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.maker_confirmation',1);    
        }

        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && $arr_search_column['q_order_to_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '>=', $order_from_date);
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

             $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $order_from_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status',$search_term_refund_status);
        } 

        if(isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && $arr_search_column['q_order_from_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];
          
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '>=', $order_from_date);
            
            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $order_from_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status',$search_term_refund_status);
        } 

        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="" && $arr_search_column['q_order_from_date']=="" && $arr_search_column['q_order_to_date']=="")
        {
          $search_term_refund_status  = $arr_search_column['q_refund_status'];
           $obj_qutoes                 = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

          $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status',$search_term_refund_status);
        } 

        $obj_qutoes = $obj_qutoes->union($sales_man_lead_obj);
        $obj_qutoes = $obj_qutoes->orderBy('id','DESC');
       
        //dd($obj_qutoes->get()->toArray());
        //Calculate total by Harshada on date 09 Sep 2020
       $total_amt = 0;        
       $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_wholesale_price;
        }, 0);
        
        $current_context = $this;
        $json_result  = Datatables::of($obj_qutoes);
        
        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return us_date_format($data->created_at);


                        })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                             
                            $products_arr = get_lead_products($id,$order_no);
                            return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);

                        })

                        ->editColumn('order_placed_by',function($data) use ($current_context)
                        {   
                            $user_name = '';

                            if(isset($data->representative_user_name) && $data->representative_user_name!='')
                            {
                               $user_name = $data->representative_user_name;
                            }
                            else
                            {
                                $user_name = '';
                            }
                            
                            return $user_name;
                        })

                        ->editColumn('payment_type',function($data) use ($current_context)
                        {   
                            $payment_type ='';

                            if(isset($data->is_direct_payment) && $data->is_direct_payment == 1)
                            {
                                $payment_type = 'Direct';
                            }
                            else
                            {
                                $payment_type = 'In-Direct';
                            }

                            return $payment_type;
                            
                        })

                        ->editColumn('build_refund_btn',function($data) use ($current_context)
                        {   
                            //get unread messages count
                            $build_refund_action = "-";
                            if($data->transaction_status == 2 && $data->refund_status == 0)
                            {
                              if($data->is_direct_payment == 0)
                              {

                                $build_refund_action = '<a  href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund payment" class="btn btn-circle btn-outline btn-success show-tooltip" onclick="refundProcess('.$data->id.')">Refund</a>';   
                              }
                              else
                              {
                                $build_refund_action = '<b>--</b>';  
                              }
                            }
                            if ($data->refund_status == 1) {

                                $build_refund_action = '<a href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund payment" class="btn btn-circle btn-outline btn-success show-tooltip">Refund Paid</a>';
                            }

                    
                            return $build_action = $build_refund_action;
                        })

                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {
                            //get unread messages count
                            $unread_message_count = get_quote_unread_messages_count($data->id,'representative');
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }

                            //check if user is online or not
                            $is_online = check_is_user_online($data->representative_id);

                            if($is_online ==true)
                            {
                              $online_status = '<span class="act-online"></span>';
                            }
                            else
                            {
                              $online_status = '<span class="act-offline"></span>';
                            }

                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            // $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>';

                          

                            return $build_action = $build_view_action;
                        })
                         ->editColumn('ship_status',function($data) use ($current_context)
                        {
                            $ship_status = get_order_status($data->ship_status);
                            return $ship_status;

                        })

                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            $payment_status ='';

                            return $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'N/A';

                            
                        });


        $build_result = $json_result->make(true)->getData();
         $build_result->total_amt = $total_amt;
        return response()->json($build_result);
    }

    public function get_export_rep_sales_cancel_orders(Request $request)
    {
      $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }


        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $user_maker_table        =  $this->UserModel->getTable();
        $prefix_user_maker_table = DB::getTablePrefix().$user_maker_table;        

       
        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

        $transaction_mapping        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

        $sales_manager_table         = $this->SalesManagerModel->getTable();
        $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

        $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
        $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $maker_table        = $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;




        $obj_qutoes = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,".

                              "RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.

                              $prefix_retailer_table.'.store_name,'.
                              $prefix_maker_table.'.company_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".

                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".

                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$prefix_representative_leads_tbl.'.retailer_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftJoin($prefix_sales_manager_table,$prefix_sales_manager_table.'.user_id','=',$prefix_user_table.'.id')
       

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })

                           ->orderBy($prefix_representative_leads_tbl.".id",'DESC')
                           ->groupBy($prefix_representative_leads_tbl.'.id')
                          
                           ->where($representative_leads.'.representative_id','!=','0')

                           ->where($representative_leads.'.order_cancel_status','=',2)
                       
                           ->where($representative_leads.'.total_wholesale_price','>','0');                           
                           


    $sales_man_lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".

                            $prefix_transaction_mapping.'.transaction_status,'.
                            $prefix_transaction_mapping.'.order_id,'.
                            $prefix_retailer_table.'.store_name,'.
                            $prefix_maker_table.'.company_name,'.

                            "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                            
                            " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                            "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
 
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                            ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.sales_manager_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_sales_manager_table,$prefix_sales_manager_table.'.user_id','=',$prefix_user_table.'.id')

                          
                           ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })

                           ->orderBy($prefix_representative_leads_tbl.".id",'DESC')

                           ->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.sales_manager_id','!=','0')

                           ->where($representative_leads.'.order_cancel_status','=',2)
       
                           ->where($representative_leads.'.total_wholesale_price','>','0');
         
       

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->all();


        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {  
            $search_term  = $arr_search_column['q_order_no'];
            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.order_no','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
        }

       
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having('store_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes = $obj_qutoes->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%');
            
        } 
     

        if(isset($arr_search_column['q_rep_sales_name']) && $arr_search_column['q_rep_sales_name']!="")
        { 
            $search_term  = $arr_search_column['q_rep_sales_name'];
           

            $obj_qutoes = $obj_qutoes->having('representative_user_name','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');
           
        }
   
        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
      
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
        } 

         if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($representative_leads.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term    = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes     = $obj_qutoes->having($representative_leads.'.total_wholesale_price','LIKE', '%'.$search_term.'%');

            $sales_man_lead_obj = $sales_man_lead_obj->having($representative_leads.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {
          $search_term  = $arr_search_column['q_payment_status'];

          if($search_term == 1) 
          {
            $obj_qutoes = $obj_qutoes->whereNotExists(function($query){
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
            });

            $sales_man_lead_obj = $sales_man_lead_obj->whereNotExists(function($query){
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
            });

          }
          else
          {

              $obj_qutoes     = $obj_qutoes->where($prefix_transaction_mapping.'.transaction_status','LIKE','%'.$search_term.'%');

              $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_transaction_mapping.'.transaction_status','LIKE','%'.$search_term.'%');

          }
        }


        if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];
           
            if ($search_term_refund_status == '0')
            {
              
                $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status)
                                           ->where($prefix_transaction_mapping.'.transaction_status','LIKE', '2');


                $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status', $search_term_refund_status)
                                    ->where($prefix_transaction_mapping.'.transaction_status','LIKE', '2');                            

               
            }
            else{
              
                $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

                $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status', $search_term_refund_status);
               
              
            }
        } 


         if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
           
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '>=', $order_from_date);
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

             $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $order_from_date);


            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status', $search_term_refund_status);


        } 
        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status'] == 0)
        {
           
            $obj_qutoes = $obj_qutoes->where($representative_leads.'.maker_confirmation','1');

            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.maker_confirmation',1);    
        }

        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && $arr_search_column['q_order_to_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '>=', $order_from_date);
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

             $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $order_from_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status',$search_term_refund_status);
        } 

        if(isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && $arr_search_column['q_order_from_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];
          
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($representative_leads.'.created_at', '>=', $order_from_date);
            
            $obj_qutoes   = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $order_to_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $order_from_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status',$search_term_refund_status);
        } 

        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="" && $arr_search_column['q_order_from_date']=="" && $arr_search_column['q_order_to_date']=="")
        {
          $search_term_refund_status  = $arr_search_column['q_refund_status'];
           $obj_qutoes                 = $obj_qutoes->where($representative_leads.'.refund_status', $search_term_refund_status);

          $sales_man_lead_obj   = $sales_man_lead_obj->where($representative_leads.'.refund_status',$search_term_refund_status);
        } 

         $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $sales_man_lead_obj->get()->toArray();

        if(count($arr_orders) <= 0)
        {
            $response['status']      = 'error';
            $response['message']      = 'No data available for export';
        
            return response()->json($response);
        } 

        foreach($arr_orders as $key => $value)
        { 
            $payment_status = 'Pending';
            if($value->transaction_status == 1 || $value->transaction_status==null)
            {
              $payment_status =  'Pending';
            }else if($value->transaction_status == 2)
            {
              $payment_status = 'Paid';
            }else
            {
              $payment_status = 'Failed';
            }


            $refund_status = "-";
            if($value->transaction_status == 2 && $value->refund_status == 0)
            {
              if($value->is_direct_payment == 0)
              {
                 $refund_status = 'Pending';   
              }
              else
              {
                $refund_status = "--";
              }
            }
            if ($value->refund_status == 1) {

                $refund_status = 'Paid';
            }

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            $arrayResponseData['Retailer']              = $value->store_name; 
            $arrayResponseData['Reps/Sales']              = $value->representative_user_name; 
            $arrayResponseData['Vendor']                = $value->company_name;      
            $arrayResponseData['Total Amount ($)']      = $value->total_wholesale_price;
            $arrayResponseData['Retailer Payment Status'] = $payment_status;
            $arrayResponseData['Refund Status']         = $refund_status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Reps Sales Cancel Orders', function($excel) use ($data) {
        
        $excel->sheet('Reps Sales Cancel Orders', function($sheet) use ($data)
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


    public function view(Request $request, $enquiry_id = 0)
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();
        $arr_refund_detail = [];

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $enquiry_id  = base64_decode($enquiry_id);
        $enquiry_arr = $orderCalculationData = [];


       $enquiry_obj = $this->BaseModel->with(['transaction_mapping',
                                               'leads_details.product_details.brand_details',
                                               'retailer_user_details.retailer_details',
                                               'maker_data',
                                               'address_details','maker_details'
                                               ])
                                        ->where('id',$enquiry_id)
                                        ->first();                                   
                
        if($enquiry_obj)
        {
            $enquiry_arr = $enquiry_obj->toArray();

            if(isset($enquiry_arr['order_no']) && !empty($enquiry_arr['order_no']))
            {
                $obj_refund_detail = $this->RefundModel->where('order_id',$enquiry_id)
                                    ->where('order_no',$enquiry_arr['order_no'])
                                    ->first();

                if($obj_refund_detail)
                {
                    $arr_refund_detail = $obj_refund_detail->toArray();
                }
            }
            
            if($loggedInUserId !=1)
            {
                Flash::error('You are not authorize user to access this page.');
                return redirect()->back();
            }
        }
        else
        {
            Flash::error('Something went wrong, please try again.');
            return redirect()->back();
        }

        /*get order calculation data from helper*/
        if(isset($enquiry_arr['order_no']) && !empty($enquiry_arr['order_no']) &&
                isset($enquiry_arr['maker_id']) && !empty($enquiry_arr['maker_id']))
        {
            $ordNo = base64_encode($enquiry_arr['order_no']);
            $vendorId = base64_encode($enquiry_arr['maker_id']);

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
        }
      
        $this->arr_view_data['arr_refund_detail']  = $arr_refund_detail;
        $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


    public function refund_payment(Request $request)
    {

        $order_id = $request['order_id'];
        $getOrderDetails = $this->BaseModel->where('id',$order_id)->first();
       
        if($getOrderDetails)
        {
            /*get stripe account details using stripe_key_id (retailer_transaction table) and refund using this stripe account */ 
            $stripe_secret_key = false;

            if(isset($getOrderDetails['stripe_key_id']) && !empty($getOrderDetails['stripe_key_id']))
            {
                $stripe_key_id = $getOrderDetails['stripe_key_id'];

                $stripe_secret_key = $this->UserStripeAccountDetailsModel->where('id',$stripe_key_id)->pluck('secret_key');
            }

            $transactionDetails = TransactionMappingModel::where('order_id',$order_id)->where('order_no',$getOrderDetails['order_no'])->first();
           
            if($transactionDetails) {
                $refundDetails = $this->StripePaymentService->retrieve_refund($transactionDetails['transaction_id'],num_format($transactionDetails['amount']),$stripe_key_id);

                //dd($refundDetails,num_format($transactionDetails['amount']));
                
                if ($refundDetails['status'] == 'succeeded') 
                {
                   
                    $updateRefundStatus = $this->BaseModel->where('id',$order_id)->update(['refund_status' => '1']);

                    $refundData['order_id'] = $order_id or ' ';
                    $refundData['paid_by']  = get_admin_id();
                    $refundData['received_by'] = $getOrderDetails['retailer_id'] or ' ';
                    $refundData['order_no'] = $getOrderDetails['order_no'] or ' ';
                    $refundData['transaction_id'] = $refundDetails['charge'];
                    // $refundData['amount'] = $refundDetails['amount'];
                    $refundData['amount'] = num_format($transactionDetails['amount']);
                    $refundData['balance_transaction'] = $refundDetails['balance_transaction'];
                    $refundData['status'] = '2';

                    $this->RefundModel->create($refundData);
                    /*Send notification to retailer*/
                    $arr_notify_data                 = [];
                    $arr_notify_data['from_user_id'] = get_admin_id();
                    $arr_notify_data['to_user_id']   = $getOrderDetails['retailer_id'] or '';

                    $arr_notify_data['description']  = 'For your canceled order('.$getOrderDetails['order_no'].') refund is initiated, it will be reflected within 5 to 10 business days. Transaction id: '.$refundDetails['balance_transaction'];
                    $arr_notify_data['title']        = 'Payment Refund';
                    $arr_notify_data['type']         = 'retailer';  
                    $arr_notify_data['link']         = '';  

                    $this->GeneralService->save_notification($arr_notify_data);

                    $response['status'] = 'success';
                    $response['msg']    = 'Refund is initiated it will reflect to retailer account within 5 to 10 business days.';
                    return $response;

                }
                else{

                $response['status'] = $refundDetails['status'];
                $response['msg']    = $refundDetails['description'];
                return $response;
            }   
            }
            else{
                $response['status'] = 'warning';
                $response['msg']    = 'Something went wrong, please try again.';
                return $response;

            }
          
       }
       else{
        $response['status'] = 'warning';
        $response['msg']    = 'Something went wrong, please try again.';
                return $response;

       }

       
    }


}
