<?php

Route::group(['prefix' => $influencer_path,'middleware' => 'influencer'], function ()
{
	/*-------------------------START Dashboard-----------------------------------------*/
	$route_slug        = "dashboard";
	$module_controller = "Influencer\DashboardController@";
	
	Route::get('/dashboard', 	 ['as'=>$route_slug.'dashboard', 
								  'uses'=>$module_controller.'index']);
		
	/*-------------------------END Dashboard-----------------------------------------*/


	/*-------------------------START Account Settings--------------------------------*/		

	Route::any('/change_password',     ['as'   => 'change_password',   
						                'uses' => 'Influencer\AccountSettingsController@change_password']);

	Route::get('/logout',     ['as'   => 'logout',   
						       'uses' => 'Influencer\AccountSettingsController@logout']);

	Route::group(array('prefix' => '/account_settings'), function()
	{
		$route_slug        = "account_settings";
		$module_controller = "Influencer\AccountSettingsController@";

		Route::get('/', ['as' => $route_slug.'index',   'uses' => $module_controller.'index']);

		Route::post('/update', ['as' => $route_slug.'update', 'uses' => $module_controller.'update']);
	});	
	
	/*-------------------------END Account Settings--------------------------------*/	

	/*-------------------------START Promo Code--------------------------------*/		

	Route::group(array('prefix' => '/promo_code'), function()
	{
		$route_slug        = "promo_code";
		$module_controller = "Influencer\PromoCodeController@";

		Route::get('/', ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
		
		Route::get('/get_promo_code_listing', ['as' => $route_slug.'get_promo_code_listing',   'uses' => $module_controller.'get_promo_code_listing']);
		
	});	
	
	/*-------------------------END Promo Code--------------------------------*/
	
	/*-------------------------Customer Orders (START)--------------------------------*/

	Route::group(array('prefix' => '/customer_orders'), function()
	{	
		$route_slug       = "_influencer";
		$module_controller = "Influencer\CustomerOrdersController@";

		Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
		
		Route::get('get_customer_orders_listing', ['as' => $route_slug.'get_customer_orders_listing', 'uses' => $module_controller.'get_customer_orders_listing']);

		Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view']);

	});

	/*-------------------------Customer Orders (END)--------------------------------*/

	/*-------------------------Quote Requests   (START)--------------------------------*/

	Route::group(array('prefix' => '/quote_requests'), function()
	{	
		$route_slug       = "_influencer";
		$module_controller = "Influencer\QuoteRequestsController@";

		Route::get('/',			 ['as' => $route_slug.'index', 'uses' => $module_controller.'index']);
		
		Route::get('get_all_get_quote_requests', ['as' => $route_slug.'get_all_get_quote_requests', 'uses' => $module_controller.'get_all_get_quote_requests']);

		Route::get('view/{enc_id}',	 ['as' => $route_slug.'view',	  'uses' => $module_controller.'view_quote_request_details']);

	});

	/*-------------------------Quote Requests (END)--------------------------------*/


	/*--------------Influencer Rewards History (START)---------------------*/

    Route::group(array('prefix'=>'/rewards_history'), function()
    {
    	$route_slug = '_rewards_history';
		$module_controller = 'Influencer\RewardHistoryController@';

		Route::get('/', ['as'  =>$route_slug.'index',
						 'uses'=>$module_controller.'index']);

		Route::get('/get_influencer_rewards_history_listing', 
		['as' =>$route_slug.'get_influencer_rewards_history_listing',
		'uses'=>$module_controller.'get_influencer_rewards_history_listing']);

		Route::get('/details/{enc_id}', 
						['as'  =>$route_slug.'details',
						 'uses'=>$module_controller.'details']);
    });

    /*--------------Influencer Rewards History (END) ----------------------*/

    /*--------------Influencer Transaction History (START)---------------------*/

    Route::group(array('prefix'=>'/transaction_history'), function()
    {
    	$route_slug = '_transaction_history';
		$module_controller = 'Influencer\TransactionHistoryController@';

		Route::get('/', ['as'  =>$route_slug.'index',
						 'uses'=>$module_controller.'index']);

		Route::get('/get_transaction_history_listing', 
					['as'  =>$route_slug.'get_transaction_history_listing',
					 'uses'=>$module_controller.'get_transaction_history_listing']);
		
    });

    /*--------------Influencer Transaction History (END) ----------------------*/

	Route::group(array('prefix' => '/notifications'), function()
	{
		$route_slug       = "notifications_";
		$module_controller = "Influencer\NotificationsController@";

		Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
		Route::get('delete/{enc_id}',		 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);
		Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
		
		Route::get('/read_notification/{id}',     ['as' => $route_slug.'read_notification','uses' => $module_controller.'read_notification']);
	});

});


Route::get('/logout',     ['as' => 'logout', 'uses' => 'Front\AuthController@logout']);	