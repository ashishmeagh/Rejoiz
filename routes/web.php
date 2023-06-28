<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/ 


Route::get("cache_clear",function()
{
	\Artisan::call("config:clear");
	\Artisan::call("cache:clear");
	\Artisan::call("view:clear");
	dd('Cache clear......!');
});

Route::get('/loaderio-de6af64e4eaac1754a0bca257ec4844a',function()
{
	dd("ok");
});
$admin_path          = config('app.project.admin_panel_slug');
$maker_path          = config('app.project.maker_panel_slug');
$retailer_path       = config('app.project.retailer_panel_slug');
$representative_path = config('app.project.representative_panel_slug');
$customer_path 		 = config('app.project.customer_panel_slug');
$influencer_path     = config('app.project.influencer_panel_slug');


Route::group(['middleware' => ['web']], function ()  use($admin_path) 
{
	/*-----------------------------FRONT START----------------------*/
	Route::group(['middleware'=>['front']],function()
	{	

		Route::get('reindex_products',['as' => 'reindex_products', 'uses' => 'Front\SearchController@reindex_products']);

		Route::get('test_reindex_products',['as' => 'test_reindex_products', 'uses' => 'Front\SearchController@test_reindex_products']);

		Route::get('flush_index_products',['as' => 'flush_index_products', 'uses' => 'Front\SearchController@flush_index_products']);

		Route::get('resync_product_index',['as' => 'user_home_manage', 'uses' => 'Admin\ProductController@resync_product_index']);

		Route::get('test_curl_call',['as' => 'test_curl_call', 'uses' => 'Front\SearchController@test_curl_call']);

		

	
		Route::get('/',['as' => 'user_home_manage', 'uses' => 'Front\HomeController@index']);
		Route::get('/img_zoom_demo',['as' => 'user_home_manage', 'uses' => 'Front\HomeController@img_zoom_demo']);
		
		Route::get('/is_login_update',['as' => 'is_login_update', 'uses' => 'Front\HomeController@is_login_update']);

		Route::get('/login/{param?}',['as' => 'login', 'uses' => 'Front\AuthController@login']);
		
		/*Remove Unwanted images from folder*/
		$module_controller = "Common\RemoveUnwantedImagesController@";
		Route::get('remove_banner_images',['as' => 'remove_banner_unwanted_images', 'uses' => $module_controller.'remove_banner_images']);
		Route::get('remove_brand_images',['as' => 'remove_branad_unwanted_images', 'uses' => $module_controller.'remove_brand_images']);
		Route::get('remove_category_images',['as' => 'remove_category_unwanted_images', 'uses' => $module_controller.'remove_category_images']);
		Route::get('remove_product_images',['as' => 'remove_product_unwanted_images', 'uses' => $module_controller.'remove_product_images']);
		Route::get('remove_product_thumb_images',['as' => 'remove_product_thumb_unwanted_images', 'uses' => $module_controller.'remove_product_thumb_images']);
		Route::get('remove_profile_images',['as' => 'remove_profile_unwanted_images', 'uses' => $module_controller.'remove_profile_images']);
		Route::get('remove_site_logo',['as' => 'remove_site_logo_unwanted_images', 'uses' => $module_controller.'remove_site_logo']);
		Route::get('remove_store_images',['as' => 'remove_store_unwanted_images', 'uses' => $module_controller.'remove_store_images']);


		/*---------------Start FAQs--------------*/
		Route::group(array('prefix' => '/faq'), function()
		{
			$route_slug        = "faqs";
			$module_controller = "Front\FaqController@";

			Route::get('/', ['as' => $route_slug.'faq', 'uses' =>$module_controller.'index']);
			Route::get('/{slug}', ['as' => $route_slug.'faq', 'uses' =>$module_controller.'faq_list']);
			Route::get('/{slug}/{enc_id}', ['as' => $route_slug.'faq', 'uses' =>$module_controller.'question_details']);
			Route::post('search_faq', ['as' => $route_slug.'faq', 'uses' =>$module_controller.'search']);

		});
		
		/*---------------End FAQs--------------*/

		Route::get('/signup_customer/{param?}',['as' => 'login', 'uses' => 'Front\AuthController@signup_customer']);
		Route::get('/signup_influencer',['as' => 'signup_influencer', 'uses' => 'Front\AuthController@signup_influencer']);

		Route::get('/signup_retailer/{param?}',['as' => 'login', 'uses' => 'Front\AuthController@signup_retailer']);

		Route::get('/signup',['as' => 'signup', 'uses' => 'Front\AuthController@signup']);
		Route::post('/process_signup',['as' => 'process_signup', 'uses' => 'Front\AuthController@process_signup']);
		Route::post('/process_login',['as' => 'process_signup', 'uses' => 'Front\AuthController@process_login']);

		Route::post('/visitors_enquiry',['as' => 'visitors_enquiry', 'uses' => 'Front\AuthController@visitors_enquiry']);

		Route::any('does_exists_tax_id/{param?}',['uses' => 'Front\AuthController@does_exists_tax_id']);
			
		Route::any('does_company_exist/{param?}',['uses' => 'Front\AuthController@does_company_exist']);
		
		Route::any('does_influencer_exist/{param?}',['uses' => 'Front\AuthController@does_influencer_exist']);

		Route::any('does_real_company_exist/{param?}',['uses' => 'Front\AuthController@does_real_company_exist']);	
		
		Route::get('/activation_complete/{user_id}/{activation_code}',['as' => 'process_signup', 'uses' => 'Front\AuthController@activation_complete']);	

		Route::any('/forgot_password',['as' => 'forgot_password', 'uses' => 'Front\AuthController@forgot_password']);

		Route::any('/reset_password/{code}',['as' => 'reset_password', 'uses' => 'Front\AuthController@reset_password']);

		Route::post('/process_reset_password',['as' => 'reset_password', 'uses' => 'Front\AuthController@process_reset_password']);

		Route::get('/page/{page_slug}',	['as'	=>'cms_page',
										 'uses'  => 'Front\HomeController@cms_page']);

		Route::get('/blockchain_platform',	['as'	=>'blockchain_platform',
										 'uses'  => 'Front\HomeController@blockchain_platform']);

		Route::get('/about',	['as'	=>'about',
										 'uses'  => 'Front\HomeController@about_us']);


		Route::post('/checkVendorStatus',	['as'=>'checkVendorStatus',
										 'uses'  => 'Front\HomeController@checkVendorStatus']);


		Route::post('/subscribe',	['as'	=>'subscribe',
										 'uses'  => 'Front\HomeController@subscribe']);

		Route::get('/promotions/{area_id?}/{category_id?}',['as'	  =>'get_promotions',
										                  'uses'  => 'Front\HomeController@get_promotions']);

		Route::get('/sales_manager_details/{sales_manager_id}',['as'	=>'sales_manager_details',
										     'uses'  => 'Front\HomeController@sales_manager_details']);

		Route::get('/find_rep/{area_id?}/{Category_id?}',['as' => 'find_rep' , 'uses' => 'Front\HomeController@find_rep']);

		Route::get('/rep_details/{rep_id}',['as' => 'rep_details','uses' => 'Front\HomeController@rep_details']);
		
		Route::get('/get_all_vendors/{area_id?}/{category_id?}',['as' => 'get_all_vendors','uses' => 'Front\HomeController@get_all_vendors']);

		Route::get('/search_vendor_from_cat/{area_id?}/{category_id?}/{category_div_id?}',['as' => 'search_vendor','uses' => 'Front\HomeController@search_vendor']);

		Route::any('/search_representative',['as' => 'search_representative','uses' => 'Front\HomeController@searchRepresentative']);

		Route::get('/getCategoryDivision',['as' => 'getCategoryDivision','uses' => 'Front\HomeController@getCategoryDivision']);
		
		Route::any('/daily_popup',['as' => 'daily_popup','uses' => 'Front\HomeController@daily_popup']);

		//Searching front...
		Route::any('/search_db',	['as'	=>'search_db',
											 'uses'  => 'Front\SearchController@search_db']);
		
		Route::any('/search/{search_term?}',	['as'	=>'search',
											 'uses'  => 'Front\SearchController@search']);
			
		Route::any('filter/{price?}/{minimum?}/{specials?}',	['as'	=>'filter',
											 'uses'  => 'Front\SearchController@filter']);

		Route::any('/products',	['as'	=>'products',
											 'uses'  => 'Front\SearchController@product_list']);
		Route::any('/maker',	['as'	=>'maker',
											 'uses'  => 'Front\SearchController@maker_list']);


		Route::any('/representative-details',	['as'	 =>'representative-details',
											     'uses'  => 'Front\SearchController@representative_details']);

		Route::any('/set_guest_url', ['as'=>'set_guest_url', 'uses'  => 'Front\SearchController@set_guest_url']);

	    
	    Route::get('/search_vendor/{letter?}',['as'=>'search_vendor','uses'=>'Front\SearchController@search_vendor']);
	    

	    Route::get('/catlogs',['as'=>'catlogs','uses'=>'Front\SearchController@catlogs']);


		/*---------------------START My Bag--------------------------------------------------------*/		
		
		// Route Slug and Controller for Retailer
		
		Route::group(array('prefix' => '/my_bag'), function()
		{	

			$route_slug        			= "my_bag";
			$module_controller 			= "Front\BagController@";

			Route::get('/', ['as' => $route_slug.'my_bag', 'uses' =>$module_controller.'my_bag']);	

			
			Route::post('/add', ['as' => $route_slug.'add_to_bag', 'uses' =>$module_controller.'add_to_bag']);	
			
			Route::get('/delete_product/{enc_prod_id}', ['as' => $route_slug.'delete_product', 'uses' =>$module_controller.'delete_product']);	

			Route::post('/save_bag', ['as' => $route_slug.'save_bag', 'uses' =>$module_controller.'save_bag']);

			Route::post('/apply_promo_code', ['as' => $route_slug.'apply_promo_code', 'uses' =>$module_controller.'apply_promo_code']);

			Route::get('/clear_promo_code', ['as' => $route_slug.'clear_promo_code', 'uses' =>$module_controller.'clear_promo_code']);	

			Route::post('/temp_save_bag', ['as' => $route_slug.'temp_save_bag', 'uses' =>$module_controller.'temp_save_bag']);	

			Route::any('/empty_cart', ['as' => $route_slug.'empty_cart', 'uses' =>$module_controller.'empty_cart']);	

			Route::any('/update_qty', ['as' => $route_slug.'update_qty', 'uses' =>$module_controller.'update_qty']);

			Route::any('/generate_reorder_data/{order_id?}/{vendor_id?}/{is_lead?}', ['as' => $route_slug.'generate_reorder_data', 'uses' =>$module_controller.'generate_reorder_data']);

			/*route for checking available size 14-10-21*/
			Route::post('/check_size_inventory', ['uses' => $module_controller.'check_size_inventory']);		
			/*----------*/
		});	

		// Route Slug and Controller for Customer
		
		Route::group(array('prefix' => '/customer_my_bag'), function()
		{	

			$route_slug     	 = "customer_my_bag";
			$module_controller	 = "Front\CustomerBagController@";


			Route::get('/', ['as' => $route_slug.'my_bag', 'uses' =>$module_controller.'my_bag']);	
			
			Route::post('/add', ['as' => $route_slug.'add_to_bag', 'uses' =>$module_controller.'add_to_bag']);	
			
			Route::get('/delete_product/{enc_prod_id}', ['as' => $route_slug.'delete_product', 'uses' =>$module_controller.'delete_product']);	

			Route::post('/save_bag', ['as' => $route_slug.'save_bag', 'uses' =>$module_controller.'save_bag']);

			Route::post('/apply_promo_code', ['as' => $route_slug.'apply_promo_code', 'uses' =>$module_controller.'apply_promo_code']);

			Route::get('/clear_promo_code', ['as' => $route_slug.'clear_promo_code', 'uses' =>$module_controller.'clear_promo_code']);	

			Route::post('/temp_save_bag', ['as' => $route_slug.'temp_save_bag', 'uses' =>$module_controller.'temp_save_bag']);	

			Route::any('/empty_cart', ['as' => $route_slug.'empty_cart', 'uses' =>$module_controller.'empty_cart']);	

			Route::any('/update_qty', ['as' => $route_slug.'update_qty', 'uses' =>$module_controller.'update_qty']);

			Route::any('/generate_reorder_data/{order_id?}/{vendor_id?}/{is_lead?}', ['as' => $route_slug.'generate_reorder_data', 'uses' =>$module_controller.'generate_reorder_data']);		
			
		});	
		/*---------END My Bag -----------------------------------------------*/	


		/* ---------------------  payments routes  -----------------------------------------*/
	
		// Retailer Slug and controller
		$route_slug       = "retailer_payment_";
		$module_controller = "Front\PaymentController@";	


		// Customer Slug and controller
		$route_slug_customer       = "retailer_payment_";
		$module_controller_customer = "Front\CustomerPaymentController@";	
		
		Route::get('/checkout/{role?}/{order_no?}/{maker_id?}',['as'=> $route_slug.'checkout',	 'uses' => $module_controller.'checkout']);

		Route::get('/customer_checkout/{role?}/{order_no?}/{maker_id?}',['as'=> $route_slug_customer.'checkout',	 'uses' => $module_controller_customer.'checkout']);

		Route::get('/email_view',['as'=> $route_slug.'email_view',	 'uses' => $module_controller.'email_view']);	
		
		Route::post('/set_session',['as'=> $route_slug.'set_session',	 'uses' => $module_controller.'set_session']);	
		
		Route::post('/net_30_payment',['as'=> $route_slug.'net_30_payment',	 'uses' => $module_controller.'net_30_payment']);

		Route::post('pay_with_card', ['as' => $route_slug.'.paymoney','uses' => $module_controller.'postPaymentWithStripe']);

		Route::post('buy', ['as' => $route_slug.'.buy','uses' => $module_controller.'buy_cart_items']);

		Route::post('customer_buy', ['as' => $route_slug_customer.'.buy','uses' => $module_controller_customer.'buy_cart_items']);

		Route::post('get_card', ['as' => $route_slug.'.get_card','uses' => $module_controller.'get_card']);

		Route::post('net_payment',['as'=>$route_slug.'.net_30','uses'=>$module_controller.'net_payment']);

        Route::get('temp',['as'=>$route_slug.'.temp','uses'=>$module_controller.'temp']);

	});

	

	/*________________________Amit END_____________________________*/

	/*START Common Routes*/
	$route_slug        = "auth";
	$module_controller = "CommonController@";

	Route::get('/get_sub_categories/{enc_id}',['as' => 'get_sub_categories',	 
											'uses' => $module_controller.'get_sub_categories']);

	Route::get('/get_third_sub_categories/{enc_id}',['as' => 'get_third_sub_categories',	 
											'uses' => $module_controller.'get_third_sub_categories']);

	Route::get('/get_fourth_sub_categories/{enc_id}',['as' => 'get_fourth_sub_categories',	 
											'uses' => $module_controller.'get_fourth_sub_categories']);

	Route::get('/notifications',['as' => 'notifications',	 
											'uses' => $module_controller.'notifications']);
											
	

	Route::any('/notifications_update_status',['as' => 'update_noti_id',	 
											'uses' => $module_controller.'notifications_update_status']);	

	Route::any('/update_user_active_time',['as' => 'update_user_active_time',	 
										 'uses' => $module_controller.'update_user_active_time']);	

	Route::get('/check_product_inventory',['as' => 'check_product_inventory',	 
											'uses' => $module_controller.'check_product_inventory']);

	Route::get('/test_email',['as' => 'test_email',	 
											'uses' => $module_controller.'test_email']);

	/*-------------------------Admin START---------------------------------*/		
	Route::group(['prefix' => $admin_path,'middleware'=>['admin']], function ()
	{
		
		/*-------------------------Auth START-----------------------------------------*/

		$route_slug        = "auth";
		$module_controller = "Admin\AuthController@";
		
		Route::get('/',                       ['as'=>$route_slug.'login',          'uses'=>$module_controller.'login']);	
		Route::get('login',                   ['as'=>$route_slug.'login',          'uses'=>$module_controller.'login']);
		Route::post('process_login',          ['as'=>$route_slug.'process_login',  'uses'=>$module_controller.'process_login']);
		Route::post('process_forgot_password',['as'=>$route_slug.'forgot_password','uses'=>$module_controller.'process_forgot_password']);
		Route::get('validate_admin_reset_password_link/{enc_id}/{enc_reminder_code}', 	['as'=>$route_slug.'validate_admin_reset_password_link', 'uses' => $module_controller.'validate_reset_password_link']);
		Route::post('reset_password',         ['as'=>$route_slug.'reset_passsword','uses'=>$module_controller.'reset_password']);
		Route::get('/logout',                 ['as'=>$route_slug.'logout',   'uses'=>$module_controller.'logout']);
		Route::get('change_password',         ['as' => $route_slug.'change_password', 'uses' => $module_controller.'change_password']);
		Route::post('update_password',        ['as' => $route_slug.'update_password', 'uses' => $module_controller.'update_password']);
		Route::get('/dashboard',              ['as'=>$route_slug.'dashboard','uses'=>'Admin\DashboardController@index']);

		Route::get('/b2c_privacy_settings',              ['as'=>$route_slug.'b2c_privacy_settings','uses'=>'Admin\B2CPrivacySettingController@index']);

		Route::post('/b2c_privacy_settings/update_is_influencer_module_on',              ['as'=>$route_slug.'update_is_influencer_module_on','uses'=>'Admin\B2CPrivacySettingController@update_is_influencer_module_on']);

		Route::post('/b2c_privacy_settings/update_is_b2c_module_on',              ['as'=>$route_slug.'update_is_b2c_module_on','uses'=>'Admin\B2CPrivacySettingController@update_is_b2c_module_on']);


		// Route::get('/commission_reports', [ 'uses' => 'Admin\CommissionReportsController@index']);
		
		Route::group(array('prefix' => 'commission_reports'), function()
		{
			$route_slug        = "commission_reports";
			$module_controller = "Admin\CommissionReportsController@";

			
			Route::any('/',['as'=>$route_slug.'index','uses'=>$module_controller.'index']);

			Route::any('/search_report',['as'=>$route_slug.'search_report','uses'=>$module_controller.'get_commission_reports']);


			Route::any('/all_report',['as'=>$route_slug.'all_report','uses'=>$module_controller.'test']);

			Route::any('all_reports/{role}',['as'=>$route_slug.'get_commission_reports','uses'=>$module_controller.'get_commission_reports']);

			Route::any('report_generator',['as'=>$route_slug.'report_generator','uses'=>$module_controller.'report_generator']);

			Route::get('report_details/{id}/{type?}/{userId?}',['as'=>$route_slug.'get_single_transaction_details','uses'=>$module_controller.'get_order_data']);

			Route::post('load_bulkPaymentModelData', ['as' => $route_slug.'load_bulkPaymentModelData',   'uses' => $module_controller.'load_bulkPaymentModelData']);
		});

		Route::group(array('prefix' => 'customer_commission_reports'), function()
		{
			$route_slug        = "customer_commission_reports";
			$module_controller = "Admin\CustomerCommissionReportsController@";

			
			Route::any('/direct_payment',['as'=>$route_slug.'direct_payment','uses'=>$module_controller.'direct_payment']);

			Route::any('/payment_intermediation',['as'=>$route_slug.'payment_intermediation','uses'=>$module_controller.'payment_intermediation']);

			Route::any('/search_report',['as'=>$route_slug.'search_report','uses'=>$module_controller.'get_commission_reports']);

			Route::any('/indirect_payment_search_report',['as'=>$route_slug.'indirect_payment_search_report','uses'=>$module_controller.'get_indirect_payment_commission_reports']);

			Route::any('/all_report',['as'=>$route_slug.'all_report','uses'=>$module_controller.'test']);


			Route::any('all_reports/{role}',['as'=>$route_slug.'get_commission_reports','uses'=>$module_controller.'get_commission_reports']);

			Route::any('report_generator',['as'=>$route_slug.'report_generator','uses'=>$module_controller.'report_generator']);

			Route::any('intermidiate_report_generator',['as'=>$route_slug.'intermidiate_report_generator','uses'=>$module_controller.'intermidiate_report_generator']);

			Route::get('report_details/{id}/{type?}/{userId?}',['as'=>$route_slug.'get_single_transaction_details','uses'=>$module_controller.'get_order_data']);	

			Route::post('load_bulkPaymentModelData', ['as' => $route_slug.'load_bulkPaymentModelData',   'uses' => $module_controller.'load_bulkPaymentModelData']);		

		});

		Route::group(array('prefix' => 'direct_payment_to_vendor'), function()
		{
			$route_slug        = "direct_payment_to_vendor";
			$module_controller = "Admin\VendorCommissionReportsController@";

			
			Route::any('/',['as'=>$route_slug.'index','uses'=>$module_controller.'index']);

			Route::any('/search_report',['as'=>$route_slug.'search_report','uses'=>$module_controller.'get_commission_reports']);


			Route::any('/all_report',['as'=>$route_slug.'all_report','uses'=>$module_controller.'test']);


			Route::any('all_reports/{role}',['as'=>$route_slug.'get_commission_reports','uses'=>$module_controller.'get_commission_reports']);

			Route::any('report_generator',['as'=>$route_slug.'report_generator','uses'=>$module_controller.'report_generator']);
			

			Route::any('/vendor_commission_report_generator',['as'=>$route_slug.'vendor_commission_report_generator','uses'=>$module_controller.'vendor_commission_report_generator']);

			

			Route::any('/vendor_commission_invoice_generator/{order_no}',['as'=>$route_slug.'vendor_commission_invoice_generator','uses'=>$module_controller.'vendor_commission_invoice_generator']);


			Route::get('report_details/{id}/{type?}/{userId?}',['as'=>$route_slug.'get_single_transaction_details','uses'=>$module_controller.'get_order_data']);

		});



		Route::group(array('prefix' => 'admin_commission_reports'), function()
		{
			$route_slug        = "admin_commission_reports";
			$module_controller = "Admin\VendorCommissionReportsController@";

			Route::any('/',['as'=>$route_slug.'show_admin_commission','uses'=>$module_controller.'show_admin_commission']);

			Route::any('/admin_commission_reports',['as'=>$route_slug.'admin_commission_reports','uses'=>$module_controller.'admin_commission_reports']);

			Route::any('/search_report',['as'=>$route_slug.'search_report','uses'=>$module_controller.'get_commission_reports']);


			Route::any('/all_report',['as'=>$route_slug.'all_report','uses'=>$module_controller.'test']);


			Route::any('all_reports/{role}',['as'=>$route_slug.'get_commission_reports','uses'=>$module_controller.'get_commission_reports']);

			Route::any('report_generator',['as'=>$route_slug.'report_generator','uses'=>$module_controller.'report_generator']);
			

			Route::any('/vendor_commission_report_generator',['as'=>$route_slug.'vendor_commission_report_generator','uses'=>$module_controller.'vendor_commission_report_generator']);

			

			Route::any('/admin_commission_invoice_generator/{order_no}/{order_type?}',['as'=>$route_slug.'admin_commission_invoice_generator','uses'=>$module_controller.'admin_commission_invoice_generator']);

			Route::post('/admin_commission_invoice_generator_bulk',['as'=>$route_slug.'admin_commission_invoice_generator_bulk','uses'=>$module_controller.'admin_commission_invoice_generator_bulk']); 


			Route::get('report_details/{id}/{type?}/{userId?}',['as'=>$route_slug.'get_single_transaction_details','uses'=>$module_controller.'get_order_data']);

		});



		Route::group(array('prefix' => 'vendor_commission_reports'), function()
		{
			$route_slug        = "vendor_commission_reports";
			$module_controller = "Admin\VendorCommissionReportsController@";
	
			Route::any('/',['as'=>$route_slug.'show_vendor_commission','uses'=>$module_controller.'show_vendor_commission']);

			Route::any('/vendor_commission_reports',['as'=>$route_slug.'vendor_commission_reports','uses'=>$module_controller.'vendor_commission_reports']);

		});

		Route::group(array('prefix' => 'all_orders_report'), function()
		{
			$route_slug        = "all_orders_report";
			$module_controller = "Admin\VendorsAccountsPayableController@";
	
			Route::any('/',['as'=>$route_slug.'index','uses'=>$module_controller.'index']);
         	Route::any('/get_all_orders',['as'=>$route_slug.'get_all_orders','uses'=>$module_controller.'getAllOrders']);

		});

		/*-------------------------START  Category----------------------------*/
        Route::group(['prefix' =>'categories'], function ()
	    {
	    	$route_slug = 'categories';
	        $module_controller = "Admin\CategoryController@";
	        
			Route::get('/', ['as' => $route_slug.'categories', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);
			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);
			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_category', ['as' => $route_slug.'get_all_category', 'uses' => $module_controller.'get_all_category']);		
        });

        /*-------------------------START request Category----------------------------*/
        Route::group(['prefix' =>'request_categories'], function ()
	    {
	    	$route_slug = 'request_categories';
	        $module_controller = "Admin\RequestCategoryController@";
	        
			Route::get('/', ['as' => $route_slug.'categories', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);
			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::any('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);
			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_request_category', ['as' => $route_slug.'get_all_request_category', 'uses' => $module_controller.'get_all_request_category']);		
        });

        /*-------------------------START request sub Category----------------------------*/
        Route::group(['prefix' =>'request_sub_categories'], function ()
	    {
	    	$route_slug = 'request_sub_categories';
	        $module_controller = "Admin\RequestSubCategoryController@";
	        
			Route::get('/', ['as' => $route_slug.'categories', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);
			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::any('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);
			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_request_sub_category', ['as' => $route_slug.'get_all_request_sub_category', 'uses' => $module_controller.'get_all_request_sub_category']);		
        });

        /*-------------------------START request third sub Category----------------------------*/
        Route::group(['prefix' =>'request_third_sub_categories'], function ()
	    {
	    	$route_slug = 'request_third_sub_categories';
	        $module_controller = "Admin\RequestThirdSubCategoryController@";
	        
			Route::get('/', ['as' => $route_slug.'categories', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);
			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::any('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);
			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_request_sub_category', ['as' => $route_slug.'get_all_request_sub_category', 'uses' => $module_controller.'get_all_request_sub_category']);		
        });


         /*-------------------------START request third sub Category----------------------------*/
        Route::group(['prefix' =>'request_fourth_sub_categories'], function ()
	    {
	    	$route_slug = 'request_fourth_sub_categories';
	        $module_controller = "Admin\RequestFourthSubCategoryController@";
	        
			Route::get('/', ['as' => $route_slug.'categories', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);
			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::any('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);
			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_request_sub_category', ['as' => $route_slug.'get_all_request_sub_category', 'uses' => $module_controller.'get_all_request_sub_category']);		
        });


        /*-------------------------START fourth sub Category----------------------------*/
        Route::group(['prefix' =>'fourth_sub_category'], function ()
	    {
	    	$route_slug = 'fourth_sub_categories';
	        $module_controller = "Admin\FourthSubCategoryController@";
	        
			Route::get('/', ['as' => $route_slug.'categories', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::post('/store', ['as' => $route_slug.'save', 'uses' => $module_controller.'store']);
			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::any('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);
			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_request_sub_category', ['as' => $route_slug.'get_all_request_sub_category', 'uses' => $module_controller.'get_all_request_sub_category']);	
            Route::get('get_categories/{third_sub_category_id}', ['as' => $route_slug.'get_categories', 'uses' => $module_controller.'getCategories']);	
             Route::get('get_sub_categories/{third_sub_category_id}', ['as' => $route_slug.'get_sub_categories', 'uses' => $module_controller.'getSubCategories']);	

              Route::get('/get_all_subcategory', ['as' => $route_slug.'get_all_subcategory', 'uses' => $module_controller.'get_all_subcategory']);		
        });
        
        
        /*-------------------------START Payment Cart--------------------------------*/		
	Route::group(array('prefix' => '/card'), function()
	{
		$route_slug        = "card";
		$module_controller = "Admin\CardController@";

		Route::get('/',    ['as' => $route_slug.'index',   'uses' => $module_controller.'index']);
		Route::get('/add',  ['as' => $route_slug.'add',   'uses' => $module_controller.'add']);


		Route::get('/edit/{enc_id}/{cus_id}',  ['as' => $route_slug.'edit',    'uses' => $module_controller.'edit']);

		Route::post('/update',                 ['as' => $route_slug.'update',  'uses' => $module_controller.'update']);

		Route::get('/delete/{enc_id}/{cus_id}',                  ['as' => $route_slug.'delete_card',   'uses' => $module_controller.'delete_card']);


		Route::post('store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);
	});
 
        Route::group(['prefix'=>'transactions'],function () 
	    {
	    	$module_controller = "Admin\TransactionTableController@";
	        
	         Route::any('/get_transaction_details',['uses' => $module_controller.'get_transaction_details']);
	       
	         Route::any('/get_transaction/{type}',['uses' => $module_controller.'transactions']);

	         Route::any('/show_transaction_details',['uses' => $module_controller.'show_transaction_details']);
	         
	         Route::any('/all',['uses' => $module_controller.'all_transaction_details']);

	         Route::any('/all_orders',['uses' => $module_controller.'all_transaction_details']);

	         Route::any('/all_transaction',['uses' => $module_controller.'all_commission_transaction']);
	         
	         Route::any('/vendor',['uses' => $module_controller.'vendor_transaction_details']);

	         Route::any('/admin',['uses' => $module_controller.'admin_transaction_details']);

	         Route::any('/customer',['uses' => $module_controller.'customer_transaction_details']);

	         Route::get('/get_customer_transaction_details',['uses' => $module_controller.'get_customer_transaction_details']);
	         
	         Route::any('/representative',['uses' => $module_controller.'reps_transaction_details']);

	         Route::get('/sales_manager',['uses' => $module_controller.'sales_manager_transaction_details']);

	         Route::any('/influencer',['uses' => $module_controller.'influencer_transaction_details']);

	         Route::get('/get_influencer_transaction_details',['uses' => $module_controller.'get_influencer_transaction_details']);
		});

        /*--------------------------------------------------------------------------------*/

        Route::group(['prefix'=>'refund'],function () 
	    {
	    	$module_controller = "Admin\TransactionTableController@";

	    	Route::get('retailer',['uses'     => $module_controller.'retailer_refunds']);
	    	Route::get('retailer/all',['uses' => $module_controller.'get_retailer_refund_details']);
	    	Route::get('retailer/get_export_retailer_refund',['uses' => $module_controller.'get_export_retailer_refund']);

	    	Route::get('rep_sales',['uses' => $module_controller.'rep_sales_refunds']);
	    	Route::get('rep_sales/all',['uses' => $module_controller.'get_rep_sales_refund_details']);
	    	Route::get('rep_sales/get_export_reps_sales_refund',['uses' => $module_controller.'get_export_reps_sales_refund']);

	    	Route::get('customer',['uses'     => $module_controller.'customer_refunds']);
	    	Route::get('customer/all',['uses' => $module_controller.'get_customer_refund_details']);
	    	Route::get('customer/get_export_customer_refund',['uses' => $module_controller.'get_export_customer_refund']);
	    });

        /*--------------------------------------------------------------------------------*/

        /* ---------- Vendor Payment Route for Orders From Retailer ---------- */
		Route::group(['prefix'=>'payment'],function () 
	    {
	    	$module_controller = "Admin\PaymentController@";
	        
	        Route::post('/vendor',['uses' => $module_controller.'pay_to_vendor']);   

	        Route::any('/payment_received/{transaction_id}',['uses' => $module_controller.'payment_received']);    

	        Route::get('/{transaction_id}/{slug_chk_qty_available_or_not?}', 	 [
									          'uses'=>$module_controller.'payment']);  

	         Route::post('/payment_proof/{transaction_id}/', 	 [
									          'uses'=>$module_controller.'payment_proof']);  
	       
		});


        /* ---------- Vendor Payment Route for Orders From Customer ---------- */
		
		Route::group(['prefix'=>'customer_payment'],function () 
	    {
	    	$module_controller = "Admin\CustomerPaymentController@";
	        
	        Route::post('/vendor',['uses' => $module_controller.'pay_to_vendor']);    

		});


         /*-------------------------group for Sub Category-------------------------------*/

        Route::group(['prefix'=>'sub_category'],function()
        {
            $route_slug = 'sub_category';
            $module_controller = "Admin\SubCategoryController@";

            Route::get('/', ['as' => $route_slug.'sub_category', 'uses' => $module_controller.'index']);

            Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

            Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

            Route::post('/store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);

            Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

            Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

			Route::get('/get_all_subcategory', ['as' => $route_slug.'get_all_subcategory', 'uses' => $module_controller.'get_all_subcategory']);
        });
				/*--------------------------------------------------------------------------*/
				
				/*-------------------------group for Third Sub Category-------------------------------*/

        Route::group(['prefix'=>'third_sub_category'],function()
        {
            $route_slug = 'third_sub_category';
            $module_controller = "Admin\ThirdSubCategoryController@";

            Route::get('/', ['as' => $route_slug.'third_sub_category', 'uses' => $module_controller.'index']);
						Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
						Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);
						Route::post('/store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);
						
						Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

						Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

						Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

						Route::get('/get_all_third_sub_categories', ['as' => $route_slug.'get_all_third_sub_categories', 'uses' => $module_controller.'get_all_third_subcategory']);

						Route::get('/get_sub_categories/{enc_id}', ['as' => $route_slug.'get_sub_categories', 'uses' => $module_controller.'get_sub_categories']);
						
        });
        /*--------------------------------------------------------------------------*/

        /*-------------------------retailer-----------------------------------------*/
		Route::group(array('prefix' => '/retailer'), function()
		{	
			$route_slug       = "_retailer";
			$module_controller = "Admin\RetailerController@";

			Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			Route::get('/create',			 ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			
			Route::get('get_retailer', ['as' => $route_slug.'get_users', 'uses' => $module_controller.'get_retailer']);

			Route::get('report_generator/{type}', ['as' => $route_slug.'report_generator', 'uses' => $module_controller.'report_generator']);


			Route::get('view/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'view']);
			Route::get('delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);
			
			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
			
			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);

			Route::get('net_30_activate', ['as' => $route_slug.'net_30_activate',	 'uses' => $module_controller.'net_30_activate']);	
			Route::get('net_30_deactivate',['as'=> $route_slug.'net_30_deactivate',	 'uses' => $module_controller.'net_30_deactivate']);

			Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);		
			
			Route::get('changeAprovalStatus',['as'=> $route_slug.'changeAprovalStatus',	 'uses' => $module_controller.'changeAprovalStatus']);	

		}); 

		 /*-------------------------customer-----------------------------------------*/
		Route::group(array('prefix' => '/customer'), function()
		{	
			$route_slug       = "_customer";
			$module_controller = "Admin\CustomerController@";

			Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			Route::get('/create',			 ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			
			Route::get('get_customer', ['as' => $route_slug.'get_users', 'uses' => $module_controller.'get_customer']);

			Route::get('report_generator/{type}', ['as' => $route_slug.'report_generator', 'uses' => $module_controller.'report_generator']);


			Route::get('view/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'view']);
			Route::get('delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);
			
			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
			
			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);

			Route::get('net_30_activate', ['as' => $route_slug.'net_30_activate',	 'uses' => $module_controller.'net_30_activate']);	
			Route::get('net_30_deactivate',['as'=> $route_slug.'net_30_deactivate',	 'uses' => $module_controller.'net_30_deactivate']);

			Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);		
			
			Route::get('changeAprovalStatus',['as'=> $route_slug.'changeAprovalStatus',	 'uses' => $module_controller.'changeAprovalStatus']);	

		});


		

        /*-------------------------representative-----------------------------------------*/
		Route::group(array('prefix' => '/representative'), function()
		{	
			$route_slug       = "_representative";
			$module_controller = "Admin\RepresentativeController@";

			Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			Route::get('/create',			 ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);
			
			// Route::any('does_exists_tax_id/{param?}',['uses' => $module_controller.'does_exists_tax_id']);	
			
 			Route::any('does_exists_tax_id/{enc_id}', ['as' => $route_slug.'does_exists_tax_id', 'uses' => $module_controller.'does_exists_tax_id']);

			Route::post('/save',			 ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

			Route::post('/update',			 ['as' => $route_slug.'update', 'uses' => $module_controller.'update']);

			Route::get('edit/{user_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

			Route::get('get_representative', ['as' => $route_slug.'get_users', 'uses' => $module_controller.'get_retailer']);

			Route::get('report_generator/{type}', ['as' => $route_slug.'report_generator', 'uses' => $module_controller.'report_generator']);

			Route::get('view/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'view']);
			Route::get('delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);
			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);	
			Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);		

			Route::get('changeAprovalStatus',['as'=> $route_slug.'changeAprovalStatus',	 'uses' => $module_controller.'changeAprovalStatus']);	

			Route::get('delete_vendor/{id}',['as' => $route_slug.'delete_vendor','uses' => $module_controller.'delete_vendor']);

			 Route::post('/fetch_area',['as'=>$route_slug.'fetch_area','uses'=>$module_controller.'fetch_area']);
			 
			 Route::post('/fetch_category',['as'=>$route_slug.'fetch_category','uses'=>$module_controller.'fetch_category']);

			


		});
        /*----------------------------------------------------------------------------*/


    Route::group(array('prefix' => '/sales_manager') , function()

	{
		$route_slug = "sales_manager";
		$module_controller = "Admin\SalesManagerController@";

		Route::get('/', ['as' => $route_slug.'index','uses' => $module_controller.'index']);

		Route::get('/create', ['as' => $route_slug.'create','uses' => $module_controller.'create']);

		Route::post('/save',['as' => $route_slug.'save','uses' => $module_controller.'save']);

		Route::get('get_sales_manager', ['as' => $route_slug.'get_sales_manager', 'uses' => $module_controller.'get_sales_manager']);

		Route::get('changeAprovalStatus',['as'=> $route_slug.'changeAprovalStatus',	 'uses' => $module_controller.'changeAprovalStatus']);

		Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
		Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);

		Route::get('edit/{user_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

		Route::post('/update',			 ['as' => $route_slug.'update', 'uses' => $module_controller.'update']);

		Route::get('delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);
		Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);
		
		Route::get('view/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'view']);

		Route::get('report_generator/{type}', ['as' => $route_slug.'report_generator', 'uses' => $module_controller.'report_generator']);

		Route::post('/fetch_category',['as'=>$route_slug.'fetch_category','uses'=>$module_controller.'fetch_category']);

		Route::post('/check_area_exist',['as'=>$route_slug.'check_area_exist','uses'=>$module_controller.'check_area_exist']);

		Route::post('/check_category_exist',['as'=>$route_slug.'check_category_exist','uses'=>$module_controller.'check_category_exist']);


	});


        Route::group(array('prefix' => '/vendor'), function()
		{	
			$route_slug       = "vendor";
			$module_controller = "Admin\MakerController@";

			Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);

			Route::get('check_sequence_no_present', ['as' => $route_slug.'check_sequence_no_present', 'uses' => $module_controller.'check_sequence_no_present']);

			Route::post('update_admin_commission', ['as' => $route_slug.'update_admin_commission', 'uses' => $module_controller.'update_admin_commission']);




			// Route::get('create',			 ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

			// Route::post('save',			 ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

			Route::get('get_makers', ['as' => $route_slug.'get_makers', 'uses' => $module_controller.'get_makers']);

			Route::get('get_makers_in_modal', ['as' => $route_slug.'get_makers_in_modal', 'uses' => $module_controller.'get_makers_in_modal']);

			Route::get('report_generator/{type}', ['as' => $route_slug.'report_generator', 'uses' => $module_controller.'report_generator']);
			
			Route::get('view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			// Route::get('edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	

			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);	

			Route::get('changeAprovalStatus',['as'=> $route_slug.'changeAprovalStatus',	 'uses' => $module_controller.'changeAprovalStatus']);
			
			Route::get('update_status_get_a_quote',['as'=> $route_slug.'update_status_get_a_quote',	 'uses' => $module_controller.'update_status_get_a_quote']);

			Route::get('checkDuplicateSequence',['as'=> $route_slug.'checkDuplicateSequence',	 'uses' => $module_controller.'checkDuplicateSequence']);
			
			Route::get('update_status_add_to_bag',['as'=> $route_slug.'update_status_add_to_bag',	 'uses' => $module_controller.'update_status_add_to_bag']);

			Route::get('updateCommission',['as'=> $route_slug.'updateCommission',	 'uses' => $module_controller.'updateCommission']);	

			Route::get('get_count',['as'=>$route_slug.'get_count','uses'=>$module_controller.'get_count']);
	
	
			Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	

			Route::get('delete_rep_mapping/{id}',['as' => $route_slug.'delete_rep_mapping','uses' => $module_controller.'delete_rep_mapping']);

			Route::post('change_payment_status',['as'=> $route_slug.'change_payment_status',	 'uses' => $module_controller.'change_payment_status']);
			
			Route::post('save_vendor_sequence',['as'=> $route_slug.'save_vendor_sequence',	 'uses' => $module_controller.'save_vendor_sequence']);

		});

			/*-------------------------Account Settings ----------------------------------*/
		Route::group(array('prefix' => '/account_settings'), function()
		{
			$route_slug        = "account_settings";
			$module_controller = "Admin\AccountSettingsController@";

			Route::get('/',                  ['as' => $route_slug.'account_settings_show',   'uses' => $module_controller.'index']);
			
			Route::post('/verify_password',                  ['as' => $route_slug.'verify_password',   'uses' => $module_controller.'verify_password']);

			Route::post('/update/{enc_id}', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);

			Route::post('/update_stripe_settings/{enc_id}', ['as' => $route_slug.'update_stripe_settings', 'uses' => $module_controller.'update_stripe_settings']);
		});	

		/*----------------------activity logs----------------------------------*/
		Route::group(array('prefix' => '/activity_logs'), function()
		{
			$route_slug        = "activity_logs_";
			$module_controller = "Admin\ActivityLogController@";

			Route::get('/',['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			Route::get('/get_records',['as' => $route_slug.'get_records', 'uses' => $module_controller.'get_records']);
	 	});

       /*------------------User Activity Log-----------------------------------------*/

        Route::group(array('prefix' => '/user_activity_logs'), function()
		{
			$route_slug        = "user_activity_logs";
			$module_controller = "Admin\UserAvtivityController@";

			Route::get('/',['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			Route::get('/get_records',['as' => $route_slug.'get_records', 'uses' => $module_controller.'get_records']);

	 	});




	 	/*---------------------------subscription-------------------------------------*/


        Route::group(array('prefix' => '/subscription'), function()
		{
			$route_slug        = "subscription";
			$module_controller = "Admin\SubscriptionController@";

			Route::get('/',['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
		

	 	});

		/*-------------------------Site Settings---------------------------------------*/
		Route::group(['prefix'=>'site_settings'],function()
        {
         	$route_slug        = 'site_settings';
         	$module_controller = "Admin\SiteSettingController@";
			Route::get('/', ['as'=>$route_slug.'site_settings','uses'=>$module_controller.'index']);
			Route::post('/update/{enc_id}', ['as' => $route_slug.'update', 'uses' => $module_controller.'update']);

			Route::get('commission_settings', ['as' => $route_slug.'commission_settings', 'uses' => $module_controller.'commission_settings']);

			Route::post('update_commission_settings', ['as' => $route_slug.'update_commission_settings', 'uses' => $module_controller.'update_commission_settings']);

			Route::any('/update_site_status',['as' => $route_slug.'update_site_status', 'uses' => $module_controller.'update_site_status']);
        });

        /*-------------------------Menu Settings---------------------------------------*/

        Route::group(['prefix' =>'menu_settings'], function ()
	    {
	    	$route_slug = 'menu_settings';
	        $module_controller = "Admin\MenuSettingController@";
	        
			Route::get('/', ['as' => $route_slug.'menu_settings', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_menu', ['as' => $route_slug.'get_all_menu', 'uses' => $module_controller.'get_all_menu']);		
        });


		/*-------------------------Menu Settings---------------------------------------*/

        Route::group(['prefix' =>'menu_settings'], function ()
	    {
	    	$route_slug = 'menu_settings';
	        $module_controller = "Admin\MenuSettingController@";
	        
			Route::get('/', ['as' => $route_slug.'menu_settings', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

			Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

			Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

			Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

            Route::get('/get_all_menu', ['as' => $route_slug.'get_all_menu', 'uses' => $module_controller.'get_all_menu']);		
        });

		/*-------------------------Influencer Settings---------------------------------------*/
		Route::group(['prefix'=>'influencer_settings'],function()
        {
         	$route_slug        = 'influencer_settings';
         	$module_controller = "Admin\InfluencerSettingsController@";
			Route::get('/', ['as'=>$route_slug.'influencer_settings','uses'=>$module_controller.'index']);
			Route::post('/update', ['as' => $route_slug.'update', 'uses' => $module_controller.'update']);
        });


        /*-------------------------FAQ---------------------------------------*/
		Route::group(['prefix'=>'faq'],function()
        {
         	$route_slug        = 'faq';
         	$module_controller = "Admin\FaqController@";
			Route::get('/', 	['as'=>$route_slug.'faq','uses'=>$module_controller.'index']);
			Route::get('create/',['as' => $route_slug.'create',	'uses' => $module_controller.'create']);
			Route::get('edit/{enc_id}',['as' => $route_slug.'edit',	'uses' => $module_controller.'edit']);
			Route::post('save',['as' => $route_slug.'store','uses' => $module_controller.'save']);
			Route::get('delete/{enc_id}',	 ['as' => $route_slug.'delete',	  'uses' => $module_controller.'delete']);
			Route::get('change_status',['as'=> $route_slug.'change_status',	 'uses' => $module_controller.'status_update']);
			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
        });

		/*-----------------------------Users--------------------------------------*/
		Route::group(array('prefix' => '/users'), function()
		{
			$route_slug        = "admin_traveller_";
			$module_controller = "Admin\UserController@";

			Route::get('/',					['as' => $route_slug.'index',		 'uses' => $module_controller.'index']);
			Route::get('create/',			['as' => $route_slug.'create',		 'uses' => $module_controller.'create']);
			
			Route::get('edit/{enc_id}',		['as' => $route_slug.'edit',		 'uses' => $module_controller.'edit']);
			
			Route::post('store',['as' => $route_slug.'store','uses' => $module_controller.'store']);
			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);
			Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);
			Route::get('delete/{enc_id}',	['as' => $route_slug.'update',		 'uses' => $module_controller.'delete']);
			
			Route::get('/get_records',['as' => $route_slug.'get_records', 'uses' => $module_controller.'get_records']);
			
			Route::post('/get_state_records',['as' => $route_slug.'get_state_records', 'uses' => $module_controller.'get_state_records']);

			Route::post('/get_city_records',['as' => $route_slug.'get_city_records', 'uses' => $module_controller.'get_city_records']);
		});

        /*----------------------general settings-------------------------------------*/
        Route::group(array('prefix'=>'/general_setting'),function()
        {
            $route_slug        = "general_setting";
			$module_controller = "Admin\GeneralSettingController@";

			Route::get('/',					 ['as' => $route_slug.'manage',	   'uses' => $module_controller.'index']);
			Route::get('/get_records',	     ['as' =>$route_slug.'get_records', 'uses' => $module_controller.'get_records']);

			Route::get('create',			 ['as' => $route_slug.'create',	    'uses' => $module_controller.'create']);

			Route::post('/store',			 ['as' => $route_slug.'store',	    'uses' => $module_controller.'store']);

			Route::get('edit/{enc_id}',      ['as' => $route_slug.'edit',       'uses'=> $module_controller.'edit']);

			Route::get('activate',           ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
			
			Route::get('deactivate',         ['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);

			Route::post('multi_action', 	 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);

			Route::get('delete/{enc_id}',	 ['as' => $route_slug.'update',		 'uses' => $module_controller.'delete']);
        });
        /*----------------------------------------------------------------------------*/
		/*-------------------------email template-----------------------------------------*/
		Route::group(array('prefix' => '/email_template'), function()
		{
			$route_slug       = "admin_email_template_";
			$module_controller = "Admin\EmailTemplateController@";

			Route::get('/',				  ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			Route::get('create/',		  ['as' => $route_slug.'create','uses' => $module_controller.'create']);
			Route::post('store/',		  ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);
			Route::get('edit/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'edit']);
			Route::get('view/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'view']);
			Route::post('update/{enc_id}',['as' => $route_slug.'update','uses' => $module_controller.'update']);
		});


		Route::group(array('prefix' => '/banner_images'), function()
		{
			$route_slug       = "banner_images";
			$module_controller = "Admin\BannerImageController@";

			
			Route::get('/',				  ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);

			Route::get('/images',				  ['as' => $route_slug.'images', 'uses' => $module_controller.'images']);

			Route::get('/create',				  ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

			Route::post('/save',				  ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

			Route::get('edit/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'edit']);
			Route::get('delete/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);

		});
		/*---------------------------Products---------------------------------------*/
		Route::group(array('prefix' => '/products'), function()
		{	
			$route_slug       = "admin_products_";
			$module_controller = "Admin\ProductController@";

			Route::get('/',				  ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			
			Route::get('create/',		  ['as' => $route_slug.'create','uses' => $module_controller.'create']);
			
			Route::post('storeProduct/',  ['as' => $route_slug.'storeProduct', 
										   'uses' => $module_controller.'storeProduct']);

			Route::get('style_and_dimensions/{product_id}',  ['as' => $route_slug.'styleAndDimensions', 
										   'uses' => $module_controller.'styleAndDimensions']);

			Route::post('storeProductStyleAndDiemensions',  ['as' => $route_slug.'styleAndDimensions', 
										   'uses' => $module_controller.'storeProductStyleAndDiemensions']);

			Route::get('additional_images/{product_id}',  ['as' => $route_slug.'additionalImages', 
										   'uses' => $module_controller.'additionalImages']);
					
			Route::post('storeProductAdditionalImages',  ['as' => $route_slug.'styleAndDimensions', 
										   'uses' => $module_controller.'storeProductAdditionalImages']);

			Route::get('get_all_products',  ['as' => $route_slug.'get_all_products', 
										   'uses' => $module_controller.'get_all_products']);

			Route::get('get_export_admin_product',  ['as' => $route_slug.'get_export_admin_product', 
										   'uses' => $module_controller.'get_export_admin_product']);
			
			Route::get('edit/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'edit']);
			
			Route::get('view/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'view']);

			Route::post('size_inventory/',  ['as' => $route_slug.'size_inventory', 
											 'uses' => $module_controller.'size_inventory']);
			
			Route::post('update/{enc_id}',['as' => $route_slug.'update','uses' => $module_controller.'update']);

			Route::get('changeStatus',['as'=> $route_slug.'changeStatus',	 'uses' => $module_controller.'changeStatus']);

			Route::get('delete/{enc_id}',['as' => $route_slug.'delete','uses' => $module_controller.'delete']);

			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	

			Route::get('get_second_level_categories/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'get_second_level_categories']);

			Route::get('get_maker_data/{enc_id}',	  ['as' => $route_slug.'edit',	 'uses' => $module_controller.'get_maker_data']);
			
			Route::any('/does_exists/{param?}',['uses' => 'Admin\ProductController@does_exists']);

			Route::any('does_exists_edit/{param?}/{pid?}',['uses' => 'Admin\ProductController@does_exists_edit']);	
			

			Route::post('product_confirmation',['uses' => 'Admin\ProductController@product_confirmation']);	



		});

	
        /*----------------------------Start CMS pages--------------------------------------*/
		Route::group(array('prefix' => '/static_pages'), function()
		{
			$route_slug       = "static_pages_";
			$module_controller = "Admin\StaticPageController@";

			Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
			Route::get('create',			 ['as' => $route_slug.'create',	  'uses' => $module_controller.'create']);
			Route::get('edit/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'edit']);
			Route::get('view',		 ['as' => $route_slug.'view',	 'uses' => $module_controller.'view']);
			Route::any('store',				 ['as' => $route_slug.'store',	 'uses' => $module_controller.'store']);
			Route::post('update/{enc_id}',	 ['as' => $route_slug.'update',	  'uses' => $module_controller.'update']);
			Route::get('delete/{enc_id}',	 ['as' => $route_slug.'delete',	  'uses' => $module_controller.'delete']);	
			Route::get('activate',  ['as' => $route_slug.'activate',  'uses' => $module_controller.'activate']);	
			Route::get('deactivate',['as' => $route_slug.'deactivate',  'uses' => $module_controller.'deactivate']);	
			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
		});

		Route::group(array('prefix' => '/notifications'), function()
		{
			$route_slug       = "notifications_";
			$module_controller = "Admin\NotificationsController@";

			Route::any('/', 				     ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);

			Route::any('get_notifications', 	 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'get_notifications']);
			
			Route::get('delete/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);
			Route::post('multi_action',		     ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
			Route::get('/export', 				 ['as' => $route_slug.'export',	  'uses' => $module_controller.'export_notifications']);

			Route::get('/read_notification/{id}',     ['as' => $route_slug.'read_notification','uses' => $module_controller.'read_notification']);


		});

		Route::group(array('prefix' => '/leads'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Admin\LeadsController@";

			Route::get('/',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);
			Route::get('get_leads',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_leads']);

			Route::get('get_export_reps_orders',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_export_reps_orders']);

			Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

			Route::get('view_order/{enc_id}',	 ['as' => $route_slug.'view_order',	  'uses' => $module_controller.'view_order']);

			Route::any('pay_commission', ['as' => $route_slug.'pay_commission',   'uses' => $module_controller.'pay_commission']);
			
			Route::any('bulk_payCommission', ['as' => $route_slug.'bulk_payCommission',   'uses' => $module_controller.'bulk_payCommission']);

			Route::post('send_stripe_acc_creation_link', ['as' => $route_slug.'send_stripe_acc_creation_link',   'uses' => $module_controller.'send_stripe_acc_creation_link']);
			

			Route::group(array('prefix' => '/conversation'), function()
			{
				$route_slug       = "conversation_";
				$module_controller = "Admin\LeadConversationController@";

				Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

				Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

				Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
			});

		});

		Route::group(array('prefix' => '/retailer_orders'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Admin\QuotesController@";

			Route::get('/',	 ['as' => $route_slug.'conversation','uses' => $module_controller.'index']);
			
			Route::any('get_enquiries',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_enquiries']);

			Route::any('get_export_retailer_orders',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_export_retailer_orders']);

			Route::post('store_payment_proof', ['as' => $route_slug.'store_payment_proof', 'uses' => $module_controller.'store_payment_proof']);

			Route::post('/generate_report', ['as'=>$route_slug.'generate_report', 
									  	'uses'=>$module_controller.'generate_report']);

			Route::any('/download_report/{from_date}/{to_date}', ['as'=>$route_slug.'download_report', 
									  	'uses'=>$module_controller.'download_report']);

			Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

			Route::group(array('prefix' => '/conversation'), function()
			{
				$route_slug       = "conversation_";
				$module_controller = "Admin\QuotesConversationController@";

				Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

				Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

				Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
			});
		});

		Route::group(array('prefix' => '/customer_orders'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Admin\CustomerQuotesController@";

			Route::get('/',	 ['as' => $route_slug.'conversation','uses' => $module_controller.'index']);
			
			Route::any('get_enquiries',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_enquiries']);

			Route::any('get_export_customer_orders',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_export_customer_orders']);

			Route::post('/generate_report', ['as'=>$route_slug.'generate_report', 
									  	'uses'=>$module_controller.'generate_report']);

			Route::any('/download_report/{from_date}/{to_date}', ['as'=>$route_slug.'download_report', 
									  	'uses'=>$module_controller.'download_report']);

			Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

			Route::group(array('prefix' => '/conversation'), function()
			{
				$route_slug       = "conversation_";
				$module_controller = "Admin\QuotesConversationController@";

				Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

				Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

				Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
			});
		});


		Route::group(array('prefix' => '/cancel_orders'), function()
		{
			$route_slug       = "cancel_orders";
			$module_controller = "Admin\CancelOrderController@";

			Route::get('/',	 ['as' => $route_slug.'conversation','uses' => $module_controller.'index']);
			
			Route::get('get_enquiries',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_enquiries']);

			Route::get('get_export_cancel_orders',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_export_cancel_orders']);

			Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

			Route::get('refund_process',	 ['as' => $route_slug.'view','uses' => $module_controller.'refund_payment']);		
		});


		/*-----------------------------customer cancel orders ----------------------*/

		Route::group(array('prefix' => '/customer_cancel_orders'), function()
		{
			$route_slug       = "customer_cancel_orders";
			$module_controller = "Admin\CustomerCancelOrderController@";

			Route::get('/',	 ['as' => $route_slug.'conversation','uses' => $module_controller.'index']);
			
			Route::get('get_enquiries',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_enquiries']);

			Route::get('get_export_customer_cancel_orders',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'get_export_customer_cancel_orders']);

			Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

			Route::get('refund_process',	 ['as' => $route_slug.'view','uses' => $module_controller.'refund_payment']);		
		});


		/*-----------------------------rep sales cancel orders ----------------------*/


		Route::group(array('prefix' => '/rep_sales_cancel_orders'), function()
		{
			$route_slug        = "rep_sales_cancel_orders";
			$module_controller = "Admin\RepSalesCanceledOrderController@";

			Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		
			Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);

			Route::get('/get_export_rep_sales_cancel_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_rep_sales_cancel_orders']);
			
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

			Route::get('refund_process',	 ['as' => $route_slug.'view','uses' => $module_controller.'refund_payment']);	

		});

		/*---------------------All Order Start--------------------------------*/

		Route::group(array('prefix' => '/all_orders'), function()
		{
			$route_slug        = "all_orders";
			$module_controller = "Admin\AllOrderController@";

			Route::get('/', ['as' => $route_slug.'index',   'uses' => $module_controller.'index']);
		
			Route::get('/get_all_orders', ['as' => $route_slug.'get_all_orders',   'uses' => $module_controller.'get_all_orders']);
		});


		/*---------------------All Order End----------------------------------*/

		Route::group(array('prefix' => '/commission'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Admin\CommissionController@";

			Route::get('/',	 ['as' => $route_slug.'commission',	  'uses' => $module_controller.'index']);

			Route::get('/representative_listing/{enc_id}',	 ['as' => $route_slug.'commission',	  'uses' => $module_controller.'representative_listing']);


			Route::get('/get_representative_listing/{enc_id}',	 ['as' => $route_slug.'commission',	  'uses' => $module_controller.'get_representative_listing']);

			Route::get('get_maker_list',	 ['as' => $route_slug.'get_maker_list',	  'uses' => $module_controller.'get_maker_list']);

			Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

			Route::post('/set_commission', 	 ['as'=>$route_slug.'set_commission', 
									  'uses'=>$module_controller.'set_commission']);

		});


		Route::group(array('prefix' => '/rep_area'), function () 
	    {
	    	$route_slug = 'rep_area';
	        $module_controller = "Admin\RepresentativeAreaController@";
	        
			Route::get('/', ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);

			Route::get('/create', ['as' => $route_slug.'create','uses' => $module_controller.'create']);
			Route::post('/save', ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);
			Route::get('get_area_list',	 ['as' => $route_slug.'get_area_list',	  'uses' => $module_controller.'get_area_list']);
			Route::get('edit/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'edit']);
			Route::post('update/{enc_id}',	 ['as' => $route_slug.'update',	  'uses' => $module_controller.'update']);

			Route::get('delete/{enc_id}',	 ['as' => $route_slug.'delete',	  'uses' => $module_controller.'delete']);

			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	

			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);
			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	


		});

		/*-------------------------Influencer-----------------------------------------*/
		Route::group(array('prefix' => '/influencer'), function()
		{	
			$route_slug       = "_influencer";
			$module_controller = "Admin\InfluencerController@";

			Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
			
			Route::get('get_influencer_list', ['as' => $route_slug.'get_users', 'uses' => $module_controller.'get_influencer_list']);

			Route::get('get_assigned_promo_code_listing', ['as' => $route_slug.'get_assigned_promo_code_listing', 'uses' => $module_controller.'get_assigned_promo_code_listing']);

			Route::get('customer_orders/{enc_id}', ['as' => $route_slug.'customer_orders', 'uses' => $module_controller.'customer_orders']);

			Route::get('get_customer_orders_listing', ['as' => $route_slug.'get_customer_orders_listing', 'uses' => $module_controller.'get_customer_orders_listing']);

			Route::get('/change_status',    ['as'  =>$route_slug.'change_status', 
									     'uses'=>$module_controller.'change_status']);

			Route::get('view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::get('delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);	

			Route::post('/multi_action',    ['as'   => $route_slug.'multi_action',
			                             'uses' => $module_controller.'multi_action']);	

			Route::post('/send_stripe_acc_creation_link', ['as' => $route_slug.'send_stripe_acc_creation_link',   'uses' => $module_controller.'send_stripe_acc_creation_link']);	

			Route::get('report_generator/{type}', ['as' => $route_slug.'report_generator', 'uses' => $module_controller.'report_generator']);

		});
        /*----------------------------------------------------------------------------*/

        /*-------------Influencer Promo Code (START)---------------------------*/
        		
    	Route::group(array('prefix' => '/influencer_promo_code'), function()
    	{
    		$route_slug = '_influencer_promo_code';
    		$module_controller = 'Admin\InfluencerPromoCodeController@';

    		Route::get('/', ['as'  =>$route_slug.'index',
    						 'uses'=>$module_controller.'index']);

    		Route::get('/get_influencer_promo_code_listing', 
										['as'  =>$route_slug.'get_influencer_promo_code_listing', 
									  	'uses' =>$module_controller.'get_influencer_promo_code_listing']);

    		Route::get('/create',  ['as' =>$route_slug.'create', 
								   'uses'=>$module_controller.'create']);

    		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  	 'uses'=>$module_controller.'edit']);

    		Route::post('/store',   ['as' =>$route_slug.'store', 
							        'uses'=>$module_controller.'store']);

    		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);

    		Route::get('/change_status',    ['as'  =>$route_slug.'change_status', 
									     'uses'=>$module_controller.'change_status']);

    		Route::post('/multi_action',    ['as'   => $route_slug.'multi_action',
			                             'uses' => $module_controller.'multi_action']);

			Route::post('/assign_promo_code',['as'   => $route_slug.'assign_promo_code',
			                             'uses' => $module_controller.'assign_promo_code']);

			Route::get('/check_stripe_account_connected_or_not',
						['as' =>$route_slug.'check_stripe_account_connected_or_not',
						'uses'=>$module_controller.'check_stripe_account_connected_or_not']);

			Route::post('/send_stripe_acc_creation_link',
						['as'  =>$route_slug.'send_stripe_acc_creation_link',
						 'uses'=>$module_controller.'send_stripe_acc_creation_link']);
    	});

				/*-------------Influencer Promo Code (END)-----------------------------*/
				
				 /*-------------Visitors Enquiry Code (START)---------------------------*/
        		
				 Route::group(array('prefix' => '/visitors_enquiry'), function()
				 {
					 $route_slug = '_visitors_enquiry';
					 $module_controller = 'Admin\VisitorsEnquiryController@';
	 
					 Route::get('/', ['as'  =>$route_slug.'index',
										'uses'=>$module_controller.'index']);

					 Route::get('/get_data', 
					   ['as'  =>$route_slug.'get_data', 
						 'uses' =>$module_controller.'get_data']);

						 Route::get('/view/{enc_id}', ['as'=>$route_slug.'view', 
											 'uses'=>$module_controller.'view']);
											 
											 Route::get('get_export_visitors_enquiry',  ['as' => $route_slug.'get_export_visitors_enquiry', 
										   'uses' => $module_controller.'get_export_visitors_enquiry']);
	 
					 
				 });
	 
					 /*-------------Visitors Enquiry Code (END)-----------------------------*/

        /*--------------Influencer Rewards History (START)---------------------*/

        Route::group(array('prefix'=>'/influencer_rewards_history'), function()
        {
        	$route_slug = '_influencer_rewards_history';
    		$module_controller = 'Admin\InfluencerRewardsHistoryController@';

    		Route::get('/', ['as'  =>$route_slug.'index',
    						 'uses'=>$module_controller.'index']);

    		Route::get('/get_influencer_rewards_history_listing', 
    		['as' =>$route_slug.'get_influencer_rewards_history_listing',
    		'uses'=>$module_controller.'get_influencer_rewards_history_listing']);

    		Route::get('/get_export_influencer_reward_history', 
    		['as' =>$route_slug.'get_export_influencer_reward_history',
    		'uses'=>$module_controller.'get_export_influencer_reward_history']);

    		Route::get('/details/{enc_id}', 
    						['as'  =>$route_slug.'details',
    						 'uses'=>$module_controller.'details']);
        	
        });

		/*--------------Influencer Rewards History (END) ----------------------*/
		
		/*------------- Quote Requests (START)---------------------------*/
        		
    	Route::group(array('prefix' => '/quote_requests'), function()
    	{
    		$route_slug = 'quote_requests';
    		$module_controller = 'Admin\QuoteRequestsController@';

    		Route::get('/', ['as'  =>$route_slug.'index', 'uses'=>$module_controller.'index']);							 
			Route::get('/get_all_get_quote_requests', ['as'  =>$route_slug.'index', 'uses'=>$module_controller.'get_all_get_quote_requests']);
			Route::post('/send_quote_email_to_vendor', ['as'  =>$route_slug.'send_quote_email_to_vendor', 'uses'=>$module_controller.'send_quote_email_to_vendor']);
			Route::get('/view/{enc_id}', ['as'  =>$route_slug.'view_quote_request_details', 'uses'=>$module_controller.'view_quote_request_details']);
    	});

        /*------------- Quote Requests (END)-----------------------------*/
	});

	/*-------------------------Admin END---------------------------------------*/	

	/*START Common Routes*/
	$route_slug        = "customer_commission_reports";
	$module_controller = "Admin\CustomerCommissionReportsController@";
	Route::any('load_bulkPaymentModelData_cust_vendor', ['as' => $route_slug.'load_bulkPaymentModelData',   'uses' => $module_controller.'load_bulkPaymentModelData']);	

});

include('routes/representative.php');
include('routes/maker.php');
include('routes/retailer.php');
include('routes/customer.php');
include('routes/sales_manager.php');
include('routes/import.php');
include('routes/influencer.php');	
include('routes/api.php');	

