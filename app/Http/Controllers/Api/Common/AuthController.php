<?php

namespace App\Http\Controllers\Api\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Common\AuthService;
use App\Common\Services\Api\Common\ResponseService;

use Validator;
use Sentinel;


class AuthController extends Controller
{
    public function __construct(AuthService $AuthService,ResponseService $ResponseService) { 


        $this->AuthService     = $AuthService;
        $this->ResponseService = $ResponseService;
    }

    public function login(Request $request) {

        $arr_rules  = [

            'email'    => 'required|email',
            'password' => 'required'
        ];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Please enter valid email and password.';
            $response['data']           = '';

            return $this->ResponseService->send($response,500);
        }

        $credentials = [
            'email'     => trim($request->input('email')),
            'password'  => trim($request->input('password'))
        ];

        $response  = $this->AuthService->login($credentials);

        return $this->ResponseService->send($response);
    }

    public function forgot_password(Request $request) {

        $arr_rules  = [ 'email' => 'required|email' ];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Please enter valid email.';
            $response['data']           = '';

            return $this->ResponseService->send($response,500);
        }

        $email = $request->input('email');

        $response  = $this->AuthService->forgot_password($email);

        return $this->ResponseService->send($response);
    }

    public function verify_otp(Request $request) {

        $arr_rules  = [ 
                        'email' => 'required|email',
                        'otp'   => 'required'
                      ];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Please enter valid OTP.';
            $response['data']           = '';

            return $this->ResponseService->send($response,500);
        }

        $data = [];

        $data['email']  = $request->input('email');
        $data['otp']    = $request->input('otp');

        $response =  $this->AuthService->verify_otp($data); 

        return $this->ResponseService->send($response);
    }

    public function change_password(Request $request) {

        $arr_rules  = [ 
                        'email'             => 'required|email',
                        'password'          => 'required',
                        'confirm_password'  => 'required|same:password'
                      ];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Please enter valid email.';
            $response['data']           = '';

            return $this->ResponseService->send($response,500);
        }

        $data               = [];
        $data['email']      = $request->input('email');
        $data['password']   = $request->input('password');

        $responce =  $this->AuthService->change_password($data);

        return $this->ResponseService->send($responce);
    }

    public function sign_up(Request $request)
    {
        $form_data = $request->all();

        $arr_rules = [

          'first_name' => 'required',
          'last_name'  => 'required',
          'email'      => 'required|email',
          'country_id' => 'required',
          'password'   => 'required',
          'role'       => 'required',
          'contact_no' => 'required',
        ];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Form validations failed, please check form feilds.';
            $response['data']           = '';

            return $this->ResponseService->send($response,500);
        }

        $response = $this->AuthService->sign_up($form_data);

        return $this->ResponseService->send($response);
    }


 public function activation_complete($user_id, $activation_code) {
        $user_id = base64_decode($user_id);
        $user = Sentinel::findById($user_id);
        $status = Activation::complete($user, $activation_code);

        $loginUser = Sentinel::check();

        if ($status != false) {
          /*after activation complete update status */

          $objUserDetails = $this->UserModel->where('id', $user_id);

          /*$isUserAlreadyActivated = $objUserDetails->where('status', 1)->count();

          if ($isUserAlreadyActivated > 0 && $loginUser) {
            return redirect('/');
          }
        */
          $updateUserDetails = $objUserDetails->update(['status' => '1']);

          Flash::success('Congratulations! your account has been verified.');
          return redirect('/login');
        } else {

          if ($loginUser) {
            return redirect(url('/'));
          }

          Flash::error('Your account is already verified, please login.');
          return redirect(url('/'));
       }
   }
}
