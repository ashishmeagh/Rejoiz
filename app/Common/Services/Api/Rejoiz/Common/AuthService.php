<?php

namespace App\Common\Services\Api\Rejoiz\Common;

use App\Models\UserLastActiveModel;
use App\Models\UserModel;
use App\Models\SiteSettingModel;
use App\Models\RetailerModel;
use Activation;


use App\Common\Services\EmailService;

use App\Common\Services\InventoryService;
use App\Common\Services\GeneralService;
use App\Common\Services\MyCartService;
use App\Common\Services\Api\Rejoiz\Front\BagService;
use App\Common\Services\Api\Rejoiz\Common\JWTService;

use \Session;
use \Mail;
use \Sentinel;
use \Reminder;
use \DB;

use Ahc\Jwt\JWT;

class AuthService {

	public function __construct(
									UserLastActiveModel $UserLastActiveModel,
									UserModel $UserModel,
									EmailService $EmailService,
									SiteSettingModel $SiteSettingModel,
									RetailerModel $RetailerModel,
									InventoryService $InventoryService,
									GeneralService $GeneralService,
									MyCartService $MyCartService,
									JWTService $JWTService,
									BagService $BagService
								) 
	{
		$this->UserLastActiveModel 	= $UserLastActiveModel;
		$this->UserModel 			= $UserModel;
		$this->SiteSettingModel 	= $SiteSettingModel;
		$this->RetailerModel        = $RetailerModel; 
		$this->InventoryService 	= $InventoryService;
		$this->GeneralService 		= $GeneralService;
		$this->MyCartService 		= $MyCartService;
		$this->JWTService 			= $JWTService;
		$this->BagService           = $BagService;
		$this->EmailService			= $EmailService;
		$this->admin_url_path       = url(config('app.project.admin_panel_slug'));

	}

