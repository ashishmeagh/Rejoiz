<?php

namespace App\Http\Controllers\sales_manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\UserService;
use App\Common\Services\EmailService;
use App\Common\Services\ReportService;
use App\Common\Services\PdfReportService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\GeneralService;

use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\MakerModel;
use App\Models\CountryModel;
use App\Models\RoleUserModel;
use App\Models\SalesManagerModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\VendorSalesmanagerMappingModel;
use App\Models\UserModel;
use App\Models\SiteSettingModel;

use App\Models\ShopSettings;
use App\Common\Traits\MultiActionTrait;
use DB;
use PDF;
use Sentinel;
use Validator;
use Flash;


class MakerController extends Controller
{
 
  use MultiActionTrait;

  public function __construct(
                      UserService $UserService,
                      EmailService $EmailService,
                      PdfReportService $PdfReportService,
                      ReportService $ReportService,
                      GeneralService $GeneralService,
                      RoleModel $RoleModel,
                      RoleUsersModel $RoleUsersModel,
                      CategoryModel $CategoryModel,
                      CountryModel $CountryModel,
                      CategoryTranslationModel $CategoryTranslationModel,
                      MakerModel $MakerModel,
                      RoleUsersModel $RoleUserModel,
                      ShopSettings $ShopSettings,
                      VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                      VendorSalesmanagerMappingModel $VendorSalesmanagerMappingModel,
                      SalesManagerModel $SalesManagerModel
                    )
  {
    $this->CategoryModel      = $CategoryModel;
    $this->RoleUserModel      = $RoleUserModel;
    $this->UserModel          = Sentinel::createModel();
    $this->MakerModel         = $MakerModel;
    $this->UserService        = $UserService;
    $this->PdfReportService   = $PdfReportService;
    $this->EmailService       = $EmailService;
    $this->ReportService      = $ReportService;
    $this->GeneralService     = $GeneralService;
    $this->RoleModel          = $RoleModel;
    $this->CategoryModel      = $CategoryModel;
    $this->CategoryTranslationModel = $CategoryTranslationModel;
    $this->RoleUsersModel     = $RoleUsersModel;
    $this->CountryModel       = $CountryModel;
    $this->ShopSettings       = $ShopSettings;
    $this->SalesManagerModel  = $SalesManagerModel;
    $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
    $this->VendorSalesmanagerMappingModel   = $VendorSalesmanagerMappingModel;

    $this->arr_view_data      = [];
    $this->module_title       = "Vendor";
    $this->module_view_folder = "sales_manager.maker";
    $this->module_url_path    = url(config('app.project.sales_manager_panel_slug')."/vendors");        
    $this->role = 'maker';
  }

  public function index()
  {
    /*get all vendors*/

     $user = Sentinel::check();
    $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }   


    $get_vendors   = $this->VendorSalesmanagerMappingModel->where('salesmanager_id',$loggedIn_userId)->with(['get_user_details'=>function($q){
                                                   $q->where('status','1');  
                                                   $q->where('is_approved','1');
                                                 }])
                                                 ->get()
                                                 ->toArray();


    $this->arr_view_data['page_title']          = "My ".str_plural( $this->module_title);
    $this->arr_view_data['vendor_arr']          = $get_vendors;
    $this->arr_view_data['module_title']        = str_plural($this->module_title);
    $this->arr_view_data['module_url_path']     = $this->module_url_path;
    
