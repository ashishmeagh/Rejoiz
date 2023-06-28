<?php

namespace App\Common\Services;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\MakerModel;
use App\Models\AddressModel;
use App\Models\RetailerModel;
use App\Models\CategoryTranslationModel;
use App\Models\CategoryModel;
use App\Models\RepresentativeLeadsModel;


use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Models\SiteSettingModel;


use \Session;
use \Sentinel;
use \Activation;
use \DB;

class UserService 
{
	public function __construct(EmailService $EmailService,
								UserModel $UserModel,
								RoleModel $RoleModel,
								RoleUsersModel $RoleUsersModel,
								AddressModel $AddressModel,
								MakerModel $maker,
								RetailerModel $RetailerModel,
								CategoryTranslationModel $CategoryTranslationModel,
								CategoryModel $CategoryModel,
								RepresentativeLeadsModel $RepresentativeLeadsModel,
								GeneralService $GeneralService,
								SiteSettingModel $SiteSettingModel){
		$this->EmailService   = $EmailService;
		$this->UserModel      = $UserModel;
		$this->RoleModel      = $RoleModel;
		$this->RoleUsersModel = $RoleUsersModel;
		$this->MakerModel     = $maker;
		$this->RetailerModel  = $RetailerModel;
		$this->CategoryTranslationModel  = $CategoryTranslationModel;
		$this->CategoryModel  = $CategoryModel;
		$this->AddressModel   = $AddressModel;
		$this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
		$this->GeneralService = $GeneralService;
		$this->admin_url_path = url(config('app.project.admin_panel_slug'));
		$this->SiteSettingModel	= $SiteSettingModel;
		$this->site_setting_obj = $this->SiteSettingModel->first();
        
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
	}

