<?php
namespace App\Common\Services;

use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\TransactionsModel;
use App\Models\TransactionMappingModel;
use App\Models\RepresentativeModel;
use App\Models\RetailerModel;
use App\Models\EmailTemplateModel;

use App\Common\Services\EmailService;
use App\Common\Services\HelperService;
use Mail;
use Request;

use App\Events\NotificationEvent;
use Session, Sentinel, DB,PDF,Storage;

class RepsEmailService
{
	public function __construct(
                                    EmailService $EmailService,
                                    HelperService $HelperService,
									RepresentativeLeadsModel $RepresentativeLeadsModel,
									RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
									RoleUsersModel $RoleUsersModel,	
									TransactionsModel $TransactionsModel,
									MakerModel $MakerModel,
									RepresentativeModel $RepresentativeModel,
									RetailerModel $RetailerModel,
									EmailTemplateModel $EmailTemplateModel,
									UserModel $UserModel,
                                    TransactionMappingModel $TransactionMappingModel
									
								)
	{		
		$this->UserModel 					   = $UserModel;		
		$this->TransactionsModel               = $TransactionsModel;
		$this->RepresentativeLeadsModel        = $RepresentativeLeadsModel;
		$this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;	
		$this->MakerModel 					   = $MakerModel;
		$this->RetailerModel 				   = $RetailerModel;
		$this->EmailTemplateModel			   = $EmailTemplateModel;
		$this->RoleUsersModel			       = $RoleUsersModel;
		$this->RepresentativeModel 			   = $RepresentativeModel;
        $this->EmailService                    = $EmailService;        
        $this->TransactionMappingModel         = $TransactionMappingModel;  
        $this->HelperService              	   = $HelperService;   
	}

	public function send_retailer_mail($product_arr,$order_no,$user=null,$file_to_path=false,$maker_addr=false,$retailer_data=false)
	{ 
        
        $order_by = $Role = '';



		// $arr_product = $product_arr;
		foreach ($product_arr as $key => $product) {

			$address_details = $product['address_details'];

			foreach ($product['leads_details'] as $key => $products) {
				$arr_product[] = $products;
			}
		}
		
		$order_no = $order_no;
		$file_to_path = $file_to_path;
		$order_summary= $promotion_session_data = [];
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = $promotion_discount = 0;

     	if(isset($arr_product) && count($arr_product)>0)
     	{
     	   	foreach ($arr_product as $key => $product) 
     	   	{

 	   			$product_details = get_product_details($product['product_id']);
     	   		$sku_no = isset($product['sku'])?$product['sku']:"-";

     	   		$product_name = $product_details['product_name'].' (SKU: '.$sku_no.')';

     	   		$order[$key]['product_name'] = $product_name;
     	   		$order[$key]['order_no']= $order_no or '';
     	   		$order[$key]['item_qty']     = $product['qty'];
     	   		$order[$key]['unit_price']   = $product['unit_wholsale_price'];
     	   		$order[$key]['total_wholesale_price'] = $product['wholesale_price'];

     	   		$order[$key]['product_discount_amount'] = $product['product_discount'];

     	   		$order[$key]['shipping_discount'] = $product['shipping_charges_discount'];

     	   		$order[$key]['shipping_charges']  = $product['product_shipping_charge'];
     	   		array_push($order_summary,$order[$key]);
     	   		
     	   	}

     	}

     	$retailer_data = $this->RepresentativeLeadsModel->where('order_no',$order_no)
     	                                                ->with(['representative_user_details','sales_manager_details'])
     	                                                ->first();

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
		}


		$shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

		$retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';

		$retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();
     	
 	    
        //create pdf for retailer
      
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;

