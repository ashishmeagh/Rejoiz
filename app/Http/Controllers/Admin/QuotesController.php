<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetailerQuotesModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\MakerModel;
use App\Models\RetailerModel;
use App\Models\StripeTransactionModel;
use App\Models\UserStripeAccountDetailsModel;

use App\Common\Services\ReportService;
use App\Common\Services\GeneralService;
use App\Common\Services\StripePaymentService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;

use Sentinel;
use DB;
use Datatables;
use Flash;
use Excel;
use Validator;
use DateTime;
use Carbon\Carbon;

class QuotesController extends Controller
{
    /*
    | Author : Shital Vijay More
    | Date   : 16 July 2019
    */
    public function __construct(RetailerQuotesModel $RetailerQuotesModel,
                                UserModel $UserModel,
                                MakerModel $MakerModel,
                                TransactionMappingModel $TransactionMappingModel,
                                ReportService $ReportService,
                                GeneralService $GeneralService,
                                CommissionService $CommissionService,
                                StripePaymentService $StripePaymentService,
                                HelperService $HelperService,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                StripeTransactionModel $StripeTransactionModel,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                RetailerModel $RetailerModel

                                )
    {
        $this->BaseModel               = $RetailerQuotesModel;
        $this->ReportService           = $ReportService;    
        $this->UserModel               = $UserModel;
        $this->MakerModel              = $MakerModel;
        $this->RetailerModel           = $RetailerModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->StripeTransactionModel  = $StripeTransactionModel;
        $this->UserStripeAccountDetailsModel  = $UserStripeAccountDetailsModel;
        $this->arr_view_data           = [];
        $this->module_title            = "Orders By Customer";
        $this->module_view_folder      = 'admin.retailer_quotes';
        $this->maker_panel_slug        = config('app.project.admin_panel_slug');
        $this->module_url_path         = url($this->maker_panel_slug.'/retailer_orders');
        $this->module_url         = url('/');
        $this->product_default_img_path = config('app.project.img_path.product_default_images');
        $this->GeneralService          = $GeneralService;                        
        $this->StripePaymentService    = $StripePaymentService;
        $this->CommissionService              = $CommissionService;   
        $this->HelperService                  = $HelperService;                     
    }