	public function user_registration($arr_data = [])
	{   

		$arr_response = [];
		
		if(isset($arr_data) && count($arr_data)>0){


			$email            = isset($arr_data['email'])?$arr_data['email']:'';
			$password         = isset($arr_data['password'])?$arr_data['password']:'';
			$confirm_password = isset($arr_data['confirm_password'])?$arr_data['confirm_password']:'';

			$first_name     = isset($arr_data['first_name'])?$arr_data['first_name']:'';
			$last_name      = isset($arr_data['last_name'])?$arr_data['last_name']:'';
			//$country_code   = isset($arr_data['country_code'])?$arr_data['country_code']:'';
			$country_code   = isset($arr_data['hid_country_code'])?$arr_data['hid_country_code']:'';
			$tax_id         = isset($arr_data['tax_id'])?$arr_data['tax_id']:'';
			$contact_no     = isset($arr_data['contact_no'])?$arr_data['contact_no']:'';
			$influencer_code     = isset($arr_data['influencer_code'])?$arr_data['influencer_code']:'';	
			
			if($contact_no && $country_code){
				$contact_no = str_replace($country_code,"",$contact_no);
			}

			$user_role_slug = isset($arr_data['role'])?$arr_data['role']:'';

			$address        = isset($arr_data['address'])?$arr_data['address']:'';		
			//$post_code      = isset($arr_data['post_code'])?$arr_data['post_code']:'';		
			$buying_status  = isset($arr_data['buying_status'])?$arr_data['buying_status']:'';		
			$store_name     = isset($arr_data['store_name'])?$arr_data['store_name']:'';			
			$dummy_store_name     = isset($arr_data['dummy_store_name'])?$arr_data['dummy_store_name']:'';			
			$store_website  = isset($arr_data['store_website'])?$arr_data['store_website']:'';			
			$country_id     = isset($arr_data['country_id'])?$arr_data['country_id']:'';

			$post_code      = isset($arr_data['zip_code'])?$arr_data['zip_code']:'';

			$years_in_business = isset($arr_data['years_in_business'])?$arr_data['years_in_business']:'';
			
			$annual_sales = isset($arr_data['Annual_Sales'])?$arr_data['Annual_Sales']:'';
			$store_description = isset($arr_data['store_description'])?$arr_data['store_description']:'';
		

            /*Check provided first name is not blank or invalid*/
            if(!isset($first_name) || (isset($first_name) && $first_name == '') || (!preg_match("/^[a-zA-Z]+$/",$first_name)))
			{
			  
			   $arr_response['status'] = 'ERROR';
			   $arr_response['msg']    = 'Provided first name should not be blank or invalid.';

			   return $arr_response;
			} 

			/*Check provided last name is not blank or invalid*/
            if(!isset($last_name) || (isset($last_name) && $last_name == '') || (!preg_match("/^[a-zA-Z]+$/",$last_name)))
			{
			   $arr_response['status'] = 'ERROR';
			   $arr_response['msg']    = 'Provided last name should not be blank or invalid.';

			   return $arr_response;
			} 


			
			/*Check provided email is natcasesort(array)ot blank or invaild*/	
			if(!isset($email) || (isset($email) && $email == '')){
				$arr_response['status'] = 'ERROR';
				$arr_response['msg']    = 'Provided email should not be blank or in valid';

				return $arr_response;
			}	
		
			/*Check provided password is not blank or invaild*/
			if(!isset($password) || (isset($password) && $password == '')){
				$arr_response['status'] = 'ERROR';
				$arr_response['msg']    = 'Provided password should not be blank or in valid';

				return $arr_response;
			}

				/*Check provided password is not blank or invaild*/
			if(!isset($confirm_password) || (isset($confirm_password) && $confirm_password == '') || $confirm_password!=$password){
				$arr_response['status'] = 'ERROR';
				$arr_response['msg']    = 'Confirm password should be same as password.';

				return $arr_response;
			}

			/*Check provided mobile no is not accepting digits*/
			if(!isset($contact_no) || (isset($contact_no) && $contact_no == '') || (!is_numeric($contact_no)))
			{
			   $arr_response['status'] = 'ERROR';
			   $arr_response['msg']    = 'Provided contact no should not be blank or invalid.';

			   return $arr_response;
			}


			$tax_id = isset($arr_data['tax_id'])?$arr_data['tax_id']:'';

			// if($user_role_slug == 'Maker' || $user_role_slug == 'retailer')
			if($user_role_slug == 'retailer')
			{
				$is_tax_id_exists = $this->UserModel->where('tax_id',$tax_id)->count();
			    // dd($is_tax_id_exists);
			    if($is_tax_id_exists > 0)
			    {
			       $arr_response['status'] = 'ERROR';
				   $arr_response['msg']    = 'Tax id already exists';
				   return $arr_response;
			    }
			}
		    

			if($user_role_slug == 'Maker')
			{
				 $company_name = isset($arr_data['company_name'])?$arr_data['company_name']:'';

				 $is_company_exists = $this->MakerModel->where('company_name',$company_name)->count();

			     if($is_company_exists > 0)
			     {
			       $arr_response['status'] = 'ERROR';
				   $arr_response['msg']    = 'Company name already exists';
				   return $arr_response;
			     }

			     /* Check real company name already exists*/
			     $real_company_name = isset($arr_data['real_company_name'])?$arr_data['real_company_name']:'';

				 $is_real_company_exists = $this->MakerModel->where('real_company_name',$real_company_name)->count();

			     if($is_real_company_exists > 0)
			     {
			       $arr_response['status'] = 'ERROR';
				   $arr_response['msg']    = 'Real company name already exists';
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
			       $arr_response['status'] = 'ERROR';
				   $arr_response['msg']    = 'Other category name already exists';
				   return $arr_response;
			     }
			}

			/*Check provided role is not blank or invaild*/
			if(Sentinel::findRoleBySlug($user_role_slug)==null){
				
				$arr_response['status'] = 'ERROR';
				$arr_response['msg']    = 'Provided role is not valid';

				return $arr_response;
			}
			else
			{
				/*check email duplication*/
				if(Sentinel::findByCredentials(['email'=>$email]) != null)
				{
					$arr_response['status'] = 'ERROR';
					$arr_response['msg']    = 'Provided email already exists in system, you may try different email';

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
				
				$credentials['address']  	   = $address;

				$credentials['buying_status']  = $buying_status;
				$credentials['store_name']     = $store_name;
				$credentials['dummy_store_name']     = $dummy_store_name;
				$credentials['store_website']  = $store_website;
				$credentials['influencer_code']  = $influencer_code;

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
				
				if($user)
				{
					//Store maker details
					$arr_maker = [];

					if($user_role_slug == 'Maker')							
					{
						$arr_maker['user_id']     = $user->id;
						// $arr_maker['brand_name']  = isset($arr_data['brand_name'])?$arr_data['brand_name']:'';
						$arr_maker['company_name']  		= isset($arr_data['company_name'])?$arr_data['company_name']:'';
						$arr_maker['real_company_name']  	= isset($arr_data['real_company_name'])?$arr_data['real_company_name']:'';
						$arr_maker['website_url'] 			= isset($arr_data['website_url'])?$arr_data['website_url']:'';
						$arr_maker['primary_category_id'] 	= isset($arr_data['primary_category_id'])?$arr_data['primary_category_id']:'';
						$arr_maker['primary_category_name'] = isset($arr_data['other_category_name'])?$arr_data['other_category_name']:'';
						$arr_maker['no_of_stores'] 			= isset($arr_data['no_of_stores'])?$arr_data['no_of_stores']:'';
						$arr_maker['insta_url'] 			= isset($arr_data['insta_url'])?$arr_data['insta_url']:'';


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
						$arr_retailer['user_id']           = $user->id;
						
						$arr_retailer['years_in_business'] = isset($arr_data['years_in_business'])?$arr_data['years_in_business']:'';
						$arr_retailer['annual_sales']      = isset($arr_data['Annual_Sales'])?$arr_data['Annual_Sales']:'';
						$arr_retailer['store_description'] = isset($arr_data['store_description'])?$arr_data['store_description']:'';

						$arr_retailer['store_name'] = isset($store_name)?$store_name:'';
						$arr_retailer['dummy_store_name'] = isset($dummy_store_name)?$dummy_store_name:'';


						$arr_retailer['store_website']     = isset($store_website)?$store_website:'';

						$arr_retailer['store_description'] = isset($store_description)?$store_description:'';
						
						$arr_retailer['category']          = isset($arr_data['category'])?$arr_data['category']:'';

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
		                $user_view_href   	= '';
		                if($user_role_slug=="Maker")
		                {
		                	$user_type="Vendor";

                            $user_view_href   = $this->admin_url_path.'/vendor/view/'.base64_encode($user->id);
		                }
		                else if($user_role_slug=="retailer")
		                {
		                	$user_type='Customer';
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
			            $notification_arr['description']  = 'A new vendor <b>'.$first_name.' '.$last_name.'</b> has requested for category named as <b>'.$notif_category_name.'</b>. Please update admin approval status	& category status.';
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
		                    $arr_response['status']   = 'ERROR';
		                    $arr_response['msg']      = 'Error occured while send email of account verification.';

		                    return $arr_response;
		                }
		                else
		                {
                            if(isset($user_role_slug) && $user_role_slug == "Maker")
                            {
                                $arr_response['status'] = 'SUCCESS';
                                $arr_response['msg']    = 'You have successfully registered as a vendor. Please check your email account <b>'.$email.'</b> for the verification.';     
                                    
                            }
                            elseif(isset($user_role_slug) && $user_role_slug == "retailer")
                            {
                                $arr_response['status'] = 'SUCCESS';
                                $arr_response['msg']    = 'You have successfully registered as a customer. Please check your email account <b>'.$email.'</b> for the verification.';    
                                    
                            }elseif(isset($user_role_slug) && $user_role_slug == "customer")
                            {
                                $arr_response['status'] = 'SUCCESS';
                                $arr_response['msg']    = 'You have successfully registered as a customer. Please check your email account '.$email.' for the verification.';    
                                    
                            }else if(isset($user_role_slug) && $user_role_slug == 'influencer'){
                            	$arr_response['status'] = 'SUCCESS';
                                $arr_response['msg']    = 'You have successfully registered as a influencer. Please check your email account '.$email.' for the verification.';
                            }
                            else
                            {
    		                    $arr_response['status'] = 'SUCCESS';
    		                    $arr_response['msg']    = 'User has been register successfully! Please check your email account for email verification';
                            }    

		                    return $arr_response;
		                }

          			}
          			else
          			{
          				$arr_response['status'] = 'ERROR';
	                    $arr_response['msg']    = 'Error occured while doing user activation';

	                    return $arr_response;
          			}
				}

			}
		}		
		else{
				$arr_response['status'] = 'ERROR';
				$arr_response['msg']    = 'Not valid array input to service';

				return $arr_response;
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

        $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';

      


        $activation_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/').'/activation_complete/'.base64_encode($user->id).'/'.$activation_code.'">Verify Account</a><br/>' ;

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

    /*Get user data in datatable format*/
    public function get_datatable_records($role_slug = false,$arr_search_column = [],$module_url_path='')
    {
        $obj_user     = $this->get_user_data($role_slug,$arr_search_column);

        $json_result     = \Datatables::of($obj_user);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->id);
                            })
        					->editColumn('build_kyc_status',function($data)
                            {
                                $build_kyc_status ='';
                                if($data->kyc_status == '1')
                                {   
                                    $build_kyc_status = built_label_html('success','Approved');
                                }
                                elseif($data->kyc_status == '2')
                                {   
                                    $build_kyc_status = built_label_html('info','Not Complete');
                                }
                                elseif($data->kyc_status == '3')
                                {   
                                    $build_kyc_status = built_label_html('warning','In-Progress');
                                }
                                elseif($data->kyc_status == '4')
                                {   
                                    $build_kyc_status = built_label_html('danger','Rejected');
                                }
                                return $build_kyc_status;
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
                                    $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                return $build_status_btn;
                            })    
                            ->editColumn('build_action_btn',function($data) use($module_url_path)
                            {   
                                $view_href =  $module_url_path.'/view/'.base64_encode($data->id);
                                $build_view_action = '<a class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'" title="View"><i class="ti-eye" ></i></a>';

                                return $build_action = $build_view_action;
                            })
                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }

