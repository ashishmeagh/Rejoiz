<?php


namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Common\Services\LanguageService;
use App\Models\LanguageModel;
use App\Models\ProductsModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\SubCategoryModel;
use App\Models\ThirdSubCategoryModel;
use App\Models\ThirdSubCategoryTranslationModel;
use App\Models\SubCategoryTranslationModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\FourthSubCategoryModel;
use App\Models\FourthSubCategoryTranslationModel;


use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;

class ThirdSubCategoryController extends Controller
{
    use MultiActionTrait;

    public function __construct( ThirdSubCategoryModel $ThirdSubCategoryModel,SubCategoryModel $SubCategoryModel,
    	                         LanguageService $langauge,
                                 SubCategoryTranslationModel $SubCategoryTranslationModel,
                                 ThirdSubCategoryTranslationModel $ThirdSubCategoryTranslationModel,
    	                         LanguageModel $language_model,
                                 ProductsModel $ProductsModel,
                                 ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                 CategoryModel $CategoryModel,
                                 CategoryTranslationModel $CategoryTranslationModel,
                                 FourthSubCategoryModel $FourthSubCategoryModel,
                                 FourthSubCategoryTranslationModel $FourthSubCategoryTranslationModel

    	                       )
    {

      $this->arr_view_data                   =   [];
      $this->LanguageModel                   =   $language_model;
      $this->ThirdSubCategoryModel           =   $ThirdSubCategoryModel;
      $this->FourthSubCategoryModel           =   $FourthSubCategoryModel;
      $this->FourthSubCategoryTranslationModel =  $FourthSubCategoryTranslationModel;
      $this->SubCategoryModel                =   $SubCategoryModel;
      $this->ProductsModel                   =   $ProductsModel;
      $this->ProductsSubCategoriesModel      =   $ProductsSubCategoriesModel;
      $this->ThirdSubCategoryTranslationModel = $ThirdSubCategoryTranslationModel;
      $this->BaseModel                       =   $this->ThirdSubCategoryModel;
      $this->CategoryModel                   =   $CategoryModel;
      $this->CategoryTranslationModel        =   $CategoryTranslationModel;
      $this->SubCategoryTranslationModel     =   $SubCategoryTranslationModel;
      $this->LanguageService                 =   $langauge;
      
      $this->curr_panel_slug                 =   config('app.project.admin_panel_slug');
      $this->module_view_folder              =   "admin.third_sub_category";
      $this->module_title                    =   "Third Level Categories";
      $this->module_url_path                 =   url(config('app.project.admin_panel_slug').'/third_sub_category');      
      $this->user_base_img_path              = base_path().config('app.project.img_path.user_profile_image');
      $this->user_public_img_path            = url('/').config('app.project.img_path.user_profile_image');        
    }

