<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


// namespace App\Http\Controllers\Customer;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;


use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\CustomerFavoriteModel;
use App\Models\ProductsModel;
use App\Models\UserModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\TransactionMappingModel;
use App\Models\MakerModel;
use App\Models\TransactionsModel;
use App\Models\CustomerQuotesProductModel;
use App\Common\Services\orderDataService;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;


use Sentinel;
use Flash;
use Session;
use Paginate;
use DB;

class DashboardController extends Controller
{
    /*
    | Author : Shital Vijay More
    | Date   : 2 July 2019
    */

    public function __construct(CustomerFavoriteModel $CustomerFavoriteModel,
                                ProductsModel $ProductsModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                UserModel $UserModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                TransactionMappingModel $TransactionMappingModel,
                                MakerModel $MakerModel,
                                TransactionsModel $TransactionsModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,
                                orderDataService $orderDataService
                               )
    {
    	$this->arr_view_data         = [];
    	$this->module_title          = "Dashboard";
    	$this->module_view_folder    = 'customer.dashboard';
      $this->CustomerFavoriteModel      = $CustomerFavoriteModel;
      $this->ProductsModel              = $ProductsModel;
      $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
      $this->UserModel                  = $UserModel;
      $this->CustomerQuotesModel        = $CustomerQuotesModel;
      $this->TransactionMappingModel    = $TransactionMappingModel;
      $this->MakerModel                 = $MakerModel;
      $this->TransactionsModel          = $TransactionsModel;
      $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
      $this->orderDataService           = $orderDataService;

      $this->customer_panel_slug   = config('app.project.customer_panel_slug');
      $this->module_url_path       = url($this->customer_panel_slug.'/dashboard');

    
    }

