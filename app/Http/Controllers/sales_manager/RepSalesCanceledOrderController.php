<?php

namespace App\Http\Controllers\sales_manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RepresentativeLeadsModel;
use App\Models\UserModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\RetailerModel;
use App\Models\MakerModel;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;

use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;

class RepSalesCanceledOrderController extends Controller
{
    public function __construct(RepresentativeLeadsModel $RepresentativeLeadsModel,
    							UserModel $UserModel,
                                RetailerModel $RetailerModel,
                                TransactionMappingModel $TransactionMappingModel,
                                TransactionsModel $TransactionsModel,
                                MakerModel $MakerModel,
                                HelperService $HelperService,
                                GeneralService $GeneralService							
    							)
    {
    	$this->BaseModel               = $RepresentativeLeadsModel;      
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;                    	
    	$this->UserModel               = $UserModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
        $this->TransactionsModel       = $TransactionsModel;
        $this->RetailerModel           = $RetailerModel;
        $this->MakerModel              = $MakerModel;
        $this->GeneralService          = $GeneralService;
        $this->HelperService           = $HelperService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Cancelled Orders";
    	$this->module_view_folder      = 'sales_manager.rep_sales_cancel_orders';
        $this->sales_manager_panel_slug = config('app.project.sales_manager_panel_slug');
        $this->module_url_path         = url($this->sales_manager_panel_slug.'/rep_sales_cancel_orders');
    }

    public function index(Request $request)
    {
        
        //$retailer_id = $request->input('retailer_id',null);
        $this->arr_view_data['module_title']     = $this->module_title;
        $this->arr_view_data['page_title']       = 'My Cancelled Orders';
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

        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;



        $obj_qutoes = DB::table($leads_table)
                                ->select(DB::raw($prefixed_leads_table.".*,".
                                                $prefixed_transaction_mapping_tbl.".id as tid,".
                                                $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                $prefix_retailer_table.'.store_name,'.

                                                $prefixed_maker_tbl.".brand_name,".
                                                $prefixed_maker_tbl.".company_name,".

                                				"CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                .$prefixed_user_tbl.".last_name) as user_name"))

                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_leads_table.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_leads_table.'.retailer_id')

                               //->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_leads_table.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')


                            ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefix_representative_leads_tbl,$prefixed_transaction_mapping_tbl){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                            })
                             
                           

                                ->leftjoin($prefixed_maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_leads_table.'.maker_id')


                                ->where($prefixed_leads_table.'.order_cancel_status','=',2)

                                ->where($prefixed_leads_table.'.sales_manager_id',$loggedInUserId)

                                ->orderBy($prefixed_leads_table.".id",'DESC');
        
        
        if(isset($arr_search_column['sales_manager_id']) && $arr_search_column['sales_manager_id']!="" && $arr_search_column['sales_manager_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['sales_manager_id']);
            
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.sales_manager_id',$retailer_id)->where($prefixed_leads_table.'.sales_manager_id',$loggedInUserId);
        }
        else
        {
            $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.sales_manager_id',$loggedInUserId);   
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
               // $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.ship_status','=',0);

                $obj_qutoes = $obj_qutoes->whereNotExists(function($query){
                    $query->select(\DB::raw("
                          transaction_mapping.order_id,
                          transaction_mapping.order_no
                      FROM
                          `transaction_mapping`
                      WHERE
                          `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                    "));
                });

                
            }
            else
            {
               //$obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');

               
                $obj_qutoes = $obj_qutoes->whereExists(function($query){
                    $query->select(\DB::raw("
                          transaction_mapping.order_id,
                          transaction_mapping.order_no
                      FROM
                          `transaction_mapping`
                      WHERE
                          `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                    "));
                });
            }

        }


        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") && (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];

            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

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
                        { //dd($data);
                            return us_date_format($data->created_at);


                        })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            
                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                            
                            $products_arr = get_lead_products($id,$order_no);
                            return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);    


                            /*$products_arr = [];
                            $products_arr = get_quote_products($data->id);

                            if(isset($products_arr) && count($products_arr)>0)
                            {
                                $products = '';

                                foreach ($products_arr as $key => $product) 
                                {
                                    $products .= '<tr>
                                                    <td>'.$product['product_details']['product_name'].'</td>
                                                    <td>'.$product['qty'].'</td>
                                                  </tr>';
                                }
                            }
                            else
                            {
                                $products = 'No Record Found';
                            }

                            return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_product_list($(this))">View Products<span> '.count($products_arr).'</span></a>
            
                                <td colspan="5">
                                    <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                                        <thead>
                                          <tr>
                                            <th>Product Title</th>
                                            <th>Quantity</th>                                
                                          </tr>
                                        </thead>
                                        <tbody>'.$products.'</tbody>
                                      </table>
                                </td>';*/

                        })

                        ->editColumn('company_name',function($data) use ($current_context)
                        {   
                           return  $company_name = isset($data->company_name) && $data->company_name!='' ?$data->company_name:'N/A';
                        })

                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {
                            //get unread messages count
                            $unread_message_count = get_quote_unread_messages_count($data->id,'representative');
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }

                            //check if user is online or not
                            $is_online = check_is_user_online($data->sales_manager_id);

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
            
    		if($enquiry_arr['sales_manager_id'] != $loggedInUserId)
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
      
    	$this->arr_view_data['enquiry_arr']     = $enquiry_arr;
    	$this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Order Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orderCalculationData'] = $orderCalculationData;

    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }




}
