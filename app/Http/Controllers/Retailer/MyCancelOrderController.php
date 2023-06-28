<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\MakerModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Models\RefundModel;

use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;


use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;

class MyCancelOrderController extends Controller
{
    
    public function __construct(RetailerQuotesModel $retailer_quote,UserModel $user_model,
                                ProductsModel $product_model,RetailerQuotesProductModel $retailer_quotes,
                                MakerModel $MakerModel,
                                TransactionsModel $TransactionsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                RefundModel $RefundModel,
                                EmailService $EmailService,
                                HelperService $HelperService,
                                GeneralService $GeneralService
                               )
    {
    	$this->arr_view_data              = [];
    	$this->module_title               = "My Cancelled Orders";
    	$this->module_view_folder         = 'retailer.my_cancel_orders'; 
    	$this->retailer_panel_slug        = config('app.project.retailer_panel_slug');
    	$this->module_url_path            = url($this->retailer_panel_slug.'/my_cancel_orders');
        $this->RetailerQuotesModel        = $retailer_quote;
        $this->UserModel                  = $user_model;
        $this->ProductsModel              = $product_model;
        $this->MakerModel                 = $MakerModel;
        $this->TransactionsModel          = $TransactionsModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->RefundModel                = $RefundModel;
        $this->EmailService               = $EmailService;
        $this->GeneralService             = $GeneralService;
        $this->HelperService              = $HelperService;
        $this->RetailerQuotesProductModel = $retailer_quotes;
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
            
        $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

        $user_tbl_name                = $this->UserModel->getTable();
        $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl                    = $this->MakerModel->getTable();        
        $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();

        $transaction_tbl              = $this->TransactionsModel->getTable();        
        $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable();

        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                      
                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                        // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                         ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                      $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                      ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                    })   

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                  
                        ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$loggedInUserId)
                        ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)
                        ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
              
         if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }
      

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term = $arr_search_column['q_description'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term = $arr_search_column['q_retailer_name'];
            $obj_qutoes  = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];

            if(isset($search_term) && $search_term == 1)
            {

            }
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }
      

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)   
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_retailer_quotes_tbl.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
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
            
           // $search_term  = date('Y-m-d',strtotime($search_term));

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }    

        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id);
        } 

        if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);

            $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '2');
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

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $from_date);

        } 
        //Calculate total by Harshada on date 09 Sep 2020     
        $total_amt = 0;      
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_wholesale_price;
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
                            $products_arr = get_quote_products($id);

                            return $product_html = $this->GeneralService->order_products_for_list($id,$order_no,$products_arr);

                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {
                            //get unread messages count
                            $unread_message_count = get_quote_unread_messages_count($data->id,'retailer');
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
                               $build_cancel_action = '<a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-outline btn-info btn-circle show-tooltip  btn-retailer-view cancelrequestedstyle" href="javascript:void(0);" data-order-id="'.$data->id.'">Cancel Requested</a>';
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

    public function get_export_retailer_cancel_orders(Request $request)
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
            
        $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

        $user_tbl_name                = $this->UserModel->getTable();
        $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl                    = $this->MakerModel->getTable();        
        $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();

        $transaction_tbl              = $this->TransactionsModel->getTable();        
        $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable();

        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                      
                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                  
                        ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$loggedInUserId)
                        ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)
                        ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->all();
              
         if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }
      

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term = $arr_search_column['q_description'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term = $arr_search_column['q_retailer_name'];
            $obj_qutoes  = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];

            if(isset($search_term) && $search_term == 1)
            {

            }
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }
      

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)   
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_retailer_quotes_tbl.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
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
            
           // $search_term  = date('Y-m-d',strtotime($search_term));

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }    

        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id);
        } 

        if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);

            $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '2');
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

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $from_date);

        } 

        $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_search_column  = $request->all();
        $arr_orders         = $obj_qutoes->get()->toArray();

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


            $refund_status = '--';
            if($value->refund_status == 0 || $value->refund_status==null)
            {
              $refund_status =  'Pending';
            }elseif($value->refund_status == 1)
            {
              $refund_status =  'Paid';
            }

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            $arrayResponseData['Vendor']                = $value->company_name;     
            $arrayResponseData['Total Amount ($)']      = $value->total_wholesale_price;
            $arrayResponseData['Payment Status']        = $payment_status;
            $arrayResponseData['Refund Status']       = $refund_status;
            
            array_push($data,$arrayResponseData);
        }

         return Excel::create('Retailer Cancel Orders', function($excel) use ($data) {
        
        $excel->sheet('Retailer Cancel Orders', function($sheet) use ($data)
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

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $enquiry_id  = base64_decode($enquiry_id);
        $enquiry_arr = $arr_refund_detail = $orderCalculationData = [];

        $enquiry_obj = $this->RetailerQuotesModel->select('order_no')->where('id',$enquiry_id)->first(); 
       
        if (isset($enquiry_obj)) {
            $order_no = $enquiry_obj['order_no'];
            $enquiry_obj = $this->RetailerQuotesModel->with(['quotes_details.product_details.brand_details',
                                                         'maker_details',
                                                         'maker_data',
                                                         'user_details.retailer_details',
                                                         'transaction_mapping'=>function($qry) use ($order_no){
                                                            $qry->where('order_no',$order_no);
                                                        }])
                                                        ->where('id',$enquiry_id)->first();          
        }
        if($enquiry_obj)
        {

            $enquiry_arr = $enquiry_obj->toArray();  

       // dd($enquiry_arr);
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

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer');
        }   
       

        
        $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['arr_refund_detail']  = $arr_refund_detail;
        $this->arr_view_data['module_title']    = $this->module_title;
        //$this->arr_view_data['page_title']      = $this->module_title.' Details';
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


}
