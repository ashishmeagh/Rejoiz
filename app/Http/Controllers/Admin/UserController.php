<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\CountryModel;
use App\Events\ActivityLogEvent;
use App\Models\ActivityLogsModel;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\RoleUsersModel;
use App\Common\Services\EmailService;

use Flash;
use Validator;
use Sentinel;
use Activation;
use DB;
use Datatables;
use Mail;



class UserController extends Controller
{

    public function __construct ( 
                                  UserModel  $usermodel,
                                  StateModel $statemodel,
                                  CityModel  $citymodel,
                                  CountryModel $countrymodel,
                                  ActivityLogsModel $activity_logs,
                                  RoleUsersModel  $role_user_model,
                                  EmailService $EmailService
                                )
    {
       
        $this->EmailService                 = $EmailService;
        $this->UserModel                    = $usermodel;
        $this->BaseModel                    = $this->UserModel;
        $this->CountryModel                 = $countrymodel;
        $this->ActivityLogsModel            = $activity_logs;
        $this->CityModel                    = $citymodel;
        $this->StateModel                   = $statemodel;
        $this->RoleUsersModel               = $role_user_model;
        $this->user_profile_base_img_path   = base_path().config('app.project.img_path.user_profile_image');
        $this->user_profile_public_img_path = url('/').config('app.project.img_path.user_profile_image');
        $this->arr_view_data                = [];
        $this->module_url_path              = url(config('app.project.admin_panel_slug')."/users");
        $this->module_title                 = "Users";
        $this->modyle_url_slug              = "users";
        $this->module_view_folder           = "admin.users";
       
    }	

    public function index()
    {
        $this->arr_view_data['arr_data'] = array();
        $arr_data = $this->BaseModel->whereHas('roles',function($query)
                                        {
                                            $query->where('slug','!=','admin');        
                                        })
        						
        							->get()->toArray();

        $this->arr_view_data['page_title']      = "Manage ".$this->module_title;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['arr_data']        = isset($arr_data) ? $arr_data: [];
        

        return view($this->module_view_folder.'.index', $this->arr_view_data);
    }

