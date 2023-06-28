<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Services\EmailService;
use App\Models\SiteSettingModel;
use App\Models\UserLastActiveModel;

use Validator;
use Flash;
use Sentinel;
use Reminder;
use URL;
use Mail;
use Session;

class AuthController extends Controller
{
  public function __construct(EmailService $EmailService)
  {
    
    $this->arr_view_data      = [];
    $this->module_title       = "Admin";
    $this->module_view_folder = "admin.auth";
    $this->EmailService       = $EmailService;
    $this->admin_panel_slug   = config('app.project.admin_panel_slug');
    $this->module_url_path    = url($this->admin_panel_slug);

  }
  
  public function login()
  { 
    $site_setting_arr = [];
    $site_setting_obj = SiteSettingModel::first();
    if($site_setting_obj)
    {
        $site_setting_arr = $site_setting_obj->toArray();            
    }

    $this->arr_view_data['site_setting_arr']     = $site_setting_arr;

    if($login_user = Sentinel::check()) {
        
      if($login_user->inRole('admin')){
        
        $panel_dashboard_url = url('/'.config('app.project.admin_panel_slug').'/dashboard');
        return redirect($panel_dashboard_url);
      }
      else{
        $this->arr_view_data['module_title']     = $this->module_title." Login";
    
      return view($this->module_view_folder.'.login',$this->arr_view_data);

      }
    }
    else{
      
      
      $this->arr_view_data['module_title']     = $this->module_title." Login";
    
      return view($this->module_view_folder.'.login',$this->arr_view_data);
    }
    
  }

  public function process_login(Request $request)
  {
    /* Check Validations and display custom message*/

        $arr_rules    = ['email'   =>'required|email',
                         'password'=>'required'
                        ];
   
        $validator    = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
           Flash::error('Form validations failed, please check form fields.');
           return redirect()->back();
        }
       

      $credentials = [
          'email'    => trim($request->input('email')),
          'password' => trim($request->input('password')),
      ];

      $remember_me = $request->has('remember_me') ? true : false; 
      //check user status is active or block
      /*---------------------------------*/
      $user_arr = Sentinel::findByCredentials($credentials);
      

      $arr_user_role = [];
      if($user_arr!=null){
        $obj_user_role = $user_arr->roles()->get();
        if($obj_user_role){
          $arr_user_role = $obj_user_role->toArray();
        }    
      }  

      $login_user_role = '';  
      if(isset($arr_user_role) && isset($arr_user_role[0]) && isset($arr_user_role[0]['slug'])){
        $login_user_role = $arr_user_role[0]['slug'];
      }

      if($login_user_role!='admin'){
         Flash::error('Invalid login details');
         return redirect()->back();
      } 

      if(\Activation::completed($user_arr)==false){
         Flash::error('Your account is not activated.');
         return redirect()->back();
      }
 
      if($user_arr)
      {
          if($user_arr->is_active == '0')
          {
            Flash::error('Sorry your account is blocked.');
            return redirect()->back();
          }
          
      }
     
      /*---------------------------------*/
      
      $check_authentication = Sentinel::authenticate($credentials,$remember_me);
     
