<?php
namespace App\Common\Services;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\AddressModel;
use App\Models\ProductsModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RepresentativeMakersModel;  
use App\Models\TransactionMappingModel;
use App\Models\MakerModel;
use App\Models\TempBagModel;
use App\Models\ProductDetailsModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\RetailerModel; 
use App\Models\CustomerModel; 
use App\Models\RepresentativeModel; 
use App\Models\RetailerQuotesProductModel; 
use App\Common\Services\MyCartService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;
use DB;
use Sentinel;
use Session;
use DateTime;


class orderDataService 
{

   public function __construct(
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                AddressModel $AddressModel,
                                ProductsModel $ProductsModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeMakersModel $RepresentativeMakersModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                MakerModel $MakerModel,
                                ProductDetailsModel $ProductDetailsModel,
                                TempBagModel $TempBagModel,
                                RetailerModel $RetailerModel,
                                CustomerModel $CustomerModel,
                                RepresentativeModel $RepresentativeModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                MyCartService $MyCartService,
                                HelperService $HelperService,
                                CommissionService $CommissionService
                               )
      {
        $this->UserModel                         = $UserModel;
        $this->RoleModel                         = $RoleModel;
        $this->AddressModel                      = $AddressModel;
        $this->ProductsModel                     = $ProductsModel;
        $this->RoleUsersModel                    = $RoleUsersModel;
        $this->RepresentativeLeadsModel          = $RepresentativeLeadsModel;
        $this->RepresentativeProductLeadsModel   = $RepresentativeProductLeadsModel;
        $this->RepresentativeMakersModel         = $RepresentativeMakersModel;
        $this->TransactionMappingModel           = $TransactionMappingModel;
        $this->RetailerQuotesModel               = $RetailerQuotesModel;
        $this->CustomerQuotesModel               = $CustomerQuotesModel;
        $this->CustomerQuotesProductModel        = $CustomerQuotesProductModel;
        $this->MakerModel                        = $MakerModel;  
        $this->RetailerModel                     = $RetailerModel;                 
        $this->CustomerModel                     = $CustomerModel;                 
        $this->ProductDetailsModel               = $ProductDetailsModel;
        $this->RepresentativeModel               = $RepresentativeModel;
        $this->RetailerQuotesProductModel        = $RetailerQuotesProductModel;
        $this->TempBagModel                      = $TempBagModel;
        $this->MyCartService                     = $MyCartService;
        $this->CommissionService                 = $CommissionService; 
        $this->HelperService                     = $HelperService;
      }

    public function get_order_list($arr_search_column=false,$module_data=false,$sales_manager_id=false,$orderBy=false,$confirmed_flag=false)
    {
      $user = \Sentinel::check();
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

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $maker_table           = $this->MakerModel->getTable();
      $prefix_maker_table    = DB::getTablePrefix().$maker_table;

      $rep_table = $this->RepresentativeModel->getTable();
      $prefix_rep_table = DB::getTablePrefix().$rep_table;

      $representative_product_leads =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

      $lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.
                              $prefix_maker_table.'.company_name,'.
                              $prefix_retailer_table.'.store_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                                   )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id');
                            
                            if(isset($confirmed_flag) && $confirmed_flag==1)
                           {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                           }  

