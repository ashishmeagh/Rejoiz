<?php
   namespace App\Common\Services\Api\Representative;
   
   use Illuminate\Http\Request;
   use App\Http\Controllers\Controller;
   use App\Models\UserModel;
   use App\Models\ProductsModel;
   use App\Models\AddressModel;
   use App\Models\RepresentativeLeadsModel;
   use App\Models\RoleModel;
   use App\Models\TempBagModel;
   use App\Models\MakerModel;
   use App\Models\RoleUsersModel;
   use App\Models\ProductDetailsModel;
   use App\Models\RepresentativeProductLeadsModel;
   use App\Models\TransactionMappingModel;
   use App\Models\RetailerRepresentativeMappingModel;
   use App\Models\RepresentativeMakersModel;  
   use App\Models\RetailerModel;  
   use App\Models\SubCategoryModel;  
   use App\Models\CountryModel;
   use App\Models\CategoryModel;
   use App\Models\ProductsSubCategoriesModel;
   use App\Models\RepresentativeModel;
   use App\Models\StateZipCodeModel;
   use App\Models\StripeTransactionModel;
   use App\Models\VendorRepresentativeMappingModel;
   use App\Models\CustomerQuotesModel;
   use App\Models\RetailerQuotesModel;
   use App\Models\SiteSettingModel;

   use App\Events\ActivityLogEvent;
   
   
   use App\Common\Services\GeneralService;
   use App\Common\Services\RepsEmailService;
   use App\Events\NotificationEvent;
   use App\Common\Services\orderDataService;
   use App\Common\Services\ProductService;
   use App\Common\Services\Api\Common\CommonService;
   use App\Common\Services\HelperService;
   use App\Common\Services\CommissionService;
   
   use Illuminate\Pagination\Paginator;
   use Illuminate\Pagination\LengthAwarePaginator;
   
   
   use \paginate;
   use Sentinel;
   use Validator;
   use DB;
   use Datatables;
   use Flash;
   use Session;
   use DateTime;

   
   class OrdersService {
   
   	public function __construct(
   							    ProductsModel $ProductsModel,
   	                            UserModel $UserModel,
   	                            TempBagModel $TempBagModel,
   	                            MakerModel $MakerModel,
   	                            RoleModel $RoleModel,
   	                            ProductDetailsModel $ProductDetailsModel,
   	                            RoleUsersModel $RoleUsersModel,
   	                            AddressModel $AddressModel,
   	                            RepresentativeLeadsModel $RepresentativeLeadsModel,
   	                            TransactionMappingModel $TransactionMappingModel,
   	                            RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
   	                            GeneralService $GeneralService,
   	                            RepsEmailService $RepsEmailService,
                                 CommonService $CommonService,
   	                            RetailerRepresentativeMappingModel $RetailerRepresentativeMappingModel,
   	                            RepresentativeMakersModel $RepresentativeMakersModel,
   	                            RetailerModel $RetailerModel,
   	                            CountryModel $CountryModel,
   	                            orderDataService $orderDataService,
   	                            ProductService $ProductService,
   	                            SubCategoryModel $SubCategoryModel,
   	                            ProductsSubCategoriesModel $ProductsSubCategoriesModel,
   	                            CategoryModel $CategoryModel,
   	                            RepresentativeModel $RepresentativeModel,
   	                            StateZipCodeModel $StateZipCodeModel,
   	                            StripeTransactionModel $StripeTransactionModel,
   	                            VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                SiteSettingModel $SiteSettingModel,
                                HelperService $HelperService,  
                                CommissionService $CommissionService 
   								) 
   	{
   		  $this->AddressModel                      = $AddressModel;
   	    $this->UserModel                         = $UserModel;
   	    $this->TempBagModel                      = $TempBagModel;
   	    $this->RoleModel                         = $RoleModel;
   	    $this->MakerModel                        = $MakerModel;
   	    $this->RoleUsersModel                    = $RoleUsersModel;
   	    $this->ProductDetailsModel               = $ProductDetailsModel;
   	    $this->RepresentativeLeadsModel          = $RepresentativeLeadsModel;
   	    $this->RepresentativeProductLeadsModel   = $RepresentativeProductLeadsModel;
   	    $this->ProductsModel                     = $ProductsModel;
   	    $this->TransactionMappingModel           = $TransactionMappingModel;
   	    $this->RetailerRepresentativeMappingModel= $RetailerRepresentativeMappingModel;
   	    $this->GeneralService                    = $GeneralService;
   	    $this->RepsEmailService                  = $RepsEmailService;
   	    $this->RetailerModel                     = $RetailerModel;
   	    $this->CountryModel                      = $CountryModel;
   	    $this->CategoryModel                     = $CategoryModel;
   	    $this->orderDataService                  = $orderDataService;
   	    $this->ProductService                    = $ProductService;
         $this->CommonService                     = $CommonService;
   	    $this->SubCategoryModel                  = $SubCategoryModel;
   	    $this->ProductsSubCategoriesModel        = $ProductsSubCategoriesModel;
   	    $this->RepresentativeMakersModel         = $RepresentativeMakersModel;
   	    $this->RepresentativeModel               = $RepresentativeModel;
   	    $this->StateZipCodeModel                 = $StateZipCodeModel;
   	    $this->StripeTransactionModel            = $StripeTransactionModel;
   	    $this->VendorRepresentativeMappingModel  = $VendorRepresentativeMappingModel;
        $this->CustomerQuotesModel               = $CustomerQuotesModel;
        $this->RetailerQuotesModel               = $RetailerQuotesModel;
        $this->SiteSettingModel                  = $SiteSettingModel;
        $this->HelperService                     = $HelperService;
        $this->CommissionService                 = $CommissionService;

   	}
   
   	public function listing($user_id=null , $request_data=null) {
   
         try
         {
   
   
         $per_page   = isset($request_data['per_page'])?$request_data['per_page']:10;	
         $page       = isset($request_data['page'])?$request_data['page']:1;  
         $order_type = isset($request_data['order_type'])?$request_data['order_type']:'';
   
         $requestData   =  $request_data;

            if(isset($requestData['order_type']) && $requestData['order_type']=='cancelled')
            {
                $leads_table          = $this->RepresentativeLeadsModel->getTable();        
                $prefixed_leads_table = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

                $transaction_mapping_table = $this->TransactionMappingModel->getTable();
                $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$transaction_mapping_table;

                $user_tbl_name              = $this->UserModel->getTable();
                $prefixed_user_tbl          = DB::getTablePrefix().$this->UserModel->getTable();

                $retailer_table             = $this->RetailerModel->getTable();
                $prefix_retailer_table      = DB::getTablePrefix().$retailer_table;

               /* $maker_tbl                = $this->MakerModel->getTable();        
                $prefixed_maker_tbl         = DB::getTablePrefix().$this->MakerModel->getTable();*/

                 $maker_table               = $this->MakerModel->getTable();
                 $prefix_maker_table        = DB::getTablePrefix().$maker_table;

                $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
                $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;


                $lead_obj = DB::table($leads_table)
                                        ->select(DB::raw($prefixed_leads_table.".*,".
                                                        $prefixed_transaction_mapping_tbl.".id as tid,".
                                                         $prefixed_transaction_mapping_tbl.".transaction_status,".

                                                        $prefix_maker_table.".brand_name,".
                                                        $prefix_maker_table.".company_name as comp_name,".

                                                        $prefix_retailer_table.'.store_name,'.
                                                  "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                         .$prefixed_user_tbl.".last_name) as user_name"))

                                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_leads_table.'.retailer_id')

                                        ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefixed_leads_table.'.retailer_id')

                                    ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefix_representative_leads_tbl,$prefixed_transaction_mapping_tbl){

                                      $join->on($prefix_representative_leads_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                      ->on($prefix_representative_leads_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');

                                    })


                                        ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefixed_leads_table.'.maker_id')

                                        ->where($prefixed_leads_table.'.order_cancel_status','=',2)

                                        ->where($prefixed_leads_table.'.representative_id',$user_id)

                                        ->orderBy($prefixed_leads_table.".id",'DESC');
            }


            else
            {  
                      $user_table        =  $this->UserModel->getTable();
                      $prefix_user_table = DB::getTablePrefix().$user_table;

                      $role_table        =  $this->RoleModel->getTable();
                      $prefix_role_table = DB::getTablePrefix().$role_table;

                      $role_user_table        =  $this->RoleUsersModel->getTable();
                      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

                      $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
                      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

                      $retailer_table        = $this->RetailerModel->getTable();
                      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

                      $maker_table           = $this->MakerModel->getTable();
                      $prefix_maker_table    = DB::getTablePrefix().$maker_table;

                      $transaction_mapping        = $this->TransactionMappingModel->getTable();
                      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

                   
                      $retailer_rep_mapping_tbl = $this->RetailerRepresentativeMappingModel->getTable();
                      $prefix_retailer_rep_mapping_tnl = DB::getTablePrefix().$retailer_rep_mapping_tbl;
                      

                      $representative_product_leads =  $this->RepresentativeProductLeadsModel->getTable();
                      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

                      $stripe_transaction        = $this->StripeTransactionModel->getTable();
                      $prefix_stripe_transaction = DB::getTablePrefix().$stripe_transaction;

                      $lead_obj = DB::table($representative_leads)
                                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                                              $prefix_maker_table.'.company_name as comp_name,'.
                                              $prefix_retailer_table.'.store_name,'.

                                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                                              "CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name"
                                               // "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                                              ))
             

                          ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                          ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_user_table." AS RL","RL.id",'=',$prefix_representative_leads_tbl.'.retailer_id')
                     
                           ->join($retailer_rep_mapping_tbl." AS REP_MAP1",'REP_MAP1.representative_id','=',$prefix_representative_leads_tbl.".representative_id")

                           ->join($retailer_rep_mapping_tbl." AS REP_MAP2","REP_MAP2.retailer_id","=",$prefix_representative_leads_tbl.'.retailer_id')

                      
                           ->join($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.is_confirm','!=',0)

                           ->where($representative_leads.'.is_confirm','!=',4)

                           ->where($representative_leads.'.maker_id','!=',0)

                           ->where($representative_leads.'.representative_id','!=',0)
                       
                           ->where($prefix_representative_leads_tbl.'.representative_id','=',$user_id)

                           ->where('REP_MAP2.representative_id','!=',0);


                          
                          if(isset($confirmed_flag) && $confirmed_flag==1)
                          {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                          }

                          if(isset($requestData['order_type']) && $requestData['order_type']=='pending')
                          {  
                             
                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               // return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                         //   ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                              return $query->where('is_split_order','=','0')
                                           ->where('ship_status','=',0)
                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3')
                                           //->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30');
                                           ->where(function($q) use($prefix_representative_leads_tbl){
                                                return $q->orwhere($prefix_representative_leads_tbl.'.payment_term','!=','Net30')
                                                         ->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30 - Online/Credit')
                                                         ->orwhereNULL('payment_term');
                                                });
                            });

                             $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);

                            });

                            $lead_obj = $lead_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                                         
                              });
                            
                            
                          }  

                          if(isset($requestData['order_type']) && $requestData['order_type']=='completed')
                          {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                               
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)

                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)
                                                    
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                                    
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1)

                                //->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30');
                                ->where(function($q) use($representative_leads){
                                                    return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                             ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                                  });
                          }

                           if(isset($requestData['order_type']) && $requestData['order_type']=='net_30_pending')
                          {


                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               // return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                         //   ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                              return $query->where('is_split_order','=','0')
                                           //->where('ship_status','=',0)

                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3');
                                           //->where($prefix_representative_leads_tbl.'.payment_term','=','Net30');;
                            });

                             $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);
                            });
                               // $lead_obj = $lead_obj->where(function($q){
                               //          return $q->orwhere('is_payment_status','=','0');
                               //        });

                             $lead_obj = $lead_obj->where(function($q){
                                      return $q->orwhere('ship_status','=','0')
                                               ->orwhere('ship_status','=','1');
                                    });

                             $lead_obj = $lead_obj->where(function($q){
                                    return $q->orwhere('payment_term','=','Net30')
                                             ->orwhere('payment_term','=','Net30 - Online/Credit');
                                  });

                            $lead_obj = $lead_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                              });

                               
                          }

                          if(isset($requestData['order_type']) && $requestData['order_type']=='net_30_completed')
                          {
                              $lead_obj = $lead_obj
                                //->where($prefix_representative_leads_tbl.'.payment_term','=','Net30')
                                ->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1)

                                // ->where(function($q) use($representative_leads){
                                //   return $q->orwhere($representative_leads.'.is_payment_status','=','0')
                                //            ->orwhere($representative_leads.'.is_payment_status','=','1');
                                // })
                                
                                ->where(function($q){
                                      return $q->orwhere('payment_term','=','Net30')
                                               ->orwhere('payment_term','=','Net30 - Online/Credit');
                                    }); 
                          }

                          if(isset($approved_flag) && $approved_flag==1)
                          {
                            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                                            
                                  ->where($prefix_representative_leads_tbl.'.order_cancel_status','!=',2);
                          }




    $rep_sales_order_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_maker_table.'.company_name as comp_name,'.
                              $prefix_retailer_table.'.store_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                              "CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name"
                               // "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                              ))
             

                          ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                          ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_user_table." AS RL","RL.id",'=',$prefix_representative_leads_tbl.'.retailer_id')
                     
                           ->join($retailer_rep_mapping_tbl." AS REP_MAP1",'REP_MAP1.representative_id','=',$prefix_representative_leads_tbl.".representative_id")

                           ->join($retailer_rep_mapping_tbl." AS REP_MAP2","REP_MAP2.retailer_id","=",$prefix_representative_leads_tbl.'.retailer_id')

                      
                           ->join($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')

                        
                           ->orderBy($prefix_representative_leads_tbl.'.created_at',"DESC")
                           
                           //->groupBy($prefix_representative_leads_tbl.'.id')

                          ->groupBy($prefix_representative_leads_tbl.'.order_no')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)
 
                           ->where($representative_leads.'.is_confirm','=',4)

                           ->where($representative_leads.'.maker_id','!=',0)


                           ->where($representative_leads.'.representative_id','!=',0)
                       
                           ->where($prefix_representative_leads_tbl.'.representative_id','=',$user_id)

                           ->where('REP_MAP2.representative_id','!=',0);



                          
                          if(isset($confirmed_flag) && $confirmed_flag==1)
                          {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                          }

                          if(isset($requestData['order_type']) && $requestData['order_type']=='pending')
                          {  
                             
                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               
                              return $query->where('is_split_order','=','0')
                                           ->where('ship_status','=',0)
                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3')
                                           //->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30');
                                           ->where(function($q) use($prefix_representative_leads_tbl){
                                                return $q->orwhere($prefix_representative_leads_tbl.'.payment_term','!=','Net30')
                                                         ->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30 - Online/Credit')
                                                         ->orwhereNULL('payment_term');
                                                });
                            });

                             $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);

                            });

                            $lead_obj = $lead_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                                         
                              });
                  
                            
                          }  

                          if(isset($requestData['order_type']) && $requestData['order_type']=='completed')
                          {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                               
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)

                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)
                                                    
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                                    
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1)

                                //->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30');
                                ->where(function($q) use($representative_leads){
                                                    return $q->orwhere($representative_leads.'.payment_term','!=','Net30')
                                                             ->where($representative_leads.'.payment_term','!=','Net30 - Online/Credit');
                                                  });
                          }

                          if(isset($requestData['order_type']) && $requestData['order_type']=='net_30_pending')
                          {


                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               // return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                         //   ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                              return $query->where('is_split_order','=','0')
                                           //->where('ship_status','=',0)
                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3');
                                           //->where($prefix_representative_leads_tbl.'.payment_term','=','Net30');;
                            });

                             $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);
                            });
                               // $lead_obj = $lead_obj->where(function($q){
                               //          return $q->orwhere('is_payment_status','=','0');
                               //        });

                             $lead_obj = $lead_obj->where(function($q){
                                      return $q->orwhere('ship_status','=','0')
                                               ->orwhere('ship_status','=','1');
                                    });

                             $lead_obj = $lead_obj->where(function($q){
                                    return $q->orwhere('payment_term','=','Net30')
                                             ->orwhere('payment_term','=','Net30 - Online/Credit');
                                  });

                            $lead_obj = $lead_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                              });

                               
                          }

                          if(isset($requestData['order_type']) && $requestData['order_type']=='net_30_completed')
                          {
                              $lead_obj = $lead_obj
                                //->where($prefix_representative_leads_tbl.'.payment_term','=','Net30')
                                ->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1)

                                ->where(function($q){
                                      return $q->orwhere('payment_term','=','Net30')
                                               ->orwhere('payment_term','=','Net30 - Online/Credit');
                                    }); ;
                          }

                          if(isset($approved_flag) && $approved_flag==1)
                          {
                            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                                            
                                  ->where($prefix_representative_leads_tbl.'.order_cancel_status','!=',2);
                          }
                   }                   



                                // ---------------- Filtering Logic ----------------------------------

                          if(empty($requestData['order_type']) || (isset($requestData['order_type']) && $requestData['order_type']!="cancelled"))
                        {    

                          if(isset($requestData['filter']['general_search']) && $requestData['filter']['general_search']!="")
                          {
                              $search_term      = $requestData['filter']['general_search'];


                                $lead_obj = $lead_obj->whereRaw(
                                                    "(  `".$prefix_representative_leads_tbl."`.`order_no` LIKE '%".$search_term."%' OR
                                                        `".$prefix_retailer_table."`.`store_name` LIKE '%".$search_term."%' OR
                                                        `".$prefix_maker_table."`.`company_name` LIKE '%".$search_term."%' OR
                                                        `".$prefix_representative_leads_tbl."`.`total_retail_price` LIKE '%".$search_term."%' OR
                                                        `total_wholesale_price` LIKE '%".$search_term."%' )"
                                                   );

                                $rep_sales_order_obj = $rep_sales_order_obj->whereRaw(
                                                    "( `".$prefix_representative_leads_tbl."`.`order_no` LIKE '%".$search_term."%' OR
                                                        `".$prefix_retailer_table."`.`store_name` LIKE '%".$search_term."%' OR
                                                        `".$prefix_maker_table."`.`company_name` LIKE '%".$search_term."%' OR
                                                        `".$prefix_representative_leads_tbl."`.`total_retail_price` LIKE '%".$search_term."%' OR
                                                        `total_wholesale_price` LIKE '%".$search_term."%' )"
                                                   );


                          } 


                          if(isset($requestData['filter']['retailer_approval']) && $requestData['filter']['retailer_approval']!="")
                          {  

                              $search_term = $requestData['filter']['retailer_approval'];

                              if ($search_term == "2") {

                              
                                $lead_obj = $lead_obj->where(function($query)use($search_term,$prefix_representative_leads_tbl)
                                {
                                          
                                    return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',$search_term)
                                                 ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                                });



                                $rep_sales_order_obj = $rep_sales_order_obj->where(function($query)use($search_term,$prefix_representative_leads_tbl)
                                {
                                          
                                    return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',$search_term)
                                                 ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                                });



                              }
                              else{
                               
                                $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

                                $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

                              }   

                          }


                          if(isset($requestData['filter']['payment_status']) && $requestData['filter']['payment_status']!="")
                          {  
                              $search_term = $requestData['filter']['payment_status'];

                              if($search_term == 1)
                              {
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',0);

                                  $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',0);
                              }

                              elseif ($search_term == 2) 
                              {
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',1);

                                  $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',1);
                              }
                              else
                              {
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',2);

                                  $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',2);
                              } 
                              
                          }  
              

                          if(isset($requestData['filter']['shipping_status']) && $requestData['filter']['shipping_status']!="")
                          {  
                            $search_term  = $requestData['filter']['shipping_status'];

                            $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term)
                                           ->where($representative_leads.'.is_confirm','!=',4);

                            $rep_sales_order_obj  = $rep_sales_order_obj
                                                    ->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term)
                                                    ->where($representative_leads.'.is_confirm','!=',4);

                          }

             

                          if(isset($requestData['filter']['price']) && $requestData['filter']['price']!="")
                          {

                             $search_term  = $requestData['filter']['price'];

                             $search_term  = intval($search_term);

                             $lead_obj     =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%')
                                                       ->where($representative_leads.'.is_confirm','!=',4);

                             $rep_sales_order_obj     =  $rep_sales_order_obj
                                                         ->having('total_wholesale_price','LIKE', '%'.$search_term.'%')
                                                         ->where($representative_leads.'.is_confirm','!=',4);
                          }
         
                          if(isset($requestData['filter']['date_from']) && $requestData['filter']['date_from']!="" && isset($requestData['filter']['date_to']) && $requestData['filter']['date_to']!="")
                          {
                              $search_term_from_date  = $requestData['filter']['date_from'];
                              $search_term_to_date    = $requestData['filter']['date_to'];
                              $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
                              $from_date              = $from_date->format('Y-m-d');
                              $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
                              $to_date                = $to_date->format('Y-m-d');
                          
                              $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);
                              $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);



                              $rep_sales_order_obj   = $rep_sales_order_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);

                              $rep_sales_order_obj   = $rep_sales_order_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);
                     

                          }


                          if(isset($requestData['filter']['comission_status']) && $requestData['filter']['comission_status']!="")
                          {  
                              $search_term  = $requestData['filter']['comission_status'];
                            
                              if($search_term == "1")
                              {  
                                  $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','0');


                                  $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','0');

                              }
                              else if($search_term == "2")
                              { 
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','1');


                                  $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','1');

                              }
                              else
                              { 
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','2');

                                  $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','2');

                              }
                             

                          }

                        }  



                        if(isset($requestData['order_type']) && $requestData['order_type']=="cancelled")
                        {    

                          if(isset($requestData['filter']['general_search']) && $requestData['filter']['general_search']!="")
                          {
                              $search_term      = $requestData['filter']['general_search'];


                                $lead_obj = $lead_obj->whereRaw(
                                                    "(  `".$prefix_representative_leads_tbl."`.`order_no` LIKE '%".$search_term."%' OR
                                                        `".$prefix_retailer_table."`.`store_name` LIKE '%".$search_term."%' OR
                                                        `".$prefix_maker_table."`.`company_name` LIKE '%".$search_term."%' OR
                                                        `".$prefix_representative_leads_tbl."`.`total_retail_price` LIKE '%".$search_term."%' OR
                                                        `total_wholesale_price` LIKE '%".$search_term."%' )"
                                                   );

                          } 


                          if(isset($requestData['filter']['retailer_approval']) && $requestData['filter']['retailer_approval']!="")
                          {  

                              $search_term = $requestData['filter']['retailer_approval'];

                              if ($search_term == "2") {

                              
                                $lead_obj = $lead_obj->where(function($query)use($search_term,$prefix_representative_leads_tbl)
                                {
                                          
                                    return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',$search_term)
                                                 ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                                });


                              }
                              else{
                               
                                $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

                              }   

                          }


                          if(isset($requestData['filter']['payment_status']) && $requestData['filter']['payment_status']!="")
                          {  
                              $search_term = $requestData['filter']['payment_status'];

                              if($search_term == 1)
                              {
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',0);
                              }

                              elseif ($search_term == 2) 
                              {
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',1);
                              }
                              else
                              {
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',2);
                              } 
                              
                          }  
              

                          if(isset($requestData['filter']['shipping_status']) && $requestData['filter']['shipping_status']!="")
                          {  
                            $search_term  = $requestData['filter']['shipping_status'];

                            $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term)
                                           ->where($representative_leads.'.is_confirm','!=',4);

                          }

             

                          if(isset($requestData['filter']['price']) && $requestData['filter']['price']!="")
                          {

                             $search_term  = $requestData['filter']['price'];

                             $search_term  = intval($search_term);

                             $lead_obj     =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%')
                                                       ->where($representative_leads.'.is_confirm','!=',4);
                          }
         
                          if(isset($requestData['filter']['date_from']) && $requestData['filter']['date_from']!="" && isset($requestData['filter']['date_to']) && $requestData['filter']['date_to']!="")
                          {
                              $search_term_from_date  = $requestData['filter']['date_from'];
                              $search_term_to_date    = $requestData['filter']['date_to'];
                              $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
                              $from_date              = $from_date->format('Y-m-d');
                              $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
                              $to_date                = $to_date->format('Y-m-d');
                          
                              $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);
                              $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);

                          }


                          if(isset($requestData['filter']['comission_status']) && $requestData['filter']['comission_status']!="")
                          {  
                              $search_term  = $requestData['filter']['comission_status'];
                            
                              if($search_term == "1")
                              {  
                                  $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','0');
                              }
                              else if($search_term == "2")
                              { 
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','1');
                              }
                              else
                              { 
                                  $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=','2');
                              }
                          }

                        }  

                        if(empty($requestData['order_type']) || (isset($requestData['order_type']) && $requestData['order_type']!='cancelled'))
                        {  
                         $lead_obj    = $rep_sales_order_obj->union($lead_obj);
                        }

                        $lead_obj    = $lead_obj->orderBy('id','DESC');

                        $arr_data    = $lead_obj->get();
                        $append_url  = url()->current();
                        $append_url  =  $append_url."?page=".$page;  
                        $paginator   = $this->get_pagination_data($arr_data->toArray(), $page, $per_page ,$append_url);
                        $total_amt   = 0;  

   
        if(isset($arr_data))
       {
         foreach($arr_data as $key => $order)
         {          
             //make a query if order is only save
             if($order->is_confirm == 4)
             {
                 $total_sum = $this->RepresentativeLeadsModel
                                   ->where('order_no',$order->order_no)
                                   ->where('maker_id','!=',0)
                                   ->sum('total_wholesale_price');              
                 $total_sum = num_format($total_sum);         
              }
             else
             {
                $total_sum = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0;
             }          
   
             $total_amt += $total_sum;     
          }   
        }
   
          $lead_arr = $paginator->toArray();

   
         if (isset($lead_arr['data']) && !empty($lead_arr['data'])) {
   
   
         $lead_arr['data'] = array_values($lead_arr['data']);

   			foreach ($lead_arr['data'] as $key => $value) {

   
            if(isset($value->is_confirm) && ($value->is_confirm == 2 || $value->is_confirm== 0 || $value->is_confirm== 4))
            {
               $order_type  = "Pending";
            }
   
            else if(isset($value->order_cancel_status) && $value->order_cancel_status==2)
            {
               $order_type  = "Cancelled";
            }
   
            else if(isset($value->is_confirm) && $value->is_confirm==1 && $value->ship_status==1 && isset($value->maker_confirmation) && $value->maker_confirmation==1 && isset($value->transaction_status) &&  $value->transaction_status==2)
            {
              $order_type  = "Completed";
            }
   
            else
            {
              $order_type = "";
            }
   
           //make a query if order is only save
           if($value->is_confirm == 4)
           {
               $total_sum = $this->RepresentativeLeadsModel
                                ->where('order_no',$value->order_no)
                                ->where('maker_id','!=',0)                
                                ->sum('total_wholesale_price');  
   
               $total_sum = num_format($total_sum);                   
           }
           else
           { 
              $total_sum  = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):'';
           }
   
   
   
   				$data['order_data'][intval($key)]["id"] 			       = $value->id;
   				$data['order_data'][intval($key)]["total_amount"] 	 = $total_sum;
   				$data['order_data'][intval($key)]["order_no"] 		   = $value->order_no;
   				$data['order_data'][intval($key)]["order_date"] 	   = date('m-d-Y', strtotime($value->created_at));
		      $data['order_data'][intval($key)]["is_confirm"]      = $value->is_confirm;
		      $data['order_data'][intval($key)]["is_split_order"]  = $value->is_split_order;
		      $data['order_data'][intval($key)]["promo_code"]      = $value->promo_code;
   				$data['order_data'][intval($key)]["retailer"] 		   = $value->store_name;
   				$data['order_data'][intval($key)]["vendor"] 		     = $value->comp_name;
   				$data['order_data'][intval($key)]["retailer_approval"] 	  = $value->is_confirm;
   				$data['order_data'][intval($key)]["shipping_status"] 	    = $value->ship_status;
   				$data['order_data'][intval($key)]["retailer_payment_status"]  = $value->is_payment_status;
   				$data['order_data'][intval($key)]["representative_commision"] = $value->rep_commission_status;
		        $data['order_data'][intval($key)]["order_type"]             = isset($order_type)?$order_type:'abcd';
		   		$data['order_data'][intval($key)]["products"]                 = isset($lead_products['data'])?$lead_products['data']:[];
   
   			   }
         }
   
           else
           {
             $data['order_data'] = [];
           }
   
   
       		$data['pagination']["current_page"]     = $lead_arr['current_page'];
       		$data['pagination']["first_page_url"]	  = $lead_arr['first_page_url'];
       		$data['pagination']["from"] 				    = $lead_arr['from'];
       		$data['pagination']["last_page"]			  = $lead_arr['last_page'];
       		$data['pagination']["last_page_url"]		= $lead_arr['last_page_url'];
       		$data['pagination']["next_page_url"]		= $lead_arr['next_page_url'];
       		$data['pagination']["path"] 				    = $lead_arr['path'];
       		$data['pagination']["per_page"] 			  = $lead_arr['per_page'];
       		$data['pagination']["prev_page_url"] 	  = $lead_arr['prev_page_url'];
       		$data['pagination']["to"] 				      = $lead_arr['to'];
       		$data['pagination']["total"] 			      = $lead_arr['total'];
          $data["total_order_amount"]             = isset($total_amt)?$total_amt:'';
   
   
   
          $data['order_data']   = $this->CommonService->get_status_display_names($data['order_data'],'listing');
   
            $response 				  = [];
         	$response['status']  	= 'success';
       		$response['message'] 	= 'Order list get successfully.';
       		$response['data']		  = isset($data)?$data:[];
   
   
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
   
   
     function get_pagination_data($arr_data = [], $pageStart = 1, $per_page = 0, $apppend_data = []) { 
   
           $perPage  = $per_page; /* Indicates how many to Record to paginate */
           $offSet   = ($pageStart * $perPage) - $perPage; /* Start displaying Records from this No.;*/
           $count    = count($arr_data);        /* Get only the Records you need using array_slice */
           $itemsForCurrentPage = array_slice($arr_data, $offSet, $perPage, true);       
   
           $paginator = new LengthAwarePaginator($itemsForCurrentPage, $count, $per_page, Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));
           $paginator->appends($apppend_data);
           
           return $paginator;
       }
   
   
    function product_listing($order_no ="",$lead_id="",$page="",$per_page="")
   {
     try
     { 
     	     $products = $arrVendors = [];
   
           $total_wholesale_price = $total_product_discount = $total_shipping_charges =
           $total_shipping_charge = $total_shipping_discount = $total_wholsale_price = 0;
   
           $order_number = isset($order_no)?base64_encode($order_no):'';
           
           $order_data   = $this->orderDataService->order_summary($order_number);
         
           //new code
           /*if vendor update the product details then update calculation according to new changes in representative leads and representative leads table table*/
   
           if(isset($order_data) && count($order_data)>0)
           {
               if(isset($order_data['order_details']) && count($order_data['order_details'])>0)
               {            
                     /* Workaround to brind arrays into contigious indexes Starts*/
                     $arr_tmp_holding = [];
                     $arr_final_holding = [];            
   
   
                     foreach ($order_data['order_details'] as $key => $tmpArr)
                     {                
                         if(isset($arr_tmp_holding[$tmpArr['maker_id']]) == false)
                         {
                           $arr_tmp_holding[$tmpArr['maker_id']] = [$tmpArr];
                         }
                         else
                         {
                           array_push($arr_tmp_holding[$tmpArr['maker_id']], $tmpArr);
                         }
                     }            
   
                     foreach ($arr_tmp_holding as $key => $tmpArr)
                     {
                         $arr_final_holding = array_merge($arr_final_holding,$tmpArr);
                     }   $order_data['order_details'] = $arr_final_holding;   
   
   
                     /* Workaround to brind arrays into contigious indexes Ends*/ 
   
   
                     foreach($order_data['order_details'] as $key => $orders)
                     {                
   
                         if(count($arrVendors) == 0)
                         {
                           array_push($arrVendors,$orders['maker_id']);
                         }                
   
                         if(!in_array($orders['maker_id'], $arrVendors))
                         {
                             array_push($arrVendors,$orders['maker_id']);
                             $total_wholesale_price = $total_product_discount = $total_shipping_charges =
                             $total_shipping_charge = $total_shipping_discount = $total_wholsale_price =0;
   
   
                         }                
   
                         $updated_arr['unit_wholsale_price'] = $orders['product_details']['unit_wholsale_price'];
                         $updated_arr['wholesale_price']     = $orders['product_details']['unit_wholsale_price']*$orders['qty'];               
   
                         //calculate product discount calculation
                         if(isset($orders['product_details']['prodduct_dis_type']) && $orders['product_details']['prodduct_dis_type'] == 1)
                         {                  
                             if($updated_arr['wholesale_price'] >= $orders['product_details']['product_dis_min_amt'])
                             {
                               $updated_arr['product_discount'] = $updated_arr['wholesale_price']*$orders['product_details']['product_discount']/100;
                             }                
                         }
                         elseif(isset($orders['product_details']['prodduct_dis_type']) && $orders['product_details']['prodduct_dis_type'] == 2)
                         {
                             if($updated_arr['wholesale_price'] >= $orders['product_details']['product_dis_min_amt'])
                             {
                               $updated_arr['product_discount'] = $orders['product_details']['product_discount'];
                             }                
                         }
                         else
                         {
                            $updated_arr['product_discount'] = 0.00;
                         }        
   
   
                         $updated_arr['product_shipping_charge'] = $orders['product_details']['shipping_charges'];
                         $updated_arr['shipping_charges']        = $orders['product_shipping_charge']-$orders['shipping_charges_discount'];   
   
   
   
   
                         //calculate shipping charges and discount                
   
                         if(isset($orders['product_details']['shipping_type']) && $orders['product_details']['shipping_type'] == 1)
                         {                    
                             if($updated_arr['wholesale_price'] >= $orders['product_details']['minimum_amount_off'])
                             {
                                 $updated_arr['shipping_charges_discount'] = 0.00;
                                 $updated_arr['shipping_charges']          = 0.00;
                                 $updated_arr['product_shipping_charge']   = 0.00;
                             }
                             else{                      
                                   $updated_arr['shipping_charges_discount'] = 0.00;
                                   $updated_arr['shipping_charges']          = $orders['product_shipping_charge']-$orders['shipping_charges_discount'];
                                   $updated_arr['product_shipping_charge']   = $orders['product_details']['shipping_charges'];  
                                 }              
                         }
   
                         elseif(isset($orders['product_details']['shipping_type']) && $orders['product_details']['shipping_type'] == 2)
                         {
                             if($updated_arr['wholesale_price'] >= $orders['product_details']['minimum_amount_off'])
                             {                        
                                 $updated_arr['shipping_charges_discount'] = $updated_arr['product_shipping_charge']*$orders['product_details']['off_type_amount']/100;    
   
                                 $updated_arr['shipping_charges'] = $updated_arr['product_shipping_charge'] -
                                 $updated_arr['shipping_charges_discount'];                    
                             } 
   
                         }
                         elseif(isset($orders['product_details']['shipping_type']) && $orders['product_details']['shipping_type'] == 3)
                         {
                             if($updated_arr['wholesale_price'] >= $orders['product_details']['minimum_amount_off'])
                             {                      
                               $updated_arr['shipping_charges_discount'] = $orders['product_details']['off_type_amount'];       
                               $updated_arr['shipping_charges']          = $updated_arr['product_shipping_charge'] -
                               $updated_arr['shipping_charges_discount'];                    
                             }                
                         }
                         else
                         {                    
                             $updated_arr['shipping_charges_discount'] = 0.00;
                             $updated_arr['shipping_charges']          = 0.00;
                             $updated_arr['product_shipping_charge']   = 0.00;                
                         }                
   
   
   
                         $this->RepresentativeProductLeadsModel
                         ->where('representative_leads_id',$orders['representative_leads_id'])
                         ->where('order_no',$orders['order_no'])
                         ->where('maker_id',$orders['maker_id'])
                         ->where('product_id',$orders['product_id'])
                         ->update($updated_arr);               
   
                         $total_product_discount  += isset($updated_arr['product_discount'])?$updated_arr['product_discount']:0.00;                
                         $total_shipping_charges  += isset($updated_arr['shipping_charges'])?$updated_arr['shipping_charges']:0.00;                
                         $total_shipping_charge   += isset($updated_arr['product_shipping_charge'])?$updated_arr['product_shipping_charge']:0.00;                
                         $total_shipping_discount += isset($updated_arr['shipping_charges_discount'])?$updated_arr['shipping_charges_discount']:0.00;                
                         $total_wholsale_price    += isset($updated_arr['wholesale_price'])?$updated_arr['wholesale_price']:0.00;             
                     }              
   
                     //update all total calculation in representative leads table 
   
                     $leads_updated_arr['total_wholesale_price']          = $total_wholsale_price + $total_shipping_charge - $total_product_discount - $total_shipping_discount;  
   
                     $leads_updated_arr['total_product_discount']         = $total_product_discount;
                     $leads_updated_arr['total_shipping_charges']         = $total_shipping_charges;
                     $leads_updated_arr['total_shipping_discount']        = $total_shipping_discount;
                     $leads_updated_arr['total_product_shipping_charges'] = $total_shipping_charge;             
   
   
                      $this->RepresentativeLeadsModel
                           ->where('order_no',$orders['order_no'])
                           ->where('maker_id',$orders['maker_id'])
                           ->update($leads_updated_arr);         
   
               }      
   
           }
   
   
         /*--------------------------------------------------------------------------------------------------------*/
   
   
           if($order_no!="" && $lead_id=="")
           { 
              $order_no      = $order_no;  
   
     			    $product_obj   = RepresentativeProductLeadsModel::where('order_no',$order_no)
     			                                                   ->with(['product_details' => function($product_details){
       						    								                       $product_details->select('id','product_name','description','brand','minimum_amount_off','product_dis_min_amt','shipping_type','product_discount','prodduct_dis_type','off_type_amount');
       						    								                       $product_details->with(['brand_details' => function ($brand_details) {
       						    									                     $brand_details->select('id','brand_name');
       					    								                         }]);
       						    								                       $product_details->with(['productDetails' => function ($productDetails) {
                       						    									     $productDetails->select('product_id','sku','image');
                       					    								         }]);
       					    								                       }])
                                                            ->with('get_product_min_qty')
                                                            ->with(['maker_details'=>function($maker_details){
                                                               $maker_details->select('user_id','company_name');
                                                            }]);

           }

           else if($order_no!="" && $lead_id!="")
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
                                                            ->with('get_product_min_qty')
                                                            ->with(['maker_details'=>function($maker_details){
                                                               $maker_details->select('user_id','company_name');
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

               $products['data'][$key]['sku']                     = isset($pro['sku'])?$pro['sku']:'';
               $products['data'][$key]['shipping_charges']        = isset($pro['product_shipping_charge'])?$pro['product_shipping_charge']:'';
               $products['data'][$key]['unit_price']              = isset($pro['unit_wholsale_price'])?$pro['unit_wholsale_price']:'';
               $products['data'][$key]['sub_total']               = isset($pro['wholesale_price'])?$pro['wholesale_price']:'';
               $products['data'][$key]['total_amount']            = isset($pro['wholesale_price'])?$pro['wholesale_price']:'';
               $products['data'][$key]['shipping_discount']       = isset($pro['shipping_charges_discount'])?$pro['shipping_charges_discount']:'';
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

           

            $response 							    = [];
         	  $response['status']  				= 'success';
         	  $response['message'] 				= 'Product list get successfully.';
         	  $response['data']					  = isset($products)?$products:[]; 
     
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
   
   
             /*  $arr_data['all'][0]['order_status'] = 'all'; 
               $arr_data['all'][0]['order_count']  = $total_count;                   
             */
   
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
   
   
               $arr_data['all'][0]['order_status'] = 'pending'; 
               $arr_data['all'][0]['order_count']  = $pending_count;                                  
   
   
   
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
   
               $arr_data['all'][1]['order_status'] = 'completed'; 
               $arr_data['all'][1]['order_count']  = $completed_count;                         
   
   
               $canceled_count = $this->RepresentativeLeadsModel
                                      ->where('order_cancel_status','=',2)
                                      ->where('representative_id',$user_id)
                                      ->count(); 
   
               $arr_data['all'][2]['order_status']  = 'cancelled'; 
               $arr_data['all'][2]['order_count']   = $canceled_count;                                       
   
   
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
                                       ->where('representative_id',$user_id)
                                       ->where('total_wholesale_price','>','0')
                                       ->where('order_cancel_status','!=','2')
                                       ->where(function($q){
                                       return $q->orwhere('is_payment_status','=','0')
                                                ->orwhere('is_payment_status','=','1');
                                       })->where(function($q){
                                       return $q->orwhere('ship_status','=','0')
                                                ->orwhere('ship_status','=','1');
                                       })
                                     ->where(function($q){
                                       return $q->orwhere('payment_term','=','Net30')
                                                ->orwhere('payment_term','=','Net30 - Online/Credit');
                                       })
                                   ->count();
   
               $arr_data['all'][3]['order_status']  = 'net_30_pending_count'; 
               $arr_data['all'][3]['order_count']   = $net_30_pending_count;  
   
   
              $net_30_completed_count =  DB::table($representative_leads)
                               ->select(DB::raw($prefix_representative_leads_tbl.".*,".
                                 $prefix_transaction_mapping.'.transaction_status,'.
                                 $prefix_transaction_mapping.'.order_id' )) ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                        ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');
                               })                          ->where($representative_leads.'.total_wholesale_price','>','0')                          ->where($representative_leads.'.order_cancel_status','!=',2)                          ->where('representative_id',$user_id)                          
                                                           ->where($representative_leads.'.is_confirm','=',1)
                                                           ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                           ->where($representative_leads.'.ship_status','=',1)
                                                           ->where($representative_leads.'.is_payment_status','=',1)
                                                           ->where(function($q) use($representative_leads){
                                                               return $q->orwhere($representative_leads.'.payment_term','=','Net30')
                                                                        ->orwhere($representative_leads.'.payment_term','=','Net30 - Online/Credit');
                                                             })
                                                           ->count();  
   
                   $arr_data['all'][4]['order_status']  = 'net_30_completed_count'; 
                   $arr_data['all'][4]['order_count']   =  $net_30_completed_count;                        
   
   
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
   
   
                /* $arr_data['pie_chart']['week'][0]['order_status'] = 'completed';  
                   $arr_data['pie_chart']['week'][0]['order_count']  = $complete_count; 
   
   
                   $arr_data['pie_chart']['week'][1]['order_status'] = 'pending';   
                   $arr_data['pie_chart']['week'][1]['order_count']  = $pendings_count;   
   
                   $arr_data['pie_chart']['week'][2]['order_status'] = 'canceled';    
                   $arr_data['pie_chart']['week'][2]['order_count']  = $cacels_count; */
   
   
                   $arr_data['pie_chart']['week'][0]['label']            = 'Completed';  
                   $arr_data['pie_chart']['week'][0]['data']             = $complete_count; 
                   $arr_data['pie_chart']['week'][0]['backgroundColor']  = 'rgb(255, 182, 193)';
   
   
   
                   $arr_data['pie_chart']['week'][1]['label']            = 'Pending';   
                   $arr_data['pie_chart']['week'][1]['data']             = $pendings_count;   
                   $arr_data['pie_chart']['week'][1]['backgroundColor']  = 'rgb(177, 156, 217)';
   
   
                   $arr_data['pie_chart']['week'][2]['label']            = 'Cancelled';    
                   $arr_data['pie_chart']['week'][2]['data']             = $cacels_count; 
                   $arr_data['pie_chart']['week'][2]['backgroundColor']  = 'rgb(236, 151, 135)';
   
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
   
                      /* $arr_data['pie_chart']['month'][0]['order_status'] = 'completed'; 
                       $arr_data['pie_chart']['month'][0]['order_count']  = $completed_leads_count; 
   
                       $arr_data['pie_chart']['month'][1]['order_status'] = 'pending';  
                       $arr_data['pie_chart']['month'][1]['order_count']  = $pending_leads_count;   
   
                       $arr_data['pie_chart']['month'][2]['order_status'] = 'canceled';   
                       $arr_data['pie_chart']['month'][2]['order_count']  = $canceled_leads_count; */
   
   
                         $arr_data['pie_chart']['month'][0]['label']            = 'Completed'; 
                         $arr_data['pie_chart']['month'][0]['data']             = $completed_leads_count; 
                         $arr_data['pie_chart']['month'][0]['backgroundColor']  = 'rgb(255, 182, 193)';
   
                         $arr_data['pie_chart']['month'][1]['label']            = 'Pending';  
                         $arr_data['pie_chart']['month'][1]['data']             = $pending_leads_count;
                         $arr_data['pie_chart']['month'][1]['backgroundColor']  = 'rgb(177, 156, 217)';   
   
                         $arr_data['pie_chart']['month'][2]['label']            = 'Cancelled';   
                         $arr_data['pie_chart']['month'][2]['data']             = $canceled_leads_count; 
                         $arr_data['pie_chart']['month'][2]['backgroundColor']  = 'rgb(236, 151, 135)';
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
   
                          /*$arr_data['bar_chart']['week'][0]['order_status']  = 'completed';
                          $arr_data['bar_chart']['week'][0]['order_count']   = $completed_order_cnt_arr;*/
   
   
                          $arr_data['bar_chart']['week'][0]['label']            = 'Completed';
                          $arr_data['bar_chart']['week'][0]['data']             = $completed_order_cnt_arr;
                          $arr_data['bar_chart']['week'][0]['backgroundColor']  = 'rgb(255, 182, 193)';
                          $arr_data['bar_chart']['week'][0]['borderColor']      = 'rgb(38, 194, 129)';
                          $arr_data['bar_chart']['week'][0]['borderWidth']      = 1;
   
   
   
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
   
   
                         /*  $arr_data['bar_chart']['week'][1]['order_status']  = 'pending';
                           $arr_data['bar_chart']['week'][1]['order_count']   = $pending_order_cnt_arr;*/
   
                          $arr_data['bar_chart']['week'][1]['label']            = 'Pending';
                          $arr_data['bar_chart']['week'][1]['data']             = $pending_order_cnt_arr;
                          $arr_data['bar_chart']['week'][1]['backgroundColor']  = 'rgb(177, 156, 217)';
                          $arr_data['bar_chart']['week'][1]['borderColor']      = 'rgb(38, 194, 129)';
                          $arr_data['bar_chart']['week'][1]['borderWidth']      = 1;
   
   
                           //canceled order count
                           $canceled_order_count = $this->RepresentativeLeadsModel
                                                         ->where('created_at','LIKE','%'.$date.'%')
                                                         ->where('order_cancel_status','=',2)
                                                         ->where('representative_id',$user_id)
                                                         ->sum('total_wholesale_price');
   
                           $canceled_order_cnt_arr[] = intval($canceled_order_count);                               
   
                         /* $arr_data['bar_chart']['week'][2]['order_status']  = 'cancelled';
                          $arr_data['bar_chart']['week'][2]['order_count']   =  $canceled_order_cnt_arr;*/
   
                          $arr_data['bar_chart']['week'][2]['label']            = 'Cancelled';
                          $arr_data['bar_chart']['week'][2]['data']             =  $canceled_order_cnt_arr;
                          $arr_data['bar_chart']['week'][2]['backgroundColor']  = 'rgb(236, 151, 135)';
                          $arr_data['bar_chart']['week'][2]['borderColor']      = 'rgb(38, 194, 129)';
                          $arr_data['bar_chart']['week'][2]['borderWidth']      = 1;
                     
                       }
                 } 
   
                /*-------------------get last month order  count ----------------------------*/
                 $order_data = [];
            
   
                 $currentMonth         = date('F');
   
                 $previous_month_name  = Date('M', strtotime($currentMonth . " last month"));
   
   
   
                 $month_arr            = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July ', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');
   
   
                 $first_date_month     = date("Y-m-d", strtotime("first day of previous month"));
                 $last_date_month      = date("Y-m-d", strtotime("last day of previous month"));
   
   
                     $from_date            = $first_date_month.' 00:00:00';
                     $to_date              = $last_date_month.' 23:59:59';
   
                 $completed_monthly_order_cnt_arr = $pending_monthly_order_cnt_arr = $canceled_monthly_order_cnt_arr = [];
   
                   foreach($month_arr as $month)
                  {
   
                 //completed orders count
                 $completed_monthly_count = $this->RepresentativeLeadsModel
                                         ->where('is_confirm','=',1)
                                         ->where('ship_status','=',1)
                                         ->where('representative_id',$user_id)
                                         ->whereBetween('created_at',array($from_date,$to_date))
                                         ->sum('total_wholesale_price');
   
                 if($previous_month_name!=$month)
                 {  
                   $completed_monthly_order_cnt_arr[] = 0;
                 } 
   
                 else
                 {
                   $completed_monthly_order_cnt_arr[] = intval($completed_monthly_count);
                 }
                           
   
   
                 /* $arr_data['bar_chart']['month'][$previous_month_name][0]['order_status']  = 'completed';
                  $arr_data['bar_chart']['month'][$previous_month_name][0]['order_count']     = intval($completed_count);*/
   
                   $arr_data['bar_chart']['month'][0]['label']            = 'Completed';
                   $arr_data['bar_chart']['month'][0]['data']             =  $completed_monthly_order_cnt_arr;
                   $arr_data['bar_chart']['month'][0]['backgroundColor']  = 'rgb(255, 182, 193)';
                   $arr_data['bar_chart']['month'][0]['borderColor']      = 'rgb(38, 194, 129)';
                   $arr_data['bar_chart']['month'][0]['borderWidth']      = 1;                   
                           
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
   
   
                // $pending_monthly_order_cnt_arr[] = intval($pending_count);  
   
                 if($previous_month_name!=$month)
                 {  
                   $pending_monthly_order_cnt_arr[] = 0;
                 } 
   
                 else
                 { 
                   $pending_monthly_order_cnt_arr[] = intval($pending_count); 
                 }  
   
   
                 /* $arr_data['bar_chart']['month'][$previous_month_name][1]['order_status']  = 'pending';   
                  $arr_data['bar_chart']['month'][$previous_month_name][1]['order_count']   =  intval($pending_count);*/
   
                    $arr_data['bar_chart']['month'][1]['label']            = 'Pending';   
                    $arr_data['bar_chart']['month'][1]['data']             = $pending_monthly_order_cnt_arr;
                    $arr_data['bar_chart']['month'][1]['backgroundColor']  = 'rgb(177, 156, 217)';
                    $arr_data['bar_chart']['month'][1]['borderColor']      = 'rgb(38, 194, 129)';
                    $arr_data['bar_chart']['month'][1]['borderWidth']      = 1;                    
                       
   
                 //canceled order count
                 $canceled_count = $this->RepresentativeLeadsModel
                                       ->where('order_cancel_status','=',2)
                                       ->where('representative_id',$user_id)
                                       ->whereBetween('created_at',array($from_date,$to_date))
                                        //->count();
                                       ->sum('total_wholesale_price');
   
                // $canceled_monthly_order_cnt_arr[] = intval($canceled_count);
   
                 /*$arr_data['bar_chart']['month'][$previous_month_name][2]['order_status']  = 'canceled';   
                 $arr_data['bar_chart']['month'][$previous_month_name][2]['order_count']   =  intval($canceled_count);*/
   
                 if($previous_month_name!=$month)
                 {  
                   $canceled_monthly_order_cnt_arr[] = 0;
                 } 
   
                 else
                 { 
                   $canceled_monthly_order_cnt_arr[] = intval($canceled_count); 
                 }  
   
   
                 $arr_data['bar_chart']['month'][2]['label']            = 'Cancelled';   
                 $arr_data['bar_chart']['month'][2]['data']             =  $canceled_monthly_order_cnt_arr;
                 $arr_data['bar_chart']['month'][2]['backgroundColor']  = 'rgb(236, 151, 135)';
                 $arr_data['bar_chart']['month'][2]['borderColor']      = 'rgb(38, 194, 129)';
                 $arr_data['bar_chart']['month'][2]['borderWidth']      = 1;      
   
   /*                foreach($month_arr as $month)
                  {
                      $arr_data['bar_chart']['month'][$month][0]['order_status']  =  'completed';  
                      $arr_data['bar_chart']['month'][$month][0]['order_count']   =  0;  
   
                      $arr_data['bar_chart']['month'][$month][1]['order_status']  =  'pending'; 
                      $arr_data['bar_chart']['month'][$month][1]['order_count']   =  0;  
   
                      $arr_data['bar_chart']['month'][$month][2]['order_status']  =  'canceled';   
                      $arr_data['bar_chart']['month'][$month][2]['order_count']   =  0;  
                  }*/
   
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
   
   
   	  public function details($order_id=false,$order_no=false)
   	  {
   	  	  try
   	  	  { 
   			    $main_split_order_no = $split_order_arr = $leads_arr = $data = [];
   			    $billing_address_str = $shipping_address_str = $retailer_payment_status = $commission_status = '';
   
   			    $leads_id = $order_id;
   			    $order_no = $order_no;
   
   			    $obj_data = $this->RepresentativeLeadsModel
   			                     ->with(['address_details',
   			                             'order_details',
   			                             'transaction_mapping',
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
   			                             },'maker_details'=>function($maker_details)
   			                             {
   			                                $maker_details->select('user_id','company_name');
   			                             },   
   			                               'transaction_mapping_details',
   			                               'stripe_transaction_detail',
   			                               'stripe_transaction_data'
   			                           ])
   			                      ->where('id',$leads_id)
   			                      ->where('order_no',$order_no)
   			                      ->first();
   
   
   			    if($obj_data)
   			    {
   			        $leads_arr = $obj_data->toArray();
   
   
   
   			        if(isset($leads_arr['split_order_id']) && $leads_arr['split_order_id'] != '')
   			        {
   			            $main_split_order_no = $this->RepresentativeLeadsModel->where('id',$leads_arr['split_order_id'])->first();
   			        }
   			        elseif (isset($leads_arr['is_split_order']) && $leads_arr['is_split_order'] == '1')
   			        {
   
   			          $split_order_arr = $this->RepresentativeLeadsModel->where('split_order_id',$leads_arr['id'])->get()->toArray();
   
   			        }
   
   			    }   
   
   
   			    $isFreeShipping = false;
   
   
   
   			    $promoCode     = isset($leads_arr['promo_code'])?$leads_arr['promo_code']:false;
   
             $promoCodeName = isset($leads_arr['promo_code'])?$leads_arr['promo_code']:false;
   
   			    $promoCodeData = $this->ProductService->get_promotion_and_prodo_code_details($promoCode);
   
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
   			    }
   
   
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

                  if(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==1)
                   $retailer_payment_status = 'Pending';

                  elseif(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==2)
                   $retailer_payment_status = 'Paid';

                  elseif(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==3)
                     $retailer_payment_status = 'Failed';
                  else
                    $retailer_payment_status = 'Pending';


                  $trans_status = isset($leads_arr['stripe_transaction_detail']['status'])?$leads_arr['stripe_transaction_detail']['status']:'';

                  if($trans_status == 1)
                  {
                     $commission_status = 'Pending';
                  }
                  elseif($trans_status == 2)
                  {
                     $commission_status = 'Paid';
                  }
                  elseif($trans_status == 3)
                  {
                      $commission_status = 'Failed';
                  }
                  else
                  {
                     $commission_status = '';
                  }
   
   
                   $data['retailer']      = isset($leads_arr['retailer_user_details']['retailer_details']['store_name'])?$leads_arr['retailer_user_details']['retailer_details']['store_name']:'';
   
                   $data['vendor']        = isset($leads_arr['maker_details']['company_name'])?$leads_arr['maker_details']['company_name']:'';
   
                   $data['order_no']      = isset($leads_arr['order_no'])?$leads_arr['order_no']:'';
   
                   $data['order_date']    = isset($leads_arr['created_at'])?date('m-d-Y', strtotime($leads_arr['created_at'])):'';
   
                   $data['total_amount']  = isset($leads_arr['total_wholesale_price'])?$leads_arr['total_wholesale_price']:'';
   
                   $data['promo_code']    = $promoCodeName; 
   
                   $data['shipping_status']         = isset($leads_arr['ship_status'])?$leads_arr['ship_status']:'';
   
                   $data['retailer_payment_status'] = isset($retailer_payment_status)?$retailer_payment_status:'';

                   $data['commission_status']       = isset($commission_status)?$commission_status:'';
   
                   $data['retailer_approval']       = isset($leads_arr['is_confirm'])?$leads_arr['is_confirm']:'';
   
                   $data['payment_term']            = isset($leads_arr['payment_term'])?$leads_arr['payment_term']:'';
   
                   $data['billing_address']         = isset($billing_address_str)?$billing_address_str:'';
   
                   $data['shipping_address']        = isset($shipping_address_str)?$shipping_address_str:'';
   
         			    $data['isFreeShipping']          = isset($isFreeShipping)?$isFreeShipping:'';
   
         			    $data['split_order_arr']         = isset($split_order_arr)?$split_order_arr:'';
   
         			    $data['main_split_order_no']     = isset($main_split_order_no)?$main_split_order_no:'';
   
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
   
   
   
                   $data                            = $this->CommonService->get_status_display_names($data,'details'); 
   
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
   
   
       public function reorder($order_id="")
       {
       	try{
   
       		if($order_id!="")
       		{	
   
       	    $arr_order_details      = $arr_address = $arr_order = $product_details = $arr_sku_no = [];
   
   		      $order_no               = str_pad('J2',  10, rand('1234567890',10)); 
   
   		      $order_details          = $this->RepresentativeLeadsModel->with('leads_details','address_details')->where('id',$order_id)->first();
   
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
   
   
   		    //store main order
   
   		    $arr_order['order_no']                          = $order_no;
   		    $arr_order['admin_commission']                  = isset($arr_order_details['maker_id'])?$this->CommissionService->get_admin_commission($arr_order_details['maker_id']):'';
   		    $arr_order['representative_id']                 = $arr_order_details['representative_id'];
   		    $arr_order['sales_manager_id']                  = $arr_order_details['sales_manager_id'];
   		    $arr_order['maker_id']                          = $arr_order_details['maker_id'];
   		    $arr_order['retailer_id']                       = $arr_order_details['retailer_id'];
   		    $arr_order['ship_status']                       = 0;
   		    $arr_order['is_confirm']                        = 0;
   		    $arr_order['total_retail_price']                = $arr_order_details['total_retail_price'];
   		    $arr_order['total_wholesale_price']             = $arr_order_details['total_wholesale_price'];
   		    $arr_order['sales_manager_commission_status']   = 0;
   		    $arr_order['total_product_discount']            = $arr_order_details['total_product_discount'];
   		    $arr_order['total_shipping_charges']            = $arr_order_details['total_shipping_charges'];
   		    $arr_order['total_shipping_discount']           = $arr_order_details['total_shipping_discount'];
   		    $arr_order['is_direct_payment']                 = get_maker_payment_term($arr_order_details['maker_id']);
   		    $arr_order['total_product_shipping_charges']    = $arr_order_details['total_product_shipping_charges'];
   		    $arr_order['is_direct_payment']                 = get_maker_payment_term($arr_order_details['maker_id']);
   
   		    $store_order = $this->RepresentativeLeadsModel->create($arr_order);
   
   		    if (isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0) {
   		      
   		      //$unsatisfied_product_arr = [];
   
   		      foreach ($arr_order_details['leads_details'] as $key => $product) {
   
   		        $product_details['order_no']                = $order_no;
   		        $product_details['representative_leads_id'] = $store_order->id;
   		        $product_details['maker_id']                = $product['maker_id'];
   		        $product_details['product_id']              = $product['product_id'];
   		        $product_details['sku']                     = $product['sku'];
   		        $product_details['retail_price']            = $product['retail_price'];
   		        $product_details['unit_wholsale_price']     = $product['unit_wholsale_price'];
   		        $product_details['wholesale_price']         = $product['wholesale_price'];
   		        $product_details['qty']                     = $product['qty'];
   		        $product_details['description']             = $product['description'];
   		        $product_details['product_discount']        = $product['product_discount'];
   		        $product_details['shipping_charges']        = $product['shipping_charges'];
   		        $product_details['shipping_charges_discount']= $product['shipping_charges_discount'];
   		        $product_details['product_shipping_charge'] = $product['product_shipping_charge'];
   
   		        
   		        $store_order_details = $this->RepresentativeProductLeadsModel->create($product_details);
   		      }//Foreach
           }//order details 
   
             if ($store_order_details) {
   
               $data['order_no']     =  $order_no;
         
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
   
      public function save_address($form_data="")
      {
           $is_update     = false;
           $is_addr_exist = 0;
           $data          = [];
   
           try
           {
               DB::beginTransaction();
   
               $user           = isset($form_data['auth_user'])?$form_data['auth_user']:'';
               $user_id        = $user->id;
   
               if(isset($form_data['order_no']) && $form_data['order_no'] != '')
               {
                 $order_no    = $form_data['order_no'];
                 $is_update   = true;
               }
               else
               {
                 $order_no = str_pad('J2',  10, rand('1234567890',10)); 
                 $this->verify_order_no($order_no);
               }
   
   
               /* Check if product already exists with given name*/
               $customer_obj = $this->UserModel->where('email',$form_data['bill_email'])->first();
   
               $customer_arr = [];
               
               $customer_arr = [
                         'bill_first_name'       => $form_data['bill_first_name'],
                         'bill_last_name'        => $form_data['bill_last_name'],
                         'bill_email'            => $form_data['bill_email'],
                         'bill_mobile_no'        => $form_data['bill_mobile_no'],
                         'bill_complete_address' => Null,
                         'bill_city'             => $form_data['bill_city'],
                         'bill_country'          => isset($form_data['bill_country'])?$form_data['bill_country']: '', 
                         'bill_state'            => $form_data['bill_state'],
                         'bill_zip_code'         => $form_data['bill_zip_code'],
                         'ship_first_name'       => $form_data['ship_first_name'],
                         'ship_last_name'        => $form_data['ship_last_name'],
                         'ship_email'            => $form_data['ship_email'],
                         'ship_mobile_no'        => $form_data['ship_mobile_no'],
                         'ship_complete_address' => Null,
                         'ship_city'             => $form_data['ship_city'],
                         'ship_country'          => isset($form_data['ship_country'])?$form_data['ship_country']:'', 
                         'ship_state'            => $form_data['ship_state'],
                         'ship_zip_code'         => $form_data['ship_zip_code'],
                         'order_no'              => $order_no,
                        'is_as_below'            => isset($form_data['same_as_billing'])?$form_data['same_as_billing'] :'0',
                        'bill_street_address'    => $form_data['bill_street_address'],
                        'bill_suit_apt'          => $form_data['bill_suit_apt'],
                        'ship_street_address'    => $form_data['ship_street_address'],
                        'ship_suit_apt'          => $form_data['ship_suit_apt']
   
                       ];
   
   
               if(isset($customer_obj))
               {
                 //update data to address table if already address present otherwise create new address record
                 $customer_id             = $customer_obj->id;
                 $is_save                 = '';
   
                 if($is_update)
                 {
                   $is_addr_exist         = $this->AddressModel->where('user_id',$customer_id)
                                                       ->where('order_no',$order_no)
                                                       ->count()>0;
                 }
   
                 $customer_arr['user_id'] = $customer_id;
   
                 if($is_addr_exist)
                 {
                   $is_update             = $this->AddressModel->where('user_id',$customer_id)
                                                 ->where('order_no',$order_no)
                                                 ->update($customer_arr);
                 }
                 else
                 {
                   
                   $is_save               = $this->AddressModel->create($customer_arr);
                 }
   
                  $module_id              = $customer_obj->id;
               }
               else
               {
                   $response['status']    = 'failure';               
                   $response['message']   = 'Please select valid retailer for order creation.';
                   $response['data']      = '';
   
                   return $response;
               } 
   
               $representative_id = 0;
               $sales_manager_id  = 0;
   
               if($user && $user->inRole('representative'))
               {   
                 $representative_id = $user_id;
               }
   
               if($is_save)
               {
               
                 //create one blank lead entry into representative_leads table
                 $representative_lead_arr = [];
                 $representative_lead_arr = [
   
                     'representative_id' => $representative_id,
                     'sales_manager_id'  => $sales_manager_id,                        
                     'retailer_id'       => $customer_id,
                     // 'admin_commission'  => get_admin_commission($form_data['maker_id']),
                     'order_no'          => $order_no
                 ];
   
                  $lead    = $this->RepresentativeLeadsModel->create($representative_lead_arr);
   
                  $lead_id = $lead->id;
   
                  $data['lead_id']        =  isset($lead_id)?$lead_id:'';
                  $data['order_no']       =  isset($lead['order_no'])?$lead['order_no']:'';
                  $data['module_id']      =  isset($module_id)?$module_id:'';
   
                  DB::commit();
   
                  $response['status']     = 'success';
                  $response['message']    = 'Address saved successfully.';
                  $response['data']       = isset($data)?$data:[];
                  
                  return $response;
   
               }elseif($is_update)
               {
                 $ord_no   = isset($form_data['order_no'])?$form_data['order_no']:'';
                 $lead_obj = $this->RepresentativeLeadsModel->where('order_no',$ord_no)->first();
                 if($lead_obj)
                 {
                   $data['lead_id']      = isset($lead_obj->id)?$lead_obj->id:'';
                   $data['order_no']     = isset($lead_obj->order_no)?$lead_obj->order_no:'';
                   $data['module_id']    = isset($module_id)?$module_id:'';
                 }
   
                  DB::commit();
                  $response['status']     = 'success';
                  $response['message']    = 'Address details updated successfully.';
                  $response['data']       = isset($data)?$data:[]; 
                
                  
                  return $response;
               }
               else
               {
                    DB::rollback();
   
                   $response['status']     = 'failure';               
                   $response['message']    = 'Something went wrong.';
                   $response['data']       = '';
   
                   return $response;
               }
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
      
   
      public function product_details($form_data=null)
      {
          try{
                $product_id   = $maker_id  = '';
                $product_arr  = $maker_arr = $product_data =  []; 
   
               if($form_data!= null)
               {
                  
                   if(isset($form_data) && !empty($form_data) && isset($form_data['product_id']) && !empty($form_data['product_id']))
                   {
                     $product_id = $form_data['product_id'];
                   }
                   else
                   {
                      $response['status']           = 'failure';
                      $response['message']          = 'something went wrong.';
                      $response['data']             = '';   
   
                      return $response;
                   }
   
                    /*check this product is active or not*/
   
                     if(isset($product_id) && $product_id!='')
                     {
                         $isProductActive = $this->ProductsModel->where('id',$product_id)->pluck('is_active')->first();
   
   
                         if($isProductActive !=1)
                         {
                            $response['status']           = 'failure';
                            $response['message']          = 'This product is currently unavailable.';
                            $response['data']             = '';   
                            return $response;
                            
                         }
                        
                         $data['isProductActive']         = $isProductActive;
                     }  
   
                     if($product_id)
                     {
                         $arr_data = [];
                         $obj_data = $this->ProductsModel->with(['productDetails.inventory_details','categoryDetails','brand_details'])
                                          ->where('id',$product_id)
                                          ->first();
                         if($obj_data)
                         {
                             $product_arr       = $obj_data->toArray();
   
                           $data['product_arr'] = $product_arr;
                         }
   
   
                         if(isset($product_arr) && sizeof($product_arr)>0)
                         {
                           $sku_id        = isset($product_arr['product_details'][0]['sku'])?$product_arr['product_details'][0]['sku']:"";
                           $pro_details   = get_style_dimension($sku_id);
   
   
                           /*Meta details start*/
   
                           $arr_meta_details = [];
                           $product_name     = isset($product_arr['product_name'])?$product_arr['product_name']:"";
                           $product_image    = isset($product_arr['product_image'])?$product_arr['product_image']:"";  
                           $brand_name       = isset($product_arr['brand_details']['brand_name'])?$product_arr['brand_details']['brand_name']:"";
   
                           $brand_id         = isset($product_arr['brand_details']['id'])?$product_arr['brand_details']['id']:"";
                          
                           $meta_image = ""; 
                           if($product_image!="")
                           {
   
                             $meta_image = url('/storage/app/'.$product_image); 
                             
                           }
                           else
                           {
                             $meta_image = url('/assets/images/no-product-img-found.jpg');
                           }
   
                           $data['arr_meta_details']['meta_title']  = $brand_name.'/'.$product_name;
                           $data['arr_meta_details']['meta_large_image_content']  = 'product_large_image';
                           $data['arr_meta_details']['meta_image']  = $meta_image; 
   
                           /*Meta details stop*/
   
                           $data['pro_details'] = isset($pro_details)?$pro_details:"";
                           $_data['sku_id']     = $sku_id;                  
                           
                           /*get maker details*/
                           if(isset($form_data['vendor_id']) && !empty($form_data['vendor_id']))
                           {
                             $maker_id = $form_data['vendor_id'];
                             
                             $maker_obj = $this->UserModel->with(['store_details','maker_details'])
                                                  ->where('id',$maker_id)
                                                  ->first();
                             if($maker_obj)
                             {
                                 $data['maker_arr'] = $maker_obj->toArray();
                             }
                           }                
                           /*end*/
   
                           /*get first product detail*/
                           $first_product_id   = $form_data['product_id'];
                           if(isset($first_product_id))
                           { 
                             $first_product_id = intval($first_product_id);
                             $arr_data         = [];

                             $obj_data       = $this->ProductsModel->with(['productDetails.inventory_details',
                                                                             'categoryDetails','productDetails.productMultipleImages'=>function($multiple_sku_images){$multiple_sku_images->select('product_detail_id','product_image');}]) 
                                                                     ->where('id',$first_product_id)
                                                                     ->first();

                            /*$obj_data         = $this->ProductsModel->with(['productDetails.inventory_details',
                                                                             'categoryDetails']) 
                                                                     ->where('id',$first_product_id)
                                                                     ->first();*/
                                                                                                            
                             if($obj_data)
                             {
                               $first_product_arr    = $obj_data->toArray();
                               if (isset($first_product_arr['product_details'][0]['sku'])) {
                                  $first_prod_sku    = $first_product_arr['product_details'][0]['sku']; 
                                } 
   
                                /*get related category product*/
   
                                 $category_id     = isset($first_product_arr['category_id'])?$first_product_arr['category_id']:"";
   
                                 $obj_subcategory = $this->ProductsSubCategoriesModel
                                 ->where('product_id',$product_id)
                                 ->first();
   
                                 if($obj_subcategory)
                                 {
                                   $arr_subcategory = $obj_subcategory->toArray();
   
   
                                   $data['related_product_arr'] = $this->ProductsSubCategoriesModel
                                                     ->where('sub_category_id',$arr_subcategory['sub_category_id'])
                                                     ->with('productDetails')
                                                     ->whereHas('productDetails',function($q) use($product_id){
                                                                       $q->where('id','<>',$product_id);
                                                                       $q->where('product_complete_status','4');
                                                                       $q->where('is_active','1');
                                                                       $q->where('is_deleted','0');
                                                                       $q->orderBy('updated_at','DESC');
                                                                      })
                                                     ->limit(10)
                                                     ->get()
                                                     ->toArray();
                                 }
                             }
                           
                             if(isset($first_prod_sku))
                             {                                    
                               $first_pro_details = get_style_dimension($first_prod_sku);
                               $first_pro_qty     = get_product_quantity($first_prod_sku);
                             }
                           }//if first product id
   
                         
                           $minimum_order         = "";
   
                           $get_minimum_order     = get_maker_shop_setting($data['maker_arr']['maker_details']['user_id']);
                           if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum'] == 0){$minimum_order = 'No Minimum Limit';}
                           else if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum']!= 0)
                           {$minimum_order = '$'. num_format($get_minimum_order['first_order_minimum']).' Minimum';}
   
                            //retail_price
   
   
   
                           $product_data['product_name']              = isset($data['product_arr']['product_name'])?$data['product_arr']['product_name']:''; 
   
                           $product_data['sku_no']                    = isset($data['product_arr']['product_details'][0]['sku'])?$data['product_arr']['product_details'][0]['sku']:'';
   
                           $product_data['vendor_name']               = isset($data['maker_arr']['maker_details']['company_name'])?$data['maker_arr']['maker_details']['company_name']:'';
   
                           $product_data['minimum']                   = isset($minimum_order)?$minimum_order:'';
   
                           $product_data['case_qty']                  = isset($first_product_arr['case_quantity'])?$first_product_arr['case_quantity']:'';
   
                           $product_data['product_min_qty']           = isset($data['product_arr']['product_details'][0]['product_min_qty'])?$data['product_arr']['product_details'][0]['product_min_qty']:'';
   
                           $product_data['wholsale_price']            = isset($data['product_arr']['unit_wholsale_price'])?$data['product_arr']['unit_wholsale_price']:'';
   
                           $product_data['retail_price']              = isset($data['product_arr']['retail_price'])?$data['product_arr']['retail_price']:'';
   
                           $product_data['category_name']             = isset($data['product_arr']['category_details']['category_name'])?$data['product_arr']['category_details']['category_name']:'';
                           $product_data['description']               = isset($data['product_arr']['description'])?$data['product_arr']['description']:'';
   
                           $product_data['ingrediants']               = isset($data['product_arr']['ingrediants'])?$data['product_arr']['ingrediants']:'';
                           $product_data['about_this_product']        = isset($data['product_arr']['description'])?$data['product_arr']['description']:'';
   
                           $product_data['available_qty']             = isset($data['product_arr']['product_details'][0]['inventory_details']['quantity'])?$data['product_arr']['product_details'][0]['inventory_details']['quantity']:''; 
   
   
                          /* $product_data['total_price']               = isset($data['product_arr']['unit_wholsale_price'])?$data['product_arr']['unit_wholsale_price']:'';*/
   
                           $product_data['product_image']              = $this->CommonService->imagePathProduct($data['product_arr']['product_image'], 'product', 0);
                           $product_data['product_image_thumb']        = $this->CommonService->imagePathProduct($data['product_arr']['product_image_thumb'], 'product', 0); 
   
                           $arr_related_images = $arr_mult_sku = [];
   
   
                           if(isset($product_arr['product_details']) && !empty($product_arr['product_details']))
                           {
                              foreach($product_arr['product_details'] as $key => $val)
                              {
                                 $arr_related_images[]     =  $this->CommonService->imagePathProduct($val['image'], 'product', 0);
                              }
                           }


                          if(isset($first_product_arr['product_details']) && !empty($first_product_arr['product_details']))
                           {
                              foreach($first_product_arr['product_details'] as $key => $val)
                              {
                                  $first_product_arr['product_details'][$key]['image']       = $this->CommonService->imagePathProduct($val['image'], 'product', 0);
                                  $first_product_arr['product_details'][$key]['image_thumb'] = $this->CommonService->imagePathProduct($val['image_thumb'], 'product', 0);
                              }
                           }

   
                           $product_data['related_images']       = $arr_related_images ;
                           $product_data['multiple_sku_details'] = $first_product_arr['product_details']; 
                           //$product_data['multiple_sku_details'] = $first_product_arr;  

   
                           $response['status']                        = "success"; 
                           $response['message']                       = "product details get successfully.";
                           $response['data']                          = isset($product_data)?$product_data:[];
   
   
                           return $response;
   
                         }//if product arr
                         else
                         {
                           $data['product_details']           = $product_arr;
                           $response['status']                = "failure";
                           $response['message']               = "something went wrong.";
                           $response['data']                  = isset($data)?$data:[];
   
                           return $sresponse;
                         }
   
                     }//if product id
                     else
                     {
                       $response['status']                    = "failure"; 
                       $response['message']                   = "something went wrong.";
                       $response['data']                      = "";
   
                       return $sresponse;
                     }
               }//form data
          }
   
          catch(Exception $e){
   
               DB::rollback();
   
               $response['status']      = 'failure';
               $response['message']     = $e->getMessage();
               $response['data']        = '';
                   
               return $response;
          }
      }
   
      public function add_to_bag($form_data=null)
      { 
           try
           {
             $is_update = false;
             $arr_rules = $data = [];        
             
             $order_no  = isset($form_data['order_no'])?$form_data['order_no']:false;
             $representative_leads_id = $order_no;
   
             DB::beginTransaction();
   
             $user = $form_data['auth_user'];
   
             if($user)
             {
                 $user_id = $user->id;
             }
   
             if(isset($order_no))
             {
             //Total lead calculations...
               $arr_lead_data = $lead_data = [];
               $obj_lead_data = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();
               
               if($obj_lead_data)
               {
                   $arr_lead_data = $obj_lead_data->toArray();
               }
          
               if(isset($arr_lead_data) && count($arr_lead_data)>0)
               {
                 $all_maker_id = $this->RepresentativeLeadsModel->where('order_no',$arr_lead_data['order_no'])
                                                                 ->get(['maker_id'])
                                                                 ->toArray();
   
                 $all_maker_ids = array_column($all_maker_id, 'maker_id');
   
                 $representative_lead_arr = [];
   
   
                 if($arr_lead_data['maker_id'] == '' && $arr_lead_data['maker_id'] == null)
                 { 
                       $representative_lead_arr = [
                                                 'representative_id'          => $arr_lead_data['representative_id'],
                                                 'retailer_id'                => $arr_lead_data['retailer_id'],
                                                 'order_no'                   => $arr_lead_data['order_no'],
                                                 'maker_id'                   => $form_data['maker_id'],
                                                 'is_confirm'                 => 0,
                                                 'total_retail_price'         => 0,
                                                 'total_wholesale_price'      => $form_data['total_wholesale_price'],
                                                 'total_commission_wholesale' => 0,
                                                 'tot_commi_less_wholesale'   => 0,
                                                 'admin_commission'           => $this->CommissionService->get_admin_commission($form_data['maker_id']),
                                                 'is_direct_payment'          => get_maker_payment_term($form_data['maker_id'])
   
                                                 ];
   
                         $update_order = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                                        /*->where('maker_id',$form_data['maker_id'])*/
                                                                        ->update($representative_lead_arr);
   
                         $new_lead_id = $this->RepresentativeLeadsModel->where('order_no',$arr_lead_data['order_no'])
                                                                       ->where('maker_id',$form_data['maker_id'])
                                                                       ->first(['id']);
                         if($new_lead_id){
                           $new_lead_id = $new_lead_id->toArray();
                         }
   
                   }
                   elseif(isset($form_data['maker_id']) && in_array($form_data['maker_id'], $all_maker_ids) == false)
                   {
                     $sales_manager_id = 0;
   
                    /*
                     if($user && $user->inRole('sales_manager'))
                     {
                         $loggedInUserId   = $user->id;
                         $sales_manager_id = $loggedInUserId;
                     } 
                    */
                       
                     $representative_lead_arr = [
                                                   'representative_id'          => $arr_lead_data['representative_id'],
                                                   'sales_manager_id'           => $sales_manager_id,
                                                   'retailer_id'                => $arr_lead_data['retailer_id'],
                                                   'order_no'                   => $arr_lead_data['order_no'],
                                                   'maker_id'                   => $form_data['maker_id'],
                                                   'admin_commission'           => $this->CommissionService->get_admin_commission($form_data['maker_id']),
                                                   'is_confirm'                 => 0,
                                                   'total_retail_price'         => 0,
                                                   'total_wholesale_price'      => $form_data['total_wholesale_price'],
                                                   'total_commission_wholesale' => 0,
                                                   'tot_commi_less_wholesale'   => 0,
                                                   'is_direct_payment'          => get_maker_payment_term($form_data['maker_id'])
                                                       
                                               ];
                       
   
                    $create_new_maker_order = $this->RepresentativeLeadsModel->create($representative_lead_arr);
   
                    $representative_leads_id = $create_new_maker_order['order_no'];
                    $new_lead_id             = $create_new_maker_order['id'];
   
                   }
                   else{
   
                         $new_lead_id = $this->RepresentativeLeadsModel->where('order_no',$arr_lead_data['order_no'])
                                                                       ->where('maker_id',$form_data['maker_id'])
                                                                       ->first(['id'])
                                                                       ->toArray();
                                                                   
                       }
   
   
   
                 $prev_lead_retail = $prev_lead_wholesale = $tot_lead_retail = $tot_lead_wholesale = 0;
                 $curr_lead_retail = $curr_lead_wholesale = 0;
   
                 $curr_lead_retail = isset($total_retail_price)?$total_retail_price:"0";   
   
                 $curr_lead_wholesale = isset($form_data['total_wholesale_price'])?$form_data['total_wholesale_price']:0.00;
   
                    
                 $curr_lead_id = isset($new_lead_id['id'])?$new_lead_id['id']:$new_lead_id;
                   //Store product by lead
                 $representative_lead_product_arr = [];
                 $product_id = isset($form_data['product_id'])?$form_data['product_id']:"";
                 $sku        = isset($form_data['sku_num'])?$form_data['sku_num']:""; 
                 $qty        = isset($form_data['item_qty'])?$form_data['item_qty']:"";   
                 $representative_lead_product_arr['maker_id']     = $form_data['maker_id'];
                 $representative_lead_product_arr['representative_leads_id'] = $curr_lead_id;
                 $representative_lead_product_arr['product_id']   = $product_id;
                 $representative_lead_product_arr['retail_price'] = $curr_lead_retail;
                 $representative_lead_product_arr['unit_wholsale_price'] = $form_data['wholesale_price'];
                 $representative_lead_product_arr['wholesale_price'] = isset($curr_lead_wholesale)?num_format($curr_lead_wholesale):0.00;
                 $representative_lead_product_arr['qty']          = $qty;
                 $representative_lead_product_arr['sku']          = $sku; 
                 $representative_lead_product_arr['order_no']     = $representative_leads_id; 
   
                 /* calculate product discount shipping charges discount*/
   
                 /*----------get product details  from product id-------------------------*/
   
                 $product_arr     = [];
                 $product_details = $this->ProductsModel->where('id',$form_data['product_id'])->first();
   
                 if(isset($product_details))
                 {
                    $product_arr =  $product_details->toArray(); 
                 }
   
                 //calculate shipping charges and shipping charges discount
               
                 if(isset($product_arr['shipping_type']) && $product_arr['shipping_type']==2) 
                 {
                           
                     if($form_data['total_wholesale_price']>=$product_arr['minimum_amount_off'])
                     {
                         $actual_shipping_charges    =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
   
                         $discount_amount            =  $actual_shipping_charges * (float)$product_arr['off_type_amount']/ 100;
                         $shipping_charges           =  $actual_shipping_charges-(float)$discount_amount;
   
                         if(isset($actual_shipping_charges) && $actual_shipping_charges && 
                           isset($product_arr['off_type_amount']) && $product_arr['off_type_amount']) 
                         {  
                            $discount_amount         =  $actual_shipping_charges * (float)$product_arr['off_type_amount']/ 100;
                         }
                         else 
                         {
                           $discount_amount          =  0;    
                         }  
   
                         $shipping_charges           =  $actual_shipping_charges-$discount_amount;
   
                         $shipping_charges_discount  =  $discount_amount;
   
                         $ship_charge_single_product =  (float)$actual_shipping_charges;
                         
                     }
                     else
                     {
   
                         $actual_shipping_charges    =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                         
                         $shipping_charges           = $actual_shipping_charges;
   
                         $shipping_charges_discount  = 0;
   
                         $ship_charge_single_product = $actual_shipping_charges;
   
                     }
   
   
                     $representative_lead_product_arr['shipping_charges']           = $shipping_charges;
                     $representative_lead_product_arr['shipping_charges_discount']  = $shipping_charges_discount;
                     $representative_lead_product_arr['product_shipping_charge']    = $ship_charge_single_product;
   
                 }
   
                 if(isset($product_arr['shipping_type']) && $product_arr['shipping_type'] == 1) 
                 { 
                     if($form_data['total_wholesale_price'] < $product_arr['minimum_amount_off'])
                     {
                         $actual_shipping_charges    =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                        
                         $shipping_charges           = $actual_shipping_charges;
                       
                         $shipping_charges_discount  = 0;
   
                         $ship_charge_single_product =  $actual_shipping_charges;
                     }
                     else
                     {
                        
                         $shipping_charges           = 0;
                         $shipping_charges_discount  = 0;
                         $ship_charge_single_product = 0;
                     }
   
                     $representative_lead_product_arr['shipping_charges']          = $shipping_charges;
                     $representative_lead_product_arr['shipping_charges_discount'] = $shipping_charges_discount;
                     $representative_lead_product_arr['product_shipping_charge']   = $ship_charge_single_product;
   
                 }
   
                 if(isset($product_arr['shipping_type']) && $product_arr['shipping_type'] == 3) 
                 {
                     if($form_data['total_wholesale_price'] >= $product_arr['minimum_amount_off'])
                     {
                         $actual_shipping_charges     =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                         $shipping_charges            = $actual_shipping_charges - (float)$product_arr['off_type_amount'];
                        
                         $shipping_charges_discount   = (float)$product_arr['off_type_amount'];
   
                         $ship_charge_single_product  = $actual_shipping_charges;
   
                     }
                     else
                     {
                          $actual_shipping_charges    =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                          $shipping_charges           = $actual_shipping_charges;
                          $shipping_charges_discount  = 0;
                          $ship_charge_single_product = $actual_shipping_charges;
   
                     }
   
                     $representative_lead_product_arr['shipping_charges']          = $shipping_charges;
                     $representative_lead_product_arr['shipping_charges_discount'] = $shipping_charges_discount;
                     $representative_lead_product_arr['product_shipping_charge']   = $ship_charge_single_product;
   
                 }
   
                 //calculate product discount
   
                   if(isset($product_arr['prodduct_dis_type']) && $product_arr['prodduct_dis_type']!='')
                   {
                        $product_dis_amount = $this->HelperService->calculate_product_discount($product_arr['prodduct_dis_type'],$product_arr['product_dis_min_amt'],$product_arr['product_discount'],$form_data['total_wholesale_price']);
                   }
                   else
                   {
                       $product_dis_amount = 0;
                   }
   
                   $representative_lead_product_arr['product_discount'] = $product_dis_amount;
                 /*---------------------------------------------------------------------------------------------*/
                 
                 $is_exists = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)
                                   ->where('product_id',$product_id)
                                   ->where('sku',$sku)->count();



                //get previous qty of that product if same product added into cart then qty wil increase
              
                  $prev_qty =  $this->RepresentativeProductLeadsModel
                                ->where('order_no',$order_no)
                                ->where('product_id',$product_id)
                                ->where('sku',$sku)
                                ->pluck('qty')
                                ->first();    


                  $qty = $qty + $prev_qty;
                   
                 if($is_exists>0)
                 {
                   $lead_product = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)
                                   ->where('product_id',$product_id)
                                   ->where('sku',$sku)
                                   ->update([ 'qty'=>$qty,
                                              'wholesale_price'=>$curr_lead_wholesale
                                           ]);
   
                   $amount = get_maker_total_amount($form_data['maker_id'],$order_no);
   
                 }
                 else
                 { 
                   $lead_product = $this->RepresentativeProductLeadsModel->create($representative_lead_product_arr);
                   
                   /*update represente lead table*/
   
                    /*sum of total shipping charges ,total ship_discount, product discount*/
   
                       $order_product_details = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)
                                                                                      ->get()
                                                                                      ->toArray();
                                                                                   
                                                                                   
                      
                       $total_product_discount          = array_sum((array_column($order_product_details,'product_discount')));
                       $total_shipping_charges          = array_sum((array_column($order_product_details,'shipping_charges')));
                       $total_shipping_charges_discount = array_sum((array_column($order_product_details,'shipping_charges_discount')));
   
                       $total_product_shipping_charges  = array_sum((array_column($order_product_details,'product_shipping_charge')));
   
   
                  
                       $data = [];
                       $data['total_product_discount']          = $total_product_discount;
                       $data['total_product_shipping_charges']  = $total_product_shipping_charges;
                       $data['total_shipping_charges']          = $total_shipping_charges;
                       $data['total_shipping_discount']         = $total_shipping_charges_discount;
   
   
                       $this->RepresentativeLeadsModel->where('order_no',$order_no)->update($data);
                 } 
   
   
                 if($lead_product)
                 {
   
                     $amount           = get_maker_total_amount($form_data['maker_id'],$order_no);
   
                     $order_details    = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();
   
   
                    if(isset($order_details))
                    {
                       $order_arr      = $order_details->toArray();
    
   
                       $total_wholesale_price = $order_details['total_wholesale_price']+$order_details['total_product_shipping_charges']-$order_details['total_product_discount']-num_format($order_details['total_shipping_discount']);
   
                       $this->RepresentativeLeadsModel->where('order_no',$order_no)->update(['total_wholesale_price'=>$total_wholesale_price]);
                    }
          
                     
             
                     DB::commit();
   
                    // $data['next_url'] = 'find_products/'.$order_no;
   
                     /*------------------------------------------------------*/
   
                      $response['status']      = 'success';
                      $response['message']     = 'Product added to bag.';
                      $response['data']        =  isset($data)?$data:[];
   
                      return $response;
                 }
                 else
                 {
   
   
                     DB::rollback();
   
                      $data['next_url']        = $this->module_url_path.'/find_products/'.$order_no;   
   
                      $response['status']      = 'failure';
                      $response['message']     = 'Something went wrong';
                      $response['data']        =  isset($data)?$data:[];
                    
                     return $response;
                 }
               }
               else
               {
                 
                 DB::rollback();
                 $response['status']      = 'failure';
                 $response['message']     = 'Something went wrong';
                 $response['data']        = '';
   
                 return $response;
               }  
   
             }
             else
             {
               
               DB::rollback();
               $response['status']      = 'failure';
               $response['message']     = 'Something went wrong';
               $response['data']        = '';
   
               return $response;
             }
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

                     $product_max_qty     = 0;
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
   
                            $arr['image']                 = isset($val['get_product_min_qty']['image'])?$this->CommonService->imagePathProduct($val['get_product_min_qty']['image'], 'product', 0):'';   
   
                            $arr['image_thumb']           = isset($val['get_product_min_qty']['image_thumb'])?$this->CommonService->imagePathProduct($val['get_product_min_qty']['image_thumb'], 'product', 0):''; 
   
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
                      


/*                        foreach($data['order_details'] as $key => $val)
                        {
                            $arr['product_id']            = isset($val['product_id'])?$val['product_id']:''; 
   
                            $arr['product_name']          = isset($val['product_details']['product_name'])?$val['product_details']['product_name']:'';
   
                            $arr['sku']                   = isset($val['sku'])?$val['sku']:''; 
   
                            $arr['qty']                   = isset($val['qty'])?$val['qty']:''; 

                            $arr['product_min_qty']       = isset($val['get_product_min_qty']['product_min_qty'])?$val['get_product_min_qty']['product_min_qty']:'';

                            $arr['product_max_qty']       = isset($product_max_qty)?$product_max_qty:'';
   
                            $arr['image']                 = isset($val['get_product_min_qty']['image'])?imagePath($val['get_product_min_qty']['image'], 'product', 0):'';   
   
                            $arr['image_thumb']           = isset($val['get_product_min_qty']['image_thumb'])?imagePath($val['get_product_min_qty']['image_thumb'], 'product', 0):''; 
   
                            $arr['unit_price']            = isset($val['product_details']['unit_wholsale_price'])?$val['product_details']['unit_wholsale_price']:'';
   
                            $arr['retail_price']          = isset($val['product_details']['retail_price'])?$val['product_details']['retail_price']:'';
   
                            $arr['shipping_charges']      = isset($val['product_shipping_charge'])?$val['product_shipping_charge']:'';
                            $arr['shipping_charges_discount']    = isset($val['shipping_charges_discount'])?$val['shipping_charges_discount']:'';
   
                            $arr['product_discount']       = isset($val['product_discount'])?$val['product_discount']:'';
   
                            $arr['product_dis_min_amt']    = isset($val['product_details']['product_dis_min_amt'])?$val['product_details']['product_dis_min_amt']:'';
   
                            $arr['minimum_amount_off']     = isset($val['product_details']['minimum_amount_off'])?$val['product_details']['minimum_amount_off']:'';
   
                            $arr['minimum_order_amount']   = isset($val['shop_settings']['first_order_minimum'])?$val['shop_settings']['first_order_minimum']:'';
                             
                            $arr['company_name']           = isset($val['maker_details']['company_name'])?$val['maker_details']['company_name']:'';  
   
                            $arr['shipping_type']          = isset($val['product_details']['shipping_type'])?$val['product_details']['shipping_type']:'';
                            $arr['product_discount_type']  = isset($val['product_details']['prodduct_dis_type'])?$val['product_details']['prodduct_dis_type']:'';
                            $arr['product_discount_value'] = isset($val['product_details']['product_discount'])?$val['product_details']['product_discount']:'';
                            $arr['shipping_discount_value']= isset($val['product_details']['off_type_amount'])?$val['product_details']['off_type_amount']:'';                  
                           
                            $order_data['product_data'][]  = $arr;  
                        } 
   */
                     //Shipping address details 
/*                     $order_data['address_data']['ship_first_name']                  = isset($data['address_details']['ship_first_name'])?$data['address_details']['ship_first_name']:'';
   
                     $order_data['address_data']['ship_last_name']                   = isset($data['address_details']['ship_last_name'])?$data['address_details']['ship_last_name']:'';
   
                     $order_data['address_data']['ship_email']                       = isset($data['address_details']['ship_email'])?$data['address_details']['ship_email']:'';
   
                     $order_data['address_data']['ship_mobile_no']                   = isset($data['address_details']['ship_mobile_no'])?$data['address_details']['ship_mobile_no']:'';
   
                     $order_data['address_data']['ship_city']                        = isset($data['address_details']['ship_city'])?$data['address_details']['ship_city']:'';
   
                     $order_data['address_data']['ship_state']                       = isset($data['address_details']['ship_state'])?$data['address_details']['ship_state']:'';
   
                     $order_data['address_data']['ship_country']                     = isset($data['address_details']['ship_country'])?$data['address_details']['ship_country']:'';
   
                     $order_data['address_data']['ship_zip_code']                    = isset($data['address_details']['ship_zip_code'])?$data['address_details']['ship_zip_code']:'';
   
                     $order_data['address_data']['ship_suit_apt']                    = isset($data['address_details']['ship_suit_apt'])?$data['address_details']['ship_suit_apt']:'';
   
                     $order_data['address_data']['ship_street_address']              = isset($data['address_details']['ship_street_address'])?$data['address_details']['ship_street_address']:'';
   
   
                     //Billing address details 
                     $order_data['address_data']['bill_first_name']                  = isset($data['address_details']['bill_first_name'])?$data['address_details']['bill_first_name']:'';
   
                      $order_data['address_data']['bill_last_name']                  = isset($data['address_details']['bill_last_name'])?$data['address_details']['bill_last_name']:'';
   
                     $order_data['address_data']['bill_email']                       = isset($data['address_details']['bill_email'])?$data['address_details']['bill_email']:'';
   
                     $order_data['address_data']['bill_mobile_no']                   = isset($data['address_details']['bill_mobile_no'])?$data['address_details']['bill_mobile_no']:'';
   
                     $order_data['address_data']['bill_city']                        = isset($data['address_details']['bill_city'])?$data['address_details']['bill_city']:'';
   
                     $order_data['address_data']['bill_state']                       = isset($data['address_details']['bill_state'])?$data['address_details']['bill_state']:'';
   
                     $order_data['address_data']['bill_country']                     = isset($data['address_details']['bill_country'])?$data['address_details']['bill_country']:'';
   
                     $order_data['address_data']['bill_zip_code']                    = isset($data['address_details']['bill_zip_code'])?$data['address_details']['bill_zip_code']:'';
   
                     $order_data['address_data']['bill_suit_apt']                    = isset($data['address_details']['bill_suit_apt'])?$data['address_details']['bill_suit_apt']:'';
   
                     $order_data['address_data']['bill_street_address']              = isset($data['address_details']['bill_street_address'])?$data['address_details']['bill_street_address']:'';*/


                  /*  $order_data['pagination']["current_page"]    = $data['current_page'];
                    $order_data['pagination']["first_page_url"]  = $data['first_page_url'];
                    $order_data['pagination']["from"]            = $data['from'];
                    $order_data['pagination']["last_page"]       = $data['last_page'];
                    $order_data['pagination']["last_page_url"]   = $data['last_page_url'];
                    $order_data['pagination']["next_page_url"]   = $data['next_page_url'];
                    $order_data['pagination']["path"]            = $data['path'];
                    $order_data['pagination']["per_page"]        = $data['per_page'];
                    $order_data['pagination']["prev_page_url"]   = $data['prev_page_url'];
                    $order_data['pagination']["to"]              = $data['to'];
                    $order_data['pagination']["total"]           = $data['total'];*/

   
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
   
   
      public function save($order_no="",$type="",$user=null)
      {
         try{
                $current_date = date('Y-m-d h:i:s');
   
                $order_no     = isset($order_no)?$order_no:'';
                $arr_data     = $orderData = $form_data = [];
                $user_id      = "";  
   
                $form_data['order_no'] = $order_no;
                $form_data['type']     = $type;

                if($user)
                {
                  $user_id               =  $user->id; 
                  $form_data['user_id']  = $user_id;
                }

               
               if(isset($order_no))
               {
                 $type            = isset($type)?$type:'';
                
                 $update_lead_arr = [];
                 $msg             = '';
   
                 if($type=='confirm_requested')
                 {
                   $update_lead_arr['is_confirm'] = 2;
                   $update_lead_arr['created_at'] = $current_date;
                   $message                       = "Order has been confirmed and sent to retailer for approval.";
                 }
                 //if order is save then is_confirm field is 4
                 elseif($type=='quote')
                 {
                    $update_lead_arr['is_confirm'] = 4;
                    $message                       = "Order has been saved.";

                 }
                 elseif($type=='reject')
                 {
                   $update_lead_arr['is_confirm'] = 3;
   
                    $message                      = "Order has been rejected.";
                 }
                 elseif($type=='confirm')
                 {
                   $update_lead_arr['is_confirm'] = 1;
   
                    $message                      = "Order has been confirmed.";
                 }
   
                 $orderData = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();
   
                 if($orderData)
                 {
                   $orderData = $orderData->toArray();
                 }
   
                 if(isset($orderData['sales_manager_id']) && $orderData['sales_manager_id'] != 0)
                 {
                    $update_lead_arr['rep_sales_commission'] = $this->CommissionService->get_sales_manager_commission($orderData['sales_manager_id']);
                 }
   
                 if(isset($orderData['representative_id']) && $orderData['representative_id'] != 0)
                 {
                   $update_lead_arr['rep_sales_commission']  = $this->CommissionService->get_representative_commission($orderData['representative_id']);
                 }
   
                 $is_update = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                             ->update($update_lead_arr);



                 if($is_update)
                 { 
                   if ($type=='confirm_requested') {
   
                     $objOrder = $this->RepresentativeLeadsModel->with(['leads_details','address_details'])->where('order_no',$order_no)->get();
   
                     if ($objOrder) {
                       $orderData = $objOrder->toArray();
                     }



                     $sendEmailToRetailer  = $this->RepsEmailService->send_retailer_mail($orderData,$order_no,$user);



                     $send_notification    = $this->GeneralService->representative_send_notification($order_no,$user);



                   }      
                     $data = $this->create($form_data);


                     $response['status']      = 'success';
                     $response['message']     = isset($message)?$message:'';
                     $response['data']        = '';


                     return $response; 
                 }
                   else
                   {
                     $response['status']      = 'failure';
                     $response['message']     = 'something went wrong.';
                     $response['data']        = '';
   
                     return $response;   
                   }
                }
               
               else
               {
                     $response['status']      = 'failure';
                     $response['message']     = 'something went wrong.';
                     $response['data']        = '';
   
                     return $response;   
               }   
   
               return redirect($this->module_url_path);
         }
   
         catch(Exception $e)
         {
               $response['status']      = 'failure';
               $response['message']     = $e->getMessage();
               $response['data']        = '';
   
               return $response;   
         }
   
      }
   
      public function find_products($user_id="",$order_no="")
      {
         try
         {
             $arr               = [];
             $maker_details_arr = [];
             $order_no          = $order_no;
   
             //get maker details
   
             $lead_obj = $this->RepresentativeLeadsModel->whereHas('leads_details',function($q) use($order_no){
                                                               $q->where('order_no',$order_no);
                                                           })
                                                        ->where('order_no',$order_no)
                                                        ->first();  
                                                       
             if($lead_obj)
             {
               $maker_details_obj = $this->UserModel->where('id',$lead_obj->maker_id)
                                                    ->with(['maker_details','maker_comission'=>function($query) use($user_id)
                                                    {
                                                       $query->where('representative_id',$user_id);
                                                    }])
                                                    ->first();
   
               if($maker_details_obj)
               {
                 $maker_details_arr = $maker_details_obj->toArray();
               }
   
             }
          
             $lead_product_data = $this->RepresentativeProductLeadsModel
                                                        ->where('order_no',$order_no)
                                                        ->with([ 
                                                               'product_details'=>function($product_details){$product_details->select('id','product_name','unit_wholsale_price','retail_price','available_qty','shipping_charges','product_image','shipping_type','minimum_amount_off','off_type_amount','product_dis_min_amt','product_discount');}
                                                             ])
                                                        ->with('maker_details')
                                                        ->get()
                                                        ->toArray();
           
   
             $arr_p_data = $arr = $arr_p_data = $company_names =[];
   
             if(count($lead_product_data) > 0)
             {
               foreach ($lead_product_data as $key => $product) 
               {
                 $arr_p_data['company_name'][]      = $product['maker_details']['company_name'];
               }
             }
               
             if(count($arr_p_data) > 0){
               $company_names = array_unique($arr_p_data['company_name']);
             }
   
             if(count($company_names) > 0 && isset($company_names))
             {
               foreach($company_names as $company)
               {
                  foreach ($lead_product_data as $key => $product) {
   
                   if($product['maker_details']['company_name'] == $company){
                           
                     $arr_d['qty']                = $product['qty'];
                     $arr_d['sku']                = $product['sku'];
                     $arr_d['product_name']       = $product['product_details']['product_name'];
                     $arr_d['unit_price']         = $product['product_details']['retail_price'];
                     $arr_d['wholesale_price']    = $product['product_details']['unit_wholsale_price'];
                     $arr_d['shipping_charges']   = $product['product_details']['shipping_charges'];
                     $arr_d['shipping_charges_discount'] = $product['shipping_charges_discount'];
                     $arr_d['shipping_type']      = $product['product_details']['shipping_type'];
                     $arr_d['minimum_amount_off'] = $product['product_details']['minimum_amount_off'];
                     $arr_d['product_dis_min_amt']= $product['product_details']['product_dis_min_amt'];
                     $arr_d['product_discount']   = $product['product_details']['product_discount'];
                     $arr_d['product_min_qty']    = isset($product['get_product_min_qty']['product_min_qty'])?$product['get_product_min_qty']['product_min_qty']:0;
                     
                     $arr[$company][] = $arr_d;
   
                     }
                   }
               }
             }
   
   
              $response['status']      = 'success';
              $response['message']     = 'Product list get successfully.';
              $response['data']        = isset($arr)?$arr:[];
   
              return $response;  
        }
   
        catch(Exception $e)
        {
           $response['status']      = 'failure';
           $response['message']     = $e->getMessage();
           $response['data']        = '';
   
           return $response;  
        } 
      }
   
     public function create($enc_cust_id = null)
     { 
         $user_id   = $enc_cust_id['user_id'];
         $order_no  = $enc_cust_id['order_no'];
   
         $is_exists = [];  
         $is_exists = $this->UserModel->where('id',$user_id)
                          ->where('status',1)->first();
         if($is_exists){
   
           $is_exists = $is_exists->toArray();
         }
   
         /*if(isset($enc_cust_id) && is_array($enc_cust_id) == false)
         {
          $order_no = isset($enc_cust_id['order_no'])?$enc_cust_id['order_no']:'';
         }
        */
   
         $arr_address = [];
   
         if ($order_no) {
   
          $address = $this->AddressModel->where('order_no',$order_no)
                                        ->first();
          if($address)
          {
           $arr_address = $address->toArray();
          }
         }
   
   
           /*get all active retailers*/
   
           $retailer_arr = $country_arr = [];
   
           //get only those retailer who has added by sales manager
   
           $retailer_arr = $this->RetailerRepresentativeMappingModel
                                ->with(['retailer_details','getRetailerDetails'])
                                ->whereHas('retailer_details',function($q){
                                   $q->select('store_name');
                                   $q->where('store_name','!=','');
                                   $q->orderBy('store_name','ASC');
                                })
                                ->whereHas('getRetailerDetails',function($q){
                                   $q->where('status',1);
                                   $q->where('is_approved',1);
                                })
   
                                ->where('representative_id',$user_id)
                                
                               ->get()
                               ->toArray();
      
         /*get country */
   
         $country_arr = $this->CountryModel->where('is_active',1)->orderBy('name','ASC')->get()->toArray();
   
        
         if(count($is_exists)<=0)
         { 
           Flash::error('Commission is not confirmed by admin, please wait for confirmation.');
                 return redirect()->back();
         }
     }
   
       public function verify_order_no($order_no=null) 
     {
       if($order_no)
       {
   
             $customerOrderData = $this->CustomerQuotesModel->where('order_no',$order_no)->count();
             $retailerOrderData = $this->RetailerQuotesModel->where('order_no',$order_no)->count();
             $repOrderData      = $this->RepresentativeLeadsModel->where('order_no',$order_no)->count();
             
             if($customerOrderData || $retailerOrderData || $repOrderData > 0)
             {
               $order_no = $this->generate_order_no($order_no);
             }
   
        }
       return $order_no;
      
     }
   
     public function generate_order_no($order_no=null)
     {
           $newOrderNumber = [];
           
           if($order_no)
           {
             $ordDigits      = substr($order_no, 2);
   
             $order_no       = $ordDigits + 1;
   
             $newOrderNo     = 'J2'.$order_no;
   
             $newOrderNumber = $this->verify_order_no($newOrderNo);
           }
   
           return $newOrderNumber;
      }
   
     public function remove_from_bag($order_no="",$sku_no="")
     {
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
   
   public function retailer_details($order_no=null)
   { 
     try
     {
        $arr_data = [];

        if($order_no!=null)
        {
           $obj_data = $this->AddressModel->where('order_no',$order_no)
                            ->select('user_id','bill_first_name','bill_last_name','bill_email','bill_mobile_no','bill_city','bill_state','bill_zip_code','ship_first_name','ship_last_name','ship_email','ship_mobile_no','ship_city','ship_state','bill_country','ship_country','ship_zip_code','bill_street_address','bill_suit_apt','ship_street_address','ship_suit_apt')
                            ->first();
           if($obj_data)
           {
              $arr_data = $obj_data->toArray();
           } 

           $response['status']         = 'success';               
           $response['message']        = 'Details get successfully.';    
           $response['data']           = isset($arr_data)?$arr_data:[];

           return $response;

        } 
     }

     catch(Exception $e)
     {
        $response['status']      = 'failure';
        $response['message']     = $e->getMessage();
        $response['data']        = "";  

       return $response;
     } 
   }

   public function sku_details($product_id=false,$sku_no=false)
   {
      try
      {
          $arr_data         = $response_data = [];
          $obj_data         = $this->ProductDetailsModel->with('productDetails','inventory_details') 
                                    ->where('product_id',$product_id)
                                    ->where('sku',$sku_no)
                                    ->first();

          if($obj_data)
          {
            $arr_data = $obj_data->toArray();

          
            $response_data['product_id']          = isset($arr_data['product_id'])?$arr_data['product_id']:''; 
            $response_data['sku_no']              = isset($arr_data['sku'])?$arr_data['sku']:'';
            $response_data['unit_wholsale_price'] = isset($arr_data['product_details']['unit_wholsale_price'])?$arr_data['product_details']['unit_wholsale_price']:''; 
            $response_data['description']         = isset($arr_data['sku_product_description'])?$arr_data['sku_product_description']:'';
            $response_data['ingrediants']         = isset($arr_data['product_details']['ingrediants'])?$arr_data['product_details']['ingrediants']:'';
            $response_data['image']               = isset($arr_data['image'])?$this->CommonService->imagePathProduct($arr_data['image'], 'product', 0):'';

            $response_data['product_min_qty']     = isset($arr_data['product_details']['product_min_qty'])?$arr_data['product_details']['product_min_qty']:0;
            $response_data['inventory']           = isset($arr_data['inventory_details']['quantity'])?$arr_data['inventory_details']['quantity']:'';
          }

          $response['status']         = 'success';               
          $response['message']        = 'Details get successfully.';    
          $response['data']           = isset($response_data)?$response_data:[];

          return $response;
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



 }  
   
   ?>