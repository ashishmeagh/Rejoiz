<?php
namespace App\Common\Services\Api\Rejoiz\Retailer;
   
use App\Models\UserModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Models\RetailerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Common\Services\HelperService;
use App\Models\RefundModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Common\Services\Api\Common\CommonService;
use DB;
use DateTime;
use Excel;

  
class TransactionService {

  public function __construct(UserModel $UserModel,
                              TransactionsModel $TransactionsModel,
                              RetailerQuotesModel $RetailerQuotesModel,
                              RepresentativeLeadsModel $RepresentativeLeadsModel,
                              TransactionMappingModel $TransactionMappingModel,
                              RefundModel $RefundModel,
                              CommonService $CommonService,
                              HelperService $HelperService)
  {
	    $this->UserModel                = $UserModel;
      $this->TransactionsModel         = $TransactionsModel;
      $this->RetailerQuotesModel       = $RetailerQuotesModel;
      $this->RepresentativeLeadsModel  = $RepresentativeLeadsModel;
      $this->TransactionMappingModel   = $TransactionMappingModel;
      $this->CommonService             = $CommonService;
      $this->RefundModel               = $RefundModel;
      $this->HelperService             = $HelperService;
  }

    public function list($form_data)
    {
        $user = $form_data['auth_user'];
        
        $loggedIn_userId = 0;
     
        if($user)
        {
            $loggedIn_userId = $user->id;
        }   
                
        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table =  DB::getTablePrefix().$user_table;

        $transaction_table        =  $this->TransactionsModel->getTable();
        $prefix_transaction_table =  DB::getTablePrefix().$transaction_table;

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping_table = DB::getTablePrefix().$transaction_mapping_table;


        $refund_table         = $this->RefundModel->getTable();
        $prefix_refund_table  = DB::getTablePrefix().$refund_table;

        //get data from transaction mapping for order payment

        $obj_user = DB::table($transaction_mapping_table)
                        ->select(DB::raw(
                                            $transaction_mapping_table.".amount,".
                                            $transaction_mapping_table.".order_no,".
                                            $transaction_mapping_table.".order_id,".
                                            $transaction_mapping_table.".transaction_status,".
                                            $transaction_mapping_table.".transaction_id,".
                                            $transaction_mapping_table.".created_at"
                                           
                                        ))

                        ->where($transaction_mapping_table.'.user_id','=',$loggedIn_userId)
                        ->orderBy($transaction_mapping_table.'.created_at','DESC');


        //get records from refund table for refunded trasaction

        $obj_refund_transaction =  DB::table($prefix_refund_table)
                                    ->select(DB::raw(
                                                      $prefix_refund_table.".amount,".
                                                      $prefix_refund_table.".order_no,".
                                                      $prefix_refund_table.".order_id,".
                                                      $prefix_refund_table.".status,".
                                                      $prefix_refund_table.".balance_transaction as transaction_id,".
                                                      $prefix_refund_table.".created_at"
                                      ))

                                    ->where($prefix_refund_table.'.received_by','=',$loggedIn_userId)
                                    ->orderBy($prefix_refund_table.'.created_at','DESC');                     



         /* ---------------- Filtering Logic ----------------------------------*/  
 
        if(isset($form_data['filter']['general_search']) &&  $form_data['filter']['general_search']!="")
        {

          $search_term = $form_data['filter']['general_search'];

          $obj_refund_transaction = $obj_refund_transaction->whereRaw(
                                "(  `".$prefix_refund_table."`.`order_no` LIKE '%".$search_term."%' OR
                                    `".$prefix_refund_table."`.`amount` LIKE '%".$search_term."%' OR
                                    `".$prefix_refund_table."`.`balance_transaction` LIKE '%".$search_term."%' 
                                  )"
                               );
          $obj_user = $obj_user->whereRaw(
                                "(  `".$transaction_mapping_table."`.`order_no` LIKE '%".$search_term."%' OR
                                    `".$transaction_mapping_table."`.`amount` LIKE '%".$search_term."%' OR
                                    `".$transaction_mapping_table."`.`transaction_id` LIKE '%".$search_term."%' 
                                  )"
                               );
        } 

        if(isset($form_data['filter']['transaction_date']) && $form_data['filter']['transaction_date']!="")
        {
            $search_term      = $form_data['filter']['transaction_date'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
      
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');

            $obj_refund_transaction  = $obj_refund_transaction->where($prefix_refund_table.'.created_at','LIKE','%'.$search_term.'%');
        }   

        if(isset($form_data['filter']['transaction_status']) && $form_data['filter']['transaction_status'] != '')
        {
          $search_term = $form_data['filter']['transaction_status'];
            
            $obj_refund_transaction = $obj_refund_transaction->where($prefix_refund_table.'.status','LIKE','%'.$search_term.'%'); 
            
            $obj_user = $obj_user->where($transaction_mapping_table.'.transaction_status','LIKE','%'.$search_term.'%');
        }


        if(isset($form_data['filter']['payment_type']) && $form_data['filter']['payment_type']!="")
        {

            $search_term   = $form_data['filter']['payment_type'];

            if($search_term == "refund_payment")
            {
                $obj_refund_transaction   = $obj_refund_transaction->where('received_by',$loggedIn_userId)
                                                                   ->where($prefix_refund_table.'.balance_transaction','LIKE','%txn_%');

                                                              
                $obj_user   = $obj_user->where('user_id',$loggedIn_userId)
                                       ->where($transaction_mapping_table.'.transaction_id','LIKE','%txn_%');

            }else{
              
              $obj_user   = $obj_user->where('user_id',$loggedIn_userId)
                                      ->where($transaction_mapping_table.'.transaction_id','LIKE','%ch_%');

              $obj_refund_transaction   = $obj_refund_transaction->where('received_by',$loggedIn_userId)
                                                                  ->where($prefix_refund_table.'.balance_transaction','LIKE','%ch_%');
            }            
        }

        $obj_user = $obj_user->union($obj_refund_transaction);

        $per_page   = isset($form_data['per_page'])?$form_data['per_page']:10; 
        $page       = isset($form_data['page'])?$form_data['page']:1;  

        $arr_data    = $obj_user->get();
        $append_url  = url()->current();
        $append_url  =  $append_url."?page=".$page;  

        $paginator   = $this->get_pagination_data($arr_data->toArray(), $page, $per_page ,$append_url);

        //Calculate total by Harshada on date 09 Sep 2020
        $total_amt = 0;      
        $total_amt =array_reduce($obj_user->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;
        }, 0);

        $arr_transactions = $paginator->toArray();

        $transactions['data'] = array_values($arr_transactions['data']);

        $arr_result['data'] = [];

        foreach ($transactions['data'] as $key => $value)
        {
          $arr_result['data'][intval($key)]["order_id"]   = isset($value->order_id)?$value->order_id:0;

          $arr_result['data'][intval($key)]["order_no"]   = isset($value->order_no)?$value->order_no:0;

          $arr_result['data'][intval($key)]["amount"]     = isset($value->amount)?$value->amount:0;

          $arr_result['data'][intval($key)]["transaction_status"]   = isset($value->transaction_status)?$value->transaction_status:0;
          
          $arr_result['data'][intval($key)]["transaction_id"] = isset($value->transaction_id)?$value->transaction_id:0;

          $arr_result['data'][intval($key)]["order_type"]     = $this->order_type($arr_result['data'][intval($key)]["order_no"],$arr_result['data'][intval($key)]["order_id"]);

          $arr_result['data'][intval($key)]["transaction_type"]  = $this->transaction_type($arr_result['data'][intval($key)]["order_no"],$arr_result['data'][intval($key)]["order_id"],$arr_result['data'][intval($key)]["transaction_id"]);

          $arr_result['data'][intval($key)]["created_at"]     = isset($value->created_at)?$value->created_at:0;

          $arr_result['data'][intval($key)]["company_name"] = $this->HelperService->get_order_company_name($arr_result['data'][intval($key)]["order_no"],$arr_result['data'][intval($key)]["order_id"],"Customer-Order");
        }

        $arr_result['pagination']["current_page"]     = $arr_transactions['current_page'];
        $arr_result['pagination']["first_page_url"]   = $arr_transactions['first_page_url'];
        $arr_result['pagination']["from"]             = $arr_transactions['from'];
        $arr_result['pagination']["last_page"]        = $arr_transactions['last_page'];
        $arr_result['pagination']["last_page_url"]    = $arr_transactions['last_page_url'];
        $arr_result['pagination']["next_page_url"]    = $arr_transactions['next_page_url'];
        $arr_result['pagination']["path"]             = $arr_transactions['path'];
        $arr_result['pagination']["per_page"]         = $arr_transactions['per_page'];
        $arr_result['pagination']["prev_page_url"]    = $arr_transactions['prev_page_url'];
        $arr_result['pagination']["to"]               = $arr_transactions['to'];
        $arr_result['pagination']["total"]            = $arr_transactions['total'];
        $arr_result["total_amount"]                   = isset($total_amt)?$total_amt:'';
 
        $arr_result['data']   = $this->CommonService->get_status_display_names($arr_result['data'],'listing');
 
          $response           = [];
        $response['status']   = 'success';
        $response['message']  = 'Transactions list get successfully.';
        $response['data']     = isset($arr_result)?$arr_result:[];

        return $response;      
    }


    function get_pagination_data($arr_data = [], $pageStart = 1, $per_page = 0, $apppend_data = []) 
    { 
   
           $perPage  = $per_page; /* Indicates how many to Record to paginate */
           $offSet   = ($pageStart * $perPage) - $perPage; /* Start displaying Records from this No.;*/
           $count    = count($arr_data);        /* Get only the Records you need using array_slice */
           $itemsForCurrentPage = array_slice($arr_data, $offSet, $perPage, true);       
   
           $paginator = new LengthAwarePaginator($itemsForCurrentPage, $count, $per_page, Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));
           $paginator->appends($apppend_data);
           
           return $paginator;
    }

    public function order_type($order_no,$order_id)
    {
      $order_type ='';

      $retailer_order = $this->RetailerQuotesModel
                             ->where('order_no',$order_no)
                             ->where('id',$order_id)
                             ->first();

                           

      if(isset($retailer_order))
      {
         $order_type = 'Customer-Order';
      }


      $rep_sales_order = $this->RepresentativeLeadsModel
                              ->where('order_no',$order_no)
                              ->where('id',$order_id)
                              ->first();

                             

      if(isset($rep_sales_order))
      { 
         $order_type = 'Rep-Sales-Order';
      }

      return $order_type;
    }

    public function transaction_type($order_no,$order_id,$transaction_id)
    {
      $type = '';

      if($order_no != "")
      {
            $refund_details = $this->RefundModel
                                    ->where('order_id',$order_id)
                                    ->where('order_no',$order_no)
                                    ->where('balance_transaction',$transaction_id)
                                    ->first();

            if(isset($refund_details))
            {
                                           
                $type = 'Cancel-Refund';
            }  





            $transaction_mapping_obj = $this->TransactionMappingModel
                                            ->where('order_id',$order_id)
                                            ->where('order_no',$order_no)
                                            ->where('transaction_id',$transaction_id)

                                            ->first();

            if(isset($transaction_mapping_obj))
            {
               
               $type = 'Order-Payment';
                
            }
      }

      return $type; 
    }

    public function get_export_transasction_orders($form_data)
    {
        $user = $form_data['auth_user'];
        
        $loggedIn_userId = 0;
     
        if($user)
        {
          $loggedIn_userId = $user->id;
        }   

        $arr_search_column = $form_data;

        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table =  DB::getTablePrefix().$user_table;

        $transaction_table        =  $this->TransactionsModel->getTable();
        $prefix_transaction_table =  DB::getTablePrefix().$transaction_table;

        $transaction_mapping_table        = $this->TransactionMappingModel->getTable();
        $prefix_transaction_mapping_table = DB::getTablePrefix().$transaction_mapping_table;

        $refund_table         = $this->RefundModel->getTable();
        $prefix_refund_table  = DB::getTablePrefix().$refund_table;

        $obj_user = DB::table($transaction_mapping_table)
                        ->select(DB::raw(
                                            $transaction_mapping_table.".amount,".
                                            $transaction_mapping_table.".order_no,".
                                            $transaction_mapping_table.".order_id,".
                                            $transaction_mapping_table.".transaction_status,".
                                            $transaction_mapping_table.".transaction_id,".
                                            $transaction_mapping_table.".created_at"
                                        ))
                        ->where($transaction_mapping_table.'.user_id','=',$loggedIn_userId)
                        ->orderBy($transaction_mapping_table.'.created_at','DESC');

        if(isset($form_data['filter']['general_search']) &&  $form_data['filter']['general_search']!="")
        {

          $search_term = $form_data['filter']['general_search'];

          $obj_user    = $obj_user->whereRaw(
                                "(  `".$transaction_mapping_table."`.`order_no` LIKE '%".$search_term."%' OR
                                    `".$transaction_mapping_table."`.`amount` LIKE '%".$search_term."%' OR
                                    `".$transaction_mapping_table."`.`transaction_id` LIKE '%".$search_term."%' 
                                  )"
                               );
                               
        }


        if(isset($form_data['filter']['transaction_date']) && $form_data['filter']['transaction_date']!="")
        {
            $search_term      = $form_data['filter']['transaction_date'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
        } 

        if(isset($form_data['filter']['transaction_status']) && $form_data['filter']['transaction_status'] != '')
        {
          $search_term = $form_data['filter']['transaction_status'];
            
          $obj_user     = $obj_user->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');
        }   
                      

        if(isset($form_data['filter']['payment_type']) && $form_data['filter']['payment_type']!="")
        {

            $search_term   = $form_data['filter']['payment_type'];

            if($search_term == "refund_payment")
            {
               $obj_user   = $obj_user->join($prefix_refund_table,$prefix_refund_table.'.order_no','=',$transaction_mapping_table.'.order_no');
            }
            
        }

        $type  = 'csv'; 
        $data = $arr_transaction = $arrayResponseData = [];

        // $arr_search_column  = $form_data;
        $arr_transaction         = $obj_user->get()->toArray();

        if(count($arr_transaction) <= 0)
        {
            $response['status']      = 'error';
            $response['message']      = 'No data available for export';
          
            return response()->json($response);
        }

        foreach($arr_transaction as $key => $value)
        { 
            $transaction_status = 'Pending';
            if($value->transaction_status == 1 || $value->transaction_status==null)
            {
              $transaction_status =  'Pending';
            }else if($value->transaction_status == 2)
            {
              $transaction_status = 'Paid';
            }else
            {
              $transaction_status = 'Failed';
            }

            /*Transaction Type*/
            $transaction_type = '';
            if($value->order_no != "")
            {
                  $refund_details = $this->RefundModel
                                          ->where('order_id',$value->order_id)
                                          ->where('order_no',$value->order_no)
                                          ->first();

                  if(isset($refund_details))
                  {
                      $transaction_mapping_obj = $this->TransactionMappingModel
                                                      ->where('order_id',$value->order_id)
                                                      ->where('order_no',$value->order_no)
                                                      ->first();

                      if(isset($transaction_mapping_obj))
                      {
                         $transaction_type = 'Cancel-Refund';
                      }                            
                     
                  }                                          


                  $transaction_mapping_obj = $this->TransactionMappingModel
                                                  ->where('order_id',$value->order_id)
                                                  ->where('order_no',$value->order_no)
                                                  ->first();

                  if(isset($transaction_mapping_obj))
                  {
                      $refund_details = $this->RefundModel
                                          ->where('order_id',$value->order_id)
                                          ->where('order_no',$value->order_no)
                                          ->first();

                      if(!isset($refund_details) && $refund_details==null)
                      {
                         $transaction_type = 'Order-Payment';
                      }                      
                      
                  }
            }

            $order_type ='';
            $retailer_order = $this->RetailerQuotesModel
                                   ->where('order_no',$value->order_no)
                                   ->where('id',$value->order_id)
                                   ->first();

            if(isset($retailer_order))
            {
               $order_type = 'Customer-Order';
            }


            $rep_sales_order = $this->RepresentativeLeadsModel
                                    ->where('order_no',$value->order_no)
                                    ->where('id',$value->order_id)
                                    ->first();

                                   

            if(isset($rep_sales_order))
            { 
               $order_type = 'Rep-Sales-Order';
            }


            $arrayResponseData['Order No']              = $value->order_no;
            $arrayResponseData['Order Type']            = $order_type;
            $arrayResponseData['Transaction Type']      = $transaction_type;     
            $arrayResponseData['Transaction Id']        = $value->transaction_id;     
            $arrayResponseData['Amount ($)']            = $value->amount;
            $arrayResponseData['Transaction Status']    = $transaction_status;
            $arrayResponseData['Transaction Date']       = $value->created_at;
            
            array_push($data,$arrayResponseData);
        }
        
        return Excel::create('Customer Payment Transaction', function($excel) use ($data) {
        
        $excel->sheet('Customer Payment Transaction', function($sheet) use ($data)
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

  }