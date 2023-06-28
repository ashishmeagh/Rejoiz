<?php

namespace App\Http\Controllers\Api\Rejoiz\Retailer;

use App\Models\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\Api\Rejoiz\Retailer\AccountSettingsService;
use App\Common\Services\Api\Common\ResponseService;
use App\Common\Services\Api\Common\CommonService;


use Validator;

class TestController extends Controller
{


	public function __construct(
                                AccountSettingsService $AccountSettingsService,
                                UserModel $UserModel,
                                ResponseService $ResponseService,
                                CommonService $CommonService
                              )
    {
    	
      $this->AccountSettingsService = $AccountSettingsService;
      $this->ResponseService        = $ResponseService;
      $this->UserModel              = $UserModel;
      $this->CommonService          = $CommonService;


    }

    public function testing()
    {
       dd('index');
    }


 
}
