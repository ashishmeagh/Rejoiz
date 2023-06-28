<?php

namespace App\Http\Controllers\Influencer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\CountryModel;

use App\Common\Services\GeneralService;

use Validator;
use Flash;
use Sentinel;
use Hash;
use Session;

class AccountSettingsController extends Controller
{
    public function __construct(UserModel $UserModel,
    							CountryModel $CountryModel,
    							GeneralService $GeneralService)
    {

    	$this->UserModel        = $UserModel;
    	$this->CountryModel     = $CountryModel;
    	$this->GeneralService   = $GeneralService;

    	$this->img_path               = base_path().'/storage/app/';

    	$this->arr_view_data          = [];
    	$this->module_title           = "Account Settings";
    	$this->module_view_folder     = 'influencer.account_settings'; 
    	$this->influencer_panel_slug  = config('app.project.influencer_panel_slug');
    	$this->module_url_path        = url($this->influencer_panel_slug.'/account_settings');
    }

    public function index()
    {
        $arr_user_data     = $arr_country = [];
        $arr_influencer_data = [];
        $obj_data          = Sentinel::getUser();
        $store_logo        = '';
        $user_id           = 0;

        if($obj_data){
           $user_id  = $obj_data->id;
           $is_login = $obj_data->is_login;
        }

        /*User Details*/

        $user_details = $this->UserModel->where('id',$user_id)->first();

        if(isset($user_details)){
           $arr_user_data = $user_details->toArray();
        } 


        /*Get Country Data*/

        $arr_country = $this->CountryModel->where('is_active','1')->get()->toArray();

        $this->arr_view_data['arr_user_data']     = $arr_user_data;
        $this->arr_view_data['is_login']          = $is_login;
        $this->arr_view_data['arr_country']       = $arr_country;
        $this->arr_view_data['module_title']      = $this->module_title;
    	  $this->arr_view_data['page_title'] 	      = 'Account Settings';
    	  $this->arr_view_data['module_url_path']   = $this->module_url_path;
    	  return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function update(Request $request)
    {
        $form_data  = $request->all();

        $arr_rules  = array();
        $obj_data   = Sentinel::getUser();
      
      	if($obj_data)
      	{	

			$arr_rules =    [
		                        'first_name'        =>'required',
		                        'last_name'         =>'required',
		                        'email'             =>'required|email',
		                        'contact_no'        =>'required',
		                        'country_id'        =>'required|numeric',
		                        'post_code'         =>'required'
			                ];

	        $validator = Validator::make($request->all(),$arr_rules);
	                           

		    if($validator->fails())
		    {
		        $response['status']      = 'error';
		        $response['description'] = $validator->errors()->first();
		        return response()->json($response);
		   	} 
        
	        if($this->UserModel->where('email',$request->input('email'))
	                           ->where('id','!=',$obj_data->id)
	                           ->count()>0)
	        {
	        	$response['status']      = 'error';
		        $response['description'] = 'This email id already present in our system, please try again.';
	            return response()->json($response);
	        }

	        $arr_data['first_name']     = $request->input('first_name',null);
	        $arr_data['last_name']      = $request->input('last_name',null);
	        $arr_data['email']          = $request->input('email',null);
	        $arr_data['country_id']     = $request->input('country_id',null);
	        $arr_data['contact_no']     = $request->input('contact_no');
	        $arr_data['post_code']      = $request->input('post_code',null);

          if($request->input('contact_no') && $request->input('hid_country_code')){
          $arr_data['contact_no'] = str_replace($request->input('hid_country_code'),"",$request->input('contact_no'));
          }
          $arr_data['country_code'] = $request->input('hid_country_code');

        	$profile_file_path = '';
        
	        if($request->hasFile('profile_image'))
	        {
	            $profile_image =$request->file('profile_image');
	           
	            if($profile_image!=null)
	            {
	                $profile_file_path        = $profile_image->store('profile_image');

	                if($request->old_image!=null)
	                { 
	                    $old_img_path         = $this->img_path.$request->old_image;
	                    $unlink_old_img       = $this->GeneralService->unlink_old_image($old_img_path);
	                }
	            }

	            $arr_data['profile_image'] = $profile_file_path;
	        }
	        else
	        {
	            $arr_data['profile_image'] = $request->old_image;
	        }


        	$obj_data = Sentinel::update($obj_data, $arr_data);

	        if($obj_data)
	        {
	           $response['status']      = 'success';
	           $response['description'] = 'Account settings has been updated.';
	           return response()->json($response);
	        }
	        else
	        {
	           $response['status']      = 'error';
	           $response['description'] = 'Error occurred, while updating '.str_singular($this->module_title).'.';
	           return response()->json($response);	             
	        }   
    	}
    	else
    	{
    		  $response['status']      = 'error';
	        $response['description'] = 'Something went wrong, please try again.';
	        return response()->json($response);
    	}
	}

	public function change_password(Request $request)
    {   
        if ($request->isMethod('get'))
        {
            $this->arr_view_data['influencer_panel_slug'] = $this->influencer_panel_slug;
            $this->arr_view_data['page_title']            = "Change Password";
            $this->arr_view_data['module_title']          = "Change Password";
            
            return view($this->module_view_folder.'.change_password',$this->arr_view_data);
        }

        /*Check Validatons and display custom message*/
        $inputs = request()->validate([
                                        'current_password'=> 'required',
                                        'new_password'    => 'required'
                                    ],
                                    [
                                       'current_password.required'=>'Please enter current password',
                                       'new_password.required'    =>'Please enter new password'
                                    ]);
      
     	$user = Sentinel::check();

      	$credentials = [];
      	$credentials['password'] = $request->input('current_password');

      	if (Sentinel::validateCredentials($user,$credentials)) 
      	{   
            $new_credentials = [];
            $new_credentials['password'] = $request->input('new_password');

            if(Sentinel::update($user,$new_credentials))
            {  
               	//Session::flush();

               	Session::Flash('message','Your password has been changed successfully.');

               	//return redirect('/login');
            }
            else
            {  
               	Flash::error('Error occurred while changing password.');
            }
      	} 
      	else
      	{
        	Flash::error('Invalid old password.');
      	}       
      
     	return redirect()->back();
    }

    public function logout()
    {
        Sentinel::logout();
        return redirect(url('/'));
    }
}
