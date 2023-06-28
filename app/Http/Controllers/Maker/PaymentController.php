<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TransactionsModel;
use App\Models\RetailerQuotesModel;
use App\Models\TransactionMappingModel;
use App\Models\ProductsModel;
use App\Models\AddressModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesProductModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;
use App\Common\Services\orderDataService;
use App\Common\Services\InventoryService;
use App\Common\Services\EmailService;
use App\Models\EmailTemplateModel;
use App\Models\UserModel;
use App\Models\ProductInventoryModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\ShopSettings;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\StripeTransactionModel;
use App\Models\SiteSettingModel;
use App\Models\PromotionsModel;
use App\Models\PromoCodeModel;
use App\Models\PromotionsOffersModel;
use App\Models\ProductSizesModel;
use App\Models\SizeModel;


use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;

use Stripe;
use Flash;
use DB;
use Mail;
use Validator;

use Session, Sentinel,PDF,Storage;


class PaymentController extends Controller
{
    public function __construct(TransactionsModel $TransactionsModel,
    							RetailerQuotesModel $RetailerQuotesModel,   
    							TransactionMappingModel $TransactionMappingModel,
    							StripePaymentService $StripePaymentService,							
    							GeneralService $GeneralService,
    							ProductsModel $ProductsModel,
    							AddressModel $AddressModel,
    							RetailerQuotesProductModel $RetailerQuotesProductModel,
    							RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
    							RepresentativeLeadsModel $RepresentativeLeadsModel,
    							orderDataService $orderDataService,
    							InventoryService $InventoryService,
    							EmailService $EmailService,
    							EmailTemplateModel $EmailTemplateModel,
    							UserModel $UserModel,
    							ShopSettings $ShopSettings,
    							StripeAccountDetailsModel $StripeAccountDetailsModel,
    							UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
    							StripeTransactionModel $StripeTransactionModel,
    							ProductInventoryModel $ProductInventoryModel,
    							PromotionsModel $PromotionsModel,
    							PromoCodeModel $PromoCodeModel,
    							PromotionsOffersModel $PromotionsOffersModel,
    							SiteSettingModel $SiteSettingModel,
    							HelperService $HelperService,
    							CommissionService $CommissionService,
    							ProductSizesModel $ProductSizesModel,
    							SizeModel $SizeModel			
    							)
    {

    	$this->TransactionsModel        = $TransactionsModel;  
    	$this->RetailerQuotesModel      = $RetailerQuotesModel;     
    	$this->TransactionMappingModel  = $TransactionMappingModel;
    	$this->StripePaymentService     = $StripePaymentService;
    	$this->GeneralService           = $GeneralService;
    	$this->InventoryService			= $InventoryService;
    	$this->ProductsModel			= $ProductsModel;
    	$this->AddressModel				= $AddressModel;
    	$this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
    	$this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
    	$this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->orderDataService         = $orderDataService;
        $this->EmailService             = $EmailService;
        $this->EmailTemplateModel       = $EmailTemplateModel;
        $this->UserModel                = $UserModel;
        $this->ShopSettings             = $ShopSettings;
        $this->ProductInventoryModel    = $ProductInventoryModel;
        $this->StripeTransactionModel   = $StripeTransactionModel;
        $this->StripeAccountDetailsModel  = $StripeAccountDetailsModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;
        $this->PromotionsModel         = $PromotionsModel;
        $this->PromoCodeModel          = $PromoCodeModel;
        $this->PromotionsOffersModel   = $PromotionsOffersModel;
        $this->CommissionService       = $CommissionService;
        $this->HelperService            = $HelperService;
        $this->ProductSizesModel        = $ProductSizesModel;
        $this->SizeModel 				= $SizeModel;
        $this->payment_proof = base_path() . '/storage/app/';
    	// $this->stripe_api_key           = 'sk_test_UQE8wx6WNY7Ogj1A5Uy1ZMWA00Cjg1fs3r';
    	$this->stripe_api_key           = get_admin_stripe_key();
    	$this->SiteSettingModel        = $SiteSettingModel;

    	$this->site_setting_obj  = $this->SiteSettingModel->first();
       
	    if(isset($this->site_setting_obj))
	    {
	       $this->site_setting_arr = $this->site_setting_obj->toArray();
	    }

    }

