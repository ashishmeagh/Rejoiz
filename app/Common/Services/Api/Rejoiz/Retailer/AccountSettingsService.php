<?php

namespace App\Common\Services\Api\Rejoiz\Retailer;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RetailerStoreDetailsModel;
use App\Models\RetailerModel;
use App\Models\CountryModel;

use App\Common\Services\GeneralService;

use Validator;
use Flash;
use Sentinel;
use Hash;

class AccountSettingsService {


    public function __construct(UserModel $user,
                                RetailerStoreDetailsModel $RetailerStoreDetailsModel,
                                RetailerModel $RetailerModel,
                                CountryModel $CountryModel,
                                GeneralService $GeneralService
                                )
    {
        $this->arr_view_data        = [];
        $this->module_title         = "Account Settings";
        $this->module_view_folder   = 'retailer.account_settings'; 
        $this->retailer_panel_slug  = config('app.project.retailer_panel_slug');
        $this->module_url_path      = url($this->retailer_panel_slug.'/account_settings');
        $this->RetailerStoreDetailsModel = $RetailerStoreDetailsModel;
        $this->GeneralService       = $GeneralService;
        $this->UserModel            = $user;
        $this->BaseModel            = $this->UserModel;
        $this->RetailerModel        = $RetailerModel;
        $this->CountryModel         = $CountryModel;
        $this->img_path             = base_path().'/storage/app/';
       

    }


