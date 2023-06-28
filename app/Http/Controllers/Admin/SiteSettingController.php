<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\SiteSettingModel;
/*use App\Events\ActivityLogEvent;
use App\Models\ActivityLogsModel;*/

use Validator;
use Flash;
use Input;
use Sentinel;
 
class SiteSettingController extends Controller
{
    
    public function __construct(
                                 SiteSettingModel $SiteSettingModel
                                 /*ActivityLogsModel $activity_logs*/
                               )
    {
        $this->SiteSettingModel   = $SiteSettingModel;
        $this->arr_view_data      = [];
        $this->BaseModel          = $this->SiteSettingModel;
        /*$this->ActivityLogsModel  = $activity_logs;*/

        $this->user_base_img_path   = base_path().config('app.project.img_path.user_profile_image');
        $this->user_public_img_path = url('/').config('app.project.img_path.user_profile_image');

        $this->module_title       = "Site Settings";
        $this->module_view_folder = "admin.site_settings";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/site_settings");
    }

    public function index()
    {
        $arr_data = array();   

        $obj_data =  $this->BaseModel->first();

        if($obj_data != FALSE)
        {
            $arr_data = $obj_data->toArray();    
        }
        
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    /*
    | update() : Update the Website Settings
    | auther : Sagar Jadhav
    | Date   : 26/04/2018
    | @param  int  $enc_id
    | @return \Illuminate\Http\Response
    */ 

    public function update(Request $request, $enc_id)
    {

        $form_data = $request->all();
        $id        = base64_decode($enc_id);

        /*Check Validations*/
        $arr_rules = [
                        'site_name'          =>'required',
                        'site_email_address' =>'required|email',
                        'site_contact_number'=>'required|numeric',
                        'site_address'       =>'required',
                        'fb_url'             =>'required',
                        'linkdin_url'        =>'required',
                        'twitter_url'        =>'required',
                        'youtube_url'        =>'required',
                        'instagram_url'      =>'required',
                        'whatsapp_url'      =>'required',
                        'lattitude'          =>'required',
                        'longitude'          =>'required',
                        'meta_desc'          =>'required',
                        'meta_keyword'       =>'required',
                        'website_url'        =>'required',
                        'site_short_name'    =>'required',
                        'site_short_description' =>'required',
                        'product_max_qty' => 'required',
                        'tinymce_api_key'=> 'required'
                     ];

         $validator = Validator::make($request->all(),$arr_rules); 

        if($validator->fails())
        { 
          Flash::error('Form validation failed, please check form fields.');  
        }  


        $logo_img_path = "";
        
        //upload site logo
        if ($request->hasFile('image')) 
        {
            $logo_img_path = '';
            
            $logo_image     = isset($form_data['image'])?$form_data['image']:null;

            $file_extension = strtolower(  $logo_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid profile image, please try again.';

                    return response().json($response);
                }

                 $logo_img_path =  $logo_image->store('site_logo');

        }
        else
        {
           $logo_img_path = $request->input('old_logo');
        }


        //upload login site logo

        if($request->hasFile('login_site_logo')) 
        {
            $login_logo_img_path = '';
            
            $login_logo_image     = isset($form_data['login_site_logo'])?$form_data['login_site_logo']:null;

            $file_extension = strtolower($login_logo_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid profile image, please try again.';

                    return response().json($response);
                }

                 $login_logo_img_path =  $login_logo_image->store('login_site_logo');

        }
        else
        {
           $login_logo_img_path = $request->input('old_login_site_logo');
        }


         //upload favicon

        if($request->hasFile('favicon')) 
        {
            $favicon_path = '';
            
            $favicon_image     = isset($form_data['favicon'])?$form_data['favicon']:null;

            $file_extension = strtolower($favicon_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg','ico']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid image, please try again.';

                    return response().json($response);
                }

                 $favicon_path =  $favicon_image->store('favicon');

        }
        else
        {
           $favicon_path = $request->input('old_favicon');
           //dd($request->all());
        }





        if($request->input('site_status')=='1')
        {
            $site_status = $request->input('site_status');
        }
        else
        {
            $site_status = '0';
        }

        $arr_data['site_name']              = $request->input('site_name');
        $arr_data['site_address']           = $request->input('site_address');
        $arr_data['site_contact_number']    = $request->input('site_contact_number');
        $arr_data['meta_desc']              = $request->input('meta_desc');
        $arr_data['meta_keyword']           = $request->input('meta_keyword');
        $arr_data['website_url']            = $request->input('website_url');
        $arr_data['site_email_address']     = $request->input('site_email_address');
        $arr_data['site_logo']              = $logo_img_path;
        $arr_data['login_site_logo']        = $login_logo_img_path;

        $arr_data['fb_url']                 = $request->input('fb_url');
        $arr_data['linkdin_url']            = $request->input('linkdin_url');
        $arr_data['twitter_url']            = $request->input('twitter_url');
        $arr_data['youtube_url']            = $request->input('youtube_url');
        $arr_data['rss_feed_url']           = $request->input('rss_feed_url');
        $arr_data['instagram_url']          = $request->input('instagram_url');
        $arr_data['whatsapp_url']           = $request->input('whatsapp_url');
        $arr_data['site_status']            = $site_status;
        $arr_data['lattitude']              = $request->input('lattitude');
        $arr_data['longitude']              = $request->input('longitude');
        $arr_data['site_short_name']        = $request->input('site_short_name');
        $arr_data['site_short_description'] = $request->input('site_short_description');
        $arr_data['product_max_qty']        = $request->input('product_max_qty');
        $arr_data['tinymce_api_key']        = $request->input('tinymce_api_key');
        $arr_data['favicon']                = $favicon_path;

        
        $updated = $this->SiteSettingModel->where('id',$id)->update($arr_data);
 
        if($updated)
        {
            Flash::success($this->module_title.' has been updated.'); 
        }
        else
        {
            Flash::error('Problem occurred, while updating '.$this->module_title);  
        } 
      
        return redirect()->back()->withInput();
    }

    public function update_site_status(Request $request)
    {   
        $arr_data = [];
        $enc_id   =   $request->input('id');
        $id       = base64_decode($enc_id);

        $arr_data['site_status'] = $request->input('status');
        
        $entity = $this->SiteSettingModel->where('id',$id)->update($arr_data);
        
        if($entity)
        {
            $response['status']       = 'success';
            $response['description']  = 'Site settings has been saved.';
            return response()->json($response);
        }
        else
        {
            $response['status']       = 'error';
            $response['description']  = 'Something went wrong, please try again.';
            return response()->json($response);
        } 
      
    }

    public function commission_settings()
    {
        $arr_data = array();   

        $obj_data =  $this->SiteSettingModel->first();

        if($obj_data != FALSE)
        {
            $arr_data = $obj_data->toArray();    
        }

        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = 'Commissions Settings';
        $this->arr_view_data['module_title']    = 'Commissions Settings';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.commission_settings',$this->arr_view_data);

    }

    public function update_commission_settings(Request $request)
    {
        $form_data = $request->all();

        /*Check Validations*/
        $arr_rules = [
                        'commission'                =>'required',                       
                        'representative_commission' =>'required',
                        'salesmanager_commission'   =>'required'
                     ];

         $validator = Validator::make($request->all(),$arr_rules); 

        if($validator->fails())
        { 
          Flash::error('Form validation failed,please check form fields.');  
        } 

        $commission                = $request->input('commission');
        $representative_commission = $request->input('representative_commission');
        $salesmanager_commission   = $request->input('salesmanager_commission');

        $arr_update_data = [];
        $arr_update_data['commission']               =  (float)$commission;
        $arr_update_data['representative_commission'] = (float)$representative_commission;
        $arr_update_data['salesmanager_commission']  = (float)$salesmanager_commission;
/*
         $arr_update_data['commission']               =  15;
        $arr_update_data['representative_commission'] = 2;
        $arr_update_data['salesmanager_commission']  = 1;*/
                                             ;
        $updated = $this->SiteSettingModel->where('id','1')
                                          ->update($arr_update_data);

        if($updated == 1 || $updated == true)
        {
            Flash::success('Commissions has been updated.'); 
        }
        else
        {
            Flash::error('Problem occurred, while updating Commissions');  
        } 
      
        return redirect()->back();

    }
}
