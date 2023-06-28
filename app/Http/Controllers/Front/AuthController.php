<?php

namespace App\Http\Controllers\Front;

use Activation;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\InventoryService;
use App\Common\Services\MyCartService;
use App\Common\Services\UserService;
use App\Common\Services\Api\Common\CommonService;
use App\Http\Controllers\Controller;
use App\Models\CountryModel;
use App\Models\MakerModel;
use App\Models\ProductDetailsModel;
use App\Models\RoleModel;
use App\Models\UserLastActiveModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use Flash;
use Illuminate\Http\Request;
use Reminder;
use Sentinel;
use Session;
use Validator;

// use Socialite;

class AuthController extends Controller {
  public function __construct(
    UserModel $UserModel,
    RoleModel $RoleModel,
    MakerModel $MakerModel,
    UserService $UserService,
    EmailService $EmailService,
    GeneralService $GeneralService,
    CommonService $CommonService,
    InventoryService $InventoryService,
    MyCartService $MyCartService,
    UserLastActiveModel $UserLastActiveModel,
    CountryModel $CountryModel,
    ProductDetailsModel $ProductDetailsModel,
    CategoryModel $CategoryModel,
    CategoryTranslationModel $CategoryTranslationModel


  ) {

    $this->locale = \App::getLocale();

    $this->BaseModel = $UserModel;
    $this->RoleModel = $RoleModel;
    $this->MakerModel = $MakerModel;
    $this->UserService = $UserService;
    $this->CommonService = $CommonService;
    $this->EmailService = $EmailService;
    $this->GeneralService = $GeneralService;
    $this->InventoryService = $InventoryService;
    $this->MyCartService = $MyCartService;
    $this->UserLastActiveModel = $UserLastActiveModel;
    $this->UserModel = $UserModel;
    $this->CountryModel = $CountryModel;
    $this->ProductDetailsModel = $ProductDetailsModel;
    $this->CategoryTranslationModel = $CategoryTranslationModel;
    $this->CategoryModel  = $CategoryModel;

    $this->module_view_folder = 'front';
    $this->arr_view_data = [];
  }

  public function signup() {
    $arr_roles = $this->RoleModel
      ->where('slug', '!=', 'admin')
      ->get()
      ->toArray();
    $arr_country = $this->CountryModel->where('is_active', '1')->orderBy('name', 'ASC')->get()->toArray();

    $this->arr_view_data['arr_country'] = $arr_country;
    $this->arr_view_data['arr_roles'] = $arr_roles;
    $this->arr_view_data['page_title'] = 'SignUp Vendor';

    return view($this->module_view_folder . '.signup', $this->arr_view_data);
  }

  public function signup_retailer($guest = null) {

    $category_arr = [];

    if ($guest == "guest")
    {
      $login_url = url('/');

      Session::flash('message', "Please sign up or " . '<a href="' . url('/login') . '" class="login-text">' . ' login, ' . "</a>" . " &nbsp;if you're already a member.");
    }

    $arr_roles = $this->RoleModel
                      ->where('slug', '!=', 'admin')
                      ->get()
                      ->toArray();

    $country_arr = $this->CountryModel
                        ->where('is_active', '1')
                        ->orderBy('name', 'ASC')
                        ->get()
                        ->toArray();


    //get all active categories
    $category_arr = $this->CategoryModel->where('is_active', 1)
                          ->whereTranslation('locale', $this->locale)
                          ->get()
                          ->toArray();
     
   
    $this->arr_view_data['country_arr']  = $country_arr;
    $this->arr_view_data['category_arr'] = $category_arr;
    $this->arr_view_data['arr_roles']    = $arr_roles;
    $this->arr_view_data['page_title']   = 'SignUp Customer';

    return view($this->module_view_folder . '.signup_retailer', $this->arr_view_data);


  }