	public function get($user_id=null) {

		try {

		    $user_data 	= [];

			if($user_id) {


			           $user_details = $this->UserModel->with(['retailer_details'])->where('id',$user_id)->first();
				       
				        if(isset($user_details))
				        {
				           $arr_user_data    = $user_details->toArray();
				        } 
				        
				        $retailer_details    = $this->RetailerModel->where('user_id',$user_id)->first();
				       
				        if(isset($retailer_details))
				        {
				           $arr_retailer_data = $retailer_details->toArray();
				          
				        } 
				    
				        /*get store detail from retailer id*/

				        $retailer_store_details = $this->RetailerStoreDetailsModel->where('retailer_id',$arr_user_data['id'])->first();

				        if(isset($retailer_store_details))
				        {
				            $store_logo = $retailer_store_details->store_logo;
				        }
				        else
				        {
				        	$store_logo = '';
				        }

				        /*get country data*/

				        $country_data = $this->CountryModel->where('is_active','1')
				                                                ->orderBy('name','ASC')
				                                                ->get()
				                                                ->toArray();
				    

				        // Format mobile number
				        if(isset($arr_user_data['country_code']) && $arr_user_data['country_code'] != "")
				        {
				            $contactNo = str_replace($arr_user_data['country_code'],"",$arr_user_data['contact_no']);
				            $arr_user_data['contact_no'] = $contactNo;
				        }


                           //Get first tab details

			           	   $user_data['first_name']     						= $arr_user_data['first_name'];
			           	   $user_data['last_name']      						= $arr_user_data['last_name'];
			           	   $user_data['email']          						= $arr_user_data['email'];
			           	   $user_data['post_code']      						= $arr_user_data['post_code'];
			           	   $user_data['country_id']     						= $arr_user_data['country_id'];
			           	   $user_data['country_code']     						= $arr_user_data['country_code'];
			           	   $user_data['contact_no']     						= $arr_user_data['contact_no'];
			           	   $user_data['buying_status']   						= $arr_user_data['buying_status'];
			           	   $user_data['store_name']    						    = $arr_retailer_data['store_name'];
			           	   $user_data['years_in_business']      			    = $arr_retailer_data['years_in_business'];
			           	   $user_data['annual_sales']     						= $arr_retailer_data['annual_sales'];
			           	   $user_data['store_webiste']                          = $arr_user_data['retailer_details']['store_website'];
			           	   $user_data['store_description'] 	                    = $arr_retailer_data['store_description'];
			           	   $user_data['street_address'] 	                    = $arr_user_data['address'];
			           	   $user_data['suit_apt'] 	                            = $arr_user_data['retailer_details']['address2'];
			           	   $user_data['city'] 	                                = $arr_user_data['retailer_details']['city'];
			           	   $user_data['state'] 	                                = $arr_user_data['retailer_details']['state'];


                           //Get second tab details

			           	   $user_data['profile_image']  						= imagePath($arr_user_data['profile_image'], 'user', 0);
			           	   // $user_data['store_logo']                             = url('/storage/app/'.$store_logo); 
			           	   $user_data['store_logo']                             = imagePath($store_logo, 'site_logo' , 0); 


			           	   //Get Third Tab Details 

			           	   $user_data['shipping_street_address']                = $arr_user_data['retailer_details']['shipping_addr'];
			           	   $user_data['shipping_suit_apt']                      = $arr_user_data['retailer_details']['shipping_suit_apt'];
			           	   $user_data['shipping_city']                          = $arr_user_data['retailer_details']['shipping_city'];
			           	   $user_data['shipping_state']                         = $arr_user_data['retailer_details']['shipping_state']; 
			           	   $user_data['shipping_country']                       = $arr_user_data['retailer_details']['shipping_country']; 
			           	   $user_data['shipping_zip_code']                      = $arr_user_data['retailer_details']['shipping_zip_postal_code'];
			           	   $user_data['shipping_contact_no']                    = $arr_user_data['retailer_details']['ship_contact_no'];

			           	   $user_data['billing_street_address']                 = $arr_user_data['retailer_details']['billing_address'];
			           	   $user_data['billing_suit_apt']                       = $arr_user_data['retailer_details']['billing_suit_apt'];
			           	   $user_data['billing_city']                           = $arr_user_data['retailer_details']['billing_city'];
			           	   $user_data['billing_state']                          = $arr_user_data['retailer_details']['billing_state']; 
			           	   $user_data['billing_country']                        = $arr_user_data['retailer_details']['billing_country']; 
			           	   $user_data['billing_zip_code']                       = $arr_user_data['retailer_details']['billing_zip_postal_code'];
			           	   $user_data['billing_contact_no']                     = $arr_user_data['retailer_details']['bill_contact_no'];
			           	   $user_data['tax_id']                                 = $arr_user_data['tax_id'];
 

                if(isset($user_data) && !empty($user_data)) {

                	//$user_data   = $this->CommonService->get_status_display_names($user_data,'details'); 


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

	public function update($form_data=null,$user_id=null,$type=null,$request=null)
	{
		try {
                 
		        if(isset($type) && $type!='' && $type=='personal_info')
		        {

		            $arr_data['first_name']     = $request->input('first_name',null);
		            $arr_data['last_name']      = $request->input('last_name',null);
		            $arr_data['email']          = $request->input('email',null);
		            $arr_data['country_id']     = $request->input('country_id',null);

		            $arr_data['tax_id']         = $request->input('tax_id');
		            $arr_data['contact_no']     = $request->input('contact_no');

		            if($request->input('contact_no') && $request->input('hid_country_code')){
		            $arr_data['contact_no'] = str_replace($request->input('hid_country_code'),"",$request->input('contact_no'));
		            }
		            $arr_data['country_code'] = $request->input('hid_country_code');
		            $arr_data['buying_status']  = $request->input('buying_status',null);
		            
		            $arr_data['address']        = $request->input('address',null);
		            $arr_data['post_code']      = $request->input('post_code',null);

		            $address2                   = $request->input('address2',null);
		            $city                       = $request->input('city',null);
		            $state                      = $request->input('state',null);
		            $country                    = $request->input('country',null);
		            $store_name                 = $request->input('store_name',null);
		            $store_website              = $request->input('store_website',null);
		            $years_in_business          = $request->input('years_in_business',null);
		            $Annual_Sales               = $request->input('Annual_Sales',null);
		            $store_description          = $request->input('store_description',null);
		           


		            //$obj_data = Sentinel::update($obj_data, $arr_data);
		            $update_perosonal_info      = $this->UserModel->where('id',$user_id)->update($arr_data);

		    
		            /*store some info into retailer table*/
		            $result1 = $this->RetailerModel::firstOrNew(['user_id' => $user_id]);

		            $result1->state              = isset($state)?$state:'';
		            $result1->city               = isset($city)?$city:'';
		            $result1->address2           = isset($address2)?$address2:'';
		            $result1->country            = isset($country)?$country:'';
		            $result1->store_name         = isset($store_name)?$store_name:'';
		            $result1->store_website      = isset($store_website)?$store_website:'';
		            $result1->years_in_business  = isset($years_in_business)?$years_in_business:'';
		            $result1->annual_sales       = isset($Annual_Sales)?$Annual_Sales:'';
		            $result1->store_description  = isset($store_description)?$store_description:'';
		            

		            $res = $result1->save();



		            if($res)
		            {
		               //Flash::success(str_singular($this->module_title).' Updated Successfully');
		               $response['status']      = 'success';
		               $response['message']     = 'Account settings has been updated.';
		               $response['data']        = ''; 
		               return $response;
		            }
		            else
		            {
		               $response['status']      = 'error';
		               $response['message']     = 'Error occurred,while updating '.str_singular($this->module_title).'.';
		               $response['data']        = ''; 
		               return $response;
		                 
		            } 
		        }


		        if(isset($type) && $type!='' && $type=='image_uploading')
		        {
		            $arr_data = [];
		            $profile_file_path = '';
		            $store_logo_path  = '';

		            if($request->hasFile('profile_image'))
		            {
		                $profile_image =$request->file('profile_image');
		               
		                if($profile_image!=null)
		                {
		                    $profile_file_path        = $profile_image->store('profile_image');

		                    if($request->old_image!=null)
		                   { 
		                     $old_img_path         = $this->img_path.$request->old_image;
		                     $unlink_old_img       = $this->GeneralService->unlink_old_image($old_img_path);
		                   }

		                }

		                $arr_data['profile_image'] = $profile_file_path;
		            }
		            else
		            {
		                $arr_data['profile_image'] = $request->old_image;
		            }

		            if($request->hasFile('store_logo'))
		            {
		                $store_logo = $request->file('store_logo');

		                if($store_logo !=null)
		                {
		                   $store_logo_path        = $store_logo->store('store_logo');

		                   if($request->old_store_logo!=null)
		                   { 
		                     $old_img_path         = $this->img_path.$request->old_store_logo;
		                     $unlink_old_img       = $this->GeneralService->unlink_old_image($old_img_path);
		                   }
		                }
		            }
		            else
		            {
		                $store_logo_path = $request->old_store_logo;
		            }


		            /* store logo info into retailer store table table*/
		            $result = RetailerStoreDetailsModel::firstOrNew(['retailer_id' => $user_id]); 
		            $result->retailer_id =  isset($user_id)?$user_id:0;
		            $result->store_logo  =  $store_logo_path;

		            $entity = $result->save();

		            /*profile_image update*/
		  			if(isset($form_data['auth_user']))
		  			{
		            	$obj_data = Sentinel::update($form_data['auth_user'],$arr_data);
		  			}
		       

		            if($entity)
		            {
		               $response['status']      = 'success';
		               $response['message'] = 'Account settings has been updated.';
		               return $response;
		            }
		            else
		            {
		               $response['status']      = 'error';
		               $response['message'] = 'Error occurred,while updating '.str_singular($this->module_title).'.';
		               return $response;
		            } 
		          
		        }


		        if(isset($type) && $type!='' && $type=='address_info')
		        { 
		            
		            $shipping_addr     = $request->input('shipping_addr',null);
		            $shipping_zip_postal_code  = $request->input('shipping_zip_code',null);
		            $shipping_country  = $request->input('shipping_country',null);
		            $shipping_state    = $request->input('shipping_state',null);
		            $shipping_city     = $request->input('shipping_city',null);

		            $shipping_suit_apt = $request->input('shipping_address2',null);

		            $billing_address   = $request->input('billing_addr',null);
		            $billing_zip_postal_code = $request->input('billing_zip_code',null);
		            $billing_country   = $request->input('billing_country',null);
		            $billing_state     = $request->input('billing_state',null);
		            $billing_city      = $request->input('billing_city',null);

		            $billing_suit_apt = $request->input('billing_address2',null);

		            $ship_mobile_no   = $request->input('ship_contact_no',null);
		            $bill_mobile_no   = $request->input('bill_contact_no',null);



		            $result = $this->RetailerModel::firstOrNew(['user_id' => $user_id]);

		            $result->user_id                  = isset($user_id)?$user_id:0;
		            $result->shipping_addr            = isset($shipping_addr)?$shipping_addr:'';
		            $result->shipping_city            = isset($shipping_city)?$shipping_city:'';
		            $result->shipping_state           = isset($shipping_state)?$shipping_state:'';
		            $result->shipping_country         = isset($shipping_country)?$shipping_country:'';
		            $result->shipping_zip_postal_code = isset($shipping_zip_postal_code)?$shipping_zip_postal_code:''; 

		            $result->shipping_suit_apt        = isset($shipping_suit_apt)?$shipping_suit_apt:'';

		            $result->billing_country          = isset($billing_country)?$billing_country:'';
		            $result->billing_state            = isset($billing_state)?$billing_state:'';
		            $result->billing_city             = isset($billing_city)?$billing_city:'';
		            $result->billing_address          = isset($billing_address)?$billing_address:'';
		            $result->billing_zip_postal_code  = isset($billing_zip_postal_code)?$billing_zip_postal_code:'';
		             
		            $result->billing_suit_apt        = isset($billing_suit_apt)?$billing_suit_apt:'';

		            $result->ship_contact_no         = isset($ship_mobile_no)?$ship_mobile_no:'';
		            $result->bill_contact_no         = isset($bill_mobile_no)?$bill_mobile_no:'';
		   
		            $res = $result->save();
		       
		            if($res)
		            {
		                $response['status']      = 'success';
		                $response['message']     = 'Account settings has been updated.';
		                return $response;
		            }
		            else
		            {
		               $response['status']      = 'error';
		               $response['message']     = 'Error occurred,while updating '.str_singular($this->module_title).'.';
		               return $response;
		            } 
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