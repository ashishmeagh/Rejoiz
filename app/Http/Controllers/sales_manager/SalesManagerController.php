<?php

namespace App\Http\Controllers\sales_manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RepresentativeModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Common\Services\EmailService;
use App\Common\Services\UserService;
use App\Common\Services\GeneralService;
use App\Models\RepAreaModel;
use App\Models\CountryModel;
use App\Models\MakerModel;
use App\Models\TransactionMappingModel;
use App\Common\Traits\MultiActionTrait;
use App\Helpers\common_data_helper;
use App\Models\SiteSettingModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\SalesManagerModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\VendorSalesmanagerMappingModel;



use Sentinel;
use Session;
use Validator;
use DB;
use Flash;

class SalesManagerController extends Controller
{
   use MultiActionTrait;

   public function __construct(RepresentativeModel $RepresentativeModel,
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                RepAreaModel $RepAreaModel,
                                CountryModel $CountryModel,
                                VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                EmailService $EmailService,
                                GeneralService $GeneralService,
                                SalesManagerModel $SalesManagerModel,
                                MakerModel $MakerModel,
                                UserService $UserService,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                VendorSalesmanagerMappingModel $VendorSalesmanagerMappingModel
                              )
    {
      $this->RepresentativeModel = $RepresentativeModel;
      $this->BaseModel           = $UserModel;
      $this->RepAreaModel        = $RepAreaModel;
      $this->CountryModel        = $CountryModel;
      $this->RoleModel           = $RoleModel;
      $this->TransactionMappingModel = $TransactionMappingModel;
      $this->RoleUsersModel      = $RoleUsersModel;
      $this->VendorRepresentativeMappingModel  = $VendorRepresentativeMappingModel;
      $this->EmailService        = $EmailService;
      $this->UserService         = $UserService;
      $this->GeneralService      = $GeneralService;
      $this->UserModel           = $UserModel;
      $this->MakerModel          = $MakerModel;
      $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
      $this->VendorSalesmanagerMappingModel = $VendorSalesmanagerMappingModel;

      $this->SalesManagerModel   = $SalesManagerModel;
      $this->module_view_folder  = "sales_manager";
     // $this->module_title        = "Representative";

      $this->module_view_folder  = "sales_manager";
      $this->module_title        = "Representative";
      $this->profile_image       = base_path().'/storage/app/';
        
    }


