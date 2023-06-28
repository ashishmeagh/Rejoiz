<?php

Route::group(['prefix' => $representative_path,'middleware' => 'representative'], function ()
{
	/*-------------------------START Dashboard-----------------------------------------*/
	$route_slug        = "dashboard";
	$module_controller = "Representative\DashboardController@";
	
	Route::get('/dashboard', 	 ['as'=>$route_slug.'dashboard', 
								  'uses'=>$module_controller.'index']);	
	/*-------------------------END Dashboard-----------------------------------------*/


	/*-------------------------START Account Settings--------------------------------*/		
	Route::group(array('prefix' => '/account_settings'), function()
	{
		$route_slug        = "account_settings";
		$module_controller = "Representative\AccountSettingsController@";

		Route::get('/',                  ['as' => $route_slug.'account_settings_show',   'uses' => $module_controller.'index']);
		Route::post('/update/{enc_id}', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);
	});	
	
	Route::any('/change_password',     ['as' => 'change_password',   
						      'uses' => 'Representative\AccountSettingsController@change_password']);

	
	Route::get('/logout',     ['as' => 'logout',   
						      'uses' => 'Maker\AccountSettingsController@logout']);
	/*-------------------------END Account Settings--------------------------------*/	


	/*-------------------------Orders--------------------------------*/	
	Route::group(array('prefix' => '/leads'), function()
	{
		$route_slug        = "orders";
		$module_controller = "Representative\LeadsController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);

		Route::get('/confirmed', ['as' => $route_slug.'list',   'uses' => $module_controller.'confirmed_orders']);
		
		Route::get('/pending_orders', ['as' => $route_slug.'pending_orders',   'uses' => $module_controller.'pending_orders']);
		
		Route::get('/completed_orders', ['as' => $route_slug.'completed_orders',   'uses' => $module_controller.'completed_orders']);
		
		Route::get('/approved_orders', ['as' => $route_slug.'approved_orders',   'uses' => $module_controller.'approved_orders']);

		Route::get('/net_30_completed_orders', ['as' => $route_slug.'net_30_completed_orders',   'uses' => $module_controller.'net_30_completed_orders']);		

		Route::get('/net_30_pending_orders', ['as' => $route_slug.'net_30_pending_orders',   'uses' => $module_controller.'net_30_pending_orders']);
		
		Route::any('/create/{cust_id?}', ['as' => $route_slug.'create',   'uses' => $module_controller.'create']);

		Route::get('/search_customer', ['as' => $route_slug.'search_customer',   'uses' => $module_controller.'search_customer']);

		Route::get('/reorder', ['as' => $route_slug.'reorder',   'uses' => $module_controller.'reorder']);


		Route::get('/is_customer_exists', ['as' => $route_slug.'is_customer_exists',   'uses' => $module_controller.'is_customer_exists']);

		Route::get('/lead_listing', ['as' => $route_slug.'lead_listing',   'uses' => $module_controller.'lead_listing']);


		Route::get('/find_products/{enc_lead_id}/{slug?}', ['as' => $route_slug.'find_products',   'uses' => $module_controller.'find_products']);

	
		//Retailer order routing...
		Route::get('/retailer', ['as' => $route_slug.'retailer',   'uses' => $module_controller.'retailer']);		

		Route::post('/get_customer_detail', ['as' => $route_slug.'get_customer_detail',   'uses' => $module_controller.'get_customer_detail']);

		Route::get('/lead_retailer_listing', ['as' => $route_slug.'lead_retailer_listing',   'uses' => $module_controller.'lead_retailer_listing']);
		
		// Route::get('/update_existing_lead/{lead_id}', ['as' => $route_slug.'update_existing_lead',   'uses' => $module_controller.'update_lead_entry']);


		Route::get('/get_product_list', ['as' => $route_slug.'get_product_list',   'uses' => $module_controller.'get_product_list']);

		Route::post('/save_customer_address', ['as' => $route_slug.'save_customer_address',   'uses' => $module_controller.'save_customer_address']);

		Route::get('/view_lead_listing/{enc_id}/{order_no}', 	 ['as'=>$route_slug.'view_lead_listing', 
									  'uses'=>$module_controller.'view_lead_listing']);

		Route::get('/update_lead_listing/{enc_id}', 	 ['as'=>$route_slug.'update_lead_listing', 
									  'uses'=>$module_controller.'update_lead_listing']);


		/*Route::get('/get_product_details/{enc_id}', 	 ['as'=>$route_slug.'update_lead_listing', 
									  'uses'=>$module_controller.'get_product_details']);*/

		Route::any('/get_product_details', ['as' => $route_slug.'get_product_details', 'uses' =>$module_controller.'get_product_details']);							  

		Route::post('/store_lead', 	 ['as'=>$route_slug.'store_lead', 
									  'uses'=>$module_controller.'store_lead']);

		Route::get('/delete_product_from_bucket/{enc_id}/{product_id?}', 	 ['as'=>$route_slug.'delete_product_from_bucket', 
									  'uses'=>$module_controller.'delete_product_from_bucket']);

		Route::get('/delete_product_from_bucket_no/{enc_id}/{product_id?}', 	 ['as'=>$route_slug.'delete_product_from_bucket_no', 
									  'uses'=>$module_controller.'delete_product_from_bucket_no']);

		Route::get('/order_summary/{enc_lead_id}', ['as'=>$route_slug.'order_summary', 
									  'uses'=>$module_controller.'order_summary']);


		Route::get('/delete_all_products/{enc_lead_id}', ['as'=>$route_slug.'delete_all_products', 
									  'uses'=>$module_controller.'delete_all_products']);

		Route::get('/finalize_lead/{enc_lead_id}', ['as'=>$route_slug.'finalize_lead', 
									                'uses'=>$module_controller.'finalize_lead']);


		Route::any('/update_product_qty', 	 ['as'=>$route_slug.'update_product_qty', 
									  'uses'=>$module_controller.'update_product_qty']);

		Route::get('/delete_product/{enc_prod_id}', ['as' => $route_slug.'delete_product', 'uses' =>$module_controller.'delete_product']);

		Route::any('/empty_cart', ['as' => $route_slug.'empty_cart', 'uses' =>$module_controller.'empty_cart']);	

		Route::any('/update_qty', ['as' => $route_slug.'update_qty', 'uses' =>$module_controller.'update_qty']);

		Route::post('/save_address',['as' => $route_slug.'save_address','uses' =>$module_controller.'save_address']);	

		Route::group(array('prefix' => '/conversation'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Representative\LeadConversationController@";

			Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

			Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

			Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
		});


		

	});
	
	Route::group(array('prefix' => '/retailer'), function()
	{
		$route_slug        = "customers";
		$module_controller = "Representative\CustomerController@";

		Route::get('/', ['as' => $route_slug.'customers',   'uses' => $module_controller.'index']);

		Route::get('/customers_listing', ['as' => $route_slug.'customers_listing',   'uses' => $module_controller.'customers_listing']);

		Route::get('/view/{enc_id}', 	 ['as'=>$route_slug.'view_customer', 
									  'uses'=>$module_controller.'view_customer']);
		Route::get('/create', 	 ['as'=>$route_slug.'create', 
									  'uses'=>$module_controller.'create']);
		Route::post('/save',			 ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

		Route::post('multi_action', 	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);
		
		Route::get('/edit_customer/{enc_id}', 	 ['as'=>$route_slug.'edit_customer', 
									  'uses'=>$module_controller.'edit_customer']);

		Route::get('/deactivate', 	 ['as'=>$route_slug.'deactivate', 
									  'uses'=>$module_controller.'deactivate']);

		Route::get('/activate', 	 ['as'=>$route_slug.'activate', 
									  'uses'=>$module_controller.'activate']);
		Route::get('/retailer_by_zip_code', ['as' => $route_slug.'customers',   'uses' => $module_controller.'retailer_by_zip_code']);

		Route::get('/representatives_retailer', ['as' => $route_slug.'customers_listing',   'uses' => $module_controller.'get_match_zip_retailer']);

		Route::get('/view_zip_code_retailer/{enc_id}', 	 ['as'=>$route_slug.'view_customer', 
									  'uses'=>$module_controller.'view_zip_code_retailer']);

	});

	
	Route::group(array('prefix' => '/notifications'), function()
	{
		$route_slug       = "notifications_";
		$module_controller = "Representative\NotificationsController@";

		Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
		Route::get('delete/{enc_id}',	 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);
		Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);
		Route::get('/export', 			 ['as' => $route_slug.'export',	  'uses' => $module_controller.'export_notifications']);	


		Route::get('/read_notification/{id}',     ['as' => $route_slug.'read_notification','uses' => $module_controller.'read_notification']);
	});


	Route::group(array('prefix' => '/vendors'), function()
	{
		$route_slug       = "vendors_";
		$module_controller = "Representative\MakersController@";

		Route::get('/',  ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
		Route::get('/makers_listing', ['as' => $route_slug.'makers_listing',   'uses' => $module_controller.'makers_listing']);

		Route::get('/view/{enc_id}',['as' =>$route_slug.'view' , 'uses' =>$module_controller.'view']);
		Route::get('add_maker/{enc_id}',['as' => $route_slug.'add_maker',	 'uses' => $module_controller.'add_maker']);
		
		Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	

		Route::get('/my_maker_list', ['as' => $route_slug.'my_maker_list',   'uses' => $module_controller.'my_maker_list']);

		Route::get('/load_my_maker_listing', ['as' => $route_slug.'load_my_maker_listing',   'uses' => $module_controller.'load_my_maker_listing']);

		Route::get('remove_maker/{enc_id}',['as' => $route_slug.'remove_maker',	 'uses' => $module_controller.'remove_maker']);

	});

	/*-----------------------------rep sales cancel orders ----------------------*/


	Route::group(array('prefix' => '/rep_sales_cancel_orders'), function()
	{
		$route_slug        = "rep_sales_cancel_orders";
		$module_controller = "Representative\RepSalesCanceledOrderController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
	
		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

    

	});
});