	public function login($credentials = null) {

		try {

			$user_obj  = Sentinel::findByCredentials($credentials);
			$user_role = ""; 

			if ($user_obj) {

		        /*---------get user last active details------------*/

		        	$last_login_count = $this->UserLastActiveModel->where('user_id', $user_obj->id)->count();

		        /*------------------------------------------*/

		        if ($last_login_count == 1 && $user_obj->status == '0' && $user_obj->is_approved == '0') {

		          $response['status']      	= 'failure';
		          $response['message']		= 'Sorry your account is block & disapproved.';
		          $response['data'] 		= '';

		          return $response;

		        } elseif ($user_obj->status == '0' && $user_obj->is_approved == '0') {

		          $response['status']      	= 'failure';
		          $response['message'] 	    = 'Your account has not been activated yet.';
		          $response['data'] 		= '';

		          return $response;

		        } elseif ($user_obj->is_approved == '0') {

		          $response['status']      	= 'failure';
		          $response['message'] 	    = 'Sorry your account is disapproved, please wait for admin verification.';
		          $response['data'] 		= '';

		          return $response;

		        } elseif ($last_login_count == 0 && $user_obj->status == 0) {

					$response['status']     = 'failure';
					$response['message']    = 'Your account has not been activated yet.';
					$response['data'] 		= '';

					return $response;

		        } elseif ($last_login_count == 1 && $user_obj->status == 0) {

		          $response['status']       = 'failure';
		          $response['message']      = 'Sorry your account is blocked by admin.';
		          $response['data'] 	    = '';

		          return $response;
		        }
		    }

			try {
				
				$check_authentication = Sentinel::stateless($credentials); 	// Login Check

			} catch (\Cartalyst\Sentinel\Checkpoints\NotActivatedException $e ) {

				$response = [

					'status'  => 'failure',
					'message' =>  $e->getMessage(),
					'data' 	  =>  ''
				];

				return $response;
			}

			if ($check_authentication == false) {

				$response['status']      = 'failure';
				$response['message']     = 'Invalid login credentials.';
				$response['data'] 		 = '';

	      		return $response;
			}

		    if($check_authentication) {

				$token = $this->JWTService->encode($check_authentication);  // Token Generation

				$data['user_id']	= $check_authentication->id;
				$data['first_name']	= $check_authentication->first_name;
				$data['last_name']	= $check_authentication->last_name;
				$data['email']		= $check_authentication->email;
				$data['token'] 		= $token['data'];

				$user_role = $check_authentication->roles->first()->name;


				$data['role']	=  isset($user_role)?$user_role:''; 

				if ($check_authentication->inRole('maker')) {

					$count        = 0;
					$email        = trim($credentials['email']);
					$user_details = $this->UserModel->where('email', $email)->first();

					$loggedIn_userId = $check_authentication->id;
					$count =   $this->InventoryService->check_count($loggedIn_userId);

					foreach ($count as $key => $value) {

					  	if ($value != 0) {

						    foreach ($value as $key => $val) {

								$view_href   = '';
								$view_href   = url('/') . '/vendor/products/view/' . base64_encode($val['product_id']);

								if ($val['quantity'] == 0) {

									$notification_arr                 = [];
									$notification_arr['from_user_id'] = $loggedIn_userId;
									$notification_arr['to_user_id']   = $loggedIn_userId;
									$notification_arr['description']  = "Product sku no:" . $val['sku_no'] . " is out of stock";
									$notification_arr['title']        = 'Product out of stock alert.';
									$notification_arr['type']         = 'maker';
									$notification_arr['status']       = '0';
									$notification_arr['link']         = $view_href;

						       		$this->GeneralService->save_notification($notification_arr);

						      	} 
						      	else if ($val['quantity'] != 0) { 

							        $notification_arr                 = [];
							        $notification_arr['from_user_id'] = $loggedIn_userId;
							        $notification_arr['to_user_id']   = $loggedIn_userId;
							        $notification_arr['description']  = "Product sku no: " . $val['sku_no'] . " is running out of stock current quantity:" . $val['quantity'];

							        $notification_arr['title']        = 'Product out of stock alert';
							        $notification_arr['type']         = 'maker';
							        $notification_arr['status']       = '0';
							        $notification_arr['link']         = $view_href;

							        $this->GeneralService->save_notification($notification_arr);
							    }
						    }
					  	}
					}

		            if (isset($user_details)) {

						$count = $this->UserLastActiveModel->where('user_id', $user_details->id)->count();
						$data['login_count'] = $count;
		            }

	            	$data['is_profile_complete'] = $this->GeneralService->is_profile_complete($user_obj->toArray()); 	// is profile complete 
	          	} 

	          	elseif ($check_authentication->inRole('sales_manager')) {

		            $data['is_profile_complete'] = $this->GeneralService->is_profile_complete($user_obj->toArray());
	            
	          	} 
	          	elseif ($check_authentication->inRole('retailer')) {

		            $obj_check_session_data = $this->BagService->check_cart_data_while_login($user_obj->id);

		            if ($obj_check_session_data) {

						$arr_check_session_data = $obj_check_session_data->toArray();

						$transfer_session_data = $this->BagService->transfer_session_data_while_login($arr_check_session_data,$user_obj->id);
		            }

		            $check_bag_data = 0;
		            $check_bag_data = $this->BagService->total_items($user_obj->id);

		            if (isset($check_bag_data) && $check_bag_data > 0 && $check_bag_data != 0) {

		            	$data['bag_data']  = $check_bag_data;
						//Flash::success('Please proceed to payment.');
						//$response['redirect_link'] = url('/my_bag');
		            } 

	            	else {

						$count        = 0;
						$email        = trim($credentials['email']);
						$user_details = $this->UserModel->where('email', $email)->first();

						if (isset($user_details)) {

							$count = $this->UserLastActiveModel->where('user_id',$user_obj->id)->count();
							$data['login_count'] = $count;
						}

	              		$data['is_profile_complete'] = $this->GeneralService->is_profile_complete($user_obj->toArray());
	            	}
	          	} 

	          	elseif ($check_authentication->inRole('representative')) {

					$data['is_profile_complete'] = $this->GeneralService->is_profile_complete($user_obj->toArray());
	          	}

				elseif ($check_authentication->inRole('customer')) {

		            // check session data with user id 0
		            $obj_check_session_data = $this->MyCartService->check_cart_data_while_login();

		            // after login with customer we have to update product discount  using retail price
		            $temp_product_arr = json_decode($obj_check_session_data['product_data']);

		            if (isset($temp_product_arr) && !empty($temp_product_arr)) {
		            	
			            if(isset($temp_product_arr->sku)) {

			                foreach($temp_product_arr->sku as $key1 => $product_details)  {
			                    
			                    if($product_details->total_price >= $product_details->product_dis_min_amt) { 

			                       $temp_product_arr->sku->$key1->product_discount_amount =  $product_details->total_price*$product_details->product_discount_value/100;
			                    }
			                } 
			            }  

	            		$obj_check_session_data['product_data'] = json_encode($temp_product_arr);
		            }

		            /*----------------------------------------------------------------------------------------*/

		            if ($obj_check_session_data) {

						$arr_check_session_data = $obj_check_session_data->toArray();

						$transfer_session_data = $this->MyCartService->transfer_session_data_while_login($arr_check_session_data);
		            }

		            $check_bag_data = 0;
		            $check_bag_data = $this->MyCartService->total_items();

		            if (isset($check_bag_data) && $check_bag_data > 0 && $check_bag_data != 0) {

		              $response['message']   = 'Please proceed to payment.';
		              $data['bag_data']      =  $check_bag_data;  
		             // $response['redirect_link'] = url('/customer_my_bag');
		            } 
	           		else {

						$count        = 0;
						$email        = $credentials['email'];
						$user_details = $this->UserModel->where('email', $email)->first();

						if (isset($user_details)) {

							$count = $this->UserLastActiveModel->where('user_id', $user_details->id)->count();
							$data['login_count'] = $count;
	 					}

						$data['is_profile_complete'] = $this->GeneralService->is_profile_complete($user_obj->toArray()); 
	           		}
	          	}

           		$response['status']		= 'success';
				$response['message']	= 'Login successfully.';
				$response['data']		=  isset($data) ? $data : [];

	      		return $response;
          	}	
          	else {

				$response['status']		= 'failure';
				$response['message']	= 'Invalid login credentials.';
				$response['data']		= '';

				return $response;
	        }
			
		} catch (Exception $e) {

			$response = [
				'status'  => 'failure',
				'message' => $e->getMessage(),
				'data'    => ''
			];

      		return $response;
    	}
	}

