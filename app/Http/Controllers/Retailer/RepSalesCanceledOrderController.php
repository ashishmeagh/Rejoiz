<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RepresentativeLeadsModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\RetailerModel;
use App\Models\RefundModel;
use App\Models\MakerModel;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;

use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;

class RepSalesCanceledOrderController extends Controller
{
    public function __construct(RepresentativeLeadsModel $RepresentativeLeadsModel,
    							UserModel $UserModel,
                                RetailerModel $RetailerModel,
                                RefundModel $RefundModel,
                                TransactionMappingModel $TransactionMappingModel,
                                TransactionsModel $TransactionsModel,
                                MakerModel $MakerModel,
                                HelperService $HelperService,
                                GeneralService $GeneralService				
    							)
    {
    	$this->BaseModel               = $RepresentativeLeadsModel;
        $this->UserModel               = $UserModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->TransactionsModel       = $TransactionsModel;
        $this->RetailerModel           = $RetailerModel;
        $this->RefundModel             = $RefundModel;
        $this->MakerModel              = $MakerModel;
        $this->GeneralService          = $GeneralService;
        $this->HelperService           = $HelperService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Rep/Sales Cancelled Orders";
    	$this->module_view_folder      = 'retailer.rep_sales_cancel_orders';
        $this->retailer_panel_slug     = config('app.project.retailer_panel_slug');
        $this->module_url_path         = url($this->retailer_panel_slug.'/rep_sales_cancel_orders');
    }

