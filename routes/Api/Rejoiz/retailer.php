<?php




Route::group(['prefix'=>'retailer','middleware'=>'rejoiz_auth'],function()
{
	    Route::group(['prefix' => 'dashboard'], function ()
		{

			$module_controller = 'Api\Rejoiz\Retailer\DashboardController@';

			Route::get('/',['uses' => $module_controller.'index']);

		});


		Route::group(['prefix' => 'favorite'], function ()
		{

			$module_controller = 'Api\Rejoiz\Front\HomeController@';

			Route::post('/add',['uses' => $module_controller.'add_to_favorite']);
			Route::post('/remove',['uses' => $module_controller.'remove_from_favorite']);
			Route::get('/',['uses' => $module_controller.'my_favorite']);

		});

		Route::group(['prefix' => 'calculation_data'], function ()
		{

			$module_controller = 'Api\Rejoiz\Retailer\OrdersController@';

			Route::get('/',['uses' => $module_controller.'get_order_calculation_data']);

		});

	    Route::group(['prefix' => 'account_settings'], function ()
		{

			$module_controller = 'Api\Rejoiz\Retailer\AccountSettingsController@';

			Route::get('/',			['uses' => $module_controller.'index']);

			Route::post('/update',	['uses' => $module_controller.'update']);


		});

	

		Route::group(['prefix' => ''], function ()
		{
			$module_controller = 'Api\Rejoiz\Retailer\AccountSettingsController@';

			Route::post('/change_password',	['uses' => $module_controller.'change_password']);

		});


		Route::group(['prefix' => 'my_orders'],function ()
		{

			$route_slug        = "order";
			$module_controller = "Api\Rejoiz\Retailer\OrdersController@";

			Route::get('/',					['as' => $route_slug.'index', 'uses' => $module_controller.'index']);

			Route::post('/product_listing',	['as' => $route_slug.'product_listing', 'uses' => $module_controller.'product_listing']);

			Route::get('/details',	        ['as' => $route_slug.'details', 'uses' => $module_controller.'details']);

			Route::any('/reorder',	        ['as' => $route_slug.'reorder', 'uses' => $module_controller.'reorder']);

			Route::post('/save_address',    ['as' => $route_slug.'save_address', 'uses' => $module_controller.'save_address']);

			Route::get('/product_details',  ['as' => $route_slug.'product_details', 'uses' => $module_controller.'product_details']);

			Route::post('/add_to_bag',      ['as' => $route_slug.'add_to_bag', 'uses' => $module_controller.'add_to_bag']);

			Route::post('/remove_from_bag', ['as' => $route_slug.'remove_from_bag', 'uses' => $module_controller.'remove_from_bag']);

			Route::post('/update_product_qty', ['as' => $route_slug.'update_product_qty', 'uses' => $module_controller.'update_product_qty']);

			Route::get('/summary',          ['as' => $route_slug.'summary', 'uses' => $module_controller.'summary']);

			Route::post('/save',            ['as' => $route_slug.'save', 'uses' => $module_controller.'save']);

			Route::get('/find_products',    ['as' => $route_slug.'save', 'uses' => $module_controller.'find_products']);

			Route::get('/retailer_details', ['as' => $route_slug.'retailer_details', 'uses' => $module_controller.'retailer_details']);

			Route::get('/sku_details', ['as' => $route_slug.'sku_details', 'uses' => $module_controller.'sku_details']);

		    Route::post('/cancel_order',	[
				                             'as' => $route_slug.'retailer_cancel_order', 
				                             'uses' => $module_controller.'retailer_cancel_order'
				                            ]);

        });

        Route::group(array('prefix' => '/card'), function()
		{
			$route_slug        = "card";
			$module_controller = "Api\Rejoiz\Retailer\CardController@";

			Route::get('/',    [
				                 'as'  => $route_slug.'get_user_cards', 
				                 'uses' => $module_controller.'index'
				               ]);

		    Route::post('add', [
				                'as'   => $route_slug.'add',
				                'uses' => $module_controller.'add'
				                ]);

		    Route::post('edit', [
				                'as'   => $route_slug.'edit',
				                'uses' => $module_controller.'edit'
				                ]);  
		    Route::get('update', [
				                'as'   => $route_slug.'update',
				                'uses' => $module_controller.'update'
				                ]);
		    Route::get('delete', [
				                'as'   => $route_slug.'delete',
				                'uses' => $module_controller.'delete'
				                ]);
		});	


        Route::group(['prefix' => 'rep_sales_orders'],function ()
		{

			$route_slug        = "rep_sales_orders";
			$module_controller = "Api\Rejoiz\Retailer\OrdersController@";

			Route::get('/',					[
				                              'as'   => $route_slug.'rep_sales_orders', 
				                              'uses' => $module_controller.'rep_sales_orders'
				                            ]);

			Route::get('/details',		    [
				 							  'as'   => $route_slug.'details', 
				 							  'uses' => $module_controller.'rep_sale_order_details'
				 							]);

			Route::post('/product_listing',	[
				                             'as'   => $route_slug.'product_listing', 
				                             'uses' => $module_controller.'rep_sales_product_listing'
				                            ]);	

		    Route::post('/cancel_order',	[
				                             'as'   => $route_slug.'rep_sale_cancel_order', 
				                             'uses' => $module_controller.'rep_sale_cancel_order'
				                            ]);

			Route::any('/reorder',	        [
				                              'as' => $route_slug.'rep_sales_reorder', 
				                              'uses' => $module_controller.'rep_sales_reorder'
				                            ]);

			Route::get('/summary',	        [
				                              'as' => $route_slug.'summary', 
				                              'uses' => $module_controller.'summary'
				                            ]);
			
			Route::post('/update_product_qty',	        [
				                              'as' => $route_slug.'update_product_qty', 
				                              'uses' => $module_controller.'update_product_qty'
				                            ]);
			Route::post('/remove_from_bag',	        [
				                              'as' => $route_slug.'remove_from_bag', 
				                              'uses' => $module_controller.'remove_from_bag'
				                            ]);
	        Route::get('/retailer_details',	        [
				                              'as' => $route_slug.'retailer_details', 
				                              'uses' => $module_controller.'retailer_details'
				                            ]);
	        Route::post('/save_address',	        [
				                              'as' => $route_slug.'save_address', 
				                              'uses' => $module_controller.'save_address'
				                            ]); 
	        Route::post('/apply_promocode',	        [
				                              'as' => $route_slug.'apply_promocode', 
				                              'uses' => $module_controller.'apply_promocode'
				                            ]);
	        Route::post('/clear_promo_code',	        [
				                              'as' => $route_slug.'clear_promocode', 
				                              'uses' => $module_controller.'clear_promocode'
				                            ]);


        });

        Route::group(['prefix' => 'transaction'], function ()
		{

			$module_controller = 'Api\Rejoiz\Retailer\TransactionController@';

			Route::get('/', ['uses' => $module_controller.'get_transactions']);
			
			Route::get('export', ['uses' => $module_controller.'get_export_transasction_orders']);

		});

		Route::group(['prefix' => 'notifications'], function ()
		{

			$module_controller = 'Api\Rejoiz\Retailer\NotificationsController@';

			Route::get('/',			['uses' => $module_controller.'index']);

			Route::get('/delete',				['uses' => $module_controller.'delete']);
			
			Route::get('/count',				['uses' => $module_controller.'count']);

			Route::get('/change_view_status',	['uses' => $module_controller.'change_view_status']);
			
		});



});	