	public function forgot_password($email = '') {

		$site_setting_obj = $this->SiteSettingModel->first();

		$credentials['email'] = $email;

      	$user = Sentinel::findByCredentials($credentials);

		if ($user) {

	      	if ($user->status == '0') {

				$response['status']   = 'failure';
				$response['message']  = 'Sorry your account is blocked.';
				$response['data']     = '';

				return $response;
	        }

	        if ($user->is_approved == '0') {

				$response['status']   = 'failure';
				$response['message']  = 'Sorry your account is not approved.';
				$response['data']     = '';

				return $response;
	        }

	        /**************** GENERATE OTP ****************/
                $i   = 0; 
                $otp = "";

                while($i < 5) {

                    $otp .= mt_rand(0, 9);
                    $i++;
                }

                $otp_update = $this->UserModel
					            	->where('id' , $user->id)
					            	->update(['otp' => $otp]);
					            	
            /**************** GENERATE OTP ****************/

	        $password_reset_otp = '<a target="_blank" style="background:#666; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="#">'.$otp.'</a>.<br/>';

	        $arr_built_content = [

				'FIRST_NAME'     => $user->first_name,
				'REMINDER_URL'   => $password_reset_otp,
				'EMAIL'          => $user->first_name,
				'SITE_URL'       => $site_setting_obj->site_name,
				'project_name'   => $site_setting_obj->site_name          
	        ];

	        $arr_mail_data['email_template_id'] = '81';
	        $arr_mail_data['arr_built_content'] = $arr_built_content;
	        $arr_mail_data['arr_user']          = $user;

	        try {

				$is_mail_sent = $this->EmailService->send_mail($arr_mail_data);

	        } catch (\Exception $e) {

				$response['status']   = 'failure';
				$response['message']  = $e->getMessage();
				$response['data']     = '';

				return $response;
	        }

	        $response['status']   = 'success';
	        $response['message']  = 'Email sent to ' . $user->email . ' please check email for further instructions.';
	        $response['data']     = '';

	        return $response;
      	} 
		else {

	        $response['status']   = 'failure';
	        $response['message']  = 'Account does not exist with ' . $email . ', please try again.';
	        $response['data']     = '';

	        return $response;
      	}
	}

