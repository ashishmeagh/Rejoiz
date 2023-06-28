<?php

namespace App\Http\Controllers\Api\Rejoiz\Retailer;

use App\Models\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\Api\Rejoiz\Retailer\AccountSettingsService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;
//use App\Common\Services\Api\Rejoiz\Common\CommonService;


use Validator;

class AccountSettingsController extends Controller
{



	public function __construct(
                                AccountSettingsService $AccountSettingsService,
                                UserModel $UserModel,
                                ResponseService $ResponseService
                              )
    {

      $this->AccountSettingsService = $AccountSettingsService;
      $this->ResponseService        = $ResponseService;
      $this->UserModel              = $UserModel;
      //$this->CommonService        = $CommonService;

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

        $form_data  = $request->all();

        $arr_rules  = array();
        $arr_store_details = [];
        $user       = $form_data['auth_user']; 
        $user_id    = $user->id;
        $type       = $form_data['type'];


       if(isset($form_data['type']) && $form_data['type']!='' && $form_data['type']=='image_uploading')
       {
          $arr_data          = [];
          $profile_file_path = '';
          $store_logo_path   = '';

          $arr_rules =  [
                            'profile_image'     =>'required',                                      
                            'store_logo'        =>'required'                                      
                        ];

           $validator = Validator::make($request->all(),$arr_rules);
                           

           if($validator->fails())
           {
              $response['status']     = 'failure';
              $response['message']    = 'Form validation failed,please check form fields.';
              $response['data']       = '';
              return $this->ResponseService->send($response);      
           } 
           // dd($form_data,$user_id,$type,$request);
           $response = $this->AccountSettingsService->update($form_data,$user_id,$type,$request);
                 
           return $this->ResponseService->send($response);        
         
       }
      
        if(isset($form_data['type']) && $form_data['type']!='' && $form_data['type']=='personal_info')
        {
          
           $arr_rules =               [
                                        'first_name'        =>'required',
                                        'last_name'         =>'required',
                                        'email'             =>'required|email',
                                        'address'           =>'required',
                                        'state'             =>'required',
                                        'city'              =>'required',
                                        'country_id'        =>'required',
                                        'tax_id'            =>'required',
                                        'store_name'        =>'required',
                                        'contact_no'        =>'required'
                                        
                                     ];

           $validator = Validator::make($request->all(),$arr_rules);
                           

           if($validator->fails())
           {
              $response['status']     = 'failure';
              $response['message']    = 'Form validation failed,please check form fields.';
              $response['data']       = '';
              return $this->ResponseService->send($response);      

           } 

        } 

        if(isset($form_data['type']) && $form_data['type']!='' && $form_data['type']=='address_info')
        {
           $arr_rules_tab3 =  [
                                                'shipping_addr'     =>'required',
                                                'billing_addr'      =>'required',
                                                'shipping_zip_code' =>'required',
                                                'billing_zip_code'  =>'required',
                                                'shipping_city'     =>'required',
                                                'shipping_state'    =>'required',
                                                'shipping_country'  =>'required',
                                                'billing_city'      =>'required',
                                                'billing_country'   =>'required',
                                                'billing_state'     =>'required',
                                                'bill_contact_no'   =>'required|min:10',
                                                'ship_contact_no'   =>'required|min:10'
                                           ]; 
         $validator = Validator::make($request->all(),$arr_rules_tab3);
                           

           if($validator->fails())
           {
               $response['status']      = 'failure';
               $response['message']     = 'Form validation failed,please check form fields.';
               $response['data']        = '';
               return $this->ResponseService->send($response); 
           }                                  

        }

        
        if($this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$user_id)
                           ->count()==1)
        {
             $response['status']      = 'failure';
             $response['message']     = 'This email id already present in our system,please try again.';
             $response['data']        = '';
             return $this->ResponseService->send($response); 
        } 

           $arr_data = $this->AccountSettingsService->update($form_data,$user_id,$type,$request);
                                          
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
