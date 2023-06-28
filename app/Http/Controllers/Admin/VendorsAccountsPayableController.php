<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RepresentativeLeadsModel;
use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RetailerModel;
use App\Models\StripeTransactionModel;
use App\Common\Services\CommissionService;

use Session;
use DB;
use Datatables;
use Excel, Validator;


class VendorsAccountsPayableController extends Controller
{
    public function __construct(RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                UserModel $UserModel,
                                MakerModel $MakerModel,
                                RetailerModel $RetailerModel,
                                StripeTransactionModel $StripeTransactionModel,
                                CommissionService $CommissionService,
                               )
    {
        $this->arr_view_data      = [];
        $this->module_title       = "All Orders";
        $this->module_view_folder = "admin.all_orders_report";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/all_orders_report"); 
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');
        $this->RetailerQuotesModel      = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
        $this->StripeTransactionModel = $StripeTransactionModel;
        $this->UserModel          = $UserModel;
        $this->MakerModel         = $MakerModel;
        $this->RetailerModel      = $RetailerModel;
        $this->CommissionService  = $CommissionService;
    }


    public function index()
    {   
        $this->arr_view_data['arr_data']            = array();
        $this->arr_view_data['page_title']          = "Manage ".$this->module_title;
        $this->arr_view_data['module_title']        = $this->module_title;
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        return view($this->module_view_folder.'.index', $this->arr_view_data);
    }

    public function getAllOrders(Request $request)
    {
        $representative_leads_tbl_name          = $this->RepresentativeLeadsModel->getTable();        
        $prefixed_representative_leads_tbl      = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

        $retailer_transaction_table          = $this->RetailerQuotesModel->getTable();
        $prefixed_retailer_transaction_table =  DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $maker_tbl_name      = $this->MakerModel->getTable();
        $prefixed_maker_tbl  = DB::getTablePrefix().$this->MakerModel->getTable(); 

        $retailer_table               = $this->RetailerModel->getTable();
        $prefixed_retailer_tbl        = DB::getTablePrefix().$this->RetailerModel->getTable();


        $stripe_transaction_table    = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction = DB::getTablePrefix().$this->StripeTransactionModel->getTable();



        $all_rep_orders = DB::table($representative_leads_tbl_name)
                                ->select(DB::raw($prefixed_representative_leads_tbl.".id,".
                                                  
                                                 $prefixed_representative_leads_tbl.".order_no,".
                                                 $prefixed_representative_leads_tbl.".maker_id,".  
                                                 $prefixed_representative_leads_tbl.".total_wholesale_price,".  
                                                 $prefixed_representative_leads_tbl.".maker_commission_status,".
                                                 $prefixed_representative_leads_tbl.".created_at,".  

                                                 $prefixed_maker_tbl.'.company_name,'.

                                                 $prefixed_stripe_transaction.'.amount,'.
                                                 $prefixed_stripe_transaction.'.quote_id,'.
                                                 $prefixed_stripe_transaction.'.lead_id,'.

                                                 "CONCAT(RP.first_name,' ',RP.last_name) as rep_name,".
                                                 "CONCAT(SM.first_name,' ',SM.last_name) as sales_manager_name"
                                                     
                                ))  

                                ->leftjoin($prefix_user_table.' AS RP','RP.id','=',$prefixed_representative_leads_tbl.'.representative_id')
                      
                                ->leftjoin($prefix_user_table.' AS SM','SM.id','=',$prefixed_representative_leads_tbl.'.sales_manager_id')

                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_representative_leads_tbl.'.maker_id')

                                ->leftjoin($prefixed_stripe_transaction,$prefixed_stripe_transaction.'.quote_id','=',$prefixed_representative_leads_tbl.'.id')

                                ->where($prefixed_representative_leads_tbl.'.is_confirm',1)  

                                ->orderBy($prefixed_representative_leads_tbl.".id",'DESC');
                                                                                                                                                                                              
                               
    
