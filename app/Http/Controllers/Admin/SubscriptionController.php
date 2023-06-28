<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmailSubscriptiponModel;

class SubscriptionController extends Controller
{
    public function __construct(EmailSubscriptiponModel $EmailSubscriptiponModel)
    {
        $this->arr_view_data           = [];	
        $this->EmailSubscriptiponModel = $EmailSubscriptiponModel;
        $this->BaseModel               = $EmailSubscriptiponModel;
 
        $this->module_title            = "Email Subscription";
        $this->module_view_folder      = "admin.subscription";
        $this->module_url_path         = url(config('app.project.admin_panel_slug')."/subscription");
    }


    public function index()
    {
        $arr_data = [];	 
        $obj_data = $this->BaseModel->orderBy('created_at','DESC')->get();

        if($obj_data != FALSE)
        {
            $arr_data = $obj_data->toArray();
        }

        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_plural($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


}
