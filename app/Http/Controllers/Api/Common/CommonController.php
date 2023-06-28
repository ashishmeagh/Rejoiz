<?php

namespace App\Http\Controllers\Api\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Common\CommonService;
use App\Common\Services\Api\Common\ResponseService;


class CommonController extends Controller
{
    public function __construct(CommonService $CommonService,ResponseService $ResponseService) { 


        $this->CommonService    = $CommonService;
        $this->ResponseService  = $ResponseService;
    }

    public function get_all_countries() {
          
        $arr_countries        = $this->CommonService->get_all_countries();

        return $this->ResponseService->send($arr_countries);  
    }

    public function get_phone_code(Request $request) {

        $form_data           = $request->all();
        $country_id          = $form_data['country_id']; 
      
        $phone_code          = $this->CommonService->get_phone_code($country_id);

        return $this->ResponseService->send($phone_code);
    }
}
