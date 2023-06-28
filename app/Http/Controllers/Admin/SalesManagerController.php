<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\CountryModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\RepAreaModel;
use App\Common\Services\UserService;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;

use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SalesManagerModel;
use App\Models\CategoryDivisionModel;
use App\Models\SiteSettingModel;

use App\Common\Services\ReportService;
use App\Common\Services\PdfReportService;
use DataTable;
use App\Common\Traits\MultiActionTrait;
use DB;
use Validator;
use Sentinel;
use DateTime;

class SalesManagerController extends Controller
{

  use MultiActionTrait;
	public function __construct(UserModel $UserModel,
              								RoleModel $RoleModel,
              								RoleUsersModel $RoleUsersModel,
              								RepAreaModel $RepAreaModel,
              								SalesManagerModel $SalesManagerModel,
                              VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                              CountryModel $CountryModel,
              								EmailService $EmailService,
                              UserService $UserService,
                              ReportService $ReportService,
                              PdfReportService $PdfReportService,
                              GeneralService $GeneralService,
                              CategoryDivisionModel $CategoryDivisionModel
                            )
	{
    		$this->UserModel             = $UserModel;
    		$this->BaseModel             = $UserModel;
    		$this->SalesManagerModel     = $SalesManagerModel;
    		$this->RepAreaModel          = $RepAreaModel;
        $this->CountryModel          = $CountryModel;
    		$this->RoleModel             = $RoleModel;
    		$this->RoleUsersModel        = $RoleUsersModel;
        $this->VendorRepresentativeMappingModel  = $VendorRepresentativeMappingModel;
        $this->UserService           = $UserService;
    		$this->EmailService          = $EmailService;
        $this->GeneralService        =  $GeneralService;   
        $this->CategoryDivisionModel = $CategoryDivisionModel;
        $this->ReportService         = $ReportService;
        $this->PdfReportService      = $PdfReportService;
    		$this->arr_view_data         = [];
    		$this->module_title          = "Sales Manager";
    		$this->module_view_folder    = "admin.sales_manager";
    		$this->module_url_path       = url(config('app.project.admin_panel_slug')."/sales_manager");    
        $this->curr_panel_slug       =  config('app.project.admin_panel_slug');
        $this->role                  = 'sales_manager';
        $this->profile_image         =  base_path().'/storage/app/';
	}

	public function index()
	{
		$this->arr_view_data['page_title']      = str_plural($this->module_title);
		$this->arr_view_data['module_title']    = str_plural($this->module_title);
		$this->arr_view_data['module_url_path'] = $this->module_url_path;

		return view($this->module_view_folder.'.index',$this->arr_view_data);
	}


	public function create()
	{   
    $category_division_arr  = $cat_arr =$area_category_arr= $category_already_assigned=[]; 
		
    $category_already_assigned = $this->SalesManagerModel->groupBy('category_id')->where('category_id','!=',null)->select('category_id')->get()->toArray();


    $category_already_assigned = array_column($category_already_assigned,'category_id');



    $area_names = $this->RepAreaModel->orderBy('area_name')->where('status',1)->get()->toArray();
   
      if(isset($area_names) && count($area_names)>0)
      { 
          foreach ($area_names as $key => $area)
          {   
              $category_division_arr = json_decode($area['category_id']);
             
              if(isset($category_division_arr) && count($category_division_arr)>0)
              {
                 $category_division_arr = array_diff($category_division_arr,$category_already_assigned);
              }

              if(isset($category_division_arr) && count($category_division_arr)>0)
              { 
                  $cat_arr = $this->CategoryDivisionModel->whereIn('id',$category_division_arr)->get()->toArray();

                  $area_category_arr[$key]['category_arr']  = $cat_arr; 
              }

              $area_category_arr[$key]['area_id']    = $area['id'];
              $area_category_arr[$key]['area_name']  = $area['area_name'];
          }
      }


  
		$this->arr_view_data['page_title']      = 'Create '.str_singular($this->module_title);
		$this->arr_view_data['module_title']    = str_plural($this->module_title);
		$this->arr_view_data['module_url_path'] = $this->module_url_path;
	  
    $country_arr = $this->CountryModel->where('is_active',1)
                                      ->orderBy('name','asc')
                                      ->get()
                                      ->toArray();


                  
    $this->arr_view_data['country_arr']                = isset($country_arr)?$country_arr:'';
    $this->arr_view_data['area_names']                 = isset($area_names)?$area_names:'';
    $this->arr_view_data['area_category_arr']          = isset($area_category_arr)?$area_category_arr:'';
    $this->arr_view_data['category_already_assigned']  = isset($category_already_assigned)?$category_already_assigned:'';
    
		return view($this->module_view_folder.'.create',$this->arr_view_data); 

	}

	


