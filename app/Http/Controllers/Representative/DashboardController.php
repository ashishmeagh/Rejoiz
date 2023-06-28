<?php
namespace App\Http\Controllers\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RepresentativeLeadsModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\TransactionMappingModel;

use Sentinel;
use DB;



class DashboardController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 04 July 2019
    */
    public function __construct(RepresentativeLeadsModel $RepresentativeLeadsModel,
                                VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                TransactionMappingModel $TransactionMappingModel
                                )
    {
    	  $this->arr_view_data      = [];
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
        $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
        $this->TransactionMappingModel = $TransactionMappingModel;
    	  $this->module_title       = "Dashboard";
    	  $this->module_view_folder = 'representative.dashboard';
        $this->representative_panel_slug   = config('app.project.representative_panel_slug');
        $this->module_url_path    = url($this->representative_panel_slug.'/dashboard');
    }

    public function index()
    {
        
        $user = Sentinel::check();
        $representative_id = 0;

        if($user)
        {
            $representative_id = $user->id;
        }    

/*
        $lead_count = 0;
        if(isset($representative_id) && $representative_id!="" && $representative_id!=0)
        {
          $lead_count = RepresentativeLeadsModel::where('representative_id',$representative_id)->where('total_wholesale_price','>','0')->where('order_cancel_status','!=',2)->count();
        }
     
        $confirmed_lead_count = 0;
        $conf = 1;
        if(isset($representative_id) && $representative_id!="" && $representative_id!=0)
        {
          $confirmed_lead_count = RepresentativeLeadsModel::where('representative_id',
                                                                    $representative_id)
                                                            ->where('total_wholesale_price','>','0')
                                                            ->where('is_confirm',1)
                                                            ->where('order_cancel_status','!=',2)
                                                            ->count();
        }

        $customer_count = 0;
        if(isset($representative_id) && $representative_id!="" && $representative_id!=0)
        {
          $customer_count = VendorRepresentativeMappingModel::where('representative_id',$representative_id)->count();
        }*/


          /*-------get prnding,completed,canceled,total,confirmed order count----*/
         
        $transaction_mapping        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;


        $total_count = $this->RepresentativeLeadsModel
                            ->where('representative_id',$representative_id)
                            ->where('total_wholesale_price','>','0')
                            ->where('representative_id','!=','0')
                            // ->where('order_cancel_status','!=','2')
                            ->where('is_confirm','!=','0')
                            ->where('is_confirm','!=','3')
                            ->where('maker_id','!=','0')
                            ->count();



        $pending_count   = $this->RepresentativeLeadsModel                                
                                // ->where('representative_id',$representative_id)
                                // ->where('total_wholesale_price','>','0')
                                // ->where('representative_id','!=','0')
                                // ->where('order_cancel_status','!=','2')
                                // ->where('is_split_order','=',"0")
                                // ->where('ship_status','=',"0")
                                // ->where('is_confirm','!=','0') 
                                // ->where('is_confirm','!=','3')                                 
                                // ->where(function($q){
                                //   return $q->orwhere('is_payment_status','=','0')
                                //            ->orwhere('is_payment_status','=','1');
                                // })

                              ->where('order_cancel_status','!=',2)
                              ->where('representative_id',$representative_id)
                              ->where('is_split_order','=','0')
                              ->where('is_confirm','!=','0') 
                              ->where('is_confirm','!=','3')  
                              ->where('ship_status','=',0)
                              ->where('total_wholesale_price','>','0')
                              ->where('maker_id','!=',0)
                              ->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                              })

                              ->where(function($q){
                                return $q->where('payment_term','!=','Net30')
                                         ->where('payment_term','!=','Net30 - Online/Credit')
                                         ->orwhereNULL('payment_term');
                              })

                               /* ->where('is_confirm','!=','0')
                                ->where('is_confirm','!=','4')
                                ->where('maker_id','!=','0')
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
                                })*/

                                ->count();



        $completed_count = $this->RepresentativeLeadsModel
                                // ->where('is_confirm','=',1)
                                // ->where('ship_status','=',1)
                                // ->where('representative_id',$representative_id)
                          ->where('maker_confirmation','=',1)
                          ->where('ship_status','=',1)
                          //->where($prefixed_transaction_mapping_tbl.'.transaction_status','=',2)
                          ->where('order_cancel_status','!=',2)
                          ->where('is_payment_status','=',1)
                          ->where('is_split_order','=','0')
                          ->where('representative_id',$representative_id)
                          ->where(function($q) use($representative_leads){
                              return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                       ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                            })
                                ->count(); 


        $canceled_count = $this->RepresentativeLeadsModel
                               ->where('order_cancel_status','=',2)
                               ->where('representative_id',$representative_id)
                               ->count(); 

         /* Net 30 Count */
        $net_30_pending_count = $this->RepresentativeLeadsModel
                                ->where('representative_id',$representative_id)
                                ->where('total_wholesale_price','>','0')
                                ->where('order_cancel_status','!=','2')
                                //->where('ship_status','=','0')
                                 ->where(function($q){
                                    return $q->orwhere('is_payment_status','=','0')
                                             ->orwhere('is_payment_status','=','1');
                                  })
                                  
                                  ->where(function($q){
                                              return $q->orwhere('ship_status','=','0')
                                                       ->orwhere('ship_status','=','1');
                                            })
                                  ->where(function($q){
                                    return $q->orwhere('payment_term','=','Net30')
                                             ->orwhere('payment_term','=','Net30 - Online/Credit');
                                  })
                                //->where('ship_status','=',0)
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
                           
                         ->where('representative_id',$representative_id)

                          ->where($representative_leads.'.is_confirm','=',1)
                           
                          ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                    
                          ->where($representative_leads.'.ship_status','=',1)
                          ->where($representative_leads.'.is_payment_status','=',1)
                           // ->where(function($q) use($representative_leads){
                           //    return $q->orwhere($representative_leads.'.is_payment_status','=','0')
                           //             ->orwhere($representative_leads.'.is_payment_status','=','1');
                           //  })
                                                    
                          ->where($representative_leads.'.maker_confirmation','=',1)
                          ->where(function($q) use($representative_leads){
                              return $q->orwhere($representative_leads.'.payment_term','=','Net30')
                                       ->orwhere($representative_leads.'.payment_term','=','Net30 - Online/Credit');
                            })
                          ->count();                         


                          //dd($net_30_completed_count->toSql(),$net_30_completed_count->getBindings());
        $confirmed_lead_count = 0;
        $conf = 1;
        if(isset($representative_id) && $representative_id!="" && $representative_id!=0)
        {
          $confirmed_lead_count = RepresentativeLeadsModel::where('representative_id',
                                                                    $representative_id)
                                                           
                                                            ->where('is_confirm',1)
                                                            ->where('order_cancel_status','!=',2)
                                                            ->count();
        }
                                                                

      $order_count_arr = [];
      $order_count_arr['total_count']     = $total_count or 0;
      $order_count_arr['pending_count']   = $pending_count or 0;
      $order_count_arr['completed_count'] = $completed_count or 0;
      $order_count_arr['canceled_count']  = $canceled_count or 0;
      $order_count_arr['confirmed_count'] = $confirmed_lead_count or 0;
      $order_count_arr['net_30_pending_count']      = $net_30_pending_count or 0;
      $order_count_arr['net_30_completed_count']    = $net_30_completed_count or 0;


       /*-------------new code for last 7 days order count---------------------------------*/

        $last_seven_days_arr = $this->lastSevenDays();
        
        $complete_count = $pendings_count = $cancel_count = $cacels_count = 0;
        $sales_leads_count_arr = [];

        if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
        { 
            foreach($last_seven_days_arr as $key => $date) 
            { 
               
               // completed orders count
                $compl_count = $this->RepresentativeLeadsModel
                                        ->where('created_at','LIKE','%'.$date.'%')
                                        ->where('is_confirm','=',1)
                                        ->where('ship_status','=',1)
                                        ->where('representative_id',$representative_id)
                                        ->count();

                $complete_count+= $compl_count;
                
                //pending order count
                /*$pend_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('representative_id',$representative_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                           ->orWhere('is_confirm','=',2);
                                            })
                                            ->count();*/
                $pend_count = $this->RepresentativeLeadsModel
                                            // ->where('representative_id',$representative_id)
                                            // ->where('created_at','LIKE','%'.$date.'%')
                                            // ->where('order_cancel_status','!=',2)
                                            // ->where('ship_status','=',0)
                                              ->where('representative_id',$representative_id)
                                              ->where('total_wholesale_price','>','0')
                                              ->where('representative_id','!=','0')
                                              ->where('order_cancel_status','!=','2')
                                              ->where('is_split_order','=',"0")
                                              ->where('ship_status','=',"0")
                                              ->where('is_confirm','!=','0') 
                                              ->where('maker_id','!=','0')
                                              ->where('is_confirm','!=','3')
                                              ->whereDate('created_at','LIKE','%'.$date.'%')                                 
                                              ->where(function($q){
                                              return $q->orwhere('is_payment_status','=','0')
                                                       ->orwhere('is_payment_status','=','1');
                                              })
                                            ->count();




                $pendings_count += $pend_count;                      

               // canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('representative_id',$representative_id)
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
               
               // completed orders count
                $compl_count = $this->RepresentativeLeadsModel
                                        ->where('created_at','LIKE','%'.$date.'%')
                                        ->where('is_confirm','=',1)
                                        ->where('ship_status','=',1)
                                        ->where('representative_id',$representative_id)
                                        ->count();

                $completed_leads_count+= $compl_count;
                
                //pending order count
                /*$pend_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('representative_id',$representative_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                           ->orWhere('is_confirm','=',2);
                                            })
                                            ->count();*/
                $pend_count = $this->RepresentativeLeadsModel
                                            // ->where('representative_id',$representative_id)
                                            // ->where('created_at','LIKE','%'.$date.'%')
                                            // ->where('order_cancel_status','!=',2)
                                            // ->where('ship_status','=',0)
                                            ->where('representative_id',$representative_id)
                                            ->where('total_wholesale_price','>','0')
                                            ->where('representative_id','!=','0')
                                            ->where('order_cancel_status','!=','2')
                                            ->where('is_split_order','=',"0")
                                            ->where('ship_status','=',"0")
                                            ->where('is_confirm','!=','0') 
                                            ->where('is_confirm','!=','3')
                                            ->where('maker_id','!=','0')
                                            ->whereDate('created_at','LIKE','%'.$date.'%')
                                            ->where(function($q){
                                              return $q->orwhere('is_payment_status','=','0')
                                              ->orwhere('is_payment_status','=','1');
                                            })
                                            ->count();

                $pending_leads_count += $pend_count;                      

                //canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                      ->where('created_at','LIKE','%'.$date.'%')
                                      ->where('order_cancel_status','=',2)
                                      ->where('representative_id',$representative_id)
                                      ->count();

                $canceled_leads_count+= $cancel_count;    
          
            }

            $lead_count_arr['completed_order'] = $completed_leads_count;
            $lead_count_arr['pending_order']   = $pending_leads_count;
            $lead_count_arr['canceled_order']  = $canceled_leads_count;
        }



      /*-----------------last week orders count-----------------*/ 

        
      $orders_arr = $pending_orders_arr = $canceled_orders_arr = [];

      $dates_arr = $this->getLastWeekDates();

      $week_date_arr = array('Monday'   =>$dates_arr[0],
                             'Tuesday'  =>$dates_arr[1],
                             'Wednesday'=>$dates_arr[2],
                             'Thursday' =>$dates_arr[3],
                             'Friday'   =>$dates_arr[4],
                             'Saturday' =>$dates_arr[5],
                             'Sunday'  =>$dates_arr[6]
                            );

      if(isset($week_date_arr) && count($week_date_arr)>0)
      {
          foreach($week_date_arr as $key => $date) 
          { 
               
              
               // completed orders count
                $completed_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('is_confirm','=',1)
                                              ->where('ship_status','=',1)
                                              ->where('representative_id',$representative_id)
                                             // ->count();
                                              ->sum('total_wholesale_price');

                $orders_arr[$key]['completed_order']= intval($completed_order_count);
                
               // pending order count
               /* $pending_order_count = $this->RepresentativeLeadsModel
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('representative_id',$representative_id)
                                            ->where(function($query)
                                            {
                                              return $query->where('is_confirm','=',0)
                                                           ->orWhere('is_confirm','=',2);
                                            })
                                            //->count();
                                            ->sum('total_wholesale_price');*/

                $pending_order_count = $this->RepresentativeLeadsModel
                                            //  ->where('representative_id',$representative_id)
                                            // ->where('created_at','LIKE','%'.$date.'%')
                                            // ->where('order_cancel_status','!=',2)
                                            // ->where('ship_status','=',0)
                                            ->where('representative_id',$representative_id)
                                            ->where('total_wholesale_price','>','0')
                                            ->where('representative_id','!=','0')
                                            ->where('order_cancel_status','!=','2')
                                            ->where('is_split_order','=',"0")
                                            ->where('ship_status','=',"0")
                                            ->where('is_confirm','!=','0') 
                                            ->where('is_confirm','!=','3')
                                            ->whereDate('created_at','LIKE','%'.$date.'%')
                                            ->sum('total_wholesale_price');

                $orders_arr[$key]['pending_order']= intval($pending_order_count);                      

                //canceled order count
                $canceled_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('representative_id',$representative_id)
                                              //->count();
                                              ->sum('total_wholesale_price');

                $orders_arr[$key]['canceled_order'] = intval($canceled_order_count);    
          
            }


      }             
   
      /*-------------------get last month order  count ----------------------------*/
      $order_data = [];
 
      //get the name of previous month

      $currentMonth         = date('F');

      $previous_month_name  = Date('F', strtotime($currentMonth . " last month"));


      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));


      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';


      //completed orders count
      $completed_count = $this->RepresentativeLeadsModel
                              ->where('is_confirm','=',1)
                              ->where('ship_status','=',1)
                              ->where('representative_id',$representative_id)
                              ->whereBetween('created_at',array($from_date,$to_date))
                              //->count();
                              ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['completed_order']= intval($completed_count);
                
     // pending order count
      $pending_count = $this->RepresentativeLeadsModel
                            // ->where('representative_id',$representative_id)
                            // ->whereBetween('created_at',array($from_date,$to_date))
                            // ->where('order_cancel_status','!=',2)
                            // ->where('ship_status','=',0)

                            ->where('representative_id',$representative_id)
                            ->where('total_wholesale_price','>','0')
                            ->where('representative_id','!=','0')
                            ->where('order_cancel_status','!=','2')
                            ->where('is_split_order','=',"0")
                            ->where('ship_status','=',"0")
                            ->where('is_confirm','!=','0') 
                            ->where('is_confirm','!=','3')
                            ->whereDate('created_at','LIKE','%'.$date.'%')                           
                            ->sum('total_wholesale_price');

      /*$pending_count = $this->RepresentativeLeadsModel
                            ->where('order_cancel_status','!=',2)
                            ->where('representative_id',$representative_id)
                            ->whereBetween('created_at',array($from_date,$to_date))

                            ->where(function($query)
                            {
                                return $query->where('is_confirm','=',0)
                                             ->orWhere('is_confirm','=',2);
                            })
                            //->count();
                            ->sum('total_wholesale_price');*/

      $order_data[$previous_month_name]['pending_order'] = intval($pending_count);                      

      //canceled order count
      $canceled_count = $this->RepresentativeLeadsModel
                            ->where('order_cancel_status','=',2)
                            ->where('representative_id',$representative_id)
                            ->whereBetween('created_at',array($from_date,$to_date))
                             //->count();
                            ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['canceled_order'] = intval($canceled_count);  
   

      $this->arr_view_data['sales_leads_count_arr']      = $sales_leads_count_arr;  
      $this->arr_view_data['lead_count_arr']             = $lead_count_arr; 
      $this->arr_view_data['orders_arr']                 = $orders_arr; 
      $this->arr_view_data['orders_data']                = $order_data; 
      $this->arr_view_data['module_title']               = $this->module_title;
      $this->arr_view_data['order_count_arr']            = $order_count_arr;
      $this->arr_view_data['previous_month_name']        = $previous_month_name;
      $this->arr_view_data['page_title']                 = 'Dashboard';
      $this->arr_view_data['module_url_path']            = $this->module_url_path;
      $this->arr_view_data['representative_panel_slug']  = $this->representative_panel_slug; 


    	return view($this->module_view_folder.'.index',$this->arr_view_data);
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
    

}
