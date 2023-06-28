<?php


    Route::group(['prefix' => '/sales_manager','middleware' => 'sales_manager'], function()
	{
			$route_slug       = "sales_manager";
			$module_controller = "sales_manager\SalesManagerController@";

			Route::get('/dashboard',	 ['as' => $route_slug.'sales_manager',	  'uses' => $module_controller.'index']);

			Route::get('/logout',     ['as' => 'logout',   
				  'uses' => 'sales_manager\SalesManagerController@logout']);

			Route::any('/change_password',     ['as' => 'change_password',   
				   'uses' => 'sales_manager\SalesManagerController@change_password']);

			Route::any('/account_settings',     ['as' => 'account_settings',   
				   'uses' => 'sales_manager\SalesManagerController@account_settings']);

			Route::any('/update_sales_manager/{enc_id}',     ['as' => 'update_sales_manager',   
				   'uses' => 'sales_manager\SalesManagerController@update_sales_manager']);

			Route::get('/add_representative',     ['as' => 'add_representative',   
				   'uses' => 'sales_manager\SalesManagerController@add_representative']);


			Route::any('/save_rep',     ['as' => 'save_rep',   
				   'uses' => 'sales_manager\SalesManagerController@save_rep']);

			Route::any('/representative_listing',     ['as' => 'representative_listing',   
				   'uses' => 'sales_manager\SalesManagerController@representative_listing']);

			
			Route::get('get_representative', ['as' => $route_slug.'get_users', 'uses' => $module_controller.'get_representative']);

			
			Route::get('changeAprovalStatus', ['as' => $route_slug.'changeAprovalStatus', 'uses' => $module_controller.'changeAprovalStatus']);

			Route::get('view/{enc_id}', ['as' => $route_slug.'view', 'uses' => $module_controller.'view']);

			Route::get('edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

			Route::any('save_rep/', ['as' => $route_slug.'save_rep', 'uses' => $module_controller.'save_rep']);

			Route::any('delete_rep/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete_rep']);

			Route::get('delete_vendor/{id}', ['as' => $route_slug.'delete_vendor' , 'uses' => $module_controller.'delete_vendor']);

			
			Route::get('delete/{enc_id}',	 ['as' => $route_slug.'delete',	  'uses' => $module_controller.'delete']);

			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	

			Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);

			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);

			Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::group(array('prefix' => '/notifications'), function()
			{
				$route_slug       = "notifications_";
				$module_controller = "sales_manager\NotificationController@";

				Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
				Route::get('delete/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);
				Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
				Route::get('/export', 				 ['as' => $route_slug.'export',	  'uses' => $module_controller.'export_notifications']);
 
		        Route::get('/read_notification/{id}',     ['as' => $route_slug.'read_notification','uses' => $module_controller.'read_notification']);
			});


			Route::group(array('prefix' => '/leads'), function()
			{
				$route_slug        = "orders";
				$module_controller = "sales_manager\LeadsController@";

				Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);

				Route::get('/confirmed', ['as' => $route_slug.'list',   'uses' => $module_controller.'confirmed_orders']);

				Route::get('/pending_orders', ['as' => $route_slug.'pending_orders',   'uses' => $module_controller.'pending_orders']);
		
		        Route::get('/completed_orders', ['as' => $route_slug.'completed_orders',   'uses' => $module_controller.'completed_orders']);

		        Route::get('/net_30_completed_orders', ['as' => $route_slug.'net_30_completed_orders',   'uses' => $module_controller.'net_30_completed_orders']);
		

				Route::get('/net_30_pending_orders', ['as' => $route_slug.'net_30_pending_orders',   'uses' => $module_controller.'net_30_pending_orders']);
		
		
		        Route::get('/approved_orders', ['as' => $route_slug.'approved_orders',   'uses' => $module_controller.'approved_orders']);
				
				Route::get('/reps', ['as' => $route_slug.'rep_lead_listing',   'uses' => $module_controller.'rep_lead_listing']);

				Route::get('/rep_lead_list', ['as' => $route_slug.'reps_leads',   'uses' => $module_controller.'reps_leads']);

				Route::any('/create/{cust_id?}', ['as' => $route_slug.'create',   'uses' => $module_controller.'create']);

				Route::get('/reorder', ['as' => $route_slug.'reorder',   'uses' => $module_controller.'reorder']);

				Route::get('/slice_reorder', ['as' => $route_slug.'slice_reorder',   'uses' => $module_controller.'splice_reorder_data']);

				Route::get('/search_customer', ['as' => $route_slug.'search_customer',   'uses' => $module_controller.'search_customer']);

		        Route::get('/is_customer_exists', ['as' => $route_slug.'is_customer_exists',   'uses' => $module_controller.'is_customer_exists']);

				Route::post('/get_customer_detail', ['as' => $route_slug.'get_customer_detail',   'uses' => $module_controller.'get_customer_detail']);

				Route::post('/save_customer_address', ['as' => $route_slug.'save_customer_address',   'uses' => $module_controller.'save_customer_address']);

				Route::get('/find_products/{enc_lead_id}/{slug?}', ['as' => $route_slug.'find_products',   'uses' => $module_controller.'find_products']);

				Route::get('/get_product_list', ['as' => $route_slug.'get_product_list',   'uses' => $module_controller.'get_product_list']);


                Route::get('/update_lead_listing/{enc_id}', ['as'=>$route_slug.'update_lead_listing', 
									  'uses'=>$module_controller.'update_lead_listing']);


				/*Route::get('/get_product_details/{enc_id}', 	 ['as'=>$route_slug.'get_product_details', 
									  'uses'=>$module_controller.'get_product_details']);*/

				Route::any('/get_product_details', ['as' => $route_slug.'get_product_details', 'uses' =>$module_controller.'get_product_details']);	

				Route::post('/store_lead', 	 ['as'=>$route_slug.'store_lead', 
									  'uses'=>$module_controller.'store_lead']);

				Route::get('/order_summary/{enc_lead_id}', ['as'=>$route_slug.'order_summary', 
									  'uses'=>$module_controller.'order_summary']);

				Route::get('/finalize_lead/{enc_lead_id}', ['as'=>$route_slug.'finalize_lead', 
									  'uses'=>$module_controller.'finalize_lead']);

				Route::post('/save_address',['as' => $route_slug.'save_address','uses' =>$module_controller.'save_address']);

				Route::get('/lead_listing', ['as' => $route_slug.'lead_listing',   'uses' => $module_controller.'lead_listing']);

				Route::any('/update_product_qty', 	 ['as'=>$route_slug.'update_product_qty', 
									  'uses'=>$module_controller.'update_product_qty']);

				Route::get('/view_lead_listing/{enc_id}/{order_no}', 	 ['as'=>$route_slug.'view_lead_listing', 
									  'uses'=>$module_controller.'view_lead_listing']);

			    Route::get('view_details/{enc_id}/{order_no}/{is_rep_sales_order}', 	 ['as'=>$route_slug.'view_lead_listing', 
									  'uses'=>$module_controller.'view_lead_listing']);
				
				Route::get('/delete_all_products/{enc_lead_id}', ['as'=>$route_slug.'delete_all_products', 
									  'uses'=>$module_controller.'delete_all_products']);

				Route::get('/net_30_payment/{order_no}',['as'=>$route_slug.'net_30_payment','uses'=>$module_controller.'net_30_payment']);

				  Route::get('/delete_product_from_bucket/{enc_id}/{product_id?}', 	 ['as'=>$route_slug.'delete_product_from_bucket', 
									  'uses'=>$module_controller.'delete_product_from_bucket']);

				 Route::get('/delete_product_from_bucket_no/{enc_id}/{product_id?}', 	 ['as'=>$route_slug.'delete_product_from_bucket_no', 
									  'uses'=>$module_controller.'delete_product_from_bucket_no']);


			});


		Route::group(array('prefix' => '/retailer'), function()
		{
			$route_slug        = "customers";
			$module_controller = "sales_manager\CustomerController@";

			Route::get('/',    ['as'   => $route_slug.'customers',   
				                'uses' => $module_controller.'index']);

		    Route::get('/',  ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);

			Route::get('/vendors_listing', ['as' => $route_slug.'vendors_listing',   'uses' => $module_controller.'vendors_listing']);
			Route::get('/retailer_list', ['as' => $route_slug.'retailerList',   
				                              'uses' => $module_controller.'retailerList']);


			Route::get('/view_customer/{enc_id}', ['as'=>$route_slug.'view_customer', 
										           'uses'=>$module_controller.'view_customer']);

			Route::get('/create', 	 ['as'  =>$route_slug.'create', 
									  'uses'=>$module_controller.'create']);

			Route::post('/save',     ['as'   => $route_slug.'save', 
				                      'uses' => $module_controller.'save']);

			Route::get('/change_status',  [ 'as'   => $route_slug.'change_status', 
				                            'uses' => $module_controller.'statusUpdate']);

			Route::get('/edit_customer/{enc_id}', [ 'as'  =>$route_slug.'edit_customer', 
										            'uses'=>$module_controller.'edit_customer']);

			Route::post('multi_action',	['as'   => $route_slug.'multi_action',
				                         'uses' => $module_controller.'multi_action']);	
		});


		Route::group(array('prefix' => '/vendors'), function()
		{
			$route_slug        = "vendors_";
			$module_controller = "sales_manager\MakerController@";

			Route::get('/',  ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
			Route::get('/vendors_listing', ['as' => $route_slug.'vendors_listing',   'uses' => $module_controller.'vendors_listing']);

			Route::get('/view/{enc_id}',['as' =>$route_slug.'view' , 'uses' =>$module_controller.'view']);
			Route::get('/edit/{enc_id}',['as' =>$route_slug.'edit' , 'uses' =>$module_controller.'edit']);
			Route::get('/create',['as' => $route_slug.'create',	 'uses' => $module_controller.'create']);

		    Route::post('save',['as' => $route_slug.'save',	 'uses' => $module_controller.'save']);
			
			Route::get('activate', ['as' => $route_slug.'activate',	 'uses' => $module_controller.'activate']);	
			   Route::get('deactivate',['as'=> $route_slug.'deactivate',	 'uses' => $module_controller.'deactivate']);
				
			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
		});


		/*-----------------------------rep sales cancel orders ----------------------*/


		Route::group(array('prefix' => '/rep_sales_cancel_orders'), function()
		{
			$route_slug        = "rep_sales_cancel_orders";
			$module_controller = "sales_manager\RepSalesCanceledOrderController@";

			Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		
			Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
			
			Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

	    

		});


	});


