<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GeneralSettingModel;
use App\Models\TransactionsDetailsModel;
use App\Models\TempBagModel;
use App\Models\UserModel;
use App\Models\CardModel;
use App\Models\CountryModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RetailerModel;
use App\Models\CustomerModel;
use App\Models\RetailerQuotesModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\orderDataService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Services\ProductService;
use App\Common\Services\HelperService;
use App\Models\AddressModel; 

use Validator;
use URL;
use Session;
use Redirect;
use Input;
use Flash;
use Sentinel;
use DB;
use App\User;
use Stripe\Error\Card;
use Stripe;

class PaymentController extends Controller
{
    public function __construct(GeneralSettingModel $GeneralSettingModel,
                                 TransactionsDetailsModel $TransactionsDetailsModel,
                                 TempBagModel $TempBagModel,
                                 StripePaymentService $StripePaymentService,
                                 CardModel $CardModel,
                                 UserModel $UserModel,
                                 RetailerModel $RetailerModel,
                                 CustomerModel $CustomerModel,
                                 GeneralService $GeneralService,
                                 CountryModel $CountryModel,
                                 EmailService $mail_service,
                                 RetailerQuotesModel $RetailerQuotesModel,
                                 RepresentativeLeadsModel $RepresentativeLeadsModel,
                                 ProductService $ProductService,
                                 orderDataService $orderDataService,
                                 HelperService $HelperService,
                                 AddressModel $AddressModel
                                )
    {       
        $this->arr_view_data            = [];
        $this->module_title             = "Payment Transactions";  
        $this->page_title               = "Payment Transactions";  
        $this->retailer_panel_slug      = config('app.project.retailer_panel_slug');
        $this->module_url_path          = url('/');  
        $this->GeneralSettingModel      = $GeneralSettingModel;  
        $this->TransactionsDetailsModel = $TransactionsDetailsModel;
        $this->TempBagModel             = $TempBagModel;
        $this->CardModel                = $CardModel;
        $this->UserModel                = $UserModel;
        $this->RetailerModel            = $RetailerModel;
        $this->CustomerModel            = $CustomerModel;
        $this->GeneralService           = $GeneralService;
        $this->EmailService             = $mail_service;
        $this->CountryModel             = $CountryModel;
        $this->RetailerQuotesModel      = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
        $this->StripePaymentService     = $StripePaymentService;
        $this->ProductService           = $ProductService;
        $this->orderDataService         = $orderDataService;
        $this->HelperService            = $HelperService;
        $this->AddressModel             = $AddressModel;
        $this->admin_user_id               = get_admin_id();

    }

