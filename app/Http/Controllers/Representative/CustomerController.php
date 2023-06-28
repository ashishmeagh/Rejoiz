<?php

namespace App\Http\Controllers\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;

use App\Models\ProductsModel;

use App\Models\AddressModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RoleModel;
use App\Models\RetailerModel;
use App\Models\RoleUsersModel;
use App\Models\CountryModel;
use App\Models\RepresentativeModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Models\StateZipCodeModel;
use App\Models\SiteSettingModel;
use App\Common\Services\UserService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Traits\MultiActionTrait;

use Sentinel;
use Validator;
use DB;
use Datatables;

class CustomerController extends Controller
{
    use MultiActionTrait;   
	public function __construct(ProductsModel $ProductsModel,
								UserService $UserService,
                                EmailService $EmailService,
                                CountryModel $CountryModel,
                                RetailerModel $RetailerModel,
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                AddressModel $AddressModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                GeneralService $GeneralService,
                                RetailerRepresentativeMappingModel $RetailerRepresentativeMappingModel,
                                RepresentativeModel $RepresentativeModel,
                                StateZipCodeModel $StateZipCodeModel
                             
                             )
    {
        $this->AddressModel                      = $AddressModel;
        $this->RetailerModel                     = $RetailerModel;
        $this->UserModel                         = $UserModel;
        $this->RoleModel                         = $RoleModel;
        $this->RoleUsersModel                    = $RoleUsersModel;
        $this->RepresentativeLeadsModel          = $RepresentativeLeadsModel;
        $this->RepresentativeProductLeadsModel   = $RepresentativeProductLeadsModel;
        $this->ProductsModel                     = $ProductsModel;
        $this->RetailerRepresentativeMappingModel= $RetailerRepresentativeMappingModel;
        $this->GeneralService                    = $GeneralService;
        $this->UserService  	                 = $UserService;
        $this->EmailService                      = $EmailService;
        $this->CountryModel                      = $CountryModel;
        $this->RepresentativeModel               = $RepresentativeModel;
        $this->StateZipCodeModel                 = $StateZipCodeModel;


        $this->arr_view_data      = [];
        $this->module_title       = "Customers";
        $this->module_view_folder = 'representative.retailer';
        $this->representative_panel_slug   = config('app.project.representative_panel_slug');
        $this->module_url_path             = url($this->representative_panel_slug.'/retailer');
        $this->lead_module_url_path        = url($this->representative_panel_slug.'/leads');
    }


