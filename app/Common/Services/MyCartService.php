<?php
namespace App\Common\Services;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\AddressModel;
use App\Models\ProductsModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RepresentativeMakersModel;  
use App\Models\TransactionMappingModel;
use App\Models\MakerModel;
use App\Models\TempBagModel;
use App\Models\ProductDetailsModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\RetailerModel; 
use App\Models\CustomerModel; 
use App\Models\RepresentativeModel; 
use DB;
use Sentinel;
use Session;
use DateTime;


class MyCartService 
{

   public function __construct(
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                AddressModel $AddressModel,
                                ProductsModel $ProductsModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeMakersModel $RepresentativeMakersModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                MakerModel $MakerModel,
                                ProductDetailsModel $ProductDetailsModel,
                                TempBagModel $TempBagModel,
                                RetailerModel $RetailerModel,
                                CustomerModel $CustomerModel,
                                RepresentativeModel $RepresentativeModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                CustomerQuotesModel $CustomerQuotesModel
                               )
      {
        $this->UserModel                         = $UserModel;
        $this->RoleModel                         = $RoleModel;
        $this->AddressModel                      = $AddressModel;
        $this->ProductsModel                     = $ProductsModel;
        $this->RoleUsersModel                    = $RoleUsersModel;
        $this->RepresentativeLeadsModel          = $RepresentativeLeadsModel;
        $this->RepresentativeProductLeadsModel   = $RepresentativeProductLeadsModel;
        $this->RepresentativeMakersModel         = $RepresentativeMakersModel;
        $this->TransactionMappingModel           = $TransactionMappingModel;
        $this->RetailerQuotesModel               = $RetailerQuotesModel;
        $this->CustomerQuotesModel               = $CustomerQuotesModel;
        $this->MakerModel                        = $MakerModel;  
        $this->RetailerModel                     = $RetailerModel;                 
        $this->CustomerModel                     = $CustomerModel;                 
        $this->ProductDetailsModel               = $ProductDetailsModel;
        $this->RepresentativeModel               = $RepresentativeModel;
        $this->TempBagModel                      = $TempBagModel;
      }

    private function get_auth(){
      return \Sentinel::check();
    } 

    private function get_current_ip(){
      return \Request::ip();
    } 

    private function get_session_id(){
      return session()->getId();
    } 

  // Get Cart Items Count 

    public  function total_items()
    {
      $bag_arr = [];
      $product_count = $user_id = 0;

      $user = $this->get_auth();

      if($user)
      {
        $user_id = $user->id;
      }

      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      $arr_criteria = [
        'user_id' => $user_id
      ];

      if($user_id == 0)
      {
        $arr_criteria['user_session_id'] = $session_id;
      }

      $bag_obj = $this->TempBagModel->where($arr_criteria)->first();

      if(isset($bag_obj->product_data))
      {
        $product_data_arr = json_decode($bag_obj->product_data,true);
        $product_count = isset($product_data_arr['sku']) ? count($product_data_arr['sku']) : 0;
      }

      return $product_count;  
    }


  // Get the Product from the temp_bag table

    public function get_items()
    {
            
      $user_id = 0;

      $user = $this->get_auth();

      if($user)
      {
        $user_id = $user->id;
      }

      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      $arr_criteria = [
        'user_id' => $user_id
      ];

      if($user_id == 0)
      {
        $arr_criteria['user_session_id'] = $session_id;
      }
  
      return $this->TempBagModel->where($arr_criteria)->orderBy('id','desc')->first();

    }


    // Get the Product from the temp_bag table

    public function check_cart_data_while_login()
    {
      $user_id = 0;

      $user = $this->get_auth();

      if($user)
      {
        $user_id = $user->id;
      }

      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      $arr_criteria = [
        'user_id'           => 0,
        'user_session_id'   => $session_id

      ];

      $bag_obj = $this->TempBagModel->where($arr_criteria)->orderBy('id','desc')->first();
      
      return $bag_obj; 
      

    }

    // Transfer the Product from the session to logged in user table

    public function transfer_session_data_while_login($session_bag_arr)
    {
     
      $user_id = 0;

      $user = $this->get_auth();

      if($user)
      {
        $user_id = $user->id;
      }

      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      if($user_id!=0)
      {
     
        $obj_user_cart_data = $this->get_items();


        if($obj_user_cart_data)
        { 
          $user_cart_data_arr = $obj_user_cart_data->toArray();
          $user_cart_json_data = [];            
          $user_cart_json_decoded_data = json_decode($user_cart_data_arr['product_data'],true);      
          
        
          $session_cart_json_data = [];            
          $session_cart_json_decoded_data = json_decode($session_bag_arr['product_data'],true);      
    
          $update_cart_data['sku'] = array_merge($user_cart_json_decoded_data['sku'],$session_cart_json_decoded_data['sku']);
          $update_cart_data['sku'] = array_column($update_cart_data['sku'], null,'sku_no');     

          $update_cart_data['sequence'] = array_merge($user_cart_json_decoded_data['sequence'],$session_cart_json_decoded_data['sequence']);
          $update_cart_data['sequence'] = array_unique($update_cart_data['sequence']);
          
          // dd($user_cart_json_decoded_data,$session_cart_json_decoded_data,$update_cart_data);
          /* Update product details, if product are already available on cart */
           
          $update_arr['product_data'] = json_encode($update_cart_data,true);       

          $is_updated = $this->update($update_arr);

          if($is_updated)
          {
            $delete_session_cart_data = $this->TempBagModel->where('user_session_id',$session_id)->where('id',$session_bag_arr['id'])->delete();
          }
          return true;
        }
        else
        { 
          $update_arr = array(
                        'user_id'=> $user_id
          );
  
          //after login with customer update temp bag data according to retail price

          $session_bag_arr['id']              = $session_bag_arr['id'];
          $session_bag_arr['user_id']         = $user_id;
          $session_bag_arr['ip_address']      = $session_bag_arr['ip_address'];
          $session_bag_arr['user_session_id'] = $session_bag_arr['user_session_id'];
          $session_bag_arr['product_data']    = $session_bag_arr['product_data'];
          $session_bag_arr['is_reorder']      = $session_bag_arr['is_reorder'];
         

         /* $update_cart_data = $this->TempBagModel->where('user_session_id',$session_id)->where('id',$session_bag_arr['id'])->update($update_arr);*/

          $update_cart_data = $this->TempBagModel->where('user_session_id',$session_id)->where('id',$session_bag_arr['id'])->update($session_bag_arr);

          return true;
        }
      }  

    }


    // update the Product in temp_bag table 

    public function update($prduct_arr)
    {
      $user_id = 0;

      $user = $this->get_auth();

      if($user)
      {
        $user_id = $user->id;
      }

      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      $arr_criteria = [
        'user_id' => $user_id
      ];

      if($user_id == 0)
      {
        $arr_criteria['user_session_id'] = $session_id;
      }

      
      return $bag_obj = $this->TempBagModel->where($arr_criteria)->update($prduct_arr);
      
      

    }

    // Delete the cart Items

    public function delete()
    {
      $user_id = 0;

      $user = $this->get_auth();

      if($user)
      {
        $user_id = $user->id;
      }

      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      $arr_criteria = [
        'user_id' => $user_id
      ];

      if($user_id == 0)
      {
        $arr_criteria['user_session_id'] = $session_id;
      }

       
      return $bag_obj = $this->TempBagModel->where($arr_criteria)->delete();

      
    }

   
}