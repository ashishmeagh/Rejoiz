<?php

namespace App\Http\Controllers\Influencer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\CustomerOrderService;
use App\Common\Services\HelperService;
use App\Models\CustomerQuotesModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\MakerModel;
use App\Models\CustomerModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\TransactionMappingModel;
use App\Models\StripeTransactionModel;
use App\Common\Services\GeneralService;


use DB;
use Sentinel;
use DataTable;

class CustomerOrdersController extends Controller
{
     public function __construct(   RetailerQuotesModel $RetailerQuotesModel,
                                	CustomerQuotesModel $CustomerQuotesModel,
                                	MakerModel $MakerModel,
                                	CustomerModel $CustomerModel,
                                	CustomerQuotesProductModel $CustomerQuotesProductModel,
                                	TransactionMappingModel $TransactionMappingModel,
                                	StripeTransactionModel $StripeTransactionModel,
                                    GeneralService $GeneralService,
                                    HelperService $HelperService,
                                    CustomerOrderService $CustomerOrderService,
                                    RetailerQuotesProductModel $RetailerQuotesProductModel
    						)
    {
    	$this->BaseModel                  = $RetailerQuotesModel;
        $this->CustomerQuotesModel        = $CustomerQuotesModel;
        $this->MakerModel                 = $MakerModel;
        $this->CustomerModel              = $CustomerModel;
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->StripeTransactionModel     = $StripeTransactionModel;
        $this->GeneralService             = $GeneralService;
        $this->CustomerOrderService       = $CustomerOrderService;
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->HelperService                  = $HelperService;
    	$this->arr_view_data 	       = [];
    	$this->module_title            = "Customer Orders";
        $this->module_view_folder      = "influencer.customer_orders";
        $this->module_url_path         = url(config('app.project.influencer_panel_slug')."/customer_orders");    
        $this->influencer_panel_slug   = config('app.project.influencer_panel_slug');
    }

    public function index()
    {   
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Customer Orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_customer_orders_listing(Request $request)
    {   
    	$loggedInUserId = 0;
        $user           = Sentinel::check();

        if($user){
            $loggedInUserId = $user->id;
        }


        $form_data = $request->all();
       
        $form_data['influencer_id'] = $loggedInUserId;

        $obj_qutoes = $this->CustomerOrderService->get_customer_orders_of_influencer($form_data);

        
        //Calculate total by Harshada on date 09 Sep 2020
         $total_amt = 0;        
         $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
              return $res + $item->total_retail_price;
          }, 0);
        $current_context = $this;

        $json_result  = \Datatables::of($obj_qutoes);
        
        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            
                            return us_date_format($data->created_at);

                        })
                       
                        ->editColumn('promo_code',function($data) use($current_context)
                        {
                            return isset($data->promo_code)?$data->promo_code:'--';
                        })
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            return $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'N/A'; 
                     
                        })

                        ->editColumn('company_name',function($data) use ($current_context){
                            return $company_name = isset($data->company_name)?$data->company_name:'N/A';
                        })
                        ->editColumn('customer_name',function($data) use ($current_context){
                            return $company_name = isset($data->first_name)?$data->first_name." ".$data->last_name:'N/A';
                        })

                        ->editColumn('product_html',function($data) use ($current_context)
                        {   

                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                            $products_arr = get_quote_products($id);

                            return $product_html = $this->GeneralService->order_products_for_list($id,$order_no,$products_arr);

                           /*  $products_arr = [];
                            $products_arr = get_customer_quote_products($data->id);

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
                        ->editColumn('vendor_payment_status',function($data) use ($current_context)
                        {   
                            
                            if($data->stripe_trxn_status == '2')
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
                            }

                            return $vendor_payment_status;
                     
                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {   
                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            
                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" target="_blank" href="'.$view_href.'">View</a>';
                            
                            return $build_action = $build_view_action;
                        });

        $build_result = $json_result->make(true)->getData();
        $build_result->total_amt = $total_amt;
        return response()->json($build_result);
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

    public function get_retailer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }


}