	public function save(Request $request)
	{ 
    
      $area_arr  =  $response  = $arr_user_data = [];
      $is_update = false;
      $profile_img_file_path = '';
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
              				'contact_no'  => 'required',
              		  	'rep_area'    => 'required',
                      'country_id'  =>'required',
                      'commission'  =>'required'

  			            ];


  		$validator = Validator::make($request->all(),$arr_rules);
  	

  		if($validator->fails())
  		{	
         $response['description'] = 'Form validations failed, please check form fields.';
  			 $response['status']      = "error";
  			 return response()->json($response); 
  		}

      /*check email duplication*/

      $is_duplicate = $this->UserModel->where('email',$form_data['email']);
         

      if($is_update)
      {
          $is_duplicate->where('id','<>',$user_id);       
      }
          
      $is_duplicate = $is_duplicate->count()>0;        
     
      if($is_duplicate)
      {  
         $response['status']      = 'error';
         $response['description'] = 'Email is already exists.';
         return response()->json($response);
      } 

     
  		//profile image code start
  		
  		$profile_img = isset($form_data['profile_image'])?$form_data['profile_image']:null;

  		if($profile_img!=null)
      {   
          $file_extension = strtolower( $profile_img->getClientOriginalExtension());
  		
  			  if(!in_array($file_extension,['jpg','png','jpeg']))
  	      {                           
              $response['status']       = 'error';
              $response['description']  = 'Invalid profile image, please try again.';
              return response()->json($response);      
  	    	}

  	    	$profile_img_file_path = $profile_img->store('profile_image');
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
 

  		$arr_user_data['first_name']    = $request->input('first_name');
  		$arr_user_data['last_name']     = $request->input('last_name');
  		$arr_user_data['email']         = $request->input('email');
  		$arr_user_data['contact_no']    = $request->input('contact_no');
  		$arr_user_data['country_code']  = $request->input('hid_country_code');
  	  $arr_user_data['post_code']     = $request->input('post_code');
  	  $arr_user_data['commission']    = $request->input('commission');
      $arr_user_data['country_id']    = $request->input('country_id');
  		$arr_user_data['profile_image'] = $profile_img_file_path;
  		$arr_user_data['id'] 			      = $user_id;
  		$area_id 						            = $request->input('rep_area');

   		
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
              $role = Sentinel::findRoleBySlug('sales_manager');
  				    $role->users()->attach($user);
          }


          $user->first_name    = $arr_user_data['first_name'];
          $user->last_name     = $arr_user_data['last_name'];
          $user->email         = $arr_user_data['email'];
          $user->contact_no    = $arr_user_data['contact_no'];
          // $user->nationality   = $arr_user_data['nationality'];
          $user->country_code  = $arr_user_data['country_code'];
          // $user->tax_id        = $arr_user_data['tax_id'];
          $user->post_code     = $arr_user_data['post_code'];
          $user->commission    = $arr_user_data['commission'];
          $user->country_id    = $arr_user_data['country_id'];
          $user->profile_image = $arr_user_data['profile_image'];

          $user->status      = '1';
          $user->is_approved = '1';
        