      if($check_authentication)
      {
        if(isset($remember_me) && $remember_me == "true")
        {
            setcookie("email",$request->input('email'),time()+ (10 * 365 * 24 * 60 * 60));
            setcookie("password",$request->input('password'),time()+ (10 * 365 * 24 * 60 * 60));
            setcookie("rememberd",'rememberd',time()+(10 * 365 * 24 * 60 * 60));
        }
        else
        {
          setcookie("email",'');
          setcookie("password",'');
          setcookie("rememberd",'');
        }

        $user = Sentinel::check();

        if($user->inRole('admin'))
        {
          $current_date_time = date('Y-m-d H:i:s');
          $loggedInUserId = $user->id;

          $data_arr['last_active_time'] = $current_date_time;
          $data_arr['user_id']          = $loggedInUserId;

          $is_update = UserLastActiveModel::where('user_id',$loggedInUserId)->update($data_arr);
          
          return redirect(config('app.project.admin_panel_slug').'/dashboard');
        }
        else
        {
          Flash::error('Not sufficient privileges');
          return redirect()->back();
        }

      }
      else
      {
        Flash::error('Invalid login credentials.');
        return redirect()->back();
      }

  }

  public function change_password()
  {
    $this->arr_view_data['page_title']      = " Change Password";
    $this->arr_view_data['module_title']    = " Change Password";
    $this->arr_view_data['module_url_path'] = $this->module_url_path.'/change_password';
    
    return view($this->module_view_folder.'.change_password',$this->arr_view_data);    
     
  }

  public function update_password(Request $request)
  {
    /*Check Validatons and display custom message*/
     /* $inputs = request()->validate([
        'current_password'=> 'required',
        'new_password' => 'required'
        ],
        [
          'current_password.required'=>'Please enter current password',
          'new_password.required'=>'Please enter new password'
        ]);
*/

        $arr_rules    = ['current_password'   =>'required',
                         'new_password'=>'required'
                        ];

        $validator    = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
           Flash::error('Form validations failed, please check form fields.');
           return redirect()->back();
        }
       

      
      $user = Sentinel::check();

      $credentials = [];
      $credentials['password'] = $request->input('current_password');

      if (Sentinel::validateCredentials($user,$credentials)) 
      { 
        $new_credentials = [];
        $new_credentials['password'] = trim($request->input('new_password'));

        if(Sentinel::update($user,$new_credentials))
        {
            //Flash::success('Password Change Successfully');
            //Session::flush();
            // Session::Flash('message','Your password has been reset,please login with new password.');
            // return redirect('/admin');
            // Flash('message','Your password has been reset,please login with new password.');
            Flash::success('Your password has been changed successfully.'); 
        }
        else
        {
          Flash::error('Problem occurred, while changing password.');
        }
      } 
      else
      {
        Flash::error('Invalid old password.');
      }       
      
      return redirect()->back(); 
  }

  public function process_forgot_password(Request $request)
  { 
    /*Check Validations and display custom message*/

     $arr_rules =  [
                      'email'          => 'required|email',
                   ];

     $validator    = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
           Flash::error('Form validations failed, please check form feilds.');
           return redirect()->back();
        }

    $email = $request->input('email');

    $user  = Sentinel::findByCredentials(['email' => $email]);

    if($user==null)
    {
      Flash::error('Account does not exist with '.$email.', please try again.');
      return redirect()->back();
    }

    if($user->inRole('admin')==false)
    {
      Flash::error('We are unable to process this email id.');
      return redirect()->back();
    }

    $reminder = Reminder::create($user);


    /*----------------------------------------------------------------
    | Build data for mail send
    ----------------------------------------------------------------*/
      //$arr_user = $this->get_user_details($email);

      $credentials = ['email' => $email];
      
      $arr_user = get_user_by_credentials($credentials);

      /*Get site setting data from helper*/
      $arr_site_setting = get_site_settings(['site_name','website_url']);

      $reminder_url = '<a target="_blank" style="background:#666; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.URL::to($this->admin_panel_slug.'/validate_admin_reset_password_link/'.base64_encode($arr_user['id']).'/'.base64_encode($reminder->code) ).'">Reset Password</a>.<br/>' ;

      $arr_built_content = ['FIRST_NAME'   => $arr_user['first_name'],
                              'EMAIL'        => $arr_user['email'],
                              'REMINDER_URL' => $reminder_url,
                              'PROJECT_NAME' => $arr_site_setting['site_name'],
                              'SITE_URL'     => $arr_site_setting['website_url']];


        $arr_mail_data                      = [];
        $arr_mail_data['email_template_id'] = '7';
        $arr_mail_data['arr_built_content'] = $arr_built_content;
        $arr_mail_data['arr_user']          = $arr_user;

        $email_status  = $this->EmailService->send_mail($arr_mail_data);

    /*--------------------------------------------------------------*/    

   /* if($email_status)
    {*/
      //Flash::success('Password reset link send successfully to your email id');
      //Flash::success('Password reset link send successfully to your email id');
      Flash::success('Email sent to '.$email.' please check email for further instructions.');
      return redirect()->back();


      
   // }
    /*else
    {
      Flash::error('Error while sending password reset link');
      return redirect()->back();
    }*/
  }


 /* public function get_user_details($email)
  {
    $credentials = ['email' => $email];
    $user = Sentinel::findByCredentials($credentials); // check if user exists

    if($user)
    {
      return $user->toArray();
    }
    return false;
  }
*/

  public function validate_reset_password_link($enc_id, $enc_reminder_code)
  {
    $user_id       = base64_decode($enc_id);
    $reminder_code = base64_decode($enc_reminder_code);

    $user = Sentinel::findById($user_id);

    if(!$user)
    {
      Flash::error('Invalid user request.');
      return redirect()->back();
    }

    if($reminder = Reminder::exists($user))
    {
      return view($this->module_view_folder.'.reset_password',compact('enc_id','enc_reminder_code'));
    }
    else
    {
      Flash::error('Reset password link has been expired.');
      return redirect()->back();
    }
  }

  public function reset_password(Request $request)
  {
    /*Check Validations*/
    $inputs = request()->validate([
        'password'=>'required',
        'confirm_password'=>'required',
        'enc_id'=>'required',
        'enc_reminder_code'=>'required'
      ],
      [
      'password.required'=>'Please enter password',
      'confirm_password.required'=>'Please enter confirm password'
      ]);

    $enc_id            = $request->input('enc_id');
    $enc_reminder_code = $request->input('enc_reminder_code');
    $password          = trim($request->input('password'));
    $confirm_password  = trim($request->input('confirm_password'));

    if($password  !=  $confirm_password )
    {
      Flash::error('Passwords do not match.');
      return redirect()->back();
    }

    $user_id       = base64_decode($enc_id);
    $reminder_code = base64_decode($enc_reminder_code);

    $user = Sentinel::findById($user_id);

    if(!$user)
    {
      Flash::error('Invalid user request.');
      return redirect()->back();
    }

    if ($reminder = Reminder::complete($user, $reminder_code, $password))
    {
      //Flash::success('Password reset successfully please login with new password.');
      \Session::flash('message','Your password has been reset, please login with new password.');
      return redirect($this->admin_panel_slug.'/login');
    }
    else
    {
      Flash::error('Reset password link has been expired.');
      return redirect($this->admin_panel_slug.'/login');
    }

  }

  public function logout()
  {
    Sentinel::logout();
    return redirect(url($this->admin_panel_slug));
  }

}