    return view($this->module_view_folder.'.index',$this->arr_view_data);
  }

    
  public function create()
  {      
    /* get all categories*/
    $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();
    $country_arr    =  $this->CountryModel->orderBy('id','DESC')
                                          ->get()
                                          ->toArray();
                  
    $this->arr_view_data['country_arr']          = isset($country_arr)?$country_arr:'';
    $this->arr_view_data['page_title']           = 'Add '.str_singular($this->module_title);
    $this->arr_view_data['module_title']         = str_plural($this->module_title);
    $this->arr_view_data['module_url_path']      = $this->module_url_path;
    $this->arr_view_data['categories_arr']       = $categories_arr;  

    return view($this->module_view_folder.'.create',$this->arr_view_data);
  }

  public function save(Request $request)
  { 
      $response    = $arr_user_data = [];
      $is_update   = false;
      try
      {
        $form_data = $request->all();

        $user_id   = $request->input('user_id');

        if($request->has('user_id'))
        {
          $is_update = true;
        }        

        $arr_rules =  [
                        'first_name'  => 'required|regex:/^[a-zA-Z]+$/',
                        'last_name'   => 'required|regex:/^[a-zA-Z]+$/',
                        'email'       => 'required|email',
                        'contact_no'  => 'required|numeric',
                        'country_id'  => 'required',
                        'post_code'   => 'required',
                        'company_name'=> 'required',
                        'website_url' => 'required', 
                        'primary_category_id' => 'required'
                      ];


        $validator = Validator::make($request->all(),$arr_rules);
      

        if($validator->fails())
        { 
           $response['description']   = 'Form validations failed, please check form fields.';
           $response['status']        = "error";
           return response()->json($response); 
        }

         DB::beginTransaction();

        /*check email duplication*/

        $is_duplicate = $this->UserModel->where('email',$form_data['email']);

        if($is_update)
        {
            $is_duplicate->where('id','<>',$user_id);       
        }
            
        $is_duplicate = $is_duplicate->count()>0;  

        if($is_duplicate==true)
        {  
           $response['status']          = 'error';
           $response['description']     = 'Email is already exists.';
           return response()->json($response);
        } 

       $company_name                    = isset($form_data['company_name'])?$form_data['company_name']:'';

       $is_company_exists               = $this->MakerModel->where('company_name',$company_name);
       if($is_update)
        {
            $is_company_exists->where('user_id','<>',$user_id);       
        }
        $is_company_exists = $is_company_exists->count()>0; 

        if($is_company_exists==true)
        {
          $arr_response['status']      = 'error';
          $arr_response['description'] = 'Company name already exists';
          return $arr_response;
        }

        /*check tax_id*/                
        $arr_user_data['tax_id']      = isset($form_data['tax_id'])?$form_data['tax_id']:'';

        if($arr_user_data['tax_id'] != '')
        {
          $tax_id_count = UserModel::where('tax_id', $arr_user_data['tax_id']);

          if ($user_id) {
            $tax_id_count = $tax_id_count->where('id', '<>', $user_id)->count();
          } else {
            $tax_id_count = $tax_id_count->count();
          }

          if($tax_id_count > 0)
          {
            $response['status'] = 'error';
            $response['description']    = 'Tax id already exists';
            return response()->json($response);
          }
        }

        $arr_user_data['first_name']    = $request->input('first_name');
        $arr_user_data['last_name']     = $request->input('last_name');
        $arr_user_data['email']         = $request->input('email');
        $arr_user_data['contact_no']    = $request->input('contact_no');
        $arr_user_data['post_code']     = $request->input('post_code');
        $arr_user_data['country_id']    = $request->input('country_id');
        $arr_user_data['id']            = $user_id;
        $arr_user_data['tax_id']        = isset($form_data['tax_id'])?$form_data['tax_id']:'';

        $user          = Sentinel::createModel()->where(['id' => $user_id])->first();
        $user_password = str_random(6);

        if($user == false)
        {
            $user = Sentinel::registerAndActivate([
                                                    'email'    => $arr_user_data['email'],
                                                    'password' => $user_password
                                                  ]);

            if($user)
            {
                $role = Sentinel::findRoleBySlug('maker');
                $role->users()->attach($user);
            }

            $contact_no = str_replace($request->input('hid_country_code'), "", $arr_user_data['contact_no']);
            $user->first_name    = $arr_user_data['first_name'];
            $user->last_name     = $arr_user_data['last_name'];
            $user->email         = $arr_user_data['email'];
            //$user->contact_no    = $arr_user_data['contact_no'];
            $user->contact_no    = $contact_no;
            $user->country_code  = $request->input('hid_country_code');
            $user->post_code     = $arr_user_data['post_code'];
            $user->country_id    = $arr_user_data['country_id'];
            $user->tax_id    = $arr_user_data['tax_id'];
            $user->status        = 1;
            $user->is_approved   = 0;
          
            $user->save();

            $logged_in_user            = Sentinel::check();
            $logged_in_user_id         = $logged_in_user->id;

            $arr_maker['user_id']      = $user->id;
            $arr_maker['company_name'] = isset($form_data['company_name'])?$form_data['company_name']:'';
            $arr_maker['website_url']  = isset($form_data['website_url'])?$form_data['website_url']:'';
            $arr_maker['primary_category_id'] = isset($form_data['primary_category_id'])?$form_data['primary_category_id']:'';
            $arr_maker['no_of_stores'] = isset($form_data['no_of_stores'])?$form_data['no_of_stores']:'';
            $arr_maker['insta_url']    = isset($form_data['insta_url'])?$form_data['insta_url']:'';


            $arr_vendor_sales_mapping['salesmanager_id']  = $logged_in_user_id;
            $arr_vendor_sales_mapping['vendor_id']        = $user->id;

            $store_maker_details       = $this->MakerModel->create($arr_maker); 
            $store_vendor_salesmanager_mapping = $this->VendorSalesmanagerMappingModel->create($arr_vendor_sales_mapping); 
        }
        else
        {
              $contact_no = str_replace($request->input('hid_country_code'), "", $arr_user_data['contact_no']);
              Sentinel::update($user, [
                                        'email'         => $arr_user_data['email'],
                                        'first_name'    => $arr_user_data['first_name'],
                                        'last_name'     => $arr_user_data['last_name'],
                                        'contact_no'    => $contact_no,
                                        'country_code'  => $request->input('hid_country_code'),
                                        'post_code'     => $arr_user_data['post_code'],
                                        'country_id'    => $arr_user_data['country_id'],
                                        'tax_id'        => $arr_user_data['tax_id']
                                        // 'profile_image' => $arr_user_data['profile_image']
                                      ]);


              $arr_maker['company_name'] = isset($form_data['company_name'])?$form_data['company_name']:'';
              $arr_maker['website_url']  = isset($form_data['website_url'])?$form_data['website_url']:'';
              $arr_maker['primary_category_id'] = isset($form_data['primary_category_id'])?$form_data['primary_category_id']:'';
              $arr_maker['no_of_stores'] = isset($form_data['no_of_stores'])?$form_data['no_of_stores']:'';
              $arr_maker['insta_url']    = isset($form_data['insta_url'])?$form_data['insta_url']:'';

              $update_maker  = $this->MakerModel->where('user_id',$user->id)->update($arr_maker);
        }

          if($is_update == false)
          {
              $arr_mail_data    = $this->built_mail_data($arr_user_data['email'],$user_password); 

              $email_status     = $this->EmailService->send_mail($arr_mail_data);
               DB::commit();
              $response['description'] = str_singular($this->module_title)." has been created.";

             $vendor_detail_url = url('/admin/vendor/view/'.base64_encode($user->id));
             $admin_id          = get_admin_id();

             //Send notification to admin.

                  $notification_arr                 = [];
                  $notification_arr['from_user_id'] = $logged_in_user_id;
                  $notification_arr['to_user_id']   = $admin_id;
                  $notification_arr['description']  = 'New vendor has been registered by Salesmanager '. $logged_in_user->first_name.' '.$logged_in_user->last_name;
                  $notification_arr['title']        = 'New Vendor Registration';
                  $notification_arr['type']         =  'admin';
                  $notification_arr['link']         =  $vendor_detail_url;   
          

             $this->GeneralService->save_notification($notification_arr);
          }
          else
          {
             DB::commit();
            $response['description'] = str_singular($this->module_title)." has been updated.";
          }
          
          $response['status']    = "success";
          $response['url']       = $this->module_url_path;

          return response()->json($response);
     }  
     
     catch(Exception $e)
    {
        DB::rollback();
        $response['status']      = 'error';
        $response['description'] = $e->getMessage();
        $response['url']         = $this->module_url_path;    
        return response()->json($response);
    }  
  }


    public function built_mail_data($email,$user_password)
    {   
        $credentials = ['email' => $email];
    
        $user        = Sentinel::findByCredentials($credentials); // check if user exists
        $site_setting_obj = SiteSettingModel::first();
        if($site_setting_obj)
        {
            $site_setting_arr = $site_setting_obj->toArray();            
        }

        $site_name   = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';

    
        if($user)
        {
            $arr_user= $user->toArray();

            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>' ;

            $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                                  'USER_ROLE'    => 'maker',
                                  'EMAIL'        => $arr_user['email'],
                                  'PASSWORD'     => $user_password,
                                  'LOGIN_URL'    => $reminder_url,
                                  'USER_ROLE'    => "Vendor",
                                  'APP_URL'      => $site_name
                            ];


            $arr_mail_data                        = [];
            $arr_mail_data['email_template_id']   = '34';
            $arr_mail_data['arr_built_content']   = $arr_built_content;
            $arr_mail_data['arr_user']            = $arr_user;


            return $arr_mail_data;
         }
        return FALSE;
    }


    public function vendors_listing(Request $request)
    {   
      
        $user            = Sentinel::check();
        $loggedIn_userId = 0;
        $post_code       = '';

        if($user)
        {
          $loggedIn_userId  = $user->id;
          $post_code        = $user->post_code;
        } 
            

        $arr_search_column  = $request->input('column_filter');

        $user_table         =  $this->UserModel->getTable();
        $prefix_user_table  = DB::getTablePrefix().$user_table;

        $maker_table        =  $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;

        $role_table         =  $this->RoleModel->getTable();
        $prefix_role_table  = DB::getTablePrefix().$role_table;

        $role_user_table    =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $vendor_salesmanager_mapping_table = $this->VendorSalesmanagerMappingModel->getTable();
        $prefix_vendor_salesmanager_mapping_table = DB::getTablePrefix().$vendor_salesmanager_mapping_table;

        $salesmanager_table        = $this->SalesManagerModel->getTable();
        $prefix_salesmanager_table = DB::getTablePrefix().$salesmanager_table;

         $obj_user = DB::table($vendor_salesmanager_mapping_table)
                   ->select(DB::raw($prefix_user_table.".id as uid,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".is_approved, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".country_code, ".
                                     $prefix_maker_table.".company_name, ".
                                    $vendor_salesmanager_mapping_table.".vendor_id, ".
                                    $salesmanager_table.".id as sid,".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                       ->leftJoin($prefix_user_table,$vendor_salesmanager_mapping_table.'.vendor_id','=',$user_table.'.id')

                       ->leftJoin($salesmanager_table,$vendor_salesmanager_mapping_table.'.salesmanager_id','=',$salesmanager_table.'.user_id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                       ->where('salesmanager_id',$loggedIn_userId)
                       ->orderBy('uid','DESC');
                       // ->get();

       /* ---------------- Filtering Logic ----------------------------------*/  
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term  = $arr_search_column['q_name'];
            $obj_user     = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term  = $arr_search_column['q_email'];
            $obj_user     = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        } 
           
        if(isset($arr_search_column['q_brand_name']) && $arr_search_column['q_brand_name']!="")
        {
            $search_term  = $arr_search_column['q_brand_name'];
            $obj_user     = $obj_user->where($maker_table.'.company_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term  = $arr_search_column['q_status'];

            $obj_user     = $obj_user->where($user_table.'.status','LIKE', '%'.$search_term.'%');
        }
 

        if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
            $search_term      = $arr_search_column['q_is_approved'];
            $obj_user = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }

        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
            $search_term  = $arr_search_column['q_contact_no'];
            $obj_user     = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }
        
        $json_result      = \Datatables::of($obj_user);
        
       $json_result       = $json_result->blacklist(['id']);
        $json_result      = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->uid);
                            }) 
                             ->editColumn('build_status_btn',function($data)
                            {
                                $build_status_btn ='';
                                if($data->status == '0')
                                {   
                                    $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->uid).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                elseif($data->status == '1')
                                {
                                    $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->uid).'"  id="status_'.$data->uid.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                return $build_status_btn;
                            })


                            ->editColumn('admin_approval',function($data)
                            {
                                $admin_approval_btn ='';

                                if($data->is_approved == '0')
                                {   
                                    $admin_approval_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->uid).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" readonly="true"/>';
                                }
                                elseif($data->is_approved == '1')
                                {
                                    $admin_approval_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->uid).'"  id="status_'.$data->uid.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" readonly="true"/>';
                                }
                                return $admin_approval_btn;
                            })   

                            ->editColumn('build_action_btn',function($data) 
                            {   
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->uid);
                                $edit_href =  $this->module_url_path.'/edit/'.base64_encode($data->uid);
                                $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'" title="View">View</a>
                                <a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$edit_href.'" title="Edit">Edit</a>
                                ';
                                return $build_view_action;
                            })
                             ->editColumn('contact_no',function($data)
                            {
                                if($data->country_code != "")
                                {
                                    $countryCode = ltrim($data->country_code,'+');;
                                    $data->contact_no = str_replace($countryCode, "", $data->contact_no);
                                    $contact_no = '+'.$countryCode .'-'.get_contact_no($data->contact_no);                              
                                    return $contact_no;
                                }
                                else
                                {
                                    $contact_no = get_contact_no($data->contact_no);                              
                                    return $contact_no;
                                }
                            })
                            ->make(true);

        $build_result = $json_result->getData();
        return response()->json($build_result);
    }


  public function edit($enc_id)
  {   
      $arr_user_data = [];
      $id            = base64_decode($enc_id);

         
      $obj_user_data = $this->UserModel->where('id', $id)
                                           ->with('maker_details')
                                           ->first();
      if($obj_user_data)
      {

        $arr_user_data = $obj_user_data->toArray();
      }

      $country_arr    =  $this->CountryModel->orderBy('id','DESC')
                                          ->get()
                                          ->toArray();


      $categories_obj_data      =  $this->CategoryModel->where('is_active','1')->get()->toArray();


      $this->arr_view_data['edit_mode']               = TRUE;
      $this->arr_view_data['enc_id']                  = $enc_id;        
      $this->arr_view_data['arr_user_data']           = $arr_user_data;      
      $this->arr_view_data['categories_arr']          = $categories_obj_data; 
      $this->arr_view_data['page_title']              = str_singular( $this->module_title).' Details';
      $this->arr_view_data['module_title']            = str_plural($this->module_title);
      $this->arr_view_data['module_url_path']         = $this->module_url_path;
      $this->arr_view_data['country_arr']           = isset($country_arr)?$country_arr:'';
      
      return view($this->module_view_folder.'.edit',$this->arr_view_data);
  }

  public function view($enc_id)
  {   
      $id            = base64_decode($enc_id);
         
      $obj_user_data = $this->UserModel->where('id', $id)
                                           ->with('maker_details')
                                           ->first();
      if($obj_user_data)
      {

        $arr_user_data = $obj_user_data->toArray();
      }


      /*maker details and assigned rep details*/
      $representative_data_arr  = $this->VendorRepresentativeMappingModel
                                        ->with(['get_representative_details.get_user_details',
                                              'get_representative_details.sales_manager_details.get_user_data',
                                              'get_representative_details.get_area_details'])
                                        ->where('vendor_id',$id)
                                        ->get()
                                        ->toArray();


      $categories_obj_data      =  $this->CategoryModel->where('is_active','1')->get()->toArray();


      $this->arr_view_data['edit_mode']               = TRUE;
      $this->arr_view_data['enc_id']                  = $enc_id;        
      $this->arr_view_data['arr_user_data']           = $arr_user_data;    
      $this->arr_view_data['representative_data_arr'] = $representative_data_arr;    
      $this->arr_view_data['categories_arr']          = $categories_obj_data; 
      $this->arr_view_data['page_title']              = str_singular( $this->module_title).' Details';
      $this->arr_view_data['module_title']            = str_plural($this->module_title);
      $this->arr_view_data['module_url_path']         = $this->module_url_path;
      
      return view($this->module_view_folder.'.view',$this->arr_view_data);
  }


  public function activate(Request $request)
  {
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
          return redirect()->back();
        }

        if($this->perform_activate(base64_decode($enc_id)))
        {
           $arr_response['status']  = 'SUCCESS';
           $arr_response['message'] = 'Vendor(s) has been activated.';

        }
        else
        {
           $arr_response['status']  = 'ERROR';
           $arr_response['message'] = 'Error occurred while deactivating vendor.';
        }

        $arr_response['data']       = 'ACTIVE';
        return response()->json($arr_response);
   }

    public function deactivate(Request $request)
   {
        $status = '';
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        $status = $this->perform_deactivate(base64_decode($enc_id));
     
        if($status == 'TRUE')
        {
            $arr_response['status']  = 'SUCCESS';
            $arr_response['data']    = 'DEACTIVE';
            $arr_response['message'] = 'Vendors has been deactivated.';
             return response()->json($arr_response);

        }
        elseif($status == 'FALSE')
        {  
            $arr_response['status']   = 'ERROR';
            $arr_response['message']  = 'Error occurred while deactivating vendor.';
             return response()->json($arr_response);
        }
 
   }

    public function perform_activate($id)
    {
        $entity = $this->UserModel->where('id',$id)->first();
        
        if($entity)
        {   
          $this->UserModel->where('id',$id)->update(['status'=>'1']);

          return TRUE;
        }

        return FALSE;
    }

    public function perform_deactivate($id)
    {  
        $entity = $this->UserModel->where('id',$id)->first();
    
        if($entity)
        {   
          //deactivate the user
          $this->UserModel->where('id',$id)->update(['status'=>'0']);
          return TRUE;
        }
        else
        {
           return FALSE;
        }
    }
    
}
