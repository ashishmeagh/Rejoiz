<?php
namespace App\Common\Services;

use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\TempBagModel;
use App\Models\TransactionsModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\TransactionMappingModel;
use App\Models\RepresentativeModel;
use App\Models\RetailerModel;
use App\Models\EmailTemplateModel;
use App\Models\PromoCodeRetailerMappingModel;
use App\Models\ZipExtractionModel;
use App\Models\ProductInventoryModel;
use App\Models\UserStripeAccountDetailsModel;

use App\Common\Services\EmailService;
use App\Common\Services\StripePaymentService;
use App\Common\Services\InventoryService;
use App\Common\Services\MyCartService;
use App\Common\Services\orderDataService;
use App\Common\Services\RepsEmailService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;
use App\Models\SiteSettingModel;


use Mail;
use Request;
use Stripe;
use Carbon\Carbon;
use Flash;


use App\Events\NotificationEvent;
use Session, Sentinel, DB,PDF,Storage;

class GeneralService
{
	public function __construct(
									CountryModel $CountryModel,
									StateModel $StateModel,
									CityModel $CityModel,
                                    EmailService $EmailService,
									RepresentativeLeadsModel $RepresentativeLeadsModel,
									RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
									RetailerQuotesModel $RetailerQuotesModel,
									RoleUsersModel $RoleUsersModel,
									RetailerQuotesProductModel $RetailerQuotesProductModel,
									TempBagModel $TempBagModel,
									TransactionsModel $TransactionsModel,
									MakerModel $MakerModel,
									RepresentativeModel $RepresentativeModel,
									RetailerModel $RetailerModel,
									EmailTemplateModel $EmailTemplateModel,
									UserModel $UserModel,
									StripePaymentService $StripePaymentService,
									MyCartService $MyCartService,
									orderDataService $orderDataService,
									PromoCodeRetailerMappingModel $PromoCodeRetailerMappingModel,
                                    TransactionMappingModel $TransactionMappingModel,
                                    InventoryService $InventoryService,
                                    ZipExtractionModel $ZipExtractionModel,
                                    ProductInventoryModel $ProductInventoryModel,
                                    UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                    RepsEmailService $RepsEmailService,
                                    HelperService $HelperService,
                                    CommissionService $CommissionService
									
								)
	{

		$this->CountryModel                    = $CountryModel;
		$this->UserModel 					   = $UserModel;
		$this->StateModel                      = $StateModel;
		$this->CityModel                       = $CityModel;
		$this->TransactionsModel               = $TransactionsModel;
		$this->RepresentativeLeadsModel        = $RepresentativeLeadsModel;
		$this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
		$this->RetailerQuotesModel             = $RetailerQuotesModel;
		$this->RetailerQuotesProductModel      = $RetailerQuotesProductModel;
		$this->TempBagModel                    = $TempBagModel;
		$this->MakerModel 					   = $MakerModel;
		$this->RetailerModel 				   = $RetailerModel;
		$this->EmailTemplateModel			   = $EmailTemplateModel;
		$this->RoleUsersModel			       = $RoleUsersModel;
		$this->RepresentativeModel 			   = $RepresentativeModel;
        $this->EmailService                    = $EmailService;
        $this->InventoryService 			   = $InventoryService;
        $this->MyCartService 			   	   = $MyCartService;
        $this->TransactionMappingModel         = $TransactionMappingModel;
        $this->StripePaymentService            = $StripePaymentService;
        $this->orderDataService                = $orderDataService;
        $this->PromoCodeRetailerMappingModel   = $PromoCodeRetailerMappingModel;
        $this->ZipExtractionModel              = $ZipExtractionModel;
        $this->ProductInventoryModel           = $ProductInventoryModel;
        $this->UserStripeAccountDetailsModel   = $UserStripeAccountDetailsModel;
        $this->RepsEmailService                = $RepsEmailService;
        $this->CommissionService          	   = $CommissionService;
        $this->HelperService            	   = $HelperService;
      	$this->product_default_img_path        = config('app.project.img_path.product_default_images');
      	$this->storage_path                    = config('app.url').'/storage/app/public/pdf/';
	}

	public function getCountries()
	{
		$arr_country = [];
		$arr_country = $this->CountryModel->get()->toArray();
		return $arr_country;
	}

	public function getStates($country_id = false)
	{
		$arr_state = [];

		if($country_id){	
			$arr_state = $this->StateModel->where('country_id',$country_id)
										  ->get()
 										  ->toArray();

		}

		return $arr_state;
	}

	public function getCities($state_id = false)
	{
		$arr_city = [];

		if($state_id){
			$arr_city = $this->CityModel->where('state_id',$state_id)
										->get()
										->toArray();
		}
		return $arr_city;
	}

	public function get_bucket_items($lead_id = 0)
	{
		$lead_arr = [];

		$user = Sentinel::check();
		
		$loggedIn_userId = 0;

		if($user)
		{
		    $loggedIn_userId = $user->id;
		}   
		
		$lead_obj = $this->RepresentativeLeadsModel->with(['leads_details.product_details'=>function($query)
													{
														$query->orderBy('created_at','DESC');	
													}])
												   ->where('id',$lead_id)
												   // ->where('is_confirm',0)
												   ->where('representative_id',$loggedIn_userId)
												   ->first();

		if($lead_obj)
		{
			$lead_arr = $lead_obj->toArray();
		}

		return $lead_arr;
	}

