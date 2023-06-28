<?php

namespace App\Http\Controllers\Api\Rejoiz\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Common\Services\Api\Rejoiz\Front\VendorService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;

use Validator;



class VendorController extends Controller
{
    public function __construct(VendorService $VendorService,
                                ResponseService $ResponseService) { 


        $this->VendorService    = $VendorService;
        $this->ResponseService  = $ResponseService;
    }

  public function search(Request $request)
  { 

     $form_data          = $request->all();
     $per_page           = isset($form_data['per_page'])?$form_data['per_page']:'';
     $letter             = isset($form_data['letter'])?$form_data['letter']:'';
     $arr_data           = $this->VendorService->search($letter,$per_page);

     return $this->ResponseService->send($arr_data);
  }

  public function details(Request $request)
  { 

     $form_data          = $request->all();
     $vendor_id          = isset($form_data['vendor_id'])?$form_data['vendor_id']:'';
     $arr_data           = $this->VendorService->details($vendor_id);

     return $this->ResponseService->send($arr_data);
  }

  public function categories(Request $request)
  { 

     $form_data          = $request->all();
     $vendor_id          = isset($form_data['vendor_id'])?$form_data['vendor_id']:'';
     $per_page           = isset($form_data['per_page'])?$form_data['per_page']:8;
     $arr_data           = $this->VendorService->categories($vendor_id,$per_page);

     return $this->ResponseService->send($arr_data);
  }

  public function promotions(Request $request)
  { 

     $form_data          = $request->all();
     $vendor_id          = isset($form_data['vendor_id'])?$form_data['vendor_id']:'';
     $per_page           = isset($form_data['per_page'])?$form_data['per_page']:8;
     $arr_data           = $this->VendorService->promotions($vendor_id,$per_page);

     return $this->ResponseService->send($arr_data);
  }

  public function products(Request $request)
  { 

     $form_data          = $request->all();
     $vendor_id          = isset($form_data['vendor_id'])?$form_data['vendor_id']:'';
     $per_page           = isset($form_data['per_page'])?$form_data['per_page']:8;
     $arr_data           = $this->VendorService->products($request);

     return $this->ResponseService->send($arr_data);
  }

  public function add_to_favorite(Request $request)
   {
      $data = [];
      $user_id = 0;
      $form_data = $request->all();


      $user = Sentinel::check();
      if($user)
      {
        $user_id = $user->id;
      }

      $id   = base64_decode($form_data['id']);
      $type = $form_data['type'];

      /*check duplication*/

      if($type == 'maker')
      {
         $count = $this->FavoriteModel->where('retailer_id',$user_id)->where('maker_id',$id)->count();
         if($count > 0)
         {
             $response['status']      = 'ERROR';
             $response['description'] = 'Vendor is already added into the favorite list.';
             return response()->json($response);
         }
      }
      else if($type == 'product')
      {
          $count = $this->FavoriteModel->where('retailer_id',$user_id)->where('product_id',$id)->count(); 
          if($count > 0)
          {
             $response['status']      = 'ERROR';
             $response['description'] = 'Product is already added into the favorite list.';
             return response()->json($response);
          }
      }


      if($type == 'maker')
      {
         $data['retailer_id'] = $user_id;
         $data['maker_id']    = $id;
         $data['product_id']  = 0;
         $data['type']        = 'maker';

         $result = $this->FavoriteModel->create($data);
      }
      else if($type == 'product')
      {
         $data['retailer_id'] = $user_id;
         $data['maker_id']    = 0;
         $data['product_id']  = $id;
         $data['type']        = 'product';
         
         $result = $this->FavoriteModel->create($data);
      }

      if($result)
      {  
         if($type == 'maker')
         {
            $response['status']      = 'SUCCESS';
            $response['description'] = 'Vendor added to favorite list.'; 
         }
         else if($type == 'product')
         {
            $response['status']      = 'SUCCESS';
            $response['description'] = 'Product added to favorite list.';
         }
         
         return response()->json($response);
      }
      else
      {
            if($type == 'maker')
              {
                 $response['status']      = 'ERROR';
                 $response['description'] = 'Error occurred while adding vendor into the favorite list.'; 
              }
              else if($type == 'product')
              {
                $response['status']      = 'ERROR';
                $response['description'] = 'Error occurred while adding product into the favorite list.';
              }
             
             return response()->json($response);
      }
  
   }


}
