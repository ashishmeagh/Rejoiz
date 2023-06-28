<?php

namespace App\Http\Controllers\Api\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Representative\VendorsService;
use App\Common\Services\Api\Common\CommonService;

use App\Common\Services\Api\Common\ResponseService;


use \Sentinel;
use \Validator;

class VendorsController extends Controller
{
    public function __construct( 
    								VendorsService  $VendorsService,
                                    ResponseService $ResponseService,
                                    CommonService   $CommonService
    							)
    { 
    	$this->VendorsService 	= $VendorsService;
        $this->ResponseService  = $ResponseService;
        $this->CommonService    = $CommonService;
    }

    public function index(Request $request) {


    	$search 	= $request->input('search');
    	$perpage 	= $request->input('perpage',10);
    	$user_obj 	= $request->input('auth_user');

    	$response   = $this->VendorsService->get_list($user_obj,$perpage,$search);

    	return $this->ResponseService->send($response);
    }

    public function product_list(Request $request) {

        $user       = $request->input('auth_user');
        $search     = $request->input('search');
        $perpage    = $request->input('perpage' , 10);
        
        $response = $this->VendorsService->product_list($user->id , $search , $perpage);

        return $this->ResponseService->send($response);
    }
}
