<?php

namespace App\Common\Services\Api\Representative;

use App\Models\UserModel;
use App\Models\RepresentativeModel;
use App\Models\CountryModel;
use App\Common\Services\GeneralService;
use App\Common\Services\Api\Common\CommonService;


use Sentinel;

class AccountSettingsService {

	public function __construct(
									UserModel $UserModel,
									RepresentativeModel $RepresentativeModel,
									CountryModel $CountryModel,
									GeneralService $GeneralService,
									CommonService $CommonService
								) 
	{
		$this->RepresentativeModel 	= $RepresentativeModel;
		$this->UserModel 			= $UserModel;
		$this->CountryModel 	    = $CountryModel;
		$this->GeneralService 		= $GeneralService;
		$this->CommonService        = $CommonService;
		$this->profile_image      	= base_path().'/storage/app/';
	}

	public function get($user_id=null) {

		try {

		    $user_data 	= [];

			if($user_id) {

			    $obj_data   =  $this->RepresentativeModel
			                        ->select('user_id','description','sales_manager_id','area_id','category_id') 
			                        ->with(['get_area_details' => function($area_details) {

										$area_details->select('id','area_name');
    								}])
			                        ->with(['sales_manager_details' => function($sales_manager_details) {

	    								$sales_manager_details->select('user_id');
	    								$sales_manager_details->with(['get_user_data' => function ($get_user_data) {

											$get_user_data->select('first_name','last_name','id');
	    								}]);
			    					}])
			    					->with(['get_user_details' => function($user_details) {

	    								$user_details->select('first_name','last_name','email','profile_image','post_code','country_id','id','status');
	    								$user_details->with(['country_details' => function ($country_details) {

											$country_details->select('id','name');
	    								}]);
									}])
									->where('user_id',$user_id)->first();
	           if($obj_data) {

	             $arr_data 	= $obj_data->toArray();
	           }  

	           if(isset($arr_data) && !empty($arr_data)) {


	           	   $user_data['first_name']     						= $arr_data['get_user_details']['first_name'];
	           	   $user_data['last_name']      						= $arr_data['get_user_details']['last_name'];
	           	   $user_data['email']          						= $arr_data['get_user_details']['email'];
	           	   $user_data['profile_image']  						= imagePath($arr_data['get_user_details']['profile_image'], 'user', 0);
	           	   $user_data['post_code']      						= $arr_data['get_user_details']['post_code'];
	           	   $user_data['country_id']     						= $arr_data['get_user_details']['country_id'];
	           	   $user_data['country_name']   						= $arr_data['get_user_details']['country_details']['name'];
	           	   $user_data['description']    						= $arr_data['description'];
	           	   $user_data['area_name']      						= $arr_data['get_area_details']['area_name'];
	           	   $user_data['status']     						    = $arr_data['get_user_details']['status'];
	           	   $user_data['sales_manager_details']['first_name'] 	= $arr_data['sales_manager_details']['get_user_data']['first_name'];
	           	   $user_data['sales_manager_details']['last_name']  	= $arr_data['sales_manager_details']['get_user_data']['last_name'];
	           }

                if(isset($user_data) && !empty($user_data)) {

                	$user_data   = $this->CommonService->get_status_display_names($user_data,'details'); 


	           		$response['status']         = 'success';
					$response['message']        = 'Account settings get successfully.';
					$response['data'] 		    =  isset($user_data)?$user_data:[];

		      		return $response;
	      	    }

	      	    else {

	      	    	$response['status']         = 'failure';
					$response['message']        = 'Something went wrong, while getting account settings.';
					$response['data'] 		    = '';

					return $response;

	      	    }
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

	public function update($form_data=null,$user_id=null,$request=null)
	{

		try {


			/********************************************** Image Upload **********************************************/
				
		       /* if (isset($form_data['profile_image']) && !empty($form_data['profile_image'])) {

		            $filename_name 	= md5(time().uniqid()).".jpg"; 
		            $image_data 	= explode( ',', $form_data['profile_image'] ); 
		            
		            $trim    = isset($image_data[1])?$image_data[1]:$image_data[0];

		            $decoded = base64_decode($trim);   		//remove base64
		            
		            $true = file_put_contents($this->profile_image.'profile_image/'.$filename_name ,$decoded);

		            if($true) {

		            	if(isset($form_data['old_image']) && $form_data['old_image']!="") {

		                   $old_img_path   = $this->profile_image.'profile_image/'.$form_data['old_image'];

		                   $this->GeneralService->unlink_old_image($old_img_path);
		                }

		            	$arr_data['profile_image'] = 'profile_image/'.$filename_name;
		            }	
		            else {

	            		$response['status']    = 'failure';
			            $response['message']   = 'Error while saving image.';
			            $response['data']      = '';

		            	return $response;
		            }
		    	}*/


		    	if(isset($form_data['profile_image']) && $form_data['profile_image'] != '') {


		            $profile_image = $form_data['profile_image'];

	                $file_extension = strtolower($profile_image->getClientOriginalExtension());  // Validation for product image

	                if(!in_array($file_extension,['jpg','png','jpeg'])) {

	                    $response['status']     = 'failure';
			            $response['message']    = 'Please select valid file type.';
			            $response['data']       = '';

			            return $response; 
	                }

		            if($profile_image != null) {

		                $profile_file_path = $profile_image->store('profile_image');

		                if(isset($form_data['old_image']) && $form_data['old_image']!="") {

		                   $old_img_path   = $this->profile_image.$form_data['old_image'];

		                   $this->GeneralService->unlink_old_image($old_img_path); // Unlink old image
		                }
		            }

		            $arr_data['profile_image']= $profile_file_path;
		        }

			/********************************************** Image Upload **********************************************/

	        $arr_data['first_name']   = isset($form_data['first_name'])?$form_data['first_name']:'';    
	        $arr_data['last_name']    = isset($form_data['last_name'])?$form_data['last_name']:'';
	        $arr_data['email']        = isset($form_data['email'])?$form_data['email']:'';
	        $arr_data['post_code']    = isset($form_data['post_code'])?$form_data['post_code']:'';
	        $arr_data['country_id']   = isset($form_data['country_id'])?$form_data['country_id']:'';

	        $description = isset($form_data['description'])?$form_data['description']:'';

	        $obj_data    = $this->UserModel->where('id',$user_id)->update($arr_data); 

	        if($obj_data) {

	           	$update_representative = $this->RepresentativeModel->where('user_id',$user_id)->update(['description'=>$description]);

	           	$response['status']    = 'success';
	            $response['message']   = 'Account settings has been updated.';
	            $response['data']      = '';

	            return $response;
	        }
	        
	        else {

	           	$response['status']     = 'failure';
	            $response['message']    = 'Something went wrong, while updating account settings.';
	            $response['data']       = '';

	            return $response; 
	        }
		}
		catch(Exception $e) {

            $response = [
				'status'  => 'failure',
				'message' => $e->getMessage(),
				'data'    => ''
			];

      		return $response; 
		}
	}

	public function change_password($form_data=null,$user=null)
	{
       try
       {
           $credentials             = [];
	       $credentials['password'] = $form_data['current_password'];

	        if (Sentinel::validateCredentials($user,$credentials)) {

				$new_credentials      = [];
				$new_credentials['password'] = $form_data['new_password'];

	          	if(Sentinel::update($user,$new_credentials)) {

		          	$response['status']    = 'success';
			        $response['message']   = 'Password has been changed successfully.';
			        $response['data']      = '';

			        return $response;
	          	}
	          	else {

					$response['status']    = 'failure';
					$response['message']   = 'Something went wrong, while updating password.';
					$response['data']      = '';

					return $response;  
	          	}
	        } 
		    else {

				$response['status']    = 'failure';
				$response['message']   = 'Current password is incorrect.';
				$response['data']      = '';

				return $response;  
		    }   
       }

       	catch(Exception $e) {

			$response = [

				'status'  => 'failure',
				'message' => $e->getMessage(),
				'data'    => ''
			];

      	   return $response; 
       	}
	}
}

?>