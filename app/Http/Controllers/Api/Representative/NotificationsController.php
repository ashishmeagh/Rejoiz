<?php

namespace App\Http\Controllers\Api\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Representative\NotificationsService;
use App\Common\Services\Api\Common\ResponseService;

use \Validator;

class NotificationsController extends Controller
{
    public function __construct( ResponseService  $ResponseService, NotificationsService  $NotificationsService ) {
    	
		$this->ResponseService 		= $ResponseService;
		$this->NotificationsService = $NotificationsService;
    }

    public function index(Request $request) {

    	$user_obj	= $request->input('auth_user');
    	$perpage 	= $request->input('perpage');

    	$response = $this->NotificationsService->get_list($user_obj->id,$perpage);

    	return $this->ResponseService->send($response);
    }

    public function delete(Request $request) {

    	$arr_rules  = [ 'notifications_id'	=> 'required'];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Something went wrong.';
            $response['data']           = [];

            return $this->ResponseService->send($response,500);
        }

    	$user_obj 			= $request->input('auth_user');
    	$notifications_id 	= $request->input('notifications_id');

    	$response = $this->NotificationsService->delete($user_obj->id,$notifications_id);

    	return $this->ResponseService->send($response);
    }

    public function change_view_status(Request $request) {
 
        $arr_rules  = [ 'notification_id'  => 'required'];

        $validator  = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {

            $response['status']         = 'failure';
            $response['message']        = 'Something went wrong.';
            $response['data']           = [];

            return $this->ResponseService->send($response,500);
        }

        $user_obj           = $request->input('auth_user');
        $notification_id   = $request->input('notification_id');

        $response = $this->NotificationsService->change_view_status($user_obj->id,$notification_id);

        return $this->ResponseService->send($response);
    }

    public function count(Request $request) {

        $user_obj = $request->input('auth_user');

        $response = $this->NotificationsService->count($user_obj->id);

        return $this->ResponseService->send($response);
    }
}
