<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Common\Services\LanguageService;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\SubCategoryModel;
use App\Models\SubCategoryTranslationModel;
use App\Models\LanguageModel;
use App\Models\ProductsModel;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\GeneralService;



use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;

/* 
|  Author : SAgar B. Jadhav
|  Date   : 21 June 2019
*/

class CategoryController extends Controller
{
    use MultiActionTrait;
    public function __construct( CategoryModel $CategoryModel,
                                 LanguageService $langauge,
                                 CategoryTranslationModel $CategoryTranslationModel,
                                 LanguageModel $language_model,
                                 SubCategoryModel $SubCategoryModel,
                                 ProductsModel $ProductsModel,
                                 SubCategoryTranslationModel $SubCategoryTranslationModel,
                                 ElasticSearchService $ElasticSearchService,
                                 GeneralService $GeneralService

                               )
    {
      $this->arr_view_data                   =  [];
      $this->LanguageModel                   =  $language_model;
      $this->ElasticSearchService            =  $ElasticSearchService;
      $this->GeneralService                  =  $GeneralService;   
      $this->CategoryModel                   =  $CategoryModel;
      $this->BaseModel                       =  $this->CategoryModel;
      $this->CategoryTranslationModel        =  $CategoryTranslationModel;
      $this->SubCategoryModel                =  $SubCategoryModel;
      $this->SubCategoryTranslationModel     =  $SubCategoryTranslationModel;
      $this->LanguageService                 =  $langauge;
      $this->ProductsModel                   =  $ProductsModel;
      $this->module_view_folder              =  "admin.category";
      $this->module_title                    =  "Category";
      $this->module_url_path                 =  url(config('app.project.admin_panel_slug').'/categories');
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

     public function get_category_details(Request $request)
    {
        $service_category_details       = $this->BaseModel->getTable();

        
        $prefixed_service_category      = DB::getTablePrefix().$this->BaseModel->getTable();
        $prefixed_category_translation  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();


        $obj_category = DB::table($service_category_details)
                                ->select(DB::raw($prefixed_service_category.".id,".  
                                                 $prefixed_service_category.'.is_active,'.
                                                 $prefixed_service_category.'.category_image,'.
                                                 $prefixed_service_category.'.priority,'.
                                                 $prefixed_category_translation.'.category_name'
                                               ))
                                ->join($prefixed_category_translation,$prefixed_service_category.'.id','=',$prefixed_category_translation.'.category_id')
                                ->where($prefixed_category_translation.'.locale','en')
                                ->where($prefixed_service_category.'.admin_confirm_status','=','0')
                                ->whereNull($service_category_details.'.deleted_at')
                                ->orderBy('id','DESC');

                                // dd($obj_category);

                                

        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_category_name']) && $arr_search_column['q_category_name']!="")
        {
            $search_term      = $arr_search_column['q_category_name'];
            $obj_category = $obj_category->where($prefixed_category_translation.'.category_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            
            $obj_category     = $obj_category->where($service_category_details.'.is_active',$search_term);
        }

        if(isset($arr_search_column['q_priority']) && $arr_search_column['q_priority']!="")
        {
            $search_term      = $arr_search_column['q_priority'];
            
            $obj_category     = $obj_category->where($service_category_details.'.priority','LIKE', '%'.$search_term.'%');
            //dd($obj_category);

        }
          return $obj_category;
    }

    public function get_all_category(Request $request)
    {  
        $obj_data        = $this->get_category_details($request);

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
                            $image_name = (isset($data->category_image))? $data->category_image:"";
                            $image_type = "category";
                            $is_resize = 0;
                            $category_img_path = imagePath($image_name, $image_type, $is_resize); 
                            $build_category_image = '<img src="'.$category_img_path.'" border="0" width="60" class="img-rounded" align="center" />';                             

                            return  $build_category_image;                            
                        })
                        ->editColumn('status',function($data) use ($current_context)
                        {
                            $button = '';
                            $url    = '';    
                            $msg    = '';

                            if(isset($data->is_active))
                            {
                                if($data->is_active==1)
                                {
                                   
                                    $msg = "return confirm_action(this,event,'Are you sure? Do you want to deactivate this record.');";

                                   $button = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>';
 
                                    return $button;
                                }
                                elseif($data->is_active==0)
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
       
        $this->arr_view_data['arr_lang']             = $this->LanguageService->get_all_language(); 
        $this->arr_view_data['page_title']           = 'Create '.str_singular($this->module_title);
        $this->arr_view_data['module_title']         = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']      = $this->curr_panel_slug;  

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }


    public function edit($enc_id)
    {
        $id       = base64_decode($enc_id);

        $this->arr_view_data['count'] = $this->LanguageModel->where('status','1')->count();

        $arr_lang = $this->LanguageService->get_all_language();
        
        $obj_data = $this->BaseModel->where('id', $id)->with(['translations'])->first();
        $arr_data = [];

        if($obj_data)
        {
           $arr_data = $obj_data->toArray();
           /* Arrange Locale Wise */
           $arr_data['translations'] = $this->arrange_locale_wise($arr_data['translations']);
        }
  
        $this->arr_view_data['edit_mode']       = TRUE;
        $this->arr_view_data['enc_id']          = $enc_id;
        $this->arr_view_data['arr_lang']        = $this->LanguageService->get_all_language();  
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

         //dd($form_data);

        $enc_id = base64_decode($request->input('enc_id',false));
        $enc_id = $enc_id == "" ? false : $enc_id;

        if($request->has('enc_id'))
        {
            $is_update = true;
        }

        $arr_rules1 = ['category_name_en' => 'required'];

        if($is_update == false)
        {
            $arr_rules2 = [
                            'category_image' => 'required',
                            'priority' => 'required'
                          ];
        }   
        
        
        $validation_arr = array_merge($arr_rules1,$arr_rules2); 

        $validator = Validator::make($request->all(),$validation_arr); 

        if($validator->fails())
        {        
           $response['status']      = 'warning';
           $response['description'] = "Form validation failed, please check all fields.";

          return response()->json($response);
        }  

        /* Check if location already exists with given translation */
        $is_duplicate = $this->BaseModel
                             ->whereHas('translations',function($query) use($request)
                                        {
                                            $query->where('locale','en')
                                                  ->where('category_name',$request->input('category_name_en'));
                                        });

        if($is_update == true)
        {
           $is_duplicate = $is_duplicate->where('id','<>',$enc_id);
        } 

        $does_exists = $is_duplicate->count();
       
        if($does_exists)
        {  
           $response['status']      = 'warning';
           $response['description'] = str_singular($this->module_title).' already exists.';

           return response()->json($response);
        }   
           //order by priority asc 1 2 3 4 5
        /* Check if priority value already existprioritys start */

        if($request->input('priority')){
            $is_duplicate_priority = $this->BaseModel
                                     ->where('priority',$request->input('priority'));

            if($is_update == true)
            {
                $is_duplicate_priority = $is_duplicate_priority->where('id','<>',$enc_id);
            } 

            $does_exists_priority = $is_duplicate_priority->count();
            
            if($does_exists_priority)
            {  
                $response['status']      = 'warning';
                $response['description'] = 'Priority value already allotted, please choose another value.';

                return response()->json($response);
            } 
        }
          /* Check if priority value already exists start */

  

         /*image uploading*/
        $category_img_file_path = '';
            
        $category_image = isset($form_data['category_image'])?$form_data['category_image']:null;
            

        if($category_image!=null)
        {
            //Validation for product image
            $file_extension = strtolower( $category_image->getClientOriginalExtension());

            if(!in_array($file_extension,['jpg','png','jpeg']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid profile image, please try again.';

               return response()->json($response);
            }

            $category_img_file_path = $category_image->store('category_image');

            //unlink old image 
            if(isset($form_data['old_category_image']) && $form_data['old_category_image']!="")
            {    
              $old_img_path           = $this->category_image.$form_data['old_category_image'];
              $unlink_old_img         = $this->GeneralService->unlink_old_image($old_img_path);
            }
        }

        else
        {
            $category_img_file_path = $form_data['old_category_image'];
        }


        $entity = CategoryModel::firstOrNew(['id' => $enc_id]);  

         /* Insert into  Table */
     
        if(isset($form_data['category_status']) && !empty($form_data['category_status']))
        {
           $category_status = $form_data['category_status'];
        }
        else
        {
           $category_status = '0';
        }

        if(isset($form_data['priority']) && !empty($form_data['priority']))
        {
           $priority = $form_data['priority'];
        }
        else
        {
           $priority = '0';
        }

        $entity->is_active = $category_status;
        $entity->priority = $priority;
        $entity->category_image = $category_img_file_path;

        $result = $entity->save();

        if($result)
        {
             /*get all languages*/
            $arr_lang =  $this->LanguageService->get_all_language();

            /* insert record into translation table */
            if(sizeof($arr_lang) > 0 )
            {
                foreach ($arr_lang as $lang) 
                {            
                    $arr_data = array();
                    $category_name = $form_data['category_name_'.$lang['locale']];                
                   
                    if((isset($category_name) && $category_name != ''))
                    { 
                      
                        /* Get Existing Language Entry */
                        $translation = $entity->getTranslation($lang['locale'],false);  

                        if($translation)
                        {
                            $translation->category_name  = $category_name;
                            $translation->category_slug  = str_slug($category_name,'_');
                            $translation->save();    
                        }  
                        else
                        {
                            /* Create New Language Entry  */
                            $translation = $entity->getNewTranslation($lang['locale']);
                            $translation->category_id   = $entity->id;
                            $translation->category_name = $category_name;
                            $translation->category_slug = str_slug($category_name,'_');

                           
                            $translation->save();
                        }                       
                    }
                }
                //foreach 
                /*-------------------------------------------------------
                   |   Activity log Event
                --------------------------------------------------------*/
                $arr_event                 = [];

                //get login user 

                $user = Sentinel::check();

                if($is_update == false)
                {
                    $arr_event['ACTION']       = 'ADD';
                    $arr_event['MODULE_ID']    =  $entity->id;
                }
                else
                {
                    $arr_event['ACTION']   = 'EDIT';
                    $arr_event['MODULE_ID'] = $enc_id;
                }

                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = $json_data;
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);

               /*----------------------------------------------------------------------*/
                      
                if($is_update == false)
                {

                    if($entity->id)
                    {
                        $response['link'] = $this->module_url_path.'/edit/'.base64_encode($entity->id);

                    }
                }
                else
                {
                    $response['link'] = $this->module_url_path.'/edit/'.base64_encode($enc_id);
                }
            } 

            $response['status'] = 'success';
            $response['description'] = str_singular($this->module_title).' has been saved.';           
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
        try
        {   
            $category_id = base64_decode($request->input('category_id'));
            $status = $request->input('status');
            if($status == 'activate')
            {          
                $mag = "Category has been activated.";
                $is_active = '1';
                /*Activate all approve product of this category*/
                    $category_id = isset($category_id)?intval($category_id):0;
                    $product_details = [];
                    $product_details = $this->ProductsModel
                                            ->where('category_id',$category_id)
                                            ->where('is_deleted', 0)
                                            ->where('is_active', 1)
                                            ->whereHas('userDetails',function($q){
                                              return $q->where('status',1)->where('is_approved',1);
                                              })->get();

                   
                   
                    if (count($product_details) > 0)
                    {
                        /* 
                        foreach ($product_details as $key => $product) {

                           $update_product_status = $this->ProductsModel->where('id',$product['id'])->update(['is_active'=>1]);
                        }*/
                    }
                // $this->ElasticSearchService->activate_category_product($category_id);
                $data['is_active'] = $is_active;

                $update = $this->CategoryModel->where('id',$category_id)->update($data);
            }
            else if($status == 'deactivate')

            {     
                $mag = "Category has been deactivated.";


                $is_active = '0';
                // $this->ElasticSearchService->deactivate_category_product($category_id);

                /*Deactive all product of this category*/
                $product_details = [];
                $product_details = $this->ProductsModel
                                        ->where('category_id',$category_id)
                                        ->where('is_deleted', 0)
                                        ->where('product_complete_status',4)
                                        ->whereHas('userDetails',function($q){
                                        return $q->where('status',1)->where('is_approved',1);
                                        })->get();
                
                
                if (count($product_details) > 0)
                {

                    $mag = "Category contains active product(s), can not change category status";

                    $update = "warning";
                    /*foreach ($product_details as $key => $product)
                    {

                       $update_product_status = $this->ProductsModel->where('id',$product['id'])->update(['is_active'=>0]);
                    }*/
                }
                else
                {    
                     $data['is_active'] = $is_active;
                     $update = $this->CategoryModel->where('id',$category_id)->update($data);
                }
                
            }

            if($update && $update!="warning")
            {
                $user = Sentinel::check();

                $response['status']    = 'success';
                $response['message']   = $mag;

                /*-------------------activity log*------------------------------------*/
                $arr_event['ACTION']       = 'EDIT';
                $arr_event['MODULE_ID']    = $category_id;
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = json_encode(['id'=>$category_id,'status'=>$status]);
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);

                /*----------------------------------------------------------------------*/
            }
            else if($update=="warning")
            {
                $response['status']  = 'warning';
                $response['type']    = "warning";
                $response['title']   = "warning";

                $response['message'] = 'Category contains active product(s), can not change category status';

            }
            else
            {
                $response['status']  = 'error';
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

    public function perform_deactivate($id)
    {   
        try
        {  
            $category_id = isset($id)?intval($id):0;

            $product_details = $this->ProductsModel->where('category_id',$category_id)
                                                    ->where('is_deleted', 0)
                                                    ->where('product_complete_status',4)
                                                    ->whereHas('userDetails',function($q){
                                                    return $q->where('status',1)->where('is_approved',1);
                                                    })->count();
            
            
            if ($product_details == 0) 
            {
                $this->CategoryModel->where('id',$category_id)->update(['is_active'=>'0']);
            }
            return true;
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return FALSE;
        }
    }

    public function perform_activate($id_arr)
    {
        foreach ($id_arr as $key => $record_id) 
        {  
            $category_id = isset($record_id)?base64_decode($record_id):0;
            $this->CategoryModel->where('id',$category_id)->update(['is_active'=>'1']);
        }
        Flash::success('Category activated successfully');
        return true;
    }

    public function delete($enc_id)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_delete(base64_decode($enc_id)))
        {
            Flash::success($this->module_title.' has been deleted.');
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' deletion.');
        }

        return redirect()->back();
    }

     public function perform_multi_delete($id_arr)
    {
         foreach ($id_arr as $key => $record_id) 
        {  
            $category_id = isset($record_id)?base64_decode($record_id):0;

            $cat_count_in_sub_cat = $this->SubCategoryModel->where('category_id',$category_id)->count();
            $cat_count_in_product = $this->ProductsModel->where('category_id',$category_id)->count();
            if($cat_count_in_sub_cat > 0 || $cat_count_in_product)
            {
                Flash::error("Category can't be deleted,first you have to delete this category
                from subcategory,product and representative.");
                return redirect()->back();
            }    
            else{
             $delete = $this->CategoryModel->where('id',$category_id)->delete();
            }
            //dd($update);
        }
        return $delete;
       
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

        $multi_action = $request->input('multi_action');
        $checked_record = $request->input('checked_record');

        /* Check if array is supplied*/
        if(is_array($checked_record) && sizeof($checked_record)<=0)
        {
            Flash::error('Problem occurred,while doing multi action.');
            return redirect()->back();
        }
        $active_id_arr   = [];
        $deactive_id_arr = [];
        if($multi_action =="deactivate")
        {
            foreach ($checked_record as $key => $record_id) 
            {  
                $category_id = isset($record_id)?base64_decode($record_id):0;

                $product_details = $this->ProductsModel->where('category_id',$category_id)
                                                        ->where('is_deleted', 0)
                                                        ->where('product_complete_status',4)
                                                        ->whereHas('userDetails',function($q){
                                                        return $q->where('status',1)->where('is_approved',1);
                                                        })->count();
                if($product_details==0)
                {
                    $category_name = get_catrgory_name($category_id);
                    $this->perform_deactivate($category_id);

                    array_push($deactive_id_arr,$category_name);
                }
                else
                {   $category_name = get_catrgory_name($category_id);
                    array_push($active_id_arr,$category_name);
                }
            }
            $active_message   = '';
            $deactive_message = '';
            $active_message   =  implode(',', $active_id_arr);
            $deactive_message =  implode(',', $deactive_id_arr);

            if(count($active_id_arr)>0)
            {   $type = "error";
                $message = "Cannot perform this action categories contains active products";
                if(count($active_id_arr)==1)
                {
                    $message = "Cannot perform action category contains active product(s)";
                }
            }
            if(count($deactive_id_arr)>0)
            {   $type = "success";
                $message = "Categories deactivated successfully";
                if(count($deactive_id_arr)==1)
                {
                    $message = "Category deactivated successfully";
                }
            }

            if(count($active_id_arr)>0 && count($deactive_id_arr)>0)
            {
                $type = "success";
                $active_message  = implode(',', $active_id_arr);
                $deactive_message = implode(',', $deactive_id_arr);
                $message = "List of deactivated categories: ".$deactive_message.",and some categories can not be deactivated because they contains active products, following is list of active categories: ".$active_message;
            }
        }
        else if($multi_action == "activate")
        {
            $this->perform_activate($checked_record);
            $message = "Categories activated successfully";
            $type = "success";

        }  
        else if($multi_action == "delete")
        {
            $update = $this->perform_multi_delete($checked_record);
            $message = "Categories deleted successfully";
            $type = "success";
            if($update)
            {
                Flash::$type($message);
                return redirect()->back();
            }
            else
            {   $message = "Error while perofrming action, please try again";
                $type = "error";
                
            }
        } 
        
       
        Flash::$type($message);
        return redirect()->back();
    }

    public function perform_delete($id)
    {
         /*first check category already assign or not*/
        $count = 0;

        $cat_count_in_sub_cat = $this->SubCategoryModel->where('category_id',$id)->count();
        $cat_count_in_product = $this->ProductsModel->where('category_id',$id)->count();
          
        if($cat_count_in_sub_cat >0 || $cat_count_in_product >0)
        {
           Flash::error("Category can't be deleted,first you have to delete this category
              from subcategory,product and representative.");
           return redirect()->back();
        }
        else
        {

            /*-----------------------------------------------*/

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
                /*-------------------------------------------------------
                |   Activity log Event
                --------------------------------------------------------*/

                    $user = Sentinel::check();

                    $arr_event                 = [];
                    $arr_event['ACTION']       = 'REMOVED';
                    $arr_event['MODULE_ID']    = $id;
                    $arr_event['MODULE_TITLE'] = $this->module_title;
                    $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'status'=>'REMOVED']);
                    $arr_event['USER_ID']      = $user->id;
                    
                    $this->save_activity($arr_event);
                /*----------------------------------------------------------------------*/
                DB::commit();
                Flash::success($this->module_title.' has been deleted.');
            }
            else
            {
               DB::rollback();
               Flash::error('Error occurred while '.$this->module_title.'deletion.');
            }

        } 

    }


    public function deactivate($enc_id = FALSE)
    {  dd("deactivate");
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_deactivate(base64_decode($enc_id)))
        {
            Flash::success($this->module_title. ' has been deactivated.');
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
}
