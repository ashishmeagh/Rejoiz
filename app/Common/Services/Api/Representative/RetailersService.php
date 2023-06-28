<?php

namespace App\Common\Services\Api\Representative;

use App\Models\UserModel;
use App\Models\RepresentativeModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Models\CountryModel;
use App\Models\SiteSettingModel;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Services\Api\Common\CommonService;


use App\Models\RetailerModel;
use \DB;
use \paginate;
use \Sentinel;

class RetailersService {

	public function __construct(
									UserModel $UserModel,
									RepresentativeModel $RepresentativeModel,
									CountryModel $CountryModel,
									GeneralService $GeneralService,
									RetailerModel $RetailerModel,
									EmailService $EmailService,
									SiteSettingModel $SiteSettingModel,
									RetailerRepresentativeMappingModel $RetailerRepresentativeMappingModel,
                  CommonService $CommonService
								) 
	{
		$this->RepresentativeModel 				= $RepresentativeModel;
		$this->UserModel 							    = $UserModel;
		$this->CountryModel 	    				= $CountryModel;
		$this->RetailerModel 					   	= $RetailerModel;
		$this->GeneralService 						= $GeneralService;
		$this->SiteSettingModel 					= $SiteSettingModel;
		$this->EmailService 						  = $EmailService;
    $this->CommonService              = $CommonService;

		$this->RetailerRepresentativeMappingModel 	= $RetailerRepresentativeMappingModel;
	}

	public function get_list($user_id, $perpage,$search) {

        try {

            $user_table         =  $this->UserModel->getTable();
            $prefix_user_table  = DB::getTablePrefix().$user_table;

            $retailer_representative_table          =  $this->RetailerRepresentativeMappingModel->getTable();
            $prefix_retailer_representative_table   = DB::getTablePrefix().$retailer_representative_table;

            $retailer_table         =  $this->RetailerModel->getTable();
            $prefix_retailer_table  = DB::getTablePrefix().$retailer_table;

            $obj_user = DB::table($user_table)
                            ->select(DB::raw($prefix_user_table.".id as id,".
                                                $prefix_user_table.".email as email, ".
                                                $prefix_user_table.".contact_no as contact_no, ".
                                                $prefix_user_table.".country_code, ".
                                                $prefix_user_table.".post_code, ".
                                                $prefix_user_table.".profile_image, ".
                                                $prefix_user_table.".status as status, ".
                                                $prefix_retailer_table.".store_name as store_name, ".
                                                $prefix_retailer_table.".years_in_business as years_in_business, ".
                                                $prefix_retailer_table.".annual_sales as annual_sales, ".

                                                "CONCAT(".$prefix_user_table.".first_name,' '," .$prefix_user_table.".last_name) as user_name"
                                            ))
                            ->leftJoin($retailer_representative_table,$retailer_representative_table.'.retailer_id','=',$user_table.'.id')
                            ->leftJoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$user_table.'.id')
                            ->where($retailer_representative_table.'.representative_id',$user_id)
                            ->orderBy('id', 'desc');

            /* ---------------- Filtering Logic ----------------------------------*/  
            
                if(isset($search) && $search != "" ) {

                    $obj_user = $obj_user->whereRaw(
                                                    "(  `".$prefix_user_table."`.`first_name` LIKE '%".$search."%' OR
                                                        `".$prefix_user_table."`.`last_name` LIKE '%".$search."%' OR
                                                        `".$prefix_user_table."`.`email` LIKE '%".$search."%' OR
                                                        `".$prefix_user_table."`.`contact_no` LIKE '%".$search."%' OR
                                                        `".$prefix_retailer_table."`.`store_name` LIKE '%".$search."%' )"
                                                   );
                }   

            /* ---------------- Filtering Logic ----------------------------------*/  

            $obj_user = $obj_user->paginate($perpage);      // Pagination

            $user_arr = $obj_user->toArray();

            $user_data['data'] = [];

