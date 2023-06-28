<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustomerQuotesModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\ProductInventoryModel;
use App\Models\CustomerModel;
use App\Common\Services\GeneralService;
use App\Common\Services\CustomerOrderService;
use App\Common\Services\HelperService;

use Sentinel;
use DB;
use Datatables;
use Flash; 
use DateTime;

class CustomerOrderCancelRequestController extends Controller
{
    public function __construct( CustomerQuotesModel $CustomerQuotesModel,
                                 UserModel $UserModel,
                                 CustomerModel $CustomerModel,
                                 TransactionMappingModel $TransactionMappingModel,
                                 TransactionsModel $TransactionsModel,
                                 ProductInventoryModel $ProductInventoryModel,
                                 GeneralService $GeneralService,
                                 CustomerOrderService $CustomerOrderService,
                                 HelperService $HelperService
                               )
    {
    	$this->arr_view_data           = [];
        $this->BaseModel               = $CustomerQuotesModel;                        	
        $this->UserModel               = $UserModel;
        $this->CustomerModel           = $CustomerModel;
    	$this->ProductInventoryModel   = $ProductInventoryModel;
    	$this->TransactionMappingModel = $TransactionMappingModel;
    	$this->TransactionsModel       = $TransactionsModel;
    	$this->GeneralService          = $GeneralService;
        $this->CustomerOrderService    = $CustomerOrderService;
        $this->HelperService           = $HelperService;
    	$this->module_title            = "Customer Canceled Orders Requests";
    	$this->module_view_folder      = 'maker.customer_cancel_orders_request';
        $this->maker_panel_slug        = config('app.project.maker_panel_slug');
        $this->module_url_path         = url($this->maker_panel_slug.'/customer_cancel_orders_request');
    }


