<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use DB;
use Datatables;
use Sentinel;
use Flash;

class NotificationController extends Controller
{
    public function __construct(NotificationsModel $NotificationsModel,
                                UserModel $UserModel)
    {
    	$this->NotificationsModel = $NotificationsModel;   
        $this->UserModel          = $UserModel; 	
    	$this->arr_view_data      = [];        
        $this->module_title       = "Notifications";
        $this->module_view_folder = 'representative.notification';
        $this->representative_panel_slug   = config('app.project.representative_panel_slug');
        $this->module_url_path    = url($this->representative_panel_slug.'/notifications');
    }

    public function index()
    {	
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = $this->module_title;
    	$this->arr_view_data['page_title']      = $this->module_title;

    	$user = Sentinel::getUser();

    	if ($user->inRole('representative'))
        {	
        	$user_id = $user->id;
    		$update_read_status = $this->NotificationsModel->where('to_user_id',$user_id)
    													   ->update(['is_read'=>'1']);
    	}
        
    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_all_notification(Request $request)
    {
    	$user = Sentinel::getUser();

    	if ($user->inRole('representative'))
        {	
        	$user_id = $user->id;

			$notification_table           = $this->NotificationsModel->getTable();

	        $prefixed_notification_table  = DB::getTablePrefix().$this->NotificationsModel->getTable();
	       
	        $user_table           = $this->UserModel->getTable();
	        $prefixed_user_table  = DB::getTablePrefix().$this->UserModel->getTable();
	      
	        $obj_notification = DB::table($notification_table)
	                                ->select(DB::raw($prefixed_notification_table.".*,".
                                             $prefixed_user_table.'.first_name'))
	                                				 // "CONCAT(".$prefixed_user_table.".first_name,' ',"
			                                   //        .$prefixed_user_table.".last_name) as user_name"
	                                     //             ))
	                                ->leftjoin($prefixed_user_table,$prefixed_user_table.'.id' ,'=', $prefixed_notification_table.'.from_user_id')
	                                ->orderBy($prefixed_notification_table.'.created_at','DESC')
	                                ->where($prefixed_notification_table.'.to_user_id',$user_id);
	                                
	        /* ---------------- Filtering Logic ----------------------------------*/                    

	        $arr_search_column = $request->input('column_filter');
	       

	       if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
	        {
	            $search_term  = $arr_search_column['q_user_name'];
	            $obj_notification    = $obj_notification->having('user_name','LIKE', '%'.$search_term.'%');
	        }


	        $json_result     = Datatables::of($obj_notification);
	        $current_context = $this;
	   		
	   		 /* Modifying Columns */
	        $json_result =  $json_result->editColumn('user_name',function($data) use ($current_context)
	                      {
	                         return $user_name = isset($data->user_name) ? $data->user_name : 'NA';
	                         
	                      })

	                     ->editColumn('message',function($data) use($current_context)
	                      {
	                         return $message = isset($data->message) ? $data->message : 'NA';
	                      })


	                      ->editColumn('build_action_btn',function($data) use ($current_context)
	                        {   

                                $confirm_delete = 'onclick="confirm_delete(this,event);"';

	                            $delete_href =  $this->module_url_path.'/delete/'.base64_encode($data->id);

	                            $build_edit_action = '<a class="btn btn-outline btn-danger btn-circle show-tooltip" '.$confirm_delete.' href="'.$delete_href.'" title="Delete"><i class="ti-trash" ></i></a>';

	                            return $build_action = $build_edit_action;
	                        })
	                      ->make(true);

	                      $build_result = $json_result->getData();
	        
	        			  return response()->json($build_result);	
        }
    }

    public function delete_notification($enc_notification_id)
    {
    	$notification_id = isset($enc_notification_id)?base64_decode($enc_notification_id):false;

    	if($enc_notification_id)
    	{
    		$delete_notification = $this->NotificationsModel->where('id',$notification_id)->delete();
    		
    		if($delete_notification)
    		{
    			Flash::success(str_singular($this->module_title).' has been deleted.');
    		}
    		else
    		{
    			Flash::error('Error occurred while deleting '.str_singular($this->module_title));	
    		}
       		return redirect()->back();
    	}
    }
}