	public function store_retailers_cart_items($transaction_id=false,$order_addr_data,$payment_type=false)
	{ 
		
		if(Session::has('payment_type'))
		{

			$order_id = Session::get('order_id');

			$representative_order_id = Session::get('representative_order_id');
			

	    	if(isset($order_id) && $order_id!='')
	    	{ 
	    		$loggedInUserId = 0;
		        $user = \Sentinel::check();

		        if($user && $user->inRole('retailer'))
		        {
		            $loggedInUserId = $user->id;
		            $retailerEmail = $user->email;
		        }
	      
		    	$order_details = [];
	
		    	$order_details = $this->RetailerQuotesModel->where('id',$order_id)
		    									->with(['quotes_details','user_details'])
		    								   ->where('id',$order_id)->first();


               
				$arr_data = $arr_charge_data = [];
		    	 
		    	if($order_details)
		    	{
		    		$order_details = $order_details->toArray();                  
		    		
		  
   
		    		$arr_charge_data['order_amount'] = isset($order_details['total_wholesale_price'])?$order_details['total_wholesale_price']:0;

		    		$arr_charge_data['stripe_key'] = isset($order_details['transaction_details']['strip_key_details']['secret_key'])?$order_details['transaction_details']['strip_key_details']['secret_key']:false;

		    		$paymentStripeKeyId = isset($order_details['transaction_details']['strip_key_details']['id'])?$order_details['transaction_details']['strip_key_details']['id']:false;

		    		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

	
		    		/************************Direct payment data ********************************/

	    		    $ordNo = isset($order_details['order_no'])?$order_details['order_no']:false;


		    		// $isDirectPayment = isset($order_details['maker_data']['is_direct_payment'])?$order_details['maker_data']['is_direct_payment']:0;

		    		$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;
		    				
    				$retailerId = isset($order_details['retailer_id'])?$order_details['retailer_id']:0;

    				$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:false;
    				
    				$cardId = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:false;

    				$makerId = isset($order_details['maker_id'])?$order_details['maker_id']:false;

    				$arrData = [];

    				$arrData['customerId']   = $customerId;
    				$arrData['retailerId']   = $retailerId;
    				$arrData['vendorId']     = $makerId;
    				$arrData['stripe_key']   = $arr_charge_data['stripe_key'];
    				$arrData['order_amount'] = $arr_charge_data['order_amount'];
    				$arrData['cardId']       = $cardId;
    				$arrData['order_no']     = isset($ordNo)?$ordNo:'';
    				
    				/********************************************************************************/
		    	
		    	    /* if(isset($arr_charge_data) && count($arr_charge_data) > 0 )
		    	      {*/

		    			
				        if($charge['status'] != 'Error')
				        {
				        	//$charge_status = $charge->status;
				        	$charge_status = isset($charge['status'])?$charge['status']:"";

				        	if($charge_status == 'succeeded')
				        	{
				        		$status = '2';
				        	}else if($charge_status == 'pending')
				        	{
				        		$status = '1';
				        	}else
				        	{
				        		$status = '3'; // Failed
				        	}

				

				        	//dd($status);

				        	if($status == '2')
				        	{			        		
			                	$updated_data = [];

					            $updated_data['payment_term'] = 'Net30 - Online/Credit';
					            $updated_data['stripe_key_id'] = $stripKeyId;
					            
					            $update = $this->RetailerQuotesModel->where('id',$order_id)->update($updated_data);  
				            
				        		/* get admin id */

				        		$admin_id = get_admin_id();


				        		$maker_details = \Sentinel::findById($order_details['maker_id']);
				        		$view_href   =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);

                          
				        		/* send admin notification*/
				        		$arr_notify_data                 = [];
							    $arr_notify_data['from_user_id'] = $order_details['retailer_id'] or '';
							    $arr_notify_data['to_user_id']   = $admin_id or '';

							    if($isDirectPayment == 1)
							    {
							        $arr_notify_data['description']  = 'Net30 Payment is done by '.$order_details['user_details']['first_name'].' '.$order_details['user_details']['last_name'].' for order('.$order_details['order_no'].')';

							    }
							    else
							    {
							    	$arr_notify_data['description']  = 'Payment is done by '.$order_details['user_details']['first_name'].' '.$order_details['user_details']['last_name'].' for order('.$order_details['order_no'].')';
							    }

							    $arr_notify_data['title']        = 'Order Payment';
							    $arr_notify_data['type']         = 'admin';  
							    $arr_notify_data['link']         = $view_href;  

				        		$this->save_notification($arr_notify_data);

				        		/* send retailer notification */
				        		if($isDirectPayment == 1)
								{

									$order_status_update = $this->RetailerQuotesModel->where('id',$order_details['id'])
				        													         ->where('maker_id',$makerId)
				        													         ->update(['is_direct_payment'=>$isDirectPayment]);


								    $view_href = url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
								    $arr_notify_data                 = [];
								    $arr_notify_data['to_user_id']   = $loggedInUserId or '';
								    $arr_notify_data['from_user_id'] = $admin_id or '';
  

								    $arr_notify_data['description']  = 'Your Net30 order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';

								    $arr_notify_data['title']        = 'Net30 payment';
								    $arr_notify_data['type']         = 'retailer';  
								    $arr_notify_data['link']         = $view_href;  

								    $this->save_notification($arr_notify_data);


								   
								   /* send maker notification */
								    $view_href = url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);
								    $arr_notify_data                 = [];
								    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
								    $arr_notify_data['from_user_id'] = $admin_id or '';


								    $arr_notify_data['description']  = 'Your Net30 order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';


								    $arr_notify_data['title']        = 'Net30 payment';
								    $arr_notify_data['type']         = 'maker';  
								    $arr_notify_data['link']         = $view_href;  

								    $this->save_notification($arr_notify_data);



								    /* get user mail details */
								    $user_details = \Sentinel::findById($order_details['maker_id']);

								    $user_email = isset($user_details->email)?$user_details->email:false;
								    
								   

	                                //send notification to admin after net30 order payment done
	                                $admin_id = get_admin_id();
	                                $view_href = url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
									$arr_notify_data                 = [];
									$arr_notify_data['to_user_id']   = $admin_id or '';
									$arr_notify_data['from_user_id'] = $loggedInUserId or '';

									$arr_notify_data['description']  = 'Net30 order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from customer account.';

									$arr_notify_data['title']        = 'Net30 payment';
									$arr_notify_data['type']         = 'admin';  
									$arr_notify_data['link']         = $view_href;  

									$this->save_notification($arr_notify_data);




	       							/*send mail to admin,retailer,vendor for net30 order payment done*/
	                                  
	                                //send mail to retailer  for deduct amount 
	                                $role = 'Retailer';

	                                $this->order_payment_mail($retailerEmail,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);

	                                //send mail to admin for retailer deduct amount

	                                $admin_id      = get_admin_id();
					        		$admin_details = \Sentinel::findById($admin_id);

					        		$admin_email = isset($admin_details->email)?$admin_details->email:false;

	                                $role = 'Admin';
	                                
	                                $this->order_payment_mail($admin_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);


	                                //send mail to vendor for amount credited
	                                $role = 'Vendor';  
	                                $this->order_payment_mail($user_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);

									/*-----------------------------------------------------------*/ 
								}
								else
								{
									//indirect payment

									//send notification to retailer for amount deducted

								    $view_href = url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
								    $arr_notify_data                 = [];
								    $arr_notify_data['to_user_id']   = $loggedInUserId or '';
								    $arr_notify_data['from_user_id'] = $admin_id or '';

								    $arr_notify_data['description']  = 'Your Net30 order('.$order_details['order_no'].') payment has been done, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';

								    $arr_notify_data['title']        = 'Net30 order payment';
								    $arr_notify_data['type']         = 'retailer';  
								    $arr_notify_data['link']         = $view_href;  

								    $this->save_notification($arr_notify_data);

                                    //send notification to admin for amount credited

                                    $view_href = url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
									$arr_notify_data                 = [];
									$arr_notify_data['to_user_id']   = $admin_id or '';
									$arr_notify_data['from_user_id'] = $loggedInUserId or '';

									$arr_notify_data['description']  = 'Net30 order('.$order_details['order_no'].') payment has been done, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';

									$arr_notify_data['title']        = 'Net30 order payment';
									$arr_notify_data['type']         = 'admin';  
									$arr_notify_data['link']         = $view_href;  

									$this->save_notification($arr_notify_data);



									/*send mail to admin and retailer for net30 payment done*/

									//send mail to retailer  for deducted amount 
	                                $role = 'Retailer';

	                                $this->order_payment_mail($retailerEmail,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);

	                                //send mail to admin for amount credited

	                                $admin_id      = get_admin_id();
					        		$admin_details = \Sentinel::findById($admin_id);

					        		$admin_email = isset($admin_details->email)?$admin_details->email:false;

	                                $role = 'Admin';
	                                
	                                $this->order_payment_mail($admin_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);
                                     
									/*------------------------------------------------------*/
								}

             					$response['status']      = 'success';
                                $response['description'] = 'Payment has been done.';
					        	return $response;
				        	}
				        	else
				        	{ 
				        		$response['status']      = 'warning';
					        	$response['description'] = 'Something went wrong,please try again.';
					        	return $response;
				        	}
				        	
		        }
		        else
	    		{
	    			$response['status']       = 'warning';
			        $response['description']  = $charge['description'];
			        return $response;
	    		}

	    	
		    	}
	        }
	        elseif(isset($representative_order_id) && $representative_order_id!='')
	        {

	        	$paymentTermStatus = $this->RepresentativeLeadsModel->where('id',$representative_order_id)
						        	                                ->pluck('payment_term')
						        	                                ->first();
						                                
                $loggedInUserId = 0;
		        $user = \Sentinel::check();

		        if($user && $user->inRole('retailer'))
		        {
		            $loggedInUserId = $user->id;
		            $retailerEmail  = $user->email;
		        }

		        $order_details = $this->RepresentativeLeadsModel
		                                ->where('id',$representative_order_id)
										->with(['transaction_details'])
										->first();

			     $cardData = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

	    		 /* card verification */

			      if(isset($cardData))
			      {
			        $is_valid = $this->StripePaymentService->is_card_valid($cardData);

			        if(isset($is_valid['status']) && $is_valid['status'] =='error')
			        {
			          $response['status']      = 'error';
			          $response['description'] = isset($is_valid['message'])?$is_valid['message']:'';
			          // return response()->json($response);
			          return $response;
			        }
			      }
			      
				if($paymentTermStatus == 'Net30')
				{
	                
					$order_details = $this->RepresentativeLeadsModel
		    									->with(['transaction_details.strip_key_details',
		    										    'leads_details',
		    										    'user_details',
		    										    'retailer_user_details',
		    										    'maker_details.stripe_account_details'
		    										])
		    								   ->where('id',$representative_order_id)->first();
		    								   
					$arr_data = $arr_charge_data = [];
			    	    	
			    	if($order_details)
			    	{
			    		$order_details = $order_details->toArray();

			            
			    		$arr_charge_data['order_amount'] = isset($order_details['total_wholesale_price'])?$order_details['total_wholesale_price']:0;

			    		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

			    		$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

			    		$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';

			    		$arr_charge_data['stripe_key'] = isset($order_details['transaction_details']['strip_key_details']['secret_key'])?$order_details['transaction_details']['strip_key_details']['secret_key']:false;

			    		$paymentStripeKeyId = isset($order_details['transaction_details']['strip_key_details']['id'])?$order_details['transaction_details']['strip_key_details']['id']:false;
			    	
			    		/************** Direct payment data *****************************************/

		    		    $ordNo = isset($order_details['order_no'])?$order_details['order_no']:false;


			    		// $isDirectPayment = isset($order_details['maker_details']['is_direct_payment'])?$order_details['maker_details']['is_direct_payment']:0;

			    		$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;
			    				
	    				$retailerId = isset($order_details['retailer_id'])?$order_details['retailer_id']:0;

	    				$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:false;
	    				
	    				$cardId = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:false;

	    				$makerId = isset($order_details['maker_id'])?$order_details['maker_id']:false;

	    				$arrData = [];

	    				$arrData['customerId']   = $customerId;
	    				$arrData['retailerId']   = $retailerId;
	    				$arrData['vendorId']     = $makerId;
	    				$arrData['order_amount'] = $arr_charge_data['order_amount'];
	    				$arrData['stripe_key']   = $arr_charge_data['stripe_key'];
	    				$arrData['cardId']       = $cardId;
	    				$arrData['order_no']     = isset($ordNo)?$ordNo:'';
	    				
	    				/********************************************************************************/
	    				// dd($isDirectPayment,$arrData,$arr_charge_data);
				        if(isset($arr_charge_data) && count($arr_charge_data) > 0)
			    		{
			    		
			    			// Payment duducted

			    			if($isDirectPayment == 1)
		    				{
		    					$stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($makerId);
								
								$stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';
								
		 	    				$charge = $this->StripePaymentService->create_direct_charge($arrData);
		    				}
		    				else
		    				{
		    					$admin_user_id       = get_admin_id();

								$stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($admin_user_id);

								$stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

								$arrStripeDetails = $this->StripePaymentService->build_stripe_card_and_key_data($cardId,$customerId,$arr_charge_data['stripe_key'],$paymentStripeKeyId,$admin_user_id,$retailerId);

								$arr_charge_data['customer_id']   = $arrStripeDetails['customer_id'];

								$arr_charge_data['stripe_key']    = $arrStripeDetails['secret_key'];

								$arr_charge_data['card_id']       = $arrStripeDetails['card_id'];
							
		 	    				$charge = $this->StripePaymentService->create_charge($arr_charge_data);
		    				}
			    			
					        if($charge['status'] != 'Error')
					        {
					        	//$charge_status = $charge->status;
					        	$charge_status = isset($charge['status'])?$charge['status']:"";

					        	if($charge_status == 'succeeded')
					        	{
					        		$status = '2';
					        	}else if($charge_status == 'pending')
					        	{
					        		$status = '1';
					        	}else{
					        		$status = '3'; // Failed
					        	}	                     

	                          	if(Session::get('payment_type') == 'Net30')
	                          	{

	                            /* Update payment status in order table by Harshada on date 21 Oct 2020 */
							    $this->RepresentativeLeadsModel->where('id',$order_id)   										   ->where('order_no',$ordNo)										   ->update(['is_payment_status' => '1',
					        	                   	                     'stripe_key_id'     => $stripKeyId
					        	                   	                    ]);


		                          	$arr_data['user_id']             = $order_details['retailer_id'] or '';
						        	$arr_data['order_id']            = $order_details['id'] or '';
						        	$arr_data['order_no']            = $order_details['order_no'] or '';
						        	$arr_data['amount']              = $order_details['total_wholesale_price'] or '';
						        	$arr_data['transaction_id']      = isset($charge['id'])?$charge['id']:"";
						        	$arr_data['transaction_status']  = $status or '';
						        	$arr_data['payment_type']        = '1' or '';

						        	$transaction_entry = $this->TransactionMappingModel->create($arr_data);

	                          	}
					        	
					        	if($status == '2')
					        	{
					        		

				                	$updated_data = [];

						            $updated_data['payment_term'] = 'Net30 - Online/Credit';

						            $update = $this->RepresentativeLeadsModel->where('id',$representative_order_id)->update($updated_data);  


					        		$admin_id = get_admin_id();

					        		$maker_details = \Sentinel::findById($order_details['maker_id']);
					        	    $view_href     =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);

					        		/* send admin notification*/
					        		$arr_notify_data                 = [];
								    $arr_notify_data['from_user_id'] = $order_details['retailer_id'] or '';
								    $arr_notify_data['to_user_id']   = $admin_id or '';

								  
								    if($isDirectPayment == 1)
    	    				        {
								      $arr_notify_data['description']  = 'Net30 Payment is done by '.$order_details['retailer_user_details']['first_name'].' '.$order_details['retailer_user_details']['last_name'].' for order('.$order_details['order_no'].')';

								    }else
								    {
								      $arr_notify_data['description']  = 'Payment is done by '.$order_details['retailer_user_details']['first_name'].' '.$order_details['retailer_user_details']['last_name'].' for order('.$order_details['order_no'].')';
								    }

								    $arr_notify_data['title']        = 'Order Payment';
								    $arr_notify_data['type']         = 'admin';  
								    $arr_notify_data['link']         = $view_href;  


					        		$this->save_notification($arr_notify_data);
	      	
					        		//$this->send_maker_mail($order_details['order_no']);

					        		/* send maker notification */
					        		if($isDirectPayment == 1)
    	    				        {
    	    				        	$order_status_update = $this->RepresentativeLeadsModel
    	    				        	                            ->where('id',$order_details['id'])
    			        										    ->where('maker_id',$order_details['maker_id'])
    			        										    ->update(['is_direct_payment'=>$isDirectPayment]);

					        			/* send maker notification */
									    $view_href = url('/').'/vendor/representative_orders/view/'.base64_encode($order_details['order_no']);
									    $arr_notify_data                 = [];
									    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
									    $arr_notify_data['from_user_id'] = $admin_id or '';

								        $arr_notify_data['description']  = 'Your Net30 order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';


									    $arr_notify_data['title']        = 'Net30 payment';
									    $arr_notify_data['type']         = 'maker';  
									    $arr_notify_data['link']         = $view_href;  

								        $this->save_notification($arr_notify_data);


								   
								        /* send retailer notification */
									   	$view_href = url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);
									    $arr_notify_data                 = [];
									    $arr_notify_data['to_user_id']   = $loggedInUserId or '';
									    $arr_notify_data['from_user_id'] = $admin_id or '';

									    $arr_notify_data['description']  = 'Your Net30 order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';

									    $arr_notify_data['title']        = 'Net30 payment';
									    $arr_notify_data['type']         = 'retailer';  
									    $arr_notify_data['link']         = $view_href;  

									    $this->save_notification($arr_notify_data);


									     /* get user mail details */
									    $user_details = \Sentinel::findById($order_details['maker_id']);

									    $user_email = isset($user_details->email)?$user_details->email:false;
									    
									      
                                        /*send mail to admin,retailer,vendor for net30 order payment done*/
	                                  
		                                //send mail to retailer  for deduct amount 
		                                $role = 'Retailer';

		                                $this->order_payment_mail($retailerEmail,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);


		                                //send mail to admin for retailer deduct amount

		                                $admin_id      = get_admin_id();
						        		$admin_details = \Sentinel::findById($admin_id);

						        		$admin_email = isset($admin_details->email)?$admin_details->email:false;

		                                $role = 'Admin';
		                                
		                                $this->order_payment_mail($admin_email,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);


		                                //send mail to vendor for amount credited
		                                $role = 'Vendor';  
		                                $this->order_payment_mail($user_email,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);

										/*-----------------------------------------------------------*/ 

						        	}
						        	else
						        	{
                                       
                                        //indirect payment method

										//send notification to retailer for amount deducted

									    $view_href = url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
									    $arr_notify_data                 = [];
									    $arr_notify_data['to_user_id']   = $loggedInUserId or '';
									    $arr_notify_data['from_user_id'] = $admin_id or '';

									    $arr_notify_data['description']  = 'Your Net30 order('.$order_details['order_no'].') payment has been done, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';

									    $arr_notify_data['title']        = 'Net30 order payment';
									    $arr_notify_data['type']         = 'retailer';  
									    $arr_notify_data['link']         = $view_href;  

									    $this->save_notification($arr_notify_data);

	                                    //send notification to admin for amount credited

	                                    $view_href = url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
										$arr_notify_data                 = [];
										$arr_notify_data['to_user_id']   = $admin_id or '';
										$arr_notify_data['from_user_id'] = $loggedInUserId or '';

										$arr_notify_data['description']  = 'Net30 order('.$order_details['order_no'].') payment has been done, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';

										$arr_notify_data['title']        = 'Net30 order payment';
										$arr_notify_data['type']         = 'admin';  
										$arr_notify_data['link']         = $view_href;  

										$this->save_notification($arr_notify_data);



										/*send mail to admin and retailer for net30 payment done*/

										//send mail to retailer  for deducted amount 
		                                $role = 'Retailer';

		                                $this->order_payment_mail($retailerEmail,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);

		                                //send mail to admin for amount credited

		                                $admin_id      = get_admin_id();
						        		$admin_details = \Sentinel::findById($admin_id);

						        		$admin_email = isset($admin_details->email)?$admin_details->email:false;

		                                $role = 'Admin';
		                                
		                                $this->order_payment_mail($admin_email,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$role);
	                                     
										/*------------------------------------------------------*/


						        	}


                                

			                        $objOrder = $this->RepresentativeLeadsModel
			                                         ->with(['leads_details','address_details'])
			                                         ->where('id',$representative_order_id)
			                                         ->get();

								    if($objOrder)
								    {
								       $orderData = $objOrder->toArray();
								    }


					                $order_number = $orderData[0]['order_no'];

						        	/*send notification to retailer after payment*/
     
							        $view_href  =  url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_number).'/'.base64_encode($orderData[0]['maker_id']);

							        $arr_event                 = [];
							        $arr_event['from_user_id'] = $loggedInUserId;
							        $arr_event['to_user_id']   = $loggedInUserId;


							        $arr_event['description']  = 'Your Net30 payment has been done for Order No : '.$order_number;

							        $arr_event['title']        = 'Order Payment';
							        $arr_event['type']         = 'retailer'; 
							        $arr_event['link']         = $view_href; 
				
							        $this->save_notification($arr_event);



							        /*send notification to rep/sales after payment*/

							        if(isset($orderData[0]['representative_id']) && $orderData[0]['representative_id']!=0)
							        {
			                            $view_href  =  url('/').'/representative/leads/view_lead_listing/'.base64_encode($representative_order_id).'/'.base64_encode($order_number);

			                            $type    = 'representative';
			                            $user_id = $orderData[0]['representative_id'];
							        }
							        else if(isset($orderData[0]['sales_manager_id']) && $orderData[0]['sales_manager_id']!=0)
							        {
			                           $view_href  =  url('/').'/sales_manager/leads/view_lead_listing/'.base64_encode($representative_order_id).'/'.base64_encode($order_number);

			                           $type    = 'sales_manager';
			                           $user_id = $orderData[0]['sales_manager_id'];
							        }
							        else
							        {
							        	$view_href ='';
							        	$type = '';
							        	$user_id = '';
							        }

						
							        $first_name = isset($user->first_name)?$user->first_name:"";
							        $last_name  = isset($user->last_name)?$user->last_name:"";  

							        $arr_event                 = [];
							        $arr_event['from_user_id'] = $loggedInUserId;
							        $arr_event['to_user_id']   = $user_id;


							        $arr_event['description']  = 'Net30 payment has been done by customer '.$first_name.' '.$last_name.' . Order No : '.$order_number;

							        $arr_event['title']        = 'Order Payment';
							        $arr_event['type']         = $type; 
							        $arr_event['link']         = $view_href; 
				
							        $this->save_notification($arr_event);

					              
	                                          
						        	$response['status']      = 'success';
	                                $response['description'] = 'Payment has been done.';
						        	return $response;
					        	}
					        	else
					        	{ 
					        		$response['status']      = $charge['status'];
						        	$response['description'] = $charge['description'];
						        	return $response;
					        	}
					        	
					        }
					        else
				    		{

				    			$response['status']       = $charge['status'];
						        $response['description']  = $charge['description'];

						        return $response;
				    		}

			    		}
			    		else
			    		{   
			    			$response['status']      = 'warning';
				        	$response['description'] = 'Something went wrong,please try again.';
				        	return $response;
			    		
			    		}
			    	}

				}
				else
				{
					if(Session::get('payment_type') == 'Online/Credit') 
	                {
	                	$updated_data = [];

			            $updated_data['payment_term'] = 'Offline';

			            $update = $this->RepresentativeLeadsModel->where('id',$representative_order_id)->update($updated_data);  



			            /*send purchase order mail*/

			            $objOrder = $this->RepresentativeLeadsModel
			                             ->with(['leads_details','address_details'])
			                             ->where('id',$representative_order_id)
			                             ->get();

					    if($objOrder)
					    {
					       $orderData = $objOrder->toArray();
					    }


					    $order_number = $orderData[0]['order_no'];

                        //send payment mail to admin,rep/sales,retailer,vendor
					   
					   $sendEmailToRetailer  = $this->RepsEmailService->rep_sales_purchase_order_mail_to_admin($orderData,$order_number);


					    /*send notification to admin after payment*/

					    $admin_id = get_admin_id();
				       

				        $view_href  =  url('/').'/admin/leads/view/'.base64_encode($representative_order_id);

				        $first_name = isset($user->first_name)?$user->first_name:"";
				        $last_name  = isset($user->last_name)?$user->last_name:"";  

				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $admin_id;


				        $arr_event['description']  = 'Order has been placed by customer '.$first_name.' '.$last_name.' . Order No : '.$order_number;

				        $arr_event['title']        = 'Order Placed';
				        $arr_event['type']         = 'admin'; 
				        $arr_event['link']         = $view_href; 
	
				        $this->save_notification($arr_event);


				        /*send notification to retailer after payment*/
     
				       /* $view_href  =  url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_number).'/'.base64_encode($orderData[0]['maker_id']);

				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $loggedInUserId;


				        $arr_event['description']  = 'Your payment has been done for Order No : '.$order_number;

				        $arr_event['title']        = 'Order Payment';
				        $arr_event['type']         = 'retailer'; 
				        $arr_event['link']         = $view_href; 
	
				        $this->save_notification($arr_event);*/


				        /*send notification to vendor after payment*/

				        $view_href  =  url('/').'/vendor/representative_orders/view/'.base64_encode($order_number);

				        $first_name = isset($user->first_name)?$user->first_name:"";
				        $last_name  = isset($user->last_name)?$user->last_name:"";  

				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $orderData[0]['maker_id'];
				        $retailerDummyName = get_retailer_dummy_shop_name($order_details['retailer_id']);

				        $arr_event['description']  = 'Order has been placed by customer '.$retailerDummyName.' . Order No : '.$order_number;

				        $arr_event['title']        = 'Order Placed';
				        $arr_event['type']         = 'maker'; 
				        $arr_event['link']         = $view_href; 
	
				        $this->save_notification($arr_event);



				        /*send notification to rep/sales after payment*/

				        if(isset($orderData[0]['representative_id']) && $orderData[0]['representative_id']!=0)
				        {
                            $view_href  =  url('/').'/representative/leads/view_lead_listing/'.base64_encode($representative_order_id).'/'.base64_encode($order_number);

                            $type    = 'representative';
                            $user_id = $orderData[0]['representative_id'];
				        }
				        else if(isset($orderData[0]['sales_manager_id']) && $orderData[0]['sales_manager_id']!=0)
				        {
                           $view_href  =  url('/').'/sales_manager/leads/view_lead_listing/'.base64_encode($representative_order_id).'/'.base64_encode($order_number);

                           $type    = 'sales_manager';
                           $user_id = $orderData[0]['sales_manager_id'];
				        }
				        else
				        {
				        	$view_href ='';
				        	$type = '';
				        	$user_id = '';
				        }


				        

			
				        /*$first_name = isset($user->first_name)?$user->first_name:"";
				        $last_name  = isset($user->last_name)?$user->last_name:"";  

				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $user_id;


				        $arr_event['description']  = 'Payment has been done by retailer '.$first_name.' '.$last_name.' . Order No : '.$order_number;

				        $arr_event['title']        = 'Order Payment';
				        $arr_event['type']         = $type; 
				        $arr_event['link']         = $view_href; 
	
				        $this->save_notification($arr_event);*/

	                }

				                           
		        	$response['status']      = 'success';
	                $response['description'] = 'Order has been confirmed by customer.';
		        	return $response;
				}                
	        }
	        else
			{	
		        $quotes_arr    = [];
		        $next_due_date = $payment_term = '';
	  
		     
		        if(isset($payment_type) && $payment_type!=false)
		        {	        	

		           $payment_term = $payment_type;

		           $next_due_date = date('Y-m-d H:i:s', strtotime("+30 days"));
	     
		        }
		        else
		        {
		        
		        	$payment_term = 'Offline';
		        	$next_due_date = '';
		        }


			    if(!Session::has('bag_data'))
			    {
			        $response_arr['status']   = 'failure';
			        return $response_arr;
			    }
		       

		        $loggedInUserId = $quote_id = 0;
		        $user = Sentinel::check();



		        if($user && $user->inRole('retailer'))
		        {
		           $loggedInUserId = $user->id;
		        }	        
		        else
		        {
		        	$response_arr['status']   = 'failure';        		
		        	return $response_arr;
		        } 
		         
		        $ip_address = \Request::ip();
		        $session_id = session()->getId();

	            // $bag_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)
		    								   // ->first(['product_data']);
				$bag_data = $this->MyCartService->get_items();

				if($bag_data)
				{
					$bag_data->toArray();
		    		$bag_data  = isset($bag_data['product_data'])?$bag_data['product_data']:"";
				}

		    	
		        $bag_arr   = json_decode($bag_data,true);
		      	// dd($bag_data);
		       	$arr_product = $bag_arr['sku'];

		        
		       $pro_arr = 	$arr_product;	
		      
		        $result = $promo_mappping = [];


		        if(isset($arr_product) && count($arr_product) > 0)
		        {
		        	foreach ($arr_product as $key => $value) 
			        {
			        	$result[$value['maker_id']][] = $value;
			        }
		        }
		       
		        try
		        {

		        	$promotion_session_data = [];
		        	if (is_array(Session::get('promotion_data')) && count(Session::get('promotion_data')) > 0) 
		        	{
		        		$promotion_session_data = Session::get('promotion_data');
		        	}

		        	DB::beginTransaction();
		        	if(count($result)>0)
		        	{
		        	
		        		$order_no = str_pad('J2',  10, rand('1234567890',10)); 

		        		$order_no = $this->orderDataService->verify_order_no($order_no);
		        		
		        		$all_product_arr = [];
		        		$promo_discount_amount = $promo_codeId = $promo_code = 0;
		        		foreach ($result as $key => $product_arr) 
				        {

				        	if (isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
				        	{

				        		$promo_discount_amount  = isset($promotion_session_data[$key]['final_total'][$key]['discount_amt'])?$promotion_session_data[$key]['final_total'][$key]['discount_amt']:0;
				        		$promo_shipping_charges = isset($promotion_session_data[$key]['final_total'][$key]['shipping_charges'])?$promotion_session_data[$key]['final_total'][$key]['shipping_charges']:1;

				        		$promo_code  = isset($promotion_session_data[$key]['promo_code'])?$promotion_session_data[$key]['promo_code']:0;

				        		array_push($all_product_arr,$product_arr);
				        	
				        		$total_retail_price = $total_wholesale_price = 0;
				        	
				        		$total_retail_price = array_sum(array_column($product_arr,'total_price'));

				        		$total_price = array_sum(array_column($product_arr,'total_wholesale_price'));

				        		$total_wholesale_price = $total_price - $promo_discount_amount;

				        	
				        	}
				        	else{

				        		$promo_discount_amount = 0;
				        		$promo_shipping_charges = 1;

				        		$promo_code = '';
				        		array_push($all_product_arr,$product_arr);
				        	
					        	$total_retail_price = $total_wholesale_price = 0;
					        	
					        	$total_retail_price = array_sum(array_column($product_arr,'total_price'));

					        	$total_price = array_sum(array_column($product_arr,'total_wholesale_price'));

					        	$total_wholesale_price = $total_price;
				        	}			        	
				        	
				        	$pro_ship_charge =0;
				        	$pro_ship_dis =0;
				        	$pro_dis = 0;
				        	foreach ($product_arr as $pro_key => $pro) 
				        	{
				        		if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

				        			$pro_ship_charge 	+= 0;
				        			$pro_ship_dis 		+= 0;

				        		}
				        		else{

				        			$pro_ship_charge 	+= isset($pro['shipping_charges'])?$pro['shipping_charges']:0;
				        			$pro_ship_dis 		+= isset($pro['shipping_discount'])?$pro['shipping_discount']:0;
				        		}
				        		
				        		$pro_dis 			+= isset($pro['product_discount_amount'])?$pro['product_discount_amount']:0;

				        	}

				        	
				        	
				         	$total_wholesale_price = $total_wholesale_price+$pro_ship_charge-$pro_ship_dis-$pro_dis;

				         	$is_direct_payment = $this->MakerModel->where('user_id',$key)->first();
				         	if(isset($is_direct_payment))
				         	{
				         		$temp_vendor_data_arr = $is_direct_payment->toArray();
				         		$is_direct_payment = isset($temp_vendor_data_arr['is_direct_payment'])?$temp_vendor_data_arr['is_direct_payment']:0;
				         	}


				        	$quotes_arr = [];
				        	$quotes_arr['maker_id']              = $key;
				        	$quotes_arr['order_no']              = $order_no;
				        	$quotes_arr['admin_commission']      = $this->CommissionService->get_admin_commission($key);
						    $quotes_arr['retailer_id']           = $loggedInUserId;
						    $quotes_arr['status']                = 0;
						    $quotes_arr['transaction_id']        = $transaction_id or '';
						    $quotes_arr['total_retail_price']    = num_format($total_retail_price);
						    $quotes_arr['total_wholesale_price'] = num_format($total_wholesale_price);
						    $quotes_arr['order_cancel_status']   = 0;

								$quotes_arr['shipping_addr']	      = $order_addr_data['shipping'];
								$quotes_arr['influencer_code']	      = $order_addr_data['influencer_code'];
						    $quotes_arr['promotion_discount']	  = $promo_discount_amount;
						    $quotes_arr['promo_code']	          = $promo_code;
						    
						    $quotes_arr['billing_addr']			  = $order_addr_data['billing'];
						    
						    $quotes_arr['payment_term']           = $payment_term;
						    $quotes_arr['is_direct_payment']      = $is_direct_payment;
						    
				        	$create_quote = $this->RetailerQuotesModel->create($quotes_arr);
								
				        	/*Store mapping data of retailer used promo code*/
				        	if (isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
				        	{

				        		$promo_codeId  = isset($promotion_session_data[$key]['promo_codeId'])?$promotion_session_data[$key]['promo_codeId']:0;


				        		$promo_mappping['retailer_id'] 		= $loggedInUserId;
				        		$promo_mappping['promo_code_id'] 	= $promo_codeId;

				        		$retailer_promo_mapping = $this->PromoCodeRetailerMappingModel->create($promo_mappping);
				        	
				        	}

				        	foreach($product_arr as $product)
				        	{ 
				        	
				        		$quote_product_arr = [];
				        		
				        		$quote_product_arr['retailer_quotes_id'] = $create_quote->id;
				        		$quote_product_arr['product_id']         = $product['product_id'];
				        		$quote_product_arr['sku_no']             = $product['sku_no'];
				        		$quote_product_arr['qty']                = $product['item_qty'];
				        		$quote_product_arr['retail_price']       = $product['retail_price'];
				        		$quote_product_arr['unit_wholsale_price']= $product['wholesale_price'];
				        		$quote_product_arr['wholesale_price']    = $product['wholesale_price'];
				        		$quote_product_arr['description']  		 = '';

				        	

			        			$quote_product_arr['shipping_charge']    = isset($product['shipping_charges'])?$product['shipping_charges']:0;
			        			$quote_product_arr['shipping_discount']  = isset($product['shipping_discount'])?$product['shipping_discount']:0;


				        		$quote_product_arr['product_discount']  = isset($product['product_discount_amount'])?$product['product_discount_amount']:0;
				        		
				        		$grand_total = $product['total_wholesale_price'] + $quote_product_arr['shipping_charge']-$quote_product_arr['shipping_discount']-$product['product_discount_amount']; 
				        	
				        		$quote_product_arr['wholesale_price']    = $grand_total;
				        		 	        		
				        		$create_quote_product = $this->RetailerQuotesProductModel->create($quote_product_arr);
				        		
				        	}

				        	$quote_id = $create_quote->id;



				         /******************Notification to Admin START*******************************/


					        $first_name = isset($user->first_name)?$user->first_name:"";
					        $last_name  = isset($user->last_name)?$user->last_name:"";  

					        $order_view_link = url('/').'/vendor/retailer_orders/view/'.base64_encode($create_quote->id);


					        $arr_event                 = [];
					        $arr_event['from_user_id'] = $loggedInUserId;
					        $arr_event['to_user_id']   = $key;

					       $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;
					        
					        $arr_event['title']        = 'New Order';
					        $arr_event['type']         = 'maker'; 
					        $arr_event['link']         = $order_view_link; 

					          
					        $this->save_notification($arr_event);
					        
				     	 $arr_email = $product_name = [];

				     	}
				     	

				     	    /* Get Admin Id and send notification to admin after order placing*/

					        $admin_id = get_admin_id();
									$order_id = $this->RetailerQuotesModel->select('id')->where('order_no',$order_no)->first()->id;
									
					        $view_href     =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_id);


					        $first_name = isset($user->first_name)?$user->first_name:"";
					        $last_name  = isset($user->last_name)?$user->last_name:"";  

					        $arr_event                 = [];
					        $arr_event['from_user_id'] = $loggedInUserId;
					        $arr_event['to_user_id']   = $admin_id;

					     /*   $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.'<a href="'.$view_href.'">'.$order_no.'</a>';*/

					        $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;

					        $arr_event['title']        = 'New Order';
					        $arr_event['type']         = 'admin'; 
					        $arr_event['link']         = $view_href; 
		

					        $this->save_notification($arr_event);

					        /*send purchase order mail to the vendors*/

					     	foreach ($result as $key1 => $product_data) 
					     	{

						     		$order = [];
						     	   
						     	   	$price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
						     	   	$arr_email = Sentinel::findById($key1)->email;
						     	   	$maker[$key1]['maker_id'] = Sentinel::findById($key1);

						     	   	foreach ($product_data as $key2 => $product) 
						     	   	{
						     	   		if (isset($promotion_session_data) && $promotion_session_data != null && isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
							        	{

							        		$promo_discount_amount  = isset($promotion_session_data[$key]['final_total'][$key]['discount_amt'])?$promotion_session_data[$key]['final_total'][$key]['discount_amt']:0;
							        		$promo_shipping_charges = isset($promotion_session_data[$key]['final_total'][$key]['shipping_charges'])?$promotion_session_data[$key]['final_total'][$key]['shipping_charges']:1;

							        		$promo_code  = isset($promotion_session_data[$key]['promo_code'])?$promotion_session_data[$key]['promo_code']:0;

							        	
							        	}
						     	   		$product_details              = get_product_details($product['product_id']);

						     	   		
						     	   		$product_name 				  = $product_details['product_name'];
						     	   		$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";        

						     	   		$order[$key2]['order_no']     = $order_no or '';
						     	   		$order[$key2]['product_name'] = $product_name.' (SKU:'.$sku_no.')';
						     	   		$order[$key2]['order_no']     = $order_no or '';
						     	   		$order[$key2]['item_qty']     = $product['item_qty'];
						     	   		$order[$key2]['unit_price']   = $product['wholesale_price'];
						     	   		$order[$key2]['total_wholesale_price'] = $product['total_wholesale_price'];
						     	   		
						     	   		
						     	   		$order[$key2]['product_discount_amount'] = $product['product_discount_amount'];

							     	   	       /*	if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

						     	   		    	$order[$key2]['shipping_discount'] = 0;
						     	   			}
						     	   			else{*/

						     	   				$order[$key2]['shipping_discount'] = $product['shipping_discount'];
						     	   			/*}

						     	   			if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

						     	   		    	$order[$key2]['shipping_charges']  = 0;
						     	   			}
						     	   			else{*/

						     	   				$order[$key2]['shipping_charges']  = $product['shipping_charges'];

						     	   		/*	}*/
				     	   		    
						     	   	}


						     	    $maker[$key1]['order_details']= $order;
						     	    
						     	    
						     	    $sum = 0;
						     	    foreach ($order as $key => $order_data) 
						     	   	{
									   $sum += $order_data['total_wholesale_price'];
									   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
								   	}

						     	    $maker[$key1]['email_id'] = $arr_email;
						     	

				                    //Create pdf here for maker

				                    $retailer_data = $this->RetailerQuotesModel 
				                    					  ->with(['quotes_details',])
				                    					  ->where('order_no',$order_no)
				                    					  ->first()
				 										  ->toArray();

					  			   	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {
					  	     	   			
					       	   			$sum = $sum - $promo_discount_amount;
					       	   			$retailer_data['promo_discount'] = $promo_discount_amount;
					       	   		}
					       	   		else{

					       	   			$sum = $sum;
					       	   		}

				 					$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

				 					$retailer_data['store_name'] = isset($store_name)?$store_name:'';
									 
				 					$retailer_data['user_details'] = $this->UserModel
				 										  ->with(['retailer_details'])	
				                    					  ->where('id',$retailer_data['retailer_id'])
				                    					  ->first()
				 										  ->toArray();


				 					$order_date = '';					  

				 					$maker_id = $maker[$key1]['maker_id'];

				 					
				 					$maker_addr = $this->UserModel->where('id',$key1)
														  ->first()
				 										  ->toArray();
					 	  			
					 	  			$company_name = get_maker_company_name($key1);
					 	  			$maker_addr['company_name'] = isset($company_name)?$company_name:'';

		                            if(isset($promotion_session_data) && count($promotion_session_data)>0)
		                            {
		                            	if(isset($promotion_session_data[$key1]))
		                            	{
		                                    $order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data[$key1],$key1);
		                            	}
		                            	else
		                            	{
		                            		$order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data);
		                            	}
		                               
		                            }
		                            else
		                            {
		                               $order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data);
		                            }
			                   
		                            $hide_retailer_addr_from_vendor = 1;
					 	  			$pdf = PDF::loadView('front/invoice/vendor_purchase_order_invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','order_details','hide_retailer_addr_from_vendor'));
					 	  		

					 	  			$currentDateTime = $order_no.date('H:i:s').'.pdf';

					 	  			$pdf_arr = 	[
								    				'PDF'           => $pdf,
								            		'PDF_FILE_NAME' => $currentDateTime
								               	];


				                    // Send Mail To maker 
				                    $to_mail_id = isset($maker[$key1]['email_id'])?$maker[$key1]['email_id']:"";

				                    $credentials = ['email' => $to_mail_id];
                                    
                                    $arr_user = get_user_by_credentials($credentials);


                                    /*Get site setting data from helper*/
                                    
                                    $arr_site_setting = get_site_settings(['site_name','website_url']);
																    

                                    $arr_built_content = [
							                                'PROJECT_NAME' => $arr_site_setting['site_name']
							                             ];

						        
                                    $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'43',$arr_user);

                                    $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

					     	}

					     	/*send purchase order mail to retailer*/

		                    $retailer_mail = $user->email;			     	 
		
					     	$this->send_retailer_mail($retailer_mail,$pro_arr,$order_no,$file_to_path,$maker_addr,$retailer_data);


		                    /*send retailer purchase order mail to admin*/

		                    $admin_email = 0;

		                    $admin_details = $this->UserModel->where('id',1)->first();

		                    if(isset($admin_details))
		                    {
		                       $admin_mail = $admin_details->email;
		                    }

		                    $this->send_retailer_mail($admin_mail,$pro_arr,$order_no,$file_to_path,$maker_addr,$retailer_data);


					     	$ip_address = \Request::ip();
					     	$session_id = session()->getId();

					        // $is_deleted = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->delete();
					        $is_deleted = $this->MyCartService->delete();

	                
					    	DB::commit();
					    	$response_arr['status']   = 'success';
					    	$response_arr['quote_id'] = $quote_id;
					    	
					    	return $response_arr;
		        	}
		        	else
		        	{	
		        		$response_arr['status']   = 'failure';
		        		
		        		return $response_arr;
		        	}
	            }catch(Exception $e)
		        {
		        	DB::roleback();
		        	$response_arr['status']   = 'failure';
		        	
		        	return $response_arr;
		        }    
	        }
        }
		else
		{
				
	        $quotes_arr    = [];
	        $payment_term = '';
  
	    	$payment_term = 'Offline';

		    if(!Session::has('bag_data'))
		    {
		        $response_arr['status']   = 'failure';
		        return $response_arr;
		    }
	       

	        $loggedInUserId = $quote_id = 0;
	        $user = Sentinel::check();	        

	        if($user && $user->inRole('retailer'))
	        {
	           $loggedInUserId = $user->id;
	        }	        
	        else
	        {
	        	$response_arr['status']   = 'failure';        		
	        	return $response_arr;
	        } 
	         
	        $ip_address = \Request::ip();
	        $session_id = session()->getId();

            // $bag_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)
	    								   // ->first(['product_data']);
			$bag_data = $this->MyCartService->get_items();

			if($bag_data)
			{
				$bag_data->toArray();
	    		$bag_data  = isset($bag_data['product_data'])?$bag_data['product_data']:"";
			}

	    	
	        $bag_arr   = json_decode($bag_data,true);
	      	// dd($bag_data);
	       	$arr_product = $bag_arr['sku'];

	        
	       $pro_arr = 	$arr_product;	
	      
	        $result = $promo_mappping = [];


	        if(isset($arr_product) && count($arr_product) > 0)
	        {
	        	foreach ($arr_product as $key => $value) 
		        {
		        	$result[$value['maker_id']][] = $value;
		        }
	        }
	       
	        try
	        {

	        	$promotion_session_data = [];
	        	if (is_array(Session::get('promotion_data')) && count(Session::get('promotion_data')) > 0) 
	        	{
	        		$promotion_session_data = Session::get('promotion_data');
	        	}

	        	DB::beginTransaction();
	        	if(count($result)>0)
	        	{
	        	
	        		$order_no = str_pad('J2',  10, rand('1234567890',10)); 

	        		$order_no = $this->orderDataService->verify_order_no($order_no);
	        		
	        		$all_product_arr = [];
	        		$promo_discount_amount = $promo_codeId = $promo_code = 0;
	        		foreach ($result as $key => $product_arr) 
			        {

			        	if (isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
			        	{

			        		$promo_discount_amount  = isset($promotion_session_data[$key]['final_total'][$key]['discount_amt'])?$promotion_session_data[$key]['final_total'][$key]['discount_amt']:0;
			        		$promo_shipping_charges = isset($promotion_session_data[$key]['final_total'][$key]['shipping_charges'])?$promotion_session_data[$key]['final_total'][$key]['shipping_charges']:1;

			        		$promo_code  = isset($promotion_session_data[$key]['promo_code'])?$promotion_session_data[$key]['promo_code']:0;

			        		array_push($all_product_arr,$product_arr);
			        	
			        		$total_retail_price = $total_wholesale_price = 0;
			        	
			        		$total_retail_price = array_sum(array_column($product_arr,'total_price'));

			        		$total_price = array_sum(array_column($product_arr,'total_wholesale_price'));

			        		$total_wholesale_price = $total_price - $promo_discount_amount;

			        	
			        	}
			        	else{

			        		$promo_discount_amount = 0;
			        		$promo_shipping_charges = 1;

			        		$promo_code = '';
			        		array_push($all_product_arr,$product_arr);
			        	
				        	$total_retail_price = $total_wholesale_price = 0;
				        	
				        	$total_retail_price = array_sum(array_column($product_arr,'total_price'));

				        	$total_price = array_sum(array_column($product_arr,'total_wholesale_price'));

				        	$total_wholesale_price = $total_price;
			        	}			        	
			        	
			        	$pro_ship_charge =0;
			        	$pro_ship_dis =0;
			        	$pro_dis = 0;
			        	foreach ($product_arr as $pro_key => $pro) 
			        	{
									//dd($product_arr);
			        		if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

			        			$pro_ship_charge 	+= 0;
			        			$pro_ship_dis 		+= 0;

			        		}
			        		else{

			        			$pro_ship_charge 	+= isset($pro['shipping_charges'])?$pro['shipping_charges']:0;
			        			$pro_ship_dis 		+= isset($pro['shipping_discount'])?$pro['shipping_discount']:0;
			        		}
			        		
			        		$pro_dis 			+= isset($pro['product_discount_amount'])?$pro['product_discount_amount']:0;

			        	}

			        	
			        	
			         	$total_wholesale_price = $total_wholesale_price+$pro_ship_charge-$pro_ship_dis-$pro_dis;

			         	$is_direct_payment = $this->MakerModel->where('user_id',$key)->first();
			         	if(isset($is_direct_payment))
			         	{
			         		$temp_vendor_data_arr = $is_direct_payment->toArray();
			         		$is_direct_payment = isset($temp_vendor_data_arr['is_direct_payment'])?$temp_vendor_data_arr['is_direct_payment']:0;
			         	}


			        	$quotes_arr = [];
			        	$quotes_arr['maker_id']              = $key;
			        	$quotes_arr['order_no']              = $order_no;
			        	$quotes_arr['admin_commission']      = $this->CommissionService->get_admin_commission($key);
								$quotes_arr['retailer_id']           = $loggedInUserId;
								$quotes_arr['status']                = 0;
								$quotes_arr['transaction_id']        = $transaction_id or '';
								$quotes_arr['total_retail_price']    = num_format($total_retail_price);
								$quotes_arr['total_wholesale_price'] = num_format($total_wholesale_price);
								$quotes_arr['order_cancel_status']   = 0;

								$quotes_arr['shipping_addr']	      = $order_addr_data['shipping'];
								$quotes_arr['promotion_discount']	  = $promo_discount_amount;
								$quotes_arr['promo_code']	          = $promo_code;
								
								$quotes_arr['billing_addr']			  = $order_addr_data['billing'];
								$quotes_arr['influencer_code']			  = $order_addr_data['influencer_code'];
								$quotes_arr['payment_term']           = $payment_term;
								$quotes_arr['is_direct_payment']      = $is_direct_payment;
					     
			        	$create_quote = $this->RetailerQuotesModel->create($quotes_arr);

								 

			        	/*Store mapping data of retailer used promo code*/
			        	if (isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
			        	{

			        		$promo_codeId  = isset($promotion_session_data[$key]['promo_codeId'])?$promotion_session_data[$key]['promo_codeId']:0;


			        		$promo_mappping['retailer_id'] 		= $loggedInUserId;
			        		$promo_mappping['promo_code_id'] 	= $promo_codeId;

			        		$retailer_promo_mapping = $this->PromoCodeRetailerMappingModel->create($promo_mappping);
			        	
			        	}

			        	foreach($product_arr as $product)
			        	{ 
			        	
			        		$quote_product_arr = [];
			        		
			        		$quote_product_arr['retailer_quotes_id'] = $create_quote->id;
			        		$quote_product_arr['product_id']         = $product['product_id'];
									$quote_product_arr['sku_no']             = $product['sku_no'];
									$quote_product_arr['color']             = $product['color'];
									$quote_product_arr['size_id']             = $product['size_id'];
			        		$quote_product_arr['qty']                = $product['item_qty'];
			        		$quote_product_arr['retail_price']       = $product['retail_price'];
			        		$quote_product_arr['unit_wholsale_price']= $product['wholesale_price'];
			        		$quote_product_arr['wholesale_price']    = $product['wholesale_price'];
			        		$quote_product_arr['description']  		 = '';

			        		/*if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

			        			
			        			$quote_product_arr['shipping_charge']    = 0;
			        			$quote_product_arr['shipping_discount']  = 0;

			        		}

			        		else{*/

			        			$quote_product_arr['shipping_charge']    = isset($product['shipping_charges'])?$product['shipping_charges']:0;
			        			$quote_product_arr['shipping_discount']  = isset($product['shipping_discount'])?$product['shipping_discount']:0;

			        		// }

			        		$quote_product_arr['product_discount']  = isset($product['product_discount_amount'])?$product['product_discount_amount']:0;
			        		
			        		$grand_total = $product['total_wholesale_price'] + $quote_product_arr['shipping_charge']-$quote_product_arr['shipping_discount']-$product['product_discount_amount']; 
			        	
			        		$quote_product_arr['wholesale_price']    = $grand_total;
			        		 	        		
			        		$create_quote_product = $this->RetailerQuotesProductModel->create($quote_product_arr);
			        		
			        	}

			        	$quote_id = $create_quote->id;
								


			         /******************Notification to Admin START*******************************/
			         	$userName = "";
			         	if($user && $user->inRole('retailer'))
	        			{
	        				$userName = get_retailer_dummy_shop_name($loggedInUserId);
	        				
	        			} else {
	        				$first_name = isset($user->first_name)?$user->first_name:"";
				        	$last_name  = isset($user->last_name)?$user->last_name:"";  
	        				$userName = $first_name.' '.$last_name;
	        			}
				        

				        $order_view_link = url('/').'/vendor/retailer_orders/view/'.base64_encode($create_quote->id);


				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $key;

				       $arr_event['description']  = 'New order placed from '.$userName.'. Order No : '.$order_no;
				        
				        $arr_event['title']        = 'New Order';
				        $arr_event['type']         = 'maker'; 
				        $arr_event['link']         = $order_view_link; 

				          
				        $this->save_notification($arr_event);
				        
			     	 $arr_email = $product_name = [];
								
			     	}
			     	

			     	    /* Get Admin Id and send notification to admin after order placing*/

				        $admin_id = get_admin_id();
				        $order_id = $this->RetailerQuotesModel->select('id')->where('order_no',$order_no)->first()->id;
				        $view_href     =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_id);


				        $first_name = isset($user->first_name)?$user->first_name:"";
				        $last_name  = isset($user->last_name)?$user->last_name:"";  

				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $admin_id;

				     /*   $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.'<a href="'.$view_href.'">'.$order_no.'</a>';*/

				        $arr_event['description']  = 'New order placed from '.$first_name.' '.$last_name.' . Order No : '.$order_no;

				        $arr_event['title']        = 'New Order';
				        $arr_event['type']         = 'admin'; 
				        $arr_event['link']         = $view_href; 
	

				        $this->save_notification($arr_event);

				    /*Send purchase order mail to vendors */

			     	foreach ($result as $key1 => $product_data) 
			     	{
							
				     		$order = [];
				     	   
				     	   	$price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
				     	   	$arr_email = Sentinel::findById($key1)->email;
				     	   	$maker[$key1]['maker_id'] = Sentinel::findById($key1);

				     	   	foreach ($product_data as $key2 => $product) 
				     	   	{
				     	   		if (isset($promotion_session_data) && $promotion_session_data != null && isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
					        	{

					        		$promo_discount_amount  = isset($promotion_session_data[$key]['final_total'][$key]['discount_amt'])?$promotion_session_data[$key]['final_total'][$key]['discount_amt']:0;
					        		$promo_shipping_charges = isset($promotion_session_data[$key]['final_total'][$key]['shipping_charges'])?$promotion_session_data[$key]['final_total'][$key]['shipping_charges']:1;

					        		$promo_code  = isset($promotion_session_data[$key]['promo_code'])?$promotion_session_data[$key]['promo_code']:0;

					        	
					        	}
				     	   		$product_details              = get_product_details($product['product_id']);

				     	   		
				     	   		$product_name 				  = $product_details['product_name'];
				     	   		$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";        

				     	   		$order[$key2]['order_no']     = $order_no or '';
				     	   		$order[$key2]['product_name'] = $product_name.' (SKU:'.$sku_no.')';
				     	   		$order[$key2]['order_no']     = $order_no or '';
				     	   		$order[$key2]['item_qty']     = $product['item_qty'];
				     	   		$order[$key2]['color']     = $product['color'];
				     	   		$order[$key2]['size']     = get_size_from_id($product['size_id']);
				     	   		$order[$key2]['unit_price']   = $product['wholesale_price'];
				     	   		$order[$key2]['total_wholesale_price'] = $product['total_wholesale_price'];
				     	   	
				     	   	
				     	   		$order[$key2]['product_discount_amount'] = $product['product_discount_amount'];

					     	   	       /*	if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

				     	   		    	$order[$key2]['shipping_discount'] = 0;
				     	   			}
				     	   			else{*/

				     	   				$order[$key2]['shipping_discount'] = $product['shipping_discount'];
				     	   			/*}

				     	   			if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

				     	   		    	$order[$key2]['shipping_charges']  = 0;
				     	   			}
				     	   			else{*/

				     	   				$order[$key2]['shipping_charges']  = $product['shipping_charges'];

				     	   		/*	}*/
		     	   		    
				     	   	}


				     	    $maker[$key1]['order_details']= $order;
				     	    
				     	    
				     	    $sum = 0;
				     	    foreach ($order as $key => $order_data) 
				     	   	{
							   $sum += $order_data['total_wholesale_price'];
							   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
						   	}

				     	    $maker[$key1]['email_id'] = $arr_email;
				     	
		                    //Create pdf here for maker

		                    $retailer_data = $this->RetailerQuotesModel 
		                    					  ->with(['quotes_details',])
		                    					  ->where('order_no',$order_no)
		                    					  ->first()
		 										  ->toArray();

			  			   	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {
			  	     	   			
			       	   			$sum = $sum - $promo_discount_amount;
			       	   			$retailer_data['promo_discount'] = $promo_discount_amount;
			       	   		}
			       	   		else{

			       	   			$sum = $sum;
			       	   		}

		 					//$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

		 					$store_name = get_retailer_dummy_shop_name($retailer_data['retailer_id']);

		 					$retailer_data['store_name'] = isset($store_name)?$store_name:'';

		 					$retailer_data['user_details'] = $this->UserModel
		 										  ->with(['retailer_details'])	
		                    					  ->where('id',$retailer_data['retailer_id'])
		                    					  ->first()
		 										  ->toArray();


		 					$order_date = '';					  

		 					$maker_id = $maker[$key1]['maker_id'];

		 					
		 					$maker_addr = $this->UserModel->where('id',$key1)
							 ->first()
							 ->toArray();
			 	  			
			 	  			$company_name = get_maker_company_name($key1);
			 	  			$maker_addr['company_name'] = isset($company_name)?$company_name:'';

                            if(isset($promotion_session_data) && count($promotion_session_data)>0)
                            {
                            	if(isset($promotion_session_data[$key1]))
                            	{
                                    $order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data[$key1],$key1);
                            	}
                            	else
                            	{
                            		$order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data);
                            	}
                               
                            }
                            else
                            {
                               $order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data);
                            }
	                    
														 $hide_retailer_addr_from_vendor = 1;
														 
			 	  			$pdf = PDF::loadView('front/invoice/vendor_purchase_order_invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','order_details','hide_retailer_addr_from_vendor'));
							
			 	  			$currentDateTime = $order_no.date('H:i:s').'.pdf';


                            $pdf_arr = 	[
							    					'PDF'           => $pdf,
							            	'PDF_FILE_NAME' => $currentDateTime
							            ];

													
		     	 			$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;

								// Send Mail To maker 
								$to_mail_id = isset($maker[$key1]['email_id'])?$maker[$key1]['email_id']:"";
								$credentials = ['email' => $to_mail_id];

								$arr_user = get_user_by_credentials($credentials);


								/*Get site setting data from helper*/

								$arr_site_setting = get_site_settings(['site_name','website_url']);
								

								$influencer_code = $retailer_data['influencer_code'];
								$influencer_details = [];
								$arr_built_content = [];

								if(isset($influencer_code) && $influencer_code != null){

									$influencer_details = $this->UserModel->where('influencer_code',$influencer_code)->first()->toArray();
									$arr_built_content = [
										'PROJECT_NAME' => $arr_site_setting['site_name'],
										'INFLUENCER_CODE' => $retailer_data['influencer_code'],
										'INFLUENCER_NAME' => $influencer_details['first_name'],
									];
								}else {
									
									$arr_built_content = [
										'PROJECT_NAME' => $arr_site_setting['site_name'],
										'INFLUENCER_CODE' => 'N/A',
										'INFLUENCER_NAME' => ' ',
									];
								}

								$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'43',$arr_user);
								
								$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
							
						 }
						 
						 /*Send purchase order mail to Influencer */

			     	foreach ($result as $key1 => $product_data) 
			     	{
							
				     		$order = [];
				     	   
				     	   	$price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
				     	   	$arr_email = Sentinel::findById($key1)->email;
				     	   	$maker[$key1]['maker_id'] = Sentinel::findById($key1);

				     	   	foreach ($product_data as $key2 => $product) 
				     	   	{
				     	   		if (isset($promotion_session_data) && $promotion_session_data != null && isset($promotion_session_data[$key]) && count($promotion_session_data[$key]) > 0) 
					        	{

					        		$promo_discount_amount  = isset($promotion_session_data[$key]['final_total'][$key]['discount_amt'])?$promotion_session_data[$key]['final_total'][$key]['discount_amt']:0;
					        		$promo_shipping_charges = isset($promotion_session_data[$key]['final_total'][$key]['shipping_charges'])?$promotion_session_data[$key]['final_total'][$key]['shipping_charges']:1;

					        		$promo_code  = isset($promotion_session_data[$key]['promo_code'])?$promotion_session_data[$key]['promo_code']:0;

					        	
					        	}
				     	   		$product_details              = get_product_details($product['product_id']);

				     	   		
				     	   		$product_name 				  = $product_details['product_name'];
				     	   		$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";        

				     	   		$order[$key2]['order_no']     = $order_no or '';
				     	   		$order[$key2]['product_name'] = $product_name.' (SKU:'.$sku_no.')';
				     	   		$order[$key2]['order_no']     = $order_no or '';
				     	   		$order[$key2]['item_qty']     = $product['item_qty'];
				     	   		$order[$key2]['unit_price']   = $product['wholesale_price'];
				     	   		$order[$key2]['total_wholesale_price'] = $product['total_wholesale_price'];
				     	   		
				     	   		
				     	   		$order[$key2]['product_discount_amount'] = $product['product_discount_amount'];

					     	   	       /*	if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

				     	   		    	$order[$key2]['shipping_discount'] = 0;
				     	   			}
				     	   			else{*/

				     	   				$order[$key2]['shipping_discount'] = $product['shipping_discount'];
				     	   			/*}

				     	   			if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

				     	   		    	$order[$key2]['shipping_charges']  = 0;
				     	   			}
				     	   			else{*/

				     	   				$order[$key2]['shipping_charges']  = $product['shipping_charges'];

				     	   		/*	}*/
		     	   		    
				     	   	}


				     	    $maker[$key1]['order_details']= $order;
				     	    
				     	    
				     	    $sum = 0;
				     	    foreach ($order as $key => $order_data) 
				     	   	{
							   $sum += $order_data['total_wholesale_price'];
							   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
						   	}

				     	    $maker[$key1]['email_id'] = $arr_email;
				     	
		                    //Create pdf here for maker

		                    $retailer_data = $this->RetailerQuotesModel 
		                    					  ->with(['quotes_details',])
		                    					  ->where('order_no',$order_no)
		                    					  ->first()
		 										  ->toArray();

			  			   	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {
			  	     	   			
			       	   			$sum = $sum - $promo_discount_amount;
			       	   			$retailer_data['promo_discount'] = $promo_discount_amount;
			       	   		}
			       	   		else{

			       	   			$sum = $sum;
			       	   		}

		 					//$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

		 					$store_name = get_retailer_dummy_shop_name($retailer_data['retailer_id']);

		 					$retailer_data['store_name'] = isset($store_name)?$store_name:'';

		 					$retailer_data['user_details'] = $this->UserModel
		 										  ->with(['retailer_details'])	
		                    					  ->where('id',$retailer_data['retailer_id'])
		                    					  ->first()
		 										  ->toArray();


		 					$order_date = '';					  

		 					$maker_id = $maker[$key1]['maker_id'];

		 					
		 					$maker_addr = $this->UserModel->where('id',$key1)
							 ->first()
							 ->toArray();
			 	  			
			 	  			$company_name = get_maker_company_name($key1);
			 	  			$maker_addr['company_name'] = isset($company_name)?$company_name:'';

                            if(isset($promotion_session_data) && count($promotion_session_data)>0)
                            {
                            	if(isset($promotion_session_data[$key1]))
                            	{
                                    $order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data[$key1],$key1);
                            	}
                            	else
                            	{
                            		$order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data);
                            	}
                               
                            }
                            else
                            {
                               $order_details  = $this->get_vendor_order_details($product_data,$promotion_session_data);
                            }
	                    
														 $hide_retailer_addr_from_vendor = 1;
														 
			 	  			$pdf = PDF::loadView('front/invoice/vendor_purchase_order_invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','order_details','hide_retailer_addr_from_vendor'));
								 
			 	  			$currentDateTime = $order_no.date('H:i:s').'.pdf';


                            $pdf_arr = 	[
							    					'PDF'           => $pdf,
							            	'PDF_FILE_NAME' => $currentDateTime
							            ];

													
		     	 			$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;

		                    // Send Mail To maker 
		                    $to_mail_id = isset($maker[$key1]['email_id'])?$maker[$key1]['email_id']:"";


		                    $credentials = ['email' => $to_mail_id];
      
												/*Get site setting data from helper*/

												$arr_site_setting = get_site_settings(['site_name','website_url']);
												
												if(isset($influencer_code) && $influencer_code != null){

													$influencer_details = [];
													$arr_built_content = [];

													$influencer_code = $retailer_data['influencer_code'];
													$arr_user = $this->UserModel->where('influencer_code',$influencer_code)->first()->toArray();
												
													$arr_built_content = [
														'PROJECT_NAME' => $arr_site_setting['site_name'],
														'INFLUENCER_CODE' => $retailer_data['influencer_code'],
														'INFLUENCER_NAME' => $arr_user['first_name'],
														'ORDER_NO' => $retailer_data['order_no']
														
													];
													
													$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'87',$arr_user);

												$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

												}
			     	}
						 
			     	/* send retailer mail */

			     	$hide_vendor_addr_from_vendor = 1;
                    $retailer_mail = $user->email;			     	 
 
			     	$this->send_retailer_mail($retailer_mail,$pro_arr,$order_no,$file_to_path,$maker_addr,$retailer_data,$hide_vendor_addr_from_vendor);

                    /*send the mail to admin*/

                    $admin_email = 0;

                    $admin_details = $this->UserModel->where('id',1)->first();

                    if(isset($admin_details))
                    {
                       $admin_mail = $admin_details->email;
                    }
                    $store_name_for_admin = get_retailer_shop_name($retailer_data['retailer_id']);
										$retailer_data['store_name'] = isset($store_name_for_admin)?$store_name_for_admin:'';
										$retailer_data['influencer_code'] = $quotes_arr['influencer_code'];
										
                    $this->send_retailer_mail($admin_mail,$pro_arr,$order_no,$file_to_path,$maker_addr,$retailer_data);


			     	$ip_address = \Request::ip();
			     	$session_id = session()->getId();

			        // $is_deleted = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->delete();
			        $is_deleted = $this->MyCartService->delete();

                
			    	DB::commit();
			    	$response_arr['status']   = 'success';
			    	$response_arr['quote_id'] = $quote_id;
			    	
			    	return $response_arr;
	        	}
	        	else
	        	{	
	        		$response_arr['status']   = 'failure';
	        		
	        		return $response_arr;
	        	}
            }catch(Exception $e)
	        {
	        	DB::roleback();
	        	$response_arr['status']   = 'failure';
	        	
	        	return $response_arr;
	        }    
        
		}
        
		
	}

	public function send_retailer_mail($to_mail_id,$product_arr,$order_no,$file_to_path=false,$maker_addr=false,$retailer_data,$hide_vendor_addr_from_vendor = false)
	{	
		$user = Sentinel::check();
		
		$loggedIn_userId = 0;

		if($user)
		{
		    $loggedIn_userId = $user->id;
		} 


		/*if admin is there so change the template*/

        $user_id = $this->UserModel->where('email',$to_mail_id)->pluck('id')->first();
 

        $store_name = get_retailer_shop_name($retailer_data['retailer_id']);
        $retailer_data['store_name'] = isset($store_name)?$store_name:'';


		$arr_product = $product_arr;
		$order_no = $order_no;
		$file_to_path = $file_to_path;
		$order_summary= $promotion_session_data = [];
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = $promotion_discount = $promotion_discount_percent = 0;

     	if(isset($arr_product) && count($arr_product)>0)
     	{
     	   	foreach ($arr_product as $key2 => $product) 
     	   	{
     	   		$product_details              = get_product_details($product['product_id']);
     	   		$product_name                 = $product_details['product_name'];
     	   		$order[$key2]['product_name'] = $product_name;
     	   		$order[$key2]['order_no']     = $order_no or '';
     	   		$order[$key2]['item_qty']     = $product['item_qty'];
     	   		$order[$key2]['unit_price']   = $product['wholesale_price'];
     	   		$order[$key2]['total_wholesale_price'] = $product['wholesale_price'];

     	   		$order[$key2]['product_discount_amount'] = $product['product_discount_amount'];

     	   		$order[$key2]['shipping_discount'] = $product['shipping_discount'];

     	   		$order[$key2]['shipping_charges']  = $product['shipping_charges'];
     	   		array_push($order_summary,$order[$key2]);
     	   	}

     	}


        
     	$credentials = ['email' => $to_mail_id];
      
        $arr_user = get_user_by_credentials($credentials);


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);

        //create pdf for retailer
				
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;
        $promo_discount_percent  = 0;

 		foreach ($arr_product as $key => $product_data) 
 		{  
 			$promotion_session_data = Session::get('promotion_data');

 			if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
        	{

        		$promo_discount_amount  = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt']:0;

        		$promo_discount_percent  = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_percent'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_percent']:0;

        		$promo_shipping_charges = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges']:1;

        		$promo_code  = isset($promotion_session_data[$product_data['maker_id']]['promo_code'])?$promotion_session_data[$product_data['maker_id']]['promo_code']:0;

        	
        	}

        	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {

        		$sum += $product_data['total_wholesale_price'] - $promo_discount_amount;
        	}
        	else{

        		$sum += $product_data['total_wholesale_price'];
        	}
			
			
			$product_details = get_product_details( $product_data['product_id']);
			
 	   		$product_name = $product_details['product_name'];
 	   		$sku_no = isset($product_data['sku_no'])?$product_data['sku_no']:"-";

			$arr_product[$key]['unit_price']  = num_format($product_data['wholesale_price'], 2, '.', '');
			$arr_product[$key]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
			$arr_product[$key]['shipping_charges'] = $product_details['shipping_charges'];

			$arr_product[$key]['shipping_type'] = $product_details['shipping_type'];

			$arr_product[$key]['minimum_amount_off'] = $product_details['minimum_amount_off']; 
			$arr_product[$key]['maker_company_name'] = get_maker_company_name($product_data['maker_id']); 

			if($arr_product[$key]['unit_price']>=$arr_product[$key]['minimum_amount_off'])
			{
             	/*if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
             	{
             		$shipping_charges += 0;
             	}
             	else{*/
             		$shipping_charges += isset($arr_product[$key]['shipping_charges']) &&$arr_product[$key]['shipping_charges']!=''?$arr_product[$key]['shipping_charges']:0;
             	//}
				

				$sum = $sum-$shipping_charges;
			}

			$total_sum = $sum+$shipping_charges;

			$arr_product[$key]['product_discount_amount'] = $product_data['product_discount_amount'];

		/*	if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
         	{
         		$arr_product[$key]['shipping_discount'] = 0;

 	   			$arr_product[$key]['shipping_charges']  = 0;
         	}
         	else{*/
         		$arr_product[$key]['shipping_discount'] = $product_data['shipping_discount'];

 	   			$arr_product[$key]['shipping_charges']  = $product_data['shipping_charges'];
         	//}

 	   		/*Add promotion code data in arr_product*/
 	   		
 	   		
 	   			if(isset($promotion_session_data) && count($promotion_session_data) > 0)
 	   			{
	 	   			foreach ($promotion_session_data as $promoKey => $promotion)
		            { 
		            	// dd($promoKey,$promotion,$promotion['final_total'][$product_data['maker_id']]['discount_amt']);
		            	
		               // $arr_product[$key]['prod_promotion_discount_amount'] = isset($promotion['final_total'][$product_data['maker_id']]['discount_amt'])?$promotion['final_total'][$product_data['maker_id']]['discount_amt']:0;
		 				
		 				$arr_product[$key]['prod_promotion_discount_percent'] = isset($promotion['final_total'][$product_data['maker_id']]['discount_percent'])?$promotion['final_total'][$product_data['maker_id']]['discount_percent']:0;

		 				$arr_product[$key]['prod_promotion_discount_amount'] = 0;
		 				if(isset($arr_product[$key]['prod_promotion_discount_percent']) && $arr_product[$key]['prod_promotion_discount_percent'] != 0)
		 				{

     	   					$sub_total_amt = $product_data['item_qty'] * $product_data['wholesale_price'];

		 					$total_gross_amt = $sub_total_amt + $product_data['shipping_charges'] - $product_data['shipping_discount'] - $product_data['product_discount_amount'];


		 					$arr_product[$key]['prod_promotion_discount_amount'] = num_format($total_gross_amt*($arr_product[$key]['prod_promotion_discount_percent'])/100);
		 				}

		 				$arr_product[$key]['prod_promotion_free_shipping'] = 0;
		 				if(isset($promotion['final_total'][$product_data['maker_id']]['shipping_charges']) && $promotion['final_total'][$product_data['maker_id']]['shipping_charges'] == 0)
		 				{
		 					$promo_is_free = is_promocode_freeshipping($promotion['promo_code']);

		 					if($promo_is_free == true)
		 					{		 						
		 					 	$arr_product[$key]['prod_promotion_free_shipping'] = num_format($product_data['shipping_charges']-$product_data['shipping_discount']);
		 					}
		 					else
		 					{
		 						$arr_product[$key]['prod_promotion_free_shipping'] = '0';		
		 					}
		 				}
		 				else
		 				{
		 					$arr_product[$key]['prod_promotion_free_shipping'] = '0';		
		 				}
		            }
		        }
        		
 	   		/*end*/
 	   		
 	   		array_push($order_summary,$order[$key2]);
 		}

 		if(isset($promo_discount_amount) && $promo_discount_amount != 0)
 		{
 			$promotion_discount = $promo_discount_amount;
 		}

 		if(isset($promo_discount_percent) && $promo_discount_percent != 0)
 		{
 			$promotion_discount_percent = $promo_discount_percent;
 		}


 		$order_details  = $this->get_order_details($arr_product,$promotion_session_data);

			
  		$order = $arr_product;
  		
  		$sno  = '0';
  		$role = 'Retailer';

  	 	   
  	    $pdf = PDF::loadView('front/invoice/retailer_purchase_order_invoice',compact('role','order','key','retailer_data','order_no','maker_addr','sum','sno','total_sum','shipping_charges','promotion_discount','order_details','promotion_discount_percent','hide_vendor_addr_from_vendor'));

        $currentDateTime = $order_no.date('H:i:s').'.pdf';
			

	    	$pdf_arr = 	[
	    			   		 'PDF'           => $pdf,
	            	   'PDF_FILE_NAME' => $currentDateTime
	               	];

   
        if($user_id == 1)
        { 
					
					$influencer_code = $retailer_data['influencer_code'];
					
					if(isset($influencer_code) && $influencer_code != null){

						$influencer_details = $this->UserModel->where('influencer_code',$influencer_code)->first()->toArray();
					

						$arr_built_content = [
							'PROJECT_NAME' => $arr_site_setting['site_name'],
							'order_no'     => $order_no,
							'INFLUENCER_CODE' => $retailer_data['influencer_code'],
							'INFLUENCER_NAME' => $influencer_details['first_name'],
					];

					} else{

						$arr_built_content = [
							'PROJECT_NAME' => $arr_site_setting['site_name'],
							'order_no'     => $order_no,
							'INFLUENCER_CODE' => 'N/A',
							'INFLUENCER_NAME' => ' ',
					 ];

					}
            $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'48',$arr_user);
	    		}
        		else{

						$arr_built_content = [
																		'PROJECT_NAME' => $arr_site_setting['site_name'],
																		'INFLUENCER_CODE' => $retailer_data['influencer_code'],
																 ];
																 

            $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'36',$arr_user); 
		}           	

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);  
       	

	}


	/************************Notification Event START**************************/

    public function save_notification($ARR_DATA = [])
    {  
        if(isset($ARR_DATA) && count($ARR_DATA)>0)
        {
            $ARR_EVENT_DATA                 = [];
            $ARR_EVENT_DATA['from_user_id'] = $ARR_DATA['from_user_id'];
            $ARR_EVENT_DATA['to_user_id']   = $ARR_DATA['to_user_id'];
            $ARR_EVENT_DATA['description']  = $ARR_DATA['description'];
            $ARR_EVENT_DATA['title']        = $ARR_DATA['title'];
           	$ARR_EVENT_DATA['type']         = $ARR_DATA['type'];
           	$ARR_EVENT_DATA['link']         = isset($ARR_DATA['link'])?$ARR_DATA['link']:'';

           	$ARR_EVENT_DATA['status']       = isset($ARR_DATA['status'])?$ARR_DATA['status']:0;	

            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }
        return false;
    }

    /************************Notification Event END  **************************/

    /*  Create transaction entry on transaction table  */

    public function save_transaction($arr_data)
    {
    	/* Generate random transaction id*/
        $transaction_id = str_random(10);
        /* redirect transaction details page*/
		$admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();
        $admin_id   = 0;
        $amount = session::get('amount');
        if($admin_obj)
        {
            $admin_id = $admin_obj->user_id;            
        }

        $user_id = 0;

        /* Check user login */
        if(Sentinel::check())
        {
            $user_id = Sentinel::check()->id;           
        }
  	
	    $trans_arr['paid_by']            = $user_id or '';
	    $trans_arr['transaction_id']     = $transaction_id or '' ;
	    $trans_arr['received_by']        = $admin_id or '';
	    $trans_arr['amount']             = $amount or '';
	    $trans_arr['payment_type']       = $arr_data['payment_type'] or '';

	    $transactions = $this->TransactionsModel->create($trans_arr);

	    return $transactions;

    }

	public function send_mail($to_mail_id=false,$arr_product,$order_no,$charge_status=false,$loggedInUserId,$order_id=false,$isDirectPayment=false,$hide_vendor_address_from_retailer = false)
	{
       
        $html = $order_by = $Role = '';
		$arr_email = $product_name = [];

		$retailer_data = $this->RetailerQuotesModel
		                      ->where('order_no',$order_no);
		                      if(isset($order_id) && $order_id!=false)
		                      {
		                      	$retailer_data = $retailer_data->where('id',$order_id);
		                      }


		        $retailer_data = $retailer_data->first();


		$role = ''; $Role = 'Retailer';

		if($retailer_data)
		{
			$retailer_data = $retailer_data->toArray();
			$role     = 'retailer';
			$order_by = 'retailer';
		
		}

		/*this else for if order not present in retailer quotes model means this order placed by representative.*/
		else
		{

			$retailer_data = $this->RepresentativeLeadsModel
			                      ->with('address_details',
			                      	     'representative_user_details',
			                      	     'sales_manager_details'
			                      	    )
			                      ->where('order_no',$order_no);

			                    if(isset($order_id) && $order_id!=false)
		                        {
		                      	   $retailer_data = $retailer_data->where('id',$order_id);
		                        }


		        $retailer_data = $retailer_data->first();

			                     

			if($retailer_data)
			{
			  $retailer_data = $retailer_data->toArray();

			  if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
			  {
                 $order_by = 'representative';
                 $Role     = "Representative";
			  }
			  elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
			  {
                 $order_by = 'sales_manager';
                 $Role     = "Sales Manager";
			  }
			  else
			  {
			    $order_by = '';	
			  }

			  $role = 'reps';
			  
			}
		}


        /*if admin is there so change the template*/

        $user_id = $this->UserModel->where('email',$to_mail_id)->pluck('id')->first();

      

	 	foreach ($arr_product as $key1 => $product_data) 
	 	{

	 	   $arr_email = Sentinel::findById($key1)->email;

	 	   foreach ($product_data as $key2 => $product) 
	 	   {
	 	   		$product_details              = get_product_details($product['product_id']);
	 	   		$product_name 				  = isset($product_details['product_name'])?$product_details['product_name']:'';
	 	   		$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['item_qty']     = isset($product['item_qty'])?$product['item_qty']:0;

	 	   		$order[$key2]['unit_price']   = isset($product['unit_price'])?$product['unit_price']:0;

	 	   		$order[$key2]['product_discount_amount']   = isset($product['product_discount'])?$product['product_discount']:0;

	 	   		$order[$key2]['shipping_discount']   = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	 	   		$order[$key2]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:0;

	 	   		$order[$key2]['deducted_amount'] = isset($product['total_wholesale_price'])?num_format($product['total_wholesale_price']):0;

	 	   		if (isset($role) && $role == 'reps') {

	 	   			$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges'] + $product['shipping_discount']:0;
	 	   		}
	 	   		else{

	 	   			$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:0;
	 	   		}
	 	   }
	 	   
	 	    $sum = 0;
		    $total_amount = $shipping_charges = $shipping_discount = $prod_discount = $deducted_amount = 0;
		    if (isset($role) && $role == 'reps') {

		    	foreach ($order as $key => $order_data) 
		     	{ 
					$sum += $order_data['total_wholesale_price'];
					$shipping_charges += $order_data['shipping_charges'];
					$shipping_discount += $order_data['shipping_discount'];
					$prod_discount += $order_data['product_discount_amount'];
					$deducted_amount += $order_data['deducted_amount'];

					$total_amount = $deducted_amount + $shipping_charges - $shipping_discount - $prod_discount;
					$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
			 	}
		    }
		    else{

		    	foreach ($order as $key => $order_data) 
		     	{ 
					$sum += $order_data['total_wholesale_price'];
					$total_amount += $order_data['deducted_amount'];
					$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
			 	}
		    }
			
	 	    $shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

	 	    $retailer_data['user_details'] = $this->UserModel
                        ->with(['retailer_details'])  
                                  ->where('id',$retailer_data['retailer_id'])
                                  ->first()
                        ->toArray();


	 	    $retailer_data['store_name'] = isset($shop_name)?$shop_name:'';
	 	    $retailer_data['charge_status'] = isset($charge_status)?$charge_status:'';

	 	    $maker_company_name = get_maker_company_name($loggedInUserId);

	 	    $maker[$key1]['order_details']= $order;
	 	  
	 	    $maker[$key1]['email_id'] = $arr_email;
	 	  	

	 	  	$maker_id = $retailer_data['maker_id'];

 			$maker_addr = $this->UserModel->where('id',$maker_id)
										  ->first()
 										  ->toArray();
	 	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';
	
	 	  	
	 	  	$ordNo = isset($order_no)?base64_encode($order_no):'';

			$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

			if(isset($order_by) && $order_by == 'representative')
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
			}
			elseif(isset($order_by) && $order_by == 'sales_manager')
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
			}
			else
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer'); 
			}
			
			$pdf = PDF::loadView('front/invoice',compact('Role','order','key','retailer_data','order_no','maker_addr','sum','order_by','orderCalculationData','hide_vendor_address_from_retailer'));
	 	  	
           	$currentDateTime = $order_no.date('H:i:s').'.pdf';
			

           	$credentials = ['email' => $to_mail_id];
      
        	$arr_user    = get_user_by_credentials($credentials);
           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);

			

		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];

	        if($user_id == 1)
	        {
	        	
                    if($isDirectPayment == 1)
                    {
                       $html = '<p>order no: '.$order_no.' has been confirmed successfully!</p>';
                    }
                    else
                    {
                       $html = '<p>order no: '.$order_no.' has been confirmed successfully!</p>';
                    }

	       	

					$arr_built_content = [
		 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
		                            'order_no'  		=> $order_no,
		                            'user_role'  		=> 'retailer',
		                            'deducted_amount' 	=> $retailer_data['total_wholesale_price'],
		                            'HTML'				=> $html
		                        ];

		            $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'74',$arr_user);

	        }
	        else
	        {

		        // Sendar Email From Database 
		        $arr_built_content = [
		 	  					   	    'PROJECT_NAME'      => $arr_site_setting['site_name'],
		                                'order_no'  		=> $order_no,
		                                'deducted_amount' 	=> $retailer_data['total_wholesale_price']
		                            ];

		        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'85',$arr_user);
	 	        
	        }

	     	$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

	 	 }
	}


   
	public function net30_payment_received_mail($to_mail_id=false,$arr_product,$order_no,$charge_status=false,$loggedInUserId,$order_id=false,$isDirectPayment=false,$role_slug=false)
	{ 
        $html = $role = $order_by ='';
 
		$total_amount = $shipping_charges = $shipping_discount = $prod_discount = $deducted_amount = $sum = 0;
        
        $arr_email = $product_name = $arr_mail_data = [];

		$retailer_data = $this->RetailerQuotesModel
		                      ->where('order_no',$order_no)
		                      ->where('id',$order_id)
		                      ->first();

        
		if($retailer_data)
		{
			$retailer_data = $retailer_data->toArray();
			$role     = 'Retailer';

			$order_by = 'retailer';
		
		}

		//this else for if order not present in retailer quotes model means this order placed by representative.
		else
		{

			$retailer_data = $this->RepresentativeLeadsModel
			                      ->with('address_details',
			                      	     'representative_user_details',
			                      	     'sales_manager_details')
			                      ->where('order_no',$order_no);

			                    if(isset($order_id) && $order_id!=false)
		                        {
		                      	   $retailer_data = $retailer_data->where('id',$order_id);
		                        }

            $retailer_data = $retailer_data->first();

			if($retailer_data)
			{
			    $retailer_data = $retailer_data->toArray();

			    if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
				{
	                $order_by = 'representative';
	                $Role     = "Representative";
				}
				elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
				{
	                $order_by = 'sales_manager';
	                $Role     = "Sales Manager";
				}
				else
				{
				    $order_by = '';	

				}

			    $role = 'reps';
			    
			}

		}

        $user_id = $this->UserModel->where('email',$to_mail_id)->pluck('id')->first();
  

	 	foreach ($arr_product as $key1 => $product_data) 
	 	{
            
	 	    $arr_email = Sentinel::findById($key1)->email;

	 	    foreach ($product_data as $key2 => $product) 
	 	    {
               
	 	   		$product_details              = get_product_details($product['product_id']);
	 	   		$product_name 				  = isset($product_details['product_name'])?$product_details['product_name']:'';

	 	   		$sku_no = isset($product['sku_no'])?$product['sku_no']:$product['sku'];

	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['item_qty']     = isset($product['item_qty'])?$product['item_qty']:0;
	 	   		
	 	   		$order[$key2]['unit_price']   = isset($product['unit_price'])?$product['unit_price']:0;

	 	   		$order[$key2]['product_discount_amount']   = isset($product['product_discount'])?$product['product_discount']:0;

	 	   		$order[$key2]['shipping_discount']   = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	 	   		$order[$key2]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:0;

	 	   		$order[$key2]['deducted_amount'] = isset($product['total_wholesale_price'])?num_format($product['total_wholesale_price']):0;

	 	   		if (isset($role) && $role == 'reps') {

	 	   			$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges'] + $product['shipping_discount']:0;
	 	   		}
	 	   		else{

	 	   			$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:0;
	 	   		}
	 	    }
	 	   
	 	   

		    if(isset($role) && $role == 'reps')
		    {

		    	foreach ($order as $key => $order_data) 
		     	{ 
					$sum += $order_data['total_wholesale_price'];
					$shipping_charges += $order_data['shipping_charges'];
					$shipping_discount += $order_data['shipping_discount'];
					$prod_discount += $order_data['product_discount_amount'];
					$deducted_amount += $order_data['deducted_amount'];

					$total_amount = $deducted_amount + $shipping_charges - $shipping_discount - $prod_discount;
					$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
			 	}
		    }
		    else
		    {
                foreach ($order as $key => $order_data) 
		     	{ 
					$sum += $order_data['total_wholesale_price'];
					$total_amount += $order_data['deducted_amount'];
					$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
			 	}
		    }
			
	 	    $shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

	 	    $retailer_data['user_details'] = $this->UserModel
                                                  ->with(['retailer_details'])  
                                                  ->where('id',$retailer_data['retailer_id'])
                                                  ->first()
                                                  ->toArray();


	 	    $retailer_data['store_name']    = isset($shop_name)?$shop_name:'';
	 	    $retailer_data['charge_status'] = isset($charge_status)?$charge_status:'';

	 	  

	 	    $maker[$key1]['order_details'] = $order;
	 	  
	 	    $maker[$key1]['email_id']      = $arr_email;
	 	  	

	 	  	$maker_id = $retailer_data['maker_id'];

 			$maker_addr = $this->UserModel->where('id',$maker_id)
										  ->first()
 										  ->toArray();

 			$maker_company_name = get_maker_company_name($maker_id);					
	 	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'
	 	  	';
			

			$ordNo = isset($order_no)?base64_encode($order_no):'';

			$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

			if(isset($order_by) && $order_by == 'representative')
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
			}
			elseif(isset($order_by) && $order_by == 'sales_manager')
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
			}
			else
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer'); 
			}

	 	  	$pdf = PDF::loadView('front/invoice',compact('Role','order','key','retailer_data','order_no','maker_addr','sum','order_by','orderCalculationData'));
	 	  	
           	$currentDateTime = $order_no.date('H:i:s').'.pdf';
			
			


			/*Get site setting data from helper*/
            $arr_site_setting = get_site_settings(['site_name','website_url']);

	        $credentials = ['email' => $to_mail_id];
      
            $arr_user = get_user_by_credentials($credentials);
			
		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];


	        if($role_slug == 'Admin')
	        {
   
				
                    if($isDirectPayment == 1)
                    {
                       $html = '<p>Net30 order payment has been done for order no: '.$order_no.' successfully! , <br /><label>$'.$retailer_data['total_wholesale_price'].'</label> deducted from customer account,</p>';
                    }
                    else
                    {
                       $html = '<p>Net30 order payment has been done for order no: '.$order_no.' successfully! , <br /><label>$'.$retailer_data['total_wholesale_price'].'</label> credited in your account,</p>';
                    }


                    $arr_built_content = [
				 	  						'User_Role'      => 'Admin',
				                            'HTML'           => $html,
				                            'PROJECT_NAME'   => $arr_site_setting['site_name']
		                                ];
		        
		            $arr_mail_data['arr_built_content']   = $arr_built_content;
                    $arr_mail_data['email_template_id']   = '66';
                    $arr_mail_data['arr_user']            = $arr_user;

                    $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'66',$arr_user);



		       
	        }
	        elseif($role_slug == 'Retailer')
		    {
		         
                    $html = '<p>Net30 order payment has been done for order no: '.$order_no.' successfully! , <br /><label>$'.$retailer_data['total_wholesale_price'].'</label> deducted from your account,</p>';


                    $arr_built_content = [
				 	  						'User_Role'      => 'Retailer',
				                            'HTML'           => $html,
				                            'PROJECT_NAME'   => $arr_site_setting['site_name']
		                                ];
		        
		            $arr_mail_data['arr_built_content']   = $arr_built_content;
                    $arr_mail_data['email_template_id']   = '66';
                    $arr_mail_data['arr_user']            = $arr_user;

                    $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'66',$arr_user);
                
		        	
	 	    }
            elseif ($role_slug == 'Vendor') 
            {
                if($isDirectPayment == 1)
                {
                	
                        $html = '<p>Net30 order payment has been done for order no: '.$order_no.' successfully! , <br /><label>$'.$retailer_data['total_wholesale_price'].'</label> credited in your account,</p>';


                        $arr_built_content = [
				 	  						   'User_Role'      => 'Vendor',
				                               'HTML'           => $html,
				                               'PROJECT_NAME'   => $arr_site_setting['site_name']
		                                    ];
		        
			            $arr_mail_data['arr_built_content']   = $arr_built_content;
	                    $arr_mail_data['email_template_id']   = '66';
	                    $arr_mail_data['arr_user']            = $arr_user;

	                    $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'66',$arr_user);
		        } 	
            }

            else
            {
            	Flash::Error("Something went wrong,please try again.");
            	return false;
            }
			$this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
		}
	}

	public function order_payment_mail($to_mail_id=false,$arr_product,$order_no,$user_id,$charge_status=false,$loggedInUserId,$order_id=false,$isDirectPayment=false,$role=false)
    {
        
    	$temp_data = isset($arr_product['leads_details'])?$arr_product['leads_details']:$arr_product;

    	/* Build array of mail data */
			foreach($temp_data as $key => $product)
			{ 
				$mail_data[$user_id][$key]['unit_price']       = isset($product['unit_wholsale_price'])?$product['unit_wholsale_price']:0.00;

				
				$mail_data[$user_id][$key]['product_id']       = isset($product['product_id'])?$product['product_id']:0;

				$mail_data[$user_id][$key]['item_qty']         = isset($product['qty'])?$product['qty']:0.00;

				$mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

				$prod_whole_price = $mail_data[$user_id][$key]['unit_price'] * $mail_data[$user_id][$key]['item_qty'];

				$mail_data[$user_id][$key]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:$product['wholesale_price'];

				$mail_data[$user_id][$key]['wholesale_price']  = isset($prod_whole_price)?$prod_whole_price:0.00;

				
				$prod_ship_charge = isset($product['shipping_charge'])?$product['shipping_charge']:0.00;

				$prod_ship_disc = isset($product['shipping_discount'])?$product['shipping_discount']:0.00;

				$mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

				$mail_data[$user_id][$key]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:$prod_ship_charge;

				$mail_data[$user_id][$key]['shipping_discount'] = isset($product['shipping_charges_discount'])?$product['shipping_charges_discount']:$prod_ship_disc;

				$mail_data[$user_id][$key]['sku_no'] = isset($product['sku_no'])?$product['sku_no']:'';
			}
		
			/* sending mail */

			if($charge_status == 'succeeded')
			{
				$charge_status = 'Paid';
			}
			
			// $email_status = $this->send_mail($to_mail_id,$mail_data,$order_no,$charge_status,$loggedInUserId);

			$email_status = $this->net30_payment_received_mail($to_mail_id,$mail_data,$order_no,$charge_status,$loggedInUserId,$order_id,$isDirectPayment,$role);

			
		return $email_status;	    		
	}



	public function send_email_to_maker($order_details)
	{
		$product_data = $order_details;
		$arr_email = $product_name = [];

		$arr_email = Sentinel::findById($product_data['maker_id'])->email;
	   	foreach ($product_data['quotes_details'] as $key2 => $product) 
	   	{
	   		$product_details              = get_product_details($product['product_id']);
	   		$product_name 				  = isset($product_details['product_name'])?$product_details['product_name']:'';
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['product_name'] = $product_name;
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['item_qty']     = isset($product['qty'])?$product['qty']:0;

	   		$order[$key2]['unit_price']   = isset($product['unit_wholsale_price'])?$product['unit_wholsale_price']:0;

	   		$order[$key2]['product_discount']   = isset($product['product_discount'])?$product['product_discount']:0;

	   		$order[$key2]['shipping_discount']   = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	   		$order[$key2]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:0;

	   		$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:0;
	   	}


	    $retailer_data = $this->RetailerQuotesModel->where('order_no',$product_data['order_no'])->first();
	    
		if($retailer_data)
		{
			$retailer_data = $retailer_data->toArray();
		}

		/*this else for if order not present in retailer quotes model means this order placed by representative.*/
		else
		{
			$retailer_data = $this->RepresentativeLeadsModel->where('order_no',$product_data['order_no'])->first();

			if($retailer_data)
			{
			  $retailer_data = $retailer_data->toArray();
			}
		}				

	    $shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

	    $retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';
	    
	    $maker_company_name = get_maker_company_name($retailer_data['maker_id']);

	    $retailer_data['user_details'] = $this->UserModel
                        ->with(['retailer_details'])  
                                  ->where('id',$retailer_data['retailer_id'])
                                  ->first()
                        ->toArray();

	  	$maker_id = $retailer_data['maker_id'];

		$maker_addr = $this->UserModel->where('id',$maker_id)
								  ->first()
									  ->toArray();

	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';
	 	$sum = 0;

		foreach ($order as $key => $order_data) 
		{ 
			$sum += $order_data['total_wholesale_price'];
			$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		}

		$order_no = $product_data['order_no'];

		$ordNo = isset($order_no)?base64_encode($order_no):'';

		$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

		if(isset($order_by) && $order_by == 'representative')
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
		}
		elseif(isset($order_by) && $order_by == 'sales_manager')
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
		}
		else
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer'); 
		}


	  	$pdf = PDF::loadView('front/invoice',compact('order','key','retailer_data','order_no','maker_addr','sum','orderCalculationData'));

		  	
	   	$currentDateTime = $order_no.date('H:i:s').'.pdf';

	   	$arr_site_setting  = get_site_settings(['site_name','website_url']);
		
		Storage::put('public/pdf/'.$currentDateTime, $pdf->output());
		 	$pdfpath = Storage::url($currentDateTime);

		  	$arr_built_content = [
		 	  						'EMAIL'          => $arr_email,
		                            'order_details'  => $order,
		                            'charge_status'  => isset($charge_status)?$charge_status:'',
		                            'email_template_id' => '38',
		                            'PROJECT_NAME' => $arr_site_setting['site_name']
		                           ];
		        
		        $arr_mail_data['arr_built_content']   = $arr_built_content;

		        $obj_email_template = $this->EmailTemplateModel->where('id','38')->first();
				
				if($obj_email_template)
		      	{
		        	$arr_email_template = $obj_email_template->toArray();
		        	

		        	$content = $arr_email_template['template_html'];
		        	$from_user_id = isset($arr_email_template['template_from_mail']) ? $arr_email_template['template_from_mail'] : '-'; //  Sender email from database
		        	// $deducted_amount = $arr_email_template
	 	        }
		       	
		       	$content = str_replace("##PROJECT_NAME##",$arr_site_setting['site_name'],$content);
				$content = str_replace("##deducted_amount##",$sum,$content);
				$content = str_replace("##order_no##",$order_no,$content);

		        $content = view('email.front_general',compact('content'))->render();
		        $content = html_entity_decode($content);

		        $html_build = view('front.email_template.purchase_order',$arr_mail_data)->render(); 

	    	
				$to_mail_id = $this->HelperService->get_user_mail($retailer_data['retailer_id']);

		    	$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;
		    	$admin_role = Sentinel::findRoleBySlug('admin');        
		        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
		        $admin_id = $admin_obj->user_id;      
		 		//$from_user_id = Sentinel::findById($admin_id)->email;

		    	$send_mail = Mail::send(array(),array(), function($message) use($content,$to_mail_id,$file_to_path,$from_user_id,$pdf,$currentDateTime)
		        {
		          
		          $message->from($from_user_id);
		          $message->to($to_mail_id)
				          ->subject('Order confirmed')
				          ->setBody($content, 'text/html');
				  /*$message->attach($file_to_path);*/
				  $message->attachData($pdf->output(), $currentDateTime, [
                        'mime' => 'application/pdf',
                    ]);
				       
		        });
	}

    //this function when retailer send cancel order request to the vendor 
	public function send_request_email_to_maker($order_details)
	{
        $Role = $order_by = '';
		$product_data = $order_details;
		
		$arr_email = $product_name = [];

		$arr_email = Sentinel::findById($product_data['maker_id'])->email;

	   	foreach ($product_data['quotes_details'] as $key2 => $product) 
	   	{ 
	   		$product_details              = get_product_details($product['product_id']);
	   		$product_name 				  = isset($product_details['product_name'])?$product_details['product_name']:'';
            $sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['item_qty']     = isset($product['qty'])?$product['qty']:0;

	   		$order[$key2]['unit_price']   = isset($product['unit_wholsale_price'])?$product['unit_wholsale_price']:0;

	   		$order[$key2]['product_discount_amount']   = isset($product['product_discount'])?$product['product_discount']:0;

	   		$order[$key2]['shipping_discount']   = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	   		$order[$key2]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:0;

	   		$order[$key2]['shipping_charges'] = isset($product['shipping_charge'])?$product['shipping_charge']:0;
	   	}


	    $retailer_data = $this->RetailerQuotesModel
	                          ->where('order_no',$product_data['order_no'])
	                          ->where('id',$product_data['id'])
	                          ->first();
	    
		if($retailer_data)
		{
			$retailer_data = $retailer_data->toArray();

			$Role     = 'Retailer';
			$order_by = 'retailer';
		}

		/*this else for if order not present in retailer quotes model means this order placed by representative.*/
		else
		{
			$retailer_data = $this->RepresentativeLeadsModel
			                      ->where('order_no',$product_data['order_no'])
			                      ->where('id',$product_data['id'])
			                      ->first();

			if($retailer_data)
			{
			    $retailer_data = $retailer_data->toArray();

			    if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
		        {
		           $Role     = 'Representative';
		           $order_by = 'representative';
		        }
		        elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
		        {
		          $Role     = 'Sales Manager';
		          $order_by = 'sales_manager';
		        }
		        else
		        { 
		           $Role = '';
		        }
			}
		}

	    $shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

	    $retailer_data['store_name'] = isset($shop_name)?$shop_name:'';
	    
	    $maker_company_name = get_maker_company_name($retailer_data['maker_id']);

	    
	    $retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();
	  	

	  	$maker_id = $retailer_data['maker_id'];

		$maker_addr = $this->UserModel->where('id',$maker_id)
								      ->first()
									  ->toArray();

	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';
	 	$sum = 0;

		foreach ($order as $key => $order_data) 
		{ 
			$sum += $order_data['total_wholesale_price'];
			$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		}

		$order_no = $product_data['order_no'];
		$order_date = $product_data['created_at'];

		/*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);


        $credentials = ['email' => $arr_email];
      
        $arr_user = get_user_by_credentials($credentials);

        $ordNo = isset($order_no)?base64_encode($order_no):'';

        $order_payment_status = isset($retailer_data['is_payment_status'])?$retailer_data['is_payment_status']:"";

		$retailer_data['email_type']           = 'order_cancel_by_retailer';
        

	    if($order_payment_status == 1)
	    {
	    	$order_payment_status = 'Paid';
	    }
	    else
	    {
	    	$order_payment_status = 'Pending';
	    }
		
        $retailer_data['order_payment_status'] = $order_payment_status;

		$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

		if(isset($order_by) && $order_by == 'representative')
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
		}
		elseif(isset($order_by) && $order_by == 'sales_manager')
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
		}
		else
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer'); 
		}

	  	$pdf = PDF::loadView('front/invoice',compact('order','key','retailer_data','order_no','order_date','maker_addr','sum','Role','order_by','orderCalculationData'));
		  	
	   	$currentDateTime = $order_no.date('H:i:s').'.pdf';


	   	$pdf_arr = 	[
    				   'PDF'           => $pdf,
            		   'PDF_FILE_NAME' => $currentDateTime
               	    ];
		

		$arr_built_content = [
		 	  					'ORDER_NO'     => $order_no,
		                        'PROJECT_NAME' => $arr_site_setting['site_name']
		                    ];


		$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'42',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr); 


		  
	}

    /* author : priyanka 
       date : 1/4/2020
       this function use for send cancel request to maker for rep/sales order
    */
	public function send_cancel_request_to_maker($order_details)
	{
        $Role = $order_by = '';
		$product_data = $order_details;
		$order_id     = $product_data['id'];
		
		$arr_email = $product_name = [];

		$arr_email = Sentinel::findById($product_data['maker_id'])->email;

	   	foreach ($product_data['leads_details'] as $key2 => $product) 
	   	{
	   		$product_details              = get_product_details($product['product_id']);
	   		$product_name 				  = isset($product_details['product_name'])?$product_details['product_name']:'';

	   		$sku_no = isset($product['sku'])?$product['sku']:"-";

	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['item_qty']     = isset($product['qty'])?$product['qty']:0;

	   		$order[$key2]['unit_price']   = isset($product['unit_wholsale_price'])?$product['unit_wholsale_price']:0;

	   		$order[$key2]['product_discount']   = isset($product['product_discount'])?$product['product_discount']:0;

	   		$order[$key2]['shipping_discount']   = isset($product['shipping_charges_discount'])?$product['shipping_charges_discount']:0;

	   		$order[$key2]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:0;

	   		$order[$key2]['shipping_charges'] = isset($product['product_shipping_charge'])?$product['product_shipping_charge']:0;
	   	}


	   	 $retailer_data = $this->RepresentativeLeadsModel 
            				    ->with(['leads_details',
            				  	        'retailer_user_details.retailer_details',
            				  	        'retailer_user_details.city_details',
            				  	        'retailer_user_details.state_details',
            				  	        'retailer_user_details.country_details',
            				  	        'representative_user_details',
			                            'sales_manager_details',
			                            'address_details',
			                            'transaction_mapping_details'
			                        ])
            				    ->where('order_no',$product_data['order_no'])
            				    ->where('id',$order_id)
            				    ->first()
							    ->toArray();

        if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
        {
           $Role     = 'Representative';
           $order_by = 'representative';
        }
        elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
        {
          $Role     = 'Sales Manager';
          $order_by = 'sales_manager';
        }
        else
        { 
           $Role = '';
        }							    


   
	    $shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

	    $retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';
	    
	    $maker_company_name = get_maker_company_name($retailer_data['maker_id']);

	    
	    $retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();
	  	

	  	$maker_id = $retailer_data['maker_id'];

		$maker_addr = $this->UserModel->where('id',$maker_id)
								      ->first()
									  ->toArray();

	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';
	 	$sum = 0;

		foreach ($order as $key => $order_data) 
		{ 
			$sum += $order_data['total_wholesale_price'];
			$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		}

		$order_no = $product_data['order_no'];
		$order_date = $product_data['created_at'];

		//Get pryment status..
	   /* $order_payment_status = isset($retailer_data['transaction_mapping_details']['transaction_status'])?$retailer_data['transaction_mapping_details']['transaction_status']:"";

		$order_payment_status = get_payment_status($order_payment_status);

		if($order_payment_status == "")
		{
		   $order_payment_status = "Pending";
		}*/

		$order_payment_status = isset($retailer_data['is_payment_status'])?$retailer_data['is_payment_status']:"";


	    if($order_payment_status == 1)
	    {
	    	$order_payment_status = 'Paid';
	    }
	    else
	    {
	    	$order_payment_status = 'Pending';
	    }
		
        $retailer_data['order_payment_status'] = $order_payment_status;

        $ordNo = isset($order_no)?base64_encode($order_no):'';
		$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

		if(isset($order_by) && $order_by == 'representative')
		{
   			$orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
		}
		elseif(isset($order_by) && $order_by == 'sales_manager')
		{
   			$orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
		}
		else
		{
   			$orderCalculationData = [];
		}
	  	$pdf = PDF::loadView('front/cancel_order_invoice',compact('order','key','retailer_data','order_no','order_date','maker_addr','sum','Role','order_by','orderCalculationData'));
		  	
	   	$currentDateTime = $order_no.date('H:i:s').'.pdf';


	   	/*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);


	   	$credentials = ['email' => $arr_email];
      
        $arr_user = get_user_by_credentials($credentials);


	   	$pdf_arr = 	[
    				  'PDF'           => $pdf,
            		  'PDF_FILE_NAME' => $currentDateTime
               	    ];
		
		
	  	$arr_built_content = [
	 	  						'ORDER_NO'     => $order_no,
	                            'PROJECT_NAME' => $arr_site_setting['site_name']
	                         ];


	    $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'42',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);                     
		        
 
	}

	public function cancel_order_status_mail($status,$order_details,$reason=false)
	{
		$Role = '';
	    if($status == 2)
	    {
	           /*if order cancel request approved by vendor then attach pdf*/
	        $sum = 0;
	   		$product_data = $order_details;
	   		$order_no     = $product_data['order_no'];	  

	   		$user = $this->UserModel->where('id',$product_data['retailer_id'])->first();

	  
	     	$price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;

	     	$arr_email = Sentinel::findById($product_data['maker_id'])->email;
	     	$maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);
	    
	     	foreach ($product_data['quotes_details'] as $key2 => $product) 
	     	{
	 	   		$product_details              = get_product_details($product['product_id']);
	 	   		$product_name 				  = $product_details['product_name'];
	 	   		$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['item_qty']     = $product['qty'];
	 	   		$order[$key2]['unit_price']   = $product['unit_wholsale_price'];
	 	   		$total_wholesale_price = $product['unit_wholsale_price']*$product['qty'];
	 	   		$order[$key2]['total_wholesale_price'] = isset($total_wholesale_price)?$total_wholesale_price:'0.00';
	 	   		
	 	   		
	 	   		$order[$key2]['product_discount_amount'] = isset($product['product_discount'])?$product['product_discount']:0;

		   		    $order[$key2]['shipping_discount'] = $product['shipping_discount'];

		   		    $order[$key2]['shipping_charges']  = $product['shipping_charge'];

	     	}
	     	  	
	     	$maker[$product_data['maker_id']]['order_details']= $order;
				     	    
				     	    
		    foreach ($order as $key => $order_details) 
		    { 

			   $sum += $order_details['total_wholesale_price'];
			   $order[$key]['unit_price']  = num_format($order_details['unit_price'], 2, '.', '');
		    }

	     	$maker[$product_data['maker_id']]['email_id'] = $arr_email;
	     	  	
	     	$arr_built_content =[
	     	  						'EMAIL'          => $maker[$product_data['maker_id']]['email_id'],
	                                'order_details'  => $maker[$product_data['maker_id']]['order_details']
	                            ];
	            
	        $arr_mail_data['arr_built_content']   = $arr_built_content;
	        $arr_mail_data['user']                = $user;
	       
	        $html_build = view('front.email_template.purchase_order',$arr_mail_data)->render(); 

	        //Create pdf here for maker

	        $retailer_data = $this->RetailerQuotesModel 
	                			   ->with(['quotes_details','user_details','transaction_mapping_details'])
	                			   ->where('order_no',$order_no)
	                			   ->where('id',$product_data['id'])
	                			   ->first()
								   ->toArray();

            $Role = 'Retailer';								   


			//Get Order Status..
			$order_cancel_status = isset($retailer_data['order_cancel_status'])?$retailer_data['order_cancel_status']:"";										  
			$order_cancel_status = get_order_cancel_status($order_cancel_status);
			//Get pryment status..
			/*$order_payment_status = isset($retailer_data['transaction_mapping_details']['transaction_status'])?$retailer_data['transaction_mapping_details']['transaction_status']:"";

			$order_payment_status = get_payment_status($order_payment_status);

			if($order_payment_status=="")
			{
				$order_payment_status = "Pending";
			}*/

			$order_payment_status = isset($retailer_data['is_payment_status'])?$retailer_data['is_payment_status']:"";

		    if($order_payment_status == 1)
		    {
		    	$order_payment_status = 'Paid';
		    }
		    else
		    {
		    	$order_payment_status = 'Pending';
		    }

			$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

			$retailer_data['store_name'] = isset($store_name)?$store_name:'';
			$maker_id = $maker[$product_data['maker_id']]['maker_id'];

				
			$maker_addr = $this->UserModel->where('id',$product_data['maker_id'])
								->first()
								->toArray();
	 	  			
	 	  	$company_name = get_maker_company_name($product_data['maker_id']);
	 	  	$maker_addr['company_name'] = isset($company_name)?$company_name:'';

	 	  	$retailer_data['email_type']           = 'order_cancel_by_vendor';
	 	  	$retailer_data['order_payment_status'] = isset($order_payment_status)?$order_payment_status:"Pending";
	 	  	$retailer_data['cancel_status'] 	   = $order_cancel_status;


	 	  	$retailer_data['user_details'] = $this->UserModel
	                                              ->with(['retailer_details'])  
	                                              ->where('id',$retailer_data['retailer_id'])
	                                              ->first()
	                                              ->toArray();
		
	 	  		
	        $ordNo = isset($order_no)?base64_encode($order_no):'';

			$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

			if(isset($order_by) && $order_by == 'representative')
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
			}
			elseif(isset($order_by) && $order_by == 'sales_manager')
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
			}
			else
			{
			   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer'); 
			}

	 	  	$pdf = PDF::loadView('front/invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','Role','orderCalculationData'));
			$currentDateTime = $order_no.date('H:i:s').'.pdf';
			 //    Storage::put('public/pdf/'.$currentDateTime, $pdf->output());
				// $pdfpath = Storage::url($currentDateTime);

				// $file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;


	        // Send Mail To maker 
	        $to_mail_id = isset($maker[$product_data['maker_id']]['email_id'])?$maker[$product_data['maker_id']]['email_id']:"";

	        // Send Mail To retailer 
	        $to_retailer_id = isset($retailer_data['user_details']['email'])?$retailer_data['user_details']['email']:"";

	        /*Send Retailer Mail*/
		        $credentials = ['email' => $to_retailer_id];
	      
	        	$arr_user    = get_user_by_credentials($credentials);

	           	/*Get site setting data from helper*/
		        $arr_site_setting = get_site_settings(['site_name','website_url']);
				
			    $pdf_arr = 	[
			    						'PDF'           => $pdf,
			            		'PDF_FILE_NAME' => $currentDateTime
			               	];

			    $arr_built_content  =  	[
				 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
				 	  					   	'ORDER_NO'			=> $order_details['order_no']
																];
																
																

				$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'87',$arr_user);   

				$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

			/*end*/	                        


            //get email of admin
            $admin_role = Sentinel::findRoleBySlug('admin');        
	        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
		    $admin_id     = $admin_obj->user_id;      
			$admin_email_id = Sentinel::findById($admin_id)->email;


	   

			$credentials = ['email' => $admin_email_id];
	      
        	$arr_admin_user    = get_user_by_credentials($credentials);

           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);
			
		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];

		    $arr_built_content  =  	[
			 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
			 	  					   	'ORDER_NO'			=> $order_details['order_no']
			                        ];
				                        
			$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'87',$arr_admin_user);   

			$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

	}
	else
	{
        $to_user_id   = Sentinel::findById($order_details['user_details']['id'])->email;
		$from_user_id = Sentinel::findById($order_details['maker_details']['id'])->email;

		//get email of admin
        $admin_role = Sentinel::findRoleBySlug('admin');        
	    $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
		$admin_id     = $admin_obj->user_id;      
		$admin_email_id = Sentinel::findById($admin_id)->email;

        //send mail to retailer
			$credentials = ['email' => $to_user_id];	      
	        $arr_user    = get_user_by_credentials($credentials);

           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);
			
		    $arr_built_content = 
								[
									'ORDER_NO'   	=> $order_details['order_no'],
	                            	'REJECT_REASON' => $reason,
	                            	'PROJECT_NAME' 	=> $arr_site_setting['site_name'],					                            
	                        	];

            $arr_mail_data                      = [];
	        $arr_mail_data['email_template_id'] = '73';
	        $arr_mail_data['arr_built_content'] = $arr_built_content;
	        $arr_mail_data['arr_user']          = $arr_user;

	        $email_status  = $this->EmailService->send_mail($arr_mail_data);


	    //send mail to admin
	        $credentials = ['email' => $admin_email_id];	      
	        $arr_user    = get_user_by_credentials($credentials);

            $arr_mail_data                      = [];
	        $arr_mail_data['email_template_id'] = '73';
	        $arr_mail_data['arr_built_content'] = $arr_built_content;
	        $arr_mail_data['arr_user']          = $arr_user;

	        $email_status  = $this->EmailService->send_mail($arr_mail_data);
		  
	}
        

		
	}


	public function rep_sales_cancel_order_status_mail($status,$order_details,$reason=false,$to_mail_id)
	{
	
        $Role = '';

	    if($status == 2)
	    {
		        $user         = Sentinel::check();	
		   		$product_data = $order_details;
		   		$order_no     = $product_data['order_no'];
		   		$order_id     = $product_data['id']; 	  

		        $price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
		     	   
		     	$arr_email = Sentinel::findById($product_data['maker_id'])->email;
		     	   
		     	$maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);
		    
		     	foreach ($product_data['leads_details'] as $key2 => $product) 
		     	{ 
		 	   		$product_details              = get_product_details($product['product_id']);
		 	   		$product_name 				  = $product_details['product_name'];
		 	   		$sku_no = isset($product['sku'])?$product['sku']:"-";

		 	   		$order[$key2]['order_no']     = $order_no or '';
		 	   		$order[$key2]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
		 	   		$order[$key2]['order_no']     = $order_no or '';
		 	   		$order[$key2]['item_qty']     = $product['qty'];
		 	   		$order[$key2]['unit_price']   = $product['unit_wholsale_price'];
		 	   		$total_wholesale_price        = $product['unit_wholsale_price']*$product['qty'];
		 	   		$order[$key2]['total_wholesale_price'] = isset($total_wholesale_price)?$total_wholesale_price:'0.00';
		 	   		
		 	   		
		 	   		$order[$key2]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0;

			   		$order[$key2]['shipping_discount'] = $product['shipping_charges_discount'];

			   		$order[$key2]['shipping_charges']  = $product['product_shipping_charge'];

		     	}
		     	  	
		     	$maker[$product_data['maker_id']]['order_details']= $order;
					     	    
			    $sum = 0;
		     	    
		     	foreach ($order as $key => $order_data) 
		     	{ 

					$sum += $order_data['total_wholesale_price'];
					$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
				}

		     	$maker[$product_data['maker_id']]['email_id'] = $arr_email;
		     	  	
		     	$arr_built_content= [
		     	  						'EMAIL'          => $maker[$product_data['maker_id']]['email_id'],
		                                'order_details'  => $maker[$product_data['maker_id']]['order_details']
		                            ];
		            
		        $arr_mail_data['arr_built_content']   = $arr_built_content;
		            $arr_mail_data['user']            = $user;
		       
		        $html_build = view('front.email_template.purchase_order',$arr_mail_data)->render(); 

		        //Create pdf here for maker

		        $retailer_data = $this->RepresentativeLeadsModel 
		            				  ->with(['leads_details',
		            				  	      'retailer_user_details.retailer_details',
		            				  	      'retailer_user_details.city_details',
		            				  	      'retailer_user_details.state_details',
		            				  	      'retailer_user_details.country_details',
		            				  	      'representative_user_details',
					                          'sales_manager_details',
					                          'address_details',
					                          'transaction_mapping_details'
					                        ])

		            				  ->where('order_no',$order_no)
		            				  ->where('id',$order_id)
		            				  ->first()
									  ->toArray();
		     

		        if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
		        {
		           $Role     = 'Representative';
		           $order_by = 'representative';
		        }
		        elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
		        {
		          $Role     = 'Sales Manager';
		          $order_by = 'sales_manager';
		        }
		        else
		        { 
		           $Role = '';
		        }

				$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

				$retailer_data['store_name'] = isset($store_name)?$store_name:'';
				$maker_id = $maker[$product_data['maker_id']]['maker_id'];

				$maker_addr = $this->UserModel
				                    ->where('id',$product_data['maker_id'])
								    ->first()
									->toArray();

			    //Get pryment status..
			    /*$order_payment_status = isset($retailer_data['transaction_mapping_details']['transaction_status'])?$retailer_data['transaction_mapping_details']['transaction_status']:"";

				$order_payment_status = get_payment_status($order_payment_status);

				if($order_payment_status == "")
				{
				   $order_payment_status = "Pending";
				}*/

				$order_payment_status = isset($retailer_data['is_payment_status'])?$retailer_data['is_payment_status']:"";

			    if($order_payment_status == 1)
			    {
			    	$order_payment_status = 'Paid';
			    }
			    else
			    {
			    	$order_payment_status = 'Pending';
			    }
				
		        $retailer_data['order_payment_status'] = $order_payment_status; 						
			  			
			    $company_name = get_maker_company_name($product_data['maker_id']);
			  	$maker_addr['company_name'] = isset($company_name)?$company_name:'';
			  	$ordNo = isset($order_no)?base64_encode($order_no):'';
				$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

			  	if(isset($order_by) && $order_by == 'representative')
				{
   					$orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
				}
				elseif(isset($order_by) && $order_by == 'sales_manager')
				{
   					$orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
				}
				else
				{
   					$orderCalculationData = [];
				}

			  	$pdf = PDF::loadView('front/cancel_order_invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','Role','order_by','orderCalculationData'));


			  	$currentDateTime = $order_no.date('H:i:s').'.pdf';
				
					

			  	$credentials = ['email' => $to_mail_id];
	      
	        	$arr_user    = get_user_by_credentials($credentials);

	        	// dd($arr_user);

	           	/*Get site setting data from helper*/
		        $arr_site_setting = get_site_settings(['site_name','website_url']);
				
			    $pdf_arr = 	[
			    				'PDF'           => $pdf,
			            		'PDF_FILE_NAME' => $currentDateTime
			               	];

			    $arr_built_content  =  	[
				 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
				 	  					   	'ORDER_NO'			=> $order_details['order_no']
				                        ];

				$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'87',$arr_user);   

				$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
        	
        }
        else
        {
        	$to_user_id = Sentinel::findById($order_details['retailer_user_details']['id'])->email;
		    $from_user_id = Sentinel::findById($order_details['maker_data']['id'])->email;


		    //get email of admin
	        $admin_role = Sentinel::findRoleBySlug('admin');        
		    $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
			$admin_id     = $admin_obj->user_id;      
			$admin_email_id = Sentinel::findById($admin_id)->email;

			/*send mail to user*/
				$credentials = ['email' => $to_user_id];	      
		        $arr_user    = get_user_by_credentials($credentials);

	           	/*Get site setting data from helper*/
		        $arr_site_setting = get_site_settings(['site_name','website_url']);
				
			    $arr_built_content = 
									[
										'ORDER_NO'   	=> $order_details['order_no'],
		                            	'REJECT_REASON' => $reason,
		                            	'PROJECT_NAME' 	=> $arr_site_setting['site_name'],					                            
		                        	];

	            $arr_mail_data                      = [];
		        $arr_mail_data['email_template_id'] = '73';
		        $arr_mail_data['arr_built_content'] = $arr_built_content;
		        $arr_mail_data['arr_user']          = $arr_user;

		        $email_status  = $this->EmailService->send_mail($arr_mail_data);
			/*end*/


			$credentials = ['email' => $admin_email_id];	      
	        $arr_user    = get_user_by_credentials($credentials);

            $arr_mail_data                      = [];
	        $arr_mail_data['email_template_id'] = '73';
	        $arr_mail_data['arr_built_content'] = $arr_built_content;
	        $arr_mail_data['arr_user']          = $arr_user;

	        $email_status  = $this->EmailService->send_mail($arr_mail_data);


        }

		
	}





	public function is_profile_complete($user_arr=[])
	{
	
		if(count($user_arr)>0 && isset($user_arr))
		{
	

			if(count($user_arr)>0)
				if($user = Sentinel::findById($user_arr['id']))
				  {
				   if($user->inRole('maker'))
				    {
						$maker_data = $this->MakerModel->where('user_id','=',$user_arr['id'])->first();
				        
				         
				         if(empty($user_arr['first_name'])||empty($user_arr['last_name'])||empty($user_arr['email'])||empty($user_arr['contact_no'])||empty($user_arr['country_id'])||empty($maker_data['company_name'])||empty($maker_data['website_url'])||empty($maker_data['primary_category_id'])||empty($user_arr['post_code']))
				  		{     
				  			return false;
				  		}
				  		else
				  		{   
							return true;
				  		} 
				    }
				    elseif($user->inRole('retailer'))
				    {
				    	
						if(isset($user_arr['id']) && $user_arr['id']!='')
						{		
						    $retailer_data = $this->RetailerModel->where('user_id','=',$user_arr['id'])->first();
						

						    if(empty($user_arr['first_name'])||empty($user_arr['last_name'])||empty($user_arr['email'])||empty($user_arr['contact_no'])||empty($user_arr['country_id'])||empty($retailer_data['store_name'])||empty($user_arr['address'])||empty($retailer_data['shipping_addr'])||empty($retailer_data['billing_address']))

					  		{     
					  			return false;
					  		}
					  		else
					  		{   
								return true;
					  		}
					  	} 
				    }

				    elseif($user->inRole('representative'))
				    {
						$representative_data = $this->RepresentativeModel->where('user_id','=',$user_arr['id'])->first();

						if(empty($user_arr['first_name'])||empty($user_arr['last_name'])||empty($user_arr['email'])||empty($user_arr['post_code'])||empty($representative_data['description']))
				  		{     
				  			return false;
				  		}
				  		else
				  		{   
							return true;
				  		} 
				    }
				    elseif($user->inRole('customer'))
				    {
				    	// dd($user);
				    	
						if(isset($user_arr['id']) && $user_arr['id']!='')
						{		
						    if(empty($user_arr['first_name'])||empty($user_arr['last_name'])||empty($user_arr['email'])||empty($user_arr['contact_no'])||empty($user_arr['country_id']))

					  		{     
					  			return false;
					  		}
					  		else
					  		{   
								return true;
					  		}
					  	} 
				    }
				}
				else
				{   
					return true;
				} 
			}

	}

	/*send the mail to the retailer after the due date over*/

	public function mail_after_duedate_over()
	{   
	    $retailer_order_data = [];
	    $retailer_order_data = $this->RetailerQuotesModel->with(['user_details'=>function($q){
	                                                            $q->select('id','first_name','last_name','email');
	                                                          },'transaction_mapping'])
	                                                         ->get()->toArray();
	      
	    $current_date = date('Y-m-d');

	    if(isset($retailer_order_data) && count($retailer_order_data)>0)
	    {
	        foreach ($retailer_order_data as $key => $order) 
	        {
	            if(isset($order['payment_due_date']) && $order['payment_due_date'] != '')
	            {
	                
	                if($order['transaction_mapping']['transaction_status'] !=2) 
	                {

	                    $due_date_time = $order['payment_due_date']; 
	                    $due_date = date('Y-m-d',strtotime($due_date_time));

	                    if($current_date == $due_date)
	                    {
	                       //send mail to retailer
	                        $email     = $order['user_details']['email'];
	                        $order_id  = $order['id'];
	                        $mail      = $this->built_mail_data($email,$order_id);
	                        $result    = $this->EmailService->send_mail($mail);

	                    }


	                }         
	               
	            }
	          
	        }
	    }
	}

	public function built_mail_data($email,$order_id)
	{ 
	    $credentials = ['email' => $email];
	    
		$user = Sentinel::findByCredentials($credentials); // check if user exists
		
			// $site_setting_obj = SiteSettingModel::first();
		  	//       if($site_setting_obj)
		  	//       {
		  	//           $site_setting_arr = $site_setting_obj->toArray();            
		  	//       }

		  //       $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';

        $arr_site_setting  = get_site_settings(['site_name','website_url']);
	    
	    if($user)
	    {
	   	 	$arr_user = $user->toArray();

	   	 	$url = url('/retailer/my_orders/view/').'/'.base64_encode($order_id);

	   	 	$html = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.$url.'">View Order</a>.<br/>' ;

	   	 	$message = 'Your payment due date is over today, If you want to do the payment please click on below link';

	    	$arr_built_content = ['USER_NAME'    => $arr_user['first_name'].' '.$arr_user['last_name'],
	                              'EMAIL'        => $arr_user['email'],
	                              'HTML'         => $html,
	                              'SUBJECT'      => 'Net30 Reminder Mail',
								  'MESSAGE'      => $message,
								  'SITE_URL'     => $arr_site_setting['website_url'],
								  'PROJECT_NAME' => $arr_site_setting['site_name']
	                             ];

	        $arr_mail_data                      = [];
	        $arr_mail_data['email_template_id'] = '40';
	        $arr_mail_data['arr_built_content'] = $arr_built_content;
	        $arr_mail_data['user']              = $arr_user;

	        return $arr_mail_data;
	   	}
	    	
	    return FALSE;
	}

   	/*
   		Auth : Jaydip
   		Date : 09 Dec 2019
   		Desc : send mail to maker
   	*/

	public function send_maker_mail($order_no = false)
	{

		$order = [];

	    $order_data = $this->RepresentativeLeadsModel 
	    					  ->with('leads_details')
	    					  ->where('order_no',$order_no)
	    					  ->get()
	    					  ->toArray();
	
   		$user = Sentinel::check();		  
   		
     	foreach ($order_data as $key => $product_data) 
     	{
     	   $price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
     	   $arr_email = Sentinel::findById($product_data['maker_id'])->email;
     	   $maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);
     
     	   foreach ($product_data['leads_details'] as $key2 => $product) 
     	   {
     	   		$product_details              = get_product_details($product['product_id']);
     	   		$product_name 				  = $product_details['product_name'];
     	   		$order[$key2]['order_no']     = $order_no or '';
     	   		$order[$key2]['product_name'] = $product_name;
     	   		$order[$key2]['order_no']     = $order_no or '';
     	   		$order[$key2]['item_qty']     = $product['qty'];
     	   		$order[$key2]['unit_price']   = $product['unit_wholsale_price'];
     	   		$order[$key2]['total_wholesale_price'] = $product['wholesale_price'];
     	   		
     	   		
     	   		$order[$key2]['product_discount_amount'] = $product['product_discount'];

 	   		    $order[$key2]['shipping_discount'] = $product['shipping_charges_discount'];

 	   		    $order[$key2]['shipping_charges']  = $product['shipping_charges'];

     	   }

     	    $maker[$product_data['maker_id']]['order_details']= $order;
     	    
     	    $maker[$product_data['maker_id']]['email_id'] = $arr_email;
     	  	
     	  	$arr_built_content = [
     	  						'EMAIL'          => $maker[$product_data['maker_id']]['email_id'],
                                'order_details'  => $maker[$product_data['maker_id']]['order_details']
                               ];
            
            $arr_mail_data['arr_built_content']   = $arr_built_content;
            $arr_mail_data['user']                = $user;
       
            $html_build = view('front.email_template.purchase_order',$arr_mail_data)->render(); 
            //Create pdf here for maker

           	$role = '';
           	if (isset($product_data['representative_id']) && $product_data['representative_id'] != 0) {
           		$role = 'Representative';
           	}
           	elseif (isset($product_data['sales_manager_id']) && $product_data['sales_manager_id'] != 0) {
           		$role = 'Sales Manager';
           	}
           	else{
           		$role = 'Representative';
           	}
            $retailer_data = $this->RepresentativeLeadsModel 
		    					  ->with(['address_details','retailer_user_details.retailer_details'])
		    					  ->where('order_no',$order_no)
		    					  ->first()
		    					  ->toArray();
		    			//dd($retailer_data);			  
		
				$store_name = get_retailer_shop_name($retailer_data['retailer_id']);
				
				$retailer_data['odr_shipping_addr'] = $retailer_data['address_details']['ship_complete_address'];
				$retailer_data['odr_billing_addr'] = $retailer_data['address_details']['bill_complete_address'];
				//$retailer_data['store_name'] = isset($store_name)?$store_name:'';
				$maker_id = $retailer_data['maker_id'];

				$retailer_data['user_details'] = $this->UserModel
                        ->with(['retailer_details'])  
                                  ->where('id',$retailer_data['retailer_id'])
                                  ->first()
                        ->toArray();
                        
				$maker_addr = $this->UserModel->where('id',$maker_id)
								  ->first()
									  ->toArray();
	  			
	  			$company_name = get_maker_company_name($maker_id);
	  			$maker_addr['company_name'] = isset($company_name)?$company_name:'';	  			

	  			$pdf = PDF::loadView('front/reps_invoice',compact('role','order','key','retailer_data','maker_addr','order_no'));
	  			
	  			$currentDateTime = $order_no.date('H:i:s').'.pdf';
				Storage::put('public/pdf/'.$currentDateTime, $pdf->output());
	 			$pdfpath = Storage::url($currentDateTime);

	 			$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;


            // Send Mail To maker 
            $to_mail_id = isset($maker[$product_data['maker_id']]['email_id'])?$maker[$product_data['maker_id']]['email_id']:"";
        		$obj_email_template = $this->EmailTemplateModel->where('id','43')->first();

	
			if($obj_email_template)
  			{
    			$arr_email_template = $obj_email_template->toArray();
    			$content = $arr_email_template['template_html'];
    			$from_user_id = isset($arr_email_template['template_from_mail']) ? $arr_email_template['template_from_mail'] : '-'; //  Sender email from database
			}

			// $site_setting_obj = SiteSettingModel::first();
			// if($site_setting_obj)
			// {
			// 	$site_setting_arr = $site_setting_obj->toArray();            
			// }

			// $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';

			$arr_site_setting  = get_site_settings(['site_name','website_url']);
			
			$content = str_replace("##SITE_URL##",$arr_site_setting['website_url'],$content);
			$content = str_replace("##PROJECT_NAME##",$site_setting_arr['site_name'],$content);

    		$content = view('email.front_general',compact('content'))->render();
    		$content = html_entity_decode($content);
    		$admin_role = Sentinel::findRoleBySlug('admin');        
	        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
	        $admin_id = $admin_obj->user_id;      
 			//$from_user_id = Sentinel::findById($admin_id)->email;
        	
        	$send_mail = Mail::send(array(),array(), function($message) use($content,$to_mail_id,$file_to_path,$from_user_id,$pdf,$currentDateTime)
	        {
	          $message->from($from_user_id);
	          $message->to($to_mail_id)
			          ->subject('Purchase order')
			          ->setBody($content, 'text/html');
			  // $message->attach($file_to_path);
			  $message->attachData($pdf->output(), $currentDateTime, [
                        'mime' => 'application/pdf',
                    ]);
	        });


     	}
	
	}

   	/*
   		Auth : Bhagyashri
   		Date : 16 Dec 2019
   		Desc : send cancel order email to maker and retailer
   	*/

	public function cancel_order_mail($order_data = false)
	{			
		
   		$user = Sentinel::check();	
   		$product_data = $order_data;
   		$order_no = $product_data['order_no'];	  

 	    $price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;

 	    $arr_email = Sentinel::findById($product_data['maker_id'])->email;


 	    $maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);

 	    foreach ($product_data['quotes_details'] as $key2 => $product) 
 	    {
 	   	    
 	   		$product_details              = get_product_details($product['product_id']);
        	$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";
 	   		
 	   		$product_name 				  = $product_details['product_name'].' (SKU: '.$sku_no.')';
 	   		$order[$key2]['order_no']     = $order_no or '';
 	   		$order[$key2]['product_name'] = $product_name;
 	   		$order[$key2]['order_no']     = $order_no or '';
 	   		$order[$key2]['item_qty']     = $product['qty'];
 	   		$order[$key2]['unit_price']   = $product['unit_wholsale_price'];
 	   		$total_wholesale_price = $product['unit_wholsale_price']*$product['qty'];
 	   		$order[$key2]['total_wholesale_price'] = isset($total_wholesale_price)?$total_wholesale_price:'0.00';
 	   		
 	   		
 	   		$order[$key2]['product_discount_amount'] = isset($product['product_discount'])?$product['product_discount']:0;

	   		    $order[$key2]['shipping_discount'] = $product['shipping_discount'];

	   		    $order[$key2]['shipping_charges']  = $product['shipping_charge'];

 	    }
     	  	
     	$maker[$product_data['maker_id']]['order_details']= $order;
			     	    
			     	    
 	    $sum = 0;

 	    foreach ($order as $key => $order_data) 
 	    { 

		   $sum += $order_data['total_wholesale_price'];
		   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
	    }

 	    $maker[$product_data['maker_id']]['email_id'] = $arr_email;
 	  	
 	   

        //Create pdf here for maker

        $retailer_data = $this->RetailerQuotesModel 
        					  ->with(['quotes_details','user_details','transaction_mapping_details'])
        					  ->where('order_no',$order_no)
        					  ->where('id',$product_data['id'])
        					  ->first()
							  ->toArray();




		//Get Order Status..
		$order_cancel_status = isset($retailer_data['order_cancel_status'])?$retailer_data['order_cancel_status']:"";	

		$order_cancel_status = get_order_cancel_status($order_cancel_status);

		//Get pryment status..
		/*$order_payment_status = isset($retailer_data['transaction_mapping_details']['transaction_status'])?$retailer_data['transaction_mapping_details']['transaction_status']:"";

		$order_payment_status = get_payment_status($order_payment_status);

		if($order_payment_status=="")
		{
			$order_payment_status = "Pending";
		}*/

		$order_payment_status = isset($retailer_data['is_payment_status'])?$retailer_data['is_payment_status']:"";


	    if($order_payment_status == 1)
	    {
	    	$order_payment_status = 'Paid';
	    }
	    else
	    {
	    	$order_payment_status = 'Pending';
	    }

		$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

		$retailer_data['store_name'] = isset($store_name)?$store_name:'';
		$maker_id = $maker[$product_data['maker_id']]['maker_id'];

		
		$maker_addr = $this->UserModel->where('id',$product_data['maker_id'])
						              ->first()
							          ->toArray();
			
		$company_name = get_maker_company_name($product_data['maker_id']);
		$maker_addr['company_name'] = isset($company_name)?$company_name:'';

		$retailer_data['email_type']           = 'order_cancel_by_retailer';
		$retailer_data['order_payment_status'] = isset($order_payment_status)?$order_payment_status:"Pending";
		$retailer_data['cancel_status'] 	   = $order_cancel_status;


		$retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();

	    $Role = 'Retailer';


		/*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);

        $ordNo = isset($order_no)?base64_encode($order_no):'';

		$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

		if(isset($order_by) && $order_by == 'representative')
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
		}
		elseif(isset($order_by) && $order_by == 'sales_manager')
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
		}
		else
		{
		   $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='retailer'); 
		}


		$pdf = PDF::loadView('front/invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','Role','orderCalculationData'));


		$currentDateTime = $order_no.date('H:i:s').'.pdf';


	    $pdf_arr = 	[
			          'PDF'           => $pdf,
    		          'PDF_FILE_NAME' => $currentDateTime
       	            ];


        $arr_built_content = [
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                            ]; 

	
        // Send Mail To maker 
        $to_mail_id = isset($maker[$product_data['maker_id']]['email_id'])?$maker[$product_data['maker_id']]['email_id']:"";


        $credentials = ['email' => $to_mail_id];
  
        $arr_user = get_user_by_credentials($credentials);



        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'39',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr); 


        // Send Mail To retailer 
        $to_retailer_id = isset($retailer_data['user_details']['email'])?$retailer_data['user_details']['email']:"";


        $credentials = ['email' => $to_retailer_id];
  
        $arr_user = get_user_by_credentials($credentials);


        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'39',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr); 


        //send mail to admin

		$admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
        $admin_id       = $admin_obj->user_id;      
 		$admin_email_id = Sentinel::findById($admin_id)->email;

 		$credentials = ['email' => $admin_email_id];
  
        $arr_user = get_user_by_credentials($credentials);

        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'39',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr); 
 
    
	}


    /*
   		Auth : Priyanka
   		Date : 1 april 2020
   		Desc : send cancel order email to maker and retailer
   	*/

    public function rep_sales_cancel_order_mail($order_data = false,$to_mail_id=false)
    {
    	$Role         = '';
        $user         = Sentinel::check();	
   		$product_data = $order_data;
   		$order_no     = $product_data['order_no'];
   		$order_id     = $product_data['id']; 	  

        $price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
     	   
     	$arr_email = Sentinel::findById($product_data['maker_id'])->email;
     	   
     	$maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);
    
     	foreach ($product_data['leads_details'] as $key2 => $product) 
     	{ 
 	   		$product_details              = get_product_details($product['product_id']);
 	   		$sku_no = isset($product['sku'])?$product['sku']:"-";

 	   		$product_name 				  = $product_details['product_name'].' (SKU: '.$sku_no.')';
 	   		$order[$key2]['order_no']     = $order_no or '';
 	   		$order[$key2]['product_name'] = $product_name;
 	   		$order[$key2]['order_no']     = $order_no or '';
 	   		$order[$key2]['item_qty']     = $product['qty'];
 	   		$order[$key2]['unit_price']   = $product['unit_wholsale_price'];
 	   		$total_wholesale_price        = $product['unit_wholsale_price']*$product['qty'];
 	   		$order[$key2]['total_wholesale_price'] = isset($total_wholesale_price)?$total_wholesale_price:'0.00';
 	   		
 	   		
 	   		$order[$key2]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0;

	   		$order[$key2]['shipping_discount'] = $product['shipping_charges_discount'];

	   		$order[$key2]['shipping_charges']  = $product['product_shipping_charge'];

     	}
     	  	
     	$maker[$product_data['maker_id']]['order_details']= $order;
			     	    
	    $sum = 0;
     	    
     	foreach ($order as $key => $order_data) 
     	{ 

			$sum += $order_data['total_wholesale_price'];
			$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		}

     	$maker[$product_data['maker_id']]['email_id'] = $arr_email;
     	  	

        //Create pdf here for maker

        $retailer_data = $this->RepresentativeLeadsModel 
            				  ->with(['leads_details',
            				  	      'retailer_user_details.retailer_details',
            				  	      'retailer_user_details.city_details',
            				  	      'retailer_user_details.state_details',
            				  	      'retailer_user_details.country_details',
            				  	      'representative_user_details',
			                          'sales_manager_details',
			                          'address_details',
			                          'transaction_mapping_details'
			                      ])

            				  ->where('order_no',$order_no)
            				  ->where('id',$order_id)
            				  ->first()
							  ->toArray();
     

        if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
        {
           $Role     = 'Representative';
           $order_by = 'representative';
        }
        elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
        {
          $Role     = 'Sales Manager';
          $order_by = 'sales_manager';
        }
        else
        { 
           $Role = '';
        }

		$store_name = get_retailer_shop_name($retailer_data['retailer_id']);

		$retailer_data['store_name'] = isset($store_name)?$store_name:'';
		$maker_id = $maker[$product_data['maker_id']]['maker_id'];

		$maker_addr = $this->UserModel
		                    ->where('id',$product_data['maker_id'])
						    ->first()
							->toArray();
	  			
	    $company_name = get_maker_company_name($product_data['maker_id']);
	  	$maker_addr['company_name'] = isset($company_name)?$company_name:'';

	  	//Get pryment status..
	    // $order_payment_status = isset($retailer_data['transaction_mapping_details']['transaction_status'])?$retailer_data['transaction_mapping_details']['transaction_status']:""; 

	    $order_payment_status = isset($retailer_data['is_payment_status'])?$retailer_data['is_payment_status']:"";


	    if($order_payment_status == 1)
	    {
	    	$order_payment_status = 'Paid';
	    }
	    else
	    {
	    	$order_payment_status = 'Pending';
	    }
		// $order_payment_status = get_payment_status($order_payment_status);

		if($order_payment_status == "")
		{
		   $order_payment_status = "Pending";
		}
		
        $retailer_data['order_payment_status'] = $order_payment_status; 
        $ordNo = isset($order_no)?base64_encode($order_no):'';
		$vendorId = isset($retailer_data['maker_id'])?base64_encode($retailer_data['maker_id']):'';

        if(isset($order_by) && $order_by == 'representative')
		{
   			$orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='representative');
		}
		elseif(isset($order_by) && $order_by == 'sales_manager')
		{
   			$orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
		}
		else
		{
   			$orderCalculationData = [];
		}
	  	
	  	$pdf = PDF::loadView('front/cancel_order_invoice',compact('order','key','retailer_data','sum','maker_addr','order_no','Role','order_by','orderCalculationData'));


	  	$currentDateTime = $order_no.date('H:i:s').'.pdf';
	
  		/*send email*/
  		$credentials = ['email' => $to_mail_id];
  
    	$arr_user    = get_user_by_credentials($credentials);

       	/*Get site setting data from helper*/
        $arr_site_setting = get_site_settings(['site_name','website_url']);

			

	    $pdf_arr = 	[
	    				'PDF'           => $pdf,
	            		'PDF_FILE_NAME' => $currentDateTime
	               	];

        	

		$arr_built_content = [
		 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
		                            'ORDER_NO'  		=> $order_no
		                     ];

		$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'52',$arr_user);
		$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);


			/*end*/

    }


	/*
   		Auther : Bhagaysri
   		Date : 10 Dec 2019
   		Desc : representative send notification to retailer and admin
   	*/

   	public function representative_send_notification($order_no,$user=null)
   	{

        /*get role of logedin user*/

        $role_details = $this->RoleUsersModel->where('user_id',$user->id)->with(['role_name'])->first();
        
        if(isset($role_details))
        {
            $role_arr = $role_details->toArray(); 
        }
	   
   		if($order_no)
   		{
   			
   			$obj_order_details = $this->RepresentativeLeadsModel->where('order_no',$order_no)
   			                                                    ->where('maker_id','!=',0)
   			                                                    ->first();


   			if ($obj_order_details) {
   				
   				$order_details = $obj_order_details->toArray();
   			}
   	

   			/*Get retailer details*/
   			$retailer_id = $order_details['retailer_id'];

   			$obj_retailer_details = Sentinel::findById($retailer_id);

   			if ($obj_retailer_details)
   			{

   				$retailer_details = $obj_retailer_details->toArray();
   			}

   			$admin_id = get_admin_id();

   			$first_name = isset($user->first_name)?$user->first_name:"";
   			$last_name  = isset($user->last_name)?$user->last_name:"";  

   			$arr_event                 = [];



            //time being we have hidden the admin notification for rep/sales purchase order
            
   			/*Notification to the admin */

            
            /*   $view_href   =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);

   			$arr_event['from_user_id'] = $user->id;
   			$arr_event['to_user_id']   = $admin_id;

   			$arr_event['description']  = 'New order placed from '.$role_arr['role_name']['name'].': '. $first_name.' '.$last_name.' to the retailer: '.$retailer_details['first_name'].' '.$retailer_details['last_name'].' Order No : '.$order_no;

   			$arr_event['title']        = 'New Order';
   			$arr_event['type']         = 'admin';
   			$arr_event['link']         = $view_href;


   			$this->save_notification($arr_event);*/



   			/*Notification to the retailer*/

   			$view_href   = url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_no).'/'.base64_encode($order_details['maker_id']);


   			$arr_event['from_user_id'] = $user->id;
   			$arr_event['to_user_id']   = $retailer_id;

   			$arr_event['description']  = 'New order placed from '.$role_arr['role_name']['name'].':'.$first_name.' '.$last_name.' Order No : '.$order_no;

   			$arr_event['title']        = 'New Order';
   			$arr_event['type']         = 'retailer'; 
   			$arr_event['link']         = $view_href; 


   			$this->save_notification($arr_event);

   		}
   		else{

   		}
   	}
   	/*
   		Auther : Bhagyasri
   		Date : 10 Dec 2019
   		reason: Order confirmation
   		Desc : retailer send notification to representative, vendors and admin,
   	*/

   	public function retailer_send_notification($order_id,$type)
   	{
   		$loggedInUserId = 0;
   		$user = Sentinel::check();

	    if($user)
	    {
	        $loggedInUserId = $user->id;
	    }

   		if ($order_id) {
   			
   			$obj_order_details = $this->RepresentativeLeadsModel->where('id',$order_id)->first();

   			if ($obj_order_details) {
   				
   				$order_details = $obj_order_details->toArray();
   			}
   			
   			$order_no = isset($order_details['order_no'])?$order_details['order_no']:false;
   	
   			/*Get representative or sales manager details*/
   			if ($order_details['representative_id'] != 0) {

   				$reps_id = $order_details['representative_id'];
   			}
   			elseif ($order_details['sales_manager_id'] != 0) {

   				$reps_id = $order_details['sales_manager_id'];
   			}
   			if ($reps_id) {

   				$role_details = $this->RoleUsersModel->where('user_id',$reps_id)->with(['role_name'])->first();
	        
		        if(isset($role_details))
		        {
		            $role_arr = $role_details->toArray(); 
		        }
   			}
   			$vendor_id = $order_details['maker_id'];
   			

   			$obj_representative_details = Sentinel::findById($reps_id);

   			if ($obj_representative_details) {

   				$representative_details = $obj_representative_details->toArray();
   			}
   			
   			$admin_id = get_admin_id();

   			$first_name = isset($user->first_name)?$user->first_name:"";
   			$last_name  = isset($user->last_name)?$user->last_name:"";  

   			$arr_event                 = [];

           
	
            $view_href   = url('/').'/admin/leads/view/'.base64_encode($order_details['id']);

            /*Notification to the admin */
            if ($type == 'confirm') {
            	
            	
            	$arr_event['description']  = 'Order confirmed from: ' . $first_name.' '.$last_name.' of the '.$role_arr['role_name']['name'].': '.$representative_details['first_name'].' '.$representative_details['last_name'].' Order No: '.$order_no;

   				$arr_event['title']        = 'Order Confirmed';
            }

            if ($type == 'reject') {

            	$arr_event['description']  = 'Order rejected from: ' . $first_name.' '.$last_name.' of the '.$role_arr['role_name']['name'].': '.$representative_details['first_name'].' '.$representative_details['last_name'].' Order No: '.$order_no;

   				$arr_event['title']        = 'Order Rejected';
            }
   			
   			$arr_event['from_user_id'] = $loggedInUserId;
   			$arr_event['to_user_id']   = $admin_id;
   			
   			$arr_event['type']         = 'admin';
   			$arr_event['link']         = $view_href;


   			$this->save_notification($arr_event);


   			/*Notification to the representative or sales manager*/
                
   				if($role_arr['role_name']['slug']=="representative")
   				{	
   				   $view_href   = url('/') .'/representative/leads/view_lead_listing/'.base64_encode($order_details['id']).'/'.base64_encode($order_no);
   			    }
   			    if($role_arr['role_name']['slug']=="sales_manager")
   			    {
   			      $view_href   = url('/') .'/sales_manager/leads/view_lead_listing/'.base64_encode($order_details['id']).'/'.base64_encode($order_no); 
   			    }

   			

   			if ($type == 'confirm') {
            	
            	$arr_event['description']  = 'Order confirmed from customer: ' .$first_name.' '.$last_name.' Order No: '.$order_no;

            	$arr_event['title']        = 'Order Confirmed';
            }

   			if ($type == 'reject') {

            	$arr_event['description']  = 'Order rejected from customer: ' .$first_name.' '.$last_name.' Order No: '.$order_no;

   				$arr_event['title']        = 'Order Rejected';
            }
    


   			$arr_event['from_user_id'] = $loggedInUserId;
   			$arr_event['to_user_id']   = $reps_id;
	
   			$arr_event['type']         = $role_arr['role_name']['slug']; 	
   			$arr_event['link']         = $view_href; 	

   			$this->save_notification($arr_event);

   			$obj_order_details = $this->RepresentativeLeadsModel->where('order_no',$order_no)->get();

	   			
			/*Notification to the vendors*/
			$view_href   = url('/') .'/vendor/representative_orders/view/'.base64_encode($order_no);

			if ($type == 'confirm') {
				
				$arr_event['from_user_id'] = $loggedInUserId;
				$arr_event['to_user_id']   = $vendor_id;

				$retailer_id = isset($order_details['retailer_id']) ? $order_details['retailer_id'] : 0;
				$retDummyStoreName = get_retailer_dummy_shop_name($retailer_id );

				// $arr_event['description']  = 'New order placed from '.$role_arr['role_name']['name'].': '.$representative_details['first_name'].' '.$representative_details['last_name'].' to the retailer: '. $first_name.' '.$last_name.' Order No : '.$order_no;

				$arr_event['description']  = 'New order placed from '.$role_arr['role_name']['name'].' to the customer: '.$retDummyStoreName.' Order No : '.$order_no;

				$arr_event['title']        = 'New Order';
				$arr_event['type']         = 'maker';
				$arr_event['link']         = $view_href;


				$this->save_notification($arr_event);
			}
			

   		}
   	}	


   	//Delete product image zip file after 3 daya
   	public function delete_uploaded_prod_zip()
   	{
   		$current = Carbon::now();

   		/* Get current date data */
        $objGetTodaysDeleteZipIdes = $this->ZipExtractionModel->whereDate('delete_date',Carbon::today())
                                                              ->where('zip_status',1)
                                                              ->where('is_deleted',0);
                                                          
        $GetTodaysDeleteZipIdes = $objGetTodaysDeleteZipIdes->get(['zip_name'])
                                                            ->toArray();
        
        if(count($GetTodaysDeleteZipIdes)>0)
        {
        	try
        	{
        		foreach($GetTodaysDeleteZipIdes as $deleteZip)
	            {
	                $zipName = isset($deleteZip['zip_name'])?$deleteZip['zip_name']:"";
	                $deleteFolder = Storage::delete('product_zip/'.$zipName); 
	            }

	            /* update is_deleted column if file has been successfully deleted */
	            $updateResponse = $objGetTodaysDeleteZipIdes->update(['is_deleted'=>1]);
	            return $response = true;
        	}
        	catch(\Exception $e)
        	{
        		/* catch error which is occurred while deleting file */
        		dump($e->getMessage());
        	}
        }

        return $response = false;
   	}


   	public function unlink_old_image($image_path="")
   	{
   		if($image_path!=""  && file_exists($image_path))
   		{
   			return @unlink($image_path);
   		}

   		else
   		{
   			return false;
   		}
   	}


   	/*product quantity check mail- Vishal*/
   	public function check_product_inventory()
   	{
   		$arr_prod_inventory = $this->ProductInventoryModel
   									->where('quantity',0)
   									->get(['product_id','user_id','sku_no','quantity'])
   									->toArray();

	   	if(isset($arr_prod_inventory) && count($arr_prod_inventory) > 0)
	   	{

	   		$arr_email = ['vishal.vetal@rwaltz.com',	   					    
	   					    'bhushan.pagar@rwaltz.com',
	   					    'abhijeet.bhosale@rwaltz.com',
	   					    'akshay.nair@rwaltz.com',
	   					    'bhavana.shirude@rwaltz.com',
	   					    'jaydip.bachhav@rwaltz.com',
	   					    'mona.bapte@rwaltz.com',
	   					    'nitish.kasar@rwaltz.com',
	   					    'pravin.thakare@rwaltz.com',
	   					    'priyanka.kedare@rwaltz.com',	   					    
	   					    'shital.more@rwaltz.com'
	   					];	

	        $content = json_encode($arr_prod_inventory);
    		$admin_role = Sentinel::findRoleBySlug('admin');        
	        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
	        $admin_id = $admin_obj->user_id;      
	 		$from_user_id = Sentinel::findById($admin_id)->email;
			$to_mail_id = $arr_email;

			$arr_site_setting  = get_site_settings(['site_name','website_url']);
			
			$send_mail = Mail::send(array(),array(), function($message) use($content,$to_mail_id,$from_user_id)
	        {
	          	$message->from($from_user_id,$arr_site_setting['site_name'].' Admin');
	          	$message->to($to_mail_id)			          	
			   	->subject('Product Inventory Reminder (Following Product Quantity is 0)')
			    ->setBody($content, 'text/html');
	        });

			    return true;
	   		/*end*/
	   	}
	   	return true;
   	}
   	/*end*/



   	function order_products_for_list($id=null,$order_no=null,$products_arr=[])
   	{
		if(isset($products_arr) && count($products_arr)>0)
		{
		    $products = '';

		    foreach ($products_arr as $key => $product) 
		    {
					
		    	$sku_no = isset($product['sku_no'])?$product['sku_no']:"";
		        $skuThumbImage = "";
		        $skuThumbImage = get_sku_image($sku_no);
		      if(isset($product['color']) && isset($product['size_id'])){
						$products .= '<tr>
		                        <td><img class="zoom-img" src="'.$skuThumbImage.'" height="100px" width="100px"></td>
														<td>'.$product['product_details']['product_name'].'- SKU: ('.$sku_no.')|Color:&nbsp;'.$product['color'].'&nbsp;|Size:&nbsp;'.get_size_from_id($product['size_id']).'</td>
														<td>'.$product['qty'].'</td>
		                      </tr>';
					}elseif(isset($product['size_id'])){
						$products .= '<tr>
													<td><img class="zoom-img" src="'.$skuThumbImage.'" height="100px" width="100px"></td>
													<td>'.$product['product_details']['product_name'].'- SKU: ('.$sku_no.')&nbsp;|Size:&nbsp;'.get_size_from_id($product['size_id']).'</td>
													<td>'.$product['qty'].'</td>
												 </tr>';
					}elseif(isset($product['color'])){
						$products .= '<tr>
													<td><img class="zoom-img" src="'.$skuThumbImage.'" height="100px" width="100px"></td>
													<td>'.$product['product_details']['product_name'].'- SKU: ('.$sku_no.')|Color:&nbsp;'.$product['color'].'</td>
													<td>'.$product['qty'].'</td>
												</tr>';
					}
					else{
						$products .= '<tr>
		                        <td><img class="zoom-img" src="'.$skuThumbImage.'" height="100px" width="100px"></td>
														<td>'.$product['product_details']['product_name'].'- SKU: ('.$sku_no.')</td>
														<td>'.$product['qty'].'</td>
		                      </tr>';
					}
		        
		    }
		}
		else
		{
		    $products = 'No Record Found';
		}
		    
	    return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$id.'" data-toggle="modal" data-target="#viewProductsModel_'.$id.'">View Products<span> '.count($products_arr).'</span></a>

	    <div id="viewProductsModel_'.$id.'" class="modal fade" role="dialog">
	      <div class="modal-dialog modal-lg view-produt-modal-dialog">

	        <!-- Modal content-->
	        <div class="modal-content">
	          <div class="modal-header">
	            <h4 class="modal-title">Order No. '.$order_no.'</h4>
	            <button type="button" class="close" data-dismiss="modal">&times;</button>
	          </div>
	          <div class="modal-body">
	            <div class="viewProductsModelList">
	              <table id="tbl_'.$id.'">
	                  
	                    <table class="table table-bordered product-list">
	                        <thead>
	                          <tr>
	                            <th>Product Image</th>
															<th>Product Name</th>
															
															<th>Product Quantity</th>         
	                          </tr>
	                        </thead>
	                       <tbody>'.$products.'</tbody>
	                      </table>
	                  
	              </table>
	            </div>  
	          </div>
	          <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	          </div>
	        </div>

	      </div>
	    </div>';  
   	}

   	function order_rep_sles_products_for_list($id=null,$order_no=null,$products_arr=[])
   	{
		if(isset($products_arr) && count($products_arr)>0)
		{
		    $products = '';

		    foreach ($products_arr as $key => $product) 
		    {
		    	$sku_no = isset($product['sku'])?$product['sku']:"";
		        $skuThumbImage = "";
		        $skuThumbImage = get_sku_image($sku_no);
		      
		        $products .= '<tr>
		                        <td><img class="zoom-img" src="'.$skuThumbImage.'" height="100px" width="100px"></td>
		                        <td>'.$product['product_details']['product_name'].'- SKU: ('.$sku_no.')</td>
		                        <td>'.$product['qty'].'</td>
		                      </tr>';
		    }
		}
		else
		{
		    $products = 'No Record Found';
		}
		    
	    return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$id.'" data-toggle="modal" data-target="#viewProductsModel_'.$id.'">View Products<span> '.count($products_arr).'</span></a>

	    <div id="viewProductsModel_'.$id.'" class="modal fade" role="dialog">
	      <div class="modal-dialog modal-lg view-produt-modal-dialog">

	        <!-- Modal content-->
	        <div class="modal-content">
	          <div class="modal-header">
	            <h4 class="modal-title">Order No. '.$order_no.'</h4>
	            <button type="button" class="close" data-dismiss="modal">&times;</button>
	          </div>
	          <div class="modal-body">
	            <div class="viewProductsModelList">
	              <table id="tbl_'.$id.'">
	                  
	                    <table class="table table-bordered product-list">
	                        <thead>
	                          <tr>
	                            <th>Product Image</th>
	                            <th>Product Name</th>
	                            <th>Product Quantity</th>                                
	                          </tr>
	                        </thead>
	                       <tbody>'.$products.'</tbody>
	                      </table>
	                  
	              </table>
	            </div>  
	          </div>
	          <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	          </div>
	        </div>

	      </div>
	    </div>';  
   	}

   	/*delete zero user id record from temp bag table, before a day*/
   	public function delete_tempbag_zero()
   	{
   		$current = Carbon::now();   		
   		$previous = $current->copy()->subDays(3);
   		
   		$bag_data = $this->TempBagModel
   					->where('user_id',0)
   					->whereDate('updated_at','<',$previous)
   					->delete();   										

   		return true;

   	}

   	/* Function to send pdf in payment invoice mail */
   	public function send_payment_invoice($arr_mail_data,$arrOrderData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,$isRepSalesOrder=false)
    {

    	// dd($arr_mail_data,$arrOrderData,$arrUserData,$bulk_transaction_key,$arrOrderAmount,$isRepSalesOrder);
      $invoiceData['invoice_no'] = isset($bulk_transaction_key)?$bulk_transaction_key:0;
      $invoiceData['invoice_date'] = date('d/m/Y');

      $pdf = PDF::loadView('front/admin_commission_paid_invoice',compact('arrUserData','arrOrderData','invoiceData','arrOrderAmount','isRepSalesOrder'));
        
      $currentDateTime = $bulk_transaction_key.date('H:i:s').'.pdf';

      $pdf_arr =  [
                  'PDF'           => $pdf,
                  'PDF_FILE_NAME' => $currentDateTime
                  ];

      return $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

       
    }

    /* Function to send pdf in payment invoice mail */
   	public function send_payment_invoice_to_admin($arr_mail_data,$arrOrderData,$arrUserData,$bulk_transaction_key,$arrOrderAmount)
    {

    	//dd($arrOrderData);
      $invoiceData['invoice_no'] = isset($bulk_transaction_key)?$bulk_transaction_key:0;
      $invoiceData['invoice_date'] = date('d/m/Y');

      $pdf = PDF::loadView('front/invoice_send_to_admin',compact('arrUserData','arrOrderData','invoiceData','arrOrderAmount'));
        
      $currentDateTime = $bulk_transaction_key.date('H:i:s').'.pdf';

      $pdf_arr =  [
                  'PDF'           => $pdf,
                  'PDF_FILE_NAME' => $currentDateTime
                  ];

      return $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

       
    }



   	/*this function for retailer to show order data*/
  	function get_order_details($product_arr,$promotion_arr)
  	{
      	$final_total = []; $total_discount_amount = 0;
  
      	if($product_arr)
      	{
          	$total_amount = $ordSubTotal = $ordProductDiscout = $ordShipCharge = $ordShipDiscout =$orderShippingCharge = 0;

          	foreach ($product_arr as $product) 
          	{ 
              	$orderShippingCharge += $product['shipping_charges'] - $product['shipping_discount'];

              	$subTotal = $product['item_qty'] * $product['wholesale_price'];
              	$total_amount += $subTotal + $product['shipping_charges'] - $product['shipping_discount'] - $product['product_discount_amount'];
              	$ordSubTotal += $subTotal;
              	$ordProductDiscout += $product['product_discount_amount'];
              	$ordShipCharge += $product['shipping_charges'];
              	$ordShipDiscout += $product['shipping_discount'];
          	}

            $final_total['sub_total']         = $ordSubTotal;
            $final_total['product_discount']  = $ordProductDiscout;
            $final_total['ship_discount']     = $ordShipDiscout;
            $final_total['ship_charges']      = $ordShipCharge;
            $final_total['sub_grand_total']   = $total_amount;
      	}

    	/******************* get promo code data  ***********************/

	    if(isset($promotion_arr) && count($promotion_arr)>0)
	    {
	       
	         $shipDiff = $ordShipCharge-$ordShipDiscout;
	         
	        $promo_discount_amt = 0.00;

	        foreach ($promotion_arr as $promoKey => $promotion)
          	{ 
              	$promo_shipping_charges = isset($promotion['final_total'][$promoKey]['shipping_charges'])?$promotion['final_total'][$promoKey]['shipping_charges']:1;
 
              	$promo_discount_amt += isset($promotion['final_total'][$promoKey]['discount_amt'])?$promotion['final_total'][$promoKey]['discount_amt']:0;
           
              	$final_total['discount_amt'] =  $promo_discount_amt;
          	}
	        
	   
	        if($promo_shipping_charges == 0 && $promo_discount_amt)
	        {	             
	            //  if promotion discount type is freeshipping and  % off 
	            $final_total['grand_total'] = $ordSubTotal - $ordProductDiscout - $promo_discount_amt;

             	$final_total['promotion_shipping_charges'] = $shipDiff;

        	}
	        elseif($promo_shipping_charges == 0)
	        { 
             	// $promo_shipping_charges == 0 then order type is free shipping 
              	$shipDiff = $ordShipCharge-$ordShipDiscout;

	            $final_total['grand_total'] = $final_total['sub_grand_total']-$shipDiff;

              	$final_total['promotion_shipping_charges'] = $shipDiff;

	        }
          	else
          	{ 
        	    $final_total['grand_total'] = $ordSubTotal - $ordProductDiscout + $ordShipCharge - $ordShipDiscout - $promo_discount_amt;
    	  	}
	    } 

    	return $final_total;     
  	}

  	/*this function for vendor to show order data*/
	function get_vendor_order_details($product_arr,$promotion_arr,$vendor_id=false)
	{
	    $final_total = []; $total_discount_amount = 0;
	    
      	if($product_arr)
  		{
          	$total_amount = $ordSubTotal = $ordProductDiscout = $ordShipCharge = $ordShipDiscout =$orderShippingCharge = 0;

		    foreach ($product_arr as $product) 
	    	{ 
		        $orderShippingCharge += $product['shipping_charges'] - $product['shipping_discount'];

		          $subTotal = $product['item_qty'] * $product['wholesale_price'];
		          $total_amount += $subTotal + $product['shipping_charges'] - $product['shipping_discount'] - $product['product_discount_amount'];
		          $ordSubTotal += $subTotal;
		          $ordProductDiscout += $product['product_discount_amount'];
		          $ordShipCharge += $product['shipping_charges'];
		          $ordShipDiscout += $product['shipping_discount'];
		    }

	            $final_total['sub_total']         = $ordSubTotal;
	            $final_total['product_discount']  = $ordProductDiscout;
	            $final_total['ship_discount']     = $ordShipDiscout;
	            $final_total['ship_charges']      = $ordShipCharge;
	            $final_total['sub_grand_total']   = $total_amount;
	    }

	      /******************* get promo code data  ***********************/

	    if(isset($promotion_arr) && count($promotion_arr)>0)
	    {
	         
            $shipDiff = $ordShipCharge-$ordShipDiscout;
           
            $promo_discount_amt = 0.00;
            $discount_percent = 0.00;

            /*foreach ($promotion_arr as $promoKey => $promotion)
            {*/  
                $promo_shipping_charges = isset($promotion_arr['final_total'][$vendor_id]['shipping_charges'])?$promotion_arr['final_total'][$vendor_id]['shipping_charges']:1;
              
                $promo_discount_amt += isset($promotion_arr['final_total'][$vendor_id]['discount_amt'])?$promotion_arr['final_total'][$vendor_id]['discount_amt']:0;

                $discount_percent += isset($promotion_arr['final_total'][$vendor_id]['discount_percent'])?$promotion_arr['final_total'][$vendor_id]['discount_percent']:0;
              
                $final_total['discount_amt'] =  $promo_discount_amt;
                $final_total['discount_percent'] =  $discount_percent;
			/*
			            }
			          
			 */
            if($promo_shipping_charges == 0 && $promo_discount_amt)
            { 
                //  if promotion discount type is freeshipping and  % off 
                $final_total['grand_total'] = $ordSubTotal - $ordProductDiscout - $promo_discount_amt;

                $final_total['promotion_shipping_charges'] = $shipDiff;

            }
            elseif($promo_shipping_charges == 0)
            { 
               // $promo_shipping_charges == 0 then order type is free shipping 
                $shipDiff = $ordShipCharge-$ordShipDiscout;

                $final_total['grand_total'] = $final_total['sub_grand_total']-$shipDiff;

                $final_total['promotion_shipping_charges'] = $shipDiff;

            }
            else
            {
              $final_total['grand_total'] = $ordSubTotal - $ordProductDiscout + $ordShipCharge - $ordShipDiscout - $promo_discount_amt;
            }

	    } 
	    
	    return $final_total; 
	}
}

?>