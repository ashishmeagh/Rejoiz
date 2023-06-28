<?php 
 
namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TransactionsModel;
use App\Models\CustomerQuotesModel;
use App\Models\RetailerQuotesModel;
use App\Models\TransactionMappingModel;
use App\Models\ProductsModel;
use App\Models\AddressModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\RetailerQuotesProductModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\CustomerOrderService;
use App\Common\Services\orderDataService;
use App\Common\Services\InventoryService;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;
use App\Models\EmailTemplateModel;
use App\Models\UserModel;
use App\Models\ProductInventoryModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\ShopSettings;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\StripeTransactionModel;
use App\Models\SiteSettingModel;

use Stripe;
use Flash;
use DB;
use Mail;

 
use Session, Sentinel,PDF,Storage;


class CustomerPaymentController extends Controller
{
    public function __construct(TransactionsModel $TransactionsModel,
    							CustomerQuotesModel $CustomerQuotesModel,   
    							RetailerQuotesModel $RetailerQuotesModel,   
    							TransactionMappingModel $TransactionMappingModel,
    							StripePaymentService $StripePaymentService,							
    							CustomerOrderService $CustomerOrderService,
    							GeneralService $GeneralService,
    							ProductsModel $ProductsModel,
    							AddressModel $AddressModel,
    							CustomerQuotesProductModel $CustomerQuotesProductModel,
    							RetailerQuotesProductModel $RetailerQuotesProductModel,
    							RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
    							RepresentativeLeadsModel $RepresentativeLeadsModel,
    							orderDataService $orderDataService,
    							InventoryService $InventoryService,
    							EmailService $EmailService,
    							HelperService $HelperService,
    							EmailTemplateModel $EmailTemplateModel,
    							UserModel $UserModel,
    							ShopSettings $ShopSettings,
    							StripeAccountDetailsModel $StripeAccountDetailsModel,
    							UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
    							StripeTransactionModel $StripeTransactionModel,
								ProductInventoryModel $ProductInventoryModel,
								SiteSettingModel $SiteSettingModel
    							)
    {

    	$this->TransactionsModel        = $TransactionsModel;  
    	$this->CustomerQuotesModel      = $CustomerQuotesModel;     
    	$this->RetailerQuotesModel      = $RetailerQuotesModel;     
    	$this->TransactionMappingModel  = $TransactionMappingModel;
    	$this->GeneralService           = $GeneralService;
    	$this->StripePaymentService     = $StripePaymentService;
    	$this->CustomerOrderService           = $CustomerOrderService;
    	$this->InventoryService			= $InventoryService;
    	$this->ProductsModel			= $ProductsModel;
    	$this->AddressModel				= $AddressModel;
    	$this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
    	$this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
    	$this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
    	$this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->orderDataService         = $orderDataService;
        $this->EmailService             = $EmailService;
        $this->HelperService           	= $HelperService;
        $this->EmailTemplateModel       = $EmailTemplateModel;
        $this->UserModel                = $UserModel;
        $this->ShopSettings             = $ShopSettings;
        $this->ProductInventoryModel    = $ProductInventoryModel;
        $this->StripeTransactionModel   = $StripeTransactionModel;
        $this->StripeAccountDetailsModel  = $StripeAccountDetailsModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;

    	// $this->stripe_api_key           = 'sk_test_UQE8wx6WNY7Ogj1A5Uy1ZMWA00Cjg1fs3r';
		$this->stripe_api_key           = get_admin_stripe_key();
		$this->SiteSettingModel			= $SiteSettingModel;
		$this->site_setting_obj        = $this->SiteSettingModel->first();
        
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }

    }

    public function customer_payment($order_id,$slug_chk_qty_available_or_not=null)
    {	
    	$order_data = [];
    	// dd($order_id);
	  	$order_id = isset($order_id)?base64_decode($order_id):'';
    	$payment_term = $this->CustomerQuotesModel->where('id',$order_id)->pluck('payment_term')->first();

      
    	if($order_id)
    	{

    		$loggedInUserId = 0;
	        $user = \Sentinel::check();


	        if($user && $user->inRole('maker'))
	        {
	            $loggedInUserId = $user->id;
	        }
      
	    	$order_details = $out_of_stock_details = [];
	    	
	    	$order_details = $this->CustomerQuotesModel->where('id',$order_id)
	    									->with(['transaction_details.strip_key_details','quotes_details','quotes_details.product_details','maker_data.stripe_account_details'])
	    								    ->first();
	    								   

			$arr_data = $arr_charge_data = [];
	    	    	
	    	if($order_details)
	    	{

	    		$order_details = $order_details->toArray();

	    		$split_order_free_shipping = $this->ShopSettings->where('maker_id',$order_details['maker_id'])
																->pluck('split_order_free_shipping')
			                                                    ->first();
	    		
	    		/*  check Product Availability  */

	    		$ord_no = isset($order_details['order_no'])?$order_details['order_no']:false;
	    		$order_data = $this->orderDataService->get_order_details('customer',$ord_no,$order_details['maker_id']);

	    		$arr_available_qty_by_skno = [];
	    		$arr_requested_qty_by_skno = [];
	    		$arr_sku_no = [];

	    		/* If True then Direct Inventory Deduction Applicable
					  False then Order Split Applicable with Email Sent to Retailer for Confirmation 

	    		*/
	    		$is_completely_fulfilled = true;

	    		$arr_product = array_column($order_data, 'quotes_details');
	    		/* Extract SKU */
	    		if($order_data)

	    		{				      
			        if($arr_product && count($arr_product) > 0)
			        {
				        foreach($arr_product as $product_data)
				        {
				        	if($product_data && sizeof($product_data) > 0)
				        	{
					        	foreach ($product_data as $key => $product)
					        	{
					        		$arr_sku_no[] = $product['sku_no'];
					        		$arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);
					        	}
					        }
					    }    	
					}    
				}    


			    /* Get Available SKU Quantity */
			    
			    $arr_available_qty_by_skno = $this->InventoryService->get_available_qty($arr_sku_no);
			    $arr_available_qty_by_skno = array_column($arr_available_qty_by_skno, 'quantity','sku_no');
    			
	    		

			    if(sizeof($arr_requested_qty_by_skno) > 0)
			    {
			    	foreach ($arr_requested_qty_by_skno as $sku_no => $requested_quantity) {

			    		if(isset($arr_available_qty_by_skno[$sku_no]) && $arr_available_qty_by_skno[$sku_no] < $requested_quantity)
			    		{
			    			$is_completely_fulfilled = false;
			    		}
			    	}
			    }
			    // dd($slug_chk_qty_available_or_not);

			    /*When click on confirm order and insufficient inventory then return view and tell to vender that click on split order...*/
			    if(isset($slug_chk_qty_available_or_not) && $slug_chk_qty_available_or_not!=null)
			    {
			    	if($is_completely_fulfilled == false)
			    	{
			    		$response['status']      = 'split_warning';
			        	$response['description'] = 'Sorry, Product inventory is insufficient, for order processing please update product inventory.';
			        	return $response;	
			    	}
			    	else
			    	{
			    		$response['status']      = 'success';
			        	$response['description'] = 'Inventory available, you can process.';
			        	return $response;		
			    	}
			    	
			    }


			    if($is_completely_fulfilled == true)
			    {
			    	/* Fulfilled Order */
			    	// dd($order_details);
		    		$arr_charge_data['order_amount'] = isset($order_details['total_retail_price'])?$order_details['total_retail_price']:0;

		    		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

		    		$arr_charge_data['stripe_key'] = isset($order_details['transaction_details']['strip_key_details']['secret_key'])?$order_details['transaction_details']['strip_key_details']['secret_key']:false;

		    		$paymentStripeKeyId = isset($order_details['transaction_details']['strip_key_details']['id'])?$order_details['transaction_details']['strip_key_details']['id']:false;

		    		$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

		    		$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';

			      	if(isset($arr_charge_data) && count($arr_charge_data) > 0 )
		    		{
		    			if($payment_term == 'Online/Credit')
		    			{
		    				// Payment duducted

		    				// $isDirectPayment = isset($order_details['maker_data']['is_direct_payment'])?$order_details['maker_data']['is_direct_payment']:0;

		    				$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;

    	    				$endUserId = isset($order_details['customer_id'])?$order_details['customer_id']:0;

		    				$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';
		    				
		    				$cardId = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

		    				$arrData = [];

		    				$arrData['customerId']   = $customerId;
		    				$arrData['endUserId']    = $endUserId;
		    				$arrData['vendorId']     = $loggedInUserId;
		    				$arrData['order_amount'] = $arr_charge_data['order_amount'];
    	    				$arrData['stripe_key']   = $arr_charge_data['stripe_key'];
		    				$arrData['cardId']       = $cardId;
		    				$arrData['order_no']     = isset($ord_no)?$ord_no:'';

    	    				
    	    				if($isDirectPayment == 1)
    	    				{
    	    					$stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($loggedInUserId);

					    	    $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

					    	    $stripKey = isset($stripeApiKeyData['stripeKey'])?$stripeApiKeyData['stripeKey']:'';
    	    					
         	    				$charge = $this->StripePaymentService->create_customer_direct_charge($arrData);
    	    				}
    	    				else
    	    				{
    	    					$admin_user_id       = get_admin_id();

					    	    $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($admin_user_id);

					    	    $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

					    	    $arrStripeDetails = $this->StripePaymentService->build_stripe_card_and_key_data($cardId,$customerId,$arr_charge_data['stripe_key'],$paymentStripeKeyId,$admin_user_id,$endUserId);

					    	    $arr_charge_data['customer_id']   = $arrStripeDetails['customer_id'];

								$arr_charge_data['stripe_key']    = $arrStripeDetails['secret_key'];

								$arr_charge_data['card_id']       = $arrStripeDetails['card_id'];

         	    				$charge = $this->StripePaymentService->create_charge($arr_charge_data);
    	    				}

		    				if(isset($charge['status']) && $charge['status'] == 'Error')
							{
								$response['status']      = 'warning';
					        	$response['description'] = isset($charge['description'])?$charge['description']:'Something went wrong, please try again.';
					        	return $response;
							}

				        	if($charge)
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

					        	$arr_data['user_id']             = $order_details['customer_id'] or '';
				        		$arr_data['order_id']            = $order_details['id'] or '';
				        		$arr_data['order_no']            = $order_details['order_no'] or '';
				        		$arr_data['amount']              = $order_details['total_retail_price'] or '';
				        		$arr_data['transaction_id']      = isset($charge['id'])?$charge['id']:"";
				        		$arr_data['transaction_status']  = $status or '';
				        		$arr_data['payment_type']        = '1' or '';

				        		$transaction_entry = $this->TransactionMappingModel->create($arr_data);

				        		if($status == '2')
				        		{


				        		/* Update payment status in order table by Harshada on date 21 Oct 2020 */
					        	$this->CustomerQuotesModel->where('id',$order_id)   										  ->where('order_no',$ord_no)										  ->where('maker_id',$order_details['maker_id']) 					   ->update(['is_payment_status' => '1','stripe_key_id'=>$stripKeyId]);



				        		$order_status_update = $this->CustomerQuotesModel->where('id',$order_id)
				        													 ->where('maker_id',$order_details['maker_id'])

				        													 ->update(['maker_confirmation' => '1','stripe_key_id'=>$stripKeyId]);



					        		/* send customer notification*/

					        	 	$view_order_href=  url('/').'/customer/my_orders/view/'.base64_encode($order_details['id']);


					        		$arr_notify_data                 = [];
								    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
								    $arr_notify_data['to_user_id']   = $order_details['customer_id'] or '';
 
								    $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_retail_price']).' is deducted from your account.';

								    $arr_notify_data['title']        = 'Order Confirmed';
								    $arr_notify_data['type']         = 'customer';  
								    $arr_notify_data['link']         = $view_order_href;  


								    $this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);

				        		
				        			$this->CustomerOrderService->save_notification($arr_notify_data);

				        			/* get admin id */

				        			$admin_id  = get_admin_id();
				        			$view_href =  url('/').'/admin/customer_orders/view/'.base64_encode($order_details['id']);

				        			$maker_details = \Sentinel::findById($order_details['maker_id']);

					        		/* send admin notification*/
					        		$arr_notify_data                 = [];
								    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
								    $arr_notify_data['to_user_id']   = $admin_id or '';

								    $arr_notify_data['description']  = 
								    'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

								    $arr_notify_data['title']        = 'Order Confirmed';
								    $arr_notify_data['type']         = 'admin';  
								    $arr_notify_data['link']         = $view_href;  

				        			$this->CustomerOrderService->save_notification($arr_notify_data);


				        			/* send vendor notification*/
				        			$view_href =  url('/').'/vendor/customer_orders/view/'.base64_encode($order_details['id']);
					        		$arr_notify_data                 = [];
								    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
								    $arr_notify_data['from_user_id'] = $admin_id or '';

								    $arr_notify_data['description']  = 
								    'Order('.$order_details['order_no'].') is confirmed and payment went through successfully.';

								    $arr_notify_data['title']        = 'Order Confirmed';
								    $arr_notify_data['type']         = 'maker';  
								    $arr_notify_data['link']         = $view_href;  

				        			$this->CustomerOrderService->save_notification($arr_notify_data);


				        			/* send amount credit notification to vendor if payment type id direct payment */
				        			/* send vendor notification*/
				        			if($isDirectPayment == 1)
    	    				        {
					        			$view_href = '';
						        		$arr_notify_data                 = [];
									    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
									    $arr_notify_data['from_user_id'] = $admin_id or '';

									    /*$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_retail_price']).' is credited in your account.';*/
									    $arr_notify_data['description']  = 
								    'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

									    $arr_notify_data['title']        = 'Order Confirmed';
									    $arr_notify_data['type']         = 'maker';  
									    $arr_notify_data['link']         = $view_href;  

					        			$this->CustomerOrderService->save_notification($arr_notify_data);
						        		
						        		/* get user mail details */
		 								$user_details = \Sentinel::findById($order_details['maker_id']);

		 								$user_email = isset($user_details->email)?$user_details->email:false;

		 								if($user_email != false)
		 								{

		 									$credentials = ['email' => $user_email];
	                      					$arr_user = get_user_by_credentials($credentials);

			 						
			 								/*call to dynamic function send_mail*/
			 								$arr_site_setting = get_site_settings(['site_name','website_url']);

			 								$arr_built_content = 
			 									[
			 										'credit_amount'   	=> num_format($order_details['total_retail_price']),
						                            'order_no'        	=> $order_details['order_no'],
						                            'PROJECT_NAME' 		=> $arr_site_setting['site_name'],					                            
						                        ];

						                    $arr_mail_data                      = [];
									        $arr_mail_data['email_template_id'] = '54';
									        $arr_mail_data['arr_built_content'] = $arr_built_content;
									        $arr_mail_data['arr_user']          = $arr_user;
			 								/*end*/



							        		/* send mail to vendor for order amount is credited on your acount */

							        		$email_status  = $this->EmailService->send_mail($arr_mail_data);

		                                    

		 								}	
		 								

	                                }

					        		/* Send mail to customer */

					        		$customer_mail = $this->HelperService->get_user_mail($order_details['customer_id']);
					        		
					        		$this->send_mail($customer_mail,$order_details['quotes_details'],$order_details['order_no'],$order_details['customer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_id,$isDirectPayment);
				    		

                                    /*send the mail to admin*/
				                    
				                    $admin_email = 0;

				                    $admin_details = $this->UserModel->where('id',1)->first();

				                    if(isset($admin_details))
				                    {
				                       $admin_email = $admin_details->email;

				                    }

                                    $this->send_mail($admin_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['customer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_id,$isDirectPayment);
				    		
                                  
	                                /* send notification and mail to the vendor and admin
	                                 for insufficient inventory after confirm the order*/  
	                                 
	                                 
	                                /* get all product list from order id*/
	                                $product_inventory_arr = [];

	                                $order_product_details = $this->CustomerQuotesProductModel
	                                                            ->with(['product_details'])
	                                                            ->where('customer_quotes_id',$order_id)
	                                                            ->get()
	                                                            ->toArray();
                                  

	                                if(isset($order_product_details) && count($order_product_details)>0)
	                                {
	                                	foreach($order_product_details as $key => $product) 
	                                	{  
	                                		// check inventory
	                                		$inventory = $this->ProductInventoryModel
	                                		                  ->where('product_id',$product['product_id'])
	                                		                  ->where('sku_no',$product['sku_no'])
	                                		                  ->pluck('quantity')
	                                		                  ->first();

	                                		if($inventory <=200)
	                                		{
	                                			$product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

	                                			$product_inventory_arr[$key]['sku_no']= $product['sku_no'];

	                                            $product_inventory_arr[$key]['inventory_stock'] = $inventory;
	                                		}                 
	                                	

	                                	}
	                                }
	                                 
	                               
	                                if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
	                                {
	                                     $key1 = '';
	                                    //send vendor notification
	                                   
						        		$arr_notify_data                 = [];
									    $arr_notify_data['from_user_id'] = $admin_id or '';

									    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
	                                    $html = '';
									    $html= "Following products having insufficient inventory: ";

									    foreach ($product_inventory_arr as $key => $value) {

	                                    if($key == 0)
	                                    {
	                                       $key1 = 1;
	                                    }
	                                    else
	                                    {
                                          $key1 = $key+1;
	                                    }

									    $html.= '  '.$key1.') Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
									           ' and Available Stock : '.$value['inventory_stock'].'  ';
									    }

	                                    $arr_notify_data['description']  = $html;
									    $arr_notify_data['title']        = 'Insufficient Inventory';
									    $arr_notify_data['type']         = 'maker';  
									    $arr_notify_data['link']         = '';  

					        			$this->CustomerOrderService->save_notification($arr_notify_data);
			
	 
	                                }

	                                /*send email to the vendor*/
	                                $maker_id = '';

	                                $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

	                                $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

	                                $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'customer_order'); 

			                        $email_status  = $this->EmailService->send_mail($arr_mail_data);
	                              
	                                /*---------------------------------------------------*/ 
     
						            $response['status']      = 'success';
						            $response['description'] = 'Order has been confirmed and payment went through successfully.';
						        	return $response;
					        	}
					        	else
					        	{
					        		$response['status']      = 'warning';
						        	$response['description'] = 'Something went wrong, please try again.';
						        	return $response;
					        	}
					        }
					        else{
					        	 
					        	    $response['status']      = 'warning';
						        	$response['description'] = 'Something went wrong, please try again.';
						        	return $response;
					            }	
		    			}

			        	else
			        	{
			        		$response['status']      = 'warning';
				        	$response['description'] = 'Something went wrong, please try again.';
				        	return $response;
			        	}
				        	
			        }
			        else
		    		{
		    			$response['status']      = 'warning';
				        $response['description'] = $charge['description'];
				        return $response;
		    		}
                }
                else
			    {
			    	
			    	/* Split Order */

			    	$is_quantity_available = array_filter($arr_available_qty_by_skno);

			    	if (empty($is_quantity_available)) 
			    	{
			    		/*Product stock is not available*/
			    		$response['status']      = 'warning';
			    		$response['description'] = 'Oops..! Stock is not available currently, please update quantity of product.';
			        	return $response;
			    		
			    	}
			    	

			    	$order_arr = $order_arr['fulfill'] = [];

			    	// $partial_order_no = str_pad('J2',  10, rand('1234567890',10)); 
			    	// $fulfill_order_no = str_pad('J2',  10, rand('1234567890',10)); 

			    	$partial_order_no = $this->orderDataService->generate_order_no($order_details['order_no']);
			    	$fulfill_order_no = $this->orderDataService->generate_order_no($partial_order_no);

			    	// dd($partial_order_no);

			    	/* Split the quantity of product*/

			    	
			    	foreach ($order_data as $key => $product_arr) {

			    		$order_arr = $product_arr;

			
			    		if (sizeof($product_arr['quotes_details']) > 0) {
			    			
			    			foreach ($product_arr['quotes_details'] as $key => $product) {

			    				foreach ($arr_available_qty_by_skno as $availble_qty_key => $available_qty_by_skno) {
			    					

			    					if ($product['sku_no'] == $availble_qty_key) {

			    						if ($product['qty'] > $available_qty_by_skno) {

			    							/*
			    								#If product have maximum quantity than available quantity
													# split product 1) Fulfill the quantity requirement
												2) Send remaining quantity in partial order 	
			    							*/

												if($available_qty_by_skno == 0){
													/*Product have 0 stock */

													$remainig_product_qty = $product['qty'] - $available_qty_by_skno;
				    							$total_price = $remainig_product_qty * $product['unit_retail_price'];

				    							
				    							$order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';
				    							$order_arr['partial'][$key]['product_name'] = $product['product_details']['product_name'] or '';
				    							$order_arr['partial'][$key]['sku_no'] = $product['sku_no'] or '';
				    							$order_arr['partial'][$key]['qty'] 	= $remainig_product_qty or '';
				    							$order_arr['partial'][$key]['unit_price'] 	= $product['unit_retail_price'];
				    							$order_arr['partial'][$key]['total_price'] 	= isset($total_price)?$total_price:0;

				    							$product_discount = $this->product_discount($product['product_id'], $order_arr['partial'][$key]['qty']);


				    							
					    							if(isset($split_order_free_shipping) && $split_order_free_shipping = 1)
					    							{
					    								$order_arr['partial'][$key]['shipping_charges']  = 0.00;
					    								$order_arr['partial'][$key]['shipping_discount'] = 0.00;
					    							}
					    							else
					    							{				    								
						    							$order_arr['partial'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;

						    							$order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
					    							}


				    							$order_arr['partial'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0;	

												}
												else
												{
													$remainig_product_qty = $product['qty'] - $available_qty_by_skno;
													$total_price = $remainig_product_qty * $product['unit_retail_price'];

													
													$order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';
													$order_arr['partial'][$key]['product_name'] = $product['product_details']['product_name'] or '';
													$order_arr['partial'][$key]['sku_no'] = $product['sku_no'] or '';
													$order_arr['partial'][$key]['qty'] 	= $remainig_product_qty or '';
													$order_arr['partial'][$key]['unit_price'] 	= $product['unit_retail_price'];
													$order_arr['partial'][$key]['total_price'] 	= isset($total_price)?$total_price:0;

													$product_discount = $this->product_discount($product['product_id'], $order_arr['partial'][$key]['qty']);


													/* if maker has set split_order_free_shipping */
				    							
					    							if(isset($split_order_free_shipping) && $split_order_free_shipping == 1)
					    							{
					    								$order_arr['partial'][$key]['shipping_charges']  = 0.00;
					    								$order_arr['partial'][$key]['shipping_discount'] = 0.00;
					    							}
					    							else
					    							{	
														$order_arr['partial'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
														
														$order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
													}

													$order_arr['partial'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0;		



													
													$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';
													$order_arr['fulfill'][$key]['product_name'] = $product['product_details']['product_name'] or '';
													$order_arr['fulfill'][$key]['sku_no'] 		= $product['sku_no'] or '';
													$order_arr['fulfill'][$key]['qty'] 		= $available_qty_by_skno or '';
													$order_arr['fulfill'][$key]['unit_price'] 	= $product['unit_retail_price'];
													$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;


													$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

													$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
													$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
													$order_arr['fulfill'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0;

													$arr_requested_qty_by_skno[$product['sku_no']] = intval($available_qty_by_skno);


													$total_price = $available_qty_by_skno * $product['unit_retail_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['product_discount'];

													
													$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;
												}
			    							
			    							
				    					}
				    					
				    					else{

			    							/*
			    								If product have full quantity
												*/
			    							

			    							$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

			    							$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
			    							$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
			    							$order_arr['fulfill'][$key]['product_discount'] = (isset($product_discount['product_discount']))?$product_discount['product_discount']:0;


			    							$total_price = $product['qty'] * $product['unit_retail_price'];	

				    						
			    							$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';
			    							$order_arr['fulfill'][$key]['product_name'] = $product['product_details']['product_name'] or '';
			    							$order_arr['fulfill'][$key]['sku_no'] 		= $product['sku_no'] or '';
			    							$order_arr['fulfill'][$key]['qty'] 		= $product['qty'] or '';
			    							$order_arr['fulfill'][$key]['unit_price'] 	= $product['unit_retail_price'];
			    							$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;

				    					}	
			    					}			    					
			    				}

			    			}

			    		}	
			    		
			    	}

			    	/*Store split order record */
			    	if($order_arr) {
			    		
			    		/*Update Order status to split order*/

			    		$this->CustomerQuotesModel->where('id',$order_details['id'])->update(['is_split_order'=>'1']);

			    		/*Store fulfill order record*/

			    		if (isset($order_arr['fulfill']) && sizeof($order_arr['fulfill']) > 0) {
			    			$order_arr['fulfill'] = array_values($order_arr['fulfill']);


			    			$total_wholesale_price = $total_retail_price = 0;
			    			$pro_ship_charge = 0;
			    			$pro_ship_dis = 0;
			    			$pro_dis = 0;

			    			$total_retail_price = array_sum(array_column($order_arr['fulfill'],'total_price'));
			    			$total_wholesale_price = array_sum(array_column($order_arr['fulfill'],'total_price'));

			    			$pro_ship_charge = array_sum(array_column($order_arr['fulfill'],'shipping_charges'));
		    				$pro_ship_dis = array_sum(array_column($order_arr['fulfill'],'shipping_discount'));
		    				$pro_dis =array_sum(array_column($order_arr['fulfill'],'product_discount'));

		    				$is_direct_payment = $this->MakerModel->where('user_id',$order_arr['maker_id'])->first();
			         		if(isset($is_direct_payment))
			         		{
			         		$temp_vendor_data_arr = $is_direct_payment->toArray();
			         		$is_direct_payment    = isset($temp_vendor_data_arr['is_direct_payment'])?$temp_vendor_data_arr['is_direct_payment']:0;
			         		}

			    			$total_retail_price = $total_retail_price+$pro_ship_charge-$pro_dis;
		    				$arr_order['order_no'] = $fulfill_order_no;
		    				$arr_order['maker_id'] = $order_arr['maker_id'];
		    				$arr_order['customer_id'] = $order_arr['customer_id'];
		    				$arr_order['transaction_id'] = $order_arr['transaction_id'];
		    				$arr_order['total_retail_price'] = $total_retail_price;
		    				$arr_order['shipping_addr'] = $order_arr['shipping_addr'];
		    				$arr_order['shipping_addr_zip_code'] = $order_arr['shipping_addr_zip_code'];
		    				$arr_order['billing_addr'] = $order_arr['billing_addr'];
		    				$arr_order['billing_addr_zip_code'] = $order_arr['billing_addr_zip_code'];
		    				$arr_order['payment_term'] = $order_arr['payment_term'];
		    				$arr_order['split_order_id'] = $order_arr['id'];
		    				$arr_order['is_direct_payment'] = $is_direct_payment;

		    				$create_quote = $this->CustomerQuotesModel->create($arr_order);

		    				foreach ($order_arr['fulfill'] as $key => $product) {

			        		$quote_product_arr = [];
			        		

			        		$quote_product_arr['customer_quotes_id'] = $create_quote->id;
			        		$quote_product_arr['product_id']         = $product['product_id'];
			        		$quote_product_arr['sku_no']             = $product['sku_no'];
			        		$quote_product_arr['qty']                = $product['qty'];
			        		$quote_product_arr['wholesale_price']       = $product['unit_price'];
			        		$quote_product_arr['unit_retail_price']= $product['unit_price'];
			        		$quote_product_arr['retail_price']    = $product['qty']*$product['unit_price'];
			        		$quote_product_arr['description']  		 = '';


			        		$quote_product_arr['shipping_discount']  = $product['shipping_discount'];
			        		$quote_product_arr['shipping_charge']    = $product['shipping_charges']+$quote_product_arr['shipping_discount'];

			        		$quote_product_arr['product_discount']  = $product['product_discount'];
			        		
			        		$grand_total = $product['total_price'] + $product['shipping_charges']-$quote_product_arr['shipping_discount']-$product['product_discount']; 
			        		$quote_product_arr['retail_price']    = $grand_total;
			        		$create_quote_product = $this->CustomerQuotesProductModel->create($quote_product_arr);
		    				}

		    				$payment_status = $this->payment_process($arr_order);

			        		 	
		    				if ($payment_status['status'] == 'success') {

		    					/*reduce the quantity of sku*/
		    					$this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);

		    				}


				    }
			

			    		/* Store partial order record */
			    		if (isset($order_arr['partial']) && sizeof($order_arr['partial']) > 0) 
			    		{

			    			$order_arr['partial'] = array_values($order_arr['partial']);

			    			$total_wholesale_price = $total_retail_price = 0;
			    			$pro_ship_charge = 0;
			    			$pro_ship_dis    = 0;
			    			$pro_dis         = 0;

			    			$total_retail_price = array_sum(array_column($order_arr['partial'],'total_price'));
			    			$total_wholesale_price = array_sum(array_column($order_arr['partial'],'total_price'));

			    			$pro_ship_charge = array_sum(array_column($order_arr['partial'],'shipping_charges'));
		    				$pro_ship_dis = array_sum(array_column($order_arr['partial'],'shipping_discount'));
		    				$pro_dis =array_sum(array_column($order_arr['partial'],'product_discount'));

			    			$total_retail_price = $total_retail_price+$pro_ship_charge-$pro_dis;
			    			

		    				$arr_order['order_no'] = $partial_order_no;
		    				$arr_order['maker_id'] = $order_arr['maker_id'];
		    				$arr_order['customer_id'] = $order_arr['customer_id'];
		    				$arr_order['transaction_id'] = $order_arr['transaction_id'];
		    				$arr_order['total_retail_price'] = $total_retail_price;
		    				$arr_order['total_wholesale_price'] = $total_wholesale_price;
		    				$arr_order['shipping_addr'] = $order_arr['shipping_addr'];
		    				$arr_order['shipping_addr_zip_code'] = $order_arr['shipping_addr_zip_code'];
		    				$arr_order['billing_addr'] = $order_arr['billing_addr'];
		    				$arr_order['billing_addr_zip_code'] = $order_arr['billing_addr_zip_code'];
		    				$arr_order['payment_term'] = $order_arr['payment_term'];
		    				$arr_order['split_order_id'] = $order_arr['id'];
		    			

		    				$create_quote = $this->CustomerQuotesModel->create($arr_order);

		    				foreach ($order_arr['partial'] as $key => $product) {
		    					//dd($product);
			        		$quote_product_arr = [];
			        		

			        		$quote_product_arr['customer_quotes_id'] = $create_quote->id;
			        		$quote_product_arr['product_id']         = $product['product_id'];
			        		$quote_product_arr['sku_no']             = $product['sku_no'];
			        		$quote_product_arr['qty']                = $product['qty'];
			        		$quote_product_arr['wholesale_price']       = $product['unit_price'];
			        		$quote_product_arr['unit_retail_price']= $product['unit_price'];
			        		$quote_product_arr['retail_price']    = $product['qty']*$product['unit_price'];
			        		$quote_product_arr['description']  		   = '';


			        		$quote_product_arr['shipping_charge']    = $product['shipping_charges']+$product['shipping_discount'];
			        		$quote_product_arr['shipping_discount']  = $product['shipping_discount'];

			        		$quote_product_arr['product_discount']  = $product['product_discount'];
			        		
			        		$grand_total = $product['total_price'] + $product['shipping_charges']-$quote_product_arr['shipping_discount']-$product['product_discount']; 
			        	
			        		$quote_product_arr['retail_price']    = $grand_total;
			        		 	        		
				        	
			        		$create_quote_product = $this->CustomerQuotesProductModel->create($quote_product_arr);
		    				}

		    				if(empty($order_arr['fulfill']))
		    				{
		    					$main_order_status_update = $this->CustomerQuotesModel->where('id',$order_arr['id'])
		    														 ->where('maker_id',$order_arr['maker_id'])

		    														 ->update(['maker_confirmation' => '1']);
		    				}
		    					
			    		}


			        /*send the mail to retailer*/

                    $customer_email_id = $this->HelperService->get_user_mail($order_arr['customer_id']);
                      
                    $email_status      = $this->send_split_order_mail($order_arr,$customer_email_id,$partial_order_no,$fulfill_order_no);

                    /*-------------------------------------------------------------*/

                    /*send the mail to admin*/
                    $admin_id  = get_admin_id();
                    $admin_email = 0;

                    $admin_details = $this->UserModel->where('id',1)->first();

                    if(isset($admin_details))
                    {
                       $admin_email = $admin_details->email;
                    }


                    $email_status = $this->send_split_order_mail($order_arr,$admin_email,$partial_order_no,$fulfill_order_no);

                    
                     /* send notification and mail to the vendor and admin
	                for insufficient inventory after confirm the order*/  
	                                 
	                                 
                    /* get all product list from order id*/
                    $product_inventory_arr = [];

                    $order_product_details = $this->CustomerQuotesProductModel
                                                ->with(['product_details'])
                                                ->where('customer_quotes_id',$order_id)
                                                ->get()
                                                ->toArray();
                  

                    if(isset($order_product_details) && count($order_product_details)>0)
                    {
                    	foreach($order_product_details as $key => $product) 
                    	{  
                    		// check inventory
                    		$inventory = $this->ProductInventoryModel
                    		                  ->where('product_id',$product['product_id'])
                    		                  ->where('sku_no',$product['sku_no'])
                    		                  ->pluck('quantity')
                    		                  ->first();

                    		if($inventory <=200)
                    		{
                    			$product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

                    			$product_inventory_arr[$key]['sku_no']= $product['sku_no'];

                                $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                    		}                 
                    	

                    	}
                    }
                     
                   
                    if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                    {
                        $key1 = '';
                        //send vendor notification
                       
		        		$arr_notify_data                 = [];
					    $arr_notify_data['from_user_id'] = $admin_id or '';

					    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
                        $html = '';
					    $html= "Following products having insufficient inventory: ";

					    foreach ($product_inventory_arr as $key => $value) {

                        if($key == 0)
                        {
                           $key1 = 1;
                        }
                        else
                        {
                          $key1 = $key+1;
                        }

					    $html.= '  '.$key1.') Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
					           ' and Available Stock : '.$value['inventory_stock'].'  ';
					    }

                        $arr_notify_data['description']  = $html;
					    $arr_notify_data['title']        = 'Insufficient Inventory';
					    $arr_notify_data['type']         = 'maker';  
					    $arr_notify_data['link']         = '';  

	        			$this->CustomerOrderService->save_notification($arr_notify_data);
                    }

                    /*send email to the vendor*/
                    $maker_id = '';

                    $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

                    $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

                    $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'customer_order'); 

                    $email_status  = $this->EmailService->send_mail($arr_mail_data);
                      

                   /*------------------------------------------------------*/



			        $response['status']      = 'success';
			        $response['description'] = 'Order is split because of inadequate inventory to fulfill.';
			        return $response;
			    		
			    	}
			    	$response['status']      = 'warning';
			        $response['description'] = 'Something went wrong, please try again';
			        return $response;
			    }
	    		
	    		if($out_of_stock_details && count($out_of_stock_details)>0)	    		
	    		{

	    			$response['status']      = 'warning';
			        $response['description'] = '';
			        return $response;
	    		}


    		}
    		else
    		{
    			$response['status']      = 'warning';
	        	$response['description'] = 'Something went wrong, please try again.';
	        	return $response;
    		
    		}
	    	
    	}
    }

    /*
    	Author: Bhagyashri
    	date: 19-12-2019
    	Start Time: 2:25 PM
    	End Time: 3:10 PM
    	Discription : Calculate product sipping charges and product discount
    */
    public function product_discount($product_id, $quantity)
    {
    	$arr_product = $arr_discount = [];
    	$total_retail_price = 0;
    	$obj_product = $this->ProductsModel->where('id',$product_id)->first();
    	if ($obj_product) {
    		
    		$arr_product_details = $obj_product->toArray();
    	}
    	// dd($arr_product_details);
    	$total_price = $quantity * $arr_product_details['retail_price'];

    	$total_retail_price = isset($total_price)?$total_price:0;


    	if($arr_product_details['shipping_type']==2) 
    	{
    	    if($total_retail_price>=$arr_product_details['minimum_amount_off'])
            {
               
                $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

                if(is_numeric($shipping_charges) && is_numeric($arr_product_details['off_type_amount']))
                {

                    $discount_amount =  $shipping_charges * $arr_product_details['off_type_amount']/ 100;
                    $shipping_charges = $shipping_charges-$discount_amount;
                    $arr_discount['shipping_discount'] = $discount_amount;
                    $arr_discount['shipping_charges'] = $shipping_charges;
                }
                else
                {
                    $arr_discount['shipping_discount'] = 0;
                    $arr_discount['shipping_charges'] = 0;
                }
                
            }
            else
            {

             	$shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
             	$arr_discount['shipping_charges'] = $shipping_charges;
             	$arr_discount['shipping_discount'] = 0;

            }
        }

        if($arr_product_details['shipping_type']==1) 
        { 
            if($total_retail_price<$arr_product_details['minimum_amount_off'])
            {
                $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
                   
                $arr_discount['shipping_charges'] = isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
                $arr_discount['shipping_discount'] =  0;

            }
            else
            {
                   
                $arr_discount['shipping_discount'] = 0;
                $arr_discount['shipping_charges'] = 0;
                //dd($cart_product_arr[$key]['shipping_charges']);

            }
        }

        if($arr_product_details['shipping_type']==3) 
        {
            if($total_retail_price>=$arr_product_details['minimum_amount_off'])
            {
        	
        		
              $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
              $shipping_charges = $shipping_charges;
              $arr_discount['shipping_charges'] = $shipping_charges;
              $arr_discount['shipping_discount'] = $arr_product_details['off_type_amount'];
            }
            elseif($total_retail_price<$arr_product_details['minimum_amount_off'])
            {
        	

             	$shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
             	$arr_discount['shipping_charges'] = $shipping_charges;
             	$arr_discount['shipping_discount'] = 0;
            }
          

        }
        if($arr_product_details['prodduct_dis_type']==1)
		{ 

		    if($total_retail_price>=$arr_product_details['product_dis_min_amt'])
            {
                $pro_discount =  $total_retail_price * $arr_product_details['product_discount']/ 100;
		       	$arr_discount['product_discount'] = isset($pro_discount)?$pro_discount:0;

            }
            else
            {                                 
                $arr_discount['product_discount'] = 0;
            }

		}    
	    if($arr_product_details['prodduct_dis_type']==2)
	    {
	    	if($total_retail_price>=$arr_product_details['product_dis_min_amt'])
            {
                $pro_discount = $arr_product_details['product_discount'];
	      		$arr_discount['product_discount'] = isset($pro_discount)?$pro_discount:0;

            }
            else
            {                                 
                $arr_discount['product_discount'] = 0;
            }
	      
		
	    }

        return $arr_discount;
    } 

    public function send_mail($to_mail_id=false,$arr_product,$order_no,$user_id,$charge_status=false,$loggedInUserId,$orderId=false,$isDirectPayment=false)
    {

    	$temp_data = isset($arr_product['leads_details'])?$arr_product['leads_details']:$arr_product;
    		// dd(123,$temp_data);
    	/* Build array of mail data */
			foreach($temp_data as $key => $product)
			{
				$mail_data[$user_id][$key]['unit_price']       = isset($product['unit_retail_price'])?$product['unit_retail_price']:0.00;

				
				$mail_data[$user_id][$key]['product_id']       = isset($product['product_id'])?$product['product_id']:0;
				$mail_data[$user_id][$key]['sku_no']       = isset($product['sku_no'])?$product['sku_no']:0;

				$mail_data[$user_id][$key]['item_qty']         = isset($product['qty'])?$product['qty']:0.00;

				$mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

				$prod_retail_price = $mail_data[$user_id][$key]['unit_price'] * $mail_data[$user_id][$key]['item_qty'];

				$mail_data[$user_id][$key]['total_retail_price'] = isset($product['retail_price'])?$product['retail_price']:$product['retail_price'];

				$mail_data[$user_id][$key]['retail_price']  = isset($prod_retail_price)?$prod_retail_price:0.00;

				
				$prod_ship_charge = isset($product['shipping_charge'])?$product['shipping_charge']:0.00;

				$prod_ship_disc = isset($product['shipping_discount'])?$product['shipping_discount']:0.00;

				$mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

				$mail_data[$user_id][$key]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:$prod_ship_charge;

				$mail_data[$user_id][$key]['shipping_discount'] = isset($product['shipping_charges_discount'])?$product['shipping_charges_discount']:$prod_ship_disc;
				$mail_data[$user_id][$key]['maker_id'] = isset($loggedInUserId)?$loggedInUserId:0;
			}
		
			/* sending mail */

			if($charge_status == 'succeeded')
			{
				$charge_status = 'Paid';
			}
    		
			$email_status = $this->CustomerOrderService->send_mail($to_mail_id,$mail_data,$order_no,$charge_status,$loggedInUserId,$orderId,$isDirectPayment);
			
		    return $email_status;	    		
	}

    public function payment_process($order_arr)
    {
    	$loggedInUserId = 0;
        $user = \Sentinel::check();

        if($user && $user->inRole('customer'))
        {
            $loggedInUserId = $user->id;
        }
    	
    	$order_details = $this->CustomerQuotesModel->where('order_no',$order_arr['order_no'])
	    									->with(['transaction_details.strip_key_details','quotes_details','quotes_details.product_details','maker_data.stripe_account_details'])
	    								   ->first();



		if($order_details)
		{
			$order_details = $order_details->toArray();
			$isDirectPayment = isset($order_details['maker_details']['is_direct_payment'])?$order_details['maker_details']['is_direct_payment']:0;
		}
		
	    								 
    	$arr_charge_data['order_amount'] = isset($order_details['total_retail_price'])?$order_details['total_retail_price']:0;

			$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

			$arr_charge_data['stripe_key'] = isset($order_details['transaction_details']['strip_key_details']['secret_key'])?$order_details['transaction_details']['strip_key_details']['secret_key']:false;

			$paymentStripeKeyId = isset($order_details['transaction_details']['strip_key_details']['id'])?$order_details['transaction_details']['strip_key_details']['id']:false;

			$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

			$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';

      	if(isset($arr_charge_data) && count($arr_charge_data) > 0 )
		{
			if($order_details['payment_term'] == 'Online/Credit')
			{

				// Payment duducted
				DB::beginTransaction();
				try{

				    // $isDirectPayment = isset($order_details['maker_data']['is_direct_payment'])?$order_details['maker_data']['is_direct_payment']:0;

				    $isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;

    				$endUserId = isset($order_details['customer_id'])?$order_details['customer_id']:0;

    				$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';
    				
    				$cardId = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

    				$arrData = [];

    				$arrData['customerId']   = $customerId;
    				$arrData['endUserId']   = $endUserId;
    				$arrData['vendorId']     = $loggedInUserId;
    				$arrData['order_amount'] = $arr_charge_data['order_amount'];
    	    		$arrData['stripe_key']   = $arr_charge_data['stripe_key'];
    				$arrData['cardId']       = $cardId;
    				$arrData['order_no']     = $arr_charge_data['order_no'];


    				if($isDirectPayment == 1)
    				{
    					$stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($loggedInUserId);

                        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

                        $stripKey = isset($stripeApiKeyData['stripeKey'])?$stripeApiKeyData['stripeKey']:'';


 	    				$charge = $this->StripePaymentService->create_customer_direct_charge($arrData);
    				}
    				else
    				{
    					$admin_user_id       = get_admin_id();

    					$stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($admin_user_id);

						$stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

			    	    $arrStripeDetails = $this->StripePaymentService->build_stripe_card_and_key_data($cardId,$customerId,$arr_charge_data['stripe_key'],$paymentStripeKeyId,$admin_user_id,$endUserId);

			    	    $arr_charge_data['customer_id']   = $arrStripeDetails['customer_id'];

						$arr_charge_data['stripe_key']    = $arrStripeDetails['secret_key'];

						$arr_charge_data['card_id']       = $arrStripeDetails['card_id'];

 	    				$charge = $this->StripePaymentService->create_charge($arr_charge_data);
    				}
					
					if(isset($charge['status']) && $charge['status'] == 'Error')
					{
						$response['status']      = 'warning';
				        $response['description'] = isset($charge['description'])?$charge['description']:'Something went wrong, please try again.';
				        return $response;
					}

					if($charge)
	        		{
						
		        		$charge_status = $charge->status;

			        	$charge_status = isset($charge['status'])?$charge['status']:"";

			        	if($charge_status == 'succeeded')
			        	{
			        		$status = '2';
			        	}
			        	else if($charge_status == 'pending')
			        	{
			        		$status = '1';
			        	}
			        	else{
			        		$status = '3'; // Failed
			        	}
			        	

			        	$arr_data['user_id']             = $order_details['customer_id'] or '';
		        		$arr_data['order_id']            = $order_details['id'] or '';
		        		$arr_data['order_no']            = $order_details['order_no'] or '';
		        		$arr_data['amount']              = $order_details['total_retail_price'] or '';
		        		//$arr_data['transaction_id']      = $charge->id or '';
		        		$arr_data['transaction_id']      = isset($charge['id'])?$charge['id']:"";
		        		$arr_data['transaction_status']  = $status or '';
		        		$arr_data['payment_type']        = '1' or '';

		        		$transaction_entry = $this->TransactionMappingModel->create($arr_data);

		        		if($status == '2')
		        		{
		        			if (isset($order_details['quotes_details'])) {
		        				
		        				$order_status_update = $this->CustomerQuotesModel->where('order_no',$order_arr['order_no'])
		        													 ->where('maker_id',$order_details['maker_id'])

		        													 ->update(['maker_confirmation' => '1',
						        		                                'stripe_key_id'=>$stripKeyId]);
		        													 

		        				$main_order_status_update = $this->CustomerQuotesModel->where('id',$order_details['split_order_id'])
		        													 ->where('maker_id',$order_details['maker_id'])

		        													 ->update(['maker_confirmation' => '1',
						        		                                'stripe_key_id' => $stripKeyId]);
		        			}

		        			elseif (isset($order_details['leads_details'])) {
		        				
		        				$order_status_update = $this->RepresentativeLeadsModel->where('order_no',$order_arr['order_no'])
		        													 ->where('maker_id',$order_details['maker_id'])

		        													 ->update(['maker_confirmation' => '1']);
		        													 

		        				$main_order_status_update = $this->RepresentativeLeadsModel->where('id',$order_details['split_order_id'])
		        													 ->where('maker_id',$order_details['maker_id'])

		        													 ->update(['maker_confirmation' => '1']);
		        			}
		        				

		        		    /* send retailer notification*/

		        	        $view_href = url('/').'/customer/my_orders/view/'.base64_encode($order_details['id']);


		        		    $arr_notify_data                 = [];
					    	$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
					    	$arr_notify_data['to_user_id']   = $order_details['customer_id'] or '';

					    	/*$arr_notify_data['description']  = 'Your Order('.'<a href="'.$view_href.'">'.$order_details['order_no'].'</a>'.') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';*/

					    	$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_retail_price']).' is deducted from your account.';

					    	$arr_notify_data['title']        = 'Order Confirmed';
					    	$arr_notify_data['type']         = 'customer';  
					    	$arr_notify_data['link']         = $view_href;  

		        			$this->CustomerOrderService->save_notification($arr_notify_data);

		        			/* get admin id */

		        			$admin_id  = get_admin_id();
		        			$view_href =  url('/').'/admin/customer_orders/view/'.base64_encode($order_details['id']);

		        			$maker_details = \Sentinel::findById($order_details['maker_id']);

			        		/* send admin notification*/
			        		$arr_notify_data                 = [];
						    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
						    $arr_notify_data['to_user_id']   = $admin_id or '';

						    /*$arr_notify_data['description']  = 'Order('.'<a href="'.$view_href.'">'.$order_details['order_no'].'</a>'.') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;*/

						    $arr_notify_data['description']  = 'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

							$arr_notify_data['title']        = 'Orders Confirmed';
							$arr_notify_data['type']         = 'admin'; 
							$arr_notify_data['link']         = $view_href; 
		        		


		        			$this->CustomerOrderService->save_notification($arr_notify_data);


		        			/* send vendor notification*/
		        			$view_href =  url('/').'/vendor/customer_orders/view/'.base64_encode($order_details['id']);
			        		$arr_notify_data                 = [];
						    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
						    $arr_notify_data['from_user_id'] = $admin_id or '';

						    $arr_notify_data['description']  = 
						    'Order('.$order_details['order_no'].') is confirmed and payment went through successfully.';

						    $arr_notify_data['title']        = 'Order Confirmed';
						    $arr_notify_data['type']         = 'maker';  
						    $arr_notify_data['link']         = $view_href;  

		        			$this->CustomerOrderService->save_notification($arr_notify_data);

		        			if($isDirectPayment == 1)
    				        {
			        			$view_href = '';
				        		$arr_notify_data                 = [];
							    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
							    $arr_notify_data['from_user_id'] = $admin_id or '';

							    $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_retail_price']).' is credited in your account.';

							    $arr_notify_data['title']        = 'Order Confirmed';
							    $arr_notify_data['type']         = 'maker';  
							    $arr_notify_data['link']         = $view_href;  

			        			$this->CustomerOrderService->save_notification($arr_notify_data);
				        		
				        		/* get user mail details */
								$user_details = \Sentinel::findById($order_details['maker_id']);

								$user_email = isset($user_details->email)?$user_details->email:false;

			        		    /* send mail to vendor for order amount is credited on your acount */

	                           $is_mail_sent = $this->EmailService->amount_credited_email(num_format($order_details['total_retail_price']),$order_details['order_no'],$user_email,false);
			        		}
			        		/* Send mail to retailer */

			        		// $retailer_mail = get_retailer_mail($order_details['retailer_id']);

			        		/*if(isset($order_details['quotes_details']))
			        		{
			        			$this->send_mail($retailer_mail,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId);
			        		}
			        		elseif(isset($order_details['leads_details']))
			        		{
			        			$this->send_mail($retailer_mail,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId);
			        		}*/
			        		
		    		
			        		DB::commit();
				        	$response['status'] = 'success';

				        	
				        	return $response;
			        	}
			        	else
			        	{
			        		$response['status'] = 'warning';
				        	$response['description'] = 'Sorry, Payment has been failed please try again.';
				        	return $response;
			        	}

					}
					
			  	}

			  	catch(Exception $e)
		        {  
		         
	            DB::rollback();
	            
	            $response['status']      =   'warning';
	            $response['description'] = $e->getMessage();

	            return $response;
		        }	
			}

			elseif($order_details['payment_term'] == 'Net30')
			{
				$order_status_update = $this->CustomerQuotesModel->where('id',$order_id)
        													 ->where('maker_id',$order_details['maker_id'])

        													 ->update(['maker_confirmation' => '1']);

         		$view_href =  url('/').'/customer/my_orders/view/'.base64_encode($order_details['id']);


        		$arr_notify_data                 = [];

		    	$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
		    	$arr_notify_data['to_user_id']   = $order_details['customer_id'] or '';
		    	/*$arr_notify_data['description']  = 'Your order('.'<a href="'.$view_href.'">'.$order_details['order_no'].'</a>'.') is confirmed successfully.';*/

		    	$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed successfully.';

		    	$arr_notify_data['title']        = 'Order Confirmed';
		    	$arr_notify_data['type']         = 'customer';  
		    	$arr_notify_data['link']         = $view_href;  

				
				$this->CustomerOrderService->save_notification($arr_notify_data);

        		/* get admin id */

    			$admin_id                        = get_admin_id();
    			$view_href                       = url('/').'/admin/customer_orders/view/'.base64_encode($order_details['id']);

    			$maker_details = \Sentinel::findById($order_details['maker_id']);

        		/* send admin notification*/
        		$arr_notify_data                     = [];
			    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
			    $arr_notify_data['to_user_id']   = $admin_id or '';

			    /*$arr_notify_data['description']  = 'Order('.'<a href="'.$view_href.'">'.$order_details['order_no'].'</a>'.') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;*/

			    $arr_notify_data['description']  = 'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

				$arr_notify_data['title']        = 'Orders Confirmed';
				$arr_notify_data['type']         = 'admin';  
				$arr_notify_data['link']         = $view_href;  

    			$this->CustomerOrderService->save_notification($arr_notify_data);


			    /* send vendor notification*/
    			$view_href =  url('/').'/vendor/customer_orders/view/'.base64_encode($order_details['id']);
        		$arr_notify_data                 = [];
			    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
			    $arr_notify_data['from_user_id'] = $admin_id or '';

			    $arr_notify_data['description']  = 
			    'Order('.$order_details['order_no'].') is confirmed and payment went through successfully : ';

			    $arr_notify_data['title']        = 'Order Confirmed';
			    $arr_notify_data['type']         = 'maker';  
			    $arr_notify_data['link']         = $view_href;  

    			$this->CustomerOrderService->save_notification($arr_notify_data);


	        	$response['status']      = 'success';
                $response['description'] = 'Order has been confirmed and payment went through successfully.';
	        	return $response;
			}

        	else
        	{
        		$response['status']      = 'warning';
	        	$response['description'] = 'Sorry, Payment has been failed, please try again.';
	        	return $response;
        	}
	        	
        }
    }


   

    /*Order from customer send mail for split order to customer and admin*/

	public function send_split_order_mail($order_arr=false,$to_mail_id=false,$partial_order_no=false,$fulfill_order_no=false)
	{
	    $order_summary = [];
        $to_mail_id    = $to_mail_id;
        $user = Sentinel::check();
		
		$loggedIn_userId = 0;

		if($user)
		{
		    $loggedIn_userId = $user->id;
		}   
	
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = 0;
		     
    
     	$email_arr = ['jojymu@getnada.com'];
     	  	
     	$arr_built_content = [
     	  						'EMAIL'          => $email_arr[0],
                                'order_details'  =>  $order_summary
                                   
                             ];

        $arr_mail_data['arr_built_content']     = $arr_built_content;
           
        $html_build = view('front.email_template.split_order',$arr_mail_data)->render();

        /*if admin is there so change the template*/

        $user_id = $this->UserModel->where('email',$to_mail_id)->pluck('id')->first();



        /*total calculation for main order,confirm order,pending order*/

        $calculation = [];
        $sub_total  = $total_sub_total = $total_shipping_charges = $total_shipping_discount = $total_product_discount = $final_total = 0.00; 
        
        $fulfill_sub_total = $fulfill_total_ship_charges = $fulfill_total_ship_discount = $fulfill_total_product_discount = $fulfill_final_total = 0.00;

        $partial_sub_total = $partial_total_ship_charges = $partial_total_ship_discount = $partial_total_product_discount =$partial_final_total = 0.00;

      
        if(isset($order_arr['quotes_details']) && count($order_arr['quotes_details'])>0)
        {
            foreach($order_arr['quotes_details'] as $key=>$quote)
            {
            	$sub_total = $quote['qty']*$quote['unit_retail_price'];

                $total_sub_total+= $sub_total;
            }
        }

        if(isset($order_arr['leads_details']) && count($order_arr['leads_details'])>0)
        {
            foreach($order_arr['leads_details'] as $key=>$quote)
            {
            	$sub_total = $quote['qty']*$quote['unit_retail_price'];

                $total_sub_total+= $sub_total;
            }
        }

  
        if(isset($order_arr['quotes_details']))
        {
        	$total_shipping_charges    = array_sum(array_column($order_arr['quotes_details'],'shipping_charge'));
	        $total_shipping_discount   = array_sum(array_column($order_arr['quotes_details'],'shipping_discount'));
	        $total_product_discount    = array_sum(array_column($order_arr['quotes_details'],'product_discount'));
        }
        if (isset($order_arr['leads_details'])) {
        	$total_shipping_charges    = array_sum(array_column($order_arr['leads_details'],'shipping_charge'));
	        $total_shipping_discount   = array_sum(array_column($order_arr['leads_details'],'shipping_discount'));
	        $total_product_discount    = array_sum(array_column($order_arr['leads_details'],'product_discount'));
        }
        

        $final_total = $total_sub_total+$total_shipping_charges-$total_shipping_discount-$total_product_discount;

        $calculation['mail_order']['subtotal']                = isset($total_sub_total)?$total_sub_total:0.00;
        $calculation['mail_order']['total_shipping_charges']  = isset($total_shipping_charges)?$total_shipping_charges:0.00;
        $calculation['mail_order']['total_shipping_discount'] = isset($total_shipping_discount)?$total_shipping_discount:0.00;
        $calculation['mail_order']['total_product_discount']  = isset($total_product_discount)?$total_product_discount:0.00;
        $calculation['mail_order']['final_total']             = isset($final_total)?$final_total:0.00;


     

	    $fulfill_sub_total               = array_sum(array_column($order_arr['fulfill'],'total_price'));

	    $fulfill_total_ship_charges      = array_sum(array_column($order_arr['fulfill'],'shipping_charges'));
	    $fulfill_total_ship_discount     = array_sum(array_column($order_arr['fulfill'],'shipping_discount'));
	    $fulfill_total_product_discount  = array_sum(array_column($order_arr['fulfill'],'product_discount'));

	    $fulfill_final_total = $fulfill_sub_total+$fulfill_total_ship_charges-$fulfill_total_ship_discount-$fulfill_total_product_discount;


        $calculation['fulfill']['sub_total']               = isset($fulfill_sub_total)?$fulfill_sub_total:0.00;
        $calculation['fulfill']['total_shipping_charges']  = isset($fulfill_total_ship_charges)?$fulfill_total_ship_charges:0.00;
        $calculation['fulfill']['total_shipping_discount'] = isset($fulfill_total_ship_discount)?$fulfill_total_ship_discount:0.00;
        $calculation['fulfill']['total_product_discount']  = isset($fulfill_total_product_discount)?$fulfill_total_product_discount:0.00;
        $calculation['fulfill']['final_total']             = isset($fulfill_final_total)?$fulfill_final_total:0.00;
	        



        $partial_sub_total               = array_sum(array_column($order_arr['partial'],'total_price'));

	    $partial_total_ship_charges      = array_sum(array_column($order_arr['partial'],'shipping_charges'));
	    $partial_total_ship_discount     = array_sum(array_column($order_arr['partial'],'shipping_discount'));
	    $partial_total_product_discount  = array_sum(array_column($order_arr['partial'],'product_discount'));

	    $partial_final_total = $partial_sub_total+$partial_total_ship_charges-$partial_total_ship_discount-$partial_total_product_discount;


	    $calculation['partial']['sub_total']               = isset($partial_sub_total)?$partial_sub_total:0.00;
        $calculation['partial']['total_shipping_charges']  = isset($partial_total_ship_charges)?$partial_total_ship_charges:0.00;
        $calculation['partial']['total_shipping_discount'] = isset($partial_total_ship_discount)?$partial_total_ship_discount:0.00;
        $calculation['partial']['total_product_discount']  = isset($partial_total_product_discount)?$partial_total_product_discount:0.00;
        $calculation['partial']['final_total']             = isset($partial_final_total)?$partial_final_total:0.00;


        $date = date("d-m-Y",strtotime($order_arr['created_at']));
        $order_place_date = us_date_format($date);

        /*-----------------------------------------------------------------*/

        
	    if($user_id == 1)
        {
          $obj_email_template = $this->EmailTemplateModel->where('id','45')->first();
          if($obj_email_template)
	      	{
	        	$arr_email_template = $obj_email_template->toArray();
	        	

	        	$content = $arr_email_template['template_html'];
	        	// $deducted_amount = $arr_email_template
 	        }
	       
			
			$content = str_replace("##ORDER_NO##",$order_arr['order_no'],$content);
	
        }
        else
        {
          $obj_email_template = $this->EmailTemplateModel->where('id','44')->first();

          	if($obj_email_template)
  			{
    			$arr_email_template = $obj_email_template->toArray();
    			$content = $arr_email_template['template_html'];
    			$from_user_id = isset($arr_email_template['template_from_mail']) ? $arr_email_template['template_from_mail'] : ''; //  Sender mail from database
    		}
	
        }
       
        $arr_site_setting = get_site_settings(['site_name','website_url']);

        $content = str_replace("##PROJECT_NAME##",$arr_site_setting['site_name'],$content);
    		
    	$content = view('email.front_general',compact('content'))->render();
    	$content = html_entity_decode($content);
        
       
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;
       		
	  	$sno  = '0';
	  	$role = 'Customer';

	
	    $pdf = PDF::loadView('front/customer_split_order_invoice',compact('order_arr','partial_order_no','fulfill_order_no','calculation','order_place_date'));
	  	
   	    $currentDateTime = 'Split_Order'.date('H:i:s').'.pdf';
	
	    Storage::put('public/pdf/'.$currentDateTime, $pdf->output());
	 	$pdfpath = Storage::url($currentDateTime);

	 	$file_to_path = url("/")."/storage/app/public/pdf/".$currentDateTime;

        $maker_email = isset($user->email)?$user->email:''; 

          
        $send_mail = Mail::send(array(),array(), function($message) use($content,$email_arr,$file_to_path,$to_mail_id,$from_user_id,$pdf,$currentDateTime)
	    {
	          //$message->from('admin@justgot2haveit.com');
	        $message->from($from_user_id);
	        $message->to($to_mail_id)
			          ->subject('Split Order')
			          ->setBody($content, 'text/html');
			/*$message->attach($file_to_path);*/
            $message->attachData($pdf->output(), $currentDateTime, [
                    'mime' => 'application/pdf',
                ]);

	    });

		     	 
	}


	



    public function mailForInsufficientInventory($email,$order_id,$status)
    {   


    	$product_inventory_arr = [];

        $user = $this->get_user_details($email);
      	
        if(isset($user) && $user)
        {
            $arr_user = $user->toArray();  
            $html     = '';
           
            /* get all product list from order id*/
            
            if(isset($status) && $status == 'customer_order')
            {
                $order_product_details =  $this->CustomerQuotesProductModel
                                               ->with(['product_details'])
                                               ->where('customer_quotes_id',$order_id)
                                               ->get()
                                               ->toArray();


	            if(isset($order_product_details) && count($order_product_details)>0)
	            {
	            	foreach($order_product_details as $key => $product) 
	            	{  
	            		// check inventory
	            		$inventory = $this->ProductInventoryModel
	            		                  ->where('product_id',$product['product_id'])
	            		                  ->where('sku_no',$product['sku_no'])
	            		                  ->pluck('quantity')
	            		                  ->first();

	            		if($inventory <=200)
	            		{
	            			$product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

	            			$product_inventory_arr[$key]['sku_no']= $product['sku_no'];

	                        $product_inventory_arr[$key]['inventory_stock'] = $inventory;
	            		}                 
	            	

	            	}
	            }
	                                 
	            if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
	            {
	               
	                $html = '';
				    $html = "Following products having insufficient inventory:<br> ";
                    $key1 = '';

				    foreach ($product_inventory_arr as $key => $value)
				    {

		                if($key == 0)
		                {
		                   $key1 = 1;
		                }
		                else
		                {
		                	$key1 = $key+1;
		                }

					    $html.= $key1.') Product Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
					           '  and Available Stock : '.$value['inventory_stock'].'</br>';
					}
					
					// $site_setting_obj = SiteSettingModel::first();
					// if($site_setting_obj)
					// {
					// 	$site_setting_arr = $site_setting_obj->toArray();            
					// }

					// $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz';	
					$arr_site_setting = get_site_settings(['site_name','website_url']);

	                $arr_built_content = ['USER_NAME'       => $arr_user['first_name'],
	                                      'APP_NAME'        => $arr_site_setting['site_name'],
										  'HTML'            => $html,
										  'SITE_URL'     	=> $arr_site_setting['site_name']
										];

		            $arr_mail_data                      = [];
		            $arr_mail_data['email_template_id'] = '51';
		            $arr_mail_data['arr_built_content'] = $arr_built_content;
		            $arr_mail_data['user']              = $arr_user;
		            $arr_mail_data['arr_user']              = $arr_user;

	                return $arr_mail_data;
	            }
	            else
	            {
	            	$arr_mail_data = [];
	            	return $arr_mail_data;
	            }
            }  
            else if(isset($status) && $status == 'rep_sales_order')
            {

                $order_product_details = $this->RepresentativeProductLeadsModel->with(['product_details'])
                                                ->where('representative_leads_id',$order_id)
                                                ->get()
                                                ->toArray();


                if(isset($order_product_details) && count($order_product_details)>0)
                {
                    foreach($order_product_details as $key => $product) 
                    {  
                        // check inventory
                        $inventory = $this->ProductInventoryModel
                                		  ->where('product_id',$product['product_id'])
                                		  ->where('sku_no',$product['sku'])
                                		  ->pluck('quantity')
                                		  ->first();

                		if($inventory <=200)
                		{
                			$product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];
                			$product_inventory_arr[$key]['sku_no']= $product['sku_no'];
                            $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                		}                 
                	

                	}
                }
                                 
                if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                {
                    $html = '';
				    $html = "Following products having insufficient inventory:<br> ";
                    $key1 = '';
				    foreach ($product_inventory_arr as $key => $value)
				    {

		                if($key == 0)
		                {
		                   $key1 = 1;
		                }
		                else
		                {
		                   $key1 = $key+1;
		                }

					     $html.= $key1.') Product Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
					           '  and Available Stock : '.$value['inventory_stock'].'</br>';
				    }

				    $arr_site_setting = get_site_settings(['site_name','website_url']);
				 
	                $arr_built_content = ['USER_NAME'       => $arr_user['first_name'],
	                                      'APP_NAME'        => $arr_site_setting['site_name'],
	                                      'HTML'            => $html];

		            $arr_mail_data                      = [];
		            $arr_mail_data['email_template_id'] = '51';
		            $arr_mail_data['arr_built_content'] = $arr_built_content;
		            $arr_mail_data['user']              = $arr_user;
		            $arr_mail_data['arr_user']              = $arr_user;

	                return $arr_mail_data;
                
                } 
                else
	            {
	            	$arr_mail_data = [];
	            	return $arr_mail_data;
	            }                 
            }
        }    

        return false;
    }

    public function  get_user_details($email)
    {
        $credentials = ['email' => $email];
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        if($user)
        {
          return $user;
        }
        return false;
    }

 //    public function verify_order_no($order_no)
	// {
	// 	$customerOrderData = $this->CustomerQuotesModel->where('order_no',$order_no)->count();
	// 	$retailerOrderData = $this->RetailerQuotesModel->where('order_no',$order_no)->count();
	// 	$repOrderData = $this->RepresentativeLeadsModel->where('order_no',$order_no)->count();
		
	// 	if($customerOrderData && $retailerOrderData && $repOrderData > 0)
	// 	{
	// 		$order_no = $this->generate_order_no($order_no);
	// 	}

	// 	return $order_no;
	// }

	// public function generate_order_no($order_no)
	// {

	// 	$ordDigits = substr($order_no, 2);

	// 	$order_no = $ordDigits + 1;
	// 	// dd($ordDigits,$order_no);

	// 	$newOrderNo = 'J2'.$order_no;

	// 	$newOrderNumber = $this->verify_order_no($newOrderNo);

	// 	return $newOrderNumber;
	// }


	public function pay_to_admin(Request $request)
    {
        $form_data = $request->all();
        // dd($form_data);
        $orderId = isset($form_data['order_id'])?$form_data['order_id']:false;
        $vendorId = isset($form_data['maker_id'])?$form_data['maker_id']:false;

        $adminId = get_admin_id();

         /*  Get admin stripe secret key id  */
        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key(get_admin_id());

        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

        /*  Get maker stripe secret key id  */
        $vendorStripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($vendorId);

        $vendorStripKeyId = isset($vendorStripeApiKeyData['id'])?$vendorStripeApiKeyData['id']:'';

        /* get vendor client id */
        
        /*$clien_id = $this->UserStripeAccountDetailsModel->where('user_id',$vendorId)
			        									->pluck('client_id')
			        									->first();
    									// dd($clien_id);
		if($clien_id)
		{*/
	        $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$adminId)
	                                                                      ->where('vendor_id',$vendorId)
	                                                                      ->where('admin_stripe_key_id',$stripKeyId)
                                                                          ->where('vendor_stripe_key_id',$vendorStripKeyId)
	                                                                      ->first();

	       /* $vendorStripeKey = $this->UserStripeAccountDetailsModel->where('user_id',$vendorId)
	                                                               ->pluck('secret_key')
	                                                               ->first();*/
	               
	        if($obj_stripe_account_details)
	        {

	        	$vendorStripeKey = $this->UserStripeAccountDetailsModel->where('user_id',$vendorId)
	                                                               ->where('is_active','1')
	                                                               // ->pluck('secret_key')
	                                                               ->first();

	        	$vendorStripeKey = isset($vendorStripeKey->secret_key)?$vendorStripeKey->secret_key:false;

	            $stripe_acc_id = isset($obj_stripe_account_details->stripe_acc_id)?$obj_stripe_account_details->stripe_acc_id:false;
	            
	            if($stripe_acc_id)
	            {
	                $order_data = get_customer_order_data($orderId);

	                /*check already paid*/		
	            	if(isset($order_data['admin_commission_status']) && $order_data['admin_commission_status'] == '1')
	                {
	                    $response['status']  = 'pay-warning';
	                    $response['message'] = "Payment already completed for this order.";
	                    
	                   return response()->json($response);
	                }
	            	/*end*/
	                
	                $arr_transfer_data['StripeKey']   = isset($vendorStripeKey)?$vendorStripeKey:'';
	                $arr_transfer_data['amount']      = $form_data['amount'];
	                $arr_transfer_data['account_id']  = $stripe_acc_id;
	                // $arr_transfer_data['description'] = 'Payment for Order No:'.isset($order_data['order_no'])?$order_data['order_no']:false;

	                $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

	                if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
	                {
	                    $response['status'] = 'error';
	                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
	                    return response()->json($response);
	                }

	                if($transfer_response)
	                {   

	                    $arr_data['customer_order_id']         = $form_data['order_id'];
	                    $arr_data['amount']          = $form_data['amount'];
	                    $arr_data['transaction_id']  = $transfer_response->balance_transaction;
	                    $arr_data['transfer_id']     = $transfer_response->id;
	                    $arr_data['destination_payment'] = $transfer_response->destination_payment;
	                    $arr_data['status']          = '2';
	                    $arr_data['received_by']     = get_admin_id();
	                    $arr_data['paid_by']         = $vendorId;

	                    
	                     /* Create Entry in StripTransaction table */
	                    $create_stripe_transaction = $this->StripeTransactionModel->create($arr_data);

	                    if($create_stripe_transaction)
	                    {
	                    	 $bulk_transaction_key = rand(1000,9999).date("s");
	                    	 $arrUpdateDate = [];
		            		 $arrUpdateDate['admin_commission_status']           = '1';
		            		 $arrUpdateDate['received_commission_stripe_key_id'] = $stripKeyId;
                             $arrUpdateDate['transfer_commission_stripe_key_id'] = $vendorStripKeyId;

	                    	$this->CustomerQuotesModel->where('id',$orderId)->update($arrUpdateDate);
	                    	
	                        $login_user = \Sentinel::check();

	                        if($login_user)
	                        {
	                            $loggedInUserId = $login_user->id;
	                        }
	                        else
	                        {
	                            $loggedInUserId = 1;
	                        }
	                        /*   Notification Sending   */
	                        $notification_arr                 = [];
	                        $notification_arr['from_user_id'] = $vendorId;
	                        $notification_arr['to_user_id']   = get_admin_id();

	                        /*check order from rep or sales*/
	                        $view_href = '';

	                        $view_href = url('/').'/admin/customer_orders/view/'.base64_encode($orderId);
	                  
	                        $notification_arr['description']  = 'Commission ($'.$form_data['amount'].') is received successfully of Order No: '.$order_data['order_no'];

	                        $notification_arr['title']        = 'Commission Received';
	                        $notification_arr['type']         = 'admin'; 
	                        $notification_arr['link']         = $view_href; 

	                        
	                        $this->CustomerOrderService->save_notification($notification_arr);

	                        /* send commission received email with PDF to admin */
	                        //$email_status  = $this->EmailService->send_mail($arr_mail_data);
	                        $admin_email = get_admin_email();

	                         /* Get Admin details */
	                        $credentials = ['email' => $admin_email];
	                      	$arr_user = get_user_by_credentials($credentials);

	                      	/* Get Vendor details */
							$maker_email = $this->HelperService->get_user_mail($vendorId);
							$credentials = ['email' => $maker_email];
							$arr_maker_user = get_user_by_credentials($credentials);

							$arr_site_setting = get_site_settings(['site_name','website_url']);

							$arr_built_content = 
								[
									'commission_amount'   	=> num_format($form_data['amount']),
	                            	'order_no'        	=> $order_data['order_no'],
	                            	'PROJECT_NAME' 		=> $arr_site_setting['site_name'],					                            
	                        	];

		                    $arr_mail_data                      = [];
					        $arr_mail_data['email_template_id'] = '61';
					        $arr_mail_data['arr_built_content'] = $arr_built_content;
					        $arr_mail_data['arr_user']          = $arr_user;


							//$email_status  = $this->EmailService->send_mail($arr_mail_data);
	                        
							$arrMailData['customer_order_data'][] = $order_data;
							$arrUserData['business_details'] = $arr_user;
							$arrUserData['personla_details'] = $arr_maker_user;

							$arrOrderAmount = [];

							/*  ------ Send payment Invoice to Admin -----------------  */
							$invoice_data = $this->GeneralService->send_payment_invoice_to_admin($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount);
							/*  ----------- END ------------  */


	                        // $this->EmailService->commission_paid_mail(num_format($form_data['amount']),$order_data['order_no'],$admin_email);


	                        $response['status']  = 'success';
	                        $response['message'] = 'Commission has been transferred.';
	                    }
	                    else
	                    {
	                        $response['status']  = 'error';
	                        $response['message'] = 'Something went wrong, please try again.';
	                    }
	                    return response()->json($response);
	                }
	                else
	                {
	                    $response['status'] = 'error';
	                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
	                    return response()->json($response);
	                }
	            }
	            else
	            {
	                $response['status'] = 'error';
	                $response['message'] = 'Please verify this users stripe account details.';
	                return response()->json($response);
	            }

	        }
	        else
	        {
	            $response['status']    = 'warning';
	            $response['message']   = "This user are not connected to ".$this->site_setting_arr['site_name']." stripe account";
	            $response['user_id']   = $adminId;
	            // $response['client_id'] = $clien_id;
	            $response['vendor_id'] = $vendorId;

	            return response()->json($response);
	        }
		/*}
		else
		{
			$response['status']  = 'error';
            $response['message'] = "Stripe client id is missing, please provide client id for further transaction.";
            
            return response()->json($response);
		}*/

    }

    

    public function send_stripe_acc_creation_link(Request $request)
    {

      $user_id   = $request->input('user_id');
      // $clientId  = $request->input('client_id');
      $vendor_id = $request->input('vendor_id');

      
       /* get vendor client id */
      $client_id = $this->UserStripeAccountDetailsModel->where('user_id',$vendor_id)
                                                       ->where('is_active','1')
			        									->pluck('client_id')
			        									->first();
     
     if(isset($client_id) && $client_id)
     {
        $userData = [];
		$userData['user_id']   = $user_id;
		$userData['vendor_id'] = $vendor_id;

		$connection_response = $this->StripePaymentService->connection_request($user_id,$client_id,$vendor_id);
	     
	    if($connection_response)
	    {
	     	$response['status']      = 'success';
	       	$response['message']     = 'Link has been sent.';
	    }
	    else
	    {
	       	$response['status']      = 'error';
	       	$response['message']     = 'Something went wrong, please try again.';
	    }
     }
     else
     {
   		$response['status']      = 'error';
        $response['message']     = 'Client id is missing, please verify our client id.';
     }

     return response()->json($response);

    }
}