            if (isset($user_arr['data']) && !empty($user_arr['data'])) {
                
                foreach ($user_arr['data'] as $key => $value) {

                    $user_data['data'][$key]["id"]                  = isset($value->id) ? $value->id : '';
                    $user_data['data'][$key]["email"]               = isset($value->email) ? $value->email : '';
                    $user_data['data'][$key]["contact_no"]          = isset($value->contact_no) ? $value->contact_no : '';
                    $user_data['data'][$key]["profile_image"]       = imagePath($value->profile_image, 'user', 0);
                    $user_data['data'][$key]["country_code"]        = isset($value->country_code) ? $value->country_code : '';
                    $user_data['data'][$key]["post_code"]           = isset($value->post_code) ? $value->post_code : '';
                    $user_data['data'][$key]["status"]              = isset($value->status) ? $value->status : '';
                    $user_data['data'][$key]["store_name"]          = isset($value->store_name) ? $value->store_name : '';
                    $user_data['data'][$key]["years_in_business"]   = isset($value->years_in_business) ? $value->years_in_business : '';
                    $user_data['data'][$key]["annual_sales"]        = isset($value->annual_sales) ? $value->annual_sales : '';
                    $user_data['data'][$key]["user_name"]           = isset($value->user_name) ? $value->user_name : '';
                }
            }

            $user_data['pagination']["current_page"]    = $user_arr['current_page'];
            $user_data['pagination']["first_page_url"]  = $user_arr['first_page_url'];
            $user_data['pagination']["from"]            = $user_arr['from'];
            $user_data['pagination']["last_page"]       = $user_arr['last_page'];
            $user_data['pagination']["last_page_url"]   = $user_arr['last_page_url'];
            $user_data['pagination']["next_page_url"]   = $user_arr['next_page_url'];
            $user_data['pagination']["path"]            = $user_arr['path'];
            $user_data['pagination']["per_page"]        = $user_arr['per_page'];
            $user_data['pagination']["prev_page_url"]   = $user_arr['prev_page_url'];
            $user_data['pagination']["to"]              = $user_arr['to'];
            $user_data['pagination']["total"]           = $user_arr['total'];

            $user_data['data']                  = $this->CommonService->get_status_display_names($user_data['data'],'listing'); 

            $response                           = [];
            $response['status']                 = 'success';
            $response['message']                = 'Customers list get successfully.';
            $response['data']                   = $user_data;
            
