<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BannerImageModel;
use App\Common\Services\GeneralService;


use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;


class BannerImageController extends Controller
{
	  public function __construct( BannerImageModel $BannerImageModel, GeneralService $GeneralService)
    {
      $this->arr_view_data                   =  [];
      $this->BannerImageModel                =  $BannerImageModel;  
      $this->GeneralService                  =  $GeneralService;     
      $this->module_view_folder              =  "admin.banner_images";
      $this->module_title                    =  "Banner Images";
      $this->module_url_path                 =  url(config('app.project.admin_panel_slug').'/banner_images');
      $this->curr_panel_slug                 =  config('app.project.admin_panel_slug');
      $this->banner_image                    =  base_path().'/storage/app/';
     
    }
 
     public function index()
    { 
    	
    	$banner_arr = $this->BannerImageModel->get()->toArray();

        $this->arr_view_data['page_title']      = 'Manage '.str_singular( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']  = $this->curr_panel_slug;  
        $this->arr_view_data['banner_arr']  = $banner_arr;  

        return view($this->module_view_folder.'.banner_images',$this->arr_view_data);
    }

    public function create()
    {
        $this->arr_view_data['page_title']           = 'Create '.str_singular($this->module_title);
        $this->arr_view_data['module_title']         = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']      = $this->curr_panel_slug;  

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }


   public function edit($enc_id)
    {
        $id       = base64_decode($enc_id);

     	  $obj_data = $this->BannerImageModel->where('id', $id)
        							->first();
        $arr_data = [];

        if($obj_data)
        {
           $arr_data = $obj_data->toArray();
        }
  
        $this->arr_view_data['edit_mode']       = TRUE;
        $this->arr_view_data['enc_id']          = $enc_id;
       
        $this->arr_view_data['arr_data']        = $arr_data; 
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;  
        
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function save(Request $request)
    {
        $is_update = false;
        $banner_url = '';
        $arr_rules1 = [];
        $arr_rules2 = [];

        $form_data = $request->all();
       // dd($form_data);

        $json_data = json_encode($form_data);

        $enc_id = base64_decode($request->input('enc_id',false));
        $enc_id = $enc_id == "" ? false : $enc_id;

        if($request->has('enc_id'))
        {
            $is_update = true;
        }

        if($is_update == false)
        {
           $arr_rules2 = [
                            'banner_image' => 'required'
                          ];
           $arr_rules1 = ['type' => 'required'];
        }   
        
        $validation_arr = array_merge($arr_rules1,$arr_rules2); 

        $validator = Validator::make($request->all(),$validation_arr); 

        if($validator->fails())
        {        
           $response['status']      = 'warning';
           $response['description'] = 'Form validation failed, please check all fields.';

          return response()->json($response);
        }  

         /*image uploading*/
        $banner_img_file_path = '';
        $banner_img_resp_file_path = '';
            
        $banner_image = isset($form_data['banner_image'])?$form_data['banner_image']:null;
        $banner_image_resp = isset($form_data['banner_image_resp'])?$form_data['banner_image_resp']:null;
        

        if($banner_image!=null)
        {
            //Validation for product image
            $file_extension = strtolower( $banner_image->getClientOriginalExtension());

            if(!in_array($file_extension,['jpg','png','jpeg']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid profile image, please try again.';

               return response()->json($response);
            }

            $banner_img_file_path = $banner_image->store('banner_image');

            //unlink_old_image
            if(isset($form_data['old_banner_image']) && $form_data['old_banner_image']!="")
           {    
            $old_img_path           = $this->banner_image.$form_data['old_banner_image'];
            $unlink_old_img         = $this->GeneralService->unlink_old_image($old_img_path);
           }
        }
        else
        {
           $banner_img_file_path = $form_data['old_banner_image'];
        }
        
        if($banner_image_resp!=null)
        {
            //Validation for product image
            $file_extension = strtolower( $banner_image_resp->getClientOriginalExtension());
            
            if(!in_array($file_extension,['jpg','png','jpeg']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid profile image, please try again.';

               return response()->json($response);
            }

            $banner_img_resp_file_path = $banner_image_resp->store('banner_image/767X300');

            //unlink_old_image
            if(isset($form_data['old_banner_image_resp']) && $form_data['old_banner_image_resp']!="")
           {    
            $old_img_path           = $form_data['old_banner_image_resp'];
            $unlink_old_img         = $this->GeneralService->unlink_old_image($old_img_path);
           }
        }
        else
        {
           $banner_img_resp_file_path = isset($form_data['old_banner_image_resp']) ? $form_data['old_banner_image_resp'] : "";
        }

        $entity = BannerImageModel::firstOrNew(['id' => $enc_id]);  

         /* Insert into  Table */
     
        if(isset($form_data['type']) && !empty($form_data['type']))
        {
           $banner_type = $form_data['type'];
        }
        else
        {
           $banner_type = $form_data['old_type'];
        }

        if(isset($form_data['url']) && !empty($form_data['url']))
        {
           $banner_url  = isset($form_data['url'])?$form_data['url']:'';
        }
        // else
        // {
        //    $banner_url  = isset($form_data['old_url'])?$form_data['old_url']:'';
        // }

        //Check uploaded image count..
        if($banner_type!=3)
        {
            $banner_img_resp_file_path = "";
            if($is_update==false)
            {
                $images_count = $this->BannerImageModel->where('type',$banner_type)->count();
            }
            else
            {
                $images_count = $this->BannerImageModel->where('type',$banner_type)
                                                       ->where('id','<>',$enc_id) 
                                                       ->count();   
            }

            // if($images_count>=2)
            // {
            //    $response['status'] = 'warning';
            //    $response['description'] = "Not more than 2 Banner type ".$banner_type." images are allowed.";

            //   return response()->json($response);
            // }
            
        }

        $entity->type = $banner_type;
        $entity->url  = $banner_url;
        $entity->banner_image = $banner_img_file_path;
        $entity->banner_image_small = $banner_img_resp_file_path;

        $result = $entity->save();

      	if($result)
        {
           $response['status'] = 'success';
           $response['description'] = str_singular($this->module_title).' has been saved.';
        }
        else
        {
          $response['status'] = 'failure';
          $response['description'] = 'Error occurred while saving '.str_singular($this->module_title); 
        }

        $response['link'] = $this->module_url_path;  

        return response()->json($response);                                 
    }

    public function delete($enc_id)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_delete(base64_decode($enc_id)))
        {
            Flash::success('Banner image has been deleted.');
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' deletion.');
        }

        return redirect()->back();
    }

    public function perform_delete($id)
    {

            /*-----------------------------------------------*/

            DB::beginTransaction();
            $deleteBanner_image = $this->BannerImageModel->where('id',$id)->delete();
           
            if($deleteBanner_image)
            {          
                /*-------------------------------------------------------
                |   Activity log Event
                --------------------------------------------------------*/
                    // $arr_event                 = [];
                    // $arr_event['ACTION']       = 'REMOVED';
                    // $arr_event['MODULE_ID']    = $id;
                    // $arr_event['MODULE_TITLE'] = $this->module_title;
                    // $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'status'=>'REMOVED']);
                    
                    // $this->save_activity($arr_event);
                /*----------------------------------------------------------------------*/
                DB::commit();
                Flash::success('Banner image has been deleted.');
            }
            else
            {
               DB::rollback();
               Flash::error('Error occurred while banner image deletion.');
            }

    }

}
