<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ShopImagesModel;
use App\Models\ShopSettings;
use App\Models\ProductsModel;

use App\Jobs\SyncCompanySettingJob;


use App\Common\Services\ElasticSearchService;


use Sentinel;
use Validator;


class ShopController extends Controller
{
    /* 
	|  Author : SAgar B. Jadhav
	|  Date   : 27 June 2019
	*/
	public function __construct(ShopImagesModel $ShopImagesModel,
                                ShopSettings $ShopSettings,
                                ProductsModel $ProductsModel,
                                ElasticSearchService $ElasticSearchService)

    {   $this->ShopImagesModel    = $ShopImagesModel;
        $this->ProductsModel       = $ProductsModel;
        $this->ElasticSearchService = $ElasticSearchService;
        $this->ShopSettings       = $ShopSettings;
    	$this->arr_view_data      = [];
    	$this->module_title       = "My Shop";
    	$this->module_view_folder = 'maker.shop'; 
    	$this->maker_panel_slug   = config('app.project.maker_panel_slug');
    	$this->module_url_path    = url($this->maker_panel_slug.'/company_settings');
        $this->image_path         = base_path().'/storage/app/';
    }

    public function my_shop()
    {
    	// dd("inside my shop");
        $this->arr_view_data['module_title']    = $this->module_title;
    	$this->arr_view_data['page_title'] 	    = 'Company settings';
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
    	return view($this->module_view_folder.'.my_shop',$this->arr_view_data);
    }

     public function images()
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

         if($user)
        {
            $loggedIn_userId = $user->id;
        }
        

        $images_obj  = $this->ShopImagesModel->where('maker_id',$loggedIn_userId)->first();


    $this->arr_view_data['page_title']      = 'Vendor Company Images';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;

