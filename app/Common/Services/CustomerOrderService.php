<?php
namespace App\Common\Services;
 
use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\TempBagModel;
use App\Models\TransactionsModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\TransactionMappingModel;
use App\Models\RepresentativeModel;
use App\Models\RetailerModel;
use App\Models\CustomerModel;
use App\Models\EmailTemplateModel;
use App\Models\PromoCodeRetailerMappingModel;
use App\Models\PromoCodeCustomerMappingModel;
use App\Models\ZipExtractionModel;
use App\Models\StripeTransactionModel;
use App\Models\SiteSettingModel;
use App\Common\Services\EmailService;
use App\Common\Services\StripePaymentService;
use App\Common\Services\InventoryService;
use App\Common\Services\MyCartService;
use App\Common\Services\InfluencerService;
use App\Common\Services\orderDataService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;

use Mail;
use Request;
use Stripe;
use Carbon\Carbon;
use DateTime;

use App\Events\NotificationEvent;
use Session, Sentinel, DB,PDF,Storage;

class CustomerOrderService
{
	public function __construct(
									CountryModel $CountryModel,
									StateModel $StateModel,
									CityModel $CityModel,
                                    EmailService $EmailService,
									RepresentativeLeadsModel $RepresentativeLeadsModel,
									RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
									RetailerQuotesModel $RetailerQuotesModel,
									CustomerQuotesModel $CustomerQuotesModel,
									RoleUsersModel $RoleUsersModel,
									RetailerQuotesProductModel $RetailerQuotesProductModel,
									CustomerQuotesProductModel $CustomerQuotesProductModel,
									TempBagModel $TempBagModel,
									TransactionsModel $TransactionsModel,
									MakerModel $MakerModel,
									RepresentativeModel $RepresentativeModel,
									RetailerModel $RetailerModel,
									CustomerModel $CustomerModel,
									EmailTemplateModel $EmailTemplateModel,
									UserModel $UserModel,
									StripePaymentService $StripePaymentService,
									PromoCodeRetailerMappingModel $PromoCodeRetailerMappingModel,
									PromoCodeCustomerMappingModel $PromoCodeCustomerMappingModel,
                                    TransactionMappingModel $TransactionMappingModel,
                                    InventoryService $InventoryService,
                                    MyCartService $MyCartService,
                                    InfluencerService $InfluencerService,
                                    orderDataService $orderDataService,
                                    ZipExtractionModel $ZipExtractionModel,
                                    StripeTransactionModel $StripeTransactionModel,
                                    SiteSettingModel $SiteSettingModel,
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
		$this->CustomerQuotesModel             = $CustomerQuotesModel;
		$this->RetailerQuotesProductModel      = $RetailerQuotesProductModel;
		$this->CustomerQuotesProductModel      = $CustomerQuotesProductModel;
		$this->TempBagModel                    = $TempBagModel;
		$this->MakerModel 					   = $MakerModel;
		$this->RetailerModel 				   = $RetailerModel;
		$this->CustomerModel 				   = $CustomerModel;
		$this->EmailTemplateModel			   = $EmailTemplateModel;
		$this->RoleUsersModel			       = $RoleUsersModel;
		$this->RepresentativeModel 			   = $RepresentativeModel;
        $this->EmailService                    = $EmailService;
        $this->InventoryService 			   = $InventoryService;
        $this->MyCartService 			       = $MyCartService;
        $this->TransactionMappingModel         = $TransactionMappingModel;
        $this->StripePaymentService            = $StripePaymentService;
        $this->InfluencerService               = $InfluencerService;
        $this->orderDataService                = $orderDataService;
        $this->PromoCodeRetailerMappingModel   = $PromoCodeRetailerMappingModel;
        $this->PromoCodeCustomerMappingModel   = $PromoCodeCustomerMappingModel;
        $this->ZipExtractionModel              = $ZipExtractionModel;
        $this->StripeTransactionModel          = $StripeTransactionModel;
        $this->SiteSettingModel				   = $SiteSettingModel; 
      	$this->product_default_img_path = config('app.project.img_path.product_default_images');
      	$this->CommissionService          	   = $CommissionService;
      	$this->HelperService            	   = $HelperService;
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


	public function store_customer_cart_items($transaction_id=false,$order_addr_data,$payment_type=false)
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
		        }
	      
		    	$order_details = [];
	
		    	$order_details = $this->RetailerQuotesModel
		    	                      ->where('id',$order_id)
		    					      ->with(['transaction_details','quotes_details','user_details'])
		    					      ->where('id',$order_id)->first();

               
				$arr_data = $arr_charge_data = [];
		    	    	