  public function index()
  {

        $user = Sentinel::check();
        $sales_manager_id = $total_order_count =0;

        if($user)
        {
            $sales_manager_id = $user->id;
        }    


        $rep_count = 0;
        if(isset($sales_manager_id) && $sales_manager_id!="" && $sales_manager_id!=0)
        {
          $rep_count = RepresentativeModel::where('sales_manager_id',$sales_manager_id)->count();
        }


        /*get sales manager confirm order count*/
        $confirm_order_count = $this->RepresentativeLeadsModel
                                    ->where('sales_manager_id',$sales_manager_id)
                                    ->where('total_wholesale_price','>','0')
                                    ->where('is_confirm',1)
                                    ->where('order_cancel_status','!=',2)
                                    ->count();


        /*get total order count*/

        $total_order_count = $this->RepresentativeLeadsModel
                                  ->where('sales_manager_id',$sales_manager_id)
                                  ->where('total_wholesale_price','>','0')
                                  ->where('order_cancel_status','!=',2)

                                  ->count();



      /*-------get prnding,completed,canceled,total,confirmed order count----*/

      $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $transaction_mapping        = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $total_count = $this->RepresentativeLeadsModel
                          ->where('sales_manager_id',$sales_manager_id)
                          ->where('total_wholesale_price','>','0')
                          // ->where('order_cancel_status','!=',2)-+
                          ->where('is_confirm','!=',0)
                          ->where('is_confirm','!=',4)
                          ->where('is_confirm','!=',3)
                          ->where('maker_id','!=',0)
                          ->count();

                           
       /*$pending_count = $this->RepresentativeLeadsModel
                          ->where('sales_manager_id',$sales_manager_id)
                          ->where('total_wholesale_price','>','0')
                          ->where('order_cancel_status','!=',2)
                          ->where('is_confirm','!=',0)
                          ->where('is_confirm','!=',4)
                          ->where('ship_status','=',0)
                          ->where('maker_id','!=',0)
                          ->where(function($query)
                                {
                                  return $query->where('is_confirm','=',0)
                                  ->orWhere('is_confirm','=',2);
                                })
                          ->whereNotExists(function($query) use ($prefix_transaction_mapping,$representative_leads)
                                {
                                  $query->select(\DB::raw("
                                          transaction_mapping.order_id,
                                          transaction_mapping.order_no
                                      FROM
                                          `transaction_mapping`
                                      WHERE
                                          `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                                  "));
                                })
                          ->count();*/ 

        $pending_count = $this->RepresentativeLeadsModel
                                // ->where('sales_manager_id',$sales_manager_id)
                                // ->where('total_wholesale_price','>','0')
                                // ->where('order_cancel_status','!=','2')
                                // ->where('ship_status','=',0)
                                 ->where('order_cancel_status','!=',2)
                                 ->where('total_wholesale_price','>','0')
                                  ->where('sales_manager_id',$sales_manager_id)
                                  ->where('is_split_order','=','0')
                                 // ->where('is_payment_status','=','0')
                                  ->where('is_confirm','!=',0)
                                  ->where('is_confirm','!=',4)
                                  ->where('is_confirm','!=',3)
                                  ->where('maker_id','!=',0)
                                  /*->where(function($q){
                                    return $q->orwhere('ship_status','=','0')
                                             ->orwhere('ship_status','=','1');
                                  })*/
                                   ->where(function($q){
                                         $q->where('is_payment_status','=','1')
                                                 ->where('ship_status','=','0')
                                       ->orwhere(function($q){
                                           $q->where('is_payment_status','=','0')
                                                   ->where('ship_status','=','1');
                                        })
                                         ->orwhere(function($q){
                                          return $q->where('is_payment_status','=','0')
                                                   ->where('ship_status','=','0');
                                        });
                                      }
                                    )

                                  ->where(function($q){
                                    return $q->where('payment_term','!=','Net30')
                                             ->orwhere('payment_term','!=','Net30 - Online/Credit')
                                             ->orwhereNULL('payment_term');
                                  })
                               ->count();  

        //dd($pending_count->toSql(),$pending_count->getBindings());             
                       

       $completed_count =  DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id'

                              )) 

                            // ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                            //     $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                            //          ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');
                            // })

                          ->where($representative_leads.'.total_wholesale_price','>','0')

                          ->where($representative_leads.'.order_cancel_status','!=',2)
                           
                         ->where('sales_manager_id',$sales_manager_id)

                          ->where($representative_leads.'.is_confirm','=',1)
                           
                          //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                    
                          ->where($representative_leads.'.ship_status','=',1)
                                                    
                          ->where($representative_leads.'.maker_confirmation','=',1)

                          ->where($representative_leads.'.is_payment_status','=',1)

                          ->where('is_confirm','!=',0)
                          ->where('is_confirm','!=',4)
                          ->where('maker_id','!=',0)

                          ->count(); 

      /* Net 30 Count */
        $net_30_pending_count = $this->RepresentativeLeadsModel
                                ->where('sales_manager_id',$sales_manager_id)
                                ->where('total_wholesale_price','>','0')
                                ->where('order_cancel_status','!=','2')
                                ->where('payment_term','=','Net30')
                                ->where('ship_status','=',0)
                                ->where('is_confirm','!=',0)
                                ->where('is_confirm','!=',4)
                                ->count();                
                       

       $net_30_completed_count =  DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id'

                              )) 

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                     ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');
                            })

                          ->where($representative_leads.'.total_wholesale_price','>','0')

                          ->where($representative_leads.'.order_cancel_status','!=',2)
                           
                         ->where('sales_manager_id',$sales_manager_id)

                          ->where($representative_leads.'.is_confirm','=',1)
                           
                          ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                    
                          ->where($representative_leads.'.ship_status','=',1)
                                                    
                          ->where($representative_leads.'.maker_confirmation','=',1)
                          ->where('payment_term','=','Net30')
                          ->where('is_confirm','!=',0)
                          ->where('is_confirm','!=',4)
                          ->count();                         


      $canceled_count = $this->RepresentativeLeadsModel
                             ->where('order_cancel_status','=',2)
                             ->where('sales_manager_id',$sales_manager_id)
                             ->count();                                          

      $order_count_arr = [];
      $order_count_arr['total_count']               = $total_count or 0;
      $order_count_arr['pending_count']             = $pending_count or 0;
      $order_count_arr['completed_count']           = $completed_count or 0;
      $order_count_arr['canceled_count']            = $canceled_count or 0;
      $order_count_arr['net_30_pending_count']      = $net_30_pending_count or 0;
      $order_count_arr['net_30_completed_count']    = $net_30_completed_count or 0;
      $order_count_arr['confirmed_count']           = $confirm_order_count or 0;

     /*--------------------------------------------------------------------*/

      // get all last week order count 
       
      $orders_arr = $pending_orders_arr = $canceled_orders_arr = [];

      $dates_arr = $this->getLastWeekDates();

      $week_date_arr = array('Monday'=>$dates_arr[0],
                             'Tuesday'=>$dates_arr[1],
                             'Wednesday'=>$dates_arr[2],
                             'Thursday'=>$dates_arr[3],
                             'Friday' =>$dates_arr[4],
                             'Saturday'=>$dates_arr[5],
                             'Sunday' =>$dates_arr[6]
                            );

      if(isset($week_date_arr) && count($week_date_arr)>0)
      {
          foreach($week_date_arr as $key => $date) 
          { 
               
              //order count
             /*  // completed orders count
                $completed_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('is_confirm','=',1)
                                              ->where('ship_status','=',1)
                                              ->where('sales_manager_id',$sales_manager_id)
                                              ->count();

                $orders_arr[$key]['completed_order']= $completed_order_count;
                
               // pending order count
                $pending_order_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('sales_manager_id',$sales_manager_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                           ->orWhere('is_confirm','=',2);
                                            })
                                            ->count();

                $orders_arr[$key]['pending_order']= $pending_order_count;                      

                //canceled order count
                $canceled_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('sales_manager_id',$sales_manager_id)
                                              ->count();

                $orders_arr[$key]['canceled_order'] = $canceled_order_count;  */  



                /*total order amount*/

                // completed orders count
                $completed_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('is_confirm','=',1)
                                              ->where('ship_status','=',1)
                                              ->where('sales_manager_id',$sales_manager_id)
                                              ->sum('total_wholesale_price');
      

                $orders_arr[$key]['completed_order']= intval($completed_order_count);

                
                // pending order count
                /*$pending_order_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('sales_manager_id',$sales_manager_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                          ->orWhere('is_confirm','=',2);
                                            })
                                            ->sum('total_wholesale_price');*/

                $pending_order_count = $this->RepresentativeLeadsModel
                                            ->where('sales_manager_id',$sales_manager_id)
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            //->where('ship_status','=',0)
                                            ->where('is_confirm','!=',0)
                                            ->where('is_confirm','!=',4)
                                            ->where('is_confirm','!=',3)
                                            ->where('maker_id','!=',0)
                                            ->where(function($q){
                                                 $q->where('is_payment_status','=','1')
                                                         ->where('ship_status','=','0')
                                               ->orwhere(function($q){
                                                   $q->where('is_payment_status','=','0')
                                                           ->where('ship_status','=','1');
                                                })
                                                 ->orwhere(function($q){
                                                  return $q->where('is_payment_status','=','0')
                                                           ->where('ship_status','=','0');
                                                });
                                              }
                                            )
                                            //->count();
                                            ->sum('total_wholesale_price');

                $orders_arr[$key]['pending_order']= intval($pending_order_count);                      

                //canceled order count
                $canceled_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('sales_manager_id',$sales_manager_id)
                                              ->sum('total_wholesale_price');
                                              
                $orders_arr[$key]['canceled_order'] = intval($canceled_order_count);    
       
            }
      }             
   
      /*-------------------get last month order  count ----------------------------*/
      $order_data = [];
 
      //get the name of previous month

      $currentMonth         = date('F');

      $previous_month_name = Date('F', strtotime($currentMonth . " last month"));


      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));


      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';



      //completed orders count
      $completed_count = $this->RepresentativeLeadsModel
                              ->where('is_confirm','=',1)
                              ->where('ship_status','=',1)
                              ->where('sales_manager_id',$sales_manager_id)
                              ->whereBetween('created_at',array($from_date,$to_date))
                              //->count();
                              ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['completed_order']= intval($completed_count);
                
     // pending order count
      /*$pending_count = $this->RepresentativeLeadsModel
                            ->where('order_cancel_status','!=',2)
                            ->where('sales_manager_id',$sales_manager_id)
                            ->whereBetween('created_at',array($from_date,$to_date))

                            ->where(function($query)
                            {
                                return $query->where('is_confirm','=',0)
                                             ->orWhere('is_confirm','=',2);
                            })
                            ->sum('total_wholesale_price');*/

      $pending_count = $this->RepresentativeLeadsModel
                            ->where('sales_manager_id',$sales_manager_id)
                            ->whereBetween('created_at',array($from_date,$to_date))
                            ->where('order_cancel_status','!=',2)
                            //->where('ship_status','=',0)
                            ->where('is_confirm','!=',0)
                            ->where('is_confirm','!=',4)
                            ->where('is_confirm','!=',3)
                            ->where('maker_id','!=',0)
                            ->where(function($q){
                               $q->where('is_payment_status','=','1')
                                       ->where('ship_status','=','0')
                             ->orwhere(function($q){
                                 $q->where('is_payment_status','=','0')
                                         ->where('ship_status','=','1');
                              })
                               ->orwhere(function($q){
                                return $q->where('is_payment_status','=','0')
                                         ->where('ship_status','=','0');
                              });
                            }
                            )
                            //->count();
                            ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['pending_order'] = intval($pending_count);                      

      //canceled order count
      $canceled_count = $this->RepresentativeLeadsModel
                                   ->where('order_cancel_status','=',2)
                                   ->where('sales_manager_id',$sales_manager_id)
                                   ->whereBetween('created_at',array($from_date,$to_date))
                                   //->count();
                                   ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['canceled_order'] = intval($canceled_count);  


      /*-------------new code for last 7 days order count---------------------------------*/

        $last_seven_days_arr = $this->lastSevenDays();
        $complete_count = $pendings_count = $cancel_count = $cacels_count = 0;
        $sales_leads_count_arr = [];

        if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
        { 
            foreach($last_seven_days_arr as $key => $date) 
            { 
               
                //completed orders count
                $compl_count = $this->RepresentativeLeadsModel
                                        ->where('created_at','LIKE','%'.$date.'%')
                                        ->where('is_confirm','=',1)
                                        ->where('ship_status','=',1)
                                        ->where('sales_manager_id',$sales_manager_id)
                                        ->count();



                $complete_count+= $compl_count;
                
                //pending order count
                /*$pend_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('sales_manager_id',$sales_manager_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                           ->orWhere('is_confirm','=',2);
                                            })
                                            ->count();*/

                $pend_count = $this->RepresentativeLeadsModel
                                            ->where('sales_manager_id',$sales_manager_id)
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('is_confirm','!=',0)
                                            ->where('is_confirm','!=',4)
                                            ->where('is_confirm','!=',3)
                                            ->where('maker_id','!=',0)
                                            //->where('ship_status','=',0)
                                            ->where(function($q){
                                                 $q->where('is_payment_status','=','1')
                                                         ->where('ship_status','=','0')
                                               ->orwhere(function($q){
                                                   $q->where('is_payment_status','=','0')
                                                           ->where('ship_status','=','1');
                                                })
                                                 ->orwhere(function($q){
                                                  return $q->where('is_payment_status','=','0')
                                                           ->where('ship_status','=','0');
                                                });
                                              }
                                            )
                                            ->count();

                $pendings_count += $pend_count;                      

                //canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('sales_manager_id',$sales_manager_id)
                                              ->count();

                $cacels_count+= $cancel_count;    
          
            }

            $sales_leads_count_arr['completed_order'] = $complete_count;
            $sales_leads_count_arr['pending_order']   = $pendings_count;
            $sales_leads_count_arr['canceled_order']  = $cacels_count;

        } 


      /*------------new code for last 30 days order count ---------------------*/    

       $last_thirty_days_arr = $this->lastThirtyDays();
     
       $completed_leads_count = $pending_leads_count = $canceled_leads_count =0;

       $lead_count_arr=[];


        if(isset($last_thirty_days_arr) && count($last_thirty_days_arr)>0)
        { 
            foreach($last_thirty_days_arr as $key => $date) 
            { 
               
                //completed orders count
                $compl_count = $this->RepresentativeLeadsModel
                                        ->where('created_at','LIKE','%'.$date.'%')
                                        ->where('is_confirm','=',1)
                                        ->where('ship_status','=',1)
                                        ->where('sales_manager_id',$sales_manager_id)
                                        ->count();

                $completed_leads_count+= $compl_count;
                
                //pending order count
                /*$pend_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('sales_manager_id',$sales_manager_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                           ->orWhere('is_confirm','=',2);
                                            })
                                            ->count();*/
                $pend_count = $this->RepresentativeLeadsModel
                                            ->where('sales_manager_id',$sales_manager_id)
                                             ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('ship_status','=',0)
                                            ->where('is_confirm','!=',0)
                                            ->where('is_confirm','!=',4)
                                            ->where('is_confirm','!=',3)
                                            ->where('maker_id','!=',0)
                                            ->where(function($q){
                                                 $q->where('is_payment_status','=','1')
                                                         ->where('ship_status','=','0')
                                               ->orwhere(function($q){
                                                   $q->where('is_payment_status','=','0')
                                                           ->where('ship_status','=','1');
                                                })
                                                 ->orwhere(function($q){
                                                  return $q->where('is_payment_status','=','0')
                                                           ->where('ship_status','=','0');
                                                });
                                              }
                                            )
                                            ->count();

                $pending_leads_count += $pend_count;                      

              //  canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                      ->where('created_at','LIKE','%'.$date.'%')
                                      ->where('order_cancel_status','=',2)
                                      ->where('sales_manager_id',$sales_manager_id)
                                      ->count();

                $canceled_leads_count+= $cancel_count;    
          
            }

            $lead_count_arr['completed_order'] = $completed_leads_count;
            $lead_count_arr['pending_order']   = $pending_leads_count;
            $lead_count_arr['canceled_order']  = $canceled_leads_count;

        } 


      
          $this->arr_view_data['sales_leads_count_arr']     = $sales_leads_count_arr;  
          $this->arr_view_data['lead_count_arr']            = $lead_count_arr;  
          $this->arr_view_data['orders_arr']                = $orders_arr;  
          $this->arr_view_data['order_count_arr']           = $order_count_arr;  
          $this->arr_view_data['orders_data']               = $order_data;
          $this->arr_view_data['previous_month_name']       = $previous_month_name;
          $this->arr_view_data['rep_count']                 = $rep_count;
          $this->arr_view_data['order_count']               = $confirm_order_count;
          $this->arr_view_data['total_order_count']         = $total_order_count;
          $this->arr_view_data['page_title']                = 'Dashboard';

          return view($this->module_view_folder.'/dashboard/index',$this->arr_view_data);

  }    

  /*this function will get all last week dates*/
  public function getLastWeekDates()
  {
    $lastWeek = array();
 
    $prevMon = abs(strtotime("previous monday"));
    $currentDate = abs(strtotime("today"));
    $seconds = 86400; //86400 seconds in a day
 
    $dayDiff = ceil( ($currentDate-$prevMon)/$seconds ); 
 
    if( $dayDiff < 7 )
    {
        $dayDiff += 1; //if it's monday the difference will be 0, thus add 1 to it
        $prevMon = strtotime( "previous monday", strtotime("-$dayDiff day") );
    }
 
    $prevMon = date("Y-m-d",$prevMon);
 
    // create the dates from Monday to Sunday
    for($i=0; $i<7; $i++)
    {
        $d = date("Y-m-d", strtotime( $prevMon." + $i day") );
        $lastWeek[]=$d;
    }
 
    return $lastWeek;
  }


  public function lastSevenDays()
  {
        $m= date("m");

        $de= date("d");

        $y= date("Y");

        for($i=0; $i<7; $i++)
        {

            $last_seven_days_arr[] = date('Y-m-d',mktime(0,0,0,$m,($de-$i),$y)); 
        }

        return $last_seven_days_arr;

  }

  public function lastThirtyDays()
  {

      $m= date("m");

      $de= date("d");

      $y= date("Y");

      for($i=0; $i<30; $i++)
      {

        $last_thirty_days_arr[] = date('Y-m-d',mktime(0,0,0,$m,($de-$i),$y)); 
      }

      return $last_thirty_days_arr;

  }

  public function change_password(Request $request)
  { 

    if($request->isMethod('get'))
    {
           
        $this->arr_view_data['page_title']      = "Change Password";
        $this->arr_view_data['module_title']    = "Change Password";
        $this->arr_view_data['module_url_path'] = url('/').'/sales_manager/change_password';
        
        return view($this->module_view_folder.'.account_settings.change_password',$this->arr_view_data);  
    }
         $inputs = request()->validate([
                                        'current_password'=> 'required',
                                        'new_password'    => 'required'
                                    ],
                                    [
                                       'current_password.required'=>'Please enter current password',
                                       'new_password.required'    =>'Please enter new password'
                                    ]);

      
        $user = Sentinel::check();

        $credentials = [];
        $credentials['password']         = $request->input('current_password');

      if (Sentinel::validateCredentials($user,$credentials)) 
      {   
            $new_credentials             = [];
            $new_credentials['password'] = $request->input('new_password');

            if(Sentinel::update($user,$new_credentials))
            {  
               //Flash::success('Password changed successfully');
               
               //Session::flush();
               Flash::success('Your password has been changed successfully.');
               //return redirect('/login');
               return redirect()->back();

            }
            else
            {  
               Flash::error('Error occurred while changing password.');
            }
      } 
      else
      {
        Flash::error('Invalid old password.');
      }       
      
      return redirect()->back();
  
    
  }

  public function add_representative()
  {   $getVendorsData = [];
      $user = \Sentinel::check();
      if($user)
      {
        $loggedInUserId = $user->id;
      }


      // $getVendorsData =  $this->MakerModel
      //                         ->with(['user_details'=> function($query){ 
      //                               $query->where([['status',1],['is_approved',1]]);
      //                           }])
      //                         ->get()
      //                         ->toArray();


      //get only assigned vendor to the sales manager
      
      $getVendorsData =  $this->VendorSalesmanagerMappingModel
                              ->with(['get_user_details'=> function($query){ 
                                    $query->where([['status',1],['is_approved',1]]);
                                },'get_user_details.maker_details'])
                              ->where('salesmanager_id',$loggedInUserId)
                              ->get()
                              ->toArray();

     $this->arr_view_data['page_title']      = "Add Representative";
     $this->arr_view_data['module_title']    = "Add Representative";
     $this->arr_view_data['module_url_path'] = url('/').'/sales_manager/representative_listing';
     $this->arr_view_data['sales_manager_id']= $loggedInUserId;
     $this->arr_view_data['all_vendors']    = $getVendorsData;

     $country_arr = $this->CountryModel->orderBy('id','ASC')
                                      ->get()
                                      ->toArray();
                  
    $this->arr_view_data['country_arr']   = isset($country_arr)?$country_arr:'';
     
    return view($this->module_view_folder.'.representative.create',$this->arr_view_data);  
  } 

  public function save_rep(Request $request)
  { 
      $is_update = false;
      
      $user = \Sentinel::check();

      if($user)
      {
        $loggedInUserId = $user->id;
      }

      $form_data = $request->all();

      $user_id = $request->input('user_id');

      
      /* find area  from user id*/

     // $area_id = $this->SalesManagerModel->where('user_id',$loggedInUserId)->get()->toArray();
      $area_id = $this->SalesManagerModel->where('user_id',$loggedInUserId)->pluck('area_id')->first();

      if($request->has('user_id'))
      {
        $is_update = true;
      }        
      $arr_rules = [    'tax_id'      =>'required',
                        'first_name'  =>'required',
                        'last_name'   =>'required',
                        'email'       =>'required|email',
                        'country_id'  =>'required',
                        'post_code'   =>'required',
                        'contact_no'  =>'required',
                     ];
       
      $validator = Validator::make($request->all(),$arr_rules);
      
      if($validator->fails())
      {
          $response['status']      = "error";
          $response['description'] = "Form validation failed, please check form fields.";
          return response()->json($response); 
      }
       
      /* Check for email duplication */
      $is_duplicate =  $this->UserModel->where('email','=',$request->input('email'));  
      if($is_update)
      {
          $is_duplicate = $is_duplicate->where('id','<>',$user_id);
      }

      $does_exists = $is_duplicate->count();

      if($does_exists)
      {
         $response['status']      = "error";
         $response['description'] = "Email id already exists.";
         return response()->json($response);
      }                 


      if($request->input('tax_id')!=null||$request->input('tax_id')!=0)
      {
        
          $is_duplicate_tax_id =  $this->UserModel->where('tax_id','=',$request->input('tax_id'));
      
    
        if($is_update)
        {
           $is_duplicate_tax_id = $is_duplicate_tax_id->where('id','<>',$user_id);
        }

        $does_exists = $is_duplicate_tax_id->count();
    
        if($does_exists)
        {
           $response['status']      = "error";
           $response['description'] = "Tax id already exists.";
           return response()->json($response);
        }        
      }
        
      $profile_img_file_path = '';
            
      $profile_image = isset($form_data['profile_image'])?$form_data['profile_image']:null;
            

      if($profile_image!=null)
      {
            //Validation for product image
            $file_extension = strtolower( $profile_image->getClientOriginalExtension());

            if(!in_array($file_extension,['jpg','png','jpeg']))
            {                           
                $arr_response['status']       = 'FAILURE';
                $arr_response['description']  = 'Invalid profile image,please try again.';

                return response().json($response);
            }

            $profile_img_file_path = $profile_image->store('profile_image');
            if(isset($form_data['old_profile_image']) && $form_data['old_profile_image']!="") 
            {
               $old_img_path       = $this->profile_image.$form_data['old_profile_image'];
               $unlink_old_img     = $this->GeneralService->unlink_old_image($old_img_path);
            }
      }

      else
      {
           $profile_img_file_path = $form_data['old_profile_image'];
      }
            
      $contact_no = str_replace($request->input('hid_country_code'), "", $request->input('contact_no'));
      $arr_user_data['first_name'] = $request->input('first_name');
      $arr_user_data['last_name']  = $request->input('last_name');
      $arr_user_data['email']      = $request->input('email');
      $arr_user_data['country_code'] = $request->input('hid_country_code');
      $arr_user_data['contact_no'] = $contact_no;
      $arr_user_data['post_code']  = $request->input('post_code');
      $arr_user_data['commission'] = 0;
      $arr_user_data['country_id'] = $request->input('country_id'); 
      $arr_user_data['tax_id']     = $request->input('tax_id'); 

      $arr_user_data['profile_image'] = $profile_img_file_path;


      /* File Upload */
      $user = Sentinel::createModel()->where(['id' => $user_id])->first();

      $user_password = str_random(6);

      if($user == false)
      {
          $user = Sentinel::registerAndActivate([
              'email' => $arr_user_data['email'],
              'password' => $user_password
          ]);

          if($user)
          {
              $role = Sentinel::findRoleBySlug('representative');
              $role->users()->attach($user);
          }

          $user->first_name    = $arr_user_data['first_name'];
          $user->last_name     = $arr_user_data['last_name'];
          $user->email         = $arr_user_data['email'];
          // $user->password   = $arr_user_data['password'];
          $user->country_code  = $arr_user_data['country_code'];
          $user->contact_no    = $arr_user_data['contact_no'];
          $user->country_id    = $arr_user_data['country_id'];

          $user->post_code     = $arr_user_data['post_code'];
          $user->profile_image = $arr_user_data['profile_image'];
          $user->commission    = $arr_user_data['commission'];
          $user->tax_id        = $arr_user_data['tax_id'];
          
          /*if representative add from sales manager then status and is_approved bydefault '1'*/
          $user->status        = '1';
          $user->is_approved   = '1';

          $user->save();
      }
      else
      {
       
              Sentinel::update($user, [
                                        'email'         => $arr_user_data['email'],
                                        'first_name'    => $arr_user_data['first_name'],
                                        'last_name'     => $arr_user_data['last_name'],
                                        'country_code'  => $arr_user_data['country_code'],
                                        'contact_no'    => $arr_user_data['contact_no'],
                                        'post_code'     => $arr_user_data['post_code'],
                                        'country_id'    => $arr_user_data['country_id'],
                                        'profile_image' => $arr_user_data['profile_image'],
                                        'tax_id'        => $arr_user_data['tax_id']
        
                                 
                                    ]);
      }

  

        $representative                   =  RepresentativeModel::firstOrNew(['user_id' => $user->id]);
        $representative->sales_manager_id = $loggedInUserId;
        $representative->area_id          = isset($area_id)?$area_id:0;

        $representative->save();


        /*assign vendor to the representative*/

      
        $check_record = $this->VendorRepresentativeMappingModel->where('representative_id',$user_id)->get();
        if($check_record)
        {
            $this->VendorRepresentativeMappingModel->where('representative_id',$user_id)->delete();
        }

        if ($request->input('vendor_id') != "")
        {
            foreach ($request->input('vendor_id') as $key => $value)
            {
                $this->VendorRepresentativeMappingModel->create(['representative_id' => $user->id, 'vendor_id'=>$value]);
            }
            
        }


        //send login details to representative email address

        $this->module_title    = "Representative";
        $this->module_url_path = url('/').'/sales_manager/representative_listing';

        if($is_update == false)
        {
            $arr_mail_data = $this->built_mail_data($arr_user_data['email'],$user_password); 

            $email_status  = $this->EmailService->send_mail($arr_mail_data);

            $response['status']          = "success";
            $response['description']     = str_singular($this->module_title)." has been created.";
            $response['url']             = url('/')."/sales_manager/representative_listing";
        }
        else
        {
            $response['description']     = str_singular($this->module_title)." has been updated.";
            $response['status']          = "success";
            $response['url']             = url('/')."/sales_manager/representative_listing";
        }
    
        return response()->json($response); 
  }


  function changeAprovalStatus(Request $request)
  {
    
      $representative_status = $request->input('representativeAprovalStatus');
     
      $representative_id     = $request->input('representative_id');
     
      $representative_id           = base64_decode($representative_id);
 
      if($representative_status==1)
      {
        
        $this->UserModel->where('id',$representative_id)->update(['is_approved'=>1]);
        
        $response['status']  = 'SUCCESS';
        $response['message'] = $this->module_title.' has been approved.';

      }
      elseif($representative_status==0)
      {
        
        $this->UserModel->where('id',$representative_id)->update(['is_approved'=>0]);

        $response['status']  = 'SUCCESS';
        $response['message'] = $this->module_title.' has been disapproved.';
      }
      else
      {
        $response['status']  = 'ERROR';
        $response['message'] = 'Something went wrong, please try again.';
      }
      
    return response()->json($response); 
}

