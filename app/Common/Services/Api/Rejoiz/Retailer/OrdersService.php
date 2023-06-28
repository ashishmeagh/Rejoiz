<?php
namespace App\Common\Services\Api\Rejoiz\Retailer;
   
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetailerQuotesModel;
use App\Models\FavoriteModel;
use App\Models\ProductsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\UserModel;
use App\Models\PromotionsModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\PromoCodeModel;
use App\Models\TransactionMappingModel;
use App\Models\PromotionsOffersModel;
use App\Models\TransactionsModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerModel;
use App\Models\AddressModel;
use App\Models\ProductInventoryModel;
use App\Models\PromoCodeRetailerMappingModel;
use App\Models\CountryModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\orderDataService;
use App\Common\Services\ProductService;
use App\Common\Services\HelperService;
use App\Common\Services\Api\Common\CommonService;
use App\Common\Services\CommissionService;
use App\Events\NotificationEvent;
use App\Models\SiteSettingModel;

   
use \paginate;
use Sentinel;
use Validator;
use DB;
use Datatables;
use Flash;
use Session;
use DateTime;

   
   class OrdersService {

      public function __construct(FavoriteModel $FavoriteModel,
                                    ProductsModel $ProductsModel,
                                    RetailerModel $RetailerModel,
                                    PromotionsModel $PromotionsModel,
                                    RepresentativeLeadsModel $RepresentativeLeadsModel,
                                    UserModel $UserModel,
                                    RetailerQuotesModel $RetailerQuotesModel,
                                    TransactionMappingModel $TransactionMappingModel,
                                    TransactionsModel $TransactionsModel,
                                    PromotionsOffersModel $PromotionsOffersModel,
                                    MakerModel $MakerModel,
                                    RoleModel $RoleModel,
                                    AddressModel $AddressModel,
                                    PromoCodeModel $PromoCodeModel,
                                    RoleUsersModel $RoleUsersModel,
                                    RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                    RetailerQuotesProductModel $RetailerQuotesProductModel,
                                    PromoCodeRetailerMappingModel $PromoCodeRetailerMappingModel,
                                    ProductService $ProductService,
                                    HelperService $HelperService,
                                    GeneralService $GeneralService,
                                    CommonService $CommonService,
                                    SiteSettingModel $SiteSettingModel,
                                    orderDataService $orderDataService,
                                    RetailerRepresentativeMappingModel $RetailerRepresentativeMappingModel,
                                    CommissionService $CommissionService 
                                   )
        {
          $this->arr_view_data         = [];
          $this->module_title          = "Dashboard";
          $this->module_view_folder    = 'retailer.dashboard';
          $this->FavoriteModel         = $FavoriteModel;
          $this->RetailerQuotesModel   = $RetailerQuotesModel;
          $this->ProductsModel         = $ProductsModel;
          $this->PromotionsModel       = $PromotionsModel;
          $this->PromoCodeRetailerMappingModel       = $PromoCodeRetailerMappingModel;
          $this->TransactionMappingModel = $TransactionMappingModel;
          $this->PromotionsOffersModel = $PromotionsOffersModel;
          $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
          $this->TransactionsModel     = $TransactionsModel;
          $this->UserModel             = $UserModel;
          $this->PromoCodeModel        = $PromoCodeModel;
          $this->MakerModel            = $MakerModel;
          $this->SiteSettingModel      = $SiteSettingModel;
          $this->ProductService        = $ProductService;
          $this->HelperService         = $HelperService;
          $this->GeneralService         = $GeneralService;
          $this->CommonService         = $CommonService;
          $this->RoleModel             = $RoleModel;
          $this->RoleUsersModel        = $RoleUsersModel;
          $this->RetailerModel         = $RetailerModel; 
          $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
          $this->RetailerRepresentativeMappingModel = $RetailerRepresentativeMappingModel;
          $this->RetailerQuotesProductModel   = $RetailerQuotesProductModel;
          $this->AddressModel                 = $AddressModel;
          $this->orderDataService             = $orderDataService;
          $this->CommissionService            = $CommissionService;
          $this->admin_user_id                = get_admin_id();
        }


       public function get_order_counts($user_id=null,$duration=null,$chart_type=null) 
       {
          try
          {
               /*-------get prnding,completed,canceled,total,confirmed order count----*/

              /*--------------get rep/sales order count----------------------------*/
           
              $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
              $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

              $transaction_mapping             = $this->TransactionMappingModel->getTable();
              $prefix_transaction_mapping      = DB::getTablePrefix().$transaction_mapping;

              $maker_table                     = $this->MakerModel->getTable();
              $prefix_maker_table              = DB::getTablePrefix().$maker_table; 

              $user_table                      =  $this->UserModel->getTable();
              $prefix_user_table               = DB::getTablePrefix().$user_table;

              $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
              $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

               /*Order by rep/sales total count*/                    
               $total_count     = $this->get_rep_sales_tot_count($user_id);
               $pending_count   = $this->get_rep_sales_tot_count($user_id,1,false);  
               $completed_count = $this->get_rep_sales_tot_count($user_id,false,1);    
               $net_30_pending_count = $this->get_rep_sales_tot_count($user_id,false,false,1);  
               $net_30_completed_count = $this->get_rep_sales_tot_count($user_id,false,false,false,1);            
               $canceled_count  = $this->get_rep_sales_cancel_count($user_id); 
                                    
               $arr_data['rep_sales_orders'] = $arr_data['my_orders'] = [];

              
   
               $arr_data['rep_sales_orders'][0]['order_status']  = 'all'; 
               $arr_data['rep_sales_orders'][0]['order_count']   = $total_count;                                  

   
               $arr_data['rep_sales_orders'][1]['order_status'] = 'pending'; 
               $arr_data['rep_sales_orders'][1]['order_count']  = $pending_count;   

               $arr_data['rep_sales_orders'][2]['order_status'] = 'completed'; 
               $arr_data['rep_sales_orders'][2]['order_count']  = $completed_count;  

               $arr_data['rep_sales_orders'][3]['order_status'] = 'cancelled'; 
               $arr_data['rep_sales_orders'][3]['order_count']  = $canceled_count;   

               $arr_data['rep_sales_orders'][4]['order_status'] = 'net_30_completed'; 
               $arr_data['rep_sales_orders'][4]['order_count']  = $net_30_completed_count;  

               $arr_data['rep_sales_orders'][5]['order_status'] = 'net_30_pending'; 
               $arr_data['rep_sales_orders'][5]['order_count']  = $net_30_pending_count;    



               /*----------get my order count --------------------------------------*/


                $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
                $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

                $transaction_mapping_table          = $this->TransactionMappingModel->getTable();
                $prefixed_transaction_mapping_tbl   = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

                $transaction_tbl                    = $this->TransactionsModel->getTable();        
                $prefixed_transaction_tbl           = DB::getTablePrefix().$this->TransactionsModel->getTable();


                  $my_total_count = $this->RetailerQuotesModel
                                     ->where('retailer_id',$user_id)
                                     ->where('is_split_order','=','0')
                                     ->count();


                  $my_pending_count = $this->RetailerQuotesModel
                                        ->where('order_cancel_status','!=',2)
                                        ->where('retailer_id',$user_id)
                                        ->where('is_split_order','=','0')
                                        ->where('ship_status','=',0)
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
                                        })
                                        ->count();   
                
                  //pending orders      
                   $my_completed_count = DB::table($retailer_quotes_tbl_name)
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
                                      ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$user_id)
                                      ->where(function($q) use($prefixed_retailer_quotes_tbl){
                                          return $q->orwhere($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30')
                                                   ->where($prefixed_retailer_quotes_tbl.'.payment_term','!=','Net30 - Online/Credit');
                                        })
                                      ->count();

                  $my_canceled_count = $this->RetailerQuotesModel
                                         ->where('order_cancel_status','=',2)
                                         ->where('retailer_id',$user_id)
                                         ->where('is_split_order','=','0')
                                         ->count();   

                  // For Net30
                  $my_net_30_pending_count = $this->RetailerQuotesModel
                                ->where('order_cancel_status','!=',2)
                                ->where('retailer_id',$user_id)
                                ->where('is_split_order','=','0')
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
                              ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$user_id)
                              ->where(function($q){
                                      return $q->orwhere('payment_term','=','Net30')
                                               ->orwhere('payment_term','=','Net30 - Online/Credit');
                                    })
                              ->count();  


                   $arr_data['my_orders'][0]['order_status'] = 'all'; 
                   $arr_data['my_orders'][0]['order_count']  = $my_total_count;                                  

       
                   $arr_data['my_orders'][1]['order_status'] = 'pending'; 
                   $arr_data['my_orders'][1]['order_count']  = $my_pending_count;   

                   $arr_data['my_orders'][2]['order_status'] = 'completed'; 
                   $arr_data['my_orders'][2]['order_count']  = $my_completed_count;  

                   $arr_data['my_orders'][3]['order_status'] = 'cancelled'; 
                   $arr_data['my_orders'][3]['order_count']  = $my_canceled_count;   

                   $arr_data['my_orders'][4]['order_status'] = 'net_30_completed'; 
                   $arr_data['my_orders'][4]['order_count']  = $my_net_30_completed_count;  

                   $arr_data['my_orders'][5]['order_status'] = 'net_30_pending'; 
                   $arr_data['my_orders'][5]['order_count']  = $my_net_30_pending_count;    



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
                                                      ->where('retailer_id',$user_id)
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
                                                    ->where('retailer_id',$user_id)
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
                                                      ->where('retailer_id',$user_id)
                                                      ->count();

                       $cancel_count+= $canceled_quotes_count; 

                }

                $arr_data['pie_chart']['my_orders_week'][0]['label']            = 'Completed';  
                $arr_data['pie_chart']['my_orders_week'][0]['data']            = $complete_count; 
                $arr_data['pie_chart']['my_orders_week'][0]['backgroundColor'] = 'rgb(255, 182, 193)';



                $arr_data['pie_chart']['my_orders_week'][1]['label']            = 'Pending';   
                $arr_data['pie_chart']['my_orders_week'][1]['data']             = $pendings_count;   
                $arr_data['pie_chart']['my_orders_week'][1]['backgroundColor']  = 'rgb(177, 156, 217)';


                $arr_data['pie_chart']['my_orders_week'][2]['label']            = 'Cancelled';    
                $arr_data['pie_chart']['my_orders_week'][2]['data']             = $cancel_count; 
                $arr_data['pie_chart']['my_orders_week'][2]['backgroundColor']  = 'rgb(236, 151, 135)';

               

               } 


          /*--------------------new code for last 30 days retailer quotes---------------------*/

           $last_thirty_days_arr = $this->lastThirtyDays();

         
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
                                                  ->where('retailer_id',$user_id)
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
                                                  ->where('retailer_id',$user_id)    
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
                                                  ->where('retailer_id',$user_id)
                                                  ->count();

                    $canceled_retailer_quotes_count+= $quotes_count_canceled; 
              
                }

                 $arr_data['pie_chart']['my_orders_month'][0]['label']            = 'Completed';  
                 $arr_data['pie_chart']['my_orders_month'][0]['data']             = $complete_retailer_quotes_count; 
                 $arr_data['pie_chart']['my_orders_month'][0]['backgroundColor']  = 'rgb(255, 182, 193)';



                 $arr_data['pie_chart']['my_orders_month'][1]['label']            = 'Pending';   
                 $arr_data['pie_chart']['my_orders_month'][1]['data']             = $pending_retailer_quotes_count;   
                 $arr_data['pie_chart']['my_orders_month'][1]['backgroundColor']  = 'rgb(177, 156, 217)';


                 $arr_data['pie_chart']['my_orders_month'][2]['label']            = 'Cancelled';    
                 $arr_data['pie_chart']['my_orders_month'][2]['data']             = $canceled_retailer_quotes_count; 
                 $arr_data['pie_chart']['my_orders_month'][2]['backgroundColor']  = 'rgb(236, 151, 135)';

            } 


           /*------------------new code for last 7 days for rep/sales leads-----*/


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
                                            ->where('retailer_id',$user_id)
                                            ->where(function($q) use($representative_leads){
                                            return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                            ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                            })
                                            ->count();


                     $leads_complete_count += $compl_count;    
                    
                    //pending order count
                    $pend_count = $this->RepresentativeLeadsModel
                                        ->where('retailer_id',$user_id)
                                        ->whereDate('created_at','LIKE','%'.$date.'%')
                                        ->where('order_cancel_status','!=',2)
                                        ->where($representative_leads.'.is_confirm','!=',4)
                                        ->where($representative_leads.'.is_confirm','!=',0)
                                        ->where($representative_leads.'.maker_id','!=',0)
                                        ->where('ship_status','=','0')
                                        ->where('is_payment_status','=','0')
                                        ->where('is_split_order','=','0')
                                         ->where(function($q) use($representative_leads){
                                                    return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                             ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                                             ->orwhereNULL('payment_term');
                                                  })
                                        ->count();


                      $leads_pendings_count += $pend_count;    
                    //canceled order count
                    $cancel_count = $this->RepresentativeLeadsModel
                                         ->where('created_at','LIKE','%'.$date.'%')
                                          ->where('order_cancel_status','=',2)
                                          ->where('is_split_order','=','0')

                                          ->where('retailer_id',$user_id)
                                          ->count();

                    $leads_cacels_count+= $cancel_count;    
              
                }

                 $arr_data['pie_chart']['rep_sales_orders_week'][0]['label']            = 'Completed';  
                 $arr_data['pie_chart']['rep_sales_orders_week'][0]['data']             = $leads_complete_count; 
                 $arr_data['pie_chart']['rep_sales_orders_week'][0]['backgroundColor']  = 'rgb(255, 182, 193)';


                 $arr_data['pie_chart']['rep_sales_orders_week'][1]['label']            = 'Pending';   
                 $arr_data['pie_chart']['rep_sales_orders_week'][1]['data']             = $leads_pendings_count;   
                 $arr_data['pie_chart']['rep_sales_orders_week'][1]['backgroundColor']  = 'rgb(177, 156, 217)';


                 $arr_data['pie_chart']['rep_sales_orders_week'][2]['label']            = 'Cancelled';    
                 $arr_data['pie_chart']['rep_sales_orders_week'][2]['data']             = $leads_cacels_count; 
                 $arr_data['pie_chart']['rep_sales_orders_week'][2]['backgroundColor']  = 'rgb(236, 151, 135)';

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
                                        ->where('retailer_id',$user_id)
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
                                             ->where('retailer_id',$user_id)
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
                                            ->count(); 

                  $pending_leads_count += $pend_count;  

               
                  

               // canceled order count
                $cancel_count = $this->RepresentativeLeadsModel
                                      ->where('created_at','LIKE','%'.$date.'%')
                                      ->where('order_cancel_status','=',2)
                                      ->where('is_split_order','=','0')
                                      ->where('retailer_id',$user_id)
                                      ->count();

                $canceled_leads_count+= $cancel_count;    
          
            }

             $arr_data['pie_chart']['rep_sales_orders_month'][0]['label']            = 'Completed';  
             $arr_data['pie_chart']['rep_sales_orders_month'][0]['data']             = $completed_leads_count; 
             $arr_data['pie_chart']['rep_sales_orders_month'][0]['backgroundColor']  = 'rgb(255, 182, 193)';


             $arr_data['pie_chart']['rep_sales_orders_month'][1]['label']            = 'Pending';   
             $arr_data['pie_chart']['rep_sales_orders_month'][1]['data']             = $pending_leads_count;   
             $arr_data['pie_chart']['rep_sales_orders_month'][1]['backgroundColor']  = 'rgb(177, 156, 217)';


             $arr_data['pie_chart']['rep_sales_orders_month'][2]['label']            = 'Cancelled';    
             $arr_data['pie_chart']['rep_sales_orders_month'][2]['data']             = $cancel_count; 
             $arr_data['pie_chart']['rep_sales_orders_month'][2]['backgroundColor']  = 'rgb(236, 151, 135)';



     /*-----------------last week rep/sales leads count count-----------------*/ 
   
      $orders_arr = $completed_order_arr = $pending_orders_arr = $canceled_orders_arr = [];

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
                                            ->where('retailer_id',$user_id)
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

                  $completed_order_arr[] = intval($completed_order_count);                            


                   $arr_data['bar_chart']['rep_sales_orders_week'][0]['label']                 = 'Completed';
                   $arr_data['bar_chart']['rep_sales_orders_week'][0]['data']                  = $completed_order_arr;
                   $arr_data['bar_chart']['rep_sales_orders_week'][0]['backgroundColor']       = 'rgb(255, 182, 193)';
                   $arr_data['bar_chart']['rep_sales_orders_week'][0]['borderColor']           = 'rgb(38, 194, 129)';
                   $arr_data['bar_chart']['rep_sales_orders_week'][0]['borderWidth']           = 1;


                
                //pending order count
                $pending_order_count = $this->RepresentativeLeadsModel
                                            ->where('retailer_id',$user_id)
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


                $pending_orders_arr[] = intval($pending_order_count);                                   


                   $arr_data['bar_chart']['rep_sales_orders_week'][1]['label']                 = 'Pending';
                   $arr_data['bar_chart']['rep_sales_orders_week'][1]['data']                  = $pending_orders_arr;
                   $arr_data['bar_chart']['rep_sales_orders_week'][1]['backgroundColor']       = 'rgb(177, 156, 217)';
                   $arr_data['bar_chart']['rep_sales_orders_week'][1]['borderColor']           = 'rgb(38, 194, 129)';
                   $arr_data['bar_chart']['rep_sales_orders_week'][1]['borderWidth']           = 1;                  

                //  canceled order count
                $canceled_order_count = $this->RepresentativeLeadsModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('is_split_order','=','0')
                                              ->where('retailer_id',$user_id)
                                             // ->count();
                                              ->sum('total_wholesale_price');


               $canceled_orders_arr[] = intval($canceled_order_count);                                    


                   $arr_data['bar_chart']['rep_sales_orders_week'][2]['label']                 = 'Cancelled';
                   $arr_data['bar_chart']['rep_sales_orders_week'][2]['data']                  = $canceled_orders_arr;
                   $arr_data['bar_chart']['rep_sales_orders_week'][2]['backgroundColor']       = 'rgb(236, 151, 135)';
                   $arr_data['bar_chart']['rep_sales_orders_week'][2]['borderColor']           = 'rgb(38, 194, 129)';
                   $arr_data['bar_chart']['rep_sales_orders_week'][2]['borderWidth']           = 1;                                        
          
             }
          }   

      } 
      /*----------------------------------------------------------------------------*/


      /*-------------get last month order  count for rep/sales orders------------------*/
      $order_data = [];
 
      $currentMonth         = date('F');

      $previous_month_name  = Date('M', strtotime($currentMonth . " last month"));


      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));


      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';

      $month_arr    = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July ', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');

      $rep_sales_last_month_comp_orders_arr = $rep_sales_last_month_pending_orders_arr = $rep_sales_last_month_cancelled_orders_arr = [];

      foreach($month_arr as $month)
      {  
                //completed orders count
                $completed_count = $this->RepresentativeLeadsModel
                                        ->where('retailer_id',$user_id)
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


                   if($previous_month_name!=$month)
                   {  
                     $rep_sales_last_month_comp_orders_arr[] = 0;
                   } 

                   else
                   {
                     $rep_sales_last_month_comp_orders_arr[] = floatval($completed_count);
                   }
                          
               // pending order count
                $pending_count = $this->RepresentativeLeadsModel
                                      ->where('retailer_id',$user_id)
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

                   if($previous_month_name!=$month)
                   {  
                     $rep_sales_last_month_pending_orders_arr[] = 0;
                   } 

                   else
                   {
                     $rep_sales_last_month_pending_orders_arr[] = floatval($pending_count);
                   }                    

                //canceled order count
                $canceled_count = $this->RepresentativeLeadsModel
                                       ->where('order_cancel_status','=',2)
                                       ->where('retailer_id',$user_id)
                                       ->where('is_split_order','=','0')
                                       ->whereBetween('created_at',array($from_date,$to_date))
                                       ->sum('total_wholesale_price');


                 if($previous_month_name!=$month)
                 {  
                   $rep_sales_last_month_cancelled_orders_arr[] = 0;
                 } 

                 else
                 {
                   $rep_sales_last_month_cancelled_orders_arr[] = floatval($canceled_count);
                 }  


                     $arr_data['bar_chart']['rep_sales_orders_month'][0]['label']            = 'Completed';
                     $arr_data['bar_chart']['rep_sales_orders_month'][0]['data']             =  $rep_sales_last_month_comp_orders_arr;
                     $arr_data['bar_chart']['rep_sales_orders_month'][0]['backgroundColor']  = 'rgb(255, 182, 193)';
                     $arr_data['bar_chart']['rep_sales_orders_month'][0]['borderColor']      = 'rgb(38, 194, 129)';
                     $arr_data['bar_chart']['rep_sales_orders_month'][0]['borderWidth']      = 1;     


                    $arr_data['bar_chart']['rep_sales_orders_month'][1]['label']            = 'Pending';   
                    $arr_data['bar_chart']['rep_sales_orders_month'][1]['data']             = $rep_sales_last_month_pending_orders_arr;
                    $arr_data['bar_chart']['rep_sales_orders_month'][1]['backgroundColor']  = 'rgb(177, 156, 217)';
                    $arr_data['bar_chart']['rep_sales_orders_month'][1]['borderColor']      = 'rgb(38, 194, 129)';
                    $arr_data['bar_chart']['rep_sales_orders_month'][1]['borderWidth']      = 1;                    


                   $arr_data['bar_chart']['rep_sales_orders_month'][2]['label']            = 'Cancelled';   
                   $arr_data['bar_chart']['rep_sales_orders_month'][2]['data']             =  $rep_sales_last_month_cancelled_orders_arr;
                   $arr_data['bar_chart']['rep_sales_orders_month'][2]['backgroundColor']  = 'rgb(236, 151, 135)';
                   $arr_data['bar_chart']['rep_sales_orders_month'][2]['borderColor']      = 'rgb(38, 194, 129)';
                   $arr_data['bar_chart']['rep_sales_orders_month'][2]['borderWidth']      = 1;      

    }  



      /*------------------last week retailer order report-----------------*/

        
      $retailer_orders_arr = $retailer_comp_order_arr = $retailer_pending_order_arr = $retailer_comp_order_arr = [];

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
                                            ->where('retailer_id',$user_id)
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

             $retailer_comp_order_arr[] =  floatval($quotes_count_completed);                               


               $arr_data['bar_chart']['my_orders_week'][0]['label']                 = 'Completed';
               $arr_data['bar_chart']['my_orders_week'][0]['data']                  = $retailer_comp_order_arr;
               $arr_data['bar_chart']['my_orders_week'][0]['backgroundColor']       = 'rgb(255, 182, 193)';
               $arr_data['bar_chart']['my_orders_week'][0]['borderColor']           = 'rgb(38, 194, 129)';
               $arr_data['bar_chart']['my_orders_week'][0]['borderWidth']           = 1;

            
            
              //pending order count
              $quotes_count_pending = $this->RetailerQuotesModel
                                            ->where('retailer_id',$user_id)
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

             $retailer_pending_order_arr[] = floatval($quotes_count_pending);                               



               $arr_data['bar_chart']['my_orders_week'][1]['label']                 = 'Pending';
               $arr_data['bar_chart']['my_orders_week'][1]['data']                  = $retailer_pending_order_arr;
               $arr_data['bar_chart']['my_orders_week'][1]['backgroundColor']       = 'rgb(255, 182, 193)';
               $arr_data['bar_chart']['my_orders_week'][1]['borderColor']           = 'rgb(38, 194, 129)';
               $arr_data['bar_chart']['my_orders_week'][1]['borderWidth']           = 1;                   

            
              // canceled order count
              $quotes_count_canceled = $this->RetailerQuotesModel
                                              ->where('created_at','LIKE','%'.$date.'%')
                                              ->where('order_cancel_status','=',2)
                                              ->where('is_split_order','=','0')
                                              ->where('retailer_id',$user_id)
                                              ->sum('total_wholesale_price');

              $retailer_cancelled_order_arr[] =   floatval($quotes_count_canceled);                              


               $arr_data['bar_chart']['my_orders_week'][2]['label']                 = 'Cancelled';
               $arr_data['bar_chart']['my_orders_week'][2]['data']                  =  $retailer_cancelled_order_arr;
               $arr_data['bar_chart']['my_orders_week'][2]['backgroundColor']       = 'rgb(255, 182, 193)';
               $arr_data['bar_chart']['my_orders_week'][2]['borderColor']           = 'rgb(38, 194, 129)';
               $arr_data['bar_chart']['my_orders_week'][2]['borderWidth']           = 1;  
       }        
    }

      /*---------------------last month retailer order report---------------------*/
    
      $retailer_order_data = [];
 
      $currentMonth         = date('F');
      $previous_month_name  = Date('M', strtotime($currentMonth . " last month"));


      $first_date_month = date("Y-m-d", strtotime("first day of previous month"));
      $last_date_month  = date("Y-m-d", strtotime("last day of previous month"));

      $from_date = $first_date_month.' 00:00:00';
      $to_date   = $last_date_month.' 23:59:59';

      $month_arr    = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July ', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');

      $orders_arr  = $pending_orders_arr = $canceled_orders_arr = $completed_order_cnt_arr = $pending_order_cnt_arr =  $canceled_order_cnt_arr = $pending_monthly_order_cnt_arr = $completed_monthly_order_cnt_arr = $canceled_monthly_order_cnt_arr  = [];


      foreach($month_arr as $month)
     { 

      //completed orders count
      $completed_count = $this->RetailerQuotesModel
                              ->where('retailer_id',$user_id)
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



        if($previous_month_name!=$month)
         {  
           $completed_monthly_order_cnt_arr[] = 0;
         } 

         else
         {
           $completed_monthly_order_cnt_arr[] = floatval($completed_count);
         }

           $arr_data['bar_chart']['my_orders_month'][0]['label']            = 'Completed';
           $arr_data['bar_chart']['my_orders_month'][0]['data']             =  $completed_monthly_order_cnt_arr;
           $arr_data['bar_chart']['my_orders_month'][0]['backgroundColor']  = 'rgb(255, 182, 193)';
           $arr_data['bar_chart']['my_orders_month'][0]['borderColor']      = 'rgb(38, 194, 129)';
           $arr_data['bar_chart']['my_orders_month'][0]['borderWidth']      = 1;     
                
      // pending order count
      $pending_count = $this->RetailerQuotesModel
                              ->where('retailer_id',$user_id)
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


      if($previous_month_name!=$month)
       {  
         $pending_monthly_order_cnt_arr[] = 0;
       } 

       else
       { 
         $pending_monthly_order_cnt_arr[] = floatval($pending_count); 
       }       


          $arr_data['bar_chart']['my_orders_month'][1]['label']            = 'Pending';   
          $arr_data['bar_chart']['my_orders_month'][1]['data']             = $pending_monthly_order_cnt_arr;
          $arr_data['bar_chart']['my_orders_month'][1]['backgroundColor']  = 'rgb(177, 156, 217)';
          $arr_data['bar_chart']['my_orders_month'][1]['borderColor']      = 'rgb(38, 194, 129)';
          $arr_data['bar_chart']['my_orders_month'][1]['borderWidth']      = 1;                                 

      //canceled order count
      $canceled_count = $this->RetailerQuotesModel
                             ->where('order_cancel_status','=',2)
                             ->where('retailer_id',$user_id)
                             ->where('is_split_order','=','0')
                             ->whereBetween('created_at',array($from_date,$to_date))
                             ->sum('total_wholesale_price');

          if($previous_month_name!=$month)
         {  
           $canceled_monthly_order_cnt_arr[] = 0;
         } 

         else
         { 
           $canceled_monthly_order_cnt_arr[] = floatval($canceled_count); 
         }  
                  

       $arr_data['bar_chart']['my_orders_month'][2]['label']            = 'Cancelled';   
       $arr_data['bar_chart']['my_orders_month'][2]['data']             =  $canceled_monthly_order_cnt_arr;
       $arr_data['bar_chart']['my_orders_month'][2]['backgroundColor']  = 'rgb(236, 151, 135)';
       $arr_data['bar_chart']['my_orders_month'][2]['borderColor']      = 'rgb(38, 194, 129)';
       $arr_data['bar_chart']['my_orders_month'][2]['borderWidth']      = 1;      

    }
                  
      $response = [
                       'status'  => 'success',
                       'message' => 'Order counts get successfully',
                       'data'    => isset($arr_data)?$arr_data:[]
                        ];
   
      return $response;     


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



    public function my_orders($user_id=null,$form_data=[])
    {

        try
        {

          $per_page   = isset($form_data['per_page'])?$form_data['per_page']:'';
          $order_type = isset($form_data['order_type'])?$form_data['order_type']:''; 

          $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
          $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

          $transaction_mapping_table = $this->TransactionMappingModel->getTable();
          $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

          $user_tbl_name                = $this->UserModel->getTable();
          $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

          $maker_tbl                    = $this->MakerModel->getTable();        
          $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();
 
          $transaction_tbl              = $this->TransactionsModel->getTable();        
          $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable();

          $retailer_product_tbl = $this->RetailerQuotesProductModel->getTable();

          $prefixed_retailer_product_tbl = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();



        if(isset($order_type) && !empty($order_type) && $order_type=='cancelled')
      {   

       $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                         $prefixed_retailer_product_tbl.".shipping_charge,".
                                         $prefixed_retailer_product_tbl.".shipping_discount,".
                                      
                                        "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_retailer_product_tbl, $prefixed_retailer_product_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')
                        
                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                            ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                        })

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_tbl,$transaction_tbl.'.transaction_id','=',$prefixed_retailer_quotes_tbl.'.transaction_id')

                        ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$user_id)
                        ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','=',2)

                        ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC')
                        ->groupBy($prefixed_retailer_quotes_tbl.".id");
                     
         }               

        else
        {  

/*                $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
                $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

                $transaction_mapping_table = $this->TransactionMappingModel->getTable();
                $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

                $user_tbl_name                = $this->UserModel->getTable();
                $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

                $maker_tbl                    = $this->MakerModel->getTable();        
                $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();

                $transaction_tbl              = $this->TransactionsModel->getTable();        
                $prefixed_transaction_tbl     = DB::getTablePrefix().$this->TransactionsModel->getTable(); */

               
        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                         $prefixed_maker_tbl.".company_name,".
                                         
                                         $prefixed_transaction_mapping_tbl.".id as tid,".
                                         $prefixed_transaction_mapping_tbl.".transaction_status,".
                                         $prefixed_retailer_product_tbl.".shipping_charge,".
                                         $prefixed_retailer_product_tbl.".shipping_discount,".
                                      
                                        "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))

                        ->leftjoin($prefixed_retailer_product_tbl, $prefixed_retailer_product_tbl.'.retailer_quotes_id','=',$prefixed_retailer_quotes_tbl.'.id')
                        
                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_retailer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                            $join->on($prefixed_retailer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                            ->on($prefixed_retailer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                        })

                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')

                        ->leftjoin($prefixed_transaction_tbl,$transaction_tbl.'.transaction_id','=',$prefixed_retailer_quotes_tbl.'.transaction_id')

                        ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$user_id)
                        // ->where($prefixed_retailer_quotes_tbl.'.order_cancel_status','!=',2)

                        ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC')
                        ->groupBy($prefixed_retailer_quotes_tbl.".id");



                 if(isset($order_type) && $order_type=='completed')
                 {
                    $obj_qutoes = $obj_qutoes->where('maker_confirmation','=',1)
                                             ->where('ship_status','=',1)
                                             ->where('order_cancel_status','!=',2)
                                             ->where('is_split_order','=','0')

                                             // ->where($prefixed_transaction_mapping_tbl.'.transaction_status','=',2)
                                             ->where($retailer_quotes_tbl_name.'.is_payment_status','=',1)
                                             ->where(function($q){
                                                  return $q->orwhere('payment_term','!=','Net30')
                                                           ->where('payment_term','!=','Net30 - Online/Credit');
                                                });             
                 }                  

                 if(isset($order_type) && $order_type=='pending')
                 {
                  
                    $form_data['filter']['ship_status'] = "0";


                      $obj_qutoes = $obj_qutoes->where('order_cancel_status','!=',2)
                                                ->where('retailer_id',$user_id)
                                                ->where('is_split_order','=','0')
                                                
                                                // ->where('ship_status','=',0)
                                                
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


                                                );
                                               // ->get()->toArray();

                                              // $obj_qutoes;

                 }

                 if(isset($order_type) && $order_type=='net_30_pending')
                 {
                   
                    //$arr_search_column['q_ship_status'] = "0";
                        $obj_qutoes = $obj_qutoes->where('is_split_order','=','0')

                                                ->where(function($q){
                                                   $q->where('is_payment_status','=','1')
                                                    ->where('ship_status','=','0')
                                                 
                                                 ->orwhere(function($q){
                                                     $q->where('is_payment_status','=','0')
                                                             ->where('ship_status','=','1');
                                                  })


                                                   ->orwhere(function($q){
                                                    $q->where('is_payment_status','=','0')
                                                             ->where('ship_status','=','0');
                                                  });
                                                }
                                                );

                        
                        $obj_qutoes = $obj_qutoes->where(function($q){
                                              return $q->orwhere('payment_term','=','Net30')
                                                       ->orwhere('payment_term','=','Net30 - Online/Credit');
                                            });  

                        
                 }

                if(isset($order_type) && $order_type=='net_30_completed')
                {
                  $obj_qutoes = $obj_qutoes->where('maker_confirmation','=',1)
                                         ->where('ship_status','=',1)
                                         // ->where($prefixed_transaction_mapping_tbl.'.transaction_status','=',2)
                                         ->where($retailer_quotes_tbl_name.'.is_payment_status','=',1)
                                         ->where(function($q){
                                              return $q->orwhere('payment_term','=','Net30')
                                                       ->orwhere('payment_term','=','Net30 - Online/Credit');
                                            });       
                }

      }  


         /*---------------------------------Filtering Search Start Here-------------------------------------------*/

          if(isset($form_data['filter']['general_search']) && $form_data['filter']['general_search']!="")
          {
              $search_term      = $form_data['filter']['general_search'];

              $obj_qutoes         = $obj_qutoes->whereRaw(
                                  "(  `".$prefixed_retailer_quotes_tbl."`.`order_no` LIKE '%".$search_term."%' OR
                                      `".$prefixed_maker_tbl."`.`company_name` LIKE '%".$search_term."%' OR
                                      `".$prefixed_retailer_quotes_tbl."`.`total_wholesale_price` LIKE '%".$search_term."%' OR
                                      `total_wholesale_price` LIKE '%".$search_term."%' OR 
                                      `".$prefixed_retailer_quotes_tbl."`.`total_retail_price` LIKE '%".$search_term."%' OR
                                      `total_retail_price` LIKE '%".$search_term."%'
                                    )"
                                 );
          }                                          


        if(isset($form_data['filter']['price']) && $form_data['filter']['price']!="")
        {
            $search_term = $form_data['filter']['price'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($form_data['filter']['shipping_status']) && $form_data['filter']['shipping_status']!="")
        {
            $search_term = $form_data['filter']['shipping_status'];
           
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }


        if(isset($form_data['filter']['payment_status']) && $form_data['filter']['payment_status']!="")
        {  

            $search_term = $form_data['filter']['payment_status'];
            
            if($search_term == 1)
            {            

                $obj_qutoes = $obj_qutoes->where('is_payment_status','1');

               /* $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = retailer_transaction.order_no AND `transaction_mapping`.`order_id` = retailer_transaction.id
                        "));
 
                    });*/
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }

        }


         /*search data from  from date and to date*/
        if(isset($form_data['filter']['date_from']) && $form_data['filter']['date_from']!="" && isset($form_data['filter']['date_to']) && $form_data['filter']['date_to']!="")
        {
            $search_term_from_date  = $form_data['filter']['date_from'];
            $search_term_to_date    = $form_data['filter']['date_to'];

            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

     
            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_retailer_quotes_tbl.'.created_at', '>=', $from_date);

        } 

        /*----------------------------------Filtering Search End Here--------------------------------------------------------------*/

        $total_amt = 0;      
        $total_amt =array_reduce($obj_qutoes->get()->toArray(), function(&$res, $item) {
            return $res + $item->total_wholesale_price;
        }, 0);


        $obj_qutoes = $obj_qutoes->paginate($per_page); 

        $arr_quotes = $obj_qutoes->toArray();

        $data['order_data'] = [];



       if(isset($arr_quotes['data']) && !empty($arr_quotes['data']))
       { 
         $arr_quotes['data'] = array_values($arr_quotes['data']);

          foreach($arr_quotes['data'] as $key => $value)
          {
              $data['order_data'][$key]["id"]              = $value->id;
              $data['order_data'][$key]["order_no"]        = $value->order_no;
              $data['order_data'][$key]["order_date"]      = date('m-d-Y', strtotime($value->created_at));
              $data['order_data'][$key]["vendor"]          = $value->company_name;
              $data['order_data'][$key]["total_amount"]    = $value->total_wholesale_price;
              $data['order_data'][$key]["payment_status"]  = $value->is_payment_status;
              $data['order_data'][$key]["shipping_status"] = $value->ship_status;
              $data['order_data'][$key]["request_rejection_status"] = $value->order_cancel_rejected_status;
              $data['order_data'][$key]["is_split_order"]  = $value->is_split_order;
              $data['order_data'][$key]["order_cancel_status"]  = $value->order_cancel_status;

          } 
       }  


          $data['pagination']["current_page"]     = $arr_quotes['current_page'];
          $data['pagination']["first_page_url"]   = $arr_quotes['first_page_url'];
          $data['pagination']["from"]             = $arr_quotes['from'];
          $data['pagination']["last_page"]        = $arr_quotes['last_page'];
          $data['pagination']["last_page_url"]    = $arr_quotes['last_page_url'];
          $data['pagination']["next_page_url"]    = $arr_quotes['next_page_url'];
          $data['pagination']["path"]             = $arr_quotes['path'];
          $data['pagination']["per_page"]         = $arr_quotes['per_page'];
          $data['pagination']["prev_page_url"]    = $arr_quotes['prev_page_url'];
          $data['pagination']["to"]               = $arr_quotes['to'];
          $data['pagination']["total"]            = $arr_quotes['total'];
          $data["total_order_amount"]             = isset($total_amt)?$total_amt:'';


          $response             = [];
          $response['status']   = 'success';
          $response['message']  = 'Order list get successfully.';
          $response['data']     = isset($data)?$data:[];
   
   
          return $response;
      }
      
      catch(Exception $e)
      {
         $response = [
        'status'  => 'failure',
        'message' => $e->getMessage(),
        'data'    => ''
        ];
   
        return $response;
      }    
   
                                
    }


    public function rep_sales_orders($user_id=null,$form_data=[])
    {


      $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $maker_table        = $this->MakerModel->getTable();
      $prefix_maker_table =  DB::getTablePrefix().$maker_table;   

      try
      {
        $order_type = isset($form_data['order_type'])?$form_data['order_type']:'';  
        $per_page   = isset($form_data['per_page'])?$form_data['per_page']:10; 

        if(isset($order_type) && !empty($order_type) && $order_type=='cancelled')
        {  
            $leads_table          = $this->RepresentativeLeadsModel->getTable();        
            $prefixed_leads_table = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

            $transaction_mapping_table  = $this->TransactionMappingModel->getTable();
            $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

            $user_tbl_name              = $this->UserModel->getTable();
            $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

            $retailer_table             = $this->RetailerModel->getTable();
            $prefix_retailer_table      = DB::getTablePrefix().$retailer_table;


            $maker_tbl                  = $this->MakerModel->getTable();        
            $prefixed_maker_tbl         = DB::getTablePrefix().$this->MakerModel->getTable();


            $lead_obj = DB::table($leads_table)
                            ->select(DB::raw($prefixed_leads_table.".*,".
                                            $prefixed_transaction_mapping_tbl.".id as tid,".
                                            $prefixed_transaction_mapping_tbl.".transaction_status,".
                                            $prefix_retailer_table.'.store_name,'.

                                            $prefixed_maker_tbl.".brand_name,".
                                            $prefixed_maker_tbl.".company_name,".
                                           

                                            " CONCAT(RS.first_name,' ',RS.last_name) as sales_user_name,". 
                                            " CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"

                                        ))


                                  ->leftJoin($prefixed_user_tbl." AS RL", 'RL.id','=',$prefixed_leads_table.'.retailer_id')

                                    ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_leads_table.'.retailer_id')

                                     ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_leads_table,$prefixed_transaction_mapping_tbl){

                                          $join->on($prefixed_leads_table.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                          ->on($prefixed_leads_table.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                        })   

                                    ->leftjoin($prefixed_maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_leads_table.'.maker_id')

                                    ->leftJoin($prefixed_user_tbl." AS RR", 'RR.id','=',$prefixed_leads_table.'.representative_id')

                                    ->leftJoin($prefixed_user_tbl." AS RS", 'RS.id','=',$prefixed_leads_table.'.sales_manager_id')


                                    ->where($prefixed_leads_table.'.order_cancel_status','=',2)

                                    ->where($prefixed_leads_table.'.retailer_id',$user_id)
                                    ->groupBy($prefixed_leads_table.".id")
                                    ->orderBy($prefixed_leads_table.".created_at",'DESC');

                                    // dd($lead_obj->get()->toArray());
            }                    


             else
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


                  $retailer_rep_mapping            = $this->RetailerRepresentativeMappingModel->getTable();
                  $prefix_retailer_rep_mapping_tbl = DB::getTablePrefix().$retailer_rep_mapping;



                  //get all rep/sales of loggedin retailer
                  $rep_sales_arr = [];
                  $representative_id = $sales_manager_id = 0;

                  $rep_sales_obj = $this->RetailerRepresentativeMappingModel->where('retailer_id',$user_id)->first();

                 
                 if(isset($rep_sales_obj))
                 {
                    $rep_sales_arr = $rep_sales_obj->toArray();

                    $representative_id =  $rep_sales_arr['representative_id'];

                    $sales_manager_id = $rep_sales_arr['sales_manager_id'];

                 }


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

                                        ->where($prefix_representative_leads_tbl.'.retailer_id','=',$user_id)

                                       ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                                       ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                                        
                                       ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                                       ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                                       ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_representative_leads_tbl.'.maker_id')


                                        ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                            $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                            ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                                        });


                                        //this condition for rep sales and  mapping

                                        if(isset($representative_id) && $representative_id!=0)
                                        {
                                          $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.representative_id',$representative_id);
                                        }
                                        
                                        if(isset($sales_manager_id) && $sales_manager_id!=0)
                                        {
                                            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.sales_manager_id',$sales_manager_id);

                                        }
                                       

                                      
                  $lead_obj = $lead_obj->groupBy($prefix_representative_leads_tbl.'.id')

                                        ->where($representative_leads.'.total_wholesale_price','>','0')

                                        ->where($representative_leads.'.order_cancel_status','!=',2)
                                        ->where($representative_leads.'.is_confirm','!=',4)
                                        ->where($representative_leads.'.is_confirm','!=',0)

                                        ->where($representative_leads.'.maker_id','!=',0)
                                        ->orderBy($prefix_representative_leads_tbl.'.id','DESC');

                                        if(isset($retailer_id) && $retailer_id!='')
                                        {
                                          
                                          $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.retailer_id','=',$user_id);
                                        }

                                        if(isset($order_type) && !empty($order_type) && $order_type=='completed')
                                        {
                                            $lead_obj = $lead_obj->where($representative_leads.'.is_confirm','=',1)
                                                                ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                                ->where($representative_leads.'.ship_status','=',1)
                                                                ->where($representative_leads.'.maker_confirmation','=',1)
                                                                 ->where(function($q) use($representative_leads){
                                                                return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                                         ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                                              });
                                        }

                               
                    /* ---------------- Filtering Logic ----------------------------------*/  

                  if(isset($order_type) && !empty($order_type) && $order_type=='pending')
                  {
                   /* $arr_search_column['q_payment_status'] = "1";
                    $arr_search_column['q_ship_status'] = "0";
    */               /* $lead_obj = $lead_obj
                            ->where(function($q) use($representative_leads){
                            return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                     ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                     ->orwhereNULL('payment_term');
                            });*/


                      $lead_obj = $lead_obj->where('order_cancel_status','!=',2)
                                                    ->where('retailer_id',$user_id)
                                                    ->where('is_split_order','=','0')
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
                                                    });
                  }

                  if(isset($order_type) && !empty($order_type) && $order_type=='net_30_pending')
                  {      
                    $lead_obj = $lead_obj->where('is_split_order','=','0')
                                          ->where(function($q){
                                             $q->where('is_payment_status','=','1')
                                              ->where('ship_status','=','0')
                                           
                                           ->orwhere(function($q){
                                               $q->where('is_payment_status','=','0')
                                                       ->where('ship_status','=','1');
                                            })
                                           ->orwhere(function($q){
                                              $q->where('is_payment_status','=','0')
                                                       ->where('ship_status','=','0');
                                            });
                                          }
                                          ); 

                    $lead_obj = $lead_obj->where(function($q){
                                                return $q->orwhere('payment_term','=','Net30')
                                                         ->orwhere('payment_term','=','Net30 - Online/Credit');
                                              });  
                  }

                  if(isset($order_type) && !empty($order_type) && $order_type=='net_30_completed')
                  {        
                        $lead_obj = $lead_obj->where($representative_leads.'.is_confirm','=',1)
                                             // ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                             ->where($representative_leads.'.ship_status','=',1)
                                             ->where($representative_leads.'.is_payment_status','=',1)
                                             ->where($representative_leads.'.maker_confirmation','=',1)
                                             ->where(function($q){
                                                  return $q->orwhere('payment_term','=','Net30')
                                                           ->orwhere('payment_term','=','Net30 - Online/Credit');
                                                }); 
                    
                  }

              }    


            /*---------------------------------Filtering Search Start Here-------------------------------------------*/

              if(isset($form_data['filter']['general_search']) && $form_data['filter']['general_search']!="")
              {
                  $search_term      = $form_data['filter']['general_search'];

                  $lead_obj         = $lead_obj->whereRaw(
                                      "(  `".$representative_leads."`.`order_no` LIKE '%".$search_term."%' OR
                                          `".$prefix_maker_table."`.`company_name` LIKE '%".$search_term."%' OR
                                          `".$representative_leads."`.`total_wholesale_price` LIKE '%".$search_term."%' OR
                                          `total_wholesale_price` LIKE '%".$search_term."%' OR 
                                          `".$representative_leads."`.`total_retail_price` LIKE '%".$search_term."%' OR
                                          `total_retail_price` LIKE '%".$search_term."%'
                                        )"
                                     );

                               // $lead_obj =  $lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');

              }                                       


              if(isset($form_data['filter']['date_from']) && $form_data['filter']['date_from']!="" && isset($form_data['filter']['date_to']) && $form_data['filter']['date_to']!="")
              {            

                  $search_term_from_date  = $form_data['filter']['date_from'];
                  $search_term_to_date    = $form_data['filter']['date_to'];
                  $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
                  $from_date              = $from_date->format('Y-m-d');
                  $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
                  $to_date                = $to_date->format('Y-m-d');
              
                  $lead_obj   = $lead_obj->whereDate($representative_leads.'.created_at', '<=', $to_date);
                  $lead_obj   = $lead_obj->whereDate($representative_leads.'.created_at', '>=', $from_date);

              }




              if(isset($form_data['filter']['order_status']) && $form_data['filter']['order_status']!="")
              {
                  $search_term      = $form_data['filter']['order_status'];
                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

              }

              if(isset($form_data['filter']['shipping_status']) && $form_data['filter']['shipping_status']!="")
              {  
                  $search_term  = $form_data['filter']['shipping_status'];
                  $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term);
              } 

              if(isset($form_data['filter']['refund_status']) && $form_data['filter']['refund_status']!="")
              {  
                  $search_term  = $form_data['filter']['refund_status'];
                  $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.refund_status','=', $search_term);
              }

              if(isset($form_data['filter']['payment_status']) && $form_data['filter']['payment_status']!="")
              {
                $search_term  = $form_data['filter']['payment_status'];

                if($search_term == 1)
                {

                    $lead_obj = $lead_obj->where('is_payment_status','=','1');
                   /* whereNotExists(function($query) use ($prefix_transaction_mapping,$prefix_representative_leads_tbl)
                            {

                                $query->select(\DB::raw("
                                        transaction_mapping.order_id,
                                        transaction_mapping.order_no
                                    FROM
                                        `transaction_mapping`
                                    WHERE
                                        `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                                "));
                            });  */                      
                                   
                }
                else
                {
                   $lead_obj  = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','=', $search_term);
                }
              }



              $total_amt = 0;      
              $total_amt =array_reduce($lead_obj->get()->toArray(), function(&$res, $item) {
                  return $res + $item->total_wholesale_price;
              }, 0);


              $lead_obj  = $lead_obj->paginate($per_page); 

              $leads_arr = $lead_obj->toArray();

              $data['order_data'] = [];



       if(isset($leads_arr['data']) && !empty($leads_arr['data']))
       { 
         $leads_arr['data'] = array_values($leads_arr['data']);

          foreach($leads_arr['data'] as $key => $value)
          {
              $data['order_data'][$key]["id"]              = $value->id;
              $data['order_data'][$key]["order_no"]        = $value->order_no;
              $data['order_data'][$key]["order_date"]      = date('m-d-Y', strtotime($value->created_at));
              $data['order_data'][$key]["vendor"]          = $value->company_name;
              $data['order_data'][$key]["total_amount"]    = $value->total_wholesale_price;
              $data['order_data'][$key]["payment_status"]  = $value->is_payment_status;
              $data['order_data'][$key]["shipping_status"] = $value->ship_status;
              $data['order_data'][$key]["request_rejection_status"] = $value->order_cancel_rejected_status;
              $data['order_data'][$key]["is_split_order"]  = $value->is_split_order;
              $data['order_data'][$key]["order_cancel_status"]  = $value->order_cancel_status;
              $data['order_data'][$key]["representative_user_name"]  = $value->representative_user_name;
              $data['order_data'][$key]["is_confirm"]      = $value->is_confirm;
          } 
       }  


          $data['pagination']["current_page"]     = $leads_arr['current_page'];
          $data['pagination']["first_page_url"]   = $leads_arr['first_page_url'];
          $data['pagination']["from"]             = $leads_arr['from'];
          $data['pagination']["last_page"]        = $leads_arr['last_page'];
          $data['pagination']["last_page_url"]    = $leads_arr['last_page_url'];
          $data['pagination']["next_page_url"]    = $leads_arr['next_page_url'];
          $data['pagination']["path"]             = $leads_arr['path'];
          $data['pagination']["per_page"]         = $leads_arr['per_page'];
          $data['pagination']["prev_page_url"]    = $leads_arr['prev_page_url'];
          $data['pagination']["to"]               = $leads_arr['to'];
          $data['pagination']["total"]            = $leads_arr['total'];
          $data["total_order_amount"]             = isset($total_amt)?$total_amt:'';


          $response             = [];
          $response['status']   = 'success';
          $response['message']  = 'Order list get successfully.';
          $response['data']     = isset($data)?$data:[];
   
         return $response;   

      }

      catch(Exception $e)
      {
         $response = [
        'status'  => 'failure',
        'message' => $e->getMessage(),
        'data'    => ''
        ];
   
        return $response;   
      }

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

                           
                           ->where($representative_leads.'.is_confirm','!=',4)
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
                               $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=','0')                               
                                ->where($representative_leads.'.order_cancel_status','!=','2')
                                ->where(function($q){
                                  return $q->orwhere('is_payment_status','=','0')
                                           ->orwhere('is_payment_status','=','1');
                                })

                                ->where(function($q) use($representative_leads){
                                    return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                             ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                             ->orwhereNULL($representative_leads.'.payment_term');
                                  });

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

  public function rep_sale_order_details($order_id=false,$order_no=false)
  {
      try
      { 
          $main_split_order_no = $split_order_arr = $leads_arr = $data = [];
          $billing_address_str = $shipping_address_str = $retailer_payment_status = $commission_status = '';

          $leads_id = $order_id;
          $order_no = $order_no;

          $obj_data =  $this->RepresentativeLeadsModel
                            ->with(['address_details',
                                   'retailer_user_details.retailer_details'=>function($retailer_details) 
                                   {
                                      $retailer_details->select('user_id','store_name');
                                   },
                                   'representative_user_details'=>function($q2)
                                   {
                                      $q2->select('id','email','first_name','last_name');
                                   },'order_details'=>function($q3) use ($leads_id)
                                   {
                                     $q3->where('representative_leads_id',$leads_id);
                                   },
                                   'maker_details'=>function($maker_details)
                                   {
                                      $maker_details->select('user_id','company_name');
                                   },
                                   'transaction_mapping'=>function($qry) use ($order_no){
                                      $qry->select('transaction_id','order_no')->where('order_no',$order_no);
                                  }  
                               ])
                               ->where('id',$leads_id)
                               ->where('order_no',$order_no)
                               ->first();

          if($obj_data)
          {
            $leads_arr = $obj_data->toArray();

            if(isset($leads_arr['split_order_id']) && $leads_arr['split_order_id'] != '')
            {
                $obj_main_split_order_no = $this->RepresentativeLeadsModel->select('order_no','id')
                                                                          ->where('id',$leads_arr['split_order_id'])
                                                                          ->first();

                if(isset($obj_main_split_order_no))
                {
                  $main_split_order_no = $obj_main_split_order_no->toArray();
                }                
            }
            elseif (isset($leads_arr['is_split_order']) && $leads_arr['is_split_order'] == '1')
            {

              $split_order_arr = $this->RepresentativeLeadsModel->select('order_no','id')
                                                                ->where('split_order_id',$leads_arr['id'])
                                                                ->get()
                                                                ->toArray();
            }
          }   


          $promoCode     = isset($leads_arr['promo_code'])?$leads_arr['promo_code']:false;
          
          $isFreeShipping = is_promocode_freeshipping($promoCode);

          if($leads_id!=0 && $leads_id!='')
          {
            $tracking_details = $this->HelperService->getTrackingDetails($leads_id,$order_no);

            $data['tracking_no'] = isset($tracking_details['tracking_no'])?$tracking_details['tracking_no']:0;

            if(isset($tracking_details['company_id']) && $tracking_details['company_id']==1)
            {
              $url = 'https://www.fedex.com/en-in/home.html';

              //$url = "http://www.fedex.com/apps/fedextrack/?action=track&tracknumbers='".$tracking_no."'";
            } 
            elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==2)
            {
              $url = "https://www.ups.com/in/en/Home.page";
            }
            elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==3)
            {
              $url = "https://www.usps.com/";
            }
            elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==4)
            {
              $url = "https://www.dhl.com/en.html";
            }
            else
            {
              $url = '';
            }

            $data['url'] = $url;
           }

         /* $promoCodeData = $this->ProductService->get_promotion_and_prodo_code_details($promoCode);

          if(isset($promoCodeData) && count($promoCodeData)>0)
          {
            foreach ($promoCodeData as $promoCode) 
            {
                if(isset($promoCode['get_promotions_offer_details']) && count($promoCode['get_promotions_offer_details']) > 0)
                {
                    foreach($promoCode['get_promotions_offer_details'] as $promo_code)
                    {
                        if(isset($promo_code['get_prmotion_type']['promotion_type_name']) && count($promo_code['get_prmotion_type']['promotion_type_name'] > 0))
                        {
                            if($promo_code['get_prmotion_type']['promotion_type_name'] == 'Free Shipping')
                            {
                                $isFreeShipping = true;
                            }
                        }
                        
                    }
                }
            }
          }*/

          $billing_address_str .= !empty($leads_arr['address_details']['bill_street_address'])?$leads_arr['address_details']['bill_street_address'].',':'';

          $billing_address_str .= !empty($leads_arr['address_details']['bill_suit_apt'])?$leads_arr['address_details']['bill_suit_apt'].',':'';

          $billing_address_str .= !empty($leads_arr['address_details']['bill_city'])?$leads_arr['address_details']['bill_city'].',':''; 

          $billing_address_str .= !empty($leads_arr['address_details']['bill_state'])?$leads_arr['address_details']['bill_state'].',':'';

          $billing_address_str .= !empty($leads_arr['address_details']['bill_country'])?get_country($leads_arr['address_details']['bill_country']).',':'';

          $billing_address_str .=  !empty($leads_arr['address_details']['bill_zip_code'])?$leads_arr['address_details']['bill_zip_code'].',':'';

          if(!empty($billing_address_str)) 
          { 
            $billing_address_str .= 'Mobile.No:';
          } 

           $billing_address_str .= !empty($leads_arr['address_details']['bill_mobile_no'])?$leads_arr['address_details']['bill_mobile_no']:'';


           $shipping_address_str .= !empty($leads_arr['address_details']['ship_street_address'])?$leads_arr['address_details']['ship_street_address'].',':'';

           $shipping_address_str .= !empty($leads_arr['address_details']['ship_suit_apt'])?$leads_arr['address_details']['ship_suit_apt'].',':'';

           $shipping_address_str .= !empty($leads_arr['address_details']['ship_city'])?$leads_arr['address_details']['ship_city'].',':''; 

           $shipping_address_str .= !empty($leads_arr['address_details']['ship_state'])?$leads_arr['address_details']['ship_state'].',':'';

           $shipping_address_str .=  !empty($leads_arr['address_details']['ship_country'])?get_country($leads_arr['address_details']['ship_country']).',':'';

           $shipping_address_str .=  !empty($leads_arr['address_details']['ship_zip_code'])?$leads_arr['address_details']['ship_zip_code'].',':'';


           if(!empty($shipping_address_str)) 
           { 
              $shipping_address_str .= 'Mobile.No:';
           } 

           $shipping_address_str .= !empty($leads_arr['address_details']['ship_mobile_no'])?$leads_arr['address_details']['ship_mobile_no']:'';
          
           $data['transaction_id'] = isset($leads_arr['transaction_mapping']['transaction_id'])?$leads_arr['transaction_mapping']['transaction_id']:'N/A';
           
           $is_payment_status = isset($leads_arr['is_payment_status'])?$leads_arr['is_payment_status']:0;

           $data['promo_code']    = $promoCode; 

           $data['retailer']      = isset($leads_arr['retailer_user_details']['retailer_details']['store_name'])?$leads_arr['retailer_user_details']['retailer_details']['store_name']:'';

           $data['vendor']        = isset($leads_arr['maker_details']['company_name'])?$leads_arr['maker_details']['company_name']:'';

           $data['order_no']      = isset($leads_arr['order_no'])?$leads_arr['order_no']:'';

           $data['order_date']    = isset($leads_arr['created_at'])?date('m-d-Y', strtotime($leads_arr['created_at'])):'';

           $data['total_amount']  = isset($leads_arr['total_wholesale_price'])?$leads_arr['total_wholesale_price']:'';


           $data['shipping_status']         = isset($leads_arr['ship_status'])?$leads_arr['ship_status']:'';

           $data['rep_sales_retailer_payment_status'] = isset($is_payment_status)?$is_payment_status:'';

           $data['commission_status']       = isset($commission_status)?$commission_status:'';

           $data['retailer_approval']       = isset($leads_arr['is_confirm'])?$leads_arr['is_confirm']:'';

           $data['payment_term']            = isset($leads_arr['payment_term'])?$leads_arr['payment_term']:'';

           $data['billing_address']         = isset($billing_address_str)?$billing_address_str:'';

           $data['shipping_address']        = isset($shipping_address_str)?$shipping_address_str:'';

           $data['isFreeShipping']          = isset($isFreeShipping)?$isFreeShipping:'';

           $data['split_order_arr']         = isset($split_order_arr)?$split_order_arr:[];

           $data['main_split_order_no']     = isset($main_split_order_no)?$main_split_order_no:[];

           $ordNo                           = isset($leads_arr['order_no'])?base64_encode($leads_arr['order_no']):'';

           $vendorId                        = isset($leads_arr['maker_id'])?base64_encode($leads_arr['maker_id']):''; 

           $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');


           if((isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) || (isset($orderCalculationData['promotion_shipping_charges'])))
           {
             if(isset($orderCalculationData['sub_grand_total']))
             {  
                $data['promotion_total_amount'] = isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']):0.00;
             }
           } 

           if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) 
           {
              $data['promotion_discount_percentage'] = isset($orderCalculationData['discount_per'])?$orderCalculationData['discount_per'] : 0 ;

              $data['promotion_discount_amount']     = isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00;
           } 


           if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)  
           {
               $data['promotion_shipping_charges']   = isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 ;
           }

           $data['total_amount_wholesale']        = isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 ;
           $data  = $this->CommonService->get_status_display_names($data,'details'); 
           
           $response = [
                        'status'  => 'success',
                        'message' => 'Order details get successfully.',
                        'data'    => isset($data)?$data:[]
                      ];

           return $response;       
      }
      catch(Exception $e)
      {
          $response = [
                      'status'  => 'failure',
                      'message' => 'Something went wrong.',
                      'data'    => ''
                    ];

          return $response;
     }    
  }


  function rep_sales_product_listing($order_no ="",$lead_id="",$page="",$per_page="")
  {
   try
   { 
         $products = $arrVendors = [];
 
         $total_wholesale_price = $total_product_discount = $total_shipping_charges =
         $total_shipping_charge = $total_shipping_discount = $total_wholsale_price = 0;
 
         $order_number = isset($order_no)?base64_encode($order_no):'';
         
         if($order_no!="" && $lead_id!="")
         {
             $lead_id     = $lead_id;
             $order_no    = $order_no;  
 
             $product_obj = RepresentativeProductLeadsModel::where('order_no',$order_no)
                                                           ->where('representative_leads_id',$lead_id)
                                                           ->with(['product_details' => function($product_details){
                                                            $product_details->select('id','product_name','description','brand','minimum_amount_off','product_dis_min_amt','shipping_type','product_discount','prodduct_dis_type','off_type_amount');
                                                            $product_details->with(['brand_details' => function ($brand_details) {
                                                            $brand_details->select('id','brand_name');
                                                            }]);
                                                            $product_details->with(['productDetails' => function ($productDetails) {
                                                            $productDetails->select('product_id','sku','image');
                                                            }]);
                                                          }])
                                                          ->with(['get_product_min_qty'])
                                                          ->with(['maker_details'=>function($maker_details){
                                                             $maker_details->select('user_id','company_name')
                                                                           ->with(['shop_settings']);
                                                                           // ->with(['shop_settings'=>function($q){
                                                                           //  $q->select('first_order_minimum');
                                                                           // }]);
                                                          }]);
          $product_obj = $product_obj->paginate($per_page);  // Pagination
          $product_arr = $product_obj->toArray();
          
          $products = []; 

           $product_max_qty     = "";
           $obj_product_max_qty = $this->SiteSettingModel->select('product_max_qty')->first();  

           if($obj_product_max_qty)
           {
               $product_max_qty = $obj_product_max_qty->product_max_qty;
           }

         if(isset($product_arr['data']) && count($product_arr)>0) 
         {  
          
          $products['company_name'] = isset($product_arr['data'][0]['maker_details']['company_name'])?$product_arr['data'][0]['maker_details']['company_name']:'';

          $products['minimum_order_amount'] = isset($product_arr['data'][0]['maker_details']['shop_settings']['first_order_minimum'])?num_format($product_arr['data'][0]['maker_details']['shop_settings']['first_order_minimum']):0;

          $products['data'] = [];

          foreach($product_arr['data'] as $key=>$pro)
          {


             $products['data'][$key]['id']                   = isset($pro['product_details']['id'])?$pro['product_details']['id']:'';
             $products['data'][$key]['name']                 = isset($pro['product_details']['product_name'])?$pro['product_details']['product_name']:'';
             $products['data'][$key]['brand_name']           = isset($pro['product_details']['brand_details']['brand_name'])?$pro['product_details']['brand_details']['brand_name']:'';
             $products['data'][$key]['company_name']         = isset($pro['maker_details']['company_name'])?$pro['maker_details']['company_name']:'';   

             $products['data'][$key]['minimum_order_amount'] = isset($pro['maker_details']['company_name']['shop_settings']['first_order_minimum'])?$pro['maker_details']['company_name']['shop_settings']['first_order_minimum']:'';
             $products['data'][$key]['description']          = isset($pro['product_details']['description'])?$pro['product_details']['description']:'';

             if(isset($pro['sku_images']['image']))
             { 
               $products['data'][$key]['image']               = $this->CommonService->imagePathProduct($pro['sku_images']['image'], 'product', 0);
             }

             $products['data'][$key]['sku']                     = isset($pro['sku'])?$pro['sku']:'';
             $products['data'][$key]['shipping_charges']        = isset($pro['product_shipping_charge'])?$pro['product_shipping_charge']:'';
             $products['data'][$key]['unit_price']              = isset($pro['unit_wholsale_price'])?$pro['unit_wholsale_price']:'';
             $products['data'][$key]['sub_total']               = isset($pro['wholesale_price'])?$pro['wholesale_price']:'';
             $products['data'][$key]['total_amount']            = isset($pro['wholesale_price'])?$pro['wholesale_price']:'';
             $products['data'][$key]['shipping_discount']       = isset($pro['shipping_charges_discount'])?$pro['shipping_charges_discount']:'';
             $products['data'][$key]['product_discount']        = isset($pro['product_discount'])?$pro['product_discount']:'';
             $products['data'][$key]['qty']                     = isset($pro['qty'])?$pro['qty']:'';
            
             $products['data'][$key]['image']                   = $this->CommonService->get_sku_image($products['data'][$key]['qty']);

             $products['data'][$key]['product_min_qty']         = isset($pro['get_product_min_qty']['product_min_qty'])?$pro['get_product_min_qty']['product_min_qty']:0;
              $products['data'][$key]['product_max_qty']       = isset($product_max_qty)?$product_max_qty:0;
             $products['data'][$key]['product_dis_min_amt']     = isset($pro['product_details']['product_dis_min_amt'])?$pro['product_details']['product_dis_min_amt']:'';
             $products['data'][$key]['minimum_amount_off']      = isset($pro['product_details']['minimum_amount_off'])?$pro['product_details']['minimum_amount_off']:'';
             $products['data'][$key]['shipping_type']           = isset($pro['product_details']['shipping_type'])?$pro['product_details']['shipping_type']:'';
             $products['data'][$key]['product_discount_type']   = isset($pro['product_details']['prodduct_dis_type'])?$pro['product_details']['prodduct_dis_type']:'';
             $products['data'][$key]['product_discount_value']  = isset($pro['product_details']['product_discount'])?$pro['product_details']['product_discount']:'';
             $products['data'][$key]['shipping_discount_value'] = isset($pro['product_details']['off_type_amount'])?$pro['product_details']['off_type_amount']:'';
 
            }
          }  

          $products['pagination']["current_page"]    = $product_arr['current_page'];
          $products['pagination']["first_page_url"]  = $product_arr['first_page_url'];
          $products['pagination']["from"]            = $product_arr['from'];
          $products['pagination']["last_page"]       = $product_arr['last_page'];
          $products['pagination']["last_page_url"]   = $product_arr['last_page_url'];
          $products['pagination']["next_page_url"]   = $product_arr['next_page_url'];
          $products['pagination']["path"]            = $product_arr['path'];
          $products['pagination']["per_page"]        = $product_arr['per_page'];
          $products['pagination']["prev_page_url"]   = $product_arr['prev_page_url'];
          $products['pagination']["to"]              = $product_arr['to'];
          $products['pagination']["total"]           = $product_arr['total'];

         

          $response                   = [];
          $response['status']         = 'success';
          $response['message']        = 'Product list get successfully.';
          $response['data']           = isset($products)?$products:[]; 
   
         } 
         else
         {
              $response                   = [];
              $response['status']         = 'failure';
              $response['message']        = 'Something went wrong, while get product listing.';
              $response['data']           = []; 
         }                                                 

          return $response;
      
   }
 
   catch(Exception $e)
   {
     $response = [
      'status'  => 'failure',
      'message' => $e->getMessage(),
      'data'    => ''
     ];
 
    return $response;
   }
  }

  public function rep_sales_order_cancel($order_id = false,$auth_user = null)
  {
    try
    {
               /* get current time */
            $order_detail_arr = [];
            $current_time     = $current_date = $placed_time = $placed_date = '';

            $datetime = date('d/m/Y H:i:s');

            if($auth_user)
            {
                $loggedInUserId = $auth_user->id;
            }
           
          
            $obj_order_details =   $this->RepresentativeLeadsModel
                                        ->with(['leads_details.product_details',
                                              'user_details'=>function($q)
                                            {
                                              $q->select('id','first_name','last_name','email');
                                            },'maker_data'=>function($q1){
                                              $q1->select('id','first_name','last_name','email');
                                            }])
                                        ->where('id',$order_id)
                                        ->first();


            if(isset($obj_order_details))
            {
               $order_detail_arr = $obj_order_details->toArray();
            }


            $now = new DateTime();
        
            $replydue = new DateTime($order_detail_arr['created_at']);

            $timetoreply = date_diff($now, $replydue);

            $timetoreply_hours = $timetoreply->days * 24 + $timetoreply->h;


            if($timetoreply_hours > 24 || $order_detail_arr['maker_confirmation']==1)
            {
                //If order cancel after 24 hours from order generate.
        
                $updated_arr['order_cancel_status'] = 1;
                $result = $this->RepresentativeLeadsModel->where('id',$order_id)->update($updated_arr);

                if($result)
                {
                    /*send mail to maker*/
                    // $service_response = $this->GeneralService->send_cancel_request_to_maker($order_detail_arr);
                  
                    /*send notification to maker*/

                    $vendor_view_href    = url('/').'/vendor/cancel_order_requests/view/'.base64_encode($order_id);
               

                    $first_name   = isset($user->first_name)?$user->first_name:"";
                    $last_name    = isset($user->last_name)?$user->last_name:""; 
                    
                    $notification_arr                 = [];
                    $notification_arr['from_user_id'] = $loggedInUserId;
                    $notification_arr['to_user_id']   = $order_detail_arr['maker_id'];


                     $notification_arr['description']  = 'Customer '.$first_name.' '.$last_name.' requested for cancel the order '.$order_detail_arr['order_no'].' please confirm the request.';

                    $notification_arr['title']        = 'Cancel Order Request';
                    $notification_arr['type']         = 'maker';
                    $notification_arr['link']         = $vendor_view_href;

                    $this->GeneralService->save_notification($notification_arr);


                    //send cancel order  request notficaition to the admin
                 
                    $first_name   = isset($user->first_name)?$user->first_name:"";
                    $last_name    = isset($user->last_name)?$user->last_name:""; 

                    $company_name = get_maker_company_name($order_detail_arr['maker_id']);
                    
                    $notification_arr                 = [];
                    $notification_arr['from_user_id'] = $loggedInUserId;
                    $notification_arr['to_user_id']   = 1;
                    $notification_arr['description']  = 'Customer '.$first_name.' '.$last_name.' requested for cancel the order '.$order_detail_arr['order_no'].' to the vendor '.$company_name;
                    $notification_arr['title']        = 'Cancel Order Request';
                    $notification_arr['type']         = 'admin';
                    $notification_arr['link']         = '';

                    $this->GeneralService->save_notification($notification_arr);




                    $response['status']       = 'success';
                    $response['message']  = "Order cancel request has been sent to vendor.";

                    return $response;

                }
                else
                {
                    $response['status']       = 'error';
                    $response['message']  = "Error occurred while sending cancel request to vendor.";

                    return $response;
                }    
            }
            else if($timetoreply_hours < 24)
            {
                // If order place before 24 Hours
                $updated_arr['order_cancel_status'] = 2;

                $result = $this->RepresentativeLeadsModel->where('id',$order_id)->update($updated_arr);
       
                if($result)
                {
                    /*Update quantity*/
                     if(isset($order_detail_arr['maker_confirmation']) && $order_detail_arr['maker_confirmation'] == 1)
                    {
                        /*Update quantity*/

                        foreach ($order_detail_arr['leads_details'] as $key => $value)
                        {
                           $update_qunty = $this->ProductInventoryModel->where('sku_no',$value['sku'])->increment('quantity',$value['qty']);
                        }

                    }

                  

                    /*send  cancel order mail to maker*/
                    $maker_id = isset($order_detail_arr['maker_id'])?$order_detail_arr['maker_id']:'';

                    $maker_mail = $this->get_email($maker_id);

                    // $service_response = $this->GeneralService->rep_sales_cancel_order_mail($order_detail_arr,$maker_mail); 


                    /*send  cancel order mail to admin*/
                    $admin_id = get_admin_id();

                    $admin_email = $this->get_email($admin_id);     

                    $service_response = $this->GeneralService->rep_sales_cancel_order_mail($order_detail_arr,$admin_email);   



                    /*send cancel order mail to rep or sales*/
                    if(isset($order_detail_arr['representative_id']) && $order_detail_arr['representative_id']!='')
                    {
                       $id = $order_detail_arr['representative_id'];
                    }
                    elseif(isset($order_detail_arr['sales_manager_id']) && $order_detail_arr['sales_manager_id']!='')
                    {
                       $id = $order_detail_arr['sales_manager_id'];
                    }
                    else
                    {
                        $id = 0;
                    }

                    $email = $this->get_email($id); 

                    $service_response = $this->GeneralService->rep_sales_cancel_order_mail($order_detail_arr,$email);  


                    /*send cancel order mail to retailer*/

                    $retailer_id = isset($order_detail_arr['retailer_id'])?$order_detail_arr['retailer_id']:'';

                    $retailer_email = $this->get_email($retailer_id);

                    $service_response = $this->GeneralService->rep_sales_cancel_order_mail($order_detail_arr,$retailer_email);


                    /*---------------------------------------------------------------*/

                    /*send cancel order notification to maker*/

                    $vendor_view_href    = url('/').'/vendor/rep_sales_cancel_orders/view/'.base64_encode($order_id);


                    $first_name   = isset($user->first_name)?$user->first_name:"";
                    $last_name    = isset($user->last_name)?$user->last_name:""; 
                   
                    
                    $notification_arr                 = [];
                    $notification_arr['from_user_id'] = $loggedInUserId;
                    $notification_arr['to_user_id']   = $order_detail_arr['maker_id'];
           
                    $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by retailer '.$first_name.' '.$last_name;

                    $notification_arr['title']        = 'Order Cancelled';
                    $notification_arr['type']         = 'maker';
                    $notification_arr['link']         = $vendor_view_href;

                    $this->GeneralService->save_notification($notification_arr,'retailer');



                    /*send cancel order notification to admin*/

                    $admin_view_href   = url('/').'/admin/rep_sales_cancel_orders/view/'.base64_encode($order_id);



                    $first_name   = isset($user->first_name)?$user->first_name:"";
                    $last_name    = isset($user->last_name)?$user->last_name:""; 
                   
                    
                    $notification_arr                 = [];
                    $notification_arr['from_user_id'] = $loggedInUserId;
                    $notification_arr['to_user_id']   = 1;

                    
                     $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by Customer '.$first_name.' '.$last_name;


                    $notification_arr['title']        = 'Order Cancelled';
                    $notification_arr['type']         = 'admin';
                    $notification_arr['link']         = $admin_view_href;

                    $this->GeneralService->save_notification($notification_arr);



                    /*send cancel order notification to sales or rep*/

                
                    if(isset($order_detail_arr['representative_id']) && $order_detail_arr['representative_id']!='')
                    {
                       $id   = $order_detail_arr['representative_id'];
                       $type = 'representative';

                       $view_href   = url('/').'/representative/rep_sales_cancel_orders/view/'.base64_encode($order_id);


                    }
                    elseif(isset($order_detail_arr['sales_manager_id']) && $order_detail_arr['sales_manager_id']!='')
                    {
                       $id   = $order_detail_arr['sales_manager_id'];
                       $type = 'sales_manager';

                       $view_href   = url('/').'/sales_manager/rep_sales_cancel_orders/view/'.base64_encode($order_id);
                    }
                    else
                    {
                        $id = 0;
                    }

                    $first_name   = isset($user->first_name)?$user->first_name:"";
                    $last_name    = isset($user->last_name)?$user->last_name:""; 
                   
                    
                    $notification_arr                 = [];
                    $notification_arr['from_user_id'] = $loggedInUserId;
                    $notification_arr['to_user_id']   = $id;

                    
                     $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by Customer '.$first_name.' '.$last_name;


                    $notification_arr['title']        = 'Order Cancelled';
                    $notification_arr['type']         = $type;
                    $notification_arr['link']         = $view_href;

                    $this->GeneralService->save_notification($notification_arr);


                    /*send cancel order notification to retailer*/
                    
                    $retailer_view_href   = url('/').'/retailer/rep_sales_cancel_orders/view/'.base64_encode($order_id);


                    $notification_arr                 = [];
                    $notification_arr['from_user_id'] = $loggedInUserId;
                    $notification_arr['to_user_id']   = $loggedInUserId;

                    
                     $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled.';


                    $notification_arr['title']        = 'Order Cancelled';
                    $notification_arr['type']         = 'retailer';
                    $notification_arr['link']         = $retailer_view_href;

                    $this->GeneralService->save_notification($notification_arr);


                    /*---------------------------------------------------------------*/

                    $response['status']      = 'success';
                    $response['message'] = "Order has been cancelled.";

                    return $response;

                }
                else
                {
                    $response['status']       = 'error';
                    $response['message']  = "Error occurred while canceling the order.";

                    return $response;
                }
      }
    }
    catch(Exception $e)
    {
      $response = [
         'status'  => 'failure',
         'message' => $e->getMessage(),
         'data'    => ''
      ];
 
      return $response;
    }
     
  }

  public function retailer_order_cancel($order_id = false,$user = null)
  {
      /* get current time */
      $order_detail_arr = [];
      $current_time     = $current_date = $placed_time = $placed_date = '';

      $datetime = date('d/m/Y H:i:s');

      if($user)
      {
          $loggedInUserId = $user->id;
      }
     
    
      $obj_order_details = $this->RetailerQuotesModel->with([ 'quotes_details.product_details',
                                                              'user_details'=>function($q){
                                                                  $q->select('id','first_name','last_name','email');

                                                              },'maker_details'=>function($q1){
                                                                  $q1->select('id','first_name','last_name','email');
                                                              }])
                                                     ->where('id',$order_id)
                                                     ->first();


      if(isset($obj_order_details))
      {
         $order_detail_arr = $obj_order_details->toArray();
      }

      $now = new DateTime();
  
      $replydue = new DateTime($order_detail_arr['created_at']);

      $timetoreply = date_diff($now, $replydue);

      $timetoreply_hours = $timetoreply->days * 24 + $timetoreply->h;



      if($timetoreply_hours > 24 || $order_detail_arr['maker_confirmation']==1)
      {
          //If order cancel after 24 hours from order generate.
  
          $updated_arr['order_cancel_status'] = 1;
          $result = $this->RetailerQuotesModel->where('id',$order_id)->update($updated_arr);

          if($result)
          {
              /*send mail to maker*/
              $service_response = $this->GeneralService->send_request_email_to_maker($order_detail_arr);
            
              /*send notification to maker*/

              //$view_href    = url('/').'/vendor/retailer_orders/view/'.base64_encode($order_id);
              $view_href    = url('/').'/vendor/retailer_cancel_orders/view/'.base64_encode($order_id);

              $first_name   = isset($user->first_name)?$user->first_name:"";
              $last_name    = isset($user->last_name)?$user->last_name:""; 
              
              $notification_arr                 = [];
              $notification_arr['from_user_id'] = $loggedInUserId;
              $notification_arr['to_user_id']   = $order_detail_arr['maker_id'];


               $notification_arr['description']  = 'Customer '.$first_name.' '.$last_name.' requested for cancel the order '.$order_detail_arr['order_no'].' please confirm the request.';

              $notification_arr['title']        = 'Cancel Order Request';
              $notification_arr['type']         = 'maker';
              $notification_arr['link']         = $view_href;

              $this->GeneralService->save_notification($notification_arr);

             

              //send cancel order  request notficaition to the admin
           
              $first_name   = isset($user->first_name)?$user->first_name:"";
              $last_name    = isset($user->last_name)?$user->last_name:""; 

              $company_name = get_maker_company_name($order_detail_arr['maker_id']);
              
              $notification_arr                 = [];
              $notification_arr['from_user_id'] = $loggedInUserId;
              $notification_arr['to_user_id']   = 1;


              $notification_arr['description']  = 'Customer '.$first_name.' '.$last_name.' requested for cancel the order '.$order_detail_arr['order_no'].' to the vendor '.$company_name;

              $notification_arr['title']        = 'Cancel Order Request';
              $notification_arr['type']         = 'admin';
              $notification_arr['link']         = '';

              $this->GeneralService->save_notification($notification_arr);


              $response['status']       = 'success';
              $response['message']  = "Order cancel request has been sent to vendor.";

              return $response;

          }
          else
          {
              $response['status']       = 'error';
              $response['message']  = "Error occurred while sending cancel request to vendor.";

              return $response;
          }  
          
      }
      else
      {
          // If order place before 24 Hours
          $updated_arr['order_cancel_status'] = 2;
          $result = $this->RetailerQuotesModel->where('id',$order_id)->update($updated_arr);

          
          if($result)
          {

              if(isset($order_detail_arr['maker_confirmation']) && $order_detail_arr['maker_confirmation'] == 1)
              {
                  /*Update quantity*/

                  foreach ($order_detail_arr['quotes_details'] as $key => $value) {
      
                      $update_qunty = $this->ProductInventoryModel->where('sku_no',$value['sku_no'])->increment('quantity',$value['qty']);

                  }

              }
              /*send mail to maker*/
               $service_response = $this->GeneralService->cancel_order_mail($order_detail_arr); 

              /*send notification to maker*/

              $view_href    = url('/').'/vendor/cancel_orders/view/'.base64_encode($order_id);

              $first_name   = isset($user->first_name)?$user->first_name:"";
              $last_name    = isset($user->last_name)?$user->last_name:""; 
             
              
              //send notification to vendor for cancel order

              $notification_arr                 = [];
              $notification_arr['from_user_id'] = $loggedInUserId;
              $notification_arr['to_user_id']   = $order_detail_arr['maker_id'];

              $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by customer '.$first_name.' '.$last_name;

              $notification_arr['title']        = 'Order Cancelled';
              $notification_arr['type']         = 'maker';
              $notification_arr['link']         = $view_href;

              $this->GeneralService->save_notification($notification_arr,'retailer');



              /*send notification to admin for cancel order*/

              $view_href   = url('/').'/admin/cancel_orders/view/'.base64_encode($order_id);

              $first_name   = isset($user->first_name)?$user->first_name:"";
              $last_name    = isset($user->last_name)?$user->last_name:""; 
             
              
              $notification_arr                 = [];
              $notification_arr['from_user_id'] = $loggedInUserId;
              $notification_arr['to_user_id']   = 1;

              
               $notification_arr['description']  = 'Order '.$order_detail_arr['order_no'].' has been cancelled by customer '.$first_name.' '.$last_name;


              $notification_arr['title']        = 'Order Cancelled';
              $notification_arr['type']         = 'admin';
              $notification_arr['link']         = $view_href;

              $this->GeneralService->save_notification($notification_arr);


              /*send notification to retailer for cancel order*/

              $view_href   = url('/').'/retailer/my_cancel_orders/view/'.base64_encode($order_id);

              $notification_arr                 = [];
              $notification_arr['from_user_id'] = $loggedInUserId;
              $notification_arr['to_user_id']   = $loggedInUserId;
   
              $notification_arr['description']  = 'Your order '.$order_detail_arr['order_no'].' has been cancelled';

              $notification_arr['title']        = 'Order Cancelled';
              $notification_arr['type']         = 'retailer';
              $notification_arr['link']         = $view_href;

              $this->GeneralService->save_notification($notification_arr);


              $response['status']      = 'success';
              $response['message']     = "Order has been cancelled.";

              return $response;

          }
          else
          {
              $response['status']       = 'error';
              $response['message']      = "Error occurred while canceling the order.";

              return $response;
          }
         
        


      }
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


    public function details($user_id,$order_no,$order_id)
    {
      
        $main_split_order_no = "";

        $enquiry_arr = $split_order_arr = [];
                
        $enquiry_obj = $this->RetailerQuotesModel->with([  'maker_data',
                                                           'user_details'
                                                      ])                                                  
                                                ->where('id',$order_id);

        $obj_enquiry_data = $enquiry_obj->first(); 

        if($obj_enquiry_data)
        {
            $enquiry_arr = $obj_enquiry_data->toArray();  

            if ($enquiry_arr['split_order_id'] != '') {

                $main_split_order_no = $this->RetailerQuotesModel->select('id','order_no')
                                                                 ->where('id',$enquiry_arr['split_order_id'])
                                                                 ->first();
            }
            elseif ($enquiry_arr['is_split_order'] == '1') {

                $split_order_arr = $this->RetailerQuotesModel->select('id','order_no')
                                                             ->where('split_order_id',$enquiry_arr['id'])
                                                             ->get()
                                                             ->toArray(); 
            }         
        } 

        $order_no = isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:0;

        $all_details_enquiry_obj = $enquiry_obj->with(['transaction_mapping'=>function($qry) use ($order_no){
                                                        $qry->where('order_no',$order_no);
                                                      }])->first();

      if($all_details_enquiry_obj)
      {
        $enquiry_arr = $all_details_enquiry_obj->toArray();
      }  

        
        $isFreeShipping = false;

        $promoCode = isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:false;

        $isFreeShipping = is_promocode_freeshipping($promoCode);

        /*$promoCodeData = $this->ProductService->get_promotion_and_prodo_code_details($promoCode);

        if(isset($promoCodeData) && count($promoCodeData)>0)
        {
            foreach ($promoCodeData as $promoCode) 
            {
                if(isset($promoCode['get_promotions_offer_details']) && count($promoCode['get_promotions_offer_details']) > 0)
                {
                    foreach($promoCode['get_promotions_offer_details'] as $promo_code)
                    {
                        if(isset($promo_code['get_prmotion_type']['promotion_type_name']) && count($promo_code['get_prmotion_type']['promotion_type_name'] > 0))
                        {
                            if($promo_code['get_prmotion_type']['promotion_type_name'] == 'Free Shipping')
                            {
                                $isFreeShipping = true;
                            }
                        }
                        
                    }
                }
            }
        }*/

        $enquiry_arr_id       = isset($enquiry_arr['id'])?$enquiry_arr['id']:0;
        $enquiry_arr_order_no = isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:'';


        if($enquiry_arr_id!=0 && $enquiry_arr_order_no!='')
        {
          $tracking_details = $this->HelperService->getTrackingDetails($enquiry_arr_id,$enquiry_arr_order_no);
          $tracking_no = isset($tracking_details['tracking_no'])?$tracking_details['tracking_no']:0;
        }  

       
            if(isset($enquiry_arr['order_no']) && !empty($enquiry_arr['order_no']) &&
                      isset($enquiry_arr['maker_id']) && !empty($enquiry_arr['maker_id']))
            {
                $ordNo = base64_encode($enquiry_arr['order_no']);
                $vendorId = base64_encode($enquiry_arr['maker_id']);

                $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer');
            }  
            if(isset($enquiry_arr['shipping_addr']) && $enquiry_arr['shipping_addr']!=null && $enquiry_arr['shipping_addr_zip_code']==null) 
            {
              $shipping_address = $enquiry_arr['shipping_addr']; 
            }
            else if(isset($enquiry_arr['shipping_addr']) && $enquiry_arr['shipping_addr']!=null && $enquiry_arr['shipping_addr_zip_code']!=null) 
            {
              $shipping_address = $enquiry_arr['shipping_addr'].','.$enquiry_arr['shipping_addr_zip_code']; 
            }
            else
            {
               $shipping_address = 'N/A';
            }
             
            if(isset($enquiry_arr['billing_addr']) && $enquiry_arr['billing_addr']!=null && $enquiry_arr['billing_addr_zip_code']==null) 
            {
              $billing_address = $enquiry_arr['billing_addr'];
            }
            else if(isset($enquiry_arr['billing_addr']) && $enquiry_arr['billing_addr']!=null && $enquiry_arr['billing_addr_zip_code']!=null) 
            {
              $billing_address = $enquiry_arr['billing_addr'].','.$enquiry_arr['billing_addr_zip_code'];
            }
            else
            {
             $billing_address = 'N/A'; 
            }

            $payment_status = "";
            if(isset($enquiry_arr['is_payment_status']) && $enquiry_arr['is_payment_status'] == 1)
            {
              $payment_status = 'Paid';
            }
            else
            {
              $payment_status = 'Pending';
            }

          $tracking_url = "";
          if(isset($tracking_details['company_id']) && $tracking_details['company_id']==1)
          {
             $tracking_url = 'https://www.fedex.com/en-in/home.html';
          } 
          elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==2)
          {
            $tracking_url = "https://www.ups.com/in/en/Home.page";
          }
          elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==3)
          {
             $tracking_url = "https://www.usps.com/";
          }
          elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==4)
          {
             $tracking_url = "https://www.dhl.com/en.html";
          }
          else
          {
             $tracking_url = '';
          }

          /*For shipping status*/
          $shipping_status = isset($enquiry_arr['ship_status'])?get_order_status($enquiry_arr['ship_status']):'N/A';  


          $data['retailer']                 = isset($enquiry_arr['user_details']['first_name'])?$enquiry_arr['user_details']['first_name']:'';

          $data['retailer']                 .= isset($enquiry_arr['user_details']['last_name'])? ' '.$enquiry_arr['user_details']['last_name']:'';

          $data['vendor']                   = isset($enquiry_arr['maker_data']['company_name'])?$enquiry_arr['maker_data']['company_name']:'';

          $data['order_no']                 = isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:'';

          $data['order_id']                 = isset($order_id)?$order_id:'';

          $data['order_date']               = isset($enquiry_arr['created_at'])?date('m-d-Y', strtotime($enquiry_arr['created_at'])):'';

          $data['total_amount']             = isset($enquiry_arr['total_wholesale_price'])?$enquiry_arr['total_wholesale_price']:'';

          $data['promo_code']               = $promoCode; 

          $data['shipping_status']          = $shipping_status;

          $data['payment_term']             = isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'';

          $data['payment_status']           = isset($payment_status)?$payment_status:'';

          $data['billing_address']          = isset($billing_address)?$billing_address:'';

          $data['shipping_address']         = isset($shipping_address)?$shipping_address:'';

          $data['isFreeShipping']           = isset($isFreeShipping)?$isFreeShipping:'';

          $data['split_order_arr']          = isset($split_order_arr)?$split_order_arr:'';

          $data['main_split_order_no']      = isset($main_split_order_no)?$main_split_order_no:'';

          $data['tracking_no']              = isset($tracking_no)?$tracking_no:'';

          $data['tracking_url']             = isset($tracking_url)?$tracking_url:'';

          $data['transaction_id']           = isset($enquiry_arr['transaction_mapping']['transaction_id'])?$enquiry_arr['transaction_mapping']['transaction_id']:'';

          $ordNo                            = isset($enquiry_arr['order_no'])?base64_encode($enquiry_arr['order_no']):'';

          $vendorId                         = isset($enquiry_arr['maker_id'])?base64_encode($enquiry_arr['maker_id']):''; 


          $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer');


          if((isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) || (isset($orderCalculationData['promotion_shipping_charges'])))
          {
          if(isset($orderCalculationData['sub_grand_total']))
          {  
          $data['promotion_total_amount'] = isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']):0.00;
          }
          } 

          if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) 
          {
          $data['promotion_discount_percentage'] = isset($orderCalculationData['discount_per'])?$orderCalculationData['discount_per'] : 0 ;

          $data['promotion_discount_amount']     = isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00;
          } 


          if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)  
          {
          $data['promotion_shipping_charges']   = isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 ;
          }

          $data['total_amount_wholesale']        = isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 ;

           $response = [
                  'status'  => 'success',
                  'message' => 'Order details get successfully.',
                  'data'    => isset($data)?$data:[]
                ];
          return $response;


    }

  public function product_listing($order_no ="",$lead_id="",$page="",$per_page="")
   {
     try
     { 
           $product_obj = $product_arr = [];   
   
            if($order_no!="" && $lead_id!="")
            {   
            $product_obj = RetailerQuotesProductModel::where('retailer_quotes_id',$lead_id)
                                                     ->with(['product_details' => function($product_details){

                                                      $product_details->select(
                                                                              'id',
                                                                              'product_name',
                                                                              'description',
                                                                              'brand',
                                                                              'minimum_amount_off',
                                                                              'product_dis_min_amt',
                                                                              'shipping_type',
                                                                              'product_discount',
                                                                              'prodduct_dis_type',
                                                                              'off_type_amount'
                                                                            );

                                                      $product_details->with(['brand_details' => function ($brand_details) {
                                                      $brand_details->select('id','brand_name');
                                                      }]);
                                                      $product_details->with(['productDetails' => function ($productDetails) {
                                                      $productDetails->select('product_id','sku','image');
                                                      }]);
                                                    }]);
            }                                                  

            $product_obj = $product_obj->paginate($per_page);  // Pagination
            $product_arr = $product_obj->toArray();
           
            $products['data'] = []; 

           if(isset($product_arr['data']) && count($product_arr)>0) 
          {  
            foreach($product_arr['data'] as $key=>$pro)
            {
               $products['data'][$key]['id']                   = isset($pro['product_details']['id'])?$pro['product_details']['id']:'';
               $products['data'][$key]['name']                 = isset($pro['product_details']['product_name'])?$pro['product_details']['product_name']:'';
               $products['data'][$key]['brand_name']           = isset($pro['product_details']['brand_details']['brand_name'])?$pro['product_details']['brand_details']['brand_name']:'';
               $products['data'][$key]['company_name']         = isset($pro['maker_details']['company_name'])?$pro['maker_details']['company_name']:'';
               $products['data'][$key]['description']          = isset($pro['product_details']['description'])?$pro['product_details']['description']:'';
               
               if(isset($pro['sku_images']['image']))
               { 
                 $products['data'][$key]['image']               = $this->CommonService->imagePathProduct($pro['sku_images']['image'], 'product', 0);
               }

               $sub_total = $pro['unit_wholsale_price'] * $pro['qty'];

               $products['data'][$key]['sku']                     = isset($pro['sku_no'])?$pro['sku_no']:'';

               $products['data'][$key]['image']               = $this->CommonService->get_sku_image($products['data'][$key]['sku']);

               $products['data'][$key]['shipping_charges']        = isset($pro['shipping_charge'])?$pro['shipping_charge']:'';
               $products['data'][$key]['unit_price']              = isset($pro['unit_wholsale_price'])?$pro['unit_wholsale_price']:'';
               $products['data'][$key]['sub_total']               = isset($sub_total)?$sub_total:0;
               $products['data'][$key]['total_amount']            = isset($pro['wholesale_price'])?$pro['wholesale_price']:'';
               $products['data'][$key]['shipping_discount']       = isset($pro['shipping_discount'])?$pro['shipping_discount']:'';
               $products['data'][$key]['product_discount']        = isset($pro['product_discount'])?$pro['product_discount']:'';
               $products['data'][$key]['qty']                     = isset($pro['qty'])?$pro['qty']:'';
               $products['data'][$key]['product_min_qty']         = isset($pro['get_product_min_qty']['product_min_qty'])?$pro['get_product_min_qty']['product_min_qty']:0;
               $products['data'][$key]['product_dis_min_amt']     = isset($pro['product_details']['product_dis_min_amt'])?$pro['product_details']['product_dis_min_amt']:'';
               $products['data'][$key]['minimum_amount_off']      = isset($pro['product_details']['minimum_amount_off'])?$pro['product_details']['minimum_amount_off']:'';
               $products['data'][$key]['shipping_type']           = isset($pro['product_details']['shipping_type'])?$pro['product_details']['shipping_type']:'';
               $products['data'][$key]['product_discount_type']   = isset($pro['product_details']['prodduct_dis_type'])?$pro['product_details']['prodduct_dis_type']:'';
               $products['data'][$key]['product_discount_value']  = isset($pro['product_details']['product_discount'])?$pro['product_details']['product_discount']:'';
               $products['data'][$key]['shipping_discount_value'] = isset($pro['product_details']['off_type_amount'])?$pro['product_details']['off_type_amount']:'';
   
              }
            }  

            $products['pagination']["current_page"]    = $product_arr['current_page'];
            $products['pagination']["first_page_url"]  = $product_arr['first_page_url'];
            $products['pagination']["from"]            = $product_arr['from'];
            $products['pagination']["last_page"]       = $product_arr['last_page'];
            $products['pagination']["last_page_url"]   = $product_arr['last_page_url'];
            $products['pagination']["next_page_url"]   = $product_arr['next_page_url'];
            $products['pagination']["path"]            = $product_arr['path'];
            $products['pagination']["per_page"]        = $product_arr['per_page'];
            $products['pagination']["prev_page_url"]   = $product_arr['prev_page_url'];
            $products['pagination']["to"]              = $product_arr['to'];
            $products['pagination']["total"]           = $product_arr['total'];

           

            $response                   = [];
            $response['status']         = 'success';
            $response['message']        = 'Product list get successfully.';
            $response['data']           = isset($products)?$products:[]; 
            
            return $response;
   
     }
   
     catch(Exception $e)
     {
       $response = [
        'status'  => 'failure',
        'message' => $e->getMessage(),
        'data'    => ''
       ];
   
      return $response;
     }
   
   }


   public function reorder($order_id="",$user = "")
   {  
    try{

      if($order_id!="")
      { 

        $arr_order_details      = $arr_address = $arr_order = $product_details = $arr_sku_no = [];
      
        $order_no               = str_pad('J2',  10, rand('1234567890',10)); 

        $order_details          = $this->RetailerQuotesModel->with('quotes_details','address_details')->where('id',$order_id)->first();


      if ($order_details)
      {

        $arr_order_details = $order_details->toArray();
       
        /* get sku nos of order for getting product status*/
        if(isset($arr_order_details) && count($arr_order_details) >0 )
        {
          if(isset($arr_order_details['quotes_details']) && count($arr_order_details['quotes_details']) > 0)
          {
            $arr_sku_no = array_column($arr_order_details['quotes_details'], 'sku_no');


            $active_products = $this->orderDataService->get_active_product($arr_sku_no);

            $sku_count = isset($arr_sku_no)?count($arr_sku_no):0;

            $arr_active_product_count = isset($active_products)?count($active_products):0;

            $deactive_products_count  = $sku_count - $arr_active_product_count;

            if($deactive_products_count > 0 && $deactive_products_count !== $sku_count)
            {
               $response['status']  = 'warning';
               $response['message'] = 'The order you are trying to place, has '.$deactive_products_count.' product(s) unavailable at the moment, would you still like to proceed ?.';
               $response['data']    = '';
               return $response;
            }

            if(isset($active_products) && count($active_products) == 0)
            {
              $response['status']   = 'Apologies';
              $response['message']  = 'None of the product(s) are available at the moment in this order.';
              $response['data']     = '';
               return $response;
            }
          }
        }
      }

      // Store address of order
      if(isset($arr_order_details['address_details']) && count($arr_order_details['address_details']) > 0){
            $arr_address['order_no']              = $order_no;
            $arr_address['user_id']               = isset($arr_order_details['address_details']['user_id'])?$arr_order_details['address_details']['user_id']:'';
            $arr_address['bill_first_name']       = isset($arr_order_details['address_details']['bill_first_name'])?$arr_order_details['address_details']['bill_first_name']:'';
            $arr_address['bill_last_name']        = isset($arr_order_details['address_details']['bill_last_name'])?$arr_order_details['address_details']['bill_last_name']:'';
            $arr_address['bill_email']            = isset($arr_order_details['address_details']['bill_email'])?$arr_order_details['address_details']['bill_email']:'';
            $arr_address['bill_mobile_no']        = isset($arr_order_details['address_details']['bill_mobile_no'])?$arr_order_details['address_details']['bill_mobile_no']:'';
            $arr_address['bill_complete_address'] = isset($arr_order_details['address_details']['bill_complete_address'])?$arr_order_details['address_details']['bill_complete_address']:Null;
            $arr_address['bill_city']             = isset($arr_order_details['address_details']['bill_city'])?$arr_order_details['address_details']['bill_city']:'';
            $arr_address['bill_state']            = isset($arr_order_details['address_details']['bill_state'])?$arr_order_details['address_details']['bill_state']:'';
            $arr_address['bill_zip_code']         = isset($arr_order_details['address_details']['bill_zip_code'])?$arr_order_details['address_details']['bill_zip_code']:'';
            $arr_address['ship_first_name']       = isset($arr_order_details['address_details']['ship_first_name'])?$arr_order_details['address_details']['ship_first_name']:'';
            $arr_address['ship_last_name']        = isset($arr_order_details['address_details']['ship_last_name'])?$arr_order_details['address_details']['ship_last_name']:'';
            $arr_address['ship_email']            = isset($arr_order_details['address_details']['ship_email'])?$arr_order_details['address_details']['ship_email']:'';
            $arr_address['ship_mobile_no']        = isset($arr_order_details['address_details']['ship_mobile_no'])?$arr_order_details['address_details']['ship_mobile_no']:'';
            $arr_address['ship_complete_address'] = isset($arr_order_details['address_details']['ship_complete_address'])?$arr_order_details['address_details']['ship_complete_address']:Null;
            $arr_address['ship_city']             = isset($arr_order_details['address_details']['ship_city'])?$arr_order_details['address_details']['ship_city']:'';
            $arr_address['ship_state']            = isset($arr_order_details['address_details']['ship_state'])?$arr_order_details['address_details']['ship_state']:'';
            $arr_address['bill_country']          = isset($arr_order_details['address_details']['bill_country'])?$arr_order_details['address_details']['bill_country']:'';
            $arr_address['ship_country']          = isset($arr_order_details['address_details']['ship_country'])?$arr_order_details['address_details']['ship_country']:'';
            $arr_address['ship_zip_code']         = isset($arr_order_details['address_details']['ship_zip_code'])?$arr_order_details['address_details']['ship_zip_code']:'';
            $arr_address['is_as_below']           = isset($arr_order_details['address_details']['is_as_below'])?$arr_order_details['address_details']['is_as_below']:'';
            
            $arr_address['bill_street_address']   = isset($arr_order_details['address_details']['bill_street_address'])?$arr_order_details['address_details']['bill_street_address']:'';
            $arr_address['bill_suit_apt']         = isset($arr_order_details['address_details']['bill_suit_apt'])?$arr_order_details['address_details']['bill_suit_apt']:'';
            $arr_address['ship_street_address']   = isset($arr_order_details['address_details']['ship_street_address'])?$arr_order_details['address_details']['ship_street_address']:'';
            $arr_address['ship_suit_apt']         = isset($arr_order_details['address_details']['ship_suit_apt'])?$arr_order_details['address_details']['ship_suit_apt']:'';
            
            $store_address = $this->AddressModel->create($arr_address);
     }
     // dd($arr_order_details);
      //store main order
      $arr_order['order_no']                          = $order_no;
      $arr_order['admin_commission']                  = isset($arr_order_details['maker_id'])?$this->CommissionService->get_admin_commission($arr_order_details['maker_id']):'';
      $maker_id                                       = isset($arr_order_details['maker_id']) ? $arr_order_details['maker_id'] : 0;
      $arr_order['maker_id']                          = $arr_order_details['maker_id'];
      $arr_order['retailer_id']                       = $arr_order_details['retailer_id'];
      $arr_order['ship_status']                       = 0;
      $arr_order['is_confirm']                        = 0;
      $arr_order['total_retail_price']                = $arr_order_details['total_retail_price'];
      $arr_order['total_wholesale_price']             = $arr_order_details['total_wholesale_price']; 
      $arr_order['shipping_addr']                     = $arr_order_details['shipping_addr'];    
      $arr_order['billing_addr']                      = $arr_order_details['billing_addr'];    
      $arr_order['billing_addr_zip_code']             = $arr_order_details['billing_addr_zip_code'];    
      $arr_order['payment_term']                      = $arr_order_details['payment_term'];    
      $arr_order['shipping_addr_zip_code']            = $arr_order_details['shipping_addr_zip_code'];    
      $arr_order['is_direct_payment']                 = $arr_order_details['is_direct_payment'];    
      $arr_order['is_direct_payment']                 = get_maker_payment_term($arr_order_details['maker_id']);
      
      $store_order = $this->RetailerQuotesModel->create($arr_order);
    
      if (isset($arr_order_details['quotes_details']) && count($arr_order_details['quotes_details']) > 0) { 

        foreach ($arr_order_details['quotes_details'] as $key => $product) {

          $product_details['order_no']                = $order_no;
          $product_details['retailer_quotes_id']      = $store_order->id;
          $product_details['product_id']              = $product['product_id'];
          $product_details['sku_no']                  = $product['sku_no'];
          $product_details['retail_price']            = $product['retail_price'];
          $product_details['unit_wholsale_price']     = $product['unit_wholsale_price'];
          $product_details['wholesale_price']         = $product['wholesale_price'];
          $product_details['qty']                     = $product['qty'];
          $product_details['description']             = $product['description'];
          $product_details['product_discount']        = $product['product_discount'];
          $product_details['shipping_discount']        = $product['shipping_discount'];
          $product_details['shipping_charge']        = $product['shipping_charge'];

          
          $store_order_details = $this->RetailerQuotesProductModel->create($product_details);


         
        }//Foreach
       }//order details 

         if ($store_order_details) {
          $data['order_no']     =  $order_no;

         /* Get Admin Id and send notification to admin after order placing*/
          $admin_id = get_admin_id();
          $order_id = $this->RetailerQuotesModel->select('id')->where('order_no',$order_no)->first()->id;
          $view_href     =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_id);


          $first_name = isset($user->first_name)?$user->first_name:"";
          $last_name  = isset($user->last_name)?$user->last_name:"";  

          $arr_event                 = [];
          $arr_event['from_user_id'] = $user->id;
          $arr_event['to_user_id']   = $admin_id;

          $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;

          $arr_event['title']        = 'New Order';
          $arr_event['type']         = 'admin'; 
          $arr_event['link']         = $view_href; 


          $this->save_notification($arr_event);

          /******************Notification to Vendor START*******************************/

          $first_name = isset($user->first_name)?$user->first_name:"";
          $last_name  = isset($user->last_name)?$user->last_name:"";  

          $order_view_link = url('/').'/vendor/retailer_orders/view/'.base64_encode($order_id);

          $arr_event                 = [];
          $arr_event['from_user_id'] = $user->id;
          $arr_event['to_user_id']   = $maker_id ;

          $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;

          $arr_event['title']        = 'New Order';
          $arr_event['type']         = 'maker'; 
          $arr_event['link']         = $order_view_link; 

          $this->save_notification($arr_event);

          $response['status']   = 'success';
          $response['message']  = 'Order has been created.';
          $response['data']     =  isset($data)?$data:''; 
          return $response;
        }

        else{

          $response['status']   = 'failure';
          $response['message']  = 'Something went wrong.';
          $response['data']     = '';
          return $response;
        } 
      }  
 
    }//try 

    catch(Exception $e)
    {
         $response = [
        'status'  => 'failure',
        'message' => 'Something went wrong.',
        'data'    => ''
      ];

        return $response;
    }

   }

   public function rep_sales_reorder($order_id="",$user = "")
   { 

    try{

      if($order_id!="")
      { 

        $arr_order_details      = $arr_address = $arr_order = $product_details = $arr_sku_no = [];
      
        $order_no               = str_pad('J2',  10, rand('1234567890',10)); 

        $order_details          = $this->RepresentativeLeadsModel->with('leads_details','address_details')->where('id',$order_id)->first();
       

        $order_data = $this->orderDataService->order_summary(base64_encode($order_details['order_no']),base64_encode($order_details['maker_id']));
       

      if ($order_details)
      {

        $arr_order_details = $order_details->toArray();
       
        /* get sku nos of order for getting product status*/
        if(isset($arr_order_details) && count($arr_order_details) >0 )
        {
          if(isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0)
          {
            $arr_sku_no = array_column($arr_order_details['leads_details'], 'sku');


            $active_products = $this->orderDataService->get_active_product($arr_sku_no);

            $sku_count = isset($arr_sku_no)?count($arr_sku_no):0;

            $arr_active_product_count = isset($active_products)?count($active_products):0;

            $deactive_products_count  = $sku_count - $arr_active_product_count;

            if($deactive_products_count > 0 && $deactive_products_count !== $sku_count)
            {
               $response['status']  = 'warning';
               $response['message'] = 'The order you are trying to place, has '.$deactive_products_count.' product(s) unavailable at the moment, would you still like to proceed ?.';
               $response['data']    = '';
               return $response;
            }

            if(isset($active_products) && count($active_products) == 0)
            {
              $response['status']   = 'Apologies';
              $response['message']  = 'None of the product(s) are available at the moment in this order.';
              $response['data']     = '';
               return $response;
            }
          }
        }
      }

      // Store address of order

      $shipping_address = $billing_address = "";
      if(isset($order_data['address_details']) && count($order_data['address_details']) > 0){
          

            if(isset($order_data['address_details']['ship_street_address']))
            {
              $shipping_address .= $order_data['address_details']['ship_street_address'];
            }

            if(isset($order_data['address_details']['ship_suit_apt']))
            {
              $shipping_address .= ', '.$order_data['address_details']['ship_suit_apt'];
            }

            if(isset($order_data['address_details']['ship_city']))
            {
              $shipping_address .= ', '.$order_data['address_details']['ship_city'];
            }

            if(isset($order_data['address_details']['ship_state']))
            {
              $shipping_address .= ', '.$order_data['address_details']['ship_state'];
            }

            if(isset($order_data['address_details']['ship_country']))
            {
              $shipping_address .= ', '.get_country($order_data['address_details']['ship_country']);
            }

            if(isset($order_data['address_details']['ship_zip_code']))
            {
              $shipping_address .= ', '.($order_data['address_details']['ship_zip_code']);
            }

            if(isset($order_data['address_details']['ship_mobile_no']))
            {
              $shipping_address .= ', Mobile.No: '.($order_data['address_details']['ship_mobile_no']);
            }


            /*For Billing address*/

            if(isset($order_data['address_details']['bill_street_address']))
            {
              $billing_address .= $order_data['address_details']['bill_street_address'];
            }

            if(isset($order_data['address_details']['bill_suit_apt']))
            {
              $billing_address .= ', '.$order_data['address_details']['bill_suit_apt'];
            }

            if(isset($order_data['address_details']['bill_city']))
            {
              $billing_address .= ', '.$order_data['address_details']['bill_city'];
            }

            if(isset($order_data['address_details']['bill_state']))
            {
              $billing_address .= ', '.$order_data['address_details']['bill_state'];
            }

            if(isset($order_data['address_details']['bill_country']))
            {
              $billing_address .= ', '.get_country($order_data['address_details']['bill_country']);
            }

            if(isset($order_data['address_details']['bill_zip_code']))
            {
              $billing_address .= ', '.($order_data['address_details']['bill_zip_code']);
            }

            if(isset($order_data['address_details']['bill_mobile_no']))
            {
              $billing_address .= ', Mobile.No: '.($order_data['address_details']['bill_mobile_no']);
            }

           
     }
    
      //store main order
        $arr_order['order_no']                          = $order_no;
        $arr_order['admin_commission']                  = isset($arr_order_details['maker_id'])?$this->CommissionService->get_admin_commission($arr_order_details['maker_id']):'';
        $maker_id                                       = isset($arr_order_details['maker_id']) ? $arr_order_details['maker_id'] : 0;
        $arr_order['maker_id']                          = $arr_order_details['maker_id'];
        $arr_order['retailer_id']                       = $arr_order_details['retailer_id'];
        $arr_order['ship_status']                       = 0;
        $arr_order['is_confirm']                        = 0;
        $arr_order['total_retail_price']                = $arr_order_details['total_retail_price'];
        $arr_order['total_wholesale_price']             = $arr_order_details['total_wholesale_price']; 
        $arr_order['shipping_addr']                     =  $shipping_address;    
        $arr_order['billing_addr']                      = $billing_address; 
        $arr_order['payment_term']                      = $arr_order_details['payment_term'];
        $arr_order['is_direct_payment']                 = $arr_order_details['is_direct_payment'];    
        $arr_order['is_direct_payment']                 = get_maker_payment_term($arr_order_details['maker_id']);
      
        $store_order = $this->RetailerQuotesModel->create($arr_order);
    
        if (isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0) { 

          foreach ($arr_order_details['leads_details'] as $key => $product) {

            $product_details['order_no']                = $order_no;
            $product_details['retailer_quotes_id']      = $store_order->id;
            $product_details['product_id']              = $product['product_id'];
            $product_details['sku_no']                     = $product['sku'];
            $product_details['retail_price']            = $product['retail_price'];
            $product_details['unit_wholsale_price']     = $product['unit_wholsale_price'];
            $product_details['wholesale_price']         = $product['wholesale_price'];
            $product_details['qty']                     = $product['qty'];
            $product_details['description']             = $product['description'];
            $product_details['product_discount']        = $product['product_discount'];
            $product_details['shipping_discount']       = $product['shipping_charges_discount'];
            $product_details['shipping_charge']         = $product['product_shipping_charge'];
            
            $store_order_details = $this->RetailerQuotesProductModel->create($product_details);
           
          }//Foreach
         }//order details 

         if (isset($store_order_details) && $store_order_details) {
          $data['order_no']     =  $order_no;

         /* Get Admin Id and send notification to admin after order placing*/
          $admin_id = get_admin_id();
          $order_id = $this->RetailerQuotesModel->select('id')->where('order_no',$order_no)->first()->id;
          $view_href     =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_id);


          $first_name = isset($user->first_name)?$user->first_name:"";
          $last_name  = isset($user->last_name)?$user->last_name:"";  

          $arr_event                 = [];
          $arr_event['from_user_id'] = $user->id;
          $arr_event['to_user_id']   = $admin_id;

          $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;

          $arr_event['title']        = 'New Order';
          $arr_event['type']         = 'admin'; 
          $arr_event['link']         = $view_href; 


          $this->save_notification($arr_event);

          /******************Notification to Vendor START*******************************/

          $first_name = isset($user->first_name)?$user->first_name:"";
          $last_name  = isset($user->last_name)?$user->last_name:"";  

          $order_view_link = url('/').'/vendor/retailer_orders/view/'.base64_encode($order_id);

          $arr_event                 = [];
          $arr_event['from_user_id'] = $user->id;
          $arr_event['to_user_id']   = $maker_id ;

          $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;

          $arr_event['title']        = 'New Order';
          $arr_event['type']         = 'maker'; 
          $arr_event['link']         = $order_view_link; 

          $this->save_notification($arr_event);

          $response['status']   = 'success';
          $response['message']  = 'Order has been created.';
          $response['data']     =  isset($data)?$data:''; 
          return $response;
        }

        else{

          $response['status']   = 'failure';
          $response['message']  = 'Something went wrong.';
          $response['data']     = '';
          return $response;
        } 
      }  
 
    }//try 

    catch(Exception $e)
    {
         $response = [
        'status'  => 'failure',
        'message' => 'Something went wrong.',
        'data'    => ''
      ];

        return $response;
    }

   }
   
    /************************Notification Event START**************************/

    public function save_notification($ARR_DATA = [])
    {  
        if(isset($ARR_DATA) && count($ARR_DATA)>0)
        {
            $ARR_EVENT_DATA                 = [];
            $ARR_EVENT_DATA['from_user_id'] = $ARR_DATA['from_user_id'];
            $ARR_EVENT_DATA['to_user_id']   = $ARR_DATA['to_user_id'];
            $ARR_EVENT_DATA['description']  = $ARR_DATA['description'];
            $ARR_EVENT_DATA['title']        = $ARR_DATA['title'];
            $ARR_EVENT_DATA['type']         = $ARR_DATA['type'];
            $ARR_EVENT_DATA['link']         = isset($ARR_DATA['link'])?$ARR_DATA['link']:'';

            $ARR_EVENT_DATA['status']       = isset($ARR_DATA['status'])?$ARR_DATA['status']:0; 

            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }
        return false;
    }


    public function summary($order_no=false,$enc_maker_id=false,$per_page=false)
    {

         try{
                   /*---update whole calculation vendor wise---*/
                   $result = $data = [];
                   $total_product_shipping_charges = 0;
                   $order_num = $order_no;
   
                   $order_product_details_arr = $this->RepresentativeProductLeadsModel ->where('order_no',$order_num)
                                                                                       ->get()
                                                                                       ->toArray();
                   
                   if(isset($order_product_details_arr) && count($order_product_details_arr)>0)
                   {
                       foreach ($order_product_details_arr as $key => $value) 
                       {
                         $result[$value['maker_id']][] = $value;
                       }
   
   
                       foreach($result as $key => $res) 
                       {
                           $i = 0;
   
                           $total_product_discount                  = array_sum((array_column($res,'product_discount')));
                           $total_shipping_charges                  = array_sum((array_column($res,'shipping_charges')));
                           $total_shipping_charges_discount         = array_sum((array_column($res,'shipping_charges_discount')));
                           $total_product_shipping_charges          = array_sum((array_column($res,'product_shipping_charge')));
                           $total_wholesale_price                   = array_sum((array_column($res,'wholesale_price')));
   
                           $data['total_product_discount']          = $total_product_discount;
                           $data['total_product_shipping_charges']  = $total_product_shipping_charges;
                           $data['total_shipping_charges']          = $total_shipping_charges;
                           $data['total_shipping_discount']         = $total_shipping_charges_discount;
   
                           $data['total_wholesale_price']           = $total_wholesale_price+$total_product_shipping_charges-$total_product_discount-$total_shipping_charges_discount;
   
                           $results = $this->RepresentativeLeadsModel->where('order_no',$order_num)->where('maker_id',$key)->update($data);
   
                           $data['total_product_shipping_charges'] += $res[$i]['product_shipping_charge'];
                          
                           $i++;
                       }
                   } 
   
                 $arr_data = $order_data =  $company_names  = [];
                 $order_no = isset($order_no)?$order_no:false;
                 $maker_id = isset($enc_maker_id)?$enc_maker_id:false;
                 $per_page = isset($per_page)?$per_page:false;
   
                 if(isset($order_no))
                 {
                      if(isset($maker_id) && $maker_id!='')
                      {
                         $obj_data = $this->RepresentativeLeadsModel->with(['maker_data.maker_details',
                                                                       'order_details.shop_settings',
                                                                       'order_details.maker_details',
                                                                       'order_details.get_product_min_qty',
                                                                       'order_details',
                                                                       'order_details.product_details.brand_details',
                                                                       'address_details','transaction_mapping',
                                                                       'representative_user_details',
                                                                       'sales_manager_details',
                                                                       'transaction_mapping_details'
                                                                     ])
                                                              ->where('order_no',$order_no)
                                                              ->where('maker_id',$maker_id)->first();
                      }
                      else
                      {
                         $obj_data = $this->RepresentativeLeadsModel->select('id','order_no','is_confirm') 
                                                                      ->with(['maker_data.maker_details',
                                                                       'order_details.shop_settings'=>function($shop_settings){$shop_settings->select('first_order_minimum','maker_id');},
                                                                       'order_details.get_product_min_qty'=>function($product_min_qty){$product_min_qty->select('product_id','sku','image','image_thumb','product_min_qty');},
                                                                       'order_details.maker_details'=>function($maker_details){$maker_details->select('company_name','user_id');},
                                                                       'order_details.product_details'=>function($product_details){$product_details->select('id','product_name','unit_wholsale_price','retail_price','available_qty','shipping_charges','minimum_amount_off','shipping_type','product_discount','prodduct_dis_type','off_type_amount','product_dis_min_amt');},
                                                                       'address_details','transaction_mapping',
                                                                       'representative_user_details',
                                                                       'sales_manager_details'
                                                                     ])
                                                              ->where('order_no',$order_no)->first();
                                                             
                      }
                   
                   if($obj_data)
                   {
                     //$obj_data                     = $obj_data->paginate($per_page);
                     $data                           = $obj_data->toArray();



                     if(isset($data['order_details']) && !empty($data['order_details']))
                     {

                         foreach($data['order_details'] as $key => $val)
                         {
                             $company_names[]         = $val['maker_details']['company_name']; 
                         } 
   
                         $order_data['order_no']      = $val['order_no']; 
                         $order_data['is_confirm']    = $val['is_confirm']; 
                     }


                     $order_data['product_data'] = array();

                     $company_names       = array_unique($company_names);

                     $product_max_qty     = "";
                     $obj_product_max_qty = $this->SiteSettingModel->select('product_max_qty')->first();  

                     if($obj_product_max_qty)
                     {
                         $product_max_qty = $obj_product_max_qty->product_max_qty;
                     }

    
                   if(isset($company_names) && !empty($company_names))
                    { 
                     foreach($company_names as $comp_name)
                     {  
                         $temp_data = array();
                         $temp_data['company_name'] = $comp_name;
                         $temp_data['minimum_order_amount'] = '';
                         $temp_data['products']     =  array();

                         foreach($data['order_details'] as $key => $val)
                        {
                           if($comp_name==$val['maker_details']['company_name'])
                          {  
                             
                            $arr['product_id']            = isset($val['product_id'])?$val['product_id']:''; 
   
                            $arr['product_name']          = isset($val['product_details']['product_name'])?$val['product_details']['product_name']:'';
   
                            $arr['sku']                   = isset($val['sku'])?$val['sku']:''; 
   
                            $arr['qty']                   = isset($val['qty'])?$val['qty']:''; 

                            $arr['product_min_qty']       = isset($val['get_product_min_qty']['product_min_qty'])?$val['get_product_min_qty']['product_min_qty']:0;

                            $arr['product_max_qty']       = isset($product_max_qty)?$product_max_qty:0;
   
                            $arr['image']                 = isset($val['get_product_min_qty']['image'])?$this->CommonService->imagePathProduct($val['get_product_min_qty']['image'], 'product', 0):0;   
   
                            $arr['image_thumb']           = isset($val['get_product_min_qty']['image_thumb'])?$this->CommonService->imagePathProduct($val['get_product_min_qty']['image_thumb'], 'product', 0):0; 
   
                            $arr['unit_price']            = isset($val['product_details']['unit_wholsale_price'])?$val['product_details']['unit_wholsale_price']:'';
   
                            $arr['retail_price']          = isset($val['product_details']['retail_price'])?$val['product_details']['retail_price']:'';
   
                            $arr['shipping_charges']      = isset($val['product_shipping_charge'])?$val['product_shipping_charge']:'';
                            $arr['shipping_charges_discount']    = isset($val['shipping_charges_discount'])?$val['shipping_charges_discount']:'';
   
                            $arr['product_discount']      = isset($val['product_discount'])?$val['product_discount']:'';
   
                            $arr['product_dis_min_amt']   = isset($val['product_details']['product_dis_min_amt'])?$val['product_details']['product_dis_min_amt']:'';
   
                            $arr['minimum_amount_off']    = isset($val['product_details']['minimum_amount_off'])?$val['product_details']['minimum_amount_off']:'';
   
                           $temp_data['minimum_order_amount'] = isset($val['shop_settings']['first_order_minimum'])?$val['shop_settings']['first_order_minimum']:'';

                             
                            $arr['company_name']          = isset($val['maker_details']['company_name'])?$val['maker_details']['company_name']:'';  
   
                            $arr['shipping_type']          = isset($val['product_details']['shipping_type'])?$val['product_details']['shipping_type']:'';
                            $arr['product_discount_type']  = isset($val['product_details']['prodduct_dis_type'])?$val['product_details']['prodduct_dis_type']:'';
                            $arr['product_discount_value'] = isset($val['product_details']['product_discount'])?$val['product_details']['product_discount']:'';
                            $arr['shipping_discount_value']= isset($val['product_details']['off_type_amount'])?$val['product_details']['off_type_amount']:'';                  
                           
                            // $order_data['product_data'][$comp_name][] = $arr;  
                            
                            array_push($temp_data['products'],$arr);  

                            } 
                          } 
                           
                            array_push($order_data['product_data'],$temp_data);  
                       } 
                     }                   

                   }//if obj data
   
                   $response['status']      = 'success';
                   $response['message']     = 'Order summary get successfully.';
                   $response['data']        = isset($order_data)?$order_data:[] ;
   
                   return $response;
   
                 }//if order no
         }
   
         catch(Exception $e)
         {
               DB::rollback();
               
               $response['status']      = 'failure';
               $response['message']     = $e->getMessage();
               $response['data']        = '';
   
               return $response;
         }
      }

      public function update_product_qty($form_data=null)
      {
         try
         {
             $unit_price = $shipping_charges =  $shipping_discount  = $product_discount   = $product_dis_min_amt = $shipping_dis_min_amt = "";
   
            if(isset($form_data) && $form_data!=null)
            { 
   
             $order_no        = isset($form_data['order_no'])?$form_data['order_no']:false;
             $product_id      = $form_data['product_id'];
             $pro_sku_id      = $form_data['sku_num'];
             $qty             = isset($form_data['qty'])?$form_data['qty']:'';
   
             $arr_product     = get_product_details($product_id);
          
   
             $produt_retail_price  = isset($arr_product['retail_price'])?$arr_product['retail_price']:"";
             $produt_whosale_price = isset($arr_product['unit_wholsale_price'])?$arr_product['unit_wholsale_price']:"";
   
             $tot_wholesale = $tot_retail = 0;
             $tot_wholesale = (float)$produt_whosale_price * (float)$qty;
             $tot_retail    = (float)$produt_retail_price * (float)$qty;
   
             $pro_lead_data['qty']             = $qty;
             $pro_lead_data['wholesale_price'] = $tot_wholesale;
             $pro_lead_data['retail_price']    = $tot_retail;
   
   
             /*------------update data-----------------------*/
            
               //calculate shipping charges
   
               $shipping_type      = isset($arr_product['shipping_type'])?$arr_product['shipping_type']:0;
   
               $minimum_amount_off = isset($arr_product['minimum_amount_off'])?$arr_product['minimum_amount_off']:0;
   
               $off_type_amount    = isset($arr_product['off_type_amount'])?$arr_product['off_type_amount']:0;
   
               $shipping_charges   = isset($arr_product['shipping_charges'])?$arr_product['shipping_charges']:0;
                           
               $shipping_discount  = isset($arr_product['shipping_discount'])?$arr_product['shipping_discount']:0;
                      
               $shipping_arr       = $this->HelperService->calculate_shipping_discount($shipping_type,$tot_wholesale,$minimum_amount_off,$off_type_amount,$shipping_charges);
   
   
               //calculate product discount
   
               if($arr_product['prodduct_dis_type']!='')
               {
                   $product_dis_amount = $this->HelperService->calculate_product_discount($arr_product['prodduct_dis_type'],$arr_product['product_dis_min_amt'],$arr_product['product_discount'],$tot_wholesale);
               }
               else
               {
                   $product_dis_amount = 0;
               }
   
   
             /* $pro_lead_data['shipping_charges']          = isset($shipping_arr['shipping_charge'])?$shipping_arr['shipping_charge']:0;*/
          
             $shipping_charges                           = isset($shipping_arr['shipping_charge'])?$shipping_arr['shipping_charge']:0;
             $shipping_discount = isset($shipping_arr['shipping_discount'])?$shipping_arr['shipping_discount']:0;
             $pro_lead_data['shipping_charges']          = $shipping_charges-$shipping_discount;
              
             $pro_lead_data['shipping_charges_discount'] = isset($shipping_arr['shipping_discount'])?$shipping_arr['shipping_discount']:0;
   
             $pro_lead_data['product_shipping_charge']   = isset($shipping_arr['product_ship_charge'])?$shipping_arr['product_ship_charge']:0;
   
             $pro_lead_data['product_discount']          = $product_dis_amount;
   
   
             /*-----------------------------------------------*/
   
             $update_product_qty = $this->RepresentativeProductLeadsModel
                                         ->where('order_no',$order_no)
                                         ->where('product_id',$product_id)
                                         ->where('sku',$pro_sku_id)
                                         ->update($pro_lead_data);
   
   
             /*sum of total shipping charges ,total ship_discount, product discount and update it into representative  lead table*/
   
             $order_product_details = $this->RepresentativeProductLeadsModel ->where('order_no',$order_no)
                                                                             ->get()
                                                                             ->toArray();
      
             $total_product_discount = array_sum((array_column($order_product_details,'product_discount')));
             $total_shipping_charges = array_sum((array_column($order_product_details,'shipping_charges')));
             $total_shipping_charges_discount = array_sum((array_column($order_product_details,'shipping_charges_discount')));
   
             $total_product_shipping_charges = array_sum((array_column($order_product_details,'product_shipping_charge')));
   
             $ar_data['total_product_discount']          = $total_product_discount;
             $ar_data['total_product_shipping_charges']  = $total_product_shipping_charges;
             $ar_data['total_shipping_charges']          = $total_shipping_charges;
             $ar_data['total_shipping_discount']         = $total_shipping_charges_discount;
   
              $this->RepresentativeLeadsModel->where('order_no',$order_no)->update($ar_data);
   
   
             if($update_product_qty)
             {
               $maker_data         = $this->RepresentativeProductLeadsModel
                                         /* ->with(['product_details' => function($product_details) {
                                               $product_details->select('minimum_amount_off','product_dis_min_amt','id');
                                               }])*/
                                          ->where('product_id',$product_id)
                                          ->where('order_no',$order_no)
                                          ->first(['product_id','maker_id','unit_wholsale_price','product_shipping_charge','product_discount','shipping_charges_discount']);
   
   
               get_maker_total_amount($maker_data->maker_id,$order_no);
   
               //dd($maker_data->product_details);
   
   
              /* $unit_price        = $maker_data->unit_wholsale_price;
               $shipping_charges   = $maker_data->product_shipping_charge;
               $shipping_discount  = $maker_data->shipping_charges_discount;
               $product_discount   = $maker_data->product_discount;
               $product_dis_min_amt = $arr_product['product_dis_min_amt'];
               $shipping_dis_min_amt = $arr_product['minimum_amount_off'];*/
   
               //Get count for retail and wholesale...
               $total_pro_reatil = $total_pro_wholesale = 0;
   
               $total_pro_reatil = $this->RepresentativeProductLeadsModel
                                         ->where('order_no',$order_no)
                                         ->sum('retail_price');
   
               $total_pro_wholesale = $this->RepresentativeProductLeadsModel
                                         ->where('order_no',$order_no)
                                         ->sum('wholesale_price');
               
   
               $total_commission_wholesale = 0;
               $comission_less_wholesale   = 0;
               
               $commission                 = isset($form_data['comission'])?$form_data['comission']:0;   
               
               $total_commission_wholesale = ($commission / 100) * $total_pro_wholesale;
               
               //Comission less from total whole price..
               $comission_less_wholesale   =  $total_pro_wholesale - $total_commission_wholesale; 
                               
               $arr_data = [];
   
   
               $arr_data['order_no']                       = $order_no;
                
               $order_num                                  =  $arr_data['order_no'];
         
               $order_product_details_arr  = $this->RepresentativeProductLeadsModel->where('order_no',$order_num)
                                                                                ->get()
                                                                                ->toArray();
   
                                                                    
        
                 if(isset($order_product_details_arr) && count($order_product_details_arr)>0)
                 {
                   foreach ($order_product_details_arr as $key => $value) 
                   {
                     $result[$value['maker_id']][] = $value;
                   }
   
                   
   
                   foreach($result as $key => $res) 
                   {
                       $total_product_discount = array_sum((array_column($res,'product_discount')));
                       $total_shipping_charges = array_sum((array_column($res,'shipping_charges')));
                       $total_shipping_charges_discount = array_sum((array_column($res,'shipping_charges_discount')));
   
                       $total_product_shipping_charges  = array_sum((array_column($res,'product_shipping_charge')));
                        
                       $total_wholesale_price           = array_sum((array_column($res,'wholesale_price')));
   
                       $data['total_product_discount']  = $total_product_discount;
                       $data['total_product_shipping_charges']  = $total_product_shipping_charges;
                       $data['total_shipping_charges']  = $total_shipping_charges;
                       $data['total_shipping_discount'] = $total_shipping_charges_discount;
   
                       $data['total_wholesale_price']   = $total_wholesale_price+$total_product_shipping_charges-$total_product_discount-$total_shipping_charges_discount;
   
                       $this->RepresentativeLeadsModel->where('order_no',$order_num)->where('maker_id',$key)->update($data);
   
                   }
   
                }
   
                $arr_data['total_product_discount']   = $ar_data['total_product_discount'];
                $arr_data['total_shipping_charges']   = $ar_data['total_product_shipping_charges'];
                $arr_data['total_shipping_discount']  = $ar_data['total_shipping_discount'];
                $arr_data['wholesale_sub_total']      = $comission_less_wholesale; 
              /* if($shipping_discount != "0.000000" || $product_discount != "0.000000")
               { 
                 $arr_data['total']                   = $data['total_wholesale_price'];
               }*/
   
               DB::commit();
               $response['status']         = 'success';               
               $response['message']        = 'Quantity updated successfully';    
               $response['data']           = isset($arr_data)?$arr_data:[];
   
   
               return $response;
             } 
           }
         } 
         catch(Exception $e)
         {
             DB::rollback();
             
             $response['status']      = 'failure';
             $response['message']     = $e->getMessage();
             $response['data']        = "";  
   
             return $response;
         }
       }
    
     public function remove_from_bag($order_no="",$sku_no="")
     {
          $response = [];

          $order_data = $this->orderDataService->delete_product_from_bucket(base64_encode($order_no),base64_encode($sku_no));

          if(isset($order_data['status']) && $order_data['status'] == 'SUCCESS')
          {
            $response['status']  = 'success';
            $response['message'] = 'Product has been deleted from cart.';
            return $response;
          }
          else
          {
            $response['status']  = 'failure';
            $response['message'] = 'Something went wrong, while delete product from bag.';
            return $response;
          }


         $product_lead_arr = $retail_price       = $wholesale_price       = [];         
         $lead_id          = $total_retail_price = $total_wholesale_price = 0;
         $maker_id         = "";
   
         try
         {
   
           if($order_no!="" && $sku_no!="")
           {  
   
            $product_lead_obj = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)
                                                                     ->where('sku',$sku_no)
                                                                     ->first();
          if($product_lead_obj)
          {
            $product_lead_arr = $product_lead_obj->toArray();
            $lead_id          = $product_lead_arr['representative_leads_id'];
   
            //check is lead confirm or not
            $lead_obj = $this->RepresentativeLeadsModel->where('id',$lead_id)->first();
   
            if($lead_obj)
            {
              if($lead_obj->is_confirm == 1)
              { 
                 $response['status']  = 'failure';
                 $response['message'] = 'This product is from confirm order,You can not delete this product.';
                 $response['data']    = '';
   
                return $response;
              }
             }
   
             $maker_id = isset($product_lead_arr['maker_id'])?$product_lead_arr['maker_id']:"0";
           }
           
           DB::beginTransaction();
   
           $is_delete = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)
                                                              ->where('sku',$sku_no)
                                                              ->delete();
   
                                                                
           if($is_delete)
           {
   
             //------
             $is_count = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)
                                                               ->where('maker_id',$maker_id)
                                                               ->count();
   
             if($is_count==0)
             {
               /*$update_maker_id = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                                 ->where('maker_id',$maker_id)
                                                                 ->update(['maker_id'=>0]);*/
   
              /* $update_maker_id = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                                 ->where('maker_id',$maker_id)
                                                                 ->delete();*/                                                  
             }
             /*after delete product sum of total shipping charges ,total ship_discount, product discount and update it into representative  lead table*/
   
             $order_product_details = $this->RepresentativeProductLeadsModel ->where('order_no',$order_no)
                                                                             ->get()
                                                                             ->toArray();
   
             if(isset($order_product_details) && count($order_product_details)>0)
             {
                 $total_product_discount = array_sum((array_column($order_product_details,'product_discount')));
                 $total_shipping_charges = array_sum((array_column($order_product_details,'shipping_charges')));
                 $total_shipping_charges_discount = array_sum((array_column($order_product_details,'shipping_charges_discount')));
   
                 $total_product_shipping_charges = array_sum((array_column($order_product_details,'product_shipping_charge')));
   
                 $data['total_product_discount']          = $total_product_discount;
                 $data['total_product_shipping_charges']  = $total_product_shipping_charges;
                 $data['total_shipping_charges']          = $total_shipping_charges;
                 $data['total_shipping_discount']         = $total_shipping_charges_discount;
   
                 $this->RepresentativeLeadsModel->where('order_no',$order_no)->update($data);
   
             }
             else
             {
                 $data['total_product_discount']          = 0;
                 $data['total_product_shipping_charges']  = 0;
                 $data['total_shipping_charges']          = 0;
                 $data['total_shipping_discount']         = 0;
   
                 $this->RepresentativeLeadsModel->where('order_no',$order_no)->update($data);
             }
          
             
             /*----------------------------------------------------------------------------*/
   
             get_maker_total_amount($lead_obj->maker_id,$order_no);
             DB::commit();
   
             $order_details = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();
         
              if(isset($order_details))
              {
                 $order_arr = $order_details->toArray();
               
                 $total_wholesale_price = $order_details['total_wholesale_price']+$order_details['total_product_shipping_charges']-$order_details['total_product_discount']-$order_details['total_shipping_discount'];
   
                 $this->RepresentativeLeadsModel->where('order_no',$order_no)->update(['total_wholesale_price'=>$total_wholesale_price]);
              }
             
             $response['status']      = 'success';
             $response['message']     = 'Product has been deleted from cart.';
             $response['data']        = '';
             return $response;
           }
           else
           {
             DB::rollback();
             $response['status']      = 'failure';
             $response['message']     = 'Error occurred while deleting product.';
             $response['data']        = '';
             return $response;
           }
          }
          
          else
          {
   
             $response['status']      = 'failure';
             $response['message']     = 'Error occurred while deleting product.';
             $response['data']        = '';
             return $response;
          } 
             
         }catch(Exception $e)
         {
   
           $response['status']      = 'failure';
           $response['message']     = $e->getMessage();
           $response['data']        = '';
           return $response; 
         }
     }

     public function apply_promo_code($form_data)
     {
        
        $promo_code_id = $vendor_id = $user_id = $promotion_id = 0;

        $from_date = $todate = '';

        $is_action_type = isset($form_data['action_type'])?$form_data['action_type']:false;

        $final_total = $promotion_arr = $promotion_array = [];

        $promo_code   = $form_data['promo_code'];
        $total_amount = $form_data['total_amt'];
        $vendor_id    = $form_data['maker_id'];

        $total_shipping_charges = $total_shiping_discount = $total_product_discount = $final_total = $sub_total = 0;

        $current_date = date('Y-m-d');

        $user = $form_data['auth_user'];

        if ($user)
        {
          $user_id = $user->id;
        }

        if ($promo_code == "")
        {
          $response['status']      = 'error';
          $response['message'] = 'Please enter promo code.';
          return $response;
        }


        //check promocode is present or not
            
            $promocode_exists = $this->PromoCodeModel
                                 ->where('promo_code_name',$promo_code)
                                 ->count();

        
        // get  promo code details from that promo code

        $promo_code_details = $this->PromoCodeModel
                                   ->where('promo_code_name', $promo_code)
                                   ->where('vendor_id',$vendor_id)
                                   ->first();

        if($promocode_exists > 0)
        {

          if (isset($promo_code_details) && $promo_code_details['vendor_id'] == $vendor_id)
          {
            $promo_code_id = $promo_code_details->id;

            //get promotions from promo code id

            $promotion_details = $this->PromotionsModel
                                      ->where('promo_code', $promo_code_id)
                                      ->first();

            if (isset($promotion_details))
            {
              $vendor_id = $promotion_details->maker_id;
              $from_date = $promotion_details->from_date;
              $todate = $promotion_details->to_date;
              $promotion_id = $promotion_details->id;
            }

            
            //check date of that promo code
            if ($todate >= $current_date)
            {
              //check this promo code is already used or not for that logged in user
              $count = 0;
              $count = $this->PromoCodeRetailerMappingModel
                            ->where('retailer_id', $user_id)
                            ->where('promo_code_id', $promo_code_id)
                            ->count();


              if ($count > 0)
              {
                $response['status']      = 'error';
                $response['message'] = 'This promo code is already used.';
                return $response;
              }
              else
              {
                $final_total = [];
                //get all promotion type of that promotion
                $promotion_offers_arr = $this->PromotionsOffersModel
                              ->with(['get_prmotion_type'])
                              ->where('promotion_id', $promotion_id)
                              ->get()
                              ->toArray();

                if (isset($promotion_offers_arr) && count($promotion_offers_arr) > 0)
                {

                  foreach ($promotion_offers_arr as $key => $offers_types)
                  {
                    if ($total_amount >= $offers_types['minimum_ammount'])
                    {
                      $promotion_type = '';
                      $promotion_type = $offers_types['get_prmotion_type']['promotion_type_name'];

                      if ($promotion_type == '% Off')
                      {
                        $discount         = $offers_types['discount'];
                        $get_discount_amt = ($discount * $total_amount / 100);

                        $get_discount_amt = isset($get_discount_amt) ? num_format($get_discount_amt) : '';

                        $final_total[$vendor_id]['total_wholesale_price'] = $total_amount - $get_discount_amt;
                        $final_total[$vendor_id]['discount_amt'] = $get_discount_amt;
                        $final_total[$vendor_id]['discount_percent'] = $discount;

                      }
                      if ($promotion_type == 'Free Shipping')
                      {

                        $final_total[$vendor_id]['shipping_charges'] = 0;
                      }

                    }
                    else
                    {
                      $response['status']      = 'failure';
                      $response['message'] = 'Promo code is not applicable for this order.';
                      return $response;
                    }
                  }

                  $response['status']  = 'success';
                  $response['message'] = 'Promo code has been applied.';
                  $response['data']    = $final_total;

                  $exisiting_promotion_data = Session::get('promotion_data');
                  $exisiting_promotion_data = is_array($exisiting_promotion_data) ? $exisiting_promotion_data : [];

                  $exisiting_promotion_data[$vendor_id]['final_total'] = $final_total;
                  $exisiting_promotion_data[$vendor_id]['promo_code'] = $promo_code;
                  $exisiting_promotion_data[$vendor_id]['promo_codeId'] = $promo_code_id;

                  Session::put('promotion_data', $exisiting_promotion_data);

                  if($is_action_type == 'clear_promo_code')
                  {
                    return $this->clear_promo_code($vendor_id);
                  }
                  
                  return $response;
                }

              }
            }
            else
            {
              $response['status']      = 'failure';
              $response['message'] = 'This promo code has been expired.';
              return $response;
            }

          } else {

            $response['status']      = 'failure';
            $response['message'] = 'Promo code is not applicable for this vendor.';
            return $response;

          }

        } else {
          $response['status']      = 'failure';
          $response['message'] = 'Invalid promo code.';
          return $response;
        }
     }

     public function clear_promo_code($maker_id=false) 
     {
        if ($maker_id) {

          $getsessiondata = is_array(Session::get('promotion_data')) ? Session::get('promotion_data') : [];
          
          if (sizeof($getsessiondata) > 0) {
            $products = session()->pull('promotion_data', []); // Second argument is a default value

            if (($key = array_search($getsessiondata[$maker_id], $products)) !== false) {

              unset($getsessiondata[$key]);

              Session::put('promotion_data', $getsessiondata);

              $response['status'] = 'success';
              $response['message'] = 'Promotion code has been removed.';
              return $response;
            }

          } else {
            $response['status'] = 'failure';
            $response['message'] = 'Something went wrong, please try again.';
            return $response;
          }

        } else {
          $response['status'] = 'failure';
          $response['message'] = 'Something went wrong, please try again.';
          return $response;
        }
     }

     public function get_order_calculation_data($form_data)
     {
      // dd($form_data);
      $order_no = $form_data['order_no'];
      $maker_id = $form_data['maker_id'];
      $segment  = $form_data['segment'];

      try 
      {
        $orderCalculationData = $this->HelperService->get_order_calculation_data(base64_encode($order_no),base64_encode($maker_id),$segment);

        $response['status'] = 'success';
        $response['message'] = 'Order calculation get successfully.';
        $response['data'] = $orderCalculationData;
        return $response;

      } 
      catch (Exception $e) 
      {
        $response['status']  = 'failure';
        $response['message'] = 'Something went wrong, while get order calculations..';
        $response['data']    = [];
        return $response;
      }
      
     }
   
 }  
   
   ?>