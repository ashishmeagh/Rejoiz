<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetailerQuotesModel;
use App\Models\FavoriteModel;
use App\Models\ProductsModel;
use App\Models\UserModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\TransactionMappingModel;
use App\Models\TransactionsModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerModel;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;


use Sentinel;
use Flash;
use Session;
use Paginate;
use DB;

class DashboardController extends Controller
{

    public function __construct(FavoriteModel $FavoriteModel,
                                ProductsModel $ProductsModel,
                                RetailerModel $RetailerModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                UserModel $UserModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                TransactionMappingModel $TransactionMappingModel,
                                TransactionsModel $TransactionsModel,
                                MakerModel $MakerModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel
                               )
    {
    	$this->arr_view_data         = [];
    	$this->module_title          = "Dashboard";
    	$this->module_view_folder    = 'retailer.dashboard';
      $this->FavoriteModel         = $FavoriteModel;
      $this->RetailerQuotesModel   = $RetailerQuotesModel;
      $this->ProductsModel         = $ProductsModel;
      $this->TransactionMappingModel = $TransactionMappingModel;
      $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
      $this->TransactionsModel     = $TransactionsModel;
      $this->UserModel             = $UserModel;
      $this->MakerModel            = $MakerModel;
      $this->RoleModel             = $RoleModel;
      $this->RoleUsersModel        = $RoleUsersModel;
      $this->RetailerModel         = $RetailerModel; 
      $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
      $this->retailer_panel_slug   = config('app.project.retailer_panel_slug');
      $this->module_url_path       = url($this->retailer_panel_slug.'/dashboard');
    
    }