 		foreach ($arr_product as $key => $product_data) 
 		{ 
 			
	 			$promotion_session_data = Session::get('promotion_data');
	 			
	 			if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
	        	{

	        		$promo_discount_amount  = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt']:0;
	        		$promo_shipping_charges = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges']:1;

	        		$promo_code  = isset($promotion_session_data[$product_data['maker_id']]['promo_code'])?$promotion_session_data[$product_data['maker_id']]['promo_code']:0;

	        	
	        	}

	        	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {

	        		$sum += $product_data['wholesale_price'] - $promo_discount_amount;
	        	}
	        	else{

	        		$sum += $product_data['wholesale_price'];
	        	}
				
				
				$product_details = get_product_details( $product_data['product_id']);
	 	   		$product_name = $product_details['product_name'];

				$arr_product[$key]['unit_price']  = num_format($product_data['wholesale_price'], 2, '.', '');
				$sku_no = isset($product_data['sku'])?$product_data['sku']:"-";

				$arr_product[$key]['product_name']     = $product_name.' (SKU: '.$sku_no.')';
				$arr_product[$key]['shipping_charges'] = $product_details['shipping_charges'];

				$arr_product[$key]['shipping_type']    = $product_details['shipping_type'];

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

				$arr_product[$key]['product_discount'] = $product_data['product_discount'];
				if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
	         	{
	         		$arr_product[$key]['shipping_discount'] = 0;

	 	   			$arr_product[$key]['shipping_charges']  = 0;
	         	}
	         	else{
	         		$arr_product[$key]['shipping_discount'] = $product_data['shipping_charges_discount'];

	 	   			$arr_product[$key]['shipping_charges']  = $product_data['shipping_charges'];
	         	}
	 	   		
	 	   		array_push($order_summary,$order[$key]);
	 		// }
	 	}
 		if(isset($promo_discount_amount) && $promo_discount_amount != 0)
 		{
 			$promotion_discount = $promo_discount_amount;
 		}


  		$order = $arr_product;

  		$order_details = $this->get_rep_sales_order_details($arr_product,$promotion_session_data);
  		
  		$sno = '0';
  		$role = Sentinel::findById($user->id)->roles()->first();
  		$role = $role->name;



 	  	$pdf = PDF::loadView('representative/invoice/rep_sales_purchase_order_invoice',compact('role','order','retailer_data','user','order_no','sum','sno','total_sum','shipping_charges','promotion_discount','address_details','order_details','order_by','Role'));
 	  
       	$currentDateTime = $order_no.date('H:i:s').'.pdf';


       	$pdf_arr = 	[
    			      'PDF'           => $pdf,
            	      'PDF_FILE_NAME' => $currentDateTime
                    ];


