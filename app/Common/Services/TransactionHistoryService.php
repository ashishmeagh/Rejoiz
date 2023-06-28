<?php
namespace App\Common\Services;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\StripeTransactionModel;
use App\Models\ProductsModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RepresentativeModel;  
use App\Models\TransactionMappingModel;
use App\Models\MakerModel;
use App\Models\RefundModel;
use App\Models\TempBagModel;
use App\Models\ProductDetailsModel;
use App\Models\RetailerQuotesModel;
use App\Models\RetailerModel;
use App\Models\SalesManagerModel;
use App\Models\CustomerQuotesModel;


use Request;
use DB;
use Sentinel;
use Session;
use DateTime;

class TransactionHistoryService 
{

   public function __construct(
                                UserModel           $UserModel,
                                RoleModel           $RoleModel,
                                SalesManagerModel   $SalesManagerModel,
                                StripeTransactionModel $StripeTransactionModel,
                                ProductsModel $ProductsModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeModel $RepresentativeModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                MakerModel $MakerModel,
                                ProductDetailsModel $ProductDetailsModel,
                                TempBagModel $TempBagModel,
                                RetailerModel $RetailerModel,
                                RefundModel $RefundModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                CustomerQuotesModel $CustomerQuotesModel
                               )
  {
    $this->UserModel                         = $UserModel;
    $this->RoleModel                         = $RoleModel;
    $this->StripeTransactionModel            = $StripeTransactionModel;
    $this->SalesManagerModel                 = $SalesManagerModel;
    $this->ProductsModel                     = $ProductsModel;
    $this->RoleUsersModel                    = $RoleUsersModel;
    $this->RepresentativeLeadsModel          = $RepresentativeLeadsModel;
    $this->RepresentativeProductLeadsModel   = $RepresentativeProductLeadsModel;
    $this->RepresentativeModel               = $RepresentativeModel;
    $this->TransactionMappingModel           = $TransactionMappingModel;
    $this->RetailerQuotesModel               = $RetailerQuotesModel;
    $this->MakerModel                        = $MakerModel;  
    $this->RetailerModel                     = $RetailerModel;                 
    $this->RefundModel                       = $RefundModel;                 
    $this->ProductDetailsModel               = $ProductDetailsModel;
    $this->TempBagModel                      = $TempBagModel;
    $this->CustomerQuotesModel               = $CustomerQuotesModel;
  }

