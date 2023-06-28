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
use App\Models\SiteSettingModel;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Services\HelperService;


use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;
use Session;

/* 
|  Author : Harshada
|  Date   : 10 Nov 2020
*/

class RequestCategoryController extends Controller
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
                                 GeneralService $GeneralService,
                                 EmailService $EmailService,
                                 HelperService $HelperService,
                                 SiteSettingModel $SiteSettingModel
                               )
    {
      $this->arr_view_data                   =  [];
      $this->LanguageModel                   =  $language_model;
      $this->ElasticSearchService            =  $ElasticSearchService;
      $this->GeneralService                  =  $GeneralService;   
      $this->HelperService                   = $HelperService;
      $this->CategoryModel                   =  $CategoryModel;
      $this->BaseModel                       =  $this->CategoryModel;
      $this->EmailService                    =  $EmailService;  
      $this->CategoryTranslationModel        =  $CategoryTranslationModel;
      $this->SubCategoryModel                =  $SubCategoryModel;
      $this->SubCategoryTranslationModel     =  $SubCategoryTranslationModel;
      $this->LanguageService                 =  $langauge;
      $this->ProductsModel                   =  $ProductsModel;
      $this->module_view_folder              =  "admin.request_category";
      $this->module_title                    =  "Request Category";
      $this->module_url_path                 =  url(config('app.project.admin_panel_slug').'/request_categories');
      $this->curr_panel_slug                 =  config('app.project.admin_panel_slug');
      $this->user_base_img_path              =  base_path().config('app.project.img_path.user_profile_image');
      $this->user_public_img_path            =  url('/').config('app.project.img_path.user_profile_image');  
      $this->category_image                  =  base_path().'/storage/app/';
      $this->SiteSettingModel                =  $SiteSettingModel;
      $this->site_setting_obj  = $this->SiteSettingModel->first();
    
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
    }
 
    public function index()
    { 
        $this->arr_view_data['page_title']      = str_plural( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']  = $this->curr_panel_slug;  

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

     public function get_request_category_details(Request $request)
    {
        $service_category_details       = $this->BaseModel->getTable();

        
        $prefixed_service_category      = DB::getTablePrefix().$this->BaseModel->getTable();
        $prefixed_category_translation  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();


        $obj_category = DB::table($service_category_details)
                                ->select(DB::raw($prefixed_service_category.".id,".  
                                                 $prefixed_service_category.'.is_active,'.
                                                 $prefixed_service_category.'.category_image,'.
                                                 $prefixed_category_translation.'.category_name,'.
                                                 $prefixed_service_category.'.admin_confirm_status,'.
                                                 $prefixed_service_category.'.reject_reason'
                                               ))
                                ->join($prefixed_category_translation,$prefixed_service_category.'.id','=',$prefixed_category_translation.'.category_id')
                                ->where($prefixed_category_translation.'.locale','en')
                                ->where($prefixed_service_category.'.admin_confirm_status','!=','0')
                                ->where($prefixed_service_category.'.is_active','=','0')
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
            
            $obj_category     = $obj_category->where($service_category_details.'.admin_confirm_status',$search_term);
        }
  
        return $obj_category;
    }

    public function get_all_request_category(Request $request)
    {  
        $obj_data        = $this->get_request_category_details($request);

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
                            ->editColumn('admin_confirm_status',function($data) use ($current_context)
                        {
                            return $data->admin_confirm_status;
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

    public function view($enc_id)
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
        $this->arr_view_data['page_title']      = 'View '.str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;  
        
        if(count($this->arr_view_data['arr_data']) <= 0)
        {
          Session::flash('error',"Category record not available");
          return redirect()->back();

        }
          return view($this->module_view_folder.'.view',$this->arr_view_data);  
        
        
    }


    public function save(Request $request)
    {   
        $is_update = false;

        $arr_rules1 = [];
        $arr_rules2 = [];

        $form_data = $request->all();
        $json_data = json_encode($form_data);

        // dd($form_data);

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
                            'category_image' => 'required'
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

        $entity->is_active = $category_status;
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
                $arr_event = [];

                $user = Sentinel::check();

                if($is_update == false)
                {
                    $arr_event['ACTION']       = 'ADD';
                    $arr_event['MODULE_ID']    =  $entity->id;
                }
                else
                {
                    $arr_event['ACTION']    = 'EDIT';
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
    {  

        $mag = '';
        $user = Sentinel::check();


        try
        {

            $category_id    = base64_decode($request->input('cat_id'));
            $status         = $request->input('status');
            $reject_reason  = $request->input('reject_reason');
            $category_name  = $request->input('category_name');


            $obj_get_category = $this->BaseModel->where('id', $category_id)->get()->toArray();

            if(!empty($obj_get_category))
            {
              $maker_id = isset($obj_get_category[0]['maker_id']) ? $obj_get_category[0]['maker_id'] : 0;
            }



            $maker_user_name = get_user_name($maker_id);

            if($request->input('status') == '0')
            {
                  $data['admin_confirm_status']   = $request->input('status');
                  $data['request_cat_update_date']    = date('Y-m-d H:i:s');
                  $data['is_active']              = 1;

                  $notification_arr['description']  = 'Requested category named as <b>'.$category_name.'</b> has been approved by admin.';

                  $notification_arr['email_template']  = 'Requested category named as <b>'.$category_name.'</b> has been approved by admin.<br><br>Thank you ..!';

            }
            else
            {
                $data['reject_reason']              = $reject_reason;
                $data['request_cat_update_date']    = date('Y-m-d H:i:s');
                $data['admin_confirm_status']       = $request->input('status');
                
                $notification_arr['description']  = 'Requested category named as <b>'.$category_name.'</b> has been rejected by admin.<br><b>Rejected Reason : </b>'.$data['reject_reason'];

                $notification_arr['email_template']  = 'Requested category named as <b>'.$category_name.'</b> has been rejected by admin.<br><b>Rejected Reason : </b>'.$data['reject_reason'].'<br><br>Thank you ..!';
            }

            $update = $this->CategoryModel->where('id',$category_id)->update($data);

            if($update)
            {
                $response['status']    = 'success';
                //$response['message']   = "Status has been changed.";
                $response['message']   = $mag;

                /*-------------------activity log*------------------------------------*/
                $arr_event['ACTION']       = 'View';
                $arr_event['MODULE_ID']    = $category_id;
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = json_encode(['id'=>$category_id,'status'=>$status]);
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);
                /*----------------------------------------------------------------------*/

                // If successfully submited then send notification and send mail             
                $admin_id = get_admin_id();  
                
                

                $maker_user_email = $this->HelperService->get_user_mail($maker_id);
               
                //dd($maker_user_name, $maker_user_email);
                
                $notification_arr['to_user_id']     = $maker_id;
                $notification_arr['from_user_id']   = $admin_id;
                // $notification_arr['description']  = 'User name : <b>'.$user_name.'</b> has requested for category named as <b>'.$notif_category_name.'</b>. Please update its status.';
                $notification_arr['title']        = 'Requested category status';
                $notification_arr['type']         = 'maker';
                $notification_arr['link']         = '';
                $this->GeneralService->save_notification($notification_arr);


                // Send requested category mail to the vendor

                $maker_email     = $this->HelperService->get_user_mail($maker_id);
                $credentials     = ['email' => $maker_email];
                $redirection_url = ""; 

                $arr_user  = get_user_by_credentials($credentials);


                /*Get site setting data from helper*/
                $arr_site_setting  = get_site_settings(['site_name','website_url']);

                $arr_built_content = ['USER_FNAME'           => $arr_user['first_name'],
                                      'APP_NAME'             => isset($arr_site_setting['site_name'])?$arr_site_setting['site_name']:'',
                                      'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
                                      'EMAIL_DESCRIPTION'    => isset($notification_arr['email_template'])?$notification_arr['email_template']:''
                                    ];


                $arr_mail_data                      = [];
                $arr_mail_data['email_template_id'] = '62';
                $arr_mail_data['arr_built_content'] = $arr_built_content;
                $arr_mail_data['arr_user']          = $arr_user;

                $email_status  = $this->EmailService->send_mail($arr_mail_data);



                $response['status']      = 'success';
                $response['description'] = 'Status has been changed.';
            }
            else
            {
                $response['status']  = 'error';
                $response['message'] = 'Error occurred while updating status.';
            }

            $response['link'] = url('/admin/request_categories');
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
            Flash::success($this->module_title.' has been deleted.');
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
         /*first check category already assign or not*/
        $count = 0;

        $user = Sentinel::check();


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
                    $arr_event                 = [];
                    $arr_event['ACTION']       = 'REMOVED';
                    $arr_event['MODULE_ID']    = $id;
                    $arr_event['MODULE_TITLE'] = $this->module_title;
                    $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'status'=>'REMOVED']);
                    $arr_event['USER_ID']      = $user->id;
                    
                    $this->save_activity($arr_event);
                /*----------------------------------------------------------------------*/
                DB::commit();
                Flash::success('Request Category(s) has been deleted.');
            }
            else
            {
               DB::rollback();
               Flash::error('Error occurred while '.$this->module_title.'deletion.');
            }

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

        $product_count = count($checked_record);

        foreach ($checked_record as $key => $record_id) 
        {            
            if($multi_action=="delete")
            {  
               $this->perform_delete(base64_decode($record_id));    
               Flash::success($product_count.' requested category(s) have been deleted.'); 
               
            } 
            elseif($multi_action=="approve")
            {
               $this->perform_approve(base64_decode($record_id),$product_count); 
               Flash::success('Requested category(s) have been approved.');
               
            }
        }
        return redirect()->back();        
  }


  public function perform_approve($id)
  {   
      $static_page = $this->BaseModel->where('id',$id)->first();
        
      if($static_page)
      {          
          {           
            $updateProduct = $this->BaseModel->where('id',$id)->update(['admin_confirm_status'=>'0','request_cat_update_date' => date('Y-m-d H:i:s'),'is_active' => 1]);
            return $updateProduct;             
          }
       
      }

      return FALSE;
  }

}