	public function verify_otp($data = []) {

		$credentials['email'] = $data['email'];

      	$user = Sentinel::findByCredentials($credentials);

      	if ($user->otp == $data['otp'] ) {

      		$otp_update = $this->UserModel
				            	->where('id' , $user->id)
				            	->update(['otp' => '']);

      		$response['status']		= 'success';
			$response['message']	= 'OTP verified successfully.';
			$response['data']		= '';

			return $response;
      	}
      	else {

      		$response['status']		= 'failure';
			$response['message']	= 'Wrong OTP, please try again.';
			$response['data']		= '';
			
			return $response;
      	}
	}

	public function change_password($data = []) {

		$credentials 			= [];
		$credentials['email'] 	= $data['email'];

		$user = Sentinel::findByCredentials($credentials);

		$update_credentials['password']	= $data['password'];

		if ($user) {
			
			try {

				Sentinel::update($user,$update_credentials);

	      		$response['status']		= 'success';
				$response['message']	= 'Password has been changed successfully.';
				$response['data']		= '';
				
				return $response;

			} catch (Exception $e) {

				$response = [
	                'status'	=> 'failure',
	                'message'	=> $e->getMessage(),
	                'data'		=> ''
	            ];

	            return $response; 			
			}
		}
		else {

      		$response['status']		= 'failure';
			$response['message']	= 'User not found.';
			$response['data']		= '';
			
			return $response;
		}
	}

