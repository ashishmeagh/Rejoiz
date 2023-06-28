<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\MakerModel;

use Sentinel;
use DB;
use Datatables;
use Flash;

class MyQuotesController extends Controller
{
    /* 
	|  Author : Shital Vijay More
	|  Date   : 1 July 2019
	*/
	public function __construct(RetailerQuotesModel $retailer_quote,UserModel $user_model,
                                ProductsModel $product_model,RetailerQuotesProductModel $retailer_quotes,
                                MakerModel $MakerModel)
    {
    	$this->arr_view_data         = [];
    	$this->module_title          = "My Orders";
    	$this->module_view_folder    = 'retailer.my_quote'; 
    	$this->retailer_panel_slug   = config('app.project.retailer_panel_slug');
    	$this->module_url_path       = url($this->retailer_panel_slug.'/my_quote');
        $this->RetailerQuotesModel   = $retailer_quote;
        $this->UserModel             = $user_model;
        $this->ProductsModel         = $product_model;
        $this->MakerModel            = $MakerModel;
        $this->RetailerQuotesProductModel = $retailer_quotes;
    }

    public function index()
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'My orders';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_my_quote(Request $request)
    { 
   
       $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
        
        $retailer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
        $prefixed_retailer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $user_tbl_name                = $this->UserModel->getTable();
        $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl          = $this->MakerModel->getTable();        
        $prefixed_maker_tbl = DB::getTablePrefix().$this->MakerModel->getTable();


        $obj_qutoes = DB::table($retailer_quotes_tbl_name)
                        ->select(DB::raw($prefixed_retailer_quotes_tbl.".*,".
                                         $prefixed_maker_tbl.".brand_name,".
                                          "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                                  .$prefixed_user_tbl.".last_name) as user_name"))
                        ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_retailer_quotes_tbl.'.maker_id')
                        ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_retailer_quotes_tbl.'.maker_id')
                        ->where($prefixed_retailer_quotes_tbl.'.retailer_id',$loggedInUserId)
                        ->orderBy($prefixed_retailer_quotes_tbl.".id",'DESC');
        /*dd($obj_qutoes->get()->toArray());                */

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
              
        if(isset($arr_search_column['q_enquiry_id']) && $arr_search_column['q_enquiry_id']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_id'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_brand_name']) && $arr_search_column['q_brand_name']!="")
        {
            $search_term      = $arr_search_column['q_brand_name'];
            $obj_qutoes = $obj_qutoes->where($prefixed_maker_tbl.'.brand_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retailer_name']) && $arr_search_column['q_retailer_name']!="")
        {
            $search_term      = $arr_search_column['q_retailer_name'];
            $obj_qutoes = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term      = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];
            
            $search_term  = date('Y-m-d',strtotime($search_term));

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.created_at','LIKE', '%'.$search_term.'%');
        }    

        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);

            $obj_qutoes = $obj_qutoes->where($prefixed_retailer_quotes_tbl.'.retailer_id',$retailer_id);
        } 


        $current_context = $this;

        $json_result  = Datatables::of($obj_qutoes);
        
        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            return date('d-M-Y',strtotime($data->created_at));

                        })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $products_arr = [];
                            $products_arr = get_quote_products($data->id);

                            if(isset($products_arr) && count($products_arr)>0)
                            {
                                $products = '';

                                foreach ($products_arr as $key => $product) 
                                {
                                    $products .= '<tr>
                                                    <td>'.$product['product_details']['product_name'].'</td>
                                                    <td>'.$product['qty'].'</td>
                                                  </tr>';
                                }
                            }
                            else
                            {
                                $products = 'No Record Found';
                            }

                            return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_product_list($(this))">View Products<span> '.count($products_arr).'</span></a>
            
                                <td colspan="5">
                                    <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                                        <thead>
                                          <tr>
                                            <th>Product Title</th>
                                            <th>Quantity</th>                                
                                          </tr>
                                        </thead>
                                        <tbody>'.$products.'</tbody>
                                      </table>
                                </td>';

                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {
                            //get unread messages count
                            $unread_message_count = get_quote_unread_messages_count($data->id,'retailer');
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

                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);
                            $chat_href   = $this->module_url_path.'/conversation/'.base64_encode($data->id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-danger btn-outline show-tooltip" href="'.$view_href.'">View</a>';

                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.'</a>';                                      

                            return $build_action = $build_view_action.' '.$build_chat_action;

                        });

        $build_result = $json_result->make(true)->getData();
        /*dd($build_result);*/
        return response()->json($build_result);
        
                                
    }

    public function view(Request $request, $enquiry_id = 0)
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $enquiry_id  = base64_decode($enquiry_id);
        $enquiry_arr = [];

        
        $enquiry_obj = $this->RetailerQuotesModel->with(['quotes_details.product_details','maker_details','maker_data'])
                                                 ->where('id',$enquiry_id)->first();          
       
        if($enquiry_obj)
        {
            $enquiry_arr = $enquiry_obj->toArray();            
        }    
        //dd($enquiry_arr);
        $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = $this->module_title.' Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


}

