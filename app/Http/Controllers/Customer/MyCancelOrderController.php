<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\CustomerQuotesModel;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\MakerModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Models\RefundModel;

use App\Common\Services\EmailService;
use App\Common\Services\CustomerOrderService;
use App\Common\Services\HelperService;


use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;

class MyCancelOrderController extends Controller
{
    
    public function __construct(CustomerQuotesModel $customer_quote,UserModel $user_model,
                                ProductsModel $product_model,CustomerQuotesProductModel $customer_quotes,
                                MakerModel $MakerModel,
                                TransactionsModel $TransactionsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                RefundModel $RefundModel,
                                EmailService $EmailService,
                                HelperService $HelperService,
                                CustomerOrderService $CustomerOrderService
                               )
    {
    	$this->arr_view_data              = [];
    	$this->module_title               = "My Cancelled Orders";
    	$this->module_view_folder         = 'customer.my_cancel_orders'; 
    	$this->customer_panel_slug        = config('app.project.customer_panel_slug');
    	$this->module_url_path            = url($this->customer_panel_slug.'/my_cancel_orders');
        $this->CustomerQuotesModel        = $customer_quote;
        $this->UserModel                  = $user_model;
        $this->ProductsModel              = $product_model;
        $this->MakerModel                 = $MakerModel;
        $this->TransactionsModel          = $TransactionsModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->RefundModel                = $RefundModel;
        $this->EmailService               = $EmailService;
        $this->CustomerOrderService       = $CustomerOrderService;
        $this->CustomerQuotesProductModel = $customer_quotes;
        $this->HelperService              = $HelperService;
    }


    public function index()
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'My Cancelled Orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

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

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                      
                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.maker_id')

                        // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                            ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                        })

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_customer_quotes_tbl.'.maker_id')
 
                  
                        ->where($prefixed_customer_quotes_tbl.'.customer_id',$loggedInUserId)
                        ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','=',2)
                        ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC');
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
                //$obj_qutoes = $obj_qutoes->whereNotIn($prefixed_customer_quotes_tbl.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
                //$obj_qutoes = $obj_qutoes->whereNull($prefixed_transaction_mapping_tbl.'.transaction_status');
                  // Condition added by Harshada On date 31 Aug 2020 Reference by Priyanka mam
                // To search filter by pending
                $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = customer_transaction.order_no AND `transaction_mapping`.`order_id` = customer_transaction.id
                        "));       
                    }); 
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }

        }

        if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 
       

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
           // $search_term  = date('Y-m-d',strtotime($search_term));

            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }    

        if(isset($arr_search_column['customer_id']) && $arr_search_column['customer_id']!="" && $arr_search_column['customer_id']!='0')
        {
            $customer_id = base64_decode($arr_search_column['customer_id']);

            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.customer_id',$customer_id);
        } 

        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="")
        {
            $search_term_from_date  = $arr_search_column['q_order_from_date'];
            $search_term_to_date    = $arr_search_column['q_order_to_date'];
            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');
        
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_customer_quotes_tbl.'.created_at', '<=', $to_date);
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_customer_quotes_tbl.'.created_at', '>=', $from_date);
        }


        //Calculate total by Harshada on date 09 Sep 2020   

        $total_amt = 0;      
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_retail_price ;
        }, 0);

        $current_context = $this;
        //dd($obj_qutoes->get()->toArray());
        $json_result  = Datatables::of($obj_qutoes);


        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $us_date_format = us_date_format($data->created_at);
                        })
                      
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            
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
                            $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);

                            $cancel_href = $this->module_url_path.'/cancel/'.base64_encode($data->id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-outline btn-success btn-circle show-tooltip  viewstyle" href="'.$view_href.'">View</a>';

                            $build_cancel_action='';
                           
                            if($data->order_cancel_status == 0)
                            {
                               $build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Order" class="btn btn-outline btn-info btn-circle show-tooltip  btn-retailer-view cancelstyle" href="javascript:void(0);" onclick="cancelOrder($(this));" data-order-id="'.$data->id.'">Cancel</a>';
                            }
                            elseif($data->order_cancel_status == 1)
                            {
                               /*$build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-outline btn-info btn-circle show-tooltip  btn-retailer-view cancelrequestedstyle" href="javascript:void(0);" data-order-id="'.$data->id.'">Cancel Requested</a>';*/
                            }


                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.'</a>';


                            if($data->ship_status !=1)
                            {
                               return $build_action = $build_view_action.' '.$build_cancel_action;
                            } 
                            else
                            {
                                return $build_action = $build_view_action; 
                            }                                     
                            
                        })
                        ->editColumn('build_refund_btn',function($data) use ($current_context)
                        {   
                            $build_refund_action = "-";
                            $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'N/A'; 
                            if($payment_status=='Paid')
                            {    
                                //get unread messages count   
                                if($data->refund_status == 0)
                                {
                                    $build_refund_action = '<span class="label label-warning">Refund Pending</span>';   
                                }
                                if ($data->refund_status == 1) {

                                    $build_refund_action = '<span class="label label-warning">Refund Paid</span>';
                                }
                            }    
                         

                            return $build_action = $build_refund_action;
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
        $enquiry_arr = $arr_refund_detail = $orderCalculationData = [];

        
        $enquiry_obj = $this->CustomerQuotesModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping'])
                                                 ->where('id',$enquiry_id)->first();          
       
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
        }

         /*get order calculation data from helper*/
        if(isset($enquiry_arr['order_no']) && !empty($enquiry_arr['order_no']) &&
                isset($enquiry_arr['maker_id']) && !empty($enquiry_arr['maker_id']))
        {
            $ordNo = base64_encode($enquiry_arr['order_no']);
            $vendorId = base64_encode($enquiry_arr['maker_id']);

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='customer');
        }    
        
        $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['arr_refund_detail']  = $arr_refund_detail;
        $this->arr_view_data['module_title']    = $this->module_title;
        //$this->arr_view_data['page_title']      = $this->module_title.' Details';
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

        // dd($this->arr_view_data);

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


}
