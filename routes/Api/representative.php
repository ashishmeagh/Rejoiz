<?php

Route::group(['prefix'=>'/representative','middleware'=>'api.auth'],function()
{

	    Route::group(['prefix' => 'dashboard'], function ()
		{

			$module_controller = 'Api\Representative\DashboardController@';

			Route::get('/',['uses' => $module_controller.'index']);

		});


		Route::group(['prefix' => 'account_settings'], function ()
		{

			$module_controller = 'Api\Representative\AccountSettingsController@';

			Route::any('/',			['uses' => $module_controller.'index']);

			Route::post('/update',	['uses' => $module_controller.'update']);

		});

		Route::group(['prefix' => ''], function ()
		{
			$module_controller = 'Api\Representative\AccountSettingsController@';

			Route::post('/change_password',	['uses' => $module_controller.'change_password']);

		});


		Route::group(['prefix' => 'retailers'], function () {

			$module_controller = 'Api\Representative\RetailersController@';

			Route::get('/',							['uses' => $module_controller.'index']);

			Route::post('/create',					['uses' => $module_controller.'create']);

			Route::post('/change_status',	['uses' => $module_controller.'change_status']);

			Route::get('/name_list',			    ['uses' => $module_controller.'name_list']);	

			Route::get('/details',			        ['uses' => $module_controller.'details']);		
		});


		Route::group(['prefix' => 'vendors'], function () {

			$module_controller = 'Api\Representative\VendorsController@';

			Route::get('/',					['uses' => $module_controller.'index']);

			Route::get('/product_list',		['uses' => $module_controller.'product_list']);

			Route::get('/details/{enc_id}',	['uses' => $module_controller.'details']);

		});

		/*Route::group(['prefix' => 'notifications'], function () {

			$module_controller	= 'Api\Representative\NotificationsController@';*/


       Route::group(['prefix' => 'notifications'], function ()
		{

			$module_controller = 'Api\Representative\NotificationsController@';

			Route::get('/',			['uses' => $module_controller.'index']);

			Route::get('/delete',				['uses' => $module_controller.'delete']);
			
			Route::get('/count',				['uses' => $module_controller.'count']);

			Route::get('/change_view_status',	['uses' => $module_controller.'change_view_status']);
			
		});


        Route::group(['prefix' => 'orders'],function ()
		{

			$route_slug        = "order";
			$module_controller = "Api\Representative\OrdersController@";

			Route::get('/',					['as' => $route_slug.'index', 'uses' => $module_controller.'index']);

			Route::get('/product_listing',	['as' => $route_slug.'product_listing', 'uses' => $module_controller.'product_listing']);

			Route::get('/details',	        ['as' => $route_slug.'details', 'uses' => $module_controller.'details']);

			Route::post('/reorder',	        ['as' => $route_slug.'reorder', 'uses' => $module_controller.'reorder']);

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

        });

});	





