<?php

namespace App\Http\Controllers\Api\Rejoiz\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Rejoiz\Retailer\TransactionService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;



class TransactionController extends Controller
{
  

  public function __construct(TransactionService $TransactionService,
                              ResponseService $ResponseService
                             )

    {
      $this->TransactionService = $TransactionService;
      $this->ResponseService = $ResponseService;
    }

    public function get_transactions(Request $request)
    {  
		    $form_data = $request->all();

        $response   = $this->TransactionService->list($form_data);
   
        return $this->ResponseService->send($response);
    }

    public function get_export_transasction_orders(Request $request)
    {
       $form_data = $request->all();

       $response   = $this->TransactionService->get_export_transasction_orders($form_data);
   
       return $this->ResponseService->send($response);
      
    }
}