    public function index()
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        } 


      /*--------------get rep/sales order count----------------------------*/
           
      $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $transaction_mapping        = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $maker_table        = $this->MakerModel->getTable();
      $prefix_maker_table =  DB::getTablePrefix().$maker_table; 

      $user_table        =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

       /*Order by rep/sales total count*/                    
       $total_count     = $this->get_rep_sales_tot_count($loggedIn_userId);
       $pending_count   = $this->get_rep_sales_tot_count($loggedIn_userId,1,false);  
       $completed_count = $this->get_rep_sales_tot_count($loggedIn_userId,false,1);    
       $net_30_pending_count = $this->get_rep_sales_tot_count($loggedIn_userId,false,false,1);  
       $net_30_completed_count = $this->get_rep_sales_tot_count($loggedIn_userId,false,false,false,1);            
       $canceled_count  = $this->get_rep_sales_cancel_count($loggedIn_userId); 
                            
      $order_count_arr = [];
      $order_count_arr['total_count']     = $total_count or 0;
      $order_count_arr['pending_count']   = $pending_count or 0;
      $order_count_arr['completed_count'] = $completed_count or 0;
      $order_count_arr['canceled_count']  = $canceled_count or 0;  
      $order_count_arr['net_30_completed_count']  = $net_30_completed_count or 0;  
      $order_count_arr['net_30_pending_count']  = $net_30_pending_count or 0;  


      /*----------------------------------------------*/


      /*----------get my order count --------------------------------------*/


      $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
      $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

      $transaction_mapping_table          = $this->TransactionMappingModel->getTable();
      $prefixed_transaction_mapping_tbl   = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

      $transaction_tbl                = $this->TransactionsModel->getTable();        
      $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable();


      // My orders functionality
      {
      $total_count = $this->RetailerQuotesModel
                           ->where('retailer_id',$loggedIn_userId)
                           ->where('is_split_order','=','0')
                           ->count();


        $pending_count = $this->RetailerQuotesModel
                              ->where('order_cancel_status','!=',2)
                              ->where('retailer_id',$loggedIn_userId)
                              ->where('is_split_order','=','0')
                              ->where('ship_status','=',0)
                              ->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                              })

                              ->where(function($q){
                                return $q->where('payment_term','!=','Net30')
                                         ->where('payment_term','!=','Net30 - Online/Credit');
                              })
                              ->count();   
                              //dd($pending_count->toSql(),$pending_count->getBindings());              
    
      //pending orders      
       $completed_count = DB::table($retailer_quotes_tbl_name)
                          ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status"
                                        ))

                          ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');
                          })   
                          ->where($prefixed_retailer_quotes_tbl.'.maker_confirmation','=',1)
                          ->where($prefixed_retailer_quotes_tbl.'.ship_status','=',1)
                          ->where($prefixed_transaction_mapping_tbl.'.transaction_status','=',2)
                          ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',2)
                          ->where($prefixed_retailer_quotes_tbl.'.is_split_order','=','0')
                          ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$loggedIn_userId)
                          ->where(function($q) use($prefixed_retailer_quotes_tbl){
                              return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                                       ->where($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30 - Online/Credit');
                            })
                          ->count();

      $canceled_count = $this->RetailerQuotesModel
                             ->where('order_cancel_status','=',2)
                             ->where('retailer_id',$loggedIn_userId)
                             ->where('is_split_order','=','0')
                             ->count();   

      // For Net30
      $my_net_30_pending_count = $this->RetailerQuotesModel
                    ->where('order_cancel_status','!=',2)
                    ->where('retailer_id',$loggedIn_userId)
                    ->where('is_split_order','=','0')
                    //->where('ship_status','=',0)
                    //->where('payment_term','=','Net30')
                    ->where(function($q){
                      return $q->orwhere('is_payment_status','=','0')
                               ->orwhere('is_payment_status','=','1');
                    })
                    ->where(function($q){
                                return $q->where('is_payment_status','=','0');
                                         //->where('ship_status','=','1');
                              })
                    ->where(function($q){
                                return $q->orwhere('ship_status','=','0')
                                         ->orwhere('ship_status','=','1');
                              })
                    ->where(function($q){
                      return $q->orwhere('payment_term','=','Net30')
                               ->orwhere('payment_term','=','Net30 - Online/Credit');
                    })
                   ->count(); 
                  //dd($my_net_30_pending_count->toSql())


       $my_net_30_completed_count = DB::table($retailer_quotes_tbl_name)
                          ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status"
                                        ))

                          ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');
                          })   
                          ->where($prefixed_retailer_quotes_tbl.'.maker_confirmation','=',1)
                          ->where($prefixed_retailer_quotes_tbl.'.ship_status','=',1)
                          ->where($prefixed_transaction_mapping_tbl.'.transaction_status','=',2)
                          ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',2)
                          ->where($prefixed_retailer_quotes_tbl.'.is_split_order','=','0')
                          ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$loggedIn_userId)
                          ->where(function($q){
                                  return $q->orwhere('payment_term','=','Net30')
                                           ->orwhere('payment_term','=','Net30 - Online/Credit');
                                })
                          ->count();



       $quotes_arr = [];
       $quotes_arr['total_count']               = $total_count;                                            
       $quotes_arr['pending_count']             = $pending_count;                                            
       $quotes_arr['completed_count']           = $completed_count;                                            
       $quotes_arr['canceled_count']            = $canceled_count;  
       $quotes_arr['my_net_30_pending_count']   = $my_net_30_pending_count;   
       $quotes_arr['my_net_30_completed_count'] = $my_net_30_completed_count;   



      /*-------------------------------------------------------------------*/      

     /*---------new code for last 7 days order retailer quotes---------------------*/

        $last_seven_days_arr = $this->lastSevenDays();
        $complete_count = $pendings_count = $cancel_count = $cacels_count = 0;
        $sales_quotes_count_arr = [];
        if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
        { 
            foreach($last_seven_days_arr as $key => $date) 
            { 
                  $completed_quotes_count = $this->RetailerQuotesModel
                                                  ->where('created_at','LIKE','%'.$date.'%')
                                                  // ->where('maker_confirmation',1)
                                                  ->where('maker_confirmation','=',1)
                                                  ->where('ship_status','=',1)
                                                  ->where('is_payment_status','=',1)
                                                  ->where('order_cancel_status','!=',2)
                                                  ->where('is_split_order','=','0')
                                                  ->where('retailer_id',$loggedIn_userId)
                                                  ->where(function($q) use($prefixed_retailer_quotes_tbl){
                                                      return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                                                               ->where($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30 - Online/Credit');
                                                    })
                                                  ->count();

                    

                    $complete_count+= $completed_quotes_count;
                          
                          
                    //pending order count
                    $pending_quotes_count = $this->RetailerQuotesModel
                                                ->where('created_at','LIKE','%'.$date.'%')
                                                ->where('order_cancel_status','!=',2)
                                                ->where('retailer_id',$loggedIn_userId)
                                                ->where('maker_confirmation','=',NULL)
                                                ->where('ship_status','=',0)
                                                ->where('is_split_order','=','0')
                                                ->where(function($q){
                                                  return $q->orwhere('is_payment_status','=','0')
                                                           ->orwhere('is_payment_status','=','1');
                                                })
                                                ->where(function($q){
                                                  return $q->where('payment_term','!=','Net30')
                                                           ->where('payment_term','!=','Net30 - Online/Credit');
                                                })
                                                ->count();

                     $pendings_count+= $pending_quotes_count;   
                 

                          
                    //canceled order count
                    $canceled_quotes_count = $this->RetailerQuotesModel
                                                  ->where('created_at','LIKE','%'.$date.'%')
                                                  ->where('order_cancel_status','=',2)
                                                  ->where('is_split_order','=','0')
                                                  ->where('retailer_id',$loggedIn_userId)
                                                  ->count();

                   $cancel_count+= $canceled_quotes_count; 
                
            }

            $sales_quotes_count_arr['completed_order'] = $complete_count;
            $sales_quotes_count_arr['pending_order']   = $pendings_count;
            $sales_quotes_count_arr['canceled_order']  = $cancel_count;

        } 

  

     /*--------------------new code for last 30 days retailer quotes---------------------*/

       $last_thirty_days_arr = $this->lastThirtyDays();

       // dd($last_thirty_days_arr); 
     
       $complete_retailer_quotes_count = $pending_retailer_quotes_count = $canceled_retailer_quotes_count =0;

       $retailer_quotes_arr=[];


        if(isset($last_thirty_days_arr) && count($last_thirty_days_arr)>0)
        { 
            foreach($last_thirty_days_arr as $key => $date) 
            { 
               
                $quotes_count_completed = $this->RetailerQuotesModel
                                              ->whereDate('created_at','LIKE','%'.$date.'%')
                                              ->where('maker_confirmation','=',1)
                                              ->where('ship_status','=',1)
                                              ->where('is_payment_status','=',1)
                                              ->where('order_cancel_status','!=','2')
                                              ->where('is_split_order','=','0')
                                              ->where('retailer_id',$loggedIn_userId)
                                              ->where(function($q) use($prefixed_retailer_quotes_tbl){
                                              return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                                              ->where($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30 - Online/Credit');
                                              })
                                               ->count();                
                $complete_retailer_quotes_count+= $quotes_count_completed;
            
            
                //pending order count
                $quotes_count_pending = $this->RetailerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','!=',2)
                                              ->where('is_split_order','=','0')

                                              ->where('ship_status','=','0')
                                              ->where('retailer_id',$loggedIn_userId)    
                                              ->where(function($q){
                                                return $q->orwhere('is_payment_status','=','0')
                                                         ->orwhere('is_payment_status','=','1');
                                              })
                                              ->where(function($q) use($prefixed_retailer_quotes_tbl){
                                                  return $q->orwhere('payment_term','!=','Net30')
                                                           ->where('payment_term','!=','Net30 - Online/Credit')
                                                           ->orwhereNULL('payment_term');
                                                })
                                              ->count();

                
                $pending_retailer_quotes_count+= $quotes_count_pending;                      

            
                // canceled order count
                $quotes_count_canceled = $this->RetailerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('is_split_order','=','0')
                                              ->where('retailer_id',$loggedIn_userId)
                                              ->count();

                $canceled_retailer_quotes_count+= $quotes_count_canceled; 
          
            }

            $retailer_quotes_arr['completed_order'] = $complete_retailer_quotes_count;
            $retailer_quotes_arr['pending_order']   = $pending_retailer_quotes_count;
            $retailer_quotes_arr['canceled_order']  = $canceled_retailer_quotes_count;

        } 
        }

        /*------------------new code for last 7 days for rep/sales leads-----*/
         // Rep / sales  orders functionality
        { 
        $last_seven_days_arr = $this->lastSevenDays();
        $leads_complete_count = $leads_pendings_count = $leads_cacels_count = 0;
        $sales_leads_count_arr = [];
        //dd($last_seven_days_arr);
        if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
        { 
            foreach($last_seven_days_arr as $key => $date) 
            { 
               
                //completed orders count
                $compl_count = $this->RepresentativeLeadsModel
                                        ->whereDate('created_at','LIKE','%'.$date.'%')
                                        ->where('is_confirm','=',1)
                                        ->where('ship_status','=',1)
                                        ->where('is_split_order','=','0')
                                        ->where('order_cancel_status','!=',2)
                                        ->where('retailer_id',$loggedIn_userId)
                                        ->where(function($q) use($representative_leads){
                                        return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                        ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                        })
                                        ->count();


                 $leads_complete_count += $compl_count;    
                
                //pending order count
                $pend_count = $this->RepresentativeLeadsModel
                                    ->where('retailer_id',$loggedIn_userId)
                                    ->whereDate('created_at','LIKE','%'.$date.'%')
                                    ->where('order_cancel_status','!=',2)
                                    ->where($representative_leads.'.is_confirm','!=',4)
                                    ->where($representative_leads.'.is_confirm','!=',0)
                                    ->where($representative_leads.'.maker_id','!=',0)
                                    /*->where('ship_status','=','0')
                                    ->where('is_payment_status','=','0')*/
                                    ->where(function($q) use($prefix_representative_leads_tbl){
                                          $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','1')
                                            ->where($prefix_representative_leads_tbl.'.ship_status','=','0')
                                    ->orwhere(function($q) use($prefix_representative_leads_tbl){
                                          $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','0')
                                            ->where($prefix_representative_leads_tbl.'.ship_status','=','1');
                                    })
                                    ->orwhere(function($q) use($prefix_representative_leads_tbl){
                                          return $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','0')
                                                    ->where($prefix_representative_leads_tbl.'.ship_status','=','0');
                                    });
                                    }
                                    )
                                    ->where('is_split_order','=','0')
                                     ->where(function($q) use($representative_leads){
                                                return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                         ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                                         ->orwhereNULL('payment_term');
                                              })
                                    ->count();

                  //dd($pend_count->toSql(),$pend_count->getBindings());

                  $leads_pendings_count += $pend_count;    
                //canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                     ->where('created_at','LIKE','%'.$date.'%')
                                      ->where('order_cancel_status','=',2)
                                      ->where('is_split_order','=','0')

                                      ->where('retailer_id',$loggedIn_userId)
                                      ->count();

                $leads_cacels_count+= $cancel_count;    
          
            }

            $sales_leads_count_arr['completed_order'] = $leads_complete_count;
            $sales_leads_count_arr['pending_order']   = $leads_pendings_count;
            $sales_leads_count_arr['canceled_order']  = $leads_cacels_count;

        } 


      /*------------new code for last 30 days order count for rep/sales-------------------*/    

       $last_thirty_days_arr = $this->lastThirtyDays();
     
       $completed_leads_count = $pending_leads_count = $canceled_leads_count =0;

       $lead_count_arr=[];


        if(isset($last_thirty_days_arr) && count($last_thirty_days_arr)>0)
        { 
            foreach($last_thirty_days_arr as $key => $date) 
            { 
               
                //completed orders count
                $compl_count = $this->RepresentativeLeadsModel
                                        // ->where('created_at','LIKE','%'.$date.'%')
                                        // ->where('is_confirm','=',1)
                                        // ->where('ship_status','=',1)
                                         ->where('retailer_id',$loggedIn_userId)
                                         ->where('created_at','LIKE','%'.$date.'%')
                                        ->where('is_confirm','=',1)
                                        ->where('is_payment_status','=','1')
                                        ->where('ship_status','=',1)
                                        ->where('is_split_order','=','0')
                                        ->where('maker_confirmation','=',1)
                                        ->where(function($q) use($representative_leads){
                                        return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                        ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                        })
                                        ->count();

               

                $completed_leads_count+= $compl_count;
                
              //  pending order count           
                $pend_count = $this->RepresentativeLeadsModel
                                             ->where('retailer_id',$loggedIn_userId)
                                            ->whereDate('created_at','LIKE','%'.$date.'%')
                                            ->where($representative_leads.'.order_cancel_status','!=','2')
                                           // ->where('is_payment_status','=','0')
                                            ->where($representative_leads.'.is_confirm','!=',3)
                                            ->where($representative_leads.'.is_confirm','!=',0)
                                            ->where($representative_leads.'.maker_id','!=',0)
                                            ->where('is_split_order','=','0')
                                            //->where('ship_status','=',0)   
                                            ->where(function($q) use($prefix_representative_leads_tbl){
                                                  $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','1')
                                                    ->where($prefix_representative_leads_tbl.'.ship_status','=','0')
                                            ->orwhere(function($q) use($prefix_representative_leads_tbl){
                                                  $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','0')
                                                    ->where($prefix_representative_leads_tbl.'.ship_status','=','1');
                                            })
                                            ->orwhere(function($q) use($prefix_representative_leads_tbl){
                                                  return $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','0')
                                                            ->where($prefix_representative_leads_tbl.'.ship_status','=','0');
                                            });
                                            }
                                            )
                                            ->where(function($q) use($representative_leads){
                                                return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                         ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                                         ->orwhereNULL('payment_term');
                                              })
                                            ->count(); 




                                            //dump($pend_count);

                  $pending_leads_count += $pend_count;  

               
                  

               // canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                      ->where('created_at','LIKE','%'.$date.'%')
                                      ->where('order_cancel_status','=',2)
                                      ->where('is_split_order','=','0')
                                      ->where('retailer_id',$loggedIn_userId)
                                      ->count();

                $canceled_leads_count+= $cancel_count;    
          
            }

            $lead_count_arr['completed_order'] = $completed_leads_count;
            $lead_count_arr['pending_order']   = $pending_leads_count;
            $lead_count_arr['canceled_order']  = $canceled_leads_count;

        } 
      /*----------------------------------------------------------------------------*/

      //dd($lead_count_arr);
      /*-----------------last week rep/sales leads count count-----------------*/ 
   
      $orders_arr = $pending_orders_arr = $canceled_orders_arr = [];

      $dates_arr  = $this->getLastWeekDates();

      $week_date_arr = array('Monday'   =>$dates_arr[0],
                             'Tuesday'  =>$dates_arr[1],
                             'Wednesday'=>$dates_arr[2],
                             'Thursday' =>$dates_arr[3],
                             'Friday'   =>$dates_arr[4],
                             'Saturday' =>$dates_arr[5],
                             'Sunday'   =>$dates_arr[6]
                            );
      //dd($week_date_arr);
      if(isset($week_date_arr) && count($week_date_arr)>0)
      {
          foreach($week_date_arr as $key => $date) 
          { 
               
              //  completed orders count
                $completed_order_count = $this->RepresentativeLeadsModel
                                              // ->where('created_at','LIKE','%'.$date.'%')
                                              // ->where('is_confirm','=',1)
                                              // ->where('ship_status','=',1)
                                              // ->where('is_split_order','=','0')
                                              // ->where('retailer_id',$loggedIn_userId)
                                            ->where('retailer_id',$loggedIn_userId)
                                            ->whereDate('created_at','LIKE','%'.$date.'%')
                                            ->where('is_confirm','=',1)
                                            ->where('is_payment_status','=','1')
                                            ->where('ship_status','=',1)
                                            ->where('is_split_order','=','0')
                                            ->where('maker_confirmation','=',1)
                                            ->where(function($q) use($representative_leads){
                                            return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                            ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                            })  
                                            ->sum('total_wholesale_price');

                $orders_arr[$key]['completed_order'] = floatval($completed_order_count);
                
                //pending order count
                $pending_order_count = $this->RepresentativeLeadsModel
                                            // ->where('created_at','LIKE','%'.$date.'%')
                                            // ->where('order_cancel_status','!=',2)
                                            // ->where('is_split_order','=','0')
                                            // ->where('retailer_id',$loggedIn_userId)
                                            // ->where($representative_leads.'.is_confirm','!=',4)
                                            // ->where($representative_leads.'.is_confirm','!=',0)
                                            // ->where($representative_leads.'.maker_id','!=',0)
                                            // ->where('ship_status','=',0)    
                                             ->where('retailer_id',$loggedIn_userId)
                                            ->whereDate('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('is_payment_status','=','0')
                                            ->where($representative_leads.'.is_confirm','!=',4)
                                            ->where($representative_leads.'.is_confirm','!=',0)
                                            ->where($representative_leads.'.maker_id','!=',0)
                                            ->where('is_split_order','=','0')
                                            ->where('ship_status','=',0)   
                                            ->where(function($q) use($representative_leads){
                                                return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                         ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                                         ->orwhereNULL('payment_term');
                                              })                                       
                                            ->sum('total_wholesale_price');

                $orders_arr[$key]['pending_order'] = floatval($pending_order_count);                      

                //  canceled order count
                $canceled_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('is_split_order','=','0')
                                              ->where('retailer_id',$loggedIn_userId)
                                             // ->count();
                                              ->sum('total_wholesale_price');

                $orders_arr[$key]['canceled_order'] = floatval($canceled_order_count);    
          
            }
      }             
   
      /*-------------get last month order  count for rep/sales orders------------------*/
      $order_data = [];
 
     // get the name of previous month

      $currentMonth         = date('F');

      $previous_month_name  = Date('F', strtotime($currentMonth . " last month"));


      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));


      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';


      //completed orders count
      $completed_count = $this->RepresentativeLeadsModel
                              // ->where('is_confirm','=',1)
                              // ->where('ship_status','=',1)
                              // ->where('is_split_order','=','0')
                              // ->where('retailer_id',$loggedIn_userId)
                              // ->whereBetween('created_at',array($from_date,$to_date))
                              ->where('retailer_id',$loggedIn_userId)
                              ->whereBetween('created_at',array($from_date,$to_date))
                              ->where('is_confirm','=',1)
                              ->where('is_payment_status','=','1')
                              ->where('ship_status','=',1)
                              ->where('is_split_order','=','0')
                              ->where('maker_confirmation','=',1)
                              ->where(function($q) use($representative_leads){
                              return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                              ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                              })  
                              ->sum('total_wholesale_price');


      $order_data[$previous_month_name]['completed_order']= floatval($completed_count);
                
     // pending order count
      $pending_count = $this->RepresentativeLeadsModel
                            // ->where('order_cancel_status','!=',2)
                            // ->where('is_split_order','=','0')
                            // ->where('retailer_id',$loggedIn_userId)
                            // ->whereBetween('created_at',array($from_date,$to_date))
                            // ->where($representative_leads.'.is_confirm','!=',4)
                            // ->where($representative_leads.'.is_confirm','!=',0)
                            // ->where($representative_leads.'.maker_id','!=',0)
                            // ->where('ship_status','=',0)
                            ->where('retailer_id',$loggedIn_userId)
                            ->whereBetween('created_at',array($from_date,$to_date))
                            ->where('order_cancel_status','!=',2)
                            ->where('is_payment_status','=','0')
                            ->where($representative_leads.'.is_confirm','!=',4)
                            ->where($representative_leads.'.is_confirm','!=',0)
                            ->where($representative_leads.'.maker_id','!=',0)
                            ->where('is_split_order','=','0')
                            ->where('ship_status','=',0)   
                            ->where(function($q) use($representative_leads){
                            return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                            ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                            ->orwhereNULL('payment_term');
                            })
                           
                            ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['pending_order'] = floatval($pending_count);                      

      //canceled order count
      $canceled_count = $this->RepresentativeLeadsModel
                             ->where('order_cancel_status','=',2)
                             ->where('retailer_id',$loggedIn_userId)
                             ->where('is_split_order','=','0')
                             ->whereBetween('created_at',array($from_date,$to_date))
                             //->count();
                             ->sum('total_wholesale_price');

      $order_data[$previous_month_name]['canceled_order'] = floatval($canceled_count);


      /*------------------last week retailer order report-----------------*/

        
      $retailer_orders_arr = [];

      $dates_arr  = $this->getLastWeekDates();

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
             
              $quotes_count_completed = $this->RetailerQuotesModel
                                             // ->where('created_at','LIKE','%'.$date.'%')
                                             // ->where('maker_confirmation',1)
                                             // ->where('ship_status','=',1)
                                             // ->where('is_split_order','=','0')
                                             // ->where('retailer_id',$loggedIn_userId)
                                            ->where('retailer_id',$loggedIn_userId)
                                            ->where('created_at','LIKE','%'.$date.'%') 
                                            ->where('is_payment_status','=','1')
                                            ->where('ship_status','=',1)
                                            ->where('is_split_order','=','0')
                                            ->where('maker_confirmation','=',1)
                                            ->where(function($q) use($prefixed_retailer_quotes_tbl){
                                            return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                                            ->where($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30 - Online/Credit');
                                            }) 
                                           
                                             ->sum('total_wholesale_price');

              $retailer_orders_arr[$key]['completed_order'] = floatval($quotes_count_completed);
            
            
              //pending order count
              $quotes_count_pending = $this->RetailerQuotesModel
                                           // ->where('created_at','LIKE','%'.$date.'%')
                                           // ->where('order_cancel_status','!=',2)
                                           // ->where('retailer_id',$loggedIn_userId)
                                           // ->where('maker_confirmation','=',NULL)
                                           // ->where('ship_status','=',0)
                                           // ->where('is_split_order','=','0')
                                            ->where('retailer_id',$loggedIn_userId)
                                            ->where('created_at','LIKE','%'.$date.'%')
                                            ->where('order_cancel_status','!=',2)
                                            ->where('is_payment_status','=','0')
                                            ->where('maker_confirmation','=',NULL)
                                            ->where('is_split_order','=','0')
                                            ->where('ship_status','=',0)   
                                            ->where(function($q) use($prefixed_retailer_quotes_tbl){
                                            return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                                            ->where('payment_term','!=','Net30 - Online/Credit')
                                            ->orwhereNULL('payment_term');
                                            })
                                           
                                            ->sum('total_wholesale_price');


              $retailer_orders_arr[$key]['pending_order'] = floatval($quotes_count_pending);                      

            
              // canceled order count
              $quotes_count_canceled = $this->RetailerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('is_split_order','=','0')
                                              ->where('retailer_id',$loggedIn_userId)
                                              //->count();
                                              ->sum('total_wholesale_price');

              $retailer_orders_arr[$key]['canceled_order'] = floatval($quotes_count_canceled); 
                  
          
          }
      }             
   

      /*---------------------last month retailer order report---------------------*/
    
      $retailer_order_data = [];
 
      $currentMonth         = date('F');
      $previous_month_name  = Date('F', strtotime($currentMonth . " last month"));

      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));

      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';


      //completed orders count
      $completed_count = $this->RetailerQuotesModel
                              // ->where('maker_confirmation',1)
                              // ->where('ship_status','=',1)
                              // ->where('is_split_order','=','0')
                              // ->where('retailer_id',$loggedIn_userId)
                              // ->whereBetween('created_at',array($from_date,$to_date))
                              ->where('retailer_id',$loggedIn_userId)
                              ->whereBetween('created_at',array($from_date,$to_date))
                              
                              ->where('is_payment_status','=','1')
                              ->where('ship_status','=',1)
                              ->where('is_split_order','=','0')
                              ->where('maker_confirmation','=',1)
                              ->where(function($q) use($prefixed_retailer_quotes_tbl){
                              return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                              ->where($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30 - Online/Credit');
                              }) 
                              ->sum('total_wholesale_price');

      $retailer_order_data[$previous_month_name]['completed_order']= floatval($completed_count);
                
      // pending order count
      $pending_count = $this->RetailerQuotesModel
                             // ->where('order_cancel_status','!=',2)
                             // ->where('retailer_id',$loggedIn_userId)
                             // ->where('maker_confirmation','=',NULL)
                             // ->where('is_split_order','=','0')
                             // ->where('ship_status','=',0)
                             // ->whereBetween('created_at',array($from_date,$to_date))
                              ->where('retailer_id',$loggedIn_userId)
                              ->whereBetween('created_at',array($from_date,$to_date))
                              ->where('order_cancel_status','!=',2)
                              ->where('is_payment_status','=','0')
                              ->where('maker_confirmation','=',NULL)
                              ->where('is_split_order','=','0')
                              ->where('ship_status','=',0)   
                              ->where(function($q) use($prefixed_retailer_quotes_tbl){
                              return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                              ->where('payment_term','!=','Net30 - Online/Credit')
                              ->orwhereNULL('payment_term');
                              })
                             ->sum('total_wholesale_price');


      $retailer_order_data[$previous_month_name]['pending_order'] = floatval($pending_count);                      

      //canceled order count
      $canceled_count = $this->RetailerQuotesModel
                             ->where('order_cancel_status','=',2)
                             ->where('retailer_id',$loggedIn_userId)
                             ->where('is_split_order','=','0')
                             ->whereBetween('created_at',array($from_date,$to_date))
                             //->count();
                             ->sum('total_wholesale_price');

      $retailer_order_data[$previous_month_name]['canceled_order'] = floatval($canceled_count);

    }

      /*---------------------------------------------------------------------------------*/

      //retailer quotes for pie chart
      $this->arr_view_data['sales_quotes_count_arr']  = $sales_quotes_count_arr;
      $this->arr_view_data['retailer_quotes_arr']     = $retailer_quotes_arr;

      //rep/sales leads for pie chart
      $this->arr_view_data['sales_leads_count_arr']   = $sales_leads_count_arr;
      $this->arr_view_data['lead_count_arr']          = $lead_count_arr;


      //last week and last month count for rep/sales leads
      // /dd($orders_arr);
      $this->arr_view_data['orders_arr']            = $orders_arr; 
      $this->arr_view_data['orders_data']           = $order_data; 


      //last week and last month count for retailer orders
      $this->arr_view_data['retailer_orders_arr']   = $retailer_orders_arr; 
      $this->arr_view_data['retailer_order_data']   = $retailer_order_data; 

      $this->arr_view_data['order_count_arr']       = $order_count_arr;  
      $this->arr_view_data['quotes_arr']            = $quotes_arr;  
      $this->arr_view_data['module_title']          = $this->module_title;
      $this->arr_view_data['previous_month_name']   = $previous_month_name;
      $this->arr_view_data['page_title']            = 'Dashboard';
      $this->arr_view_data['module_url_path']       = $this->module_url_path;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
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
    

    public function get_retailer_quote_count($retailer_id)
    {
        $quote_count = 0;
        if(isset($retailer_id) && $retailer_id!="" && $retailer_id!=0)
        {
          $quote_count = RetailerQuotesModel::where('retailer_id',$retailer_id)
                                              ->where('order_cancel_status','!=',2)
                                              ->where('is_split_order','=','0')
                                              ->count();
        }

        return $quote_count;
    }

    public function get_retailer_cancel_quote_count($retailer_id)
    {
        $quote_count = 0;
        if(isset($retailer_id) && $retailer_id!="" && $retailer_id!=0)
        {
          $quote_count = RetailerQuotesModel::where('retailer_id',$retailer_id)
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
                                                  ->where('is_split_order','=','0')
                                                  ->where('is_confirm','!=',0)
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
        $this->arr_view_data['module_url_path'] = url($this->retailer_panel_slug);
        
        return view($this->module_view_folder.'.change_password',$this->arr_view_data);    
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
            return redirect()->back(); 
        }
        else
        {
          Flash::error('Problem occurred,while changing password.');
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
      
     

      $obj_product_data =  $this->FavoriteModel
                                ->where('retailer_id',$user_id)
                                ->with(['productDetails'=>function($query) 
                                {
                                  $query->select('id','user_id','product_name','brand','product_image','product_image_thumb','unit_wholsale_price','retail_price','is_active','product_status','product_complete_status','description');

                                  $query->where('is_active',1);
                                  $query->where('product_status',1);
                                  $query->where('product_complete_status',4);

                                },'productDetails.brand_details'=>function($query1)
                                {
                                  $query1->select('id','user_id','brand_name','is_active');
                                  $query1->where('is_active',1);

                                }])
                                ->where('type','product')
                                ->paginate(8);

      if($obj_product_data)
      {
        $arr_product_pagination     = clone $obj_product_data;
        $arr_product_data           = $obj_product_data->toArray();
      } 
      


      $obj_maker_data = $this->FavoriteModel->where('retailer_id',$user_id)

                                            ->with(['makerDetails'=>function($query)
                                            {
                                                $query->select('id','user_id','company_name');
                                            },'store_image_details'=>function($query1){
                                                $query1->select('id','maker_id','store_profile_image');
                                            },'makerDetails.user_details'=>function($q1){
                                                $q1->where('status',1);
                                                $q1->where('is_approved',1);
                                            }

                                            ])
                                            ->where('type','maker')
                                            ->paginate(8);

      if($obj_maker_data)
      {
         $arr_maker_pagination  = clone $obj_maker_data;
         $arr_maker_data        = $obj_maker_data->toArray();
      }
      
      /*-----------------------------------------------------------------------------*/

      $get_favorite_data = $this->FavoriteModel->where('retailer_id',$user_id)->get()->toArray(); 
 
      $favorite_arr['product']  = $arr_product_data;
      $favorite_arr['maker']    = $arr_maker_data;


      $this->arr_view_data['arr_maker_pagination']     = $arr_maker_pagination;
      $this->arr_view_data['arr_product_pagination']   = $arr_product_pagination;
      $this->arr_view_data['favorite_arr']             = $favorite_arr;
      $this->arr_view_data['page_title']               = 'My Favorites';


      return view('retailer.favorites.my_favorite',$this->arr_view_data); 

  }

  public function get_rep_sales_tot_count($loggedIn_userId,$pending_flag=false,$completed_flag=false,$net_30_pending_count = false,$net_30_completed_count = false)
  {

     $user_table        =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $user_maker_table        =  $this->UserModel->getTable();
      $prefix_user_maker_table = DB::getTablePrefix().$user_maker_table;    

      $maker_table        = $this->MakerModel->getTable();
      $prefix_maker_table =  DB::getTablePrefix().$maker_table;     

      $role_table        =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table        =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $transaction_mapping        = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

      $lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_maker_table.'.company_name,'.

                              $prefix_transaction_mapping.'.order_id,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".


                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                                   )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')


                            /*->leftJoin($prefix_transaction_mapping,$prefix_transaction_mapping.'.order_id','=',$prefix_representative_leads_tbl.'.id')*/

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })


                           ->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           
                           ->where($representative_leads.'.is_confirm','!=',3)
                           ->where($representative_leads.'.is_confirm','!=',0)
                           ->where($representative_leads.'.maker_id','!=',0)
                           ->where($representative_leads.'.is_split_order','=','0')
                           ->orderBy($prefix_representative_leads_tbl.'.id','DESC');

                            if(isset($loggedIn_userId) && $loggedIn_userId!='')
                            {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.retailer_id','=',$loggedIn_userId);
                            }


                            if(isset($pending_flag) && $pending_flag!=0)
                            {
                               /*$lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=','0')                               
                                ->where($representative_leads.'.order_cancel_status','!=','2')
                                ->where(function($q){
                                  return $q->orwhere('is_payment_status','=','0')
                                           ->orwhere('is_payment_status','=','1');
                                })*/


                                $lead_obj = $lead_obj->where($representative_leads.'.order_cancel_status','!=','2');
                                $lead_obj = $lead_obj->where(function($q) use($representative_leads){
                                    return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                             ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                             ->orwhereNULL($representative_leads.'.payment_term');
                                  });

                                 $lead_obj = $lead_obj->where(function($q) use($prefix_representative_leads_tbl){
                                           $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','1')
                                            ->where($prefix_representative_leads_tbl.'.ship_status','=','0')
                                         ->orwhere(function($q) use($prefix_representative_leads_tbl){
                                             $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','0')
                                               ->where($prefix_representative_leads_tbl.'.ship_status','=','1');
                                          })
                                           ->orwhere(function($q) use($prefix_representative_leads_tbl){
                                            return $q->where($prefix_representative_leads_tbl.'.is_payment_status','=','0')
                                                     ->where($prefix_representative_leads_tbl.'.ship_status','=','0');
                                          });
                              }
                            );

                            }

                            if(isset($completed_flag) && $completed_flag!=0)
                            {
                                $lead_obj = $lead_obj->where($representative_leads.'.is_confirm','=',1)
                                                    ->where($representative_leads.'.order_cancel_status','!=','2')
                                                    ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                    ->where($representative_leads.'.ship_status','=',1)
                                                    ->where($representative_leads.'.maker_confirmation','=',1)
                                                    //->where($representative_leads.'.payment_term','!=','Net30');
                                                    ->where(function($q) use($representative_leads){
                                                      return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                               ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                                    });


                            }

                            if(isset($net_30_pending_count) && $net_30_pending_count!=0)
                            {
                               $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=',0)                               
                               ->where($representative_leads.'.order_cancel_status','!=',2)
                               //->where($representative_leads.'.payment_term','=','Net30');
                              ->where(function($q) use($representative_leads){
                              return $q->orwhere($representative_leads.'.payment_term','=','Net30')
                                   ->orwhere($representative_leads.'.payment_term','=','Net30 - Online/Credit');
                              });

                            }
                            if(isset($net_30_completed_count) && $net_30_completed_count!=0)
                            {
                              
                               $lead_obj = $lead_obj->where($representative_leads.'.is_confirm','=',1)
                                                    ->where($representative_leads.'.order_cancel_status','!=',2)
                                                    ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                    ->where($representative_leads.'.ship_status','=',1)
                                                    ->where($representative_leads.'.maker_confirmation','=',1)
                                                    //->where($representative_leads.'.payment_term','=','Net30');
                                                    ->where(function($q) use($representative_leads){
                                                      return $q->orwhere($representative_leads.'.payment_term','=','Net30')
                                                               ->orwhere($representative_leads.'.payment_term','=','Net30 - Online/Credit');
                                                    });
                            }
    // dd($lead_obj->toSql(),$lead_obj->getBindings());                       
     $lead_obj = $lead_obj->get()->toArray();                       
     return $leads_count = count($lead_obj);
    
  }

  public function get_rep_sales_cancel_count($loggedInUserId)
  {
       $leads_table          = $this->RepresentativeLeadsModel->getTable();        
        $prefixed_leads_table = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

        $user_tbl_name              = $this->UserModel->getTable();
        $prefixed_user_tbl      = DB::getTablePrefix().$this->UserModel->getTable();

        $retailer_table             = $this->RetailerModel->getTable();
        $prefix_retailer_table      = DB::getTablePrefix().$retailer_table;


        $maker_tbl                  = $this->MakerModel->getTable();        
        $prefixed_maker_tbl         = DB::getTablePrefix().$this->MakerModel->getTable();


        $obj_qutoes = DB::table($leads_table)
                        ->select(DB::raw($prefixed_leads_table.".*,".
                                        // $prefixed_transaction_mapping_tbl.".id as tid,".
                                        // $prefixed_transaction_mapping_tbl.".transaction_status,".
                                        $prefix_retailer_table.'.store_name,'.

                                        $prefixed_maker_tbl.".brand_name,".
                                        $prefixed_maker_tbl.".company_name,".

                                    "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                 .$prefixed_user_tbl.".last_name) as user_name"))

                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_leads_table.'.retailer_id')

                                ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_leads_table.'.retailer_id')

                                //->leftjoin($prefixed_transaction_mapping_tbl,$prefixed_leads_table.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')

                                ->leftjoin($prefixed_maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_leads_table.'.maker_id')


                                ->where($prefixed_leads_table.'.order_cancel_status','=',2)

                                ->where($prefixed_leads_table.'.retailer_id',$loggedInUserId)

                                ->where($prefixed_leads_table.'.is_split_order','=','0')

                                ->orderBy($prefixed_leads_table.".id",'DESC');
        
    $obj_qutoes = $obj_qutoes->where($prefixed_leads_table.'.retailer_id',$loggedInUserId);   
    
    $obj_qutoes = $obj_qutoes->get()->toArray();                       
    return $leads_count = count($obj_qutoes);
  }
}
