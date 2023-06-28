<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\TransactionsModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\TransactionMappingModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\SalesManagerModel;
use App\Models\RetailerModel;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\UserService;
use App\Common\Services\orderDataService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;
use App\Models\OrderTrackDetailsModel;
use App\Models\SiteSettingModel;

use Sentinel;
use DB;
use Validator;
use Datatables;
use Flash;
use DateTime;
use Excel;

class MyOrdersController extends Controller
{
    /* 
    |  Show Retailer orders with status   
  |  Author : Shital Vijay More
  |  Date   : 29 Aug 2019
  */
  public function __construct(RetailerQuotesModel $retailer_quote,UserModel $user_model,
                                ProductsModel $product_model,RetailerQuotesProductModel $retailer_quotes,
                                SalesManagerModel $SalesManagerModel,
                                MakerModel $MakerModel,
                                TransactionsModel $TransactionsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                EmailService $EmailService,
                                GeneralService $GeneralService,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                UserService $UserService,
                                RetailerModel $RetailerModel,
                                orderDataService $orderDataService,
                                CommissionService $CommissionService,
                                OrderTrackDetailsModel $OrderTrackDetailsModel,
                                SiteSettingModel $SiteSettingModel,
                                HelperService $HelperService
                               )
    {  
        $this->arr_view_data              = [];
        $this->module_title               = "My Orders";
        $this->module_view_folder         = 'maker.order_from_representative'; 
        $this->maker_panel_slug           = config('app.project.maker_panel_slug');
        $this->module_url_path            = url($this->maker_panel_slug.'/representative_orders');
        $this->RetailerQuotesModel        = $retailer_quote;
        $this->RetailerModel              = $RetailerModel;
        $this->UserModel                  = $user_model;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->ProductsModel              = $product_model;
        $this->MakerModel                 = $MakerModel;
        $this->TransactionsModel          = $TransactionsModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->SalesManagerModel          = $SalesManagerModel;
        $this->OrderTrackDetailsModel     = $OrderTrackDetailsModel;
        $this->EmailService               = $EmailService;
        $this->GeneralService             = $GeneralService;
        $this->UserService                =$UserService;
        $this->RetailerQuotesProductModel = $retailer_quotes;
        $this->RoleModel                  = $RoleModel;
        $this->orderDataService           = $orderDataService;
        $this->CommissionService          = $CommissionService;
        $this->HelperService              = $HelperService;
        $this->RepresentativeProductLeadsModel  = $RepresentativeProductLeadsModel;
        $this->SiteSettingModel           = $SiteSettingModel;
        $this->site_setting_obj           = $this->SiteSettingModel->first();
    
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
    }

