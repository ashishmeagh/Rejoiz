<?php

namespace App\Common\Services\Api\Representative;

use App\Models\RepresentativeLeadsModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\TransactionMappingModel;

use Sentinel;
use DB;


class DashboardService {

	public function __construct(
							     RepresentativeLeadsModel $RepresentativeLeadsModel,
                                 VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                 TransactionMappingModel $TransactionMappingModel
								) 
	{
		$this->RepresentativeLeadsModel         = $RepresentativeLeadsModel;
        $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
        $this->TransactionMappingModel          = $TransactionMappingModel;
	}

	public function get_order_counts($user_id=null,$duration=null,$chart_type=null) 
    {
	   try
		{
            /*-------get prnding,completed,canceled,total,confirmed order count----*/

           
	         
		        $transaction_mapping        = $this->TransactionMappingModel->getTable();
		        $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

		        $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
		        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;


		        $total_count = $this->RepresentativeLeadsModel
		                            ->where('representative_id',$user_id)
		                            ->where('total_wholesale_price','>','0')
		                            ->where('representative_id','!=','0')
		                            ->where('is_confirm','!=','0')
		                            ->where('is_confirm','!=','3')
		                            ->where('maker_id','!=','0')
		                            ->count();


		        $arr_data['all'][0]['order_status'] = 'all'; 
		        $arr_data['all'][0]['order_count']  = $total_count;                   


		        $pending_count   = $this->RepresentativeLeadsModel
			                      ->where('order_cancel_status','!=',2)
	                              ->where('representative_id',$user_id)
	                              ->where('is_split_order','=','0')
	                              ->where('is_confirm','!=','0')
	                              ->where('is_confirm','!=','3')
	                              ->where('ship_status','=',0)
	                              ->where(function($q){
	                                return $q->orwhere('is_payment_status','=','0')
	                                         ->orwhere('is_payment_status','=','1');
	                              })                              ->where(function($q){
	                                return $q->where('payment_term','!=','Net30')
	                                         ->where('payment_term','!=','Net30 - Online/Credit')
	                                         ->orwhereNULL('payment_term');
	                              })->count();


		        $arr_data['all'][1]['order_status'] = 'pending'; 
		        $arr_data['all'][1]['order_count']  = $pending_count;                                  



		       $completed_count = $this->RepresentativeLeadsModel
			                          ->where('maker_confirmation','=',1)
			                          ->where('ship_status','=',1)
			                          ->where('order_cancel_status','!=',2)
			                          ->where('is_payment_status','=',1)
			                          ->where('is_split_order','=','0')
			                          ->where('representative_id',$user_id)
			                          ->where(function($q) use($representative_leads){
		                                return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
		                                       ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
			                            })
                                      ->count();

		        $arr_data['all'][2]['order_status'] = 'completed'; 
		        $arr_data['all'][2]['order_count']  = $completed_count;                         


		        $canceled_count = $this->RepresentativeLeadsModel
		                               ->where('order_cancel_status','=',2)
		                               ->where('representative_id',$user_id)
		                               ->count(); 

		        $arr_data['all'][3]['order_status']  = 'cancelled'; 
		        $arr_data['all'][3]['order_count']   = $canceled_count;                                       


		        $confirmed_lead_count = 0;
		        $conf = 1;
		        if(isset($user_id) && $user_id!="" && $user_id!=0)
		        {
		          $confirmed_lead_count = RepresentativeLeadsModel::where('representative_id',
		                                                                    $user_id)
		                                                            ->where('is_confirm',1)
		                                                            ->where('order_cancel_status','!=',2)
		                                                            ->count();
		        }


                 // Get net30 pending count
		         $net_30_pending_count = $this->RepresentativeLeadsModel
                                ->where('representative_id',$representative_id)
                                ->where('total_wholesale_price','>','0')
                                ->where('order_cancel_status','!=','2')
                                ->where(function($q){
                                    return $q->orwhere('is_payment_status','=','0')
                                             ->orwhere('is_payment_status','=','1');
                                  })                                  ->where(function($q){
                                              return $q->orwhere('ship_status','=','0')
                                                       ->orwhere('ship_status','=','1');
                                            })
                                  ->where(function($q){
                                    return $q->orwhere('payment_term','=','Net30')
                                             ->orwhere('payment_term','=','Net30 - Online/Credit');
                                  })
                                ->count();

                $arr_data['all'][4]['order_status']  = 'net_30_pending_count'; 
		        $arr_data['all'][4]['order_count']   = $net_30_pending_count;  


		       $net_30_completed_count =  DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id'                              ))                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                     ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');
                            })                          ->where($representative_leads.'.total_wholesale_price','>','0')                          ->where($representative_leads.'.order_cancel_status','!=',2)                         ->where('representative_id',$representative_id)                          ->where($representative_leads.'.is_confirm','=',1)                          ->where($prefix_transaction_mapping.'.transaction_status','=',2)                          ->where($representative_leads.'.ship_status','=',1)
                          ->where($representative_leads.'.is_payment_status','=',1)
                          ->where(function($q) use($representative_leads){
                              return $q->orwhere($representative_leads.'.payment_term','=','Net30')
                                       ->orwhere($representative_leads.'.payment_term','=','Net30 - Online/Credit');
                            })
                          ->count();  

                $arr_data['all'][5]['order_status']  = 'net_30_completed_count'; 
		        $arr_data['all'][5]['order_count']   = $net_30_completed_count;                        


	         /*if($duration!=null && $duration=="week" && $chart_type!=null && $chart_type=='pie_chart')
	         {*/
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
		                                        ->where('representative_id',$user_id)
		                                        ->count();

		                $complete_count+= $compl_count;
		              
		               $pend_count =          $this->RepresentativeLeadsModel
                                              ->where('representative_id',$user_id)
                                              ->where('total_wholesale_price','>','0')
                                              ->where('representative_id','!=','0')
                                              ->where('order_cancel_status','!=','2')
                                              ->where('is_split_order','=',"0")
                                              ->where('ship_status','=',"0")
                                              ->where('is_confirm','!=','0')
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
		                                              ->where('representative_id',$user_id)
		                                              ->count();

		                $cacels_count+= $cancel_count;    
		          
		            }


                    $arr_data['pie_chart']['week'][0]['order_status'] = 'completed';  
		            $arr_data['pie_chart']['week'][0]['order_count']  = $complete_count; 


                    $arr_data['pie_chart']['week'][1]['order_status'] = 'pending';   
		            $arr_data['pie_chart']['week'][1]['order_count']  = $pendings_count;   

                    $arr_data['pie_chart']['week'][2]['order_status'] = 'canceled';    
		            $arr_data['pie_chart']['week'][2]['order_count']  = $cacels_count; 

		        } 
	        // }

	         /*if($duration!=null && $duration=="month" && $chart_type!=null && $chart_type=='pie_chart')
	         {*/

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
			                                        ->where('representative_id',$user_id)
			                                        ->count();

			                $completed_leads_count+= $compl_count;
			                
			               
			                $pend_count = $this->RepresentativeLeadsModel
                                            ->where('representative_id',$user_id)
                                            ->where('total_wholesale_price','>','0')
                                            ->where('representative_id','!=','0')
                                            ->where('order_cancel_status','!=','2')
                                            ->where('is_split_order','=',"0")
                                            ->where('ship_status','=',"0")
                                            ->where('is_confirm','!=','0')
                                            ->where('is_confirm','!=','3')
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
			                                      ->where('representative_id',$user_id)
			                                      ->count();

			                $canceled_leads_count+= $cancel_count;    
			          
			              }

	                        $arr_data['pie_chart']['month'][0]['order_status'] = 'completed'; 
				            $arr_data['pie_chart']['month'][0]['order_count']  = $completed_leads_count; 

	                        $arr_data['pie_chart']['month'][1]['order_status'] = 'pending';  
			                $arr_data['pie_chart']['month'][1]['order_count'] = $pending_leads_count;   

	                        $arr_data['pie_chart']['month'][2]['order_status'] = 'canceled';   
			                $arr_data['pie_chart']['month'][2]['order_count'] = $canceled_leads_count; 
			           } 	

			         
		                  /*-----------------last week orders count-----------------*/ 

        
                    $orders_arr  = $pending_orders_arr = $canceled_orders_arr = $completed_order_cnt_arr = $pending_order_cnt_arr =  $canceled_order_cnt_arr = $pending_monthly_order_cnt_arr = $completed_monthly_order_cnt_arr = $canceled_monthly_order_cnt_arr  = [];

			      $dates_arr = $this->getLastWeekDates();

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
				               
				              
				               // completed orders count
				                $completed_order_count = $this->RepresentativeLeadsModel
				                                              ->where('created_at','LIKE','%'.$date.'%')
				                                              ->where('is_confirm','=',1)
				                                              ->where('ship_status','=',1)
				                                              ->where('representative_id',$user_id)
				                                             // ->count();
				                                              ->sum('total_wholesale_price');

				                $completed_order_cnt_arr[] = intval($completed_order_count);                               

				               $arr_data['bar_chart']['week'][0]['order_status']  = 'completed';
				               $arr_data['bar_chart']['week'][0]['order_count']   = $completed_order_cnt_arr;


				               $pending_order_count = $this->RepresentativeLeadsModel
		                                            ->where('representative_id',$user_id)
		                                            ->where('total_wholesale_price','>','0')
		                                            ->where('representative_id','!=','0')
		                                            ->where('order_cancel_status','!=','2')
		                                            ->where('is_split_order','=',"0")
		                                            ->where('ship_status','=',"0")
		                                            ->where('is_confirm','!=','0')
		                                            ->where('is_confirm','!=','3')
		                                            ->whereDate('created_at','LIKE','%'.$date.'%')
		                                            ->sum('total_wholesale_price');

                                $pending_order_cnt_arr[] = intval($pending_order_count);                               


                               $arr_data['bar_chart']['week'][1]['order_status']  = 'pending';
				               $arr_data['bar_chart']['week'][1]['order_count']   = $pending_order_cnt_arr;


				                //canceled order count
				                $canceled_order_count = $this->RepresentativeLeadsModel
				                                              ->where('created_at','LIKE','%'.$date.'%')
				                                              ->where('order_cancel_status','=',2)
				                                              ->where('representative_id',$user_id)
				                                              ->sum('total_wholesale_price');

				                $canceled_order_cnt_arr[] = intval($canceled_order_count);                               

                               $arr_data['bar_chart']['week'][2]['order_status']  = 'cancelled';
				               $arr_data['bar_chart']['week'][2]['order_count']   =  $canceled_order_cnt_arr;
				          
				            }
				      } 

				     /*-------------------get last month order  count ----------------------------*/
				      $order_data = [];
				 

				      $currentMonth         = date('F');

				      $previous_month_name  = Date('M', strtotime($currentMonth . " last month"));


                      $month_array          = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July ', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');

				       $month_arr           = array_diff($month_array, array($previous_month_name));




				      $first_date_month     = date("Y-m-d", strtotime("first day of previous month"));
				      $last_date_month      = date("Y-m-d", strtotime("last day of previous month"));


				      $from_date            = $first_date_month.' 00:00:00';
				      $to_date              = $last_date_month.' 23:59:59';


				      //completed orders count
				      $completed_count = $this->RepresentativeLeadsModel
				                              ->where('is_confirm','=',1)
				                              ->where('ship_status','=',1)
				                              ->where('representative_id',$user_id)
				                              ->whereBetween('created_at',array($from_date,$to_date))
				                              //->count();
				                              ->sum('total_wholesale_price');


				       $arr_data['bar_chart']['month'][$previous_month_name][0]['order_status']  = 'completed';
				       $arr_data['bar_chart']['month'][$previous_month_name][0]['order_count']   =  intval($completed_count);                   
				                
				     // pending order count
				      $pending_count = $this->RepresentativeLeadsModel
			                            ->where('total_wholesale_price','>','0')
			                            ->where('representative_id','!=','0')
			                            ->where('order_cancel_status','!=','2')
			                            ->where('is_split_order','=',"0")
			                            ->where('ship_status','=',"0")
			                            ->where('is_confirm','!=','0')
			                            ->where('is_confirm','!=','3')
			                            ->whereDate('created_at','LIKE','%'.$date.'%')
			                            ->sum('total_wholesale_price');


				      $pending_monthly_order_cnt_arr[] = intval($pending_count);  


				       $arr_data['bar_chart']['month'][$previous_month_name][1]['order_status']  = 'pending';   
				       $arr_data['bar_chart']['month'][$previous_month_name][1]['order_count']   =  intval($pending_count);                    

				      //canceled order count
				      $canceled_count = $this->RepresentativeLeadsModel
				                            ->where('order_cancel_status','=',2)
				                            ->where('representative_id',$user_id)
				                            ->whereBetween('created_at',array($from_date,$to_date))
				                             //->count();
				                            ->sum('total_wholesale_price');

				      $canceled_monthly_order_cnt_arr[] = intval($canceled_count);

				      $arr_data['bar_chart']['month'][$previous_month_name][2]['order_status']  = 'canceled';   
				      $arr_data['bar_chart']['month'][$previous_month_name][2]['order_count']   =  intval($canceled_count);

				        foreach($month_arr as $month)
				       {
				       	   $arr_data['bar_chart']['month'][$month][0]['order_status']  =  'completed';  
				       	   $arr_data['bar_chart']['month'][$month][0]['order_count']   =  0;  

                           $arr_data['bar_chart']['month'][$month][1]['order_status']  =  'pending'; 
				       	   $arr_data['bar_chart']['month'][$month][1]['order_count']   =  0;  

                           $arr_data['bar_chart']['month'][$month][2]['order_status']  =  'canceled';   
				       	   $arr_data['bar_chart']['month'][$month][2]['order_count']   =  0;  
				       }


				      $response = [
										'status'  => 'success',
										'message' => 'Order counts get successfully',
										'data'    => isset($arr_data)?$arr_data:[]
									   ];

					  return $response;       
	      } 

	    }    


			catch (Exception $e) {
				$response = [
					'status'  => 'failure',
					'message' => 'Something went wrong.',
					'data'    => ''
				];

	      		return $response;
	    	}
		}

    
	  public function lastSevenDays()
	  {
	        $m  = date("m");

	        $de = date("d");

	        $y  = date("Y");

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

}




?>