	public function sign_up($arr_data = []){

     $arr_response = [];

     try
     {
    
    if(isset($arr_data) && count($arr_data)>0){


      $email            = isset($arr_data['email'])?$arr_data['email']:'';
      $password         = isset($arr_data['password'])?$arr_data['password']:'';
      $confirm_password = isset($arr_data['confirm_password'])?$arr_data['confirm_password']:'';

      $first_name       = isset($arr_data['first_name'])?$arr_data['first_name']:'';
      $last_name        = isset($arr_data['last_name'])?$arr_data['last_name']:'';
      //$country_code   = isset($arr_data['country_code'])?$arr_data['country_code']:'';
      $country_code     = isset($arr_data['country_code'])?$arr_data['country_code']:'';
      $tax_id           = isset($arr_data['tax_id'])?$arr_data['tax_id']:'';
      $contact_no       = isset($arr_data['contact_no'])?$arr_data['contact_no']:'';  
      if($contact_no && $country_code){
        $contact_no     = str_replace($country_code,"",$contact_no);
      }
   
      $user_role_slug   = isset($arr_data['role'])?$arr_data['role']:'';

      $address          = isset($arr_data['address'])?$arr_data['address']:'';    
      //$post_code      = isset($arr_data['post_code'])?$arr_data['post_code']:'';    
      $buying_status    = isset($arr_data['buying_status'])?$arr_data['buying_status']:'';    
      $store_name       = isset($arr_data['store_name'])?$arr_data['store_name']:'';      
      $store_website    = isset($arr_data['store_website'])?$arr_data['store_website']:'';      
      $country_id       = isset($arr_data['country_id'])?$arr_data['country_id']:'';

      $post_code        = isset($arr_data['zip_code'])?$arr_data['zip_code']:'';

      $years_in_business = isset($arr_data['years_in_business'])?$arr_data['years_in_business']:'';
      
      $annual_sales      = isset($arr_data['Annual_Sales'])?$arr_data['Annual_Sales']:'';
      $store_description = isset($arr_data['store_description'])?$arr_data['store_description']:'';
    

            /*Check provided first name is not blank or invalid*/
       if(!isset($first_name) || (isset($first_name) && $first_name == '') || (!preg_match("/^[a-zA-Z]+$/",$first_name)))
      {
        
         $arr_response['status']     = 'failure';
         $arr_response['message']    = 'Provided first name should not be blank or invalid.';
         $arr_response['data']       = ""; 

         return $arr_response;
      } 

      /*Check provided last name is not blank or invalid*/
       if(!isset($last_name) || (isset($last_name) && $last_name == '') || (!preg_match("/^[a-zA-Z]+$/",$last_name)))
      {
         $arr_response['status']     = 'failure';
         $arr_response['message']    = 'Provided last name should not be blank or invalid.';
         $arr_response['data']       = ""; 

         return $arr_response;
      } 


      
      /*Check provided email is natcasesort(array)ot blank or invaild*/ 
      if(!isset($email) || (isset($email) && $email == '')){
        $arr_response['status']  = 'failure';
        $arr_response['message'] = 'Provided email should not be blank or in valid';
        $arr_response['data']    = ""; 

        return $arr_response;
      } 
    
      /*Check provided password is not blank or invaild*/
      if(!isset($password) || (isset($password) && $password == '')){
        $arr_response['status']  = 'failure';
        $arr_response['message'] = 'Provided password should not be blank or in valid';
        $arr_response['data']    = ""; 

        return $arr_response;
      }

        /*Check provided password is not blank or invaild*/
      if(!isset($confirm_password) || (isset($confirm_password) && $confirm_password == '') || $confirm_password!=$password){
        $arr_response['status']  = 'failure';
        $arr_response['message'] = 'Confirm password should be same as password.';
        $arr_response['data']    = ""; 

        return $arr_response;
      }

      /*Check provided mobile no is not accepting digits*/
      if(!isset($contact_no) || (isset($contact_no) && $contact_no == '') || (!is_numeric($contact_no)))
      {
         $arr_response['status']  = 'failure';
         $arr_response['message'] = 'Provided contact no should not be blank or invalid.';
         $arr_response['data']    = ""; 

         return $arr_response;
      }


      $tax_id = isset($arr_data['tax_id'])?$arr_data['tax_id']:'';

      // if($user_role_slug == 'Maker' || $user_role_slug == 'retailer')
      if($user_role_slug == 'retailer')
      {
        $is_tax_id_exists = $this->UserModel->where('tax_id',$tax_id)->count();

          if($is_tax_id_exists > 0)
          {
             $arr_response['status']  = 'failure';
             $arr_response['message'] = 'Tax id already exists';
             $arr_response['data']    = ""; 

           return $arr_response;
          }
      }
        

      if($user_role_slug == 'Maker')
      {
        $company_name = isset($arr_data['company_name'])?$arr_data['company_name']:'';

         $is_company_exists = $this->MakerModel->where('company_name',$company_name)->count();

           if($is_company_exists > 0)
           {
             $arr_response['status']  = 'failure';
             $arr_response['message'] = 'Company name already exists';
             $arr_response['data']    = ""; 

             return $arr_response;
           }

           $other_category_name = isset($arr_data['other_category_name'])?$arr_data['other_category_name']:'';

         $is_category_exists = $this->CategoryTranslationModel->where('category_name',$other_category_name)->count();




        /* check other category name is already exists */
        $service_category_details       = $this->CategoryModel->getTable();
        $prefixed_service_category      = DB::getTablePrefix().$this->CategoryModel->getTable();
            $prefixed_category_translation  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

        $is_category_exists = DB::table($service_category_details)
        ->select(DB::raw($prefixed_service_category.".id,".  
            $prefixed_service_category.'.is_active,'.
            $prefixed_service_category.'.category_image,'.
            $prefixed_category_translation.'.category_name'
        ))
        ->join($prefixed_category_translation,$prefixed_service_category.'.id','=',$prefixed_category_translation.'.category_id')
        ->where($prefixed_category_translation.'.locale','en')
        ->where($prefixed_service_category.'.admin_confirm_status','=','0')
        ->where($prefixed_service_category.'.is_active','=','1')
        ->where('category_name',$other_category_name)
        ->whereNull($service_category_details.'.deleted_at')
        ->count();


           if($is_category_exists > 0)
           {
             $arr_response['status']     = 'failure';
             $arr_response['message']    = 'Other category name already exists';
             $arr_response['data']       = ""; 

             return $arr_response;
           }
      }

      /*Check provided role is not blank or invaild*/
      if(Sentinel::findRoleBySlug($user_role_slug)==null){
        
        $arr_response['status']     = 'failure';
        $arr_response['message']    = 'Provided role is not valid';
        $arr_response['data']       = ""; 

        return $arr_response;
      }
      else
      {
        /*check email duplication*/
        if(Sentinel::findByCredentials(['email'=>$email]) != null)
        {
          $arr_response['status']     = 'failure';
          $arr_response['message']    = 'Provided email already exists in system, you may try different email';
          $arr_response['data']       = ""; 

          return $arr_response;
        }

        /*Register user with provided credentials*/
        $credentials                   = [];
        $credentials['email']          = $email;
        $credentials['password']       = $password;
        $credentials['first_name']     = $first_name;
        $credentials['last_name']      = $last_name;
        $credentials['country_code']   = $country_code;
        $credentials['tax_id']         = $tax_id;
        $credentials['contact_no']     = trim($contact_no); 
        
        $credentials['address']      = $address;

        $credentials['buying_status']  = $buying_status;
        $credentials['store_name']     = $store_name;
        $credentials['store_website']  = $store_website;

        $credentials['country_id']     = $country_id;
        $credentials['post_code']      = $post_code;
        $credentials['is_login']       = 0;
        
        if($user_role_slug == 'customer' || $user_role_slug == 'influencer')
        {
          $credentials['is_approved']    = 1;
        }
        else
        {
          $credentials['is_approved']    = 0;

        }
        //dd($credentials);
        /*$credentials['ref_no']         = md5(uniqid(rand().time(), true));*/

        $user = Sentinel::register($credentials);
        /*dd($user->id);*/
        if($user)
        {
          //Store maker details
          $arr_maker = [];

          if($user_role_slug == 'Maker')              
          {
            $arr_maker['user_id']     = $user->id;
            // $arr_maker['brand_name']  = isset($arr_data['brand_name'])?$arr_data['brand_name']:'';
            $arr_maker['company_name']  = isset($arr_data['company_name'])?$arr_data['company_name']:'';
            $arr_maker['website_url'] = isset($arr_data['website_url'])?$arr_data['website_url']:'';
            $arr_maker['primary_category_id'] = isset($arr_data['primary_category_id'])?$arr_data['primary_category_id']:'';
            $arr_maker['primary_category_name'] = isset($arr_data['other_category_name'])?$arr_data['other_category_name']:'';
            $arr_maker['no_of_stores'] = isset($arr_data['no_of_stores'])?$arr_data['no_of_stores']:'';
            $arr_maker['insta_url'] = isset($arr_data['insta_url'])?$arr_data['insta_url']:'';


            // Get maximum sequence no
            $obj_max_seq_no = MakerModel::
                            select('listing_sequence_no')
                            ->orderBy('listing_sequence_no','desc')
                            ->limit(1)
                            ->get();
                        $maker_seq_no = 1;
                        $res_maker_seq_no = $obj_max_seq_no->toArray();
                        if(!empty($res_maker_seq_no)){
                          $maker_seq_no = isset($res_maker_seq_no[0]['listing_sequence_no']) ? $res_maker_seq_no[0]['listing_sequence_no']+1 : 1;
                        }    
                        $arr_maker['listing_sequence_no'] = $maker_seq_no;                       

            $store_maker_details = $this->MakerModel->create($arr_maker);
            $new_user_id = $arr_maker['user_id'];
            //dd($store_maker_details->id,$arr_maker['user_id']);

          } 

          if($user_role_slug == 'retailer')             
          {
            $arr_retailer['user_id']     = $user->id;
            $arr_retailer['years_in_business'] = isset($arr_data['years_in_business'])?$arr_data['years_in_business']:'';
            $arr_retailer['annual_sales'] = isset($arr_data['Annual_Sales'])?$arr_data['Annual_Sales']:'';
            $arr_retailer['store_description'] = isset($arr_data['store_description'])?$arr_data['store_description']:'';
            $arr_retailer['store_name'] = isset($store_name)?$store_name:'';

            $arr_retailer['store_website'] = isset($store_website)?$store_website:'';

            $arr_retailer['store_description'] = isset($store_description)?$store_description:'';

            $store_retailer_details = $this->RetailerModel->create($arr_retailer);  
          }

          $activation = Activation::create($user);

          if($activation)
                {

                /******************Notification to admin START*******************************/
                    $admin_role = Sentinel::findRoleBySlug('admin');  

                    $admin_obj  = DB::table('role_users')->where('role_id',$admin_role->id)->first();
                    $admin_id   = 0;
                    if($admin_obj)
                    {
                        $admin_id = $admin_obj->user_id;            
                    }
                    
                    $user_type      = "";
                    $user_view_href     = '';
                    if($user_role_slug=="Maker")
                    {
                      $user_type="Vendor";

                            $user_view_href   = $this->admin_url_path.'/vendor/view/'.base64_encode($user->id);
                    }
                    else if($user_role_slug=="retailer")
                    {
                      $user_type='Retailer';
                      $user_view_href   = $this->admin_url_path.'/retailer/view/'.base64_encode($user->id);
                    }
                  else if($user_role_slug=="customer")
                  {
                    $user_type='Customer';
                    $user_view_href   = $this->admin_url_path.'/customer/view/'.base64_encode($user->id);
                  }
                  else if($user_role_slug=='influencer')
                  {
                    $user_type='Influencer';
                    $user_view_href   = $this->admin_url_path.'/influencer/view/'.base64_encode($user->id);
                  }

                    $arr_event                 = [];
                    $arr_event['from_user_id'] = $user->id;
                    $arr_event['to_user_id']   = $admin_id;
                    $arr_event['type']         = 'admin';
                    $arr_event['description']  = 'A New '. $user_type.' '.$first_name.' '.$last_name.' has been registered successfully.';
                    $arr_event['title']        = 'New User Registration';
                    $arr_event['link']         = $user_view_href;
                    
                    $this->GeneralService->save_notification($arr_event);


                    // If successfully submited then send notification and send mail   

            // If vendor selected category as Other
            if(isset($arr_maker['primary_category_name']) && $arr_maker['primary_category_name'] != "" && $user_role_slug=="Maker"){ 

            $notif_category_name = $arr_maker['primary_category_name'];         
                  $admin_id = get_admin_id();  
                  $view_href = '';
                  
                  $notification_arr['from_user_id'] = $new_user_id;
                  $notification_arr['to_user_id']   = $admin_id;
                  $notification_arr['description']  = 'A new vendor <b>'.$first_name.' '.$last_name.'</b> has requested for category named as <b>'.$notif_category_name.'</b>. Please update admin approval status  & category status.';
                  $notification_arr['title']        = 'New User Request Category';
                  $notification_arr['type']         = 'admin';
                  $notification_arr['link']         = $view_href;
                  $this->GeneralService->save_notification($notification_arr);

                  // Send mail
                  $arr_user = $user->toArray();
                  $redirection_url = "";              
                  $arr_built_content = ['USER_FNAME'           => '',
                                        'APP_NAME'             => isset($this->site_setting_arr['site_name'])?$this->site_setting_arr['site_name']:'',
                                        'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
                                        'EMAIL_DESCRIPTION'    => isset($notification_arr['description'])?$notification_arr['description']:''
                                       ];

                  $arr_mail_data                          = [];
                  $arr_mail_data['email_template_id']     = '76';
                  $arr_mail_data['arr_built_content']     = $arr_built_content;
                  //$arr_mail_data['template_subject_new']  = 'New User Request Category';
                  $arr_mail_data['arr_user']              = $arr_user;

                  $email_status  = $this->EmailService->send_mail($arr_mail_data);
              }


                /**********************Notification to admin END*********************************/

                  $role = Sentinel::findRoleBySlug($user_role_slug);

            $role->users()->attach($user);

            $arr_mail_data = $this->registration_activation_built_mail_data($email, $activation->code); 

                    $email_status  = $this->EmailService->send_mail($arr_mail_data);

                    if(\Mail::failures())
                    {
                        $arr_response['status']   = 'failure';
                        $arr_response['message']  = 'Error occured while send email of account verification.';
                        $arr_response['data']     = '';

                        return $arr_response;
                    }
                    else
                    {
                            if(isset($user_role_slug) && $user_role_slug == "Maker")
                            {
                                $arr_response['status']     = 'success';
                                $arr_response['message']    = 'You have successfully registered as a vendor. Please check your email account <b>'.$email.'</b> for the verification.';  
                                $arr_response['data']       = '';    
                                    
                            }
                            elseif(isset($user_role_slug) && $user_role_slug == "retailer")
                            {
                                $arr_response['status']     = 'success';
                                $arr_response['message']    = 'You have successfully registered as a retailer. Please check your email account <b>'.$email.'</b> for the verification.';   
                                $arr_response['data']       = '';     
                                    
                            }elseif(isset($user_role_slug) && $user_role_slug == "customer")
                            {
                                $arr_response['status']     = 'success';
                                $arr_response['message']    = 'You have successfully registered as a customer. Please check your email account '.$email.' for the verification.';    
                                $arr_response['data']       = '';     
                                    
                            }else if(isset($user_role_slug) && $user_role_slug == 'influencer'){
                               $arr_response['status']      = 'success';
                               $arr_response['message']     = 'You have successfully registered as a influencer. Please check your email account '.$email.' for the verification.';
                               $arr_response['data']        = '';   
                            }
                            else
                            {
	                            $arr_response['status']     = 'success';
	                            $arr_response['message']    = 'User has been register successfully! Please check your email account for email verification';
	                            $arr_response['data']       = '';   
                            }    

                        return $arr_response;
                    }

                }
                else
                {
                  $arr_response['status']     = 'failure';
                  $arr_response['message']    = 'Error occured while doing user activation';
                  $arr_response['data']       = '';  

                  return $arr_response;
                }
        }

      }
    }   
    else{
	        $arr_response['status']     = 'failure';
	        $arr_response['message']    = 'Not valid array input to service';
	        $arr_response['data']       = '';  

            return $arr_response;
    }
   }
   
   catch (Exception $e) {

				$response = [
	                'status'	=> 'failure',
	                'message'	=> $e->getMessage(),
	                'data'		=> ''
	            ];

	            return $response; 			
			}

	}


