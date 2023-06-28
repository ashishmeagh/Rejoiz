<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RetailerModel;
use App\Models\RetailerQuotesModel;
use App\Models\RoleUsersModel;
use App\Models\StripeTransactionModel;
use App\Models\TransactionMappingModel;
use App\Models\RetailerQuotesProductModel;

use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\SalesManagerModel;

use DB;
use Datatables;

/*
	Author - Bhavana
	Date   - 11 May 2020
*/

class AllOrderController extends Controller
{
    public function __construct(
    							UserModel $UserModel,
    							MakerModel $MakerModel,
    							RetailerModel $RetailerModel,
    							RetailerQuotesModel $RetailerQuotesModel,
    							RoleUsersModel $RoleUsersModel,
    							StripeTransactionModel $StripeTransactionModel,
    							TransactionMappingModel $TransactionMappingModel,
    							RetailerQuotesProductModel $RetailerQuotesProductModel,
    							RepresentativeLeadsModel $RepresentativeLeadsModel,
    							RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
    							SalesManagerModel $SalesManagerModel
    						)
    {

        $this->UserModel                  = $UserModel;
        $this->MakerModel                 = $MakerModel;
        $this->RetailerModel              = $RetailerModel;
        $this->RetailerQuotesModel        = $RetailerQuotesModel;
        $this->RoleUsersModel             = $RoleUsersModel;
    	$this->StripeTransactionModel     = $StripeTransactionModel;
        $this->TransactionMappingModel    = $TransactionMappingModel; 
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->SalesManagerModel          = $SalesManagerModel;

    	$this->arr_view_data      = [];
        $this->module_title       = "All Orders";
        $this->module_view_folder = "admin.all_orders";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/all_orders");    
        $this->curr_panel_slug    = config('app.project.admin_panel_slug');
    }

    /*Author - Bhavana (Do not touch the code)*/
    public function index()
    {
    	$this->arr_view_data['page_title']          = $this->module_title;
        $this->arr_view_data['module_title']        = $this->module_title;
        $this->arr_view_data['module_url_path']     = $this->module_url_path;

        return view($this->module_view_folder.'.index', $this->arr_view_data);
    }

    /*Author - Bhavana (Do not touch the code)*/
    public function get_all_orders(Request $request)
    {
    	$representative_leads_tbl             	= $this->RepresentativeLeadsModel->getTable();        
      	$prefix_representative_leads_tbl    	= DB::getTablePrefix().$representative_leads_tbl;

      	$representative_leads_pro_tbl          	= $this->RepresentativeProductLeadsModel->getTable();
      	$prefix_representative_leads_pro_tbl	= DB::getTablePrefix().$representative_leads_pro_tbl;

      	$retailer_quotes_tbl    				= $this->RetailerQuotesModel->getTable();        
      	$prefix_retailer_quotes_tbl 			= DB::getTablePrefix().$retailer_quotes_tbl;

      	$retailer_quotes_details_tbl            = $this->RetailerQuotesProductModel->getTable();
      	$prefix_retailer_quotes_details_tbl     = DB::getTablePrefix().$retailer_quotes_details_tbl;

      	$sales_manager_tbl                	   	= $this->SalesManagerModel->getTable();        
      	$prefix_sales_manager_tbl            	= DB::getTablePrefix().$sales_manager_tbl;

      	$maker_tbl                    			= $this->MakerModel->getTable();
      	$prefix_maker_tbl          		    	= DB::getTablePrefix().$maker_tbl; 

      	$retailer_tbl               			= $this->RetailerModel->getTable();
      	$prefix_retailer_tbl          			= DB::getTablePrefix().$retailer_tbl;

      	$transaction_mapping_tbl          		= $this->TransactionMappingModel->getTable();
      	$prefix_transaction_mapping_tbl 		= DB::getTablePrefix().$transaction_mapping_tbl;

      	$stripe_transaction_tbl            		= $this->StripeTransactionModel->getTable();
      	$prefix_stripe_transaction_tbl 			= DB::getTablePrefix().$stripe_transaction_tbl;

      	$user_tbl       						= $this->UserModel->getTable();
      	$prefix_user_tbl 						= DB::getTablePrefix().$user_tbl;

      	$obj_rep_orders  = DB::table($representative_leads_tbl)
                                ->select(DB::raw($prefix_representative_leads_tbl.".id,".  
                                				 $prefix_representative_leads_tbl.".order_no,". 
                                				 $prefix_representative_leads_tbl.".ship_status,". 
                                				 $prefix_representative_leads_tbl.".order_cancel_status,". 
                                				 $prefix_representative_leads_tbl.".total_retail_price,". 
                                				 $prefix_representative_leads_tbl.".total_wholesale_price,". 
                                				 $prefix_representative_leads_tbl.".created_at as order_date,". 
                                                 $prefix_maker_tbl.'.company_name,'.

                                                 $prefix_transaction_mapping_tbl.".transaction_status,".

                                                 "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                                                          .$prefix_user_tbl.".last_name) as rep_name,".

                                                  'null as sales_manager_name,'.
                                                  'null as retailer_name'
                                                ))                                
                                ->leftjoin($user_tbl,$prefix_user_tbl.'.id','=',$prefix_representative_leads_tbl.'.representative_id')

