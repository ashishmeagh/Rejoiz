<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\InventoryService;
use App\Common\Services\GeneralService;  
use App\Common\Services\orderDataService;  
use App\Models\RepresentativeLeadsModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\RetailerQuotesModel;
use App\Models\TransactionMappingModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\UserModel;
use App\Models\RetailerModel;
use App\Models\StripeTransactionModel;

use Sentinel;
use DB;
use Flash;

class DashboardController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 27 June 2019
    */

    public function __construct(InventoryService $InventoryService,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                GeneralService $GeneralService,
                                orderDataService $orderDataService,
                                RetailerQuotesModel $RetailerQuotesModel,
                                TransactionMappingModel $TransactionMappingModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                UserModel $UserModel,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                RetailerModel $RetailerModel,
                                StripeTransactionModel $StripeTransactionModel)
    {
    	$this->arr_view_data      = [];
      $this->InventoryService   = $InventoryService;
      $this->GeneralService     = $GeneralService;
      $this->orderDataService     = $orderDataService;
      $this->RepresentativeLeadsModel     = $RepresentativeLeadsModel;
      $this->RetailerQuotesModel = $RetailerQuotesModel;
      $this->UserModel           = $UserModel;
      $this->UserStripeAccountDetailsModel= $UserStripeAccountDetailsModel;
      $this->RetailerModel       = $RetailerModel;
      $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
      $this->TransactionMappingModel = $TransactionMappingModel;
      $this->StripeTransactionModel = $StripeTransactionModel;
    	$this->module_title       = "Dashboard";
    	$this->module_view_folder = 'maker.dashboard';
      $this->maker_panel_slug   = config('app.project.maker_panel_slug');
      $this->module_url_path    = url($this->maker_panel_slug.'/dashboard');
    }

    public function index()
    {
       $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }   

        $obj_strip         =  $this->UserStripeAccountDetailsModel->where('user_id',$loggedIn_userId)
                                                                  ->where('is_active','1')
                                                                  ->first();
        if($obj_strip == null)
        {
          Flash::error('Stripe account details missing, Please add stripe account details.');
          return redirect('vendor/account_settings');
        }
       
        $arr_quote_count  = $orders_arr   = $orders_data    = [];


       
        
        $retailerOrderData = $this->orderDataService->get_retailer_last_seven_days_order($loggedIn_userId);
        $repSalesOrderData = $this->orderDataService->get_rep_sales_last_seven_days_order($loggedIn_userId);
        $customerOrderData = $this->orderDataService->get_customer_last_seven_days_order($loggedIn_userId);

        $orderAmountData['pending_amount']   = $retailerOrderData['pendingAmount'] + $repSalesOrderData['pendingAmount'] + $customerOrderData['pendingAmount'];
        
        $orderAmountData['collected_amount'] = $retailerOrderData['collectedAmount'] + $repSalesOrderData['collectedAmount'] + $customerOrderData['collectedAmount'];
       
        $arr_count['product_count']      = get_maker_product_count($loggedIn_userId);
        $arr_count['reps_order_count']   = $this->reps_order($loggedIn_userId);
        $arr_count['sales_order_count']  = $this->sales_orders($loggedIn_userId);

        // dd($retailerOrderData,$repSalesOrderData);
        $this->arr_view_data['arr_count']       = $arr_count;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Dashboard';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['orders_arr']      = $orders_arr; 
        $this->arr_view_data['orders_data']     = $orders_data; 
        $this->arr_view_data['orderAmountData'] = $orderAmountData; 
      
      

    	  return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function reps_order($maker_id)
    {
      $quote_count = 0;
      if(isset($maker_id) && $maker_id!="" && $maker_id!=0)
      {
        $quote_count = RepresentativeLeadsModel::where('maker_id',$maker_id)
                        ->where('is_confirm',1)
                        ->where('order_cancel_status','!=',2)
                        ->where('is_split_order','=','0')
                        ->count();
      }

      return $quote_count;
    }


    public function sales_orders($maker_id)
    {
        $arr_qutoes = [];
       /* $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $sales_orders = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status"
                                                 ))

                              ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                              
                              ->where($prefixed_retailer_quotes_tbl.'.maker_id',$maker_id)

                              ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',2)
                              ->count();*/

        
        $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $retailer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
        $retailer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl      = DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        $arr_qutoes = DB::table($retailer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                                 $prefixed_transaction_mapping_tbl.".id as tid,".
                                                 $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                 $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".
                                                 $prefix_retailer_table.'.store_name,'.
                                                 $retailer_quotes_details.'.shipping_charge,'.
                                                 $retailer_quotes_details.'.shipping_discount,'.


                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                          .$prefixed_user_tbl.".last_name) as user_name"))
                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_retailer_quotes_tbl.'.retailer_id')

                                ->leftjoin($retailer_quotes_details,$retailer_quotes_details_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')

                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.lead_id')


                                ->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                              
                                ->where($prefixed_retailer_quotes_tbl.'.maker_id',$maker_id)

                                ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',0)
                                ->where($prefixed_retailer_quotes_tbl.'.is_split_order','=','0')

                                ->groupBy($prefixed_retailer_quotes_tbl.".id")->get()->toArray();                        
                       
        
        return $sales_orders = count($arr_qutoes);                 

    }

}
