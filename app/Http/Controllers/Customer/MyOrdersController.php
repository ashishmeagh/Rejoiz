<?php

namespace App\Http\Controllers\Customer;
//dd("okkk");
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\CustomerQuotesModel;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\TransactionsModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\TransactionMappingModel;
use App\Models\ProductInventoryModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\PromoCodeRetailerMappingModel;

use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\CustomerOrderService;
use App\Common\Services\orderDataService;
use App\Common\Services\ProductService;
use App\Common\Services\HelperService;



use Sentinel, Session;
use DB;
use Validator;
use Datatables;
use Flash;
use DateTime;

class MyOrdersController extends Controller
{
    /* 
    |  Show Retailer orders with status   
    |  Author : Shital Vijay More
    |  Date   : 29 Aug 2019
    */
    public function __construct(CustomerQuotesModel $customer_quote,UserModel $user_model,
                                ProductsModel $product_model,CustomerQuotesProductModel $customer_quotes,
                                MakerModel $MakerModel,
                                TransactionsModel $TransactionsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                EmailService $EmailService,
                                GeneralService $GeneralService,
                                CustomerOrderService $CustomerOrderService,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                PromoCodeRetailerMappingModel $PromoCodeRetailerMappingModel,
                                ProductInventoryModel $ProductInventoryModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,

                               orderDataService $orderDataService,
                               ProductService $ProductService,
                               HelperService $HelperService 
 
                               )
    {
        $this->arr_view_data              = [];
        $this->module_title               = "My Orders";
        $this->module_view_folder         = 'customer.my_orders'; 
        $this->customer_panel_slug        = config('app.project.customer_panel_slug');
        $this->module_url_path            = url($this->customer_panel_slug.'/my_orders');
        $this->CustomerQuotesModel        = $customer_quote;
        $this->UserModel                  = $user_model;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->ProductsModel              = $product_model;
        $this->ProductInventoryModel      = $ProductInventoryModel;
        $this->MakerModel                 = $MakerModel;
        $this->TransactionsModel          = $TransactionsModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->EmailService               = $EmailService;
        $this->GeneralService             = $GeneralService;
        $this->CustomerOrderService             = $CustomerOrderService;
        $this->orderDataService                =$orderDataService;
        $this->CustomerQuotesProductModel = $customer_quotes;
        $this->RoleModel                  = $RoleModel;
        $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->PromoCodeRetailerMappingModel = $PromoCodeRetailerMappingModel;
        $this->HelperService              = $HelperService;
        
        $this->ProductService             = $ProductService;
    }

