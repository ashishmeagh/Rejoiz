<?php

Route::group(['prefix' => $maker_path,'middleware' => 'maker'], function ()
{
	/*-------------------------START Dashboard-----------------------------------------*/
	$route_slug        = "dashboard";
	$module_controller = "Maker\DashboardController@";
	
	Route::get('/dashboard', 	 ['as'=>$route_slug.'dashboard', 
								  'uses'=>$module_controller.'index']);	
	/*-------------------------END Dashboard-----------------------------------------*/

	/*-------------------------START Account Settings--------------------------------*/		
	Route::group(array('prefix' => '/account_settings'), function()
	{
		$route_slug        = "account_settings";
		$module_controller = "Maker\AccountSettingsController@";

		Route::get('/',                  ['as' => $route_slug.'account_settings_show',   'uses' => $module_controller.'index']);
		Route::post('/update/{enc_id}', ['as' => $route_slug.'account_settings_update', 'uses' => $module_controller.'update']);
		Route::post('/update_stripe_settings/{enc_id}', ['as' => $route_slug.'update_stripe_settings', 'uses' => $module_controller.'update_stripe_settings']);
		Route::post('/verify_password', ['as' => $route_slug.'verify_password', 'uses' => $module_controller.'verify_password']);


	});

	Route::any('/does_exists_tax_id/{param?}',['uses' => 'Maker\AccountSettingsController@does_exists_tax_id']);
		
	/*-------------------------END Account Settings--------------------------------*/	

	/*-------------------------START Shop-----------------------------------------*/
	Route::group(array('prefix' => '/company_settings'), function()
	{
		$route_slug        = "shop";
		$module_controller = "Maker\ShopController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'my_shop', 
									  'uses'=>$module_controller.'my_shop']);

		Route::get('/images', ['as'=>$route_slug.'images', 
									  	'uses'=>$module_controller.'images']);

		Route::post('/save_images', ['as'=>$route_slug.'save_images', 
									  	'uses'=>$module_controller.'save_images']);	

		Route::get('/shop_settings', ['as'=>$route_slug.'shop_settings', 
									  	'uses'=>$module_controller.'shop_settings']);

		Route::post('/save_settings', ['as'=>$route_slug.'save_settings', 
									  	'uses'=>$module_controller.'save_settings']);
		

		Route::get('/shop_story', ['as'=>$route_slug.'shop_story', 
									  	'uses'=>$module_controller.'shop_story']);

		Route::post('/save_shop_story', ['as'=>$route_slug.'save_shop_story', 
									  	'uses'=>$module_controller.'save_shop_story']);
	});

	/*-------------------------END Shop-----------------------------------------*/

	Route::group(array('prefix' => '/brand'), function()
	{
		$route_slug        = "brand";
		$module_controller = "Maker\BrandController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'index', 
									  'uses'=>$module_controller.'index']);

		Route::get('/create', ['as'=>$route_slug.'add', 
									  	'uses'=>$module_controller.'create']);

		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  	'uses'=>$module_controller.'edit']);

		Route::any('/does_brand_exists/{enc_id?}', ['as'=>$route_slug.'does_brand_exists', 
									  	'uses'=>$module_controller.'does_brand_exists']);

		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);

		Route::get('/all_brands', ['as'=>$route_slug.'all_brands', 
									  	'uses'=>$module_controller.'all_brands']);

		Route::get('/change_status', ['as'=>$route_slug.'change_status', 
									  	'uses'=>$module_controller.'change_status']);

		Route::post('/store', ['as'=>$route_slug.'store', 
									  	'uses'=>$module_controller.'store']);	

		Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

	});



	/*-------------------------Promotions-----------------------------------------*/

	Route::group(array('prefix' => '/promotions'), function()
	{
		$route_slug        = "promotions";
		$module_controller = "Maker\PromotionsController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'index', 
									  'uses'=>$module_controller.'index']);

		Route::get('/create', ['as'=>$route_slug.'create', 
									  	'uses'=>$module_controller.'create']);

		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  	'uses'=>$module_controller.'edit']);
		
		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);

		Route::get('/view/{enc_id}', ['as'=>$route_slug.'view', 
									  	'uses'=>$module_controller.'view']);

		Route::get('/delete_row', ['as'=>$route_slug.'delete_row', 
									  	    'uses'=>$module_controller.'delete_row']);

		Route::get('/get_maker_promotions', ['as'  =>$route_slug.'get_maker_promotions', 
									  	     'uses'=>$module_controller.'get_maker_promotions']);

		Route::get('/change_status', ['as'  =>$route_slug.'change_status', 
									  'uses'=>$module_controller.'change_status']);

		Route::post('/store', ['as'  =>$route_slug.'store', 
							   'uses'=>$module_controller.'store']);	

		Route::post('/multi_action', ['as'   => $route_slug.'multi_action',
			                          'uses' => $module_controller.'multi_action']);

	});
	


    /*---------------------------Promo Code--------------------------------------------*/
    Route::group(array('prefix' => '/promo_code'), function()
	{
		$route_slug        = "promo_code";
		$module_controller = "Maker\PromoCodeController@";
		
		Route::get('/', 	        ['as'=>$route_slug.'index', 
									 'uses'=>$module_controller.'index']);

		Route::get('/create',       ['as'=>$route_slug.'create', 
									 'uses'=>$module_controller.'create']);

		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  'uses'=>$module_controller.'edit']);
		
		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);

		Route::get('/view/{enc_id}',   ['as'=>$route_slug.'view', 
									  	'uses'=>$module_controller.'view']);
	
		Route::get('/get_maker_promo_code', ['as'  =>$route_slug.'get_maker_promo_code', 
									  	     'uses'=>$module_controller.'get_maker_promo_code']);

		Route::get('/change_status',    ['as'  =>$route_slug.'change_status', 
									     'uses'=>$module_controller.'change_status']);

		Route::post('/store',           ['as'  =>$route_slug.'store', 
							             'uses'=>$module_controller.'store']);	

		Route::post('/multi_action',    ['as'   => $route_slug.'multi_action',
			                             'uses' => $module_controller.'multi_action']);

	});


    /*--------------------------------------------------------------------------------*/

	/*-------------------------Customer  Start-----------------------------------------*/

	Route::group(array('prefix' => '/customers_listing'), function()
	{
		$route_slug        = "customers_listing";
		$module_controller = "Maker\CustomerController@";

		Route::get('/', 	 ['as'=>$route_slug.'customers_listing', 
									  'uses'=>$module_controller.'customers_listing']);

		Route::get('/get_listing_data', 	 ['as'=>$route_slug.'get_listing_data', 
									  'uses'=>$module_controller.'get_listing_data']);
		Route::get('/get_listing_quote_data', 	 ['as'=>$route_slug.'get_listing_quote_data', 
									  'uses'=>$module_controller.'get_listing_quote_data']);

		Route::get('/get_quote_listing_data', 	 ['as'=>$route_slug.'get_quote_listing_data', 
									  'uses'=>$module_controller.'get_quote_listing_data']);

		


		Route::get('/via_leads', 	 ['as'=>$route_slug.'via_leads', 
									  'uses'=>$module_controller.'index_vie_lead']);

		Route::get('/get_listing_data_vie_lead', 	 ['as'=>$route_slug.'get_listing_data_vie_lead', 
									  'uses'=>$module_controller.'get_listing_data_vie_lead']);
	});

	/*-------------------------Customer  End-----------------------------------------*/


	/*-------------------------Representative Leads-----------------------------------------*/
	Route::group(array('prefix' => '/leads_by_representative'), function()
	{
		$route_slug        = "leads_by_representative";
		$module_controller = "Maker\RepresentativeLeadsController@";

		Route::get('/', 	 ['as'=>$route_slug.'leads_by_representative', 
									  'uses'=>$module_controller.'index']);

		Route::get('/get_leads', 	 ['as'=>$route_slug.'get_leads', 
									  'uses'=>$module_controller.'get_leads']);
		
		Route::get('/view/{enc_id}', 	 ['as'=>$route_slug.'view', 
									  'uses'=>$module_controller.'view']);

		Route::group(array('prefix' => '/conversation'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Maker\LeadConversationController@";

			Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

			Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

			Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
		});

	});

	/*-------------------------START Products-----------------------------------------*/
	Route::group(array('prefix' => '/products'), function()
	{
		$route_slug        = "shop";
		$module_controller = "Maker\ProductController@";
		
		Route::get('/', 	 ['as'  =>$route_slug.'products', 
							  'uses'=>$module_controller.'index']);	
		
	    Route::get('/product_invetory_script', ['as' => $route_slug.'maker-product_invetory_script',   'uses' => $module_controller.'product_invetory_script']);	

	    

		Route::get('/get_maker_products',[ 'as'   => $route_slug.'get_maker_products', 
									     'uses' => $module_controller.'get_maker_products']);

		Route::get('/create',[ 'as'   => $route_slug.'create', 
									     'uses' => $module_controller.'create']);

		Route::get('/edit/{enc_product_id}',[ 'as'   => $route_slug.'edit', 
									     'uses' => $module_controller.'edit']);	

		Route::any('/delete_multi_image',[ 'as'   => $route_slug.'delete_multi_image', 
									     'uses' => $module_controller.'delete_multi_image']);	

		Route::get('/get_export_maker_products',[ 'as'   => $route_slug.'get_export_maker_products', 
									     'uses' => $module_controller.'get_export_maker_products']);	

		Route::get('changeStatus',['as'=> $route_slug.'changeStatus',	 'uses' => $module_controller.'changeStatus']);



		Route::get('changeProductStatus',['as'=> $route_slug.'changeProductStatus',	 'uses' => $module_controller.'changeProductStatus']);
		

		Route::post('storeProduct/',  ['as' => $route_slug.'storeProduct', 
										   'uses' => $module_controller.'storeProduct']);

		Route::post('storeStyleAndDiemensions/',  ['as' => $route_slug.'storeStyleAndDiemensions', 
										   'uses' => $module_controller.'storeStyleAndDiemensions']);

		Route::post('storeAdditionalImages/',  ['as' => $route_slug.'storeAdditionalImages', 
										   'uses' => $module_controller.'storeAdditionalImages']);

		Route::post('storeCategories/',  ['as' => $route_slug.'storeCategories', 
										   'uses' => $module_controller.'storeCategories']);

		Route::get('view/{enc_product_id}',  ['as' => $route_slug.'view', 
										   'uses' => $module_controller.'view']);

		Route::get('product_details/{enc_product_id}',  ['as' => $route_slug.'product_details', 
										   'uses' => $module_controller.'product_details']);

		Route::post('updateStyleAndDiemensions',  ['as' => $route_slug.'updateStyleAndDiemensions', 
										   'uses' => $module_controller.'updateStyleAndDiemensions']);	

		Route::post('addnewrow',  ['as' => $route_slug.'addnewrow', 
										   'uses' => $module_controller.'addnewrow']);

		Route::any('addneweditrow',  ['as' => $route_slug.'addneweditrow', 
										   'uses' => $module_controller.'addneweditrow']);

	    Route::any('deleterow',  ['as' => $route_slug.'deleterow', 
										   'uses' => $module_controller.'deleterow']);		

		Route::post('update_product_category',  ['as' => $route_slug.'update_product_category', 
										         'uses' => $module_controller.'update_product_category']);	

		
		Route::any('does_exists/{param?}/{pid?}',['uses' => 'Maker\ProductController@does_exists']);

		Route::any('does_exists_edit/{param?}/{pid?}',['uses' => 'Maker\ProductController@does_exists_edit']);	
		
		Route::get('export_sheet',['uses' => 'Maker\ProductController@export_sheet']);	

		Route::post('importExcel',['uses' => 'Maker\ProductController@importExcel']);	

		Route::get('delete/{enc_id}',['as' => $route_slug.'delete','uses' => $module_controller.'delete']);

	    Route::post('multi_action',	['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
	    Route::get('getSkuImage',['uses' => 'Maker\ProductController@getSkuImage']);	
	    Route::post('show_size',['uses'=>'Maker\ProductController@show_size']);
	    Route::post('size_inventory/',  ['as' => $route_slug.'size_inventory', 
											 'uses' => $module_controller.'size_inventory']);

	});

	Route::group(array('prefix' => '/product_images'), function()
	{
		$route_slug        = "product_images";
		$module_controller = "Maker\ProductImageController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'index', 
									  'uses'=>$module_controller.'index']);
	    Route::post('uploadZip',['uses' => $module_controller.'uploadZip']);

	    Route::get('extract',['uses' => $module_controller.'extractZipFile']);	

	});	

	/* Upload multiple images for old products */
	Route::group(array('prefix' => '/product_old_images'), function()
	{
		$route_slug        = "product_old_images";
		$module_controller = "Maker\ProductOldImageController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'index', 
									  'uses'=>$module_controller.'index']);
	    Route::post('uploadZip',['uses' => $module_controller.'uploadZip']);

	    Route::get('extract',['uses' => $module_controller.'extractZipFile']);	

	});

	/* ------------------------ Requested categories and subcategories -------------*/

	Route::group(array('prefix' => '/request_category'), function()
	{
		$route_slug        = "request_category";
		$module_controller = "Maker\RequestCategoryController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'index', 
									  'uses'=>$module_controller.'index']);  

		Route::get('/create', ['as'=>$route_slug.'create', 
									  	'uses'=>$module_controller.'create']);

		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  	'uses'=>$module_controller.'edit']);

		Route::any('/does_brand_exists/{enc_id?}', ['as'=>$route_slug.'does_brand_exists', 
									  	'uses'=>$module_controller.'does_brand_exists']);

		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);

		Route::get('/all_request_categories', ['as'=>$route_slug.'all_request_categories', 
									  	'uses'=>$module_controller.'all_request_categories']);

		Route::get('/change_status', ['as'=>$route_slug.'change_status', 
									  	'uses'=>$module_controller.'change_status']);

		Route::post('/store', ['as'=>$route_slug.'store', 
									  	'uses'=>$module_controller.'store']);	

		Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);  

	});

	Route::group(['prefix'=>'request_sub_category'],function()
        {
            $route_slug = 'sub_category';
            $module_controller = "Maker\RequestSubCategoryController@";

            Route::get('/', ['as' => $route_slug.'sub_category', 'uses' => $module_controller.'index']);

            Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

            Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

            Route::post('/store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);

            Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

            Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

			Route::get('/get_all_subcategory', ['as' => $route_slug.'get_all_subcategory', 'uses' => $module_controller.'get_all_subcategory']);
        });

		Route::group(['prefix'=>'request_third_level_category'],function()
        {
            $route_slug = 'request_third_level_category';
            $module_controller = "Maker\RequestThirdSubCategoryController@";

            Route::get('/', ['as' => $route_slug.'sub_category', 'uses' => $module_controller.'index']);

            Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

            Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

            Route::post('/store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);

            Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

            Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

			Route::get('/get_all_subcategory', ['as' => $route_slug.'get_all_subcategory', 'uses' => $module_controller.'get_all_subcategory']);
        });

		Route::group(['prefix'=>'request_fourth_level_category'],function()
        {
            $route_slug = 'request_fourth_level_category';
            $module_controller = "Maker\RequestFourthSubCategoryController@";

            Route::get('/', ['as' => $route_slug.'sub_category', 'uses' => $module_controller.'index']);

            Route::get('/create', ['as' => $route_slug.'create', 'uses' => $module_controller.'create']);

            Route::get('/edit/{enc_id}', ['as' => $route_slug.'edit', 'uses' => $module_controller.'edit']);

            Route::post('/store', ['as' => $route_slug.'store', 'uses' => $module_controller.'store']);

            Route::get('/delete/{enc_id}', ['as' => $route_slug.'delete', 'uses' => $module_controller.'delete']);

            Route::get('/change_status', ['as' => $route_slug.'change_status', 'uses' => $module_controller.'status_update']);

			Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);

			Route::get('/get_all_subcategory', ['as' => $route_slug.'get_all_subcategory', 'uses' => $module_controller.'get_all_subcategory']);
        });

	/*------------------------------------------------------------------------------*/

	/*-------------------------END Products-----------------------------------------*/

        Route::group(array('prefix' => '/notifications'), function()
		{
			$route_slug       = "notifications_";
			$module_controller = "Maker\MakerNotificationController@";

			Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'index']);
			Route::get('delete/{enc_id}',	 ['as' => $route_slug.'edit',	 'uses' => $module_controller.'delete']);
			Route::post('multi_action',		 ['as' => $route_slug.'multi_action','uses' => $module_controller.'multi_action']);	
			Route::get('/export', 			 ['as' => $route_slug.'export',	  'uses' => $module_controller.'export_notifications']);

			
			Route::get('/read_notification/{id}',     ['as' => $route_slug.'read_notification','uses' => $module_controller.'read_notification']);
		});


	/*-------------------------Retailer Quotes Start-----------------------------------------*/

	Route::group(array('prefix' => '/retailer_orders'), function()
	{
		$route_slug        = "retailer_orders";
		$module_controller = "Maker\RetailerQuotesController@";

		Route::get('/', 	          ['as' =>$route_slug.'retailer_orders', 
									   'uses'=>$module_controller.'index']);

		Route::get('/get_enquiries',  ['as'  =>$route_slug.'get_enquiries', 
									   'uses'=>$module_controller.'get_enquiries']);

		Route::get('/get_export_retailer_orders',  ['as'  =>$route_slug.'get_export_retailer_orders', 
									   'uses'=>$module_controller.'get_export_retailer_orders']);
		
		
		Route::get('/view/{enc_id}', ['as'  =>$route_slug.'view', 
									  'uses'=>$module_controller.'view']);

		Route::post('/ship_order', 	 ['as'  =>$route_slug.'ship_order', 
									  'uses'=>$module_controller.'ship_order']);

		Route::post('/save_track_details', ['as'  =>$route_slug.'save_track_details', 
									        'uses'=>$module_controller.'saveOrderTrackDetails']);

		Route::group(array('prefix' => '/conversation'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Maker\QuoteConversationController@";

			Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

			Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

			Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
		});


	});


	Route::group(array('prefix' => '/customer_orders'), function()
	{
		$route_slug        = "customer_orders";
		$module_controller = "Maker\CustomerQuotesController@";

		Route::get('/', 	          ['as' =>$route_slug.'customer_orders', 
									   'uses'=>$module_controller.'index']);

		Route::get('/get_export_customer_orders',  
										['as'  =>$route_slug.'get_export_customer_orders', 
									   'uses'=>$module_controller.'get_export_customer_orders']);

		Route::get('/get_enquiries',  ['as'  =>$route_slug.'get_enquiries', 
									   'uses'=>$module_controller.'get_enquiries']);
		
		Route::get('/view/{enc_id}', ['as'  =>$route_slug.'view', 
									  'uses'=>$module_controller.'view']);

		Route::post('/ship_order', 	 ['as'  =>$route_slug.'ship_order', 
									  'uses'=>$module_controller.'ship_order']);

		Route::post('/save_track_details', ['as'  =>$route_slug.'save_track_details', 
									        'uses'=>$module_controller.'saveOrderTrackDetails']);

		Route::group(array('prefix' => '/conversation'), function()
		{
			$route_slug       = "conversation_";
			$module_controller = "Maker\QuoteConversationController@";

			Route::get('/{enc_id}',	 ['as' => $route_slug.'conversation',	  'uses' => $module_controller.'index']);

			Route::post('/send_message',	 ['as' => $route_slug.'send_message', 'uses' => $module_controller.'send_message']);		

			Route::get('/chat/get_message',	 ['as' => $route_slug.'get_message', 'uses' => $module_controller.'get_message']);		
		});


	});

	Route::group(array('prefix' => '/representative_orders'),function()
	{
		$route_slug        = "representative_orders";
		$module_controller = "Maker\MyOrdersController@";

		Route::get('/',['as' => $route_slug.'representative_orders', 'uses' => $module_controller.'get_order_from_representative']);

		Route::get('/get_listing', ['as' => $route_slug.'get_listing',   'uses' => $module_controller.'get_listing']);

		Route::get('/get_export_representative_orders', ['as' => $route_slug.'get_export_representative_orders',   'uses' => $module_controller.'get_export_representative_orders']);


		Route::get('/view/{enc_id}',['as' =>$route_slug.'view' , 'uses' =>$module_controller.'view']);
		
		Route::post('/ship_order', 	 ['as'=>$route_slug.'ship_order', 
									          'uses'=>$module_controller.'ship_order']);

		Route::post('/save_track_details', ['as'  =>$route_slug.'save_track_details', 
									        'uses'=>$module_controller.'saveOrderTrackDetails']);

	});


	/*-----------------------------Retailer Cancel Order-------------------------------------*/

    Route::group(array('prefix' =>'/retailer_cancel_orders'),function(){

        $route_slug        = "retailer_cancel_orders";
		$module_controller = "Maker\OrderCancelController@";

		Route::get('/', 	          ['as' =>$route_slug.'retailer_cancel_orders', 
									  'uses'=>$module_controller.'index']);

		Route::get('/get_enquiries',  ['as'  =>$route_slug.'get_enquiries', 
									   'uses'=>$module_controller.'get_enquiries']);
		
		Route::get('/view/{enc_id}',  ['as'  =>$route_slug.'view', 
									   'uses'=>$module_controller.'view']);

		Route::post('/order_confirmation',  ['as'  =>$route_slug.'order_confirmation', 
									         'uses'=>$module_controller.'order_confirmation']);

    });


	/*---------------------------------------------------------------------------------------*/
	/*-----------------------------Customer Cancel Order Request -------------------------------------*/

    Route::group(array('prefix' =>'/customer_cancel_orders_request'),function(){

        $route_slug        = "customer_cancel_orders_request";
		$module_controller = "Maker\CustomerOrderCancelRequestController@";

		Route::get('/', 	          ['as' =>$route_slug.'customer_cancel_orders_request', 
									  'uses'=>$module_controller.'index']);

		Route::get('/get_enquiries',  ['as'  =>$route_slug.'get_enquiries', 
									   'uses'=>$module_controller.'get_enquiries']);
		
		Route::get('/view/{enc_id}',  ['as'  =>$route_slug.'view', 
									   'uses'=>$module_controller.'view']);

		Route::post('/order_confirmation',  ['as'  =>$route_slug.'order_confirmation', 
									         'uses'=>$module_controller.'order_confirmation']);

    });


	/*---------------------------------------------------------------------------------------*/


    /*----------------------------rep sales order cancel request---------------*/ 

    Route::group(array('prefix' =>'/cancel_order_requests'),function(){

        $route_slug        = "cancel_order_requests";
		$module_controller = "Maker\RepSalesCancelRequestController@";

		Route::get('/', 	          ['as' =>$route_slug.'retailer_cancel_orders', 
									  'uses'=>$module_controller.'index']);

		Route::get('/get_enquiries',  ['as'  =>$route_slug.'get_enquiries', 
									   'uses'=>$module_controller.'get_enquiries']);
		
		Route::get('/view/{enc_id}',  ['as'  =>$route_slug.'view', 
									   'uses'=>$module_controller.'view']);

		Route::post('/order_confirmation',  ['as'  =>$route_slug.'order_confirmation', 
									         'uses'=>$module_controller.'order_confirmation']);

    });

    /*----------------------retailer cancel order ------------------------------------*/


    Route::group(array('prefix' => '/cancel_orders'), function()
	{
		$route_slug        = "cancel_orders";
		$module_controller = "Maker\RetailerCancelOrderController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		

		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		Route::get('/get_export_retailer_cancel_order', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_retailer_cancel_order']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

		Route::get('refund_process',	 ['as' => $route_slug.'view','uses' => $module_controller.'refund_payment']);		

    

	});
/*----------------------customer cancel order ------------------------------------*/


    Route::group(array('prefix' => '/customer_cancel_orders'), function()
	{
		$route_slug        = "customer_cancel_orders";
		$module_controller = "Maker\CustomerCancelOrderController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		

		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		Route::get('/get_export_customer_cancel_order', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_customer_cancel_order']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

		Route::get('refund_process',	 ['as' => $route_slug.'view','uses' => $module_controller.'refund_payment']);
    

	});



	/*--------------------------------transactions------------------------*/
     
        Route::group(['prefix'=>'transactions'],function () 
	    {
	    	$module_controller = "Maker\TransactionTableController@";
	        
	         Route::any('/get_transaction_details',['uses' => $module_controller.'get_transaction_details']);
	       
	         Route::any('/vendor_transaction',['uses' => $module_controller.'transactions']);

	         Route::any('/admin_transaction',['uses' => $module_controller.'admin_transactions']);

	         Route::any('/get_export_admin_transaction',['uses' => $module_controller.'get_export_admin_transaction']);

	          Route::any('/get_export_vendor_transaction',['uses' => $module_controller.'get_export_vendor_transaction']);


	         Route::any('/show_transaction_details',['uses' => $module_controller.'show_transaction_details']);
	         
	         Route::any('/all',['uses' => $module_controller.'all_transaction_details']);
	         
	         Route::any('/vendor',['uses' => $module_controller.'vendor_transaction_details']);

	         Route::any('/admin',['uses' => $module_controller.'admin_transaction_details']);

	         Route::any('/customer',['uses' => $module_controller.'customer_transaction_details']);

	         Route::get('/get_customer_transaction_details',['uses' => $module_controller.'get_customer_transaction_details']);
	         
	         Route::any('/representative',['uses' => $module_controller.'reps_transaction_details']);

	         Route::get('/sales_manager',['uses' => $module_controller.'sales_manager_transaction_details']);

	         Route::any('/influencer',['uses' => $module_controller.'influencer_transaction_details']);

	         Route::get('/get_influencer_transaction_details',['uses' => $module_controller.'get_influencer_transaction_details']);
		});


	/*---------------------------------------------------------------------------*/

	Route::group(['prefix'=>'refund'],function () 
	    {
	    	$module_controller = "Maker\TransactionTableController@";

	    	Route::get('retailer',['uses' => $module_controller.'retailer_refunds']);
	    	Route::get('retailer/all',['uses' => $module_controller.'get_retailer_refund_details']);
	    	Route::get('retailer/get_export_retailer_refund_transaction',['uses' => $module_controller.'get_export_retailer_refund_transaction']);

	    	Route::get('rep_sales',['uses' => $module_controller.'rep_sales_refunds']);
	    	Route::get('rep_sales/all',['uses' => $module_controller.'get_rep_sales_refund_details']);
	    	Route::get('rep_sales/get_export_reps_sales_refund_transactions',['uses' => $module_controller.'get_export_reps_sales_refund_transactions']);

	    	Route::get('customer',['uses'     => $module_controller.'customer_refunds']);
	    	Route::get('customer/all',['uses' => $module_controller.'get_customer_refund_details']);
	    	Route::get('customer/get_export_customer_refund_transaction',['uses' => $module_controller.'get_export_customer_refund_transaction']);
	    });


	/*-----------------------------rep sales cancel orders ----------------------*/


	Route::group(array('prefix' => '/rep_sales_cancel_orders'), function()
	{
		$route_slug        = "rep_sales_cancel_orders";
		$module_controller = "Maker\RepSalesCanceledOrderController@";

		Route::get('/', ['as' => $route_slug.'list',   'uses' => $module_controller.'index']);
		

		Route::get('/get_my_orders', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_my_orders']);
		Route::get('/get_export_rep_sale_cancel_order', ['as' => $route_slug.'list',   'uses' => $module_controller.'get_export_rep_sale_cancel_order']);
		
		Route::get('/view/{enc_id}', ['as' => $route_slug.'view',   'uses' => $module_controller.'view']);

    
		Route::get('refund_process/',	 ['as' => $route_slug.'view','uses' => $module_controller.'refund_payment']);	
	});


	Route::group(array('prefix' => '/my_representative'),function(){

		$route_slug    = "my representative";
		$module_controller  = "Maker\MyRepresentativeController@";

		Route::get('/',['as' => $route_slug.'list','uses' => $module_controller.'index']);


		Route::get('/get_listing_data', ['as'=>$route_slug.'get_listing_data', 
									  'uses'=>$module_controller.'get_listing_data']);
		Route::get('/view/{enc_id}',  ['as'  =>$route_slug.'view', 
									   'uses'=>$module_controller.'view']);
		


	});


	Route::group(array('prefix' => '/commissions'),function(){

		$route_slug    = "commissions";
		$module_controller  = "Maker\CommissionController@";
		
		Route::any('/',['as' => $route_slug.'list','uses' => $module_controller.'index']);
		
		Route::any('/get_commissions', ['as'=>$route_slug.'get_commissions', 
									  'uses'=>$module_controller.'get_commissions']);
		

		Route::any('/commission_report_generator', ['as'=>$route_slug.'commission_report_generator', 
									  'uses'=>$module_controller.'commission_report_generator']);

		Route::any('/intermediate_commission_report_generator', ['as'=>$route_slug.'intermediate_commission_report_generator', 
									  'uses'=>$module_controller.'intermediate_commission_report_generator']);

		
		Route::any('/commission_invoice_generator/{lead_id}', ['as'=>$route_slug.'commission_invoice_generator', 
									  'uses'=>$module_controller.'commission_invoice_generator']);

		Route::get('/view/{enc_id}',  ['as'  =>$route_slug.'view', 
									   'uses'=>$module_controller.'view']);

		
		Route::get('/admin_fees/{user_id}',  ['as'  =>$route_slug.'view_direct_payment_to_vendor', 
									   'uses'=>$module_controller.'view_direct_payment_to_vendor']);

		Route::get('/payments/{user_id}',  ['as'  =>$route_slug.'view_payment_intermidiation_report', 
									   'uses'=>$module_controller.'view_payment_intermidiation_report']);
		
		Route::get('/direct_payment_to_vendor/{user_id}',  ['as'  =>$route_slug.'direct_payment_to_vendor', 
									   'uses'=>$module_controller.'direct_payment_to_vendor']);
		
		Route::get('/payment_intermediation_commission_reports',  ['as'  =>$route_slug.'payment_intermediation_commission_reports', 
									   'uses'=>$module_controller.'payment_intermediation_commission_reports']);

		Route::get('/get_commission_reports',  ['as'  =>$route_slug.'get_commission_reports', 
									   'uses'=>$module_controller.'get_commission_reports']);

		Route::get('/payment_intermediation',  ['as'  =>$route_slug.'payment_intermediation', 
									   'uses'=>$module_controller.'payment_intermediation']);
	});

	Route::group(array('prefix' => '/customer_commission_reports'),function(){

		$route_slug    = "customer_commission_reports";
		$module_controller  = "Maker\CommissionController@";
		
		Route::get('/admin_fees/{user_id}',  ['as'  =>$route_slug.'view_customer_direct_payment_to_vendor', 
									   'uses'=>$module_controller.'view_customer_direct_payment_to_vendor']);

		Route::get('/customer_direct_payment_commission_reports',  ['as'  =>$route_slug.'customer_direct_payment_commission_reports', 
									   'uses'=>$module_controller.'customer_direct_payment_commission_reports']);
		
		
		Route::get('/payments/{user_id}',  ['as'  =>$route_slug.'view_customer_payment_intermidiation_report', 
									   'uses'=>$module_controller.'view_customer_payment_intermidiation_report']);

		Route::get('/customer_indirect_payment_commission_reports',  ['as'  =>$route_slug.'customer_indirect_payment_commission_reports', 
									   'uses'=>$module_controller.'customer_indirect_payment_commission_reports']);

		Route::any('customer_report_generator',['as'=>$route_slug.'customer_report_generator','uses'=>$module_controller.'customer_report_generator']);

		Route::any('customer_intermidiate_report_generator',['as'=>$route_slug.'customer_intermidiate_report_generator','uses'=>$module_controller.'customer_intermidiate_report_generator']);
	});


	
	/*-------------------------Manage Representative-----------------------------------------*/	
	  	Route::group(array('prefix' => '/manage_representative'), function()
		{
			$route_slug       = "representative_";
			$module_controller = "Maker\RepresentativeLeadsController@";

			Route::get('/', 				 ['as' => $route_slug.'manage',	  'uses' => $module_controller.'manage_representative']);
			Route::get('/load_representative', 	 ['as'=>$route_slug.'get_leads', 
									  'uses'=>$module_controller.'load_representative']);

			Route::get('/commission/{enc_id}', 	 ['as'=>$route_slug.'get_leads', 
									  'uses'=>$module_controller.'commission']);

			Route::post('/set_commission', 	 ['as'=>$route_slug.'set_commission', 
									  'uses'=>$module_controller.'set_commission']);
			
			
		});


	/*--------------------------------- Payment Controller  --------------------------------*/

	Route::group(array('prefix' => '/payment'), function()
	{
		$route_slug        = "payment";
		$module_controller = "Maker\PaymentController@";


		Route::any('/representative_payment_received/{transaction_id}',['uses' => $module_controller.'representative_payment_received']);

		Route::get('representative_order_payment/{transaction_id}/{slug_chk_inventory?}', ['as'=>$route_slug.'payment', 
									          'uses'=>$module_controller.'representative_order_payment']);

		

		Route::post('/admin',['uses' => $module_controller.'pay_to_admin']);  

		Route::any('/payment_received/{transaction_id}',['uses' => $module_controller.'payment_received']);   

		Route::post('send_stripe_acc_creation_link', ['as' => $route_slug.'send_stripe_acc_creation_link',   'uses' => $module_controller.'send_stripe_acc_creation_link']);

		Route::get('/{transaction_id}/{slug_chk_qty_available_or_not?}', 	 ['as'=>$route_slug.'payment', 
									          'uses'=>$module_controller.'payment']);
		 Route::post('/payment_proof/{transaction_id}/', 	 [
									          'uses'=>$module_controller.'payment_proof']);  

	});


	// Customer payment Controller 
	Route::group(array('prefix' => '/customer_payment'), function()
	{
		$route_slug        = "customer_payment";
		$module_controller = "Maker\CustomerPaymentController@";

		Route::get('/{transaction_id}/{slug_chk_qty_available_or_not?}', 	 ['as'=>$route_slug.'customer_payment', 
									          'uses'=>$module_controller.'customer_payment']);

		Route::post('/admin',['uses' => $module_controller.'pay_to_admin']);   

		Route::post('send_stripe_acc_creation_link', ['as' => $route_slug.'send_stripe_acc_creation_link',   'uses' => $module_controller.'send_stripe_acc_creation_link']);
	});




	/*------------------------------catlogs----------------------------------------------*/
    Route::group(array('prefix' => '/catalogs'), function()
	{
	
        $route_slug        = "catlogs";
		$module_controller = "Maker\CatlogController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'index', 
									  'uses'=>$module_controller.'index']);

		Route::get('/get_catlogs', ['as'=>$route_slug.'get_catlogs', 
									  	'uses'=>$module_controller.'get_catlogs']);

		Route::get('/create', ['as'=>$route_slug.'add', 
									  	'uses'=>$module_controller.'create']);

		Route::post('/store', ['as'=>$route_slug.'store', 
									  	'uses'=>$module_controller.'store']);

		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  	'uses'=>$module_controller.'edit']);

		/*Route::any('/does_brand_exists/{enc_id?}', ['as'=>$route_slug.'does_brand_exists', 
									  	'uses'=>$module_controller.'does_brand_exists']);*/

		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);

		

		Route::get('/change_status', ['as'=>$route_slug.'change_status', 
									  	'uses'=>$module_controller.'change_status']);

			

		Route::post('/multi_action', ['as' => $route_slug.'multi_action', 'uses' => $module_controller.'multi_action']);



	});

	/*----------------------------------------------------------------------------------*/


	Route::group(array('prefix' => '/catalog_pages'), function()
	{
	
        $route_slug        = "catalog_pages";
		$module_controller = "Maker\CatlogImagesController@";
		
		Route::get('view/{enc_id}', ['as'  =>$route_slug.'index', 
									 'uses'=>$module_controller.'index']);

		Route::get('/get_catlogs', ['as'  =>$route_slug.'get_catlogs', 
									'uses'=>$module_controller.'get_catlogs']);

		
		Route::get('view_catalog_page/{enc_id}',['as'  =>$route_slug.'view_catalog_page', 
									             'uses'=>$module_controller.'view_catalog_page']);	
	
		Route::get('/create/{catalog_id?}', ['as'  =>$route_slug.'add', 
							                 'uses'=>$module_controller.'create']);

		Route::post('/store', ['as'  =>$route_slug.'store', 
							   'uses'=>$module_controller.'store']);

		Route::post('/update', ['as'  =>$route_slug.'update', 
							    'uses'=>$module_controller.'update']);

		Route::get('/edit/{enc_id}', ['as'  =>$route_slug.'edit', 
									  'uses'=>$module_controller.'edit']);

		Route::get('/delete_catalog_pages/{enc_id}', ['as'  =>$route_slug.'delete_catalog_pages', 
									  	              'uses'=>$module_controller.'delete_catalog_pages']);

		Route::get('/view/{enc_id}', ['as'  =>$route_slug.'view', 
									  'uses'=>$module_controller.'view']);

		Route::get('/change_status', ['as'  =>$route_slug.'change_status', 
									  'uses'=>$module_controller.'change_status']);

		Route::get('/delete_row', ['as'  =>$route_slug.'delete_row', 
								   'uses'=>$module_controller.'delete_row']);	

		Route::post('/multi_action', ['as'   => $route_slug.'multi_action', 
			                          'uses' => $module_controller.'multi_action']);

		Route::get('/upload_pdf', ['as'   => $route_slug.'uploadPdf', 
			                       'uses' => $module_controller.'uploadPdf']);
		
		Route::post('/save_pdf',  ['as'   => $route_slug.'save_pdf', 
			                       'uses' => $module_controller.'savePdf']);


	});

	/*----------------------------------------------------------------------------------*/
    
		Route::group(array('prefix' => '/catalog_pdf'), function()
		{
		
	        $route_slug        = "catalog_pdf";
			$module_controller = "Maker\CatalogController@";


			Route::get('/temp',               ['as'  => $route_slug.'temp', 
										   'uses'=> $module_controller.'temp']);

			
			Route::get('/',               ['as'  => $route_slug.'index', 
										   'uses'=> $module_controller.'index']);

			Route::get('/get_catlogs',    ['as'  => $route_slug.'get_catlogs', 
									       'uses'=> $module_controller.'get_catlogs']);

			Route::get('/change_status',  ['as'  => $route_slug.'change_status', 
										   'uses'=> $module_controller.'change_status']);

			Route::get('/delete_row',     ['as'  => $route_slug.'delete_row', 
									       'uses'=> $module_controller.'delete_row']);	


			Route::post('/multi_action',  ['as'   => $route_slug.'multi_action', 
				                           'uses' => $module_controller.'multi_action']);

			Route::get('/delete/{enc_id}', ['as'  =>$route_slug.'delete', 
									   'uses'=>$module_controller.'delete']);	


			Route::get('/create',        ['as'   => $route_slug.'create', 
				                          'uses' => $module_controller.'create']);
			
			Route::post('/save_pdf',     ['as'   => $route_slug.'save_pdf', 
				                          'uses' => $module_controller.'savePdf']);

			
			Route::post('/update_pdf',  ['as'   => $route_slug.'update_pdf', 
				                         'uses' => $module_controller.'updatePdf']);


		    Route::get('/edit/{enc_id}', ['as'  =>$route_slug.'edit', 
									      'uses'=>$module_controller.'edit']);


		});


		/*-------------------------catalog images----------------------*/

		Route::group(array('prefix' => '/catalog_images'), function()
		{
		
	        $route_slug        = "catalog_images";
			$module_controller = "Maker\CatalogPdfImageController@";
			
			Route::get('/',                ['as'   => $route_slug.'index', 
										    'uses' => $module_controller.'index']);

			Route::get('/get_images',      ['as'   => $route_slug.'get_images', 
									        'uses' => $module_controller.'get_images']);

			Route::get('/change_status',   ['as'   => $route_slug.'change_status', 
										    'uses' => $module_controller.'change_status']);


			Route::post('/multi_action',   ['as'   => $route_slug.'multi_action', 
				                            'uses' => $module_controller.'multi_action']);

			Route::get('/delete/{enc_id}', ['as'  =>$route_slug.'delete', 
									        'uses'=>$module_controller.'delete']);	


		    Route::post('/store',          ['as'  =>$route_slug.'store', 
							                'uses'=>$module_controller.'store']);


			Route::get('/create',        ['as'   => $route_slug.'create', 
				                          'uses' => $module_controller.'create']);
			
	
		    Route::get('/edit/{enc_id}', ['as'  =>$route_slug.'edit', 
									      'uses'=>$module_controller.'edit']);

		    Route::post('/update',        ['as'  =>$route_slug.'update', 
							               'uses'=>$module_controller.'update']);


		});

	/*-------------------------START Shop-----------------------------------------*/
	Route::group(array('prefix' => '/analytics'), function()
	{
		$route_slug        = "analytics";
		$module_controller = "Maker\AnalyticsController@";
		
		Route::get('/', 	 ['as'=>$route_slug.'analytics', 
									  'uses'=>$module_controller.'index']);
	});

	/*---------------------------Influencer Promo Code--------------------------------------------*/
    Route::group(array('prefix' => '/influencer_promo_code'), function()
	{
		$route_slug        = "influencer_promo_code";
		$module_controller = "Maker\InfluencerPromoCodeController@";
		
		Route::get('/', 	        ['as'=>$route_slug.'index', 
									 'uses'=>$module_controller.'index']);

		Route::get('/create',       ['as'=>$route_slug.'create', 
									 'uses'=>$module_controller.'create']);

		Route::get('/edit/{enc_id}', ['as'=>$route_slug.'edit', 
									  'uses'=>$module_controller.'edit']);
		
		Route::get('/delete/{enc_id}', ['as'=>$route_slug.'delete', 
									  	'uses'=>$module_controller.'delete']);
	
		Route::get('/get_influencer_promo_code_listing', 
										['as'  =>$route_slug.'get_influencer_promo_code_listing', 
									  	'uses' =>$module_controller.'get_influencer_promo_code_listing']);

		Route::get('/change_status',    ['as'  =>$route_slug.'change_status', 
									     'uses'=>$module_controller.'change_status']);

		Route::post('/store',           ['as'  =>$route_slug.'store', 
							             'uses'=>$module_controller.'store']);	

		Route::post('/multi_action',    ['as'   => $route_slug.'multi_action',
			                             'uses' => $module_controller.'multi_action']);

		Route::post('/assign_promo_code',['as'   => $route_slug.'assign_promo_code',
			                             'uses' => $module_controller.'assign_promo_code']);

	});


    /*--------------------------------------------------------------------------------*/

	
	Route::any('/change_password',     ['as'   => 'change_password',   
						                'uses' => 'Maker\AccountSettingsController@change_password']);

	Route::get('/logout',     ['as'   => 'logout',   
						       'uses' => 'Maker\AccountSettingsController@logout']);
	


});