  public function signup_customer($guest = null) {
    if ($guest == "guest") {
      $login_url = url('/');

      Session::flash('message', "Please sign up or " . '<a href="' . url('/login') . '" class="login-text">' . ' login, ' . "</a>" . " &nbsp;if you're already a member.");
    }

    $arr_roles = $this->RoleModel
      ->where('slug', '!=', 'admin')
      ->get()
      ->toArray();

    $country_arr = $this->CountryModel->where('is_active', '1')->orderBy('name', 'ASC')->get()->toArray();

    $this->arr_view_data['country_arr'] = $country_arr;
    $this->arr_view_data['arr_roles'] = $arr_roles;
    $this->arr_view_data['page_title'] = 'SignUp Customer';

    return view($this->module_view_folder . '.signup_customer', $this->arr_view_data);
  }

  public function signup_influencer() {
    $arr_roles = $this->RoleModel
      ->where('slug', '!=', 'admin')
      ->get()
      ->toArray();

    $arr_country = $this->CountryModel->where('is_active', '1')->orderBy('name', 'ASC')->get()->toArray();

    $this->arr_view_data['arr_country'] = $arr_country;
    $this->arr_view_data['arr_roles'] = $arr_roles;
    $this->arr_view_data['page_title'] = 'SignUp Influencer';

    return view($this->module_view_folder . '.signup_influencer', $this->arr_view_data);
  }

  public function process_signup(Request $request) {
    $form_data = $request->all();
    
    $arr_rules = [

      'first_name' => 'required',
      'last_name' => 'required',
      'email' => 'required|email',
      'country_id' => 'required',
      'password' => 'required',
      'role' => 'required',
      'contact_no' => 'required',
    ];

    if (Validator::make($form_data, $arr_rules)->fails()) {
      $response = [
        'status' => 'ERROR',
        'msg' => 'Form validations failed, please check form feilds.',
      ];

      return response()->json($response);
    }

    $response = $this->UserService->user_registration($form_data);

    return response()->json($response);
  }

