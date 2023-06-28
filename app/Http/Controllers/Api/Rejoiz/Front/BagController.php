<?php

namespace App\Http\Controllers\Api\Rejoiz\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmailSubscriptiponModel;


use App\Common\Services\Api\Rejoiz\Front\BagService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;

use Validator;



class BagController extends Controller
{
    public function __construct(BagService $BagService,
                                ResponseService $ResponseService,
                                EmailSubscriptiponModel $EmailSubscriptiponModel) { 

        $this->BagService              = $BagService;
        $this->ResponseService         = $ResponseService;
        $this->EmailSubscriptiponModel = $EmailSubscriptiponModel;
    }

    public function index(Request $request)
    {
       $arr_data     = $this->BagService->my_bag($request);

       return $this->ResponseService->send($arr_data);  
    }

    public function add(Request $request) {

      if ($request != null) {
      $product_arr = $new_arr = [];
      $arr_rules = [
        'product_id' => 'required',
        'item_qty'   => 'required',
      ];

      $validator = Validator::make($request->all(), $arr_rules);

      if ($validator->fails()) {
        $response['status']      = 'failure';
        $response['message']     = 'Form validations failed, please check form fields.';
        $response['data']        = ''; 
        return $this->ResponseService->send($response);  
      }
        
       $arr_data        = $this->BagService->add($request);

      return $this->ResponseService->send($arr_data);  
    }
  }  

   public function delete(Request $request) {
      
      $arr_data        = $this->BagService->delete($request);

      return $this->ResponseService->send($arr_data);  
  }

  public function empty(Request $request) {
      
      $arr_data        = $this->BagService->empty($request);

      return $this->ResponseService->send($arr_data);  
  }


  public function product_summary(Request $request){
  
      $arr_data        = $this->BagService->product_summary($request);

      return $this->ResponseService->send($arr_data);  
    
  }

  public function update_qty(Request $request){
  
      $arr_data        = $this->BagService->update_qty($request);

      return $this->ResponseService->send($arr_data);  
  }

  public function get_cart_count(Request $request){

      $form_data       = $request->all();
      $user            = isset($form_data['auth_user'])?$form_data['auth_user']:'';
      if($user)
      {
         $user_id      = $user->id;
      }

      else
      {
         $user_id      = null;
      }


      $arr_data        = $this->BagService->get_cart_count($user_id);

      return $this->ResponseService->send($arr_data);
  }

}
