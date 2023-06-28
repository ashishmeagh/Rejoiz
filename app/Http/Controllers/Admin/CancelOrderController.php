<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\MakerModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\RetailerModel;
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

class CancelOrderController extends Controller
{
     public function __construct(RetailerQuotesModel $RetailerQuotesModel,
    							UserModel $UserModel,
                                MakerModel $MakerModel,
                                RefundModel $RefundModel,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                StripePaymentService $StripePaymentService,
                                GeneralService $GeneralService,
                                HelperService $HelperService,
                                RetailerModel $RetailerModel
    							)
    {
    	$this->BaseModel               = $RetailerQuotesModel;                        	
    	$this->UserModel               = $UserModel;
        $this->MakerModel              = $MakerModel;
        $this->RefundModel             = $RefundModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;
        $this->StripePaymentService    = $StripePaymentService;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->RetailerModel           = $RetailerModel;
        $this->GeneralService          = $GeneralService;
        $this->HelperService           = $HelperService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Retailer Cancelled Orders";
    	$this->module_view_folder      = 'admin.cancel_orders';
        $this->admin_panel_slug        = config('app.project.admin_panel_slug');
        $this->module_url_path         = url($this->admin_panel_slug.'/cancel_orders');
        $this->module_url              = url('/');
    }