    /*Get user list from user role*/
    public function get_user_data($role_slug = false,$arr_search_column = [])
    {
    	$user_table =  $this->UserModel->getTable();
		$prefix_user_table = DB::getTablePrefix().$user_table;

		$role_table =  $this->RoleModel->getTable();
		$prefix_role_table = DB::getTablePrefix().$role_table;

		$role_user_table =  $this->RoleUsersModel->getTable();
		$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

		$obj_user = DB::table($user_table)
						->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".wallet_address as wallet_address, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".kyc_status as kyc_status, ".
                                     $role_table.".slug as slug, ".
                                     $role_table.".name as name, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
						->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
						->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
						->where($role_table.'.slug','=',$role_slug)
						->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC');

        /* ---------------- Filtering Logic ----------------------------------*/  
        
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

        if(isset($arr_search_column['q_wallet_address']) && $arr_search_column['q_wallet_address']!="")
        {
            $search_term      = $arr_search_column['q_wallet_address'];
            $obj_user = $obj_user->where($user_table.'.wallet_address','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_kyc_status']) && $arr_search_column['q_kyc_status']!="")
        {
            $search_term      = $arr_search_column['q_kyc_status'];
            $obj_user = $obj_user->where($user_table.'.kyc_status','=', $search_term);
        }

