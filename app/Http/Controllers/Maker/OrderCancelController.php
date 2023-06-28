<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\ProductInventoryModel;
use App\Models\RetailerModel;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;

use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;

class OrderCancelController extends Controller
{
    public function __construct( RetailerQuotesModel $RetailerQuotesModel,
                                 UserModel $UserModel,
                                 RetailerModel $RetailerModel,
                                 TransactionMappingModel $TransactionMappingModel,
                                 TransactionsModel $TransactionsModel,
                                 ProductInventoryModel $ProductInventoryModel,
                                 HelperService $HelperService,
                                 GeneralService $GeneralService
                               )
    {
    	$this->arr_view_data           = [];
        $this->BaseModel               = $RetailerQuotesModel;                        	
        $this->UserModel               = $UserModel;
        $this->RetailerModel           = $RetailerModel;
    	$this->ProductInventoryModel   = $ProductInventoryModel;
    	$this->TransactionMappingModel = $TransactionMappingModel;
    	$this->TransactionsModel       = $TransactionsModel;
    	$this->GeneralService          = $GeneralService;
        $this->HelperService           = $HelperService;
    	$this->module_title            = "Retailer Cancelled Orders Requests";
    	$this->module_view_folder      = 'maker.retailer_cancel_orders';
        $this->maker_panel_slug        = config('app.project.maker_panel_slug');
        $this->module_url_path         = url($this->maker_panel_slug.'/retailer_cancel_orders');
    }


    public function index(Request $request)
    {
        $retailer_id = $request->input('retailer_id',null);

        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Retailer Cancelled Orders Requests';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['retailer_id']      = $retailer_id;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_enquiries(Request $request)
    {  
       //dd("ok");
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $retailer_quotes_tbl_name         = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl     = DB::getTablePrefix().$this->BaseModel->getTable();

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name                    = $this->UserModel->getTable();
        $prefixed_user_tbl 			      = DB::getTablePrefix().$this->UserModel->getTable();

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
                                ->where($retailer_quotes_tbl_name.'.order_cancel_status','=',1)

                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                      $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                      ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                    })                               
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
              
        if(isset($arr_search_column['q_enquiry_id']) && $arr_search_column['q_enquiry_id']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_id'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term  = $arr_search_column['q_description'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term = $arr_search_column['q_retailer_name'];
            $obj_qutoes  = $obj_qutoes->having('dummy_store_name','LIKE', '%'.$search_term.'%');
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
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
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

        //dd($obj_qutoes->toSql(),$obj_qutoes->getBindings());

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

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer');
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
        try
        {


    	$loggedInUserId =0;
        $form_data = $request->all();

        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }

        $updated_arr = [];
        $retailer_trans_arr = [];
        $updated_arr['order_cancel_status']   = isset($form_data['status'])?$form_data['status']:'';
        $updated_arr['order_rejected_reason'] = isset($form_data['reason'])?$form_data['reason']:'';


        $order_id = base64_encode($form_data['order_id']);

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

        $retailer_transaction_details = $this->BaseModel->where('id',$form_data['order_id'])
                                                        ->with(['quotes_details.product_details','user_details'=>function($q)
                                                        {
                                                        	$q->select('id','first_name','last_name');
                                                        },'maker_details'=>function($q1){
                                                        	$q1->select('id','first_name','last_name');
                                                        }])
                                                        ->first();

      
        if(isset($retailer_transaction_details))
        {
            $retailer_trans_arr = $retailer_transaction_details->toArray();

            $retailer_id = $retailer_trans_arr['retailer_id'];
        }
        
        $notification_link = "";
        if($result)
        {

            $msg = $order_no = $admin_msg = '';
            /*send notification to retailer*/

            $order_no = isset($retailer_trans_arr['order_no'])?$retailer_trans_arr['order_no']:'';

            if($form_data['status'] == 2)
            {
                /*Update quantity*/
                 if(isset($retailer_trans_arr['maker_confirmation']) && $retailer_trans_arr['maker_confirmation'] == 1)
                {
                    /*Update quantity*/

                   foreach ($retailer_trans_arr['quotes_details'] as $key => $value) 
                   {
    
                    $update_qunty = $this->ProductInventoryModel->where('sku_no',$value['sku_no'])->increment('quantity',$value['qty']); 

                    }

                }
                
  
                $msg ='Your cancel order request ('.$order_no.') approved by '.$retailer_trans_arr['maker_details']['first_name'].' '.$retailer_trans_arr['maker_details']['last_name'];

                $admin_msg = 'Cancel order request ('.$order_no.') approved by '.$retailer_trans_arr['maker_details']['first_name'].' '.$retailer_trans_arr['maker_details']['last_name'];

                $notification_link = url('/retailer/my_cancel_orders/view/'.$order_id);
                
                // Send cancel order status email to retailer
                $this->GeneralService->cancel_order_status_mail($form_data['status'],$retailer_trans_arr);


            }
            else if($form_data['status'] == 0)
            {
                try {
                   
                $data = [];
                $data['order_cancel_rejected_status'] = 1;
         
                $this->BaseModel->where('id',$form_data['order_id'])->update($data);
    
                } 
                catch (Exception $e) {
                   $response['status']      = 'error';
                   $response['description'] = $e->getMessage();
                   
                   return response()->json($response);
                }
     
                $msg ='Your cancel order request ('.$order_no.') rejected by '.$retailer_trans_arr['maker_details']['first_name'].' '.$retailer_trans_arr['maker_details']['last_name'];


                $admin_msg = 'Cancel order request ('.$order_no.') rejected by '.$retailer_trans_arr['maker_details']['first_name'].' '.$retailer_trans_arr['maker_details']['last_name'];

                $notification_link = url('/retailer/my_orders/view/'.$order_id);

                $reason = isset($form_data['reason'])?strip_tags($form_data['reason']):'';
            
                // Send cancel order status email to retailer
                $this->GeneralService->cancel_order_status_mail($form_data['status'],$retailer_trans_arr,$reason);

            }
            

            
            $notification_arr                       = [];
            $notification_arr['from_user_id']       = $loggedInUserId;
            $notification_arr['to_user_id']         = $retailer_id;
            $notification_arr['link']               = $notification_link;
            $notification_arr['description']        = $msg;
            $notification_arr['title']              = 'Order Request Confirmation';
            $notification_arr['type']               = 'retailer';   
          
            $this->GeneralService->save_notification($notification_arr,'retailer');

       
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
       	        $response['link']        = url('/vendor/cancel_orders');

       	        return response()->json($response);
        	}
        	elseif($form_data['status'] == 0)
        	{
        		$response['status']      = 'success';
       	        $response['description'] = 'Cancel order request has been rejected.';
       	        $response['link']        = url('/vendor/retailer_orders');
       	        return response()->json($response);
        	}
            else
            {
               $response['status']       = 'error';
               $response['description']  = 'Something went wrong, please try again.';
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
    catch(Exception $e)
    {
      $response['status']       = 'error';
      $response['description']  = $e->getMessage();
      return response()->json($response);
    } 

   }
   
}
