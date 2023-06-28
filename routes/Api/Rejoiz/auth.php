<?php 


$module_controller = 'Api\Rejoiz\Common\AuthController@';
Route::any('/test',			['uses' => $module_controller.'test']);
Route::post('/login',			['uses' => $module_controller.'login']);
Route::post('/forgot_password',	['uses' => $module_controller.'forgot_password']);
Route::post('/verify_otp',		['uses' => $module_controller.'verify_otp']);
Route::post('/change_password',	['uses' => $module_controller.'change_password']);
Route::post('/sign_up',	        ['uses' => $module_controller.'sign_up']);
Route::get('/activation_complete',['uses' => $module_controller.'activation_complete']);


$module_controller = 'Api\Rejoiz\Common\CommonController@';
Route::get('/get_all_countries', ['uses' => $module_controller.'get_all_countries']);
Route::get('/get_phone_code',	 ['uses' => $module_controller.'get_phone_code']);


$module_controller = 'Api\Rejoiz\Front\HomeController@';
Route::get('/get_menus', ['uses' => $module_controller.'get_menus']);

