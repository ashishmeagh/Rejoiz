<?php 

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;    
use App\Models\MakerModel;    
use App\Models\TransactionMappingModel;    
use App\Models\RetailerModel;    
use App\Models\UserModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;
use App\Models\StripeTransactionModel;
use App\Models\StripeBulkTransactionModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\SalesManagerModel;
use App\Models\GeneralSettingModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Common\Services\EmailService;
use App\Common\Services\orderDataService;

use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;

use Sentinel;
use Validator;
use DB;
use DateTime;
use Excel;
use PDF;

class LeadsController extends Controller
{
  public function __construct(RepresentativeLeadsModel $representative_leads,
                                RepresentativeProductLeadsModel $representative_product_leads,
                                MakerModel $MakerModel,
                                SalesManagerModel $SalesManagerModel,
                                GeneralSettingModel $GeneralSettingModel,
                                TransactionMappingModel $TransactionMappingModel,
                                RetailerModel $RetailerModel,
                                StripeTransactionModel $StripeTransactionModel,
                                StripeBulkTransactionModel $StripeBulkTransactionModel,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                StripePaymentService $StripePaymentService,
                                GeneralService $GeneralService,
                                orderDataService $orderDataService,
                                RetailerQuotesModel $RetailerQuotesModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                UserModel $user,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                EmailService $EmailService,
                                HelperService $HelperService,
                                CommissionService $CommissionService
                              )
    {   
      $this->arr_view_data      = [];
      $this->module_title       = "Orders by Reps / Sales Manager";
      $this->module_view_folder = 'admin.representative_leads'; 
      $this->maker_panel_slug   = config('app.project.admin_panel_slug');
      $this->module_url_path    = url($this->maker_panel_slug.'/leads');
      $this->module_url         = url('/');

      $this->RepresentativeLeadsModel        = $representative_leads;
      $this->RepresentativeProductLeadsModel = $representative_product_leads;
      $this->MakerModel                      = $MakerModel;
      $this->TransactionMappingModel         = $TransactionMappingModel;
      $this->RetailerModel                   = $RetailerModel;
      $this->UserModel                       = $user;
      $this->UserStripeAccountDetailsModel   = $UserStripeAccountDetailsModel;
      $this->RetailerQuotesModel             = $RetailerQuotesModel;
      $this->CustomerQuotesModel             = $CustomerQuotesModel;
      $this->StripeTransactionModel          = $StripeTransactionModel;
      $this->StripeBulkTransactionModel      = $StripeBulkTransactionModel;
      $this->SalesManagerModel               = $SalesManagerModel;
      $this->GeneralSettingModel             = $GeneralSettingModel;
      $this->StripeAccountDetailsModel       = $StripeAccountDetailsModel;
      $this->StripePaymentService            = $StripePaymentService;
      $this->loginuserid                     = Sentinel::check();
      $this->GeneralService                  = $GeneralService;
      $this->orderDataService                = $orderDataService;
      $this->EmailService                    = $EmailService;   
      $this->CommissionService               = $CommissionService; 
      $this->HelperService                   = $HelperService;

      $this->retailer_id = 0;
       
    }

    public function index(Request $request)
    {  
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Orders by Reps / Sales Manager';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_url']      = $this->module_url;
       
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_leads(Request $request)
    {
      

      $admin_commission = $this->CommissionService->get_admin_commission();

      // $representative_commission            = $this->CommissionService->get_representative_commission();

      $representative_leads_tbl_name        = $this->RepresentativeLeadsModel->getTable();        
      $prefixed_representative_leads_tbl    = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

      $sales_manager_tbl_name                = $this->SalesManagerModel->getTable();        
      $prefixed_sales_manager_tbl            = DB::getTablePrefix().$this->SalesManagerModel->getTable();

      $representative_leads_pro_tbl_name     = $this->RepresentativeProductLeadsModel->getTable();        
      $prefixed_representative_leads_pro_tbl = DB::getTablePrefix().$this->RepresentativeProductLeadsModel->getTable();

      $maker_tbl_name               = $this->MakerModel->getTable();
      $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

      $retailer_table               = $this->RetailerModel->getTable();
      $prefixed_retailer_tbl        = DB::getTablePrefix().$this->RetailerModel->getTable();

      $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $stripe_transaction_table          = $this->StripeTransactionModel->getTable();
      $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

      $user_table        =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;


      $obj_products = DB::table($representative_leads_tbl_name)
                                ->select(DB::raw($prefixed_representative_leads_tbl.".*,".  
                                                 
                                                 $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.
                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                     
                                                ))                                
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_leads_tbl.'.representative_id')

                                //->leftjoin($prefixed_sales_manager_tbl,$prefixed_sales_manager_tbl.'.user_id','=',$user_table.'.id')

                                //->leftjoin($user_table.' as SM','SM.id','=',$prefixed_sales_manager_tbl.'.user_id')

