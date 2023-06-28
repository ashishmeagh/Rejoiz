<?php

namespace App\Http\Controllers\Api\Rejoiz\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Common\Services\Api\Rejoiz\Front\ProductSearchService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;

use Validator;



class ProductController extends Controller
{
    public function __construct(ProductSearchService $ProductSearchService,
                                ResponseService $ResponseService) { 


        $this->ProductSearchService    = $ProductSearchService;
        $this->ResponseService         = $ResponseService;
    }

  public function details(Request $request)
  { 
     $form_data          = $request->all();
     $arr_data           = $this->ProductSearchService->product_details($request);

     return $this->ResponseService->send($arr_data);
  }

  public function get_a_quote(Request $request)
  { 
     $form_data          = $request->all();
     $arr_data           = $this->ProductSearchService->get_a_quote($form_data);

     return $this->ResponseService->send($arr_data);
  }

}
