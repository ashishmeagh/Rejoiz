<?php 


Route::group(['prefix' => 'product'], function ()
{
	$module_controller = 'Api\Rejoiz\Front\ProductController@';
	Route::get('/details', ['uses' => $module_controller.'details']);
	Route::post('/get_a_quote', ['uses' => $module_controller.'get_a_quote']);
});


Route::group(['prefix' => 'vendor'], function ()
{
	$module_controller = 'Api\Rejoiz\Front\VendorController@';
	Route::get('/search', ['uses' => $module_controller.'search']);
	Route::get('/details', ['uses' => $module_controller.'details']);
	Route::get('/categories', ['uses' => $module_controller.'categories']);
	Route::get('/promotions', ['uses' => $module_controller.'promotions']);
	Route::get('/products', ['uses' => $module_controller.'products']);
});


Route::group(['middleware'=>'rejoiz_auth'],function()
{
	Route::group(['prefix' => 'bag'], function ()
	{
		$module_controller = 'Api\Rejoiz\Front\BagController@';
		Route::post('/add', ['uses' => $module_controller.'add']);
		Route::post('/update_qty', ['uses' => $module_controller.'update_qty']);
		Route::post('/delete', ['uses' => $module_controller.'delete']);
		Route::post('/empty', ['uses' => $module_controller.'empty']);
		Route::get('/', ['uses' => $module_controller.'index']);
		Route::get('/product_summary', ['uses' => $module_controller.'product_summary']);
		Route::get('/get_cart_count', ['uses'  => $module_controller.'get_cart_count']);
	});
});



$module_controller = 'Api\Rejoiz\Front\HomeController@';
Route::get('/get_menus', ['uses' => $module_controller.'get_menus']);
Route::get('/get_slider_images', ['uses' => $module_controller.'get_slider_images']);
Route::get('/get_categories', ['uses' => $module_controller.'get_categories']);
Route::get('/about_us', ['uses' => $module_controller.'about_us']);
Route::get('/subscribe', ['uses' => $module_controller.'subscribe']);
Route::get('/faqs', ['uses' => $module_controller.'faqs']);
Route::get('/get_social_links', ['uses' => $module_controller.'get_social_links']);
Route::get('/special_offers', ['uses' => $module_controller.'special_offers']);
Route::get('/rep_center', ['uses' => $module_controller.'rep_center']);
Route::get('/get_promotions', ['uses' => $module_controller.'get_promotions']);
Route::get('/find_rep', ['uses' => $module_controller.'find_rep']);
Route::get('/get_area_wise_vendors', ['uses' => $module_controller.'get_area_wise_vendors']);
Route::get('/sales_manager_details', ['uses' => $module_controller.'sales_manager_details']);
Route::get('/rep_details', ['uses' => $module_controller.'rep_details']);


$module_controller = 'Api\Rejoiz\Front\SearchController@';
Route::get('/search', ['uses' => $module_controller.'index']);













