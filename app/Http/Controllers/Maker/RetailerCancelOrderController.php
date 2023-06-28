<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\RetailerModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\RefundModel;


use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;


use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;

class RetailerCancelOrderController extends Controller
{
    public function __construct(RetailerQuotesModel $RetailerQuotesModel,
    							UserModel $UserModel,
                                RetailerModel $RetailerModel,
                                TransactionMappingModel $TransactionMappingModel,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                TransactionsModel $TransactionsModel,
                                RefundModel $RefundModel,
                                StripePaymentService $StripePaymentService,
                                HelperService $HelperService,
                                GeneralService $GeneralService							
    							)
    {
    	$this->BaseModel               = $RetailerQuotesModel;                        	
    	$this->UserModel               = $UserModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->TransactionsModel       = $TransactionsModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;
        $this->RetailerModel           = $RetailerModel;
        $this->RefundModel             = $RefundModel;
        $this->StripePaymentService    = $StripePaymentService;
        $this->GeneralService          = $GeneralService;
        $this->HelperService           = $HelperService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Customer Cancelled Orders";
    	$this->module_view_folder      = 'maker.cancel_orders';
        $this->maker_panel_slug        = config('app.project.maker_panel_slug');
        $this->module_url_path         = url($this->maker_panel_slug.'/cancel_orders');
    }

