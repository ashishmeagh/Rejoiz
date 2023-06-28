<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustomerQuotesModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\CustomerModel;
use App\Models\OrderTrackDetailsModel;
use App\Models\InfluencerSettingModel;
use App\Models\InfluencerRewardsModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\StripeTransactionModel;

use App\Models\StripeAccountDetailsModel;
use App\Models\GeneralSettingModel;

use App\Common\Services\CustomerOrderService;
use App\Common\Services\EmailService;
use App\Common\Services\StripePaymentService;
use App\Common\Services\InfluencerService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;
use App\Models\SiteSettingModel;

use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;


class CustomerQuotesController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 03 July 2019
    */
    public function __construct(CustomerQuotesModel $CustomerQuotesModel,
    							UserModel $UserModel,
                                TransactionMappingModel $TransactionMappingModel,
                                CustomerModel $CustomerModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,
                                StripeTransactionModel $StripeTransactionModel,
                                TransactionsModel $TransactionsModel,
                                CustomerOrderService $CustomerOrderService,
                                OrderTrackDetailsModel $OrderTrackDetailsModel,
                                EmailService $EmailService,
                                HelperService $HelperService,
                                InfluencerSettingModel $InfluencerSettingModel,
                                InfluencerRewardsModel $InfluencerRewardsModel,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                GeneralSettingModel $GeneralSettingModel,
                                StripePaymentService $StripePaymentService,
                                CommissionService $CommissionService,
                                InfluencerService $InfluencerService,
                                SiteSettingModel $SiteSettingModel
    							)
    { 
        $this->InfluencerSettingModel  = $InfluencerSettingModel;
        $this->InfluencerRewardsModel  = $InfluencerRewardsModel;
        $this->StripeAccountDetailsModel = $StripeAccountDetailsModel;
        $this->StripePaymentService      = $StripePaymentService;
    	$this->BaseModel               = $CustomerQuotesModel;                        	
    	$this->UserModel               = $UserModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->TransactionsModel       = $TransactionsModel;
        $this->CustomerModel           = $CustomerModel;        
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->StripeTransactionModel  = $StripeTransactionModel;
        $this->GeneralSettingModel     = $GeneralSettingModel;
        $this->CustomerOrderService    = $CustomerOrderService;
        $this->OrderTrackDetailsModel  = $OrderTrackDetailsModel;
        $this->EmailService            = $EmailService;
        $this->InfluencerService       = $InfluencerService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Customer Orders";
    	$this->module_view_folder      = 'maker.customer_quotes';
        $this->maker_panel_slug        = config('app.project.maker_panel_slug');
        $this->module_url_path         = url($this->maker_panel_slug.'/customer_orders');
        $this->SiteSettingModel        = $SiteSettingModel;
        $this->site_setting_obj        = $this->SiteSettingModel->first();
        $this->CommissionService       = $CommissionService;
        $this->HelperService           = $HelperService;
        
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
    }

    public function index(Request $request)
    {
        $customer_id = $request->input('customer_id',null);
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Customer Orders';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['customer_id']      = $customer_id;
        // dd($this->arr_view_data);
    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_enquiries(Request $request)
    {
        //dd("123");
        $admin_commission = $this->CommissionService->get_admin_commission();

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
 
        $customer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $customer_quotes_details      = $this->CustomerQuotesProductModel->getTable();
        $customer_quotes_details_tbl  = DB::getTablePrefix().$this->CustomerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl 			= DB::getTablePrefix().$this->UserModel->getTable();

        $customer_table        = $this->CustomerModel->getTable();
        $prefix_customer_table = DB::getTablePrefix().$customer_table;

        $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".                
                                                 $customer_quotes_details.'.shipping_charge,'.
                                				  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.customer_id')

                                ->leftjoin($customer_table,$customer_table.'.user_id','=',$prefixed_customer_quotes_tbl.'.customer_id')

                                ->leftjoin($customer_quotes_details,$customer_quotes_details_tbl.'.customer_quotes_id','=',$prefixed_customer_quotes_tbl.'.id')

                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.customer_order_id')
 
                                // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                              
                                ->where($prefixed_customer_quotes_tbl.'.maker_id',$loggedInUserId)

                                /*->where($prefixed_customer_quotes_tbl.'.order_cancel_status','!=',2)*/

                                ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','=',0)

                                ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC')

                                 ->groupBy($prefixed_customer_quotes_tbl.".id")
                                 ;

        //dd($obj_qutoes->get()->toArray());
                                

                               
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
            $search_term      = $arr_search_column['q_enquiry_id'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
        {
            $search_term     = $arr_search_column['q_customer_name'];
            $obj_qutoes      = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term  = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }
        /*
                if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
                {
                    $search_term = $arr_search_column['q_total_wholesale_cost'];
                    dd($search_term);
                    $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
                }
        */
         if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
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

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

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

          if(isset($arr_search_column['admin_commission_status']) && $arr_search_column['admin_commission_status']!="")
        {  

            $search_term = $arr_search_column['admin_commission_status'];
            if($search_term == 0)
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.admin_commission_status','=', NULL);
            }
            else
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.admin_commission_status','=', ''.$search_term.'');
            }            
        }

       // echo "<pre>";print_r($obj_qutoes->get()->toArray());die;

        //Calculate total by Harshada on date 09 Sep 2020
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
                        ->editColumn('build_action_btn',function($data) use ($current_context,$admin_commission)
                        {
                            // dd($data);
                            $admin_commission = $data->admin_commission;
                            if($data->admin_commission_status == '1')
                            {
                               $is_disabled = 'display:none';
                            }
                            else
                            {
                               $is_disabled='display:block';
                            }

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

                            $ord_retail_price = isset($data->total_retail_price)?$data->total_retail_price:0;

                            // $ord_retail_price = $ord_retail_price - $data->shipping_charge;

                            $shippingCharges = $this->get_customer_order_shipping_charges($data->id);


                            $ord_retail_price = $ord_retail_price - $shippingCharges;

                            $admin_commission_amount = $ord_retail_price*($admin_commission / 100);

                            $pay_admin_button = '';

                             if($data->ship_status == 1 && $data->is_direct_payment == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                             {
                               /* $pay_admin_button = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_admin_btn" title="Pay '.$this->site_setting_arr['site_name'].'"  onclick="fillData('.$ord_retail_price.','.$admin_commission.','.$admin_commission_amount.','.$data->id.','.$data->maker_id.')" style="'.$is_disabled.'" >Pay '.$this->site_setting_arr['site_name'].'</button>';*/
                                
                             }



                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            // $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>'.$pay_admin_button;

                            /*$build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.'</a>';*/

                            return $build_action = $build_view_action;
                        })
                         ->editColumn('ship_status',function($data) use ($current_context)
                        {
                            $ship_status = get_order_status($data->ship_status);
                            return $ship_status;

                        })
                         ->editColumn('admin_commission_status',function($data) use ($current_context)
                        {                             
                        return $data->admin_commission_status;
                            
                        })



                        ->editColumn('payment_term',function($data) use ($current_context)
                        {   
                            $is_direct_payment  = isset($data->is_direct_payment)?$data->is_direct_payment:"";

                          if($is_direct_payment == 1)
                          {
                             $payment_term = '<span class="label label-success">Direct</span>';
                          }
                          else
                          {
                             $payment_term = '<span class="label label-success">In-Direct</span>';
                          }
                            
                        return $payment_term;
                            
                        })

                        
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            $payment_status ='';

                            return $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'Pending';

                            
                        });


        $build_result = $json_result->make(true)->getData();
        $build_result->total_amt = $total_amt;
        return response()->json($build_result);
    }

    public function get_export_customer_orders(Request $request)
    {
        $admin_commission = $this->CommissionService->get_admin_commission();

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
 
        $customer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $customer_quotes_details      = $this->CustomerQuotesProductModel->getTable();
        $customer_quotes_details_tbl  = DB::getTablePrefix().$this->CustomerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

        $customer_table        = $this->CustomerModel->getTable();
        $prefix_customer_table = DB::getTablePrefix().$customer_table;

        $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".                
                                                 $customer_quotes_details.'.shipping_charge,'.
                                                  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.customer_id')

                                ->leftjoin($customer_table,$customer_table.'.user_id','=',$prefixed_customer_quotes_tbl.'.customer_id')

                                ->leftjoin($customer_quotes_details,$customer_quotes_details_tbl.'.customer_quotes_id','=',$prefixed_customer_quotes_tbl.'.id')

                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.customer_order_id')
 
                                // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                              
                                ->where($prefixed_customer_quotes_tbl.'.maker_id',$loggedInUserId)

                                /*->where($prefixed_customer_quotes_tbl.'.order_cancel_status','!=',2)*/

                                ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','=',0)

                                ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC')

                                 ->groupBy($prefixed_customer_quotes_tbl.".id")
                                 ;
                 
       
        $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.maker_id',$loggedInUserId);   
         

        /* ---------------- Filtering Logic ----------------------------------*/                    
         $arr_search_column = $request->all();
              
        if(isset($arr_search_column['q_enquiry_id']) && $arr_search_column['q_enquiry_id']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_id'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
        {
            $search_term     = $arr_search_column['q_customer_name'];
            $obj_qutoes      = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term  = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }
       
        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
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

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

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

          if(isset($arr_search_column['admin_commission_status']) && $arr_search_column['admin_commission_status']!="")
        {  

            $search_term = $arr_search_column['admin_commission_status'];
            if($search_term == 0)
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.admin_commission_status','=', NULL);
            }
            else
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.admin_commission_status','=', ''.$search_term.'');
            }            
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


            $shipping_status = 'Pending';
            if($value->ship_status == 0 || $value->ship_status==null)
            {
              $shipping_status =  'Pending';
            }elseif($value->ship_status == 1)
            {
              $shipping_status =  'Shipped';
            }
            else
            {
               $shipping_status = 'Incomplete';
            }     


            $payment_type = 'Direct';
            if($value->is_direct_payment == 1 || $value->is_direct_payment==null)
            {
              $payment_type =  'Direct';
            }else
            {
              $payment_type = 'In-Direct';
            }  
    
            $admin_status = 'Paid';
            if($value->admin_commission_status == 1)
            {
                $admin_status =  'Paid';
            }
            else
            {
                $admin_status = 'Pending';
            }


            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            $arrayResponseData['Customer']              = $value->user_name;      
            $arrayResponseData['Total Amount ($)']      = $value->total_retail_price;
            $arrayResponseData['Payment Status']        = $payment_status;
            $arrayResponseData['Shipping Status']       = $shipping_status;
            $arrayResponseData['Payment Type']          = $payment_type;
            $arrayResponseData['Admin Commission Status'] = $admin_status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Customer Orders', function($excel) use ($data) {        
            $excel->sheet('Customer Orders', function($sheet) use ($data)
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
    	$enquiry_arr = $split_order_arr = $main_split_order_no = $orderCalculationData = [];

        $customer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $customer_quotes_details      = $this->CustomerQuotesProductModel->getTable();
        $customer_quotes_details_tbl  = DB::getTablePrefix().$this->CustomerQuotesProductModel->getTable();

    	$transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

    	$enquiry_obj = $this->BaseModel->with(['quotes_details.product_details.brand_details',
                                               'user_details.customer_details',
                                               'maker_details',
                                               'transaction_mapping',
                                               'maker_data',
                                               'stripe_transaction_detail',
                                               'maker_brand_details'=>function($query){
                                        $query->select('id','user_id','brand_name','admin_commission as adm_commision_percent');
                                       }])
                                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                            $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                                 ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                        })
                                        ->select($prefixed_customer_quotes_tbl.'.*',$prefixed_transaction_mapping_tbl.'.transaction_status')
    								   ->where($prefixed_customer_quotes_tbl.'.id',$enquiry_id)
                                       ->first();
                            // dd($enquiry_obj);
        //                                // ->toSql();
        // $customer_quotes_tbl_name     = $this->BaseModel->getTable();        
        // $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        // $customer_quotes_details      = $this->CustomerQuotesProductModel->getTable();
        // $customer_quotes_details_tbl  = DB::getTablePrefix().$this->CustomerQuotesProductModel->getTable();

        // $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        // $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        // $user_tbl_name              = $this->UserModel->getTable();
        // $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

        // $customer_table        = $this->CustomerModel->getTable();
        // $prefix_customer_table = DB::getTablePrefix().$customer_table;

        // $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        // $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        // $obj_qutoes = DB::table($customer_quotes_tbl_name)
        //                         ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
        //                                          $prefixed_transaction_mapping_tbl.".id as tid,".
        //                                          $prefixed_transaction_mapping_tbl.".transaction_status,".
        //                                          $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".                
        //                                          $customer_quotes_details.'.shipping_charge,'.
        //                                           "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
        //                                                   .$prefixed_user_tbl.".last_name) as user_name"))
        //                         ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.customer_id')

        //                         ->leftjoin($customer_table,$customer_table.'.user_id','=',$prefixed_customer_quotes_tbl.'.customer_id')

        //                         ->leftjoin($customer_quotes_details,$customer_quotes_details_tbl.'.customer_quotes_id','=',$prefixed_customer_quotes_tbl.'.id')

        //                         ->leftjoin($prefixed_stripe_transaction_table,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.lead_id')
 
        //                         // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
        //                         ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

        //                             $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
        //                             ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

        //                         })
                              
        //                         ->where($prefixed_customer_quotes_tbl.'.maker_id',$loggedInUserId)

        //                         ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','!=',2)

        //                         ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC')

        //                          ->groupBy($prefixed_customer_quotes_tbl.".id")
        //                          ;


        if($enquiry_obj)
    	{
    		$enquiry_arr = $enquiry_obj->toArray();
                            // dd($enquiry_arr);
            
           
            if ($enquiry_arr['split_order_id'] != '') {

                $main_split_order_no = $this->BaseModel->where('id',$enquiry_arr['split_order_id'])->first();

            }
            elseif ($enquiry_arr['is_split_order'] == '1') {

                $split_order_arr = $this->BaseModel->where('split_order_id',$enquiry_arr['id'])->get()->toArray();

            }
            
    		if($enquiry_arr['maker_id'] != $loggedInUserId)
			{
				Flash::error('You are not authorize user to access this page.');
    			return redirect()->back();
			}
            $shippingCharges = $this->get_customer_order_shipping_charges($enquiry_id);
            
    	}
    	else
    	{
    		Flash::error('Something went wrong, please try again.');
    		return redirect()->back();
    	}

       

        $adm_comm_percent = 0;
        if(!empty($enquiry_arr)){
                $adm_comm_percent = $enquiry_arr['maker_brand_details']['adm_commision_percent'];
        }


        /*get order calculation data from helper*/
        if(isset($enquiry_arr['order_no']) && !empty($enquiry_arr['order_no']) &&
                isset($enquiry_arr['maker_id']) && !empty($enquiry_arr['maker_id']))
        {
            $ordNo = base64_encode($enquiry_arr['order_no']);
            $vendorId = base64_encode($enquiry_arr['maker_id']);

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='customer');
        }

        $enquiry_arr_id       = isset($enquiry_arr['id'])?$enquiry_arr['id']:0;
        $enquiry_arr_order_no = isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:'';

        $tracking_details = [];
        $tracking_no = 0;

        if($enquiry_arr_id!=0 && $enquiry_arr_order_no!='')
        {
          $tracking_details = $this->HelperService->getTrackingDetails($enquiry_arr_id,$enquiry_arr_order_no);
          $tracking_no = isset($tracking_details['tracking_no'])?$tracking_details['tracking_no']:0;
        }   

    	$this->arr_view_data['enquiry_arr']                 = $enquiry_arr;
        $this->arr_view_data['adm_comm_percent']            = $adm_comm_percent;
        $this->arr_view_data['split_order_arr']             = $split_order_arr;
        $this->arr_view_data['main_split_order_no']         = $main_split_order_no;
    	$this->arr_view_data['module_title']                = $this->module_title;
        $this->arr_view_data['page_title']                  = 'Order Details';
        $this->arr_view_data['module_url_path']             = $this->module_url_path;
        $this->arr_view_data['project_name']                = $this->site_setting_arr['site_name'];
        $this->arr_view_data['order_shipping_charge']       = isset($shippingCharges)?$shippingCharges:0;
        $this->arr_view_data['orderCalculationData']        = $orderCalculationData;

        $this->arr_view_data['tracking_details']            = $tracking_details;
        $this->arr_view_data['tracking_no']                 = $tracking_no;

    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


    public function order_from_representative()
    {
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Products List';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        return view($this->module_view_folder.'.order_from_representative',$this->arr_view_data);
    }

    /*
        Date : 13 Dec 2019
        Auth : Jaydip
        Desc : maintain order status when vendor ship order
    */

    public function ship_order(Request $request)
    {   
        $response    = [];
        $user_id =$admin_id = 0;
        $order_id    = $request->order_id;
        $maker_id    = $request->maker_id;
        $customer_id = $request->customer_id;

        /*get loggedin user*/

        $user = Sentinel::check();

       
        if(isset($user))
        {
          $loggedInUserId = $user->id;
        }
        /*----------------*/



        /* Update Order Status */

        $order_details = $this->BaseModel->where('id',$order_id)->first();
        if ($order_details) {

            if ($order_details['payment_term'] == 'Net30') {

                $order_status_update = $this->BaseModel->where('id',$order_id)
                                               ->where('maker_id',$maker_id)
                                               ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s'),'payment_due_date'=>date('Y-m-d H:i:s', strtotime("+30 days"))]);
            }
            else{

                $order_status_update = $this->BaseModel->where('id',$order_id)
                                               ->where('maker_id',$maker_id)
                                               ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s')]);
            }
           
        }
        

        if($order_status_update)
        {  
            /*after shipping the order send notification to admin & customer*/

            $admin_id = get_admin_id();

            //Get maker name 

            $first_name   = isset($user->first_name)?$user->first_name:"";
            $last_name    = isset($user->last_name)?$user->last_name:""; 
            
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $admin_id;

          
            $order_detail_url = url('/admin/customer_orders/view/'.base64_encode($order_id));

            //$order_url        = '<a href="'.$order_detail_url.'">'.$order_details->order_no.'</a>';
              
            $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name;

            $notification_arr['title']        = 'Order Shipped';
            $notification_arr['type']         = 'admin';
            $notification_arr['link']         = $order_detail_url;


            $this->CustomerOrderService->save_notification($notification_arr);



            /*send to customer*/

            //Get maker name 
         
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $customer_id;

          
            $order_detail_url = url('/customer/my_orders/view/'.base64_encode($order_id));

            //$order_url        = '<a href="'.$order_detail_url.'">'.$order_details->order_no.'</a>';
              
            $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name;

            $notification_arr['title']        = 'Order Shipped';
            $notification_arr['type']         = 'customer';
            $notification_arr['link']         =  $order_detail_url;


            $this->CustomerOrderService->save_notification($notification_arr);


            /*--------------------------------------------------------------------------*/
            $response['status']      = 'success';
            $response['description'] = 'Order has been shipped.';
        }
        else
        {
            $response['status']      = 'warning';
            $response['description'] = 'Something went wrong, please try again.';
        }
        
        return response()->json($response);
    }



    public function saveOrderTrackDetails(Request $request)
    {
        try
        {
            DB::beginTransaction();
                /*------store order tracking details----------*/  
                $data = []; 
                $user_id =$admin_id = 0;
                 
               
                $order_id    = $request->input('order_id');
                $order_no    = $request->input('order_no');
                $company_id  = $request->input('shipping_company');
                $tracking_no = $request->input('tracking_no');
                $maker_id    = $request->input('maker_id');
                $customer_id = $request->input('customer_id');


               
                $data['company_id']  = isset($company_id)?$company_id:0;
                $data['order_no']    = isset($order_no)?$order_no:'';
                $data['order_id']    = isset($order_id)?$order_id:0;
                $data['tracking_no'] = isset($tracking_no)?$tracking_no:'';
 

                $result = $this->OrderTrackDetailsModel->create($data);  


                /*--------ship order-----------------------*/

              
                $user = Sentinel::check();

               
                if(isset($user))
                {
                  $loggedInUserId = $user->id;
                }
             
                /* Update Order Status */

                $order_details = $this->BaseModel->where('id',$order_id)->first();
                
                if ($order_details)
                {

                    if ($order_details['payment_term'] == 'Net30')
                    {
                        $order_status_update = $this->BaseModel->where('id',$order_id)
                                                       ->where('maker_id',$loggedInUserId)
                                                       ->update(['ship_status' => 1,'ship_date' => date('Y-m-d H:i:s'),'payment_due_date'=>date('Y-m-d H:i:s', strtotime("+30 days"))]);
                    }
                    else
                    {
                        
                        $order_status_update = $this->BaseModel->where('id',$order_id)
                                                       ->where('maker_id',$loggedInUserId)
                                                       ->update(['ship_status' => 1,'ship_date' => date('Y-m-d H:i:s')]);
                    }
                   
                }
                
         
                /*after shipping the order send notification to admin & customer*/

                $admin_id = get_admin_id();

                //Get maker name 

                $first_name   = isset($user->first_name)?$user->first_name:"";
                $last_name    = isset($user->last_name)?$user->last_name:""; 
                
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = $admin_id;

              
                //create shipping company url
                if(isset($company_id) && $company_id==1)
                {
                   //$url = 'https://www.fedex.com/en-in/home.html';
                   $url =  "http://www.fedex.com/apps/fedextrack/?action=track&tracknumbers='".$tracking_no."'";
                } 
                elseif(isset($company_id) && $company_id==2)
                {
                  $url = "https://www.ups.com/in/en/Home.page";
                }
                elseif(isset($company_id) && $company_id==3)
                {
                   $url = "https://www.usps.com/";
                }
                elseif(isset($company_id) && $company_id==4)
                {
                   $url = "https://www.dhl.com/en.html";
                }
                else
                {
                   $url = '';
                }

                     
                $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name.' Tracking No: '.$tracking_no;

                $notification_arr['title']        = 'Order Shipped';
                $notification_arr['type']         = 'admin';
                $notification_arr['link']         = $url;


                $this->CustomerOrderService->save_notification($notification_arr);



                /*send to customer*/

                //Get maker name 
             
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = $customer_id;

              
                //$order_detail_url = url('/customer/my_orders/view/'.base64_encode($order_id));
                 
                //create shipping company url
                if(isset($company_id) && $company_id==1)
                {
                   $url      =  "http://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=".$tracking_no;
                    $company = 'Fedex';
                } 
                elseif(isset($company_id) && $company_id==2)
                {
                   $url      = "https://www.ups.com/in/en/Home.page";
                   $company  = 'UPS';
                }
                elseif(isset($company_id) && $company_id==3)
                {
                   $url      = "https://www.usps.com/";
                   $company  = 'USPS';
                }
                elseif(isset($company_id) && $company_id==4)
                {
                   $url     = "https://www.dhl.com/en.html";
                   $company = 'DHL';
                }
                else
                {
                   $url     = '';
                   $company = '';
                }

                  
                $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name.' Tracking No: '.$tracking_no;

                $notification_arr['title']        = 'Order Shipped';
                $notification_arr['type']         = 'customer';
                $notification_arr['link']         =  $url;


                $this->CustomerOrderService->save_notification($notification_arr);

                /*--------------------------------------------------------------------------*/

                /*send shippment mail to the customer*/

                $customer_email  = $this->get_email($customer_id);
                $tracking_url    = "<a href=".$url." target='_blank'>".$tracking_no."</a>";

                $html = 'Your order has been shipped. Order No: ';

                $arr_mail_data   = $this->send_order_ship_mail($customer_email,$order_no,$company,$tracking_url,$html); 



                $email_status  = $this->EmailService->send_mail($arr_mail_data);


                /*send shippment mail to the admin*/
                $admin_email     = get_admin_email();
                $tracking_url    = "<a href=".$url." target='_blank'>".$tracking_no."</a>";

                $html = 'Order has been shipped. Order No: ';


                $arr_mail_data   = $this->send_order_ship_mail($admin_email,$order_no,$company,$tracking_url,$html); 

                $email_status  = $this->EmailService->send_mail($arr_mail_data);

                /*--------------------------------------------------------------------------*/

                /*--------------------Influencer Reward Section (START)-------------------------*/
                /* 
                    -  Check whether customer applied promo code or not, if customer applied the promo code then calculate the retail_price of as per influencer id 
                    -  When vendor click on shipped button from order listing, then give reward to the influencer. but how? We have to calculate sum of all previous orders whose reward we have not given to influencer. and check that sum with sales target if it satisfies then send reward to influencer and marked those orders. (like they have used for calculating the sum). and while calculating for next sum do not consider those orders.
                */
                $sales_target  = $reward_amount = $total_order_price = $influencer_remaining_order_amount = $total_reward_amount = $cnt = $carry_forward_amt = $current_order_amount =  0;
                $arr_influencer_settings = $arr_primary_ids = $arr_orders = [];

                $promo_code    = $request->input('promo_code');
                $influencer_id = $request->input('influencer_id');

                if((isset($promo_code) && $promo_code != '') &&
                    (isset($influencer_id) && $influencer_id != 0))
                {
                    /*Get Orders from customer_transaction table*/
                    $arr_orders = $this->BaseModel
                                        ->where('influencer_id',$influencer_id)
                                        ->where('is_considered_for_calculation',0)
                                        ->where('ship_status',1)
                                        ->get()
                                        ->toArray();
                   
                    /*Calculate Sum of Total Retail Price*/
                    $total_order_price = array_sum(array_column($arr_orders, 'total_retail_price'));
                    $total_order_price = num_format($total_order_price);
                    $current_order_amount = $total_order_price;

                    /*Get influencer_remaining_order_amount from user table, add in total_order_price*/
                    $obj_user = $this->UserModel->where('id',$influencer_id)->first();

                    if($obj_user)
                    {
                        $arr_user = $obj_user->toArray();
                        $influencer_remaining_order_amount = isset($arr_user['influencer_remaining_order_amount'])?num_format($arr_user['influencer_remaining_order_amount']):0;
                    }

                    $total_order_price = $total_order_price + $influencer_remaining_order_amount;
                   
                    /*Get Influencer settings which is set by admin*/
                    $obj_influencer_settings = $this->InfluencerSettingModel->first();
                        
                    if($obj_influencer_settings)
                    {
                        $arr_influencer_settings = $obj_influencer_settings->toArray();

                        $sales_target            = isset($arr_influencer_settings['sales_target'])?num_format($arr_influencer_settings['sales_target']):0;
                        $reward_amount           = isset($arr_influencer_settings['reward_amount'])?num_format($arr_influencer_settings['reward_amount']):0;

                         /* If $total_order_price >= $sales_target then give reward to influencer which is set by admin*/
                        if($total_order_price >= $sales_target)
                        {
                            /* Calculate carry forward amount */
                            
                            $calculated_amt = $total_order_price;

                            while($calculated_amt > 0)
                            {
                                $calculated_amt = $calculated_amt - $sales_target;

                                if($calculated_amt > 0){
                                    $carry_forward_amt = $calculated_amt;
                                    $cnt++;
                                }
                            }

                            /* Calculate Total Reward Amount for Influencer */
                            $total_reward_amount = $reward_amount * $cnt;

                            /* Fixed input data which will be stored in influencer_rewards table */
                            $input_data['influencer_id']                = $influencer_id;
                            $input_data['reward_amount']                = $total_reward_amount;
                            $input_data['admin_settled_sales_target']   = $sales_target;
                            $input_data['admin_settled_reward_amount']  = $reward_amount;
                            $input_data['current_order_amount']         = $current_order_amount;
                            $input_data['previous_carry_forward_amount']= $influencer_remaining_order_amount;
                            $input_data['total_order_amount']           = $total_order_price;
                            $input_data['used_order_amount']            = $total_order_price - $carry_forward_amt;
                            $input_data['carry_forward_amount']         = $carry_forward_amt;

                            /*Check whether influencer is connected to admin stripe account or not*/
                            $obj_stripe_account_details = $this->StripeAccountDetailsModel
                                                                ->where('user_id',$influencer_id)
                                                                ->first();

                            /* If influencer is not connected to admin stripe account then send email to influencer */
                            if(!$obj_stripe_account_details)
                            {
                                $general_setting_obj = $this->GeneralSettingModel
                                  ->where('data_id','STRIPE_CLIENT_ID')
                                  ->where('type','admin')
                                  ->first();

                                $client_id = isset($general_setting_obj->data_value)?$general_setting_obj->data_value:'';
                                if($client_id!='')
                                {
                                    $connection_response = $this->StripePaymentService->connection_request($influencer_id,$client_id);
                                }

                                /* Store influencer reward into table influencer_rewards with status = 1 (Pending) and also give description that influencer stripe account is not connected with admin stripe account */

                                $input_data['status']                       = '1'; //Pending
                                $input_data['description']                  = 'Influencer stripe account is not connected to '.$this->site_setting_arr['site_name'].' stripe account. We have sent connection request to influencer through email.'; 

                                $this->update_influencer_reward_details($input_data,$arr_orders,$influencer_id,$carry_forward_amt);
                                $this->send_notification_to_influencer($input_data);
                                $this->send_email_notification_to_influencer($input_data);
                            }

                            /* If influencer is connected to admin stripe account then send rewards to influencer */
                            if($obj_stripe_account_details)
                            {
                                $stripe_acc_id = isset($obj_stripe_account_details->stripe_acc_id)?$obj_stripe_account_details->stripe_acc_id:false;

                                if($stripe_acc_id)
                                {
                                    /* Transfer Reward Amount to Influencer Stripe Account */
                                    $arr_transfer_data['amount']      = $total_reward_amount;
                                    $arr_transfer_data['account_id']  = $stripe_acc_id;
                                    $arr_transfer_data['description'] = 'Reward for promo code.';
                                                                    
                                    $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

                                    if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
                                    {

                                        /* If stripe gives any error then store influencer reward into influencer_rewards table with stripe error */

                                        $stripe_error_resp = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';

                                        $input_data['status']          = '3'; //Failed
                                        $input_data['description']     = 'Error occured while sending reward amount through stripe. Stripe error response: '.$stripe_error_resp;

                                        $this->update_influencer_reward_details($input_data,$arr_orders,$influencer_id,$carry_forward_amt);
                                        $this->send_notification_to_influencer($input_data);
                                        $this->send_email_notification_to_influencer($input_data);
                                    }

                                    /* If transfer_response gives other than Error then create entry into influencer reward transaction table */

                                    if(isset($transfer_response) && isset($transfer_response->id) && $transfer_response->id != '')
                                    {
                                        /*Create entry into the influencer_rewards table with stripe transaction detail*/
                                       
                                        $input_data['transaction_id']      = isset($transfer_response->balance_transaction)?$transfer_response->balance_transaction:0;
                                        $input_data['transfer_id']         = isset($transfer_response->id)?$transfer_response->id:0;
                                        $input_data['destination_payment'] = isset($transfer_response->destination_payment)?$transfer_response->destination_payment:0;
                                        $input_data['status']              = '2'; //Success
                                        $input_data['paid_by']             = get_admin_id();
                                        $input_data['description']         = 'Reward sent successfully.';

                                        $this->update_influencer_reward_details($input_data,$arr_orders,$influencer_id,$carry_forward_amt);
                                        $this->send_notification_to_influencer($input_data);
                                        $this->send_email_notification_to_influencer($input_data);
                                    }
                                }
                                else
                                {
                                    $input_data['status']          = '1'; //Pending
                                    $input_data['description']     = 'Unable to find influencer stripe account details. Please verify the influencer stripe account details';

                                    $this->update_influencer_reward_details($input_data,$arr_orders,$influencer_id,$carry_forward_amt);
                                    $this->send_notification_to_influencer($input_data);
                                    $this->send_email_notification_to_influencer($input_data);
                                }
                            }
                        }
                    }
                }

                /*---------------------Influencer Reward Section (End)--------------------------*/

                    DB::commit();
                    
                    if($result)
                    {
                      $response['status']      = 'success';
                      $response['description'] = 'Order tracking details has been added.';

                      return response()->json($response);
                    }
                    else
                    {
                       $response['status']      = 'error';
                       $response['description'] = 'Something went wrong, please try again.';
                       return response()->json($response);
                    }

        }
        catch(Exception $e)
        {   
            DB::rollback();

            $response['status']      = 'error';
            $response['description'] = $e->getMessage();
            return response()->json($response);

        }       
    }

    function update_influencer_reward_details($input_data,$arr_orders,$influencer_id,$carry_forward_amt)
    {
        /*Get all primary ids from array orders*/
        $arr_primary_ids = array_column($arr_orders, 'id');

        $input_data['order_ids'] = isset($arr_primary_ids)?implode(', ', $arr_primary_ids):'';

        $this->InfluencerRewardsModel->create($input_data);

        /* Update is_considered_for_calculation = 1 in customer_transaction table */
        $this->BaseModel->whereIn('id',$arr_primary_ids)
                        ->update(['is_considered_for_calculation'=>1]);

        /* Update influencer remaining order amount in user table */
        $this->UserModel->where('id',$influencer_id)
                        ->update(['influencer_remaining_order_amount'=>$carry_forward_amt]);
        
    }

    function send_notification_to_influencer($input_data)
    {
        /*------------ Send Notification to Influencer (START)-------------*/
            
            $arr_notify_data                 = [];

            $influencer_id = isset($input_data['influencer_id'])?$input_data['influencer_id']:0;

            $admin_id              = get_admin_id();
            $influencer_panel_slug = config('app.project.influencer_panel_slug');
            $view_href             = url($influencer_panel_slug.'/rewards_history');

            $arr_notify_data['from_user_id'] = $admin_id or '';
            $arr_notify_data['to_user_id']   = $influencer_id;

            $status = isset($input_data['status'])?$input_data['status']:0;
            
            $arr_site_setting = get_site_settings(['site_name','website_url']);
            if($status == '1') //Pending
            {
                $arr_notify_data['description']  = isset($input_data['description'])?$input_data['description']:'--';
            }
            else if($status == '2' ) //Success
            {
                $get_reward_amt = isset($input_data['reward_amount'])?$input_data['reward_amount']:0;
                $arr_notify_data['description']  = $arr_site_setting['site_name'].' has sent rewards. Reward Amount:'.$get_reward_amt;
            }
            else if($status  == '3') //Failed
            {
                $arr_notify_data['description']  = isset($input_data['description'])?$input_data['description']:'--';
            }
            else
            {
                $arr_notify_data['description'] = '--';
            }

            $arr_notify_data['title']        = 'Get Rewards';
            $arr_notify_data['type']         = 'influencer';
            $arr_notify_data['link']         = $view_href;


            $this->InfluencerService->save_notification($arr_notify_data);

        /*------------ Send Notification to Influencer (END)  -------------*/
    }


    function send_email_notification_to_influencer($input_data)
    {
        /*------------ Send Email Notification to Influencer (START)-------------*/
            
        $arr_notify_data                 = [];

        $influencer_id = isset($input_data['influencer_id'])?$input_data['influencer_id']:0;

        
        $influencer_panel_slug = config('app.project.influencer_panel_slug');
        $view_href             = url($influencer_panel_slug.'/rewards_history');
        
        $arr_site_setting = get_site_settings(['site_name','website_url']);

        $status = isset($input_data['status'])?$input_data['status']:0;

        if($status == '1') //Pending
        {
            $arr_notify_data['description']  = isset($input_data['description'])?$input_data['description']:'--';
        }
        else if($status == '2' ) //Success
        {
            $get_reward_amt = isset($input_data['reward_amount'])?$input_data['reward_amount']:0;
            $arr_notify_data['description']  = $arr_site_setting['site_name'].' has sent rewards. Reward Amount:'.$get_reward_amt;
        }
        else if($status  == '3') //Failed
        {
            $arr_notify_data['description']  = isset($input_data['description'])?$input_data['description']:'--';
        }
        else
        {
            $arr_notify_data['description'] = '--';
        }
        

        $redirection_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.$view_href.'">View Details</a><br/>';

        $user = Sentinel::findById($influencer_id);
        
        $arr_site_setting = get_site_settings(['site_name','website_url']);
        if(isset($user) && $user)
        {
            $arr_user = $user->toArray();  
            
            $arr_built_content = ['USER_FNAME'           => $arr_user['first_name'],
                                  'APP_NAME'             => $arr_site_setting['site_name'],
                                  'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
                                  'EMAIL_DESCRIPTION'    => isset($arr_notify_data['description'])?$arr_notify_data['description']:''
                                 ];

            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '62';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['user']              = $arr_user;

            $email_status  = $this->EmailService->send_mail($arr_mail_data);
            
        }                

        /*------------ Send Email Notification to Influencer (END)  -------------*/
    }

    public function send_order_ship_mail($email=false,$order_no=false,$comapny=false,$tracking_url=false,$html=false)
    {     
        $user = $this->get_user_details($email);
        
        if(isset($user) && $user)
        {
            $arr_user = $user->toArray();  

            $arr_site_setting = get_site_settings(['site_name','website_url']);
             
            
            $arr_built_content = ['USER_NAME'     => $arr_user['first_name'],
                                  'ORDER_NO'      => isset($order_no)?$order_no:'',
                                  'COMPANY'       => isset($comapny)?$comapny:'',
                                  'TRACKING_URL'  => isset($tracking_url)?$tracking_url:'',
                                  'HTML'          => isset($html)?$html:'',
                                  'SITE_URL'      => $arr_site_setting['site_name'],
                                  'PROJECT_NAME'  => $arr_site_setting['site_name']
                                 ];

            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '53';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['arr_user']              = $arr_user;

            return $arr_mail_data;
            
        }    

        return false;
    }

    public function  get_user_details($email)
    {
        $credentials = ['email' => $email];
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        if($user)
        {
          return $user;
        }
        return false;
    }

    public function get_email($id)
    {
      $email = $this->UserModel->where('id',$id)->pluck('email')->first();

      return $email;
    }

    public function get_customer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_discount');

        // dd($shipCharge,$shipChargeDisount);
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }
}