    public function get_order_list($arr_search_column = [])
    {

      $request_param = Request::segment(4);

      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $representative_leads =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $retailer_leads =  $this->RetailerQuotesModel->getTable();
      $prefix_retailer_leads_tbl = DB::getTablePrefix().$retailer_leads;

      $stripe_transaction = $this->StripeTransactionModel->getTable();
      $prefix_stripe_transaction = DB::getTablePrefix().$stripe_transaction;

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $maker_table           = $this->MakerModel->getTable();
      $prefix_maker_table    = DB::getTablePrefix().$maker_table;

      $reps_table           = $this->RepresentativeModel->getTable();
      $prefix_reps_table    = DB::getTablePrefix().$reps_table;

      $sales_manager_table  = $this->SalesManagerModel->getTable();
      $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

      $transaction_mapping_table           = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping_table    = DB::getTablePrefix().$transaction_mapping_table;

      $role_table        =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table        =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;


      $customer_quotes_table        = $this->CustomerQuotesModel->getTable();
      $prefix_customer_quotes_table =  DB::getTablePrefix().$customer_quotes_table;



      if ($request_param == 'vendor') {

        $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_retailer_leads_tbl.'.order_no,'.
                          $prefix_retailer_leads_tbl.'.id as order_id,'.
                          $prefix_maker_table.'.company_name,'.

                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"


                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')


                        // ->join($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
                       ->leftJoin($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.id','=',$stripe_transaction.'.lead_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','maker')                       

                       ->where($prefix_stripe_transaction.'.lead_id','!=',0);

          $quote_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.'.id as order_id,'.
                          $prefix_maker_table.'.company_name,'.

                           "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"


                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
                       ->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','maker')                       

                       ->where($prefix_stripe_transaction.'.quote_id','!=',0);  


        $customer_order_obj =  DB::table($stripe_transaction)
                              ->select(DB::raw($stripe_transaction.".*," .
                                $prefix_customer_quotes_table.'.order_no,'.
                                $prefix_customer_quotes_table.'.id as order_id,'.
                                $prefix_maker_table.'.company_name,'.

                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"
                              ))

                            ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                            ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                            
                            ->leftJoin($prefix_customer_quotes_table,$prefix_customer_quotes_table.'.id','=',$stripe_transaction.'.customer_order_id')

                            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                            ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                            ->where($role_table.'.slug','=','maker')                       

                            ->where($prefix_stripe_transaction.'.customer_order_id','!=',0);                




                if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
                {
                    $search_term      = $arr_search_column['q_username'];
                    $lead_obj = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->having('user_name','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
                {
                    $search_term      = $arr_search_column['q_order_no'];

                    $lead_obj = $lead_obj->having('order_no','LIKE',$search_term."%");
                    $quote_obj = $quote_obj->having('order_no','LIKE',$search_term."%");
                    $customer_order_obj = $customer_order_obj->having('order_no','LIKE',$search_term."%");


                }

                if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
                {
                    $search_term      = $arr_search_column['q_amount'];
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');

                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');

                } 

                if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_status'];
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');

                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transfer_id']) && $arr_search_column['q_transfer_id']!="")
                {
                    $search_term      = $arr_search_column['q_transfer_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');

                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                }

                

                if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
                {


                    $search_term      = $arr_search_column['q_created_at'];
                    $date             = DateTime::createFromFormat('m-d-Y',$search_term);
                    $date             = $date->format('Y-m-d');
                    
                   // $search_term  = date('Y-m-d',strtotime($search_term));
                    $lead_obj  = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    
                    $customer_order_obj = $customer_order_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');

                } 

                //dd($lead_obj->get()->toArray());
                //dd($quote_obj->get()->toArray());
                //dd($customer_order_obj->get()->toArray());
                
                $lead_obj = $lead_obj->union($quote_obj); 
                $lead_obj = $lead_obj->union($customer_order_obj); 

                $lead_obj = $lead_obj->orderBy('created_at','DESC');

               
                          
      }
       
      if ($request_param == 'representative' || $request_param == 'sales_manager') 
      {


        $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.'.id as order_id,'.

                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"
                                                    

                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by');

                        if($request_param == 'sales_manager')
                        {

                          $lead_obj = $lead_obj->join($sales_manager_table,$sales_manager_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                          ->groupBy($prefix_stripe_transaction.'.quote_id');
                        }else
                        {
                          $lead_obj = $lead_obj->join($reps_table,$reps_table.'.user_id','=',$prefix_stripe_transaction.'.received_by');
                        }


                       $lead_obj = $lead_obj->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->where($prefix_stripe_transaction.'.quote_id','!=',0);  

                
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $lead_obj = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }
  
      
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term  = $arr_search_column['q_amount'];
            $lead_obj     = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term  = $arr_search_column['q_transaction_status'];
            $lead_obj     = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {  
            $search_term  = $arr_search_column['q_transaction_id'];

            $lead_obj     = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transfer_id']) && $arr_search_column['q_transfer_id']!="")
        {
            $search_term  = $arr_search_column['q_transfer_id'];

            $lead_obj     = $lead_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
        }

   
        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {
            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
           
            $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
        }

        $lead_obj = $lead_obj->orderBy('created_at','DESC');

      }

      if ($request_param == 'all') {

        $retailer_obj = DB::table($transaction_mapping_table)
                        ->select(DB::raw($transaction_mapping_table.".order_no as order_no," .
                          $transaction_mapping_table.".order_id as order_id," .
                          $transaction_mapping_table.'.transaction_id,'.
                          $transaction_mapping_table.'.amount,'.
                          $transaction_mapping_table.'.transaction_status,'.
                          $transaction_mapping_table.'.created_at,'.
                          // $transaction_mapping_table.'.user_id as company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"


                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id');
        

        $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($prefix_retailer_leads_tbl.'.order_no,'.
                          $prefix_retailer_leads_tbl.".id as order_id," .
                          $stripe_transaction.".transaction_id as transaction_id," .
                          $stripe_transaction.".amount," .
                          $stripe_transaction.".status as transaction_status," .
                          $stripe_transaction.".created_at," .
                          // $prefix_maker_table.'.company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"


                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
                       ->leftJoin($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.id','=',$stripe_transaction.'.lead_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($prefix_stripe_transaction.'.lead_id','!=',0);

          $quote_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.".id as order_id," .
                          $stripe_transaction.".transaction_id as transaction_id," .
                          $stripe_transaction.".amount," .
                          $stripe_transaction.".status as transaction_status," .
                          $stripe_transaction.".created_at," .
                          // $prefix_maker_table.'.company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"


                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
                       ->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($prefix_stripe_transaction.'.quote_id','!=',0);                 


                if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
                {
                    $search_term      = $arr_search_column['q_username'];
                    $lead_obj = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $retailer_obj = $retailer_obj->having('user_name','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
                {
                    $search_term      = $arr_search_column['q_order_no'];

                    $lead_obj = $lead_obj->having('order_no','LIKE',$search_term."%");
                    $quote_obj = $quote_obj->having('order_no','LIKE',$search_term."%");
                    $retailer_obj = $retailer_obj->having('order_no','LIKE',$search_term."%");

                }

                if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
                {
                    $search_term      = $arr_search_column['q_amount'];
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $retailer_obj = $retailer_obj->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_status'];
                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $retailer_obj = $retailer_obj->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $retailer_obj = $retailer_obj->where($transaction_mapping_table.'.transaction_id','LIKE', '%'.$search_term.'%');
                }


                

                if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
                {


                    $search_term      = $arr_search_column['q_created_at'];
                    $date             = DateTime::createFromFormat('m-d-Y',$search_term);
                    $date             = $date->format('Y-m-d');
            
                    
                   // $search_term  = date('Y-m-d',strtotime($search_term));
                    $lead_obj      = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    $quote_obj     = $quote_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    $retailer_obj  = $retailer_obj->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
                } 

                if(isset($arr_search_column['q_user_role_name']) && $arr_search_column['q_user_role_name']!="")
                {
                    $search_term  = $arr_search_column['q_user_role_name'];

                    $lead_obj     = $lead_obj->having('role_name','LIKE',$search_term."%");
                    $quote_obj    = $quote_obj->having('role_name','LIKE',$search_term."%");
                    $retailer_obj = $retailer_obj->having('role_name','LIKE',$search_term."%");
                }

                $lead_obj = $lead_obj->union($quote_obj); 
                $lead_obj = $lead_obj->union($retailer_obj); 
                $lead_obj = $lead_obj->orderBy('created_at','DESC');

                //dd($lead_obj->get()->toArray(),332323123);          
      }

      if ($request_param == 'admin') {

        $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_retailer_leads_tbl.'.order_no,'.
                          $prefix_retailer_leads_tbl.'.id as order_id,'.
                          $prefix_maker_table.'.company_name,'.

                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"

                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.paid_by')


                        // ->join($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.paid_by')
                      
                       ->leftJoin($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.id','=',$stripe_transaction.'.lead_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','admin')
                       
                       ->where($prefix_stripe_transaction.'.lead_id','!=',0);

          $quote_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($stripe_transaction.".*," .
                          $prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.'.id as order_id,'.
                          $prefix_maker_table.'.company_name,'.

                           "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"

                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.paid_by')

                       ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.paid_by')
                      
                       ->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                       ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                       ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                       ->where($role_table.'.slug','=','admin')
                  
                       ->where($prefix_stripe_transaction.'.quote_id','!=',0); 




        $customer_order_obj =  DB::table($stripe_transaction)
                              ->select(DB::raw($stripe_transaction.".*," .
                                $prefix_customer_quotes_table.'.order_no,'.
                                $prefix_customer_quotes_table.'.id as order_id,'.
                                $prefix_maker_table.'.company_name,'.

                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"

                              ))

                            ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.paid_by')

                            ->join($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.paid_by')
                            
                            ->leftJoin($prefix_customer_quotes_table,$prefix_customer_quotes_table.'.id','=',$stripe_transaction.'.customer_order_id')

                            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$stripe_transaction.'.received_by')

                            ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                            ->where($role_table.'.slug','=','admin')                       

                            ->where($prefix_stripe_transaction.'.customer_order_id','!=',0); 

                               
                if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
                {
                    $search_term        = $arr_search_column['q_username'];
                    $lead_obj           = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $quote_obj          = $quote_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $customer_order_obj = $customer_order_obj->having('user_name','LIKE', '%'.$search_term.'%');

                } 

                if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
                {
                    $search_term      = $arr_search_column['q_order_no'];

                    $lead_obj    = $lead_obj->having('order_no','LIKE',$search_term."%");
                    $quote_obj   = $quote_obj->having('order_no','LIKE',$search_term."%");
                    $customer_order_obj = $customer_order_obj->having('order_no','LIKE',$search_term."%");
                    
                }

                if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
                {
                    $search_term      = $arr_search_column['q_amount'];

                    $lead_obj         = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $quote_obj        = $quote_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');

                    $customer_order_obj =  $customer_order_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
                {
                    $search_term       = $arr_search_column['q_transaction_status'];
                    $lead_obj          = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $quote_obj         = $quote_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');

                    $customer_order_obj =  $customer_order_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
                {
                    $search_term      = $arr_search_column['q_transaction_id'];

                    $lead_obj  = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');

                    $customer_order_obj =  $customer_order_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transfer_id']) && $arr_search_column['q_transfer_id']!="")
                {
                    $search_term      = $arr_search_column['q_transfer_id'];

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');

                    $customer_order_obj =  $customer_order_obj->where($prefix_stripe_transaction.'.transfer_id','LIKE', '%'.$search_term.'%');
                }

                

                if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
                {


                    $search_term      = $arr_search_column['q_created_at'];
                    $date             = DateTime::createFromFormat('m-d-Y',$search_term);
                    $date             = $date->format('Y-m-d');
                    
                   // $search_term  = date('Y-m-d',strtotime($search_term));

                    $lead_obj = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    
                    $quote_obj = $quote_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                   
                    $customer_order_obj =  $customer_order_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');

                } 

                $lead_obj = $lead_obj->union($quote_obj);
                $lead_obj = $lead_obj->union($customer_order_obj);

                $lead_obj = $lead_obj->orderBy('created_at','DESC');
                          
      }


      if($request_param == 'all_orders')
      { 
          $retailer_obj = DB::table($transaction_mapping_table)
                        ->select(DB::raw($transaction_mapping_table.".order_no as order_no," .
                          $transaction_mapping_table.".order_id as order_id," .
                          $transaction_mapping_table.'.transaction_id,'.
                          $transaction_mapping_table.'.amount,'.
                          $transaction_mapping_table.'.transaction_status,'.
                          $transaction_mapping_table.'.created_at,'.
                          $transaction_mapping_table.'.user_id as company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"

                        ))

                        ->join($prefix_user_table,$prefix_user_table.'.id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$transaction_mapping_table.'.user_id')

                        ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id');




            if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
            {
                $search_term  = $arr_search_column['q_username'];
                $retailer_obj = $retailer_obj->having('user_name','LIKE', '%'.$search_term.'%');
            } 

            if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
            {
                $search_term  = $arr_search_column['q_order_no'];
                $retailer_obj = $retailer_obj->having('order_no','LIKE',$search_term."%");

            }

            if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
            {
                $search_term  = $arr_search_column['q_amount'];
                $retailer_obj = $retailer_obj->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
            } 

            if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
            {
                $search_term  = $arr_search_column['q_transaction_status'];
                $retailer_obj = $retailer_obj->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
            {
                $search_term  = $arr_search_column['q_transaction_id'];
                $retailer_obj = $retailer_obj->where($transaction_mapping_table.'.transaction_id','LIKE', '%'.$search_term.'%');
            }


            if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
            {

                $search_term      = $arr_search_column['q_created_at'];
                $date             = DateTime::createFromFormat('m-d-Y',$search_term);
                $date             = $date->format('Y-m-d');
                 
                $retailer_obj  = $retailer_obj->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
            } 

            if(isset($arr_search_column['q_user_role_name']) && $arr_search_column['q_user_role_name']!="")
            {
                $search_term  = $arr_search_column['q_user_role_name'];
                $retailer_obj = $retailer_obj->having('role_name','LIKE',$search_term."%");
            }

            
            if(isset($arr_search_column['q_order_type']) && $arr_search_column['q_order_type']!="")
            {
                $search_term  = $arr_search_column['q_order_type'];

                if($search_term == 'retailer_order')
                { 
                  $retailer_obj = $retailer_obj->join($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.order_no',$prefix_transaction_mapping_table.'.order_no');
                }

                if($search_term == 'rep_sales_orders')
                {
                  

                  $retailer_obj = $retailer_obj->join($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.order_no',$prefix_transaction_mapping_table.'.order_no');
                }
               
                if($search_term == 'customer_order')
                {
                   $retailer_obj = $retailer_obj->join($prefix_customer_quotes_table,$prefix_customer_quotes_table.'.order_no',$prefix_transaction_mapping_table.'.order_no');
                }

            }

            $lead_obj = $retailer_obj->orderBy('created_at','DESC'); 

      }

      if($request_param == 'all_transaction')
      {

          $lead_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($prefix_retailer_leads_tbl.'.order_no,'.
                          $prefix_retailer_leads_tbl.".id as order_id," .
                          $stripe_transaction.".transaction_id as transaction_id," .
                          $stripe_transaction.".amount," .
                          $stripe_transaction.".status as transaction_status," .
                          $stripe_transaction.".created_at," .

                          $stripe_transaction.".paid_by," .
                          $stripe_transaction.".received_by," .

                          $prefix_maker_table.'.company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"
                        ))

                      ->leftJoin($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                      ->leftJoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                       
                      ->leftJoin($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.id','=',$stripe_transaction.'.lead_id')

                      ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                      ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                      ->where($prefix_stripe_transaction.'.lead_id','!=',0);

          $quote_obj = DB::table($stripe_transaction)
                        ->select(DB::raw($prefix_representative_leads_tbl.'.order_no,'.
                          $prefix_representative_leads_tbl.".id as order_id," .
                          $stripe_transaction.".transaction_id as transaction_id," .
                          $stripe_transaction.".amount," .
                          $stripe_transaction.".status as transaction_status," .
                          $stripe_transaction.".created_at," .

                          $stripe_transaction.".paid_by," .
                          $stripe_transaction.".received_by," .

                          $prefix_maker_table.'.company_name,'.
                          $prefix_user_table.'.id as user_id,'.
                          $role_table.'.name as role_name,'.
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                          .$prefix_user_table.".last_name) as user_name"


                        ))

                    ->leftJoin($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                    ->leftJoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')
                      
   
                    ->leftJoin($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.id','=',$stripe_transaction.'.quote_id')

                    ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                    ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                    ->where($prefix_stripe_transaction.'.quote_id','!=',0);   


          $customer_orders_obj = DB::table($stripe_transaction)
                                  ->select(DB::raw($prefix_customer_quotes_table.'.order_no,'.
                                  $prefix_customer_quotes_table.".id as order_id," .
                                  $stripe_transaction.".transaction_id as transaction_id," .
                                  $stripe_transaction.".amount," .
                                  $stripe_transaction.".status as transaction_status," .
                                  $stripe_transaction.".created_at," .

                                  $stripe_transaction.".paid_by," .
                                  $stripe_transaction.".received_by," .

                                  $prefix_maker_table.'.company_name,'.
                                  $prefix_user_table.'.id as user_id,'.
                                  $role_table.'.name as role_name,'.
                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                  .$prefix_user_table.".last_name) as user_name"

                        ))

                    ->leftJoin($prefix_user_table,$prefix_user_table.'.id','=',$prefix_stripe_transaction.'.received_by')

                    ->leftJoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                      
                    ->leftJoin($prefix_customer_quotes_table,$prefix_customer_quotes_table.'.id','=',$stripe_transaction.'.customer_order_id')

                    ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_stripe_transaction.'.received_by')

                    ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                    ->where($prefix_stripe_transaction.'.customer_order_id','!=',0);                            


                if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
                {
                    $search_term         = $arr_search_column['q_username'];
                    $lead_obj            = $lead_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $quote_obj           = $quote_obj->having('user_name','LIKE', '%'.$search_term.'%');
                    $customer_orders_obj = $customer_orders_obj->having('user_name','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
                {
                    $search_term     = $arr_search_column['q_order_no'];
                    $lead_obj        = $lead_obj->having('order_no','LIKE',$search_term."%");
                    $quote_obj       = $quote_obj->having('order_no','LIKE',$search_term."%");
                    $customer_orders_obj    = $customer_orders_obj->having('order_no','LIKE',$search_term."%");

                }

                if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
                {
                    $search_term        = $arr_search_column['q_amount'];
                    $lead_obj           = $lead_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $quote_obj           = $quote_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                    $customer_orders_obj = $customer_orders_obj->where($prefix_stripe_transaction.'.amount','LIKE', '%'.$search_term.'%');
                } 

                if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
                {
                    $search_term         = $arr_search_column['q_transaction_status'];
                    $lead_obj            = $lead_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $quote_obj           = $quote_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                    $customer_orders_obj = $customer_orders_obj->where($prefix_stripe_transaction.'.status','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
                {
                    $search_term        = $arr_search_column['q_transaction_id'];

                    $lead_obj            = $lead_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $quote_obj           = $quote_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                    $customer_orders_obj = $customer_orders_obj->where($prefix_stripe_transaction.'.transaction_id','LIKE', '%'.$search_term.'%');
                }

                if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
                {

                    $search_term      = $arr_search_column['q_created_at'];
                    $date             = DateTime::createFromFormat('m-d-Y',$search_term);
                    $date             = $date->format('Y-m-d');
                
                    // $search_term  = date('Y-m-d',strtotime($search_term));
                    $lead_obj             = $lead_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    $quote_obj            = $quote_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                    $customer_orders_obj  = $customer_orders_obj->where($prefix_stripe_transaction.'.created_at','LIKE', '%'.$date.'%');
                } 

                if(isset($arr_search_column['q_user_role_name']) && $arr_search_column['q_user_role_name']!="")
                {
                    $search_term         = $arr_search_column['q_user_role_name'];

                    $lead_obj            = $lead_obj->having('role_name','LIKE',$search_term."%");
                    $quote_obj           = $quote_obj->having('role_name','LIKE',$search_term."%");
                    $customer_orders_obj = $customer_orders_obj->having('role_name','LIKE',$search_term."%");
                }

/*
                if(isset($arr_search_column['q_order_type']) && $arr_search_column['q_order_type']!="")
                {
                    $search_term  = $arr_search_column['q_order_type'];

                    if($search_term == 'retailer_order')
                    { 
                        $lead_obj = $lead_obj->join($prefix_retailer_leads_tbl,$prefix_retailer_leads_tbl.'.lid',$prefix_stripe_transaction.'.lead_id');
                    }

                    if($search_term == 'rep_sales_orders')
                    {
                        $quote_obj = $quote_obj->join($prefix_representative_leads_tbl,$prefix_representative_leads_tbl.'.qid',$prefix_stripe_transaction.'.quote_id');
                    }
                   
                    if($search_term == 'customer_order')
                    {
                       $customer_orders_obj = $customer_orders_obj->join($prefix_customer_quotes_table,$prefix_customer_quotes_table.'.cust_order_id',$prefix_stripe_transaction.'.customer_order_id');
                    }

                }*/


              $lead_obj = $lead_obj->union($quote_obj); 
              $lead_obj = $lead_obj->union($customer_orders_obj); 
              $lead_obj = $lead_obj->orderBy('created_at','DESC');

             

      }

     // dd($lead_obj->get()->toArray());
      return $lead_obj;


    }

    /*
       Date :  11/06/2020
       Auth : Jaydip
       Parameters : $userRole - for identification of panel like show list for admin panel,vendor panel
       Update by Pranit - remove rep/sales refund from retailer on 17 july 20
    */
    public function get_retailer_refund($userRole,$arr_search_column)
    {
      $loginUser = Sentinel::getUser();

      if($loginUser)
      {
        $loginUserId = $loginUser->id;
      }


        /* ---------------- View Query ----------------------------------*/  
        $obj_result = DB::table('vw_retailer_refund');

      $user_table        = $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $refund_table        = $this->RefundModel->getTable();
      $prefix_refund_table = DB::getTablePrefix().$refund_table;

      $role_table        = $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
      $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $rep_leads_table        = $this->RepresentativeLeadsModel->getTable();
      $prefix_rep_leads_table = DB::getTablePrefix().$rep_leads_table;

      $role_user_table        = $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $obj_result = DB::table($prefix_refund_table)
                      ->select(DB::raw("RN.id as retailer_id,".
                                          "CONCAT(RN.first_name,' ',RN.last_name) as retailer_name,".
                                          "CONCAT(paid_by.first_name,' ',paid_by.last_name) as paid_by,".
                                          $retailer_table.".dummy_store_name,".
                                          $prefix_refund_table.".amount,".
                                          $prefix_refund_table.".status,".
                                          $prefix_refund_table.".balance_transaction,".
                                          $prefix_refund_table.".order_no,".
                                          $prefix_refund_table.".order_id,".
                                          $prefix_refund_table.".created_at"
                                   ))

                      // ->leftJoin($prefix_retailer_quotes_table,$prefix_refund_table.'.order_id','=',$prefix_retailer_quotes_table.'.id');

                      ->leftjoin($prefix_retailer_quotes_table,function($join) use($prefix_refund_table,$prefix_retailer_quotes_table){

                          $join->on($prefix_refund_table.'.order_id','=',$prefix_retailer_quotes_table.'.id')
                          ->on($prefix_refund_table.'.order_no','=',$prefix_retailer_quotes_table.'.order_no');

                      });



                      $obj_result = $obj_result->leftJoin($user_table.' as RN',$prefix_retailer_quotes_table.'.retailer_id','=','RN.id')

                      ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefix_retailer_quotes_table.'.retailer_id')

                      ->leftJoin($user_table.' as paid_by',$prefix_refund_table.'.paid_by','=','paid_by.id')
                     
                      ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_retailer_quotes_table.'.retailer_id')

                      ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                      ->where($role_table.'.slug','=','retailer');

                       if($userRole == 'maker')
                      {
                       /* $obj_result = $obj_result->where($prefix_retailer_quotes_table.'.is_direct_payment','1')
                                            ->where($prefix_retailer_quotes_table.'.maker_id',$loginUserId);*/

                        $obj_result = $obj_result->where($prefix_refund_table.'.paid_by','=',
                          $loginUserId);

                      }


                      //dd($obj_result->toSql(),$obj_result->getBindings());

        // $rep_orders = DB::table($prefix_refund_table)
        //               ->select(DB::raw("RN.id as retailer_id,".
        //                                   "CONCAT(RN.first_name,' ',RN.last_name) as retailer_name,".
        //                                   "CONCAT(paid_by.first_name,' ',paid_by.last_name) as paid_by,".
        //                                   $prefix_refund_table.".amount,".
        //                                   $prefix_refund_table.".status,".
        //                                   $prefix_refund_table.".balance_transaction,".
        //                                   $prefix_refund_table.".order_no,".
        //                                   $prefix_refund_table.".order_id,".
        //                                   $prefix_refund_table.".created_at"
        //                            ))

        //               // ->leftJoin($prefix_rep_leads_table,$prefix_refund_table.'.order_id','=',$prefix_rep_leads_table.'.id');

        //               ->leftjoin($prefix_rep_leads_table,function($join) use($prefix_refund_table,$prefix_rep_leads_table){

        //                     $join->on($prefix_refund_table.'.order_id','=',$prefix_rep_leads_table.'.id')
        //                     ->on($prefix_refund_table.'.order_no','=',$prefix_rep_leads_table.'.order_no');

        //                 });

        //               $rep_orders = $rep_orders->leftJoin($user_table.' as RN',$prefix_rep_leads_table.'.retailer_id','=','RN.id')

        //               ->leftJoin($user_table.' as paid_by',$prefix_refund_table.'.paid_by','=','paid_by.id')
                     
        //               ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_rep_leads_table.'.retailer_id')

        //               ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

        //               ->where($role_table.'.slug','=','retailer');


        //                if($userRole == 'maker')
        //                 {
                        
        //                   $rep_orders = $rep_orders->where($prefix_refund_table.'.paid_by','=',
        //                     $loginUserId);

        //                 }


        /* ---------------- Filtering Logic ----------------------------------*/  
      
        
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_result = $obj_result->having('retailer_name','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_result = $obj_result->where($retailer_table.".dummy_store_name",'LIKE', '%'.$search_term.'%');
            //$rep_orders = $rep_orders->having('retailer_name','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_result = $obj_result->where('.amount','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_paid_by']) && $arr_search_column['q_paid_by']!="")
        {
            $search_term      = $arr_search_column['q_paid_by'];

            $search = strtolower($search_term);
            

            if(preg_match("/{$search}/i", 'admin') === 1) {
             
                $obj_result = $obj_result->where('paid_by',null);
            }
            else{
              
                $obj_result = $obj_result->having('paid_by','LIKE', '%'.$search_term.'%');
            }
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_result = $obj_result->where('.balance_transaction','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_result = $obj_result->where('.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {
            
            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
           
            $date             = $date->format('Y-m-d');
            $obj_result = $obj_result->where('.created_at','LIKE', '%'.$date.'%');
        }
                      

        //$obj_result = $obj_result->union($rep_orders); 
        //dd($obj_result->toSql(),$obj_result->getBindings());

        return $obj_result;
    }

    public function get_rep_sales_refund($userRole,$arr_search_column)
    {


      $loginUser = Sentinel::getUser();

      if($loginUser)
      {
        $loginUserId = $loginUser->id;
      }

      $user_table        = $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $refund_table        = $this->RefundModel->getTable();
      $prefix_refund_table = DB::getTablePrefix().$refund_table;

      $role_table        = $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $retailer_quotes_table        = $this->RetailerQuotesModel->getTable();
      $prefix_retailer_quotes_table = DB::getTablePrefix().$retailer_quotes_table;

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $rep_leads_table        = $this->RepresentativeLeadsModel->getTable();
      $prefix_rep_leads_table = DB::getTablePrefix().$rep_leads_table;

      $role_user_table        = $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $obj_result = DB::table($prefix_refund_table)
                      ->select(DB::raw("RN.id as retailer_id,".
                                          "CONCAT(RN.first_name,' ',RN.last_name) as retailer_name,".
                                          "CONCAT(paid_by.first_name,' ',paid_by.last_name) as paid_by,".
                                          "CONCAT(UserRepSales.first_name,' ',UserRepSales.last_name) as user_name,".
                                          // "CONCAT(UserSales.first_name,' ',UserSales.last_name) as sales_name,".
                                          $prefix_refund_table.".amount,".
                                          $prefix_refund_table.".status,".
                                          $prefix_refund_table.".balance_transaction,".
                                          $prefix_refund_table.".order_no,".
                                          $prefix_refund_table.".order_id,".
                                          $prefix_rep_leads_table.".representative_id,".
                                          $prefix_rep_leads_table.".sales_manager_id,".
                                          $prefix_refund_table.".created_at,".
                                          $retailer_table.".dummy_store_name"
                                   ))

                      // ->leftJoin($prefix_rep_leads_table,$prefix_refund_table.'.order_id','=',$prefix_rep_leads_table.'.id');

                      ->leftjoin($prefix_rep_leads_table,function($join) use($prefix_refund_table,$prefix_rep_leads_table){

                            $join->on($prefix_refund_table.'.order_id','=',$prefix_rep_leads_table.'.id')
                            ->on($prefix_refund_table.'.order_no','=',$prefix_rep_leads_table.'.order_no');

                        });

                      $obj_result = $obj_result->leftJoin($user_table.' as RN',$prefix_rep_leads_table.'.retailer_id','=','RN.id')

                      ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefix_rep_leads_table.'.retailer_id')

                      ->leftJoin($user_table.' as paid_by',$prefix_refund_table.'.paid_by','=','paid_by.id')
                     
                      ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_rep_leads_table.'.retailer_id')

                      ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                      ->where($role_table.'.slug','=','retailer')
                      ->where($prefix_rep_leads_table.'.representative_id','!=' ,0)
                      ->orderBy($prefix_refund_table.".id",'DESC');
                      if($userRole == 'maker')
                      {
                      
                        $obj_result = $obj_result->where($prefix_refund_table.'.paid_by','=',
                          $loginUserId);
                      }

                  $obj_result = $obj_result->leftJoin($user_table.' as UserRepSales',$prefix_rep_leads_table.'.representative_id','=','UserRepSales.id');


        $obj_sales_result = DB::table($prefix_refund_table)
                      ->select(DB::raw("RN.id as retailer_id,".
                                          "CONCAT(RN.first_name,' ',RN.last_name) as retailer_name,".
                                          "CONCAT(paid_by.first_name,' ',paid_by.last_name) as paid_by,".
                                          "CONCAT(UserRepSales.first_name,' ',UserRepSales.last_name) as user_name,".
                                          // "CONCAT(UserSales.first_name,' ',UserSales.last_name) as sales_name,".
                                          $prefix_refund_table.".amount,".
                                          $prefix_refund_table.".status,".
                                          $prefix_refund_table.".balance_transaction,".
                                          $prefix_refund_table.".order_no,".
                                          $prefix_refund_table.".order_id,".
                                          $prefix_rep_leads_table.".representative_id,".
                                          $prefix_rep_leads_table.".sales_manager_id,".
                                          $prefix_refund_table.".created_at,".
                                          $retailer_table.".dummy_store_name"
                                   ))

                      // ->leftJoin($prefix_rep_leads_table,$prefix_refund_table.'.order_id','=',$prefix_rep_leads_table.'.id');

                      ->leftjoin($prefix_rep_leads_table,function($join) use($prefix_refund_table,$prefix_rep_leads_table){

                            $join->on($prefix_refund_table.'.order_id','=',$prefix_rep_leads_table.'.id')
                            ->on($prefix_refund_table.'.order_no','=',$prefix_rep_leads_table.'.order_no');

                        });

                      $obj_sales_result = $obj_sales_result->leftJoin($user_table.' as RN',$prefix_rep_leads_table.'.retailer_id','=','RN.id')

                      ->leftJoin($user_table.' as paid_by',$prefix_refund_table.'.paid_by','=','paid_by.id')

                      ->leftjoin($retailer_table,$retailer_table.'.user_id','=',$prefix_rep_leads_table.'.retailer_id')
                     
                      ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$prefix_rep_leads_table.'.retailer_id')

                      ->leftJoin($role_table,$role_table.'.id','=',$role_user_table.'.role_id')

                      ->where($role_table.'.slug','=','retailer')
                      ->where($prefix_rep_leads_table.'.sales_manager_id','!=' ,0)
                      ->orderBy($prefix_refund_table.".id",'DESC');
                      if($userRole == 'maker')
                      {                      
                        $obj_sales_result = $obj_sales_result->where($prefix_refund_table.'.paid_by','=',
                          $loginUserId);
                      }

                      

          /*get representative and sales manager name*/
          $obj_sales_result = $obj_sales_result->leftJoin($user_table.' as UserRepSales',$prefix_rep_leads_table.'.sales_manager_id','=','UserRepSales.id');
          // $obj_result = $obj_result->leftJoin($user_table.' as UserSales',$prefix_rep_leads_table.'.sales_manager_id','=','UserSales.id');

        /* ---------------- Filtering Logic ----------------------------------*/  


    
        /* ---------------- View Query ----------------------------------*/  
        $obj_result = DB::table('vw_rep_sales_refund');
    
        /* ---------------- Filtering Logic ----------------------------------*/
        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_result = $obj_result->having('retailer_name','LIKE', '%'.$search_term.'%');
           
        } 

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_result = $obj_result->having('dummy_store_name','LIKE', '%'.$search_term.'%');
            $obj_sales_result = $obj_sales_result->having('dummy_store_name','LIKE', '%'.$search_term.'%');
        } 
        if(isset($arr_search_column['q_rep_sales_name']) && $arr_search_column['q_rep_sales_name']!="")
        {
            $search_term      = $arr_search_column['q_rep_sales_name'];
            $obj_result = $obj_result->having('user_name','LIKE', '%'.$search_term.'%');
            
        }   
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term      = $arr_search_column['q_amount'];
            $obj_result = $obj_result->where('.amount','LIKE', '%'.$search_term.'%');
            
        }   

        if(isset($arr_search_column['q_paid_by']) && $arr_search_column['q_paid_by']!="")
        {
            $search_term      = $arr_search_column['q_paid_by'];

            $search = strtolower($search_term);
            

            if(preg_match("/{$search}/i", 'admin') === 1) {
             
                $obj_result = $obj_result->where('paid_by',null);
            }
            else{
              
                $obj_result = $obj_result->having('paid_by','LIKE', '%'.$search_term.'%');
            }
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term      = $arr_search_column['q_transaction_id'];

            $obj_result = $obj_result->where('.balance_transaction','LIKE', '%'.$search_term.'%');
            
        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];

            $obj_result = $obj_result->where('.order_no','LIKE', '%'.$search_term.'%');
            
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {
            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            $obj_result = $obj_result->where('.created_at','LIKE', '%'.$date.'%');
            
        }


        // $obj_result = $obj_result->union($obj_sales_result);
        
        // $obj_result = $obj_result->orderBy("id",'DESC');
        // dd($obj_result->get()->toArray());

        return $obj_result;
    }

    public function get_customer_refund($userRole,$arr_search_column)
    {
      $loginUser = Sentinel::getUser();

      if($loginUser)
      {
        $loginUserId = $loginUser->id;
      }

        /* ---------------- View Query ----------------------------------*/  
        $obj_result = DB::table('vw_customer_refund');
    
        /* ---------------- Filtering Logic ----------------------------------*/
        
        if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
        {
            $search_term  = $arr_search_column['q_customer_name'];
            $obj_result   = $obj_result->having('customer_name','LIKE', '%'.$search_term.'%');
            
        }   
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term = $arr_search_column['q_amount'];
            $obj_result  = $obj_result->where('.amount','LIKE', '%'.$search_term.'%');
            
        }   

        if(isset($arr_search_column['q_paid_by']) && $arr_search_column['q_paid_by']!="")
        {
            $search_term      = $arr_search_column['q_paid_by'];

            $search = strtolower($search_term);
            

            if(preg_match("/{$search}/i", 'admin') === 1) {
             
                $obj_result = $obj_result->where('paid_by',null);
            }
            else{
              
                $obj_result = $obj_result->having('paid_by','LIKE', '%'.$search_term.'%');
            }
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term  = $arr_search_column['q_transaction_id'];

            $obj_result   = $obj_result->where('.balance_transaction','LIKE', '%'.$search_term.'%');
            

        }

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term  = $arr_search_column['q_order_no'];

            $obj_result   = $obj_result->where('.order_no','LIKE', '%'.$search_term.'%');

           
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {
            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m/d/Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            $obj_result = $obj_result->where('.created_at','LIKE', '%'.$date.'%');
            
        }
                      
    
        return $obj_result;
    }
}