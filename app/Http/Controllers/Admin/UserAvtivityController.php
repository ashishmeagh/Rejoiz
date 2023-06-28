<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserActivityModel;
use App\Models\UserModel;


use Sentinel;
use Validator;
use Session;
use Flash;
use Datatables;
use DB;

class UserAvtivityController extends Controller
{
    
    public function __construct( UserActivityModel $user_activity_log,
   	                             UserModel         $user_model
                               )
    {

      $arr_view_data              = [];
      $this->UserActivityModel    = $user_activity_log;
      $this->BaseModel            = $this->UserActivityModel; 
      $this->UserModel            = $user_model;
      $this->module_url_path      = url(config('app.project.admin_panel_slug')."/user_activity_logs");
      $this->module_view_folder   = "admin.user_activity_log";
      $this->module_title         = "User Activity Logs";

    }


    public function index()
    {
        $this->arr_view_data['page_title']      = "Manage ".str_singular($this->module_title);
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_records(Request $request)
    {
        $obj_activity    = $this->get_activity_log($request);
       
        $current_context = $this;

        $json_result     = Datatables::of($obj_activity);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('user_name',function($data) use ($current_context)
                            {
                                if($data->user_name != null)
                                {
                                   return $data->user_name;
                                }
                                else
                                {
                                    return "NA";
                                }
                            })    
                            ->editColumn('order_id',function($data) use ($current_context)
                            {
                            	if($data->order_id !=null)
                            	{
                            	  return  $data->order_id;	
                            	}
                            	else
                            	{
                            		return "NA";
                            	}
                                
                            })

                            ->editColumn('shipping_address',function($data) use ($current_context)
                            {
                            	if($data->shipping_address !=null)
                            	{
                            	  return  $data->shipping_address;	
                            	}
                            	else
                            	{
                            		return "NA";
                            	}
                                
                            })
                            
                            ->editColumn('billing_address',function($data) use ($current_context)
                            {
                            	if($data->billing_address !=null)
                            	{
                            	  return  $data->billing_address;	
                            	}
                            	else
                            	{
                            		 return "N/A";
                            	}
                            	
                            })

                            ->editColumn('payment_mode',function($data) use ($current_context)
                            {
                            	if($data->payment_mode !=null)
                            	{
                            	 return $data->payment_mode;	
                            	}
                            	else
                            	{
                            		return "N/A";
                            	}
                            	
                            })

                            ->editColumn('payment_status',function($data) use($current_context)
                            {
                                if($data->payment_status !=null)
                                {
                                  return $data->payment_status;
                                }
                                else
                                {
                                	return "N/A";
                                }
                            })

                            ->editColumn('grand_total',function($data) use($current_context)
                            {
                                if($data->grand_total !=null)
                                {
                                  return $data->grand_total;	
                                }
                                else
                                {
                                   return "N/A";
                                }
                            	
                            })
                            
                            ->editColumn('order_place_date',function($data) use($current_context)
                            {
                                if($data->order_place_date !=null)
                                {
                                  return $data->order_place_date;
                                }
                                else
                                {
                                	return "N/A";
                                }
                            })
                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
        
    }

    function get_activity_log(Request $request)
    {
        $activity_log_table           = $this->BaseModel->getTable();
        $prefixed_activity_log_table  = DB::getTablePrefix().$this->BaseModel->getTable();

        $user_table                   = $this->UserModel->getTable();
        $prefixed_user_table          = DB::getTablePrefix().$this->UserModel->getTable();

        $obj_activity_log = DB::table($activity_log_table)
                                ->select(DB::raw($prefixed_activity_log_table.".id as id,".
                                                 $prefixed_activity_log_table.".user_id,".   
                                                 $prefixed_activity_log_table.".order_id,".
                                                 $prefixed_activity_log_table.".shipping_address,".
                                                 $prefixed_activity_log_table.".	billing_address,".
                                                 $prefixed_activity_log_table.".payment_mode,".

                                                 $prefixed_activity_log_table.".payment_status,".
                                                 $prefixed_activity_log_table.".grand_total,".
                                                 $prefixed_activity_log_table.".order_place_date,".

                                                 "CONCAT(".$prefixed_user_table.".first_name,' ',"
                                                          .$prefixed_user_table.".last_name) as user_name"
                                                 ))
                                ->whereNull($activity_log_table.'.deleted_at')
                                ->orderBy($activity_log_table.'.id','DESC')
                                ->leftJoin($user_table,$activity_log_table.'.user_id' ,'=', $user_table.'.id');      
                                                                                 
        /* ---------------- Filtering Logic ----------------------------------*/                    

        $arr_search_column = $request->input('column_filter');
        

        if(isset($arr_search_column['q_order_id']) && $arr_search_column['q_order_id']!="")
        {
           
            $search_term      = $arr_search_column['q_order_id'];
            $obj_activity_log = $obj_activity_log->where($activity_log_table.'.order_id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_shipping_addr']) && $arr_search_column['q_shipping_addr']!="")
        {
            $search_term      = $arr_search_column['q_shipping_addr'];
            $obj_activity_log = $obj_activity_log->where($activity_log_table.'.shipping_address','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_billing_addr']) && $arr_search_column['q_billing_addr']!="")
        {
            $search_term      = $arr_search_column['q_billing_addr'];
            $obj_activity_log = $obj_activity_log->where($activity_log_table.'.billing_address','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
        {
            $search_term       = $arr_search_column['q_user_name'];
            $obj_activity_log  = $obj_activity_log->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_mode']) && $arr_search_column['q_payment_mode']!="")
        {
            $search_term       = $arr_search_column['q_payment_mode'];
            $obj_activity_log  = $obj_activity_log->where($activity_log_table.'.payment_mode','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {
            $search_term       = $arr_search_column['q_payment_status'];
            $obj_activity_log  = $obj_activity_log->where($activity_log_table.'.payment_status','LIKE', '%'.$search_term.'%');
        }




        return $obj_activity_log;
    }

}