    public function checkout(Request $request,$role=false,$order_no=false,$maker_id=false)
    { 


        $loggedInUserId = 0;
        $user = Sentinel::check();
        
        if($user)
        {
            $loggedInUserId = $user->id;
        }

        if ($loggedInUserId == 0) {
           return redirect('/');
        }

        $form_data    = $request->all();
     
        $arr_final_data = [];
        $user_arr = [];
        
        /*---get logged in user id--*/
        
        // $user = Sentinel::check();
        $user_id = $promotional_discount = 0;

        if(isset($user))
        {
          $user_id = $user->id;  
        }

        /*-----------------------*/

        $role = isset($role)?$role : false;
        $order_no = isset($order_no)?base64_decode($order_no):false;
        $maker_id = isset($maker_id)?base64_decode($maker_id):false;
        
        if($role && $order_no)
        { 
          
          $arr_temp_data = $this->orderDataService->get_order_details($role,$order_no,$maker_id);
          /* Rearrange array data */
          foreach($arr_temp_data as $key => $arr_data)
          {

            if(isset($arr_data['leads_details']) && count($arr_data['leads_details'])>0)
            {

              
              foreach($arr_data['leads_details'] as $product_key => $product_data)
              {
               $arr_final_data[$key]['product_details'][] = isset($product_data)?$product_data:'';

               $arr_final_data[$key]['product_details'][$product_key]['product_name'] = isset($product_data['product_details']['product_name'])?$product_data['product_details']['product_name']:'';

               $arr_final_data[$key]['product_details'][$product_key]['sku_no'] = isset($product_data['sku'])?$product_data['sku']:0;

                $arr_final_data[$key]['product_details'][$product_key]['wholesale_price'] = isset($product_data['product_details']['unit_wholsale_price'])?$product_data['product_details']['unit_wholsale_price']:0;

                $arr_final_data[$key]['product_details'][$product_key]['item_qty'] = isset($product_data['qty'])?$product_data['qty']:0;

                $arr_final_data[$key]['product_details'][$product_key]['product_dis amount'] = isset($product_data['product_discount'])?$product_data['product_discount']:0;
               
                $ship_charg_dis = isset($product_data['shipping_charges_discount'])?$product_data['shipping_charges_discount']:0;
                
                // $ship_charg = isset($product_data['shipping_charges'])?num_format($product_data['shipping_charges']) + $ship_charg_dis :0;
                $ship_charg = isset($product_data['product_shipping_charge'])?num_format($product_data['product_shipping_charge']) :0;
                
                $arr_final_data[$key]['product_details'][$product_key]['shipping_charges'] = isset($ship_charg)?$ship_charg:0;
             
                $arr_final_data[$key]['product_details'][$product_key]['shipping_discount'] = isset($ship_charg_dis)?$ship_charg_dis:0;

              }

               $promoCode = isset($arr_data['promo_code'])?$arr_data['promo_code']:false;

                $promoCodeData = $this->ProductService->get_promotion_and_prodo_code_details($promoCode);

                $isFreeShipping = false;

                if(isset($promoCodeData) && count($promoCodeData)>0)
                {
                    foreach ($promoCodeData as $promoCode) 
                    {
                        if(isset($promoCode['get_promotions_offer_details']) && count($promoCode['get_promotions_offer_details']) > 0)
                        {
                            foreach($promoCode['get_promotions_offer_details'] as $promo_code)
                            {
                                if(isset($promo_code['get_prmotion_type']['promotion_type_name']) && count($promo_code['get_prmotion_type']['promotion_type_name'] > 0))
                                {
                                    if($promo_code['get_prmotion_type']['promotion_type_name'] == 'Free Shipping')
                                    {
                                        $isFreeShipping = true;
                                    }
                                }
                                
                            }
                        }
                    }
                }

                $this->arr_view_data['isFreeShipping']  = $isFreeShipping;
                

              $promotional_discount = $arr_data['promotion_discount'];
            }
            elseif(isset($arr_data['quotes_details']) && count($arr_data['quotes_details'])>0)
            {
              

                foreach($arr_data['quotes_details'] as $product_key => $product_data)
                {
                  
                   $arr_final_data[$key]['product_details'][]     = isset($product_data)?$product_data:'';

                   $arr_final_data[$key]['product_details'][$product_key]['product_name'] = isset($product_data['product_details']['product_name'])?$product_data['product_details']['product_name']:'';
                   
                   $arr_final_data[$key]['product_details'][$product_key]['sku_no'] = isset($product_data['sku_no'])?$product_data['sku_no']:0;

                    $arr_final_data[$key]['product_details'][$product_key]['wholesale_price'] = isset($product_data['product_details']['unit_wholsale_price'])?$product_data['product_details']['unit_wholsale_price']:0;

                    $arr_final_data[$key]['product_details'][$product_key]['item_qty'] = isset($product_data['qty'])?$product_data['qty']:0;

                    $arr_final_data[$key]['product_details'][$product_key]['product_dis amount'] = isset($product_data['product_discount'])?$product_data['product_discount']:0;
                   
                    $ship_charg_dis = isset($product_data['shipping_discount'])?$product_data['shipping_discount']:0;

                    $ship_charg = isset($product_data['shipping_charge'])?$product_data['shipping_charge'] - $ship_charg_dis:0;

                   /* $ship_charg = isset($product_data['product_details']['shipping_charges'])?$product_data['product_details']['shipping_charges']:0;

                    $ship_charg_dis = isset($product_data['product_details']['shipping_discount'])?$product_data['product_details']['shipping_discount']:0;*/


                    $arr_final_data[$key]['product_details'][$product_key]['shipping_discount'] = isset($ship_charg_dis)?$ship_charg_dis:0;

                    $arr_final_data[$key]['product_details'][$product_key]['shipping_charges']  = isset($ship_charg)?$ship_charg:0;
                }

                $promotional_discount = $arr_data['promotion_discount'];

                $promoCode = isset($arr_data['promo_code'])?$arr_data['promo_code']:false;

                $promoCodeData = $this->ProductService->get_promotion_and_prodo_code_details($promoCode);

                $isFreeShipping = false;

                if(isset($promoCodeData) && count($promoCodeData)>0)
                {
                    foreach ($promoCodeData as $promoCode) 
                    {
                        if(isset($promoCode['get_promotions_offer_details']) && count($promoCode['get_promotions_offer_details']) > 0)
                        {
                            foreach($promoCode['get_promotions_offer_details'] as $promo_code)
                            {
                                if(isset($promo_code['get_prmotion_type']['promotion_type_name']) && count($promo_code['get_prmotion_type']['promotion_type_name'] > 0))
                                {
                                    if($promo_code['get_prmotion_type']['promotion_type_name'] == 'Free Shipping')
                                    {
                                        $isFreeShipping = true;
                                    }
                                }
                                
                            }
                        }
                    }
                }

               /* $promo_code_type_name = isset($promoCodeData['get_promotions_offer_details']['get_prmotion_type']['promotion_type_name'])?$promoCodeData['get_promotions_offer_details']['get_prmotion_type']['promotion_type_name']:'';

                if($promo_code_type_name == 'Free Shipping')
                {
                    $isFreeShipping = true;
                }*/

                $this->arr_view_data['isFreeShipping']  = $isFreeShipping;

            }
          }

        }

        else
        {
           $arr_final_data = $this->orderDataService->get_bag_details($user_id);
        }
              // dd($arr_final_data);

        $this->arr_view_data['arr_final_data'] = isset($arr_final_data)?$arr_final_data:[]; 
      
     // dD($arr_final_data);
        /* calculation  */
        $ship_discount = $prod_discount = $sub_total = $shipping_charg = $total_amt = $temp_ship_discount = 0;

         if(isset($arr_final_data) && count($arr_final_data)>0){ 
                  
             foreach($arr_final_data as $product_details){
                                     
                 if(isset($product_details['product_details']) && count($product_details['product_details'])>0){
                  
                   foreach($product_details['product_details'] as $arr_product_data){
                            
                            $product_shipping_charg = isset($arr_product_data['shipping_charges'])?num_format($arr_product_data['shipping_charges']):0.00;
                            
                            $temp_ship_discount += isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):0.00;

                            $ship_discount = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):0.00;

                            if($role == 'representative' || $role == 'sale_manager')
                            { 
                              $shipping_charg += $product_shipping_charg;
                            }
                            else
                            {
                              $shipping_charg += $product_shipping_charg + $ship_discount;
                            }
                            
                            

                            $prod_discount += isset($arr_product_data['product_dis amount'])?num_format($arr_product_data['product_dis amount']):0.00;
                            
                            $item_qty = isset($arr_product_data['item_qty'])?$arr_product_data['item_qty']:0;
                            // dd($arr_product_data);
                            $login_user  = Sentinel::check(); 

                            if($login_user->inRole('customer'))
                            {
                              $unit_price = isset($arr_product_data['unit_retail_price'])?$arr_product_data['unit_retail_price']:0;
                            }else{
                              $unit_price = isset($arr_product_data['wholesale_price'])?$arr_product_data['wholesale_price']:0;

                            }


                            $sub_total += $item_qty*$unit_price;

                            $total_amt = ($sub_total)+($shipping_charg)-($prod_discount)-($temp_ship_discount);

                    }
                 }
              }
          }

          $total_amount      = $sub_total;
          $shipping_charges  = $shipping_charg;
          $shipping_discount = $temp_ship_discount;
          $product_discount  = $prod_discount;
          $amount            = $total_amt;
          // dd($shipping_charges,$shipping_discount);
        /*************************************************/


          if($user)
          {
            $user_id = $user->id;

            $user_obj      = $this->UserModel->where('id',$user_id)->first();
            $status_net_30 = $user_obj->status_net_30;
          }

          $stripApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

          $stripeKeyId = isset($stripApiKeyData['id'])?$stripApiKeyData['id']:'';

          $user_id = \Sentinel::getUser()->id;

          $arr_card_details = [];
          
          $card_data = $this->CardModel->where('user_id',$user_id)
                                            ->where('stripe_key_id',$stripeKeyId)
                                            ->groupBy('fingerprint')
                                            ->get()
                                            ->toArray();
          
        /*  $card_data = $this->CardModel->where('id','!=',$obj_stripe_key_data->id)
                                        ->where('user_id',$user_id)
                                        ->get()
                                        ->toArray();*/

          $arr_cards = [];

          if($card_data && count($card_data))
          {
            $arr_cards = $this->StripePaymentService->get_card_data($card_data);
           
            if(isset($arr_cards['status']) && $arr_cards['status'] == 'Error')
            {
               $arr_cards = [];
            }
          }
          
          
          if($user->inRole('customer'))
          {

            /*get retailer details from */
            $retailer_details = $this->UserModel->with(['customer_details'])->where('id',$user_id)->first();

            $retailer_addr_data = $this->CustomerModel->where('user_id',$user_id)->first();   

          }
          else
          {
          /*get retailer details from */
            $retailer_details = $this->UserModel->with(['retailer_details'])->where('id',$user_id)->first();

            $retailer_addr_data = $this->RetailerModel->where('user_id',$user_id)->first();        

          }
          if(isset($retailer_addr_data))
          {
              $retailer_addr_data =  $retailer_addr_data->toArray();

          }
                
          
          $retailer_addr_data_arr =[];

          $shipping_addr    = isset($retailer_addr_data['shipping_addr']) && $retailer_addr_data['shipping_addr']!="" ?$retailer_addr_data['shipping_addr']:'';


          $shipping_suit_apt =isset($retailer_addr_data['shipping_suit_apt']) && $retailer_addr_data['shipping_suit_apt']!=''?$retailer_addr_data['shipping_suit_apt']:'';

          $shipping_city    = isset($retailer_addr_data['shipping_city']) && $retailer_addr_data['shipping_city']!=''?$retailer_addr_data['shipping_city']:'';
          $shipping_state   = isset($retailer_addr_data['shipping_state']) && $retailer_addr_data['shipping_state']!=''?$retailer_addr_data['shipping_state']:'';

          $ship_contact_no = isset($retailer_addr_data['ship_contact_no']) && $retailer_addr_data['ship_contact_no']!=''?$retailer_addr_data['ship_contact_no']:'';

      

          if(isset($retailer_addr_data['shipping_country'])&& $retailer_addr_data['shipping_country']!='')
          {
            $shipping_country = get_country($retailer_addr_data['shipping_country']);
            $shipping_country = isset($shipping_country)&& $shipping_country!=''? $shipping_country:'';
          }
          else
          {
              $shipping_country ='';
          }
          
          $billing_addr    = isset($retailer_addr_data['billing_address']) && $retailer_addr_data['billing_address']!='' ?$retailer_addr_data['billing_address']:'';

          $billing_suit_apt =isset($retailer_addr_data['billing_suit_apt']) && $retailer_addr_data['billing_suit_apt']!=''?$retailer_addr_data['billing_suit_apt']:'';


          $billing_city    = isset($retailer_addr_data['billing_city']) && $retailer_addr_data['billing_city']!=''?$retailer_addr_data['billing_city']:'';
          $billing_state   = isset($retailer_addr_data['billing_state']) && $retailer_addr_data['billing_state']!=''?$retailer_addr_data['billing_state']:'';

          $bill_contact_no = isset($retailer_addr_data['bill_contact_no']) && $retailer_addr_data['bill_contact_no']!=''?$retailer_addr_data['bill_contact_no']:'';
          
          if(isset($retailer_addr_data['billing_country'])&&$retailer_addr_data['billing_country']!='')
          {
             $billing_country = get_country($retailer_addr_data['billing_country']);
                 
              $billing_country = isset($billing_country) && $billing_country!=''?$billing_country:'';
          }
          else
          {
              $billing_country = '';
          }

          $retailer_shippping_post_code = isset($retailer_addr_data['shipping_zip_postal_code'])?$retailer_addr_data['shipping_zip_postal_code']:'';
         
          $retailer_billing_post_code = isset($retailer_addr_data['billing_zip_postal_code'])?$retailer_addr_data['billing_zip_postal_code']:'';

          $country_data               = $this->CountryModel->where('is_active','1')->orderBy('name','ASC')->get()->toArray();

          $retailer_shipping_addr['shipping_addr']        =  $shipping_addr;
          $retailer_shipping_addr['shipping_suit_apt']    =  $shipping_suit_apt;
          $retailer_shipping_addr['shipping_city']        =  $shipping_city;
          $retailer_shipping_addr['shipping_state']       =  $shipping_state;
          $retailer_shipping_addr['shipping_country']     =  $retailer_addr_data['shipping_country'];
          $retailer_shipping_addr['shipping_zip_postal_code'] = $retailer_shippping_post_code;
          $retailer_shipping_addr['ship_contact_no']      = $ship_contact_no;



          $retailer_billing_addr['billing_addr']    =  $billing_addr;
          $retailer_billing_addr['billing_suit_apt']=  $billing_suit_apt;
          $retailer_billing_addr['billing_city']    =  $billing_city;
          $retailer_billing_addr['billing_state']   =  $billing_state;
          $retailer_billing_addr['billing_country'] =  $retailer_addr_data['billing_country'];
          $retailer_billing_addr['billing_zip_postal_code'] = $retailer_billing_post_code;
          $retailer_billing_addr['bill_contact_no'] = $bill_contact_no;
   

          $this->arr_view_data['page_title']         = 'Payment Transaction';
          $this->arr_view_data['module_url_path']    = $this->module_url_path;
          $this->arr_view_data['status_net_30']      = $status_net_30;
          $this->arr_view_data['retailer_data']      = $user_arr;
          $this->arr_view_data['retailer_addr_data'] = $retailer_addr_data_arr;
          $this->arr_view_data['arr_card']           = $arr_cards;
          $this->arr_view_data['retailer_shipping_addr'] = $retailer_shipping_addr;
          $this->arr_view_data['retailer_billing_addr']  = $retailer_billing_addr;
          $this->arr_view_data['country_data']       = $country_data;

        /*this id is put into session when order placed by net 30*/
        $order_id = Session::get('order_id');

        /*this id is set into session when order placed by representative.and retailer going to do payment that time this id is will used*/
        $representative_order_id = Session::get('representative_order_id');

        

        if(isset($order_id) && $order_id!='')
        { 
            $order_data_arr = [];
            
            $obj_order_data = $this->RetailerQuotesModel->where('id',$order_id)->first();

            if(isset($obj_order_data))
            {
               $order_data_arr = $obj_order_data->toArray(); 

              $this->arr_view_data['amount']            = isset($amount)?$amount:''; 
              $this->arr_view_data['product_discount']  = isset($product_discount)?$product_discount:'';
              $this->arr_view_data['total_amount']      = isset($total_amount)?$total_amount:''; 
              $this->arr_view_data['shipping_charges']  = isset($shipping_charges)?$shipping_charges:'';
              $this->arr_view_data['shipping_discount'] = isset($shipping_discount)?$shipping_discount:'';

               /*get payment terms*/

              $payment_term = '';

              $payment_term = $this->HelperService->get_payment_term($order_data_arr['order_no'],$order_id);

              $this->arr_view_data['payment_term'] = isset($payment_term)?$payment_term:'';
              //$this->arr_view_data['promo_code_slug'] = isset($order_data_arr['promo_code'])?$order_data_arr['promo_code']:'';

             
              /*--------------------*/

              
            }
        }
        elseif(isset($representative_order_id) && $representative_order_id!='')
        { 
           
            $order_arr = [];
            $payment_term = '';

            //$total_shipping_charges = Session::get('total_shipping_charges');
            $Wholsale_sub_total = Session::get('Wholsale_sub_total');
            
            $obj_order_data = $this->RepresentativeLeadsModel->where('id',$representative_order_id)->first();
           
            if(isset($obj_order_data))
            {
               $order_arr = $obj_order_data->toArray();

              $this->arr_view_data['amount']            = isset($order_arr['total_wholesale_price'])?$order_arr['total_wholesale_price']:0.00; 

              $this->arr_view_data['product_discount']  = isset($order_arr['total_product_discount'])?$order_arr['total_product_discount']:0;

              $this->arr_view_data['promo_discount']  = isset($order_arr['promotion_discount'])?$order_arr['promotion_discount']:0;

              $this->arr_view_data['total_amount']      = isset($Wholsale_sub_total)?$Wholsale_sub_total:0.00; 

              $this->arr_view_data['shipping_charges']  = isset($order_arr['total_product_shipping_charges'])?$order_arr['total_product_shipping_charges']:0; 

      
             
              $this->arr_view_data['shipping_discount'] = isset($order_arr['total_shipping_discount'])?$order_arr['total_shipping_discount']:0; 

              /*get payment terms*/

              $payment_term = '';

              $payment_term = $this->HelperService->get_payment_term($order_arr['order_no'],$representative_order_id);

              $this->arr_view_data['payment_term'] = isset($payment_term)?$payment_term:'';
              $this->arr_view_data['promo_code_slug'] = isset($order_arr['promo_code'])?$order_arr['promo_code']:'';
              
              /*--------------------*/
          
            }

            //get address details of rep/sales order
            $address_arr = [];
            if(isset($order_arr) && count($order_arr)>0)
            {
               $address_details = $this->AddressModel->where('order_no',$order_arr['order_no'])->first();

               if(isset($address_details))
               {
                 $address_arr = $address_details->toArray();
               }
            }
           
        }

        $userSegment = request()->segment(2);

        if($userSegment == 'representative' || $userSegment == 'sales_manager')
        {
          $ordNo    = request()->segment(3);
          $vendorId = request()->segment(4);

          $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment);
        }
        elseif($userSegment == 'retailer')
        {
          $ordNo    = request()->segment(3);
          $vendorId = request()->segment(4);
          
          $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment);
        }
        else
        {
          $orderCalculationData = [];
        }
      
        $this->arr_view_data['role']                 = isset($role)?$role:false;
        $this->arr_view_data['address_arr']          = isset($address_arr)?$address_arr:[];
        $this->arr_view_data['amount']               = isset($amount)?$amount:0; 
        $this->arr_view_data['product_discount']     = isset($product_discount)?$product_discount:0;
        $this->arr_view_data['total_amount']         = isset($total_amount)?$total_amount:0; 
        $this->arr_view_data['shipping_charges']     = isset($shipping_charges)?$shipping_charges:0;
        $this->arr_view_data['shipping_discount']    = isset($shipping_discount)?$shipping_discount:0;
        $this->arr_view_data['promotional_discount'] = isset($promotional_discount)?$promotional_discount:0;
        $this->arr_view_data['orderCalculationData'] = isset($orderCalculationData)?$orderCalculationData:[];
        
        return view('front.checkout',$this->arr_view_data);
    }

    public function postPaymentWithStripe($arr_data)
    {   
        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user && $user->inRole('retailer'))
        {
            $loggedInUserId = $user->id;
        }
        elseif($user && $user->inRole('customer'))
        {
            $loggedInUserId = $user->id;
        }
        else
        {
            Sentinel::logout();
            Flash::error('Please login as retailer or customer for buying product.');
            return redirect('/login');
        }  
        
        $process_payment = $this->StripePaymentService->process_payment($arr_data);

        return $process_payment;
    }

    public function get_card(Request $request)
    {
      $card_id     = isset($request->card_id)?$request->card_id:false;
      $customer_id = isset($request->customer_id)?$request->customer_id:false;
      
      $card_arr = [];

      if($card_id)
      {
          $card = $this->CardModel->where('stripe_card_id',$card_id)
                                  ->where('stripe_customer_id',$customer_id)
                                  ->first();
                                  
          if($card)
          {
            $card_data = $card->toArray();

            $card_data = $this->StripePaymentService->get_single_card_details($card_id,$customer_id);
            
            $card_expiry_month = isset($card_data->exp_month)?$card_data->exp_month:'';
            $card_expiry_year  = isset($card_data->exp_year)?$card_data->exp_year:'';
            $stripe_card_id    = isset($card_data->stripe_card_id)?$card_data->stripe_card_id:'';
            $card_no           = str_pad($card_data->last4, 16, "X", STR_PAD_LEFT);
            $card_expiry       = $card_expiry_month.' / '.$card_expiry_year;

            $card_arr['number'] = $card_no;
            $card_arr['expiry'] = $card_expiry;
          }
          
      }
      return $card_arr;
    }

    public function buy_cart_items(Request $request)
    { 

        $request_data = [];
        $form_data    = $request->all();
      

        $arr_rule=[
                    /*'number'           => 'required',
                    'expiry'           => 'required',*/
                    'shipping_addr'    =>'required',
                    'shipping_city'    =>'required',
                    'shipping_state'   =>'required',
                    'shipping_country' =>'required',
                    'shipping_zip_postal_code' =>'required',

                    'billing_addr'     =>'required',
                    'billing_city'     =>'required',
                    'billing_state'    =>'required',
                    'billing_country'  =>'required',
                    'billing_zip_postal_code' =>'required',
                    'bill_contact_no'  =>'required',
                    'ship_contact_no'  =>'required',
                    // "influencer_code" => 'required'

                  ];

        $validator = Validator::make($form_data,$arr_rule);

        if($validator->fails())
        {
          
          $response['status']      = 'error';
          $response['description'] = 'Form validation failed, please check form fields.';
          return response()->json($response);
        }

        $user         = \Sentinel::check();
        
        

        if($user)
        {
          $user_id = $user->id;
        }

        $adminId = get_admin_id();
              
        if(isset($request_data))
        {
        
            $request_data = [];
            $form_data = $request->all();

            /*$request_data['number'] = $form_data['number'] or '';
            $request_data['expiry'] = $form_data['expiry'] or '';
            $request_data['cvc']    = $form_data['cvc'] or '';
            $request_data['is_exists'] = $form_data['is_exists'] or '';*/
            $request_data['payment_type']             = isset($form_data['payment_type']) ? $form_data['payment_type'] : 3;
            $request_data['shipping_addr']            = $form_data['shipping_addr'];
            $request_data['shipping_city']            = $form_data['shipping_city'];
            $request_data['shipping_state']           = $form_data['shipping_state'];
            $request_data['shipping_country']         = $form_data['shipping_country'];
            $request_data['shipping_zip_postal_code'] = $form_data['shipping_zip_postal_code'];
            $request_data['ship_contact_no']          = $form_data['ship_contact_no'];
            $request_data['shipping_suit_apt']        = $form_data['shipping_suit_apt'];

            $request_data['billing_addr']             = $form_data['billing_addr'];
            $request_data['billing_city']             = $form_data['billing_city'];
            $request_data['billing_state']            = $form_data['billing_state'];
            $request_data['billing_country']          = $form_data['billing_country'];
            $request_data['billing_zip_postal_code']  = $form_data['billing_zip_postal_code'];
            $request_data['bill_contact_no']          = $form_data['bill_contact_no'];
            $request_data['billing_suit_apt']         = $form_data['billing_suit_apt'];
            $request_data['influencer_code']         = $form_data['influencer_code'];
            //dd($request_data);
            $stripe_payment = $this->buy_items($request_data);
            return $stripe_payment;
        }
        else
        {
        
           $response['status'] = 'error';
           $response['description'] = $token['description'] or 'Something went wrong while adding card.';
           return response()->json($response);
        }

                
    }

    public function buy_items($arr_data)
    {
      $user         = \Sentinel::check();

      if($user)
      {
        $user_id = $user->id;
      }
        $shipping_country_name = $billing_country_name = "";

        $shipping_addr            = $arr_data['shipping_addr']; 
        $shipping_city            = $arr_data['shipping_city'];
        $shipping_state           = $arr_data['shipping_state'];
        $shipping_country         = $arr_data['shipping_country'] ;
        $shipping_zip_postal_code = $arr_data['shipping_zip_postal_code'] ;
        $influencer_code = $arr_data['influencer_code'] ;
        
        $shipping_suit_apt        = isset($arr_data['shipping_suit_apt'])?$arr_data['shipping_suit_apt']:'';
        $ship_contact_no          = isset($arr_data['ship_contact_no'])?$arr_data['ship_contact_no']:'';

        
        if(isset($shipping_country) && $shipping_country!="")
        {  
          $shipping_country_name = get_country($shipping_country);
        }

        $arr_data['shipping'] = $shipping_addr . ' ' . $shipping_suit_apt .' '. $shipping_city . ' ' . $shipping_state . ' ' .$shipping_country_name . ' ' . $shipping_zip_postal_code.' '.', Mobile.No: '.$ship_contact_no;


        $billing_addr            = $arr_data['billing_addr']; 
        $billing_city            = $arr_data['billing_city'];
        $billing_state           = $arr_data['billing_state'];
        $billing_country         = $arr_data['billing_country'];
        $billing_zip_postal_code = $arr_data['billing_zip_postal_code'];
        $billing_suit_apt        = isset($arr_data['billing_suit_apt'])?$arr_data['billing_suit_apt']:'';
        $billing_contact_no      = isset($arr_data['bill_contact_no'])?$arr_data['bill_contact_no']:'';


        if(isset($billing_country) && $billing_country!="")
        {  
          $billing_country_name = get_country($billing_country);
        }
        
        $addressing_details = $this->RetailerModel::firstOrNew(['user_id' => $user_id]);
        // $addressing_details = $this->RetailerModel::where([['user_id','=',$user_id],['bill_contact_no','=',''],['bill_contact_no','=','']])->first();
        $representativeOrderId = false;
        
        $representative_order_id = Session::get('representative_order_id');

        if(isset($representative_order_id) && $representative_order_id != '' && $representative_order_id != null)
        {
          $representativeOrderId = true;
        }
        
        if($addressing_details['shipping_addr'] == null && $representativeOrderId == false)
        {

            if ($addressing_details) {
              $addressing_details->user_id                  = isset($user_id)?$user_id:0;
              $addressing_details->shipping_addr            = isset($shipping_addr)?$shipping_addr:'';
              $addressing_details->shipping_city            = isset($shipping_city)?$shipping_city:'';
              $addressing_details->shipping_state           = isset($shipping_state)?$shipping_state:'';
              $addressing_details->shipping_country         = isset($shipping_country)?$shipping_country:'';
              $addressing_details->shipping_zip_postal_code = isset($shipping_zip_postal_code)?$shipping_zip_postal_code:'';
              $addressing_details->shipping_suit_apt        = isset($shipping_suit_apt)?$shipping_suit_apt:''; 
              $addressing_details->ship_contact_no          = isset($ship_contact_no)?$ship_contact_no:'';
              $addressing_details->billing_address          = isset($billing_addr)?$billing_addr:'';
              $addressing_details->billing_city             = isset($billing_city)?$billing_city:'';
              $addressing_details->billing_state            = isset($billing_state)?$billing_state:'';
              $addressing_details->billing_country          = isset($billing_country)?$billing_country:'';
              $addressing_details->billing_zip_postal_code  = isset($billing_zip_postal_code)?$billing_zip_postal_code:'';
              $addressing_details->billing_suit_apt         = isset($billing_suit_apt)?$billing_suit_apt:'';
              $addressing_details->bill_contact_no          = isset($billing_contact_no)?$billing_contact_no:'';
              $addressing_details->influencer_code          = isset($influencer_code)?$influencer_code:'';  
              
              $save_addresses = $addressing_details->save();
            }
        }
        
          $arr_data['billing'] = $billing_addr . ' ' . $billing_suit_apt .' '. $billing_city . ' ' . $billing_state . ' ' .$billing_country_name . ' ' . $billing_zip_postal_code.' '.', Mobile.No: '.$billing_contact_no;

          $transaction_id = str_pad('TXN_',  14, rand('1234567890',10)); 

          /*update transaction id into retailer transaction table*/

          $order_id = Session::get('order_id');

          $representative_order_id = Session::get('representative_order_id');

          $data['transaction_id'] =$transaction_id;

          $result = $this->RetailerQuotesModel->where('id',$order_id)->update($data);

          /*also update transaction id in representative leads*/

          $updated_result = $this->RepresentativeLeadsModel->where('id',$representative_order_id)->update($data);
          
          $order_addr_data = [];
          $order_addr_data['shipping'] = isset($arr_data['shipping'])?$arr_data['shipping']:'';
          $order_addr_data['influencer_code'] = isset($arr_data['influencer_code'])?$arr_data['influencer_code']:'';
          
          
          $order_addr_data['billing'] = isset($arr_data['billing'])?$arr_data['billing']:'';
          
         
          $service_response = $this->GeneralService->store_retailers_cart_items($transaction_id,$order_addr_data);

          if(isset($service_response) &&  $service_response['status'] == 'success'){
              $response['status']      = 'success';
              $response['description'] = 'Order has been placed successfully.';
          } else {
              $response['status']      = 'failure';
              $response['description'] = 'Something went wrong,please try again.';
          }
          return response()->json($response);

    }

  

    public function email_view()
    {
        return view('front.email_template.purchase_order');
    }


    public function net_payment(Request $request)
    {


       $form_data      = $request->all();
       
       $arr_rules1    =  [

                  'shipping_addr'   => 'required',
                  'shipping_city'   => 'required',
                  'shipping_state'  => 'required',
                  'shipping_country'=> 'required',
                  'shipping_zip_postal_code'=> 'required',                        
                  'billing_addr'    => 'required',
                  'billing_city'    => 'required',
                  'billing_state'   => 'required',
                  'billing_country' => 'required',
                  'billing_zip_postal_code' => 'required'
                ];

                $arr_rules2 = [
                  'number'          =>  'required',
                  'expiry'          =>  'required',
                  'cvc'             =>  'required'
                ];

                $arr_rules = $arr_rules1;

                /* only for stripe */
                if($form_data['payment_type'] != 'Net30')
                {
                  $arr_rules = array_merge($arr_rules1,$arr_rules2);
                }

        $form_data      = $request->all();

        $order_addr_data = [];
        $shipping_addr    = isset($form_data['shipping_addr'])?$form_data['shipping_addr']:'';

        $shipping_suit_apt  = isset($form_data['shipping_suit_apt']) && $form_data['shipping_suit_apt']!=''?$form_data['shipping_suit_apt']:'';

        $shipping_city    = isset($form_data['shipping_city']) && $form_data['shipping_city']!=''?$form_data['shipping_city']:'';
        $shipping_state  = isset($form_data['shipping_state']) && $form_data['shipping_state']!=''?$form_data['shipping_state']:'';

    

        if(isset($form_data['shipping_country'])&& $form_data['shipping_country']!='')
        {
          //$shipping_country = get_country($form_data['shipping_country']);
          $shipping_country = $form_data['shipping_country'];
          $shipping_country = isset($shipping_country)&& $shipping_country!=''? $shipping_country:'';
          $shipping_country_name = get_country($shipping_country);
        }
        else
        {
            $shipping_country = $shipping_country_name = '';
        }

        $shipping_zip_postal_code  = isset($form_data['shipping_zip_postal_code']) && $form_data['shipping_zip_postal_code']!=''?$form_data['shipping_zip_postal_code']:'';

        $order_addr_data['shipping'] = $shipping_addr . ' ' . $shipping_city . ' ' . $shipping_state . ' ' .$shipping_country_name . ' ' . $shipping_zip_postal_code;


        $billing_addr   = isset($form_data['billing_addr']) && $form_data['billing_addr']!='' ?$form_data['billing_addr']:'';

        $billing_suit_apt =isset($form_data['billing_suit_apt']) && $form_data['billing_suit_apt']!=''?$form_data['billing_suit_apt']:'';


        $billing_city    = isset($form_data['billing_city']) && $form_data['billing_city']!=''?$form_data['billing_city']:'';

        $billing_state  = isset($form_data['billing_state']) && $form_data['billing_state']!=''?$form_data['billing_state']:'';
        
        if(isset($form_data['billing_country'])&&$form_data['billing_country']!='')
        {
           //$billing_country = get_country($form_data['billing_country']);
           $billing_country = $form_data['billing_country'];
               
           $billing_country = isset($billing_country) && $billing_country!=''?$billing_country:'';
           $billing_country_name = get_country($billing_country);
        }
        else
        {
            $billing_country = $billing_country_name = '';
        }

        $billing_zip_postal_code  = isset($form_data['billing_zip_postal_code'])?$form_data['billing_zip_postal_code']:'';

        $order_addr_data['billing'] = $billing_addr . ' ' . $billing_city . ' ' . $billing_state . ' ' .$billing_country_name . ' ' . $billing_zip_postal_code;


        $payment_type = isset($form_data['payment_type'])?$form_data['payment_type']:'';
        
    
        $service_response = $this->GeneralService->store_retailers_cart_items(false,$order_addr_data,$payment_type);
          
        if($service_response['status'] == 'success')
        {
            $response['status']      = 'success';
            $response['description'] = 'Order has been generated.';

            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }


    }

}