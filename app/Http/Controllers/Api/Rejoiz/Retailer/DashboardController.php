<?php

namespace App\Http\Controllers\Api\Rejoiz\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\Api\Rejoiz\Retailer\OrdersService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;

use Validator;

class DashboardController extends Controller
{
    
	  public function __construct(
                                OrdersService   $OrdersService,
                                ResponseService $ResponseService
                               )
    {
      $this->OrdersService      = $OrdersService;
      $this->ResponseService    = $ResponseService; 
    }

    public function index(Request $request)
    {

      $user      = $request->input('auth_user');
      $user_id   = $user->id;

      $arr_data  = $this->OrdersService->get_order_counts($user_id);

      return $this->ResponseService->send($arr_data);
    }
  }  