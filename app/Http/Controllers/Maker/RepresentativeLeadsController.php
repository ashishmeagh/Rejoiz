<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;    
use App\Models\UserModel;
use App\Models\RepresentativeMakersModel;
use App\Common\Services\GeneralService;


use Sentinel;
use Validator;
use DB;
use DateTime;


class RepresentativeLeadsController extends Controller
{
    
	public function __construct(RepresentativeLeadsModel $representative_leads,
                                RepresentativeProductLeadsModel $representative_product_leads,
                                UserModel $user,
                                GeneralService $GeneralService,
                                RepresentativeMakersModel $RepresentativeMakersModel)
    {   
    	$this->arr_view_data      = [];
        $this->module_title       = "Representative Orders";
    	$this->module_view_folder = 'maker.representative_leads'; 
    	$this->maker_panel_slug   = config('app.project.maker_panel_slug');
    	$this->module_url_path    = url($this->maker_panel_slug.'/leads_by_representative');

        $this->RepresentativeLeadsModel        = $representative_leads;
        $this->RepresentativeProductLeadsModel = $representative_product_leads;
        $this->UserModel                       = $user;
        $this->RepresentativeMakersModel       = $RepresentativeMakersModel;
        $this->GeneralService                  = $GeneralService;

        $this->retailer_id = 0;
       
    }