    $this->arr_view_data['profile_image'] =  $images_obj['store_profile_image'];
    $this->arr_view_data['cover_image'] =    $images_obj['store_cover_image'];
    //dd($this->arr_view_data);   
    return view($this->module_view_folder.'.my_shop_images',$this->arr_view_data);

    }

    public function save_images(Request $request)
    {
        $form_data = $request->all();


        $user = Sentinel::check();
        $loggedIn_userId = 0;
        $is_update = false;
        
        if($user)
        {
            $loggedIn_userId = $user->id;
        }
        
        $images_obj  = $this->ShopImagesModel->where('maker_id',$loggedIn_userId)->first();


        if(isset($images_obj))
        {
            $is_update = true;
        }

        
        if($request->hasFile('cover_image')) 
        {
             $cover_img_path = "";
            
             $cover_image = isset($form_data['cover_image'])?$form_data['cover_image']:null;
            
             $file_extension_cvr_img = strtolower( $cover_image->getClientOriginalExtension());

                if(!in_array($file_extension_cvr_img,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid profile image, please try again.';

                    return response().json($response);
                }

               
                $cover_img_path = $cover_image->store('store_image');
                if($request->old_cover_img!="")
                {
                    $unlink_old_cover_img = $this->image_path.$request->old_cover_img;
                    if(file_exists($unlink_old_cover_img))
                    {
                        @unlink($unlink_old_cover_img);
                    }
                }



        }
        else
        {
            $cover_img_path =    $images_obj['store_cover_image'];
         
        }

        if($request->hasFile('profile_image')) 
        {
             $prfile_img_path = "";
            
             $profile_image = isset($form_data['profile_image'])?$form_data['profile_image']:null;
            
             $file_extension_pro_img = strtolower( $profile_image->getClientOriginalExtension());

                if(!in_array($file_extension_pro_img,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid profile image,please try again.';

                    return response().json($response);
                }

               
                $profile_img_path = $profile_image->store('store_image');

                if($request->old_profile_img!="")
                {
                    $unlink_old_profile_img  = $this->image_path.$request->old_profile_img;
                    if(file_exists($unlink_old_profile_img))
                    {
                        @unlink($unlink_old_profile_img);
                    }
                }

        }
        else
        {
            $profile_img_path =    $images_obj['store_profile_image'];
         
        }

        $arr_data =  [];

        $arr_data['store_profile_image']  =  $profile_img_path;
        $arr_data['store_cover_image']    =  $cover_img_path;
        $arr_data['maker_id']             =  $loggedIn_userId;

        // dd($arr_data);
        
        if($is_update == true)
        {   

            $entity = $this->ShopImagesModel->where('maker_id',$loggedIn_userId)->update($arr_data);
            
            
            if($entity)
            {
               
            $response['status']     = "success";
            
            $response['url']        = $this->module_url_path;

            $response['description']= 'My shop images has been updated.'; 

            return response()->json($response);
            }
        
        }
        


        else
        {
            $entity = $this->ShopImagesModel->create($arr_data);

        }
        
        if($entity)
        {

            $response['status']      = "success";
            
            $response['url'] = $this->module_url_path;

            $response['description']= 'My shop images has been updated.'; 

            return response()->json($response);
        }
        else
        {
            $response['status']      = "failure";
            
            $response['url']         = $this->module_url_path;

            $response['description'] = 'Error occurred while updating my shop images.'; 

            return response()->json($response);
        }

    }

    public function shop_story()
    {
        $user            = Sentinel::check();
        $loggedIn_userId = 0;
        
        if($user)
        {
            $loggedIn_userId = $user->id;
        }
        
        $shop_obj  = $this->ShopSettings->where('maker_id',$loggedIn_userId)->first();

        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = $this->module_title;
        //$this->arr_view_data['page_title']      = 'Shop Story';
        $this->arr_view_data['page_title']      = 'Company Story';
        $this->arr_view_data['shop_data']       = $shop_obj; 

        return view($this->module_view_folder.'.shop_story',$this->arr_view_data);
    }

    public function save_shop_story(Request $request)
    {

        $form_data = $request->all();
      
        $user            = Sentinel::check();
        $loggedIn_userId = 0;
        $is_update       = false;
        
        if($user)
        {
            $loggedIn_userId = $user->id;
        }
                
        $arr_rules = [
                        'shop_story'=>'required'
                     ];
       
        $validator = Validator::make($request->all(),$arr_rules);


        if($validator->fails())
        {
            /*return redirect()->back()
                            ->withInput($request->all())
                            ->withErrors($validator);*/

            $response['status']      = "error";    
            $response['description'] = 'Form validations failed, please try again.'; 

            //$response['url'] = $this->module_url_path;

           return response()->json($response);                
        }
        
        $shop_obj  = $this->ShopSettings->where('maker_id',$loggedIn_userId)
                                        ->first();
     
        if(isset($shop_obj))
        {
            $is_update = true;
        }
       
        $shop_story       = isset($form_data['shop_story'])?$form_data['shop_story']:0;

        $arr_shop_data = [];

      
        $arr_shop_data['maker_id']               = $loggedIn_userId;
        $arr_shop_data['shop_story']             = $form_data['shop_story'];
        
        

        if($is_update == true)
        {
            $entity = $this->ShopSettings->where('maker_id',$loggedIn_userId)
                                         ->update($arr_shop_data);
        }
        else
        {
            $entity = $this->ShopSettings->create($arr_shop_data);
        }
        
        if($entity)
        {
            $response['status']      = "success"; 
            $response['description'] = 'Company story has been saved.'; 
        }
        else
        {
            $response['status']      = "failure";    
            $response['description'] = 'Error occurred while updating shop images.'; 
        }

        $response['url'] = $this->module_url_path;

        return response()->json($response);
    }


    public function shop_settings()
    {
        $user            = Sentinel::check();
        $loggedIn_userId = 0;
        
        if($user)
        {
            $loggedIn_userId = $user->id;
        }
        
        $shop_obj  = $this->ShopSettings->where('maker_id',$loggedIn_userId)->first();

        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Sales Terms';
        $this->arr_view_data['shop_data']       = $shop_obj; 

        return view($this->module_view_folder.'.shop_settings',$this->arr_view_data);
    }

    public function save_settings(Request $request)
    {

        $form_data       = $request->all(); 
       
        $user            = Sentinel::check();
        $loggedIn_userId = 0;
        $is_update       = false;
        
        if($user)
        {
            $loggedIn_userId = $user->id;
        }
                
        $arr_rules = [
                        'first_order_min'=>'required'
                        // 're_order_min'=>'required',
                        //'shop_lead_time'=>'required'
                     ];
       
        $validator = Validator::make($request->all(),$arr_rules);


        if($validator->fails())
        {
           /* return redirect()->back()
                            ->withInput($request->all())
                            ->withErrors($validator);*/

           $response['status']     = "failure";    
           $response['description']= 'Form validations failed, please try again.'; 

           $response['url'] = $this->module_url_path;

           return response()->json($response);
        }
        
        $shop_obj  = $this->ShopSettings->where('maker_id',$loggedIn_userId)
                                        ->first();
     
        if(isset($shop_obj))
        {
            $is_update = true;
        }
        
        $schedule_orders        = isset($form_data['schedule_orders'])?$form_data['schedule_orders']:0;

        $handling_and_packaging = isset($form_data['handling_and_packaging'])?$form_data['handling_and_packaging']:0;

        $sell_to_online_retaile = isset($form_data['sell_to_online_retaile'])?$form_data['sell_to_online_retaile']:0;

        

        $arr_shop_data = [];

        if(isset($form_data['vacation_mode_start']) && $form_data['vacation_mode_start']!= '')
        {
            $start_date = date("Y-m-d",strtotime($form_data['vacation_mode_start']));
            $arr_shop_data['vacation_mode_start']    = $start_date;
        }

        if(isset($form_data['vacation_mode_end']) && $form_data['vacation_mode_end'] != '')
        {            
            $end_date  = date("Y-m-d",strtotime($form_data['vacation_mode_end']));
            $arr_shop_data['vacation_mode_end']      = $end_date;
        }

       
        $arr_shop_data['maker_id']               = $loggedIn_userId;
        $arr_shop_data['first_order_minimum']    = $form_data['first_order_min'];
        $arr_shop_data['re_order_minimum']       = isset($form_data['re_order_min'])?$form_data['re_order_min']:0;
        $arr_shop_data['shop_lead_time']         = isset($form_data['shop_lead_time'])?$form_data['shop_lead_time']:0;
        $arr_shop_data['schedule_orders']        = isset($schedule_orders)?$schedule_orders:"";
        $arr_shop_data['handling_and_packaging'] = isset($handling_and_packaging)?$handling_and_packaging:"";
        $arr_shop_data['sell_to_online_retaile'] = isset($sell_to_online_retaile)?$sell_to_online_retaile:"";
        $arr_shop_data['split_order_free_shipping'] = isset($form_data['split_order_free_shipping'])?1:0;
        
       

        if($is_update == true)
        {
            $entity = $this->ShopSettings->where('maker_id',$loggedIn_userId)
                                         ->update($arr_shop_data);
            
            // $products_arr = $this->ProductsModel->where('user_id',$loggedIn_userId)->get()->toArray();


           /* $this->ElasticSearchService->update_lead_time($products_arr,$arr_shop_data['shop_lead_time'],$arr_shop_data['first_order_minimum']);*/

            
        }
        else
        {
            $entity = $this->ShopSettings->create($arr_shop_data);
        }
        
        if($entity)
        {
            $response['status']     = "success"; 
            $response['description']= 'My shop settings has been updated.'; 


           /* dispatch(new \App\Jobs\SyncCompanySettingJob($loggedIn_userId,
                                        $arr_shop_data['shop_lead_time'],
                                        $arr_shop_data['first_order_minimum'],
                                        $this->ElasticSearchService)); */
            $this->ElasticSearchService->delete_vendor_product($loggedIn_userId);

            $this->ElasticSearchService->index_vendor_product($loggedIn_userId);

        }
        else
        {
            $response['status']     = "failure";    
            $response['description']= 'Error occurred while updating my shop settings.'; 
        }

        $response['url'] = $this->module_url_path;

        return response()->json($response);
    }
}

