<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GeneralSettingModel;


use Validator;
use Session;
use Flash;
use File;
use Sentinel;
use DB;
use Datatables;

class GeneralSettingController extends Controller
{
    public function __construct(GeneralSettingModel $GeneralSettingModel)
    {
        $this->arr_view_data            = [];
        $this->GeneralSettingModel      = $GeneralSettingModel;
        $this->BaseModel                = $this->GeneralSettingModel;
        $this->module_url_path          = url(config('app.project.admin_panel_slug')."/general_setting");
        $this->banner_base_img_path   = base_path().config('app.project.img_path.banner_image');
        $this->banner_public_img_path  = url('/').config('app.project.img_path.banner_image');
        $this->module_title             = "General Settings";
        $this->module_view_folder       = "admin.general_setting";
    }

    public function index()
    {
        $arr_view_data['page_title']               = "Manage ".$this->module_title;
		$arr_view_data['module_title']             = $this->module_title;
		$arr_view_data['module_url_path']          = $this->module_url_path;
		$arr_view_data['banner_public_img_path']   = $this->banner_public_img_path;
     	
        return view($this->module_view_folder.'.index',$arr_view_data);
        
    }

    public function get_banner_images(Request $request)
    {
    	$general_setting_table          = $this->BaseModel->getTable();
        $prefix_general_setting_table 	= DB::getTablePrefix().$this->BaseModel->getTable();

        $banner_images_obj =  DB::table($prefix_general_setting_table)
                                ->select($prefix_general_setting_table.'.id',
                                     $prefix_general_setting_table.'.banner_image',
                                     $prefix_general_setting_table.'.is_active',
                                     $prefix_general_setting_table.'.banner_order_sequence')
                                
                                ->whereNull($prefix_general_setting_table.'.deleted_at');

        /* ---------------- Filtering Logic ----------------------------------*/

        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term       = $arr_search_column['q_status'];
            $banner_images_obj = $banner_images_obj->where($prefix_general_setting_table.'.is_active','LIKE', '%'.$search_term.'%');
        }
    