            return $response;
            
        } catch(Exception $e) {
      
            $response['status']     = 'failure';
            $response['message']    = $e->getMessage();
            $response['data']       = '';

            return $response;
        }
    }

    public function create ($data = []) {

    	try {


            DB::beginTransaction();

            $users = $data['auth_user'];

            $loggedin_userId = $users->id;

            /* Check for email duplication */
            $does_exists =  $this->UserModel  
            						->where('email',$data['email'])
            						->count();	
            if($does_exists) {

                $response['status']      = "failure";
                $response['message']     = "Email id already exist with system ,please try different one.";
                $response['data']        = [];

                return $response;
            }

            $arr_user_data['first_name']		= isset($data['first_name']) ? $data['first_name'] : '';
            $arr_user_data['last_name']			= isset($data['last_name']) ? $data['last_name'] : '';
            $arr_user_data['country_id']		= isset($data['country_code']) ? $data['country_code'] : '';
            $arr_user_data['email']				= isset($data['email']) ? $data['email'] : '';
            $arr_user_data['contact_no']		= isset($data['contact_no']) ? $data['contact_no'] : '';
            $arr_user_data['zip_code']			= isset($data['zip_code']) ? $data['zip_code'] : '';
            $arr_user_data['buying_status']		= isset($data['buying_status']) ? $data['buying_status'] : '';
            $arr_user_data['country_code']		= isset($data['hid_country_code']) ? $data['hid_country_code'] : '';
            $arr_user_data['store_name'] 		= isset($data['store_name']) ? $data['store_name'] : '';
            $arr_user_data['years_in_business']	= isset($data['years_in_business']) ? $data['years_in_business'] : '';
            $arr_user_data['annual_sales']		= isset($data['annual_sales']) ? $data['annual_sales'] : '';
            $arr_user_data['store_website']		= isset($data['store_website']) ? $data['store_website'] : '';

            $user_password = str_random(6);

            $user = Sentinel::registerAndActivate([

	                    'email' 	=> $arr_user_data['email'],
	                    'password' 	=> $user_password
	                ]);

            if($user) {

                $role = Sentinel::findRoleBySlug('retailer');

                $role->users()->attach($user);
            }

            $user->first_name       = $arr_user_data['first_name'];
            $user->last_name        = $arr_user_data['last_name'];
            $user->country_id       = $arr_user_data['country_id'];
            $user->email            = $arr_user_data['email'];
            $user->post_code        = $arr_user_data['zip_code'];
            $user->buying_status    = $arr_user_data['buying_status'];
            $user->country_code     = $arr_user_data['country_code'];
            $user->store_name       = $arr_user_data['store_name'];
            $user->contact_no       = $arr_user_data['contact_no'];
            
            $user->save();

            $retailer_representative_table = RetailerRepresentativeMappingModel::firstOrNew(['retailer_id' => $user->id]);

            $retailer_representative_table->representative_id = $loggedin_userId;

            $retailer_representative_table->save();

            $retailer_table = RetailerModel::firstOrNew(['user_id' => $user->id]);

            $retailer_table->years_in_business  = $arr_user_data['years_in_business'];
            $retailer_table->store_name         = $arr_user_data['store_name'];
            $retailer_table->annual_sales       = $arr_user_data['annual_sales'];
            $retailer_table->country            = $arr_user_data['country_code'];

            $retailer_table->save();

            $arr_mail_data = $this->built_mail_data($arr_user_data['email'],$user_password); 

			$email_status  = $this->EmailService->send_mail($arr_mail_data);

           /* try {    

				$email_status  = $this->EmailService->send_mail($arr_mail_data);

	        } catch (\Exception $e) {

				DB::rollback();

				$response['status']   				= 'failure';
				$response['message']				= $e->getMessage();
				$response['data']     				= '';

				return $response;
	        }*/

            DB::commit();

            /*********** Send notification to Admin ***********/

	            $notification_arr                 = [];
	            $notification_arr['from_user_id'] = $loggedin_userId;
	            $notification_arr['to_user_id']   = get_admin_id();
	            $notification_arr['description']  = 'A New Customer '.$arr_user_data['first_name'].' '.$arr_user_data['last_name'].' has been registered by Representative ' . $users->first_name.' ' .$users->last_name ;

	            $notification_arr['title']        = 'New User Registration';
	            $notification_arr['type']         = 'admin';  
	            $notification_arr['status']       = '0';  
	            $notification_arr['link']         = url('/').'/admin/retailer/view/'.base64_encode($user->id);  
	               
                $this->GeneralService->save_notification($notification_arr);            

            /*********** Send notification to Admin ***********/

            $response['status']                 = 'success'; 
            $response['message']                = "Customers has been created.";
            $response['data']                   = '';

            return $response;         
        }
        catch(Exception $e) {

            DB::rollback();
      
            $response['status']     = 'failure';
            $response['message'] 	= $e->getMessage();
            $response['data'] 		= '';

            return $response;
        }
    }

    public function built_mail_data($email,$user_password) {

        $credentials = ['email' => $email];

        $user = Sentinel::findByCredentials($credentials);
    
        if($user) {

            $arr_user = $user->toArray();

            $reminder_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.url('/login').'"> Login </a>.<br/>' ;

            $site_setting_obj = SiteSettingModel::first();

            if($site_setting_obj) {

                $site_setting_arr = $site_setting_obj->toArray();            
            }

            $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'; 

            $arr_built_content = [
            						'FIRST_NAME'   => $arr_user['first_name'],
									'EMAIL'        => $arr_user['email'],
									'PASSWORD'     => $user_password,
									'LOGIN_URL'    => $reminder_url,
									'USER_ROLE'    => "Retailer",
									'APP_URL'      => $site_name,
                                    'project_name' => $site_name
                              	];

            /*$arr_built_subject =  [
                                      'project_name'      => $site_name
                                  ];    */
                                                                          
            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '34';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['arr_user']          = $arr_user;


            return $arr_mail_data;
        }
        return FALSE;
    }

    public function change_status($data = []) {

		$status_update = $this->UserModel
								->where('id',$data['retailer_id'])
								->update(['status' => $data['status']]);
		if ($status_update) {

			$response['status']     = 'success';
            $response['message'] 	= 'Customer status changed successfully.';
            $response['data'] 		= '';

            return $response;
		}
		else {

			$response['status']     = 'failure';
            $response['message'] 	= 'Something went wrong, please try again.';
            $response['data'] 		= '';

            return $response;
		}
    }

    public function name_list($user_id="") {

        try {


            if($user_id!="") {

                $retailer_arr   =  $this->RetailerRepresentativeMappingModel
                                        ->with(['retailer_details','getRetailerDetails'])
                                        ->whereHas('retailer_details',function($q) {

                                            $q->select('store_name');
                                            $q->where('store_name','!=','');
                                            $q->orderBy('store_name','ASC');
                                        })
                                        ->whereHas('getRetailerDetails',function($q) {

                                            $q->where('status',1);
                                            $q->where('is_approved',1);
                                        })
                                        ->where('representative_id',$user_id)
                                        ->orderBy('id', 'desc')
                                        ->get()
                                        ->toArray();

                if(isset($retailer_arr) && !empty($retailer_arr)) {

                    foreach($retailer_arr as $key => $val) {

                        $data[$key]['id']   = $val['retailer_id']; 
                        $data[$key]['name'] = $val['retailer_details']['store_name']; 
                    }
                }

               $response['status']     = 'success';
               $response['message']    = 'Customer list get successfully.';
               $response['data']       = isset($data)?$data:'';

               return $response;                    
            }                
            
        } catch (Exception $e) {

            $response['status']     = 'failure';
            $response['message']    = $e->getMessage();
            $response['data']       = '';

            return $response;
        } 
    }

    public function details($user_id="") {

        try {

                $customer_arr = [];
                $customer_obj = $this->UserModel
                                        ->where('id',$user_id)
                                        ->with(['address_details'])
                                        ->with(['retailer_details'])
                                        ->where('status',1)
                                        ->first();
                if($customer_obj) {

                    $customer_arr  = $customer_obj->toArray();
                }

                if(count($customer_arr)>0) {

                    $data['first_name']            = isset($customer_arr['first_name'])?$customer_arr['first_name']:'';
                    $data['last_name']             = isset($customer_arr['last_name'])?$customer_arr['last_name']:'';
                    $data['email_address']         = isset($customer_arr['email'])?$customer_arr['email']:'';
                    $data['ship_street_address']   = isset($customer_arr['retailer_details']['shipping_addr'])?$customer_arr['retailer_details']['shipping_addr']:'';
                    $data['ship_suit_apt']         = isset($customer_arr['retailer_details']['shipping_suit_apt'])?$customer_arr['retailer_details']['shipping_suit_apt']:'';
                    $data['ship_state']            = isset($customer_arr['retailer_details']['shipping_state'])?$customer_arr['retailer_details']['shipping_state']:'';
                    $data['ship_city']             = isset($customer_arr['retailer_details']['shipping_city'])?$customer_arr['retailer_details']['shipping_city']:'';
                    $data['ship_country']           = isset($customer_arr['retailer_details']['shipping_country'])?(int)$customer_arr['retailer_details']['shipping_country']:'';
                    $data['ship_mobile_no']         = isset($customer_arr['retailer_details']['ship_contact_no'])?$customer_arr['retailer_details']['ship_contact_no']:'';
                    $data['ship_zip_code']          = isset($customer_arr['retailer_details']['shipping_zip_postal_code'])?$customer_arr['retailer_details']['shipping_zip_postal_code']:'';
                    $data['bill_street_address']    = isset($customer_arr['retailer_details']['billing_address'])?$customer_arr['retailer_details']['billing_address']:'';
                    $data['bill_suit_apt']          = isset($customer_arr['retailer_details']['billing_suit_apt'])?$customer_arr['retailer_details']['billing_suit_apt']:'';
                    $data['bill_state']             = isset($customer_arr['retailer_details']['billing_state'])?$customer_arr['retailer_details']['billing_state']:'';
                    $data['bill_city']              = isset($customer_arr['retailer_details']['billing_city'])?$customer_arr['retailer_details']['billing_city']:'';
                    $data['bill_country']           = isset($customer_arr['retailer_details']['billing_country'])?(int)$customer_arr['retailer_details']['billing_country']:'';
                    $data['bill_mobile_no']         = isset($customer_arr['retailer_details']['bill_contact_no'])?$customer_arr['retailer_details']['bill_contact_no']:'';
                    $data['bill_zip_code']          = isset($customer_arr['retailer_details']['billing_zip_postal_code'])?$customer_arr['retailer_details']['billing_zip_postal_code']:'';

                   // dd($data);

                    $response['status']     = 'success';
                    $response['message']    = 'Customer details get successfully.';
                    $response['data']       = isset($data)?$data:'';

                    return $response;
                }
                else {

                    $response['status']     = 'failure';
                    $response['message']    = 'Something went wrong.';
                    $response['data']       = '';

                    return $response;
                }
        }
        catch (Exception $e) {

            $response['status']     = 'failure';
            $response['message']    = $e->getMessage();
            $response['data']       = '';

            return $response;
        }
    }
}

?>