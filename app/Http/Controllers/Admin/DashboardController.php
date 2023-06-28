<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RetailerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\CustomerQuotesModel;
use App\Common\Services\UserService;

use Session;
use DateTime;

class DashboardController extends Controller
{
	public function __construct(UserModel $user,
								UserService $UserService,
                                CustomerQuotesModel $CustomerQuotesModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel
                               )
	{
          
		$this->arr_view_data       = [];
		$this->module_title        = "Dashboard";
		$this->UserModel           = $user;
        $this->CustomerQuotesModel = $CustomerQuotesModel;
		$this->UserService         = $UserService;
        $this->RetailerQuotesModel = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
		$this->module_view_folder  = "admin.dashboard";
		$this->admin_url_path      = url(config('app.project.admin_panel_slug'));
      
    }
   
    public function index()
    {

    	$arr_user_count                   = [];
    	$arr_user_count['maker']          = $this->UserService->get_user_count('maker');
    	$arr_user_count['retailer']       = $this->UserService->get_user_count('retailer');
      $arr_user_count['representative'] = $this->UserService->get_user_count('representative');
    	$arr_user_count['sales_manager']  = $this->UserService->get_user_count('sales_manager');
      $arr_user_count['customer']       = $this->UserService->get_user_count('customer');
      $arr_user_count['products']       = get_product_count();
            
    	$this->arr_view_data['page_title']       = $this->module_title;
    	$this->arr_view_data['admin_url_path']   = $this->admin_url_path;
    	$this->arr_view_data['arr_user_count']   = $arr_user_count;


        /*--------------all orders of current year--------------------------------------*/  

        $first_date_month  = date('Y-m-d', strtotime('first day of january this year'));
        $last_date_month   = date('Y-m-d', strtotime('last day of december this year')); 

        $from_date = $first_date_month.' 00:00:00';
        $to_date   = $last_date_month.' 23:59:59';


        $total_rep_sales_amt = $this->RepresentativeLeadsModel
                                       ->where('total_wholesale_price','>','0')
                                       ->whereBetween('created_at',array($from_date,$to_date))
                                       ->where('refund_status','0')
                                       ->where('is_split_order','<>','1')
                                       ->where('order_cancel_status','<>','2')
                                       ->where('is_confirm','!=','3')
                                       ->where('is_confirm','!=','0')
                                       ->sum('total_wholesale_price');

        $total_retailer_order_amt = $this->RetailerQuotesModel
                                           ->where('total_wholesale_price','>','0')
                                           ->whereBetween('created_at',array($from_date,$to_date))
                                           ->where('order_cancel_status','<>','2')
                                           ->where('refund_status','0')
                                           ->where('is_split_order','<>','1')
                                           ->where('order_cancel_rejected_status','!=','1')
                                           /*->where('is_confirm','!=','0')*/
                                           ->sum('total_wholesale_price');

        $total_customer_order_amt = $this->CustomerQuotesModel
                                   ->where('total_retail_price','>','0')
                                   ->whereBetween('created_at',array($from_date,$to_date))
                                   ->where('order_cancel_status','<>','2')
                                   ->where('refund_status','0')
                                   ->where('is_split_order','<>','1')                                  
                                   ->where('order_cancel_rejected_status','!=','1')
                                   ->sum('total_retail_price');

      
        $total_order_amt = num_format($total_rep_sales_amt)+num_format($total_retailer_order_amt)+num_format($total_customer_order_amt);

        $total_amt = num_format($total_order_amt);


        /*-------------------get orders from last 7 days----------------------*/
        $current_date         = date('Y-m-d');
        $last_seven_days_date = date('Y-m-d', strtotime('-7 days'));


        $start_date    = $last_seven_days_date.' 00:00:00';
        $end_date      = $current_date.' 23:59:59';


        $total_rep_sales_amount = $this->RepresentativeLeadsModel
                                        ->where('total_wholesale_price','>','0')
                                        ->whereBetween('created_at',array($start_date,$end_date))
                                        ->where('order_cancel_status','<>','2')
                                        ->where('refund_status','0')
                                        ->where('is_confirm','1')
                                        ->where('is_split_order','<>','1')
                                        ->where('is_confirm','!=','3')
                                        ->where('is_confirm','!=','0')
                                        ->sum('total_wholesale_price');

        $total_retailer_order_amount = $this->RetailerQuotesModel
                                            ->where('total_wholesale_price','>','0')
                                            ->whereBetween('created_at',array($start_date,$end_date))
                                            ->where('order_cancel_status','<>','2')
                                            ->where('refund_status','0')
                                            ->where('is_split_order','<>','1')
                                            ->where('order_cancel_rejected_status','!=','1')
                                            ->sum('total_wholesale_price');

        $total_customer_order_amount = $this->CustomerQuotesModel
                                            ->where('total_retail_price','>','0')
                                            ->whereBetween('created_at',array($start_date,$end_date))
                                            ->where('order_cancel_status','<>','2')
                                            ->where('refund_status','0')
                                            ->where('is_split_order','<>','1')
                                            ->where('order_cancel_rejected_status','!=','1')
                                            ->sum('total_retail_price');

        $total_order_amount = num_format($total_rep_sales_amount) + num_format($total_retailer_order_amount)+ num_format($total_customer_order_amount);                          
        /*------------------------------------------------------------------*/


        /*----------------------get orders from last 30 days------------------*/
          
        $current_date          = date('Y-m-d');
        $last_thirty_days_date = date('Y-m-d', strtotime('-30 days'));

        $s_date       = $last_thirty_days_date.' 00:00:00';
        $l_date       = $current_date.' 23:59:59';

        $rep_sales_amount = $this->RepresentativeLeadsModel
                               ->where('total_wholesale_price','>','0')
                               ->whereBetween('created_at',array($s_date,$l_date))
                               ->where('order_cancel_status','<>','2')
                               ->where('refund_status','0')
                               ->where('is_confirm','1')
                               ->where('is_split_order','<>','1')
                               ->sum('total_wholesale_price');

        $retailer_order_amount = $this->RetailerQuotesModel
                                    ->where('total_wholesale_price','>','0')
                                    ->whereBetween('created_at',array($s_date,$l_date))
                                    ->where('order_cancel_status','<>','2')
                                    ->where('refund_status','0')
                                    ->where('is_split_order','<>','1')
                                    ->where('order_cancel_rejected_status','!=','1')
                                    ->sum('total_wholesale_price');

        $customer_order_amount = $this->CustomerQuotesModel
                                    ->where('total_retail_price','>','0')
                                    ->whereBetween('created_at',array($s_date,$l_date))
                                    ->where('order_cancel_status','<>','2')
                                    ->where('refund_status','0')
                                    ->where('is_split_order','<>','1')
                                    ->where('order_cancel_rejected_status','!=','1')
                                    ->sum('total_retail_price');

       
        $total_orders_amount = num_format($rep_sales_amount) + num_format($retailer_order_amount)+ num_format($customer_order_amount);                          

        /*---------------------------------------------------------------------*/

        $this->arr_view_data['total_order_amt']     = $total_amt;
        $this->arr_view_data['total_order_amount']  = $total_order_amount;
        $this->arr_view_data['total_orders_amount'] = $total_orders_amount;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);


       

    }

}

