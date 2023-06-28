<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\UserService;

use App\Models\UserModel;
use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\CityModel;

use App\Common\Services\GeneralService;
use Sentinel;
use Validator;


class UserController extends Controller
{	

	public function __construct(
									UserModel $UserModel,
									CountryModel $CountryModel,
									StateModel $StateModel,
									CityModel $CityModel,
									UserService $UserService,
									GeneralService $GeneralService
								)

    {          
    	$this->UserModel      = $UserModel;
    	$this->CountryModel   = $CountryModel;
    	$this->StateModel     = $StateModel;
    	$this->CityModel      = $CityModel;
    	$this->UserService    = $UserService;
    	$this->GeneralService = $GeneralService;

        $this->module_view_folder   = 'front.user';  
        $this->arr_view_data        = [];

    }


	public function my_profile()
	{
		 
		$user = \Sentinel::getUser();

		$arr_user_role = [];
		$obj_user_role = $user->roles()->first();
		if($obj_user_role){
			$arr_user_role = $obj_user_role->toArray();
		}
		
		$user_id = false;
		if($user!=null || $user!=false){
			$user_id = $user->id;
		}	

		$user_slug = isset($arr_user_role['slug'])?$arr_user_role['slug']:false;

		$user_information = $this->UserService->get_user_information($user_id,$user_slug);

		$this->arr_view_data['user_data'] = $user_information;

		return view($this->module_view_folder.'.my_profile',$this->arr_view_data);
	}

	public function update_profile_data(Request $request){

		$user = \Sentinel::getUser();

		$first_name     = $request->input('first_name');
		$last_name      = $request->input('last_name');
		$wallet_address = $request->input('wallet_address');

		$arr_rules = [
                       'first_name' => 'required',
                       'last_name' => 'required',
                       'wallet_address' => 'required'
                     ];

        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            $response['status']      = "ERROR";
            $response['description'] = "Form validation failed, please check all fields.";
            return response()->json($response);
        }

        $arr_data                   = [];
        $arr_data['first_name']     = $first_name;
        $arr_data['last_name']      = $last_name;
        $arr_data['wallet_address'] = $wallet_address;


        $profile_file_path = '';
        if($request->hasFile('profile-image'))
        {
            $profile_image =$request->file('profile-image');

            if($profile_image!=null){
                $profile_file_path = $profile_image->store('profile_image');
            }

            $arr_data['profile_image']= $profile_file_path;
        }


        /*check wallet address duplication*/
		if($this->UserModel::where('wallet_address',$wallet_address)->where('id','!=',$user->id)->count() == 1){
			$response['status'] = 'ERROR';
			$response['description'] = 'Provided wallet address already exists in system, try different one';

			return response()->json($response);
		}

		$status = $this->UserModel->where('id',$user->id)->update($arr_data);

		if($status){
			$response['status']      = 'SUCCESS';
			$response['description'] = 'Profile has been updated.';

			return response()->json($response);
		}
		else{
			$response['status']      = 'ERROR';
			$response['description'] = 'Error occurred while updating profile.';

			return response()->json($response);
		}

		
	}

	public function kyc_details()
	{		
		$arr_country    = $arr_user_role =  [];
		$user_role_slug = '';

		$arr_country = $this->GeneralService->getCountries();

		if(Sentinel::check())
		{
			$user           = Sentinel::getUser();

			/*$user_role      = Sentinel::findRoleById($user->id);
			$user_role_slug = $user_role->slug;*/

		    $obj_user_role = $user->roles()->first();
		    if($obj_user_role){
		        $arr_user_role = $obj_user_role->toArray();
		    }

			$user_role_slug = isset($arr_user_role['slug'])?$arr_user_role['slug']:false;
		}

		if($user->kyc_status == '1'){
			return redirect('/my_profile');
		}

		$this->arr_view_data['user']		   = $user;
		$this->arr_view_data['user_role_slug'] = $user_role_slug;
		$this->arr_view_data['arr_country']    = $arr_country;

		return view($this->module_view_folder.'.kyc',$this->arr_view_data);
	}


	public function update_kyc(Request $request)
	{
		$form_data = $request->all();

		$response = $this->UserService->user_kyc_save($form_data);

		return $response;
	}

	public function get_states(Request $request)
	{
		$country_id = $request->input('country_id');

		$arr_states = $this->GeneralService->getStates($country_id);

		if(isset($arr_states) && count($arr_states) > 0)
		{
			$response['status']	    = 'success';
			$response['arr_states'] = $arr_states;
		}
		else
		{
			$response['status'] = 'error';
		}

		return response()->json($response);
	}

	public function get_cities(Request $request)
	{
		$state_id = $request->input('state_id');

		$arr_cities = $this->GeneralService->getCities($state_id);

		if(isset($arr_cities) && count($arr_cities) > 0)
		{
			$response['status']	    = 'success';
			$response['arr_cities'] = $arr_cities;
		}
		else
		{
			$response['status'] = 'error';
		}

		return response()->json($response);
	}


	public function change_password(Request $request)
    {
        $credentials = $new_credentials = [];

        $this->arr_view_data['page_title']   = 'Change-Password';
        
        if($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(),[
	            'current_password'  => 'required',
	            'new_password'      => 'required|min:8|max:16|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W+).{8,}$/',
	            'confirm_password' => 'required|same:new_password'
            ]);

            if($validator->fails())
            {
                $response['status'] = 'FAILURE';
                $response['message']= 'Required fields missing.';

                return response()->json($response);
            }

            $curr_password = $request->input('current_password');
            $new_password  = $request->input('new_password');

            $user = Sentinel::check();
    
            $credentials['password'] = $curr_password;

            if(Sentinel::validateCredentials($user,$credentials)) 
            {                 
                $new_credentials['password'] = $new_password;

                if(Sentinel::update($user,$new_credentials))
                {
                    $response['status']  = 'SUCCESS';
                    $response['message'] = 'Password has been changed.';
                    return response()->json($response);
                }
                else
                {
                    $response['status'] = 'FAILURE';
                    $response['message']= 'Error occurred while changing password, please try again.';                    
                    return response()->json($response);
                }
            } 
            else
            {	
            	$response['status'] = 'FAILURE';
                $response['message']= 'You have entered wrong current password.';

                return response()->json($response);
            }   
        }
        else
        {
            return view($this->module_view_folder.'.change_password',$this->arr_view_data);
        }        
    }

    public function does_old_password_exist(Request $request)
    {
        $user = Sentinel::check();

        $credentials['password'] = $request->input('current_password');

        if(Sentinel::validateCredentials($user,$credentials)) 
        {
            return response()->json(['exists'=>'false']);            
        }
        else
        {
            return response()->json(['exists'=>'true'],404);
        }
    }

    public function referral_link(){
    	$user = Sentinel::check();

    	$ref_link = url('/').'/signup';
    	if($user->ref_no!=null){
    		$ref_link = $ref_link.'/'.$user->ref_no;
    	}

    	$this->arr_view_data['ref_link'] = $ref_link;

    	return view($this->module_view_folder.'.referral_link',$this->arr_view_data);
    }

}