    public function index(Request $request)
    {

        $retailer_id = $request->input('retailer_id',null);

        $this->arr_view_data['retailer_id']     = $retailer_id;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_leads(Request $request)
    {
        
        $loggedInUserId = 0;
        $user = Sentinel::check();
        
        $retailer_id = $request->input('retailer_id');
        if($user)
        {
            $loggedInUserId = $user->id;
        }

      $representative_leads_tbl_name     = $this->RepresentativeLeadsModel->getTable();        
      $prefixed_representative_leads_tbl = DB::getTablePrefix().$this->RepresentativeLeadsModel->getTable();

      $representative_leads_pro_tbl_name     = $this->RepresentativeProductLeadsModel->getTable();        
      $prefixed_representative_leads_pro_tbl = DB::getTablePrefix().$this->RepresentativeProductLeadsModel->getTable();

      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;


      $obj_products = DB::table($representative_leads_tbl_name)
                                ->select(DB::raw($prefixed_representative_leads_tbl.".id,".  
                                                 $prefixed_representative_leads_tbl.'.description,'.
                                                 $prefixed_representative_leads_tbl.'.total_retail_price,'.
                                                 $prefixed_representative_leads_tbl.'.total_wholesale_price,'.
                                                 $prefixed_representative_leads_tbl.".created_at,".
                                                 $prefixed_representative_leads_tbl.".representative_id,".

                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                )
                                               )                                
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_leads_tbl.'.representative_id')
                                ->where($prefixed_representative_leads_tbl.'.is_confirm',1)
                                ->where($prefixed_representative_leads_tbl.'.order_cancel_status','!=',2)
                                ->where($prefixed_representative_leads_tbl.'.maker_id',$loggedInUserId)
                                ->orderBy($prefixed_representative_leads_tbl.".id",'DESC');
                                
         
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['retailer_id']) && $arr_search_column['retailer_id']!="" && $arr_search_column['retailer_id']!='0')
        {
            $retailer_id = base64_decode($arr_search_column['retailer_id']);
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.retailer_id',$retailer_id);
        }    

        if(isset($arr_search_column['q_lead_id']) && $arr_search_column['q_lead_id']!="")
        {
            $search_term      = $arr_search_column['q_lead_id'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term = $arr_search_column['q_username'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_tot_retail']) && $arr_search_column['q_tot_retail']!="")
        {
            $search_term  = $arr_search_column['q_tot_retail'];
            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }        

        if(isset($arr_search_column['q_tot_wholesale']) && $arr_search_column['q_tot_wholesale']!="")
        {
            $search_term      = $arr_search_column['q_tot_wholesale'];
            
            $obj_products     = $obj_products->where($prefixed_representative_leads_tbl.'.total_wholesale_price',$search_term);
        }
  
        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term      = $arr_search_column['q_enquiry_date'];

            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));

            $obj_products = $obj_products->where($prefixed_representative_leads_tbl.'.created_at','LIKE', '%'.$date.'%');
        }   
        
        $current_context = $this;

        $json_result  = \Datatables::of($obj_products);
            
        /* Modifying Columns */
        $json_result =  $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            //return $formated_date = format_date($data->created_at);
                            return us_date_format($data->created_at);
                        })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $products_arr = [];
                            $products_arr = get_lead_products($data->id);

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
                        ->editColumn('build_action_btn',function($data) 
                        {   
                            //get unread messages count
                            $unread_message_count = get_lead_unread_messages_count($data->id,'maker');
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }
                            
                            //check if user is online or not
                            $is_online = check_is_user_online($data->representative_id);

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

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-outline btn-success btn-circle show-tooltip btn-retailer-view" href="'.$view_href.'">View</a>';

                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.'</a>';                                      

                            //return $build_action = $build_view_action.' '.$build_chat_action;
                            return $build_action = $build_view_action;
                        })
                        ->make(true);

        $build_result = $json_result->getData();
         
        return response()->json($build_result);
    }


    public function view($enc_id)
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $leads_id = base64_decode($enc_id);

        $leads_arr = [];
        $obj_data = $this->RepresentativeLeadsModel
                         ->with(['leads_details.product_details','retailer_user_details'=>function($q1)
                         {
                            $q1->select('id','email','first_name','last_name');
                         },'representative_user_details'=>function($q2)
                         {
                            $q2->select('id','email','first_name','last_name');
                         }])
                         ->where('id',$leads_id)
                         ->first();
        if($obj_data)
        {
            $leads_arr = $obj_data->toArray();
        }                                           
        // dd($leads_arr);
        $this->arr_view_data['leads_arr']     = $leads_arr;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
      
        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

    public function manage_representative()
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_url_path'] =  url($this->maker_panel_slug.'/manage_representative');
        return view($this->module_view_folder.'.manage_representative',$this->arr_view_data);
    }

    public function load_representative(Request $request)
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }   

        $arr_search_column = $request->input('column_filter');

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $rep_maker_table =  $this->RepresentativeMakersModel->getTable();
        $prefix_rep_maker_table = DB::getTablePrefix().$rep_maker_table;

      
        $obj_user = DB::table($rep_maker_table)
                        ->select(DB::raw($rep_maker_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".tax_id, ".
                                     $prefix_rep_maker_table.".commission, ".
                                     $prefix_rep_maker_table.".is_lock, ".
                                     $prefix_rep_maker_table.".is_confirm, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                        ->leftJoin($user_table,$prefix_user_table.'.id','=',$rep_maker_table.'.representative_id')
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->where($user_table.'.status','=',1)
                        ->where($rep_maker_table.'.maker_id','=',$loggedIn_userId)
                        ->orderBy($rep_maker_table.'.created_at','DESC');                      

                        /*->get();*/   
       /* ---------------- Filtering Logic ----------------------------------*/  
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_commission']) && $arr_search_column['q_commission']!="")
        {
            $search_term      = $arr_search_column['q_commission'];
            $obj_user = $obj_user->where($maker_table.'.commission','LIKE', '%'.$search_term.'%');
        }
        
        $json_result     = \Datatables::of($obj_user);
        $json_result     = 
        $json_result->editColumn('commission_status',function($data)
                            {
                                $is_lock    = $data->is_lock;
                                $is_confirm = $data->is_confirm;
                                $commission = "-";

                                if($is_lock==1 && $is_confirm==1)
                                {
                                    $commission  = "Confirm By Admin";
                                }
                                elseif($is_lock==0 && $is_confirm==1)
                                {
                                    $commission  = "Request To Admin";   
                                }
                                else
                                {
                                    $commission  = "Request Pending By Admin";      
                                }

                                return $commission;
                            })
                            ->editColumn('build_action_btn',function($data) 
                            {   

                                $module_url_path = url($this->maker_panel_slug.'/manage_representative');
                                $view_href   = $module_url_path.'/commission/'.base64_encode($data->id);
                                
                                $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="Manage Representative Commission" class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'">Manage Commission</a>';

                                return $build_action = $build_view_action;
                            })                
                            ->editColumn('commission',function($data) 
                            {   
                                $commission = $data->commission;
                                if($commission==null || $commission=="")
                                {
                                    $commission = "-";   
                                }    
                                return $commission;
                            })     
                            ->editColumn('user_name',function($data) 
                            {   
                                $user_name = ucfirst($data->user_name);
                                
                                return $user_name;
                            })                      
                            ->make(true);

        $build_result = $json_result->getData();
      
        return response()->json($build_result);
    }

    public function commission($enc_id=null)
    {
        $id = base64_decode($enc_id);

        $arr_data = [];
        $obj_data = $this->RepresentativeMakersModel->where('id',$id)
                         ->with(['representative_details'=>function($query)
                        {
                            $query->select('id','first_name','last_name');
                        }])->first();
        if($obj_data)
        {
            $arr_data = $obj_data->toArray();
        }

        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['module_title']    = 'Manage Representative Commission';
        $this->arr_view_data['page_title']      = 'Representative Commission';
        $this->arr_view_data['module_url_path'] =  url($this->maker_panel_slug.'/manage_representative');
        return view($this->module_view_folder.'.create_commission',$this->arr_view_data);
    }

    public function set_commission(Request $request)
    {
        $id         = $request->input('tran_id',null);
        $commission = $request->input('commission',null);
        $maker_id   = $request->input('maker_id',null);
        $rep_id     = $request->input('rep_id',null);

        if($id)
        {
            $update_comm = $this->RepresentativeMakersModel->where('id',$id)->update(['commission'=>$commission,'is_confirm'=>'1']);
            if($update_comm)
            {
                /******************Notification to maker START*******************************/
                  $loggedInUserId = 0;
                  $user = Sentinel::check();

                  if($user)
                  {
                      $loggedInUserId = $user->id;
                  }

                  $first_name = isset($user->first_name)?$user->first_name:"";
                  $last_name  = isset($user->last_name)?$user->last_name:"";  

                  $arr_event                 = [];
                  $arr_event['from_user_id'] = $loggedInUserId;
                  $arr_event['to_user_id']   = 1;
                  $arr_event['description']  = 'Commission confirm request sent by a '.$first_name.' '.$last_name.' .';
                  $arr_event['title']        = 'Commission request sent by a maker';
                  $arr_event['type']         = 'admin';   
                  
                  $this->GeneralService->save_notification($arr_event);
               /**********************Notification to admin END*********************************/

                $response['status']  = 'SUCCESS';
                $response['message'] = 'Commission has been set.';
            }
            else
            {
                $response['status']  = 'ERROR';
                $response['message'] = 'Something went wrong, please try again.';
            }

        }
        else
        {
            $response['status']  = 'ERROR';
            $response['message'] = 'Something went wrong, please try again.';
        }  

        return response()->json($response);   
    }
}

