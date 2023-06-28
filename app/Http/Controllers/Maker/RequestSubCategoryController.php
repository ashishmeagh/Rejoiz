<?php

/* Author : Harshada
   Date : 10 Nov 2020
*/

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Common\Services\LanguageService;
use App\Models\LanguageModel;
use App\Models\ProductsModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\SubCategoryModel;
use App\Models\SubCategoryTranslationModel;
use App\Models\CategoryModel;
use App\Models\SiteSettingModel;
use App\Models\CategoryTranslationModel;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;


class RequestSubCategoryController extends Controller
{
    use MultiActionTrait;

    public function __construct( SubCategoryModel $SubCategoryModel,
    	                         LanguageService $langauge,
    	                         SubCategoryTranslationModel $SubCategoryTranslationModel,
    	                         LanguageModel $language_model,
                                 ProductsModel $ProductsModel,
                                 ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                               CategoryModel $CategoryModel,
                               CategoryTranslationModel $CategoryTranslationModel,
                               ElasticSearchService $ElasticSearchService,
                               GeneralService $GeneralService,
                               EmailService $EmailService,
                               SiteSettingModel $SiteSettingModel
    	                       )
    {

      $this->arr_view_data                   =   [];
      $this->LanguageModel                   =   $language_model;
      $this->SubCategoryModel                =   $SubCategoryModel;
      $this->ProductsModel                   =   $ProductsModel;
      $this->ProductsSubCategoriesModel      =   $ProductsSubCategoriesModel;
      $this->BaseModel                       =   $this->SubCategoryModel;
      $this->GeneralService                  =   $GeneralService; 
      $this->EmailService                    =   $EmailService;  
      $this->CategoryModel                   =   $CategoryModel;
      $this->CategoryTranslationModel        =   $CategoryTranslationModel;
      $this->SubCategoryTranslationModel     =   $SubCategoryTranslationModel;
      $this->LanguageService                 =   $langauge;
      $this->ElasticSearchService            =   $ElasticSearchService;
      $this->curr_panel_slug                 =   config('app.project.maker_panel_slug');
      $this->module_view_folder              =   "maker.request_sub_category";
      $this->module_title                    =   "Request Sub Categories";
      $this->module_url_path                 =   url(config('app.project.maker_panel_slug').'/request_sub_category');      
      $this->user_base_img_path              = base_path().config('app.project.img_path.user_profile_image');
      $this->user_public_img_path            = url('/').config('app.project.img_path.user_profile_image');        
      $this->SiteSettingModel                =    $SiteSettingModel;
      $this->site_setting_obj                = $this->SiteSettingModel->first();
    
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
    }