    public function index()
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'My Orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function my_pending_orders()
    {
        $this->arr_view_data['module_title']    = 'My Pending Orders';
        $this->arr_view_data['page_title']      = 'My Pending Orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['pending_flag']    = 1;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function my_completed_orders()
    {
        $this->arr_view_data['module_title']    = 'My Completed Orders';
        $this->arr_view_data['page_title']      = 'My Completed Orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['completed_flag']  = 1;

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

        $pending_flag = $request->input('pending_flag');
        $pending_flag = isset($pending_flag)?$pending_flag:'';

        $completed_flag = $request->input('completed_flag');
        $completed_flag = isset($completed_flag)?$completed_flag:'';
        
        
        $customer_quotes_tbl_name     = $this->CustomerQuotesModel->getTable();        
        $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->CustomerQuotesModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

        $user_tbl_name                = $this->UserModel->getTable();
        $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl                    = $this->MakerModel->getTable();        
        $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();

        $transaction_tbl              = $this->TransactionsModel->getTable();        
        $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable();

        $customer_product_tbl = $this->CustomerQuotesProductModel->getTable();

        $prefixed_customer_product_tbl = DB::getTablePrefix().$this->CustomerQuotesProductModel->getTable();

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                         $prefixed_customer_product_tbl.".shipping_charge,".
                                         $prefixed_customer_product_tbl.".shipping_discount,".
                                      
                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_customer_product_tbl, $prefixed_customer_product_tbl.'.customer_quotes_id','=',$prefixed_customer_quotes_tbl.'.id')
                        
                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                            ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                        })

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_customer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_tbl,$transaction_tbl.'.transaction_id','=',$prefixed_customer_quotes_tbl.'.transaction_id')

                        ->where($prefixed_customer_quotes_tbl.'.customer_id',$loggedInUserId)
                        ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','!=',2)
                   
                     
                        ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC')
                        ->groupBy($prefixed_customer_quotes_tbl.".id");

                        // dd($obj_qutoes->get());


          if(isset($pending_flag) && $pending_flag==1)
                        {                      

                           $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.order_cancel_status','=',0)
                           ->whereNotExists(function($query) use($customer_quotes_tbl_name){

                            $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = customer_transaction.order_no AND `transaction_mapping`.`order_id` = customer_transaction.id AND
                                  customer_transaction.ship_status = 1   

                           
                            "));
                           

                        })
                        ->orwhereExists(function($query) use($customer_quotes_tbl_name){

                            $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = customer_transaction.order_no AND `transaction_mapping`.`order_id` = customer_transaction.id AND
                                 customer_transaction.ship_status = 0
                            "));
                       

                        })
                            
                        ->whereNotExists(function($query){

                            $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = customer_transaction.order_no AND `transaction_mapping`.`order_id` = customer_transaction.id AND

                                customer_transaction.ship_status=0
                            "));

                        });
                        

                        }

        if(isset($completed_flag) && $completed_flag==1)
        {
            $obj_qutoes = $obj_qutoes->where('maker_confirmation','=',1)
                                     ->where('ship_status','=',1)
                                     ->where('order_cancel_status','=',0)
                                     ->where($prefixed_transaction_mapping_tbl.'.transaction_status','=',2);              
        }

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
              
         if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }
      

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term = $arr_search_column['q_description'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
        {
            $search_term = $arr_search_column['q_customer_name'];
            $obj_qutoes  = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];

            if(isset($search_term) && $search_term == 1)
            {

            }
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  

            $search_term = $arr_search_column['q_payment_status'];
            
            if($search_term == 1)
            {            

                $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = customer_transaction.order_no AND `transaction_mapping`.`order_id` = customer_transaction.id
                        "));


                        // $query->from($prefixed_transaction_mapping_tbl)
                        //       ->whereRaw($prefixed_customer_quotes_tbl.'.order_no',$prefixed_transaction_mapping_tbl.'.order_no')
                        
                        //       ->whereRaw($prefixed_customer_quotes_tbl.'.id',$prefixed_transaction_mapping_tbl.'.order_id');        
                    });
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }



        }


        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');

            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }    

        if(isset($arr_search_column['customer_id']) && $arr_search_column['customer_id']!="" && $arr_search_column['customer_id']!='0')
        {
            $customer_id = base64_decode($arr_search_column['customer_id']);

            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.customer_id',$customer_id);
        } 
         
        /*search data from  from date and to date*/
        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") && (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];

            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

            
         /*   $search_term_from_date  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));
            $search_term_to_date    = date('Y-m-d', strtotime($arr_search_column['q_to_date']));*/

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_customer_quotes_tbl.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_customer_quotes_tbl.'.created_at', '>=', $from_date);

        }     

        $current_context = $this;
        //Calculate total by Harshada on date 09 Sep 2020
      
        $total_amt = 0;      
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_retail_price ;
        }, 0);
       
        $json_result  = Datatables::of($obj_qutoes);


        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $us_date_format = us_date_format($data->created_at);
                        })
                      
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            //dump($data->transaction_status);
                           return  $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'N/A'; 
                            

                        })

                        ->editColumn('company_name',function($data) use ($current_context)
                        {   
                           return  $company_name = isset($data->company_name) && $data->company_name!='' ?$data->company_name:'N/A';
                        })

                        ->editColumn('ship_status',function($data) use ($current_context)
                        {   
                            return $ship_status = get_order_status($data->ship_status); 

                        })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                            $products_arr = get_customer_quote_products($id);

                            return $product_html = $this->CustomerOrderService->order_products_for_list($id,$order_no,$products_arr);

                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {
                            //get unread messages count
                            $unread_message_count = get_quote_unread_messages_count($data->id,'customer');
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }   

                            //check if user is online or not
                            $is_online = check_is_user_online($data->maker_id);

                            if($is_online ==true)
                            {
                              $online_status = '<span class="act-online"></span>';
                            }
                            else
                            {
                              $online_status = '<span class="act-offline"></span>';
                            }

                            $view_href   = $this->module_url_path.'/view/'.base64_encode($data->id);

                            $cancel_href = $this->module_url_path.'/cancel/'.base64_encode($data->id);
                           
                            $reorder_url =  url('/').'/customer_my_bag/generate_reorder_data/'.base64_encode($data->order_no).'/'.base64_encode($data->maker_id);

                            $build_reorder_action = '';
                            if($data->is_split_order!=1)
                            {
                                $build_reorder_action = '<a data-toggle="tooltip"  data-size="small" title="Reorder Details" class="btn btn-outline btn-success btn-circle show-tooltip reorderstyle" onclick="return confirm_reorder($(this))" href="javascript:void(0);" data-link="'.$reorder_url.'" data-order_no="'.$data->order_no.'" data-maker_id="'.$data->maker_id.'">Reorder</a>';
                            }

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-outline btn-success btn-circle show-tooltip  viewstyle" data-link="'.$view_href.'" href="'.$view_href.'">View</a> ';


                            
                            $build_cancel_action='';
                            if($data->is_split_order!=1)
                            {
                                if($data->ship_status != 1 && $data->order_cancel_status == 0)
                                { 
                                   $build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Order" class="btn btn-outline btn-info btn-circle show-tooltip  btn-customer-view cancelstyle" href="javascript:void(0);" onclick="cancelOrder($(this));" data-order-id="'.$data->id.'">Cancel</a>';
                                }
                                elseif($data->order_cancel_status == 1)
                                {
                                  /* $build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-outline btn-info btn-circle show-tooltip  btn-customer-view cancelrequestedstyle" href="javascript:void(0);" data-order-id="'.$data->id.'">Cancel Requested</a>';*/
                                }
                            }

                            if($data->ship_status !=1)
                            {

                               return $build_action = $build_reorder_action." ".$build_view_action.' '.$build_cancel_action;
                            } 
                            else
                            {
                                return $build_action = $build_reorder_action." ".$build_view_action; 
                            }                                     
                            
                        });

        $build_result = $json_result->make(true)->getData();
        $build_result->total_amt = $total_amt;
        return response()->json($build_result);

        
                                
    }

    public function view(Request $request, $enquiry_id = 0)
    {
      
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $enquiry_id  = base64_decode($enquiry_id);
        $enquiry_arr = $split_order_arr = $main_split_order_no = $tracking_details =  [];
        
        $enquiry_obj = $this->CustomerQuotesModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data'])->where('id',$enquiry_id)->first(); 
       
        if (isset($enquiry_obj)) {
            
            $order_no = $enquiry_obj['order_no'];
            $enquiry_obj = $this->CustomerQuotesModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping'=>function($qry) use ($order_no){
                    $qry->where('order_no',$order_no);
                }])
                ->where('id',$enquiry_id)->first(); 
        }

                        
        if($enquiry_obj)
        {
            $enquiry_arr = $enquiry_obj->toArray();  

            if ($enquiry_arr['split_order_id'] != '') {

                $main_split_order_no = $this->CustomerQuotesModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping'])
                                                 ->where('id',$enquiry_arr['split_order_id'])->first();

            }
            elseif ($enquiry_arr['is_split_order'] == '1') {

                $split_order_arr = $this->CustomerQuotesModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping'])
                                                 ->where('split_order_id',$enquiry_arr['id'])->get()->toArray(); 
            }         
        } 
       
        /*check count whether in a payment done or fail*/

        $count = $this->TransactionMappingModel->where('order_id',$enquiry_id)->count();

        $enquiry_arr_id       = isset($enquiry_arr['id'])?$enquiry_arr['id']:0;
        $enquiry_arr_order_no = isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:'';

        $tracking_details = [];
        $tracking_no = 0;

        if($enquiry_arr_id!=0 && $enquiry_arr_order_no!='')
        {
          $tracking_details = $this->HelperService->getTrackingDetails($enquiry_arr_id,$enquiry_arr_order_no);
          $tracking_no = isset($tracking_details['tracking_no'])?$tracking_details['tracking_no']:0;
        }  

        $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['split_order_arr']     = $split_order_arr;
        $this->arr_view_data['main_split_order_no']     = $main_split_order_no;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['count']           = $count;
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['tracking_details']= $tracking_details;
        $this->arr_view_data['tracking_no']     = $tracking_no;

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


    public function order_cancel(Request $request)
    {


        $order_id = $request->input('order_id');


        /* get current time */
        $order_detail_arr = [];
        $current_time     = $current_date = $placed_time = $placed_date = '';

        $order_id  = base64_decode($order_id); 
   
        $datetime = date('d/m/Y H:i:s');

        $user    = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
       
      
        $obj_order_details = $this->CustomerQuotesModel->with([ 'quotes_details.product_details',
                                                                'user_details'=>function($q){
                                                                    $q->select('id','first_name','last_name','email');

                                                                },'maker_details'=>function($q1){
                                                                    $q1->select('id','first_name','last_name','email');
                                                                }])
                                                       ->where('id',$order_id)
                                                       ->first();


        if(isset($obj_order_details))
        {
           $order_detail_arr = $obj_order_details->toArray();
        }

        //$service_response = $this->CustomerOrderService->send_email_to_maker($order_detail_arr); 

        $now = new DateTime();
    
        $replydue = new DateTime($order_detail_arr['created_at']);

        $timetoreply = date_diff($now, $replydue);

        $timetoreply_hours = $timetoreply->days * 24 + $timetoreply->h;
 

        if($timetoreply_hours > 24 || $order_detail_arr['maker_confirmation']==1)
        {

            //If order cancel after 24 hours from order generate.
            
            $updated_arr['order_cancel_status'] = 1;
            $result = $this->CustomerQuotesModel->where('id',$order_id)->update($updated_arr);

            if($result)
            {
                /*send mail to maker*/
                $service_response = $this->CustomerOrderService->send_request_email_to_maker($order_detail_arr);
              
                /*send notification to maker*/

                $view_href    = url('/').'/vendor/customer_cancel_orders_request/view/'.base64_encode($order_id);

                $first_name   = isset($user->first_name)?$user->first_name:"";
                $last_name    = isset($user->last_name)?$user->last_name:""; 
                
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = $order_detail_arr['maker_id'];


                 $notification_arr['description']  = 'Customer '.$first_name.' '.$last_name.' requested for cancel the order '.$order_detail_arr['order_no'].' please confirm the request.';

                $notification_arr['title']        = 'Cancel Order Request';
                $notification_arr['type']         = 'maker';
                $notification_arr['link']         = $view_href;

                $this->CustomerOrderService->save_notification($notification_arr);


                //send cancel order  request notficaition to the admin
             
                $first_name   = isset($user->first_name)?$user->first_name:"";
                $last_name    = isset($user->last_name)?$user->last_name:""; 
                
                $company_name = get_maker_company_name($order_detail_arr['maker_id']);
                
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = 1;


                $notification_arr['description']  = 'Customer '.$first_name.' '.$last_name.' requested for cancel the order '.$order_detail_arr['order_no'].' to the vendor '.$company_name;
                
                $notification_arr['title']        = 'Cancel Order Request';
                $notification_arr['type']         = 'admin';
                $notification_arr['link']         = '';

                $this->GeneralService->save_notification($notification_arr);




                $response['status']       = 'success';
                $response['description']  = "Order cancel request has been sent to vendor.";

                return response()->json($response);

            }
            else
            {
                $response['status']       = 'error';
                $response['description']  = "Error occurred while sending cancel request to vendor.";

                return response()->json($response);
            }
            
        }
        else
        {
            // If order place before 24 Hours
            $updated_arr['order_cancel_status'] = 2;
            $result = $this->CustomerQuotesModel->where('id',$order_id)->update($updated_arr);

            
            if($result)
            {


                if(isset($order_detail_arr['maker_confirmation']) && $order_detail_arr['maker_confirmation'] == 1)
                {
                    /*Update quantity*/

                    foreach ($order_detail_arr['quotes_details'] as $key => $value)
                    {
        
                        $update_qunty = $this->ProductInventoryModel->where('sku_no',$value['sku_no'])->increment('quantity',$value['qty']);

                    }

                }


                
                /*send mail to maker*/
               
                 $service_response = $this->CustomerOrderService->cancel_order_mail($order_detail_arr); 

                /*send notification to maker*/

                $view_href    = url('/').'/vendor/customer_cancel_orders/view/'.base64_encode($order_id);

                $first_name   = isset($user->first_name)?$user->first_name:"";
                $last_name    = isset($user->last_name)?$user->last_name:""; 
               
                
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = $order_detail_arr['maker_id'];

                $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by customer '.$first_name.' '.$last_name;

                $notification_arr['title']        = 'Order Cancelled';
                $notification_arr['type']         = 'maker';
                $notification_arr['link']         = $view_href;

                $this->CustomerOrderService->save_notification($notification_arr,'customer');



                /*send notification to admin*/

                $view_href   = url('/').'/admin/customer_cancel_orders/view/'.base64_encode($order_id);

                $first_name   = isset($user->first_name)?$user->first_name:"";
                $last_name    = isset($user->last_name)?$user->last_name:""; 
               
                
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = 1;

                
                 $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by customer '.$first_name.' '.$last_name;


                $notification_arr['title']        = 'Order Cancelled';
                $notification_arr['type']         = 'admin';
                $notification_arr['link']         = $view_href;

                $this->CustomerOrderService->save_notification($notification_arr);


                /*send notification to customer for cancel order*/

                $view_href   = url('/').'/customer/my_cancel_orders/view/'.base64_encode($order_id);

                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = $loggedInUserId;
     
                $notification_arr['description']  = 'Your order '.$order_detail_arr['order_no'].' has been cancelled';

                $notification_arr['title']        = 'Order Cancelled';
                $notification_arr['type']         = 'customer';
                $notification_arr['link']         = $view_href;

                $this->GeneralService->save_notification($notification_arr);


                $response['status']      = 'success';
                $response['description'] = "Order has been cancelled.";

                return response()->json($response);

            }
            else
            {
                $response['status']       = 'error';
                $response['description']  = "Error occurred while canceling the order.";

                return response()->json($response);
            }

        }
    }

    
    public function get_order_list($arr_search_column=false,$module_data=false,$retailer_id=false)
    {
      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $user_maker_table =  $this->UserModel->getTable();
      $prefix_user_maker_table = DB::getTablePrefix().$user_maker_table;    

      $maker_table = $this->MakerModel->getTable();
      $prefix_maker_table =  DB::getTablePrefix().$maker_table;     

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $representative_leads =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $transaction_mapping = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $representative_product_leads =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

      $lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_maker_table.'.company_name,'.

                              $prefix_transaction_mapping.'.order_id,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".


                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                                   )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')


                            /*->leftJoin($prefix_transaction_mapping,$prefix_transaction_mapping.'.order_id','=',$prefix_representative_leads_tbl.'.id')*/

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })


                           ->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)
                           ->where($representative_leads.'.is_confirm','!=',0)

                           ->orderBy($prefix_representative_leads_tbl.'.id','DESC');

                           if(isset($retailer_id) && $retailer_id!='')
                           {
                              
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.retailer_id','=',$retailer_id);
                           }





        /* ---------------- Filtering Logic ----------------------------------*/  

      if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {
          $search_term      = $arr_search_column['q_order_no'];
          $lead_obj = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
      } 
      if(isset($arr_search_column['q_lead_date']) && $arr_search_column['q_lead_date']!="")
      {
          $date     = DateTime::createFromFormat('m-d-Y',$arr_search_column['q_lead_date']);
          $date     = $date->format('Y-m-d');
          $lead_obj = $lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
      } 

      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term      = $arr_search_column['q_customer_name'];
          $lead_obj =  $lead_obj->having('retailer_user_name','LIKE', '%'.$search_term.'%');
      }

      
      if(isset($arr_search_column['q_representative_name']) && $arr_search_column['q_representative_name']!="")
      {
          $search_term      = $arr_search_column['q_representative_name'];
          $lead_obj =  $lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');
      }


      if(isset($arr_search_column['q_maker_name']) && $arr_search_column['q_maker_name']!="")
      {

          $search_term      = $arr_search_column['q_maker_name'];
          $lead_obj =  $lead_obj->having('maker_user_name','LIKE', '%'.$search_term.'%');
      }

        if(isset($arr_search_column['q_maker_company']) && $arr_search_column['q_maker_company']!="")
      {

          $search_term      = $arr_search_column['q_maker_company'];
          $lead_obj =  $lead_obj->having('company_name','LIKE', '%'.$search_term.'%');
      }


      if(isset($arr_search_column['q_lead_status']) && $arr_search_column['q_lead_status']!="")
      {
          $search_term      = $arr_search_column['q_lead_status'];
          $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

      }

      if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
      {  
          $search_term  = $arr_search_column['q_ship_status'];
          $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term);
      }

      if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
      {
        $search_term  = $arr_search_column['q_payment_status'];

        if($search_term == 1)
        {
             

            $lead_obj = $lead_obj->whereNotExists(function($query) use ($prefix_transaction_mapping,$prefix_representative_leads_tbl)
                    {

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
           $lead_obj  = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','=', $search_term);
        }
          
          
      }



      if(isset($arr_search_column['q_total_costing_retail']) && $arr_search_column['q_total_costing_retail']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_retail'];
          $lead_obj =  $lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_wholesale'];
          $lead_obj =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
      }

      return $lead_obj;
    }


   
    public function get_listing(Request $request)
    { 

        $search_data = $request->input('column_filter');

        $module_data['module_url'] = $this->module_url_path;
        $module_data['is_confirm'] = '0';

        $user = Sentinel::check();
        $retailer_id = 0;

        if($user)
        {
            $retailer_id = $user->id;
        }    


        // $lead_obj = $this->orderDataService->get_order_list($search_data,$module_data,$retailer_id);
         $lead_obj = $this->get_order_list($search_data,$module_data,$retailer_id);


        
        $current_context = $this;
      
        $json_result     = \Datatables::of($lead_obj);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                          {
                              return base64_encode($data->id);
                          })
                         ->editColumn('total_retail_price', function($data){

                          return isset($data->total_retail_price)?num_format($data->total_retail_price):'';
                          
                         })
                         ->editColumn('total_wholesale_price', function($data){

                          return isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'';

                         })
                          ->editColumn('product_html',function($data) use ($current_context)
                          {   
                              $products_arr = [];
                              $products_arr = get_lead_products($data->id,$data->order_no);

                              if(isset($products_arr) && count($products_arr)>0)
                              {
                                  $products = '';

                                  foreach ($products_arr as $key => $product) 
                                  {
                                    
                                      $products .= '<tr>
                                                      <td>'.$product['product_details']['product_name'].'</td>
                                                      <td>'.$product['qty'].'</td>
                                                    </tr>';
                                    
                                  }
                              }
                              else
                              {
                                  $products = '<tr>
                                                      <td colspan=2>No Record Found</td>
                                                    </tr>';
                              }

                              return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_product_list($(this))">View Products<span> '.count($products_arr).'</span></a>
              
                                  <td colspan="5">
                                      <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                                          <thead>
                                            <tr>
                                              <th>Product Title</th>
                                              <th>Quantity</th>                                
                                            </tr>
                                          </thead>
                                          <tbody>'.$products.'</tbody>
                                        </table>
                                  </td>';

                            })

                            ->editColumn('payment_status', function($data){

                                return isset($data->transaction_status)?$data->transaction_status:'N/A';

                            })
                            ->editColumn('ship_status',function($data) use ($current_context)
                            {   
                                return $ship_status = get_order_status($data->ship_status); 

                            })
                          ->editColumn('build_action_btn',function($data)
                          {
                              //get unread messages count
                              $unread_message_count = get_lead_unread_messages_count($data->id,'representative');
                              
                              if($unread_message_count>0)
                              {
                                  $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                              }
                              else
                              {
                                  $unread_msg_count = '';
                              }

                              //check if user is online or not
                              $is_online = check_is_user_online($data->maker_id);

                              if($is_online ==true)
                              {
                                $online_status = '<span class="act-online"></span>';
                              }
                              else
                              {
                                $online_status = '<span class="act-offline"></span>';
                              }

                              $build_edit_action = $build_view_action = $build_chat_action = '';


                             $view_href   =  $this->module_url_path.'/order_summary/'.base64_encode($data->order_no).'/'.base64_encode($data->maker_id);

                           
                            $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);



                              $reorder_url =  url('/').'/my_bag/generate_reorder_data/'.base64_encode($data->order_no).'/'.base64_encode($data->maker_id).'/lead';

                              $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-outline btn-success btn-circle show-tooltip viewstyle" href="'.$view_href.'">View</a><a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-outline btn-success btn-circle show-tooltip reorder" onclick="return confirm_reorder($(this))"  data-link="'.$reorder_url.'" href="javascript:void(0);" data-order_no="'.$data->order_no.'" data-maker_id="'.$data->maker_id.'">Reorder</a>';

                              $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.' </a>';


                            $build_cancel_action='';
                                
                                if($data->ship_status != 1 && $data->order_cancel_status == 0)
                                { 
                                   $build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Order" class="btn btn-outline btn-info btn-circle show-tooltip  btn-retailer-view cancelstyle" href="javascript:void(0);" onclick="cancelOrder($(this));" data-order-id="'.$data->id.'">Cancel</a>';
                                }
                                elseif($data->order_cancel_status == 1)
                                {
                                   /*$build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-outline btn-info btn-circle show-tooltip  btn-retailer-view cancelrequestedstyle" href="javascript:void(0);" data-order-id="'.$data->id.'">Cancel Requested</a>';*/
                                }

                                if($data->ship_status !=1)
                                {
                                   
                                   return $build_action = $build_edit_action.' '.$build_view_action.' '.$build_cancel_action;
                                } 
                                else
                                {
                                    return $build_action = $build_edit_action.' '.$build_view_action; 
                                }

                              
                          })
                          ->editColumn('created_at',function($data)
                            {
                              //return  format_date($data->created_at);
                              return  us_date_format($data->created_at);
                            })->make(true);

      $build_result = $json_result->getData();

      return response()->json($build_result);  

    }
  



    
    public function delete_product_from_bucket($enc_order_no,$sku_no)
    {
        $response = $this->orderDataService->delete_product_from_bucket($enc_order_no,$sku_no);
        // forget promotion session
        Session::forget('promotion_data');
        

        if($response['status'] == 'FAILURE')
        {

          Flash::error($response['description']);
              
        }else
        {
          Flash::success($response['description']);
        }
        
        return redirect()->back();
    }

    public function finalize_lead(Request $request,$order_id = 0)
    { 
        $promotion_session_data = [];
        $order_id = base64_decode($order_id);
        $promotion_discount_amt = $promotion_shipping_charges = $promo_code = $promo_codeId = 0;


        $order_data = $this->RepresentativeLeadsModel->where('id',$order_id)->first();

        if (is_array(Session::get('promotion_data')) && Session::get('promotion_data') != '') {

            $promotion_session_data = Session::get('promotion_data');
    

            $promotion_discount_amt = isset($promotion_session_data[$order_data['maker_id']]['final_total'][$order_data['maker_id']]['discount_amt'])?$promotion_session_data[$order_data['maker_id']]['final_total'][$order_data['maker_id']]['discount_amt']:0;

            $promotion_shipping_charges = isset($promotion_session_data[$order_data['maker_id']]['final_total'][$order_data['maker_id']]['shipping_charges'])?$promotion_session_data[$order_data['maker_id']]['final_total'][$order_data['maker_id']]['shipping_charges']:1;

            $promo_code = isset($promotion_session_data[$order_data['maker_id']]['promo_code'])?$promotion_session_data[$order_data['maker_id']]['promo_code']:'';

            $promo_codeId = isset($promotion_session_data[$order_data['maker_id']]['promo_codeId'])?$promotion_session_data[$order_data['maker_id']]['promo_codeId']:'';
            
        }
           
        $arr_data    = $promo_mappping = [];

        if(isset($order_id))
        {
            $type = $request->input('type');
            
            $update_lead_arr = [];
            if($type=='confirm_requested')
            {
               $update_lead_arr['is_confirm'] = 2;
            }
            elseif($type=='quote')
            {
               $update_lead_arr['is_confirm'] = 0;
            }elseif($type=='reject'){
              $update_lead_arr['is_confirm'] = 3;
            }else{
               $update_lead_arr['is_confirm'] = 1;
            }


            $is_update = $this->RepresentativeLeadsModel->where('id',$order_id)
                                                        ->update($update_lead_arr);

            $user      = Sentinel::check();

            if($user)
            {
                $user_id = $user->id;
            }                                            

            
            if($is_update)
            {
                $arr_event                  = [];                 
                $arr_event['ACTION']        = 'EDIT';
                $arr_event['MODULE_ID']     = $order_id;
                $arr_event['USER_ID']       = $user_id;
                $arr_event['MODULE_TITLE']  = $this->module_title;               

                $this->save_activity($arr_event);

                if($type=='confirm')
                {

                    /*if promotion applicable then update the price of order*/
                    if (is_array(Session::get('promotion_data')) && Session::get('promotion_data') != '') {
                       
                    
                     

                        $promo_discount_update = [];

                        $promo_discount_update['total_wholesale_price'] = $order_data['total_wholesale_price'] - $promotion_discount_amt - $order_data['total_product_shipping_charges'] + $order_data['total_shipping_discount'];

                        $promo_discount_update['promotion_discount']    = $promotion_discount_amt;

                        $promo_discount_update['promo_code']            = $promo_code;

                        if($promotion_shipping_charges == 0){

                            $promo_discount_update['total_shipping_charges']         = 0;
                            $promo_discount_update['total_shipping_discount']        = 0;
                            $promo_discount_update['total_product_shipping_charges'] = 0;

                        }
                        $this->RepresentativeLeadsModel->where('id',$order_id)->update($promo_discount_update);

                        if($promotion_shipping_charges == 0){

                            $get_products = $this->RepresentativeProductLeadsModel->where('representative_leads_id',$order_id)->get()->toArray();

                            if (isset($get_products) && count($get_products) > 0) 
                            {
                                foreach ($get_products as $key => $product) {

                                    $promo_product['shipping_charges'] = 0;
                                    $promo_product['shipping_charges_discount'] = 0;
                                    $promo_product['product_shipping_charge'] = 0;

                                    $this->RepresentativeProductLeadsModel->where('id',$product['id'])->update($promo_product);
                                    
                                }
                            }  
                        }

                        $promo_mappping['retailer_id']      = $order_data['retailer_id'];
                        $promo_mappping['promo_code_id']    = $promo_codeId;

                        $retailer_promo_mapping = $this->PromoCodeRetailerMappingModel->create($promo_mappping);                                  

                    }
                    Session::forget('promotion_data');

                    // send mail notification to the vendors, admin and representative or sales manager
                    $send_notification = $this->CustomerOrderService->retailer_send_notification($order_id,$type);

                }
                elseif($type=='reject'){

                    $send_notification = $this->CustomerOrderService->retailer_send_notification($order_id,$type);
                }


                Flash::success('Order status has been updated.');
            }
            else
            {
              Flash::error('Something went wrong, please try again.');
            }
          
        }
        else
        {
          Flash::error('Something went wrong, please try again.');
        }   

        return redirect($this->module_url_path.'/order_from_representative');
    }

 

    public function update_product_qty(Request $request)
    {
        $update_product = $this->orderDataService->update_product_qty($request);

        /*after changing the quantity update total wholsale amount*/

        $order_num = $update_product['arr_responce']['order_no'];
      
        $order_product_details_arr = $this->RepresentativeProductLeadsModel->where('order_no',$order_num)
                                                                             ->get()
                                                                             ->toArray();
         
        if(isset($order_product_details_arr) && count($order_product_details_arr)>0)
        {
            foreach ($order_product_details_arr as $key => $value) 
            {
              $result[$value['maker_id']][] = $value;
            }


            foreach($result as $key => $res) 
            {
                $total_product_discount = array_sum((array_column($res,'product_discount')));
                $total_shipping_charges = array_sum((array_column($res,'shipping_charges')));
                $total_shipping_charges_discount = array_sum((array_column($res,'shipping_charges_discount')));

                $total_product_shipping_charges = array_sum((array_column($res,'product_shipping_charge')));
                 
                $total_wholesale_price = array_sum((array_column($res,'wholesale_price')));

                
                $data['total_product_discount']  = $total_product_discount;
                $data['total_product_shipping_charges']  = $total_product_shipping_charges;
                $data['total_shipping_charges']  = $total_shipping_charges;
                $data['total_shipping_discount'] = $total_shipping_charges_discount;

                $data['total_wholesale_price'] =  $total_wholesale_price+$total_product_shipping_charges-$total_product_discount-$total_shipping_charges_discount;

                $this->RepresentativeLeadsModel->where('order_no',$order_num)->where('maker_id',$key)->update($data);

            }
            // forget promotion session
            Session::forget('promotion_data');
            
        }

        /*--------------------------------------------------------------*/

        
        return response()->json($update_product);
    }



    public function save_address(Request $request)
    {

        $form_data = $request->all();
        // dd($form_data);

        $arr_rules = [
                    // 'bill_first_name'    => 'required',
                    'bill_mobile_no'     => 'required',
                    'bill_complete_addr' => 'required',
                    'bill_state'         => 'required',
                    // 'bill_last_name'     => 'required',
                    'bill_email'         => 'required',
                    'bill_city'          => 'required',
                    'bill_zip'           =>'required',
                    // 'ship_first_name'    => 'required',
                    'ship_mobile_no'     => 'required',
                    'ship_complete_addr' => 'required',
                    'ship_state'         => 'required',
                    // 'ship_last_name'     => 'required',
                    'ship_email'         => 'required',
                    'ship_city'          => 'required',
                    'ship_zip_code'      =>'required'                            
                ];

        $validator = Validator::make($request->all(),$arr_rules); 

        // dd($validator);

        if($validator->fails())
        {
           $response['status']      = 'warning';
           $response['description'] = 'Something went wrong, please check all fields.';
           return response()->json($response);
        }

        $save_address = $this->orderDataService->store_order_address($request);

        // dd($save_address);

        // return $save_address;
        $response['status']      = "success";
        $response['description'] = "Order has been confirmed.";
        $response['url'] = $this->module_url_path;
        return response()->json($response); 
    } 

 


    public function chk_products_availability(Request $request)
    {
       
        $form_data = $request->all();

        $arr_rules = [
                    'order_no'     => 'required',
                    'maker_id'     => 'required'
                        
                ];

        $validator = Validator::make($request->all(),$arr_rules); 


        if($validator->fails())
        {
           $response['status']      = 'warning';
           $response['description'] = 'Something went wrong, please check all fields';
           return response()->json($response);
        }


        $cnt_non_available_products = $this->ProductService->check_products_availability($request);

        $request['order_from']="customer";
        
       

        if(is_numeric($cnt_non_available_products)==false)
        {
           $response['title']       = "Apologies";
           $response['status']      = 'warning';
           $response['description'] = "None of the product(s) are available at the moment in this order.";
           return response()->json($response); 
        }

        else if($cnt_non_available_products == 0)
        {

           $response['title']       = "Success";
           $response['status']      = 'success';
           $response['description'] = "Order has been created.";
           return response()->json($response); 
        }
        else
        {  
            $response['title']       = "Need Confirmation";
            $response['status']      = 'warning';
            $response['description'] = "The order you are trying to place, has ". $cnt_non_available_products. " product(s) unavailable at the moment, would you still like to proceed ?";
           return response()->json($response); 
 
        }

    } 


    public function get_email($id)
    {
      $email = $this->UserModel->where('id',$id)->pluck('email')->first();

      return $email;
    }

}