	 private function registration_activation_built_mail_data($email, $activation_code)
    {
      $user = $this->get_user_details($email);
        
      if($user)
      {
	    $arr_user = $user->toArray();
	    
	    $site_setting_obj = SiteSettingModel::first();
	        if($site_setting_obj)
	        {
	            $site_setting_arr = $site_setting_obj->toArray();            
	        }

	        $site_name      = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';

	        $activation_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/').'api/activation_complete/'.base64_encode($user->id).'/'.$activation_code.'">Verify Account</a><br/>' ;

	        $arr_site_setting  = get_site_settings(['site_name','website_url']);

	        $arr_built_content = ['USER_FNAME'     => $arr_user['first_name'],
	                              'ACTIVATION_URL' => $activation_url,
	                'APP_NAME'       => $arr_site_setting['site_name'],
	                'SITE_URL'       => $arr_site_setting['site_name']
	              ];

	        $arr_mail_data                      = [];
	        $arr_mail_data['email_template_id'] = '6';
	        $arr_mail_data['arr_built_content'] = $arr_built_content;
	        $arr_mail_data['arr_user']          = $arr_user;

	        return $arr_mail_data;
      }
      return false;
    }


    private function  get_user_details($email)
    {
        $credentials = ['email' => $email];
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        if($user)
        {
          return $user;
        }
        return false;
    }
}

?>