    public function index(Request $request)
    {
        $customer_id = $request->input('customer_id',null);

        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Customer Canceled Orders Requests';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['customer_id']      = $customer_id;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_enquiries(Request $request)
    {  
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $customer_quotes_tbl_name         = $this->BaseModel->getTable();        
        $prefixed_customer_quotes_tbl     = DB::getTablePrefix().$this->BaseModel->getTable();

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name                    = $this->UserModel->getTable();
        $prefixed_user_tbl 			      = DB::getTablePrefix().$this->UserModel->getTable();

        $customer_table        = $this->CustomerModel->getTable();
        $prefix_customer_table = DB::getTablePrefix().$customer_table;

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefixed_transaction_mapping_tbl.".order_id,".
                                                    
                                                 $prefix_customer_table.'.store_name,'.
                                				    "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))

                                ->where($customer_quotes_tbl_name.'.order_cancel_status','=',1)

                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.customer_id')

                                ->leftjoin($customer_table,$customer_table.'.user_id','=',$prefixed_customer_quotes_tbl.'.customer_id')
/*
                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')*/

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl)
                                {

                                      $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                           ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                               
                                ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC');
        
        
        if(isset($arr_search_column['customer_id']) && $arr_search_column['customer_id']!="" && $arr_search_column['customer_id']!='0')
        {
            $customer_id = base64_decode($arr_search_column['customer_id']);
            
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.customer_id',$customer_id)->where($prefixed_customer_quotes_tbl.'.maker_id',$loggedInUserId);
        }
        else
        {
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.maker_id',$loggedInUserId);   
        } 
         

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
              
        if(isset($arr_search_column['q_enquiry_id']) && $arr_search_column['q_enquiry_id']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_id'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term  = $arr_search_column['q_description'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
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
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_payment_type']) && $arr_search_column['q_payment_type']!="")
        {
            $search_term = $arr_search_column['q_payment_type'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.is_direct_payment','=',$search_term);
        }

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  
            

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_customer_quotes_tbl.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }

        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');

            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
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


       //Calculate total by Harshada on date 10 Sep 2020
        $total_amt = 0;        
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
          return $res + $item->total_retail_price;
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
                            $products_arr = get_customer_quote_products($id);

                            return $product_html = $this->CustomerOrderService->order_products_for_list($id,$order_no,$products_arr);

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
                            $is_online = check_is_user_online($data->customer_id);

                            if($is_online ==true)
                            {
                              $online_status = '<span class="act-online"></span>';
                            }
                            else
                            {
                              $online_status = '<span class="act-offline"></span>';
                            }

                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                        

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>';


                            return $build_action = $build_view_action;
                        })
                         ->editColumn('ship_status',function($data) use ($current_context)
                        {
                            $ship_status = get_order_status($data->ship_status);
                            return $ship_status;

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
    	$enquiry_arr = $orderCalculationData = [];

    	
    	$enquiry_obj = $this->BaseModel->with(['transaction_mapping','quotes_details.product_details.brand_details','user_details.customer_details','maker_details','maker_data','maker_brand_details'=>function($query){
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

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='customer');
        }


    	$this->arr_view_data['enquiry_arr']     = $enquiry_arr;
    	$this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


    public function order_confirmation(Request $request)
    {  
    	$loggedInUserId = $customer_id = $order_no = 0;
        $form_data = $request->all();


        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }

        $updated_arr = [];
        $customer_trans_arr = [];
        $updated_arr['order_cancel_status']   = $form_data['status'];
        $updated_arr['order_rejected_reason'] = isset($form_data['reason'])?$form_data['reason']:'';

        $order_id = base64_encode($form_data['order_id']);
        //$result = $this->BaseModel->where('id',$form_data['order_id'])
        // Get order detail
        $objOrderDetails = $this->BaseModel->where('id',$form_data['order_id']);

        // Get order_cancel_status 
        if($objOrderDetails)
        {
            $orderCancelStatus = $objOrderDetails->first(['order_cancel_status']);
        }
        
        // If order is already approved
        if($orderCancelStatus->order_cancel_status == $updated_arr['order_cancel_status'])
        {
           $response['status']       = 'error';
           $response['description']  = 'Order is already cancelled.';
           return response()->json($response);
        }

         $result = $objOrderDetails->update($updated_arr);

        $customer_transaction_details = $this->BaseModel->where('id',$form_data['order_id'])
                                                        ->with(['quotes_details.product_details','user_details'=>function($q)
                                                        {
                                                        	$q->select('id','first_name','last_name');
                                                        },'maker_details'=>function($q1){
                                                        	$q1->select('id','first_name','last_name');
                                                        }])
                                                        ->first();

        if(isset($customer_transaction_details))
        { 
            $customer_trans_arr = $customer_transaction_details->toArray();
            $customer_id = isset($customer_trans_arr['customer_id'])?$customer_trans_arr['customer_id']:0;
            $order_no = isset($customer_trans_arr['order_no'])?$customer_trans_arr['order_no']:0;
        }
        
        $notification_link = "";
        if($result)
        {

            $msg = $admin_msg = '';
            $link = '';
            /*send notification to customer*/

            if($form_data['status'] == 2)
            {
                /*Update quantity*/

                if(isset($customer_trans_arr['maker_confirmation']) && $customer_trans_arr['maker_confirmation'] == 1)
                {
                    foreach ($customer_trans_arr['quotes_details'] as $key => $value) 
                    {
                        $update_qunty = $this->ProductInventoryModel->where('sku_no',$value['sku_no'])->increment('quantity',$value['qty']);
                    }               
                }


                $msg ='Your cancel order request ('.$order_no.') is approved by '.$customer_trans_arr['maker_details']['first_name'].' '.$customer_trans_arr['maker_details']['last_name'];

                $admin_msg = 'Cancel order request ('.$order_no.') is approved by '.$customer_trans_arr['maker_details']['first_name'].' '.$customer_trans_arr['maker_details']['last_name'];


                $link = url('/customer/my_cancel_orders');

                $notification_link = url('/customer/my_cancel_orders/view/'.$order_id);
            }
            else if($form_data['status'] == 0)
            {
                $update_cancel_reject_status = $this->BaseModel->where('id',$form_data['order_id'])->update(['order_cancel_rejected_status'=>1]);
                
                $msg ='Your cancel order request ('.$order_no.') is rejected by '.$customer_trans_arr['maker_details']['first_name'].' '.$customer_trans_arr['maker_details']['last_name'];

                $admin_msg = 'Cancel order request ('.$order_no.') is rejected by '.$customer_trans_arr['maker_details']['first_name'].' '.$customer_trans_arr['maker_details']['last_name'];

                $link = url('/customer/my_orders');
                $notification_link = url('/customer/my_orders/view/'.$order_id);
            }


            // Send cancel order status email to customer
            $reason = isset($form_data['reason'])?strip_tags($form_data['reason']):'';

            $this->CustomerOrderService->cancel_order_status_mail($form_data['status'],$customer_trans_arr,$reason);


            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $customer_id;
            $notification_arr['description']  = $msg;
            $notification_arr['title']        = 'Order Request Confirmation';
            $notification_arr['type']         = 'customer';   
            $notification_arr['link']         = $notification_link;

            $this->CustomerOrderService->save_notification($notification_arr);


            //send cancel order  request notficaition to the admin
                    
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = 1;
            $notification_arr['description']  = $admin_msg;

            $notification_arr['title']        = 'Order Request Confirmation';
            $notification_arr['type']         = 'admin';
            $notification_arr['link']         = '';

            $this->GeneralService->save_notification($notification_arr);




        	if($form_data['status'] == 2)
        	{
        		$response['status']      = 'success';
       	        $response['description'] = 'Cancel order request has been approved.';
       	        $response['link']        = url('/vendor/customer_cancel_orders');

       	        return response()->json($response);
        	}
        	elseif($form_data['status'] == 0)
        	{
        		$response['status']      = 'success';
       	        $response['description'] = 'Cancel order request has been rejected.';
       	        $response['link']        = url('/vendor/customer_orders');
       	        return response()->json($response);
        	}
       	
        }
        else
       	{
           $response['status']       = 'error';
       	   $response['description']  = 'Something went wrong, please try again.';
       	   return response()->json($response);
       	} 
    }

}