                                ->leftjoin($maker_tbl,$prefix_maker_tbl.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefix_transaction_mapping_tbl,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping_tbl){

                                    $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping_tbl.'.order_id')
                                    ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping_tbl.'.order_no');
                                })

                                ->where($prefix_representative_leads_tbl.'.representative_id','!=' ,0)

                                ->orderBy($prefix_representative_leads_tbl.".id",'DESC');

        $obj_sales_orders =  DB::table($representative_leads_tbl)
                                ->select(DB::raw($prefix_representative_leads_tbl.".id,".  
                                				 $prefix_representative_leads_tbl.".order_no,". 
                                				 $prefix_representative_leads_tbl.".ship_status,". 
                                				 $prefix_representative_leads_tbl.".order_cancel_status,". 
                                				 $prefix_representative_leads_tbl.".total_retail_price,". 
                                				 $prefix_representative_leads_tbl.".total_wholesale_price,".   
                                				 $prefix_representative_leads_tbl.".created_at as order_date,".
                                                 
                                                $prefix_maker_tbl.'.company_name,'.

                                                $prefix_transaction_mapping_tbl.".transaction_status,".

                                               	"null as rep_name,".

                                                "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                                                          .$prefix_user_tbl.".last_name) as sales_manager_name,".
                                                "null as retailer_name"
                                                     
                                                ))                                
                                ->leftjoin($user_tbl,$prefix_user_tbl.'.id','=',$prefix_representative_leads_tbl.'.sales_manager_id')

                                ->leftjoin($maker_tbl,$prefix_maker_tbl.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefix_transaction_mapping_tbl,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping_tbl){

                                    $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping_tbl.'.order_id')
                                    ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping_tbl.'.order_no');
                                })

                                ->where($prefix_representative_leads_tbl.'.sales_manager_id','!=' ,0)

                                ->orderBy($prefix_representative_leads_tbl.".id",'DESC'); 

        $obj_retailer_orders = DB::table($retailer_quotes_tbl)
                                ->select(DB::raw($prefix_retailer_quotes_tbl.".id,".  
                                				 $prefix_retailer_quotes_tbl.".order_no,". 
                                				 $prefix_retailer_quotes_tbl.".ship_status,". 
                                				 $prefix_retailer_quotes_tbl.".order_cancel_status,". 
                                				 $prefix_retailer_quotes_tbl.".total_retail_price,". 
                                				 $prefix_retailer_quotes_tbl.".total_wholesale_price,". 
                                				 $prefix_retailer_quotes_tbl.".created_at as order_date,". 
                                                 $prefix_maker_tbl.'.company_name,'.

                                                 $prefix_transaction_mapping_tbl.".transaction_status,".

                                                 "null as rep_name,".
                                                 "null as sales_manager_name,".
                                                 "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                                                          .$prefix_user_tbl.".last_name) as retailer_name"
                                                ))                                
                                ->leftjoin($user_tbl,$prefix_user_tbl.'.id','=',$prefix_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($maker_tbl,$prefix_maker_tbl.'.user_id','=',$prefix_retailer_quotes_tbl.'.maker_id')

                                ->leftjoin($prefix_transaction_mapping_tbl,function($join) use($prefix_retailer_quotes_tbl,$prefix_transaction_mapping_tbl){

                                    $join->on($prefix_retailer_quotes_tbl.'.id','=',$prefix_transaction_mapping_tbl.'.order_id')
                                    ->on($prefix_retailer_quotes_tbl.'.order_no','=',$prefix_transaction_mapping_tbl.'.order_no');
                                })

                                ->orderBy($prefix_retailer_quotes_tbl.".id",'DESC');

        $obj_all_orders = $obj_rep_orders->union($obj_sales_orders);

        $obj_all_orders = $obj_all_orders->union($obj_retailer_orders);

        $obj_all_orders = $obj_all_orders->orderBy("order_date",'DESC');

        // dd($obj_all_orders->get()->toArray());

        $current_context = $this;

        $json_result  = \Datatables::of($obj_all_orders);
        
        /* Modifying Columns */
        // $json_result =  $json_result
        //                 ->editColumn('order_no',function($data) use ($current_context)
        //                 {
        //                     //us_date_format($data->created_at);
        //                     return $order_no = $data->order_no;
        //                 })
        //                 ->editColumn('order_date',function($data) use ($current_context)
        //                 {
        //                     //us_date_format($data->created_at);
        //                     return $order_date = us_date_format($data->created_at);
        //                 })     
        //                 ->editColumn('vendor',function($data) use ($current_context)
        //                 {   
        //                     $company_name = '';

        //                     if(isset($data->company_name) && $data->company_name!='')
        //                     {
        //                        $company_name = $data->company_name;
        //                     }
                            
        //                     return $company_name;
        //                 })

        //                 ->editColumn('total_amount',function($data) use ($current_context)
        //                 {  
        //                     if(isset($data->total_wholesale_price) && $data->total_wholesale_price!='')
        //                     {
        //                        $total_amount = $data->total_wholesale_price;
        //                     }
        //                     else
        //                     {
        //                         $total_amount = 'N/A';
        //                     }
                            
        //                     return $total_amount;
        //                 })
        //                 ->editColumn('payment_status',function($data) use ($current_context)
        //                 {   
        //                     if($data->maker_commission_status == '1')
        //                     {
        //                        $vendor_payment_status = '<span class="label label-success">Paid</span>';
        //                     }
        //                     else
        //                     {
        //                         $vendor_payment_status = '<span class="label label-warning">Pending</span>';
        //                     }

        //                     return $vendor_payment_status;
                     
        //                 })
        //                 ->make(true);

        // $build_result = $json_result->getData();
         
        return response()->json($build_result);
    }

}