    public function index(Request $request)
    {

        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Orders By Customer';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_url']      = $this->module_url;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_enquiries(Request $request)
    {
       $admin_commission = $this->CommissionService->get_admin_commission();

       $from_date = $request->input('from_date');
       $to_date   = $request->input('to_date'); 
      
      $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
      $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

      $user_tbl_name                = $this->UserModel->getTable();
      $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

      $maker_tbl_name               = $this->MakerModel->getTable();
      $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

      $retailer_table               = $this->RetailerModel->getTable();
      $prefixed_retailer_tbl        = DB::getTablePrefix().$this->RetailerModel->getTable();

      $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
      $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

      $transaction_mapping_table     = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
      $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();


      $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".

                                                $prefixed_transaction_mapping_tbl.".id as tid,".
                                                $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".
                                                $prefixed_stripe_transaction_table.".status,".

                                                $prefixed_maker_tbl.'.user_id as mid,'.
                                                $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.
                                                $retailer_quotes_details.'.shipping_charge'

                                            ))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_retailer_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_tbl.'.user_id')

                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')
                                ->leftjoin($retailer_quotes_details,$retailer_quotes_details_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')


                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.lead_id')

        /*                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_stripe_transaction_table.'.received_by')
        */ 

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=','2')
                                
                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC')
                                ->groupBy($prefixed_retailer_quotes_tbl.".id");


        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');

             
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            $date         = DateTime::createFromFormat('m/d/Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }   

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_qutoes = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->having($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term = $arr_search_column['q_total_retail_cost'];
            $search_term = intval($search_term);
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term  = $arr_search_column['q_total_wholesale_cost'];
            $search_term  = intval($search_term);

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }     

        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id);
        } 

        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_vendor_payment']) && $arr_search_column['q_vendor_payment']!="")
        {
            $search_term = $arr_search_column['q_vendor_payment'];
           

            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_retailer_quotes_tbl.'.id',DB::table($prefixed_stripe_transaction_table)->pluck('lead_id'));
            }
            else
            {
               $obj_qutoes  = $obj_qutoes->where($prefixed_stripe_transaction_table.'.status','LIKE', '%'.$search_term.'%');
            }
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
                                `transaction_mapping`.`order_no` = retailer_transaction.order_no AND `transaction_mapping`.`order_id` = retailer_transaction.id
                        "));       
                    });                                                   
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }
        }


        if(isset($arr_search_column['q_vendor_payment_status']) && $arr_search_column['q_vendor_payment_status']!="")
        {
            $search_term  = $arr_search_column['q_vendor_payment_status'];

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','=', '0');

            
            if($search_term == 0){
             
                $obj_qutoes   = $obj_qutoes->where(function($query) use($prefixed_retailer_quotes_tbl,$search_term)
                    {
                         return $query->orWhere($prefixed_retailer_quotes_tbl.'.maker_commission_status','LIKE', '%'.$search_term.'%')
                        ->orWhereNull($prefixed_retailer_quotes_tbl.'.maker_commission_status');

                }); 
            }
            else
                $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_commission_status','LIKE', '%'.$search_term.'%');

         //   dd($obj_qutoes);
        }   



        if(isset($arr_search_column['q_admin_payment_status']) && $arr_search_column['q_admin_payment_status']!="")
        {
            $search_term  = $arr_search_column['q_admin_payment_status'];

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','=', '1');   
            
                
            if($search_term == 0){
                $obj_qutoes   = $obj_qutoes->where(function($query) use($prefixed_retailer_quotes_tbl,$search_term)
                    {
                         return $query->orWhereNull($prefixed_retailer_quotes_tbl.'.admin_commission_status')->orWhere($prefixed_retailer_quotes_tbl.'.admin_commission_status','=', $search_term)
                        ;

                });

            // dd($obj_qutoes);
            }
            else
                $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.admin_commission_status','LIKE', '%'.$search_term.'%');
        }   

        /*search data from  from date and to date*/
        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") && (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];




            $from_date              = DateTime::createFromFormat('m/d/Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m/d/Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

         /*   $search_term_from_date  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));
            $search_term_to_date    = date('Y-m-d', strtotime($arr_search_column['q_to_date']));*/

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $from_date);

        } 
        
      //  echo "<pre> ===>"; print_r($obj_qutoes->get()->toArray()); exit;
        $total_amt = 0;      
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_wholesale_price;
        }, 0);

        $current_context = $this;

        $json_result  = Datatables::of($obj_qutoes);
        
        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            //return date('d-M-Y',strtotime($data->created_at));
                            return us_date_format($data->created_at);

                        })
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            return $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'N/A'; 
                     
                        })

                        ->editColumn('company_name',function($data) use ($current_context){
                            return $company_name = isset($data->company_name)?$data->company_name:'N/A';
                        })

                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                            $products_arr = get_quote_products($id);

                            return $product_html = $this->GeneralService->order_products_for_list($id,$order_no,$products_arr);
                            
                        })
                        ->editColumn('vendor_payment_status',function($data) use ($current_context)
                        {   
                            
                            /*if($data->stripe_trxn_status == '2')
                            {
                               $vendor_payment_status = '<span class="label label-success">Paid</span>';
                            }
                            else if($data->stripe_trxn_status == '3')
                            {
                                $vendor_payment_status = '<span class="label label-warning">Failed</span>';
                            }
                            elseif($data->stripe_trxn_status == '1') 
                            {
                               $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                            }
                            else
                            {
                                $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                            }*/
                            $vendor_payment_status = '-';
                            return $vendor_payment_status;
                     
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
                        ->editColumn('build_action_btn',function($data) use ($current_context,$admin_commission)
                        {   

                            $admin_commission = $data->admin_commission;
                            $pay_vendor_button = $generate_invoice = '';

                            if($data->stripe_trxn_status == '2')
                            {
                               $is_disabled = 'display:none';
                            }
                            else
                            {
                               $is_disabled='display:block';
                            }

                            $unread_message_count = get_quote_unread_messages_count($data->id,'admin');
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }

                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);

                            $shippingCharges = $this->get_retailer_order_shipping_charges($data->id);

                            $ord_wholesale_price = isset($data->total_wholesale_price)?$data->total_wholesale_price:0;

                            $is_freeshipping = is_promocode_freeshipping($data->promo_code);

                            if($is_freeshipping == false)
                            {
                              $ord_wholesale_price = $ord_wholesale_price - $shippingCharges;
                            }

                            // $ord_wholesale_price = $ord_wholesale_price - $data->shipping_charge;
                            // $ord_wholesale_price = $ord_wholesale_price - $shippingCharges;

                             $admin_commission_amount = $ord_wholesale_price*($admin_commission / 100);

                             $vendor_pay_amount = $ord_wholesale_price - $admin_commission_amount;


                             if($data->ship_status == 1 && $data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                             { 

                                /*$pay_vendor_button = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$data->maker_id.','.$data->id.')" style="'.$is_disabled.'" >Pay Vendor</button>';*/

                                $pay_vendor_button = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="View Vendor Commission"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$data->maker_id.','.$data->id.')" style="'.$is_disabled.'" >View Vendor Commission</button>';
                             }

                             if($data->is_direct_payment == 1 && $data->admin_commission_status == 0 && $data->ship_status == 1)
                             {
                             
                                 /* $generate_invoice = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip" id="generate_inoice" onclick="generate_invoice('.$data->id.')" title="Generate Invoice" >Generate Invoice</button>';*/
                             }



                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a> '.$pay_vendor_button . $generate_invoice;

                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-circle btn-danger btn-outline show-tooltip" href="'.$chat_href.'">Chat'.$unread_msg_count.
                             ' </a>';

                             if($data->order_cancel_status == 1)
                             {
                               /* $build_view_action .= '<a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-circle btn-success btn-outline show-tooltip" href="javascript::void(0)">Cancel Requested</a>';*/
                             }

                            return $build_action = $build_view_action;
                        });

        $build_result = $json_result->make(true)->getData();
        //dd($build_result);
       // echo "<pre> ==>"; print_r($build_result); exit;

        $build_result->total_amt = $total_amt;       
        return response()->json($build_result);
    }

    public function get_export_retailer_orders(Request $request)
    {
        $admin_commission = $this->CommissionService->get_admin_commission();

       $from_date = $request->input('from_date');
       $to_date   = $request->input('to_date'); 

       // dd($admin_commission,$from_date,$to_date);
      
      $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
      $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

      $user_tbl_name                = $this->UserModel->getTable();
      $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

      $maker_tbl_name               = $this->MakerModel->getTable();
      $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

      $retailer_table               = $this->RetailerModel->getTable();
      $prefixed_retailer_tbl        = DB::getTablePrefix().$this->RetailerModel->getTable();

      $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
      $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

      $transaction_mapping_table     = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
      $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();


      $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".

                                                $prefixed_transaction_mapping_tbl.".id as tid,".
                                                $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".
                                                $prefixed_stripe_transaction_table.".status,".

                                                $prefixed_maker_tbl.'.user_id as mid,'.
                                                $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.
                                                $retailer_quotes_details.'.shipping_charge'

                                            ))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_retailer_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_tbl.'.user_id')

                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')
                                ->leftjoin($retailer_quotes_details,$retailer_quotes_details_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')


                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.lead_id')

        /*                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_stripe_transaction_table.'.received_by')
        */ 

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                })
                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=','0')
                                
                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC')
                                ->groupBy($prefixed_retailer_quotes_tbl.".id");

        $arr_search_column = $request->all();


        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            $date         = DateTime::createFromFormat('m-d-Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }   

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_qutoes = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->having($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term  = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }     

        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id);
        } 

        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_vendor_payment']) && $arr_search_column['q_vendor_payment']!="")
        {
            $search_term = $arr_search_column['q_vendor_payment'];
           

            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_retailer_quotes_tbl.'.id',DB::table($prefixed_stripe_transaction_table)->pluck('lead_id'));
            }
            else
            {
               $obj_qutoes  = $obj_qutoes->where($prefixed_stripe_transaction_table.'.status','LIKE', '%'.$search_term.'%');
            }
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
                                `transaction_mapping`.`order_no` = retailer_transaction.order_no AND `transaction_mapping`.`order_id` = retailer_transaction.id
                        "));       
                    });                                                   
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }
        }


        if(isset($arr_search_column['q_vendor_payment_status']) && $arr_search_column['q_vendor_payment_status']!="")
        {
            $search_term  = $arr_search_column['q_vendor_payment_status'];

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','=', '0');

            
            if($search_term == 0){
             
                $obj_qutoes   = $obj_qutoes->where(function($query) use($prefixed_retailer_quotes_tbl,$search_term)
                    {
                         return $query->orWhere($prefixed_retailer_quotes_tbl.'.maker_commission_status','LIKE', '%'.$search_term.'%')
                        ->orWhereNull($prefixed_retailer_quotes_tbl.'.maker_commission_status');

                }); 
            }
            else
                $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_commission_status','LIKE', '%'.$search_term.'%');

         //   dd($obj_qutoes);
        }   



        if(isset($arr_search_column['q_admin_payment_status']) && $arr_search_column['q_admin_payment_status']!="")
        {
            $search_term  = $arr_search_column['q_admin_payment_status'];

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.is_direct_payment','=', '1');   
            
                
            if($search_term == 0){
                $obj_qutoes   = $obj_qutoes->where(function($query) use($prefixed_retailer_quotes_tbl,$search_term)
                    {
                         return $query->orWhereNull($prefixed_retailer_quotes_tbl.'.admin_commission_status')->orWhere($prefixed_retailer_quotes_tbl.'.admin_commission_status','=', $search_term)
                        ;

                });

            // dd($obj_qutoes);
            }
            else
                $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.admin_commission_status','LIKE', '%'.$search_term.'%');
        }   

        /*search data from  from date and to date*/
        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") && (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];

            $from_date              = DateTime::createFromFormat('m/d/Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m/d/Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

         /*   $search_term_from_date  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));
            $search_term_to_date    = date('Y-m-d', strtotime($arr_search_column['q_to_date']));*/

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

            $vendor_payment_status = '-';
            /*if($value->is_direct_payment != 1)
            {
                $vendor_payment_status = 'Pending';
                if($value->maker_commission_status == 1 || $value->maker_commission_status==null)
                {
                  $vendor_payment_status =  'Pending';
                }else
                {
                  $vendor_payment_status = 'Paid';
                }
            }
            else
            {
                $vendor_payment_status = '--';
            } */ 


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
    
            $admin_status = '-';
           /* if($value->is_direct_payment != 0)
            {
                $admin_status = 'Paid';
                if($value->admin_commission_status == 1) 
                {
                    $admin_status =  'Paid';
                  }
                else
                {
                    $admin_status = 'Pending';
                }
            }
            else
            {
                $admin_status = '--';
            } */ 


            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            $arrayResponseData['Customer']              = $value->store_name;      
            $arrayResponseData['Vendor']                = $value->company_name;      
            $arrayResponseData['Total Amount ($)']      = $value->total_wholesale_price;
            $arrayResponseData['Shipping Status']       = $shipping_status;
            $arrayResponseData['Customer Payment Status'] = $payment_status;
            $arrayResponseData['Vendor Payment Status']   = $vendor_payment_status;
            $arrayResponseData['Admin Payment Status']    = $admin_status;
            $arrayResponseData['Payment Type']          = $payment_type;
            
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

    public function view($enquiry_id)
    {

        $enquiry_id  = base64_decode($enquiry_id);
        $enquiry_arr = $split_order_arr = $main_split_order_no = $arr_stripe_account_details = $orderCalculationData = [];

        

        $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
        $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;


        $enquiry_obj = $this->BaseModel->with(['quotes_details.product_details.brand_details',
                                               'maker_details',
                                               'maker_data',
                                               'transaction_mapping',
                                               'user_details',
                                               'user_details.retailer_details',
                                               'stripe_transaction_detail',
                                               'stripe_transaction_data'
                                              ])
                                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                            $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                                 ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                        })
                                        ->select($prefixed_retailer_quotes_tbl.'.*',$prefixed_transaction_mapping_tbl.'.transaction_status')
                                        ->where($prefixed_retailer_quotes_tbl.'.id',$enquiry_id)
                                        ->first();                               
        

        if($enquiry_obj)
        {
            $enquiry_arr = $enquiry_obj->toArray();
            
            if($enquiry_arr['split_order_id'] != '')
            {

                $main_split_order_no = $this->BaseModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping'])
                                                 ->where('id',$enquiry_arr['split_order_id'])->first();

            }
            elseif ($enquiry_arr['is_split_order'] == '1')
            {

                $split_order_arr = $this->BaseModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping'])
                                                 ->where('split_order_id',$enquiry_arr['id'])->get()->toArray(); 
            }

            $shippingCharges = $this->get_retailer_order_shipping_charges($enquiry_id);


            /*end*/

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
      
       
        $this->arr_view_data['enquiry_arr']         = $enquiry_arr;
        $this->arr_view_data['module_title']        = $this->module_title;
    
        $this->arr_view_data['page_title']          = 'Order Details';
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        $this->arr_view_data['split_order_arr']     = $split_order_arr;
        $this->arr_view_data['main_split_order_no'] = $main_split_order_no;
        $this->arr_view_data['order_shipping_charge'] = $shippingCharges;
        $this->arr_view_data['tracking_details']    = $tracking_details;
        $this->arr_view_data['tracking_no']         = $tracking_no;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

     public function generate_report(Request $request)
    {
        $request     = $request->all();

        $arr_rules   = [
                        'order_from_date'=>'required',
                        'order_to_date'  =>'required'
                       ];

        $validator   = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
           Flash::error('Form validations failed, please check form fields.');
           return redirect()->back();
        } 
                    
       else
       { 

            if(isset($request))
            {
                $from_date = date("m-d-Y",strtotime($request['order_from_date']));
                $to_date   = date("m-d-Y",strtotime($request['order_to_date']));

                $from_date = $from_date.' 00:00:00';
                $to_date   = $to_date.' 23:59:59';

            }
            $enquiry_obj   = $this->BaseModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping','user_details'])
                                ->whereBetween('created_at',array($from_date,$to_date))
                                ->get();                               
            

            if($enquiry_obj)
            {
                $enquiry_arr = $enquiry_obj->toArray();
            }
            
            $this->ReportService->order_report($enquiry_arr)->download();
        }  
    }


    public function download_report()
    { 
        $from_date = \Request::segment(4);
        $to_date   = \Request::segment(5); 

        if($from_date == "" || $to_date == "")
        {
          Flash::error('Form validation failed, please check form fields.');
          return redirect()->back();
        }

        $from_date = $from_date.' 00:00:00';
        $to_date   = $to_date.' 23:59:59';
        
        $enquiry_obj = $this->BaseModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping','user_details'])
                            ->whereBetween('created_at',array($from_date,$to_date))
                            ->get();                               
        

        if($enquiry_obj)
        {
            $enquiry_arr = $enquiry_obj->toArray();
          
        }
      
        $order_data = [];
        $order      = [];

        foreach ($enquiry_arr as $key => $value) {

           $order_data['Order Id']      = $value['order_no'];
           $order_data['Retailer Name'] = $value['user_details']['first_name'].' '.$value['user_details']['last_name'];
           $order_data['Company Name']  = $value['maker_data']['company_name'];
           $order_data['Order Date']    = us_date_format($value['created_at']);

           $order_data['Total Price']   = number_format((float)$value['total_wholesale_price'], 2, '.', '');

           if($value['transaction_mapping']['transaction_status']==2)
           {
            $order_data['Transaction Status'] = "Paid";
           }
           elseif($value['transaction_mapping']['transaction_status'] ==3)
           {
            $order_data['Transaction Status'] = "Failed";
           }
           else
           {
            $order_data['Transaction Status'] = "Pending";
           }
           
          if($value['ship_status']=='0')
          {
            $order_data['Shipping Status']    = "Pending";
          }
          elseif ($value['ship_status']=='1')
          {
            $order_data['Shipping Status']    = "Shipped";
          }
          else
          {
            $order_data['Shipping Status']    = "Failed";
          }
           $order_data['Shipping Address']    = $value['shipping_addr'].','.$value['shipping_addr_zip_code'];
           $order_data['Billing Address']     = $value['billing_addr'].','.$value['billing_addr_zip_code'];

           array_push($order,$order_data);

           }
              
            $this->ReportService->order_report($order);
    }

    public function get_retailer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

    public function store_payment_proof(Request $request)
    {
        dd($request->file());
    }
}
