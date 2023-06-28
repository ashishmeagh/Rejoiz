<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Models\RetailerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Common\Services\HelperService;
use App\Models\RefundModel;
use DB;
use Sentinel;
use DateTime;
use Excel;


class TransactionTableController extends Controller
{
  

  public function __construct(UserModel $UserModel,
    						              TransactionsModel $TransactionsModel,
                              RetailerQuotesModel $RetailerQuotesModel,
                              RepresentativeLeadsModel $RepresentativeLeadsModel,
    						              TransactionMappingModel $TransactionMappingModel,
                              RefundModel $RefundModel,
                              HelperService $HelperService
                             )

    {
      $this->UserModel    	           = $UserModel;
      $this->TransactionsModel         = $TransactionsModel;
      $this->RetailerQuotesModel       = $RetailerQuotesModel;
      $this->RepresentativeLeadsModel  = $RepresentativeLeadsModel;
      $this->TransactionMappingModel   = $TransactionMappingModel;
      $this->RefundModel               = $RefundModel;
      $this->HelperService             = $HelperService;
      $this->retailer_panel_slug       = config('app.project.retailer_panel_slug');
      $this->module_url_path           = url($this->retailer_panel_slug.'/transactions/get_transaction_details');
      $this->module_url_base_path      = url($this->retailer_panel_slug.'/transactions');
      $this->module_title              = "Payment Transactions";
      $this->module_view_folder        = 'retailer.transaction_tables';

    }

    public function show_transaction_details(Request $request)
    {  
		    $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = $this->module_title;
		    $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_url_base_path'] = $this->module_url_base_path;
	
       	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_transaction_details(Request $request)
    {
    	  $user = Sentinel::check();
     		
    		$loggedIn_userId = 0;
     
     		if($user)
     		{
    		    $loggedIn_userId = $user->id;
     		}   

    	  $arr_search_column = $request->input('column_filter');

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
                        //->orderBy($transaction_mapping_table.'.created_at','ASC');
                        ->orderBy($transaction_mapping_table.'.id','DESC');


      





         /* ---------------- Filtering Logic ----------------------------------*/  
 
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term  = $arr_search_column['q_amount'];
            $obj_user     = $obj_user->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');

            
        }	

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term  = $arr_search_column['q_order_no'];
            $obj_user     = $obj_user->where($transaction_mapping_table.'.order_no','LIKE', '%'.$search_term.'%');

           
        }	

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term  = $arr_search_column['q_transaction_status'];

            $obj_user     = $obj_user->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');

            
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term  = $arr_search_column['q_transaction_id'];