        return $banner_images_obj;                    
    }

    public function get_records(Request $request)
    {
        $obj_banner_images   = $this->get_banner_images($request);

        $current_context     = $this;

        $json_result         = Datatables::of($obj_banner_images);
 
        $json_result         = $json_result->blacklist(['id']);
        
        $json_result         = $json_result->editColumn('enc_id',function($data) use ($current_context)
                            {
                                return base64_encode($data->id);
                            })

                            ->editColumn('banner_image',function($data) use ($current_context)
                            {
                               
                                if($data->banner_image != "")
                                {
                                    return $data->banner_image;
                                }
                                else
                                {
                                    return "N/A";
                                }
                            })

                            ->editColumn('banner_order_sequence',function($data) use ($current_context)
                            {
                               
                                if($data->banner_order_sequence != "")
                                {
                                    return $data->banner_order_sequence;
                                }
                                else
                                {
                                    return "N/A";
                                }
                            })

                            ->editColumn('build_status_btn',function($data) use ($current_context)
                            {
                                 $build_status_btn = "";

                                if($data->is_active == '0')
                                { 
                                    $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';

                                }
                                elseif($data->is_active == '1')
                                {
                                    $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262"/>';
                                }

                                return $build_status_btn;
         
 
                            }) 

                            ->editColumn('build_action_btn',function($data) use ($current_context)
                            {   
                                $edit_href =  $this->module_url_path.'/edit/'.base64_encode($data->id);
                                $build_edit_action = '<a class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$edit_href.'" title="Edit"><i class="ti-pencil-alt2" ></i></a>';

                                $delete_href =  $this->module_url_path.'/delete/'.base64_encode($data->id);
                                $confirm_delete = 'onclick="confirm_delete(this,event);"';
                                $build_delete_action = '<a class="btn btn-circle btn-danger btn-outline show-tooltip" '.$confirm_delete.' href="'.$delete_href.'" title="Delete"><i class="ti-trash" ></i></a>';

                                return $build_action = $build_edit_action.' '.$build_delete_action;
                            })
                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }


    public function create()
    {
    	$this->arr_view_data['page_title']      = "Create ".$this->module_title;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }


    public function edit($enc_id)
    {
        $id              = base64_decode($enc_id);
        $data            = array();
        $arr_banner_data = [];
     
        $banner_image    = "default.jpg";
    
        $obj_data = $this->BaseModel->where('id', $id)->first();

        if($obj_data)
        {
           $arr_banner_data = $obj_data->toArray(); 
        }

        $banner_public_img_path = $this->banner_public_img_path;

        $this->arr_view_data['edit_mode']                = TRUE;
        $this->arr_view_data['banner_public_img_path']   = $banner_public_img_path;
        $this->arr_view_data['module_url_path']          = $this->module_url_path;
        $this->arr_view_data['arr_banner_data']          = isset($arr_banner_data)?$arr_banner_data :[];  
        $this->arr_view_data['page_title']               = "Edit ".$this->module_title;
        $this->arr_view_data['module_title']             = $this->module_title;

        return view($this->module_view_folder.'.edit',$this->arr_view_data); 
    }


    public function store(Request $request)
    { 
        /* Is Update/ Create Process */

        $user = Sentinel::check();

     
        $form_data         = $request->all();

        $is_update_process = false;
        $id                = $request->input('id',false);

        if($request->has('id'))
        {
            $is_update_process = true; 
        }

        $arr_rules = [
                       'order_sequence' => 'required'
                       
                     ];

        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            $response['status']      = "error";
            $response['description'] = "Form validation failed, please check all fields.";
            return response()->json($response);
        }
    
     /* Duplication Check */
     /*   $obj_dup_check = $this->BaseModel->where('category_title',$request->input('title'));
                           
        if($is_update_process)
        {
            $obj_dup_check = $obj_dup_check->where('id','<>',$request->input("id"));
        }

        $does_exists = $obj_dup_check->count();

        if($does_exists)
        {
            Flash::error(str_singular($this->module_title).' Already Exists.');
            return redirect()->back()->withInput($request->all());
        }
      */

        /* Main Model Entry */
        $entity = $this->BaseModel->firstOrNew(['id' => $id]);

        $entity->banner_order_sequence = $request->input('order_sequence');
   
        if(isset($form_data['status']) && !empty($form_data['status']))
        {
           $banner_status = $form_data['status'];
        }
        else
        {
           $banner_status = '0';
        }   

        $file_name = '';

        if($request->hasFile('banner_image'))
        {
            $file_extension = strtolower($request->file('banner_image')->getClientOriginalExtension());

            $file_name = sha1(uniqid().$request->file('banner_image').uniqid()).'.'.$file_extension;
           
            $request->file('banner_image')->move($this->banner_base_img_path, $file_name);

            $unlink_old_img_path  = $this->banner_base_img_path.'/'.$request->input('old_img');
                            
            if(file_exists($unlink_old_img_path))
            {
                @unlink($unlink_old_img_path);  
            }
        } 
        else
        {
           $file_name = $request->input('old_img');
        }

        $entity->is_active        = $banner_status;
        $entity->banner_image     = $file_name;
      
        $entity->save();

        if($entity)
        {

            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
            if($is_update_process == true)
            {
                $arr_event                 = [];
                $arr_event['ACTION']       = 'EDIT';
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);
            }
            elseif ($is_update_process != true) 
            {
                $arr_event                 = [];
                $arr_event['ACTION']       = 'ADD';
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);
            }
            
           /*----------------------------------------------------------------------*/

            if($is_update_process == false)
            {
                if($entity->id)
                {
                    $response['link'] =$this->module_url_path.'/edit/'.base64_encode($entity->id);

                }
            }
            else
            {
                $response['link'] = $this->module_url_path.'/edit/'.base64_encode($id);
            }

            $response['status']      = "success";
            $response['description'] = "General settings has been saved."; 

        }
        else
        {
            $response['status']      = "error";
            $response['description'] = "Error occurred while adding general settings.";
        }

        return response()->json($response);
    }


    public function multi_action(Request $request)
    { 
        /*Check Validations*/
        $input = request()->validate([
                                        'multi_action'   => 'required',
                                        'checked_record' => 'required'
                                    ], 
                                    [
                                        'multi_action.required'   => 'Please  select record required',
                                        'checked_record.required' => 'Please select record required'
                                    ]);

        $multi_action   = $request->input('multi_action');
        $checked_record = $request->input('checked_record');


        /* Check if array is supplied*/
        if(is_array($checked_record) && sizeof($checked_record)<=0)
        {
            Flash::error('Error occurred while doing multi action.');
            return redirect()->back();
        }

        foreach ($checked_record as $key => $record_id) 
        {  
            if($multi_action=="delete")
            {
               $this->perform_delete(base64_decode($record_id));    
               Flash::success(str_plural($this->module_title).' has been deleted.'); 
            } 
            elseif($multi_action=="activate")
            {
               $this->perform_activate(base64_decode($record_id)); 
               Flash::success(str_plural($this->module_title).' has been activated.'); 
            }
            elseif($multi_action=="deactivate")
            {
               $this->perform_deactivate(base64_decode($record_id));    
               Flash::success(str_plural($this->module_title).' has been blocked.');  
            }
        }

        return redirect()->back();
    }
    
    public function activate(Request $request)
    {
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        $arr_response = [];    
        if($this->perform_activate(base64_decode($enc_id)))
        {
            $arr_response['status'] = 'SUCCESS';
        }
        else
        {
            $arr_response['status'] = 'ERROR';
        }

        $arr_response['data'] = 'ACTIVE';

        return response()->json($arr_response);
    }

    public function deactivate(Request $request)
    {
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        $arr_response = []; 
        
        if($this->perform_deactivate(base64_decode($enc_id)))
        {
             $arr_response['status'] = 'SUCCESS';
        }
        else
        {
            $arr_response['status'] = 'ERROR';
        }

        $arr_response['data'] = 'DEACTIVE';

        return response()->json($arr_response);
    }

    public function delete($enc_id = FALSE)
    {
       
        if(!$enc_id)
        {
            return redirect()->back();
        }
            
        if($this->perform_delete(base64_decode($enc_id)))
        {
           Flash::success(str_singular($this->module_title).' has been deleted.');
        }
        else
        {
            Flash::error('Error occurred while deleting '.str_singular($this->module_title));
        }

        return redirect()->back();
    }


    public function perform_activate($id)
    {
        $result = $this->BaseModel->where('id',$id)->update(['is_active'=>'1']);
       
        if($result)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function perform_deactivate($id)
    {
        $result = $this->BaseModel->where('id',$id)->update(['is_active'=>'0']);
        if($result)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

   
    public function perform_delete($id)
    {
        $user = Sentinel::check();

        $banner_image      ='';
        $banner_details    = $this->GeneralSettingModel->where('id',$id)->first();

        if(isset($banner_details) && count($banner_details) > 0)
        {
           $banner_image = $banner_details->banner_image;  
        }

        $entity = $this->GeneralSettingModel->where('id',$id)->delete();
        
        if($entity)
        {
            $unlink_image  = $this->banner_base_img_path.$banner_image;
       
            if(file_exists($unlink_image))
            {
                @unlink($unlink_image);  
            }

            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
                $arr_event                 = [];
                $arr_event['ACTION']       = 'REMOVED';
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);
            /*----------------------------------------------------------------------*/
            return TRUE;
        }
 
        return FALSE;
    }


}