    public function index()
    { 
      	$this->arr_view_data['module_title']         = 'My '.$this->module_title;
        $this->arr_view_data['page_title']           = 'My Customers';
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['lead_module_url_path'] = $this->lead_module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function customers_listing(Request $request)
    {

    	$input = $request->all();

        $arr_search_column = $request->input('column_filter');

        $user = Sentinel::check();
    	$loggedIn_userId = 0;

    	if($user)
   		{
        	$loggedIn_userId = $user->id;
    	}    

    	$user_table =  $this->UserModel->getTable();
  		$prefix_user_table = DB::getTablePrefix().$user_table;

  		$retailer_representative_table =  $this->RetailerRepresentativeMappingModel->getTable();
  		$prefix_retailer_representative_table = DB::getTablePrefix().$retailer_representative_table;


  		$role_table =  $this->RoleModel->getTable();
  		$prefix_role_table = DB::getTablePrefix().$role_table;

  		$role_user_table =  $this->RoleUsersModel->getTable();
  		$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $retailer_table =  $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

  		$obj_user = DB::table($user_table)
						->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".country_code, ".
                                     $prefix_user_table.".status as status, ".
                                     $prefix_user_table.".is_approved, ".
                                     $prefix_retailer_table.".store_name as store_name, ".
                                     
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                               ))
						->leftJoin($retailer_representative_table,$retailer_representative_table.'.retailer_id','=',$user_table.'.id')
                        ->leftJoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$user_table.'.id')
						->where($retailer_representative_table.'.representative_id',$loggedIn_userId);


                        // dd($obj_user);

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term      = $arr_search_column['q_name'];
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
            $obj_user = $obj_user->where($user_table.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_shop_name']) && $arr_search_column['q_shop_name']!="")
        {
            $search_term      = $arr_search_column['q_shop_name'];
            $obj_user = $obj_user->where($prefix_retailer_table.'.store_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
            $search_term = $arr_search_column['q_is_approved'];
            $obj_user    = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }


       

        $json_result     = \Datatables::of($obj_user);
      
        $json_result     = $json_result->editColumn('enc_id',function($data)
                        {
                            
                            return base64_encode($data->id);
                        })

                        ->editColumn('contact_no',function($data)
                        {
                            // $countryCode = $data->country_code;
                            // $data->contact_no = str_replace($countryCode, "", $data->contact_no);

                            // return $contact_no = $countryCode .'-'. get_contact_no($data->contact_no);
                            if($data->country_code != "")
                              {
                                  $countryCode = $data->country_code;
                                  $data->contact_no = str_replace($countryCode, "", $data->contact_no);
                                  $contact_no = $countryCode .'-'.get_contact_no($data->contact_no);                              
                                  return $contact_no;
                              }
                              else
                              {
                                  $contact_no = get_contact_no($data->contact_no);                              
                                  return $contact_no;
                              }
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
                                $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                            }
                            return $build_status_btn;
                        })  

                        ->editColumn('admin_approval',function($data)
                        {
                            $build_status_btn ='';

                            if($data->is_approved == '0')
                            {   
                                $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" readonly="true"/>';
                            }
                            elseif($data->is_approved == '1')
                            {
                                $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" readonly="true"/>';
                            }
                            return $build_status_btn;
                        })  

                        ->make(true);
   
        $build_result = $json_result->getData();



        return response()->json($build_result);               
    }

    public function create()
    { 
        $arr_country = $this->CountryModel->where('is_active','1')->orderBy('id','ASC')->get()->toArray();  
        $this->arr_view_data['country_arr'] = $arr_country;
        
        $this->arr_view_data['page_title']      = 'Add Customer';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function save(Request $request)
    { 
        
        try
        {
            $arr_rules = [
                            'first_name'=>'required',
                            'last_name'=>'required',
                            'country_code'=>'required',
                            'email'=>'required|email',
                            'contact_no'=>'required',
                            /*'complete_addr'=>'required',
                            'state'=>'required',
                            'city'=>'required',
                            'zip_code'=>'required',
                            'buying_status'=>'required',*/
                         ];
           
            $validator = Validator::make($request->all(),$arr_rules);

            if($validator->fails())
            {
                $response['status'] = 'warning';
                $response['description'] = 'Form validation failed, please check all fields.';

                return response()->json($response);
            }

            DB::beginTransaction();
            $users = Sentinel::check();
            
            $loggedIn_userId = 0;

            if($users)
            {
                $loggedIn_userId = $users->id;
              
            }    
            $is_update = false;

            $form_data = $request->all();

            $user_id = $request->input('user_id');

            if($request->has('user_id'))
            {
               $is_update = true;
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

             /*check tax_id*/                
            $arr_user_data['tax_id']      = isset($request['tax_id'])?$request['tax_id']:'';

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

            $arr_user_data['first_name'] = isset($request['first_name'])?$request['first_name']:'';
            $arr_user_data['last_name']  = isset($request['last_name'])?$request['last_name']:'';
            $arr_user_data['country_id']  = isset($request['country_code'])?$request['country_code']:'';
            $arr_user_data['email']      = isset($request['email'])?$request['email']:'';
            $arr_user_data['contact_no'] = isset($request['contact_no'])?$request['contact_no']:'';
            $arr_user_data['buying_status'] = isset($request['buying_status'])?$request['buying_status']:'';
            $arr_user_data['country_code'] = isset($request['hid_country_code'])?$request['hid_country_code']:'';

            $arr_user_data['buying_status'] = (int)$arr_user_data['buying_status'];
            
            if((int)$arr_user_data['buying_status'] == 1)
            {
                $arr_user_data['store_name'] = isset($request['store_name'])?$request['store_name']:'';
                $arr_user_data['annual_sales'] = isset($request['Annual_Sales'])?$request['Annual_Sales']:'';
                $arr_user_data['years_in_business'] = isset($request['years_in_business'])?$request['years_in_business']:'';

                $arr_user_data['store_website'] = '';
                $arr_user_data['zip_code'] = '';
            }
            else if($arr_user_data['buying_status'] == 2)
            {
                $arr_user_data['store_website'] = isset($request['store_website'])?$request['store_website']:'';
                $arr_user_data['store_name'] = isset($request['store_name'])?$request['store_name']:'';

                $arr_user_data['annual_sales'] = '';
                $arr_user_data['years_in_business'] = '';
                $arr_user_data['zip_code'] = '';
            }
            else if($arr_user_data['buying_status'] == 3)
            {
                $arr_user_data['zip_code'] = isset($request['zip_code'])?$request['zip_code']:'';
                $arr_user_data['store_name'] = isset($request['store_name'])?$request['store_name']:'';

                $arr_user_data['annual_sales'] = '';
                $arr_user_data['years_in_business'] = '';
                $arr_user_data['store_website'] = '';
            }
            else
            {
                $arr_user_data['store_name'] = '';
                $arr_user_data['annual_sales'] = '';
                $arr_user_data['years_in_business'] = '';
                $arr_user_data['store_website'] = '';
                $arr_user_data['zip_code'] = '';
            }


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
                    $role = Sentinel::findRoleBySlug('retailer');

                    $role->users()->attach($user);
                }
            }
            else
            {
                Sentinel::update($user, [
                    'email' => $arr_user_data['email'],
                   
                ]);
            }

            // $contact_no = str_replace($arr_user_data['country_code'], "", $arr_user_data['contact_no']);

            $user->first_name       = $arr_user_data['first_name'];
            $user->last_name        = $arr_user_data['last_name'];
            $user->country_id       = $arr_user_data['country_id'];
            $user->email            = $arr_user_data['email'];
            $user->tax_id            = $arr_user_data['tax_id'];
            $user->post_code        = $arr_user_data['zip_code'];
            $user->buying_status    = $arr_user_data['buying_status'];
            $user->country_code     = $arr_user_data['country_code'];
            $user->store_name       = $arr_user_data['store_name'];
            $user->store_website    = $arr_user_data['store_website'];
            $user->contact_no       = $arr_user_data['contact_no'];
            
            $user->save();


            $retailer_representative_table = RetailerRepresentativeMappingModel::firstOrNew(['retailer_id' => $user->id]);

            $retailer_representative_table->representative_id = $loggedIn_userId;

            $retailer_representative_table->save();

            $retailer_table = RetailerModel::firstOrNew(['user_id' => $user->id]);

            $retailer_table->years_in_business  = $arr_user_data['years_in_business'];
            $retailer_table->store_name         = $arr_user_data['store_name'];
            $retailer_table->annual_sales       = $arr_user_data['annual_sales'];
            $retailer_table->country            = $arr_user_data['country_code'];
            $retailer_table->store_website      = $arr_user_data['store_website'];

            $retailer_table->save();


            if($is_update==false)
            {

                //send credentials mail to the retailer after add

                $arr_mail_data = $this->built_mail_data($arr_user_data['email'],$user_password); 


                $email_status  = $this->EmailService->send_mail($arr_mail_data);



                DB::commit();
                $response['description'] = str_singular($this->module_title)." has been created.";
                $response['status']      = 'success'; 
                $response['url']         = $this->module_url_path;

                //Send notification to Admin
                $notification_arr                 = [];
                $notification_arr['from_user_id'] = $loggedIn_userId;
                $notification_arr['to_user_id']   = get_admin_id();
                $notification_arr['description']  = 'A New Customer '.$arr_user_data['first_name'].' '.$arr_user_data['last_name'].' has been registered by Representative ' . $users->first_name.' ' .$users->last_name ;

                $notification_arr['title']        = 'New User Registration';
                $notification_arr['type']         = 'admin';  
                $notification_arr['status']       = '0';  
                $notification_arr['link']         = url('/').'/admin/retailer/view/'.base64_encode($user->id);  
                   
                $this->GeneralService->save_notification($notification_arr);            

                return response()->json($response);
            }
            else
            {
                
                DB::commit();
                $response['description']     = str_singular($this->module_title)." has been updated.";
                $response['status']      = 'success';  
                $response['url']         = $this->module_url_path;      
                return response()->json($response);
            }
        }
        catch(Exception $e)
        {
            DB::rollback();
      
            $response['status']      = 'FAILURE';
            $response['description'] = $e->getMessage();
            $response['url']         = $this->module_url_path;    
            return response()->json($response);
        }
    } 

    public function view_customer($customer_id)
    {

      	$loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }

        $cust_id = base64_decode($customer_id);
       // $arr_user_data = $this->RetailerModel->with(['user_details'])->where('user_id',$cust_id)->first()->toArray();

        $arr_user_data = $this->UserService->get_user_information($cust_id,"retailer");

       
		$arr_user = [];

		$this->arr_view_data['arr_user']        = isset($arr_user_data['user_details'])?$arr_user_data['user_details']:[];


        $this->arr_view_data['addr_details']    = $arr_user_data;
        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = 'My '.str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        // dd($this->arr_view_data);
    	return view($this->module_view_folder.'.view',$this->arr_view_data);

    }

    
    public function built_mail_data($email,$user_password)
    {   
        $credentials = ['email' => $email];
    
        $user = Sentinel::findByCredentials($credentials); // check if user exists
    
        if($user)
        {
            $arr_user = $user->toArray();

            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>' ;

            $site_setting_obj = SiteSettingModel::first();
            
            if($site_setting_obj)
            {
                $site_setting_arr = $site_setting_obj->toArray();            
            }

            $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'; 


            $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                                  'EMAIL'        => $arr_user['email'],
                                  'PASSWORD'     => $user_password,
                                  'LOGIN_URL'    => $reminder_url,
                                  'USER_ROLE'    => "Retailer",
                                  'APP_URL'      => $site_name
                              ];


            $arr_mail_data                       = [];
            $arr_mail_data['email_template_id']  = '34';
            $arr_mail_data['arr_built_content']  = $arr_built_content;
            $arr_mail_data['arr_user']           = $arr_user;



            return $arr_mail_data;
        }
        return FALSE;
    }

    public function edit_customer($customer_id)
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }

        $arr_country = $this->CountryModel->where('is_active','1')->orderBy('id','ASC')->get()->toArray();  
        

        $cust_id = base64_decode($customer_id);

        // $arr_user_data = $this->UserService->get_user_info($cust_id,"retailer");
        $arr_user_data = $this->UserService->get_user_information($cust_id,"retailer");

        // dd($arr_user_data);

        $arr_user = [];

        $this->arr_view_data['country_arr']     = $arr_country;
        // $this->arr_view_data['arr_user']        = isset($arr_user_data['user_details'])?$arr_user_data['user_details']:'';
        // $this->arr_view_data['arr_user_address']= $arr_user_data;
        $this->arr_view_data['arr_user']= $arr_user_data;
        $this->arr_view_data['arr_user_address']= isset($arr_user_data['retailer_details'])?$arr_user_data['retailer_details']:'';

        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        // dd($this->arr_view_data);
        
        return view($this->module_view_folder.'.edit',$this->arr_view_data);

    }


    function changeAprovalStatus(Request $request)
    {
        
      $sales_manager_status = $request->input('salesManagerAprovalstatus');
      $sales_manager_id     = $request->input('salesmanager_id');

    
      if($sales_manager_status=='1')
      {
        $this->UserModel->where('id',$sales_manager_id)->update(['is_approved'=>1]);
        
        $response['status']  = 'SUCCESS';
        $response['message'] = $this->module_title.' has been approved.';

      }
      elseif($sales_manager_status=='0')
      {
        $this->UserModel->where('id',$sales_manager_id)->update(['is_approved'=>0]);

        $response['status']  = 'SUCCESS';
        $response['message'] = $this->module_title.' has been disapproved.';
      }
      else
      {
        $response['status']  = 'ERROR';
        $response['message'] = 'Something went wrong, please try again.';
      }
      
    return response()->json($response); 
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
        return FALSE;
    }

    public function retailer_by_zip_code()
    {
        $this->arr_view_data['module_title']         = $this->module_title;
        $this->arr_view_data['page_title']           = 'Customers';
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['lead_module_url_path'] = $this->lead_module_url_path;

        return view($this->module_view_folder.'.retailer_by_zip_code',$this->arr_view_data);
    }
    
    public function get_match_zip_retailer(Request $request)
    {

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }

        //Get area id
        $arrRep     = [];
        $arrZipCode = [];
        $objRep = $this->RepresentativeModel->where('user_id',$loggedInUserId)->first(['area_id']);
        if($objRep)
        {
            $arrRep = $objRep->toArray();
        }

        $areaId = isset($arrRep['area_id'])?$arrRep['area_id']:"0";
        //Get zip code from area
        $arrZipCode = $this->StateZipCodeModel->where('area_id',$areaId)->get(['zip_code'])->toArray();

        //Match zip code retailer
        
        $arrZipCodeRetailer = $this->RetailerModel->whereIn('billing_zip_postal_code',$arrZipCode)->get()->toArray();
        //dd($arrZipCodeRetailer);

        $input = $request->all();

        $arr_search_column = $request->input('column_filter');

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $retailer_representative_table =  $this->RetailerRepresentativeMappingModel->getTable();
        $prefix_retailer_representative_table = DB::getTablePrefix().$retailer_representative_table;


        $role_table =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $retailer_table =  $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $obj_user = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".status as status, ".
                                     $prefix_retailer_table.".store_name as store_name, ".
                                     
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                               ))
                        ->leftJoin($retailer_representative_table,$retailer_representative_table.'.retailer_id','=',$user_table.'.id')
                        ->leftJoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$user_table.'.id')
                        ->whereIn($retailer_table.'.billing_zip_postal_code',$arrZipCode);

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term      = $arr_search_column['q_name'];
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
            $obj_user = $obj_user->where($user_table.'.status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_shop_name']) && $arr_search_column['q_shop_name']!="")
        {
            $search_term      = $arr_search_column['q_shop_name'];
            $obj_user = $obj_user->where($prefix_retailer_table.'.store_name','LIKE', '%'.$search_term.'%');
        }

       

        $json_result     = \Datatables::of($obj_user);
      
        $json_result     = $json_result->editColumn('enc_id',function($data)
                        {
                            
                            return base64_encode($data->id);
                        })

                        ->editColumn('contact_no',function($data)
                        {

                          return $contact_no = get_contact_no($data->contact_no);
                        })
                        /*->editColumn('build_status_btn',function($data)
                        {
                            $build_status_btn ='';
                            if($data->status == '0')
                            {   
                                $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';
                            }
                            elseif($data->status == '1')
                            {
                                $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                            }
                            return $build_status_btn;
                        })*/  
                        ->make(true);
   
        $build_result = $json_result->getData();



        return response()->json($build_result);                               

    }

    public function view_zip_code_retailer($customer_id)
    {

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }

        $cust_id = base64_decode($customer_id);
        $arr_user_data = $this->RetailerModel->with(['user_details'])->where('user_id',$cust_id)->first()->toArray();
       
        $arr_user = [];

        $this->arr_view_data['arr_user']        = isset($arr_user_data['user_details'])?$arr_user_data['user_details']:[];


        $this->arr_view_data['addr_details']    = $arr_user_data;
        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path.'/retailer_by_zip_code';
         
        return view($this->module_view_folder.'.retailer_by_zip_code_view',$this->arr_view_data);

    }

}