            $obj_user     = $obj_user->where($transaction_mapping_table.'.transaction_id','LIKE', '%'.$search_term.'%');

           
        }
        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
      
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');

            
        }   


        if(isset($arr_search_column['q_payment_type']) && $arr_search_column['q_payment_type']!="")
        {

            $search_term   = $arr_search_column['q_payment_type'];

 
         /*   if($search_term == "refund_payment")
            {
               $obj_user   = $obj_user->join($prefix_refund_table,$prefix_refund_table.'.order_no','=',$transaction_mapping_table.'.order_no');
            }
*/

            if($search_term == "refund_payment")
            {
                
                                                              
                $obj_user   = $obj_user->where('user_id',$loggedIn_userId)
                                       ->where($transaction_mapping_table.'.transaction_id','LIKE','%txn_%');

            }else{
              
              $obj_user   = $obj_user->where('user_id',$loggedIn_userId)
                                      ->where($transaction_mapping_table.'.transaction_id','LIKE','%ch_%');

             

                                       
                        
            }

            
        }

       // dd($obj_refund_transaction->get()->toArray(),$obj_user->get()->toArray());
       // $obj_user = $obj_user->union($obj_refund_transaction);


        //Calculate total by Harshada on date 09 Sep 2020
        $total_amt = 0;      
        $total_amt =array_reduce($obj_user->get()->toArray(), function(&$res, $item) {
            return $res + $item->amount;
        }, 0);

    	  $current_context = $this;
    	  $json_result     = \Datatables::of($obj_user);

       
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return $formated_date = us_date_format($data->created_at);
                        })

                        ->editColumn('order_link',function($data) use ($current_context)
                        {
                            if ($data->order_no != "") {

                                $arr_retailer_order_details = $this->RetailerQuotesModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();
                                if($arr_retailer_order_details) 
                                {
                                    $refund_details = $this->RefundModel->where('order_id',$data->order_id)
                                                                        ->where('order_no',$data->order_no)
                                                                        ->first();
                                    if(isset($refund_details))
                                    {
                                      return '/retailer/my_cancel_orders/view/'.base64_encode($data->order_id);
                                    }
                                   
                                    $transaction_mapping_table = $this->TransactionMappingModel
                                                                      ->where('order_id',$data->order_id)
                                                                      ->where('order_no',$data->order_no)
                                                                      ->first();

                                    if(isset($transaction_mapping_table))
                                    {
                                      return '/retailer/my_orders/view/'.base64_encode($data->order_id);
                                    }                                  

                                    
                                                         
                                } 
                                $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                             ->where('order_no',$data->order_no)
                                                             ->where('id',$data->order_id)
                                                             ->first();

                                if ($arr_rep_order_details) 
                                {
                                    $refund_details_obj = $this->RefundModel->where('order_id',$data->order_id)
                                                                            ->where('order_no',$data->order_no)
                                                                            ->first();

                                    if(isset($refund_details_obj))
                                    {
                                      return '/retailer/rep_sales_cancel_orders/view/'.base64_encode($data->order_id);
                                    }     


                                    $transaction_mapping_obj = $this->TransactionMappingModel
                                                                    ->where('order_id',$data->order_id)
                                                                    ->where('order_no',$data->order_no)
                                                                    ->first();

                                    if(isset($transaction_mapping_obj))
                                    {
                                       return '/retailer/my_orders/order_summary/'.base64_encode($arr_rep_order_details['order_no']).'/'.base64_encode($arr_rep_order_details['maker_id']);
                                    }                                                                 
                                                          
                                } 
                            }
                        })

                        ->editColumn('order_type',function($data) use ($current_context)
                        {
                            $order_type ='';

                            $retailer_order = $this->RetailerQuotesModel
                                                   ->where('order_no',$data->order_no)
                                                   ->where('id',$data->order_id)
                                                   ->first();

                                                 

                            if(isset($retailer_order))
                            {
                               $order_type = 'Retailer-Order';
                            }


                            $rep_sales_order = $this->RepresentativeLeadsModel
                                                    ->where('order_no',$data->order_no)
                                                    ->where('id',$data->order_id)
                                                    ->first();

                                                   

                            if(isset($rep_sales_order))
                            { 
                               $order_type = 'Rep-Sales-Order';
                            }

                            return $order_type;
                        })

                       ->editColumn('transaction_type',function($data) use ($current_context)
                        {
                            $type = '';

                            if($data->order_no != "")
                            {
                                  $refund_details = $this->RefundModel
                                                          ->where('order_id',$data->order_id)
                                                          ->where('order_no',$data->order_no)
                                                          ->where('balance_transaction',$data->transaction_id)
                                                          ->first();

                                  if(isset($refund_details))
                                  {
                                                                 
                                      $type = 'Cancel-Refund';
                                  }  




           
                                  $transaction_mapping_obj = $this->TransactionMappingModel
                                                                  ->where('order_id',$data->order_id)
                                                                  ->where('order_no',$data->order_no)
                                                                  ->where('transaction_id',$data->transaction_id)

                                                                  ->first();

                                  if(isset($transaction_mapping_obj))
                                  {
                                     
                                     $type = 'Order-Payment';
                                      
                                  }
                            }

                            return $type; 

                        }) 

                         ->editColumn('company_name',function($data) use ($current_context)
                        {

                             $company_name = $this->HelperService->get_order_company_name($data->order_no,$data->order_id,"Retailer-Order");
                            return $company_name;
                        })

     					          ->editColumn('amount',function($data) use ($current_context)
                        {
                            return $amount = '<i class="fa fa-usd" aria-hidden="true"></i>'.num_format($data->amount);
                        })->make(true);
                       

        $build_result = $json_result->getData();
         $build_result->total_amt = $total_amt;
        return response()->json($build_result);
        
    }


    public function get_export_transasction_orders(Request $request)
    {
      $user = Sentinel::check();
        
        $loggedIn_userId = 0;
     
        if($user)
        {
            $loggedIn_userId = $user->id;
        }   

        // $arr_search_column = $request->input('column_filter');
        $arr_search_column = $request->all();

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

         /* ---------------- Filtering Logic ----------------------------------*/  
 
        if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
        {
            $search_term  = $arr_search_column['q_amount'];
            $obj_user     = $obj_user->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term  = $arr_search_column['q_order_no'];
            $obj_user     = $obj_user->where($transaction_mapping_table.'.order_no','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
        {
            $search_term  = $arr_search_column['q_transaction_status'];
            $obj_user     = $obj_user->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term  = $arr_search_column['q_transaction_id'];

            $obj_user     = $obj_user->where($transaction_mapping_table.'.transaction_id','LIKE', '%'.$search_term.'%');
        }
        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {


            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term    = date('Y-m-d',strtotime($search_term));
            $obj_user = $obj_user->where($transaction_mapping_table.'.created_at','LIKE', '%'.$date.'%');
        }   


        if(isset($arr_search_column['q_payment_type']) && $arr_search_column['q_payment_type']!="")
        {

            $search_term   = $arr_search_column['q_payment_type'];

            if($search_term == "refund_payment")
            {
               $obj_user   = $obj_user->join($prefix_refund_table,$prefix_refund_table.'.order_no','=',$transaction_mapping_table.'.order_no');
            }
            
        }

        $type  = 'csv'; 
        $data = $arr_transaction = $arrayResponseData = [];

        $arr_search_column  = $request->all();
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
               $order_type = 'Retailer-Order';
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