    $retailer_orders = DB::table($retailer_transaction_table)
                           ->select(DB::raw($prefixed_retailer_transaction_table.".id,".
                                            
                                            $prefixed_retailer_transaction_table.".order_no,".
                                            $prefixed_retailer_transaction_table.".maker_id,".
                                            $prefixed_retailer_transaction_table.".total_wholesale_price,".  
                                            $prefixed_retailer_transaction_table.".maker_commission_status,". 
                                            $prefixed_retailer_transaction_table.".created_at,".

                                            $prefixed_maker_tbl.'.company_name,'.

                                            $prefixed_stripe_transaction.'.amount,'.
                                            $prefixed_stripe_transaction.'.quote_id,'.
                                            $prefixed_stripe_transaction.'.lead_id,'.
                                         
                                            "CONCAT(RT.first_name,' ',RT.last_name) as rep_name,".

                                            "CONCAT(RT.address,' ',RT.post_code) as sales_manager_name"         
                                            )) 

                           
                            ->leftjoin($prefix_user_table.' AS RT','RT.id','=',$prefixed_retailer_transaction_table.'.retailer_id')
                      
                            ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_retailer_transaction_table.'.maker_id')

                            ->leftjoin($prefixed_stripe_transaction,$prefixed_stripe_transaction.'.lead_id','=',$prefixed_retailer_transaction_table.'.id')

                            ->orderBy($prefixed_retailer_transaction_table.".id",'DESC');                  

        $all_orders = $all_rep_orders->union($retailer_orders);
      
        $all_orders = $all_orders->orderBy("id",'DESC');

        $arr_search_column = $request->input('column_filter');

        $formData['commissionStatus'] = isset($arr_search_column['commission_status'])?$arr_search_column['commission_status']:false;

        /*get total commissions*/
        $total_commission_amount = $this->calculateTotalCommission('all',$formData,$arr_search_column);  

        /*-----------------------*/

  
        $current_context = $this;

        $json_result  = \Datatables::of($all_orders);
            
        /* Modifying Columns */
        $json_result =  $json_result->editColumn('id',function($data) use ($current_context)
                        {
                            return $data->id;
                        })
                        ->editColumn('order_no',function($data) use ($current_context)
                        {
                            //us_date_format($data->created_at);
                            return $order_no = $data->order_no;
                        })

                        ->editColumn('total_commission_pending',function($data) use ($total_commission_amount)
                        {
                            return num_format($total_commission_amount['totalCommissionPending']);
                        })

                        ->editColumn('total_commission_paid',function($data) use ($total_commission_amount)
                        {
                            return num_format($total_commission_amount['totalCommissionPaid']);
                        })

                        ->editColumn('order_date',function($data) use ($current_context)
                        {
                            //us_date_format($data->created_at);
                            return $order_date = us_date_format($data->created_at);
                        })

                        ->editColumn('vendor_commission_amount',function($data) use ($current_context)
                        {
                            $vendorCommissionAmount = 0.0;
                            
                            $adminCommission  = isset($data->admin_commission)?$order->admin_commission:0;

                            if($adminCommission == 0)
                            {
                                $adminCommission = $this->CommissionService->get_admin_commission();
                            }

                            if(isset($data->maker_commission_status) && $data->maker_commission_status ==1)
                            {
                               $vendorCommissionAmount = $data->amount;
                            }
                            else
                            {
                                $totalPrice            = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                                $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                                $vendorCommissionAmount = $data->total_wholesale_price-$adminCommissionAmount;
                            }

                           

                            return num_format($vendorCommissionAmount);
                        })


                        
                        ->editColumn('order_placed_by',function($data) use ($current_context)
                        {   
                            $user_name = '';
                            if(isset($data->sales_manager_name) && $data->sales_manager_name!='')
                            {
                               $user_name = $data->sales_manager_name;
                            }
                            elseif(isset($data->rep_name) && $data->rep_name!='')
                            {
                              $user_name = $data->rep_name;
                            }
                            else
                            {
                                $user_name = '';
                            }
                            
                            return $user_name;
                        })
                        ->editColumn('vendor',function($data) use ($current_context)
                        {   
                            $company_name = '';

                            if(isset($data->company_name) && $data->company_name!='')
                            {
                               $company_name = $data->company_name;
                            }
                            
                            return $company_name;
                        })

                        ->editColumn('total_amount',function($data) use ($current_context)
                        {  
                            if(isset($data->total_wholesale_price) && $data->total_wholesale_price!='')
                            {
                               $total_amount = $data->total_wholesale_price;
                            }
                            else
                            {
                                $total_amount = 'N/A';
                            }
                            
                            return $total_amount;
                        })

        
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            if($data->maker_commission_status == '1')
                            {
                               $vendor_payment_status = '<span class="label label-success">Paid</span>';
                            }
                            else
                            {
                                $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                            }

                            return $vendor_payment_status;
                     
                        })
 
                       
                        ->make(true);

        $build_result = $json_result->getData();
         
        return response()->json($build_result);      
                     
    }


    public function calculateTotalCommission($status,$formData,$arr_search_column=false)
    {
        $totalCommission = $totalOrderAmount = 0;
        $totalAmounts    = [];
        $totalAmounts['totalCommission'] = 0;
        $totalAmounts['totalCommissionPending'] = $totalAmounts['totalCommissionPaid'] = $totalAmounts['totalOrderAmountPending'] = $totalAmounts['totalOrderAmountPaid']= 0;

        if (isset($status)) {
            
           
           // if ($status == 'retailer') {

                $objOrders = $this->retailer_order_query($formData,$arr_search_column);

                if ($objOrders) {

                    $orders = $objOrders->get()->toArray();

                    foreach ($orders as $key => $order) {

                        if ($order->status == '') {

                            

                        $adminCommission = isset($order->admin_commission)?$order->admin_commission:0;

                        if($adminCommission == 0)
                        {
                          $adminCommission  = $this->CommissionService->get_admin_commission();
                        }

                        $totalPrice       = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $totalAmounts['totalCommissionPending']       += $totalPrice - $adminCommissionAmount;
                        
                        // $totalAmounts['totalOrderAmount']   += $totalPrice;
                            
                        }
                        else{

                            $totalAmounts['totalCommissionPaid'] += $order->amount;
                            
                        }
                        //-------------get commission totals-----------------
                        if ($order->transaction_status == '') {

                            $totalAmounts['totalOrderAmountPending']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                        }
                        else{

                            $totalAmounts['totalOrderAmountPaid']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                        }
                    }
                }
           // }
        }
        return $totalAmounts;
    }

}
