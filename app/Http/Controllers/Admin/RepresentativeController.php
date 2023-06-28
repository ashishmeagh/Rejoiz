<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\CountryModel;

use App\Common\Services\UserService;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;

use App\Models\RoleModel;
use App\Models\MakerModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeModel;
use App\Common\Services\ReportService;
use App\Common\Services\PdfReportService;
use App\Models\RepAreaModel;
use App\Models\SalesManagerModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\VendorSalesmanagerMappingModel;

use App\Models\CategoryDivisionModel;

use App\Common\Traits\MultiActionTrait;
use DB;
use Validator;
use Sentinel;
use Flash;
use DataTable;
use DateTime;
use Carbon\Carbon;



class RepresentativeController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 22 June 2019
    */
    use MultiActionTrait;
    public function __construct(UserModel $UserModel,
                                UserService $UserService,
                                EmailService $EmailService,
                                ReportService $ReportService,
                                RepAreaModel $RepAreaModel,
                                RoleModel $RoleModel,
                                MakerModel $MakerModel,
                                CountryModel $CountryModel,
                                SalesManagerModel $SalesManagerModel,
                                RoleUsersModel $RoleUsersModel,
                                CategoryModel $CategoryModel,
                                CategoryTranslationModel $CategoryTranslationModel,
                                RepresentativeModel $RepresentativeModel,
                                VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                PdfReportService $PdfReportService,
                                GeneralService $GeneralService,
                                CategoryDivisionModel $CategoryDivisionModel,
                                VendorSalesmanagerMappingModel $VendorSalesmanagerMappingModel
                            )
    {
        $this->UserModel          = $UserModel;
        $this->BaseModel          = $UserModel;
        $this->RepresentativeModel= $RepresentativeModel;
        $this->SalesManagerModel  = $SalesManagerModel;
        $this->UserService        = $UserService;
        $this->RepAreaModel       = $RepAreaModel;
        $this->CountryModel       = $CountryModel;
        $this->EmailService       = $EmailService;
        $this->ReportService      = $ReportService;
        $this->PdfReportService   = $PdfReportService;
        $this->GeneralService     =  $GeneralService;   
        $this->RoleModel          = $RoleModel;
        $this->CategoryModel      = $CategoryModel;
        $this->CategoryTranslationModel = $CategoryTranslationModel;
        $this->RoleUsersModel     = $RoleUsersModel;
        $this->MakerModel         = $MakerModel;
        $this->VendorRepresentativeMappingModel         = $VendorRepresentativeMappingModel;
        $this->CategoryDivisionModel  = $CategoryDivisionModel;
        $this->VendorSalesmanagerMappingModel = $VendorSalesmanagerMappingModel;
        $this->arr_view_data      = [];
        $this->module_title       = "Representative";
        $this->module_view_folder = "admin.representative";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/representative");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');

        $this->role = 'representative';
        $this->profile_image      =  base_path().'/storage/app/';

    }

    public function index()
    {
        $this->arr_view_data['page_title']      = str_plural( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_retailer(Request $request)
    { 

        $arr_search_column = $request->input('column_filter');

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $obj_user = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".commission,".
                                     $prefix_user_table.".is_approved, ".
                                     $prefix_user_table.".wallet_address as wallet_address, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".country_code, ".
                                     $prefix_user_table.".created_at, ".
                                     $role_table.".slug as slug, ".
                                     $role_table.".name as name, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
                        ->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC');

                        // dd($obj_user->get()->toArray());

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
            $search_term      = $arr_search_column['q_contact_no'];
            $obj_user = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            $obj_user = $obj_user->where($user_table.'.status','=', $search_term);
        }

         if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
            $search_term      = $arr_search_column['q_is_approved'];
            $obj_user = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }
        if(isset($arr_search_column['q_commission']) && $arr_search_column['q_commission']!="")
        {
            $search_term      = $arr_search_column['q_commission'];
            $obj_user = $obj_user->having('commission','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_date']) && $arr_search_column['q_date']!="")
        {
            
            $search_term  = $arr_search_column['q_date'];

            $date         = DateTime::createFromFormat('m/d/Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_user     = $obj_user->where($user_table.'.created_at','LIKE', '%'.$date.'%');
        } 


        $json_result     = \Datatables::of($obj_user);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->id);
                            })                          
                            ->editColumn('build_status_btn',function($data)
                            {
                                $build_status_btn ='';
                                if($data->status == '0')
                                {   
                                    $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                elseif($data->status == '1')
                                {
                                    $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" readonly />';
                                }
                                return $build_status_btn;
                            })    
                            
                             ->editColumn('build_is_approved_btn',function($data)
                            {
                                $build_is_approved_btn ='';
                                if($data->status == '0')
                                {   
                                    $build_is_approved_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                elseif($data->status == '1')
                                {
                                    $build_is_approved_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                return $build_is_approved_btn;
                            })    

                            ->editColumn('build_action_btn',function($data) 
                            {   
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->id);
                                $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'" title="View">View</a>';

                               /* $delete_href =  $this->module_url_path.'/delete/'.base64_encode($data->id);
                                $build_delete_action = '<a class="btn btn-outline btn-danger btn-circle show-tooltip" onclick="confirm_delete($(this),event)" href="'.$delete_href.'" title="Delete">Delete</a>';*/

                                $edit_href = $this->module_url_path.'/edit/'.base64_encode($data->id);

                                    $build_edit_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip"  href="'.$edit_href.'" title="edit">Edit</a>';
/*

                                return $build_action = $build_view_action.' '.$build_delete_action.''.$build_edit_action;*/

                                 return $build_action = $build_view_action.' '.$build_edit_action;
                            })

                            ->editColumn('contact_no',function($data)
                            { 
                                $contactNo = $data->contact_no;
                                if($data->country_code == "")
                                {
                                    $contactNo = str_replace($data->country_code,"",$contactNo);
                                    $contactNo = get_contact_no($contactNo);
                                }
                                else
                                {
                                    $contactNo = str_replace($data->country_code,"",$contactNo);
                                    $contactNo = $data->country_code.'-'.get_contact_no($contactNo);
                                }
                            
                                return $contactNo;
                            })

                            ->editColumn('commission',function($data)
                            {

                              if (isset($data->commission) && $data->commission == "" || $data->commission == 0) {

                                $commission = '-';
                              }
                              else{

                                $commission =  number_format((float)$data->commission, 2, '.', '');
                                //$commission = $data->commission;
                              
                              }
                              return $commission;
                            })

                            ->editColumn('registration_date',function($data)
                            {

                                $date = '';

                                $date = isset($data->created_at)?us_date_format($data->created_at):'';

                                return $date;
                            })

                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }


    
    public function edit($user_id)
    {

        $id              = base64_decode($user_id);

        $getSalesManager = $this->SalesManagerModel
                                           ->with(['get_user_data' => function($query){ 
                                                $query->where([['status',1],['is_approved',1]]);
                                            }
                                            ,'area_details'])
                                           ->groupBy('user_id')
                                           ->get()
                                           ->toArray();
    
        $getVendorsData =  $this->MakerModel
                                ->with(['user_details'=> function($query){ 
                                    $query->where([['status',1],['is_approved',1]]);
                                }])
                                ->get()
                                ->toArray();


        $obj_user_data = $this->UserModel->with(['representative_details'])->where('id', $id)->first();

        $category_id_arr = $user_area_id_arr = [];

        if($obj_user_data)
        {
            $arr_user_data = $obj_user_data->toArray();

            $category_id_arr = json_decode($arr_user_data['representative_details']['category_id']);

        }

        $vendor_id_arr = $this->VendorRepresentativeMappingModel->where('representative_id',$id)->pluck('vendor_id')->toArray();

        $user_area_id = isset($arr_user_data['representative_details']['area_id'])?$arr_user_data['representative_details']['area_id']:'';

        $area_names       = $this->RepAreaModel->get()->toArray();
  
        $category_div_arr = $this->CategoryDivisionModel->where('is_active',1)->get()->toArray();


        $user_area_id_obj = $this->RepresentativeModel->where('user_id','=',$id)->first();
       

        /*get all categories from selected rep area_id*/

        $cattegory_from_selected_area = [];

        $rep_area_id = isset($user_area_id_obj->area_id)?$user_area_id_obj->area_id:0;

        $area_details_obj = $this->RepAreaModel->where('id',$rep_area_id)->first();

        if(isset($area_details_obj))
        { 

          $cattegory_from_selected_area = json_decode($area_details_obj->category_id);

          if (isset($cattegory_from_selected_area) && count($cattegory_from_selected_area)) {
            
            /*$cat_array_from_area = $this->CategoryTranslationModel->whereIn('category_id',$cattegory_from_selected_area)->get()->toArray();*/

            $cat_array_from_area = $this->CategoryDivisionModel->whereIn('id',$cattegory_from_selected_area)->get()->toArray();

          }

        }


        if(isset($user_area_id_obj))
        {
           $user_area_id_arr = $user_area_id_obj->toArray();
        }


         $country_arr = $this->CountryModel->orderBy('id','ASC')
                                      ->get()
                                      ->toArray();

     
       
        $this->arr_view_data['user_area_id_arr'] = $user_area_id_arr;
        $this->arr_view_data['country_arr']      = isset($country_arr)?$country_arr:'';
        $this->arr_view_data['area_names']       = $area_names;
        $this->arr_view_data['cat_translation']  = $category_div_arr;
        $this->arr_view_data['category_id_arr']  = $category_id_arr;
        
        $this->arr_view_data['cat_array_from_area']  = isset($cat_array_from_area)?$cat_array_from_area:[];
     
        $this->arr_view_data['edit_mode']           = TRUE;
        $this->arr_view_data['user_id']             = $id;        
        $this->arr_view_data['arr_user_data']       = $arr_user_data;        
        $this->arr_view_data['sales_manager_list']  = $getSalesManager;        
        $this->arr_view_data['all_vendors']         = $getVendorsData;        
        $this->arr_view_data['vendor_id_arr']       = $vendor_id_arr;        
        $this->arr_view_data['page_title']          = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_title']        = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']     = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
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

    public function perform_activate($id)
    {
        $entity = $this->UserModel->where('id',$id)->first();
        
        if($entity)
        {   
            //Activate the user
            $this->UserModel->where('id',$id)->update(['status'=>'1', 'is_approved' => 1]);

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
            $this->UserModel->where('id',$id)->update(['status'=>'0', 'is_approved' => 0]);

            return TRUE;
        }
        return FALSE;
    }

    public function create()
    { 
        $getSalesManager = $this->SalesManagerModel
                                           ->with(['get_user_data' => function($query){ 
                                                $query->where([['status',1],['is_approved',1]]);
                                            }
                                            ,'area_details'])
                                           ->withCount(['get_user_data as sales_m_name' => function($query) {
                                                $query->select(DB::raw('first_name'));
                                            }])
                                           ->groupBy('user_id')
                                           ->orderBy('sales_m_name','ASC')
                                           ->get()
                                           ->toArray();
        //dd($getSalesManager);
        $getVendorsData =  $this->MakerModel
                                ->with(['user_details'=> function($query){ 
                                    $query->where([['status',1],['is_approved',1]]);
                                }])
                                ->orderBy('company_name','ASC')
                                ->get()
                                ->toArray();


        //$getVendorsData =[];
         $country_arr = $this->CountryModel->orderBy('name','ASC')
                                      ->where('is_active',1)
                                      ->get()
                                      ->toArray();
                  
        $this->arr_view_data['country_arr']   = isset($country_arr)?$country_arr:'';                        

       
       
        $this->arr_view_data['page_title']           = 'Create '.str_singular($this->module_title);
        $this->arr_view_data['module_title']         = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['sales_manager_list']   = $getSalesManager;
        $this->arr_view_data['all_vendors']          =$getVendorsData;

        
        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }


    public function view($enc_id)
    {   
        $arr_user= [];

        $user_id = base64_decode($enc_id);
        
        //$arr_user = $this->UserService->get_user_information($user_id,$this->role);
        
        $arr_user_obj = $this->RepresentativeModel->with(['get_user_details.country_details','get_area_details','sales_manager_details.get_user_data'])
                                                  ->where('user_id',$user_id)
                                                  ->first();


        $arr_user     = $this->UserService->get_user_information($user_id,$this->role);

        
        if($arr_user_obj)
        {
           $arr_user = $arr_user_obj->toArray();
        }


        $arr_data    = $this->VendorRepresentativeMappingModel->select('vendor_representative_mapping.id as mapping_id','vendor_representative_mapping.representative_id','vendor_representative_mapping.vendor_id','users.id as user_id','users.first_name','users.last_name','makers.company_name')
                                                        ->join('users','users.id','=','vendor_representative_mapping.vendor_id')
                                                        ->join('makers','makers.user_id','=','vendor_representative_mapping.vendor_id')
                                                        ->where('representative_id',$user_id)
                                                        ->get();


        $this->arr_view_data['arr_user']        = $arr_user;
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;


        
        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

   
     public function does_exists_tax_id(Request $request , $param = false)
   { 
        $form_data = $request->all();
       
        $tax_id = $form_data['tax_id'];
        
       $tax_id_count = UserModel::where('tax_id',$tax_id)->count();
      // dd($sku_count);
      if($tax_id_count==0)
      {
       //return 404;
        return response()->json(['exists'=>'false'],200);
      }
      else
      {
        return response()->json(['exists'=>'true'],404);
      }

   }

    public function save(Request $request)
    {   
        $is_update = false;

        $form_data = $request->all();
        $user_id = $request->input('user_id');


        if($request->has('user_id'))
        {
           $is_update = true;
        }        
        $arr_rules = [
                       /* 'tax_id'=>'required',*/
                        'first_name'=>'required|regex:/^[a-zA-Z]+$/',
                        'last_name'=>'required|regex:/^[a-zA-Z]+$/',
                        'email'=>'required|email',
                        'contact_no'=>'required',
                        'country_id'=>'required',
                        'sales_manager_id'=>'required',
                        'vendor_id'=>'required',
                        'commission'=>'required'
                     ];
       
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
           
            $response['status']      = "error";
            $response['description'] = "Form validations failed, please check form fields.";
        
            return response()->json($response); 
        }
       
        /* Check for email duplication */
        $is_duplicate =  $this->UserModel->where('email','=',$request->input('email'));  
        if($is_update)
        {
            $is_duplicate = $is_duplicate->where('id','<>',$user_id);
        }

        $does_exists = $is_duplicate->count();

        if($does_exists)
        {
           $response['status']      = "error";
           $response['description'] = "Email id already exists.";
           return response()->json($response);
        }                 

        /* Check for tax_id duplication */
        /*$is_duplicate_tax_id =  $this->UserModel->where('tax_id','=',$request->input('tax_id'));  
        if($is_update)
        {
            $is_duplicate_tax_id = $is_duplicate_tax_id->where('id','<>',$user_id);
        }

        $does_exists = $is_duplicate_tax_id->count();

        if($does_exists)
        {
           $response['status']      = "error";
           $response['description'] = "Tax Id Already Exists";
           return response()->json($response);
        }      */  



            $profile_img_file_path = '';
            
            $profile_image = isset($form_data['profile_image'])?$form_data['profile_image']:null;
            

            if($profile_image!=null)
            {
                //Validation for product image
                $file_extension = strtolower( $profile_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'error';
                    $arr_response['description']  = 'Invalid profile image, please try again.';

                    return response().json($response);
                }

                $profile_img_file_path = $profile_image->store('profile_image');

               //unlink old image 
                if(isset($form_data['old_profile_image']) && $form_data['old_profile_image']!="")
                {    
                  $old_img_path           = $this->profile_image.$form_data['old_profile_image'];
                  $unlink_old_img         = $this->GeneralService->unlink_old_image($old_img_path);
                }
            }

            else
            {
               $profile_img_file_path = $form_data['old_profile_image'];
            }

        /*get area from sales manager id*/
        
        $area_id = $this->SalesManagerModel->where('user_id',$request->input('sales_manager_id'))->pluck('area_id')->first();

        $arr_user_data['first_name'] = $request->input('first_name');
        $arr_user_data['last_name']  = $request->input('last_name');
        $arr_user_data['email']      = $request->input('email');
        $arr_user_data['contact_no'] = $request->input('contact_no');
        $arr_user_data['nationality']= $request->input('nationality');
        $arr_user_data['country_code']= $request->input('hid_country_code');
        $arr_user_data['tax_id']      = $request->input('tax_id');
        $arr_user_data['post_code']   = $request->input('post_code');
        $arr_user_data['commission'] = $request->input('commission');
        // $arr_user_data['commission'] = '';
        $arr_user_data['country_id'] = $request->input('country_id');
        $arr_user_data['sales_manager_id'] = $request->input('sales_manager_id');
        $arr_user_data['category_id']      = $request->input('category_id');
  
        $arr_user_data['profile_image'] = $profile_img_file_path;   

        // dd($arr_user_data);

        /* File Upload */
        $user = Sentinel::createModel()->where(['id' => $user_id])->first();

        $user_password = str_random(6);

        if($user == false)
        {
            $user = Sentinel::registerAndActivate([
                'email' => $arr_user_data['email'],
                'password' => $user_password
            ]);

            if($user)
            {
                $role = Sentinel::findRoleBySlug('representative');

                $role->users()->attach($user);
            }
        }
        else
        {
            
            Sentinel::update($user, [
                'email' => $arr_user_data['email'],
               
            ]);
        }
        // dd($request->input('sales_manager_id'));

        $user->first_name   = $arr_user_data['first_name'];
        $user->last_name    = $arr_user_data['last_name'];
        $user->email        = $arr_user_data['email'];
        $user->contact_no   = $arr_user_data['contact_no'];
        $user->country_code = $arr_user_data['country_code'];
        $user->tax_id       = $arr_user_data['tax_id'];
        $user->nationality  = $arr_user_data['nationality'];
        $user->country_id   = $arr_user_data['country_id'];
        $user->post_code     = $arr_user_data['post_code'];
        $user->profile_image = $arr_user_data['profile_image'];
        $user->commission    = $arr_user_data['commission'];
        // $user->commission    = '';


        /*if admin add representative then status and is_approved by default '1'*/
        $user->status        = '1';
        $user->is_approved   = '1';

        $user->save();


        $category = '';
        if(isset($form_data['category_id']) && !empty($form_data['category_id']))
        {
            $category_id = $form_data['category_id'];
            $category = json_encode($category_id);
          
            
        }

        $representative                   =  RepresentativeModel::firstOrNew(['user_id' => $user->id]);
        $representative->sales_manager_id = $arr_user_data['sales_manager_id'];
        $representative->area_id          = isset($area_id)?$area_id:0;
        $representative->category_id      = $category;


        $representative->save();

        /*Check if record available or not in mapping table*/
        $check_record = $this->VendorRepresentativeMappingModel->where('representative_id',$user_id)->get();

        // dd($check_record);
        // Delete all record of representative
        if($check_record)
        {
            $this->VendorRepresentativeMappingModel->where('representative_id',$user_id)->delete();
        }

        if ($request->input('vendor_id') != "") {
            // dd($value);
            foreach ($request->input('vendor_id') as $key => $value) {
                $this->VendorRepresentativeMappingModel->create(['representative_id' => $user->id, 'vendor_id'=>$value]);
            }
            
        }

   
          
        if($is_update==false)
        {

           //send login credentials to the  representative

            $credentials = ['email' => $arr_user_data['email']];
      
            $arr_user = get_user_by_credentials($credentials);

            /*Get site setting data from helper*/
            $arr_site_setting = get_site_settings(['site_name','website_url']);


            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>';


            $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                                  'USER_ROLE'    => 'representative',
                                  'EMAIL'        => $arr_user['email'],
                                  'PASSWORD'     => $user_password,
                                  'LOGIN_URL'    => $reminder_url,
                                  'APP_URL'      => $arr_site_setting['website_url']
                                ];



            $arr_mail_data                          = [];
            $arr_mail_data['email_template_id']     = '34';
            $arr_mail_data['arr_built_content']     = $arr_built_content;
            $arr_mail_data['arr_user']              = $arr_user;


            //$arr_mail_data = $this->built_mail_data($arr_user_data['email'],$user_password); 

            $email_status  = $this->EmailService->send_mail($arr_mail_data);

            $response['description']     = str_singular($this->module_title)." has been created.";
        }
        else
        {
            $response['description']     = str_singular($this->module_title)." has been updated.";
        }
    
        $response['status'] = "success";
        
        $response['url']    = $this->module_url_path;

        return response()->json($response); 
       
    }
    
   /* public function built_mail_data($email,$user_password)
    {   
        $credentials = ['email' => $email];
    
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';
    
        if($user)
        {
            $arr_user = $user->toArray();

            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>' ;

            $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                                  'USER_ROLE'    => 'representative',
                                  'EMAIL'        => $arr_user['email'],
                                  'PASSWORD'     => $user_password,
                                  'LOGIN_URL'    => $reminder_url,
                                  'APP_URL'      => $site_name
                              ];


            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '34';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['user']              = $arr_user;

            return $arr_mail_data;
        }
        return FALSE;
    }
*/

    function changeAprovalStatus(Request $request)
    {
          $representative_status = $request->input('representativeAprovalStatus');
          $representative_id     = $request->input('representative_id');
          $type                  = $request->input('type');

        
          if($representative_status=='1')
          {
            $this->UserModel->where('id',$representative_id)->update(['is_approved'=>1]);
            
            // $response['status']  = 'SUCCESS';
            // $response['message'] = $this->module_title.' has been approved.';

            if($type=='activate')
            {
                $response['status']  = 'SUCCESS';
                $response['message'] = $this->module_title.' has been approved.';
                $response['data'] = 'ACTIVE';
            } else {
                $response['status']  = 'SUCCESS';
                $response['message'] = $this->module_title.' has been disapproved.';
                $response['data'] = 'DEACTIVE';
            }

          }
          elseif($representative_status=='0')
          {
            $this->UserModel->where('id',$representative_id)->update(['is_approved'=>0]);

            // $response['status']  = 'SUCCESS';
            // $response['message'] = $this->module_title.' has been disapproved.';
             if($type=='activate')
            {
                $response['status']  = 'SUCCESS';
                $response['message'] = $this->module_title.' has been approved.';
                $response['data'] = 'ACTIVE';
            } else {
                $response['status']  = 'SUCCESS';
                $response['message'] = $this->module_title.' has been disapproved.';
                $response['data'] = 'DEACTIVE';
            }
          }
          else
          {
            $response['status']  = 'ERROR';
            $response['message'] = 'Something went wrong, please try again.';
          }
          
        return response()->json($response); 
    }

    public function report_generator(Request $request,$type=false)
    {         $type  = \Request::segment(4);

        if($type=="pdf")
        {
         $inventory_pdf =  $this->PdfReportService->downloadPdfRepresentative($type);
         
          return $inventory_pdf->download('representative report.pdf');
        }
        else
        {
          $this->ReportService->downloadExcelRepresentative($type);
        }
    }

    public function delete_vendor(Request $request,$id)
    {
        $id = base64_decode($id);
    
       
        $data = $this->VendorRepresentativeMappingModel->where('id',$id)->first();
    
        $representative_id = $data['representative_id'];
     
        $vendor_id = $data['vendor_id'];
       
        $delete = $this->VendorRepresentativeMappingModel->where('representative_id',$representative_id)
                                                         ->where('vendor_id',$vendor_id)
                                                         ->delete();
       
        if($delete)
        {
            Flash::success('Vendor has been deleted.');
            return redirect()->back();
        }    
        else
        {
            Flash::error('Error occurred while vendor deletion.');
            return redirect()->back();
        }

        
    }