    public function index()
    {
      // dd(123);
        $user = Sentinel::check();
        $loggedIn_userId = 0;
        
        if($user)
        {
            $loggedIn_userId = $user->id;
        } 
        
        
        $arr_quote_count                    = [];
        $arr_count['quote_count']           = $this->get_customer_quote_count($loggedIn_userId);
        $arr_count['cancel_quote_count']    = $this->get_canceled_order_count($loggedIn_userId);
        $arr_count['pending_quote_count']   = $this->get_pending_order_count($loggedIn_userId);
        $arr_count['complete_quote_count']   = $this->get_completed_order_count($loggedIn_userId);

        $sevenDaysOrderCount = $this->get_last_seven_days_orders($loggedIn_userId);
        
        $thirtyDaysOrderCount = $this->get_last_thirty_days_orders($loggedIn_userId);

        $lastWeekOrders = $this->get_last_week_orders($loggedIn_userId);

        $lastMonthOrders = $this->get_last_month_orders($loggedIn_userId);
        
        $currentMonth         = date('F');

        $previous_month_name  = Date('F', strtotime($currentMonth . " last month"));
        
        $this->arr_view_data['arr_count']       = $arr_count;  
        $this->arr_view_data['sevenDaysOrderCount']  = $sevenDaysOrderCount;  
        $this->arr_view_data['thirtyDaysOrderCount'] = $thirtyDaysOrderCount;  
        $this->arr_view_data['lastWeekOrders']      = $lastWeekOrders;  
        $this->arr_view_data['lastMonthOrders']      = $lastMonthOrders;  
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Dashboard';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['previous_month_name']   = $previous_month_name;


    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_customer_quote_count($customer_id)
    {
        $quote_count = 0;
        if(isset($customer_id) && $customer_id!="" && $customer_id!=0)
        {
          $quote_count = CustomerQuotesModel::where('customer_id',$customer_id)
                                              ->where('order_cancel_status','!=',2)
                                              ->where('is_split_order','=','0')
                                              ->count();
        }

        return $quote_count;
    }

    public function get_customer_cancel_quote_count($customer_id)
    {
        $quote_count = 0;
        if(isset($customer_id) && $customer_id!="" && $customer_id!=0)
        {
          $quote_count = CustomerQuotesModel::where('customer_id',$customer_id)
                                            ->where('order_cancel_status','=',2)
                                            ->where('is_split_order','=','0')
                                            ->count();
        }

        return $quote_count;
    }

    public function get_reps_order_count($retailer_id)
    {
      $quote_count = 0;
        if(isset($retailer_id) && $retailer_id!="" && $retailer_id!=0)
        {
          $quote_count = RepresentativeLeadsModel::where('retailer_id',$retailer_id)
                                                  ->where('order_cancel_status','!=',2)
                                                  ->where('is_confirm','!=',0)
                                                  ->where('is_split_order','=','0')
                                                  ->count();
        }

        return $quote_count;
    }

    public function get_reps_cancle_order_count($retailer_id)
    {
      $quote_count = 0;
        if(isset($retailer_id) && $retailer_id!="" && $retailer_id!=0)
        {
          $quote_count = RepresentativeLeadsModel::where('retailer_id',$retailer_id)
                                                  ->where('order_cancel_status','=',2)
                                                  ->where('is_split_order','=','0')
                                                  ->count();
        }

        return $quote_count;
    }


    public function change_password()
    {
        $this->arr_view_data['page_title']      = "Change Password";
        $this->arr_view_data['module_title']    = "Change Password";
        $this->arr_view_data['module_url_path'] = url($this->customer_panel_slug);
        
        return view($this->module_view_folder.'.change_password',$this->arr_view_data);    
    }

  public function get_customer_pending_quote_count($loggedInUserId)
  { 

        $customer_quotes_tbl_name     = $this->CustomerQuotesModel->getTable();        
        $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->CustomerQuotesModel->getTable();

        $transaction_mapping_table = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

        $user_tbl_name                = $this->UserModel->getTable();
        $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl                    = $this->MakerModel->getTable();        
        $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();

        $transaction_tbl              = $this->TransactionsModel->getTable();        
        $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable();

        $customer_product_tbl = $this->CustomerQuotesProductModel->getTable();

        $prefixed_customer_product_tbl = DB::getTablePrefix().$this->CustomerQuotesProductModel->getTable();

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                         $prefixed_customer_product_tbl.".shipping_charge,".
                                         $prefixed_customer_product_tbl.".shipping_discount,".
                                      
                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_customer_product_tbl, $prefixed_customer_product_tbl.'.customer_quotes_id','=',$prefixed_customer_quotes_tbl.'.id')
                        
                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                            ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                        })

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_customer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_tbl,$transaction_tbl.'.transaction_id','=',$prefixed_customer_quotes_tbl.'.transaction_id')

                        ->where($prefixed_customer_quotes_tbl.'.customer_id',$loggedInUserId)
                        ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','!=',2)
                        ->where($prefixed_customer_quotes_tbl.'.ship_status','!=',1)
                        ->where($prefixed_customer_quotes_tbl.'.is_split_order','=','0')
                        ->groupBy($prefixed_customer_quotes_tbl.".id");

        // $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

        //         $query->select(\DB::raw("
        //                 transaction_mapping.order_id,
        //                 transaction_mapping.order_no
        //             FROM
        //                 `transaction_mapping`
        //             WHERE
        //                 `transaction_mapping`.`order_no` = customer_transaction.order_no AND `transaction_mapping`.`order_id` = customer_transaction.id
        //         "));

        //     });

          

          return $obj_qutoes = $obj_qutoes->get()->count();     
          
  }

  public function update_password(Request $request)
  {
    /*Check Validatons and display custom message*/
      $inputs = request()->validate([
        'current_password'=> 'required',
        'new_password' => 'required'
        ],
        [
          'current_password.required'=>'Please enter current password',
          'new_password.required'    =>'Please enter new password'
        ]);
      
      $user = Sentinel::check();

      $credentials = [];
      $credentials['password'] = $request->input('current_password');

      if (Sentinel::validateCredentials($user,$credentials)) 
      { 
        $new_credentials = [];
        $new_credentials['password'] = $request->input('new_password');

        if(Sentinel::update($user,$new_credentials))
        {
          //Flash::success('Password Change Successfully');
            //Session::flush();
            Flash::success('Your password has been changed successfully.');
            //return redirect('/login');
        }
        else
        {
          Flash::error('Problem occurred, while changing password.');
        }
      } 
      else
      {
        Flash::error('Invalid old password.');
      }       
      
      return redirect()->back(); 
  }



  public function get_favorite_data(Request $request)
  {
      /*get current login user*/
      $user_id = 0;
      $product_details_arr = $maker_details_arr = $favorite_arr =  $arr_product_data= $arr_maker_data =[];
      $user = Sentinel::check();

      if($user)
      {
         $user_id = $user->id; 
      }

      /*get all favorite data*/
      
     

      $obj_product_data = $this->CustomerFavoriteModel->where('customer_id',$user_id)
                               ->with(['productDetails'=>function($query) 
                               {
                                  $query->select('id','user_id','product_name','brand','product_image','product_image_thumb','unit_wholsale_price','retail_price');
                               },'productDetails.brand_details'=>function($query1)
                               {
                                  $query1->select('id','user_id','brand_name');
                               }])
                               ->where('type','product')
                               ->paginate(12);


      if($obj_product_data)
      {
        $arr_product_pagination     = clone $obj_product_data;
        $arr_product_data           = $obj_product_data->toArray();
      } 
      

      $obj_maker_data = $this->CustomerFavoriteModel->where('customer_id',$user_id)

                                            ->with(['makerDetails'=>function($query)
                                            {
                                                $query->select('id','user_id','company_name');
                                            },'store_image_details'=>function($query1){
                                                $query1->select('id','maker_id','store_profile_image');
                                            }])
                                            ->where('type','maker')
                                            ->paginate(10);

      if($obj_maker_data)
      {
         $arr_maker_pagination  = clone $obj_maker_data;
         $arr_maker_data        = $obj_maker_data->toArray();
      }

      /*-----------------------------------------------------------------------------*/

      $get_favorite_data = $this->CustomerFavoriteModel->where('customer_id',$user_id)->get()->toArray(); 
 
      $favorite_arr['product']  = $arr_product_data;
      $favorite_arr['maker']    = $arr_maker_data;


      $this->arr_view_data['arr_maker_pagination']     = $arr_maker_pagination;
      $this->arr_view_data['arr_product_pagination']   = $arr_product_pagination;
      $this->arr_view_data['favorite_arr']             = $favorite_arr;
      $this->arr_view_data['page_title']               = 'My Favorites';


      return view('customer.favorites.my_favorite',$this->arr_view_data); 

  }

  public function get_completed_order_count($loggedIn_userId,$date=false)
  {
     $quotes_count_completed = $this->CustomerQuotesModel
                                       ->where('maker_confirmation',1)
                                       ->where('ship_status','=',1)
                                       ->where('is_split_order','=','0')
                                       ->where('customer_id',$loggedIn_userId);

                                        if($date != false)
                                        {
                                           $quotes_count_completed=$quotes_count_completed->where('created_at','LIKE','%'.$date.'%');
                                        }

                                       $quotes_count_completed=$quotes_count_completed->count();

     return $quotes_count_completed;
  } 

  public function get_pending_order_count($loggedIn_userId,$date=false)
  {
     $quotes_count_pending = $this->CustomerQuotesModel
                                            ->where('order_cancel_status','!=',2)
                                            ->where('customer_id',$loggedIn_userId)
                                            ->where('maker_confirmation','=',NULL)
                                            ->where('ship_status','=',0)
                                            ->where('is_split_order','=','0');

                                            if($date != false)
                                            {
                                               $quotes_count_pending = $quotes_count_pending->where('created_at','LIKE','%'.$date.'%');
                                            }
                                            $quotes_count_pending=$quotes_count_pending->count();


    return $quotes_count_pending;
  } 

  public function get_canceled_order_count($loggedIn_userId,$date=false)
  {
     $quotes_count_canceled = $this->CustomerQuotesModel
                                           ->where('order_cancel_status','=',2)
                                           ->where('customer_id',$loggedIn_userId)
                                           ->where('is_split_order','=','0');
                                            if($date != false)
                                            {
                                               $quotes_count_canceled=$quotes_count_canceled->where('created_at','LIKE','%'.$date.'%');
                                            }
                                           $quotes_count_canceled=$quotes_count_canceled->count();

      return $quotes_count_canceled;
  }

  public function get_last_seven_days_orders($loggedIn_userId)
  {
     $last_seven_days_arr = $this->orderDataService->lastSevenDays();

      $complete_count = $pendings_count = $cancel_count = $cacels_count = 0;
      $sales_quotes_count_arr = [];


      if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
      { 
          foreach($last_seven_days_arr as $key => $date) 
          { 
                $completed_quotes_count = $this->get_completed_order_count($loggedIn_userId,$date);


                  $complete_count+= $completed_quotes_count;
                        
                  //pending order count
                  $pending_quotes_count = $this->get_pending_order_count($loggedIn_userId,$date);


                  $pendings_count+= $pending_quotes_count;                      

                        
                  //canceled order count
                  $canceled_quotes_count = $this->get_canceled_order_count($loggedIn_userId,$date);

                 $cancel_count+= $canceled_quotes_count; 
              
          }

          $sales_quotes_count_arr['completed_order'] = $complete_count;
          $sales_quotes_count_arr['pending_order']   = $pendings_count;
          $sales_quotes_count_arr['canceled_order']  = $cancel_count;

      } 

      return $sales_quotes_count_arr;
  }

  public function get_last_thirty_days_orders($loggedIn_userId)
  {

       $last_thirty_days_arr = $this->orderDataService->lastThirtyDays();
     
       $complete_customer_quotes_count = $pending_customer_quotes_count = $canceled_customer_quotes_count =0;

       $customer_quotes_arr=[];


        if(isset($last_thirty_days_arr) && count($last_thirty_days_arr)>0)
        { 
            foreach($last_thirty_days_arr as $key => $date) 
            { 
               
                $quotes_count_completed = $this->CustomerQuotesModel
                                               ->where('created_at','LIKE','%'.$date.'%')
                                               ->where('maker_confirmation',1)
                                               ->where('ship_status','=',1)
                                               ->where('is_split_order','=','0')
                                               ->where('customer_id',$loggedIn_userId)
                                               ->count();

                $complete_customer_quotes_count+= $quotes_count_completed;
            
            
                //pending order count
                $quotes_count_pending = $this->CustomerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','!=',2)
                                              ->where('customer_id',$loggedIn_userId)
                                              ->where('maker_confirmation','=',NULL)
                                              ->where('ship_status','=',0)
                                              ->where('is_split_order','=','0')
                                              ->count();

                $pending_customer_quotes_count+= $quotes_count_pending;                      

            
                // canceled order count
                $quotes_count_canceled = $this->CustomerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('customer_id',$loggedIn_userId)
                                              ->where('is_split_order','=','0')
                                              ->count();

                $canceled_customer_quotes_count+= $quotes_count_canceled; 
          
            }

            $customer_quotes_arr['completed_order'] = $complete_customer_quotes_count;
            $customer_quotes_arr['pending_order']   = $pending_customer_quotes_count;
            $customer_quotes_arr['canceled_order']  = $canceled_customer_quotes_count;

        }

        return $customer_quotes_arr;

  }

  public function get_last_week_orders($loggedIn_userId)
  {
    /*------------------last week customer order report-----------------*/
        
      $customer_orders_arr = [];

      $dates_arr  = $this->orderDataService->getLastWeekDates();
      $week_date_arr = array('Monday'   =>$dates_arr[0],
                             'Tuesday'  =>$dates_arr[1],
                             'Wednesday'=>$dates_arr[2],
                             'Thursday' =>$dates_arr[3],
                             'Friday'   =>$dates_arr[4],
                             'Saturday' =>$dates_arr[5],
                             'Sunday'   =>$dates_arr[6]
                            );

      if(isset($week_date_arr) && count($week_date_arr)>0)
      {
          foreach($week_date_arr as $key => $date) 
          { 
              $quotes_count_completed = $this->CustomerQuotesModel
                                             ->where('created_at','LIKE','%'.$date.'%')
                                             ->where('maker_confirmation',1)
                                             ->where('ship_status','=',1)
                                             ->where('customer_id',$loggedIn_userId)
                                             ->where('is_split_order','=','0')
                                             ->sum('total_retail_price');

              $customer_orders_arr[$key]['completed_order'] = intval($quotes_count_completed);
            
            
              //pending order count
              $quotes_count_pending = $this->CustomerQuotesModel
                                           ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('customer_id',$loggedIn_userId)
                                            ->where('maker_confirmation','=',NULL)
                                            ->where('ship_status','=',0)
                                            ->where('is_split_order','=',0)
                                            ->sum('total_retail_price');


              $customer_orders_arr[$key]['pending_order'] = intval($quotes_count_pending);                      

            
              // canceled order count
              $quotes_count_canceled = $this->CustomerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('customer_id',$loggedIn_userId)
                                              ->where('is_split_order','=','0')
                                              ->sum('total_retail_price');

              $customer_orders_arr[$key]['canceled_order'] = intval($quotes_count_canceled); 
                  
          
          }
      } 

      return $customer_orders_arr;
  }

  public function get_last_month_orders($loggedIn_userId)
  {
     /*---------------------last month retailer order report---------------------*/
    
      $customer_order_data = [];
 
      $currentMonth         = date('F');
      $previous_month_name  = Date('F', strtotime($currentMonth . " last month"));

      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));

      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';


      //completed orders count
      $completed_count = $this->CustomerQuotesModel
                              ->where('maker_confirmation',1)
                              ->where('ship_status','=',1)
                              ->where('customer_id',$loggedIn_userId)
                              ->where('is_split_order','=','0')
                              ->whereBetween('created_at',array($from_date,$to_date))
                              //->count();
                              ->sum('total_wholesale_price');

      $customer_order_data[$previous_month_name]['completed_order']= intval($completed_count);
                
      // pending order count
      $pending_count = $this->CustomerQuotesModel
                             ->where('order_cancel_status','!=',2)
                             ->where('customer_id',$loggedIn_userId)
                             ->where('maker_confirmation','=',NULL)
                             ->where('is_split_order','=','0')
                             ->where('ship_status','=',0)
                             ->whereBetween('created_at',array($from_date,$to_date))
                            // ->count();
                             ->sum('total_wholesale_price');


      $customer_order_data[$previous_month_name]['pending_order'] = intval($pending_count);                      

      //canceled order count
      $canceled_count = $this->CustomerQuotesModel
                             ->where('order_cancel_status','=',2)
                             ->where('customer_id',$loggedIn_userId)
                             ->where('is_split_order','=','0')
                             ->whereBetween('created_at',array($from_date,$to_date))
                             //->count();
                             ->sum('total_wholesale_price');

      $customer_order_data[$previous_month_name]['canceled_order'] = intval($canceled_count);


      return $customer_order_data;
  }




}