    public function index(Request $request)
    {
        
        //$retailer_id = $request->input('retailer_id',null);
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = $this->module_title;
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        //$this->arr_view_data['retailer_id']      = $retailer_id;

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
        
        $leads_table          = $this->BaseModel->getTable();        
        $prefixed_leads_table = DB::getTablePrefix().$this->BaseModel->getTable();

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl 			= DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table             = $this->RetailerModel->getTable();
        $prefix_retailer_table      = DB::getTablePrefix().$retailer_table;


        $maker_tbl                  = $this->MakerModel->getTable();        
        $prefixed_maker_tbl         = DB::getTablePrefix().$this->MakerModel->getTable();


        $obj_qutoes = DB::table($leads_table)
                        ->select(DB::raw($prefixed_leads_table.".*,".
                                        $prefixed_transaction_mapping_tbl.".id as tid,".
                                        $prefixed_transaction_mapping_tbl.".transaction_status,".
                                        $prefix_retailer_table.'.store_name,'.

                                        $prefixed_maker_tbl.".brand_name,".
                                        $prefixed_maker_tbl.".company_name,".
                                       

                                        " CONCAT(RS.first_name,' ',RS.last_name) as sales_user_name,". 
                                        " CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"

                                    ))

                              /*  ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_leads_table.'.retailer_id')*/

                              ->leftJoin($prefixed_user_tbl." AS RL", 'RL.id','=',$prefixed_leads_table.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_leads_table.'.retailer_id')

                                // ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_leads_table.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                 ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_leads_table,$prefixed_transaction_mapping_tbl){

                                      $join->on($prefixed_leads_table.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                      ->on($prefixed_leads_table.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                    })   

                                ->leftjoin($prefixed_maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_leads_table.'.maker_id')

                                ->leftJoin($prefixed_user_tbl." AS RR", 'RR.id','=',$prefixed_leads_table.'.representative_id')

                                ->leftJoin($prefixed_user_tbl." AS RS", 'RS.id','=',$prefixed_leads_table.'.sales_manager_id')


                                ->where($prefixed_leads_table.'.order_cancel_status','=',2)

                                ->where($prefixed_leads_table.'.retailer_id',$loggedInUserId)
                                ->groupBy($prefixed_leads_table.".id")
                                // ->orderBy($prefixed_leads_table.".id",'DESC');
                                ->orderBy($prefixed_leads_table.".created_at",'DESC');
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.retailer_id',$retailer_id)->where($prefixed_leads_table.'.retailer_id',$loggedInUserId);
        }
        else
        {
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.retailer_id',$loggedInUserId);   
        } 
         

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
            
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {  
            $search_term  = $arr_search_column['q_order_no'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_leads_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

       
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_rep_sales_name']) && $arr_search_column['q_rep_sales_name']!="")
        {
            $search_term  = $arr_search_column['q_rep_sales_name'];

            $obj_qutoes   = $obj_qutoes->having('representative_user_name','LIKE', '%'.$search_term.'%')

                                       ->orhaving('sales_user_name','LIKE', '%'.$search_term.'%');
        }
     

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_qutoes   = $obj_qutoes->where($prefixed_leads_table.'.created_at','LIKE', '%'.$date.'%');
        } 

        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  
          
            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_leads_table.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }

        }

        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status'] != "")
        {
            $search_term      = $arr_search_column['q_refund_status'];
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.refund_status','=', ''.$search_term.'');
        }

        /*search data from  from date and to date*/
        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") || (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];

            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

            
         /*   $search_term_from_date  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));
            $search_term_to_date    = date('Y-m-d', strtotime($arr_search_column['q_to_date']));*/

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_leads_table.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_leads_table.'.created_at', '>=', $from_date);

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
                            $products_arr = get_lead_products($id,$order_no);
                            
                            return  $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);   
                            
                        })

                        ->editColumn('company_name',function($data) use ($current_context)
                        {   
                           return  $company_name = isset($data->company_name) && $data->company_name!='' ?$data->company_name:'N/A';
                        })

                        ->editColumn('order_by',function($data) use ($current_context)
                        {   
                            $order_by = '';

                            if(isset($data->representative_id) && $data->representative_id!=null && $data->representative_id!='')
                            {
                               return  $order_by = isset($data->representative_user_name) && $data->representative_user_name!='' ?$data->representative_user_name:'N/A';
                            }
                            elseif (isset($data->sales_manager_id) && $data->sales_manager_id!=null && $data->sales_manager_id!='')
                            {
                               return  $order_by = isset($data->sales_user_name) && $data->sales_user_name!='' ?$data->sales_user_name:'N/A';
                            }
                            else
                            {
                               return  $order_by= '';
                            }
                          
                           
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

                            
                        })

                        ->editColumn('refund_status',function($data) use ($current_context)
                        {   
                            $refund_status = '-';
                            return $refund_status = ($data->refund_status == 0) ? '<span class="label label-warning">Pending</span>':'<span class="label label-warning">Paid</span>';
                        });


        $build_result = $json_result->make(true)->getData();
         $build_result->total_amt = $total_amt;
        return response()->json($build_result);
    }

     public function get_export_rep_sales_cancel_orders(Request $request)
    { 
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
        
        $leads_table          = $this->BaseModel->getTable();        
        $prefixed_leads_table = DB::getTablePrefix().$this->BaseModel->getTable();

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table             = $this->RetailerModel->getTable();
        $prefix_retailer_table      = DB::getTablePrefix().$retailer_table;


        $maker_tbl                  = $this->MakerModel->getTable();        
        $prefixed_maker_tbl         = DB::getTablePrefix().$this->MakerModel->getTable();


        $obj_qutoes = DB::table($leads_table)
                        ->select(DB::raw($prefixed_leads_table.".*,".
                                        $prefixed_transaction_mapping_tbl.".id as tid,".
                                        $prefixed_transaction_mapping_tbl.".transaction_status,".
                                        $prefix_retailer_table.'.store_name,'.

                                        $prefixed_maker_tbl.".brand_name,".
                                        $prefixed_maker_tbl.".company_name,".
                                       

                                        " CONCAT(RS.first_name,' ',RS.last_name) as sales_user_name,". 
                                        " CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"

                                    ))

                              /*  ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_leads_table.'.retailer_id')*/

                              ->leftJoin($prefixed_user_tbl." AS RL", 'RL.id','=',$prefixed_leads_table.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_leads_table.'.retailer_id')

                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_leads_table.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                ->leftjoin($prefixed_maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_leads_table.'.maker_id')

                                ->leftJoin($prefixed_user_tbl." AS RR", 'RR.id','=',$prefixed_leads_table.'.representative_id')

                                ->leftJoin($prefixed_user_tbl." AS RS", 'RS.id','=',$prefixed_leads_table.'.sales_manager_id')


                                ->where($prefixed_leads_table.'.order_cancel_status','=',2)

                                ->where($prefixed_leads_table.'.retailer_id',$loggedInUserId)
                                ->groupBy($prefixed_leads_table.".id")
                                ->orderBy($prefixed_leads_table.".id",'DESC');
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.retailer_id',$retailer_id)->where($prefixed_leads_table.'.retailer_id',$loggedInUserId);
        }
        else
        {
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.retailer_id',$loggedInUserId);   
        } 
         

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->all();
            
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {  
            $search_term  = $arr_search_column['q_order_no'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_leads_table.'.order_no','LIKE', '%'.$search_term.'%');
        }

       
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term  = $arr_search_column['q_retailer_name'];
            $obj_qutoes   = $obj_qutoes->having('store_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_rep_sales_name']) && $arr_search_column['q_rep_sales_name']!="")
        {
            $search_term  = $arr_search_column['q_rep_sales_name'];

            $obj_qutoes   = $obj_qutoes->having('representative_user_name','LIKE', '%'.$search_term.'%')

                                       ->orhaving('sales_user_name','LIKE', '%'.$search_term.'%');
        }
     

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            

            $obj_qutoes   = $obj_qutoes->where($prefixed_leads_table.'.created_at','LIKE', '%'.$date.'%');
        } 

        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {  
          
            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_leads_table.'.id',DB::table($prefixed_transaction_mapping_tbl)->pluck('order_id'));
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }

        }

        if(isset($arr_search_column['q_refund_status']) && $arr_search_column['q_refund_status'] != "")
        {
            $search_term      = $arr_search_column['q_refund_status'];
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.refund_status','=', ''.$search_term.'');
        }

        /*search data from  from date and to date*/
        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") || (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];

            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

            
         /*   $search_term_from_date  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));
            $search_term_to_date    = date('Y-m-d', strtotime($arr_search_column['q_to_date']));*/

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_leads_table.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_leads_table.'.created_at', '>=', $from_date);

        } 
         //Calculate total by Harshada on date 09 Sep 2020

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

            $arrayResponseData['Order No']            = $value->order_no;
            $arrayResponseData['Order Date']          = $value->created_at;
            $arrayResponseData['Vendor']              = $value->company_name;     
            $arrayResponseData['Rep/Sales']           = $value->sales_user_name;     
            $arrayResponseData['Total Amount ($)']    = $value->total_wholesale_price;
            $arrayResponseData['Payment Status']      = $payment_status;
            $arrayResponseData['Refund Status']       = $refund_status;
            
            array_push($data,$arrayResponseData);
        }

        return Excel::create('Rep Sales Orders', function($excel) use ($data) {
        
        $excel->sheet('Rep Sales Orders', function($sheet) use ($data)
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



       $enquiry_obj = $this->BaseModel->with(['transaction_mapping',
                                               'leads_details.product_details.brand_details',
                                               'retailer_user_details.retailer_details',
                                               'maker_data',
                                               'address_details','maker_details'
                                               ])
                                        ->where('id',$enquiry_id)
                                        ->first();                                   
    	    	
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
            
    		if($enquiry_arr['retailer_id'] != $loggedInUserId)
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

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
        } 

        // dd($orderCalculationData);
      
    	$this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['arr_refund_detail'] = $arr_refund_detail;
    	$this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }




}
