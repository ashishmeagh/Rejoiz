<?php

/* Author : priyanka
   Date : 23/7/2018
*/

namespace App\Http\Controllers\admin;

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
use App\Models\CategoryTranslationModel;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Services\HelperService;
use App\Models\SiteSettingModel;
use Sentinel;
use Validator;
use Flash;
use Datatables;
use DB;
use Session;

class RequestSubCategoryController extends Controller
{
    use MultiActionTrait;

    public function __construct(    SubCategoryModel $SubCategoryModel,
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
                                    HelperService $HelperService,
                                    SiteSettingModel $SiteSettingModel
    	                       )
    {

      $this->arr_view_data                   =   [];
      $this->LanguageModel                   =   $language_model;
      $this->SubCategoryModel                =   $SubCategoryModel;
      $this->ProductsModel                   =   $ProductsModel;
      $this->ProductsSubCategoriesModel      =   $ProductsSubCategoriesModel;
      $this->BaseModel                       =   $this->SubCategoryModel;
      $this->CategoryModel                   =   $CategoryModel;
      $this->CategoryTranslationModel        =   $CategoryTranslationModel;
      $this->SubCategoryTranslationModel     =   $SubCategoryTranslationModel;
      $this->LanguageService                 =   $langauge;
      $this->ElasticSearchService            =   $ElasticSearchService;
      $this->GeneralService                  =  $GeneralService;   
      $this->EmailService                    =  $EmailService;  
      $this->HelperService                   = $HelperService;
      $this->curr_panel_slug                 =   config('app.project.admin_panel_slug');
      $this->module_view_folder              =   "admin.request_sub_category";
      $this->module_title                    =   "Request Sub Categories";
      $this->module_url_path                 =   url(config('app.project.admin_panel_slug').'/request_sub_categories');      
      $this->user_base_img_path              =   base_path().config('app.project.img_path.user_profile_image');
      $this->user_public_img_path            =   url('/').config('app.project.img_path.user_profile_image');        
      $this->SiteSettingModel                =    $SiteSettingModel;
      $this->site_setting_obj  = $this->SiteSettingModel->first();
    
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

     public function get_all_request_sub_category(Request $request)
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

    function get_category_details(Request $request)
    {
        $category_id = $request->input('category_id'); 
  
        $sub_category_details   = $this->BaseModel->getTable();        
        $prefixed_sub_category   = DB::getTablePrefix().$this->BaseModel->getTable();
        $prefixed_sub_category_trans  = DB::getTablePrefix().$this->SubCategoryTranslationModel->getTable();       
        $category_trans_tbl         = $this->CategoryTranslationModel->getTable();                
        $prefixed_category_trans_tbl= DB::getTablePrefix().$this->CategoryTranslationModel->getTable();
        

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
                                ->where($prefixed_sub_category.'.admin_confirm_status','!=','0')
                                ->where($prefixed_sub_category.'.is_active','=','0')
                                ->whereNull($sub_category_details.'.deleted_at')
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



    public function status_update(Request $request)
    {

        $user = Sentinel::check();

        $mag = '';
        try
        {

            $category_id    = base64_decode($request->input('cat_id'));
            $status         = $request->input('status');
            $reject_reason  = $request->input('reject_reason');
            $sub_category_name  = $request->input('sub_cat_name');
            

            $obj_get_category = $this->BaseModel->where('id', $category_id)->get()->toArray();
            if(!empty($obj_get_category)){
              $maker_id = isset($obj_get_category[0]['maker_id']) ? $obj_get_category[0]['maker_id'] : 0;
            }

            $maker_user_name = get_user_name($maker_id);

            if($request->input('status') == '0'){
                    $data['admin_confirm_status']   = $request->input('status');
                    $data['request_cat_update_date']    = date('Y-m-d H:i:s');
                    $data['is_active']              = 1;

                    $notification_arr['description']  = 'Requested Sub-category named as <b>'.$sub_category_name.'</b> has been approved by admin.';

                    $notification_arr['email_template']  = '<br>Requested Sub-category named as <b>'.$sub_category_name.'</b> has been approved by admin.<br><br>Thank you ..!';

                    $mag = "Status has been changed.";

            } else {
                $data['reject_reason']              = $reject_reason;
                $data['request_cat_update_date']    = date('Y-m-d H:i:s');
                $data['admin_confirm_status']       = $request->input('status');
                
                $notification_arr['description']  = 'Requested Sub-category named as <b>'.$sub_category_name.'</b>  has been rejected by admin.<br><b>Rejected Reason : </b>'.$data['reject_reason'].'';

                $notification_arr['email_template']  = '<br>Requested Sub-category named as <b>'.$sub_category_name.'</b>  has been rejected by admin.<br><b>Rejected Reason : </b>'.$data['reject_reason'].'<br><br>Thank you ..!';

                    $mag = "Status has been changed.";
            }
            $update = $this->BaseModel->where('id',$category_id)->update($data);

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
            $notification_arr['title']        = 'Requested Sub-category status';
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

            $response['link'] = url('/admin/request_sub_categories');

        }
         catch(Exception $e)
         {
             $response['status'] = 'error';
             $response['message'] = 'Error occurred while updating status.';
         }

         
         return response()->json($response);
    
    }


    public function multi_status_update($subcat_id = false,$status=false)
    {   
        $user = Sentinel::check();

        $category_id = isset($subcat_id)?intval($subcat_id):0;
      
        $status = isset($status)?$status:'';

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
            $arr_event['USER_ID']      = $user->id;

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
        $user = Sentinel::check();

        $is_subcategory_having_products = $this->ProductsSubCategoriesModel->where('sub_category_id',$id)->count();

        if($is_subcategory_having_products > 0)
        {
          Flash::error("Subcategory can't be deleted, First you have to delete this subcategory
              from product.");
        } 

        else
        {    
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


    public function view($enc_id)
    {

        $category_name = "";
        $id = base64_decode($enc_id);           
        $subcat_data = [];
        $obj_data = $this->BaseModel->where('id', $id)->with(['translations'])->first();
        $arr_data = [];

        if($obj_data)
        {
           $arr_data = $obj_data->toArray();          
           
           if(!empty($arr_data)){
                $obj_cat_data = $this->CategoryModel->where('id', $arr_data['category_id'])->get()->toArray();

                if(!empty($obj_cat_data)){
                    $category_name = $obj_cat_data[0]['category_name'];
                }
           }
           $subcat_data['category_name'] = $category_name;
           $subcat_data['sub_category_name'] = $arr_data['subcategory_name'];
           $subcat_data['sub_category_id'] = $arr_data['id'];
           $subcat_data['admin_confirm_status'] = $arr_data['admin_confirm_status'];
           $subcat_data['reject_reason'] = $arr_data['reject_reason'];
           $subcat_data['id'] = $arr_data['id'];

        }
        //dd($subcat_data);
        $this->arr_view_data['subcat_data']       = $subcat_data;
        $this->arr_view_data['enc_id']            = $enc_id;      
        $this->arr_view_data['page_title']      = 'View '.str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;  
        
        //dd(count($this->arr_view_data['subcat_data']));
        if(count($this->arr_view_data['subcat_data']) <= 0)
        {
          Session::flash('error',"SubCategory record not available");
          return redirect()->back();
        }

        return view($this->module_view_folder.'.view',$this->arr_view_data);
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
               Flash::success('Requested sub-category(s) have been approved.');
               
            }
            elseif($multi_action=="reject")
            {
               $this->perform_reject(base64_decode($record_id),$product_count); 
               Flash::success('Requested sub-category(s) have been rejected.');
               
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

    public function perform_reject($id)
  {   
      $static_page = $this->BaseModel->where('id',$id)->first();
        
      if($static_page)
      {          
          {           
            $updateProduct = $this->BaseModel->where('id',$id)->update(['admin_confirm_status'=>'1','request_cat_update_date' => date('Y-m-d H:i:s')]);
            return $updateProduct;             
          }
       
      }

      return FALSE;
  }
}
