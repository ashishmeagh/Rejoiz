<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Common\Services\LanguageService;
use App\Models\MenuSettingModel;
use App\Common\Services\GeneralService;
use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;

/* 
|  Author : Harshada Kothmire 
|  Date   : 02 Nov 2020
*/

class MenuSettingController extends Controller
{
    use MultiActionTrait;
    public function __construct( MenuSettingModel $MenuSettingModel )
    {
      $this->arr_view_data                   =  [];     
      $this->MenuSettingModel                   =  $MenuSettingModel;
      $this->BaseModel                       =  $this->MenuSettingModel;     
      $this->module_view_folder              =  "admin.menu_setting";
      $this->module_title                    =  "Menu Setting";
      $this->module_url_path                 =  url(config('app.project.admin_panel_slug').'/menu_settings');
      $this->curr_panel_slug                 =  config('app.project.admin_panel_slug');
      $this->user_base_img_path              =  base_path().config('app.project.img_path.user_profile_image');
      $this->user_public_img_path            =  url('/').config('app.project.img_path.user_profile_image');  
      $this->category_image                  =  base_path().'/storage/app/';
    }
 
    public function index()
    { 
        $this->arr_view_data['page_title']      = str_plural( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']  = $this->curr_panel_slug;  

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

     public function get_menu_details(Request $request)
    {
        $service_menu_details       = $this->BaseModel->getTable();
        $obj_category = DB::table($service_menu_details)
                                ->select(DB::raw($service_menu_details.".id,".  
                                                 $service_menu_details.'.menu_status,'.
                                                 $service_menu_details.'.menu_slug,'.
                                                 $service_menu_details.'.menu_name'
                                               ))                              
                                //->orderBy('id','DESC');
                                ->orderBy('id','ASC');

                                // dd($obj_category);

                                

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_menu_name']) && $arr_search_column['q_menu_name']!="")
        {
            $search_term      = $arr_search_column['q_menu_name'];
            $obj_category = $obj_category->where($service_menu_details.'.menu_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            
            $obj_category     = $obj_category->where($service_menu_details.'.menu_status',$search_term);
        }
  
        return $obj_category;
    }

    public function get_all_menu(Request $request)
    {  
        $obj_data        = $this->get_menu_details($request);

        $current_context = $this;
        // dd($current_context);

        $json_result  = Datatables::of($obj_data);


        /* Modifying Columns */
        $json_result =  $json_result->editColumn('enc_id',function($data) use ($current_context)
                        {
                            return  base64_encode(($data->id));
                        })
                        ->editColumn('build_category_image',function($data) 
                            {   
                                
                            if(isset($data->category_image) && $data->category_image!='' && file_exists(base_path().'/storage/app/'.$data->category_image))
                            {
                                $category_img = url('/storage/app/'.$data->category_image);
                            }
                            else
                            {                  
                                $category_img = "/assets/images/no-product-img-found.jpg";           
                            }
                                // $build_category_image = '<a class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'" title="View">View</a>';
                               
                                $build_category_image = '<img src="'.$category_img.'" border="0" width="60" class="img-rounded" align="center" />';
                               
                                return $build_category_image;
                            })
                            ->editColumn('status',function($data) use ($current_context)
                        {
                            $button = '';
                            $url    = '';    
                            $msg    = '';

                            if(isset($data->menu_status))
                            {
                                if($data->menu_status==1)
                                {
                                   
                                    $msg = "return confirm_action(this,event,'Are you sure? Do you want to deactivate this record.');";

                                   $button = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>';
 
                                    return $button;
                                }
                                elseif($data->menu_status==0)
                                {
                                    $msg = "return confirm_action(this,event,'Are you sure? Do you want to activate this record.');";

                                    $button = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>';


                                    return $button;   
                                }
                            }
                        })
                            ->make(true);

        $build_result = $json_result->getData();
        // dd($build_result);

        return response()->json($build_result);

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

        $this->arr_view_data['count'] = $this->MenuSettingModel->where('menu_status','1')->count();      
        
        $obj_data = $this->BaseModel->where('id', $id)->first();
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

        $arr_rules1 = [];
        $arr_rules2 = [];

        $form_data = $request->all();
        $json_data = json_encode($form_data);

        

        $enc_id = base64_decode($request->input('enc_id',false));
        $enc_id = $enc_id == "" ? false : $enc_id;

        if($request->has('enc_id'))
        {
            $is_update = true;
        }



        $arr_rules1 = ['menu_name' => 'required','menu_slug' => 'required'];      
        
        
        $validation_arr = array_merge($arr_rules1,$arr_rules2); 

        $validator = Validator::make($request->all(),$validation_arr); 

        if($validator->fails())
        {        
           $response['status']      = 'warning';
           $response['description'] = "Form validation failed, please check all fields.";

          return response()->json($response);
        } 

        $entity = MenuSettingModel::firstOrNew(['id' => $enc_id]);  

         /* Insert into  Table */
     
        if(isset($form_data['menu_status']) && !empty($form_data['menu_status']))
        {
           $menu_status = $form_data['menu_status'];
        }
        else
        {
           $menu_status = '0';
        }

        $entity->menu_status = $menu_status;
        $entity->menu_name   = $form_data['menu_name'];
        $entity->menu_slug   = $form_data['menu_slug'];
        $result = $entity->save();


        if($result)
        {     
            $response['status'] = 'success';
            $response['description'] = str_singular($this->module_title).' has been saved.';  
            if($is_update == "")
            {
                if($entity->id)
                {
                    $response['link'] = $this->module_url_path;
                } else {
                    $response['link'] = $this->module_url_path;
                }
            }
            else
            {
                    $response['link'] = $this->module_url_path;
            }         
        }
        else
        {
            $response['status'] = 'error';
            $response['description'] = 'Error occurred while save '.str_singular($this->module_title);
        }

        return response()->json($response);
                                 
    }

    public function status_update(Request $request)

    {   $mag = '';

        $user = Sentinel::check();

        try
        {

            $menu_id = base64_decode($request->input('menu_id'));
            $status = $request->input('status');
            $is_active = 0;
            
            if($status == 'activate')
            {
                $mag =  "Menu has been activated.";
                $is_active = 1;

            }
            else if($status == 'deactivate')
            {     
                $mag = "Menu has been deactivated.";
                $is_active = 0;                
            }

            $data['menu_status'] = $is_active;

            $update = $this->MenuSettingModel->where('id',$menu_id)->update($data);

            if($update)
            {
                $response['status']    = 'success';
                //$response['message']   = "Status has been changed.";
                $response['message']   = $mag;

            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $menu_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id'=>$menu_id,'menu_status'=>$status]);
            $arr_event['USER_ID']      = $user->id;

            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
            }
            else
            {
                $response['status'] = 'error';
                $response['message'] = 'Error occurred while updating status.';
            }

            
        }
         catch(Exception $e)
         {
             $response['status'] = 'error';
             $response['message'] = 'Error occurred while updating status.';
         }
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
            Flash::success('Menu has been deleted.');
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' deletion.');
        }

        return redirect()->back();
    }

   /* public function perform_delete($id)
    {
        DB::beginTransaction();
        $deleteCategory = $this->BaseModel->where('id',$id)->delete();
        $deleteCategoryTrans =  $this->CategoryTranslationModel->where('category_id',$id)->delete();
        $deleteSubcategory = $this->SubCategoryModel->where('category_id',$id)->delete();        
        //get subcategories
        $subcategoriesArr = $this->SubCategoryModel->where('category_id',$id)
                                                   ->get()->toArray();
        $subcategoryIds = array_column($subcategoriesArr, 'id');
        $deleteSubcategoryTrans = $this->SubCategoryTranslationModel->whereIn('subcategory_id',$subcategoryIds)->delete();
        
        if($deleteCategory)
        {          
           // -------------------------------------------------------
            //|   Activity log Event
            //--------------------------------------------------------
                $arr_event                 = [];
                $arr_event['ACTION']       = 'REMOVED';
                $arr_event['MODULE_ID']    = $id;
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'status'=>'REMOVED']);
                
                $this->save_activity($arr_event);
            //----------------------------------------------------------------------
            DB::commit();
            Flash::success($this->module_title.' deleted successfully');
        }
        else
        {
           DB::rollback();
           Flash::error('Problem occured while '.$this->module_title.'deletion');
        }

    }*/


    public function perform_delete($id)
    {            
        $user = Sentinel::check();

        DB::beginTransaction();
        $deleteCategory = $this->MenuSettingModel->where('id',$id)->delete();         
        
        if($deleteCategory)
        {          
            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
                $arr_event                 = [];
                $arr_event['ACTION']       = 'REMOVED';
                $arr_event['MODULE_ID']    = $id;
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'menu_status'=>'REMOVED']);
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);
            /*----------------------------------------------------------------------*/
            DB::commit();
            Flash::success('Menu has been deleted.');
        }
        else
        {
           DB::rollback();
           Flash::error('Error occurred while '.$this->module_title.'deletion.');
        }        

    }


    public function deactivate($enc_id = FALSE)
    {  
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_deactivate(base64_decode($enc_id)))
        {
            Flash::success('Menu has been deactivated.');
        }
        else
        {
            Flash::error('Error occurred while '.str_plural($this->module_title).' deactivation.');
        }

        return redirect()->back();
    }

    public function arrange_locale_wise(array $arr_data)
    {
        if(sizeof($arr_data)>0)
        {
            foreach ($arr_data as $key => $data) 
            {
                $arr_tmp = $data;
                unset($arr_data[$key]);

                $arr_data[$data['locale']] = $data;                    
            }

            return $arr_data;
        }
        else
        {
            return [];
        }
    }

    public function multi_action(Request $request)
  {
        $arr_rules = array();
        $arr_rules['multi_action'] = "required";
        $arr_rules['checked_record'] = "required";

        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Flash::error('Please select '.$this->module_title.' to perform multi actions.');
            return redirect()->back()->withErrors($validator)->withInput();
        }

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
               Flash::success('Menu(s) has been deleted.'); 
               
            } 
            elseif($multi_action=="activate")
            {
               $this->perform_activate(base64_decode($record_id)); 
               Flash::success('Menu(s) has been activated.');
               
            }
            elseif($multi_action=="deactivate")
            {
               $this->perform_deactivate(base64_decode($record_id));    
               Flash::success('Menu(s) has been deactivated.'); 
               
            }

        }

        return redirect()->back(); 
        
  }

    public function perform_activate($id)
    {
        $result = $this->MenuSettingModel->where('id',$id)->update(['menu_status'=>'1']);
       
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
        $result = $this->MenuSettingModel->where('id',$id)->update(['menu_status'=>'0']);
        if($result)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

   
  

}