                           if($orderBy == 'reps' && $orderBy != false)
                           {
                             $lead_obj = $lead_obj->leftJoin($rep_table,$rep_table.'.user_id','=',$representative_leads.'.representative_id')
                              ->where($rep_table.'.sales_manager_id','=',$loggedInUserId);

                           }
                           else
                           {
                             if(isset($sales_manager_id) && $sales_manager_id!='')
                             {  
                                $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id);
                             }
                           }

                           $lead_obj=$lead_obj->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                           // ->leftJoin($prefix_transaction_mapping,$prefix_transaction_mapping.'.order_no','=',$prefix_representative_leads_tbl.'.order_no')

                            // ->leftJoin($prefix_transaction_mapping,$prefix_transaction_mapping.'.order_id','=',$prefix_representative_leads_tbl.'.id')

                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })


                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           ->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.is_confirm','!=',0)

                           ->where($representative_leads.'.maker_id','!=',0);

                           
                          
      /* ---------------- Filtering Logic ----------------------------------*/  

     if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {
          $search_term      = $arr_search_column['q_order_no'];
          $lead_obj = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
      } 
      if(isset($arr_search_column['q_lead_date']) && $arr_search_column['q_lead_date']!="")
      {
          $search_term      = $arr_search_column['q_lead_date'];
          $date             = DateTime::createFromFormat('m-d-Y',$search_term);
          $date             = $date->format('Y-m-d');
          //$search_term    = date('Y-m-d',strtotime($search_term));

          $lead_obj = $lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
      } 

      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term      = $arr_search_column['q_customer_name'];
          $lead_obj =  $lead_obj->having('store_name','LIKE', '%'.$search_term.'%');
      }

      
      if(isset($arr_search_column['q_representative_name']) && $arr_search_column['q_representative_name']!="")
      {
          $search_term      = $arr_search_column['q_representative_name'];
          $lead_obj =  $lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');
      }


      if(isset($arr_search_column['q_maker_name']) && $arr_search_column['q_maker_name']!="")
      {

          $search_term      = $arr_search_column['q_maker_name'];
          $lead_obj =  $lead_obj->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_lead_status']) && $arr_search_column['q_lead_status']!="")
      {
        
          $search_term      = $arr_search_column['q_lead_status'];
          // if ($search_term == "2") {
       

          //   $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term)->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id)->orwhere($prefix_representative_leads_tbl.'.is_confirm','=', 0)->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id);
          // }
          // else{
           
            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

          // }
         

      }

      if(isset($arr_search_column['q_shipping_status']) && $arr_search_column['q_shipping_status']!="")
      {  
          $search_term  = $arr_search_column['q_shipping_status'];
          $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term);
      }



      if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
      {
          $search_term  = $arr_search_column['q_payment_status'];
          /* $lead_obj     = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','=', $search_term);*/

          if($search_term == 1)
          {
             
              $lead_obj = $lead_obj->whereNotExists(function($query) use ($prefix_transaction_mapping,$prefix_representative_leads_tbl)
                    {

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
             $lead_obj = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','=',$search_term);
          }
          
      }


      if(isset($arr_search_column['q_total_costing_retail']) && $arr_search_column['q_total_costing_retail']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_retail'];
          $lead_obj =  $lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_wholesale'];

          $search_term   = intval($search_term);          
          $lead_obj =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
      }

      /*from and to date order filter*/
      if(isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="" && isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!="")
      {
          $search_term_from_date  = $arr_search_column['q_from_date'];
          $search_term_to_date    = $arr_search_column['q_to_date'];
          $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
          $from_date              = $from_date->format('Y-m-d');
          $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
          $to_date                = $to_date->format('Y-m-d');
      
          $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);
          $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);
      }


      return $lead_obj;
    }

     /* Create lead */
    public function store_lead($request)
    { 
        $role_arr = [];
        try
        {
          $is_update = false;
          $arr_rules = [];        
          $loggedInUserId = 0;
          $form_data = $request; 
          
          $order_no  = isset($form_data['order_no'])?base64_decode($form_data['order_no']):false;
          $representative_leads_id = $order_no;

          DB::beginTransaction();

          $user = Sentinel::check();

          if($user)
          {
              $loggedInUserId = $user->id;

              /*get role of logedin user*/

              $role_details = $this->RoleUsersModel->where('user_id',$user->id)->with(['role_name'])->first();
              
              if(isset($role_details))
              {
                  $role_arr = $role_details->toArray(); 
              }
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
                elseif(in_array($form_data['maker_id'], $all_maker_ids) == false)
                {
                  $sales_manager_id = 0;

                  if($user && $user->inRole('sales_manager'))
                  {
                      $loggedInUserId   = $user->id;
                      $sales_manager_id = $loggedInUserId;
                  } 

                    
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
              $representative_lead_product_arr['qty'] = $qty;
              $representative_lead_product_arr['sku'] = $sku; 
              $representative_lead_product_arr['order_no'] = $representative_leads_id; 

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
                      $actual_shipping_charges =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;

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

                      $actual_shipping_charges =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                      
                      $shipping_charges = $actual_shipping_charges;

                      $shipping_charges_discount = 0;

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
                      $actual_shipping_charges =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                     
                      $shipping_charges = $actual_shipping_charges;
                    
                      $shipping_charges_discount = 0;

                      $ship_charge_single_product =  $actual_shipping_charges;
                   

                  }
                  else
                  {
                     
                      $shipping_charges = 0;
                      $shipping_charges_discount = 0;
                      $ship_charge_single_product = 0;

                  
                  }

                  $representative_lead_product_arr['shipping_charges'] = $shipping_charges;
                  $representative_lead_product_arr['shipping_charges_discount'] = $shipping_charges_discount;
                  $representative_lead_product_arr['product_shipping_charge']    = $ship_charge_single_product;

              }

              if(isset($product_arr['shipping_type']) && $product_arr['shipping_type'] == 3) 
              {
                  if($form_data['total_wholesale_price'] >= $product_arr['minimum_amount_off'])
                  {
                      $actual_shipping_charges =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                      $shipping_charges = $actual_shipping_charges - (float)$product_arr['off_type_amount'];
                     
                      $shipping_charges_discount = (float)$product_arr['off_type_amount'];

                      $ship_charge_single_product = $actual_shipping_charges;

                  }
                  else
                  {
                       $actual_shipping_charges =  isset($product_arr['shipping_charges'])?(float)$product_arr['shipping_charges']:0;
                       $shipping_charges = $actual_shipping_charges;
                       $shipping_charges_discount= 0;
                       $ship_charge_single_product = $actual_shipping_charges;


                  }

                  $representative_lead_product_arr['shipping_charges']          = $shipping_charges;
                  $representative_lead_product_arr['shipping_charges_discount'] = $shipping_charges_discount;
                  $representative_lead_product_arr['product_shipping_charge']    = $ship_charge_single_product;


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


              /*------------------------------------------*/

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
                                                                                
                                                                                
                   
                    $total_product_discount = array_sum((array_column($order_product_details,'product_discount')));
                    $total_shipping_charges = array_sum((array_column($order_product_details,'shipping_charges')));
                    $total_shipping_charges_discount = array_sum((array_column($order_product_details,'shipping_charges_discount')));

                    $total_product_shipping_charges = array_sum((array_column($order_product_details,'product_shipping_charge')));


               
                    $data = [];
                    $data['total_product_discount']  = $total_product_discount;
                    $data['total_product_shipping_charges']  = $total_product_shipping_charges;
                    $data['total_shipping_charges']  = $total_shipping_charges;
                    $data['total_shipping_discount'] = $total_shipping_charges_discount;


                    $this->RepresentativeLeadsModel->where('order_no',$order_no)->update($data);

              

              } 

              if($lead_product)
              {

                  $amount = get_maker_total_amount($form_data['maker_id'],$order_no);
          
                  DB::commit();


                  /*------------------------------------------------------*/

                  $response['status']      = 'SUCCESS';               
                  $response['order_no']    = $order_no;

                  return $response;
              }
              else
              {
                 
                  DB::rollback();
                  $response['status']      = 'FAILURE';

                  return $response;
              }
            }
            else
            {
              
              DB::rollback();
              $response['status']      = 'FAILURE';

              return $response;
            }  

          }
          else
          {
            
            DB::rollback();
            $response['status']      = 'FAILURE';

            return $response;
          }
        }
        catch(Exception $e)
        {  
         
            DB::rollback();
            
            $response['status']      = 'FAILURE';
            $response['description'] = $e->getMessage();

            return $response;
        }
    }

    public function update_product_qty($request)
    {

      try
      {
          //update lead product details
          $form_data = $data = [];
          $form_data = $request->all();
       
          $order_no        = isset($form_data['order_no'])?base64_decode($form_data['order_no']):false;
          $product_id      = base64_decode($form_data['pro_id']);
          $pro_sku_id      = base64_decode($form_data['pro_sku_id']);
          $qty             = $request->input('qty',"0");
           
          $arr_product = get_product_details($product_id);

          $produt_retail_price = isset($arr_product['retail_price'])?$arr_product['retail_price']:"";
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
       
          $shipping_charges  = isset($shipping_arr['shipping_charge'])?$shipping_arr['shipping_charge']:0;
          $shipping_discount = isset($shipping_arr['shipping_discount'])?$shipping_arr['shipping_discount']:0;
          $pro_lead_data['shipping_charges']    = $shipping_charges-$shipping_discount;
           
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

          $data['total_product_discount']          = $total_product_discount;
          $data['total_product_shipping_charges']  = $total_product_shipping_charges;
          $data['total_shipping_charges']          = $total_shipping_charges;
          $data['total_shipping_discount']         = $total_shipping_charges_discount;

           $this->RepresentativeLeadsModel->where('order_no',$order_no)->update($data);


          if($update_product_qty)
          {
            $maker_data = $this->RepresentativeProductLeadsModel
                                      ->where('order_no',$order_no)
                                      ->where('product_id',$product_id)
                                      ->first(['maker_id']);

            get_maker_total_amount($maker_data->maker_id,$order_no);

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
            
            $commission       = isset($form_data['comission'])?$form_data['comission']:0;   
            
            $total_commission_wholesale = ($commission / 100) * $total_pro_wholesale;
            
            //Comission less from total whole price..
            $comission_less_wholesale =  $total_pro_wholesale - $total_commission_wholesale; 
                            
            $arr_responce = [];

            $arr_responce['order_no']       = $order_no;
            $arr_responce['id']             = $product_id;
            $arr_responce['tot_pro_retail'] = $tot_retail;
            $arr_responce['tot_pro_whole']  = $tot_wholesale;
            $arr_responce['tot_whole']      = $total_pro_reatil;
            $arr_responce['tot_retail']     = $total_pro_wholesale;
            $arr_responce['tot_commi_less_wholesale'] = $comission_less_wholesale;  
            
            DB::commit();
            $response['status']         = 'SUCCESS';               
            $response['arr_responce']   = $arr_responce;    
            return $response;
          } 
      }
      catch(Exception $e)
      {
          DB::rollback();
          
          $response['status']      = 'FAILURE';
          $response['description'] = $e->getMessage();

          return $response;
      }
  }

  public function delete_all_products($enc_order_no = 0)
  {
      $order_no = 0;
      $order_no = base64_decode($enc_order_no);
   
      //check is lead confirm or not
      $lead_obj = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();

      if($lead_obj)
      {
        if($lead_obj->is_confirm == 1)
        { 
          $response['status']      = 'FAILURE';
          $response['description'] = 'This product is from confirm order,You can not delete this product.';

          return $response;
        }
      }
      
      DB::beginTransaction();

      try
      {

        $is_delete = $this->RepresentativeProductLeadsModel->where('order_no',$order_no)                                               ->delete();
        //update total prices
        $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                       ->update(['total_retail_price' => 0,
                                                 'total_wholesale_price'=>0]);
       
        DB::commit();

        $response['status']      = 'SUCCESS';
        $response['description'] = 'Product has been deleted from cart.';
        return $response;
                 
      }
      catch (\Exception $e) {
      
        DB::rollback();
        
        $response['status']      = 'FAILURE';
        $response['description'] = $e->getMessage();
        return $response;
      }
  }

  public function delete_product_from_bucket($enc_order_no,$sku_no)
  {
      $product_lead_arr = $retail_price       = $wholesale_price       = [];         
      $lead_id          = $total_retail_price = $total_wholesale_price = 0;
      $order_no         = base64_decode($enc_order_no);
      $sku_no           = base64_decode($sku_no);
      $maker_id         = "";

      try
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
              $response['status']  = 'FAILURE';
              $response['description'] = 'This product is from confirm order,You can not delete this product.';
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

            $update_maker_id = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                              ->where('maker_id',$maker_id)
                                                              ->delete();                                                  
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
          
          $response['status']      = 'SUCCESS';
          $response['description'] = 'Product has been deleted from cart.';
          return $response;
        }
        else
        {
          DB::rollback();
          $response['status']      = 'FAILURE';
          $response['description'] = 'Error occurred while deleting product.';
          return $response;
        }
          
      }catch(Exception $e)
      {

        $response['status']      = 'FAILURE';
        $response['description'] = $e->getMessage();
        return $response; 
      }
  }

  public function delete_product_from_bucket_no($enc_order_no,$sku_no)
  {
      $product_lead_arr = $retail_price       = $wholesale_price       = [];         
      $lead_id          = $total_retail_price = $total_wholesale_price = 0;
      $order_no         = base64_decode($enc_order_no);
      $sku_no           = base64_decode($sku_no);
      $maker_id         = "";

      try
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
              $response['status']  = 'FAILURE';
              $response['description'] = 'This product is from confirm order,You can not delete this product.';
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
            $update_maker_id = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                              ->where('maker_id',$maker_id)
                                                              ->update(['maker_id'=>0]);

            // $update_maker_id = $this->RepresentativeLeadsModel->where('order_no',$order_no)
            //                                                   ->where('maker_id',$maker_id)
            //                                                   ->delete();                                                  
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
          
          $response['status']      = 'SUCCESS';
          $response['description'] = 'Product has been deleted from cart.';
          return $response;
        }
        else
        {
          DB::rollback();
          $response['status']      = 'FAILURE';
          $response['description'] = 'Error occurred while deleting product.';
          return $response;
        }
          
      }catch(Exception $e)
      {

        $response['status']      = 'FAILURE';
        $response['description'] = $e->getMessage();
        return $response; 
      }
  }

  public function order_summary($order_no=false,$enc_maker_id=false)
  {
    $arr_data = [];
    $order_no = isset($order_no)?base64_decode($order_no):false;
    $maker_id = isset($enc_maker_id)?base64_decode($enc_maker_id):false;

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
                                                 ->where('maker_id',$maker_id)
                                                 ->first();
                                          

         }
         else
         {
              $obj_data = $this->RepresentativeLeadsModel->with(['maker_data.maker_details',
                                                          'order_details.shop_settings',
                                                          'order_details.get_product_min_qty',
                                                          'order_details.maker_details',
                                                          'order_details',
                                                          'order_details.product_details.brand_details',
                                                          'address_details','transaction_mapping',
                                                          'representative_user_details',
                                                          'sales_manager_details'
                                                        ])
                                                 ->where('order_no',$order_no)
                                                 ->first();

         }
      


      if($obj_data)
      {
        $arr_data = $obj_data->toArray();
      
      }
      

    }
    
    return $response['arr_data'] = $arr_data;
  }

   public function save_customer_address($form_data)
   {
        $is_update = false;
        $is_addr_exist = 0;

        try
        {
            DB::beginTransaction();

            $user = Sentinel::check();

            if($user)
            {
                $loggedInUserId = $user->id;
            }

            if(isset($form_data['order_no']) && $form_data['order_no'] != '')
            {
              $order_no = $form_data['order_no'];
              $is_update = true;
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
                      'bill_zip_code'         => $form_data['bill_zip'],
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
                     'bill_street_address'    => $form_data['billing_street_address'],
                     'bill_suit_apt'          => $form_data['billing_suite_apt'],
                     'ship_street_address'    => $form_data['shipping_street_address'],
                     'ship_suit_apt'          => $form_data['shipping_suite_apt']

                    ];


            if(isset($customer_obj))
            {


              //update data to address table if already address present otherwise create new address record
              $user_id                 = $customer_obj->id;
              $is_save = '';

              if($is_update)
              {
                $is_addr_exist = $this->AddressModel->where('user_id',$user_id)
                                                    ->where('order_no',$order_no)
                                                    ->count()>0;
              }

              $customer_arr['user_id'] = $user_id;

              if($is_addr_exist)
              {
                $is_update = $this->AddressModel->where('user_id',$user_id)
                                                ->where('order_no',$order_no)
                                                ->update($customer_arr);
              }
              else
              {
                

                $is_save = $this->AddressModel->create($customer_arr);
              }

               $module_id = $customer_obj->id;
            }
            else
            {
                $response['status']      = 'FAILURE';               
                $response['description'] = 'Please select valid customer for order creation.';

                return $response;
            } 

            $representative_id = 0;
            $sales_manager_id = 0;

            if($user && $user->inRole('representative'))
            {   
                $loggedInUserId = $user->id;
                $representative_id = $loggedInUserId;

            }
            elseif($user && $user->inRole('sales_manager'))
            {
                $loggedInUserId = $user->id;
                
                $sales_manager_id = $loggedInUserId;
                
            }

            if($is_save)
            {
            
              //create one blank lead entry into representative_leads table
              $representative_lead_arr = [];
              $representative_lead_arr = [

                  'representative_id' => $representative_id,
                  'sales_manager_id'  => $sales_manager_id, 
                  'payment_term'      => '',                        
                  'retailer_id'       => $user_id,
                  // 'admin_commission'  => $this->CommissionService->get_admin_commission($form_data['maker_id']),
                  'order_no'          => $order_no
              ];

               $lead = $this->RepresentativeLeadsModel->create($representative_lead_arr);

               $lead_id = $lead->id;

               DB::commit();
               $response['status']     = 'SUCCESS';
               $response['lead_id']    = $lead_id;
               $response['order_no']   = $lead['order_no'];
               $response['module_id']  = $module_id;

               return $response;
            }elseif($is_update)
            {
              $ord_no = isset($form_data['order_no'])?$form_data['order_no']:'';
              $lead_obj = $this->RepresentativeLeadsModel->where('order_no',$ord_no)                                        ->first();
              if($lead_obj)
              {
                $lead_id = isset($lead_obj->id)?$lead_obj->id:'';
              }

               DB::commit();
               $response['status']     = 'SUCCESS';
               $response['lead_id']    = isset($lead_id)?$lead_id:0;
               $response['order_no']   = isset($lead_obj->order_no)?$lead_obj->order_no:'';
               $response['module_id']  = $module_id;
               
               return $response;
            }
            else
            {
                 DB::rollback();

                $response['status']      = 'FAILURE';               
                $response['description'] = 'Something went wrong,please tey again.';

                return $response;
            }
          }
          catch(Exception $e)
          {
            DB::rollback();
            
            $response['status']      = 'FAILURE';
            $response['description'] = $e->getMessage();

            return response()->json($response);
          }
   }