public function fetch_area(Request $request)
    {
        $sales_manager_id=$request->get('sales_manager_id');
        $area = [];
        $getVendorsData = [];
        $area_obj = $this->SalesManagerModel->with(['area_details'])
                                         ->withCount(['area_details as area_name' => function($query) {
                                                $query->select(DB::raw('area_name'));
                                            }])
                                        ->where('user_id',$sales_manager_id)
                                        ->groupBy('area_id')
                                        ->orderBy('area_name','ASC')
                                        ->get();
                                       
        if(isset($area_obj))
        {
            $area = $area_obj->toArray();
        }                            
                                        
        $getVendorsData_obj =  $this->VendorSalesmanagerMappingModel
                                ->with(['get_user_details'=> function($query){ 
                                    $query->where([['status',1],['is_approved',1]]);
                                }])
                                ->with(['get_maker_details'=> function($query){ 
                                    $query->orderBy('company_name','ASC');
                                }])
                                ->where('salesmanager_id','=',$sales_manager_id)
                                ->get();
                               
        
        
        if(isset($getVendorsData_obj))
        {
            $getVendorsData = $getVendorsData_obj->toArray();
        }

        return response()->json([$area,$getVendorsData]);

    }


    public function fetch_category(Request $request)
    {
        $category_details_arr = [];
        $area_id=$request->get('area_id');
      
        $category_obj = $this->RepAreaModel->where('id',$area_id)->first();

        if(isset($category_obj))
        {
            $category_arr = $category_obj->toArray(); 
            
        }
       
        $category_id_arr = json_decode($category_arr['category_id']);
   
        if(isset($category_id_arr) && count($category_id_arr)>0)
        {
            $category_details_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->orderBy('cat_division_name','ASC')->get()->toArray(); 
        }
       
        return response()->json($category_details_arr);

    }
}
