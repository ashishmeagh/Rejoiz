<?php

namespace App\Http\Controllers\Api\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Representative\RetailersService;
use App\Common\Services\Api\Common\CommonService;

use App\Common\Services\Api\Common\ResponseService;

use \Sentinel;
use \Validator;

class RetailersController extends Controller
{
    public function __construct( 
    								RetailersService  $RetailersService,
                                    ResponseService   $ResponseService,
                                    CommonService     $CommonService
    							)
    { 
    	$this->RetailersService = $RetailersService;
        $this->ResponseService  = $ResponseService;
        $this->CommonService    = $CommonService;
    }

    public function index(Request $request) {

        $search     = $request->input('search');
        $user_obj   = $request->input('auth_user');
        $perpage    = $request->input('perpage' , 10);

    	$response   = $this->RetailersService->get_list($user_obj->id, $perpage,$search);

        return $this->ResponseService->send($response);
    }

    public function create(Request $request) {

    	$arr_rules = [
                        'first_name'	=>'required',
                        'last_name'		=>'required',
                        'country_code'	=>'required',
                        'email'			=>'required|email',
                        'contact_no'	=>'required',
             		];
           
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails()) {

            $response['status']     = 'failure';
            $response['message']    = 'Please enter valid data.';
            $response['data']       = '';

            return $this->ResponseService->send($response , 500);
        }

        $data = $request->all();

    	$responce = $this->RetailersService->create($data);

        return $this->ResponseService->send($responce);
    }

    public function change_status(Request $request) {

        $arr_rules = [
                        'retailer_id'   =>'required',
                        'status'        =>'required'
                    ];
           
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails()) {

            $response['status']     = 'failure';
            $response['message']    = 'Please enter valid data.';
            $response['data']       = '';

            return $this->ResponseService->send($response, 500);
        }

        $data     = $request->all();

        $response = $this->RetailersService->change_status($data);

        return $this->ResponseService->send($response);
    }

    public function name_list(Request $request) {

        $user_obj   = $request->input('auth_user');

        $user_id    = $user_obj->id;
        $response   = $this->RetailersService->name_list($user_id);

        return $this->ResponseService->send($response);
    }

    public function details(Request $request) {

        $customer_id   = $request->input('retailer_id');
        $response      = $this->RetailersService->details($customer_id);

        return $this->ResponseService->send($response);
    }
}