       	/*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);

	        
        if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
        {
           $user_name = $retailer_data['representative_user_details']['first_name'].' '.$retailer_data['representative_user_details']['last_name'];
        }
        elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
        {
           $user_name = $retailer_data['sales_manager_details']['first_name'].' '.$retailer_data['sales_manager_details']['last_name'];
        }  



        //------------Send email to retailer--------------------------------------------

        $to_mail_id = isset($retailer_data['user_details']['email'])?$retailer_data['user_details']['email']:'';


        $credentials = ['email' => $to_mail_id];
      
        $arr_user = get_user_by_credentials($credentials);


        $arr_built_content = [
                                'PROJECT_NAME' =>  $arr_site_setting['site_name'],
                                'REP_NAME'     =>  $user_name,
                                'USER_ROLE'    => $Role

                             ];  

 	 	

 	 	$arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'46',$arr_user);


        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr); 


       
        // ------------Send email to admin------------------------------------------

        $admin_email = 0;

        $admin_details = $this->UserModel->where('id',1)->first();

        if(isset($admin_details))
        {
           $admin_email = $admin_details->email;
        }


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);



        $credentials = ['email' => $admin_email];


        $arr_user = get_user_by_credentials($credentials);



        $arr_built_content = [
                                'PROJECT_NAME' => $arr_site_setting['site_name'],
                                'REP_NAME'     =>  $user_name,
                                'USER_ROLE'    => $Role
                            ];  


        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'49',$arr_user);


        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);




        if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
        {
        	$to_mail_id = isset($retailer_data['representative_user_details']['email'])?$retailer_data['representative_user_details']['email']:'';
        }
        else if(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
        {
          $to_mail_id = isset($retailer_data['sales_manager_details']['email'])?$retailer_data['sales_manager_details']['email']:'';
        }
        else
        {
        	$to_mail_id = '';
        }


        $credentials = ['email' => $to_mail_id];
  
        $arr_user = get_user_by_credentials($credentials);



        $arr_built_content = [
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                             ];  


        
        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'50',$arr_user);


        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);      


    }


    //this function will use when retailer will do payment for rep/sales order
    public function rep_sales_purchase_order_mail($product_arr,$order_no,$file_to_path=false,$maker_addr=false,$retailer_data=false)
    { 
        $user = Sentinel::check();
		
		$loggedIn_userId = 0;
		$order_by = $Role = '';

		if($user)
		{
		    $loggedIn_userId = $user->id;
		} 

		// $arr_product = $product_arr;
		foreach ($product_arr as $key => $product)
		{
			$address_details = $product['address_details'];

			foreach ($product['leads_details'] as $key => $products)
			{
				$arr_product[] = $products;
			}
		}
		
		$order_no     = $order_no;
		$file_to_path = $file_to_path;
		$order_summary= $promotion_session_data = [];
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = $promotion_discount = 0;

     	if(isset($arr_product) && count($arr_product)>0)
     	{
     	   	foreach ($arr_product as $key => $product) 
     	   	{
     	   		// foreach ($products['leads_details'] as $key2 => $product) {
     	   			
	     	   		$product_details = get_product_details($product['product_id']);

	     	   		$sku_no          = isset($product['sku'])?$product['sku']:"-";

	     	   		$product_name    = $product_details['product_name'].' (SKU: '.$sku_no.')';

	     	   		$order[$key]['product_name'] = $product_name;
	     	   		$order[$key]['order_no']= $order_no or '';
	     	   		$order[$key]['item_qty']     = $product['qty'];
	     	   		$order[$key]['unit_price']   = $product['unit_wholsale_price'];
	     	   		$order[$key]['total_wholesale_price'] = $product['wholesale_price'];

	     	   		$order[$key]['product_discount_amount'] = $product['product_discount'];

	     	   		$order[$key]['shipping_discount'] = $product['shipping_charges_discount'];

	     	   		$order[$key]['shipping_charges']  = $product['product_shipping_charge'];
	     	   		array_push($order_summary,$order[$key]);
     	   		// }
     	   	}

     	}

     	$retailer_data = $this->RepresentativeLeadsModel->where('order_no',$order_no)
     	                                                ->where('id',$product_arr[0]['id'])
     	                                                ->with(['representative_user_details','sales_manager_details'])
     	                                                ->first();

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

		}


		$shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

		$retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';

		$retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();
     	

        //create pdf for retailer
      
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;

 		foreach ($arr_product as $key => $product_data) 
 		{ 
 			
	 			$promotion_session_data = Session::get('promotion_data');
	 			
	 			if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
	        	{

	        		$promo_discount_amount  = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt']:0;
	        		$promo_shipping_charges = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges']:1;

	        		$promo_code  = isset($promotion_session_data[$product_data['maker_id']]['promo_code'])?$promotion_session_data[$product_data['maker_id']]['promo_code']:0;

	        	
	        	}

	        	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {

	        		$sum += $product_data['wholesale_price'] - $promo_discount_amount;
	        	}
	        	else{

	        		$sum += $product_data['wholesale_price'];
	        	}
				
				
				$product_details = get_product_details( $product_data['product_id']);
	 	   		$product_name = $product_details['product_name'];

				$arr_product[$key]['unit_price']  = num_format($product_data['wholesale_price'], 2, '.', '');
				$sku_no = isset($product_data['sku'])?$product_data['sku']:"-";

				$arr_product[$key]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
				
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

				$arr_product[$key]['product_discount'] = $product_data['product_discount'];
				if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
	         	{
	         		$arr_product[$key]['shipping_discount'] = 0;

	 	   			$arr_product[$key]['shipping_charges']  = 0;
	         	}
	         	else{
	         		$arr_product[$key]['shipping_discount'] = $product_data['shipping_charges_discount'];

	 	   			$arr_product[$key]['shipping_charges']  = $product_data['shipping_charges'];
	         	}
	 	   		
	 	   		array_push($order_summary,$order[$key]);
	 		
	 	}
 		if(isset($promo_discount_amount) && $promo_discount_amount != 0)
 		{
 			$promotion_discount = $promo_discount_amount;
 		}


  		$order = $arr_product;

  		$order_details = $this->get_rep_sales_order_details($arr_product,$promotion_session_data,$retailer_data['maker_id']);
  		
  		$sno  = '0';
  		$role = Sentinel::findById($loggedIn_userId)->roles()->first();
  		$role = $role->name;

  		/*get_calculation data from helper function*/
  		$ordNo    = isset($order_no)?base64_encode($order_no):'';

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
  		/*end*/