    public function index(Request $request)
    {
       $obj_refund_status      = $this->BaseModel->select('refund_status')->pluck('refund_status');                               
        if($obj_refund_status)
        {
            $arr_refund_status = $obj_refund_status->toArray();
        }

        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Retailer Cancelled Orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_url']      = $this->module_url;
        $this->arr_view_data['arr_refund_status'] = isset($arr_refund_status) ? $arr_refund_status: [];
        $this->arr_view_data['curr_panel_slug'] = $this->admin_panel_slug;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_enquiries(Request $request)
    { 
      $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
      $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

      $user_tbl_name                = $this->UserModel->getTable();
      $prefixed_user_tbl 			= DB::getTablePrefix().$this->UserModel->getTable();

      $maker_tbl_name               = $this->MakerModel->getTable();
      $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

      $retailer_tbl_name               = $this->RetailerModel->getTable();
      $prefixed_retailer_tbl           = DB::getTablePrefix().$this->RetailerModel->getTable(); 


      $transaction_mapping_table     = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".

                                                $prefixed_transaction_mapping_tbl.".id as tid,".
                                                $prefixed_transaction_mapping_tbl.".transaction_status,".

                                                $prefixed_maker_tbl.'.user_id as mid,'.
                                                $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.

                                                "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_retailer_tbl,$prefixed_retailer_tbl.'.user_id','=',$prefixed_user_tbl.'.id')

                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                                 // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                 ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($retailer_quotes_tbl_name,$prefixed_transaction_mapping_tbl){
                                    $join->on($retailer_quotes_tbl_name.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($retailer_quotes_tbl_name.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');
                                    })

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)
                                
                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');
                                
         
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');

              
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
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
            $obj_qutoes = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_type']) && $arr_search_column['q_payment_type']!="")
        {
            $search_term = $arr_search_column['q_payment_type'];
            $obj_qutoes  = $obj_qutoes->where($retailer_quotes_tbl_name.'.is_direct_payment','=',$search_term);
        }

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->having($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term       = $arr_search_column['q_enquiry_date'];
           // $search_term  = date('Y-m-d',strtotime($search_term));
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            $obj_qutoes       = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
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

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {
           /* $search_term  = $arr_search_column['q_payment_status'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');*/

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)
            {
                //$obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','=',0);
                 // Condition added by Harshada On date 29 Aug 2020 Reference by Priyanka mam
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
// dd($arr_search_column['q_refund_status']);
        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
           
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $order_from_date);
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);


        } 
        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status'] == 0)
            {
                // dd($arr_search_column['q_refund_status']);
                $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_confirmation','1');
            }

        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && $arr_search_column['q_order_to_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $order_from_date);

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 

      if(isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && $arr_search_column['q_order_from_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];
          
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $order_to_date);
            
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 

         if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="" && $arr_search_column['q_order_from_date']=="" && $arr_search_column['q_order_to_date']=="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_status'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 
         if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];

            if ($search_term_refund_status == '0') {

                 $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status)->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '2');

               
            }
            else{

                $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);

            }
        } 

        $total_amt = 0;      
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_wholesale_price;
        }, 0);
        // dd($obj_qutoes->get()->toArray());
        $current_context = $this;

        $json_result  = Datatables::of($obj_qutoes);
		
		$json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            //return date('d-M-Y',strtotime($data->created_at));
                            return us_date_format($data->created_at);

                        })
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            
                            $payment = isset($data->transaction_status)?$data->transaction_status:1; 

                            if($payment == 1)
                            {
                                $payment_status = '<span class="label label-warning">Pending</span>';

                            }
                            elseif($payment == 2)
                            {
                                $payment_status = '<span class="label label-success">Paid</span>';
                            }elseif ($payment == 3) 
                            {
                                $payment_status = '<span class="label label-danger">Failed</span>';
                            }

                            return $payment_status;
                     
                        })
                        ->editColumn('order_no',function($data) use ($current_context)
                        {   

                          $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                          return $order_no = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" href="'.$view_href.'">'.$data->order_no.'</a>';
                     
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
                        ->editColumn('build_refund_btn',function($data) use ($current_context)
                        {   
                            //get unread messages count
                            $build_refund_action = "";
                            if($data->transaction_status == 2 && $data->refund_status == 0 && $data->is_direct_payment == 0)
                            {
                                $build_refund_action = '<a  href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund payment" class="btn btn-circle btn-outline btn-success show-tooltip" onclick="refundProcess('.$data->id.')">Refund</a>';   
                            }
                            elseif ($data->refund_status == 1) {

                                $build_refund_action = '<a href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund payment" class="btn btn-circle btn-outline btn-success show-tooltip">Refund Paid</a>';
                            }elseif($data->transaction_status == 2 && $data->refund_status == 0 && $data->is_direct_payment == 1)
                            {
                                 $build_refund_action = '<a href="javascript:void(0)" data-toggle="tooltip"  data-size="small" title="Refund Payment Pending" class="btn btn-circle btn-outline btn-success show-tooltip">Refund Pending</a>';   
                            }else
                            {
                                $build_refund_action = '<b>-</b>';  
                            }

                            

                            return $build_action = $build_refund_action;
                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {   
                            //get unread messages count
                            

                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            
                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-outline btn-success show-tooltip" href="'.$view_href.'">View</a>';

                            

                            return $build_action = $build_view_action;
                        });

        $build_result = $json_result->make(true)->getData();
        $build_result->total_amt = $total_amt;
       
        return response()->json($build_result);
    }


    public function get_export_cancel_orders(Request $request)
    {
      $retailer_quotes_tbl_name     = $this->BaseModel->getTable();        
      $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

      $user_tbl_name                = $this->UserModel->getTable();
      $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

      $maker_tbl_name               = $this->MakerModel->getTable();
      $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

      $retailer_tbl_name               = $this->RetailerModel->getTable();
      $prefixed_retailer_tbl           = DB::getTablePrefix().$this->RetailerModel->getTable(); 


      $transaction_mapping_table     = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".

                                                $prefixed_transaction_mapping_tbl.".id as tid,".
                                                $prefixed_transaction_mapping_tbl.".transaction_status,".

                                                $prefixed_maker_tbl.'.user_id as mid,'.
                                                $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_retailer_tbl.'.store_name,'.

                                                "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_retailer_tbl,$prefixed_retailer_tbl.'.user_id','=',$prefixed_user_tbl.'.id')

                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                                 // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                 ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($retailer_quotes_tbl_name,$prefixed_transaction_mapping_tbl){
                                    $join->on($retailer_quotes_tbl_name.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($retailer_quotes_tbl_name.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');
                                    })

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)
                                
                                ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');
                                
         
        /* ---------------- Filtering Logic ----------------------------------*/                    
        // $arr_search_column = $request->input('column_filter');
        $arr_search_column = $request->all();

              
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
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
            $obj_qutoes = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->having($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term       = $arr_search_column['q_enquiry_date'];
           // $search_term  = date('Y-m-d',strtotime($search_term));
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            $obj_qutoes       = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
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

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {
           /* $search_term  = $arr_search_column['q_payment_status'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');*/

            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)
            {
                //$obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','=',0);
                 // Condition added by Harshada On date 29 Aug 2020 Reference by Priyanka mam
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
// dd($arr_search_column['q_refund_status']);
        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
           
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $order_from_date);
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $order_to_date);

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);


        } 
        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status'] == 0)
            {
                // dd($arr_search_column['q_refund_status']);
                $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.maker_confirmation','1');
            }

        if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && $arr_search_column['q_order_to_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_from_date   = $arr_search_column['q_order_from_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];

            $order_from_date               = DateTime::createFromFormat('m/d/Y',$search_term_order_from_date);
            $order_from_date               = $order_from_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $order_from_date);

            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 

      if(isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="" && $arr_search_column['q_order_from_date']=="" && isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="")
        {
            $search_term_order_to_date     = $arr_search_column['q_order_to_date'];
            $search_term_refund_status     = $arr_search_column['q_refund_status'];
          
            $order_to_date                 = DateTime::createFromFormat('m/d/Y',$search_term_order_to_date);
            $order_to_date                 = $order_to_date->format('Y-m-d');
           
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $order_to_date);
            
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 

         if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status']!="" && $arr_search_column['q_order_from_date']=="" && $arr_search_column['q_order_to_date']=="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_status'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);
        } 
         if(isset($arr_search_column['q_refund_field']) && $arr_search_column['q_refund_field']!="")
        {
            $search_term_refund_status     = $arr_search_column['q_refund_field'];

            if ($search_term_refund_status == '0') {

                 $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status)->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '2');

               
            }
            else{

                $obj_qutoes   = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.refund_status', $search_term_refund_status);

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

            // $data->transaction_status == 2 && $data->refund_status == 0 && $data->is_direct_payment == 0
            $refund_status = "--";

            if($value->transaction_status == 2 && $value->refund_status == 0 && $value->is_direct_payment == 0)
            {
                $refund_status = 'Pending';
            }else if($value->refund_status == 1)
            {
                $refund_status = 'Paid';
            }else if($value->transaction_status == 2 && $value->refund_status == 0 && $value->is_direct_payment == 1)
            {
                $refund_status = 'Pending';
            }
            else
            {
                $refund_status = "--";
            }

            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Date']            = $value->created_at;
            $arrayResponseData['Retailer']              = $value->store_name; 
            $arrayResponseData['Vendor']                = $value->company_name;      
            $arrayResponseData['Total Amount ($)']      = $value->total_wholesale_price;
            $arrayResponseData['Retailer Payment Status'] = $payment_status;
            $arrayResponseData['Refund Status']         = $refund_status;
            
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
    	$enquiry_arr = $arr_refund_detail = $orderCalculationData = [];

    	/*$enquiry_obj = $this->BaseModel->with(['quotes_details.product_details','user_details','maker_data','transaction_mapping'=>function($query){
                                $query->select('user_id','id','brand_name');
                                    }])
    								   ->where('id',$enquiry_id)->first();	*/

        $enquiry_obj = $this->BaseModel->with(['quotes_details.product_details.brand_details','maker_details','maker_data','transaction_mapping','user_details','user_details.retailer_details'])
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

    public function refund_payment(Request $request)
    {
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

        $transactionDetails = TransactionMappingModel::where('order_id',$order_id)->where('order_no',$getOrderDetails['order_no'])->first();

       
        if ($transactionDetails) {
            $refundDetails = $this->StripePaymentService->retrieve_refund($transactionDetails['transaction_id'],num_format($transactionDetails['amount']),$stripe_secret_key);

            
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
                $arr_notify_data['from_user_id'] = get_admin_id();
                $arr_notify_data['to_user_id']   = $getOrderDetails['retailer_id'] or '';

                $arr_notify_data['description']  = 'For your canceled order('.$getOrderDetails['order_no'].') refund is initiated, it will be reflected within 5 to 10 business days. Transaction id: '.$refundDetails['balance_transaction'];
                $arr_notify_data['title']        = 'Payment Refund';
                $arr_notify_data['type']         = 'retailer';  
                $arr_notify_data['link']         = '';  

                $this->GeneralService->save_notification($arr_notify_data);

                $response['status'] = 'success';
                $response['msg']    = 'Refund is initiated it will reflect to retailer account within 5 to 10 business days.';
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