    public function payment($order_id,$slug_chk_qty_available_or_not=null)
    {	   

    	$order_data = [];

	  	$order_id = isset($order_id)?base64_decode($order_id):'';

    	$payment_term = $this->RetailerQuotesModel->where('id',$order_id)->pluck('payment_term')->first();

      
    	if($order_id)
    	{

    		$loggedInUserId = 0;
	        $user = \Sentinel::check();


	        if($user && $user->inRole('maker'))
	        {
	            $loggedInUserId = $user->id;
	        }
      
	    	$order_details = $out_of_stock_details = [];
	    	
	    	$order_details = $this->RetailerQuotesModel->where('id',$order_id)
	    									->with(['quotes_details','quotes_details.product_details'])
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
	    		$order_data = $this->orderDataService->get_order_details('retailer',$ord_no,$order_details['maker_id']);

	    		$arr_available_qty_by_skno = [];
	    		$arr_requested_qty_by_skno = [];
	    		$arr_available_qty_by_skno_size = [];
	    		$arr_requested_qty_by_skno_size = [];
	    		$arr_sku_no = [];

	    		/* If True then Direct Inventory Deduction Applicable
					  False then Order Split Applicable with Email Sent to Retailer for Confirmation 

	    		*/
	    		$is_completely_fulfilled = true;
	    		$is_completely_fulfilled_size = true;

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
					        		$arr_sku_size[] = $product['size_id'];
					        		$arr_product_id[] = $product['product_id'];
					        		$arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);
					        		
					        		/*if size is present then this array will create*/
					        			$arr_requested_qty_by_skno_size[$key]['product_id'] = $product['product_id'];

					        			$arr_requested_qty_by_skno_size[$key]['sku_no'] = $product['sku_no'];

					        			$arr_requested_qty_by_skno_size[$key]['size_id'] = $product['size_id'];

					        			$arr_requested_qty_by_skno_size[$key]['qty'] = intval($product['qty']);
					        		
					        	}
					        }
					    }    	
					}    
				}  
		
			    /* Get Available SKU Quantity */
			    
			    $arr_available_qty_by_skno = $this->InventoryService->get_available_qty($arr_sku_no);
			   
				$arr_available_qty_by_skno_size = $this->InventoryService->get_available_qty_size($arr_sku_no,$arr_product_id,$arr_sku_size);
						    
				
			    $arr_available_qty_by_skno = array_column($arr_available_qty_by_skno, 'quantity','sku_no');

				foreach($arr_available_qty_by_skno_size as $key => $value)
				{
					
					$arr_available_qty_by_skno_size = array_column($value, 'size_inventory','size_id');	

				}				    
			    
			    // dd($arr_available_qty_by_skno_size,$arr_requested_qty_by_skno_size);
	    		
			    if(sizeof($arr_requested_qty_by_skno) > 0)
			    {
			    	foreach ($arr_requested_qty_by_skno as $sku_no => $requested_quantity) {

			    		if($arr_available_qty_by_skno[$sku_no] < $requested_quantity)
			    		{
			    			$is_completely_fulfilled = false;
			    		}
			    	}
			    }
 
			    if(sizeof($arr_available_qty_by_skno_size) > 0)
			    {
			    	$x = 0;
			    	foreach ($arr_available_qty_by_skno_size as $size => $requested_quantity) {

			    		if($arr_requested_qty_by_skno_size[$x]['qty'] > $requested_quantity)
			    		{
			    			$is_completely_fulfilled_size = false;
			    		}
			    	$x++;
			    	}

			    }
			    // dd($is_completely_fulfilled_size);
			    /*When click on confirm order and insufficient inventory then return view and tell to vender that click on split order...*/
			    if(isset($slug_chk_qty_available_or_not) && $slug_chk_qty_available_or_not!=null)
			    {
			    	if($is_completely_fulfilled == false)
			    	{
			    		$response['status']      = 'split_warning';
			        	$response['description'] = 'Sorry, Product inventory is insufficient, for order processing please fulfill the product inventory.';
			        	return $response;	
			    	}
			    	if($is_completely_fulfilled_size == false)
			    	{
			    		$response['status']      = 'split_warning';
			        	$response['description'] = 'Sorry, Product inventory is insufficient, for order processing please fulfill the product inventory.';
			        	return $response;	
			    	}
			    	else
			    	{
			    		$response['status']      = 'success';
			        	$response['description'] = 'Inventory available, you can procced.';
			        	return $response;		
			    	}
			    	
			    }



			    if($is_completely_fulfilled == true)
			    {  

			      	
		    			if($payment_term == 'Offline')
		    			{
		    				$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;
		    				
		    				$retailerId = isset($order_details['retailer_id'])?$order_details['retailer_id']:0;

    	    				$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';
    	    	
	        			 /* Update payment status in order table by Harshada on date 21 Oct 2020 */
		        	
	        			$order_status_update = $this->RetailerQuotesModel->where('id',$order_id)
	        													 ->where('maker_id',$order_details['maker_id'])

	        													 ->update(['maker_confirmation' => '1','is_direct_payment'=>$isDirectPayment]);



		        		/* send retailer notification*/

		        	 	$view_order_href=  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);


		        	 	$vendorStoreName = get_maker_company_name($order_details['maker_id']);
		        		$arr_notify_data                 = [];
					    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
					    $arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';

					    $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed from vendor : '.$vendorStoreName;

					    $arr_notify_data['title']        = 'Order Confirmed';
					    $arr_notify_data['type']         = 'retailer';  
					    $arr_notify_data['link']         = $view_order_href;  

	        			$this->GeneralService->save_notification($arr_notify_data);

					    $this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);
	        		
					    $this->InventoryService->batch_sku_update_quantity_size($arr_requested_qty_by_skno_size);

	        			/* get admin id */

	        			$admin_id  = get_admin_id();
	        			$view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);

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

	        			$this->GeneralService->save_notification($arr_notify_data);


				        		

						        		
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
										'credit_amount'   	=> num_format($order_details['total_wholesale_price']),
		                            'order_no'        	=> $order_details['order_no'],
		                            'PROJECT_NAME' 		=> $arr_site_setting['site_name'],					                            
		                        ];

		                    $arr_mail_data                      = [];
					        $arr_mail_data['email_template_id'] = '84';
					        $arr_mail_data['arr_built_content'] = $arr_built_content;
					        $arr_mail_data['arr_user']          = $arr_user;
								/*end*/



			        		/* send mail to vendor for order amount is credited on your acount */

			        		$email_status  = $this->EmailService->send_mail($arr_mail_data);

                      

                       		// $is_mail_sent = $this->EmailService->amount_credited_email(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$user_email,false);

							}					        		


			        		/* Send mail to retailer */
			        		$hide_vendor_address_from_retailer = 1;
			        		$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);
			        		
			        		$this->send_mail($retailer_mail,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$hide_vendor_address_from_retailer);
		    		

                            /*send the mail to admin*/
		                    
		                    $admin_email = 0;

		                    $admin_details = $this->UserModel->where('id',1)->first();

		                    if(isset($admin_details))
		                    {
		                       $admin_email = $admin_details->email;

		                    }

                            $this->send_mail($admin_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);



                          
                            /* send notification and mail to the vendor and admin
                             for insufficient inventory after confirm the order*/  
                             
                             
                            /* get all product list from order id*/
                            $product_inventory_arr = [];

                            $order_product_details = $this->RetailerQuotesProductModel
                                                        ->with(['product_details'])
                                                        ->where('retailer_quotes_id',$order_id)
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

                            		$size_inventory = $this->ProductSizesModel
                            						  ->where('product_id',$product['product_id'])
                            						  ->where('sku_no',$product['sku_no'])
                            						  ->where('size_id',$product['size_id'])
                            						  ->pluck('size_inventory')
                            						  ->first();

                            		// dd($size_inventory);		
                            		$size_name = $this->SizeModel
                            					 ->where('id',$product['size_id'])
                            					 ->pluck('size')
                            					 ->first();		  

                            		// dd($size_name);			 
                            		if($inventory <=200)
                            		{
                            			$product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

                            			$product_inventory_arr[$key]['sku_no']= $product['sku_no'];

                                        $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                            		}                 
                            	
                            		if($size_inventory <=200)
                            		{
                            			$product_size_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

                            			$product_size_inventory_arr[$key]['sku_no']= $product['sku_no'];

                            			$product_size_inventory_arr[$key]['size'] = $size_name;

                                        $product_size_inventory_arr[$key]['inventory_stock'] = $size_inventory;
                            		}

                            	}
                            }
                             
                           
                            if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                            {
                                 $key1 = '';
                                //send vendor notification
                                $admin_id  = get_admin_id();
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

			        			$this->GeneralService->save_notification($arr_notify_data);
	

                            }


                            if(isset($product_size_inventory_arr) && count($product_size_inventory_arr)>0)
                            {
                                 $key1 = '';
                                //send vendor notification
                                $admin_id  = get_admin_id();
				        		$arr_notify_data                 = [];
							    $arr_notify_data['from_user_id'] = $admin_id or '';

							    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
                                $html = '';
							    $html= "Following products having insufficient inventory: ";

							    foreach ($product_size_inventory_arr as $key => $value) {

                                if($key == 0)
                                {
                                   $key1 = 1;
                                }
                                else
                                {
                                  $key1 = $key+1;
                                }

							    $html.= '  '.$key1.') Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.' Size:('.$value['size'].')'.
							           ' and Available Stock : '.$value['inventory_stock'].'  ';
							    }

                                $arr_notify_data['description']  = $html;
							    $arr_notify_data['title']        = 'Insufficient Inventory';
							    $arr_notify_data['type']         = 'maker';  
							    $arr_notify_data['link']         = '';  

			        			$this->GeneralService->save_notification($arr_notify_data);
	

                            }

                            /*send email to the vendor*/
                            $maker_id = '';

                            $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

                            $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

                            $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'retailer_order'); 

	                        $email_status  = $this->EmailService->send_mail($arr_mail_data);
                          
                            /*---------------------------------------------------*/ 
     
						            $response['status']      = 'success';
						            $response['description'] = 'Order has been confirmed successfully.';
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
					
			    	/* Split Order */
			        // dd($arr_available_qty_by_skno,$arr_requested_qty_by_skno);

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

			    	
			    	/* Split the quantity of product*/

			    	
			    	foreach ($order_data as $key => $product_arr) 
			    	{
			    		$order_arr = $product_arr;
			
			    		if (sizeof($product_arr['quotes_details']) > 0) 
			    		{
			    			foreach ($product_arr['quotes_details'] as $key => $product) 
			    			{
			    				foreach ($arr_available_qty_by_skno as $availble_qty_key => $available_qty_by_skno) 
			    				{
			    					if ($product['sku_no'] == $availble_qty_key) 
			    					{
			    						if ($product['qty'] > $available_qty_by_skno) 
			    						{
			    							
			    							    /*
			    								#If product have maximum quantity than available quantity
													# split product 1) Fulfill the quantity requirement
												2) Send remaining quantity in partial order 	
			    							   */

												if($available_qty_by_skno == 0)
												{
													/*Product have 0 stock */

													$remainig_product_qty = $product['qty'] - $available_qty_by_skno;
					    							$total_price = $remainig_product_qty * $product['unit_wholsale_price'];

					    							
					    							if(count($remainig_product_qty) <= 0)
													{
														$remainig_product_qty = 0;
													}

					    							$order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';

					    							$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

					    							$order_arr['partial'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


					    							$order_arr['partial'][$key]['sku_no'] = $product['sku_no'] or '';
					    							$order_arr['partial'][$key]['qty'] 	= $remainig_product_qty or '';
					    							$order_arr['partial'][$key]['unit_price'] 	= $product['unit_wholsale_price'];
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
												}
												else
												{

													$remainig_product_qty = $product['qty'] - $available_qty_by_skno;

													$total_price = $remainig_product_qty * $product['unit_wholsale_price'];


					    							if(count($remainig_product_qty) <= 0)
													{
														$remainig_product_qty = 0;
													}
													
													$order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';

													$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

													$order_arr['partial'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


													$order_arr['partial'][$key]['sku_no'] = $product['sku_no'] or '';
													$order_arr['partial'][$key]['qty'] 	= $remainig_product_qty or '';
													$order_arr['partial'][$key]['unit_price'] 	= $product['unit_wholsale_price'];
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
														
														$order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;
													}

													/*$order_arr['partial'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
													$order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;*/

													$order_arr['partial'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0.00;		



													
													$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';

													$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

													$order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


													$order_arr['fulfill'][$key]['sku_no'] 		= $product['sku_no'] or '';
													$order_arr['fulfill'][$key]['qty'] 		= $available_qty_by_skno or '';
													$order_arr['fulfill'][$key]['unit_price'] 	= $product['unit_wholsale_price'];


													$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);


													$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0.00;

													$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;

													$order_arr['fulfill'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0.00;

													$arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);

													// $total_price = $available_qty_by_skno * $product['unit_wholsale_price'];

													$total_price = $available_qty_by_skno * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['product_discount']-$order_arr['fulfill'][$key]['shipping_discount'];

												
													$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;
												}
				    					}				    					
				    					else
				    					{




				    						/***********************************************************/
				    						$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';

				    						$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

											$order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


											$order_arr['fulfill'][$key]['sku_no'] 		= $product['sku_no'] or '';
											//$order_arr['fulfill'][$key]['qty'] 		= $available_qty_by_skno or '';

											$order_arr['fulfill'][$key]['qty'] 		    = $product['qty'] or '';
											$order_arr['fulfill'][$key]['unit_price'] 	= $product['unit_wholsale_price'];


											$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

											$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;

											$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;

											$order_arr['fulfill'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0;

											$arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);

											// $total_price = $available_qty_by_skno * $product['unit_wholsale_price'];

											$total_price = $available_qty_by_skno * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['product_discount']- $order_arr['fulfill'][$key]['shipping_discount'];

											
											$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;

											/******************************* End ***********************************************/







			    							$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

			    							$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;

			    							$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;

			    							$order_arr['fulfill'][$key]['product_discount'] = (isset($product_discount['product_discount']))?$product_discount['product_discount']:0;
			    							/*
			    								If product have full quantity
												*/
			    							/*$total_price = $product['qty'] * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['shipping_discount']-$order_arr['fulfill'][$key]['product_discount'];	*/

			    							$total_price = $product['qty'] * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['shipping_discount'] -$order_arr['fulfill'][$key]['product_discount'];

			    							
			    							//$total_price = $product['qty'] * $product['unit_wholsale_price'];	

				    						
			    							$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';

			    							$sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

			    							$order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';

			    							$order_arr['fulfill'][$key]['sku_no']   = $product['sku_no'] or '';
			    							$order_arr['fulfill'][$key]['qty'] 		= $product['qty'] or '';

			    							$order_arr['fulfill'][$key]['unit_price']  = $product['unit_wholsale_price'];

			    							$order_arr['fulfill'][$key]['total_price'] = isset($total_price)?$total_price:0;
				    					}	
			    					}			    					
			    				}
			    			}
			    		}	
			    	}



			    	/*Store split order record */

			    	$fulfill_final_total_arr = $partial_final_total_arr = [];

			    	$promotion_free_shipping = $get_discount_amt = 0;

			    	if($order_arr) {

			    		/* get parent order payment type */
			    		$ordPaymentMethod = $this->RetailerQuotesModel->where('id',$order_details['id'])
			    		                                              ->first(['is_direct_payment','admin_commission']);
			    		

			    		/*Update Order status to split order*/

			    		$this->RetailerQuotesModel->where('id',$order_details['id'])->update(['is_split_order'=>'1']);



			    		//get parent order details
                        $retailer_order_arr = $promotion_arr = [];

			    		$order_details_obj = $this->RetailerQuotesModel->with(['quotes_details'])->where('id',$order_details['id'])->first();

			    		if(isset($order_details_obj))
			    		{
                           $retailer_order_arr = $order_details_obj->toArray();
			    		}




			    		/*Store fulfill order record*/

			    		if (isset($order_arr['fulfill']) && sizeof($order_arr['fulfill']) > 0)
			    		{
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


		    				
			    			$total_retail_price = $total_retail_price+$pro_ship_charge-$pro_dis;

		    				$arr_order['order_no'] = $fulfill_order_no;
		    				$arr_order['is_direct_payment'] = isset($ordPaymentMethod->is_direct_payment)?$ordPaymentMethod->is_direct_payment:0;
		    				$arr_order['maker_id'] = $order_arr['maker_id'];
		    				$arr_order['retailer_id'] = $order_arr['retailer_id'];
		    				$arr_order['transaction_id'] = $order_arr['transaction_id'];
		    				$arr_order['total_retail_price'] = $total_retail_price;
		    				$arr_order['total_wholesale_price'] = $total_wholesale_price;
		    				$arr_order['shipping_addr'] = $order_arr['shipping_addr'];
		    				$arr_order['shipping_addr_zip_code'] = $order_arr['shipping_addr_zip_code'];
		    				$arr_order['billing_addr'] = $order_arr['billing_addr'];
		    				$arr_order['billing_addr_zip_code'] = $order_arr['billing_addr_zip_code'];
		    				$arr_order['payment_term'] = $order_arr['payment_term'];
		    				$arr_order['split_order_id'] = $order_arr['id'];
		    				
		    				$arr_order['ship_contact_no'] = $order_arr['user_details']['retailer_details']['ship_contact_no'];
		    				$arr_order['bill_contact_no'] = $order_arr['user_details']['retailer_details']['bill_contact_no'];

			        		// $arr_order['admin_commission']      = $this->CommissionService->get_admin_commission($order_arr['maker_id']);

			        		$arr_order['admin_commission']    = isset($ordPaymentMethod['admin_commission'])?$ordPaymentMethod['admin_commission']:$this->CommissionService->get_admin_commission($order_arr['maker_id']);



		    				
		    				$create_quotes = $this->RetailerQuotesModel->create($arr_order);

		    				foreach ($order_arr['fulfill'] as $key => $product) {

			        		$quote_product_arr = [];
			        		

			        		$quote_product_arr['retailer_quotes_id'] = $create_quotes->id;
			        		$quote_product_arr['product_id']         = $product['product_id'];
			        		$quote_product_arr['sku_no']             = $product['sku_no'];
			        		$quote_product_arr['qty']                = $product['qty'];
			        		$quote_product_arr['retail_price']      = $product['unit_price'];
			        		$quote_product_arr['unit_retail_price'] = $product['unit_price'];
			        		$quote_product_arr['unit_wholsale_price'] = $product['unit_price'];
			        		$quote_product_arr['wholesale_price']       = $product['qty']*$product['unit_price'];
			        		$quote_product_arr['description']  		 = '';


			        		$quote_product_arr['shipping_discount']  = $product['shipping_discount'];


			        		//$quote_product_arr['shipping_charge']    = $product['shipping_charges']+$quote_product_arr['shipping_discount'];

			        		$quote_product_arr['shipping_charge']   = $product['shipping_charges'];

			        		$quote_product_arr['product_discount']  = $product['product_discount'];
			        		
			        		$grand_total = $product['total_price'] + $product['shipping_charges']-$quote_product_arr['shipping_discount']-$product['product_discount']; 

			        		$quote_product_arr['wholesale_price']    = $grand_total;

			        		$create_quote_product = $this->RetailerQuotesProductModel->create($quote_product_arr);
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

			    			$total_wholesale_price = $total_wholesale_price+$pro_ship_charge-$pro_dis-$pro_ship_dis;
			    			
		    				$arr_order['order_no'] = $partial_order_no;
		    				$arr_order['is_direct_payment'] = isset($ordPaymentMethod->is_direct_payment)?$ordPaymentMethod->is_direct_payment:0;
		    				$arr_order['maker_id'] = $order_arr['maker_id'];
		    				$arr_order['retailer_id'] = $order_arr['retailer_id'];
		    				$arr_order['transaction_id'] = $order_arr['transaction_id'];
		    				$arr_order['total_retail_price'] = $total_wholesale_price;
		    				$arr_order['total_wholesale_price'] = $total_wholesale_price;
		    				$arr_order['shipping_addr'] = $order_arr['shipping_addr'];
		    				$arr_order['shipping_addr_zip_code'] = $order_arr['shipping_addr_zip_code'];
		    				$arr_order['billing_addr'] = $order_arr['billing_addr'];
		    				$arr_order['billing_addr_zip_code'] = $order_arr['billing_addr_zip_code'];
		    				$arr_order['payment_term'] = $order_arr['payment_term'];
		    				$arr_order['split_order_id'] = $order_arr['id'];

		    				$arr_order['ship_contact_no'] = $order_arr['user_details']['retailer_details']['ship_contact_no'];
		    				$arr_order['bill_contact_no'] = $order_arr['user_details']['retailer_details']['bill_contact_no'];

			        		// $arr_order['admin_commission']      = $this->CommissionService->get_admin_commission($order_arr['maker_id']);   				
			        		$arr_order['admin_commission']      = isset($ordPaymentMethod['admin_commission'])?$ordPaymentMethod['admin_commission']:$this->CommissionService->get_admin_commission($order_arr['maker_id']);  






		    				$create_quote = $this->RetailerQuotesModel->create($arr_order);

		    				foreach ($order_arr['partial'] as $key => $product) {
		    				
			        		$quote_product_arr = [];
			        		

			        		$quote_product_arr['retailer_quotes_id'] = $create_quote->id;
			        		$quote_product_arr['product_id']         = $product['product_id'];
			        		$quote_product_arr['sku_no']             = $product['sku_no'];
			        		$quote_product_arr['qty']                = $product['qty'];
			        		$quote_product_arr['retail_price']       = $product['unit_price'];
			        		$quote_product_arr['unit_wholsale_price']= $product['unit_price'];
			        		$quote_product_arr['wholesale_price']    = $product['qty']*$product['unit_price'];
			        		$quote_product_arr['description']  		   = '';


			        		/*$quote_product_arr['shipping_charge']    = $product['shipping_charges']+$product['shipping_discount'];
*/
			        		$quote_product_arr['shipping_charge'] = $product['shipping_charges'];

			        		$quote_product_arr['shipping_discount']  = $product['shipping_discount'];

			        		$quote_product_arr['product_discount']  = $product['product_discount'];
			        		
			        		$grand_total = $product['total_price'] + $product['shipping_charges']-$quote_product_arr['shipping_discount']-$product['product_discount']; 
			        	
			        		$quote_product_arr['wholesale_price']    = $grand_total;

  		 	        		
				        	
			        		$create_quote_product = $this->RetailerQuotesProductModel->create($quote_product_arr);
		    				}

		    				if(empty($order_arr['fulfill']))
		    				{
		    					$main_order_status_update = $this->RetailerQuotesModel->where('id',$order_arr['id'])
		    														 ->where('maker_id',$order_arr['maker_id'])

		    														 ->update(['maker_confirmation' => '1']);
		    				}
		    			}

			        /*send the mail to retailer*/

                    $retailer_email_id = $this->HelperService->get_user_mail($order_arr['retailer_id']);
                      
                    $email_status      = $this->send_split_order_mail($order_arr,$retailer_email_id,$partial_order_no,$fulfill_order_no);

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

                    $order_product_details = $this->RetailerQuotesProductModel
                                                ->with(['product_details'])
                                                ->where('retailer_quotes_id',$order_id)
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
                        $admin_id  = get_admin_id();
                       
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

	        			$this->GeneralService->save_notification($arr_notify_data);
                    }

                    /*send email to the vendor*/
                    $maker_id = '';

                    $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

                    $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

                    $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'retailer_order'); 

                    $email_status  = $this->EmailService->send_mail($arr_mail_data);
                      

                   /*------------------------------------------------------*/



			        $response['status']      = 'success';
			        $response['description'] = 'Order is split because of inadequate inventory to fulfill.';
			        return $response;
			    		
			    	}
			    	$response['status']      = 'warning';
			        $response['description'] = 'Something went wrong, please try again.';
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
    	$total_wholesale_price = 0;
    	$obj_product = $this->ProductsModel->where('id',$product_id)->first();
    	if ($obj_product) {
    		
    		$arr_product_details = $obj_product->toArray();
    	}
    	
    	$total_price = $quantity * $arr_product_details['unit_wholsale_price'];

    	$total_wholesale_price = isset($total_price)?$total_price:0;


    	if($arr_product_details['shipping_type']==2) 
    	{
    	    if($total_wholesale_price>=$arr_product_details['minimum_amount_off'])
            {
               
                $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

                if(is_numeric($shipping_charges) && is_numeric($arr_product_details['off_type_amount']))
                {

                    $discount_amount =  $shipping_charges * $arr_product_details['off_type_amount']/ 100;

                    //$shipping_charges = $shipping_charges-$discount_amount;

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
            if($total_wholesale_price<$arr_product_details['minimum_amount_off'])
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
            if($total_wholesale_price>=$arr_product_details['minimum_amount_off'])
            {
        	
        		
              $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

             // $shipping_charges = $shipping_charges;

              $arr_discount['shipping_discount'] = $arr_product_details['off_type_amount'];

             // $arr_discount['shipping_charges'] = $shipping_charges-$arr_discount['shipping_discount'];

              $arr_discount['shipping_charges'] = $shipping_charges;

            }
            elseif($total_wholesale_price<$arr_product_details['minimum_amount_off'])
            {
        	

             	$shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

             	$arr_discount['shipping_charges']  = $shipping_charges;

             	$arr_discount['shipping_discount'] = 0;
            }
          

        }
        if($arr_product_details['prodduct_dis_type']==1)
		{ 

		    if($total_wholesale_price>=$arr_product_details['product_dis_min_amt'])
            {
                $pro_discount =  $total_wholesale_price * $arr_product_details['product_discount']/ 100;
		       	$arr_discount['product_discount'] = isset($pro_discount)?$pro_discount:0;

            }
            else
            {                                 
                $arr_discount['product_discount'] = 0;
            }

		}    
	    if($arr_product_details['prodduct_dis_type']==2)
	    {
	    	if($total_wholesale_price>=$arr_product_details['product_dis_min_amt'])
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

    public function send_mail($to_mail_id=false,$arr_product,$order_no,$user_id,$charge_status=false,$loggedInUserId,$order_id=false,$isDirectPayment=false,$hide_vendor_address_from_retailer = false)
    {
    	
    	$temp_data = isset($arr_product['leads_details'])?$arr_product['leads_details']:$arr_product;

    	/* Build array of mail data */
			foreach($temp_data as $key => $product)
			{
				$mail_data[$user_id][$key]['unit_price']       = isset($product['unit_wholsale_price'])?$product['unit_wholsale_price']:0.00;

				
				$mail_data[$user_id][$key]['product_id']       = isset($product['product_id'])?$product['product_id']:0;

				$mail_data[$user_id][$key]['item_qty']         = isset($product['qty'])?$product['qty']:0.00;

				$mail_data[$user_id][$key]['color']         = isset($product['color'])?$product['color']:'';

				$mail_data[$user_id][$key]['size_id']         = isset($product['size_id'])?$product['size_id']:'';

				$mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

				$prod_whole_price = $mail_data[$user_id][$key]['unit_price'] * $mail_data[$user_id][$key]['item_qty'];

				$mail_data[$user_id][$key]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:$product['wholesale_price'];

				$mail_data[$user_id][$key]['wholesale_price']  = isset($prod_whole_price)?$prod_whole_price:0.00;

				
				$prod_ship_charge = isset($product['shipping_charge'])?$product['shipping_charge']:0.00;

				$prod_ship_disc = isset($product['shipping_discount'])?$product['shipping_discount']:0.00;

				$mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

				$mail_data[$user_id][$key]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:$prod_ship_charge;

				$mail_data[$user_id][$key]['shipping_discount'] = isset($product['shipping_charges_discount'])?$product['shipping_charges_discount']:$prod_ship_disc;

				$mail_data[$user_id][$key]['sku_no'] = isset($product['sku_no'])?$product['sku_no']:$product['sku'];
			}
		
			/* sending mail */

			if($charge_status == 'succeeded')
			{
				$charge_status = 'Paid';
			}
			
			$email_status = $this->GeneralService->send_mail($to_mail_id,$mail_data,$order_no,$charge_status,$loggedInUserId,$order_id,$isDirectPayment,$hide_vendor_address_from_retailer);
			
		return $email_status;	    		
	}

    public function payment_process($order_arr)
    {
    	$loggedInUserId = 0;
        $user = \Sentinel::check();

        if($user && $user->inRole('retailer'))
        {
            $loggedInUserId = $user->id;
        }
        else
        {
        	$loggedInUserId = $user->id;
        }
    	$ret_order_details = array();
    	$order_details = $this->RetailerQuotesModel->where('order_no',$order_arr['order_no'])
	    									       ->with(['transaction_details.strip_key_details','quotes_details','quotes_details.product_details','maker_data.stripe_account_details'])
	    								           ->first();



		if($order_details)
		{
			$order_details = $order_details->toArray();


			$ret_order_details = $order_details;
			
		    // $isDirectPayment = isset($order_details['maker_data']['is_direct_payment'])?$order_details['maker_data']['is_direct_payment']:0;

		    $isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;
		}

		/*this else for if order not present in retailer quotes model means this order placed by representative.*/
		else
		{
			$order_details = $this->RepresentativeLeadsModel->where('order_no',$order_arr['order_no'])
			                                               ->with(['transaction_details.strip_key_details','leads_details','leads_details.product_details','maker_details.stripe_account_details'])
			                                               ->first();

			if($order_details)
			{
			  $order_details = $order_details->toArray();
			  
			  // $isDirectPayment = isset($order_details['maker_details']['is_direct_payment'])?$order_details['maker_details']['is_direct_payment']:0;

			  $isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;
			}
			
		}

		$ord_no = isset($order_details['order_no'])?$order_details['order_no']:'';
	    								 
    	$arr_charge_data['order_amount'] = isset($order_details['total_wholesale_price'])?$order_details['total_wholesale_price']:0;

    	$arr_charge_data['stripe_key'] = isset($order_details['transaction_details']['strip_key_details']['secret_key'])?$order_details['transaction_details']['strip_key_details']['secret_key']:false;

		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

		$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

		$paymentStripeKeyId = isset($order_details['transaction_details']['strip_key_details']['id'])?$order_details['transaction_details']['strip_key_details']['id']:false;

		$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';


      	if(isset($arr_charge_data) && count($arr_charge_data) > 0 )
		{

			if($order_details['payment_term'] == 'Online/Credit')
			{

				// Payment duducted
				DB::beginTransaction();
				try{

		    				
    				$retailerId = isset($order_details['retailer_id'])?$order_details['retailer_id']:0;

    				$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';
    				
    				$cardId = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

    				$arrData = [];

    				$arrData['stripe_key']   = $arr_charge_data['stripe_key'];
    				$arrData['customerId']   = $customerId;
    				$arrData['retailerId']   = $retailerId;
    				$arrData['vendorId']     = $loggedInUserId;
    				$arrData['order_amount'] = $arr_charge_data['order_amount'];
    				$arrData['cardId']       = $cardId;
    				$arrData['order_no']     = isset($ord_no)?$ord_no:'';

    				if($isDirectPayment == 1)
    				{
    					$stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($loggedInUserId);

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
		        			   /* Update payment status in order table by Harshada on date 21 Oct 2020 */

					        	if($ret_order_details)
								{
									$this->RetailerQuotesModel
										   ->where('id',$order_details['id'])
										   ->where('order_no',$order_details['order_no'])	
										   ->update([
										   	          'is_payment_status' => '1',
										   	          'stripe_key_id' => $stripKeyId
										   	        ]);
								} else {
									$this->RepresentativeLeadsModel
										   ->where('id',$order_details['id'])
										   ->where('order_no',$order_details['order_no'])	
										   ->update([
										   	         'is_payment_status' => '1',
										   	         'stripe_key_id' => $stripKeyId
										   	       ]);
								}

			        			if (isset($order_details['quotes_details']))
			        			{
			        				
			        				$order_status_update = $this->RetailerQuotesModel->where('order_no',$order_arr['order_no'])
			        													 ->where('maker_id',$order_details['maker_id'])

			        													 ->update(['maker_confirmation' => '1','is_direct_payment'=>$isDirectPayment]);
			        													 

			        				$main_order_status_update = $this->RetailerQuotesModel->where('id',$order_details['split_order_id'])
			        													 ->where('maker_id',$order_details['maker_id'])

			        													 ->update(['maker_confirmation' => '1']);
                                     
                                    $view_href = url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
			                         													 
			        			}
                                elseif (isset($order_details['leads_details']))
			        			{
			        				
			        				$order_status_update = $this->RepresentativeLeadsModel->where('order_no',$order_arr['order_no'])
			        											->where('maker_id',$order_details['maker_id'])

			        											->update(['maker_confirmation' => '1','is_direct_payment'=>$isDirectPayment]);
			        													 

			        				$main_order_status_update = $this->RepresentativeLeadsModel->where('id',$order_details['split_order_id'])
			        													 ->where('maker_id',$order_details['maker_id'])

			        													 ->update(['maker_confirmation' => '1']);


			                        $view_href = url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);													 
			        			}
			        				

			        		    /* send retailer notification*/

			        		    $arr_notify_data                 = [];
						    	$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
						    	$arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';

						    	$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';

						    	$arr_notify_data['title']        = 'Order Confirmed';
						    	$arr_notify_data['type']         = 'retailer';  
						    	$arr_notify_data['link']         = $view_href;  

			        			$this->GeneralService->save_notification($arr_notify_data);



			        			/* get admin id */

			        			$admin_id  = get_admin_id();

			        			if (isset($order_details['quotes_details']))
			        			{
                                   $view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
			        			}
			        			elseif(isset($order_details['leads_details']))
			        			{
			        				$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);
			        			}
			        			

			        			$maker_details = \Sentinel::findById($order_details['maker_id']);

				        		/* send admin notification*/
				        		$arr_notify_data                 = [];
							    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
							    $arr_notify_data['to_user_id']   = $admin_id or '';

							    $arr_notify_data['description']  = 'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

								$arr_notify_data['title']        = 'Orders Confirmed';
								$arr_notify_data['type']         = 'admin'; 
								$arr_notify_data['link']         = $view_href; 
			        			
			        			$this->GeneralService->save_notification($arr_notify_data);


			        			/* send vendor notification*/

			        			if (isset($order_details['quotes_details']))
			        			{
                                   $view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);
			        			}
			        			elseif(isset($order_details['leads_details']))
			        			{
			        				$view_href =  url('/').'/vendor/representative_orders/view/'.base64_encode($order_details['order_no']);
			        			}
        			
				        		$arr_notify_data                 = [];
							    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
							    $arr_notify_data['from_user_id'] = $admin_id or '';

							    $arr_notify_data['description']  = 
							    'Order('.$order_details['order_no'].') is confirmed and payment went through successfully.';

							    $arr_notify_data['title']        = 'Order Confirmed';
							    $arr_notify_data['type']         = 'maker';  
							    $arr_notify_data['link']         = $view_href;  

			        			$this->GeneralService->save_notification($arr_notify_data);


			        			/* send amount credit notification to vendor if payment type id direct payment */

					        	/* send vendor notification*/
			        			if($isDirectPayment == 1)
	    				        {
	    				        	//here send amount credited notification to the vendor
				        			$view_href = '';

				        			/*$view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);*/

					        		if (isset($order_details['quotes_details']))
				        			{
	                                   $view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);
				        			}
				        			elseif(isset($order_details['leads_details']))
				        			{
				        				$view_href =  url('/').'/vendor/representative_orders/view/'.base64_encode($order_details['order_no']);
				        			}

					        		$arr_notify_data                 = [];
								    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
								    $arr_notify_data['from_user_id'] = $admin_id or '';

								    /*$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';*/

								    $arr_notify_data['title']        = 'Order Confirmed';
								    $arr_notify_data['type']         = 'maker';  
								    $arr_notify_data['link']         = $view_href;  

				        			$this->GeneralService->save_notification($arr_notify_data);
					        		
					        		/* get user mail details */
									$user_details = \Sentinel::findById($order_details['maker_id']);

									$user_email = isset($user_details->email)?$user_details->email:false;

				        		    /* send mail to vendor for order amount is credited on your acount */

				        		    $credentials = ['email' => $user_email];
	                      			$arr_user = get_user_by_credentials($credentials);

                                	$arr_mail_data = $this->set_amount_credited_email(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

                                	$is_mail_sent  = $this->EmailService->send_mail($arr_mail_data);

		                          


		                           //here send retailer amount deducted notification to the admin

		                           $admin_id  = get_admin_id();
                                   $view_href = '';
					        		/*$view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);*/

					        		if(isset($order_details['quotes_details']))
				        			{
	                                   $view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
				        			}
				        			elseif(isset($order_details['leads_details']))
				        			{
				        				$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);
				        			}


					        		$arr_notify_data                 = [];
								    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
							        $arr_notify_data['to_user_id']   = $admin_id or '';

								    $arr_notify_data['description']  = 'order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from retailer account.';

								    $arr_notify_data['title']        = 'Order Confirmed';
								    $arr_notify_data['type']         = 'admin';  
								    $arr_notify_data['link']         = $view_href;  

				        			$this->GeneralService->save_notification($arr_notify_data);

				        		}
				        		else
				        		{
				        			$admin_id  = get_admin_id();
				        			$view_href = '';
					        		/*$view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);*/

					        		if(isset($order_details['quotes_details']))
				        			{
	                                   $view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
				        			}
				        			elseif(isset($order_details['leads_details']))
				        			{
				        				$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);
				        			}

					        		$arr_notify_data                 = [];
								    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
							        $arr_notify_data['to_user_id']   = $admin_id or '';

								    $arr_notify_data['description']  = 'order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';

								    $arr_notify_data['title']        = 'Order Confirmed';
								    $arr_notify_data['type']         = 'admin';  
								    $arr_notify_data['link']         = $view_href;  

				        			$this->GeneralService->save_notification($arr_notify_data);
				        		}


	                        if (isset($order_details['quotes_details']))
	                        {

	                            /* Send mail to retailer */

				        		$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);
				        		
				        		$this->send_mail($retailer_mail,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);
			    		
	                            /*send the mail to admin*/
			                    
			                    $admin_email = 0;

			                    $admin_details = $this->UserModel->where('id',1)->first();

			                    if(isset($admin_details))
			                    {
			                       $admin_email = $admin_details->email;
	                            }

	                            $this->send_mail($admin_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);

	                        }
	                        
	                        if(isset($order_details['leads_details']))
	                        {
	                           
	                            /* Send mail to retailer */

				        		$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);
				        		
				        		$this->send_mail($retailer_mail,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);
			    		
	                            /*send the mail to admin*/
			                    
			                    $admin_email = 0;

			                    $admin_details = $this->UserModel->where('id',1)->first();

			                    if(isset($admin_details))
			                    {
			                       $admin_email = $admin_details->email;
	                            }

	                            $this->send_mail($admin_email,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);

	                        }

	                       
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
				
				if (isset($order_details['quotes_details']))
				{

                    $order_status_update = $this->RetailerQuotesModel
                                                ->where('id',$order_details['order_no'])
        										->where('maker_id',$order_details['maker_id'])
                                                ->update(['maker_confirmation' => '1']);
			    }
			    elseif (isset($order_details['leads_details']))
			    {
			    	$order_status_update = $this->RepresentativeLeadsModel
                                                ->where('id',$order_details['order_no'])
        										->where('maker_id',$order_details['maker_id'])
                                                ->update(['maker_confirmation' => '1']);
			    }	
			        				

         		/*$view_href =  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);


        		$arr_notify_data                 = [];

		    	$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
		    	$arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';
		    	$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed successfully.';

		    	$arr_notify_data['title']        = 'Order Confirmed';
		    	$arr_notify_data['type']         = 'retailer';  
		    	$arr_notify_data['link']         = $view_href;  

				
				$this->GeneralService->save_notification($arr_notify_data);*/


				/* send retailer notification*/

				if(isset($order_details['quotes_details']))
				{
				   $view_href =  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
				}
				elseif(isset($order_details['leads_details']))
				{
					$view_href = url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);	
				}
				
        		$arr_notify_data                 = [];
			    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
			    $arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';

			    $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account once user generate the Net30 payment.';

			    $arr_notify_data['title']        = 'Order Confirmed';

			    $arr_notify_data['type']         = 'retailer';  
			    $arr_notify_data['link']         = $view_href;  

        		
        		$this->GeneralService->save_notification($arr_notify_data);



        		/* get admin id */

    			$admin_id                        = get_admin_id();

    			/*$view_href                       = url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);*/

    			if(isset($order_details['quotes_details']))
    			{
                   $view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
    			}
    			elseif(isset($order_details['leads_details']))
    			{
    				$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);
    			}

    			$maker_details = \Sentinel::findById($order_details['maker_id']);

        		/* send admin notification*/
        		$arr_notify_data                     = [];
			    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
			    $arr_notify_data['to_user_id']   = $admin_id or '';

			    $arr_notify_data['description']  = 'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

				$arr_notify_data['title']        = 'Orders Confirmed';
				$arr_notify_data['type']         = 'admin';  
				$arr_notify_data['link']         = $view_href;  

    			$this->GeneralService->save_notification($arr_notify_data);


			    /* send vendor notification*/
    			//$view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);
                 
                if (isset($order_details['quotes_details']))
    			{
                   $view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);
    			}
    			elseif(isset($order_details['leads_details']))
    			{
    				$view_href =  url('/').'/vendor/representative_orders/view/'.base64_encode($order_details['order_no']);
    			}

        		$arr_notify_data                 = [];
			    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
			    $arr_notify_data['from_user_id'] = $admin_id or '';

			    $arr_notify_data['description']  = 
			    'Order('.$order_details['order_no'].') is confirmed and payment went through successfully : ';

			    $arr_notify_data['title']        = 'Order Confirmed';
			    $arr_notify_data['type']         = 'maker';  
			    $arr_notify_data['link']         = $view_href;  

    			$this->GeneralService->save_notification($arr_notify_data);


    			/* Send mail to retailer */

		        $retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

		        $credentials = ['email' => $retailer_mail];
          		$arr_user = get_user_by_credentials($credentials);

                $arr_mail_data = $this->set_amount_deducted_email(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,'Net30');

             	$this->EmailService->send_mail($arr_mail_data);
		    	
        		
                if($isDirectPayment == 1)
                {
                	//get vendor mail id 
                	$vendor_mail = $this->HelperService->get_user_mail($order_details['maker_id']);

                	$credentials = ['email' => $vendor_mail];
          			$arr_user = get_user_by_credentials($credentials);

                	$arr_mail_data = $this->set_amount_credited_email(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,'Net30');

                	$is_mail_sent  = $this->EmailService->send_mail($arr_mail_data);


                   
                }
                else
                {
                    $admin_id    = get_admin_id();
                    $admin_email = '';

                    $admin_details = $this->UserModel->where('id',$admin_id)->first();

                    if(isset($admin_details))
                    {
                       $admin_email = $admin_details->email;
                    }

                    $credentials = ['email' => $admin_email];
          			$arr_user = get_user_by_credentials($credentials);

                	$arr_mail_data = $this->set_amount_credited_email(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,'Net30');

                	$is_mail_sent  = $this->EmailService->send_mail($arr_mail_data);
                   
                }

	        	$response['status']      = 'success';
                $response['description'] = 'Order has been confirmed.';
	        	return $response;

			}
			elseif($order_details['payment_term'] == 'Net30 - Online/Credit')
			{
				
	            if(isset($order_details['quotes_details']))
				{
                   
                    $order_status_update = $this->RetailerQuotesModel
                                                ->where('order_no',$order_details['order_no'])
        										->where('maker_id',$order_details['maker_id'])
                                                ->update(['maker_confirmation' => 1]);
			    }
			    elseif (isset($order_details['leads_details']))
			    {
			    	
			    	$order_status_update = $this->RepresentativeLeadsModel
                                                ->where('order_no',$order_details['order_no'])
        										->where('maker_id',$order_details['maker_id'])
                                                ->update(['maker_confirmation' => 1]);
			    }   
			    										            
              
                /* send retailer notification*/

				if(isset($order_details['quotes_details']))
				{
				   $view_href =  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
				}
				elseif(isset($order_details['leads_details']))
				{
					$view_href = url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);	
				}
				
        		$arr_notify_data                 = [];
			    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
			    $arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';

			    $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') has been confirmed successfully.';

			    $arr_notify_data['title']        = 'Order Confirmed';

			    $arr_notify_data['type']         = 'retailer';  
			    $arr_notify_data['link']         = $view_href;  

        		
        		$this->GeneralService->save_notification($arr_notify_data);



        		/* get admin id */
        		/* send admin notification*/

    			$admin_id  = get_admin_id();

    			if(isset($order_details['quotes_details']))
    			{
                   $view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
    			}
    			elseif(isset($order_details['leads_details']))
    			{
    				$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);
    			}


    			$maker_details = \Sentinel::findById($order_details['maker_id']);
   		
        		$arr_notify_data                 = [];
			    $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
			    $arr_notify_data['to_user_id']   = $admin_id or '';

			    $arr_notify_data['description']  = 'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

				$arr_notify_data['title']        = 'Orders Confirmed';
				$arr_notify_data['type']         = 'admin';  
				$arr_notify_data['link']         = $view_href;  

    			$this->GeneralService->save_notification($arr_notify_data);



                /*--------------------- get maker name--------------------------*/

    			$maker_details = \Sentinel::findById($order_details['maker_id']);

    			$makerName =$maker_details->first_name.' '.$maker_details->last_name;

    			/*------------ get retailer name --------------------------------*/

    			$retailer_details = \Sentinel::findById($order_details['retailer_id']);

    			$retailerEmail = isset($retailer_details->email)?$retailer_details->email:'';


		        /*Get site setting data from helper*/

                $arr_site_setting = get_site_settings(['site_name','website_url']);


    			/* send mail to retailer */

                $credentials = ['email' => $retailerEmail];

                $arr_user = get_user_by_credentials($credentials);


                $arr_built_content = [
                                        'order_no'     => $order_details['order_no'],
                                        'vendor_name'  => $makerName,
                                        'PROJECT_NAME' => $arr_site_setting['site_name']
                                    ];  


                $arr_mail_data                         = [];
                $arr_mail_data['email_template_id']    = '58';
                $arr_mail_data['arr_built_content']    = $arr_built_content;
                $arr_mail_data['arr_user']             = $arr_user;


                $email_status  = $this->EmailService->send_mail($arr_mail_data);   


    			

                /*send the mail to admin*/
                $admin_id  = get_admin_id();

                $admin_details = \Sentinel::findById($admin_id);

    			$adminEmail = isset($admin_details->email)?$admin_details->email:'';


    			$credentials = ['email' => $adminEmail];

                $arr_user = get_user_by_credentials($credentials);


                $arr_built_content = [
                                        'order_no'     => $order_details['order_no'],
                                        'vendor_name'  => $makerName,
                                        'PROJECT_NAME' => $arr_site_setting['site_name']
                                    ];  


                $arr_mail_data                         = [];
                $arr_mail_data['email_template_id']    = '58';
                $arr_mail_data['arr_built_content']    = $arr_built_content;
                $arr_mail_data['arr_user']             = $arr_user;


                $email_status  = $this->EmailService->send_mail($arr_mail_data); 

    		

                $response['status']      = 'success';
                $response['description'] = 'Order has been confirmed successfully.';
	        	return $response;

			}
            else
        	{
        		$response['status']      = 'warning';
	        	$response['description'] = 'Sorry,payment has been failed please try again.';
	        	return $response;
        	}
	        	
        }
    }


    public function representative_order_payment($order_id,$slug_chk_inventory=null)
    { 


        $order_id     = isset($order_id)?base64_decode($order_id):'';
    	
    	$payment_term = $this->RepresentativeLeadsModel->where('id',$order_id)->pluck('payment_term')->first();
      
    	if($order_id)
    	{
    		$loggedInUserId = 0;
	        $user = \Sentinel::check();

	        if($user)
	        {
	            $loggedInUserId = $user->id;
	        }
      
	    	$order_details = $out_of_stock_details = [];

	    	
	    	$order_details = $this->RepresentativeLeadsModel->where('id',$order_id)
	    									->with(['transaction_details.strip_key_details','leads_details.product_details'])
	    								   ->where('id',$order_id)->first();
            
			$arr_data = $arr_charge_data = [];
	    	    	
	    	if($order_details)
	    	{ 
	    		$order_details = $order_details->toArray();

	       		
	    		/*  check Product Availability  */
	    		$ord_no = isset($order_details['order_no'])?$order_details['order_no']:false;


	    		$order_data = $this->orderDataService->get_order_details('representative',$ord_no,$order_details['maker_id']);

	    		

	    		$arr_available_qty_by_skno = [];
	    		$arr_requested_qty_by_skno = [];
	    		$arr_sku_no = [];

	    		/* If True then Direct Inventory Deduction Applicable
					  False then Order Split Applicable with Email Sent to Retailer for Confirmation 

	    		*/
	    		$is_completely_fulfilled = true;

	    		$arr_product = array_column($order_data, 'leads_details');

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
					        		$arr_sku_no[] = $product['sku'];
					        		$arr_requested_qty_by_skno[$product['sku']] = intval($product['qty']);
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

			    		if($arr_available_qty_by_skno[$sku_no] < $requested_quantity)
			    		{
			    			$is_completely_fulfilled = false;
			    		}
			    	}
			    }

			     /*When click on confirm order and insufficient inventory then return view and tell to vender that click on split order...*/
			    if(isset($slug_chk_inventory) && $slug_chk_inventory!=null)
			    {
			    	if($is_completely_fulfilled == false)
			    	{
			    		$response['status']      = 'split_warning';
			        	$response['description'] = 'Sorry, Product inventory is insufficient, for order processing please fulfill the product inventory.';
			        	return $response;	
			    	}
			    	else
			    	{
			    		$response['status']      = 'success';
			        	$response['description'] = 'Inventory available, you can proceed.';
			        	return $response;		
			    	}
			    	
			    }
			    

			    if($is_completely_fulfilled == true)
			    {
			    	/* Fulfilled Order */
			    	
    	    		if($out_of_stock_details && count($out_of_stock_details)>0)	    		
    	    		{

    	    			$response['status']      = 'warning';
    			        $response['description'] = 'Something went wrong, please try again.';
    			        return $response;
    	    		}


    	    		$arr_charge_data['order_amount'] = isset($order_details['total_wholesale_price'])?$order_details['total_wholesale_price']:0;

    	    		$arr_charge_data['stripe_key'] = isset($order_details['transaction_details']['strip_key_details']['secret_key'])?$order_details['transaction_details']['strip_key_details']['secret_key']:false;

    	    		$arr_charge_data['customer_id'] = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';

    	    		$arr_charge_data['card_id'] = isset($order_details['transaction_details']['card_id'])?$order_details['transaction_details']['card_id']:'';

    	    		$arr_charge_data['order_no'] = isset($order_details['order_no'])?$order_details['order_no']:'';

    		      	if(isset($arr_charge_data) && count($arr_charge_data) > 0 )
    	    		{
    	    			if($payment_term == 'Offline')
										{
										// Payment duducted
										// $isDirectPayment = isset($order_details['maker_details']['is_direct_payment'])?$order_details['maker_details']['is_direct_payment']:0;

										$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;

										$retailerId = isset($order_details['retailer_id'])?$order_details['retailer_id']:0;

										$customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';



										$order_status_update = $this->RepresentativeLeadsModel->where('id',$order_id)
										->where('maker_id',$order_details['maker_id'])

										->update(['maker_confirmation' => '1','is_direct_payment'=>$isDirectPayment]);
										// Reduse the quantity of product sku										 
										$this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);

										/* send retailer notification*/

										$view_href =  url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);


										$arr_notify_data                 = [];
										$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
										$arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';

										/* $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is deducted from your account.';*/
										$vendorStoreName = get_maker_company_name($order_details['maker_id']);
										$arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed from vendor : '.$vendorStoreName;

										$arr_notify_data['title']        = 'Order Confirmed';
										$arr_notify_data['type']         = 'retailer';  
										$arr_notify_data['link']         = $view_href;  

										$this->GeneralService->save_notification($arr_notify_data);

										/* get admin id */

										$admin_id  = get_admin_id();
										$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);

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

										$this->GeneralService->save_notification($arr_notify_data);


										/* send vendor notification*/
										/*$view_href =  url('/').'/vendor/representative_orders/view/'.base64_encode($order_details['order_no']);
										$arr_notify_data                 = [];
										$arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
										$arr_notify_data['from_user_id'] = $admin_id or '';

										$arr_notify_data['description']  = 
										'Order('.$order_details['order_no'].') is confirmed and payment went through successfully .';

										$arr_notify_data['title']        = 'Order Confirmed';
										$arr_notify_data['type']         = 'maker';  
										$arr_notify_data['link']         = $view_href;  

										$this->GeneralService->save_notification($arr_notify_data);*/

										/* send amount credit notification to vendor if payment type id direct payment */
										/* send vendor notification*/


										/*Send mail to retailer and Admin*/

										/* To Retailer */
										/* Send mail to retailer */

										$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

										$this->send_mail($retailer_mail,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$hide_vendor_address_from_retailer=1);


										/*send the mail to admin*/

										$admin_email = 0;

										$admin_details = $this->UserModel->where('id',1)->first();

										if(isset($admin_details))
										{
										$admin_email = $admin_details->email;

										}

										$this->send_mail($admin_email,$order_details['leads_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);




										/* get all product list from order id*/
										$product_inventory_arr = [];

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

										$product_inventory_arr[$key]['sku_no'] = $product['sku'];

										$product_inventory_arr[$key]['inventory_stock'] = $inventory;
										}                 


										}
										}

										if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
										{

										//send vendor notification
										$key1 = '';
										$arr_notify_data                 = [];
										$arr_notify_data['from_user_id'] = $admin_id or '';

										$arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
										$html = '';
										$html= "Following products having insufficient inventory: ";

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

										$html.= '  '.$key1.') Name : '.$value['product_name'].' SKU:('.$value['sku_no'].')'.' and Available Stock : '.$value['inventory_stock'].'  ';
										}

										$arr_notify_data['description']  = $html;
										$arr_notify_data['title']        = 'Insufficient Inventory';
										$arr_notify_data['type']         = 'maker';  
										$arr_notify_data['link']         = '';  

										$this->GeneralService->save_notification($arr_notify_data);


										}

										/*send email to the vendor*/
										$maker_id = '';

										$maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

										$vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

										$arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'rep_sales_order'); 

										$email_status  = $this->EmailService->send_mail($arr_mail_data);


										/*---------------------------------------------------*/ 

										$response['status']      = 'success';
										$response['description'] = 'Order has been confirmed successfully.';
										return $response;


							}
    				        else
    				        {

				        		$response['status']      = 'warning';
					        	
					        	$response['description'] = 'Order is not generated from customer account, please check and confirm';
					        	return $response;
    				        }	
    	    			}

    		        	else
    		        	{
    		        		$response['status']      = 'warning';
    			        	$response['description'] = 'Sorry, Payment has not been done yet from Customer. Please wait till the payment receive.';
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
                    //if payment is done or net if not order should not split

                    if($payment_term == '' && $payment_term == null)
                    {
                        $response['status']      = 'warning';
    			        $response['description'] = 'Sorry, Payment has not been done yet from Customer. Please wait till the payment receive.';
    			        return $response;
                    }


			    	/* Split Order */
			    	$is_quantity_available = array_filter($arr_available_qty_by_skno);

			    	if (empty($is_quantity_available)) 
			    	{
			    		/*Product stock is not available*/
		    			$response['status'] = 'warning';
		    			$response['description'] = 'Oops..! Stock is not available currently, please update quantity of product.';
			        	return $response;
			    		
			    	}
			    	
			    	
			    	$order_arr = $order_arr['fulfill'] = [];

			    	/*$partial_order_no = str_pad('J2',  10, rand('1234567890',10)); 
			    	$fulfill_order_no = str_pad('J2',  10, rand('1234567890',10)); */

			    	$partial_order_no = $this->orderDataService->generate_order_no($order_details['order_no']);
			    	$fulfill_order_no = $this->orderDataService->generate_order_no($partial_order_no);

			    	/* Split the quantity of product*/
			    	foreach ($order_data as $key => $product_arr) {

			    		$order_arr = $product_arr;

			    		if (sizeof($product_arr['leads_details']) > 0) {
			    			
			    			foreach ($product_arr['leads_details'] as $key => $product) {

			    	
			    				foreach ($arr_available_qty_by_skno as $availble_qty_key => $available_qty_by_skno) {
			    				// foreach ($is_quantity_available as $availble_qty_key => $available_qty_by_skno) {
			    					
			    					if ($product['sku'] == $availble_qty_key)
			    					{

			    						if ($product['qty'] > $available_qty_by_skno)
			    						{
		    							   /*
		    								#If product have maximum quantity than available quantity
											# split product 1) Fulfill the quantity requirement
													        2) Send remaining quantity in partial order 	
		    							   */

											if($available_qty_by_skno == 0)
											{
												/*Product have 0 stock */

												$remainig_product_qty = $product['qty'] - $available_qty_by_skno;
				    							$total_price = $remainig_product_qty * $product['unit_wholsale_price'];

				    							if(count($remainig_product_qty) <= 0)
												{
													$remainig_product_qty = 0;
												}

				    							
				    							$order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';

				    							$sku_no = isset($product['sku'])?$product['sku']:"-";

				    							$order_arr['partial'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';

				    							$order_arr['partial'][$key]['sku_no'] = $product['sku'] or '';
				    							$order_arr['partial'][$key]['qty'] 	= $remainig_product_qty or '';
				    							$order_arr['partial'][$key]['unit_price'] 	= $product['unit_wholsale_price'];
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

											}
											else
											{
												$remainig_product_qty = $product['qty'] - $available_qty_by_skno;
												$total_price = $remainig_product_qty * $product['unit_wholsale_price'];

												if(count($remainig_product_qty) <= 0)
												{
													$remainig_product_qty = 0;
												}

												$order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';

												$sku_no = isset($product['sku'])?$product['sku']:"-";

												$order_arr['partial'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';

												$order_arr['partial'][$key]['sku_no'] = $product['sku'] or '';
												$order_arr['partial'][$key]['qty'] 	= $remainig_product_qty or '';
												$order_arr['partial'][$key]['unit_price'] 	= $product['unit_wholsale_price'];
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
												

												$total_price = $available_qty_by_skno * $product['unit_wholsale_price'];
												
												$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';

												$sku_no = isset($product['sku'])?$product['sku']:"-";

												$order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';



												$order_arr['fulfill'][$key]['sku_no'] 		= $product['sku'] or '';
												$order_arr['fulfill'][$key]['qty'] 		= $available_qty_by_skno or '';
												$order_arr['fulfill'][$key]['unit_price'] 	= $product['unit_wholsale_price'];
												$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;


												$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

												$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
												$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
												$order_arr['fulfill'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0;

												$arr_requested_qty_by_skno[$product['sku']] = intval($product['qty']);
											}		    							
				    					}				    					
				    					else
				    					{
			    							/*
			    								If product have full quantity
											*/
			    							$total_price = $product['qty'] * $product['unit_wholsale_price'];	

				    						
			    							$order_arr['fulfill'][$key]['product_id'] 	= $product['product_id'] or '';

			    							$sku_no = isset($product['sku'])?$product['sku']:"-";

			    							$order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


			    							$order_arr['fulfill'][$key]['sku_no'] 		= $product['sku'] or '';
			    							$order_arr['fulfill'][$key]['qty'] 		= $product['qty'] or '';
			    							$order_arr['fulfill'][$key]['unit_price'] 	= $product['unit_wholsale_price'];
			    							$order_arr['fulfill'][$key]['total_price'] 	= isset($total_price)?$total_price:0;

			    							$product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

			    							$order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
			    							$order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
			    							$order_arr['fulfill'][$key]['product_discount'] = (isset($product_discount['product_discount']))?$product_discount['product_discount']:0;

				    					}	
			    					}			    					
			    				}
			    			}
			    		}	
			    		
			    	}
			    	
			    	/*Store split order record */

			    	$promotion_free_shipping = $get_discount_amt = 0;
			    	
			    	if($order_arr) {

			    		$add_details['user_id'] 		= $order_arr['address_details']['user_id'];
			    		$add_details['bill_first_name'] = $order_arr['address_details']['bill_first_name'];
	    				$add_details['bill_last_name'] 	= $order_arr['address_details']['bill_last_name'];
	    				$add_details['bill_email'] 		= $order_arr['address_details']['bill_email'];
	    				$add_details['bill_mobile_no'] 	= $order_arr['address_details']['bill_mobile_no'];
	    				$add_details['bill_complete_address'] = $order_arr['address_details']['bill_complete_address'];
	    				$add_details['bill_city'] 		= $order_arr['address_details']['bill_city'];
	    				$add_details['bill_state'] 		= $order_arr['address_details']['bill_state'];
	    				$add_details['bill_zip_code'] 	= $order_arr['address_details']['bill_zip_code'];
	    				$add_details['ship_first_name'] = $order_arr['address_details']['ship_first_name'];
	    				$add_details['ship_last_name'] 	= $order_arr['address_details']['ship_last_name'];
	    				$add_details['ship_email'] 		= $order_arr['address_details']['ship_email'];
	    				$add_details['ship_mobile_no'] 	= $order_arr['address_details']['ship_mobile_no'];
	    				$add_details['ship_complete_address'] = $order_arr['address_details']['ship_complete_address'];
	    				$add_details['ship_city'] 		= $order_arr['address_details']['ship_city'];
	    				$add_details['ship_state'] 		= $order_arr['address_details']['ship_state'];
	    				$add_details['bill_country'] 	= $order_arr['address_details']['bill_country'];
	    				$add_details['ship_country'] 	= $order_arr['address_details']['ship_country'];
	    				$add_details['ship_zip_code'] 	= $order_arr['address_details']['ship_zip_code'];
	    				$add_details['is_as_below'] 	= $order_arr['address_details']['is_as_below'];



                        $add_details['ship_street_address']  = $order_arr['address_details']['ship_street_address'];
                        $add_details['ship_suit_apt'] 	     = $order_arr['address_details']['ship_suit_apt'];
                        $add_details['bill_street_address']  = $order_arr['address_details']['bill_street_address'];
                        $add_details['bill_suit_apt'] 	     = $order_arr['address_details']['bill_suit_apt'];
                        $add_details['bill_mobile_no'] 	     = $order_arr['address_details']['bill_mobile_no'];
                        $add_details['ship_mobile_no'] 	     = $order_arr['address_details']['ship_mobile_no'];


                        /* get parent order details */
                        $parentOrderData = $this->RepresentativeLeadsModel->where('id',$order_details['id'])
                                                                          ->first(['is_direct_payment',
                                                                          	       'admin_commission',
                                                                          	       'rep_sales_commission'
                                                                          	     ]);

			    		/*Update Order status to split order*/

			    		$this->RepresentativeLeadsModel->where('id',$order_details['id'])->update(['is_split_order'=>'1'],['maker_confirmation'=>'1']);



			    		//get parent order details
                        $lead_arr = $promotion_arr = [];

			    		$order_details_obj = $this->RepresentativeLeadsModel->with(['leads_details'])->where('id',$order_details['id'])->first();

			    		if(isset($order_details_obj))
			    		{
                           $lead_arr = $order_details_obj->toArray();
			    		}


			    		/*--------get promo code details------------------------------*/

                /*        $promo_code_id = $this->PromoCodeModel->where('promo_code_name',$lead_arr['promo_code'])->pluck('id')->first();

			    		$promotion_id = $this->PromotionsModel->where('promo_code',$promo_code_id)->pluck('id')->first();

			    		//get all promotion type of that promotion
						$promotion_offers_arr = $this->PromotionsOffersModel
							                         ->with(['get_prmotion_type'])
							                         ->where('promotion_id',$promotion_id)
							                         ->get()
							                         ->toArray();*/

			            /*----------------------------------------------------------*/



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

		    			
			    			$total_wholesale_price = $total_wholesale_price+$pro_ship_charge-$pro_dis-$pro_ship_dis;

			    			
		    				$arr_order['order_no'] = $fulfill_order_no;
		    				$arr_order['representative_id'] = $order_arr['representative_id'];
		    				$arr_order['sales_manager_id'] = $order_arr['sales_manager_id'];
		    				$arr_order['maker_id'] = $order_arr['maker_id'];
		    				$arr_order['retailer_id'] = $order_arr['retailer_id'];
		    				$arr_order['is_confirm'] = $order_arr['is_confirm'];
		    				$arr_order['transaction_id'] = $order_arr['transaction_id'];
		    				$arr_order['total_retail_price'] = $total_wholesale_price;
		    				$arr_order['total_wholesale_price'] = $total_wholesale_price;
		    				$arr_order['total_commission_wholesale'] = $order_arr['total_commission_wholesale'];
		    				$arr_order['total_product_discount'] = $pro_dis;
		    				$arr_order['total_shipping_charges'] = $pro_ship_charge;
		    				$arr_order['total_shipping_discount'] = $pro_ship_dis;
		    				$arr_order['total_product_shipping_charges'] = $order_arr['total_product_shipping_charges'];
		    
		    				$arr_order['payment_term'] = $order_arr['payment_term'];
		    				$arr_order['split_order_id'] = $order_arr['id'];

		    				/*$arr_order['is_direct_payment'] = get_maker_payment_term($order_arr['maker_id']);
		    				$arr_order['admin_commission'] = $this->CommissionService->get_admin_commission($order_arr['maker_id']);*/

		    				$arr_order['is_direct_payment'] = isset($parentOrderData['is_direct_payment'])?$parentOrderData['is_direct_payment']:get_maker_payment_term($order_arr['maker_id']);

		    				$arr_order['admin_commission'] = isset($parentOrderData['admin_commission'])?$parentOrderData['admin_commission']:$this->CommissionService->get_admin_commission($order_arr['maker_id']);

		    				 if(isset($order_arr['sales_manager_id']) && $order_arr['sales_manager_id'] != 0)
						      {
						         $arr_order['rep_sales_commission'] = isset($parentOrderData['rep_sales_commission'])?$parentOrderData['rep_sales_commission']:$this->CommissionService->get_sales_manager_commission($order_arr['sales_manager_id']);
						      }

						      if(isset($order_arr['representative_id']) && $order_arr['representative_id'] != 0)
						      {
						        $arr_order['rep_sales_commission'] = isset($parentOrderData['rep_sales_commission'])?$parentOrderData['rep_sales_commission']:$this->CommissionService->get_representative_commission($order_arr['representative_id']);
						      }




		    				$create_quote = $this->RepresentativeLeadsModel->create($arr_order);

		    				/*------save address detailes---------------*/
		    				
		    				$add_details['order_no'] = $fulfill_order_no;
		    				
		    				$saveAddress = $this->AddressModel->create($add_details);

		    				foreach ($order_arr['fulfill'] as $key => $product) {
		    					
				        		$quote_product_arr = [];
				        		

				        		$quote_product_arr['order_no'] = $fulfill_order_no;
				        		$quote_product_arr['representative_leads_id'] = $create_quote->id;
				        		$quote_product_arr['maker_id'] = $order_arr['maker_id'];
				        		$quote_product_arr['product_id']         = $product['product_id'];
				        		$quote_product_arr['sku']             = $product['sku_no'];
				        		$quote_product_arr['qty']                = $product['qty'];
				        		$quote_product_arr['retail_price']       = $product['unit_price'];
				        		$quote_product_arr['unit_wholsale_price']= $product['unit_price'];
				        		$quote_product_arr['wholesale_price']    = $product['qty']*$product['unit_price'];
				        		$quote_product_arr['description']  		 = '';


				        		$quote_product_arr['shipping_charges']    = $product['shipping_charges'];
				        		$quote_product_arr['shipping_charges_discount']  = $product['shipping_discount'];

				        		/*$quote_product_arr['product_shipping_charge']  = $product['shipping_charges']+$product['shipping_discount'];*/

				        		$quote_product_arr['product_shipping_charge']  = $product['shipping_charges'];

				        		$quote_product_arr['product_discount']  = $product['product_discount'];
				        		
				        		// $grand_total = $product['total_price'] + $quote_product_arr['shipping_charge']-$quote_product_arr['shipping_discount']-$product['product_discount']; 
				        		// $quote_product_arr['wholesale_price']    = $grand_total;
				        		 	        	
				        		$create_quote_product = $this->RepresentativeProductLeadsModel->create($quote_product_arr);
		    				}
		    			
		    				$payment_status = $this->payment_process($arr_order);
		    				
		    				if ($payment_status['status'] == 'success') {

		    					// reduce the quantity
		    					$this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);

		    				}


				    	}
			

			    		/* Store partial order record */
			    		if (isset($order_arr['partial']) && sizeof($order_arr['partial']) > 0) {

			    			$order_arr['partial'] = array_values($order_arr['partial']);

			    			$total_wholesale_price = $total_retail_price = 0;
			    			$pro_ship_charge =0;
			    			$pro_ship_dis =0;
			    			$pro_dis = 0;

			    			$total_retail_price = array_sum(array_column($order_arr['partial'],'total_price'));
			    			$total_wholesale_price = array_sum(array_column($order_arr['partial'],'total_price'));

			    			$pro_ship_charge = array_sum(array_column($order_arr['partial'],'shipping_charges'));
		    				$pro_ship_dis = array_sum(array_column($order_arr['partial'],'shipping_discount'));
		    				$pro_dis =array_sum(array_column($order_arr['partial'],'product_discount'));

			    			$total_wholesale_price = $total_wholesale_price+$pro_ship_charge-$pro_dis-$pro_ship_dis;

		    				$arr_order['order_no'] = $partial_order_no;
		    				$arr_order['representative_id'] = $order_arr['representative_id'];
		    				$arr_order['sales_manager_id'] = $order_arr['sales_manager_id'];
		    				$arr_order['maker_id'] = $order_arr['maker_id'];
		    				$arr_order['retailer_id'] = $order_arr['retailer_id'];
		    				$arr_order['is_confirm'] = $order_arr['is_confirm'];
		    				$arr_order['transaction_id'] = $order_arr['transaction_id'];
		    				$arr_order['total_retail_price'] = $total_wholesale_price;
		    				$arr_order['total_wholesale_price'] = $total_wholesale_price;
		    				$arr_order['total_commission_wholesale'] = $order_arr['total_commission_wholesale'];
		    				$arr_order['total_product_discount'] = $pro_dis;
		    				$arr_order['total_shipping_charges'] = $pro_ship_charge;
		    				$arr_order['total_shipping_discount'] = $pro_ship_dis;
		    				$arr_order['total_product_shipping_charges'] = $order_arr['total_product_shipping_charges'];
		    
		    				$arr_order['payment_term'] = $order_arr['payment_term'];
		    				$arr_order['split_order_id'] = $order_arr['id'];

		    	
		    				$arr_order['is_direct_payment'] =  isset($parentOrderData['is_direct_payment'])?$parentOrderData['is_direct_payment']:get_maker_payment_term($order_arr['maker_id']);


		    				$arr_order['admin_commission'] = isset($parentOrderData['admin_commission'])?$parentOrderData['admin_commission']:$this->CommissionService->get_admin_commission($order_arr['maker_id']);
		    				
		    				 if(isset($order_arr['sales_manager_id']) && $order_arr['sales_manager_id'] != 0)
						      {
						         $arr_order['rep_sales_commission'] = isset($parentOrderData['rep_sales_commission'])?$parentOrderData['rep_sales_commission']:$this->CommissionService->get_sales_manager_commission($order_arr['sales_manager_id']);
						      }

						      if(isset($order_arr['representative_id']) && $order_arr['representative_id'] != 0)
						      {
						        $arr_order['rep_sales_commission'] = isset($parentOrderData['rep_sales_commission'])?$parentOrderData['rep_sales_commission']:$this->CommissionService->get_representative_commission($order_arr['representative_id']);
						      }

		    				

		    				$create_quote = $this->RepresentativeLeadsModel->create($arr_order);

		    				$add_details['order_no'] = $partial_order_no;
		    				
		    				$saveAddress = $this->AddressModel->create($add_details);

		    				foreach ($order_arr['partial'] as $key => $product) {
		    					//dd($product);
			        			$quote_product_arr = [];
			        		

			        			$quote_product_arr['order_no'] = $partial_order_no;
				        		$quote_product_arr['representative_leads_id'] = $create_quote->id;
				        		$quote_product_arr['maker_id'] = $order_arr['maker_id'];
				        		$quote_product_arr['product_id']         = $product['product_id'];
				        		$quote_product_arr['sku']             = $product['sku_no'];
				        		$quote_product_arr['qty']                = $product['qty'];
				        		$quote_product_arr['retail_price']       = $product['unit_price'];
				        		$quote_product_arr['unit_wholsale_price']= $product['unit_price'];
				        		$quote_product_arr['wholesale_price']    = $product['qty']*$product['unit_price'];
				        		$quote_product_arr['description']  		 = '';


				        		$quote_product_arr['shipping_charges']    = $product['shipping_charges'];
				        		$quote_product_arr['shipping_charges_discount']  = $product['shipping_discount'];
				        		
				        		//$quote_product_arr['product_shipping_charge']  = $product['shipping_charges']+$product['shipping_discount'];

				        		$quote_product_arr['product_shipping_charge']  = $product['shipping_charges'];

				        		$quote_product_arr['product_discount']  = $product['product_discount'];
			        		 	        		
				        		// dd($quote_product_arr);
			        		$create_quote_product = $this->RepresentativeProductLeadsModel->create($quote_product_arr);
		    				}

		    				if(empty($order_arr['fulfill']))
		    				{
		    					
		    					$main_order_status_update = $this->RepresentativeLeadsModel->where('id',$order_arr['id'])
		    														 ->where('maker_id',$order_arr['maker_id'])

		    														 ->update(['maker_confirmation' => '1']);
		    				}
		    					
			    		}


			        //whose placed this order
                    $order_by = '';

			        if(isset($order_arr['representative_id']) && $order_arr['representative_id']!='')
			        {
                       $order_by = 'representative';
                        
			        }
			        elseif(isset($order_arr['sales_manager_id']) && $order_arr['sales_manager_id']!=''){
                      $order_by = 'sales_manager';

			        }else{
                      $order_by = '';
			        }		


			        /*send the mail to retailer*/

                    $retailer_email_id = $this->HelperService->get_user_mail($order_arr['retailer_id']);

                    $order_arr['user_details'] = $this->UserModel
                        ->with(['retailer_details'])  
                                  ->where('id',$order_arr['retailer_id'])
                                  ->first()
                        ->toArray();
                      
                    $email_status      = $this->send_rep_split_order_mail($order_arr,$retailer_email_id,$partial_order_no,$fulfill_order_no,$order_by);

                    /*-------------------------------------------------------------*/

                    /*send the mail to admin*/
                    $admin_email = 0;

                    $admin_details = $this->UserModel->where('id',1)->first();

                    if(isset($admin_details))
                    {
                       $admin_email = $admin_details->email;
                    }


                    $email_status = $this->send_rep_split_order_mail($order_arr,$admin_email,$partial_order_no,$fulfill_order_no,$order_by);


                    /* send notification and mail to the vendor and admin
                     for insufficient inventory after confirm the order*/  
                     
                     
                    /* get all product list from order id*/
                    $admin_id  = get_admin_id();
                    
                    $product_inventory_arr = [];

                    $order_product_details = $this->RepresentativeProductLeadsModel
                                                  ->with(['product_details'])
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

                    			$product_inventory_arr[$key]['sku_no']= $product['sku'];

                                $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                    		}                 
                    	

                    	}
                    }
                     
                    if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                    {
                      
                        //send vendor notification
                        $key1 = '';
		        		$arr_notify_data                 = [];
					    $arr_notify_data['from_user_id'] = $admin_id or '';

					    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
                        $html = '';
					    $html= "Following products having insufficient inventory: ";

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

						    $html.= '  '.$key1.') Name : '.$value['product_name'].' SKU:('.$value['sku_no'].')'.' and Available Stock : '.$value['inventory_stock'].'  ';
					    }

                        $arr_notify_data['description']  = $html;
					    $arr_notify_data['title']        = 'Insufficient Inventory';
					    $arr_notify_data['type']         = 'maker';  
					    $arr_notify_data['link']         = '';  

	        			$this->GeneralService->save_notification($arr_notify_data);


                    }
                    
                    /*send email to the vendor*/
                    $maker_id = '';

                    $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

                    $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

                    $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'rep_sales_order'); 

                    $email_status  = $this->EmailService->send_mail($arr_mail_data);
                      

                   /*---------------------------------------------------*/



			        $response['status']      = 'success';
			        $response['description'] = 'Order is split because of inadequate inventory to fulfill.';
			        return $response;
			    		
			    	}
			    	$response['status']      = 'warning';
			        $response['description'] = 'Something went wrong, please try again.';
			        return $response;
			    }


    		}
   

    }


    /*Order from retailer send mail for split order to retailer and admin*/

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
            	$sub_total = $quote['qty']*$quote['unit_wholsale_price'];

                $total_sub_total+= $sub_total;
            }
        }

        if(isset($order_arr['leads_details']) && count($order_arr['leads_details'])>0)
        {
            foreach($order_arr['leads_details'] as $key=>$quote)
            {
            	$sub_total = $quote['qty']*$quote['unit_wholsale_price'];

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
/*
	    $fulfill_final_total = $fulfill_sub_total+$fulfill_total_ship_charges-$fulfill_total_ship_discount-$fulfill_total_product_discount;*/
        $fulfill_final_total = $fulfill_sub_total;



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


        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;
       		
	  	$sno  = '0';
	  	$role = 'Retailer';

		
		$completed_ordNo = isset($order_arr['order_no'])?base64_encode($order_arr['order_no']):'';
		$confirmed_ordNo = isset($fulfill_order_no)?base64_encode($fulfill_order_no):'';
		$pending_ordNo = isset($partial_order_no)?base64_encode($partial_order_no):'';

		$vendorId = isset($order_arr['maker_id'])?base64_encode($order_arr['maker_id']):'';

		$main_orderCalculationData = $this->HelperService->get_order_calculation_data($completed_ordNo,$vendorId,$userSegment='retailer'); 

		$confirmed_orderCalculationData = $this->HelperService->get_order_calculation_data($confirmed_ordNo,$vendorId,$userSegment='retailer');

		$pending_orderCalculationData = $this->HelperService->get_order_calculation_data($pending_ordNo,$vendorId,$userSegment='retailer');

	
	    $pdf = PDF::loadView('front/split_order_invoice',compact('order_arr','partial_order_no','fulfill_order_no','calculation','order_place_date','main_orderCalculationData','confirmed_orderCalculationData','pending_orderCalculationData'));
	  	
   	    $currentDateTime = 'Split_Order'.date('H:i:s').'.pdf';

        /*-----------------------------------------------------------------*/

        
	    if($user_id == 1)
        {

        	$credentials = ['email' => $to_mail_id];
      
        	$arr_user    = get_user_by_credentials($credentials);

           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);
			
		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];

		    $arr_built_content  =  	[
			 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
			 	  					   	'ORDER_NO'			=> $order_arr['order_no']
			                        ];

			$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'45',$arr_user);
	
        }
        else
        {

        	/*send email*/
	  		$credentials = ['email' => $to_mail_id];
      
        	$arr_user    = get_user_by_credentials($credentials);

           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);
			
		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];

		    $arr_built_content  =  	[
			 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name']
			                        ];

			$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'44',$arr_user);
	
        }

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
		     	 
	}


	/*Order from Reps or sales manager send mail for split order to retailer and admin*/

	public function send_rep_split_order_mail($order_arr=false,$to_mail_id=false,$partial_order_no=false,$fulfill_order_no=false,$order_by=false)
	{
		
	    $order_summary = [];
	    $role = '';
        $to_mail_id    = $to_mail_id;
        $user = Sentinel::check();
		$loggedIn_userId = 0;
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;
       	$sno  = '0';

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

       


        if(isset($order_arr['leads_details']) && count($order_arr['leads_details'])>0)
        {
            foreach($order_arr['leads_details'] as $key=>$quote)
            {
            	$sub_total = $quote['qty']*$quote['unit_wholsale_price'];

                $total_sub_total+= $sub_total;
            }
        }


        if (isset($order_arr['leads_details'])) {
        	$total_shipping_charges    = array_sum(array_column($order_arr['leads_details'],'shipping_charges'));
	        $total_shipping_discount   = array_sum(array_column($order_arr['leads_details'],'shipping_charges_discount'));
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

       
        if(isset($order_arr['representative_id']) && $order_arr['representative_id']!=0)
        {
           $role = 'Representative';
        }
        elseif(isset($order_arr['sales_manager_id']) && $order_arr['sales_manager_id']!=0)
        {
        	$role = "Sales Manager";
        }
        else
        {
        	$role = '';
        }
	    
        $completed_ordNo = isset($order_arr['order_no'])?base64_encode($order_arr['order_no']):'';

		$confirmed_ordNo = isset($fulfill_order_no)?base64_encode($fulfill_order_no):'';

		$pending_ordNo = isset($partial_order_no)?base64_encode($partial_order_no):'';

		$vendorId = isset($order_arr['maker_id'])?base64_encode($order_arr['maker_id']):'';

		if(isset($order_by) && $order_by == 'representative')
		{
		   $order_by = 'representative';
		}
		elseif(isset($order_by) && $order_by == 'sales_manager')
		{
		   $order_by = 'sales_manager';
		}


		$main_orderCalculationData = $this->HelperService->get_order_calculation_data($completed_ordNo,$vendorId,$userSegment=$order_by); 

		$confirmed_orderCalculationData = $this->HelperService->get_order_calculation_data($confirmed_ordNo,$vendorId,$userSegment=$order_by);

		$pending_orderCalculationData = $this->HelperService->get_order_calculation_data($pending_ordNo,$vendorId,$userSegment=$order_by);

	    $pdf = PDF::loadView('front/split_rep_order_invoice',compact('order_arr','partial_order_no','fulfill_order_no','calculation','order_place_date','role','order_by','main_orderCalculationData','confirmed_orderCalculationData','pending_orderCalculationData'));
	  	
   	    $currentDateTime = 'Split_Order'.date('H:i:s').'.pdf';

   	    if($user_id == 1)
        {
        	$credentials = ['email' => $to_mail_id];
      
        	$arr_user    = get_user_by_credentials($credentials);

           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);
			
		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];

		    $arr_built_content  =  	[
			 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name'],
			 	  					   	'ORDER_NO'			=> $order_arr['order_no']
			                        ];

			$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'45',$arr_user);
	
        }
        else
        {

        	/*send email*/
	  		$credentials = ['email' => $to_mail_id];
      
        	$arr_user    = get_user_by_credentials($credentials);

           	/*Get site setting data from helper*/
	        $arr_site_setting = get_site_settings(['site_name','website_url']);
			
		    $pdf_arr = 	[
		    				'PDF'           => $pdf,
		            		'PDF_FILE_NAME' => $currentDateTime
		               	];

		    $arr_built_content  =  	[
			 	  					   	'PROJECT_NAME'      => $arr_site_setting['site_name']
			                        ];

			$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'44',$arr_user);
			
	
        }

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
	
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
            
            if(isset($status) && $status == 'retailer_order')
            {
                $order_product_details =  $this->RetailerQuotesProductModel
                                               ->with(['product_details'])
                                               ->where('retailer_quotes_id',$order_id)
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
					
					
					$arr_site_setting = get_site_settings(['site_name','website_url']);

	                $arr_built_content = ['USER_NAME'       => $arr_user['first_name'],
	                                      'APP_NAME'        => $arr_site_setting['site_name'],
										  'HTML'            => $html,
										  'SITE_URL'        => $arr_site_setting['website_url']
										];

		            $arr_mail_data                      = [];
		            $arr_mail_data['email_template_id'] = '51';
		            $arr_mail_data['arr_built_content'] = $arr_built_content;
		            $arr_mail_data['arr_user']          = $arr_user;

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
                			$product_inventory_arr[$key]['sku_no']= $product['sku'];
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
		            $arr_mail_data['arr_user']          = $arr_user;

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


     public function payment_proof($order_id,Request $request)
    {
       
        $order_id = base64_decode($order_id);
       
        $arr_rules = [
            'payment_proof' => 'required'

        ];

        $validator = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {
            $response['status'] = 'warning';
            $response['description'] = 'Form validations failed, please check form fields.';

            return response()->json($response);
        }


        if($request->hasfile('payment_proof')) 
        {
   
              $file = $request->file('payment_proof')->getClientOriginalName();
              $fileName = pathinfo($file,PATHINFO_FILENAME);
              $file_extension =strtolower($request->file('payment_proof')->getClientOriginalExtension()); 
              if(in_array($file_extension,['jpg','jpeg','png','JPG','PNG','JPEG']))
              {                           
                  $file = date('mdYHis') . uniqid() .'.'.$file_extension;
                  $request->file('payment_proof')->move($this->payment_proof.'payments_proof/', $file);
              }
        } 

        $payment_proof = $this->RetailerQuotesModel->firstOrNew(['id'=>$order_id]);
        $payment_proof->payment_proof = $file;
        
        if($payment_proof->save())
        {
            
           $response = $this->payment(base64_encode($order_id));
           $data['status'] = $response['status'];
           $data['description'] = $response['description']; 
           return $data;
         
        }
            
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
	// 	$retailerOrderData = $this->RetailerQuotesModel->where('order_no',$order_no)->count();
	// 	$repOrderData = $this->RepresentativeLeadsModel->where('order_no',$order_no)->count();
		
	// 	if($retailerOrderData || $repOrderData > 0)
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

	/* 
      Auth : Jaydip
      Desc : Pay order commission amount to admin
    */

	public function pay_to_admin(Request $request)
    {
        $form_data = $request->all();

        $orderId = isset($form_data['order_id'])?$form_data['order_id']:false;
        $vendorId = isset($form_data['maker_id'])?$form_data['maker_id']:false;
        $orderFrom = isset($form_data['order_from'])?$form_data['order_from']:false;

        $adminId = get_admin_id();

        /* get vendor client id */
        /*$clien_id = $this->UserStripeAccountDetailsModel->where('user_id',$vendorId)
			        									->pluck('client_id')
			        									->first();*/
/*
		if($clien_id)
		{*/

			 /*  Get admin stripe secret key id  */
	        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key(get_admin_id());

	        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

	        /*  Get maker stripe secret key id  */
	        $vendorStripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($vendorId);

	        $vendorStripKeyId = isset($vendorStripeApiKeyData['id'])?$vendorStripeApiKeyData['id']:'';

	        $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$adminId)
	                                                                      ->where('vendor_id',$vendorId)
                                                                          ->where('admin_stripe_key_id',$stripKeyId)
                                                                          ->where('vendor_stripe_key_id',$vendorStripKeyId)
	                                                                      ->first();

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

	            	if($orderFrom == 'retailer')
	            	{
	            		/* if order from retailer */
	                	$order_data = get_order_data($orderId);
	                    $arr_data['lead_id']    = $form_data['order_id'];

	            	}
	            	else
	            	{
	            		/* if order from rep/sales */
	            	  $order_data = get_lead_data($orderId);
	                  $arr_data['quote_id']         = $form_data['order_id'];

	            	}

	            	/*check already paid*/		
		            	if(isset($order_data['admin_commission_status']) && $order_data['admin_commission_status'] == '1')
		                {
		                    $response['status']  = 'pay-warning';
		                    $response['message'] = "Payment already completed for this order.";
		                    
		                   return response()->json($response);
		                }
	            	/*end*/

	                $arr_transfer_data['StripeKey']   = isset($vendorStripeKey)?$vendorStripeKey:'';
	                $arr_transfer_data['amount']      = isset($form_data['amount'])?num_format($form_data['amount']):0;
	                $arr_transfer_data['account_id']  = $stripe_acc_id;
	                // $arr_transfer_data['description'] = 'Payment for Order No:'.isset($order_data['order_no'])?$order_data['order_no']:false;
	                // dd($vendorStripeKey,$obj_stripe_account_details,$arr_transfer_data);
	                $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

	                if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
	                {
	                    $response['status'] = 'error';
	                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong,please try again.';
	                    return response()->json($response);
	                }

	                if($transfer_response)
	                {   

	                    $arr_data['amount']          = $form_data['amount'];
	                    $arr_data['transaction_id']  = $transfer_response->balance_transaction;
	                    $arr_data['transfer_id']     = $transfer_response->id;
	                    $arr_data['destination_payment'] = $transfer_response->destination_payment;
	                    $arr_data['status']          = '2';
	                    $arr_data['received_by']     = get_admin_id();
	                    $arr_data['paid_by']         = $vendorId;

	                    
	                     /* Create Entry in StripTransaction table */
	                    $create_stripe_transaction = $this->StripeTransactionModel->create($arr_data);

	            		$arrUpdateDate = [];
	            		$arrUpdateDate['admin_commission_status']           = '1';
	            		$arrUpdateDate['received_commission_stripe_key_id'] = $stripKeyId;
                        $arrUpdateDate['transfer_commission_stripe_key_id'] = $vendorStripKeyId;

	                    if($orderFrom == 'retailer')
		            	{
		            		/* if order from retailer */

		                	 
		                	 $this->RetailerQuotesModel->where('id',$orderId)->update($arrUpdateDate);
		            	}
		            	else
		            	{
		            		/* if order from rep/sales */
		            		$this->RepresentativeLeadsModel->where('id',$orderId)->update($arrUpdateDate);
		            	} 

	                    if($create_stripe_transaction)
	                    {

	                    	$bulk_transaction_key = rand(1000,9999).date("s");
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

	                        $view_href = '';
		                    $arr_mail_data                      = [];

	                        if($orderFrom == 'retailer')
			            	{
			            	    $arrMailData['retailer_order_data'][] = $order_data;
			            		
			            		/*check order from Retailer*/
	                        	$view_href = url('/').'/admin/retailer_orders/view/'.base64_encode($orderId);
	                        	
			            	}
			            	else
			            	{
			                    $arrMailData['rep_sales_order_data'][] = $order_data;

			            		/*check order from rep or sales*/
	                        	$view_href = url('/').'/admin/leads/view/'.base64_encode($orderId);	
			            	}

	                        $notification_arr['description']  = 'Commission ($'.$form_data['amount'].') is received successfully of order No: '.$order_data['order_no'];

	                        $notification_arr['title']        = 'Commission Received';
	                        $notification_arr['type']         = 'admin'; 
	                        $notification_arr['link']         = $view_href; 
	                        
	                        
	                        $this->GeneralService->save_notification($notification_arr);

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

					        $arr_mail_data['email_template_id'] = '61';
					        $arr_mail_data['arr_built_content'] = $arr_built_content;
					        $arr_mail_data['arr_user']          = $arr_user;
							
							$arrUserData['business_details'] = $arr_user;
							$arrUserData['personla_details'] = $arr_maker_user;

							$arrOrderAmount = [];							

							/*  ------ Send payment Invoice to Admin -----------------  */
							$invoice_data = $this->GeneralService->send_payment_invoice_to_admin($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount);
							/*  ----------- END ------------  */

	                        

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
            $response['message'] = "Stripe clien id is missing, please provide client id for further transaction.";
            
            return response()->json($response);
		}*/

    }

    /* 
      Auth : Jaydip
      Desc : send stripe account creation request to end user
    */
    public function send_stripe_acc_creation_link(Request $request)
    {

      $user_id   = $request->input('user_id');
      // $clientId  = $request->input('client_id');
      $maker_id = $request->input('vendor_id');

      if($maker_id)
      {
      	$vendor_id = $maker_id;
      }
      else
      {
      	$login_user_details = \Sentinel::getUser();
      	$vendor_id = $login_user_details->id;
      }

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

    public function set_amount_credited_email($amount,$orderNo,$arr_user,$paymentType=false)
    {
    	$email_template_id = '';
    	if($paymentType == 'Net30')
        {
        	$email_template_id = '60';
        }
        else
        {
    		$email_template_id = '54';
        }

    	/*call to dynamic function send_mail*/
		$arr_site_setting = get_site_settings(['site_name','website_url']);

		$arr_built_content = 
		[
				'credit_amount'   	=> $amount,
	            'order_no'        	=> $orderNo,
	            'PROJECT_NAME' 		=> $arr_site_setting['site_name'],					                            
        ];

	    $arr_mail_data                      = [];
	    $arr_mail_data['email_template_id'] = $email_template_id;
	    $arr_mail_data['arr_built_content'] = $arr_built_content;
	    $arr_mail_data['arr_user']          = $arr_user;
		/*end*/
		return $arr_mail_data;
    }


    /*Created new function for offline payment received*/
    public function set_offline_payment_received($amount,$orderNo,$arr_user,$paymentType=false)
    {
    	
    	$email_template_id = '83';   

    	/*call to dynamic function send_mail*/
		$arr_site_setting = get_site_settings(['site_name','website_url']);

		$arr_built_content = 
		[
				'credit_amount'   	=> $amount,
	            'order_no'        	=> $orderNo,
	            'PROJECT_NAME' 		=> $arr_site_setting['site_name'],					                            
        ];

	    $arr_mail_data                      = [];
	    $arr_mail_data['email_template_id'] = $email_template_id;
	    $arr_mail_data['arr_built_content'] = $arr_built_content;
	    $arr_mail_data['arr_user']          = $arr_user;
		/*end*/
		return $arr_mail_data;
    }


    public function set_amount_deducted_email($amount,$orderNo,$arr_user,$paymentType=false)
    {
    	$email_template_id = '55';
    
    	/*call to dynamic function send_mail*/
		$arr_site_setting = get_site_settings(['site_name','website_url']);

		$arr_built_content = 
		[
				'deducted_amount'   => $amount,
	            'order_no'        	=> $orderNo,
	            'PROJECT_NAME' 		=> $arr_site_setting['site_name'],					                            
        ];

	    $arr_mail_data                      = [];
	    $arr_mail_data['email_template_id'] = $email_template_id;
	    $arr_mail_data['arr_built_content'] = $arr_built_content;
	    $arr_mail_data['arr_user']          = $arr_user;
		/*end*/
		return $arr_mail_data;
    }

    /* For retailer order's payment received */
    public function payment_received($order_id){
    	$order_data = [];

	  	$order_id = isset($order_id)?base64_decode($order_id):'';
      
    	if($order_id)
    	{

    		$loggedInUserId = 0;
	        $user = \Sentinel::check();


	        $admin_id = get_admin_id();

	        if($user && $user->inRole('maker'))
	        {
	            $loggedInUserId = $user->id;
	        }
      
	    	$order_details = $out_of_stock_details = [];
	    	
	    	$order_details = $this->RetailerQuotesModel->where('id',$order_id)
	    									->with(['quotes_details','quotes_details.product_details'])
	    								    ->first();
	    		
	    	if($order_details)
	    	{
	    		$order_details = $order_details->toArray(); 
				$order_payment_status_update = $this->RetailerQuotesModel
											->where('id',$order_id)
											->where('maker_id',$order_details['maker_id'])
											->update(['is_payment_status' => '1']);

				/* Update payment received status in transaction_mapping module */
				$transaction_payment_status_update = $this->TransactionMappingModel
											->where('order_id',$order_id)
											->where('order_no',$order_details['order_no'])
											->where('user_id',$order_details['retailer_id'])
											->update(['transaction_status' => 2]);

				/*Create entry of paid amount of orders */
				//$transaction_id = rand('1234567890',10); 
				$transaction_id = str_pad('TXN_',  14, rand('1234567890',10)); 
				$arr_data['user_id']             = $order_details['retailer_id'] or '';
				$arr_data['order_id']            = $order_id or '';
				$arr_data['order_no']            = $order_details['order_no'] or '';
				$arr_data['amount']              = $order_details['total_wholesale_price'] or '';						
				$arr_data['transaction_id']      = $transaction_id or '';
				$arr_data['transaction_status']  = '2' or '';
				$arr_data['payment_type']        = '1' or '';

				$transaction_entry = $this->TransactionMappingModel->create($arr_data);


				$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;

				/* send amount payment received notification to vendor if payment type id direct payment */
				/* send vendor notification*/
				if($isDirectPayment == 1)
				{

						/* Send mail and notification to maker */
						$view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_details['id']);
						$arr_notify_data                 = [];
						$arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
						$arr_notify_data['from_user_id'] = $admin_id or '';

						$arr_notify_data['description']  = 'Amount : $'.num_format($order_details['total_wholesale_price']).' has been received of Order No : ('.$order_details['order_no'].')';

						$arr_notify_data['title']        = 'Payment Received';
						$arr_notify_data['type']         = 'maker';  
						$arr_notify_data['link']         = $view_href;  

						$this->GeneralService->save_notification($arr_notify_data);



						/* Send  mail */
						$vendor_mail = $this->HelperService->get_user_mail($order_details['maker_id']);

						$credentials = ['email' => $vendor_mail];
						$arr_user = get_user_by_credentials($credentials);

						$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

						$email_status  = $this->EmailService->send_mail($arr_mail_data);



						/* Send mail and notification to retailer */
						$view_href =  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
						$arr_notify_data                 = [];
						$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
						$arr_notify_data['to_user_id'] 	 = $order_details['retailer_id'] or '';

						$arr_notify_data['description']  = 'Your order ( Order No :('.$order_details['order_no'].') payment has been processed successfully.';

						$arr_notify_data['title']        = 'Payment Received';
						$arr_notify_data['type']         = 'retailer';  
						$arr_notify_data['link']         = $view_href;  

						$this->GeneralService->save_notification($arr_notify_data);

						/*For send mail*/
						$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

						$credentials = ['email' => $retailer_mail];
						$arr_user = get_user_by_credentials($credentials);

						$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

						$email_status  = $this->EmailService->send_mail($arr_mail_data);






				}


				/*send notification to admin for payment received  if payment type is indirect payment*/
				if($isDirectPayment == 0)
				{					
					$view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);
					$arr_notify_data                 = [];
					$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
					$arr_notify_data['to_user_id']   = $admin_id or '';

					$arr_notify_data['description']  = 'Amount : $'.num_format($order_details['total_wholesale_price']).' has been received of Order No : ('.$order_details['order_no'].')';

					$arr_notify_data['title']        = 'Payment Received';
					$arr_notify_data['type']         = 'admin';  
					$arr_notify_data['link']         = $view_href;  

					$this->GeneralService->save_notification($arr_notify_data);

					/* Send  mail */

					$admin_id    = get_admin_id();
					$admin_email = '';

					$admin_details = $this->UserModel->where('id',$admin_id)->first();

					if(isset($admin_details))
					{
						$admin_email = $admin_details->email;
					}

					$credentials = ['email' => $admin_email];
					$arr_user = get_user_by_credentials($credentials);
					$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

					$is_mail_sent  = $this->EmailService->send_mail($arr_mail_data);



					/* Send mail and notification to retailer */
					$view_href =  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);
					$arr_notify_data                 = [];
					$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
					$arr_notify_data['to_user_id'] 	 = $order_details['retailer_id'] or '';

					$arr_notify_data['description']  = 'Your order ( Order No :('.$order_details['order_no'].') payment has been processed successfully.';

					$arr_notify_data['title']        = 'Payment Received';
					$arr_notify_data['type']         = 'retailer';  
					$arr_notify_data['link']         = $view_href;  

					$this->GeneralService->save_notification($arr_notify_data);

					/*For send mail*/
					$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

					$credentials = ['email' => $retailer_mail];
					$arr_user = get_user_by_credentials($credentials);

					$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

					$email_status  = $this->EmailService->send_mail($arr_mail_data);
				}

				$response['status']      = 'success';
	        	$response['description'] = 'Payment has been received';
	        	return $response;				    		

    		}
    		else
    		{
    			$response['status']      = 'warning';
	        	$response['description'] = 'Something went wrong, please try again.';
	        	return $response;
    		
    		}
	    	
    } else {

    			$response['status']      = 'warning';
	        	$response['description'] = 'Something went wrong, please try again.';
	        	return $response;
   		 }
   		}

   	/* For representative order's payment received */
    public function representative_payment_received($order_id){
    	$order_data = [];

	  	$order_id = isset($order_id)?base64_decode($order_id):'';
      	
    	if($order_id)
    	{

    		$loggedInUserId = 0;
	        $user = \Sentinel::check();


	        $admin_id = get_admin_id();

	        if($user && $user->inRole('maker'))
	        {
	            $loggedInUserId = $user->id;
	        }
      
	    	$order_details = $out_of_stock_details = [];
	    	
	    	$order_details = $this->RepresentativeLeadsModel->where('id',$order_id)
	    									->with(['leads_details.product_details','maker_details.stripe_account_details'])
	    								   ->where('id',$order_id)->first();
	    		
	    	if($order_details)
	    	{
	    		$order_details = $order_details->toArray(); 
				$order_payment_status_update = $this->RepresentativeLeadsModel
											->where('id',$order_id)
											->where('maker_id',$order_details['maker_id'])
											->update(['is_payment_status' => '1']);


				/* Update payment received status in transaction_mapping module */
				$transaction_payment_status_update = $this->TransactionMappingModel
											->where('id',$order_id)
											->where('order_no',$order_details['order_no'])
											->where('user_id',$order_details['retailer_id'])
											->update(['transaction_status' => '2']);


				/*Create entry of paid amount of orders */
				//$transaction_id = rand('1234567890',10); 
				$transaction_id = str_pad('TXN_',  14, rand('1234567890',10)); 
				$arr_data['user_id']             = $order_details['retailer_id'] or '';
				$arr_data['order_id']            = $order_id or '';
				$arr_data['order_no']            = $order_details['order_no'] or '';
				$arr_data['amount']              = $order_details['total_wholesale_price'] or '';						
				$arr_data['transaction_id']      = $transaction_id or '';
				$arr_data['transaction_status']  = '2' or '';
				$arr_data['payment_type']        = '1' or '';

				$transaction_entry = $this->TransactionMappingModel->create($arr_data);



				$isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;

				/* send amount payment received notification to vendor if payment type id direct payment */
				/* send vendor notification*/
				if($isDirectPayment == 1)
				{

        			$view_href = '';
	        		$arr_notify_data                 = [];
				    $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
				    $arr_notify_data['from_user_id'] = $admin_id or '';

				   /* $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed, amount : $'.num_format($order_details['total_wholesale_price']).' is credited in your account.';*/

				    $arr_notify_data['description']  = 'Amount : $'.num_format($order_details['total_wholesale_price']).' has been received of Order No : ('.$order_details['order_no'].')';

				    $arr_notify_data['title']        = 'Payment Received';
				    $arr_notify_data['type']         = 'maker';  
				    $arr_notify_data['link']         = $view_href;  

        			$this->GeneralService->save_notification($arr_notify_data);
	        		
	        		/* get user mail details */
						$user_details = \Sentinel::findById($order_details['maker_id']);

						$user_email = isset($user_details->email)?$user_details->email:false;

	        		/* send mail to vendor for order amount is credited on your acount */

	        		$credentials = ['email' => $user_email];
          			$arr_user = get_user_by_credentials($credentials);

                	$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

                	$is_mail_sent  = $this->EmailService->send_mail($arr_mail_data);


                	/* Send mail and notification to retailer */
						$view_href =  url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);
						$arr_notify_data                 = [];
						$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
						$arr_notify_data['to_user_id'] 	 = $order_details['retailer_id'] or '';

						$arr_notify_data['description']  = 'Your order ( Order No :('.$order_details['order_no'].') payment has been processed successfully.';

						$arr_notify_data['title']        = 'Payment Received';
						$arr_notify_data['type']         = 'retailer';  
						$arr_notify_data['link']         = $view_href;  

						$this->GeneralService->save_notification($arr_notify_data);

						/*For send mail*/
						$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

						$credentials = ['email' => $retailer_mail];
						$arr_user = get_user_by_credentials($credentials);

						$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

						$email_status  = $this->EmailService->send_mail($arr_mail_data);

	                                   
					        		
				}

				/* Send mail to retailer */

				$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

				/*send the mail to admin*/

				$admin_email = 0;

				$admin_details = $this->UserModel->where('id',1)->first();

				if(isset($admin_details))
				{
				$admin_email = $admin_details->email;

				}
			

				/*send notification to admin for payment received  if payment type is indirect payment*/
				if($isDirectPayment == 0)
				{
						$admin_id  = get_admin_id();
						$view_href =  url('/').'/admin/leads/view/'.base64_encode($order_details['id']);

						$arr_notify_data                 = [];

						$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
						$arr_notify_data['to_user_id']   = $admin_id or '';						

						$arr_notify_data['description']  = 'Amount : $'.num_format($order_details['total_wholesale_price']).' has been received of Order No : ('.$order_details['order_no'].')';

						$arr_notify_data['title']        = 'Payment Received';
						$arr_notify_data['type']         = 'admin';  
						$arr_notify_data['link']         = $view_href;  

						$this->GeneralService->save_notification($arr_notify_data);


						/* Send mail and notification to retailer */
						$view_href =  url('/').'/retailer/my_orders/order_summary/'.base64_encode($order_details['order_no']).'/'.base64_encode($order_details['maker_id']);
						$arr_notify_data                 = [];
						$arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
						$arr_notify_data['to_user_id'] 	 = $order_details['retailer_id'] or '';

						$arr_notify_data['description']  = 'Your order ( Order No :('.$order_details['order_no'].') payment has been processed successfully.';

						$arr_notify_data['title']        = 'Payment Received';
						$arr_notify_data['type']         = 'retailer';  
						$arr_notify_data['link']         = $view_href;  

						$this->GeneralService->save_notification($arr_notify_data);

						/*For send mail*/
						$retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);

						$credentials = ['email' => $retailer_mail];
						$arr_user = get_user_by_credentials($credentials);

						$arr_mail_data = $this->set_offline_payment_received(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$arr_user,false);

						$email_status  = $this->EmailService->send_mail($arr_mail_data);
				}

				$response['status']      = 'success';
	        	$response['description'] = 'Payment has been received';
	        	return $response;				    		

    		}
    		else
    		{
    			$response['status']      = 'warning';
	        	$response['description'] = 'Something went wrong, please try again.';
	        	return $response;
    		
    		}
	    	
    } else {

    			$response['status']      = 'warning';
	        	$response['description'] = 'Something went wrong, please try again.';
	        	return $response;
   		 }
   		}
    
}