  public function visitors_enquiry(Request $request) {
    $form_data = $request->all();
    
    $arr_rules = [

      'name' => 'required',
      'user_type' => 'required',
      'mobile_no' => 'required',
      'country_code' => 'required',
      
    ];

    if (Validator::make($form_data, $arr_rules)->fails()) {
      $response = [
        'status' => 'ERROR',
        'msg' => 'Form validations failed, please check form feilds.',
      ];

      return response()->json($response);
    }
    
    $response = $this->CommonService->submit_visitors_enquiry($form_data);
    return response()->json($response);
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

  public function login($guest = null) {

    if ($guest == "guest") {
      $login_url = url('/');

      Session::flash('message', "Please sign up if you're already a member.");
    }

    if ($login_user = Sentinel::check()) {

      if ($login_user->inRole('retailer')) {

        // $panel_dashboard_url = url('/'.config('app.project.retailer_panel_slug').'/dashboard');
        $panel_dashboard_url = url()->previous();
      } elseif ($login_user->inRole('maker')) {

        // $panel_dashboard_url = url('/'.config('app.project.maker_panel_slug').'/dashboard');
        $panel_dashboard_url = url()->previous();
      } elseif ($login_user->inRole('representative')) {
        // $panel_dashboard_url = url('/'.config('app.project.representative_panel_slug').'/dashboard');
        $panel_dashboard_url = url()->previous();
      } elseif ($login_user->inRole('sales_manager')) {
        $panel_dashboard_url = url()->previous();
        // $panel_dashboard_url = url('/').'/sales_manager' ;
      } elseif ($login_user->inRole('customer')) {
        $panel_dashboard_url = url()->previous();
      } else if ($login_user->inRole('influencer')) {

        $panel_dashboard_url = url()->previous();
      } elseif ($login_user->inRole('admin')) {

        $this->arr_view_data['page_title'] = 'Login';
        return view($this->module_view_folder . '.login', $this->arr_view_data);
      }
      return redirect($panel_dashboard_url);
    } else {

      $this->arr_view_data['page_title'] = 'Login';
      return view($this->module_view_folder . '.login', $this->arr_view_data);
    }
  }

  public function process_login(Request $request) {
    /* Check Validations and display custom message*/
    $guest_back_url = Session::get('guest_back_url');

    $form_data = [];
    $credentials = [];

    $arr_rules = [
      'user_email' => 'required|email',
      'user_password' => 'required',
    ];

    $validator = Validator::make($request->all(), $arr_rules);

    if ($validator->fails()) {
      $response['status'] = 'error';
      $response['description'] = 'Form validations failed, please check form fields.';

      return response()->json($response);
    }

    $credentials = [
      'email' => trim($request->input('user_email')),
      'password' => trim($request->input('user_password')),
    ];

    $remember_me = $request->has('remember_me') ? true : false;

    try {
      //check user status is active or block
      /*---------------------------------*/

      $user_arr = Sentinel::findByCredentials($credentials);

      if ($user_arr) {
        /*---------get user last active details------------*/

        $last_login_count = $this->UserLastActiveModel->where('user_id', $user_arr->id)->count();

        /*------------------------------------------*/

        if ($last_login_count == 1 && $user_arr->status == '0' && $user_arr->is_approved == '0') {

          $response['status'] = 'error';
          $response['description'] = 'Sorry your account is block & disapproved.';

          return response()->json($response);
        } elseif ($user_arr->status == '0' && $user_arr->is_approved == '0') {

          $response['status'] = 'error';
          $response['description'] = 'Your account has not been activated yet.';

          return response()->json($response);
        } elseif ($user_arr->is_approved == '0') {
          $response['status'] = 'error';
          $response['description'] = 'Sorry your account is disapproved, please wait for admin verification.';

          return response()->json($response);
        }

        /* elseif($user_arr->status == '0')
                            {

                                    $response['status'] = 'error';
                                    $response['description'] = 'Sorry your account is blocked by admin.';

                                    return response()->json($response);
        */elseif ($last_login_count == 0 && $user_arr->status == 0) {
          $response['status'] = 'error';
          $response['description'] = 'Your account has not been activated yet.';

          return response()->json($response);
        } elseif ($last_login_count == 1 && $user_arr->status == 0) {
          $response['status'] = 'error';
          $response['description'] = 'Sorry your account is blocked by admin.';

          return response()->json($response);
        }
      }

      /*---------------------------------*/

      $check_authentication = Sentinel::authenticate($credentials, $remember_me);
      if ($check_authentication) {

        if (isset($remember_me) && $remember_me == true) {
          setcookie("email", $request->input('user_email'), time() + (10 * 365 * 24 * 60 * 60));
          setcookie("password", $request->input('user_password'), time() + (10 * 365 * 24 * 60 * 60));
          setcookie("rememberd", 'rememberd', time() + (10 * 365 * 24 * 60 * 60));
        } else {

          setcookie("email", '');
          setcookie("password", '');
          setcookie("rememberd", '');
        }

        $user = Sentinel::check();

        $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : 'Rwaltz';

        if ($user->inRole('maker') || $user->inRole('retailer') || $user->inRole('representative') || $user->inRole('sales_manager') || $user->inRole('customer') || $user->inRole('influencer')) {
          $response['status'] = 'success';
          $response['description'] = 'Login success! welcome to ' . $site_name;
          $redirect_to = Session::get('redirect_to');

          if ($user->inRole('maker')) {
            $count = 0;
            $email = trim($request->input('user_email'));
            $user_details = $this->BaseModel->where('email', $email)->first();

            $loggedIn_userId = $user->id;
            $count = $this->InventoryService->check_count($loggedIn_userId);

            foreach ($count as $key => $value) {

              if ($value != 0) {
                foreach ($value as $key => $val) {
                  $view_href = '';
                  $view_href = url('/') . '/vendor/products/view/' . base64_encode($val['product_id']);

                  if ($val['quantity'] == 0) {

                    $notification_arr = [];
                    $notification_arr['from_user_id'] = $loggedIn_userId;
                    $notification_arr['to_user_id'] = $loggedIn_userId;

                    $notification_arr['description'] = "Product sku no:" . $val['sku_no'] . " is out of stock";

                    $notification_arr['title'] = 'Product out of stock alert.';
                    $notification_arr['type'] = 'maker';
                    $notification_arr['status'] = '0';
                    $notification_arr['link'] = $view_href;

                    $this->GeneralService->save_notification($notification_arr);
                  } else if ($val['quantity'] != 0) {
                    /* $view_href   = url('/').'/vendor/products/view/'.base64_encode($val['product_id']);*/

                    $notification_arr = [];
                    $notification_arr['from_user_id'] = $loggedIn_userId;
                    $notification_arr['to_user_id'] = $loggedIn_userId;
                    $notification_arr['description'] = "Product sku no: " . $val['sku_no'] . " is running out of stock current quantity:" . $val['quantity'];

                    $notification_arr['title'] = 'Product out of stock alert';
                    $notification_arr['type'] = 'maker';
                    $notification_arr['status'] = '0';
                    $notification_arr['link'] = $view_href;

                    $this->GeneralService->save_notification($notification_arr);
                  }
                }
              }
            }
            if (isset($user_details)) {
              $count = $this->UserLastActiveModel->where('user_id', $user_details->id)->count();
            }

            $is_complete = $this->GeneralService->is_profile_complete($user_arr);

            if ($is_complete == "true") {

              if ($request->session()->has('guest_back_url')) {
                $response['redirect_link'] = $guest_back_url;
                Session::forget('guest_back_url');
              } else {
                $response['redirect_link'] = url('/vendor/dashboard');
              }
            } else {
              if ($request->session()->has('guest_back_url')) {
                $response['redirect_link'] = $guest_back_url;
                Session::forget('guest_back_url');
              } else {

                $response['login_count'] = $count;
                $response['redirect_link'] = url('/vendor/account_settings');
                Session::flash('message', 'Please fill all the required profile fields.');
              }
            }
          } else if ($user->inRole('sales_manager')) {

            $response['redirect_link'] = url('/sales_manager/');
            $is_complete = $this->GeneralService->is_profile_complete($user_arr);

            if ($is_complete == "true") {
              $response['redirect_link'] = url('/sales_manager/dashboard');
            } else {
              $response['redirect_link'] = url('/sales_manager/account_settings');
            }
          } elseif ($user->inRole('retailer')) {
            // check session data with user id 0
            $obj_check_session_data = $this->MyCartService->check_cart_data_while_login();

            if ($obj_check_session_data) {
              $arr_check_session_data = $obj_check_session_data->toArray();

              $transfer_session_data = $this->MyCartService->transfer_session_data_while_login($arr_check_session_data);
            }

            $check_bag_data = 0;
            $check_bag_data = $this->MyCartService->total_items();

            if (isset($check_bag_data) && $check_bag_data > 0 && $check_bag_data != 0) {

              Flash::success('Please proceed to payment.');
              $response['redirect_link'] = url('/my_bag');
            } else {
              $count = 0;
              $email = trim($request->input('user_email'));
              $user_details = $this->BaseModel->where('email', $email)->first();

              if (isset($user_details)) {
                $count = $this->UserLastActiveModel->where('user_id', $user_details->id)->count();
              }

              $is_complete = $this->GeneralService->is_profile_complete($user_arr);

              if ($is_complete == "true") {

                if ($request->session()->has('guest_back_url')) {
                  $response['redirect_link'] = $guest_back_url;
                  Session::forget('guest_back_url');
                } else {

                  $response['redirect_link'] = url('/');
                }
              } else {
                if ($request->session()->has('guest_back_url')) {
                  $response['redirect_link'] = $guest_back_url;
                  Session::forget('guest_back_url');
                } else {
                  $response['login_count'] = $count;

                  $response['redirect_link'] = url('/retailer/account_settings');

                  //Session::flash('message','Please fill all the required  profile fields');
                }
              }
            }
          } elseif ($user->inRole('representative')) {
            $is_complete = $this->GeneralService->is_profile_complete($user_arr);

            if ($is_complete == "true") {
              if ($request->session()->has('guest_back_url')) {
                $response['redirect_link'] = $guest_back_url;
                Session::forget('guest_back_url');
              } else {
                $response['redirect_link'] = url('/representative/dashboard');
              }
            } else {
              if ($request->session()->has('guest_back_url')) {
                $response['redirect_link'] = $guest_back_url;
                Session::forget('guest_back_url');
              } else {
                $response['redirect_link'] = url('/representative/account_settings');
                Session::flash('message', 'Please fill all the required profile fields.');
              }
            }
          } elseif ($user->inRole('customer')) {

            // check session data with user id 0
            $obj_check_session_data = $this->MyCartService->check_cart_data_while_login();

            /*developer priyanka
                            after login with customer we have to update product discount  using retail price
            */

            if (isset($obj_check_session_data)) {
              $temp_product_arr = json_decode($obj_check_session_data['product_data']);

              $new_product_array = [];

              if (isset($temp_product_arr->sku)) {
                foreach ($temp_product_arr->sku as $key1 => $product_details) {

                  if ($product_details->total_price >= $product_details->product_dis_min_amt) {

                    $temp_product_arr->sku->$key1->product_discount_amount = $product_details->total_price * $product_details->product_discount_value / 100;
                  }

                }
              }

              $obj_check_session_data->product_data = json_encode($temp_product_arr);

            }

            /*----------------------------------------------------------------------------------------*/

            if ($obj_check_session_data) {
              $arr_check_session_data = $obj_check_session_data->toArray();

              $transfer_session_data = $this->MyCartService->transfer_session_data_while_login($arr_check_session_data);
            }

            $check_bag_data = 0;
            $check_bag_data = $this->MyCartService->total_items();

            if (isset($check_bag_data) && $check_bag_data > 0 && $check_bag_data != 0) {

              Flash::success('Please proceed to payment.');
              $response['redirect_link'] = url('/customer_my_bag');
            } else {
              $count = 0;
              $email = $request->input('user_email');
              $user_details = $this->BaseModel->where('email', $email)->first();

              if (isset($user_details)) {
                $count = $this->UserLastActiveModel->where('user_id', $user_details->id)->count();
              }

              $is_complete = $this->GeneralService->is_profile_complete($user_arr);
              // dd($is_complete);
              if ($is_complete == "true") {

                if ($request->session()->has('guest_back_url')) {
                  $response['redirect_link'] = $guest_back_url;
                  Session::forget('guest_back_url');
                } else {

                  $response['redirect_link'] = url('/');
                }
              } else {
                if ($request->session()->has('guest_back_url')) {
                  $response['redirect_link'] = $guest_back_url;
                  Session::forget('guest_back_url');
                } else {
                  $response['login_count'] = $count;

                  $response['redirect_link'] = url('/customer/account_settings');

                  //Session::flash('message','Please fill all the required  profile fields');
                }
              }
            }
          } elseif ($user->inRole('influencer')) {
            $response['redirect_link'] = url('/influencer/dashboard');
          }
        } else {
          Session::flush();
          $response['status'] = 'error';
          $response['description'] = 'Invalid login credentials.';
        }
      } else {
        Session::flush();
        $response['status'] = 'error';
        $response['description'] = 'Invalid login credentials.';
      }
    } catch (\Cartalyst\Sentinel\Checkpoints\NotActivatedException $e) {
      $response = [
        'status' => 'ERROR',
        'message' => 'Your account has not been activated yet.',
      ];

      return response()->json($response);
    } catch (\Cartalyst\Sentinel\Checkpoints\ThrottlingException $e) {
      $response = [
        'status' => 'ERROR',
        'message' => $e->getMessage(),
      ];

      return response()->json($response);
    }
    return response()->json($response);
  }

  public function forgot_password(Request $request)
  {

    $loginUser = Sentinel::check();

    if ($loginUser)
    {
      return redirect('/');
    }

    if ($request->isMethod('post'))
    {
      $arr_rules = ['email' => 'required|email'];
      $validator = Validator::make($request->all(), $arr_rules);

      if ($validator->fails())
      {
        $response['status'] = 'error';
        $response['description'] = 'Provided email should not be blank or invalid.';

        return response()->json($response);
      }

      $site_setting_arr = $request->attributes->get('site_setting_arr');

      $credentials['email'] = $request->input('email');

      $user = Sentinel::findByCredentials($credentials);

      if($user)
      {
          if ($user->status == '0')
          {
            $response['status'] = 'ERROR';
            $response['msg']    = 'Sorry your account is blocked.';

            return response()->json($response);
          }
          if ($user->is_approved == '0')
          {
            $response['status'] = 'ERROR';
            $response['msg']    = 'Sorry your account is not approve.';

            return response()->json($response);
          }

      }

      if($user)
      {
          $ss = Reminder::exists($user);

          if ($ss)
          {
            Reminder::where('user_id', $user->id)->delete();
          }

          $reminder = Reminder::create($user);

          $password_reset_url = '<a target="_blank" style="background:#666; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="' . url('/') . '/reset_password/' . $reminder->code . '">Reset Password</a>.<br/>';


          /*Get site setting data from helper*/
          $arr_site_setting = get_site_settings(['site_name','website_url']);

          $arr_built_content = [
                                  'FIRST_NAME'   => $user->first_name,
                                  'REMINDER_URL' => $password_reset_url,
                                  'EMAIL'        => $user->first_name,
                                  'PROJECT_NAME' => $arr_site_setting['site_name'],
                                  'SITE_URL'     => $arr_site_setting['website_url'],
                              ];

          $arr_mail_data['email_template_id'] = '7';
          $arr_mail_data['arr_built_content'] = $arr_built_content;
          $arr_mail_data['arr_user']          = $user;

          try {
            
              $is_mail_sent = $this->EmailService->send_mail($arr_mail_data);
          }
          catch (\Exception $e)
          {
            $response['status'] = 'ERROR';
            $response['msg']    = $e->getMessage();

            return response()->json($response);

          }

          $response['status'] = 'SUCCESS';
          $response['msg']    = 'Email sent to ' . $user->email . ' please check email for further instructions.';
         
          return response()->json($response);


      }
      else
      {
        $response['status'] = 'ERROR';
        $response['msg']    = 'Account does not exist with ' . $request->input('email') . ', please try again.';
        return response()->json($response);
      }

    }
    else 
    {
      $this->arr_view_data['page_title']    = 'Forgot Password';
      return view($this->module_view_folder . '.forgot_password', $this->arr_view_data);
    }

  }

  public function reset_password($code = false) {

    $this->arr_view_data['page_title'] = 'Reset Password';

    if ($code == false) {
      return redirect('/');
    }

    $isUserAlreadyUsedReminder = Reminder::createModel()
      ->where(['code' => $code])
      ->where(['completed' => 1])
      ->count();

    $loginUser = Sentinel::check();

    if ($loginUser != false) {
      return redirect('/');
    }

    $reminder = Reminder::createModel()->where(['code' => $code])->first();

    if ($reminder == false) {
      flash::error('Reset password link is expired.');
      return redirect('/login');
    }

    $user = Sentinel::findById($reminder->user_id);

    if ($login_user = Sentinel::check()) {
      return redirect('/');
    }

    if (Reminder::exists($user) == true) {
      $this->arr_view_data['code'] = $reminder->code;

      return view($this->module_view_folder . '.reset_password', $this->arr_view_data);
    } else {
      Flash::error('Reset password link is expired.');
      return redirect('/login');
    }
  }

  public function process_reset_password(Request $request) {
    $validator = Validator::make($request->all(), [
      'code' => 'required',
      'new_password' => 'required|min:8|max:16',
      'cnfm_new_password' => 'required|same:new_password',
    ]);

    if ($validator->fails()) {
      $response['status'] = 'FAILURE';
      $response['description'] = 'Form validation failed, please check form fields.';

      return response()->json($response);
    }

    $reminder = Reminder::createModel()->where(['code' => $request->input('code')])->first();

    if ($reminder == false) {
      $response['status'] = 'ERROR';
      $response['msg'] = 'Error occurred while reseting password, please try again.';

      return response()->json($response);
    }

    $user = Sentinel::findById($reminder->user_id);

    if (Reminder::complete($user, $reminder->code, $request->input('new_password')) == true) {
      $response['status'] = 'SUCCESS';
      /*$response['msg']     = 'Password resets successfully, please <a href="'.url('/login').'">  click here </a>to login';*/
      $response['link'] = url('/login');
      Session::flash('message', 'Your password has been reset, please login with new password.');

      return response()->json($response);
    } else {
      $response['status'] = 'ERROR';
      $response['msg'] = 'Error occurred while reseting password, please try again.';

      return response()->json($response);
    }
  }

  public function does_exists_tax_id(Request $request, $param = false) {
    $form_data = $request->all(); 

    $tax_id = $form_data['tax_id'];
    $user_id = isset($form_data['user_id']) ? $form_data['user_id'] : '';

    // $tax_id_count = UserModel::where('tax_id', $tax_id)->count();
    $tax_id_count = UserModel::where('tax_id', $tax_id);

    if ($user_id) {
      $tax_id_count = $tax_id_count->where('id', '<>', $user_id)->count();
    } else {
      $tax_id_count = $tax_id_count->count();
    }

    // dd($sku_count);
    if ($tax_id_count != 0) {
      //return response()->json(['exists'=>'false']);
      return response()->json(['exists' => 'true'], 404);
    } else {
      return response()->json(['exists' => 'true']);
    }
  }

  public function does_company_exist(Request $request, $company_name = false) {
    $form_data = $request->all();

    $company_name = isset($form_data['company_name']) ? $form_data['company_name'] : '';
    $user_id = isset($form_data['user_id']) ? $form_data['user_id'] : '';

    $is_company_exists = $this->MakerModel->where('company_name', $company_name);

    if ($user_id) {
      $is_company_exists = $is_company_exists->where('user_id', '<>', $user_id)->count();
    } else {
      $is_company_exists = $is_company_exists->count();
    }

    if ($is_company_exists > 0) {
      return response()->json(['exists' => 'true'], 404);
    } else {
      return response()->json(['exists' => 'true']);
    }
  }

  public function does_influencer_exist(Request $request, $influencer_code = false) {
    $form_data = $request->all();
    //dd($form_data);
    $influencer_code = isset($form_data['influencer_code']) ? $form_data['influencer_code'] : '';
    //dd($influencer_code);
    $user_id = isset($form_data['user_id']) ? $form_data['user_id'] : '';

    $is_influencer_exists = $this->UserModel->where('influencer_code', $influencer_code);
//dd($is_influencer_exists);
    if ($user_id) {
      $is_influencer_exists = $is_influencer_exists->where('user_id', '<>', $user_id)->count();
    } else {
      $is_influencer_exists = $is_influencer_exists->count();
    }

    if ($is_influencer_exists > 0) {
      return response()->json(['exists' => 'true'], 404);
    } else {
      return response()->json(['exists' => 'true']);
    }
  }

  /* Function to check real company name is exists already or not */
    public function does_real_company_exist(Request $request, $real_company_name = false) {
      $form_data = $request->all();

      $real_company_name = isset($form_data['real_company_name']) ? $form_data['real_company_name'] : '';
      $user_id = isset($form_data['user_id']) ? $form_data['user_id'] : '';

      $is_company_exists = $this->MakerModel->where('real_company_name', $real_company_name);

      if ($user_id) {
        $is_company_exists = $is_company_exists->where('user_id', '<>', $user_id)->count();
      } else {
        $is_company_exists = $is_company_exists->count();
      }

      if ($is_company_exists > 0) {
        return response()->json(['exists' => 'true'], 404);
      } else {
        return response()->json(['exists' => 'true']);
      }
    }


  public function logout() {
    Sentinel::logout();
    Session::flush();
    return redirect('/');
  }

  public function save_session(Request $request) {
    Session::put('redirect_to', $request->redirect_to);

    return response()->json(['status' => 'SUCCESS']);
  }

  // public function socialLogin($social)
  // {
  //    return Socialite::driver($social)->redirect();
  // }

  // public function handle_provider_callback($social)
  // {
  //     if($social == 'twitter')
  //     {
  //         $data['auth_type'] = '1';
  //     }
  //     else
  //     {
  //         $data['auth_type'] = '2';
  //     }

  //     $userSocial = Socialite::driver($social)->stateless()->user();

  //     if(isset($userSocial) && $userSocial != null)
  //     {
  //         $full_name          = isset($userSocial->name)?$userSocial->name:'';
  //         $exp_name           = explode(' ', $full_name);
  //         $data['first_name'] = isset($exp_name[0])?$exp_name[0]:'';
  //         $data['last_name']  = isset($exp_name[1])?$exp_name[1]:'';
  //         $data['auth_id']    = isset($userSocial->id)?$userSocial->id:'';
  //         $data['auth_token'] = isset($userSocial->token)?$userSocial->token:'';
  //         $data['auth_avtar'] = isset($userSocial->avatar_original)?$userSocial->avatar_original:'';
  //         $data['email']      = isset($userSocial->email)?$userSocial->email:null;
  //         $data['auth_response']  = json_encode($userSocial);
  //         $data['password']     = str_random(8);
  //         $data['tmp_password'] = $data['password'];
  //         $data['is_approved']  = '1';
  //         $data['role']         = 'retailer';

  //         $user_is_exist = $this->BaseModel->where(['auth_id' => $data['auth_id']]);

  //         if($data['email'] != null)
  //         {
  //             $user_is_exist = $user_is_exist->orWhere('email',$data['email']);
  //         }

  //         $user_is_exist = $user_is_exist->count();

  //         if($user_is_exist <= 0)
  //         {
  //             if(!isset($data['email']) || (isset($data['email']) && $data['email'] == '')){
  //               $arr_response['status'] = 'ERROR';
  //               $arr_response['msg'] = 'Provided email should not be blank or in valid';

  //               return redirect('/login');
  //             }

  //             /*Check provided password is not blank or invaild*/
  //             if(!isset($data['password']) || (isset($data['password']) && $data['password'] == '')){
  //               $arr_response['status'] = 'ERROR';
  //               $arr_response['msg'] = 'Provided password should not be blank or in valid';

  //               return redirect('/login');
  //             }

  //               if(Sentinel::findByCredentials(['email'=>$data['email']]) != null)
  //               {
  //                 // $arr_response['status'] = 'ERROR';
  //                 // $arr_response['msg'] = 'Provided email already exists in system, try different one';

  //                 return redirect('/login');
  //               }

  //               /*Register user with provided credentials*/
  //               $credentials                   = [];
  //               $credentials['email']          = $data['email'];
  //               $credentials['password']       = $data['password'];
  //               $credentials['first_name']     = $data['first_name'];
  //               $credentials['last_name']      = $data['last_name'];
  //               $credentials['is_approved']    = 1;
  //               $credentials['auth_id']        = $data['auth_id'];
  //               $credentials['auth_token']     = $data['auth_token'];
  //               $credentials['auth_avtar']     = $data['auth_avtar'];
  //               $credentials['auth_response']  = $data['auth_response'];
  //               $credentials['tmp_password']   = $data['tmp_password'];

  //               $user = Sentinel::registerAndActivate($credentials);

  //               if($user)
  //               {
  //                 $role = Sentinel::findRoleBySlug('retailer');

  //                 $role->users()->attach($user);
  //               }

  //               return redirect('/retailer/dashboard');
  //         }
  //         else
  //         {
  //             $update_user = $this->BaseModel->where('auth_id',$data['auth_id']);

  //             if($data['email'] != null){
  //                 $update_user = $update_user->orWhere('email',$data['email']);
  //             }

  //             $update_user = $update_user->update(['auth_token'=>$data['auth_token']]);

  //             if($update_user){

  //                 $obj_user = $this->BaseModel->where('auth_id',$data['auth_id'])->first();

  //                 if($obj_user)
  //                 {
  //                     $user_id = isset($obj_user->id)?$obj_user->id:'';

  //                     $user = Sentinel::findById($user_id);

  //                     if($user){

  //                         Sentinel::login($user);

  //                         return redirect('/retailer/dashboard');

  //                     }else{
  //                         return redirect('/login');
  //                     }
  //                 }else{
  //                       return redirect('/login');
  //                 }
  //             }else{
  //                 return redirect('/login');
  //             }
  //         }
  //     }
  //     else
  //     {
  //         return redirect('/login');
  //     }
  // }
}
