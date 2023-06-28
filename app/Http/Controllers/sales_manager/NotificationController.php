<?php

namespace App\Http\Controllers\sales_manager;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Common\Traits\MultiActionTrait;
use App\Models\NotificationsModel;

use App\Common\Services\ReportService;  


use Validator;
use Flash;
Use Sentinel;
 
class NotificationController extends Controller
{
    use MultiActionTrait;
    public $NotificationsModel; 
    public function __construct(NotificationsModel $notification, ReportService $ReportService)
    {      
        $this->NotificationsModel = $notification;
        $this->BaseModel         = $this->NotificationsModel;
        $this->ReportService     = $ReportService;             

        $this->module_title      = "Notifications";
        $this->module_url_slug   = "notifications";
        $this->module_view_folder= "sales_manager/notifications"; 
        $this->module_url_path   = url(config('app.project.sales_manager_panel_slug')."/notifications");
    }
     /*
    | Index  : Display listing of Notifications
    | auther : Shital More
    | Date   : 25/06/2016
    | @return \Illuminate\Http\Response
    */ 

    public function index()
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();
        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $arr_data = $this->BaseModel->orderBy('id','DESC')
                                    ->where('type','sales_manager')
                                    ->where('to_user_id',$loggedInUserId)
                                    ->get()->toArray();
                
       // $update_read_status = $this->BaseModel->where('type','sales_manager')->update(['is_read'=>'1']);
        
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_plural($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function export_notifications()
    {
       $loggedInUserId = 0;
       $user           = Sentinel::check();
       if($user)
       {
        $loggedInUserId = $user->id;
       }

        $notification_obj      = $this->BaseModel->orderBy('id','DESC')
                                    ->where('to_user_id',$loggedInUserId)
                                    ->get();

        if($notification_obj)
        {
            $notification_arr = $notification_obj->toArray();
          
        }
      
        $notification_data = [];
        $notification      = [];

        foreach ($notification_arr as $key => $value) {

           $notification_data['Date']  = notification_format_date($value['created_at']);
           $notification_data['Title'] = $value['title'];
           $notification_data['Description']  = $value['description'];

           array_push($notification,$notification_data);
        }

       $this->ReportService->notification_report($notification);

    }

       
    public function read_notification($notification_id)
    { 
        $notification_arr = [];
        $id = base64_decode($notification_id);

        $get_notifications_data = $this->NotificationsModel->where('id',$id)->first();

        if(isset($get_notifications_data))
        {
           $notification_arr = $get_notifications_data->toArray();
        }
        $data            = [];
        $data['is_read'] = '1';

        $result = $this->NotificationsModel->where('id',$id)->update($data);

        if($result)
        {
            $response['status']      = 'success';
            $response['description'] = 'Notification has been read.';
            $response['url']         =  $notification_arr['notification_url'];

            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong,please try again.';
            
            return response()->json($response); 
        }
    }

}