    public function index()
    {
        /* get all categories*/
        $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();
               
        /* Sort by Alpha */ 
        usort($categories_arr, function($sort_base, $sort_compare) {

            return $sort_base['category_name'] <=> $sort_compare['category_name'];
        });    
       

        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['categories_arr']  = $categories_arr;        
        $this->arr_view_data['curr_panel_slug']  = $this->curr_panel_slug;        
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function create()
    {
       /* get all categories*/
        $arr_service_category = array();

        $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();

         /* Sort by Alpha */ 
        usort($categories_arr, function($sort_base, $sort_compare) {
            return $sort_base['category_name'] <=> $sort_compare['category_name'];
        });   

        $this->arr_view_data['categories_arr'] = $categories_arr;
        $this->arr_view_data['arr_lang']       = $this->LanguageService->get_all_language();
        $this->arr_view_data['page_title']     = 'Create '.str_singular( $this->module_title);
        $this->arr_view_data['module_title']   =  str_plural($this->module_title);
        $this->arr_view_data['module_url_path']= $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']= $this->curr_panel_slug;       
       
        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function edit($enc_id)
    {
        $id = base64_decode($enc_id);

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
  
         /* get all categories*/
        $arr_service_category = array();

        $obj_data =  $this->CategoryModel->where('is_active','1')
                                            ->whereNull('deleted_at')
                                            ->get();

        if($obj_data != FALSE)
        {
           $arr_service_category = $obj_data->toArray();
        }

        /* Sort by Alpha */ 
        usort($arr_service_category, function($sort_base, $sort_compare) {
            return $sort_base['category_name'] <=> $sort_compare['category_name'];
        });  

      
        $this->arr_view_data['edit_mode']       = TRUE;
        $this->arr_view_data['enc_id']          = $enc_id;
        $this->arr_view_data['arr_lang']        = $this->LanguageService->get_all_language();  
        $this->arr_view_data['arr_data']        = $arr_data; 
        $this->arr_view_data['page_title']      = 'Edit '.str_singular( $this->module_title);
        $this->arr_view_data['module_title']    =  str_plural($this->module_title);
        $this->arr_view_data['arr_service_category'] = $arr_service_category;        
        $this->arr_view_data['current_panel_slug'] = 'admin';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    function get_sub_category_details(Request $request)
    {
        $category_id = $request->input('category_id'); 
  
        $sub_category_details   = $this->BaseModel->getTable();        
        $prefixed_sub_category   = DB::getTablePrefix().$this->BaseModel->getTable();
        $prefixed_sub_category_trans  = DB::getTablePrefix().$this->SubCategoryTranslationModel->getTable();       
        $category_trans_tbl         = $this->CategoryTranslationModel->getTable();                
        $prefixed_category_trans_tbl= DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

        $user = Sentinel::check();
        if(isset($user))
        {
          $loggedInUserId = $user->id;
        }
        

        $obj_subcategory = DB::table($sub_category_details)
                                ->select(DB::raw($prefixed_sub_category.".id,".
                                                 $prefixed_sub_category.'.is_active,'.
                                                 $prefixed_category_trans_tbl.'.category_name,'.
                                                 $prefixed_sub_category_trans.'.subcategory_name,'.
                                                 $prefixed_sub_category.'.admin_confirm_status,'.
                                                 $prefixed_sub_category.'.reject_reason'
                                                ))
                                ->leftjoin($prefixed_sub_category_trans,$prefixed_sub_category.'.id','=',$prefixed_sub_category_trans.'.subcategory_id')

                                ->leftjoin($category_trans_tbl,$prefixed_sub_category.'.category_id','=',$prefixed_category_trans_tbl.'.category_id')

                                ->whereNull($sub_category_details.'.deleted_at')
                                ->where($prefixed_sub_category.'.admin_confirm_status','!=','0')
                                ->where($prefixed_sub_category.'.maker_id','=',$loggedInUserId)
                                ->where($prefixed_sub_category_trans.'.locale','en')
                                ->orderBy('id','DESC');
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_category']) && $arr_search_column['q_category']!="")
        {
            $search_term      = $arr_search_column['q_category'];
            $obj_subcategory = $obj_subcategory->having('category_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_sub_category']) && $arr_search_column['q_sub_category']!="")
        {
            $search_term      = $arr_search_column['q_sub_category'];
            $obj_subcategory = $obj_subcategory->where($prefixed_sub_category_trans.'.subcategory_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
           
            $obj_subcategory = $obj_subcategory->where($sub_category_details.'.admin_confirm_status',$search_term);
        }

        if(isset($category_id) && $category_id!="")
        {
            $obj_subcategory = $obj_subcategory->where($sub_category_details.'.category_id',$category_id);
        }

        return $obj_subcategory;
    }

    public function get_all_subcategory(Request $request)
    {   
        $obj_data  = $this->get_sub_category_details($request);
       
        $json_result  = Datatables::of($obj_data);
        
        $current_context = $this;
   
        /* Modifying Columns */
        $json_result =  $json_result->editColumn('enc_id',function($data) use ($current_context)
                        {
                            return  base64_encode(($data->id));
                        })

                        ->editColumn('category_name',function($data) use ($current_context)
                        {
                            return  $data->category_name;
                        })
                     
                        ->editColumn('subcategory_name ',function($data) use ($current_context)
                        {
                            return  $data->subcategory_name;
                        })

                        ->editColumn('admin_confirm_status',function($data) use ($current_context)
                        {
                            return $data->admin_confirm_status;
                        })

                        ->editColumn('build_edit_href',function($data) use ($current_context)
                        {
                            return  $current_context->module_url_path.'/edit/'.base64_encode($data->id);
                        })                        
                        ->editColumn('built_delete_href',function($data) use ($current_context)
                        {
                            return  $current_context->module_url_path.'/delete/'.base64_encode($data->id);
                        })

                       ->make(true);
  
        $build_result = $json_result->getData();

        return response()->json($build_result);
    }

    public function store(Request $request)
    {
        $is_update = false;
        // dd($request->all());  
        $arr_rules1                       = [];
        $arr_rules2                       = [];
        $notification_arr                 = [];
        $is_send_notification             = 0;

        $form_data = $request->all();
        $json_data = json_encode($form_data);

        $enc_id = base64_decode($request->input('enc_id',false));
        $enc_id = $enc_id == "" ? false : $enc_id;

        if($request->has('enc_id'))
        {
            $is_update = true;
        }

        $arr_rules = [
        	            'category' => 'required',
                        'sub_category_name_en' => 'required'                         
                      ];
        
        
        $validator = Validator::make($request->all(),$arr_rules); 

        if($validator->fails())
        {        
           $response['status']      = 'warning';
           $response['description'] = 'Form validation failed, please check all fields.';

          return response()->json($response);
        }  

        /* Check if location already exists with given translation */
        $is_duplicate = $this->BaseModel->where('category_id',$request->input('category'))
                                        ->whereHas('translations',function($query) use($request)
                                        {
                                            $query->where('locale','en')
                                                  ->where('subcategory_name',$request->input('sub_category_name_en'));
                                        });

        if($is_update == true)
        {
           $is_duplicate = $is_duplicate->where('id','<>',$enc_id);
        } 


        /* Send notification code */
        $notif_sub_category_name = $request->input('sub_category_name_en');
        $user_name = "";
        $user = Sentinel::check();
        if(isset($user))
        {
          $loggedInUserId = $user->id;
          $user_name      = $user->first_name.' '.$user->last_name;
        }

        $notification_arr['description'] = '<b>'.$user_name.'</b> has requested for sub-category named as <b>'.$notif_sub_category_name.'</b>. Please update its status.';

        if($is_update == true)
        {
                $is_duplicate = $is_duplicate->where('id','<>',$enc_id);
                $obj_get_sub_category = $this->BaseModel->where('id', $enc_id)->get();
                if(!empty($obj_get_sub_category)){
                $previos_sub_category_name = isset($obj_get_sub_category[0]['subcategory_name']) ? $obj_get_sub_category[0]['subcategory_name'] : "";
                $previos_category_id = isset($obj_get_sub_category[0]['category_id']) ? $obj_get_sub_category[0]['category_id'] : "";
                }


                if(($previos_sub_category_name != $notif_sub_category_name) || $previos_category_id != $form_data['category']) {

                  if($previos_sub_category_name != $notif_sub_category_name){
                    $notification_arr['description']  = '<b>'.$user_name.'</b> has updated requested sub-category from <b>'.$previos_sub_category_name.'</b> to <b>'.$notif_sub_category_name.'</b>. Please update its status.';
                    } else if($previos_category_id != $form_data['category']){
                        $notification_arr['description']  = '<b>'.$user_name.'</b> has updated category of subcategory named as <b>'.$notif_sub_category_name.'</b>. Please update its status.';
                      }

                } else {
                  $is_send_notification = 1;
                }

        } 

        $does_exists = $is_duplicate->count()>0;
        
        if($does_exists)
        {   
           $response['status']      = 'warning';
           $response['description'] = str_singular($this->module_title).' already exists.';

           return response()->json($response);
        }   

        $entity = SubCategoryModel::firstOrNew(['id' => $enc_id]);  

         /* Insert into  Table */
        if(isset($form_data['sub_category_status']) && !empty($form_data['sub_category_status']))
        {
           $category_status = $form_data['sub_category_status'];
        }
        else
        {
           $category_status = '0';
        }

        $admin_confirm_status               = '2';
        $request_cat_update_date            = date('Y-m-d H:i:s');
        $entity->category_id                = $form_data['category'];
        $entity->is_active                  = $category_status;
        if($is_send_notification == 0){
          $entity->admin_confirm_status       = $admin_confirm_status;
        }
        $entity->maker_id                   = $loggedInUserId;
        $entity->request_cat_update_date    = $request_cat_update_date;
        $result = $entity->save();
        $last_inserted_id = $entity->id;

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
                    $sub_category_name = $form_data['sub_category_name_'.$lang['locale']];
                   
                    if((isset($sub_category_name) && $sub_category_name != ''))
                    { 
                      
                        /* Get Existing Language Entry */
                        $translation = $entity->getTranslation($lang['locale'],false);  

                        if($translation)
                        {
                            $translation->subcategory_id   = $entity->id;
                            $translation->subcategory_name  = $sub_category_name;
                            $translation->subcategory_slug  = str_slug($sub_category_name,'_');
                            $translation->save();    
                        }  
                        else
                        {
                            /* Create New Language Entry  */
                            $translation = $entity->getNewTranslation($lang['locale']);
                            $translation->subcategory_id   = $entity->id;
                            $translation->subcategory_name  = $sub_category_name;
                            $translation->subcategory_slug  = str_slug($sub_category_name,'_');
                            $translation->save();
                        }                       
                    }
                }
                //foreach 
                /*-------------------------------------------------------
                   |   Activity log Event
                --------------------------------------------------------*/
                $arr_event                 = [];
                if($is_update == false)
                {
                    $arr_event['ACTION']       = 'ADD';
                    $arr_event['MODULE_ID']    =  $entity->id;
                }
                else
                {
                    $arr_event['ACTION']   = 'EDIT';
                    $arr_event['MODULE_ID']= $enc_id;
                }

                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = $json_data;
                $arr_event['USER_ID']      = $loggedInUserId;

                $this->save_activity($arr_event);

               /*----------------------------------------------------------------------*/
                      
                if($is_update == false)
                {
                    if($entity->id)
                    {
                        //$response['link'] = $this->module_url_path.'/edit/'.base64_encode($entity->id);
                       $response['link'] = url('/vendor/request_sub_category');
                    }
                }
                else
                {
                   //$response['link'] = $this->module_url_path.'/edit/'.base64_encode($enc_id);
                  $response['link'] = url('/vendor/request_sub_category');
                }
            } //if


            // If successfully submited then send notification and send mail 
            $notif_category_name = $request->input('sub_category_name_en');
            $admin_id = get_admin_id();
            $user = Sentinel::check();
            $user_name = "";
            if(isset($user))
            {
              $loggedInUserId = $user->id;
              $user_name      = $user->first_name.' '.$user->last_name;
            }


            $view_href = url('/').'/admin/request_sub_categories/view/'.base64_encode($last_inserted_id);
            
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $admin_id;
            // $notification_arr['description']  = 'User name : <b>'.$user_name.'</b> has requested for sub-category named as <b>'.$notif_category_name.'<b>. Please update its status.';
            $notification_arr['title']        = 'Request Sub-category';
            $notification_arr['type']         = 'admin';
            $notification_arr['link']         = $view_href;
            //dd($notification_arr);

            // If previous category and current subcategory is not same then only send notification
            if($is_send_notification == 0){
              $this->GeneralService->save_notification($notification_arr);
            }

            // Send mail
            $arr_user = $user->toArray();


            $admin_email_id = get_admin_email();

            $credentials = ['email' => $admin_email_id];
            $arr_admin_user    = get_user_by_credentials($credentials);

            $redirection_url = "";              
            $arr_built_content = ['USER_FNAME'           => 'Admin',
                                  'APP_NAME'             => isset($this->site_setting_arr['site_name'])?$this->site_setting_arr['site_name']:'',
                                  'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
                                  'EMAIL_DESCRIPTION'    => isset($notification_arr['description'])?$notification_arr['description']:''
                                 ];

            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '79';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['arr_user']          = $arr_admin_user;

            // If previous category and current subcategory is not same then only send mail
            if($is_send_notification == 0){
             
              $email_status  = $this->EmailService->send_mail($arr_mail_data);
            }


               $response['status']      = 'success';
               $response['description'] = str_singular($this->module_title).' '. ' has been saved.';           
        }

         else
         {
            $response['status']      = 'error';
            $response['description'] = 'Error occurred while save '.str_singular($this->module_title);
         }

        return response()->json($response);
    }