    function get_users_details(Request $request)
    {
        $user_details           = $this->BaseModel->getTable();
        
        $prefixed_user_details  = DB::getTablePrefix().$this->BaseModel->getTable();
        $prefixed_role_table    = DB::getTablePrefix().$this->RoleUsersModel->getTable();

        /* Get role Details*/
        $role_slug = 'user';
        $role = Sentinel::findRoleBySlug($role_slug);

        /* filter user_id by using role_slug*/
        $arr_role_details = $this->RoleUsersModel->where('role_id',$role['id'])->get()->toArray();
 
        foreach($arr_role_details as  $role_users) 
        {
            $user_id[] = $role_users['user_id'];

        }


        $obj_user = DB::table($user_details)
                                ->select(DB::raw($prefixed_user_details.".id,".
                                                 $prefixed_user_details.".email,".
                                                 $prefixed_user_details.".phone,".
                                                 $prefixed_user_details.".is_active,".
   
                                                "CONCAT(".$prefixed_user_details.".first_name,' ',"
                                                          .$prefixed_user_details.".last_name) as user_name"
                                   ))

                                ->whereIn($user_details.'.id',$user_id)

                                ->where('id','!=',1)

                                ->whereNull($user_details.'.deleted_at')

                                ->orderBy($user_details.'.created_at','DESC');
        
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term = $arr_search_column['q_name'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_details.'.email','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_phone']) && $arr_search_column['q_phone']!="")
        {
            $search_term = $arr_search_column['q_phone'];
            $obj_user = $obj_user->where($user_details.'.phone','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term   = $arr_search_column['q_status'];
            $obj_user  = $obj_user->where($user_details.'.is_active','LIKE', '%'.$search_term.'%');
        }

        return $obj_user;
    }

    public function get_records(Request $request)
    {
        $obj_user        = $this->get_users_details($request);

        $current_context = $this;

        $json_result     = Datatables::of($obj_user);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data) use ($current_context)
                            {
                                return base64_encode($data->id);
                            })

                            ->editColumn('user_name',function($data) use ($current_context)
                            {
                                if($data->user_name != '')
                                {
                                    return $data->user_name; 
                                }
                                else
                                {
                                     return "N/A";
                                }
                            })

                            ->editColumn('email',function($data) use ($current_context)
                            {

                                if($data->email != "")
                                {
                                    return $data->email;
                                }
                                else
                                {
                                    return "N/A";
                                }

                            })
                          
                            ->editColumn('phone',function($data) use ($current_context)
                            {

                                if($data->phone != "")
                                {
                                    return $data->phone;
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
                                    $msg = "'Are you sure? Do you want to activate this record.'";   

                                    $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';

                                }
                                elseif($data->is_active == '1')
                                {
                                    $msg = "'Are you sure? Do you want to deactivate this record.'";

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
        $arr_country = $this->CountryModel->where('is_active','=','1')->get(['id','country_code','country_name'])->toArray();  
      

        $this->arr_view_data['page_title']      = "Create ".str_singular($this->module_title);
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['arr_country']     = isset($arr_country) ? $arr_country:[];
        
        return view($this->module_view_folder.'.create', $this->arr_view_data);
    }

    

    public function edit($enc_id)
    {
        $id = base64_decode($enc_id);
     
        $obj_user = $this->BaseModel->where('id',$id)->first();

        $arr_data = $arr_country = $arr_state = $arr_city = [];                                    
        
        if($obj_user)
        {
            $arr_data = $obj_user->toArray();
        }  

         /* get country*/
        $arr_country = $this->CountryModel->where('is_active','=','1')->get(['id','country_code','country_name'])->toArray();  
     
         /* get state*/
        $arr_state = $this->StateModel->where('is_active','=','1')->where('country_id',$arr_data['country'])->get()->toArray();   
    
        /* get city*/

        $arr_city = $this->CityModel->where('is_active','=','1')->where('state_id',$arr_data['state'])->get()->toArray();

       
        $this->arr_view_data['page_title']                   = "Edit ".str_singular($this->module_title);
        $this->arr_view_data['module_title']                 = $this->module_title;
        $this->arr_view_data['module_url_path']              = $this->module_url_path;
        $this->arr_view_data['arr_data']                     = isset($arr_data) ? $arr_data:[];
        $this->arr_view_data['arr_country']                  = isset($arr_country) ? $arr_country : [];
        $this->arr_view_data['arr_state']                    = isset($arr_state) ? $arr_state :[];
        $this->arr_view_data['arr_city']                     = isset($arr_city)?$arr_city:[];

        $this->arr_view_data['user_profile_public_img_path'] = $this->user_profile_public_img_path;
        
        return view($this->module_view_folder.'.edit', $this->arr_view_data);
    }


    public function get_state_records(Request $request)
    {
        $arr_state  = [];
        $country_id = $request->input('countryId');

        $arr_state  = $this->StateModel->where('country_id',$country_id)->get()->toArray();

        $response['arr_state'] = isset($arr_state) ? $arr_state : []; 

        return $response;
    }

    public function get_city_records(Request $request)
    {

        $arr_city = [];
        $state_id = $request->input('stateId');

        $arr_city = $this->CityModel->where('state_id',$state_id)->get()->toArray();

        $response['arr_city'] = isset($arr_city) ? $arr_city : [];

        return $response;
    }


    // public function store(Request $request)
    // { 
    //     $is_update = false;

    //     $form_data = $request->all();

    //     $user_id = $request->input('user_id');

    //     if($request->has('user_id'))
    //     {
    //        $is_update = true;

    //     }

    //     /*Check validations*/
    //     $arr_rules = [
    //                     'first_name'=>'required',
    //                     'last_name'=>'required',
    //                     'email'=>'required|email',
    //                     'phone'=>'required|numeric',
    //                     'country'=>'required',
    //                     'state'=>'required|numeric',
    //                     'city'=>'required|numeric',
    //                     'street_address'=>'required',
    //                     'pincode'=> 'required|numeric'
    //                  ];

    //     if($is_update == false)
    //     {
    //         $arr_rules['profile_image'] = 'required';
    //     }

    //     $validator = Validator::make($request->all(),$arr_rules);

    //     if($validator->fails())
    //     {
    //         return redirect()->back()
    //                         ->withInput($request->all())
    //                         ->withErrors($validator);
    //     }

       
    //     /* Check for email duplication */
    //     $is_duplicate = $this->BaseModel->where('email','=',$request->input('email'));  
        
    //     if($is_update)
    //     {
    //         $is_duplicate = $is_duplicate->where('id','<>',$user_id);
    //     }

    //     $does_exists = $is_duplicate->count();

    //     if($does_exists)
    //     {
    //        $response['status']      = "error";
    //        $response['description'] = "Email Id Already Exists";
    //        return response()->json($response);
    //     }   
         
    //     $user =  Sentinel::createModel()->firstOrNew(['id' => $user_id]);

    //     $user->first_name = $request->input('first_name');
    //     $user->email      = $request->input('email');
    //     $password         = generateRandomPassword();
    //     $hasher           = Sentinel::getHasher();
    //     $user->password   = $hasher->hash($password);

    //     //send mail pass user id and activation code
    //     $email            = $request->input('email');
    //     $user_details     = $user->save();
    //     $arr_mail_data    = $this->built_mail_data($email,$password);        
    //     $email_status     = $this->EmailService->send_mail($arr_mail_data);


    //     if($is_update == false)
    //     {
    //         /* Activate User By Default */
    //         $activation = Activation::create($user);    

    //         if($activation)
    //         {
    //             Activation::complete($user,$activation->code);
    //         }
    //     }

    //     $profile_image   = $request->file('profile_image');
    //     $file_name       = "default.jpg";

    //     if(isset($profile_image))
    //     {
           
    //         $file_extension = strtolower($request->file('profile_image')->getClientOriginalExtension()); 
    //         $file_name      = sha1(uniqid().$file_name.uniqid()).'.'.$file_extension;

           
    //             if($request->file('profile_image')->move($this->user_profile_base_img_path, $file_name)) 
    //             {
    //                 if(isset($profile_image))
    //                 {
    //                     $user->profile_image  = $file_name;    
    //                 }
                   
    //                 $obj_image = $this->BaseModel->where('id',$user_id)->first(['profile_image']);
                  
    //                 if($obj_image)   
    //                 {   
    //                     $_arr = [];
    //                     $_arr = $obj_image->toArray();
    //                     if(isset($_arr['profile_image']) && $_arr['profile_image'] != "" )
    //                     {
    //                         $unlink_path    = $this->user_profile_base_img_path.$_arr['profile_image'];
    //                         @unlink($unlink_path);
    //                     }
    //                 }    
    //             }                
    //     }
    //     else
    //     {
    //         $file_name  = $request->input('old_image');
    //     }  


    //     if($user_details)
    //     {   
           
    //         $user->last_name      = $request->input('last_name');
    //         $user->country        = $request->input('country');
    //         $user->state          = $request->input('state');
    //         $user->city           = $request->input('city');
    //         $user->street_address = $request->input('street_address');
    //         $user->pincode        = $request->input('pincode');
    //         $user->phone          = $request->input('phone');
    //         $user->landmark       = $request->input('landmark');
    //         $user->profile_image  = $file_name;


    //         if(isset($form_data['status']) && !empty($form_data['status']))
    //         {
    //            $status = $form_data['status'];
    //         }
    //         else
    //         {
    //            $status = '0';
    //         }

    //         $user->is_active = $status;
      
            
    //         $user->save();

    //         if($is_update == false)
    //         {               
    //             $role_slug = 'user';
    //             $role = Sentinel::findRoleBySlug($role_slug);
    //             $role->users()->attach($user);    
    //         }

    //         if($is_update == false)
    //         {
    //             // add user details into Order_user_details table
    //             $inserted_data_arr = array('user_id'=>$user->id,'first_name'=>$user->first_name,'last_name'=>$user->last_name,'email'=>$email,'mobile'=>$user->phone);

    //             $this->OrderUserDetailsModel->create($inserted_data_arr);
    //         }
            

    //         /*-------------------------------------------------------
    //         |   Activity log Event
    //         --------------------------------------------------------*/
    //             $arr_event                 = [];
    //             $arr_event['ACTION']       = 'ADD';
    //             $arr_event['MODULE_TITLE'] = $this->module_title;

    //             $this->save_activity($arr_event);
    //         /*----------------------------------------------------------------------*/

    //         if($is_update  == false)
    //         {
    //             if($user->id)
    //             {
    //                 $response['link'] =$this->module_url_path.'/edit/'.base64_encode($user->id);
    //             }
    //         }
    //         else
    //         {
    //             $response['link'] = $this->module_url_path.'/edit/'.base64_encode($user_id);
    //         }


    //         $response['status']      = "success";
    //         $response['description'] = "User Save Successfully."; 
    //     }
    //     else
    //     {
    //         $response['status']      = "error";
    //         $response['description'] = "Error Occured While Adding User.";
    //     }

    //     return response()->json($response);
    // }

    // create mail structure
    public function built_mail_data($email,$password)
    {
      $user = $this->get_user_details($email);
      
      if($user)
      {
        $arr_user = $user->toArray();

        $login_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url("/loginNow").'">Login Now</a>.<br/>' ;

        $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';



        $arr_built_content = [
                                'USER_FNAME'   => $arr_user['first_name'],
                                'APP_NAME'     => $site_name,
                                'LOGIN_URL'    => $login_url,
                                'USER_EMAIL'   => $email,
                                'USER_PASSWORD' => $password
                             ];

        $arr_mail_data                      = [];
        $arr_mail_data['email_template_id'] = '30';
        $arr_mail_data['arr_built_content'] = $arr_built_content;
        $arr_mail_data['user']              = $arr_user;

        return $arr_mail_data;
      }
      return FALSE;
    }

    public function  get_user_details($email)
    {
        
        $credentials = ['email' => $email];
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        if($user)
        {
          return $user;
        }
        return FALSE;
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
            Flash::error('Error occurred while '.str_singular($this->module_title).' deletion.');
        }

        return redirect()->back();
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

        /* Check if array is supplied*/
        if(is_array($checked_record) && sizeof($checked_record)<=0)
        {
            Session::flash('error','Error occurred while doing multi action.');
            return redirect()->back();

        }
        
        foreach ($checked_record as $key => $record_id) 
        {  
            if($multi_action=="delete")
            {
               $this->perform_delete(base64_decode($record_id));    
               Flash::success($this->module_title.' has been deleted.'); 
            } 
            elseif($multi_action=="activate")
            {
               $this->perform_activate(base64_decode($record_id)); 
               Flash::success($this->module_title.' has been activated.'); 
            }
            elseif($multi_action=="deactivate")
            {
               $this->perform_deactivate(base64_decode($record_id));    
               Flash::success($this->module_title.' has been blocked.');  
            }
        }

        return redirect()->back();
    }

    public function perform_activate($id)
    {   
       
        $entity = $this->BaseModel->where('id',$id)->first();
        

        if($entity)
        {
            return $this->BaseModel->where('id',$id)->update(['is_active'=>'1']);
        }

        return FALSE;
    }

    public function perform_deactivate($id)
    {
      
        $entity = $this->BaseModel->where('id',$id)->first();
        
        if($entity)
        {
            return $this->BaseModel->where('id',$id)->update(['is_active'=>'0']);
        }
        return FALSE;
    }

    public function perform_delete($id)
    {
        $user = Sentinel::check();

        $entity = $this->BaseModel->where('id',$id)->first();

        if($entity)
        {

            /* Detaching Role from user Roles table */
            $user = Sentinel::findById($id);
            $role_owner     = Sentinel::findRoleBySlug('owner');
            $role_traveller = Sentinel::findRoleBySlug('traveller');
            $user->roles()->detach($role_owner);
            $user->roles()->detach($role_traveller);

            $delete_success = $this->BaseModel->where('id',$id)->delete();
            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
                $arr_event                 = [];
                $arr_event['ACTION']       = 'REMOVED';
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
           return $delete_success;
        }

        return FALSE;
    }

    public function build_select_options_array(array $arr_data,$option_key,$option_value,array $arr_default)
    {

        $arr_options = [];
        if(sizeof($arr_default)>0)
        {
            $arr_options =  $arr_default;   
        }

        if(sizeof($arr_data)>0)
        {
            foreach ($arr_data as $key => $data) 
            {
                if(isset($data[$option_key]) && isset($data[$option_value]))
                {
                    $arr_options[$data[$option_key]] = $data[$option_value];
                }
            }
        }

        return $arr_options;
    }
}
