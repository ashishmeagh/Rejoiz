<?php

namespace App\Http\Controllers\sales_manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RetailerModel;
use App\Models\CountryModel;
use App\Models\AddressModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Common\Services\UserService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Traits\MultiActionTrait;
use App\Models\SiteSettingModel;


use Sentinel;
use Validator;
use DB;
use Datatables;

class CustomerController extends Controller
{
    use MultiActionTrait;
    
	  public function __construct(
								              UserService    $UserService,
                              EmailService   $EmailService,
                              UserModel      $UserModel,
                              RoleModel      $RoleModel,
                              AddressModel   $AddressModel,
                              CountryModel   $CountryModel,
                              RetailerModel  $RetailerModel,
                              RoleUsersModel $RoleUsersModel,
                              GeneralService $GeneralService,
                              RetailerRepresentativeMappingModel $RetailerRepresentativeMappingModel
                              
                             )
    {
      
      $this->UserModel                           = $UserModel;
      $this->BaseModel                           = $UserModel;
      $this->RoleModel                           = $RoleModel;
      $this->CountryModel                        = $CountryModel;
      $this->RoleUsersModel                      = $RoleUsersModel;
      $this->RetailerModel                       = $RetailerModel;
      $this->RetailerRepresentativeMappingModel  = $RetailerRepresentativeMappingModel;
      $this->GeneralService                      = $GeneralService;
      $this->UserService  	                     = $UserService;
      $this->EmailService                        = $EmailService;
      $this->AddressModel                        = $AddressModel;

      $this->arr_view_data                       = [];
      $this->module_title                        = "Customers";
      $this->module_view_folder                  = 'sales_manager.retailers';
      $this->sales_manager_panel_slug            = config('app.project.sales_manager_panel_slug');
      $this->module_url_path                     = url($this->sales_manager_panel_slug.'/retailer');
      
    }