		return $obj_user;
    } 


    /*get user details with all details using user id and role*/
    public function get_user_information($user_id=false,$role_slug=false)
    {  
    	$arr_user = [];
    	if($user_id!=false && ($role_slug!=false || $role_slug!="")){

    		$obj_user = UserModel::where('id',$user_id)

    						/*->with(['country_details','state_details','city_details','sales_manager_details'])->first();*/

    						      ->with(['country_details','state_details','city_details','sales_manager_details.area_details','retailer_store_details','retailer_details','customer_details'])
    						      
    						      ->first();
    		if($obj_user){
					$arr_user = $obj_user->toArray();
					
    		}
    	}

    	return $arr_user;
    }

    public function get_user_info($user_id=false,$role_slug=false)
    {   
    	$arr_user = [];
    	
 		$obj_user = $this->AddressModel->with('user_details')
 					->where('user_id',$user_id)->first();	

 	    if($obj_user)
 	    {
 		  $arr_user = $obj_user->toArray();			
 	    }
  	
    	return $arr_user;
    }

    public function get_user_count($role_slug = false)
    {
    	$tot_user = 0;
    	if($role_slug != false){
    		$tot_user = UserModel::whereHas('role_details.role_name',function($q) use($role_slug){
    									return $q->where('slug',$role_slug);
    								})
    							   ->count();
    	}

    	return $tot_user;
    }




   
    
}