 	  	$pdf = PDF::loadView('representative/invoice/retailer',compact('role','order','retailer_data','user','order_no','sum','sno','total_sum','shipping_charges','promotion_discount','address_details','order_details','order_by','Role','orderCalculationData'));

 	  
       	$currentDateTime = $order_no.date('H:i:s').'.pdf';


	    $pdf_arr = 	[
	    			   'PDF'           => $pdf,
	            	   'PDF_FILE_NAME' => $currentDateTime
	               	];


        /*Get site setting data from helper*/
        $arr_site_setting  = get_site_settings(['site_name','website_url']);           	

	
     	//------------Send email to retailer--------------------------------------------
    

        $html = 'Payment has been done for order: "'.$order_no.' ", please check attachment for further details';


        $arr_built_content = [
                               'USER_ROLE'    => 'Sir/Madam',
                               'HTML'         => $html,
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                            ];
	       	

        $to_mail_id = isset($retailer_data['user_details']['email'])?$retailer_data['user_details']['email']:'';

        $credentials = ['email' => $to_mail_id];
  
        $arr_user = get_user_by_credentials($credentials);



        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'67',$arr_user); 

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);
	
        
        // ------------Send email to admin------------------------------------------
           

        $admin_email   = 0;

        $retailer_name = $retailer_data['user_details']['first_name'].' '.$retailer_data['user_details']['last_name'];


        $admin_details = $this->UserModel->where('id',1)->first();

        if(isset($admin_details))
        {
           $admin_email = $admin_details->email;
        }

        $credentials = ['email' => $admin_email];
  
        $arr_user = get_user_by_credentials($credentials);


        $html = 'Payment has been done by retailer "'.$retailer_name.'" for order: "'.$order_no.' ", please check attachment for further details';


        $arr_built_content = [
	                           'USER_ROLE'    => 'Admin',
	                           'HTML'         => $html,
	                           'PROJECT_NAME' => $arr_site_setting['site_name']
	                        ];

        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'67',$arr_user); 

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);                    
	   

        /*--------Send Mail To reps or sales----------------------------------------------*/

        $html = 'Payment has been done by retailer "'.$retailer_name.'" for order: "'.$order_no.' ", please check attachment for further details';


 	 	if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
        {
        	$to_mail_id = isset($retailer_data['representative_user_details']['email'])?$retailer_data['representative_user_details']['email']:'';
        }
        else if(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
        {
          $to_mail_id = isset($retailer_data['sales_manager_details']['email'])?$retailer_data['sales_manager_details']['email']:'';
        }
        else
        {
        	$to_mail_id = '';
        }

        $credentials = ['email' => $to_mail_id];
  
        $arr_user  = get_user_by_credentials($credentials);


        $arr_built_content = [
                               'USER_ROLE'    => 'Sir/Madam',
                               'HTML'         => $html,
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                              
                            ];

        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'67',$arr_user); 

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);


	    /*--------------------send mail to vendor------------------------------------*/

        $html = 'Payment has been done by retailer "'.$retailer_name.'" for order: "'.$order_no.' ", please check attachment for further details';
	
 	 	$vendor_id   = isset($product_arr[0]['maker_id'])?$product_arr[0]['maker_id']:'';

        $to_mail_id  = Sentinel::findById($vendor_id)->email;

        $credentials = ['email' => $to_mail_id];
  
        $arr_user    = get_user_by_credentials($credentials);


        $arr_built_content = [
                               'USER_ROLE'    => 'Sir/Madam',
                               'HTML'         => $html,
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                            ];

        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'67',$arr_user); 

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);


    }

    public function rep_sales_purchase_order_mail_to_admin($product_arr,$order_no,$file_to_path=false,$maker_addr=false,$retailer_data=false)
    { 
    	
        $user = Sentinel::check();
		
		$loggedIn_userId = 0;
		$order_by = $Role = '';
		$rep_sales_name = "";

		if($user)
		{
		    $loggedIn_userId = $user->id;
		} 

		// $arr_product = $product_arr;
		foreach ($product_arr as $key => $product)
		{
			$address_details = $product['address_details'];

			foreach ($product['leads_details'] as $key => $products)
			{
				$arr_product[] = $products;
			}
		}
		
		$order_no     = $order_no;
		$file_to_path = $file_to_path;
		$order_summary= $promotion_session_data = [];
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = $promotion_discount = 0;

     	if(isset($arr_product) && count($arr_product)>0)
     	{
     	   	foreach ($arr_product as $key => $product) 
     	   	{
     	   		// foreach ($products['leads_details'] as $key2 => $product) {
     	   			
	     	   		$product_details = get_product_details($product['product_id']);

	     	   		$sku_no          = isset($product['sku'])?$product['sku']:"-";

	     	   		$product_name    = $product_details['product_name'].' (SKU: '.$sku_no.')';

	     	   		$order[$key]['product_name'] = $product_name;
	     	   		$order[$key]['order_no']= $order_no or '';
	     	   		$order[$key]['item_qty']     = $product['qty'];
	     	   		$order[$key]['unit_price']   = $product['unit_wholsale_price'];
	     	   		$order[$key]['total_wholesale_price'] = $product['wholesale_price'];

	     	   		$order[$key]['product_discount_amount'] = $product['product_discount'];

	     	   		$order[$key]['shipping_discount'] = $product['shipping_charges_discount'];

	     	   		$order[$key]['shipping_charges']  = $product['product_shipping_charge'];
	     	   		array_push($order_summary,$order[$key]);
     	   		// }
     	   	}

     	}

     	$retailer_data = $this->RepresentativeLeadsModel->where('order_no',$order_no)
     	                                                ->where('id',$product_arr[0]['id'])
     	                                                ->with(['representative_user_details','sales_manager_details'])
     	                                                ->first();
     	                                              
		if($retailer_data)
		{
		    $retailer_data = $retailer_data->toArray();



		    if(isset($retailer_data['representative_id']) && $retailer_data['representative_id']!=0)
			{
                 $order_by = 'representative';
                 $Role     = "Representative";
                 $rep_sales_name = $retailer_data['representative_user_details']['first_name'].' '.$retailer_data['representative_user_details']['last_name'];
			}
			elseif(isset($retailer_data['sales_manager_id']) && $retailer_data['sales_manager_id']!=0)
			{
                 $order_by = 'sales_manager';
                 $Role     = "Sales Manager";
                 $rep_sales_name = $retailer_data['sales_manager_details']['first_name'].' '.$retailer_data['sales_manager_details']['last_name'];
			}
			else
			{
			    $order_by = '';	
			}

		}


		$shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

		$retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';

		$retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();
     	

        //create pdf for retailer
      
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;

 		foreach ($arr_product as $key => $product_data) 
 		{ 
 			
	 			$promotion_session_data = Session::get('promotion_data');
	 			
	 			if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
	        	{

	        		$promo_discount_amount  = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt']:0;
	        		$promo_shipping_charges = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges']:1;

	        		$promo_code  = isset($promotion_session_data[$product_data['maker_id']]['promo_code'])?$promotion_session_data[$product_data['maker_id']]['promo_code']:0;

	        	
	        	}

	        	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {

	        		$sum += $product_data['wholesale_price'] - $promo_discount_amount;
	        	}
	        	else{

	        		$sum += $product_data['wholesale_price'];
	        	}
				
				
				$product_details = get_product_details( $product_data['product_id']);
	 	   		$product_name = $product_details['product_name'];

				$arr_product[$key]['unit_price']  = num_format($product_data['wholesale_price'], 2, '.', '');
				$sku_no = isset($product_data['sku'])?$product_data['sku']:"-";

				$arr_product[$key]['product_name'] = $product_name.' (SKU: '.$sku_no.')';
				
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

				$arr_product[$key]['product_discount'] = $product_data['product_discount'];
				if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
	         	{
	         		$arr_product[$key]['shipping_discount'] = 0;

	 	   			$arr_product[$key]['shipping_charges']  = 0;
	         	}
	         	else{
	         		$arr_product[$key]['shipping_discount'] = $product_data['shipping_charges_discount'];

	 	   			$arr_product[$key]['shipping_charges']  = $product_data['shipping_charges'];
	         	}
	 	   		
	 	   		array_push($order_summary,$order[$key]);
	 		
	 	}
 		if(isset($promo_discount_amount) && $promo_discount_amount != 0)
 		{
 			$promotion_discount = $promo_discount_amount;
 		}


  		$order = $arr_product;

  		$order_details = $this->get_rep_sales_order_details($arr_product,$promotion_session_data,$retailer_data['maker_id']);
  		
  		$sno  = '0';
  		$role = Sentinel::findById($loggedIn_userId)->roles()->first();
  		$role = $role->name;

  		/*get_calculation data from helper function*/
  		$ordNo    = isset($order_no)?base64_encode($order_no):'';

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
  		/*end*/

  		

 	  
       

        /*Get site setting data from helper*/
        $arr_site_setting  = get_site_settings(['site_name','website_url']);           	


        // ------------Send email to admin------------------------------------------
           

        $admin_email   = 0;

        $retailer_name = $retailer_data['user_details']['first_name'].' '.$retailer_data['user_details']['last_name'];

        $hide_retailer_name = 0;
 	  	$pdf = PDF::loadView('representative/invoice/rep_sales_purchase_order_invoice',compact('role','order','retailer_data','user','order_no','sum','sno','total_sum','shipping_charges','promotion_discount','address_details','order_details','order_by','Role','orderCalculationData','hide_retailer_name'));

		$currentDateTime = $order_no.date('H:i:s').'.pdf';
		$pdf_arr = 	[
		   'PDF'           => $pdf,
		   'PDF_FILE_NAME' => $currentDateTime
			];


        $admin_details = $this->UserModel->where('id',1)->first();

        if(isset($admin_details))
        {
           $admin_email = $admin_details->email;
        }

        $credentials = ['email' => $admin_email];
  
        $arr_user = get_user_by_credentials($credentials);


        //$html = 'Payment has been done by retailer "'.$retailer_name.'" for order: "'.$order_no.' ", please check attachment for further details';

        $html  = 'Order has been placed by retailer '.$retailer_name.'. Order No : '.$order_no;


        $arr_built_content = [
	                           //'USER_ROLE'    => $Role,
        						 'REP_NAME'    => $rep_sales_name,
	                           'USER_ROLE'    => $Role,
	                           'ORDER_NO'    => $order_no,
	                           'HTML'         => $html,
	                           'PROJECT_NAME' => $arr_site_setting['site_name']
	                        ];

        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'86',$arr_user); 

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);                    
	   
         /*--------------------send mail to vendor------------------------------------*/
         $shop_name = get_retailer_dummy_shop_name($retailer_data['retailer_id']);

		$retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';


        $hide_retailer_name = 1;
 	  	$pdf = PDF::loadView('representative/invoice/rep_sales_purchase_order_invoice',compact('role','order','retailer_data','user','order_no','sum','sno','total_sum','shipping_charges','promotion_discount','address_details','order_details','order_by','Role','orderCalculationData','hide_retailer_name'));


		$currentDateTime = $order_no.date('H:i:s').'.pdf';


		$pdf_arr = 	[
			'PDF'           => $pdf,
			'PDF_FILE_NAME' => $currentDateTime
		];


       $html  = 'Order has been placed by retailer '.$retailer_name.'. Order No : '.$order_no;
	
 	 	$vendor_id   = isset($product_arr[0]['maker_id'])?$product_arr[0]['maker_id']:'';

        $to_mail_id  = Sentinel::findById($vendor_id)->email;

        $credentials = ['email' => $to_mail_id];
  
        $arr_user    = get_user_by_credentials($credentials);


        $arr_built_content = [
                               'REP_NAME'    => $rep_sales_name,
	                           'USER_ROLE'    => $Role,
	                           'ORDER_NO'    => $order_no,
                               'HTML'         => $html,
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                            ];

        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'86',$arr_user); 

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);


    }



    public function vendor_order_retailer_mail($product_arr,$order_no,$file_to_path=false,$maker_addr=false,$retailer_data=false)
	{

		$user = Sentinel::check();
		
		$loggedIn_userId = 0;

		if($user)
		{
		    $loggedIn_userId = $user->id;
		} 


		$address_details = $product_arr['address_details'];
    	
		$arr_product = $product_arr['leads_details'];

		$order_no     = $order_no;
		$file_to_path = $file_to_path;
		$order_summary= $promotion_session_data = [];
		$inv_product_discount_amount = $inv_shipping_discount_amount = $price = $promotion_discount = 0;

     	if(isset($arr_product) && count($arr_product)>0)
     	{
     	   	foreach ($arr_product as $key => $product) 
     	   	{
     	   			
	     	   		$product_details = get_product_details($product['product_id']);
	     	   		$product_name = $product_details['product_name'];
	     	   		$order[$key]['product_name'] = $product_name;
	     	   		$order[$key]['order_no']= $order_no or '';
	     	   		$order[$key]['item_qty']     = $product['qty'];
	     	   		$order[$key]['unit_price']   = $product['unit_wholsale_price'];
	     	   		$order[$key]['total_wholesale_price'] = $product['wholesale_price'];

	     	   		$order[$key]['product_discount_amount'] = $product['product_discount'];

	     	   		$order[$key]['shipping_discount'] = $product['shipping_charges_discount'];

	     	   		$order[$key]['shipping_charges']  = $product['product_shipping_charge'];
	     	   		array_push($order_summary,$order[$key]);
     	   	}

     	}

     	$retailer_data = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();

		if($retailer_data)
		{
		  $retailer_data = $retailer_data->toArray();
		}
		$shop_name = get_retailer_shop_name($retailer_data['retailer_id']);

		$retailer_data['shop_name'] = isset($shop_name)?$shop_name:'';

		$retailer_data['user_details'] = $this->UserModel
                                              ->with(['retailer_details'])  
                                              ->where('id',$retailer_data['retailer_id'])
                                              ->first()
                                              ->toArray();
   

        //create pdf for retailer
      
        $sum = 0;
        $total_sum = 0;
        $shipping_charges = 0;

 		foreach ($arr_product as $key => $product_data) 
 		{ 

	 			$promotion_session_data = Session::get('promotion_data');
	 			
	 			if (isset($promotion_session_data) && count($promotion_session_data) > 0) 
	        	{

	        		$promo_discount_amount  = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['discount_amt']:0;
	        		$promo_shipping_charges = isset($promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges'])?$promotion_session_data[$product_data['maker_id']]['final_total'][$product_data['maker_id']]['shipping_charges']:1;

	        		$promo_code  = isset($promotion_session_data[$product_data['maker_id']]['promo_code'])?$promotion_session_data[$product_data['maker_id']]['promo_code']:0;

	        	
	        	}

	        	if (isset($promo_discount_amount) && $promo_discount_amount != 0) {

	        		$sum += $product_data['wholesale_price'] - $promo_discount_amount;
	        	}
	        	else{

	        		$sum += $product_data['wholesale_price'];
	        	}
				
				
				$product_details = get_product_details( $product_data['product_id']);
	 	   		$product_name = $product_details['product_name'];

				$arr_product[$key]['unit_price']  = num_format($product_data['wholesale_price'], 2, '.', '');
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

				$arr_product[$key]['product_discount'] = $product_data['product_discount'];
				if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) 
	         	{
	         		$arr_product[$key]['shipping_discount'] = 0;

	 	   			$arr_product[$key]['shipping_charges']  = 0;
	         	}
	         	else{
	         		$arr_product[$key]['shipping_discount'] = $product_data['shipping_charges_discount'];

	 	   			$arr_product[$key]['shipping_charges']  = $product_data['shipping_charges'];
	         	}
	 	   		
	 	   		array_push($order_summary,$order[$key]);
	 	}
 		if(isset($promo_discount_amount) && $promo_discount_amount != 0)
 		{
 			$promotion_discount = $promo_discount_amount;
 		}


        
  		$order = $arr_product;
  		
  		$sno  = '0';
  		$role = Sentinel::findById($loggedIn_userId)->roles()->first();
  		$role = $role->name;

  		/*get_calculation data from helper function*/
  		$ordNo    = isset($order_no)?base64_encode($order_no):'';

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
  		/*end*/
	  		
 	  	$pdf = PDF::loadView('representative/invoice/retailer',compact('role','order','retailer_data','user','order_no','sum','sno','total_sum','shipping_charges','promotion_discount','address_details','orderCalculationData'));
 	  	
 	  
       	$currentDateTime = $order_no.date('H:i:s').'.pdf';

	    $pdf_arr = 	[
    				  'PDF'           => $pdf,
            		  'PDF_FILE_NAME' => $currentDateTime
               	    ];


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);    


        $to_mail_id = isset($retailer_data['user_details']['email'])?$retailer_data['user_details']['email']:'';

        $credentials = ['email' => $to_mail_id];
      
        $arr_user = get_user_by_credentials($credentials); 



        $arr_built_content = [
                               'PROJECT_NAME' => $arr_site_setting['site_name'],
                               'REP_NAME'     => $user->first_name.' '.$user->last_name,
                               'USER_ROLE'    => $role
                            ];  


        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'46',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);                    	    
		

        // ---------------Send email to admin------------------------------------------

        $admin_email = 0;

        $admin_details = $this->UserModel->where('id',1)->first();

        if(isset($admin_details))
        {
           $admin_email = $admin_details->email;
        }


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);


        $credentials = ['email' => $admin_email];
  
        $arr_user = get_user_by_credentials($credentials);


        $arr_built_content = [
                                'PROJECT_NAME' => $arr_site_setting['site_name'],
                                'REP_NAME'     => $user->first_name.' '.$user->last_name,
                                'USER_ROLE'    => $role
                            ];  


        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'49',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

	
     	 	
        /*-------------Send Mail To reps or sales--------------------------------*/


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);


        $to_mail_id = isset($user->email)?$user->email:'';

        $credentials = ['email' => $to_mail_id];
  
        $arr_user = get_user_by_credentials($credentials);


        $arr_built_content = [
                               'PROJECT_NAME' => $arr_site_setting['site_name']
                             ];  


        
        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'50',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);                         

	}


	function get_rep_sales_order_details($product_arr,$promotion_arr,$vendor_id=false)
	{ 
	      $final_total = []; $total_discount_amount = 0;
	  
	      if(isset($product_arr) && count($product_arr)>0)
	      {
	          $total_amount = $ordSubTotal = $ordProductDiscout = $ordShipCharge = $ordShipDiscout =$orderShippingCharge = 0;

	          foreach ($product_arr as $key=>$product) 
	          {  
	              $orderShippingCharge += $product['product_shipping_charge'] - $product['shipping_charges_discount'];

	              $subTotal           = $product['qty'] * $product['unit_wholsale_price'];
	              $total_amount       += $subTotal + $product['product_shipping_charge'] - $product['shipping_charges_discount'] - $product['product_discount'];
	              $ordSubTotal        += $subTotal;
	              $ordProductDiscout  += $product['product_discount'];
	              $ordShipCharge      += $product['product_shipping_charge'];
	              $ordShipDiscout     += $product['shipping_charges_discount'];
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
	 
	 

	              if(isset($vendor_id) && $vendor_id!=false && $promoKey == $vendor_id)
	              { 
	                 $promo_discount_amt = isset($promotion['final_total'][$promoKey]['discount_amt'])?$promotion['final_total'][$promoKey]['discount_amt']:0;
	              }
	              else
	              {
	                
	                $promo_discount_amt += isset($promotion['final_total'][$promoKey]['discount_amt'])?$promotion['final_total'][$promoKey]['discount_amt']:0;
	              }
	           
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
}

?>