                                ->leftjoin($prefixed_retailer_tbl,$prefix_user_table.'.id','=',$prefixed_retailer_tbl.'.user_id')


                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_representative_leads_tbl.'.id','=',$prefixed_representative_leads_tbl.'.order_no')

                                ->where($prefixed_representative_leads_tbl.'.is_confirm',1)
                                
                                ->where($prefixed_representative_leads_tbl.'.representative_id','!=' ,0)

                                ->where($prefixed_representative_leads_tbl.'.order_cancel_status','!=',2)

                                ->orderBy($prefixed_representative_leads_tbl.".id",'DESC');

                                $obj_sales_products = DB::table($representative_leads_tbl_name)
                                ->select(DB::raw($prefixed_representative_leads_tbl.".*,".  
                                                 
                                                 $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.
                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                     
                                                ))                                
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_leads_tbl.'.sales_manager_id')


                                ->leftjoin($prefixed_retailer_tbl,$prefix_user_table.'.id','=',$prefixed_retailer_tbl.'.user_id')


                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_representative_leads_tbl.'.id','=',$prefixed_representative_leads_tbl.'.order_no')

                                ->where($prefixed_representative_leads_tbl.'.is_confirm',1)
                                ->where($prefixed_representative_leads_tbl.'.sales_manager_id','!=' ,0)

                                ->where($prefixed_representative_leads_tbl.'.order_cancel_status','!=',2)
                                ->where($prefixed_representative_leads_tbl.'.maker_id','!=',0)

                                ->orderBy($prefixed_representative_leads_tbl.".id",'DESC'); 

                                                    
                                
        
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.retailer_id',$retailer_id);
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.retailer_id',$retailer_id);
        }    

        if(isset($arr_search_column['q_lead_id']) && $arr_search_column['q_lead_id']!="")
        {
            $search_term      = $arr_search_column['q_lead_id'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.order_no','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term = $arr_search_column['q_username'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_products->where($prefixed_representative_leads_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');

            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_order_rep_sales_name']) && $arr_search_column['q_order_rep_sales_name']!="")
        {
            $search_term = $arr_search_column['q_order_rep_sales_name'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.description','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_store_name']) && $arr_search_column['q_store_name']!="")
        {
            $search_term  = $arr_search_column['q_store_name'];
            $obj_products = $obj_products->where($prefixed_retailer_tbl.'.store_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_retailer_tbl.'.store_name','LIKE', '%'.$search_term.'%');
        } 
        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_products = $obj_products->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }        

       /* if(isset($arr_search_column['q_tot_wholesale']) && $arr_search_column['q_tot_wholesale']!="")
        {
            $search_term      = $arr_search_column['q_tot_wholesale'];
            
            $obj_products     = $obj_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
        }*/
        
        if(isset($arr_search_column['q_tot_commi_less_wholesale']) && $arr_search_column['q_tot_commi_less_wholesale']!="")
        {
            $search_term      = $arr_search_column['q_tot_commi_less_wholesale'];
            
            $obj_products     = $obj_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
            $obj_sales_products     = $obj_sales_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
        }
  
        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');

            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
        }

        if(isset($arr_search_column['q_shipping_status']) && $arr_search_column['q_shipping_status']!="")
        {
            $search_term      = $arr_search_column['q_shipping_status'];
            
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_vendor_payment']) && $arr_search_column['q_vendor_payment']!="")
        {
            $search_term = $arr_search_column['q_vendor_payment'];
            
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.maker_commission_status','=',$search_term);

            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.maker_commission_status','=',$search_term);

        }

        if(isset($arr_search_column['q_rep_payment']) && $arr_search_column['q_rep_payment']!="")
        {
            $search_term = $arr_search_column['q_rep_payment'];
           
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.rep_commission_status','=',$search_term);

            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.sales_manager_commission_status','=',$search_term);
        }

        
        
        if(isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="" && isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!="")
        {
            

            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];
            $from_date              = DateTime::createFromFormat('m/d/Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m/d/Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');
        
            $obj_products   = $obj_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '<=', $to_date);
            $obj_products   = $obj_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '>=', $from_date);

            $obj_sales_products   = $obj_sales_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '<=', $to_date);
            $obj_sales_products   = $obj_sales_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '>=', $from_date);


        }

     
     

        $obj_products = $obj_products->union($obj_sales_products);
        
        $obj_products = $obj_products->orderBy("id",'DESC');
        //dd($obj_products->get()->toArray());
        $total_amt = 0;      
        $total_amt =array_reduce($obj_products->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_wholesale_price;
        }, 0);

        $current_context = $this;

        $json_result  = \Datatables::of($obj_products);
            
        /* Modifying Columns */
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            //return $formated_date = format_date($data->created_at);
                            return $formated_date = us_date_format($data->created_at);
                        })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   

                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                            $products_arr = get_lead_products($id,$order_no);

                            
                            return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);

                        })

                        ->editColumn('user_name',function($data) use ($current_context)
                        {   
                            return isset($data->user_name)?$data->user_name:'-';
                        })

                         ->editColumn('ship_status',function($data) use ($current_context)
                        {   
                           if($data->is_split_order == '1') 
                           {
                            return $ship_status = '--';
                           }
                           else
                           {

                              if($data->ship_status == '1')
                              {
                                 $ship_status = '<span class="label label-success">Shipped</span>';
                              }
                              else
                              {
                                  $ship_status = '<span class="label label-warning">Pending</span>';
                              }

                            }

                            return $ship_status;
                     
                        })

                        ->editColumn('vendor_payment_status',function($data) use ($current_context)
                        {   //dd($data);
                            if($data->maker_commission_status == '1')
                            {
                               $vendor_pauyment_status = '<span class="label label-success">Paid</span>';
                            }
                            else
                            {
                                $vendor_pauyment_status = '<span class="label label-warning">Pending</span>';
                            }

                            return $vendor_pauyment_status;
                     
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

                         ->editColumn('rep_payment_status',function($data) use ($current_context)
                        {   
                              $rep_payment_status = '-';
                              
                            /* if($data->is_split_order == '1') 
                             {
                              return $rep_payment_status = '--';
                             }
                             else
                             {
                                if($data->rep_commission_status == '1' || $data->sales_manager_commission_status == '1')
                                {
                                   $rep_payment_status = '<span class="label label-success">Paid</span>';
                                }
                                else
                                {
                                    $rep_payment_status = '<span class="label label-warning">Pending</span>';
                                }
                             }*/


                            return $rep_payment_status;
                     
                        })


                        ->editColumn('build_action_btn',function($data) use ($admin_commission)
                        {   
                            $admin_commission = $data->admin_commission;

                            $pay_vendor_button = $pay_sales_man_commission = $pay_commition = $is_disabled = $rep_pay_btn = $pay_admin_button = $generate_invoice = '';
                            $sale_manager_pay_btn = 'display:none';
                           
                            if($data->maker_commission_status == '1')
                            {
                               $is_disabled = 'display:none';
                            }
                            else
                            {
                               $is_disabled='display:inline-block';
                            }

                            if($data->rep_commission_status == '1')
                            {
                               $rep_pay_btn = 'display:none';

                            }
                            else
                            { 
                                if($data->sales_manager_id == 0)
                                {
                                  $rep_pay_btn = 'display:inline-block';
                                }
                                else
                                {
                                   $sale_manager_pay_btn = 'display:inline-block';
                                   $rep_pay_btn = 'display:none';

                                   if($data->sales_manager_commission_status == '1')
                                   {
                                     $sale_manager_pay_btn = 'display:none';
                                   }
                                }

                            }

                           /* if ($data->sales_manager_id != 0) {

                              $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                            }
                            else
                            {
                              $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                            }*/

                            $representative_commission = $data->rep_sales_commission;
                            //get unread messages count
                            $representative_commission = isset($representative_commission)?$representative_commission:0.00;
                            $unread_message_count = get_lead_unread_messages_count($data->id,'admin');
                            
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

                            $ord_wholesale_price = isset($data->total_wholesale_price)?$data->total_wholesale_price:0;

                            $is_freeshipping = is_promocode_freeshipping($data->promo_code);

                            if($is_freeshipping == false)
                            {
                              $ord_wholesale_price = $ord_wholesale_price - $data->total_shipping_charges;
                            }

                            $admin_commission_amount = $ord_wholesale_price*($admin_commission / 100);

                            $vendor_pay_amount = $ord_wholesale_price - $admin_commission_amount;

                            $representative_pay_amount = $admin_commission_amount * ($representative_commission / 100);

                            if($data->ship_status == '1')
                            {
                              if($data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                              {
                                /* $pay_vendor_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$data->maker_id.','."'".$data->order_no."'".','.$data->id.')" style="'.$is_disabled.'" >Pay Vendor</button>';*/

                                 $pay_vendor_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="View Vendor Commission"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$data->maker_id.','."'".$data->order_no."'".','.$data->id.')" style="'.$is_disabled.'" >View Vendor Commission</button>';

                               /*$pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->id.')" >Pay Representative</button>';*/

                               $pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Reps Commission"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->id.')" >View Reps Commission</button>';

                              /* $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->id.')" >Pay Sales Manager</button>';*/

                               $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Sales Commission"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->id.')" >View Sales Commission</button>';
                              }
                           
                            }

                            /* Pay representative commission if order payment type is direct pay\ment */
                            if($data->is_direct_payment == 1 && $data->admin_commission_status == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                            {

                              /* $pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->id.')" >Pay Representative</button>';*/

                               $pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Reps Commission"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->id.')" >View Reps Commission</button>';

                               /*$pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->id.')" >Pay Sales Manager</button>';*/

                               $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Sales Commission"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->id.')" >View Sales Commission</button>';
                            }



                             if($data->is_direct_payment == 1 && $data->admin_commission_status == 0 && $data->ship_status == 1)
                             {
                               /* $generate_invoice = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip" id="generate_inoice" onclick="generate_invoice('.$data->id.')" title="Generate Invoice" >Generate Invoice</button>';
*/

                             }
                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a> '.$pay_vendor_button. $pay_commition. $pay_sales_man_commission. $pay_admin_button . $generate_invoice;
                            // $build_pay_action = '';

                            // $build_commition_action = '';

                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.
                             ' </a>';

                             if($data->order_cancel_status == 1)
                             {
                                /*$build_view_action .= '<a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-circle btn-success btn-outline show-tooltip" href="javascript::void(0)">Cancel Requested</a>';*/
                             }

                            return $build_action = $build_view_action;
                        })
                        ->make(true);

        $build_result = $json_result->getData();
        $build_result->total_amt = $total_amt;        
        return response()->json($build_result);
    }

    public function get_export_reps_orders(Request $request)
    {
      $admin_commission = $this->CommissionService->get_admin_commission();

      // $representative_commission            = $this->CommissionService->get_representative_commission();

      $representative_leads_tbl_name        = $this->RepresentativeLeadsModel->getTable();        
      $prefixed_representative_leads_tbl    = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

      $sales_manager_tbl_name                = $this->SalesManagerModel->getTable();        
      $prefixed_sales_manager_tbl            = DB::getTablePrefix().$this->SalesManagerModel->getTable();

      $representative_leads_pro_tbl_name     = $this->RepresentativeProductLeadsModel->getTable();        
      $prefixed_representative_leads_pro_tbl = DB::getTablePrefix().$this->RepresentativeProductLeadsModel->getTable();

      $maker_tbl_name               = $this->MakerModel->getTable();
      $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

      $retailer_table               = $this->RetailerModel->getTable();
      $prefixed_retailer_tbl        = DB::getTablePrefix().$this->RetailerModel->getTable();

      $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $stripe_transaction_table          = $this->StripeTransactionModel->getTable();
      $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

      $user_table        =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;


      $obj_products = DB::table($representative_leads_tbl_name)
                                ->select(DB::raw($prefixed_representative_leads_tbl.".*,".  
                                                 
                                                 $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.
                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                     
                                                ))                                
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_leads_tbl.'.representative_id')

                                //->leftjoin($prefixed_sales_manager_tbl,$prefixed_sales_manager_tbl.'.user_id','=',$user_table.'.id')

                                //->leftjoin($user_table.' as SM','SM.id','=',$prefixed_sales_manager_tbl.'.user_id')

                                ->leftjoin($prefixed_retailer_tbl,$prefix_user_table.'.id','=',$prefixed_retailer_tbl.'.user_id')


                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_representative_leads_tbl.'.id','=',$prefixed_representative_leads_tbl.'.order_no')

                                ->where($prefixed_representative_leads_tbl.'.is_confirm',1)
                                
                                ->where($prefixed_representative_leads_tbl.'.representative_id','!=' ,0)

                                ->where($prefixed_representative_leads_tbl.'.order_cancel_status','!=',2)

                                ->orderBy($prefixed_representative_leads_tbl.".id",'DESC');

                                $obj_sales_products = DB::table($representative_leads_tbl_name)
                                ->select(DB::raw($prefixed_representative_leads_tbl.".*,".  
                                                 
                                                 $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.
                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                     
                                                ))                                
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_leads_tbl.'.sales_manager_id')


                                ->leftjoin($prefixed_retailer_tbl,$prefix_user_table.'.id','=',$prefixed_retailer_tbl.'.user_id')


                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_representative_leads_tbl.'.id','=',$prefixed_representative_leads_tbl.'.order_no')

                                ->where($prefixed_representative_leads_tbl.'.is_confirm',1)
                                ->where($prefixed_representative_leads_tbl.'.sales_manager_id','!=' ,0)

                                ->where($prefixed_representative_leads_tbl.'.order_cancel_status','!=',2)
                                ->where($prefixed_representative_leads_tbl.'.maker_id','!=',0)

                                ->orderBy($prefixed_representative_leads_tbl.".id",'DESC'); 

       /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->all();
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.retailer_id',$retailer_id);
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.retailer_id',$retailer_id);
        }    

        if(isset($arr_search_column['q_lead_id']) && $arr_search_column['q_lead_id']!="")
        {
            $search_term      = $arr_search_column['q_lead_id'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.order_no','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term = $arr_search_column['q_username'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
        {
            $search_term  = $arr_search_column['q_payment_term'];
            $obj_qutoes   = $obj_products->where($prefixed_representative_leads_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');

            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_order_rep_sales_name']) && $arr_search_column['q_order_rep_sales_name']!="")
        {
            $search_term = $arr_search_column['q_order_rep_sales_name'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.description','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_store_name']) && $arr_search_column['q_store_name']!="")
        {
            $search_term  = $arr_search_column['q_store_name'];
            $obj_products = $obj_products->where($prefixed_retailer_tbl.'.store_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_retailer_tbl.'.store_name','LIKE', '%'.$search_term.'%');
        } 
        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_products = $obj_products->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }        

       /* if(isset($arr_search_column['q_tot_wholesale']) && $arr_search_column['q_tot_wholesale']!="")
        {
            $search_term      = $arr_search_column['q_tot_wholesale'];
            
            $obj_products     = $obj_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
        }*/
        
        if(isset($arr_search_column['q_tot_commi_less_wholesale']) && $arr_search_column['q_tot_commi_less_wholesale']!="")
        {
            $search_term      = $arr_search_column['q_tot_commi_less_wholesale'];
            
            $obj_products     = $obj_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
            $obj_sales_products     = $obj_sales_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
        }
  
        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');

            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
        }

        if(isset($arr_search_column['q_shipping_status']) && $arr_search_column['q_shipping_status']!="")
        {
            $search_term      = $arr_search_column['q_shipping_status'];
            
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_vendor_payment']) && $arr_search_column['q_vendor_payment']!="")
        {
            $search_term = $arr_search_column['q_vendor_payment'];
            
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.maker_commission_status','=',$search_term);

            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.maker_commission_status','=',$search_term);

        }

        if(isset($arr_search_column['q_rep_payment']) && $arr_search_column['q_rep_payment']!="")
        {
            $search_term = $arr_search_column['q_rep_payment'];
           
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.rep_commission_status','=',$search_term);

            $obj_sales_products = $obj_sales_products->where($prefixed_representative_leads_tbl.'.sales_manager_commission_status','=',$search_term);
        }

        
        
        if(isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="" && isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!="")
        {
            

            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];
            $from_date              = DateTime::createFromFormat('m/d/Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m/d/Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');
        
            $obj_products   = $obj_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '<=', $to_date);
            $obj_products   = $obj_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '>=', $from_date);

            $obj_sales_products   = $obj_sales_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '<=', $to_date);
            $obj_sales_products   = $obj_sales_products->whereDate($prefixed_representative_leads_tbl.'.created_at', '>=', $from_date);


        }

        $obj_products = $obj_products->union($obj_sales_products);
        
        $obj_products = $obj_products->orderBy("id",'DESC');

        $type  = 'csv'; 
        $data = $arr_orders = $arrayResponseData = [];

        $arr_orders = $obj_products->get()->toArray();

        if(count($arr_orders) <= 0)
        {
            $response['status']      = 'error';
            $response['message']      = 'No data available for export';
        
            return response()->json($response);
        }


        foreach($arr_orders as $key => $value)
        { 
            /*$payment_status = 'Pending';
            if($value->rep_commission_status == 0 || $value->rep_commission_status==null)
            {
              $payment_status =  'Pending';
            }else if($value->rep_commission_status == 1)
            {
              $payment_status = 'Paid';
            }else
            {
              $payment_status = 'Failed';
            }*/

            $payment_status = "-";


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
    

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            $arrayResponseData['Reps / Sales']          = $value->user_name;      
            $arrayResponseData['Vendor']                = $value->company_name;      
            $arrayResponseData['Total Amount ($)']      = $value->total_wholesale_price;
            $arrayResponseData['Shipping Status']       = $shipping_status;
            $arrayResponseData['Reps/Sales Payment Status'] = $payment_status;
            $arrayResponseData['Payment Type']          = $payment_type;
            
            array_push($data,$arrayResponseData);
        } 

        return Excel::create('Reps Sales Orders', function($excel) use ($data) {
        
        $excel->sheet('Reps Sales Orders', function($sheet) use ($data)
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

    public function view($enc_id)
    {   
      
      $stripe_transaction_table          = $this->StripeTransactionModel->getTable();
      $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

      $representative_leads_tbl_name        = $this->RepresentativeLeadsModel->getTable();        
      $prefixed_representative_leads_tbl    = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

      $stripe_bulk_transaction_tbl_name             = $this->StripeBulkTransactionModel->getTable();        
      $prefixed_stripe_bulk_transaction_tbl_name    = DB::getTablePrefix().$this->StripeBulkTransactionModel->getTable();

        $leads_id = base64_decode($enc_id);

        $leads_arr = $split_order_arr = $main_split_order_no = $orderCalculationData = [];
        $obj_data = $this->RepresentativeLeadsModel->with([ 'leads_details.product_details.brand_details',
                                                            'transaction_mapping',
                                                            'maker_details',
                                                            'retailer_user_details.retailer_details',
                                                            'retailer_user_details'=>function($q1)
                                                            {
                                                                $q1->select('id','email','first_name','last_name');
                                                            },'maker_data'=>function($q2)
                                                            {
                                                                $q2->select('id','email','first_name','last_name');
                                                            },
                                                            'representative_user_details'=>function($q2)
                                                            {
                                                                $q2->select('id','email','first_name','last_name');
                                                            },'address_details',
                                                            'transaction_mapping_details',
                                                            'stripe_transaction_data'

                                                        ])

                                                ->where('id',$leads_id)
                                                ->first();
        if($obj_data)
        {
            $leads_arr = $obj_data->toArray();
            //dd($leads_arr);
            if ($leads_arr['split_order_id'] != '') 
            {

                $main_split_order_no = $this->RepresentativeLeadsModel
                                            ->with(['leads_details.product_details.brand_details',
                                                    'transaction_mapping',
                                                    'maker_details',
                                                    'retailer_user_details.retailer_details',
                                                    'maker_data'=>function($q2)
                                                    {
                                                        $q2->select('id','email','first_name','last_name');
                                                    },
                                                    'representative_user_details'=>function($q2)
                                                    {
                                                        $q2->select('id','email','first_name','last_name');
                                                    },'address_details',
                                                    'transaction_mapping_details'
                                                ])

                                              ->where('id',$leads_arr['split_order_id'])
                                              ->first();

            }
            elseif ($leads_arr['is_split_order'] == '1')
            {

                $split_order_arr = $this->RepresentativeLeadsModel
                                        ->with(['leads_details.product_details',
                                                'transaction_mapping','maker_details',
                                                'retailer_user_details.retailer_details',
                                                'retailer_user_details'=>function($q1)
                                                {
                                                    $q1->select('id','email','first_name','last_name');
                                                },'maker_data'=>function($q2)
                                                {
                                                    $q2->select('id','email','first_name','last_name');
                                                },
                                                'representative_user_details'=>function($q2)
                                                {
                                                    $q2->select('id','email','first_name','last_name');
                                                },'address_details',
                                                  'transaction_mapping_details'

                                              ])
                            
                                              ->where('split_order_id',$leads_arr['id'])
                                              ->get()
                                              ->toArray(); 

            }


        }       


          // Get bulk payment id from stripe transaction by Harshada
          // On date 31 Aug 2020
          $obj_get_bilk_payment_id = DB::table($stripe_transaction_table)
                                ->select(DB::raw($stripe_transaction_table.".bulk_pay_id" )) 
                                ->where('quote_id',$leads_id)
                                ->where('bulk_pay_id','!=',0)
                                ->first();
          $this->arr_view_data['is_bulk_payment'] = 0;


           $obj_res_bilk_payment = array();
          if(!empty($obj_get_bilk_payment_id)){

          $this->arr_view_data['is_bulk_payment'] = 1;

          $obj_res_bilk_payment = DB::table($stripe_transaction_table)
                                ->select(DB::raw($stripe_transaction_table.".bulk_pay_id,".
                                              $stripe_transaction_table.".amount,".
                                              $representative_leads_tbl_name.".*,".
                                              $stripe_bulk_transaction_tbl_name.".bulk_transaction_key"
                                   )) 
                                ->leftjoin($representative_leads_tbl_name,$representative_leads_tbl_name.'.id','=',$stripe_transaction_table.'.quote_id')
                                ->leftjoin($stripe_bulk_transaction_tbl_name,$stripe_bulk_transaction_tbl_name.'.id','=',$stripe_transaction_table.'.bulk_pay_id')
                                ->where('bulk_pay_id',$obj_get_bilk_payment_id->bulk_pay_id)
                                ->get()
                                ->toArray();
          }


        /************ pay commission section ***************/

        

        $maker_commission_status = isset($leads_arr['maker_commission_status'])?$leads_arr['maker_commission_status']:0;

        $sales_manager_commission_status = isset($leads_arr['sales_manager_commission_status'])?$leads_arr['sales_manager_commission_status']:0;

        $rep_commission_status = isset($leads_arr['rep_commission_status'])?$leads_arr['rep_commission_status']:0;


        $sale_manager_pay_btn = 'display:none';
                           
        if($maker_commission_status == '1')
        {
           $is_disabled = 'display:none';
        }
        else
        {
           $is_disabled='display:inline-block';
        }

        if($rep_commission_status == '1')
        {
           $rep_pay_btn = 'display:none';

        }
        else
        { 
            if($leads_arr['sales_manager_id'] == 0)
            {
              $rep_pay_btn = 'display:inline-block';
            }
            else
            {
               $sale_manager_pay_btn = 'display:inline-block';
               $rep_pay_btn = 'display:none';

               if($leads_arr['sales_manager_commission_status'] == '1')
               {
                 $sale_manager_pay_btn = 'display:none';
               }
            }

        }

        /*Get site setting data from helper*/
        $arr_site_setting = get_site_settings(['site_name','representative_commission','salesmanager_commission']);

    
        $representative_commission = isset($leads_arr['rep_sales_commission'])?$leads_arr['rep_sales_commission'] : 0;


        $leads_arr_id       = isset($leads_arr['id'])?$leads_arr['id']:0;
        $leads_arr_order_no = isset($leads_arr['order_no'])?$leads_arr['order_no']:'';

        $tracking_details = [];
        $tracking_no = 0;

        if($leads_arr_id!=0 && $leads_arr_order_no!='')
        {
          $tracking_details = $this->HelperService->getTrackingDetails($leads_arr_id,$leads_arr_order_no);
          $tracking_no = isset($tracking_details['tracking_no'])?$tracking_details['tracking_no']:0;

        /*get order calculation data from helper*/
        if(isset($leads_arr['order_no']) && !empty($leads_arr['order_no']) &&
                isset($leads_arr['maker_id']) && !empty($leads_arr['maker_id']))
        {
            $ordNo = base64_encode($leads_arr['order_no']);
            $vendorId = base64_encode($leads_arr['maker_id']);

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
        }

        /* ************************************************ */
        $this->arr_view_data['is_disabled']     = $is_disabled;
        $this->arr_view_data['rep_pay_btn']     = $rep_pay_btn;
        $this->arr_view_data['sale_manager_pay_btn']     = $sale_manager_pay_btn;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['leads_arr']       = $leads_arr;
        $this->arr_view_data['bulk_arr']        = $obj_res_bilk_payment;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['split_order_arr'] = $split_order_arr;
        $this->arr_view_data['main_split_order_no']= $main_split_order_no;
        $this->arr_view_data['arr_site_setting']   = $arr_site_setting;
        $this->arr_view_data['representative_commission'] = $representative_commission;
        $this->arr_view_data['tracking_no'] = $tracking_no;
        $this->arr_view_data['tracking_details'] = $tracking_details;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }
}
     /*
        Author: Jaydip
        Start Date: 19-12-19
        Discription: Admin will pay commission to the vendor 
    */

    public function pay_commission(Request $request)
    {

        $count = 0;
        $view_href = '';
        $order_arr = $order_data = [];
        $form_data = $request->all();
        $type = '';

     
        $maker_id    = isset($form_data['maker_id'])?$form_data['maker_id']:false;
        $rep_id      = isset($form_data['rep_id'])?$form_data['rep_id']:false;
        $sales_id    = isset($form_data['sales_id'])?$form_data['sales_id']:false;
        $customer_id = isset($form_data['customer_id'])?$form_data['customer_id']:false;
        $order_no    = isset($form_data['order_number'])?$form_data['order_number']:false;
        $order_id    = isset($form_data['order_id'])?$form_data['order_id']:false;
        
        if($maker_id)
        {
            $received_by = $maker_id;
            $type = 'maker';
        }
        elseif($rep_id)
        {
            $received_by = $rep_id;
            $type = 'representative';
        }
        elseif($sales_id){
            $received_by = $sales_id;
            $type = 'sales_manager';
        }
        else
        {
           $response['status'] = 'error';
           $response['message'] = 'Payment destination user not found, please try agian.';
           return $response->json($response);
        }

        $order_id = isset($form_data['order_id'])?$form_data['order_id']:false;
        $order_no = isset($form_data['order_no'])?$form_data['order_no']:false;

        $order_data = get_lead_data($order_id,$order_no);

        // dd($order_data,$received_by,$type);

        if($type == 'maker' && isset($order_data['maker_commission_status']) && $order_data['maker_commission_status'] == '1' || $type == 'representative' && isset($order_data['rep_commission_status']) && $order_data['rep_commission_status'] == '1' || $type == 'sales_manager' && isset($order_data['sales_manager_commission_status']) && $order_data['sales_manager_commission_status'] == '1')
        {
            $response['status']  = 'pay-warning';
            $response['message'] = "Payment already completed for this order.";
            $response['user_id'] = $received_by;
            
           return response()->json($response);
        }

        $arr_order_data_new = [];

        /*  Get admin stripe secret key id  */
        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key(get_admin_id());

        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

        /*  Get maker stripe secret key id  */
        $vendorStripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($maker_id);

        $vendorStripKeyId = isset($vendorStripeApiKeyData['id'])?$vendorStripeApiKeyData['id']:'';

        /*  Get payment Receiver role details  */
        $isRoleMaker = Sentinel::findUserById($received_by)->inRole('maker');

        $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$received_by)
                                                                     ->where('admin_stripe_key_id',$stripKeyId);

        if($isRoleMaker == 'maker')
        {
          $obj_stripe_account_details = $obj_stripe_account_details->where('vendor_stripe_key_id',$vendorStripKeyId);
        }

        $obj_stripe_account_details = $obj_stripe_account_details->first();

        if($obj_stripe_account_details)
        {
            $stripe_acc_id = isset($obj_stripe_account_details->stripe_acc_id)?$obj_stripe_account_details->stripe_acc_id:false;
            
            if($stripe_acc_id)
            {
                  /* get order data */             

                $arr_transfer_data['amount']      = $form_data['amount'];
                $arr_transfer_data['account_id']  = $stripe_acc_id;
                $arr_transfer_data['description'] = 'Payment for Order No:'.$order_data['order_no'];

                $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

                if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
                {
                    $response['status'] = 'error';
                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                    return response()->json($response);
                }

                if($transfer_response)
                {
                  $arr_data['quote_id']        = $form_data['order_id'];
                 
                  $arr_data['amount']          = $form_data['amount'];
                  $arr_data['transaction_id']  = $transfer_response->balance_transaction;
                  $arr_data['transfer_id']     = $transfer_response->id;
                  $arr_data['destination_payment'] = $transfer_response->destination_payment;
                  $arr_data['status']          = '2';
                  $arr_data['received_by']     = $received_by;
                  $arr_data['paid_by']         = get_admin_id();

                  /* Create Entry in StripTransaction table */
                  $create_stripe_transaction = $this->StripeTransactionModel->create($arr_data);

                  if($create_stripe_transaction)
                  {
                      $bulk_transaction_key = rand(1000,9999).date("s");
                      if($rep_id)
                      {
                          $update_data['rep_commission_status']  = '1';
                          $update_data['transfer_commission_stripe_key_id']  = $stripKeyId;

                          $rep_commission_status_update = $this->RepresentativeLeadsModel->where('id',$form_data['order_id'])                                                             ->update($update_data);
                      }
                      elseif($sales_id)
                      {
                          $update_data['sales_manager_commission_status']  = '1';
                          $update_data['transfer_commission_stripe_key_id']  = $stripKeyId;

                          $rep_commission_status_update = $this->RepresentativeLeadsModel->where('id',$form_data['order_id'])                                                             ->update($update_data);
                      }elseif($customer_id)
                      {
                          $update_data['maker_commission_status']  = '1';
                          $update_data['received_commission_stripe_key_id']  = $vendorStripKeyId;
                          $update_data['transfer_commission_stripe_key_id']  = $stripKeyId;

                          $rep_commission_status_update = $this->CustomerQuotesModel->where('id',$form_data['order_id'])                                                        ->update($update_data);
                      }
                      else
                      {
                          $update_data['maker_commission_status']  = '1';
                          $update_data['received_commission_stripe_key_id']  = $vendorStripKeyId;
                          $update_data['transfer_commission_stripe_key_id']  = $stripKeyId;

                           
                           if(isset($order_id) && isset($order_no))
                           {
                              $retailer_order_count = $this->RetailerQuotesModel->where('id',$order_id)
                                                                                ->where('order_no',$order_no)
                                                                                ->count();

                              if($retailer_order_count>0)
                              {
                                  $maker_commission_status_update = $this->RetailerQuotesModel
                                                                          ->where('id',$form_data['order_id'])
                                                                          ->where('maker_id',$form_data['maker_id'])
                                                                          ->where('order_no',$order_no)
                                                                          ->update($update_data);
                              }  


                              $rep_sales_order_count = $this->RepresentativeLeadsModel->where('id',$order_id)
                                                                                      ->where('order_no',$order_no)
                                                                                      ->count();  

                              if($rep_sales_order_count > 0)
                              {
                                  $maker_commission_status_update = $this->RepresentativeLeadsModel
                                                                         ->where('id',$form_data['order_id'])
                                                                         ->where('maker_id',$form_data['maker_id'])
                                                                         ->where('order_no',$order_no)
                                                                         ->update($update_data);
                              }

                           }

          
                      }

                      /* Send Notifications */

                      if($this->loginuserid)
                      {
                        $loggedInUserId = $this->loginuserid;
                      }
                      else
                      {
                        $loggedInUserId = 1;
                      }

                      $notification_arr                 = [];
                      $notification_arr['from_user_id'] = $loggedInUserId;
                      $notification_arr['to_user_id']   = $received_by;

                      if($type == 'maker')
                      {
                        
                          $order_obj = $this->RepresentativeLeadsModel->where('order_no',$order_data['order_no'])->where('maker_id',$maker_id)->first();

                         

                          if(isset($order_obj))
                          {
                                $order_arr = $order_obj->toArray();
                          } 
                            
                          $view_href = url('/').'/vendor/representative_orders/view/'.base64_encode($order_data['order_no']);

                          $notification_arr['description']  = 'Payment ($'.$form_data['amount'].') is received successfully of Order No: '.$order_data['order_no'];

                          $notification_arr['title']        = 'Payment Received';
                          $notification_arr['type']         = $type;   
                          $notification_arr['link']         = $view_href;  

                          $this->GeneralService->save_notification($notification_arr); 
                      }
                      elseif($type == 'representative')
                      {
                        
                         $view_href =  url('/').'/representative/leads/view_lead_listing/'.base64_encode($order_id).'/'.base64_encode($order_data['order_no']);

                        $notification_arr['description']  = 'Commission ($'.$form_data['amount'].') is received successfully of Order No: '.$order_data['order_no'];

                        $notification_arr['title']        = 'Commission Received';
                        $notification_arr['type']         = $type;   
                        $notification_arr['link']         = $view_href;  

                        $this->GeneralService->save_notification($notification_arr);

                      }
                      else if($type == 'sales_manager')
                      {
                        
                        $view_href =  url('/').'/sales_manager/leads/view_lead_listing/'.base64_encode($order_id).'/'.base64_encode($order_data['order_no']);


                         $notification_arr['description']  = 'Commission ($'.$form_data['amount'].') is received successfully of order No: '.$order_data['order_no'];

                        $notification_arr['title']        = 'Commission Received';
                        $notification_arr['type']         = $type;   
                        $notification_arr['link']         = $view_href; 

                        $this->GeneralService->save_notification($notification_arr); 
                      }

                      $view_href =  url('/').'/admin/leads/view/'.base64_encode($order_id);
                      $notification_arr['title']        = 'Commission Paid';
                      $notification_arr['type']         = 'admin';   
                      $notification_arr['link']         = $view_href; 

                      $this->GeneralService->save_notification($notification_arr);  


                      
                      $vendor_email = $rep_sales_email = '';
 
                      
                      //send payment recieved mail to vendor

                      if($type == 'maker')
                      {
                        $vendor_email = $this->HelperService->get_user_mail($maker_id);

                        $credentials = ['email' => $vendor_email];
      
                        $arr_user = get_user_by_credentials($credentials);

                        /*Get site setting data from helper*/
                        $arr_site_setting = get_site_settings(['site_name','website_url']);

                        $arr_built_content = ['commission_amount'   => num_format($form_data['amount']),
                                              'order_no'     => $order_data['order_no'],
                                              'PROJECT_NAME' => $arr_site_setting['site_name']
                                             ];


                        $arrOrderAmount = $arrUserData = $arr_mail_data = [];

                        $arr_mail_data['email_template_id'] = '61';
                        $arr_mail_data['arr_built_content'] = $arr_built_content;
                        $arr_mail_data['arr_user']          = $arr_user;

                        // $email_status  = $this->EmailService->send_mail($arr_mail_data);
                        $makerData = $this->MakerModel->where('user_id',$maker_id)->first()->toArray();
                        $arrUserData['business_details'] = $makerData;
                        $arrUserData['personla_details'] = $arr_user;
                        $arrMailData['rep_sales_order_data'][] = $order_data;                  

                        $invoice_data = $this->GeneralService->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,false);

                      }



                    /*send mail to user for commission recieved (rep,sales)*/

                    if($type == 'representative' || $type == 'sales_manager')
                    {
                      
                      if($type == 'representative')
                      {
                        $rep_sales_email = $this->HelperService->get_user_mail($rep_id);
                      }

                      if($type == 'sales_manager')
                      {
                        $rep_sales_email = $this->HelperService->get_user_mail($sales_id);
                      }


                      $credentials = ['email' => $rep_sales_email];
      
                      $arr_user = get_user_by_credentials($credentials);

                      /*Get site setting data from helper*/
                      $arr_site_setting = get_site_settings(['site_name','website_url']);

                      $arr_built_content = ['commission_amount'   => num_format($form_data['amount']),
                                            'order_no'     => $order_data['order_no'],
                                            'PROJECT_NAME' => $arr_site_setting['site_name']
                                           ];                     

                       $arrOrderAmount = $arrUserData = $arr_mail_data = [];

                        $arr_mail_data['email_template_id'] = '61';
                        $arr_mail_data['arr_built_content'] = $arr_built_content;
                        $arr_mail_data['arr_user']          = $arr_user;

                        // $email_status  = $this->EmailService->send_mail($arr_mail_data);
                       
                        $arrUserData['business_details'] = $arr_user;
                        $arrUserData['personla_details'] = $arr_user;
                        $arrMailData['rep_sales_order_data'][] = $order_data;                  

                        //$email_status  = $this->EmailService->send_mail($arr_mail_data);
                        $invoice_data = $this->GeneralService->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,true);


                    }
                      

                      //send mail to admin 
                      $html = '';
                      $admin_email = get_admin_email();

                      $credentials = ['email' => $admin_email];
      
                      $arr_user  = get_user_by_credentials($credentials);

                      /*Get site setting data from helper*/
                      $arr_site_setting = get_site_settings(['site_name','website_url']);

                      $html = '<p> Commission $'.num_format($form_data['amount']).' has been paid successfully of Order No:('.$order_data['order_no'].')</p>';

                      $arr_built_content = [
                                            'HTML'         => $html,
                                            'PROJECT_NAME' => $arr_site_setting['site_name']
                                            
                                           ];


                      $arr_mail_data                      = [];
                      $arr_mail_data['email_template_id'] = '47';
                      $arr_mail_data['arr_built_content'] = $arr_built_content;
                      $arr_mail_data['arr_user']          = $arr_user;

                      $email_status  = $this->EmailService->send_mail($arr_mail_data);                     

                      /*------------------------------------------------*/ 

                  


                      $response['status']  = 'success';
                      $response['message'] = 'Commission has been transferred.';
                  }
                  else
                  {
                      $response['status'] = 'error';
                      $response['message'] = 'Something went wrong, please try again.';
                  }

                  return response()->json($response);
                }
                else
                {
                    $response['status'] = 'error';
                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                    return response()->json($response);
                }
            }
            else
            {
                $response['status'] = 'error';
                $response['message'] = 'Please verify this users stripe account details.';
                return response()->json($response);
            }   
        }
        else
        {
           $response['status']  = 'warning';
           $response['message'] = "This user are not connected to ".get_site_settings(['site_name'])['site_name']." stripe account.";
           $response['user_id'] = $received_by;
            
           return response()->json($response);
        }
    }

public function bulk_payCommission(Request $request)
    {
        $count = $stripKeyId = 0;
        $view_href = '';
        $order_arr = $arrMailData = [];
        $form_data = $request->input();
      
        $model_orderId = isset($form_data['model_orderId'])?$form_data['model_orderId']:array();
        //echo "<pre> form_data ===> "; print_r($form_data); exit; 

        $maker_id = $rep_id = $sales_id = false;   

        switch($form_data['user_type'])   
        {
          case "representative": $rep_id = $form_data['user_id']; 
                                 $received_by = $rep_id;  
                                 $type = 'representative';
                                 break;
          case "sales_manager": $sales_id = $form_data['user_id']; 
                                $received_by = $sales_id;
                                $type = 'sales_manager';
                                 break;
          case "vendor": $maker_id = $form_data['user_id']; 
                                $received_by = $maker_id;
                                $type = 'maker';  
                                break;
          default:  $response['status'] = 'error';
                    $response['message'] = 'Payment destination user not found, please try agian.';
                    return $response->json($response);
                    break;
        }
        

        

        $order_ids = $model_orderId;
        $order_noArr = isset($form_data['order_noArr'])?$form_data['order_noArr']:array();


         /*  Get admin stripe secret key id  */
        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key(get_admin_id());

        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

        /*  Get maker stripe secret key id  */
        $vendorStripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($maker_id);

        $vendorStripKeyId = isset($vendorStripeApiKeyData['id'])?$vendorStripeApiKeyData['id']:'';

        /*  Get payment Receiver role details  */
        $isRoleMaker = Sentinel::findUserById($received_by)->inRole('maker');

        $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$received_by)
                                                                      ->where('admin_stripe_key_id',$stripKeyId);

        if($isRoleMaker == 'maker')
        {
          $obj_stripe_account_details = $obj_stripe_account_details->where('vendor_stripe_key_id',$vendorStripKeyId);
        }

        $obj_stripe_account_details = $obj_stripe_account_details->first();
       
        if($obj_stripe_account_details)
        {
            $stripe_acc_id = isset($obj_stripe_account_details->stripe_acc_id)?$obj_stripe_account_details->stripe_acc_id:false;
            
          if($stripe_acc_id)
          {
                /* get order data */              
              $bulk_transaction_key = rand(1000,9999).date("s");
              $arr_transfer_data['amount']      = $form_data['total_repCommissionAmount'];
              $arr_transfer_data['account_id']  = $stripe_acc_id;
              $arr_transfer_data['description'] = 'Bulk Payment of bulk pay-id: '.$bulk_transaction_key.' for Order Nos: '.implode (",", $form_data['order_noArr']);

              $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

              if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
              {
                  $response['status'] = 'error';
                  $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                  return response()->json($response);
              }

              if($transfer_response)
              {

                $paid_by          = get_admin_id();
                $transfer_id      = $transfer_response->id;
                $transaction_id   = $transfer_response->balance_transaction;
                $destination_payment   = $transfer_response->destination_payment;


                $bulkPay_data['bulk_transaction_key'] = $bulk_transaction_key;
                $bulkPay_data['paid_by'] = $paid_by;
                $bulkPay_data['received_by'] = $received_by;
                $bulkPay_data['bulk_transfer_id'] = $transfer_id;
                $bulkPay_data['bulk_transaction_id'] = $transaction_id;
                $bulkPay_data['bulk_destination_payment'] = $destination_payment;
                $bulkPay_data['total_amount'] = $form_data['total_repCommissionAmount'];
                $bulkPay_data['payment_status'] = '2';
                $bulkPay_data['payment_date'] = date("Y-m-d H:i:s");

                $create_stripe_transaction = $this->StripeBulkTransactionModel->create($bulkPay_data);

                $bulk_pay_id= $create_stripe_transaction->id;


                // $repCommissionAmount = $form_data['repCommissionAmount'];
                $repCommissionAmountData = $form_data['repCommissionAmount'];

                // $repCommissionAmountData = $repCommissionAmount;
                $repCommissionAmount = [];
                $cnt = 0;
                foreach ($repCommissionAmountData as $key => $commissionData) 
                {

                  $repCommissionAmount[$cnt] = $commissionData;
                  $cnt = $cnt + 1;
                }
                // dd($form_data['order_noArr']);
                // dd($repCommissionAmount);

                
                $update_orderId = array();
                foreach ($order_ids as $id_key => $order_id) {

                  $orderNumber = isset($order_noArr[$id_key])?$order_noArr[$id_key]:0;

                  if($orderNumber == 0)
                  {
                     $orderNumber = isset($order_noArr[$order_id])?$order_noArr[$order_id]:0;
                  }

                  $isRepSalesOrder = $this->is_rep_sales_order($orderNumber);
                  $isCustomerOrder = $this->is_customer_order($orderNumber);

                  $arr_data = array();
                  if($isRepSalesOrder == true)
                  {
                    $arr_data['quote_id']          = $order_id;
                   
                  }
                  elseif($isCustomerOrder == true)
                  {
                    $arr_data['customer_order_id']          = $order_id;                   
                  }
                  else
                  {

                    $arr_data['lead_id']          = $order_id;           

                  }
                  
                  $arr_data['bulk_pay_id']         = $bulk_pay_id;
               
                  // $arr_data['amount']              = $repCommissionAmount[$order_id];
                  $arr_data['amount']              = $repCommissionAmount[$id_key];
                  $arr_data['transaction_id']      = $transaction_id;
                  $arr_data['transfer_id']         = $transfer_id;
                  $arr_data['destination_payment'] = $destination_payment;
                  $arr_data['status']              = '2';
                  $arr_data['received_by']         = $received_by;
                  $arr_data['paid_by']             = $paid_by;

                  /* Create Entry in StripTransaction table */
                  $create_stripe_transaction = $this->StripeTransactionModel->create($arr_data);

                  if($create_stripe_transaction)
                  {
                    $update_orderId[] = $order_id;
                    // $update_orderNo[] = $form_data['order_noArr'][$id_key];
                    if(isset($form_data['order_noArr'][$id_key]))
                    {
                       $update_orderNo[] = $form_data['order_noArr'][$id_key];
                    }
                    else
                    {
                      $update_orderNo[] = $form_data['order_noArr'][$order_id];
                    }
                    
                  }
                }
    

                if(count($update_orderId)>0)
                {
                    $update_data = array();
                    $update_data['updated_at'] = date("Y-m-d H:i:s");
                   
                    if($rep_id)
                    {

                      foreach ($update_orderId as $repKey => $repOrderId) 
                      {
                        $repOrderData = $this->RepresentativeLeadsModel
                                             ->where('id',$repOrderId)
                                             ->first();

               

                        if($repOrderData)
                        {
                          $rep_sales_order_data = $repOrderData->toArray();
                          $arrMailData['rep_sales_order_data'][$repKey] = $rep_sales_order_data;
                        }
                      }

                        $update_data['rep_commission_status']  = '1';

                        $rep_commission_status_update = $this->RepresentativeLeadsModel
                                                             ->whereIn('id',$update_orderId)
                                                             ->update($update_data);

                    }
                    elseif($sales_id)
                    {
                        foreach ($update_orderId as $repKey => $repOrderId) 
                        {
                          $repOrderData = $this->RepresentativeLeadsModel
                                               ->where('id',$repOrderId)
                                               ->first();

                          if($repOrderData)
                          {
                            $rep_sales_order_data = $repOrderData->toArray();
                            $arrMailData['rep_sales_order_data'][$repKey] = $rep_sales_order_data;
                          }
                        }

                        $update_data['sales_manager_commission_status']  = '1';

                        $rep_commission_status_update = $this->RepresentativeLeadsModel->whereIn('id',$update_orderId)->update($update_data);
                    }
                    else
                    {
                     
                        $update_data['maker_commission_status']  = 1;

                        foreach ($form_data['order_noArr'] as $key1 => $orderNo) 
                        {
                          $isRepSalesOrder = $this->is_rep_sales_order($orderNo);
                          $isCustomerOrder = $this->is_customer_order($orderNo);
                    
                          if($isRepSalesOrder == true)
                          {
                            $repOrdData= $this->RepresentativeLeadsModel
                                                               ->where('order_no',$orderNo)
                                                               ->where('maker_id',$form_data['user_id'])
                                                               ->first();

                            $maker_commission_status_update =$this->RepresentativeLeadsModel
                                                               ->where('order_no',$orderNo)
                                                               ->where('transfer_commission_stripe_key_id',$stripKeyId)
                                                               ->where('maker_id',$form_data['user_id'])
                                                               ->update($update_data);

                             /* get rep-sales order data */
                            $rep_sales_order_data = $this->RepresentativeLeadsModel->where('id',$repOrdData->id)->first();

                            if($rep_sales_order_data)
                            {
                              $rep_sales_order_data = $rep_sales_order_data->toArray();
                              $arrMailData['rep_sales_order_data'][$key1] = $rep_sales_order_data;
                            }

                          }
                          elseif($isCustomerOrder == true)
                          {
                            $customerOrdData= $this->CustomerQuotesModel
                                              ->where('order_no',$orderNo)
                                              ->where('maker_id',$form_data['user_id'])
                                              ->first();

                            $maker_commission_status_update = $this->CustomerQuotesModel
                                                                   ->where('order_no',$orderNo)
                                                                   ->where('transfer_commission_stripe_key_id',$stripKeyId)
                                                                   ->where('maker_id',$form_data['user_id'])
                                                                   ->update($update_data);

                            /* get customer order data */
                            $customer_order_data = $this->CustomerQuotesModel
                                                        ->where('id',$customerOrdData->id)
                                                        ->first();

                             /* Get customer order shipping charge data */
                            $customer_order_shipping_charge = $this->orderDataService->get_customer_order_shipping_charges($customerOrdData->id);

                            if($customer_order_data)
                            {
                              $customer_order_data = $customer_order_data->toArray();
                              $arrMailData['customer_order_data'][$key1] = $customer_order_data;
                              $arrMailData['customer_order_shipping_charge'][$key1] = $customer_order_shipping_charge;
                            } 
                          }
                          else
                          {
                            
                            $retOrdData = $this->RetailerQuotesModel
                                                ->where('order_no',$orderNo)
                                                ->where('maker_id',$form_data['user_id'])
                                                ->first();
                            
                            $maker_commission_status_update = $this->RetailerQuotesModel
                                                                    ->where('order_no',$orderNo)
                                                                    ->where('maker_id',$form_data['user_id'])
                                                                    ->where('transfer_commission_stripe_key_id',$stripKeyId)
                                                                    ->update($update_data);

                             /* get retailer order data */
                            $retailer_order_data = $this->RetailerQuotesModel->where('id',$retOrdData->id)->first();
                           
                            /* Get retailer order shipping charge data */
                            $retailer_order_shipping_charge = $this->orderDataService->get_retailer_order_shipping_charges($retOrdData->id);

                            if($retailer_order_data)
                            {
                                $retailer_order_data = $retailer_order_data->toArray();

                                $arrMailData['retailer_order_data'][$key1]            = $retailer_order_data;
                                $arrMailData['retailer_order_shipping_charge'][$key1] = $retailer_order_shipping_charge;
                            }
                          }
                        }

                        
                       /* $maker_commission_status_update = $this->RepresentativeLeadsModel
                                                               ->whereIn('id',$update_orderId)
                                                               ->where('maker_id',$form_data['user_id'])
                                                               ->update($update_data);

                        $maker_commission_status_update = $this->RetailerQuotesModel
                                                                ->whereIn('id',$update_orderId)
                                                                ->where('maker_id',$form_data['user_id'])
                                                                ->update($update_data);

                        $maker_commission_status_update = $this->CustomerQuotesModel
                                                               ->whereIn('id',$update_orderId)
                                                               ->where('maker_id',$form_data['user_id'])
                                                               ->update($update_data);*/
                    }

                    /* Send Notifications */

                    if($this->loginuserid)
                    {
                      $loggedInUserId = $this->loginuserid;
                    }
                    else
                    {
                      $loggedInUserId = 1;
                    }
                
                      
                      $email_orderIds = implode (",", $update_orderNo);

                      $notification_arr                 = [];
                      $notification_arr['from_user_id'] = $loggedInUserId;
                      $notification_arr['to_user_id']   = $received_by;

                      if($type == 'maker') 
                      {                        
                            
                          // $view_href = url('/').'/vendor/representative_orders/bulk_view/'.base64_encode($bulk_pay_id);

                          $view_href = '';

                          $notification_arr['description']  = 'Payment ($'.$form_data['total_repCommissionAmount'].') is received successfully for Order Nos: '.implode (",", $update_orderNo);


                        /*  $notification_arr['description']  = 'Payment ($'.$form_data['total_repCommissionAmount'].') is received successfully of bulk pay id- '.$bulk_pay_id.' for Order Nos: '.implode (",", $update_orderNo);*/

                          $notification_arr['title']        = 'Payment Received';
                          $notification_arr['type']         = $type;   
                          $notification_arr['link']         = $view_href;  

                          $this->GeneralService->save_notification($notification_arr); 

 

                          /*-----send email to vendor for bulk payment----------------------*/

                          $maker_email = $this->HelperService->get_user_mail($maker_id);

                          $credentials = ['email' => $maker_email];
      
                          $arr_user = get_user_by_credentials($credentials);

                          /*Get site setting data from helper*/
                          $arr_site_setting = get_site_settings(['site_name','website_url']);

                          $arr_built_content = ['commission_amount'   => num_format($form_data['total_repCommissionAmount']),
                                                'order_no'            => $email_orderIds,
                                                'PROJECT_NAME'        => $arr_site_setting['site_name']
                                               ];


                          $arr_mail_data                      = [];
                          $arr_mail_data['email_template_id'] = '61';
                          $arr_mail_data['arr_built_content'] = $arr_built_content;
                          $arr_mail_data['arr_user']          = $arr_user;

                          $makerData = $this->MakerModel->where('user_id',$maker_id)->first()->toArray();

                          $arrUserData['business_details'] = $makerData;
                          $arrUserData['personla_details'] = $arr_user;

                          $arrOrderAmount = [];
                          $arrOrderAmount['total_order_amount']        = $form_data['total_orderPrice'];
                          $arrOrderAmount['total_admin_commission']    = $form_data['total_adminCommission'];
                          $arrOrderAmount['total_vendor_commission']   = $form_data['total_repCommissionAmount'];

                          /*  ------ Send Bulk payment Invoice to user -----------------  */
                          $invoice_data = $this->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount);
                          /*  ----------- END ------------  */
                         

                          /*-----------------------------------------------------------------*/

                          //$is_mail_send = $this->EmailService->commission_paid_mail(num_format($form_data['total_repCommissionAmount']),$email_orderIds,$maker_email);

                      }
                      elseif($type == 'representative')
                      {
                        
                          $view_href =  url('/').'/representative/leads/view_lead_listing_bulk/'.base64_encode($bulk_pay_id);

                        /*  $notification_arr['description']  = 'Commission ($'.$form_data['total_repCommissionAmount'].') is received successfully of bulk pay id- '.$bulk_pay_id.' for Order Nos: '.implode (",", $update_orderNo);*/


                        $notification_arr['description']  = 'Commission ($'.$form_data['total_repCommissionAmount'].') is received successfully for Order Nos: '.implode (",", $update_orderNo);

                          $notification_arr['title']        = 'Commission Received';
                          $notification_arr['type']         = $type;   
                          $notification_arr['link']         = $view_href;  

                          $this->GeneralService->save_notification($notification_arr);



                          /*send email to representative for recived commission*/

                          $rep_email   = $this->HelperService->get_user_mail($rep_id);

                          $credentials = ['email' => $rep_email];
      
                          $arr_user    = get_user_by_credentials($credentials);

                          /*Get site setting data from helper*/
                          $arr_site_setting = get_site_settings(['site_name','website_url']);

                          $arr_built_content = ['commission_amount'   => num_format($form_data['total_repCommissionAmount']),
                                                'order_no'            => $email_orderIds,
                                                'PROJECT_NAME'        => $arr_site_setting['site_name']
                                               ];


                          $arr_mail_data                      = [];
                          $arr_mail_data['email_template_id'] = '61';
                          $arr_mail_data['arr_built_content'] = $arr_built_content;
                          $arr_mail_data['arr_user']          = $arr_user;

                          // $email_status  = $this->EmailService->send_mail($arr_mail_data);
                  
                          $arrUserData['business_details'] = $arr_user;
                          $arrUserData['personla_details'] = $arr_user;

                          $arrOrderAmount = [];
                          $arrOrderAmount['total_order_amount']        = $form_data['total_orderPrice'];
                          $arrOrderAmount['total_admin_commission']    = $form_data['total_adminCommission'];
                          $arrOrderAmount['total_vendor_commission']   = $form_data['total_repCommissionAmount'];

                          $invoice_data = $this->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,true);

                      }
                      else if($type == 'sales_manager')
                      {
                        
                        $view_href =  url('/').'/sales_manager/leads/view_lead_listing_bulk/'.base64_encode($bulk_pay_id);


                       /*  $notification_arr['description']  = 'Commission ($'.$form_data['total_repCommissionAmount'].') is received successfully of bulk pay id- '.$bulk_pay_id.' for order Nos: '.implode (",", $update_orderNo);*/

                       $notification_arr['description']  = 'Commission ($'.$form_data['total_repCommissionAmount'].') is received successfully for order Nos: '.implode (",", $update_orderNo);

                        $notification_arr['title']        = 'Commission Received';
                        $notification_arr['type']         = $type;   
                        $notification_arr['link']         = $view_href; 

                        $this->GeneralService->save_notification($notification_arr); 



                        /*send mail to sales manager to recived commission */

                        $sales_email = $this->HelperService->get_user_mail($sales_id);

                        $credentials = ['email' => $sales_email];
      
                        $arr_user    = get_user_by_credentials($credentials);

                        /*Get site setting data from helper*/
                        $arr_site_setting = get_site_settings(['site_name','website_url']);

                        $arr_built_content = ['commission_amount'   => num_format($form_data['total_repCommissionAmount']),
                                              'order_no'            => $email_orderIds,
                                              'PROJECT_NAME'        => $arr_site_setting['site_name']
                                             ];


                        $arr_mail_data                      = [];
                        $arr_mail_data['email_template_id'] = '61';
                        $arr_mail_data['arr_built_content'] = $arr_built_content;
                        $arr_mail_data['arr_user']          = $arr_user;

                        // $email_status  = $this->EmailService->send_mail($arr_mail_data);


                        //$is_mail_send = $this->EmailService->commission_paid_mail(num_format($form_data['total_repCommissionAmount']),$email_orderIds,$sales_email);

                      }

                      // $view_href =  url('/').'/admin/leads/view_bulkPay/'.base64_encode($bulk_pay_id);
                      $view_href = '';
                      $notification_arr['title']        = 'Commission Paid';
                      $notification_arr['type']         = 'admin';   
                      $notification_arr['link']         = $view_href; 

                      $this->GeneralService->save_notification($notification_arr);   

                      

                      //Sent email to admin for commission paid
                      $admin_email = get_admin_email();

                      $credentials = ['email' => $admin_email];
      
                      $arr_user  = get_user_by_credentials($credentials);

                      /*Get site setting data from helper*/
                      $arr_site_setting = get_site_settings(['site_name','website_url']);

                      $html = '<p> Commission $'.num_format($form_data['total_repCommissionAmount']).' has been paid successfully of Order No:('.$email_orderIds.')</p>';

                      $arr_built_content = [
                                            'HTML'         => $html,
                                            'PROJECT_NAME' => $arr_site_setting['site_name']
                                           ];


                      $arr_mail_data                      = [];
                      $arr_mail_data['email_template_id'] = '47';
                      $arr_mail_data['arr_built_content'] = $arr_built_content;
                      $arr_mail_data['arr_user']          = $arr_user;

                      $arrUserData['business_details'] = $arr_user;
                      $arrUserData['personla_details'] = $arr_user;

                      $arrOrderAmount = [];
                      $arrOrderAmount['total_order_amount']        = $form_data['total_orderPrice'];
                      $arrOrderAmount['total_admin_commission']    = $form_data['total_adminCommission'];
                      $arrOrderAmount['total_vendor_commission']   = $form_data['total_repCommissionAmount'];

                      $invoice_data = $this->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,true);

                      // $email_status  = $this->EmailService->send_mail($arr_mail_data);

                     
                      //$is_mail_send = $this->EmailService->comission_paid_by_admin(num_format($form_data['total_repCommissionAmount']),$email_orderIds,$admin_email);



                      $response['status']  = 'success';
                      $response['message'] = 'Commission has been transferred.';
                  }
                  else
                  {
                      $response['status'] = 'error';
                      $response['message'] = 'Something went wrong, please try again.';
                  }

                  return response()->json($response);
                }
                else
                {
                    $response['status'] = 'error';
                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                    return response()->json($response);
                }
            }
            else
            {
                $response['status'] = 'error';
                $response['message'] = 'Please verify this users stripe account details.';
                return response()->json($response);
            }   
        }
        else
        {
           $response['status']  = 'warning';
           $response['message'] = "This user are not connected to ".get_site_settings(['site_name'])['site_name']." stripe account.";
           $response['user_id'] = $received_by;
            
           return response()->json($response);
        }
    }

    /* 
      Auth : Jaydip
      Date : 20 Dec 2019
      Desc : send stripe account creation request to end user
    */
    public function send_stripe_acc_creation_link(Request $request)
    {

      $admin_id = get_admin_id();

      /* get client id */
      $client_id = '';

       /* get vendor client id */
     $client_id = $this->UserStripeAccountDetailsModel->where('user_id',$admin_id)
                                                      ->where('is_active','1')
                                ->pluck('client_id')
                                ->first();

      if(isset($client_id) && $client_id)
      {
         $user_id = $request->input('user_id');
      
         $connection_response = $this->StripePaymentService->connection_request($user_id,$client_id);
       
         if($connection_response)
         {
           $response['status']      = 'success';
           $response['message']     = 'Link has been sent.';
         }
         else
         {
           $response['status']      = 'error';
           $response['message']     = 'Something went wrong, please try again.';
         }
      }
      else
      {
        $response['status']      = 'error';
        $response['message']     = 'Client id is missing, please verify our client id.';
      }

     

     return response()->json($response);

    }
   

    /* 
      Auth : Jaydip
      Date : 23 Dec 2019
      Desc : Show order details used parameter :  Order No 
    */

    public function view_order($order_no)
    {

      /* Order From Representative lead tables */
        $order_no = base64_decode($order_no);

        $leads_arr = [];
        $obj_data = $this->RepresentativeLeadsModel
                         ->with(['leads_details.product_details.brand_details','transaction_mapping','maker_details','retailer_user_details'=>function($q1)
                            {
                                $q1->select('id','email','first_name','last_name');
                            },'maker_data'=>function($q2)
                            {
                                $q2->select('id','email','first_name','last_name');
                            },
                            'representative_user_details'=>function($q2)
                            {
                                $q2->select('id','email','first_name','last_name');
                            },'address_details'])
                         ->where('order_no',$order_no)
                         ->first();
                         
        if($obj_data)
        {
            $leads_arr = $obj_data->toArray();

            $this->arr_view_data['leads_arr']       = $leads_arr;
            $this->arr_view_data['module_title']    = $this->module_title;
            $this->arr_view_data['page_title']      = $this->module_title;
            $this->arr_view_data['module_url_path'] = $this->module_url_path;

            return view($this->module_view_folder.'.view',$this->arr_view_data);
        } 
        else
        {
              
             /* Order From Retailer Transaction tables */
              $enquiry_arr = [];

              $enquiry_obj = $this->RetailerQuotesModel->with(['maker_details',
                                                              'quotes_details.product_details.brand_details',
                                                              'maker_data',
                                                              'transaction_mapping',
                                                              'user_details'])
                                                      ->where('order_no',$order_no)->first();                               
            

            if($enquiry_obj)
            {
              $enquiry_arr = $enquiry_obj->toArray();
              $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
              $this->arr_view_data['module_title']    = $this->module_title;
          
              $this->arr_view_data['page_title']      = 'Order Details';
              $this->arr_view_data['module_url_path'] = $this->module_url_path;

              return view('admin.retailer_quotes.view',$this->arr_view_data);
            }
              
               
        }                                          
    }

    public function is_rep_sales_order($orderNo)
    {
      $isValid = false;

      $getOrder = $this->RepresentativeLeadsModel->where('order_no',$orderNo)
                                                 ->get();

      if(isset($getOrder) && count($getOrder) > 0)
      {
        $isValid = true;
      }
      
      return $isValid;
    }

    public function is_customer_order($orderNo)
    {
      $isValid = false;

      $getOrder = $this->CustomerQuotesModel->where('order_no',$orderNo)
                                            ->count();
      if($getOrder > 0)
      {
        $isValid = true;
      }

      return $isValid;
    }

    public function send_payment_invoice($arr_mail_data,$arrOrderData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,$isRepSalesOrder=false)
    {
      $invoiceData['invoice_no'] = isset($bulk_transaction_key)?$bulk_transaction_key:0;
      $invoiceData['invoice_date'] = date('d/m/Y');

      $pdf = PDF::loadView('front/admin_commission_paid_invoice',compact('arrUserData','arrOrderData','invoiceData','arrOrderAmount','isRepSalesOrder'));

      $currentDateTime = $bulk_transaction_key.date('H:i:s').'.pdf';

      $pdf_arr =  [
                  'PDF'           => $pdf,
                  'PDF_FILE_NAME' => $currentDateTime
                  ];

      return $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

       
    }
}