		    	if($order_details)
		    	{
		    		$order_details = $order_details->toArray();
                    

		    
		    		$arr_charge_data['order_amount'] = isset($order_details['total_wholesale_price'])?$order_details['total_wholesale_price']:0;

		    		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

		    		$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

		    		$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';

		    	
			        if(isset($arr_charge_data) && count($arr_charge_data) > 0)
		    		{

		    			// Payment duducted
		    			$charge = $this->StripePaymentService->create_charge($arr_charge_data);
		    			
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

				        	/* Update payment status in order table by Harshada on date 21 Oct 2020 */
					        $this->RetailerQuotesModel->where('id',$order_id)   										  ->where('order_no',$ord_no)										  ->update(['is_payment_status' => '1']);


				        	$arr_data['user_id']             = $order_details['retailer_id'] or '';
				        	$arr_data['order_id']            = $order_details['id'] or '';
				        	$arr_data['order_no']            = $order_details['order_no'] or '';
				        	$arr_data['amount']              = $order_details['total_wholesale_price'] or '';
				        	//$arr_data['transaction_id']      = $charge->id or '';
				        	$arr_data['transaction_id']      = isset($charge['id'])?$charge['id']:"";
				        	$arr_data['transaction_status']  = $status or '';
				        	$arr_data['payment_type']        = '1' or '';

				        	$transaction_entry = $this->TransactionMappingModel->create($arr_data);

				        	if($status == '2')
				        	{
				        		

				        		/* get admin id */

				        		$admin_id = get_admin_id();


				        		$maker_details = \Sentinel::findById($order_details['maker_id']);
				        		$view_href   =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);

                          
				        		/* send admin notification*/
				        		$arr_notify_data                 = [];
							    $arr_notify_data['from_user_id'] = $order_details['retailer_id'] or '';
							    $arr_notify_data['to_user_id']   = $admin_id or '';

							    $arr_notify_data['description']  = 'Payment is done by '.$order_details['user_details']['first_name'].' '.$order_details['user_details']['last_name'].' for order('.$order_details['order_no'].')';

							    $arr_notify_data['title']        = 'Order Payment';
							    $arr_notify_data['type']         = 'admin';  
							    $arr_notify_data['link']         = $view_href;  

				        		$this->save_notification($arr_notify_data);

             					$response['status']      = 'success';
                                $response['description'] = 'Payment done successfully.';
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
		    		else
		    		{   
		    			$response['status']      = 'warning';
			        	$response['description'] = 'Something went wrong,please try again.';
			        	return $response;
		    		
		    		}
		    	}
	        }
	        elseif(isset($representative_order_id) && $representative_order_id!='')
	        {

                $loggedInUserId = 0;
		        $user = \Sentinel::check();

		        if($user && $user->inRole('retailer'))
		        {
		            $loggedInUserId = $user->id;
		        }

                if(Session::get('payment_type') == 'Online/Credit') 
                {
                	$updated_data = [];

		            $updated_data['payment_term'] = 'Online/Credit';

		            $update = $this->RepresentativeLeadsModel
		                           ->where('id',$representative_order_id)
		                           ->update($updated_data);  
                }
		                 
	
		    	$order_details = $this->RepresentativeLeadsModel
		    						  ->with(['transaction_details',
		    								  'leads_details',
		    								  'user_details',
		    								  'retailer_user_details'])
		    						  ->where('id',$representative_order_id)
		    						  ->first();

		    							   
		    								   
				$arr_data = $arr_charge_data = [];
		    	    	
		    	if($order_details)
		    	{
		    		$order_details = $order_details->toArray();

		            
		    		$arr_charge_data['order_amount'] = isset($order_details['total_wholesale_price'])?$order_details['total_wholesale_price']:0;

		    		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

		    		$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

		    		$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';

			        if(isset($arr_charge_data) && count($arr_charge_data) > 0)
		    		{
		    		

		    			// Payment duducted
		    			$charge = $this->StripePaymentService->create_charge($arr_charge_data);
		    			
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
					        	$this->RepresentativeLeadsModel->where('id',$order_id)   										  ->where('order_no',$ord_no)										  ->update(['is_payment_status' => '1']);


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
				        		
				        		$admin_id = get_admin_id();

				        		$maker_details = \Sentinel::findById($order_details['maker_id']);
				        	    $view_href     =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']).'/'.base64_encode($order_details['order_no']);

				        		/* send admin notification*/
				        		$arr_notify_data                 = [];
							    $arr_notify_data['from_user_id'] = $order_details['retailer_id'] or '';
							    $arr_notify_data['to_user_id']   = $admin_id or '';

							  

							    $arr_notify_data['description']  = 'Payment is done by '.$order_details['retailer_user_details']['first_name'].' '.$order_details['retailer_user_details']['last_name'].' for order('.$order_details['order_no'].')';

							    $arr_notify_data['title']        = 'Order Payment';
							    $arr_notify_data['type']         = 'admin';  
							    $arr_notify_data['link']         = $view_href;  


				        		$this->save_notification($arr_notify_data);
      	
				        		$this->send_maker_mail($order_details['order_no']);

				              
                                          
					        	$response['status']      = 'success';
                                $response['description'] = 'Payment done successfully.';
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
	        
	        	$payment_term = 'Online/Credit';
	        	$next_due_date = '';
	        }


		    if(!Session::has('bag_data'))
		    {
		        $response_arr['status']   = 'failure';
		        return $response_arr;
		    }
	       

	        $loggedInUserId = $quote_id = 0;
	        $user = Sentinel::check();



	        if($user && $user->inRole('customer'))
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

           
            $bag_data = $this->MyCartService->get_items();

			if($bag_data)
			{
				$bag_data->toArray();
	    		$bag_data  = isset($bag_data['product_data'])?$bag_data['product_data']:"";
			}			 
	    	
	        $bag_arr   = json_decode($bag_data,true);
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
	        	if (is_array(Session::get('customer_promotion_data')) && count(Session::get('customer_promotion_data')) > 0) 
	        	{
	        		$promotion_session_data = Session::get('customer_promotion_data');
	        	}

	        	DB::beginTransaction();
	        	if(count($result)>0)
	        	{
	        	
	        		$order_no = str_pad('J2',  10, rand('1234567890',10));

	        		$order_no = $this->orderDataService->verify_order_no($order_no);
	        		
	        		$all_product_arr = [];
	        		$promo_discount_amount = $promo_codeId = $promo_code =  $discount_percent = 0;
	        		foreach ($result as $key => $product_arr) 
			        {

			        	

		        		$promo_discount_amount = 0;
		        		// $promo_shipping_charges = 1;

		        		$promo_code = '';
		        		array_push($all_product_arr,$product_arr);
		        	
			        	$total_retail_price = $total_wholesale_price = 0;
			        	
			        	$total_price = array_sum(array_column($product_arr,'total_price'));

		        		$total_wholesale_price = array_sum(array_column($product_arr,'total_wholesale_price'));

		        		$total_retail_price = $total_price;
			        				        	
			        	
			        	$pro_ship_charge =0;
			        	$pro_ship_dis =0;
			        	$pro_dis = 0;
			        	foreach ($product_arr as $pro_key => $pro) 
			        	{
			        		

			        		$pro_ship_charge 	+= isset($pro['shipping_charges'])?$pro['shipping_charges']:0;
			        		
			        		$pro_ship_dis 		+= isset($pro['shipping_discount'])?$pro['shipping_discount']:0;
			        		
			        		$pro_dis 			+= isset($pro['product_discount_amount'])?$pro['product_discount_amount']:0;

			        	}

			        	if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
			        	{

			        		$total_retail_price = $total_retail_price+$pro_ship_charge-$pro_ship_dis-$pro_dis;
			        		
			        		$promo_code  = isset($promotion_session_data['promo_code'])?$promotion_session_data['promo_code']:0;

			        		$promo_codeId =  isset($promotion_session_data['promo_codeId'])?$promotion_session_data['promo_codeId']:0;


			        		/* Calculation Promotion Discount Amount */
			        		$discount_percent = isset($promotion_session_data['discount_percent'])?$promotion_session_data['discount_percent']:0;
			        		$promo_discount_amount  = ($discount_percent*$total_retail_price/100);
			        		
			        		$total_retail_price = $total_retail_price - $promo_discount_amount;
			        		
			        	}
			        	else{
			        	
			         		$total_retail_price = $total_retail_price+$pro_ship_charge-$pro_ship_dis-$pro_dis;
			         	}

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
					    $quotes_arr['customer_id']           = $loggedInUserId;
					    $quotes_arr['status']                = 0;
					    $quotes_arr['transaction_id']        = $transaction_id or '';
					    $quotes_arr['total_retail_price']    = $total_retail_price;
					    $quotes_arr['total_wholesale_price'] = $total_wholesale_price;
					    $quotes_arr['order_cancel_status']   = 0;

					    $quotes_arr['shipping_addr']	      = $order_addr_data['shipping'];
					    $quotes_arr['promotion_discount']	  = $promo_discount_amount;
					    $quotes_arr['discount_percent']	      = $discount_percent;
					    $quotes_arr['promo_code']	          = $promo_code;
					    $quotes_arr['promo_code_id']	      = $promo_codeId;
					    
					    $quotes_arr['billing_addr']			  = $order_addr_data['billing'];
					    
					    $quotes_arr['payment_term']           = $payment_term;

					    $quotes_arr['influencer_id'] 		  = isset($promotion_session_data['influencer_id'])?$promotion_session_data['influencer_id']:0;
					    $quotes_arr['is_direct_payment']      = $is_direct_payment;
				    	
			        	$create_quote = $this->CustomerQuotesModel->create($quotes_arr);
			        	
			        	/*Store mapping data of customer used promo code*/
			        	if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
			        	{

			        		$promo_codeId  = isset($promotion_session_data['promo_codeId'])?$promotion_session_data['promo_codeId']:0;


			        		$promo_mappping['customer_quotes_id'] = isset($create_quote->id)?$create_quote->id:0;
			        		$promo_mappping['customer_id'] 		  = $loggedInUserId;
			        		$promo_mappping['promo_code_id'] 	  = $promo_codeId;

			        		$retailer_promo_mapping = $this->PromoCodeCustomerMappingModel->create($promo_mappping);

			        		/* Send Notification to Influencer (START)*/
				            $admin_id = get_admin_id();
				            
				            $first_name = isset($user->first_name)?$user->first_name:"";
				        	$last_name  = isset($user->last_name)?$user->last_name:"";  

				            $influencer_panel_slug = config('app.project.influencer_panel_slug');
				            $view_href             = url($influencer_panel_slug.'/customer_orders/view/'.base64_encode($create_quote->id));

				            $arr_notify_data                 = [];
				            $arr_notify_data['from_user_id'] = $admin_id or '';
				            $arr_notify_data['to_user_id']   = $promotion_session_data['influencer_id'] or '';

				            $arr_notify_data['description']  = 'Customer <b>'.$first_name.' '.$last_name.'</b> has applied your promo code <b>'.$promo_code.'</b> for the purchase.';

				            $arr_notify_data['title']        = 'Your promo code has been used';
				            $arr_notify_data['type']         = 'influencer';
				            $arr_notify_data['link']         = $view_href;

				            $this->InfluencerService->save_notification($arr_notify_data);

				            /* Send Notification to Influencer (END)*/

				            /* Send Email Notification to Influencer */

				            $redirection_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.$view_href.'">View Details</a><br/>';

							$influencer_details_obj = Sentinel::findById($arr_notify_data['to_user_id']);
							
							$site_setting_obj = SiteSettingModel::first();
							if($site_setting_obj)
							{
								$site_setting_arr = $site_setting_obj->toArray();            
							}

							/*$site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';*/

							/*Get site setting data from helper*/
                            $arr_site_setting = get_site_settings(['site_name','website_url']);
				        
				            if(isset($influencer_details_obj) && $influencer_details_obj)
				            {
				                $arr_influencer_details = $influencer_details_obj->toArray();  
				                
				                $arr_built_content = ['USER_FNAME'           => $arr_influencer_details['first_name'],
				                                      'APP_NAME'             => isset($arr_site_setting['site_name'])?$arr_site_setting['site_name']:'',
				                                      'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
													  'EMAIL_DESCRIPTION'    => isset($arr_notify_data['description'])?$arr_notify_data['description']:'',
													  'SITE_URL'     	     => $arr_site_setting['website_url']
				                                     ];

				                $arr_mail_data                      = [];
				                $arr_mail_data['email_template_id'] = '64';
				                $arr_mail_data['arr_built_content'] = $arr_built_content;
				                $arr_mail_data['arr_user']          = $arr_influencer_details;

				                $email_status  = $this->EmailService->send_mail($arr_mail_data);
				                
				            }                

				            /* Send Email Notification to Influencer (END)*/
			        	
			        	}

			        	foreach($product_arr as $product)
			        	{ 
			        	
			        		$quote_product_arr = [];
			        		
			        		$quote_product_arr['customer_quotes_id'] = $create_quote->id;
			        		$quote_product_arr['product_id']         = $product['product_id'];
			        		$quote_product_arr['sku_no']             = $product['sku_no'];
			        		$quote_product_arr['qty']                = $product['item_qty'];
			        		$quote_product_arr['retail_price']       = $product['retail_price'];
			        		$quote_product_arr['unit_retail_price']= $product['retail_price'];
			        		$quote_product_arr['wholesale_price']    = $product['wholesale_price'];
			        		$quote_product_arr['description']  		 = '';

			        		

			        		$quote_product_arr['shipping_charge']    = isset($product['shipping_charges'])?$product['shipping_charges']:0;
			        		$quote_product_arr['shipping_discount']  = isset($product['shipping_discount'])?$product['shipping_discount']:0;

			        	

			        		$quote_product_arr['product_discount']  = isset($product['product_discount_amount'])?$product['product_discount_amount']:0;
			        		
			        		$grand_total = $product['total_price'] + $quote_product_arr['shipping_charge']-$quote_product_arr['shipping_discount']-$product['product_discount_amount']; 
			        	
			        		$quote_product_arr['retail_price']    = $grand_total;
			        		 	        		
			        		$create_quote_product = $this->CustomerQuotesProductModel->create($quote_product_arr);
			        		
			        	}

			        	$quote_id = $create_quote->id;



			         /******************Notification to Admin START*******************************/


				        $first_name = isset($user->first_name)?$user->first_name:"";
				        $last_name  = isset($user->last_name)?$user->last_name:"";  

				        $order_view_link = url('/').'/vendor/customer_orders/view/'.base64_encode($create_quote->id);


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
				        $order_id = $this->CustomerQuotesModel->select('id')->where('order_no',$order_no)->first()->id;
				        $view_href     =  url('/').'/admin/customer_orders/view/'.base64_encode($order_id);


				        $first_name = isset($user->first_name)?$user->first_name:"";
				        $last_name  = isset($user->last_name)?$user->last_name:"";  

				        $arr_event                 = [];
				        $arr_event['from_user_id'] = $loggedInUserId;
				        $arr_event['to_user_id']   = $admin_id;

				    
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

			     	   	$product_total_price = $total_product_shipping_charges = $total_product_shipping_disc = $total_product_disc = 0;

			     	   	foreach ($product_data as $key2 => $product) 
			     	   	{
			     	   		if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
				        	{
				        		$product_total_price +=$product['total_price'];

				        		$total_product_shipping_charges +=$product['shipping_charges'];
				        		$total_product_shipping_disc +=$product['shipping_discount'];
				        		$total_product_disc +=$product['product_discount_amount'];
				        		

				        		$promo_code  = isset($promotion_session_data['promo_code'])?$promotion_session_data['promo_code']:0;

				        	}
			     	   		$product_details              = get_product_details($product['product_id']);
			     	   		$product_name 				  = $product_details['product_name'];
			     	   		$order[$key2]['order_no']     = $order_no or '';
			     	   		$order[$key2]['product_name'] = $product_name;
			     	   		$order[$key2]['sku_no']     = $product['sku_no'] or '';
			     	   		$order[$key2]['order_no']     = $order_no or '';
			     	   		$order[$key2]['item_qty']     = $product['item_qty'];
			     	   		$order[$key2]['unit_price']   = $product['retail_price'];
			     	   		$order[$key2]['total_retail_price'] = $product['total_price'];
			     	   		
			     	   		
			     	   		$order[$key2]['product_discount_amount'] = $product['product_discount_amount'];

			     	   		
		     	   			$order[$key2]['shipping_discount'] = $product['shipping_discount'];
		     	   			

		     	   			$order[$key2]['shipping_charges']  = $product['shipping_charges'];
		     	   			$order[$key2]['maker_company_name'] = get_maker_company_name($product['maker_id']);

		     	   			

		     	   		    
			     	   	}

			     	   	if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
				        {
				     	   	/* Calculate Promotion Discount Amount */
			        		$discount_percent       = isset($promotion_session_data['discount_percent'])?$promotion_session_data['discount_percent']:0;

			        		$product_total_price = $product_total_price + $total_product_shipping_charges - $total_product_shipping_disc - $total_product_disc;
			        		$promo_discount_amount  = ($discount_percent*$product_total_price/100);
				        }

			     	   

			     	    $maker[$key1]['order_details']= $order;
			     	    
			     	    $sum = 0;
			     	    foreach ($order as $key => $order_data) 
			     	   	{
						   $sum += $order_data['total_retail_price'];
						   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
					   	}

			     	    $maker[$key1]['email_id'] = $arr_email;
			     	  	
			     	  

	                    //Create pdf here for maker

	                    $customer_data = $this->CustomerQuotesModel 
	                    					  ->with(['quotes_details'])
	                    					  ->where('order_no',$order_no)
	                    					  ->first()
	 										  ->toArray();
		       	   	

		  			   	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {
		  	     	   			
		       	   			$sum = $sum - $promo_discount_amount;
		       	   			$customer_data['promo_discount'] = $promo_discount_amount;
		       	   		}
		       	   		else{

		       	   			$sum = $sum;
		       	   		}


	 					$customer_data['user_details'] = $this->UserModel
	 										  ->with(['customer_details'])	
	                    					  ->where('id',$customer_data['customer_id'])
	                    					  ->first()
	 										  ->toArray();


	 					$order_date = '';					  

	 					$maker_id = $maker[$key1]['maker_id'];

	 					
	 					$maker_addr = $this->UserModel->where('id',$key1)
											  ->first()
	 										  ->toArray();
		 	  			
		 	  			$company_name = get_maker_company_name($key1);
		 	  			$maker_addr['company_name'] = isset($company_name)?$company_name:'';
			     	 
			     	    $discount_percent = isset($discount_percent)?$discount_percent:"0";

		 	  			$pdf = PDF::loadView('front/customer_invoice',compact('order','key','customer_data','sum','maker_addr','order_no','discount_percent'));
		 	  		

		 	  			$currentDateTime = $order_no.date('H:i:s').'.pdf';


                        $pdf_arr = 	[
							    		'PDF'           => $pdf,
							            'PDF_FILE_NAME' => $currentDateTime
							        ];



	     	 			$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;
	     	 		
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

			     	/* send retailer mail */

                    $customer_mail = $user->email;			     	 
 
			     	$this->send_customer_mail($customer_mail,$pro_arr,$order_no,$file_to_path,$maker_addr,$customer_data);


                    /*send the mail to admin*/

                    $admin_email = 0;

                    $admin_details = $this->UserModel->where('id',1)->first();

                    if(isset($admin_details))
                    {
                       $admin_mail = $admin_details->email;
                    }

                    $this->send_customer_mail($admin_mail,$pro_arr,$order_no,$file_to_path,$maker_addr,$customer_data);


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

	public function send_customer_mail($to_mail_id,$product_arr,$order_no,$file_to_path=false,$maker_addr=false,$customer_data=false)
	{

		$user = Sentinel::check();
		
		$loggedIn_userId = 0;

		if($user)
		{
		    $loggedIn_userId = $user->id;
		} 


		/*if admin is there so change the template*/

        $user_id = $this->UserModel->where('email',$to_mail_id)->pluck('id')->first();
 

		$arr_product = $product_arr;
		$order_no = $order_no;
		$file_to_path = $file_to_path;
		$order_summary= $promotion_session_data = [];
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = $promotion_discount = 0;

     	if(isset($arr_product) && count($arr_product)>0)
     	{
     	   	foreach ($arr_product as $key2 => $product) 
     	   	{
     	   		$product_details = get_product_details($product['product_id']);
     	   		$product_name = $product_details['product_name'];
     	   		$order[$key2]['product_name'] = $product_name;
     	   		$order[$key2]['sku_no']       = $product['sku_no'] or '';
     	   		$order[$key2]['order_no']     = $order_no or '';
     	   		$order[$key2]['item_qty']     = $product['item_qty'];
     	   		$order[$key2]['unit_price']   = $product['retail_price'];
     	   		$order[$key2]['total_retail_price'] = $product['total_price'];

     	   		$order[$key2]['product_discount_amount'] = $product['product_discount_amount'];

     	   		$order[$key2]['shipping_discount'] = $product['shipping_discount'];

     	   		$order[$key2]['shipping_charges']  = $product['shipping_charges'];
     	   		$order[$key2]['maker_company_name'] = get_maker_company_name($product['maker_id']);
     	   		array_push($order_summary,$order[$key2]);
     	   		


     	   	}

     	}


        //create pdf for retailer
      
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;

 		foreach ($arr_product as $key => $product_data) 
 		{ 
 			

        	$sum += $product_data['total_price'];
        	
			
			$product_details = get_product_details( $product_data['product_id']);
 	   		$product_name = $product_details['product_name'];

			$arr_product[$key]['unit_price']  = num_format($product_data['retail_price'], 2, '.', '');
			$arr_product[$key]['total_retail_price']  = num_format($product_data['total_price'], 2, '.', '');
			$arr_product[$key]['product_name'] = $product_name;
			$arr_product[$key]['shipping_charges'] = $product_details['shipping_charges'];

			$arr_product[$key]['shipping_type'] = $product_details['shipping_type'];

			$arr_product[$key]['minimum_amount_off'] = $product_details['minimum_amount_off']; 
			$arr_product[$key]['maker_company_name'] = get_maker_company_name($product_data['maker_id']); 
			

			if($arr_product[$key]['unit_price']>=$arr_product[$key]['minimum_amount_off'])
			{
             	if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
             	{
             		$shipping_charges += 0;
             	}
             	else{
             		$shipping_charges += isset($arr_product[$key]['shipping_charges']) &&$arr_product[$key]['shipping_charges']!=''?$arr_product[$key]['shipping_charges']:0;
             	}
				

				$sum = $sum-$shipping_charges;
			}

			$total_sum = $sum+$shipping_charges;

			$arr_product[$key]['product_discount_amount'] = $product_data['product_discount_amount'];
			if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
         	{
         		$arr_product[$key]['shipping_discount'] = 0;

 	   			$arr_product[$key]['shipping_charges']  = 0;
         	}
         	else{
         		$arr_product[$key]['shipping_discount'] = $product_data['shipping_discount'];

 	   			$arr_product[$key]['shipping_charges']  = $product_data['shipping_charges'];
         	}
 	   		
 	   		array_push($order_summary,$order[$key2]);
 		}

 		$promotion_session_data = Session::get('customer_promotion_data');
 			
		if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
    	{	
    		$promo_discount_amount  = isset($promotion_session_data['discount_amt'])?$promotion_session_data['discount_amt']:0;

    		
    	
    	}

 		if(isset($promo_discount_amount) && $promo_discount_amount != 0)
 		{
 			$promotion_discount = $promo_discount_amount;
 			$customer_data['promo_discount'] = $promotion_discount;
 		}


  		$order = $arr_product;
  	
  		$discount_percent = isset($promotion_session_data['discount_percent'])?$promotion_session_data['discount_percent']:0;
			
  		$sno  = '0';
  		$role = 'Customer';

  	    $pdf = PDF::loadView('front/customer_invoice',compact('role','order','key','customer_data','order_no','maker_addr','sum','sno','total_sum','shipping_charges','promotion_discount','discount_percent'));


        $currentDateTime = $order_no.date('H:i:s').'.pdf';


     	$credentials = ['email' => $to_mail_id];
      
        $arr_user = get_user_by_credentials($credentials);


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);


        $pdf_arr = 	[
	    			   'PDF'           => $pdf,
	            	   'PDF_FILE_NAME' => $currentDateTime
	               	];


        if($user_id == 1)
        {
            
            $arr_built_content = [
                                    'PROJECT_NAME' => $arr_site_setting['site_name'],
                                    'order_no'     => $order_no
                                 ];


            $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'48',$arr_user);                      
			
	    }
        else
        {

			$arr_built_content = [
                                    'PROJECT_NAME' => $arr_site_setting['site_name']
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

	public function send_mail($to_mail_id=false,$arr_product,$order_no,$charge_status=false,$loggedInUserId,$orderId,$isDirectPayment=false)
	{ 
		
		$arr_email = $product_name = [];
		$promotion_discount = 0;
		$customer_data = $this->CustomerQuotesModel->where('order_no',$order_no)->where('id',$orderId)->first();
		$role = ''; $Role = 'Customer';
		if($customer_data)
		{
			$customer_data = $customer_data->toArray();
			$role = 'customer';
			$promotion_discount = isset($customer_data['promotion_discount'])?$customer_data['promotion_discount']:0;
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
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['product_name'] = $product_name;
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['sku_no']     = $product['sku_no'] or '';
	 	   		$order[$key2]['item_qty']     = isset($product['item_qty'])?$product['item_qty']:0;

	 	   		$order[$key2]['unit_price']   = isset($product['unit_price'])?$product['unit_price']:0;

	 	   		$order[$key2]['product_discount_amount']   = isset($product['product_discount'])?$product['product_discount']:0;

	 	   		$order[$key2]['shipping_discount']   = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	 	   		$order[$key2]['total_retail_price'] = isset($product['retail_price'])?$product['retail_price']:0;

	 	   		$order[$key2]['deducted_amount'] = isset($product['total_retail_price'])?num_format($product['total_retail_price']):0;

	 	   		// dd($product_details);
	 	   		$order[$key2]['maker_company_name'] = get_maker_company_name($product['maker_id']); 

	 	   		if (isset($role) && $role == 'reps') {

	 	   			$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges'] + $product['shipping_discount']:0;
	 	   		}
	 	   		else{

	 	   			$order[$key2]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:0;
	 	   		}
	 	   }
	 	   
	 	    $sum = 0;
		    $total_amount = $shipping_charges = $shipping_discount = $prod_discount = $deducted_amount = 0;
		    

	    	foreach ($order as $key => $order_data) 
	     	{ 
				$sum += $order_data['total_retail_price'];
				$total_amount += $order_data['deducted_amount'];
				$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		 	}
		    			
	 	    // $shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

	 	    $customer_data['user_details'] = $this->UserModel
                        ->with(['customer_details'])  
                                  ->where('id',$customer_data['customer_id'])
                                  ->first()
                        ->toArray();

	 	    // $retailer_data['store_name'] = isset($shop_name)?$shop_name:'';
	 	    $customer_data['charge_status'] = isset($charge_status)?$charge_status:'';

	 	    $maker_company_name = get_maker_company_name($loggedInUserId);

	 	    $maker[$key1]['order_details']= $order;
	 	  
	 	    $maker[$key1]['email_id'] = $arr_email;
	 	  	

	 	  	$maker_id = $customer_data['maker_id'];

 			$maker_addr = $this->UserModel->where('id',$maker_id)
										  ->first()
 										  ->toArray();
	 	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';

		    
	 	  	$pdf = PDF::loadView('front/invoice_to_customer',compact('Role','order','key','customer_data','order_no','maker_addr','sum'));
	 	  	
           	$currentDateTime = $order_no.date('H:i:s').'.pdf';


           	$credentials = ['email' => $to_mail_id];
      
        	$arr_user    = get_user_by_credentials($credentials);
           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);

			

		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];


		    $total_amount = $total_amount - $promotion_discount;

	        if($user_id == 1)
	        {
                if($isDirectPayment == 1)
                {
                   $html = '<p>Order no: '.$order_no.' has been confirmed successfully! , <br /><label>$'.$customer_data['total_retail_price'].'</label> deducted from customers account,</p>';
                }
                else
                {
                   $html = '<p>Order no: '.$order_no.' has been confirmed successfully! , <br /><label>$'.$customer_data['total_retail_price'].'</label> credited in your account,</p>';
                }

				$arr_built_content = [
	 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
	                            'order_no'  		=> $order_no,
	                            'user_role'  		=> 'customer',
	                            'deducted_amount' 	=> $total_amount,
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
		                            'deducted_amount' 	=> $total_amount
		                        ];

		            $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'38',$arr_user);
	        }

	        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

	 	 }
	}



    //old function
	/*public function send_request_email_to_maker($order_details)
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
	   		$order[$key2]['sku_no']       = $product['sku_no'] or '';
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['item_qty']     = isset($product['qty'])?$product['qty']:0;

	   		$order[$key2]['unit_price']   = isset($product['unit_retail_price'])?$product['unit_retail_price']:0;

	   		$order[$key2]['product_discount_amount']   = isset($product['product_discount'])?$product['product_discount']:0;

	   		$order[$key2]['shipping_discount']  = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	   		$order[$key2]['total_retail_price'] = isset($product['retail_price'])?$product['retail_price']:0;

	   		$order[$key2]['shipping_charges']   = isset($product['shipping_charge'])?$product['shipping_charge']:0;
	   		$order[$key2]['maker_company_name'] = get_maker_company_name($product_data['maker_id']); 
	   	}


	    $customer_data = $this->CustomerQuotesModel->where('order_no',$product_data['order_no'])->first();
	    
		if($customer_data)
		{
			$customer_data = $customer_data->toArray();
		}

		//this else for if order not present in retailer quotes model means this order placed by representative.
		
		else
		{
			$customer_data = $this->RepresentativeLeadsModel->where('order_no',$product_data['order_no'])->first();

			if($customer_data)
			{
			  $customer_data = $customer_data->toArray();
			}
		}

	    // $shop_name = get_retailer_shop_name($customer_data['customer_id']);

	    // $customer_data['shop_name'] = isset($shop_name)?$shop_name:'';
	    
	    $maker_company_name = get_maker_company_name($customer_data['maker_id']);

	    
	    $customer_data['user_details'] = $this->UserModel
                        ->with(['customer_details'])  
                                  ->where('id',$customer_data['customer_id'])
                                  ->first()
                        ->toArray();
	  	

	  	$maker_id = $customer_data['maker_id'];

		$maker_addr = $this->UserModel->where('id',$maker_id)
								  ->first()
									  ->toArray();

	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';
	 	$sum = 0;

		foreach ($order as $key => $order_data) 
		{ 
			$sum += $order_data['total_retail_price'];
			$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		}

		$order_no   = $product_data['order_no'];
		$order_date = $product_data['created_at'];
	
		$discount_percent = isset($customer_data['discount_percent'])?$customer_data['discount_percent']:"0";
	  	$pdf = PDF::loadView('front/customer_invoice',compact('order','key','customer_data','order_no','order_date','maker_addr','sum','discount_percent'));
		  	
	   	$currentDateTime = $order_no.date('H:i:s').'.pdf';
		
		Storage::put('public/pdf/'.$currentDateTime, $pdf->output());
		$pdfpath = Storage::url($currentDateTime);
		$project_name = get_project_name();
		$arr_built_content = [
	 	  						'EMAIL'          => $arr_email,
	                            'order_details'  => $order,
	                            'charge_status'  => isset($charge_status)?$charge_status:'',
	                            'PROJECT_NAME'   => $project_name,
	                            'email_template_id' => '42'
		                    ];
		        
        $arr_mail_data['arr_built_content']   = $arr_built_content;

        $obj_email_template = $this->EmailTemplateModel->where('id','42')->first();
		
		if($obj_email_template)
      	{
        	$arr_email_template = $obj_email_template->toArray();		        	
        	$content = $arr_email_template['template_html'];
        	$from_user_id = isset($arr_email_template['template_from_mail']) ? $arr_email_template['template_from_mail'] : '-'; //  Sender Email From Database
	        }

		$content = str_replace("##ORDER_NO##",$order_no,$content);
		$content = str_replace("##PROJECT_NAME##",$project_name,$content);

        $content = view('email.front_general',compact('content'))->render();
        $content = html_entity_decode($content);
       
        $html_build = view('front.email_template.purchase_order',$arr_mail_data)->render(); 
	
		$to_mail_id = $arr_email;
		
    	$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;
		$admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
        $admin_id = $admin_obj->user_id;      
			//$from_user_id = Sentinel::findById($admin_id)->email;

    	$send_mail = Mail::send(array(),array(), function($message) use($content,$to_mail_id,$file_to_path,$from_user_id,$pdf,$currentDateTime)
        {
          
          $message->from($from_user_id);
          $message->to($to_mail_id)
		          ->subject('Cancel Order Request')
		          ->setBody($content, 'text/html');
		  //$message->attach($file_to_path);
		  $message->attachData($pdf->output(), $currentDateTime, [
                        'mime' => 'application/pdf',
                    ]);
		       
        });

	}*/


    //this function for send cancel order request
	public function send_request_email_to_maker($order_details)
	{ 
		$product_data = $order_details;
		
		$arr_email = $product_name = $pdf_arr = [];

		$arr_email = Sentinel::findById($product_data['maker_id'])->email;

	   	foreach ($product_data['quotes_details'] as $key2 => $product) 
	   	{ 

	   		$product_details              = get_product_details($product['product_id']);
	   		$product_name 				  = isset($product_details['product_name'])?$product_details['product_name']:'';
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['product_name'] = $product_name;
	   		$order[$key2]['sku_no']       = $product['sku_no'] or '';
	   		$order[$key2]['order_no']     = $product_data['order_no'] or '';
	   		$order[$key2]['item_qty']     = isset($product['qty'])?$product['qty']:0;

	   		$order[$key2]['unit_price']   = isset($product['unit_retail_price'])?$product['unit_retail_price']:0;

	   		$order[$key2]['product_discount_amount']   = isset($product['product_discount'])?$product['product_discount']:0;

	   		$order[$key2]['shipping_discount']  = isset($product['shipping_discount'])?$product['shipping_discount']:0;

	   		$order[$key2]['total_retail_price'] = isset($product['retail_price'])?$product['retail_price']:0;

	   		$order[$key2]['shipping_charges']   = isset($product['shipping_charge'])?$product['shipping_charge']:0;
	   		$order[$key2]['maker_company_name'] = get_maker_company_name($product_data['maker_id']); 
	   	}


	    $customer_data = $this->CustomerQuotesModel->where('order_no',$product_data['order_no'])->first();
	    
		if($customer_data)
		{
			$customer_data = $customer_data->toArray();
		}

	    
	    $maker_company_name = get_maker_company_name($customer_data['maker_id']);

	    
	    $customer_data['user_details'] = $this->UserModel
                                              ->with(['customer_details'])  
                                              ->where('id',$customer_data['customer_id'])
                                              ->first()
                                              ->toArray();
	  	

	  	$maker_id   = $customer_data['maker_id'];

		$maker_addr = $this->UserModel->where('id',$maker_id)
								      ->first()
									  ->toArray();

	  	$maker_addr['company_name'] = isset($maker_company_name)?$maker_company_name:'';
	 	$sum = 0;

		foreach ($order as $key => $order_data) 
		{ 
			$sum += $order_data['total_retail_price'];
			$order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		}

		$order_no   = $product_data['order_no'];
		$order_date = $product_data['created_at'];
	
		$discount_percent = isset($customer_data['discount_percent'])?$customer_data['discount_percent']:"0";
	  	$pdf = PDF::loadView('front/customer_invoice',compact('order','key','customer_data','order_no','order_date','maker_addr','sum','discount_percent'));
		  	
	   	$currentDateTime = $order_no.date('H:i:s').'.pdf';


		$credentials = ['email' => $arr_email];
      
        $arr_user    = get_user_by_credentials($credentials);

        /*Get site setting data from helper*/
        $arr_site_setting = get_site_settings(['site_name','website_url']);

		$arr_built_content = [
	 	  					    'PROJECT_NAME'   => $arr_site_setting['site_name'],
	                            'ORDER_NO'       => $order_no
	                        ];


		$pdf_arr = ['PDF'           => $pdf,
		            'PDF_FILE_NAME' => $currentDateTime
	               ];
	

        $arr_mail_data                      = [];
        $arr_mail_data['email_template_id'] = '42';
        $arr_mail_data['arr_built_content'] = $arr_built_content;
        $arr_mail_data['arr_user']          = $arr_user;

    
        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

    }



	public function cancel_order_status_mail($status,$order_details,$reason)
	{

		if($status == 2)
	    {

	    	$user = Sentinel::check();
      		$product_data = $order_details;


	   		$order_no = $product_data['order_no'];	  

	  
	 	   	$price = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;
	 	   	$arr_email = Sentinel::findById($product_data['maker_id'])->email;
	 	   	$maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);
	    
	 	   	foreach ($product_data['quotes_details'] as $key2 => $product) 
	 	   	{
				$product_details              = get_product_details($product['product_id']);
	 	   		$product_name 				  = $product_details['product_name'];
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['product_name'] = $product_name;
	 	   		$order[$key2]['sku_no']       = $product['sku_no'] or '';
	 	   		$order[$key2]['order_no']     = $order_no or '';
	 	   		$order[$key2]['item_qty']     = $product['qty'];
	 	   		$order[$key2]['unit_price']   = $product['unit_retail_price'];
	 	   		$total_retail_price = $product['unit_retail_price']*$product['qty'];
	 	   		$order[$key2]['total_retail_price'] = isset($total_retail_price)?$total_retail_price:'0.00';
	 	   		
	 	   		
	 	   		$order[$key2]['product_discount_amount'] = isset($product['product_discount'])?$product['product_discount']:0;

	   		    $order[$key2]['shipping_discount'] = $product['shipping_discount'];

	   		    $order[$key2]['shipping_charges']  = $product['shipping_charge'];
	   		    
	   		    $order[$key2]['maker_company_name'] = get_maker_company_name($product_data['maker_id']); 

	 	   	}
	 	  	
	 		$maker[$product_data['maker_id']]['order_details']= $order;
				     	    
				     	    
	 	    $sum = 0;
	 	    foreach ($order as $key => $order_data) 
	 	   	{ 

			   $sum += $order_data['total_retail_price'];
			   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
		   	}

	 	    $maker[$product_data['maker_id']]['email_id'] = $arr_email;
	 	  	
	 	  	$arr_built_content = [
	 	  						'EMAIL'          => $maker[$product_data['maker_id']]['email_id'],
	                            'order_details'  => $maker[$product_data['maker_id']]['order_details']
	                           ];
	        
	        $arr_mail_data['arr_built_content']   = $arr_built_content;
	        $arr_mail_data['user']                = $user;
	   
	        $html_build = view('front.email_template.customer_purchase_order',$arr_mail_data)->render(); 

	        //Create pdf here for maker

	        $customer_data = $this->CustomerQuotesModel 
	        					  ->with(['quotes_details','user_details'])
	        					  ->where('order_no',$order_no)
	        					  ->where('id',$product_data['id'])
	        					  ->first()
									  ->toArray();

		  	$transaction_mapping_arr =[];
			$obj_transaction_mapping = $this->TransactionMappingModel
	                    ->where('order_id',$customer_data['id'])
	                    ->where('order_no',$customer_data['order_no'])
	                    ->first();
	        if($obj_transaction_mapping)
	        {
	            $transaction_mapping_arr = $obj_transaction_mapping->toArray();   
	        }

	       /* $order_payment_status = isset($transaction_mapping_arr['transaction_status'])?$transaction_mapping_arr['transaction_status']:"";

			$order_payment_status = get_payment_status($order_payment_status);
			if($order_payment_status=="")
			{
				$order_payment_status = "Pending";
			}*/

			$order_payment_status = isset($customer_data['is_payment_status'])?$customer_data['is_payment_status']:"";

		    if($order_payment_status == 1)
		    {
		    	$order_payment_status = 'Paid';
		    }
		    else
		    {
		    	$order_payment_status = 'Pending';
		    }
				
			$order_cancel_status = isset($customer_data['order_cancel_status'])?$customer_data['order_cancel_status']:"";										  
			$order_cancel_status = get_order_cancel_status($order_cancel_status);

			$maker_id = $maker[$product_data['maker_id']]['maker_id'];

			
			$maker_addr = $this->UserModel->where('id',$product_data['maker_id'])
							  ->first()
								  ->toArray();
				
			$company_name = get_maker_company_name($product_data['maker_id']);
			$maker_addr['company_name'] = isset($company_name)?$company_name:'';

			$customer_data['charge_status'] = isset($order_payment_status)?$order_payment_status:"Pending";
			$customer_data['cancel_status'] 	   = $order_cancel_status;
			$customer_data['promo_discount'] = $customer_data['promotion_discount'];

			$discount_percent = isset($customer_data['discount_percent'])?$customer_data['discount_percent']:"0";

			$pdf = PDF::loadView('front/customer_invoice',compact('order','key','customer_data','sum','maker_addr','order_no','discount_percent'));


			$currentDateTime = $order_no.date('H:i:s').'.pdf';


	        // Send Mail To maker 
	        $to_mail_id = isset($maker[$product_data['maker_id']]['email_id'])?$maker[$product_data['maker_id']]['email_id']:"";

	        // Send Mail To customer 
		        $to_customer_id = isset($customer_data['user_details']['email'])?$customer_data['user_details']['email']:"";

		        $credentials = ['email' => $to_customer_id];
		      
		        $arr_user    = get_user_by_credentials($credentials);

	         	 $arr_site_setting = get_site_settings(['site_name','website_url']);
					
				    $pdf_arr = 	[
				    				'PDF'           => $pdf,
				            		'PDF_FILE_NAME' => $currentDateTime
				               	];

				    $arr_built_content  =  	[
					 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
					 	  					   	'ORDER_NO'			=> $order_details['order_no']
					                        ];

					$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'72',$arr_user);   

					$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);


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
				                        
