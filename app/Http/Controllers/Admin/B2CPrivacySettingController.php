<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\B2CPrivacySettingsModel;
/*use App\Events\ActivityLogEvent;
use App\Models\ActivityLogsModel;*/

use Validator;
use Flash;
use Input;
use Sentinel;
 
class B2CPrivacySettingController extends Controller
{
    
    public function __construct(
                                 B2CPrivacySettingsModel $B2CPrivacySettingsModel
                                 /*ActivityLogsModel $activity_logs*/
                               )
    {
        $this->B2CPrivacySettingsModel   = $B2CPrivacySettingsModel;
        $this->arr_view_data      = [];
        $this->BaseModel          = $this->B2CPrivacySettingsModel;
        /*$this->ActivityLogsModel  = $activity_logs;*/

        $this->user_base_img_path   = base_path().config('app.project.img_path.user_profile_image');
        $this->user_public_img_path = url('/').config('app.project.img_path.user_profile_image');

        $this->module_title       = "B2C Privacy Settings";
        $this->module_view_folder = "admin.b2c_privacy_settings";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/b2c_privacy_settings");
    }

    public function index()
    {
        $arr_data = array();   

        $obj_data =  $this->BaseModel->first();

        if($obj_data != FALSE)
        {
            $arr_data = $obj_data->toArray();    
        }
        // dd($arr_data);
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    /*
    | update() : Update the B2C visibililty Settings
    | auther : Akshay Nair
    | Date   : 07/05/2020
    */ 

    

    public function update_is_influencer_module_on(Request $request)
    {   
        $arr_data = [];
        $enc_id   =   $request->input('id');
        $id       = base64_decode($enc_id);

        $arr_data['is_influencer_module_on'] = $request->input('status');
        
        $entity = $this->B2CPrivacySettingsModel->where('id',$id)->update($arr_data);
        
        if($entity)
        {
            $response['status']       = 'success';
            $response['description']  = 'Influencer visibility has been updated.';
            return response()->json($response);
        }
        else
        {
            $response['status']       = 'error';
            $response['description']  = 'Something went wrong,please try again.';
            return response()->json($response);
        } 
      
    }
    public function update_is_b2c_module_on(Request $request)
    {   
        $arr_data = [];
        $enc_id   =   $request->input('id');
        $id       = base64_decode($enc_id);

        $arr_data['is_b2c_module_on'] = $request->input('status');
        
        $entity = $this->B2CPrivacySettingsModel->where('id',$id)->update($arr_data);
        
        if($entity)
        {
            $response['status']       = 'success';
            $response['description']  = 'B2C visibility has been updated.';
            return response()->json($response);
        }
        else
        {
            $response['status']       = 'error';
            $response['description']  = 'Something went wrong, please try again.';
            return response()->json($response);
        } 
      
    }

    
}