    public function index(Request $request)
    {
        
        $retailer_id = $request->input('retailer_id',null);
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Customer Cancelled Orders';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['retailer_id']      = $retailer_id;

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

        $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl 			= DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefix_retailer_table.'.store_name,'.
                                                 $prefix_retailer_table.'.dummy_store_name,'.
                                				  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                      $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                      ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                    })   

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)

                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');
        
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id)->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId);
        }
        else
        {
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId);   
        } 
         

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
              
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {  
            $search_term  = $arr_search_column['q_order_no'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

       
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('dummy_store_name','LIKE', '%'.$search_term.'%');
        }
     

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        } 

         if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term  = $arr_search_column['q_total_wholesale_cost'];
            $search_term  = intval($search_term);
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term      = $arr_search_column['q_refund_status'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_payment_type']) && $arr_search_column['q_payment_type']!="")
        {
            $search_term = $arr_search_column['q_payment_type'];
          
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','=',$search_term);
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


        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="")
        {
            $search_term_from_date  = $arr_search_column['q_order_from_date'];
            $search_term_to_date    = $arr_search_column['q_order_to_date'];
            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');
        
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $to_date);
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $from_date);
          
        }

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
                            $products_arr = get_quote_products($id);

                            return $product_html = $this->GeneralService->order_products_for_list($id,$order_no,$products_arr);

                        })

                        ->editColumn('payment_type',function($data) use ($current_context)
                        {
                            if($data->is_direct_payment == 1)
                            {
                              $payment_type = 'Direct';
                            }
                            else
                            {
                              $payment_type = 'In-Direct';
                            }

                            return $payment_type;

                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {
                            //get unread messages count
                            $unread_message_count = get_quote_unread_messages_count($data->id,'maker');
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }

                            //check if user is online or not
                            $is_online = check_is_user_online($data->retailer_id);

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

                            /*$build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.'</a>';*/

                            return $build_action = $build_view_action;
                        })

                        ->editColumn('build_refund_btn',function($data) use ($current_context)
                        {   
                            
                            //get unread messages count
                            $build_refund_action = "-";
                            if($data->transaction_status == 2 && $data->refund_status == 0 && $data->is_direct_payment == 1)
                            {
                                $build_refund_action = '<a  href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund payment" class="btn btn-circle btn-outline btn-success show-tooltip" onclick="refundProcess('.$data->id.')">Refund</a>';   
                            }
                            if ($data->refund_status == 1) {

                                $build_refund_action = '<a href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund payment" class="btn btn-circle btn-outline btn-success show-tooltip">Refund Paid</a>';
                            }

                            
                            return $build_action = $build_refund_action;
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

    public function get_export_retailer_cancel_order(Request $request)
    {
         $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefix_retailer_table.'.store_name,'.
                                                  $prefix_retailer_table.'.dummy_store_name,'.
                                                  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)

                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');
        
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id)->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId);
        }
        else
        {
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId);   
        } 
         

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->all();
              
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {  
            $search_term  = $arr_search_column['q_order_no'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

       
        /*if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }*/

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('dummy_store_name','LIKE', '%'.$search_term.'%');
        }
     

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        } 

         if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term      = $arr_search_column['q_refund_status'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status','LIKE', '%'.$search_term.'%');
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


        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="")
        {
            $search_term_from_date  = $arr_search_column['q_order_from_date'];
            $search_term_to_date    = $arr_search_column['q_order_to_date'];
            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');
        
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $to_date);
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $from_date);
          
        }

          $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $obj_qutoes->get()->toArray();

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
            if($value->transaction_status == 2 && $value->refund_status == 0 && $value->is_direct_payment == 1)
            {
              $refund_status =  'Pending';
            }elseif($value->refund_status == 1)
            {
              $refund_status =  'Refund Paid';
            }
             

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            //$arrayResponseData['Retailer']              = $value->store_name;      
            $arrayResponseData['Customer']              = $value->dummy_store_name;      
            $arrayResponseData['Total Amount']          = $value->total_wholesale_price;
            $arrayResponseData['Customer Payment Status'] = $payment_status;
            $arrayResponseData['Refund Status']         = $refund_status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Customers Cancel Orders', function($excel) use ($data) {
        
        $excel->sheet('Customers Cancel Orders', function($sheet) use ($data)
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

    	
    	$enquiry_obj = $this->BaseModel->with(['transaction_mapping','quotes_details.product_details.brand_details','user_details.retailer_details','maker_details','maker_data','maker_brand_details'=>function($query){
                                        $query->select('id','user_id','brand_name');
                                       }])
    								   ->where('id',$enquiry_id)->first();	
    	    	
    	if($enquiry_obj)
    	{
    		$enquiry_arr = $enquiry_obj->toArray();
            
    		if($enquiry_arr['maker_id'] != $loggedInUserId)
			{
				Flash::error('You are not authorize user to access this page.');
    			return redirect()->back();
			}

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
    	else
    	{
    		Flash::error('Something went wrong, please try again.');
    		return redirect()->back();
    	}
        // dd($enquiry_arr);

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
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;
        
    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

    public function refund_payment(Request $request)
    { 
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

       $order_id = $request['order_id'];
       $getOrderDetails = $this->BaseModel->where('id',$order_id)->first();
       if ($getOrderDetails) {

        /*get stripe account details using stripe_key_id (retailer_transaction table) and refund using this stripe account */ 
        $stripe_secret_key = false;

        if(isset($getOrderDetails['stripe_key_id']) && !empty($getOrderDetails['stripe_key_id']))
        {
            $stripe_key_id = $getOrderDetails['stripe_key_id'];

            $stripe_secret_key = $this->UserStripeAccountDetailsModel->where('id',$stripe_key_id)->pluck('secret_key');
        }
        
        // dd($order_id,$getOrderDetails['order_no']);
            $transactionDetails = TransactionMappingModel::where('order_id',$order_id)->where('order_no',$getOrderDetails['order_no'])->first();
       //dd($transactionDetails);

           
            if ($transactionDetails) {
                $refundDetails = $this->StripePaymentService->vendor_retrieve_refund($loggedInUserId,$transactionDetails['transaction_id'],num_format($transactionDetails['amount']),$stripe_secret_key);

                
                
                if ($refundDetails['status'] == 'succeeded') 
                {
                   
                    $updateRefundStatus = $this->BaseModel->where('id',$order_id)->update(['refund_status' => '1']);

                    $refundData['order_id'] = $order_id or ' ';
                    $refundData['order_no'] = $getOrderDetails['order_no'] or ' ';

                    if($getOrderDetails['is_direct_payment'] == 1)
                    {
                       $refundData['paid_by'] = $getOrderDetails['maker_id'];
                    }
                    else
                    {
                       $refundData['paid_by'] = get_admin_id();
                    }

                    $refundData['received_by'] = $getOrderDetails['retailer_id'] or ' ';
                    $refundData['transaction_id'] = $refundDetails['charge'];
                    $refundData['amount'] = num_format($transactionDetails['amount']);
                    $refundData['balance_transaction'] = $refundDetails['balance_transaction'];
                    $refundData['status'] = '2';

                    $this->RefundModel->create($refundData);
                    /*Send notification to retailer*/
                    $arr_notify_data                 = [];
                    $arr_notify_data['from_user_id'] = $loggedInUserId;
                    $arr_notify_data['to_user_id']   = $getOrderDetails['retailer_id'] or '';

                    $arr_notify_data['description']  = 'For your canceled order('.$getOrderDetails['order_no'].') refund is initiated, it will be reflected within 5 to 10 business days. Transaction id: '.$refundDetails['balance_transaction'];
                    $arr_notify_data['title']        = 'Payment Refund';
                    $arr_notify_data['type']         = 'retailer';  
                    $arr_notify_data['link']         = '';  

                    $this->GeneralService->save_notification($arr_notify_data);

                    $response['status'] = 'success';
                    $response['msg']    = 'Refund is initiated it will reflect to Customer account within 5 to 10 business days.';
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