public function built_mail_data($email,$user_password)
{   
        $credentials = ['email' => $email];
    
        $user = Sentinel::findByCredentials($credentials); // check if user exists
    
        if($user)
        {
            $arr_user = $user->toArray();

            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>' ;

            $site_setting_obj = SiteSettingModel::first();
            if($site_setting_obj)
            {
                $site_setting_arr = $site_setting_obj->toArray();            
            }

            $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'; 


            $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                                  'EMAIL'        => $arr_user['email'],
                                  'PASSWORD'     => $user_password,
                                  'LOGIN_URL'    => $reminder_url,
                                  'USER_ROLE'    => "Representative",
                                  'APP_URL'      => $site_name
                          ];


            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '34';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['user']              = $arr_user;
            $arr_mail_data['arr_user']          = $arr_user;

            return $arr_mail_data;
        }
        return FALSE;
  }
    
  public function representative_listing()
  {   
      $this->module_title    ="Representative";
      $this->module_url_path = url('/').'/sales_manager';
    
      $this->arr_view_data['page_title']      = "My ".str_plural( $this->module_title);
      $this->arr_view_data['module_title']    = str_plural($this->module_title);
      $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
      return view($this->module_view_folder.'.representative.index',$this->arr_view_data);

  }

public function get_representative(Request $request)
{
   $user = \Sentinel::check();
      if($user)
      {
        $loggedInUserId = $user->id;
      }

    $arr_search_column = $request->input('column_filter');
    $this->role = 'representative';
    $this->module_url_path = url('/').'/sales_manager';
      $user_table =  $this->UserModel->getTable();
    $prefix_user_table = DB::getTablePrefix().$user_table;

    $role_table =  $this->RoleModel->getTable();
    $prefix_role_table = DB::getTablePrefix().$role_table;

    $role_user_table =  $this->RoleUsersModel->getTable();
    $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

    $rep_table = $this->RepresentativeModel->getTable();
    $prefix_rep_table = DB::getTablePrefix().$rep_table;

    $obj_user = DB::table($user_table)
            ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".commission,".
                                     $prefix_user_table.".is_approved, ".
                                     $prefix_user_table.".wallet_address as wallet_address, ".
                                     $prefix_user_table.".country_code, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $role_table.".slug as slug, ".
                                     $role_table.".name as name, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
            ->leftJoin($rep_table,$rep_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->where($rep_table.'.sales_manager_id','=',$loggedInUserId)
            ->where($role_table.'.slug','=',$this->role)
            ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC');

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        } 
        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
            $search_term      = $arr_search_column['q_contact_no'];
            $obj_user         = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }



        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            $obj_user = $obj_user->where($user_table.'.status','=', $search_term);
        }

         if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
            $search_term      = $arr_search_column['q_is_approved'];
            $obj_user = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }
        if(isset($arr_search_column['q_commission']) && $arr_search_column['q_commission']!="")
        {
            $search_term      = $arr_search_column['q_commission'];
            $obj_user = $obj_user->having('commission','LIKE', '%'.$search_term.'%');
        } 

        $json_result     = \Datatables::of($obj_user);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->id);
                            })                  
                    
                            ->editColumn('contact_no',function($data)
                            {
                              if($data->country_code != "")
                              {
                                  $countryCode = $data->country_code;
                                  $data->contact_no = str_replace($countryCode, "", $data->contact_no);
                                  $contact_no = $countryCode .'-'.get_contact_no($data->contact_no);                              
                                  return $contact_no;
                              }
                              else
                              {
                                  $contact_no = get_contact_no($data->contact_no);                              
                                  return $contact_no;
                              }
                            })

                            ->editColumn('build_action_btn',function($data) 
                            {   
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->id);
                                $build_view_action = '<a class="btn btn-outline btn-success btn-circle show-tooltip" href="'.$view_href.'" title="View">View</a>';

                                $delete_href =  $this->module_url_path.'/delete_rep/'.base64_encode($data->id);
                                $build_delete_action = '<a class="btn btn-outline btn-info btn-circle show-tooltip" onclick="confirm_delete($(this),event)" href="'.$delete_href.'" title="Delete">Delete</a>';

                                $edit_href = $this->module_url_path.'/edit/'.base64_encode($data->id);

                                    $build_edit_action = '<a class="btn btn-outline btn-success btn-circle show-tooltip"  href="'.$edit_href.'" title="edit">Edit</a>';


                                return $build_action = $build_view_action.''.$build_edit_action.''.$build_delete_action;
                            })

                           
                            ->make(true);

        $build_result = $json_result->getData();


        
        return response()->json($build_result);
}

   
  public function account_settings()
  {
    
      $arr_data  = [];
        
      $obj_data  = Sentinel::getUser();

      $loggedIn_userId = 0;

      if($obj_data)
      {
         $loggedIn_userId = $obj_data->id;
      }  

      $obj_user = $this->UserModel->where('id',$loggedIn_userId)->with(['sales_manager_details'])->first();

      $sales_manager_area_arr = $this->SalesManagerModel->where('user_id',$loggedIn_userId)
                                                        ->with(['area_details'])
                                                        
                                                        ->get()
                                                        ->toArray();
      
        if($obj_user!=null || $obj_user!='')
        { 
           $arr_data  = $obj_user->toArray(); 
           $area_id   = isset($arr_data['sales_manager_details']['area_id'])?$arr_data['sales_manager_details']['area_id']:0;

           $area_name = $this->RepAreaModel->select('area_name')->where('id',$area_id)->first();
         
           $area_name = isset($area_name['area_name'])?$area_name['area_name']:'N/A';

           $arr_data['sales_manager_details']['area_name'] = $area_name;
        }
     
        if(isset($arr_data) && sizeof($arr_data)<=0)
        {
          $this->module_url_path = url('/').'sales_manager/representative_listing';
            //return redirect($this->module_url_path);
        }
      
        $this->module_title                           = "Account Settings";
        $this->arr_view_data['page_title']            = 'Account Settings';
        $this->module_url_path                        = url('/');
        $this->arr_view_data['arr_data']              = $arr_data;    
        $this->arr_view_data['sales_manager_area']    = $sales_manager_area_arr;    
        $this->arr_view_data['module_title']          = $this->module_title;
        $this->arr_view_data['module_url_path']       = $this->module_url_path.'/sales_manager/representative_listing';

        $country_arr = $this->CountryModel->orderBy('id','ASC')
                                          ->get()
                                          ->toArray();
                  
        $this->arr_view_data['country_arr']   = isset($country_arr)?$country_arr:'';
      
        return view($this->module_view_folder.'.account_settings.account_settings',$this->arr_view_data);  
  
  }

    public function update_sales_manager(Request $request)
    {

        $arr_rules = array();
        $user_id = 0;
        $description = '';
        $form_data = $request->all();
        $obj_data  = Sentinel::getUser();

        $user_id = $obj_data->id;

        $first_name = $obj_data->first_name;
        $last_name  = $obj_data->last_name;

        $arr_rules  = [
                                        'first_name'=>'required',
                                        'last_name'=>'required',
                                        'email'=>'required|email',
                                        'country_id'=>'required',
                                        'post_code'=>'required',
                                        'description'=>'required'
                                     ];
        if(Validator::make($form_data,$arr_rules)->fails())
        {
          Flash::error('Form validation failed, please check form fields.');
          return redirect()->back();
        }

        if($request->input('country_id')=="" && $request->input('post_code')!="")
        {
          Flash::error('Invalid zip/postal code.');
          return redirect()->back();
        }
                                     
        
        if($this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$obj_data->id)
                           ->count()==1)
        {
            Flash::error('This email id already present in our system, please try another one.');
            return redirect()->back();
        }

        $profile_file_path = '';
        if($request->hasFile('image'))
        {
            $profile_image =$request->file('image');

            if($profile_image!=null){
                $profile_file_path = $profile_image->store('profile_image');
                if(isset($form_data['old_image']) && $form_data['old_image']!="")
                {
                   $old_img_path   = $this->profile_image.$form_data['old_image'];
                   $unlink_old_img = $this->GeneralService->unlink_old_image($old_img_path);
                }
            }

            //Validation for product image
                $file_extension = strtolower( $profile_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {   
                    Flash::error('please select valid file type');
                        return redirect()->back();
                }



            $arr_data['profile_image']= $profile_file_path;
        }
       
        $arr_data['first_name']   = $request->input('first_name',null);
        $arr_data['last_name']    = $request->input('last_name',null);
        $arr_data['email']        = $request->input('email',null);
        $arr_data['post_code']    = $request->input('post_code',null);
        $arr_data['contact_no'] = $request->input('contact_name',null);
        $arr_data['nationality']  = $request->input('nationality',null);
        $arr_data['country_code'] = $request->input('country_code',null);
        $arr_data['post_code']    = $request->input('post_code',null);
        $arr_data['country_id']   = $request->input('country_id',null);
        $arr_data['country_code']   = $request->input('hid_country_code',null);
        

        $description  = $request->input('description',null);



        $obj_data = Sentinel::update($obj_data, $arr_data);

        
        if($obj_data)
        {
            /* update description filed in sales manager*/
             
            $this->SalesManagerModel->where('user_id',$user_id)->update(['description'=>$description]);

          /*-------------------------------------------*/
            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
            /*$arr_event                 = [];
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_TITLE'] = $this->module_title;

            $this->save_activity($arr_event);*/
            /*----------------------------------------------------------------------*/
            //Flash::success(str_singular($this->module_title).' Updated Successfully'); 
            Flash::success('Account settings has been updated.'); 
        }
        else
        {
            Flash::error('Error occurred, while updating '.str_singular($this->module_title));  
        } 
      
        return redirect()->back();
    }

    public function view($enc_id)
    { 
        $arr_user           = [];
        $user_id            = base64_decode($enc_id);
        $this->role         = "representative";
        $this->module_title = "View";

        //$this->module_url_path = url('/')."/sales_manager/representative_listing";

        $this->module_url_path = url('/')."/sales_manager/representative_listing";

        //$arr_user = $this->UserService->get_user_information($user_id,$this->role);

        /*get all representative details*/
        $arr_user_obj = $this->RepresentativeModel->with(['get_user_details','get_area_details','sales_manager_details.get_user_data'])
                                                  ->where('user_id',$user_id)
                                                  ->first();
       
 
        if(isset($arr_user))
        {
           $arr_user = $arr_user_obj->toArray();
        }

        $arr_data = $this->VendorRepresentativeMappingModel->select('vendor_representative_mapping.id as mapping_id','vendor_representative_mapping.representative_id','vendor_representative_mapping.vendor_id','users.id as user_id','users.first_name','users.last_name','makers.company_name')
                                                        ->join('users','users.id','=','vendor_representative_mapping.vendor_id')
                                                        ->join('makers','makers.user_id','=','vendor_representative_mapping.vendor_id')
                                                        ->where('representative_id',$user_id)
                                                        ->get();


        $this->arr_view_data['arr_user']        = $arr_user;
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.representative.view',$this->arr_view_data);  

    }

    public function edit($user_id)
    {
        $getVendorsData = [];
        $user = \Sentinel::check();

        if($user)
        {
          $loggedInUserId = $user->id;
        }

        $id       = base64_decode($user_id);
        
        $obj_user_data = $this->UserModel->where('id', $id)->first();
        if($obj_user_data)
        {

            $arr_user_data = $obj_user_data->toArray();
        }
        
        $country_arr = $this->CountryModel->orderBy('id','ASC')
                                      ->get()
                                      ->toArray();

   /*     $getVendorsData =  $this->MakerModel
                                ->with(['user_details'=> function($query){ 
                                    $query->where([['status',1],['is_approved',1]]);
                                }])
                                ->get()
                                ->toArray();  */     


           //get only assigned vendor to the sales manager
      
          $getVendorsData =  $this->VendorSalesmanagerMappingModel
                                  ->with(['get_user_details'=> function($query){ 
                                        $query->where([['status',1],['is_approved',1]]);
                                    },'get_user_details.maker_details'])
                                  ->where('salesmanager_id',$loggedInUserId)
                                  ->get()
                                  ->toArray();                      

         $vendor_id_arr = $this->VendorRepresentativeMappingModel->where('representative_id',$id)->pluck('vendor_id')->toArray();                                              
                  
        $this->arr_view_data['country_arr']   = isset($country_arr)?$country_arr:'';
     
        $this->role = "representative";
        $this->module_title = "Representative";
        //$this->module_url_path = url('/')."/sales_manager/representative_listing";
        $this->module_url_path = url('/')."/sales_manager/representative_listing";

        $this->arr_view_data['edit_mode']       = TRUE;
        $this->arr_view_data['user_id']         = $id;        
        $this->arr_view_data['arr_user_data']   = $arr_user_data;   
        $this->arr_view_data['all_vendors']     = $getVendorsData; 
        $this->arr_view_data['vendor_id_arr']   = $vendor_id_arr;    
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.representative.edit',$this->arr_view_data); 
    }

    public function delete_rep($id)
    {
        $rep_id = base64_decode($id);
        DB::beginTransaction();
        $delete_user = $this->BaseModel->where('id',$rep_id)->delete();
        
        $delete_rep =  $this->RepresentativeModel->where('user_id',$rep_id)->delete();
        
        if($delete_user)
        {   
            DB::commit();
            /*also delete from representative vendor mapping*/
            $result = $this->VendorRepresentativeMappingModel->where('representative_id',$rep_id)->delete();

            Flash::success('Representative has been deleted.');
            return redirect()->back();
        }
        else
        {
           DB::rollback();
           Flash::error('Error occurred while representative deletion.');
           return redirect()->back();
        }
            

    }

    public function delete_vendor(Request $request,$id)
    {
     
        $id = base64_decode($id);
        $data = $this->VendorRepresentativeMappingModel->where('id',$id)->first();
        $representative_id = $data['representative_id'];
      
        $vendor_id = $data['vendor_id'];
       
        $delete = $this->VendorRepresentativeMappingModel->where('representative_id',$representative_id)
                                                         ->where('vendor_id',$vendor_id)
                                                         ->delete();
       
        if($delete)
        {
            Flash::success('Vendor has been deleted.');
            return redirect()->back();
        }    
        else
        {
            Flash::error('Error occurred while vendor deletion.');
            return redirect()->back();
        }

        
    }


  public function status_update(Request $request)
  {
      try
      {     $is_active = '';
            $rep_id = base64_decode($request->input('rep_id'));
            $status = $request->input('status');
            $loggedInUserId = 0;
            $user   = Sentinel::check();
            if($user)
            {
               $loggedInUserId = $user->id;
            }

            if($status == 'activate')
            {
                $is_active = '1';
                //$this->ElasticSearchService->activate_category_product($rep_id);
            }
            else if($status == 'deactivate')
            {
                $is_active = '0';
                //$this->ElasticSearchService->deactivate_category_product($rep_id);
            }

            $data['status'] = $is_active;

            $update = $this->UserModel->where('id',$rep_id)->update($data);

            if($update)
            {
                $response['status']  = 'success';
                $response['message'] = 'Status has been changed.';

            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $rep_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id'=>$rep_id,'status'=>$status]);
            $arr_event['USER_ID']      = $loggedInUserId;

            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
            }
            else
            {
                $response['status']  = 'error';
                $response['message'] = 'Error occurred while updating status.';
            }

            
        }
        catch(Exception $e)
        {
            $response['status']  = 'error';
            $response['message'] = 'Error occurred while updating status.';
        }
         
        return response()->json($response);
  }



  public function activate(Request $request)
  {
    $this->module_title ="";
    $this->module_title ="Representative";
    $enc_id = $request->input('id');

    if(!$enc_id)
    {
      return redirect()->back();
    }

    if($this->perform_activate(base64_decode($enc_id)))
    {
      Flash::success( $this->module_title.' has been activated.');
    }
    else
    {
      Flash::error('Error occurred while '.$this->module_title.' activation.');
    }

    $arr_response['data'] = 'ACTIVE';
    return response()->json($arr_response);
  }

  public function deactivate(Request $request)
  {
    $this->module_title ="";
    $this->module_title = "Representative";
    $enc_id = $request->input('id');

    if(!$enc_id)
    {
        return redirect()->back();
    }

    if($this->perform_deactivate(base64_decode($enc_id)))
    {
         Flash::success( $this->module_title.' has been deactivated.');
    }
    else
    {
        Flash::error('Error occurred while '.$this->module_title.' deactivation.');
    }

    $arr_response['data'] = 'DEACTIVE';

    return response()->json($arr_response);
  }

  public function perform_activate($id)
  {
    $entity = $this->UserModel->where('id',$id)->first();
    
    if($entity)
    {   
      //Activate the user
      $this->UserModel->where('id',$id)->update(['status'=>'1']);

      return TRUE;
    }

    return FALSE;
  }

  public function perform_deactivate($id)
  {
    $entity = $this->UserModel->where('id',$id)->first();
    
    if($entity)
    {   
      //deactivate the user
      $this->UserModel->where('id',$id)->update(['status'=>'0']);

      return TRUE;
    }
    return FALSE;
  }


    public function logout()
    {
        Sentinel::logout();
        return redirect(url('/'));
    }
}
