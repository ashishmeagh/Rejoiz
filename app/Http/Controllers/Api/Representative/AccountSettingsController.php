<?php

namespace App\Http\Controllers\Api\Representative;

use App\Models\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\Api\Representative\AccountSettingsService;
use App\Common\Services\Api\Common\ResponseService;
use App\Common\Services\Api\Common\CommonService;


use Validator;

class AccountSettingsController extends Controller
{
    
	public function __construct(
                                AccountSettingsService $AccountSettingsService,
                                UserModel $UserModel,
                                ResponseService $ResponseService,
                                CommonService $CommonService
                              )
    {
    	
      $this->AccountSettingsService = $AccountSettingsService;
      $this->ResponseService        = $ResponseService;
      $this->UserModel              = $UserModel;
      $this->CommonService          = $CommonService;
    }

    public function index(Request $request)
    {

        $user       = $request->input('auth_user'); 

        $user_id    =  $user->id;

        $arr_data   =  $this->AccountSettingsService->get($user_id);
                                         
        return $this->ResponseService->send($arr_data);
      
    }

    public function update(Request $request)
    {

        $arr_rules = array();
        $form_data = $request->all();

       // dd($form_data,$request->file('profile_image'));

       
        $user      = $request->input('auth_user');
        $user_id   = $user->id;

        $arr_rules = [
                      'first_name' =>'required|min:3',
                      'last_name'  =>'required',
                      'email'      =>'required|email',
                      'country_id' =>'required',
                      'post_code'  =>'required',
                      'description'=>'required'
                      ];

        if(Validator::make($form_data,$arr_rules)->fails()) {  

            $response['status']     = 'failure';
            $response['message']    = 'Please enter valid form fields.';
            $response['data']       = '';

            return $this->ResponseService->send($response);  
        }

        $count = $this->UserModel
                       ->where('email',$request->input('email'))
                       ->where('id','!=',$user_id)
                       ->count();
        
        if($count == 1) {

            $response['status']     = 'failure';
            $response['message']    = 'This email id already present in our system, please try another one.';
            $response['data']       = '';

            return $this->ResponseService->send($response);      
        }

        if($request['country_id']== "" && $request['post_code']!="")
        {
            $response['status']     = 'failure';
            $response['message']    = 'Invalid zip/postal code.';
            $response['data']       = '';

            return $this->ResponseService->send($response);      
        }

           $arr_data = $this->AccountSettingsService->update($form_data,$user_id,$request);
                                          
           return $this->ResponseService->send($arr_data);
    }

    public function change_password(Request $request)
    {
         $form_data = $request->all();
         $user      = $request->input('auth_user');

         $arr_rules = [
                         'current_password'         =>'required',
                         'new_password'             =>'required',
                         'new_password_confirmation'=>'required|same:new_password'
                      ];

         if(Validator::make($form_data,$arr_rules)->fails())
        {
             
            $response['status']     = 'failure';
            $response['message']    = 'Please enter valid current password and new password.';
            $response['data']       = '';

            return $this->ResponseService->send($response);  
        }

        $arr_data = $this->AccountSettingsService->change_password($form_data,$user);

        return $this->ResponseService->send($arr_data);
    }
}
