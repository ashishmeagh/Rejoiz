<?php

Route::group(['prefix' => $retailer_path,'middleware' => 'retailer'], function ()
{
	/*-------------------------START Dashboard-----------------------------------------*/
	$route_slug        = "dashboard";
	$module_controller = "Retailer\DashboardController@";
	
	Route::get('/dashboard', 	 ['as'=>$route_slug.'dashboard', 
								  'uses'=>$module_controller.'index']);	
	
	Route::get('/change_password', 	 ['as'=>$route_slug.'change_password', 
								  'uses'=>$module_controller.'change_password']);	
	Route::post('/update_password', 	 ['as'=>$route_slug.'update_password', 
								  'uses'=>$module_controller.'update_password']);	

	Route::any('/my_favorite', ['as'=>$route_slug.'my_favorite', 
								  'uses'=>$module_controller.'get_favorite_data']);

	/*-------------------------END Dashboard-----------------------------------------*/

	/*-------------------------START Account Settings--------------------------------*/		
	Route::group(array('prefix' => '/account_settings'), function()
	{
		$route_slug        = "account_settings";
		$module_controller = "Retailer\AccountSettingsController@";

		Route::get('/',                  ['as' => $route_slug.'account_settings_show',   'uses' => $module_controller.'index']);
		//Route::post('/update/{enc_id}', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);
		Route::post('/update', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);
		
		Route::get('/is_login_update', ['as' => $route_slug.'is_login_update', 'uses' => $module_controller.'is_login_update']);
		
		Route::get('/check_profile_complete', ['as' => $route_slug.'check_profile_complete', 'uses' => $module_controller.'check_profile_complete']);

	});	

	/*-------------------------START Payment Cart--------------------------------*/		
	Route::group(array('prefix' => '/card'), function()
	{
		$route_slug        = "card";
		$module_controller = "Retailer\CardController@";

		Route::get('/',                  ['as' => $route_slug.'account_settings_show',   'uses' => $module_controller.'index']);
		Route::get('/add',                  ['as' => $route_slug.'add',   'uses' => $module_controller.'add']);


		Route::get('/edit/{enc_id}/{cus_id}',  ['as' => $route_slug.'edit',    'uses' => $module_controller.'edit']);

		Route::post('/update',                 ['as' => $route_slug.'update',  'uses' => $module_controller.'update']);

		Route::get('/delete/{enc_id}/{cus_id}/{card_finger_print}',                  ['as' => $route_slug.'delete_card',   'uses' => $module_controller.'delete_card']);


		Route::post('store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);
	});	

	Route::any('/does_exists_tax_id/{param?}',['uses' => 'Retailer\AccountSettingsController@does_exists_tax_id']);

	/*-------------------------END Account Settings--------------------------------*/	
	Route::group(array('prefix' => '/my_quote'), function()
	{
		$route_slug        = "my_quote";
		$module_controller = "Retailer\MyQuotesController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		Route::post('/update/{enc_id}', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);


		Route::get('/get_my_quote', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_quote']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

		Route::group(array('prefix' => '/conversation'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Retailer\QuoteConversationController@";

			Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

			Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

			Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
		});
	});
	

	Route::group(['prefix'=>'/transactions'],function () 
	    {
	    	$module_controller = "Retailer\TransactionTableController@";
	        
	        Route::any('/get_transaction_details',['uses' => $module_controller.'get_transaction_details']);

	         Route::any('/show_transaction_details',['uses' => $module_controller.'show_transaction_details']);
	         Route::get('/get_export_transasction_orders',['uses' => $module_controller.'get_export_transasction_orders']);
		});

	Route::group(array('prefix' => '/notifications'), function()
	{
		$route_slug       = "notifications_";
		$module_controller = "Retailer\NotificationsController@";

		Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
		Route::get('delete/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);
		Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
		Route::get('/export', 				 ['as' => $route_slug.'export',	  'uses' => $module_controller.'export_notifications']);

		Route::get('/read_notification/{id}',     ['as' => $route_slug.'read_notification','uses' => $module_controller.'read_notification']);
	});


	Route::group(array('prefix' => '/my_orders'), function()
	{
		$route_slug        = "my_quote";
		$module_controller = "Retailer\MyOrdersController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		Route::post('/update/{enc_id}', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);

		Route::get('/chk_products_availability', ['as' => $route_slug.'chk_products_availability', 'uses' => $module_controller.'chk_products_availability']);


		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		Route::get('/get_export_retailer_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_retailer_orders']);


		Route::get('/my_pending_orders', ['as' => $route_slug.'my_pending_orders','uses' => $module_controller.'my_pending_orders']);
		
		Route::get('/my_completed_orders', ['as' => $route_slug.'my_completed_orders','uses' => $module_controller.'my_completed_orders']);

		Route::get('/my_net_30_completed_orders', ['as' => $route_slug.'my_net_30_completed_orders','uses' => $module_controller.'my_net_30_completed_orders']);

		Route::get('/my_net_30_pending_orders', ['as' => $route_slug.'my_net_30_pending_orders','uses' => $module_controller.'my_net_30_pending_orders']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

        Route::get('/cancel', ['as' => $route_slug.'cancel',   'uses' => $module_controller.'order_cancel']);

        Route::get('/order_cancel', ['as' => $route_slug.'cancel',   'uses' => $module_controller.'rep_sales_order_cancel']);


        Route::get('/order_from_representative', ['as' => $route_slug.'get_order_from_representative',   'uses' => $module_controller.'get_order_from_representative']);

        Route::get('/get_listing', ['as' => $route_slug.'get_listing',   'uses' => $module_controller.'get_listing']);
        Route::get('/get_export_order_by_representative', ['as' => $route_slug.'get_export_order_by_representative',   'uses' => $module_controller.'get_export_order_by_representative']);

       /* Route::get('/order_summary/{order_no}', ['as'=>$route_slug.'order_summary', 
									  'uses'=>$module_controller.'order_summary']);*/


        Route::get('/rep_sales_pending_orders', ['as' => $route_slug.'rep_sales_pending_orders','uses' => $module_controller.'rep_sales_pending_order']);
		
		Route::get('/rep_sales_completed_orders', ['as' => $route_slug.'rep_sales_completed_orders','uses' => $module_controller.'rep_sales_completed_order']);

		Route::get('/net_30_rep_sales_completed_orders', ['as' => $route_slug.'net_30_rep_sales_completed_orders','uses' => $module_controller.'net_30_rep_sales_completed_orders']);

		Route::get('/net_30_rep_sales_pending_orders', ['as' => $route_slug.'net_30_rep_sales_pending_orders','uses' => $module_controller.'net_30_rep_sales_pending_orders']);

		Route::get('/order_summary/{order_no}/{maker_id}', ['as'=>$route_slug.'order_summary', 
									                       'uses'=>$module_controller.'order_summary']);

        Route::get('/delete_product_from_bucket/{enc_id}/{product_id?}', 	 ['as'=>$route_slug.'delete_product_from_bucket', 
									  'uses'=>$module_controller.'delete_product_from_bucket']);
       
       Route::get('/finalize_lead/{enc_lead_id}', ['as'=>$route_slug.'finalize_lead', 
									  'uses'=>$module_controller.'finalize_lead']);

      

       Route::any('/update_product_qty', 	 ['as'=>$route_slug.'update_product_qty', 
									  'uses'=>$module_controller.'update_product_qty']);

       Route::post('/save_address',['as' => $route_slug.'save_address','uses' =>$module_controller.'save_address']);	

       Route::get('/net_30_payment/{order_no}',['as'=>$route_slug.'net_30_payment','uses'=>$module_controller.'net_30_payment']);


		Route::group(array('prefix' => '/conversation'), function()
		{
			$route_slug        = "conversation_";
			$module_controller = "Retailer\QuoteConversationController@";

			Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

			Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

			Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
		});


	});


	Route::group(array('prefix' => '/my_cancel_orders'), function()
	{
		$route_slug        = "my_cancel_orders";
		$module_controller = "Retailer\MyCancelOrderController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		

		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		Route::get('/get_export_retailer_cancel_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_retailer_cancel_orders']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

     


	});


	/*-----------------------------rep sales cancel orders ----------------------*/


	Route::group(array('prefix' => '/rep_sales_cancel_orders'), function()
	{
		$route_slug        = "rep_sales_cancel_orders";
		$module_controller = "Retailer\RepSalesCanceledOrderController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
	
		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		Route::get('/get_export_rep_sales_cancel_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_rep_sales_cancel_orders']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

    

	});

	/* payments routes*/
	

	/*$route_slug       = "retailer_payment_";
	$module_controller = "Retailer\PaymentController@";

	Route::get('/checkout',['as'=> $route_slug.'checkout',	 'uses' => $module_controller.'checkout']);	
	Route::post('/set_session',['as'=> $route_slug.'set_session',	 'uses' => $module_controller.'set_session']);	
	

	Route::post('pay_with_card', ['as' => $route_slug.'.paymoney','uses' => $module_controller.'postPaymentWithStripe']);
    */
    
	Route::get('/logout',     ['as' => 'logout',   

						      'uses' => 'Maker\AccountSettingsController@logout']);	
});

//front route without login
/*-------------------------START Maker products--------------------------------------------------------*/		
Route::group(['middleware'=>['front']],function()
{
	Route::group(array('prefix' => '/vendor-details'), function()
	{
		$route_slug        = "vendor-details";
		$module_controller = "Retailer\MakerProductController@";
		$module_controller_customer = "Customer\MakerProductController@";


	    Route::get('/', ['as' => $route_slug.'maker-details',   'uses' => $module_controller.'maker_details']);	

	    Route::get('/flip_catalog', ['as' => $route_slug.'maker-details',   'uses' => $module_controller.'maker_details_new']);

		Route::any('/get_product_details', ['as' => $route_slug.'maker-details',   'uses' => $module_controller.'get_product_details']);

		Route::post('/favorite_makers',['as'=>$route_slug.'favorite_makers','uses'=>$module_controller.'favorite_makers']);

		Route::post('/remove_from_favorite',['as'=>$route_slug.'remove_from_favorite','uses'=>$module_controller.'remove_from_favorite']);

		Route::post('/favorite_products',['as'=>$route_slug.'favorite_products','uses'=>$module_controller.'favorite_products']);
		
		Route::post('/remove_product_from_favorite',['as'=>$route_slug.'remove_product_from_favorite','uses'=>$module_controller.'remove_product_from_favorite']);
		

		Route::post('/add_to_favorite',['as'=>$route_slug.'add_to_favorite','uses'=>$module_controller.'add_to_favorite']);
		Route::post('/add_to_customer_favorite',['as'=>$route_slug.'add_to_customer_favorite','uses'=>$module_controller.'add_to_customer_favorite']);
		
		Route::post('/remove_from_favorite',['as'=>$route_slug.'remove_from_favorite','uses'=>$module_controller.'remove_from_favorite']);
		
		Route::get('/catalogs/{catalog_id}',['as'=>$route_slug.'catlogs','uses'=>$module_controller.'catlogs']);

		Route::get('/catalogs/flip/{catalog_id}',['as'=>$route_slug.'catlogs','uses'=>$module_controller.'catlogs_new']);
		

		//For Customer

		Route::post('/add_to_customer_favorite',['as'=>$route_slug.'add_to_customer_favorite','uses'=>$module_controller_customer.'add_to_favorite']);

		Route::post('/remove_from_customer_favorite',['as'=>$route_slug.'remove_from_favorite','uses'=>$module_controller_customer.'remove_from_favorite']);



		/*new product detail page : Vishal*/
		Route::any('/product_detail', ['as' => $route_slug.'maker-details',   'uses' => $module_controller.'new_product_detail']);
		/*end*/

		/* add get a qoute : Pranit Shinde */
		Route::any('/send_get_a_quote', ['as' => $route_slug.'maker-details',   'uses' => $module_controller.'send_get_a_quote']);
		/*end*/
	});	
});	
/*--------------------END Maker products --------------------------------------------------*/	


		
Route::get('/logout',     ['as' => 'logout', 'uses' => 'Front\AuthController@logout']);	