    public function index()
    {
        /* get all categories*/
        $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();

        /* get all sub categories*/
        $sub_categories_arr =  $this->SubCategoryModel->where('is_active','1')->get()->toArray();
              
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['categories_arr']  = $categories_arr;        
        $this->arr_view_data['curr_panel_slug']  = $this->curr_panel_slug;        
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    function get_category_details(Request $request)
    {
        $category_id = $request->input('category_id'); 
        
        $third_sub_category_details   = $this->BaseModel->getTable();        
        $prefixed_third_sub_category   = DB::getTablePrefix().$this->BaseModel->getTable();
        $prefixed_sub_category_trans  = DB::getTablePrefix().$this->SubCategoryTranslationModel->getTable();       
        $category_trans_tbl         = $this->CategoryTranslationModel->getTable();                
        $prefixed_category_trans_tbl= DB::getTablePrefix().$this->CategoryTranslationModel->getTable();
        $prefixed_third_category_trans_tbl= DB::getTablePrefix().$this->ThirdSubCategoryTranslationModel->getTable();
        

                $obj_subcategory = DB::table($third_sub_category_details)
                ->select(DB::raw($prefixed_third_sub_category.".id,".
                                    $prefixed_third_sub_category.'.is_active,'.
                                    $prefixed_category_trans_tbl.'.category_name,'.
                                    $prefixed_sub_category_trans.'.subcategory_name,'.
                                    $prefixed_third_category_trans_tbl.'.third_sub_category_name'
))
                ->leftjoin($prefixed_sub_category_trans,$prefixed_third_sub_category.'.sub_category_id','=',$prefixed_sub_category_trans.'.subcategory_id')

                ->leftjoin($prefixed_third_category_trans_tbl,$prefixed_third_sub_category.'.id','=',$prefixed_third_category_trans_tbl.'.third_sub_category_id')

                ->leftjoin($category_trans_tbl,$prefixed_third_sub_category.'.category_id','=',$prefixed_category_trans_tbl.'.category_id')

                ->whereNull($third_sub_category_details.'.deleted_at')
                ->where($prefixed_sub_category_trans.'.locale','en')
                ->where($third_sub_category_details.'.admin_confirm_status','=','0')
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

        if(isset($arr_search_column['q_third_sub_category']) && $arr_search_column['q_third_sub_category']!="")
        {
            $search_term      = $arr_search_column['q_third_sub_category'];
            $obj_subcategory = $obj_subcategory->where($prefixed_third_category_trans_tbl.'.third_sub_category_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
           
            $obj_subcategory = $obj_subcategory->where($third_sub_category_details.'.is_active',$search_term);
        }

        if(isset($category_id) && $category_id!="")
        {
            $obj_subcategory = $obj_subcategory->where($third_sub_category_details.'.category_id',$category_id);
        }

        return $obj_subcategory;
    }

    public function get_sub_categories($category_id = 0)
    {
    	$sub_categories_arr = $this->SubCategoryModel->where('is_active',1)
                                        ->where('category_id',$category_id)
                                        ->get()->toArray();

                                        
    	$response['sub_categories_arr'] = $sub_categories_arr;

    	if($sub_categories_arr)
    	{
    		$response['status'] = 'SUCCESS';    	
    	}
    	else
    	{
    		$response['status'] = 'FAILURE';    		
    	}
    	
    	return response()->json($response);
    }

    public function get_all_third_subcategory(Request $request)
    {   
        $obj_data  = $this->get_category_details($request);
        
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

                        ->editColumn('status',function($data) use ($current_context)
                        {
                            $button = '';
                            $url    = '';    
                            $msg    = '';

                            if(isset($data->is_active) && sizeof($data->is_active)>0)
                            {
                                if($data->is_active==1)
                                {
                                   
                                    $msg = "return confirm_action(this,event,'Are you sure? Do you want to deactivate this record.');";

                                   $button = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)" action="deactivate"/>';
 
                                    return $button;
                                }
                                elseif($data->is_active==0)
                                {
                                    $msg = "return confirm_action(this,event,'Are you sure? Do you want to activate this record.');";

                                    $button = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)" action="activate"/>';


                                    return $button;   
                                }
                            }
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

    public function create()
    {
       /* get all categories*/
        $arr_service_category = array();
        $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();
        $sub_categories_arr =  $this->SubCategoryModel->where('is_active','1')->get();
        
        
         /* Sort by Alpha */ 
        usort($categories_arr, function($sort_base, $sort_compare) {
            return $sort_base['category_name'] <=> $sort_compare['category_name'];
        });
        
        $this->arr_view_data['categories_arr'] = $categories_arr;
        $this->arr_view_data['sub_categories_arr'] = isset($sub_categories_arr)?$sub_categories_arr:[];
        $this->arr_view_data['arr_lang']       = $this->LanguageService->get_all_language();
        $this->arr_view_data['page_title']     = 'Create '.str_singular( $this->module_title);
        $this->arr_view_data['module_title']   =  str_plural($this->module_title);
        $this->arr_view_data['module_url_path']= $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']= $this->curr_panel_slug;       
       
        return view($this->module_view_folder.'.create',$this->arr_view_data);
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

    public function store(Request $request)
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

        $arr_rules = [
        	            'first_level_category' => 'required',
                        'second_level_category' => 'required',
                        'third_level_category_name_en' => 'required'                         
                      ];
        
        
        $validator = Validator::make($request->all(),$arr_rules); 

        if($validator->fails())
        {        
           $response['status']      = 'warning';
           $response['description'] = 'Form validation failed, please check all fields.';

          return response()->json($response);
        }  

        /* Check if location already exists with given translation */
        $is_duplicate = $this->BaseModel->where('category_id',$request->input('first_level_category'))
        ->where('sub_category_id',$request->input('second_level_category'))
                                        ->whereHas('translations',function($query) use($request)
                                        {
                                            $query->where('locale','en')
                                                  
                                                  ->where('third_sub_category_name',$request->input('third_level_category_name_en'));
                                        });
                                        
        if($is_update == true)
        {
           $is_duplicate = $is_duplicate->where('id','<>',$enc_id);
        } 

        $does_exists = $is_duplicate->count()>0;
        
        if($does_exists)
        {   
           $response['status']      = 'warning';
           $response['description'] = str_singular($this->module_title).' already exists.';

           return response()->json($response);
        }   

        $entity = ThirdSubCategoryModel::firstOrNew(['id' => $enc_id]);  

         /* Insert into  Table */
        if(isset($form_data['third_level_category_status']) && !empty($form_data['third_level_category_status']))
        {
           $category_status = $form_data['third_level_category_status'];
        }
        else
        {
           $category_status = '0';
        }
        
        $entity->category_id = $form_data['first_level_category'];
        $entity->sub_category_id = $form_data['second_level_category'];
        $entity->is_active   = $category_status;

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
                    $third_level_category_name = $form_data['third_level_category_name_'.$lang['locale']];
                   
                    if((isset($third_level_category_name) && $third_level_category_name != ''))
                    { 
                      
                        /* Get Existing Language Entry */
                        $translation = $entity->getTranslation($lang['locale'],false);  

                        if($translation)
                        {
                            $translation->third_sub_category_id   = $entity->id;
                            $translation->third_sub_category_name  = $third_level_category_name;
                            $translation->third_sub_category_slug  = str_slug($third_level_category_name,'_');
                            $translation->save();    
                        }  
                        else
                        {
                            /* Create New Language Entry  */
                            $translation = $entity->getNewTranslation($lang['locale']);
                            $translation->third_sub_category_id   = $entity->id;
                            $translation->third_sub_category_name  = $third_level_category_name;
                            $translation->third_sub_category_slug  = str_slug($third_level_category_name,'_');
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
                    $arr_event['ACTION']   = 'EDIT';
                    $arr_event['MODULE_ID']= $enc_id;
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
            } //if
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
        $arr_service_subcategory = array();

        $obj_data =  $this->CategoryModel->where('is_active','1')
                                            ->whereNull('deleted_at')
                                            ->get();

        $subcategory_obj_data =  $this->SubCategoryModel->where('is_active','1')
        ->whereNull('deleted_at')
        ->get();

        if($obj_data != FALSE)
        {
           $arr_service_category = $obj_data->toArray();
        }

        if($subcategory_obj_data != FALSE)
        {
          
           $arr_service_subcategory =  $this->SubCategoryModel->where('is_active','1')->get();
           
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
        $this->arr_view_data['arr_service_subcategory'] = $arr_service_subcategory;        
        $this->arr_view_data['current_panel_slug'] = 'admin';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
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

    public function status_update(Request $request)
    {   
       
        $msg ='';
        $third_sub_category_id = base64_decode($request->input('category_id'));
        
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

        $third_sub_category_id = isset($third_sub_category_id)?intval($third_sub_category_id):0;
        $third_sub_cat_data = $this->ThirdSubCategoryModel->where('id',$third_sub_category_id)->get();
        // dd($third_sub_cat_data);
        if(isset($third_sub_cat_data))
        {  
            $third_sub_cat_arr = $third_sub_cat_data->toArray();
            
            $cat_id = isset($third_sub_cat_arr[0]['sub_category_id'])?$third_sub_cat_arr[0]['sub_category_id']:0;
            if(isset($cat_id))
            {  
                $cat_obj = $this->SubCategoryModel->where('id',$cat_id)->get();
                 
                if(isset($cat_obj))
                {
                    $cat_arr = $cat_obj->toArray();
                    
                    if($cat_arr[0]['is_active']!=1)
                    {   $cat_name = isset($cat_arr[0]['subcategory_name'])?$cat_arr[0]['subcategory_name']:'';
                        
                        $response =[];
                        $response['status'] = 'warning';
                        $response['message'] = $cat_name.' is deactivated can not modify the status of third level category';


                        return response()->json($response);
                     }
                     else
                     {
                        $third_sub_cat_id =  $third_sub_cat_arr[0]['sub_category_id'];
                        $third_sub_cat_id = isset($third_sub_cat_id)?intval($third_sub_cat_id):0;

                        try
                        {   if($is_active == '0')
                            {
                               
                                $msg = "Third Level category has been deactivated.";

                                     /*Deactive all product of this subcategory*/
                                $product_details = [];
                                $product_details = $this->ProductsModel
                                        ->where('is_deleted', 0)
                                        ->where('product_complete_status',4)
                                        ->whereHas('productSubCategories',function($q) use ($third_sub_cat_arr)
                                        {
                                            $q->where('third_sub_category_id',$third_sub_cat_arr[0]['id']);

                                        })
                                        ->whereHas('userDetails',function($q)
                                        {
                                            return $q->where('status',1)->where('is_approved',1);
                                        })->get();
                                
                                if (count($product_details) > 0) {

                                    $is_active = "warning";

                                    $msg = "Third level category contains active product(s), can not change third level category status";

                                    $response            = [];
                                    $response['status']  = 'warning';
                                    $response['message'] = $msg;
                                    $response['type']    = "warning";
                                    $response['title']   = "warning";
                                }
                            }
                            elseif ($is_active=='1')
                            {
                                $msg = "Third level category has been activated.";

                                $product_details = [];
                                $product_details = $this->ProductsModel
                                        ->where('is_deleted', 0)
                                        ->where('product_complete_status',4)
                                        ->whereHas('productSubCategories',function($qry) use ($third_sub_cat_arr)
                                        {

                                            $qry->where('third_sub_category_id',$third_sub_cat_arr[0]['id']);
                                        })
                                        ->whereHas('userDetails',function($q)
                                        {
                                            return $q->where('status',1)->where('is_approved',1);
                                        })->get();
                            }
                            
                            if(($is_active == 1 || $is_active == 0) && $is_active != "warning")
                            {
                               
                                DB::beginTransaction();
                                $update = $this->ThirdSubCategoryModel->where('id',$third_sub_category_id)->update($data);
                                DB::commit();
                            
                                $response            = [];
                                $response['status']  = 'success';
                                $response['message'] = $msg;
                            }

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

        $user = Sentinel::check();

        $category_id = isset($subcat_id)?intval($subcat_id):0;
      
        $status = isset($status)?$status:'';
        if($status == 'activate')
        {
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {
            $is_active = '0';
        }

        $data['is_active'] = $is_active;

        $category_id  = isset($category_id)?intval($category_id):0;
        $sub_cat_data = $this->ThirdSubCategoryModel->where('id',$category_id)->get();
        if(isset($sub_cat_data))
        {  
            $sub_cat_arr = $sub_cat_data->toArray();
            $cat_id = isset($sub_cat_arr[0]['sub_category_id'])?$sub_cat_arr[0]['sub_category_id']:0;
            if(isset($cat_id))
            {
                $cat_obj = $this->SubCategoryModel->where('id',$cat_id)->get();
                if(isset($cat_obj))
                {
                    $cat_arr = $cat_obj->toArray();
                    
                    if($cat_arr[0]['is_active']!=1)
                    {   $cat_name = isset($cat_arr[0]['subcategory_name'])?$cat_arr[0]['subcategory_name']:'';
                       Flash::error('Category '.str_singular($cat_name.' is Deactivated can not modify the status of subcategory'));
                        return redirect()->back();
                     }
            
                }

            }
            $sub_cat_id =  $sub_cat_arr[0]['sub_category_id'];
            $sub_cat_id = isset($sub_cat_id)?intval($sub_cat_id):0;
        }
        
    try
    {
        

        $product_details = $this->ProductsModel
                        ->where('is_deleted', 0)
                        ->where('product_complete_status',4)
                        ->whereHas('productSubCategories',function($q) use ($sub_cat_arr,$category_id)
                        {
                            $q->where('third_sub_category_id',$category_id);

                        })
                        ->whereHas('userDetails',function($q)
                        {
                            return $q->where('status',1)->where('is_approved',1);
                        })
                        ->get();
        if (count($product_details) == 0) 
        {
            DB::beginTransaction();
            
            $update = $this->ThirdSubCategoryModel->where('id',$category_id)->update($data);
            DB::commit();
        }
        else
        {   
           $message = "Third level category contains active product(s) can not change status";
            Flash::success($message);
            return redirect()->back();
        }

    }
    catch(\Exception $e)
        {
            DB::rollback();
         
            $response['status']  = 'error';
            $response['message'] = 'Error occurred while updating status.';
            return response()->json($response);

        }
        
            $response['status']  = 'success';
            $response['message'] = 'Status has been changed.';



            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $category_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id' => $category_id , 'status'=>$status]);
            $arr_event['USER_ID']      = $user->id;

            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
        
        
        return response()->json($response);
    }

    /*----------Abbss code-------------------*/
    public function perform_activate($id_arr)
    {
        
        foreach ($id_arr as $key => $record_id) 
        {  
            $category_id = isset($record_id)?base64_decode($record_id):0;
            
            $update = $this->ThirdSubCategoryModel->where('id',$category_id)->update(['is_active'=>'1']);
            //dd($update);
        }
        return $update;
    }

    public function perform_deactivate($id_arr)
    {
        
        foreach ($id_arr as $key => $record_id) 
        {  
            $category_id = isset($record_id)?base64_decode($record_id):0;
            
            $update = $this->ThirdSubCategoryModel->where('id',$category_id)->update(['is_active'=>'0']);
            //dd($update);
        }
        return $update;
    }

    public function perform_multi_delete($id_arr)
    {
         foreach ($id_arr as $key => $record_id) 
        {  
            $category_id = isset($record_id)?base64_decode($record_id):0;
            $cat_count_in_sub_cat = $this->FourthSubCategoryModel->where('third_sub_category_id',$category_id)->count();
            // $cat_count_in_product = $this->ProductsModel->where('category_id',$category_id)->count();
            if($cat_count_in_sub_cat > 0)
            {
                Flash::error("Category can't be deleted,first you have to delete this category
                from subcategory,product and representative.");
                return redirect()->back();
            }else{
            
             $delete = $this->ThirdSubCategoryModel->where('id',$category_id)->delete();
            }
            //dd($update);
        }
        return $delete;
       
    }

    public function perform_delete($id)
    {
        $user = Sentinel::check();

        $is_subcategory_having_products = $this->ProductsSubCategoriesModel->where('third_sub_category_id',$id)->count();
        $cat_count_in_sub_cat = $this->FourthSubCategoryModel->where('third_sub_category_id',$id)->count();
        if($is_subcategory_having_products > 0 || $cat_count_in_sub_cat > 0)
        {
          Flash::error("Subcategory can't be deleted, First you have to delete this subcategory
              from product.");
        } 

        else
        {    
          $delete = $this->ThirdSubCategoryModel->where('id',$id)->delete();

    
        
        if($delete)
        {
            $delete_service_category =  $this->ThirdSubCategoryTranslationModel->where('third_sub_category_id',$id)->delete();

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

            if($delete_service_category)
            {
                 Flash::success($this->module_title.' has been deleted.');
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
        $status         = $request->input('status');

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
    foreach ($checked_record as $subcat_id)
    {
            # code...
        $subcat_id =  base64_decode($subcat_id);
        $user = Sentinel::check();

        $category_id = isset($subcat_id)?intval($subcat_id):0;
        $is_active = '0';
        

        $data['is_active'] = $is_active;

        $category_id  = isset($category_id)?intval($category_id):0;
        $sub_cat_data = $this->ThirdSubCategoryModel->where('id',$category_id)->get();
        if(isset($sub_cat_data))
        {  
            $sub_cat_arr = $sub_cat_data->toArray();
            $cat_id = isset($sub_cat_arr[0]['sub_category_id'])?$sub_cat_arr[0]['sub_category_id']:0;
            if(isset($cat_id))
            {
                $cat_obj = $this->SubCategoryModel->where('id',$cat_id)->get();
                if(isset($cat_obj))
                {
                    $cat_arr = $cat_obj->toArray();
                    
                    if($cat_arr[0]['is_active']!=1)
                    {   $cat_name = isset($cat_arr[0]['subcategory_name'])?$cat_arr[0]['subcategory_name']:'';
                       Flash::error('Category '.str_singular($cat_name.' is Deactivated can not modify the status of subcategory'));
                        return redirect()->back();
                     }
            
                }

            }
            $sub_cat_id =  $sub_cat_arr[0]['sub_category_id'];
            $sub_cat_id = isset($sub_cat_id)?intval($sub_cat_id):0;
        }
        
    try
    {
        
        $product_details = $this->ProductsModel
                        ->where('is_deleted', 0)
                        ->where('product_complete_status',4)
                        ->whereHas('productSubCategories',function($q) use ($sub_cat_arr,$category_id)
                        {
                            $q->where('third_sub_category_id',$category_id);

                        })
                        ->whereHas('userDetails',function($q)
                        {
                            return $q->where('status',1)->where('is_approved',1);
                        })
                        ->get();
        if (count($product_details) == 0) 
        {
            DB::beginTransaction();
            
            $update = $this->ThirdSubCategoryModel->where('id',$category_id)->update($data);
            DB::commit();
            
            array_push($deactive_id_arr, $sub_cat_arr[0]['third_sub_category_name']);
        }
        else
        {   
           $message = "Third level category contains active product(s) can not change status";
           array_push($active_id_arr, $sub_cat_arr[0]['third_sub_category_name']);
            
        }

    }
    catch(\Exception $e)
        {
            DB::rollback();
         
            $response['status']  = 'error';
            $response['message'] = 'Error occurred while updating status.';
            return response()->json($response);

        }
        
           
        
        }
           
            $active_message   = '';
            $deactive_message = '';
            $active_message   =  implode(',', $active_id_arr);
            $deactive_message =  implode(',', $deactive_id_arr);

            if(count($active_id_arr)>0)
            {   $type = "error";
                $message = "Cannot perform this action third level categories contains active products";
                if(count($active_id_arr)==1)
                {
                    $message = "Cannot perform action third level category contains active products";
                }
            }
            if(count($deactive_id_arr)>0)
            {   $type = "success";
                $message = "Third level categories deactivated successfully";
                if(count($deactive_id_arr)==1)
                {
                    $message = "Third level category deactivated successfully";
                }
            }

            if(count($active_id_arr)>0 && count($deactive_id_arr)>0)
            {
                $type = "success";
                $active_message  = implode(',', $active_id_arr);
                $deactive_message = implode(',', $deactive_id_arr);
                $message = "List of deactivated third level categories: ".$deactive_message.",and some third level categories can not be deactivated because they contains active products, following is list of active third level categories: ".$active_message;
            }

            
            
            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $category_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id' => $category_id , 'status'=>$status]);
            $arr_event['USER_ID']      = $user->id;

            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/

      
        // $update = $this->perform_deactivate($checked_record);
        //     $message = "Sub categories deactivated successfully";
        //     $type = "success";
        //     if($update)
        //     {
        //         Flash::$type($message);
        //         return redirect()->back();
        //     }
        //     else
        //     {   $message = "Error while perofrming action, please try again";
        //         $type = "error";
                
        //     }

        }
        else if($multi_action == "activate")
        {  
            $update = $this->perform_activate($checked_record);
            $message = "Third level categories activated successfully";
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
        else if($multi_action == "delete")
        {
            $update = $this->perform_multi_delete($checked_record);
            $message = "Third level categories deleted successfully";
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
}

