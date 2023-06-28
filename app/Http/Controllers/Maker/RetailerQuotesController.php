<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\RetailerModel;
use App\Models\OrderTrackDetailsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\StripeTransactionModel;

use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Models\SiteSettingModel;

use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;


use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;


class RetailerQuotesController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 03 July 2019
    */
    public function __construct(RetailerQuotesModel $RetailerQuotesModel,
    							UserModel $UserModel,
                                TransactionMappingModel $TransactionMappingModel,
                                RetailerModel $RetailerModel,
                                TransactionsModel $TransactionsModel,
                                GeneralService $GeneralService,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                OrderTrackDetailsModel $OrderTrackDetailsModel,
                                StripeTransactionModel $StripeTransactionModel,
                                EmailService $EmailService,
                                CommissionService $CommissionService,
                                HelperService $HelperService,
                                SiteSettingModel $SiteSettingModel			
    							)
    { 
       
    	$this->BaseModel               = $RetailerQuotesModel;                        	
    	$this->UserModel               = $UserModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->TransactionsModel       = $TransactionsModel;
        $this->RetailerModel           = $RetailerModel;
        $this->GeneralService          = $GeneralService;
        $this->OrderTrackDetailsModel  = $OrderTrackDetailsModel;
        $this->RetailerQuotesProductModel  = $RetailerQuotesProductModel;
        $this->StripeTransactionModel  = $StripeTransactionModel;
        $this->EmailService            = $EmailService;
        $this->HelperService           = $HelperService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Customer Orders";
    	$this->module_view_folder      = 'maker.retailer_quotes';
        $this->maker_panel_slug        = config('app.project.maker_panel_slug');
        $this->module_url_path         = url($this->maker_panel_slug.'/retailer_orders');
        $this->SiteSettingModel        = $SiteSettingModel;
        $this->site_setting_obj  = $this->SiteSettingModel->first();
        $this->CommissionService       = $CommissionService;
    
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
        
    }

    public function index(Request $request)
    { 
        $retailer_id = $request->input('retailer_id',null);
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'Customer Orders';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['retailer_id']      = $retailer_id;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_enquiries(Request $request)
    {
        $admin_commission = $this->CommissionService->get_admin_commission();

        //dd($request->input('column_filter'));

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
 
        $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
        $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl 			= DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".
                                                 $prefix_retailer_table.'.store_name,'.
                                                 $prefix_retailer_table.'.dummy_store_name,'.
                                                 $retailer_quotes_details.'.shipping_charge,'.
                                                 $retailer_quotes_details.'.shipping_discount,'.


                                				  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_quotes_details,$retailer_quotes_details_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')

                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.lead_id')


                                // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                              
                                ->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId)

                                // ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',2)
                                // ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',1)

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',0)

                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC')
                                ->groupBy($prefixed_retailer_quotes_tbl.".id");
                                
                                                      
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
            $search_term      = $arr_search_column['q_enquiry_id'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_qutoes = $obj_qutoes->having('dummy_store_name','LIKE', '%'.$search_term.'%');
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

         if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
        }
        

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)   
            {
                //$obj_qutoes = $obj_qutoes->whereNotIn($prefixed_retailer_quotes_tbl.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
                 // Condition added by Harshada On date 31 Aug 2020 Reference by Priyanka mam
                // To search filter by pending
                $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = retailer_transaction.order_no AND `transaction_mapping`.`order_id` = retailer_transaction.id
                        "));       
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
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

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

        if(isset($arr_search_column['admin_commission_status']) && $arr_search_column['admin_commission_status']!="")
        {  

            $search_term = $arr_search_column['admin_commission_status'];
            if($search_term == 0)
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.admin_commission_status','=', NULL);
            }
            else
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.admin_commission_status','=', ''.$search_term.'');
            }            
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
                        ->editColumn('build_action_btn',function($data) use ($current_context,$admin_commission)
                        {
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
                            $is_online = check_is_user_online($data->retailer_id);

                            if($is_online ==true)
                            {
                              $online_status = '<span class="act-online"></span>';
                            }
                            else
                            {
                              $online_status = '<span class="act-offline"></span>';
                            }

                            /*************************************************************************/

                            $ord_wholesale_price = isset($data->total_wholesale_price)?$data->total_wholesale_price:0;

                            $shippingCharges = $this->get_retailer_order_shipping_charges($data->id);
                            
                            $is_freeshipping = is_promocode_freeshipping($data->promo_code);

                            if($is_freeshipping == false)
                            {
                              $ord_wholesale_price = $ord_wholesale_price - $shippingCharges;
                            }

                            $admin_commission_amount = $ord_wholesale_price*($admin_commission / 100);

                            $pay_admin_button = '';

                             if($data->ship_status == 1 && $data->is_direct_payment == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                             {
                                /*$pay_admin_button = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_admin_btn" title="Pay '.$this->site_setting_arr['site_name'].'"  onclick="fillData('.$ord_wholesale_price.','.$admin_commission.','.$admin_commission_amount.','.$data->id.','.$data->maker_id.')" style="'.$is_disabled.'" >Pay '.$this->site_setting_arr['site_name'].'</button>';*/

                                $pay_admin_button = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_admin_btn" title="View Commission"  onclick="fillData('.$ord_wholesale_price.','.$admin_commission.','.$admin_commission_amount.','.$data->id.','.$data->maker_id.')" style="'.$is_disabled.'" >View Commission</button>';
                                
                             }

                            /*************************************************************************/

                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            // $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a> '.$pay_admin_button;

                            /*$build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.'</a>';*/

                            return $build_action = $build_view_action;
                        })
                         ->editColumn('ship_status',function($data) use ($current_context)
                        {
                            $ship_status = get_order_status($data->ship_status);
                            return $ship_status;

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

                            
                        })
                        
                        ->editColumn('admin_commission_status',function($data) use ($current_context)
                        {   
                            $admin_commission_status ='-';
                            $status = '';

                            // return $admin_commission_status = ($data->admin_commission_status == 1)? '<span class="label label-success">Paid</span>': '<span class="label label-success">Pending</span>';

                           /* if($data->is_direct_payment == 1)
                            {

                                if(isset($data->admin_commission_status) && $data->admin_commission_status == 1)
                                {
                                   return $status = '<span class="label label-success">Paid</span>';
                                }
                                else
                                {
                                   return $status = '<span class="label label-success">Pending</span>';
                                }


                            }
                            else
                            {
                                return $status = '--';

                            }*/

                            return $status = '-';

                           


                        });


        $build_result = $json_result->make(true)->getData();
        $build_result->total_amt = $total_amt;
        return response()->json($build_result);
    }

    public function get_export_retailer_orders(Request $request)
    {
        $type  = 'csv'; 
        $admin_commission = $this->CommissionService->get_admin_commission();

        //dd($request->input('column_filter'));

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
 
        $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
        $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".
                                                 $prefix_retailer_table.'.store_name,'.
                                                 $prefix_retailer_table.'.dummy_store_name,'.
                                                 $retailer_quotes_details.'.shipping_charge,'.
                                                 $retailer_quotes_details.'.shipping_discount,'.


                                                  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_quotes_details,$retailer_quotes_details_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')

                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.lead_id')


                                // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                              
                                ->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId)

                                // ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',2)
                                // ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',1)

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',0)

                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC')
                                ->groupBy($prefixed_retailer_quotes_tbl.".id");
                                
                                                      

        $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_id',$loggedInUserId);


        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->all();
              
        if(isset($arr_search_column['q_enquiry_id']) && $arr_search_column['q_enquiry_id']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_id'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

       /* if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_qutoes = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }*/

         if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_qutoes = $obj_qutoes->having('dummy_store_name','LIKE', '%'.$search_term.'%');
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

         if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
        }
        

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)   
            {
                //$obj_qutoes = $obj_qutoes->whereNotIn($prefixed_retailer_quotes_tbl.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
                 // Condition added by Harshada On date 31 Aug 2020 Reference by Priyanka mam
                // To search filter by pending
                $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = retailer_transaction.order_no AND `transaction_mapping`.`order_id` = retailer_transaction.id
                        "));       
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
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

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

        if(isset($arr_search_column['admin_commission_status']) && $arr_search_column['admin_commission_status']!="")
        {  

            $search_term = $arr_search_column['admin_commission_status'];
            if($search_term == 0)
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.admin_commission_status','=', NULL);
            }
            else
            {
                $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.admin_commission_status','=', ''.$search_term.'');
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
            if($value->is_direct_payment == 1)
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
            //$arrayResponseData['Retailer']              = $value->store_name;      
            $arrayResponseData['Customer']              = $value->dummy_store_name;      
            $arrayResponseData['Total Amount']          = $value->total_wholesale_price;
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

        $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
        $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

    	
    	$enquiry_obj = $this->BaseModel->with(['quotes_details.product_details.brand_details',
                                               'user_details.retailer_details',
                                               'maker_details',
                                               'maker_data',
                                               'transaction_mapping',
                                               'stripe_transaction_detail',
                                               'stripe_transaction_data',
                                               'maker_brand_details'=>function($query){
                                                
                                                $query->select('id','user_id','brand_name');
                                        }])
                                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                            $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                                 ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                        })
                                        ->select($prefixed_retailer_quotes_tbl.'.*',$prefixed_transaction_mapping_tbl.'.transaction_status')
  
    								    ->where($prefixed_retailer_quotes_tbl.'.id',$enquiry_id)->first();

        if($enquiry_obj)
    	{
    		$enquiry_arr = $enquiry_obj->toArray();
     
            if ($enquiry_arr['split_order_id'] != '') 
            {

                if($enquiry_arr['is_split_order'] == '1') 
                {
                    $split_order_arr = $this->BaseModel->where('split_order_id',$enquiry_id)->get()->toArray();
                }
                else
                {
                    $main_split_order_no = $this->BaseModel->where('id',$enquiry_arr['split_order_id'])->first();
                }

            }
            elseif($enquiry_arr['is_split_order'] == '1') {

                // $split_order_arr = $this->BaseModel->where('split_order_id',$enquiry_arr['id'])->get()->toArray();
                $split_order_arr = $this->BaseModel->where('split_order_id',$enquiry_id)->get()->toArray();

            }
            
    		if($enquiry_arr['maker_id'] != $loggedInUserId)
			{
				Flash::error('You are not authorize user to access this page.');
    			return redirect()->back();
			}
            $shippingCharges = $this->get_retailer_order_shipping_charges($enquiry_id);
    	}
    	else
    	{
    		Flash::error('Something went wrong, please try again.');
    		return redirect()->back();
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

        /*get order calculation data from helper*/
        if(isset($enquiry_arr['order_no']) && !empty($enquiry_arr['order_no']) &&
                  isset($enquiry_arr['maker_id']) && !empty($enquiry_arr['maker_id']))
        {
            $ordNo = base64_encode($enquiry_arr['order_no']);
            $vendorId = base64_encode($enquiry_arr['maker_id']);

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer');
        } 

    	$this->arr_view_data['enquiry_arr']          = $enquiry_arr;
        $this->arr_view_data['split_order_arr']      = $split_order_arr;
        $this->arr_view_data['main_split_order_no']  = $main_split_order_no;
    	$this->arr_view_data['module_title']         = $this->module_title;
        $this->arr_view_data['page_title']           = 'Order Details';
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['order_shipping_charge'] = $shippingCharges;
        $this->arr_view_data['project_name']          = $this->site_setting_arr['site_name'];
        $this->arr_view_data['tracking_details']     = $tracking_details;
        $this->arr_view_data['tracking_no']          = $tracking_no;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;


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
        $retailer_id = $request->retailer_id;

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
            /*after shipping the order send notification to admin & retailer*/

            $admin_id = get_admin_id();

            //Get maker name 

            $first_name   = isset($user->first_name)?$user->first_name:"";
            $last_name    = isset($user->last_name)?$user->last_name:""; 
            
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $admin_id;

          
            $order_detail_url = url('/admin/retailer_orders/view/'.base64_encode($order_id));

            //$order_url        = '<a href="'.$order_detail_url.'">'.$order_details->order_no.'</a>';
              
            $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name;

            $notification_arr['title']        = 'Order Shipped';
            $notification_arr['type']         = 'admin';
            $notification_arr['link']         = $order_detail_url;


            $this->GeneralService->save_notification($notification_arr);



            /*send to retailer*/

            //Get maker name 
         
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $retailer_id;

          
            $order_detail_url = url('/retailer/my_orders/view/'.base64_encode($order_id));

            //$order_url        = '<a href="'.$order_detail_url.'">'.$order_details->order_no.'</a>';
              
            $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name;

            $notification_arr['title']        = 'Order Shipped';
            $notification_arr['type']         = 'retailer';
            $notification_arr['link']         =  $order_detail_url;


            $this->GeneralService->save_notification($notification_arr);


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
           $retailer_id = $request->input('retailer_id');


           
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
                                                   ->where('maker_id',$maker_id)
                                                   ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s'),'payment_due_date'=>date('Y-m-d H:i:s', strtotime("+30 days"))]);
                }
                else
                {

                    $order_status_update = $this->BaseModel->where('id',$order_id)
                                                   ->where('maker_id',$maker_id)
                                                   ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s')]);
                }
               
            }
            
     
                /*after shipping the order send notification to admin & retailer*/

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


                $this->GeneralService->save_notification($notification_arr);

                /*send to retailer*/

                //Get maker name 
             
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedInUserId;
                $notification_arr['to_user_id']   = $retailer_id;

              
                //$order_detail_url = url('/retailer/my_orders/view/'.base64_encode($order_id));
                 
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

                $VendorStoreName = get_maker_company_name($loggedInUserId);
                  
                $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$VendorStoreName.' Tracking No: '.$tracking_no;

                $notification_arr['title']        = 'Order Shipped';
                $notification_arr['type']         = 'retailer';
                $notification_arr['link']         =  $url;


                $this->GeneralService->save_notification($notification_arr);

                // ---------------- check network connection is persistance -----------------  
                $connected = @fsockopen("www.google.com", 80); 
                $is_conn = "";
                if ($connected){
                  $is_conn = true; 
                  fclose($connected);
                }else{
                 $is_conn = false; 
                }

                if($is_conn == false)
                {
                  DB::rollback();
                  $response['status']      = 'warning';
                  $response['description'] = '...Oops network issue.please try again.';
                  return response()->json($response);
                }

                /*--------------------------------------------------------------------------*/


                /*send shippment mail to the retailer*/

                $retailer_email  = $this->get_email($retailer_id);
                $tracking_url    = "<a href=".$url." target='_blank'>".$tracking_no."</a>";

                $html = 'Your order has been shipped. Order No: ';

                $arr_mail_data   = $this->send_order_ship_mail($retailer_email,$order_no,$company,$tracking_url,$html); 



                $email_status  = $this->EmailService->send_mail($arr_mail_data);


                /*send shippment mail to the admin*/
                $admin_email     = $this->get_email(1);
                $tracking_url    = "<a href=".$url." target='_blank'>".$tracking_no."</a>";

                $html = 'Order has been shipped. Order No: ';


                $arr_mail_data   = $this->send_order_ship_mail($admin_email,$order_no,$company,$tracking_url,$html); 

                $email_status  = $this->EmailService->send_mail($arr_mail_data);


       /*---------------------------------------------------------------------------------*/
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


    public function send_order_ship_mail($email=false,$order_no=false,$comapny=false,$tracking_url=false,$html=false)
    {     
        $user = $this->get_user_details($email);
        
        if(isset($user) && $user)
        {
            $arr_user = $user->toArray();  

            $site_setting_obj = SiteSettingModel::first();
            if($site_setting_obj)
            {
                $site_setting_arr = $site_setting_obj->toArray();            
            }

            $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';
            
            $arr_built_content = ['USER_NAME'     => $arr_user['first_name'],
                                  'ORDER_NO'      => isset($order_no)?$order_no:'',
                                  'COMPANY'       => isset($comapny)?$comapny:'',
                                  'TRACKING_URL'  => isset($tracking_url)?$tracking_url:'',
                                  'HTML'          => isset($html)?$html:'',
                                  'SITE_URL'     => $site_name,
                                  'PROJECT_NAME' => $site_name
                                 ];

            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '53';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['user']              = $arr_user;
            $arr_mail_data['arr_user']           = $arr_user;

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

    public function get_retailer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }
}