			$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'72',$arr_admin_user);   

			$email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
			
		}
		else
		{
	
			$to_user_id = Sentinel::findById($order_details['user_details']['id'])->email;
			$from_user_id = Sentinel::findById($order_details['maker_details']['id'])->email;


			//get email of admin
	        $admin_role = Sentinel::findRoleBySlug('admin');        
		    $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
			$admin_id     = $admin_obj->user_id;      
			$admin_email_id = Sentinel::findById($admin_id)->email;

        	//send mail to Customer
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
				        
				         
				         if(empty($user_arr['first_name'])||empty($user_arr['last_name'])||empty($user_arr['email'])||empty($user_arr['contact_no'])||empty($user_arr['country_id'])||empty($maker_data['company_name'])||empty($maker_data['website_url'])||empty($maker_data['primary_category_id'])||empty($maker_data['no_of_stores'])||empty($user_arr['tax_id'])||empty($user_arr['post_code']))
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



	public function built_mail_data($email,$order_id)
	{ 
	    $credentials = ['email' => $email];
	    
	    $user = Sentinel::findByCredentials($credentials); // check if user exists
	    
	    if($user)
	    {
	    	$arr_site_setting = get_site_settings(['site_name','website_url']);
	   	 	$arr_user = $user->toArray();

	   	 	$url = url('/retailer/my_orders/view/').'/'.base64_encode($order_id);

	   	 	$html = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.$url.'">View Order</a>.<br/>' ;

	   	 	$message = 'Your payment due date is over today, If you want to do the payment please click on below link';

	    	$arr_built_content = ['USER_NAME'    => $arr_user['first_name'].' '.$arr_user['last_name'],
	                              'EMAIL'        => $arr_user['email'],
	                              'HTML'         => $html,
	                              'SUBJECT'      => 'Net30 Reminder Mail',
	                              'MESSAGE'      => $message,
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


	  			$pdf_arr = 	[
    			              'PDF'           => $pdf,
            	              'PDF_FILE_NAME' => $currentDateTime
                            ];

                
                $to_mail_id = isset($maker[$product_data['maker_id']]['email_id'])?$maker[$product_data['maker_id']]['email_id']:"";

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
	
	}

   	/*
   		Auth : Bhagyashri
   		Date : 16 Dec 2019
   		Desc : send cancel order email to maker and customer
   	*/



	public function cancel_order_mail($order_data = false)
	{			
   		$user         = Sentinel::check();	
   		$product_data = $order_data;
   		$order_no     = $product_data['order_no'];	 
   		$sum          = 0; 
   		$pdf_arr      = [];
   		   	
   		$price     = $inv_product_discount_amount = $inv_shipping_discount_amount = 0;

 	   	$arr_email = Sentinel::findById($product_data['maker_id'])->email;

 	   	$maker[$product_data['maker_id']]['maker_id'] = Sentinel::findById($product_data['maker_id']);
    
 	   	foreach ($product_data['quotes_details'] as $key2 => $product) 
 	   	{
			$product_details              = get_product_details($product['product_id']);
 	   		$product_name 				  = $product_details['product_name'];
 	   		$order[$key2]['order_no']     = $order_no or '';
 	   		$order[$key2]['product_name'] = $product_name;
 	   		$order[$key2]['sku_no']       = $product['sku_no'] or '';
 	   		$order[$key2]['order_no']     = $order_no or '';
 	   		$order[$key2]['item_qty']     = $product['qty'];
 	   		$order[$key2]['unit_price']   = $product['unit_retail_price'];
 	   		$total_retail_price           = $product['unit_retail_price']*$product['qty'];
 	   		$order[$key2]['total_retail_price'] = isset($total_retail_price)?$total_retail_price:'0.00';
 	   		
 	   		
 	   		$order[$key2]['product_discount_amount'] = isset($product['product_discount'])?$product['product_discount']:0;

	   		    $order[$key2]['shipping_discount'] = $product['shipping_discount'];

	   		    $order[$key2]['shipping_charges']  = $product['shipping_charge'];
   		    $order[$key2]['maker_company_name']    = get_maker_company_name($product_data['maker_id']); 

 	   	}
 	  	
 		$maker[$product_data['maker_id']]['order_details']= $order;
			     	    
 	   
 	    foreach ($order as $key => $order_data) 
 	   	{ 
           $sum += $order_data['total_retail_price'];
		   $order[$key]['unit_price']  = num_format($order_data['unit_price'], 2, '.', '');
	   	}

        //Create pdf here for maker

        $customer_data = $this->CustomerQuotesModel 
        					  ->with(['quotes_details','user_details','user_details.customer_details'])
        					  ->where('order_no',$order_no)
        					  ->where('id',$product_data['id'])
        					  ->first()
							  ->toArray();

	  	$transaction_mapping_arr =[];


		$obj_transaction_mapping = $this->TransactionMappingModel
                    ->where('order_id',$customer_data['id'])
                    ->where('order_no',$customer_data['order_no'])
                    ->first();

        if($obj_transaction_mapping)
        {
            $transaction_mapping_arr = $obj_transaction_mapping->toArray();   
        }

/*        $order_payment_status = isset($transaction_mapping_arr['transaction_status'])?$transaction_mapping_arr['transaction_status']:"";

		$order_payment_status = get_payment_status($order_payment_status);*/

		$order_payment_status = isset($customer_data['is_payment_status'])?$customer_data['is_payment_status']:"";


	    if($order_payment_status == 1)
	    {
	    	$order_payment_status = 'Paid';
	    }
	    else
	    {
	    	$order_payment_status = 'Pending';
	    }

		if($order_payment_status=="")
		{
			$order_payment_status = "Pending";
		}
			
		$order_cancel_status = isset($customer_data['order_cancel_status'])?$customer_data['order_cancel_status']:"";										  
		$order_cancel_status = get_order_cancel_status($order_cancel_status);

		$maker_id   = $maker[$product_data['maker_id']]['maker_id'];

		
		$maker_addr = $this->UserModel
		                    ->where('id',$product_data['maker_id'])
						    ->first()
						    ->toArray();
			
		$company_name = get_maker_company_name($product_data['maker_id']);
		$maker_addr['company_name'] = isset($company_name)?$company_name:'';

		$discount_percent = isset($customer_data['discount_percent'])?$customer_data['discount_percent']:"0";

		$customer_data['charge_status']  = isset($order_payment_status)?$order_payment_status:"Pending";
		$customer_data['cancel_status']  = $order_cancel_status;
		$customer_data['promo_discount'] = $customer_data['promotion_discount'];

		$pdf = PDF::loadView('front/customer_invoice',compact('order','key','customer_data','sum','maker_addr','order_no','discount_percent'));


		$currentDateTime = $order_no.date('H:i:s').'.pdf';

	
	    //send email to vendor for cancel order

        $vendor_mail = $this->HelperService->get_user_mail($product_data['maker_id']);

 	    $credentials = ['email' => $vendor_mail];
      
        $arr_user    = get_user_by_credentials($credentials);

        /*Get site setting data from helper*/
        $arr_site_setting  = get_site_settings(['site_name','website_url']);
	  	
 	  	$arr_built_content = [
 	  						   'PROJECT_NAME' => $arr_site_setting['site_name']
                            ];

		$pdf_arr = ['PDF'           => $pdf,
		            'PDF_FILE_NAME' => $currentDateTime
	               ];                    

	
		$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'39',$arr_user);        


        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);


        // Send Mail To customer for cancel order
        $to_customer_id = isset($customer_data['user_details']['email'])?$customer_data['user_details']['email']:"";

        $credentials = ['email' => $to_customer_id];
      
        $arr_user = get_user_by_credentials($credentials);


        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'39',$arr_user);        


        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

	
	}

	public function get_customer_orders_of_influencer($form_data)
	{
		$influencer_id = $form_data['influencer_id'];
		$influencer_code_query = $this->UserModel->select('influencer_code')
																						->where('id','=',$influencer_id)
																						->first();
						$influencer_code = $influencer_code_query->influencer_code;
		$arr_search_column = isset($form_data['column_filter'])?$form_data['column_filter']:[];

        /*$from_date = $form_data['from_date'];
        $to_date   = $form_data['to_date']; */
      
        $customer_quotes_tbl_name     = $this->RetailerQuotesModel->getTable();        
        $prefixed_customer_quotes_tbl = DB::getTablePrefix().$this->RetailerQuotesModel->getTable();

        $user_tbl_name                = $this->UserModel->getTable();
        $prefixed_user_tbl            = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl_name               = $this->MakerModel->getTable();
        $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable(); 

        $customer_table               = $this->RetailerModel->getTable();
        $prefixed_customer_tbl        = DB::getTablePrefix().$this->RetailerModel->getTable();

        $customer_quotes_details      = $this->RetailerQuotesProductModel->getTable();
        $customer_quotes_details_tbl  = DB::getTablePrefix().$this->RetailerQuotesProductModel->getTable();

        $transaction_mapping_table     = $this->TransactionMappingModel->getTable();
        $prefixed_transaction_mapping_tbl = DB::getTablePrefix().$this->TransactionMappingModel->getTable();

        $stripe_transaction_table     = $this->StripeTransactionModel->getTable();
        $prefixed_stripe_transaction_table = DB::getTablePrefix().$this->StripeTransactionModel->getTable();

        $obj_qutoes = DB::table($customer_quotes_tbl_name)
                                ->select(DB::raw($prefixed_customer_quotes_tbl.".*,".

                                                $prefixed_transaction_mapping_tbl.".id as tid,".
                                                $prefixed_transaction_mapping_tbl.".transaction_status,".
                                                $prefixed_stripe_transaction_table.".status as stripe_trxn_status,".
                                                $prefixed_stripe_transaction_table.".status,".

                                                $prefixed_maker_tbl.'.user_id as mid,'.
                                                $prefixed_maker_tbl.'.company_name,'.
                                                $prefixed_customer_tbl.'.store_name,'.
                                                $prefixed_user_tbl.'.first_name,'.
                                                $prefixed_user_tbl.'.last_name,'.
                                                $customer_quotes_details.'.shipping_charge,'.

                                                "CONCAT(".$prefixed_user_tbl.".first_name,' ',"
                                               .$prefixed_user_tbl.".last_name) as user_name"

                                            ))

                                ->leftjoin($prefixed_user_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_quotes_tbl.'.retailer_id')

                                ->leftjoin($prefixed_customer_tbl,$prefixed_user_tbl.'.id','=',$prefixed_customer_tbl.'.user_id')

                                ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_customer_quotes_tbl.'.maker_id')

                                ->leftjoin($customer_quotes_details,$customer_quotes_details_tbl.'.retailer_quotes_id','=',$prefixed_customer_quotes_tbl.'.id')

                                ->leftjoin($prefixed_stripe_transaction_table,$prefixed_customer_quotes_tbl.'.id','=',$prefixed_stripe_transaction_table.'.customer_order_id')

                                /* ->leftjoin($maker_tbl_name,$prefixed_maker_tbl.'.user_id','=',$prefixed_stripe_transaction_table.'.received_by')
                                */

                                ->leftjoin($prefixed_transaction_mapping_tbl,function($join) use($prefixed_customer_quotes_tbl,$prefixed_transaction_mapping_tbl){

                                    $join->on($prefixed_customer_quotes_tbl.'.id','=',$prefixed_transaction_mapping_tbl.'.order_id')
                                    ->on($prefixed_customer_quotes_tbl.'.order_no','=',$prefixed_transaction_mapping_tbl.'.order_no');
                                })
                                // ->where($prefixed_customer_quotes_tbl.'.order_cancel_status','=','0')
                                ->where($prefixed_customer_quotes_tbl.'.influencer_code','=',$influencer_code)
                                
                                ->orderBy($prefixed_customer_quotes_tbl.".id",'DESC')
                                ->groupBy($prefixed_customer_quotes_tbl.".id");


        /* ---------------- Filtering Logic ----------------------------------*/                    
        if(isset($arr_search_column['q_promo_code']) && $arr_search_column['q_promo_code']!="")
        {
            $search_term  = $arr_search_column['q_promo_code'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.promo_code','LIKE', '%'.$search_term.'%');
        }
             
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {
            $search_term      = $arr_search_column['q_order_no'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.order_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_enquiry_date']) && $arr_search_column['q_enquiry_date']!="")
        {
            $search_term  = $arr_search_column['q_enquiry_date'];
            $date         = DateTime::createFromFormat('m-d-Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.created_at','LIKE', '%'.$date.'%');
        }   

        if(isset($arr_search_column['q_description']) && $arr_search_column['q_description']!="")
        {
            $search_term      = $arr_search_column['q_description'];
            $obj_qutoes = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.description','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
        {
            $search_term  = $arr_search_column['q_customer_name'];
            
          /*  $obj_qutoes   = $obj_qutoes->where($prefixed_user_tbl.'.first_name','LIKE', '%'.$search_term.'%')->orWhere($prefixed_user_tbl.'.last_name','LIKE', '%'.$search_term.'%');*/

            $obj_qutoes = $obj_qutoes->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
            $search_term  = $arr_search_column['q_company_name'];
            $obj_qutoes   = $obj_qutoes->having($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_total_retail_cost']) && $arr_search_column['q_total_retail_cost']!="")
        {
            $search_term = $arr_search_column['q_total_retail_cost'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_total_wholesale_cost']) && $arr_search_column['q_total_wholesale_cost']!="")
        {
            $search_term  = $arr_search_column['q_total_wholesale_cost'];
            $obj_qutoes   = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
        }     

        if(isset($arr_search_column['customer_id']) && $arr_search_column['customer_id']!="" && $arr_search_column['customer_id']!='0')
        {
            $customer_id = base64_decode($arr_search_column['customer_id']);
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.retailer_id',$customer_id);
        } 

        if(isset($arr_search_column['q_ship_status']) && $arr_search_column['q_ship_status']!="")
        {
            $search_term = $arr_search_column['q_ship_status'];
            $obj_qutoes  = $obj_qutoes->where($prefixed_customer_quotes_tbl.'.ship_status','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_vendor_payment']) && $arr_search_column['q_vendor_payment']!="")
        {
            $search_term = $arr_search_column['q_vendor_payment'];
           
            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotIn($prefixed_customer_quotes_tbl.'.id',DB::table($prefixed_stripe_transaction_table)->pluck('customer_order_id'));
            }
            else
            {
               $obj_qutoes  = $obj_qutoes->where($prefixed_stripe_transaction_table.'.status','LIKE', '%'.$search_term.'%');
            }
        }

        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
        {
            $search_term = $arr_search_column['q_payment_status'];

            if($search_term == 1)
            {
                $obj_qutoes = $obj_qutoes->whereNotExists(function($query){

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = retailer_transaction.order_no AND `transaction_mapping`.`order_id` = retailer_transaction.id
                        "));       
                    });                                                   
            }
            else
            {
               $obj_qutoes = $obj_qutoes->where($prefixed_transaction_mapping_tbl.'.transaction_status','LIKE', '%'.$search_term.'%');
            }
        }

        /*search data from  from date and to date*/
        if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") && (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!=""))
        {
            $search_term_from_date  = $arr_search_column['q_from_date'];
            $search_term_to_date    = $arr_search_column['q_to_date'];

            $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
            $from_date              = $from_date->format('Y-m-d');
            $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
            $to_date                = $to_date->format('Y-m-d');

         /*   $search_term_from_date  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));
            $search_term_to_date    = date('Y-m-d', strtotime($arr_search_column['q_to_date']));*/

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_customer_quotes_tbl.'.created_at', '<=', $to_date);

            $obj_qutoes   = $obj_qutoes->whereDate($prefixed_customer_quotes_tbl.'.created_at', '>=', $from_date);

        } 

        return $obj_qutoes;
	}


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
}

?>