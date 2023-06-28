<?php

use Illuminate\Http\Request;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {


    return $request->user();
});


/*Route::group(['prefix' =>'/rejoiz','middleware' => 'auth:api-rejoiz'], function ()
{
	dd("Here");

	$route_slug        = "dashboard";
	$module_controller = "Customer\DashboardController@";
	
	Route::get('/dashboard', 	function(){
		dd(123);
	});	
	
});	*/


Route::middleware('rejoiz_auth')->get('/user', function (Request $request) {

    return $request->user();

});







Route::get('product_list', 'ProductApiController@get_all_product');
Route::post('add_product', 'ProductApiController@store');
Route::post('edit_product/{id}', 'ProductApiController@update');
Route::post('delete_product/{id}', 'ProductApiController@delete');