    public function get_order_from_representative()
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Orders By Reps / Sales';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_order_list($arr_search_column=false,$module_data=false,$maker_id=false)
    {


      $loggedInUserId = 0;
      $user = Sentinel::check();
      
      // $retailer_id = $request->input('retailer_id');
      if($user)
      {
          $loggedInUserId = $user->id;
      }
      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $user_maker_table =  $this->UserModel->getTable();
      $prefix_user_maker_table = DB::getTablePrefix().$user_maker_table;        

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $representative_leads =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $transaction_mapping = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $sales_manager_table  = $this->SalesManagerModel->getTable();
      $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

      $representative_product_leads =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.
                              $prefix_retailer_table.'.store_name,'.
                              $prefix_retailer_table.'.dummy_store_name,'.
                              $prefix_role_user_table.'.role_id,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$prefix_representative_leads_tbl.'.retailer_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftJoin($prefix_sales_manager_table,$prefix_sales_manager_table.'.user_id','=',$prefix_user_table.'.id')

                           ->leftJoin($prefix_role_user_table,$prefix_role_user_table.'.user_id','=',$representative_leads.'.representative_id')

                    

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })

                          ->orderBy($prefix_representative_leads_tbl.".id",'DESC')
                          ->groupBy($prefix_representative_leads_tbl.'.id')
                          
                          ->where($representative_leads.'.representative_id','!=','0')

                          ->where($prefix_representative_leads_tbl.'.maker_id',$loggedInUserId)

                          // ->where($representative_leads.'.order_cancel_status','!=',2)

                          // ->where($representative_leads.'.order_cancel_status','!=',1)

                          ->where($representative_leads.'.order_cancel_status','!=',2)

                          ->where($representative_leads.'.total_wholesale_price','>','0');          

                          if(isset($module_data['is_confirm']) && $module_data['is_confirm'] == '0')
                          {
                         
                             $lead_obj = $lead_obj->where($representative_leads.'.is_confirm','!=',$module_data['is_confirm']);
                          }

                          if(isset($module_data['is_confirm']) && $module_data['is_confirm'] == '1')
                          {
                           
                               $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',$module_data['is_confirm'])
                                                    ->where($prefix_representative_leads_tbl.'.maker_id',$module_data['user_id']);
                          }
                          if(isset($maker_id) && $maker_id!='')
                          {
                            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.maker_id','=',$maker_id);
                          }

                          $sales_man_lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.
                              $prefix_retailer_table.'.store_name,'.
                              $prefix_retailer_table.'.dummy_store_name,'.
                              $prefix_role_user_table.'.role_id,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"

                               // "CONCAT(RS.first_name,' ',RS.last_name) as sales_man_user_name"
                                   )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.sales_manager_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$prefix_representative_leads_tbl.'.retailer_id')

                           ->leftJoin($prefix_sales_manager_table,$prefix_sales_manager_table.'.user_id','=',$prefix_user_table.'.id')

                           ->leftJoin($prefix_role_user_table,$prefix_role_user_table.'.user_id','=',$representative_leads.'.sales_manager_id')


                          
                           ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                              $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                              ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })

                           ->orderBy($prefix_representative_leads_tbl.".id",'DESC')
                           ->groupBy($prefix_representative_leads_tbl.'.id')
                           ->where($representative_leads.'.sales_manager_id','!=','0')

                           ->where($prefix_representative_leads_tbl.'.maker_id',$loggedInUserId)

                           //->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.maker_id','!=',0)

                           ->where($representative_leads.'.total_wholesale_price','>','0');
                            
                            if(isset($module_data['is_confirm']) && $module_data['is_confirm'] == '0')
                           {
                         

                             $sales_man_lead_obj = $sales_man_lead_obj->where($representative_leads.'.is_confirm','!=',$module_data['is_confirm']);
                           }

                           if(isset($module_data['is_confirm']) && $module_data['is_confirm'] == '1')
                           {
                           
                               $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',$module_data['is_confirm'])
                                                    ->where($prefix_representative_leads_tbl.'.maker_id',$module_data['user_id']);
                           }
                          if(isset($maker_id) && $maker_id!='')
                           {
                             
                              $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_representative_leads_tbl.'.maker_id','=',$maker_id);
                           }

                           

                           


  /* ---------------- Filtering Logic ----------------------------------*/  

     if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {
          $search_term        = $arr_search_column['q_order_no'];
          $lead_obj = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
          $sales_man_lead_obj = $sales_man_lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
      } 

      if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
      {
          $search_term  = $arr_search_column['q_payment_term'];
          $obj_qutoes   = $lead_obj->where($prefix_representative_leads_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
      }





      if(isset($arr_search_column['q_order_from_date']) && $arr_search_column['q_order_from_date']!="" && isset($arr_search_column['q_order_to_date']) && $arr_search_column['q_order_to_date']!="")
      {
            $search_term_from_date  = $arr_search_column['q_order_from_date'];
            $search_term_to_date    = $arr_search_column['q_order_to_date'];
            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');
        
            $lead_obj   = $lead_obj->whereDate($representative_leads.'.created_at', '<=', $to_date);
            $lead_obj   = $lead_obj->whereDate($representative_leads.'.created_at', '>=', $from_date);

            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '<=', $to_date);
            $sales_man_lead_obj   = $sales_man_lead_obj->whereDate($representative_leads.'.created_at', '>=', $from_date);
      }



      if(isset($arr_search_column['q_lead_date']) && $arr_search_column['q_lead_date']!="")
      {
          $search_term      = $arr_search_column['q_lead_date'];
          $date             = DateTime::createFromFormat('m-d-Y',$search_term);
          $date             = $date->format('Y-m-d');
             

          //$search_term      = date('Y-m-d',strtotime($search_term));
          $lead_obj = $lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
          $sales_man_lead_obj = $sales_man_lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
      } 

      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term      = $arr_search_column['q_customer_name'];
          $lead_obj =  $lead_obj->having('dummy_store_name','LIKE', '%'.$search_term.'%');
          $sales_man_lead_obj = $sales_man_lead_obj->having('dummy_store_name','LIKE', '%'.$search_term.'%');
      }

      
      if(isset($arr_search_column['q_representative_name']) && $arr_search_column['q_representative_name']!="")
      {
          $search_term = $arr_search_column['q_representative_name'];
          
          if($search_term == 'representative')
          { 
             //$lead_obj =  $lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');

             $lead_obj =  $lead_obj->where($prefix_role_user_table.'.role_id','=',3);

             $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_role_user_table.'.role_id','!=',5);
          }
          
          if($search_term == 'sales_manager')
          {
             //$sales_man_lead_obj = $sales_man_lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');

             $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_role_user_table.'.role_id','=',5);

             $lead_obj =  $lead_obj->where($prefix_role_user_table.'.role_id','!=',3);
          }
          
          
      }


      if(isset($arr_search_column['q_maker_name']) && $arr_search_column['q_maker_name']!="")
      {

          $search_term      = $arr_search_column['q_maker_name'];
          $lead_obj =  $lead_obj->having('maker_user_name','LIKE', '%'.$search_term.'%');
          $sales_man_lead_obj = $sales_man_lead_obj->having('maker_user_name','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_lead_status']) && $arr_search_column['q_lead_status']!="")
      {
          $search_term      = $arr_search_column['q_lead_status'];
          $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);
          $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

      }

      if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
      {  
          $search_term  = $arr_search_column['q_ship_status'];
          $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term);
          $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term);
      }
      
      if(isset($arr_search_column['q_payment_term']) && $arr_search_column['q_payment_term']!="")
      {
          $search_term  = $arr_search_column['q_payment_term'];
          $sales_man_lead_obj   = $sales_man_lead_obj->where($prefix_representative_leads_tbl.'.is_direct_payment','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
      {
          $search_term  = $arr_search_column['q_payment_status'];

          if($search_term == 1) 
          {
            $lead_obj = $lead_obj->whereNotExists(function($query){
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
            });

            $sales_man_lead_obj = $sales_man_lead_obj->whereNotExists(function($query){
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
            });

          }
          else
          {

              $lead_obj     = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','LIKE','%'.$search_term.'%');

              $sales_man_lead_obj = $sales_man_lead_obj->where($prefix_transaction_mapping.'.transaction_status','LIKE','%'.$search_term.'%');

          }

       
      }

      if(isset($arr_search_column['q_total_costing_retail']) && $arr_search_column['q_total_costing_retail']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_retail'];
          $lead_obj =  $lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
          $sales_man_lead_obj = $sales_man_lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_wholesale'];
          $lead_obj =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
          $sales_man_lead_obj = $sales_man_lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
      }

      $lead_obj = $lead_obj->union($sales_man_lead_obj);
      $lead_obj = $lead_obj->orderBy('id','DESC');

      return $lead_obj;
    }


    public function get_listing(Request $request)
    {
      $input = $request->all();
      $admin_commission = $this->CommissionService->get_admin_commission();
      $user = Sentinel::check();
      $loggedIn_userId = 0;
      $maker_id = 0;

      if($user)
      {
          $loggedIn_userId = $user->id;
          $maker_id = $user->id;
      }    

      $this->arr_view_data['module_title']    = $this->module_title;
      $this->arr_view_data['page_title']      = 'Create';
      $this->arr_view_data['module_url_path'] = $this->module_url_path;


      $search_data = $request->input('column_filter');

      $module_data['module_url'] = $this->module_url_path;

      $module_data['is_confirm'] = '1';
      $module_data['user_id'] = $loggedIn_userId;

      $lead_obj = $this->get_order_list($search_data,$module_data,$maker_id);

      
      /* ---------------- Filtering Logic ----------------------------------*/  
              
      $arr_search_column = $request->input('column_filter');             ;
      if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {

          $search_term = $arr_search_column['q_order_no'];
          $lead_obj = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
      }

       
      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term      = $arr_search_column['q_customer_name'];
          $lead_obj =  $lead_obj->having('retailer_user_name','LIKE', '%'.$search_term.'%');
      }
    

      if(isset($arr_search_column['q_total_costing_retail']) && $arr_search_column['q_total_costing_retail']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_retail'];
          $lead_obj =  $lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_wholesale'];
          $lead_obj =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
      }


      //Calculate total by Harshada on date 09 Sep 2020
     $total_amt = 0;        
     $total_amt =array_reduce($lead_obj->get()->toArray(), function(&$res, $item) {
          return $res + $item->total_wholesale_price;
      }, 0);

      // dd($lead_obj->get()->toArray());
      $current_context = $this;
      
      $json_result     = \Datatables::of($lead_obj);
      //$json_result     = $json_result->blacklist(['id']);
      //$build_result = $json_result->make(true)->getData();

      $json_result     = $json_result->editColumn('enc_id',function($data)
                          {
                              return base64_encode($data->id);

                            
                          })
                         ->editColumn('total_retail_price', function($data){

                          return isset($data->total_retail_price)?num_format($data->total_retail_price):'';
                          
                         })
                         ->editColumn('total_wholesale_price', function($data){

                          return isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'';

                         })

                         ->editColumn('retailer_user_name', function($data){

                          return isset($data->retailer_user_name)?$data->retailer_user_name:'';
               
                         })

                         

                          ->editColumn('representative_user_name', function($data){

                          //Rep/sales name
                          // return isset($data->representative_user_name)?$data->representative_user_name:'';
                            $data->representative_user_name = "Representative";
                            if($data->sales_manager_id!=0)
                            {
                              $data->representative_user_name = "Sales Manager";
                            }
                            return isset($data->representative_user_name)?$data->representative_user_name:'';

                         })

                        ->editColumn('maker_user_name', function($data){

                          return isset($data->maker_user_name)?$data->maker_user_name:'';

                         })

                          ->editColumn('product_html',function($data) use ($current_context)
                          {   
                              $id       = isset($data->id)?$data->id:"";
                              $order_no = isset($data->order_no)?$data->order_no:"";

                              $products_arr = [];
                              $products_arr = get_lead_products($id,$order_no);

                              return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);

                          })

                          ->editColumn('payment_term',function($data) use ($current_context)
                          {   
                              $is_direct_payment  = isset($data->is_direct_payment)?$data->is_direct_payment:"";

                            if($is_direct_payment == 1)
                            {
                               $payment_term = '<span class="label label-success">Direct</span>';
                            }
                            else
                            {
                               $payment_term = '<span class="label label-success">In-Direct</span>';
                            }
                              
                              return $payment_term;
                              
                          })
                          ->editColumn('build_action_btn',function($data) use ($current_context,$admin_commission)
                          {
                              
                              if($data->admin_commission_status == '1')
                              {
                                 $is_disabled = 'display:none';
                              }
                              else
                              {
                                 $is_disabled='display:block';
                              }

                              //get unread messages count
                              $unread_message_count = get_lead_unread_messages_count($data->id,'representative');
                              
                              if($unread_message_count>0)
                              {
                                  $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                              }
                              else
                              {
                                  $unread_msg_count = '';
                              }

                              //check if user is online or not
                              $is_online = check_is_user_online($data->maker_id);

                              if($is_online ==true)
                              {
                                $online_status = '<span class="act-online"></span>';
                              }
                              else
                              {
                                $online_status = '<span class="act-offline"></span>';
                              }

                              $build_edit_action = $build_view_action = $build_chat_action = '';

                              $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->order_no);

                              $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);


                            /*************************************************************************/
                            $pay_admin_button = '';

                            $admin_commission = isset($data->admin_commission)?$data->admin_commission:0;

                            if($admin_commission == 0)
                            {
                              $admin_commission = $this->CommissionService->get_admin_commission();
                            }

                            $ord_wholesale_price = isset($data->total_wholesale_price)?$data->total_wholesale_price:0;


                            $is_freeshipping = is_promocode_freeshipping($data->promo_code);

                            if($is_freeshipping == false)
                            {
                              $ord_wholesale_price = $ord_wholesale_price - $data->total_shipping_charges;
                            }

                            $admin_commission_amount = $ord_wholesale_price*($admin_commission / 100);

                            if($data->ship_status == '1')
                            {
                              if($data->is_direct_payment == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                              {
                               /* $pay_admin_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="Pay '.$this->site_setting_arr['site_name'].'"  onclick="fillData('.$ord_wholesale_price.','.$admin_commission.','.num_format($admin_commission_amount).','.$data->id.','.$data->maker_id.')"" style="'.$is_disabled.'" >Pay '.$this->site_setting_arr['site_name'].'</button>';*/

                               $pay_admin_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="View Commission"  onclick="fillData('.$ord_wholesale_price.','.$admin_commission.','.num_format($admin_commission_amount).','.$data->id.','.$data->maker_id.')"" style="'.$is_disabled.'" >View Commission</button>';
                              }
                            }

                            /*************************************************************************/

                              if($data->is_confirm == '0')
                              {
                                $build_edit_action = '<a href="'.$this->module_url_path.'/find_products/'.base64_encode($data->order_no).'/edit"  data-size="small" title="Edit Order Details" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</i></a>';
                              }

                              $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>'.$pay_admin_button;

                              $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.' </a>';                                      

                              //return $build_action = $build_edit_action.' '.$build_view_action.' '.$build_chat_action;

                              return $build_action = $build_edit_action.' '.$build_view_action;
                          })
                          ->editColumn('created_at',function($data)
                            {
                              //return  format_date($data->created_at);
                              return  us_date_format($data->created_at);
                            })->make(true);

      $build_result = $json_result->getData();
       $build_result->total_amt = $total_amt;
      return response()->json($build_result);         
    }

    public function get_export_representative_orders(Request $request)
    {
      $admin_commission = $this->CommissionService->get_admin_commission();
      $user = Sentinel::check();
      $loggedIn_userId = 0;
      $maker_id = 0;

      if($user)
      {
          $loggedIn_userId = $user->id;
          $maker_id = $user->id;
      }    

      $this->arr_view_data['module_title']    = $this->module_title;
      $this->arr_view_data['page_title']      = 'Create';
      $this->arr_view_data['module_url_path'] = $this->module_url_path;

      $search_data = $request->all();

      $module_data['module_url'] = $this->module_url_path;

      $module_data['is_confirm'] = '1';
      $module_data['user_id'] = $loggedIn_userId;

      $lead_obj = $this->get_order_list($search_data,$module_data,$maker_id);

      // dd($lead_obj);
      /* ---------------- Filtering Logic ----------------------------------*/  
              
      $arr_search_column = $request->input('column_filter');             ;
      if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {

          $search_term = $arr_search_column['q_order_no'];
          $lead_obj = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
      }

       
      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term      = $arr_search_column['q_customer_name'];
          $lead_obj =  $lead_obj->having('retailer_user_name','LIKE', '%'.$search_term.'%');
      }
    

      if(isset($arr_search_column['q_total_costing_retail']) && $arr_search_column['q_total_costing_retail']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_retail'];
          $lead_obj =  $lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_wholesale'];
          $lead_obj =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
      }

      $type  = 'csv'; 
      $data = $arr_orders = $arrayResponseData = [];

      $arr_orders = $lead_obj->get()->toArray();

      foreach($arr_orders as $key => $value)
      { 
          $payment_status = 'Pending';
          if($value->transaction_status == 1 || $value->transaction_status==null)
          {
            $payment_status =  'Pending';
          }else if($value->transaction_status == 2)
          {
            $payment_status = 'Paid';
          }else
          {
            $payment_status = 'Failed';
          }


          $shipping_status = 'Pending';
          if($value->ship_status == 0 || $value->ship_status==null)
          {
            $shipping_status =  'Pending';
          }elseif($value->ship_status == 1)
          {
            $shipping_status =  'Shipped';
          }
          else
          {
             $shipping_status = 'Incomplete';
          }     


          $payment_type = 'Direct';
          if($value->is_direct_payment == 1 || $value->is_direct_payment==null)
          {
            $payment_type =  'Direct';
          }else
          {
            $payment_type = 'In-Direct';
          }  

          $value->representative_user_name = "Representative";

          if($value->sales_manager_id!=0)
          {
            $value->representative_user_name = "Sales Manager";
          }

          $arrayResponseData['Order No']              = $value->order_no;
          $arrayResponseData['Order Date']            = $value->created_at;
          //$arrayResponseData['Retailer']              = $value->store_name;    
          $arrayResponseData['Customer']              = $value->dummy_store_name;        
          $arrayResponseData['Reps / Sales']          = $value->representative_user_name;      
          $arrayResponseData['Total Amount']          = $value->total_wholesale_price;
          $arrayResponseData['Payment Status']        = $payment_status;
          $arrayResponseData['Shipping Status']       = $shipping_status;
          $arrayResponseData['Payment Type']          = $payment_type;
          
          array_push($data,$arrayResponseData);
      }
      
       return Excel::create('Representative Orders', function($excel) use ($data) {
        
        $excel->sheet('Representative Orders', function($sheet) use ($data)
        {
          $sheet->fromArray($data);
          $sheet->freezeFirstRow();  
          $sheet->cells("M2:M20", function($cells) {            
            $cells->setFont(array(              
              'bold'       =>  true
            ));

          });
        });
      })->download($type);
      
    }

    public function view(Request $request, $enquiry_id = 0)
    {   
     
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $enquiry_id = base64_decode($enquiry_id);
        $enquiry_arr = $main_split_order_no = $split_order_arr = $orderCalculationData = [];


        $enquiry_obj = $this->RepresentativeLeadsModel->with(['leads_details.product_details.maker_details',
                                                               'leads_details',
                                                               'order_details',
                                                               'retailer_user_details.retailer_details',
                                                               'transaction_mapping',
                                                               'transaction_details',
                                                               'address_details',
                                                               'maker_details',
                                                               'representative_user_details',
                                                               'sales_manager_details',
                                                               'transaction_mapping_details',
                                                               'stripe_transaction_detail',
                                                               'stripe_transaction_data'
                                                             ])
                                                      ->where('order_no',$enquiry_id)
                                                      ->where('maker_id',$loggedInUserId)
                                                      ->first();

        $shippingCharges = 0;
        if($enquiry_obj)
        {
            $enquiry_arr = $enquiry_obj->toArray(); 
            $is_freeshipping = is_promocode_freeshipping($enquiry_arr['promo_code']);

            if (isset($enquiry_arr['split_order_id']) && $enquiry_arr['split_order_id'] != '')
            {

                $main_split_order_no = $this->RepresentativeLeadsModel->where('id',$enquiry_arr['split_order_id'])->first();
            }
            elseif (isset($enquiry_arr['is_split_order']) && $enquiry_arr['is_split_order'] == '1')
            {

                $split_order_arr = $this->RepresentativeLeadsModel->where('split_order_id',$enquiry_arr['id'])->get()->toArray();

            }
            $shippingCharges = $enquiry_arr['total_shipping_charges'];
                 
        }

        $enquiry_arr_id       = isset($enquiry_arr['id'])?$enquiry_arr['id']:0;
        $enquiry_arr_order_no = isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:'';
     
        $tracking_details = [];
        $tracking_no = 0;
        //dd($enquiry_arr_id,$enquiry_arr_order_no);
        if($enquiry_arr_id != 0 && $enquiry_arr_order_no != '')
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

            $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
        }
        
        $this->arr_view_data['enquiry_arr']             = $enquiry_arr;
        $this->arr_view_data['module_title']            = $this->module_title;
        $this->arr_view_data['split_order_arr']         = $split_order_arr;
        $this->arr_view_data['main_split_order_no']     = $main_split_order_no;
        $this->arr_view_data['page_title']              = 'Order Details';
        $this->arr_view_data['module_url_path']         = $this->module_url_path;
        $this->arr_view_data['is_freeshipping']         = $is_freeshipping;
        $this->arr_view_data['project_name']            = $this->site_setting_arr['site_name'];
        $this->arr_view_data['order_shipping_charge']   = $shippingCharges;
        $this->arr_view_data['tracking_details']        = $tracking_details;
        $this->arr_view_data['tracking_no']             = $tracking_no;
        $this->arr_view_data['orderCalculationData']    = $orderCalculationData;
        
        return view($this->module_view_folder.'.view',$this->arr_view_data);

    }


    /*
        Date : 23 Dec 2019
        Auth : Bhagyashri
        Desc : maintain order status when vendor ship order
    */

    public function ship_order(Request $request)
    {
        $order_no    = '';
        $response    = [];
        $order_id    = $request->order_id;
        $maker_id    = $request->maker_id;
        $retailer_id = $request->retailer_id;


        /*get loggedin user*/

        $user = Sentinel::check();

       
        if(isset($user))
        {
          $loggedInUserId = $user->id;
        }
        /*----------------*/

        /* Update Order Status */
        $order_status_update = $this->RepresentativeLeadsModel->where('id',$order_id)
                                               ->where('maker_id',$maker_id)
                                               ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s')]);


        /*get order details */

        $orders_details = $this->RepresentativeLeadsModel->where('id',$order_id)->first();

        if(isset($orders_details))
        {
           $order_no = $orders_details->order_no;
        }                                        

        if($order_status_update)
        {
            /*after shipping the order send notification to admin & retailer*/

            $admin_id = get_admin_id();

            //Get maker name 

            $first_name   = isset($user->first_name)?$user->first_name:"";
            $last_name    = isset($user->last_name)?$user->last_name:""; 
            
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $admin_id;

          
            $order_detail_url = url('/admin/leads/view/'.base64_encode($order_id));

            //$order_url        = '<a href="'.$order_detail_url.'">'.$order_no.'</a>';
              
           /* $notification_arr['description']  = 'Order No:'.$order_url.' has been shipped by '.$first_name.' '.$last_name;*/

            $notification_arr['description']  = 'Order No: '.$order_no.' has been shipped by '.$first_name.' '.$last_name;

            $notification_arr['title']        = 'Order Shipped';
            $notification_arr['type']         = 'admin';
            $notification_arr['link']         = $order_detail_url;

      
            $this->GeneralService->save_notification($notification_arr);



            /*send to retailer*/

            //Get maker name 
         
            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $retailer_id;

          
            $order_detail_url = url('/retailer/my_orders/order_summary/'.base64_encode($order_no).'/'.base64_encode($maker_id));
          

            //$order_url        = '<a href="'.$order_detail_url.'">'.$order_no.'</a>';
              
           /* $notification_arr['description']  = 'Order No:'.$order_url.' has been shipped by '.$first_name.' '.$last_name;*/

           $notification_arr['description']   = 'Order No: '.$order_no.' has been shipped by '.$first_name.' '.$last_name;

            $notification_arr['title']        = 'Order Shipped';
            $notification_arr['type']         = 'retailer';
            $notification_arr['link']         = $order_detail_url;



            $this->GeneralService->save_notification($notification_arr);


            /*--------------------------------------------------------------------------*/
            $response['status']      = 'success';
            $response['description'] = 'Order has been shipped.';
        }
        else
        {
            $response['status']      = 'warning';
            $response['description'] = 'Something went wrong, please try again.';
        }
        
        return response()->json($response);
    }


    public function saveOrderTrackDetails(Request $request)
    {
       try
        { 
            DB::beginTransaction();
            $arr_rules = [
                           'order_id'         => 'required',
                           'order_no'         => 'required',
                           'tracking_no'      => 'required',
                           'shipping_company' => 'required',
                           'maker_id'         => 'required',
                           'retailer_id'      => 'required'
                        ];

            $validator = Validator::make($request->all(),$arr_rules); 

            if($validator->fails())
            {        
               $response['status'] = 'warning';
               $response['description'] = "form validation failed please check all fields";

              return response()->json($response);
            }



             /*------store order tracking details----------*/  
             $data = []; 
             $user_id =$admin_id = 0;

             $order_id    = $request->input('order_id');
             $order_no    = $request->input('order_no');
             $company_id  = $request->input('shipping_company');
             $tracking_no = $request->input('tracking_no');
             $maker_id    = $request->input('maker_id');
             $retailer_id = $request->input('retailer_id');


             
             $data['company_id']  = isset($company_id)?$company_id:0;
             $data['order_no']    = isset($order_no)?$order_no:'';
             $data['order_id']    = isset($order_id)?$order_id:0;
             $data['tracking_no'] = isset($tracking_no)?$tracking_no:'';


             $result = $this->OrderTrackDetailsModel->create($data);  


              /*--------ship order-----------------------*/

            
              $user = Sentinel::check();

             
              if(isset($user))
              {
                $loggedInUserId = $user->id;
              }
           
              /* Update Order Status */

              $order_details = $this->RepresentativeLeadsModel->where('id',$order_id)->first();
              
              if ($order_details)
              {

                  if ($order_details['payment_term'] == 'Net30')
                  {
                      $order_status_update = $this->RepresentativeLeadsModel->where('id',$order_id)
                                                     ->where('maker_id',$maker_id)
                                                     ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s'),'payment_due_date'=>date('Y-m-d H:i:s', strtotime("+30 days"))]);
                  }
                  else
                  {

                      $order_status_update = $this->RepresentativeLeadsModel->where('id',$order_id)
                                                     ->where('maker_id',$maker_id)
                                                     ->update(['ship_status' => '1','ship_date' => date('Y-m-d H:i:s')]);
                  }
                 
              }
              
       
                  /*after shipping the order send notification to admin & retailer*/

                  $admin_id = get_admin_id();

                  //Get maker name 

                  $first_name   = isset($user->first_name)?$user->first_name:"";
                  $last_name    = isset($user->last_name)?$user->last_name:""; 
                  
                  $notification_arr                 = [];
                  $notification_arr['from_user_id'] = $loggedInUserId;
                  $notification_arr['to_user_id']   = $admin_id;

                
                  //$order_detail_url = url('/admin/leads/view/'.base64_encode($order_id));
               
                  //create shipping company url
         /*         if(isset($company_id) && $company_id==1)
                  {
                     $url = 'https://www.fedex.com/en-in/home.html';
                  } 
                  elseif(isset($company_id) && $company_id==2)
                  {
                    $url = "https://www.ups.com/in/en/Home.page";
                  }
                  elseif(isset($company_id) && $company_id==3)
                  {
                     $url = "https://www.usps.com/";
                  }
                  elseif(isset($company_id) && $company_id==4)
                  {
                     $url = "https://www.dhl.com/en.html";
                  }
                  else
                  {
                     $url = '';
                  }*/

                  if(isset($company_id) && $company_id==1)
                  {
                     //$url = 'https://www.fedex.com/en-in/home.html';
                     $url =  "http://www.fedex.com/apps/fedextrack/?action=track&tracknumbers='".$tracking_no."'";
                  } 
                  elseif(isset($company_id) && $company_id==2)
                  {
                    $url = "https://www.ups.com/in/en/Home.page";
                  }
                  elseif(isset($company_id) && $company_id==3)
                  {
                     $url = "https://www.usps.com/";
                  }
                  elseif(isset($company_id) && $company_id==4)
                  {
                     $url = "https://www.dhl.com/en.html";
                  }
                  else
                  {
                     $url = '';
                  }


                    
                  $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$first_name.' '.$last_name.' Tracking No: '.$tracking_no;

                  $notification_arr['title'] = 'Order Shipped';
                  $notification_arr['type']  = 'admin';
                  $notification_arr['link']  = $url;


                  $this->GeneralService->save_notification($notification_arr);



                  /*send to retailer*/

                  //Get maker name 
               
                  $notification_arr                 = [];
                  $notification_arr['from_user_id'] = $loggedInUserId;
                  $notification_arr['to_user_id']   = $retailer_id;

                
                  //create shipping company url
      /*            if(isset($company_id) && $company_id==1)
                  {
                     $url     = 'https://www.fedex.com/en-in/home.html';
                     $company = 'Fedex';
                  } 
                  elseif(isset($company_id) && $company_id==2)
                  {
                    $url     = "https://www.ups.com/in/en/Home.page";
                    $company = 'UPS';
                  }
                  elseif(isset($company_id) && $company_id==3)
                  {
                     $url = "https://www.usps.com/";
                     $company = 'USPS';
                  }
                  elseif(isset($company_id) && $company_id==4)
                  {
                     $url = "https://www.dhl.com/en.html";
                     $company = 'DHL';
                  }
                  else
                  {
                     $url     = '';
                     $company = '';
                  }*/

                  if(isset($company_id) && $company_id==1)
                  {
                     $url      =  "http://www.fedex.com/apps/fedextrack/?action=track&tracknumbers='".$tracking_no."'";
                      $company = 'Fedex';
                  } 
                  elseif(isset($company_id) && $company_id==2)
                  {
                     $url      = "https://www.ups.com/in/en/Home.page";
                     $company  = 'UPS';
                  }
                  elseif(isset($company_id) && $company_id==3)
                  {
                     $url      = "https://www.usps.com/";
                     $company  = 'USPS';
                  }
                  elseif(isset($company_id) && $company_id==4)
                  {
                     $url     = "https://www.dhl.com/en.html";
                     $company = 'DHL';
                  }
                  else
                  {
                     $url     = '';
                     $company = '';
                  }

               
                  //$order_detail_url = url('/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']));
                  $VendorStoreName = get_maker_company_name($loggedInUserId);
                      
                  $notification_arr['description']  = 'Order No:'.$order_details->order_no.' has been shipped by '.$VendorStoreName.' Tracking No: '.$tracking_no;

                  $notification_arr['title']  = 'Order Shipped';
                  $notification_arr['type']   = 'retailer';
                  $notification_arr['link']   =  $url;


                  $this->GeneralService->save_notification($notification_arr);

                // ---------------- check network connection is persistance -----------------  
                $connected = @fsockopen("www.google.com", 80); 
                $is_conn = "";
                if ($connected){
                  $is_conn = true; 
                  fclose($connected);
                }else{
                 $is_conn = false; 
                }

                if($is_conn == false)
                {
                  DB::rollback();
                  $response['status']      = 'warning';
                  $response['description'] = '...Oops network issue.please try again.';
                  return response()->json($response);
                }

                /*---------------------------------------------------------------------*/

                /*send shippment mail to the retailer*/

                $retailer_email  = $this->get_email($retailer_id);
                $tracking_url    = "<a href=".$url." target='_blank'>".$tracking_no."</a>";

                $html = 'Your order has been shipped. Order No: ';

                $arr_mail_data   = $this->send_order_ship_mail($retailer_email,$order_no,$company,$tracking_url,$html); 

                $email_status  = $this->EmailService->send_mail($arr_mail_data);


                /*send shippment mail to the admin*/
                $admin_email     = $this->get_email(1);
                $tracking_url    = "<a href=".$url." target='_blank'>".$tracking_no."</a>";


                $html = 'Order has been shipped. Order No: ';

                $arr_mail_data   = $this->send_order_ship_mail($admin_email,$order_no,$company,$tracking_url,$html); 
                

                $email_status  = $this->EmailService->send_mail($arr_mail_data);

                DB::commit();

              if($result)
              {
                $response['status']      = 'success';
                $response['description'] = 'Order tracking details has been added.';

                return response()->json($response);
              }
              else
              {
                 $response['status']      = 'error';
                 $response['description'] = 'Something went wrong, please try again.';
                 return response()->json($response);

              }

        }
        catch(Exception $e)
        {   
            DB::rollback();

            $response['status']      = 'error';
            $response['description'] = $e->getMessage();
            return response()->json($response);

        }   


    }



    public function send_order_ship_mail($email=false,$order_no=false,$comapny=false,$tracking_url=false,$html=false)
    {     
        $user = $this->get_user_details($email);
        
        if(isset($user) && $user)
        {
            $arr_user = $user->toArray();  
            
            $site_setting_obj = SiteSettingModel::first();
            if($site_setting_obj)
            {
                $site_setting_arr = $site_setting_obj->toArray();            
            }

            $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';
            
            $arr_built_content = ['USER_NAME'     => $arr_user['first_name'],
                                  'ORDER_NO'      => isset($order_no)?$order_no:'',
                                  'COMPANY'       => isset($comapny)?$comapny:'',
                                  'TRACKING_URL'  => isset($tracking_url)?$tracking_url:'',
                                  'HTML'          => isset($html)?$html:'',
                                  'SITE_URL'      => $site_name,
                                  'PROJECT_NAME'  => $site_name
                                 ];

            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '53';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['user']              = $arr_user;
            $arr_mail_data['arr_user']              = $arr_user;

            return $arr_mail_data;
            
        }    

        return false;
    }

    public function  get_user_details($email)
    {
        $credentials = ['email' => $email];
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        if($user)
        {
          return $user;
        }
        return false;
    }

    public function get_email($id)
    {
      $email = $this->UserModel->where('id',$id)->pluck('email')->first();

      return $email;
    }

}