    public function index()
    {
      	$this->arr_view_data['module_title']         = $this->module_title;
        $this->arr_view_data['page_title']           = 'My Customers';
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    
    public function retailerList(Request $request)
    {

    	  $input = $request->all();

      	$arr_search_column = $request->input('column_filter');

      	$user = Sentinel::check();
      	$loggedIn_userId = 0;

      	if($user)
     		{
          $loggedIn_userId = $user->id;
        }    

      	$user_table        =  $this->UserModel->getTable();
  		  $prefix_user_table = DB::getTablePrefix().$user_table;

      	$retailer_representative_table =  $this->RetailerRepresentativeMappingModel->getTable();
      	$prefix_retailer_representative_table = DB::getTablePrefix().$retailer_representative_table;


      	$role_table        =  $this->RoleModel->getTable();
      	$prefix_role_table = DB::getTablePrefix().$role_table;

        $retailer_table        = $this->RetailerModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      	$role_user_table        =  $this->RoleUsersModel->getTable();
      	$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      	$obj_user = DB::table($user_table)
      						        ->select(DB::raw($prefix_user_table.".id as id,".
                                           $prefix_user_table.".email as email,".
                                           $prefix_user_table.".country_code,".
                                           $prefix_user_table.".contact_no as contact_no,".
                                           $prefix_user_table.".status,".
                                           $prefix_user_table.".is_approved,".
                                           $prefix_retailer_table.".store_name,".
                                           $prefix_retailer_table.".user_id as rid,".
           
                                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                   .$prefix_user_table.".last_name) as user_name"
                                        ))
      						        ->leftJoin($retailer_representative_table,$retailer_representative_table.'.retailer_id','=',$user_table.'.id')

                          ->leftJoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$user_table.'.id')

      						        ->where($retailer_representative_table.'.sales_manager_id',$loggedIn_userId);

        /* ---------------- Filtering Logic ----------------------------------*/  

              
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term  = $arr_search_column['q_name'];
            $obj_user     = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }	
        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term   = $arr_search_column['q_email'];
            $obj_user      = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }	

        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
            $search_term  = $arr_search_column['q_contact_no'];
            $obj_user     = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_store_name']) && $arr_search_column['q_store_name']!="")
        {
            $search_term   = $arr_search_column['q_store_name'];
            $obj_user      = $obj_user->where($prefix_retailer_table.'.store_name','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term  = $arr_search_column['q_status'];

            $obj_user     = $obj_user->where($user_table.'.status','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
            $search_term  = $arr_search_column['q_is_approved'];
            $obj_user     = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }



       

        $json_result     = \Datatables::of($obj_user);
       
        $json_result     =  $json_result->editColumn('enc_id',function($data)
                            {
                               return base64_encode($data->id);
                            })

                            ->editColumn('contact_no',function($data)
                            {
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

                            ->editColumn('admin_approval',function($data)
                            {
                                $admin_approval_btn ='';

                                if($data->is_approved == '0')
                                {   
                                    $admin_approval_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" readonly="true"/>';
                                }
                                elseif($data->is_approved == '1')
                                {
                                    $admin_approval_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" readonly="true"/>';
                                }
                                return $admin_approval_btn;
                            })   

                            
                            ->make(true);
       
        $build_result = $json_result->getData();

        return response()->json($build_result);   

    }

    public function view_customer($customer_id)
    {

      	$loggedInUserId = 0;
        $arr_user = [];
        $user = Sentinel::check();

        if($user)
        {
           $loggedInUserId = $user->id;
        }
   
        $cust_id       = base64_decode($customer_id);
        //$arr_user_data = $this->UserService->get_user_info($cust_id,"retailer");
        $arr_user_data = $this->UserService->get_user_information($cust_id,"retailer");


    		$this->arr_view_data['arr_user']        = isset($arr_user_data['user_details'])?$arr_user_data['user_details']:[];


        $this->arr_view_data['addr_details']    = $arr_user_data;
        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
            
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


            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '34';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['user']              = $arr_user;
            $arr_mail_data['arr_user']          = $arr_user;

            return $arr_mail_data;
        }

      return FALSE;
  }



    public function create()
    {
        $country_arr = [];
        $country_arr = $this->CountryModel->where('is_active','1')->orderBy('id','ASC')->get()->toArray();

        $this->arr_view_data['country_arr']     = $country_arr;

        $this->arr_view_data['page_title']      = 'Add '.str_singular( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
          
        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function save(Request $request)
    {  

        try
        {
            $arr_rules =  [
                            'first_name'=>'required',
                            'last_name' =>'required',
                            'email'     =>'required|email',
                            'contact_no'=>'required',
                            //'buying_status'=>'required',
                            'country_id'=>'required',
                            //'store_name'=>'required'
                          ];
           
            $validator = Validator::make($request->all(),$arr_rules);

            if($validator->fails())
            {
                $response['status']      = 'warning';
                $response['description'] = 'Form validation failed, please check all fields.';

                return response()->json($response);
            }

            DB::beginTransaction();
            $users = Sentinel::check();
            
            $loggedIn_userId = 0;
            $is_update = false;

            if($users)
            {
              $loggedIn_userId = $users->id;
            }


            $form_data = $request->all();
            $user_id   = $request->input('user_id');


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

            // $arr_user_data['first_name']    = $request->input('first_name');
            // $arr_user_data['last_name']     = $request->input('last_name');
            // $arr_user_data['email']         = $request->input('email');
            // $arr_user_data['country_code']    = $request->input('hid_country_code');
            // $arr_user_data['contact_no']    = $request->input('contact_no');
            // $arr_user_data['complete_addr'] = $request->input('complete_addr');
            // $arr_user_data['state']         = $request->input('state');
            // $arr_user_data['city']          = $request->input('city');
            // $arr_user_data['zip_code']      = $request->input('zip_code');
            // $arr_user_data['buying_status'] = $request->input('buying_status');
            // $arr_user_data['country_id']    = $request->input('country_id');
            // $arr_user_data['tax_id']        = $request->input('tax_id');
            // $arr_user_data['store_name']    = $request->input('store_name');
            // $arr_user_data['store_website'] = $request->input('store_website');
            // $arr_user_data['annual_sales']  = $request->input('Annual_Sales');
            // $arr_user_data['years_in_business']  = $request->input('years_in_business');

             $arr_user_data['first_name'] = isset($request['first_name'])?$request['first_name']:'';
            $arr_user_data['last_name']  = isset($request['last_name'])?$request['last_name']:'';
            $arr_user_data['country_id']  = isset($request['country_id'])?$request['country_id']:'';
            $arr_user_data['email']      = isset($request['email'])?$request['email']:'';
            $arr_user_data['tax_id']      = isset($request['tax_id'])?$request['tax_id']:'';
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
                    'email'    => $arr_user_data['email'],
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

            $user->first_name    = $arr_user_data['first_name'];
            $user->last_name     = $arr_user_data['last_name'];
            $user->email         = $arr_user_data['email'];
            $user->tax_id       = $arr_user_data['tax_id'];
            $user->country_id    = $arr_user_data['country_id'];
            // $user->state_id      = $arr_user_data['state'];
            // $user->city_id       = $arr_user_data['city'];
            $user->post_code     = $arr_user_data['zip_code'];
            $user->buying_status = $arr_user_data['buying_status'];
            // $user->tax_id        = $arr_user_data['tax_id'];
            $user->store_name    = $arr_user_data['store_name'];
            $user->contact_no    = $arr_user_data['contact_no'];
            $user->country_code    = $arr_user_data['country_code'];
            $user->store_website    = $arr_user_data['store_website'];

             
            $user->save();

            $address_tbl =  AddressModel::firstOrNew(['user_id' => $user->id]);

            $address_tbl->bill_first_name = isset($arr_user_data['first_name'])?$arr_user_data['first_name']:'';
            $address_tbl->bill_last_name  = isset($arr_user_data['last_name'])?$arr_user_data['last_name']:'';
            $address_tbl->bill_email      = isset($arr_user_data['email'])?$arr_user_data['email']:'';
            $address_tbl->bill_mobile_no  = isset($arr_user_data['contact_no'])?$arr_user_data['contact_no']:'';
            $address_tbl->bill_complete_address = isset($arr_user_data['complete_addr'])?$arr_user_data['complete_addr']:'';
            $address_tbl->bill_city       = isset($arr_user_data['city'])?$arr_user_data['city']:'';
            $address_tbl->bill_state      = isset($arr_user_data['state'])?$arr_user_data['state']:'';
            $address_tbl->bill_city       = isset($arr_user_data['city'])?$arr_user_data['city']:'';
            $address_tbl->bill_zip_code   = isset($arr_user_data['zip_code'])?$arr_user_data['zip_code']:'';

            $address_tbl->save();

            $retailer_representative_table = RetailerRepresentativeMappingModel::firstOrNew(['retailer_id' => $user->id]);

            $retailer_representative_table->sales_manager_id = $loggedIn_userId;
            $retailer_representative_table->retailer_id      = $user->id;

            $retailer_representative_table->save();


            $retailer_table = RetailerModel::firstOrNew(['user_id' => $user->id]);

            $retailer_table->years_in_business  = isset($arr_user_data['years_in_business'])?$arr_user_data['years_in_business']:'';
            $retailer_table->store_name         = isset($arr_user_data['store_name'])?$arr_user_data['store_name']:'';
            $retailer_table->annual_sales       = isset($arr_user_data['annual_sales'])?$arr_user_data['annual_sales']:'';
            $retailer_table->country            = $arr_user_data['country_code'];
            $retailer_table->store_website      = $arr_user_data['store_website'];

            $retailer_table->save();


            if($is_update==false)
            {
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
                $notification_arr['description']  = 'A New Customer '.$arr_user_data['first_name'].' '.$arr_user_data['last_name'].' has been registered by Sales Manager ' . $users->first_name.' ' .$users->last_name ;

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
                $response['description']  = str_singular($this->module_title)." has been updated.";
                $response['status']       = 'success';  
                $response['url']          = $this->module_url_path;      
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

      $arr_user = [];

      $this->arr_view_data['country_arr']     = $arr_country;
      // $this->arr_view_data['arr_user']        = isset($arr_user_data['user_details'])?$arr_user_data['user_details']:'';
      // $this->arr_view_data['arr_user_address']= $arr_user_data;
      $this->arr_view_data['arr_user']= $arr_user_data;
      $this->arr_view_data['arr_user_address']= isset($arr_user_data['retailer_details'])?$arr_user_data['retailer_details']:'';

      $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
      $this->arr_view_data['module_title']    = str_plural($this->module_title);
      $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
      return view($this->module_view_folder.'.edit',$this->arr_view_data);

    }


  public function statusUpdate(Request $request)
  {
      try
      {     $is_active    = '';
            $retailer_id  = base64_decode($request->input('retailer_id'));
            $status       = $request->input('status');
            $loggedInUserId  = 0;
            $user = Sentinel::check();
            if($user)
            {
               $loggedInUserId  = $user->id;
            }


            if($status == 'activate')
            {
                $is_active = '1';
                $response['message'] = 'Customer has been activated.';
            }
            else if($status == 'deactivate')
            {
                $is_active = '0';
                $response['message'] = 'Customer has been deactivated.';
            }

            $data['status'] = $is_active;

            $update = $this->UserModel->where('id',$retailer_id)->update($data);

            if($update)
            {
                $response['status']  = 'success';
                /*$response['message'] = 'Retailer has been activated.';*/

                /*-------------------activity log*------------------------------------*/
                $arr_event['ACTION']       = 'EDIT';
                $arr_event['MODULE_ID']    = $retailer_id;
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['MODULE_DATA']  = json_encode(['id'=>$retailer_id,'status'=>$status]);
                $arr_event['USER_ID']      = $loggedInUserId;

                $this->save_activity($arr_event);

                /*----------------------------------------------------------------------*/
            }
            else
            {
                $response['status']  = 'error';
                $response['message'] = 'Something went wrong, please try again.';
            }

            
        }
        catch(Exception $e)
        {
            $response['status']  = 'error';
            $response['message'] = 'Something went wrong, please try again.';
        }
         
        return response()->json($response);
  }


  public function activate(Request $request)
  {
    $this->module_title ="";
    $this->module_title ="Representative";
    $enc_id = $request->input('id');

    if(!$enc_id)
    {
      return redirect()->back();
    }

    if($this->perform_activate(base64_decode($enc_id)))
    {
      Flash::success( $this->module_title.' has been activated.');
    }
    else
    {
      Flash::error('Error occurred while '.$this->module_title.' activation.');
    }

    $arr_response['data'] = 'ACTIVE';
    return response()->json($arr_response);
  }

  public function deactivate(Request $request)
  {
    $this->module_title ="";
    $this->module_title = "Representative";
    $enc_id = $request->input('id');

    if(!$enc_id)
    {
        return redirect()->back();
    }

    if($this->perform_deactivate(base64_decode($enc_id)))
    {
         Flash::success( $this->module_title.' has been deactivated.');
    }
    else
    {
        Flash::error('Error occurred while '.$this->module_title.' deactivation.');
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



}