          $user->save();
      }
      else
      {

                
            Sentinel::update($user, [
                                      'email'         => $arr_user_data['email'],
                                      'first_name'    => $arr_user_data['first_name'],
                                      'last_name'     => $arr_user_data['last_name'],
                                      'contact_no'    => $arr_user_data['contact_no'],
                                      'country_code'  => $arr_user_data['country_code'],
                                      'post_code'     => $arr_user_data['post_code'],
                                      'country_id'    => $arr_user_data['country_id'],
                                      'profile_image' => $arr_user_data['profile_image'],
                                      'commission'    => $arr_user_data['commission']
                                 
                                    ]);
      }

        
        if($is_update == true)
        {  
            $explode_arr = [];

            /*first delete all area against that sales manager*/
            $result = $this->SalesManagerModel->where('user_id',$user->id)->delete();

            /*insert multiple area to the sales manager*/
            $sales_manager_area = $request->input('rep_area');
            $category_div_arr   = $request->input('category_id');

            
            if(isset($category_div_arr) && $category_div_arr>0)
            {
                foreach($category_div_arr as  $key =>$category_id)
                {  
                 
                  $area_arr['user_id']      = $user->id;
                  $area_arr['area_id']      = $sales_manager_area;
                  $area_arr['category_id']  = $category_id;

                  $area_arr['description']  = $request->input('old_sales_manager_desc');

                  $this->SalesManagerModel->create($area_arr);

                }

            }
            else
            {
                $area_arr['user_id']      = $user->id;
                $area_arr['area_id']      = $sales_manager_area;
                $area_arr['category_id']  = null;

                $area_arr['description']  = $request->input('old_sales_manager_desc');

                $this->SalesManagerModel->create($area_arr);

            }

            
        }  
        else
        { 

            /*insert multiple area to the sales manager*/
            
            $sales_manager_area = $request->input('rep_area');

            $category_div_arr   = $request->input('category_id');

            if(isset($category_div_arr) && count($category_div_arr)>0)
            {
                foreach($category_div_arr as  $key =>$category_id)
                {  
                  
                  $area_arr['user_id']      = $user->id;
                  $area_arr['area_id']      = $sales_manager_area;
                  $area_arr['category_id']  = $category_id;

                  $this->SalesManagerModel->create($area_arr);

                }
            }
            else
            {
                $area_arr['user_id']      = $user->id;
                $area_arr['area_id']      = $sales_manager_area;
                $area_arr['category_id']  = null;

                $this->SalesManagerModel->create($area_arr);
            }
            

        } 
       
        		
        if($is_update == false)
        {
           
            //send login credentials to the  representative

            $credentials = ['email' => $arr_user_data['email']];
      
            $arr_user    = get_user_by_credentials($credentials);

            /*Get site setting data from helper*/
            $arr_site_setting = get_site_settings(['site_name','website_url']);


            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>' ;


            $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                                  'USER_ROLE'    => 'salesmanager',
                                  'EMAIL'        => $arr_user['email'],
                                  'PASSWORD'     => $user_password,
                                  'LOGIN_URL'    => $reminder_url,
                                  'APP_URL'      => $arr_site_setting['site_name']
                                ];



            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '34';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['arr_user']          = $arr_user;

            $email_status  = $this->EmailService->send_mail($arr_mail_data);

            $response['description'] = str_singular($this->module_title)." has been created.";


        }
        else
        {
          $response['description'] = str_singular($this->module_title)." has been updated.";
        }
        

        $response['status'] = "success";
            
        $response['url']    = $this->module_url_path;

        return response()->json($response); 
   
  }




  	public function get_sales_manager(Request $request)
  	{
  		
  		$arr_search_column = $request->input('column_filter');

  		$user_table = $this->UserModel->getTable();
  		$prefix_user_table = DB::getTablePrefix().$user_table;
  		

  		$role_table = $this->RoleModel->getTable();
  		$prefix_role_table = DB::getTablePrefix().$role_table;
  		

  		$role_user_table = $this->RoleUsersModel->getTable();
  		$prefix_role_user_table = DB::getTablePrefix().$role_user_table;
  	

      $rep_area_table = $this->RepAreaModel->getTable();
      $prefix_area_table = DB::getTablePrefix().$rep_area_table;

      $sales_manager_table = $this->SalesManagerModel->getTable();
      $prefix_sales_manager_table = DB::getTablePrefix().$sales_manager_table;

  		$obj_user = DB::table($user_table)
						->select(DB::raw($prefix_user_table.".id as id,".
										 $prefix_user_table.".email as email,".
										 $prefix_user_table.".status,".
										 $prefix_user_table.".commission,".
										 $prefix_user_table.".is_approved,".
										 $prefix_user_table.".contact_no as contact_no,".
										 $prefix_user_table.".country_code,".
                     $prefix_user_table.".created_at,".
										 $role_table.".slug as slug,".
										 $role_table.".name as name,".
                     $rep_area_table.".area_name as area_name,".
										 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
						->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
						->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->leftJoin($sales_manager_table,$sales_manager_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($rep_area_table,$sales_manager_table.'.area_id','=',$rep_area_table.'.id')
            ->where($role_table.'.slug','=',$this->role)
						->whereNull($user_table.'.deleted_at')
            ->where($user_table.'.id','!=',1)
            ->groupBy($sales_manager_table.'.user_id')
            ->orderBy($user_table.'.created_at','DESC');


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
        if(isset($arr_search_column['q_area']) && $arr_search_column['q_area']!="")
        {
            $search_term      = $arr_search_column['q_area'];
            $obj_user = $obj_user->having('area_name','LIKE', '%'.$search_term.'%');
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
                                    $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" readonly/>';
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

                             ->editColumn('commission',function($data)
                            {
                               
                                if($data->commission == '0' || $data->commission == ' ')
                                {   
                                  $commission = '-';
                                }
                                else
                                {

                                  return $commission =  number_format((float)$data->commission, 2, '.', '');
                                  //return $commission = $data->commission;
                                }
                                return $commission;
                            })    

                            ->editColumn('build_action_btn',function($data) 
                            {   
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->id);
                                $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'" title="View">View</a>';

                               /* $delete_href =  $this->module_url_path.'/delete/'.base64_encode($data->id);
                                $build_delete_action = '<a class="btn btn-outline btn-danger btn-circle show-tooltip" onclick="confirm_delete($(this),event)" href="'.$delete_href.'" title="Delete">Delete</a>';
*/

                                $edit_href = $this->module_url_path.'/edit/'.base64_encode($data->id);

                                    $build_edit_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip"  href="'.$edit_href.'" title="edit">Edit</a>';

/*
                                return $build_action = $build_view_action.' '.$build_delete_action.''.$build_edit_action;*/

                                return $build_action = $build_view_action.' '.$build_edit_action;
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


    function changeAprovalStatus(Request $request)
    {
    	
      $sales_manager_status = $request->input('salesManagerAprovalstatus');
      $sales_manager_id     = $request->input('salesmanager_id');
      $type                 = $request->input('type');
  
      if($sales_manager_status=='1')
      {
        $this->UserModel->where('id',$sales_manager_id)->update(['is_approved'=>1]);
        
        // $response['status']  = 'SUCCESS';
        // $response['message'] = $this->module_title.' has been approved.';
        if($type=='activate')
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been approved.';
            $response['data'] = 'ACTIVE';
        }
        else
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been disapproved.';
            $response['data'] = 'DEACTIVE';
        }

      }
      elseif($sales_manager_status=='0')
      {
        $this->UserModel->where('id',$sales_manager_id)->update(['is_approved'=>0]);

        // $response['status']  = 'SUCCESS';
        // $response['message'] = $this->module_title.' has been disapproved.';
        if($type=='activate')
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been approved.';
            $response['data'] = 'ACTIVE';
        }
        else
        {
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


	public function edit($user_id)
  {
        $user_area_id_arr =  $area_cat_array = $category_div_arr = [];
        $area_names       = $this->RepAreaModel->get()->toArray();

        $this->arr_view_data['area_names'] = $area_names;
        
        $id = base64_decode($user_id);
    

       
       	$obj_user_data = $this->UserModel->with(['sales_manager_details'])->where('id', $id)->first();

        if($obj_user_data)
        {
           $arr_user_data = $obj_user_data->toArray();
        }

         $country_arr   = $this->CountryModel->orderBy('id','ASC')
                                             ->get()
                                             ->toArray();


        $sales_area_cat_arr = $this->SalesManagerModel->where('user_id',$id)->get()->toArray();

        if(isset($sales_area_cat_arr) && count($sales_area_cat_arr)>0)
        { 
           $category_div_arr = array_column($sales_area_cat_arr,'category_id');
        }

         /*get all categories from selected sales manager area_id*/

        $cattegory_from_selected_area = [];

        $sales_area_id = isset($sales_area_cat_arr[0]['area_id'])?$sales_area_cat_arr[0]['area_id']:0;

        $area_details_obj = $this->RepAreaModel->where('id',$sales_area_id)->first();

        if(isset($area_details_obj))
        { 

          $cattegory_from_selected_area = json_decode($area_details_obj->category_id);

          if (isset($cattegory_from_selected_area) && count($cattegory_from_selected_area)) 
          {
          
              $cat_array_from_area = $this->CategoryDivisionModel->whereIn('id',$cattegory_from_selected_area)->get()->toArray();

          }

        }

        /*--------------------------------*/

        $all_category_div_arr = $this->CategoryDivisionModel->where('is_active',1)->get()->toArray();

        
      /* 	$this->arr_view_data['user_area_id']    = $arr_area_id;*/
        $this->arr_view_data['country_arr']          = isset($country_arr)?$country_arr:'';
		    $this->arr_view_data['edit_mode']            = TRUE;
        $this->arr_view_data['user_id']              = $id; 
        $this->arr_view_data['area_names']		       = isset($area_names)?$area_names:'';    

        $this->arr_view_data['arr_user_data']        = $arr_user_data;        
        $this->arr_view_data['page_title']           = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_title']         = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']      = $this->module_url_path;

        $this->arr_view_data['area_id']              = isset($sales_area_cat_arr[0]['area_id'])?$sales_area_cat_arr[0]['area_id']:0;
        $this->arr_view_data['category_div_arr']       = isset($category_div_arr)?$category_div_arr:[];

         $this->arr_view_data['all_category_div_arr']  = $all_category_div_arr;

        $this->arr_view_data['cat_array_from_area']  = isset($cat_array_from_area)?$cat_array_from_area:[];
     


        
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    


    public function view($enc_id)
    { 
        $arr_user   = [];

        $user_id    = base64_decode($enc_id);

        $arr_user   = $this->UserService->get_user_information($user_id,$this->role);

        $area_id    = $arr_user['sales_manager_details']['area_id'];

        $area_name  = $this->RepAreaModel->where('id',$area_id)->pluck('area_name')->first();

        $sales_manager_area_arr = $this->SalesManagerModel->where('user_id',$user_id)
                                                          ->with(['area_details'])
                                                          ->get()
                                                          ->toArray();

        $this->arr_view_data['arr_user']               = $arr_user;
        $this->arr_view_data['area_name']              = $area_name;
        $this->arr_view_data['sales_manager_area_arr'] = $sales_manager_area_arr;

        $this->arr_view_data['page_title']             = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']           = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']        = $this->module_url_path;
        
      return view($this->module_view_folder.'.view',$this->arr_view_data);
    }


    public function report_generator(Request $request,$type=false)
    {
        $type  = \Request::segment(4);
        if($type=="pdf")
        {
          $inventory_pdf =  $this->PdfReportService->downloadPdfSalesManager($type);
         
          return $inventory_pdf->download('sales manager.pdf');
        }
        else
        {
          $this->ReportService->downloadExcelSalesManager($type);
        }
    }

 
    
    public function fetch_category(Request $request)
    {
        $category_details_arr = [];
        $area_id = $request->get('area_id');
      
        $area_details_obj = $this->RepAreaModel->where('id',$area_id)->first();

        if(isset($area_details_obj))
        {
            $area_details_arr = $area_details_obj->toArray(); 
            
        }
   
        $category_id_arr = json_decode($area_details_arr['category_id']);
     
        if(isset($category_id_arr) && count($category_id_arr)>0)
        {
            $category_details_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->get()->toArray();
        }

        return response()->json($category_details_arr);

    }


    public function check_area_exist(Request $request)
    {
        $count = 0;
        $area_id = $request->get('area_id');

        $count = $this->SalesManagerModel->where('area_id',$area_id)->count();

        $response['count'] = $count;

        return response()->json($response);
   
    }


    public function check_category_exist(Request $request)
    {
       $count = 0;
       $category_div_id = $request->input('category_div_id');

       $user_id = $request->input('user_id');
       $user_id = isset($user_id)?$user_id:0;

      /*check category duplication*/

      if(isset($category_div_id) && count($category_div_id)>0)
      {
          foreach ($category_div_id as $key => $id)
          {
              $is_duplicate = $this->SalesManagerModel->where('category_id',$id);
       
              $is_duplicate->where('user_id','<>',$user_id);       
                    
              $is_duplicate = $is_duplicate->count()>0;        
             
              if($is_duplicate)
              {  
                 $count= 1;

              }
              else
              {
                $count = 0;         
              } 
          }

      }


      $response['count'] = $count;


      return response()->json($response);

    }
   

}



	

