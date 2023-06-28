<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Common\Traits\MultiActionTrait;

use App\Models\NotificationsModel;


use Validator;
use Flash;
Use Sentinel;
 
class NotificationsController extends Controller
{
    use MultiActionTrait;
    public $NotificationsModel; 
    public function __construct(NotificationsModel $notification)
    {      
        $this->NotificationsModel   = $notification;
        $this->BaseModel         = $this->NotificationsModel;              
        $this->module_title      = "Notification";
        $this->module_url_slug   = "notifications";
        $this->module_view_folder= "customer/notifications"; 
        $this->module_url_path   = url(config('app.project.customer_panel_slug')."/notifications");
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
                                    ->where('type','customer')
                                    ->where('to_user_id',$loggedInUserId)
                                    ->get()->toArray();
                
        //$update_read_status = $this->BaseModel->where('type','customer')->update(['is_read'=>'1']);
        
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
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