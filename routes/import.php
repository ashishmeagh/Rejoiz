<?php


  	Route::group(array('prefix' => '/import_vendor'), function()
	{
			
			$route_slug        = "import_vendor";
			$module_controller = "Front\ImportVendorController@";

			Route::get('/import_data', ['as' => $route_slug.'import_data', 'uses' =>$module_controller.'import']);

			Route::get('ftp_data_import', ['as' => $route_slug.'ftp_data_import', 'uses' =>$module_controller.'ftp_data_import']);

			Route::get('update_img_path', ['as' => $route_slug.'update_img_path', 'uses' =>$module_controller.'update_img_path']);

			Route::get('check_missing_images', ['as' => $route_slug.'check_missing_images', 'uses' =>$module_controller.'check_missing_images']);

			Route::any('hide_products', ['as' => $route_slug.'hide_products', 'uses' =>$module_controller.'hide_products']);

			Route::any('show_products', ['as' => $route_slug.'show_products', 'uses' =>$module_controller.'show_products']);

			Route::any('update_product_brand', ['as' => $route_slug.'update_product_brand', 'uses' =>$module_controller.'update_product_brand']);

			Route::get('remove_images', ['as' => $route_slug.'remove_images', 'uses' =>$module_controller.'remove_images']);
			
			Route::get('update_retail_price', ['as' => $route_slug.'update_retail_price', 'uses' =>$module_controller.'update_retail_price']);

			Route::get('find_not_available_images_sku', ['as' => $route_slug.'find_not_available_images_sku', 'uses' =>$module_controller.'find_not_available_images_sku']);


			Route::get('update_product_category_subcstegory', ['as' => $route_slug.'update_product_category_subcstegory', 'uses' =>$module_controller.'update_product_category_subcstegory']);


			
			
	});


	Route::group(array('prefix' => '/import_salesmanager'), function()
	{
		$route_slug        = "import_salesmanager";
		$module_controller = "Front\ImportSalesManegerController@";

		Route::get('/', ['as' => $route_slug.'sales', 'uses' =>$module_controller.'index']);	
	});


	Route::group(array('prefix' => '/import_representative'), function()
	{
		$route_slug        = "import_representative";
		$module_controller = "Front\ImportRepController@";

		Route::get('/import_data', ['as' => $route_slug.'faq', 'uses' =>$module_controller.'import_data']);
			

	});