    public function status_update(Request $request)
    {   
        $msg ='';
        $category_id = base64_decode($request->input('category_id'));
      
        $status      = $request->input('status');

        if($status   == 'activate')
        {   
            
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {  
            
            $is_active = '0';
        }

        $data['is_active'] = $is_active;

        $category_id = isset($category_id)?intval($category_id):0;
        $sub_cat_data = $this->SubCategoryModel->where('id',$category_id)->get();

        if(isset($sub_cat_data))
        {  
            $sub_cat_arr = $sub_cat_data->toArray();
            $cat_id = isset($sub_cat_arr[0]['category_id'])?$sub_cat_arr[0]['category_id']:0;
            if(isset($cat_id))
            {
                $cat_obj = $this->CategoryModel->where('id',$cat_id)->get();
                if(isset($cat_obj))
                {
                    $cat_arr = $cat_obj->toArray();
                    
                    if($cat_arr[0]['is_active']!=1)
                    {   $cat_name = isset($cat_arr[0]['category_name'])?$cat_arr[0]['category_name']:'';
                        
                        $response =[];
                        $response['status'] = 'warning';
                        $response['message'] = $cat_name.' is deactivated can not modify the status of subcategory';

                        return response()->json($response);
                     }
                     else
                     {
                        $sub_cat_id =  $sub_cat_arr[0]['category_id'];
                        $sub_cat_id = isset($sub_cat_id)?intval($sub_cat_id):0;

                        try
                        {


                            if($is_active == '0')
                            {
                               
                                $msg = "Subcategory has been deactivated.";

                                /*Deactive all product of this subcategory*/
                                $product_details = [];
                                $product_details = $this->ProductsModel->whereHas('productSubCategories',function($q) use ($sub_cat_arr)
                                {
                                    $q->where('sub_category_id',$sub_cat_arr[0]['id']);

                                })->get()->toArray();
                                
                                if (count($product_details) > 0) {

                                    foreach ($product_details as $key => $product) {

                                       $update_product_status = $this->ProductsModel->where('id',$product['id'])->update(['is_active'=>0]);
                                    }
                                    
                                    
                                }


                            $this->ElasticSearchService->deactivate_sub_category_product($sub_cat_id,$category_id);
                            }
                            elseif ($is_active=='1')
                            {
                                $msg = "Subcategory has been activated.";

                                $product_details = [];
                                $product_details = $this->ProductsModel->whereHas('productSubCategories',function($qry) use ($sub_cat_arr){

                                    $qry->where('sub_category_id',$sub_cat_arr[0]['id']);
                                })->where('previous_category_status','1')->get()->toArray();

                                if (count($product_details) > 0) {

                                    foreach ($product_details as $key => $product) {

                                       $update_product_status = $this->ProductsModel->where('id',$product['id'])->update(['is_active'=>1]);
                                    }
                                    
                                    
                                }
                                 

                                $this->ElasticSearchService->activate_sub_category_product($sub_cat_id,$category_id);
                            }
                                DB::beginTransaction();
                                $update = $this->SubCategoryModel->where('id',$category_id)->update($data);
                                DB::commit();
                                
                                $response            = [];
                                $response['status']  = 'success';
                                $response['message'] = $msg;
                                
                        }
                        catch(\Exception $e)
                        {
                            $response            = [];
                            $response['status']  = 'Error';
                            $response['message'] = $e->getMessage();
                        }



                        return response()->json($response);

                     }
            
                }

            }
        }
        
    
    }


    public function multi_status_update($subcat_id = false,$status=false)
    {   
       
        $category_id    = isset($subcat_id)?intval($subcat_id):0;
        $loggedInUserId = 0;
      
        $status = isset($status)?$status:'';

        $user = Sentinel::check();
        if(isset($user))
        {
          $loggedInUserId = $user->id;
        }


       // dd($category_id,$status);
        if($status == 'activate')
        {
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {
            $is_active = '0';
        }

        $data['is_active'] = $is_active;

        $category_id = isset($category_id)?intval($category_id):0;
        $sub_cat_data = $this->SubCategoryModel->where('id',$category_id)->get();
        if(isset($sub_cat_data))
        {  
            $sub_cat_arr = $sub_cat_data->toArray();
            $cat_id = isset($sub_cat_arr[0]['category_id'])?$sub_cat_arr[0]['category_id']:0;
            if(isset($cat_id))
            {
                $cat_obj = $this->CategoryModel->where('id',$cat_id)->get();
                if(isset($cat_obj))
                {
                    $cat_arr = $cat_obj->toArray();
                    
                    if($cat_arr[0]['is_active']!=1)
                    {   $cat_name = isset($cat_arr[0]['category_name'])?$cat_arr[0]['category_name']:'';
                       Flash::error('Category '.str_singular($cat_name.' is Deactivated can not modify the status of subcategory'));
                        return redirect()->back();
                     }
            
                }

            }
            $sub_cat_id =  $sub_cat_arr[0]['category_id'];
            $sub_cat_id = isset($sub_cat_id)?intval($sub_cat_id):0;
        }
        
    try
    {
        if($is_active == '0')
        {
         $this->ElasticSearchService->deactivate_sub_category_product($sub_cat_id,$category_id);
        }
        elseif ($is_active=='1')
        {
          $this->ElasticSearchService->activate_sub_category_product($sub_cat_id,$category_id);
        }

        DB::beginTransaction();
        $update = $this->SubCategoryModel->where('id',$category_id)->update($data);
        DB::commit();
    }
    catch(\Exception $e)
        {
            DB::rollback();
            return false;
        }
        
        if($update)
        {
            $response['status']  = 'success';
            $response['message'] = 'Status has been changed.';



            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $category_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id' => $category_id , 'status'=>$status]);
            $arr_event['USER_ID']      = $loggedInUserId;


            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
        }
        else
        {
            $response['status']  = 'error';
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

    public function perform_delete($id)
    {

        $is_subcategory_having_products = $this->ProductsSubCategoriesModel->where('sub_category_id',$id)->count();

        if($is_subcategory_having_products > 0)
        {
          Flash::error("Subcategory can't be deleted, First you have to delete this subcategory
              from product.");
        } 

        else
        {    


          // If successfully submited then send notification and send mail 
            //$notif_category_name = $request->input('sub_category_name_en');
            $admin_id = get_admin_id();
            $user = Sentinel::check();
            $user_name = "";
            if(isset($user))
            {
              $loggedInUserId = $user->id;
              $user_name      = $user->first_name.' '.$user->last_name;
            }

            $obj_get_subcategory = $this->BaseModel->where('id', $id)->get()->toArray();
            //dd($obj_get_subcategory);
            if(!empty($obj_get_subcategory)){
                    $notif_sub_category_name = isset($obj_get_subcategory[0]['subcategory_name']) ? $obj_get_subcategory[0]['subcategory_name'] : "";
            }


            $view_href = '';
            
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $admin_id;
            $notification_arr['description']  = '<b>'.$user_name.'</b> has deleted sub-category named as <b>'.$notif_sub_category_name.'<b>. ';
            $notification_arr['title']        = 'Request Sub-category Deleted';
            $notification_arr['type']         = 'admin';
            $notification_arr['link']         = $view_href;
            //dd($notification_arr);
            $this->GeneralService->save_notification($notification_arr);

            // Send mail
           
            $admin_mail = get_admin_email();
            
            $credentials = ['email' => $admin_mail];
      
            $arr_user = get_user_by_credentials($credentials);

            $redirection_url = "";              
            $arr_built_content = ['USER_FNAME'           => 'Admin',
                                  'APP_NAME'             => isset($this->site_setting_arr['site_name'])?$this->site_setting_arr['site_name']:'',
                                  'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
                                  'EMAIL_DESCRIPTION'    => isset($notification_arr['description'])?$notification_arr['description']:''
                                 ];

            $arr_mail_data                          = [];
            $arr_mail_data['email_template_id']     = '80';
            $arr_mail_data['arr_built_content']     = $arr_built_content;
            $arr_mail_data['arr_user']              = $arr_user;

            $email_status  = $this->EmailService->send_mail($arr_mail_data);

            $delete = $this->BaseModel->where('id',$id)->delete();

    
        
        if($delete)
        {
            $delete_service_category =  $this->SubCategoryTranslationModel->where('subcategory_id',$id)->delete();

            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
                $arr_event                 = [];
                $arr_event['ACTION']       = 'REMOVED';
                $arr_event['MODULE_ID']    = $id;
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'status'=>'REMOVED']);
                $arr_event['USER_ID']      = $loggedInUserId;


                $this->save_activity($arr_event);
            /*----------------------------------------------------------------------*/

            if($delete_service_category)
            {
                 Flash::success('Request sub-category(s) has been deleted.');
            }
            else
            {
                Flash::error('Error occurred while '.$this->module_title.' deletion.');
            }
           
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' deletion.');
        }

     }   

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