//old function
/*  public function find_product($order_no)
  {
      $arr = [];
      $user = Sentinel::check();
      $loggedIn_userId = 0;

      if($user)
      {
          $loggedIn_userId = $user->id;
      }   
     
      $maker_details_arr = [];
      $order_no = base64_decode($order_no);

      //get maker details
      $lead_obj = $this->RepresentativeLeadsModel->whereHas('leads_details',function($q) use($order_no){
                                                        $q->where('order_no',$order_no);
                                                    })
                                                
                                                 ->where('order_no',$order_no)
                                                 ->first();  
                                                
      if($lead_obj)
      {
        $maker_details_obj = $this->UserModel->where('id',$lead_obj->maker_id)
                                             ->with(['maker_details','shop_settings','store_details','maker_comission'=>function($query) use($loggedIn_userId)
                                             {
                                                $query->where('representative_id',$loggedIn_userId);
                                             }])
                                             ->first();

        if($maker_details_obj)
        {
          $maker_details_arr = $maker_details_obj->toArray();
        }

      }

   
      $lead_product_data = $this->RepresentativeProductLeadsModel
                                               ->with([ 
                                                        'product_details',
                                                        'maker_details',
                                                        'get_product_min_qty',
                                                        'product_details.shop_settings'
                                                      ])
                                               ->where('order_no',$order_no)
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
                    
              $arr_d                    = $product['product_details'];
              $arr_d['qty']             = $product['qty'];
              $arr_d['sku']             = $product['sku'];
              $arr_d['wholesale_price'] = $product['wholesale_price'];
              $arr_d['product_min_qty'] = isset($product['get_product_min_qty']['product_min_qty'])?$product['get_product_min_qty']['product_min_qty']:'';
              
              $arr[$company][] = $arr_d;


              }
            }
        }
      }
      
      return $arr;
     
  }*/

  public function find_product($order_no)
  {
      $arr = [];
      $user = Sentinel::check();
      $loggedIn_userId = 0;

      if($user)
      {
          $loggedIn_userId = $user->id;
      }   
     
      $maker_details_arr = [];
      $order_no = base64_decode($order_no);

      //get maker details
      $lead_obj = $this->RepresentativeLeadsModel->whereHas('leads_details',function($q) use($order_no){
                                                        $q->where('order_no',$order_no);
                                                    })
                                                
                                                 ->where('order_no',$order_no)
                                                 ->first();  
                                                
      if($lead_obj)
      {
        $maker_details_obj = $this->UserModel->where('id',$lead_obj->maker_id)
                                             ->with(['maker_details','shop_settings','store_details','maker_comission'=>function($query) use($loggedIn_userId)
                                             {
                                                $query->where('representative_id',$loggedIn_userId);
                                             }])
                                             ->first();

        if($maker_details_obj)
        {
          $maker_details_arr = $maker_details_obj->toArray();
        }

      }

   
      $lead_product_data = $this->RepresentativeProductLeadsModel
                                               ->with([ 
                                                        'product_details',
                                                        'maker_details',
                                                        'get_product_min_qty',
                                                        'product_details.shop_settings'
                                                      ])
                                               ->where('order_no',$order_no)
                                               ->get()
                                               ->toArray();

      /*if products is deactivated then delete from produc lead table*/
      $product_count = 0;
      $new_product_arr = [];
     

      if(isset($lead_product_data) && count($lead_product_data)>0)
      {

        foreach($lead_product_data as $key => $leads)
        {
            if( $leads['product_details']['product_status'] == 0  ||
                $leads['product_details']['is_active'] == 0 ||
                $leads['product_details']['product_complete_status'] != 4
              )

            { 
               $is_delete = $this->RepresentativeProductLeadsModel->where('product_id',$leads['product_id'])->delete();

             
            }
            else
            {  
               $new_product_arr[] = $leads;
            }

 
            //and if all product is deleted from order then that order should be deleted

            $product_count =  $this->RepresentativeProductLeadsModel
                                   ->where('maker_id',$leads['maker_id'])
                                   ->where('order_no',$leads['order_no'])
                                   ->count();
    
            if($product_count == 0)
            {
               $this->RepresentativeLeadsModel->where('maker_id',$leads['maker_id'])
                                              ->where('order_no',$leads['order_no'])
                                              ->delete();
            }

        }
    

      }

      $lead_product_data = $new_product_arr;

      /*-----------------------------------------------------------------*/                                         
                                              
      $arr_p_data = $arr = $arr_p_data = $company_names =[];

      $total_wholesale_price = $total_shipping_discount = $total_shipping_charges = $total_product_discount= $total_product_ship_charges = $final_total = 0;

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

              $total_wholesale_price += $product['wholesale_price'];
              $total_shipping_discount += $product['shipping_charges_discount'];
              $total_shipping_charges += $product['shipping_charges'];
              $total_product_ship_charges += $product['product_shipping_charge'];
              $total_product_discount  += $product['product_discount'];
              
              $final_total  = $total_wholesale_price+$total_product_ship_charges-$total_shipping_discount-$total_product_discount;

                      
              $arr_d                    = $product['product_details'];
              $arr_d['qty']             = $product['qty'];
              $arr_d['sku']             = $product['sku'];
              $arr_d['wholesale_price'] = $product['wholesale_price'];
              $arr_d['product_min_qty'] = isset($product['get_product_min_qty']['product_min_qty'])?$product['get_product_min_qty']['product_min_qty']:'';
              
              $arr[$company][] = $arr_d;


              /*update into representative leads table*/

              $data = [];
              $data['total_wholesale_price']              =  $final_total;
              $data['total_product_discount']             =  $total_product_discount;
              $data['total_shipping_charges']             =  $total_shipping_charges;
              $data['total_product_shipping_charges']     =  $total_product_ship_charges;
              $data['total_shipping_discount']            =  $total_shipping_discount;

              $this->RepresentativeLeadsModel->where('maker_id',$product['maker_id'])
                                             ->where('order_no',$product['order_no'])
                                             ->update($data);

              /*---------------------------------------*/
              

              }
              else
              {
                 $total_wholesale_price = $total_shipping_discount = $total_shipping_charges = $total_product_discount= $total_product_ship_charges = $final_total = 0;
              }

            }
 

        }
      }
    
      return $arr;
     
  }

  public function get_product_details($enc_id)
  {
    
      $product_id = base64_decode($enc_id);


      $user = Sentinel::check();
      $loggedIn_userId = 0;

      if($user)
      {
          $loggedIn_userId = $user->id;
      }  

      $is_product_from_linked_vendor = $this->is_product_from_linked_vendor($product_id,$loggedIn_userId);


      if($product_id)
      {
          $arr_data = [];

          $obj_data = $this->ProductsModel->with(['productDetails','brand_details','maker_details','shop_settings','categoryDetails.subcategory_details','inventoryDetails'])
                                          ->where('id',$product_id)
                                          ->first();

          if($obj_data)
          {
              $arr_data = $obj_data->toArray();
              if($is_product_from_linked_vendor=='true')
              {
                  $arr_data['product_price'] = $arr_data['unit_wholsale_price'];
                  $arr_data['price_type']    = "Wholesale Price";
              }

              else
              {
                 $arr_data['product_price'] = $arr_data['retail_price'];
                 $arr_data['price_type']    = "Retail Price";
              }

              
              //Get product comission
              $product_maker_id = isset($arr_data['user_id'])?$arr_data['user_id']:"0";
              $obj_commission = $this->RepresentativeMakersModel->where('maker_id',$product_maker_id)
                                     ->where('representative_id',$loggedIn_userId)->first(['commission']);
              $comission = isset($obj_commission->commission)?$obj_commission->commission:"0";   

          }
         
          if(isset($arr_data) && count($arr_data)>0)
          {
            $response['comission']   = $comission;
            $response['arr_data']    = $arr_data;
            $response['status']      = "SUCCESS";
          }
          else
          {
            $response['arr_data']   = $arr_data;
            $response['status']     = "FAILURE";  
          }
      }
      else
      {
        $response['status']     = "FAILURE";  
      }

      return $response;
   }

  public function get_customer_detail($customer)
  {
    
      $customer_arr   = [];
    
      $role = Sentinel::findRoleBySlug('retailer');
      $role_id = 0;

      if($role)
      {
        $role_id = $role->id;
      }

      $customer_obj = $this->UserModel->where('id',$customer)
                                      ->with(['address_details'])
                                      ->with(['retailer_details'])
                                      ->with(['country_details'])
                                      
                                      ->whereHas('role_details',function($q) use($role_id){
                                           $q->where('role_id',$role_id);                
                                      })

                                     ->where('status',1)
                                     ->first();

      if($customer_obj)
      {
        $customer_arr = $customer_obj->toArray();
      }
      
  

      if(count($customer_arr)>0)
      {
      
        $response['customer_arr'] = $customer_arr;
        $response['status']       = "SUCCESS";
      }
      else
      {
       
        $response['customer_arr'] = $customer_arr;
        $response['status']       = "FAILURE";  
      }

      return $response;
      
   }


    public function store_order_address($request = false)
    {
     
      $form_data = $request->all();
    
      $response = false;
      if($form_data)
      {
        try
        {
          $is_update = false;
          $loggedInUserId = 0;
          $is_addr_exist = 0;

          $user = Sentinel::check();
          if($user)
          {
            $loggedInUserId = $user->id;

          }

          if(isset($form_data['order_no']) && $form_data['order_no']!= '')
          {
            $order_no = $form_data['order_no'];
            // dd($order_no);
            $is_update = true;
          } 
          else
          {
            $order_no = str_pad('J2',  10, rand('1234567890',10)); 
          }

            $customer_obj = $this->UserModel->where('email',$form_data['bill_email'])->first();

            $str = $form_data['bill_name'];
            $bill_name = explode(" ",$str);

            $str_ship = $form_data['ship_name'];
            $ship_name = explode(" ",$str_ship);
            /*dd($bill_name);*/
            $customer_arr = [

                          'bill_first_name'       => $bill_name[0],
                          'bill_last_name'        => $bill_name[1],
                          'bill_email'            => $form_data['bill_email'],
                          'bill_mobile_no'        => $form_data['bill_mobile_no'],
                          'bill_complete_address' => isset($form_data['bill_complete_addr'])?$form_data['bill_complete_addr']:'',
                          'bill_city'             => $form_data['bill_city'],
                          'bill_state'            => $form_data['bill_state'],
                          'bill_zip_code'         => $form_data['bill_zip'],
                          'ship_first_name'       => $ship_name[0],
                          'ship_last_name'        => $ship_name[1],
                          'ship_email'            => $form_data['ship_email'],
                          'ship_mobile_no'        => $form_data['ship_mobile_no'],
                          'ship_complete_address' => isset($form_data['ship_complete_addr'])?$form_data['ship_complete_addr']:'',
                          'ship_city'             => $form_data['ship_city'],
                          'ship_state'            => $form_data['ship_state'],
                          'ship_zip_code'         => $form_data['ship_zip_code'],
                          'order_no'              => $form_data['order_no'],
                          'bill_street_address'   => $form_data['bill_street_address'],
                          'ship_street_address'   => $form_data['ship_street_address'],
                          'bill_suit_apt'         => $form_data['bill_suit_apt'],
                          'ship_suit_apt'         => $form_data['ship_suit_apt'],
                          'ship_country'          => $form_data['ship_country'],
                          'bill_country'          => $form_data['bill_country']
                        ];

        
                        
            if(isset($customer_obj))
            {
                $user_id = $customer_obj->id;
  
                if($is_update)
                {
                  $is_addr_exist = $this->AddressModel->where('user_id',$user_id)
                                  ->where('order_no',$order_no)
                                  ->count()>0;

               
                }
                if($is_addr_exist)
                {
                  $is_save = $this->AddressModel->where('user_id',$user_id)
                              ->where('order_no',$order_no)
                              ->update($customer_arr);

                  
                }
                else
                {
                    $is_save = $this->AddressModel->create($customer_arr);
                }

                $module_id = $customer_obj->id;

            }
            else
            {
                $response['status']      = 'FAILURE';               
                $response['description'] = 'Please select valid customer for order creation.';

                return $response;

            }
        }
        catch(Exception $e)
        {
            $response['status']      = 'FAILURE';
            $response['description'] = $e->getMessage();

            return $response;

        }

      }
    }


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
            $ARR_EVENT_DATA['status']         = isset($ARR_DATA['status'])?$ARR_DATA['status']:'0'; 

            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }

        return false;
    }

    /*
      Auth : Jaydip
      Date : 10 Dec 2019
      Desc : Get Order details for retialer, sales manager , representative 
    */
    public function get_order_details($role=false,$order_no=false,$maker_id=false)
    {
      /* set empty arr */
      $arr_order_details = [];

      $order_no = isset($order_no)?$order_no:false;
      $role     = isset($role)?$role:false;
    
      try{


        if($role == 'retailer' && $order_no)
        { 

          /* get sales_manager or  representative order details and table = retailer_transaction*/
          $arr_order_details = $this->RetailerQuotesModel->with('quotes_details.product_details',
                                                                'maker_data.user_details',
                                                                'user_details.retailer_details')         
                                                         ->where('order_no',$order_no)
                                                         ->where('maker_id',$maker_id)
                                                         ->get()
                                                         ->toArray();
          
        }
        elseif(($role == 'sales_manager' || $role == 'representative') && ($order_no))
        { 
          /* get retailer order details and table = representative_leads*/
          $arr_order_details = $this->RepresentativeLeadsModel->with('leads_details.product_details',
                                                                     'retailer_user_details',
                                                                     'maker_data',
                                                                     'address_details',
                                                                     'maker_details',
                                                                     'sales_manager_details',
                                                                     'representative_user_details')

                                                             ->where('order_no',$order_no)
                                                             ->where('maker_id',$maker_id)
                                                             ->get()
                                                             ->toArray();
           
        }elseif(($role == 'customer') && ($order_no))
        { 
          /* get customer order details and table = retailer_transaction*/
          $arr_order_details = $this->CustomerQuotesModel->with('quotes_details.product_details',
                                                                'maker_data.user_details',
                                                                'user_details.customer_details')         
                                                         ->where('order_no',$order_no)
                                                         ->where('maker_id',$maker_id)
                                                         ->get()
                                                         ->toArray();
           
        }

        
    
        return $arr_order_details;
      }
      catch(Exception $e)
      {
        return $arr_order_details;
      }
    }

    /*
      Auth : Jaydip
      Date : 10 Dec 2019
      Desc : get bag  details
     */




  public function get_customer_bag_details($user_id)
  {
        /* Get current login user details */
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $bag_arr  = $product_data_arr = $product_arr = $cart_product_arr = $maker_details_arr = [];
        $arr_final_data = [];
        $subtotal       = 0;
        $wholesale_subtotal = 0;
        $ip_address = \Request::ip();
        $session_id = session()->getId();

        
        /* Get Bag details */

        $bag_obj = $this->MyCartService->get_items();

        if($bag_obj)
        {
            $bag_arr          = $bag_obj->toArray();

            $data_arr = json_decode($bag_arr['product_data'],true);
            $product_data_arr = $data_arr['sku'];
           
            $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
           /* $sku_product_arr = $this->ProductDetailsModel->whereIn('sku',$sku_ids_arr)
                               ->with('productDetails') 
                               ->get()->toArray();*/

            $sku_product_arr = $this->ProductDetailsModel->whereIn('sku',$sku_ids_arr)
                                                         ->with('productDetails') 
                                                         /* Check product is active */
                                                         ->whereHas('productDetails',function($q){
                                                          return $q->where('is_active','1');
                                                         })
                                                         /* Check product category is active */                               
                                                         ->whereHas('productDetails.categoryDetails',function($q){
                                                          return $q->where('is_active','1');
                                                         })
                                                         /* Check product Sub-category is active */     
                                                         ->whereHas('productDetails.categoryDetails.subcategory_details',function($q){
                                                          return $q->orwhere('is_active','1');
                                                          })
                                                         ->get()->toArray();

                /* get active items ids  */
            $active_items = isset($sku_product_arr)?array_column($sku_product_arr,'sku'):[];

             /* get deactive items ids */
            $arr_deactivated_item = array_diff($sku_ids_arr ,$active_items);

            if($arr_deactivated_item)
            {
                foreach($arr_deactivated_item as $key => $item_sku)
                {
                    /* remove deactivated items from bag */
                   unset($product_data_arr[$item_sku]);

                   $temp_arr['sku']     = $product_data_arr;
                   $temp_arr['sequence'] = array_keys($temp_arr['sku']);
                }
                /* update temp bag data */


                $update_arr_data['product_data'] = json_encode($temp_arr);

                // $update_bag_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->update($update_arr_data); 

                $update_bag_data = $this->MyCartService->update($update_arr_data); 
                
                // $bag_obj_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();

                $bag_obj_data = $this->MyCartService->get_items(); 
                
                if($bag_obj_data)
                {
                    $bag_arr          = $bag_obj_data->toArray();

                    $data_arr = json_decode($bag_arr['product_data'],true);
                    
                    $product_data_arr = $data_arr['sku'];
                   
                    $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
                }
            }

            if(isset($sku_product_arr) && count($sku_product_arr)>0)
            {   
                foreach ($sku_product_arr as $key => $product) 
                {  //dd($sku_product_arr);
                    $cart_product_arr[$key]['user_id']        = $product['product_details']['user_id'];
                    $cart_product_arr[$key]['product_id']     = $product['product_id'];
                    $cart_product_arr[$key]['product_name']   = $product['product_details']['product_name'];
                    $cart_product_arr[$key]['product_image']  = $product['image'];
                    $sku_no = "";


                    $sku_no = isset($product_data_arr[$product['sku']]['sku_no'])?$product_data_arr[$product['sku']]['sku_no']:'';
                    
                  /*$cart_product_arr[$key]['product_image']  = get_sku_image($sku_no);*/
                    $cart_product_arr[$key]['total_price']    = isset($product_data_arr[$product['sku']]['total_price'])?$product_data_arr[$product['sku']]['total_price']:'';

                    

                    $cart_product_arr[$key]['shipping_type']    = isset($product['product_details']['shipping_type'])?$product['product_details']['shipping_type']:'';

                    
                      $cart_product_arr[$key]['minimum_amount_off']    = isset($product['product_details']['minimum_amount_off'])?$product['product_details']['minimum_amount_off']:'';

                      $cart_product_arr[$key]['off_type_amount']    = isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:'';


                      $cart_product_arr[$key]['product_weight']    = isset($product['weight'])?$product['weight']:'';

                      $cart_product_arr[$key]['product_length']    = isset($product['length'])?$product['length']:'';

                      $cart_product_arr[$key]['product_width']    = isset($product['width'])?$product['width']:'';

                      $cart_product_arr[$key]['product_height']    = isset($product['height'])?$product['height']:'';

                      $cart_product_arr[$key]['product_option_type']    = isset($product['option_type'])?$product['option_type']:'';

                      $cart_product_arr[$key]['product_option_value']    = isset($product['option'])?$product['option']:'';

                      $cart_product_arr[$key]['unit_retail_price']    = isset($product_data_arr[$product['sku']]['retail_price'])?$product_data_arr[$product['sku']]['retail_price']:'';

                      $cart_product_arr[$key]['unit_wholsale_price']    = isset($product_data_arr[$product['sku']]['unit_wholsale_price'])?$product_data_arr[$product['sku']]['unit_wholsale_price']:'';

                       //dd($product_data_arr[$product['sku']]);
                       $cart_product_arr[$key]['total_wholesale_price']   = isset($product_data_arr[$product['sku']]['total_wholesale_price'])?$product_data_arr[$product['sku']]['total_wholesale_price']:'';

                       $cart_product_arr[$key]['prodduct_dis_type'] = isset($product_data_arr[$product['sku']]['prodduct_dis_type'])?$product_data_arr[$product['sku']]['prodduct_dis_type']:'0';

                       $cart_product_arr[$key]['product_dis_min_amt'] = isset($product_data_arr[$product['sku']]['product_dis_min_amt'])?$product_data_arr[$product['sku']]['product_dis_min_amt']:'0';

                       $cart_product_arr[$key]['product_discount_value'] = isset($product_data_arr[$product['sku']]['product_discount_value'])?$product_data_arr[$product['sku']]['product_discount_value']:'0';


                       $cart_product_arr[$key]['product_dis amount'] = isset($product_data_arr[$product['sku']]['product_discount_amount'])?$product_data_arr[$product['sku']]['product_discount_amount']:'0';


                       if($cart_product_arr[$key]['shipping_type']==2) 
                       {
                          if($cart_product_arr[$key]['total_price']>=$cart_product_arr[$key]['minimum_amount_off'])
                          {
                         
                           $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';

                           $discount_amount =  $shipping_charges * $cart_product_arr[$key]['off_type_amount']/ 100;

                           //$shipping_charges = $shipping_charges-$discount_amount;

                           $cart_product_arr[$key]['shipping_discount'] = $discount_amount;
                           $cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
                          }
                          else
                          {

                           $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';

                           $cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
                           $cart_product_arr[$key]['shipping_discount'] = 0;

                          }
                       }

                       if($cart_product_arr[$key]['shipping_type']==1) 
                       { 
                          if($cart_product_arr[$key]['total_price']<$cart_product_arr[$key]['minimum_amount_off'])
                          {
                              $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                             
                              $cart_product_arr[$key]['shipping_charges'] = isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                              // $cart_product_arr[$key]['total_wholesale_price'] = $cart_product_arr[$key]['total_wholesale_price'] + $shipping_charges;
                              $cart_product_arr[$key]['shipping_discount'] =  0;
                              //dd($cart_product_arr[$key]['shipping_charges']);

                          }
                          else
                          {
                             
                              $cart_product_arr[$key]['shipping_discount'] = 0;
                              $cart_product_arr[$key]['shipping_charges'] = 0;
                              //dd($cart_product_arr[$key]['shipping_charges']);

                          }
                      }

                      if($cart_product_arr[$key]['shipping_type']==3) 
                      {
                          if($cart_product_arr[$key]['total_price']>=$cart_product_arr[$key]['minimum_amount_off'])
                          {
                            $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';

                            //$shipping_charges = $shipping_charges - $cart_product_arr[$key]['off_type_amount'];
                            
                            $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;
                            $cart_product_arr[$key]['shipping_discount'] = $cart_product_arr[$key]['off_type_amount'];
                          }
                          else
                          {
                             $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                             $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;
                             $cart_product_arr[$key]['shipping_discount'] = 0;
                          }
                      }

                      $cart_product_arr[$key]['wholesale_price']    = isset($product['product_details']['unit_wholsale_price'])?$product['product_details']['unit_wholsale_price']:'';
                      
                      $cart_product_arr[$key]['item_qty']       = isset($product_data_arr[$product['sku']]['item_qty'])?$product_data_arr[$product['sku']]['item_qty']:'';

                      $cart_product_arr[$key]['sku_no']         = isset($product_data_arr[$product['sku']]['sku_no'])?$product_data_arr[$product['sku']]['sku_no']:'';
                      $cart_product_arr[$key]['brand_name']     = get_brand_name($product['product_details']['user_id']);
                }

                $subtotal = array_sum((array_column($cart_product_arr,'total_price')));
                $wholesale_subtotal = array_sum((array_column($cart_product_arr,'total_wholesale_price')));  
            }


            $arr_prefetch_user_id = array_unique(array_column($cart_product_arr, 'user_id'));

            $arr_prefetch_user_ref =  $this->MakerModel->with('shop_settings')
                                                       ->whereIn('user_id',$arr_prefetch_user_id)
                                                       ->get()
                                                       ->toArray();

           
            $arr_prefetch_user_ref = array_column($arr_prefetch_user_ref,null, 'user_id');
            
           
            $product_sequence     = ""; 
            $arr_product_sequence = $arr_sequence = [];
            $arr_sequence         = $data_arr['sequence'];
           
            if(isset($cart_product_arr) && count($cart_product_arr)>0)
            {
                foreach($cart_product_arr as $key => $value) 
                {
                    $arr_final_data[$value['user_id']]['product_details'][$value['sku_no']] = $value;
                    $arr_final_data[$value['user_id']]['maker_details'] = isset($arr_prefetch_user_ref[$value['user_id']]) ? $arr_prefetch_user_ref[$value['user_id']] : [];
                }
            }

            /* Rearrange sequence */
            if(count($arr_final_data)>0)
            {
                foreach ($arr_final_data as $_key => $_data) 
                {   
                    $arr_relavant_sequence = array_flip(array_intersect($arr_sequence,array_keys($_data['product_details'])));
                    if(count($arr_relavant_sequence)>0)
                    {
                        foreach ($arr_relavant_sequence as $sequence_attrib => $sequence_tmp) 
                        {
                            $arr_relavant_sequence[$sequence_attrib] = isset($_data['product_details'][$sequence_attrib]) ? $_data['product_details'][$sequence_attrib] : [];
                        }
                    }
                    
              
                    $arr_final_data[$_key]['product_details'] = $arr_relavant_sequence;                    
                }
            }
        }
        
        return $arr_final_data;
    }


  public function get_bag_details($user_id)
  {
        /* Get current login user details */
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $bag_arr  = $product_data_arr = $product_arr = $cart_product_arr = $maker_details_arr = [];
        $arr_final_data = [];
        $subtotal       = 0;
        $wholesale_subtotal = 0;
        $ip_address = \Request::ip();
        $session_id = session()->getId();

        
        /* Get Bag details */

        // $bag_obj = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();
        $bag_obj = $this->MyCartService->get_items();



        if($bag_obj)
        {
            $bag_arr          = $bag_obj->toArray();

            $data_arr = json_decode($bag_arr['product_data'],true); 
            $product_data_arr = $data_arr['sku'];
           
            $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
           /* $sku_product_arr = $this->ProductDetailsModel->whereIn('sku',$sku_ids_arr)
                               ->with('productDetails') 
                               ->get()->toArray();*/


            $sku_product_arr = $this->ProductDetailsModel->whereIn('sku',$sku_ids_arr)
                                                         ->with('productDetails') 
                                                         /* Check product is active */
                                                         ->whereHas('productDetails',function($q){
                                                          return $q->where('is_active','1');
                                                         })
                                                         /* Check product category is active */                               
                                                         ->whereHas('productDetails.categoryDetails',function($q){
                                                          return $q->where('is_active','1');
                                                         })
                                                         /* Check product Sub-category is active */     
                                                         ->whereHas('productDetails.categoryDetails.subcategory_details',function($q){
                                                          return $q->orwhere('is_active','1');
                                                          })
                                                         ->get()->toArray();



                /* get active items ids  */
            $active_items = isset($sku_product_arr)?array_column($sku_product_arr,'sku'):[];

             /* get deactive items ids */
            $arr_deactivated_item = array_diff($sku_ids_arr ,$active_items);

            if($arr_deactivated_item)
            {
                foreach($arr_deactivated_item as $key => $item_sku)
                {
                    /* remove deactivated items from bag */
                   unset($product_data_arr[$item_sku]);

                   $temp_arr['sku']     = $product_data_arr;
                   $temp_arr['sequence'] = array_keys($temp_arr['sku']);
                }
                /* update temp bag data */


                $update_arr_data['product_data'] = json_encode($temp_arr);

                // $update_bag_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->update($update_arr_data); 

                $update_bag_data = $this->MyCartService->update($update_arr_data); 
                
                // $bag_obj_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();

                $bag_obj_data = $this->MyCartService->get_items(); 
                
                if($bag_obj_data)
                {
                    $bag_arr          = $bag_obj_data->toArray();

                    $data_arr = json_decode($bag_arr['product_data'],true);
                    
                    $product_data_arr = $data_arr['sku'];
                   
                    $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
                }
            }

            if(isset($sku_product_arr) && count($sku_product_arr)>0)
            {   
                foreach ($sku_product_arr as $key => $product) 
                { 
                    $cart_product_arr[$key]['user_id']        = $product['product_details']['user_id'];
                    $cart_product_arr[$key]['product_id']     = $product['product_id'];
                    $cart_product_arr[$key]['product_name']   = $product['product_details']['product_name'];
                    $cart_product_arr[$key]['product_image']  = $product['image'];
                    $sku_no = "";


                    $sku_no = isset($product_data_arr[$product['sku']]['sku_no'])?$product_data_arr[$product['sku']]['sku_no']:'';
                    
                  /*$cart_product_arr[$key]['product_image']  = get_sku_image($sku_no);*/
                    $cart_product_arr[$key]['total_price']    = isset($product_data_arr[$product['sku']]['total_price'])?$product_data_arr[$product['sku']]['total_price']:'';

                    

                    $cart_product_arr[$key]['shipping_type']    = isset($product['product_details']['shipping_type'])?$product['product_details']['shipping_type']:'';

                    
                      $cart_product_arr[$key]['minimum_amount_off']    = isset($product['product_details']['minimum_amount_off'])?$product['product_details']['minimum_amount_off']:'';

                      $cart_product_arr[$key]['off_type_amount']    = isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:'';


                      $cart_product_arr[$key]['product_weight']    = isset($product['weight'])?$product['weight']:'';

                      $cart_product_arr[$key]['product_length']    = isset($product['length'])?$product['length']:'';

                      $cart_product_arr[$key]['product_width']    = isset($product['width'])?$product['width']:'';

                      $cart_product_arr[$key]['product_height']    = isset($product['height'])?$product['height']:'';

                      $cart_product_arr[$key]['product_option_type']    = isset($product['option_type'])?$product['option_type']:'';

                      $cart_product_arr[$key]['product_option_value']    = isset($product['option'])?$product['option']:'';

                      $cart_product_arr[$key]['unit_retail_price']    = isset($product_data_arr[$product['sku']]['retail_price'])?$product_data_arr[$product['sku']]['retail_price']:'';

                      $cart_product_arr[$key]['unit_wholsale_price']    = isset($product_data_arr[$product['sku']]['unit_wholsale_price'])?$product_data_arr[$product['sku']]['unit_wholsale_price']:'';

                       //dd($product_data_arr[$product['sku']]);
                       $cart_product_arr[$key]['total_wholesale_price']   = isset($product_data_arr[$product['sku']]['total_wholesale_price'])?$product_data_arr[$product['sku']]['total_wholesale_price']:'';

                       $cart_product_arr[$key]['prodduct_dis_type'] = isset($product_data_arr[$product['sku']]['prodduct_dis_type'])?$product_data_arr[$product['sku']]['prodduct_dis_type']:'0';

                       $cart_product_arr[$key]['product_dis_min_amt'] = isset($product_data_arr[$product['sku']]['product_dis_min_amt'])?$product_data_arr[$product['sku']]['product_dis_min_amt']:'0';

                       $cart_product_arr[$key]['product_discount_value'] = isset($product_data_arr[$product['sku']]['product_discount_value'])?$product_data_arr[$product['sku']]['product_discount_value']:'0';


                       $cart_product_arr[$key]['product_dis amount'] = isset($product_data_arr[$product['sku']]['product_discount_amount'])?$product_data_arr[$product['sku']]['product_discount_amount']:'0';


                       if($cart_product_arr[$key]['shipping_type']==2) 
                       {
                          if($cart_product_arr[$key]['total_wholesale_price']>=$cart_product_arr[$key]['minimum_amount_off'])
                          {
                         
                             $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';

                             $discount_amount =  $shipping_charges * $cart_product_arr[$key]['off_type_amount']/ 100;

                             //$shipping_charges = $shipping_charges-$discount_amount;

                             $cart_product_arr[$key]['shipping_discount'] = $discount_amount;
                             $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;
                          }
                          else
                          {

                             $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                             
                             $cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
                             $cart_product_arr[$key]['shipping_discount'] = 0;

                          }
                       }

                       if($cart_product_arr[$key]['shipping_type']==1) 
                       { 
                          if($cart_product_arr[$key]['total_wholesale_price']<$cart_product_arr[$key]['minimum_amount_off'])
                          {
                              $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                             
                              $cart_product_arr[$key]['shipping_charges'] = isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                              // $cart_product_arr[$key]['total_wholesale_price'] = $cart_product_arr[$key]['total_wholesale_price'] + $shipping_charges;
                              $cart_product_arr[$key]['shipping_discount'] =  0;
                              //dd($cart_product_arr[$key]['shipping_charges']);

                          }
                          else
                          {
                             
                              $cart_product_arr[$key]['shipping_discount'] = 0;
                              $cart_product_arr[$key]['shipping_charges'] = 0;
                              //dd($cart_product_arr[$key]['shipping_charges']);

                          }
                      }

                      if($cart_product_arr[$key]['shipping_type']==3) 
                      {
                          if($cart_product_arr[$key]['total_wholesale_price']>=$cart_product_arr[$key]['minimum_amount_off'])
                          {
                            $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';

                            //$shipping_charges = $shipping_charges - $cart_product_arr[$key]['off_type_amount'];

                            $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;
                            $cart_product_arr[$key]['shipping_discount'] = $cart_product_arr[$key]['off_type_amount'];
                          }
                          else
                          {
                             $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:'';
                             $cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
                             $cart_product_arr[$key]['shipping_discount'] = 0;
                          }
                      }

                      $cart_product_arr[$key]['wholesale_price']    = isset($product['product_details']['unit_wholsale_price'])?$product['product_details']['unit_wholsale_price']:'';
                      
                      $cart_product_arr[$key]['item_qty']       = isset($product_data_arr[$product['sku']]['item_qty'])?$product_data_arr[$product['sku']]['item_qty']:'';
                      $cart_product_arr[$key]['color']       = isset($product_data_arr[$product['sku']]['color'])?$product_data_arr[$product['sku']]['color']:'';
                      $cart_product_arr[$key]['size_id']       = isset($product_data_arr[$product['sku']]['size_id'])?$product_data_arr[$product['sku']]['size_id']:'';

                      $cart_product_arr[$key]['sku_no']         = isset($product_data_arr[$product['sku']]['sku_no'])?$product_data_arr[$product['sku']]['sku_no']:'';
                      $cart_product_arr[$key]['brand_name']     = get_brand_name($product['product_details']['user_id']);
                }

                $subtotal = array_sum((array_column($cart_product_arr,'total_price')));
                $wholesale_subtotal = array_sum((array_column($cart_product_arr,'total_wholesale_price')));  
            }


            $arr_prefetch_user_id = array_unique(array_column($cart_product_arr, 'user_id'));

            $arr_prefetch_user_ref =  $this->MakerModel->with('shop_settings')
                                                       ->whereIn('user_id',$arr_prefetch_user_id)
                                                       ->get()
                                                       ->toArray();

           
            $arr_prefetch_user_ref = array_column($arr_prefetch_user_ref,null, 'user_id');
            
           
            $product_sequence     = ""; 
            $arr_product_sequence = $arr_sequence = [];
            $arr_sequence         = $data_arr['sequence'];
     
            if(isset($cart_product_arr) && count($cart_product_arr)>0)
            {
                foreach($cart_product_arr as $key => $value) 
                {
                    $arr_final_data[$value['user_id']]['product_details'][$value['sku_no']] = $value;
                    $arr_final_data[$value['user_id']]['maker_details'] = isset($arr_prefetch_user_ref[$value['user_id']]) ? $arr_prefetch_user_ref[$value['user_id']] : [];
                }
            }

            /* Rearrange sequence */
            if(count($arr_final_data)>0)
            {
                foreach ($arr_final_data as $_key => $_data) 
                {   
                    $arr_relavant_sequence = array_flip(array_intersect($arr_sequence,array_keys($_data['product_details'])));
                    if(count($arr_relavant_sequence)>0)
                    {
                        foreach ($arr_relavant_sequence as $sequence_attrib => $sequence_tmp) 
                        {
                            $arr_relavant_sequence[$sequence_attrib] = isset($_data['product_details'][$sequence_attrib]) ? $_data['product_details'][$sequence_attrib] : [];
                        }
                    }
                    
              
                    $arr_final_data[$_key]['product_details'] = $arr_relavant_sequence;                    
                }
            }


        }

         
        return $arr_final_data;
    }

  public function get_active_product($arr_sku=[])
  {
    $arr_product_data = [];

    if($arr_sku)
    {
      $sku_product_arr = $this->ProductDetailsModel->whereIn('sku',$arr_sku)
                                                   ->with('productDetails') 
                                                   /* Check product is active */
                                                   ->whereHas('productDetails',function($q){
                                                    return $q->where('is_active','1');
                                                   })
                                                   /* Check product category is active */                               
                                                   ->whereHas('productDetails.categoryDetails',function($q){
                                                    return $q->where('is_active','1');
                                                   })
                                                   /* Check product Sub-category is active */     
                                                   ->whereHas('productDetails.categoryDetails.subcategory_details',function($q){
                                                    return $q->orwhere('is_active','1');
                                                    })
                                                   ->get()->toArray();

      $arr_product_data =  $sku_product_arr;
    }

    return $arr_product_data;

  }


  public function verify_order_no($order_no=[]) 
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

  public function generate_order_no($order_no=[])
  {
    $newOrderNumber = [];
    
    if($order_no)
    {
      $ordDigits = substr($order_no, 2);

      $order_no = $ordDigits + 1;
      // dd($ordDigits,$order_no);

      $newOrderNo = 'J2'.$order_no;

      $newOrderNumber = $this->verify_order_no($newOrderNo);
    }
    return $newOrderNumber;
  } 


  public function get_retailer_last_seven_days_order($maker_id=false)
  {

     $last_seven_days_arr = $this->lastSevenDays();

      $PendingOrderData  = $completeOrderData = [];
      $pendingOrderCalculations  = 0;
      $completeOrderCalculations = 0;


      $order_no = isset($order_no)?$order_no:false;
      $role     = isset($role)?$role:false;
    
      if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
      { 
          foreach($last_seven_days_arr as $key => $date) 
          { 
                    /* get retailer pending orders */
                    $pendingAmount = $this->RetailerQuotesModel->where('total_wholesale_price','>','0')
                                                               ->where('order_cancel_status','!=','2')
                                                               ->where('ship_status','=',0)
                                                               ->where('created_at','LIKE','%'.$date.'%')
                                                               ->where('maker_id',$maker_id)
                                                               ->get()
                                                               ->toArray();

                    $PendingOrderData[$date] = $pendingAmount; 
                    /* get retailer pending orders */
                    
                    $collectedAmount = $this->RetailerQuotesModel->where('total_wholesale_price','>','0')
                                                                 ->where('order_cancel_status','!=','2')
                                                                 ->where('ship_status','=',1)
                                                                 ->where('created_at','LIKE','%'.$date.'%')
                                                                 ->where('maker_id',$maker_id)
                                                                 ->get()
                                                                 ->toArray();
                                                                   
                    $completeOrderData[$date] = $collectedAmount;      
          }

      }

     return $this->get_retailer_amount_details(['arr_pending_amount' => $PendingOrderData , 'arr_collected_amount' => $completeOrderData]);
  }

  public function get_customer_last_seven_days_order($maker_id=false)
  {

     $last_seven_days_arr = $this->lastSevenDays();

      $PendingOrderData  = $completeOrderData = [];
      $pendingOrderCalculations  = 0;
      $completeOrderCalculations = 0;


      $order_no = isset($order_no)?$order_no:false;
      $role     = isset($role)?$role:false;
    
      if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
      { 
          foreach($last_seven_days_arr as $key => $date) 
          { 
                    /* get retailer pending orders */
                    $pendingAmount =  $this->CustomerQuotesModel->where('total_wholesale_price','>','0')
                                                               ->where('order_cancel_status','!=','2')
                                                               ->where('ship_status','=',0)
                                                               ->where('created_at','LIKE','%'.$date.'%')
                                                               ->where('maker_id',$maker_id)
                                                               ->get()
                                                               ->toArray();

                    $PendingOrderData[$date] = $pendingAmount; 
                    /* get retailer pending orders */
                    
                    $collectedAmount = $this->CustomerQuotesModel->where('total_wholesale_price','>','0')
                                                                 ->where('order_cancel_status','!=','2')
                                                                 ->where('ship_status','=',1)
                                                                 ->where('created_at','LIKE','%'.$date.'%')
                                                                 ->where('maker_id',$maker_id)
                                                                 ->get()
                                                                 ->toArray();
                                                                   
                    $completeOrderData[$date] = $collectedAmount;      
          }

      }

     return $this->get_customer_amount_details(['arr_pending_amount' => $PendingOrderData , 'arr_collected_amount' => $completeOrderData]);
  }

  public function get_rep_sales_last_seven_days_order($maker_id=false)
  {

     $last_seven_days_arr = $this->lastSevenDays();

      $repSalesPendingOrderData  = $repSalescompleteOrderData = [];
      $pendingOrderCalculations  = 0;
      $completeOrderCalculations = 0;


      $order_no = isset($order_no)?$order_no:false;
      $role     = isset($role)?$role:false;
    
      if(isset($last_seven_days_arr) && count($last_seven_days_arr)>0)
      { 
          foreach($last_seven_days_arr as $key => $date) 
          { 
                    /* get retailer pending orders */
                    $pendingAmount = $this->RepresentativeLeadsModel->where('total_wholesale_price','>','0')
                                                                     ->where('order_cancel_status','!=','2')
                                                                     ->where('ship_status','=',0)
                                                                     ->where('created_at','LIKE','%'.$date.'%')
                                                                     ->where('maker_id',$maker_id)
                                                                     // ->sum('total_wholesale_price');
                                                                     ->get()
                                                                     ->toArray();

                    $repSalesPendingOrderData[$date] = $pendingAmount; /* get retailer pending orders */
                    
                    $collectedAmount = $this->RepresentativeLeadsModel->where('total_wholesale_price','>','0')
                                                                     ->where('order_cancel_status','!=','2')
                                                                     ->where('ship_status','=',1)
                                                                     ->where('created_at','LIKE','%'.$date.'%')
                                                                     ->where('maker_id',$maker_id)
                                                                     // ->sum('total_wholesale_price');
                                                                     ->get()
                                                                     ->toArray();

                    $repSalescompleteOrderData[$date] = $collectedAmount;      
          }

      }

      return $this->get_rep_sales_amount_details(['arr_pending_amount' => $repSalesPendingOrderData , 'arr_collected_amount' => $repSalescompleteOrderData]);
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

  public function get_retailer_amount_details($arrData = [])
  {

    $ordWholesalePrice = $adminCommissionAmount = $pendingAmount = $collectedAmount = 0;

    foreach ($arrData['arr_pending_amount'] as $key => $value) {
      foreach ($value as $key1 => $value1) {
        
          $adminCommission = $value1['admin_commission'];
          
          $shippingCharges = $this->get_retailer_order_shipping_charges($value1['id']);
                            
          $isFreeshipping = is_promocode_freeshipping($value1['promo_code']);
          
          if($isFreeshipping == false)
          {
            $ordWholesalePrice = $value1['total_wholesale_price'] - $shippingCharges;
          }

          $adminCommissionAmount = $ordWholesalePrice*($adminCommission / 100);

          $pendingAmount += $ordWholesalePrice - $adminCommissionAmount;
      }
    }

  
     foreach ($arrData['arr_collected_amount'] as $key => $arrData) {
      foreach ($arrData as $key1 => $arrcompletedOrder) {
        
          $adminCommission = $arrcompletedOrder['admin_commission'];
          
          $shippingCharges = $this->get_retailer_order_shipping_charges($arrcompletedOrder['id']);
                   
          $isFreeshipping = is_promocode_freeshipping($arrcompletedOrder['promo_code']);

          if($isFreeshipping == false)
          {
            $ordWholesalePrice = $arrcompletedOrder['total_wholesale_price'] - $shippingCharges;
          }

          $adminCommissionAmount = $ordWholesalePrice*($adminCommission / 100);

          $collectedAmount += $ordWholesalePrice - $adminCommissionAmount;

      }
    }

    $result = [];

    $result['collectedAmount'] = $collectedAmount;
    $result['pendingAmount']   = $pendingAmount;

   return $result;
  }

  public function get_customer_amount_details($arrData = [])
  {

    $ordRetailerPrice = $adminCommissionAmount = $pendingAmount = $collectedAmount = 0;

    foreach ($arrData['arr_pending_amount'] as $key => $value) {
      foreach ($value as $key1 => $value1) {
        
          $adminCommission = $value1['admin_commission'];
          
          $shippingCharges = $this->get_customer_order_shipping_charges($value1['id']);
                            
          $isFreeshipping = is_promocode_freeshipping($value1['promo_code']);
          
          if($isFreeshipping == false)
          {
            $ordRetailerPrice = $value1['total_retail_price'] - $shippingCharges;
          }

          $adminCommissionAmount = $ordRetailerPrice*($adminCommission / 100);

          $pendingAmount += $ordRetailerPrice - $adminCommissionAmount;
      }
    }

  
     foreach ($arrData['arr_collected_amount'] as $key => $arrData) {
      foreach ($arrData as $key1 => $arrcompletedOrder) {
        
          $adminCommission = $arrcompletedOrder['admin_commission'];
          
          $shippingCharges = $this->get_customer_order_shipping_charges($arrcompletedOrder['id']);
                   
          $isFreeshipping = is_promocode_freeshipping($arrcompletedOrder['promo_code']);

          if($isFreeshipping == false)
          {
            $ordRetailerPrice = $arrcompletedOrder['total_retail_price'] - $shippingCharges;
          }

          $adminCommissionAmount = $ordRetailerPrice*($adminCommission / 100);

          $collectedAmount += $ordRetailerPrice - $adminCommissionAmount;

      }
    }

    $result = [];

    $result['collectedAmount'] = $collectedAmount;
    $result['pendingAmount']   = $pendingAmount;

   return $result;
  }

  public function get_rep_sales_amount_details($arrData = [])
  {

    $ordWholesalePrice = $adminCommissionAmount = $pendingAmount = $collectedAmount = 0;

    foreach ($arrData['arr_pending_amount'] as $key => $value) {
      foreach ($value as $key1 => $value1) {
        
          $adminCommission = $value1['admin_commission'];
          
          $shippingCharges = $value1['total_shipping_charges'];
                            
          $isFreeshipping = is_promocode_freeshipping($value1['promo_code']);
          
          if($isFreeshipping == false)
          {
            $ordWholesalePrice = $value1['total_wholesale_price'] - $shippingCharges;
          }

          $adminCommissionAmount = $ordWholesalePrice*($adminCommission / 100);

          $pendingAmount += $ordWholesalePrice - $adminCommissionAmount;
      }
    }

  
     foreach ($arrData['arr_collected_amount'] as $key => $arrData) {
      foreach ($arrData as $key1 => $arrcompletedOrder) {
        
          $adminCommission = $arrcompletedOrder['admin_commission'];
          
          $shippingCharges = $arrcompletedOrder['total_shipping_charges'];
                   
          $isFreeshipping = is_promocode_freeshipping($arrcompletedOrder['promo_code']);

          if($isFreeshipping == false)
          {
            $ordWholesalePrice = $arrcompletedOrder['total_wholesale_price'] - $shippingCharges;
          }

          $adminCommissionAmount = $ordWholesalePrice*($adminCommission / 100);

          $collectedAmount += $ordWholesalePrice - $adminCommissionAmount;

      }
    }

    $result = [];

    $result['collectedAmount'] = $collectedAmount;
    $result['pendingAmount']   = $pendingAmount;

   return $result;
  }

  public function get_retailer_order_shipping_charges($orderId)
  {
      $shippingCharges = 0;

      $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

      $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
      
      return $shippingCharges = $shipCharge-$shipChargeDisount;
  }

  public function get_customer_order_shipping_charges($orderId)
  {
      $shippingCharges = 0;

      $shipCharge = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_charge');

      $shipChargeDisount = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_discount');

      return $shippingCharges = $shipCharge-$shipChargeDisount;
      
  }
  

  public function is_product_from_linked_vendor($product_id,$loggedInUserId)
  {
    if($product_id!="" && $loggedInUserId!="")
    {  
     $obj_maker_details = $this->ProductsModel->where('id',$product_id)->select('user_id')->first();
     if($obj_maker_details)
     {
        $arr_maker_details = $obj_maker_details->toArray();
     }
     if(isset($arr_maker_details) && $arr_maker_details!="")
     {
        $is_maker_linked = VendorRepresentativeMappingModel::where('representative_id',$loggedInUserId)->where('vendor_id',$arr_maker_details['user_id'])->count();
        if($is_maker_linked==1)
        {
          return "true";
        }
        else
        {
          return "false";
        }
     }
      return "false";
    } 
    return "false";
  }

}