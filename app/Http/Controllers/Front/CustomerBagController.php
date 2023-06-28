<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductsModel;
use App\Models\TempBagModel;
use App\Models\MakerModel;
use App\Models\ProductDetailsModel;
use App\Models\RetailerQuotesModel;
use App\Models\ProductInventoryModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\PromotionsModel;
use App\Models\PromoCodeModel;
use App\Models\PromoCodeRetailerMappingModel;
use App\Models\PromotionsOffersModel;
use App\Models\InfluencerPromoCodeModel;
use App\Models\InfluencerSettingModel;
use App\Models\CustomerQuotesModel;

use App\Common\Services\CustomerOrderService;
use App\Common\Services\MyCartService;
use App\Common\Services\HelperService;

use Validator;
use DB;
use Flash;
use Session;
use Sentinel;

class CustomerBagController extends Controller
{
    /* 
    |  Author : Sagar B. Jadhav
    |  Date   : 11 July 2019
    */
    public function __construct(ProductsModel $ProductsModel,                               
                                TempBagModel $TempBagModel,                                
                                MakerModel $MakerModel,
                                ProductDetailsModel $ProductDetailsModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                ProductInventoryModel $ProductInventoryModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                PromotionsModel $PromotionsModel,
                                PromoCodeModel $PromoCodeModel,
                                PromoCodeRetailerMappingModel $PromoCodeRetailerMappingModel,
                                PromotionsOffersModel $PromotionsOffersModel,
                                InfluencerPromoCodeModel $InfluencerPromoCodeModel, 
                                InfluencerSettingModel $InfluencerSettingModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                CustomerOrderService $CustomerOrderService,
                                HelperService $HelperService,
                                MyCartService $MyCartService
                               )
    {
        $this->arr_view_data                    = [];
        $this->module_title                     = "Bag";
        $this->module_view_folder               = 'front';         
        $this->ProductsModel                    = $ProductsModel; 
        $this->PromotionsModel                  = $PromotionsModel;      
        $this->TempBagModel                     = $TempBagModel;
        $this->PromoCodeModel                   = $PromoCodeModel;
        $this->MakerModel                       = $MakerModel;
        $this->ProductDetailsModel              = $ProductDetailsModel;
        $this->PromotionsOffersModel            = $PromotionsOffersModel;
        $this->RetailerQuotesModel              = $RetailerQuotesModel;
        $this->ProductInventoryModel            = $ProductInventoryModel;
        $this->PromoCodeRetailerMappingModel    = $PromoCodeRetailerMappingModel;
        $this->RepresentativeProductLeadsModel  = $RepresentativeProductLeadsModel; 
        $this->InfluencerPromoCodeModel         = $InfluencerPromoCodeModel;
        $this->InfluencerSettingModel           = $InfluencerSettingModel;
        $this->CustomerQuotesModel              = $CustomerQuotesModel;
        $this->CustomerOrderService             = $CustomerOrderService;
        $this->MyCartService                    = $MyCartService;
        $this->HelperService                    = $HelperService;
    }

    public function my_bag(Request $request)
    {
        // dd(Session::all());
        $loggedInUserId = 0;
        $user = Sentinel::check();
        /*        if(Session::has('unavailable_products')) Session::forget('unavailable_products');
            */
        if($user)
        {
            $loggedInUserId = $user->id;

            if($user->inRole('retailer') || $user->inRole('maker') || $user->inRole('representative') || $user->inRole('sales_manager'))
            {
                return redirect('/my_bag');
            }
        }

        if ($loggedInUserId == 0) {
            //Session::flash('error',"Please login to access your cart.");
            Flash::error("Please login to access your cart.");

           return redirect('/login');
        }
        $bag_arr  = $product_data_arr = $product_arr = $cart_product_arr = $maker_details_arr = [];
        $arr_final_data = [];
        $subtotal       = 0;
        $wholesale_subtotal = 0;
        $ip_address         = $request->ip();
        $session_id         = session()->getId();

        // $bag_obj = $this->TempBagModel->where('ip_address',$ip_address)
                        // ->where('user_session_id',$session_id)->first();


        
         $bag_obj = $this->MyCartService->get_items();
        // /echo "<pre>";print_r($bag_obj);die;

        /*empty card when logged in as a retailer*/
        if($user->inRole('maker') || $user->inRole('admin') || $user->inRole('influencer'))
        {
            // $bag_obj  = $this->TempBagModel->where('user_session_id',$session_id)->where('user_id',0)->delete();
            $bag_obj  = $this->MyCartService->delete();
            $bag_obj = "";
            Flash::error("You are not able to purchase any product. Please login as customer.");
        } 

        if($bag_obj)
        {
            $bag_arr          = $bag_obj->toArray();
           
            $data_arr = json_decode($bag_arr['product_data'],true);
                
            $product_data_arr = $data_arr['sku'];

           
            // $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
            $sku_ids_arr  = $data_arr['sequence'];


            foreach ($sku_ids_arr as $key => $sku) {

                $productData = $this->ProductDetailsModel->where('sku',$sku)
                               ->with('productDetails') 
                               /* Check product is active */
                               ->whereHas('productDetails',function($q){
                                return $q->where('is_active','1');
                               })
                               /* Check product category is active */                               
                               ->whereHas('productDetails.categoryDetails',function($q){
                                return $q->where('is_active','1');
                               })
                               /* Check product Sub-category is active */     
                               ->whereHas('productDetails.categoryDetails.subcategory_details',function($q){
                                return $q->orwhere('is_active','1');
                                })
                               /* Check product vendor is active */
                                ->whereHas('productDetails.userDetails', function ($q) {
                                    return $q->where('is_approved', '1');
                                })
                               ->first();

                               if($productData)
                               {
                                 $sku_product_arr[] = $productData->toArray();
                               }

            }
            
             /* get active items ids  */
            $active_items = isset($sku_product_arr)?array_column($sku_product_arr,'sku'):[];
             /* get deactive items ids */
            $arr_deactivated_item = array_diff($sku_ids_arr ,$active_items);
            $arr_product_ids = $arr_product_names = [];

            if($arr_deactivated_item)
            {
                foreach($arr_deactivated_item as $key => $item_sku)
                {
                    /* remove deactivated items from bag */
                   unset($product_data_arr[$item_sku]);

                   $arr_product_ids[]       = $this->ProductDetailsModel->where('sku',$item_sku)->first()->product_id;


                   $temp_arr['sku']         = $product_data_arr;
                   $temp_arr['sequence']    = array_keys($temp_arr['sku']);
                }


               /* foreach($arr_product_ids as $product_id)
                {
                    $product_names          = $this->ProductsModel->where('id',$product_id)->select('product_name')->first()->toArray();
                    $arr_product_names[]    = $product_names['product_name'];
                }

                  $cnt_unavailable_products = count($arr_product_ids);

                  $tmp ='';
                  $tmp.= '<br> The order you are trying to place, has '. $cnt_unavailable_products .' product(s) unavailable at the moment <br> Unavailable product(s) are as folllows: <br> <ul><li>'.implode('</li><li>',$arr_product_names).'</li></ul>';
            
                  Session::put('unavailable_products',$tmp);*/

                /* update temp bag data */


                $update_arr_data['product_data'] = json_encode($temp_arr);

                // $update_bag_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->update($update_arr_data); 

                $update_bag_data = $this->MyCartService->update($update_arr_data); 
                
                // $bag_obj_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first(); 

                $bag_obj_data = $this->MyCartService->get_items(); 
                
                if($bag_obj_data)
                {
                    $bag_arr          = $bag_obj_data->toArray();

                    $data_arr = json_decode($bag_arr['product_data'],true);
                    
                    $product_data_arr = $data_arr['sku'];
                   
                    $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
                }
            }
            

            if(isset($sku_product_arr) && count($sku_product_arr)>0)
            {   
                
                foreach ($sku_product_arr as $key => $product) 
                {  
                  //dd($product);
                    $cart_product_arr[$key]['user_id']          = $product['product_details']['user_id'];
                    $cart_product_arr[$key]['product_id']       = $product['product_id'];
                    $cart_product_arr[$key]['product_name']     = $product['product_details']['product_name'];
                    $cart_product_arr[$key]['product_image']    = $product['image'];
                    $sku_no = "";

                    $cart_product_arr[$key]['product_min_qty']  = $product['product_min_qty'];

                    $sku_no = isset($product_data_arr[$product['sku']]['sku_no'])?$product_data_arr[$product['sku']]['sku_no']:'';
                    
                  /*$cart_product_arr[$key]['product_image']  = get_sku_image($sku_no);*/

                    //$cart_product_arr[$key]['total_price']    = isset($product_data_arr[$product['sku']]['total_price'])?$product_data_arr[$product['sku']]['total_price']:'';



                    $cart_product_arr[$key]['total_price'] = $product_data_arr[$product['sku']]['item_qty']*$product['product_details']['retail_price'];

                   

                    $cart_product_arr[$key]['shipping_type']    = isset($product['product_details']['shipping_type'])?$product['product_details']['shipping_type']:'';

                    
                    $cart_product_arr[$key]['minimum_amount_off']    = isset($product['product_details']['minimum_amount_off'])?$product['product_details']['minimum_amount_off']:'';

                    $cart_product_arr[$key]['off_type_amount']    = isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:'';
                    

                    $cart_product_arr[$key]['product_weight']    = isset($product['weight'])?$product['weight']:'';

                    $cart_product_arr[$key]['product_length']    = isset($product['length'])?$product['length']:'';

                    $cart_product_arr[$key]['product_width']    = isset($product['width'])?$product['width']:'';

                    $cart_product_arr[$key]['product_height']    = isset($product['height'])?$product['height']:'';

                    $cart_product_arr[$key]['product_option_type']    = isset($product['option_type'])?$product['option_type']:0;

                    $cart_product_arr[$key]['product_option_value']    = isset($product['option'])?$product['option']:0;

                   /* $cart_product_arr[$key]['unit_retail_price']    = isset($product_data_arr[$product['sku']]['retail_price'])?$product_data_arr[$product['sku']]['retail_price']:0;*/

                  $cart_product_arr[$key]['unit_retail_price']  = isset($product['product_details']['retail_price'])?$product['product_details']['retail_price']:0;

                   /* $cart_product_arr[$key]['unit_wholsale_price']    = isset($product_data_arr[$product['sku']]['unit_wholsale_price'])?$product_data_arr[$product['sku']]['unit_wholsale_price']:0;
*/
                  $cart_product_arr[$key]['unit_wholsale_price']  = isset($product['product_details']['unit_wholsale_price'])?$product['product_details']['unit_wholsale_price']:0;

                     //dd($product_data_arr[$product['sku']]);
                  $cart_product_arr[$key]['total_wholesale_price']   = isset($product_data_arr[$product['sku']]['total_wholesale_price'])?$product_data_arr[$product['sku']]['total_wholesale_price']:0;

                  $cart_product_arr[$key]['prodduct_dis_type'] = isset($product['product_details']['prodduct_dis_type'])?$product['product_details']['prodduct_dis_type']:0;


                  $cart_product_arr[$key]['product_dis_min_amt'] = isset($product['product_details']['product_dis_min_amt']) ? $product['product_details']['product_dis_min_amt'] : 0;

                  $cart_product_arr[$key]['product_discount_value'] = isset($product['product_details']['product_discount'])?$product['product_details']['product_discount']:0;


                    /* $cart_product_arr[$key]['product_dis amount'] = isset($product_data_arr[$product['sku']]['product_discount_amount'])?$product_data_arr[$product['sku']]['product_discount_amount']:'0';*/

                    //calculate product discount calculation

                  if(isset($product['product_details']['prodduct_dis_type']) && $product['product_details']['prodduct_dis_type'] == 1)
                  {

                        if($cart_product_arr[$key]['total_price'] >= $product['product_details']['product_dis_min_amt'])
                        {
                           $product_discount_amt = $cart_product_arr[$key]['total_price']*$product['product_details']['product_discount']/100;
                        }
                        else
                        {
                           $product_discount_amt = 0.00;
                        }

                  }
                  elseif(isset($product['product_details']['prodduct_dis_type']) && $product['product_details']['prodduct_dis_type'] == 2)
                  {
                       
                        if($cart_product_arr[$key]['total_price'] >= $product['product_details']['product_dis_min_amt'])
                        {
                            $product_discount_amt = $product['product_details']['product_discount'];
                        }
                        else
                        {
                            $product_discount_amt = 0.00;
                        }
                  }
                  else{
                      $product_discount_amt = 0.00;
                  }
     

                    $cart_product_arr[$key]['product_dis amount'] = isset($product_discount_amt)?$product_discount_amt:'0';


                   
                      if($cart_product_arr[$key]['shipping_type']==2) 
                      {

                        
                        if($cart_product_arr[$key]['total_price']>=$cart_product_arr[$key]['minimum_amount_off'])
                        {
                       
                            $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:0;

                            if(is_numeric($shipping_charges) && is_numeric($cart_product_arr[$key]['off_type_amount']))
                            {

                                $discount_amount =  $shipping_charges * $cart_product_arr[$key]['off_type_amount']/ 100;

                                //$shipping_charges = $shipping_charges-$discount_amount;

                                $cart_product_arr[$key]['shipping_discount'] = $discount_amount;
                                $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;
                            }
                            else
                            {
                               $cart_product_arr[$key]['shipping_discount'] = 0;
                               $cart_product_arr[$key]['shipping_charges']  = 0;
                            }
                        
                        }
                        else
                        {

                            $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:0;

                            $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;
                            $cart_product_arr[$key]['shipping_discount'] = 0;

                        }

                     }

                     if($cart_product_arr[$key]['shipping_type']==1) 
                     { 
                        if($cart_product_arr[$key]['total_price']<$cart_product_arr[$key]['minimum_amount_off'])
                        {
                            $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:0;
                           
                            $cart_product_arr[$key]['shipping_charges'] = isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:0;

                            // $cart_product_arr[$key]['total_wholesale_price'] = $cart_product_arr[$key]['total_wholesale_price'] + $shipping_charges;
                            $cart_product_arr[$key]['shipping_discount'] =  0;
                            //dd($cart_product_arr[$key]['shipping_charges']);

                        }
                        else
                        {
                           
                            $cart_product_arr[$key]['shipping_discount'] = 0;
                            $cart_product_arr[$key]['shipping_charges'] = 0;
                            //dd($cart_product_arr[$key]['shipping_charges']);

                        }
                    }

                     if($cart_product_arr[$key]['shipping_type']==3) 
                     {  
                        if($cart_product_arr[$key]['total_price']>=$cart_product_arr[$key]['minimum_amount_off'])
                        {
                          $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:0;

                         // $shipping_charges = $shipping_charges - $cart_product_arr[$key]['off_type_amount'];

                          $cart_product_arr[$key]['shipping_charges']  = $shipping_charges;

                          $cart_product_arr[$key]['shipping_discount'] = $cart_product_arr[$key]['off_type_amount'];

                        }
                        else
                        {
                           $shipping_charges =  isset($product['product_details']['shipping_charges'])?$product['product_details']['shipping_charges']:0;

                           $cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
                           
                           $cart_product_arr[$key]['shipping_discount'] = 0;
                        }
                    }
                    $cart_product_arr[$key]['wholesale_price']    = isset($product['product_details']['unit_wholsale_price'])?$product['product_details']['unit_wholsale_price']:0;
                    
                    $cart_product_arr[$key]['item_qty']       = isset($product_data_arr[$product['sku']]['item_qty'])?$product_data_arr[$product['sku']]['item_qty']:0;

                    $cart_product_arr[$key]['sku_no']         = isset($product_data_arr[$product['sku']]['sku_no'])?$product_data_arr[$product['sku']]['sku_no']:0;
                    $cart_product_arr[$key]['brand_name']     = get_brand_name($product['product_details']['user_id']);
                }       
               
                
                $subtotal = array_sum((array_column($cart_product_arr,'total_price')));
                $wholesale_subtotal = array_sum((array_column($cart_product_arr,'total_wholesale_price')));  
                //dd($wholesale_subtotal);
                // dd($wholesale_subtotal-10);


            }        

            $arr_prefetch_user_id = array_unique(array_column($cart_product_arr, 'user_id'));
            $arr_prefetch_user_ref =  $this->MakerModel->with('shop_settings')->whereIn('user_id',$arr_prefetch_user_id)->get()->toArray();

           
            $arr_prefetch_user_ref = array_column($arr_prefetch_user_ref,null, 'user_id');
            
           
            $product_sequence     = ""; 
            $arr_product_sequence = $arr_sequence = [];
            $arr_sequence         = $data_arr['sequence'];
            
            if(isset($cart_product_arr) && sizeof($cart_product_arr)>0)
            {
                foreach($cart_product_arr as $key => $value) 
                {

                    $arr_final_data[$value['user_id']]['product_details'][$value['sku_no']] = $value;
                    $arr_final_data[$value['user_id']]['maker_details'] = isset($arr_prefetch_user_ref[$value['user_id']]) ? $arr_prefetch_user_ref[$value['user_id']] : [];

                    //$arr_product_sequence[$product_sequence] = $arr_final_data;   
                }
            }    
           
            /* Rearrange sequence */
            if(sizeof($arr_final_data)>0)
            {
                foreach ($arr_final_data as $_key => $_data) 
                {   
                    $arr_relavant_sequence = array_flip(array_intersect($arr_sequence,array_keys($_data['product_details'])));
                    
                    if(sizeof($arr_relavant_sequence)>0)
                    {
                        foreach ($arr_relavant_sequence as $sequence_attrib => $sequence_tmp) 
                        {
                            $arr_relavant_sequence[$sequence_attrib] = isset($_data['product_details'][$sequence_attrib]) ? $_data['product_details'][$sequence_attrib] : [];
                        }
                    }
                    
                    //array_reverse($arr_relavant_sequence);
                    $arr_final_data[$_key]['product_details'] = $arr_relavant_sequence;                    
                }
            }

           
        }


        //update cart if any field is updated by vendor 
       //date 7 dec priyanka

        $temp_product_arr = isset($bag_arr['product_data']) ? json_decode($bag_arr['product_data']) : '';

        if (isset($arr_final_data) && count($arr_final_data) > 0)
        {

            foreach ($arr_final_data as $key => $product_data)
            {
                foreach ($product_data['product_details'] as $key1 => $product_details)
                {
                      
                     $temp_product_arr->sku->$key1->product_id = isset($temp_product_arr->sku->$key1->product_id)?$temp_product_arr->sku->$key1->product_id:0;

                     $temp_product_arr->sku->$key1->item_qty = isset($temp_product_arr->sku->$key1->item_qty)?$temp_product_arr->sku->$key1->item_qty:0;

                     $temp_product_arr->sku->$key1->total_price = isset($product_details['total_price'])?$product_details['total_price']:0.00;

                     $temp_product_arr->sku->$key1->total_wholesale_price = isset($product_details['total_wholesale_price'])?$product_details['total_wholesale_price']:0.00;

                     $temp_product_arr->sku->$key1->retail_price = isset($product_details['unit_retail_price'])?$product_details['unit_retail_price']:0.00;

                     $temp_product_arr->sku->$key1->wholesale_price = isset($product_details['wholesale_price'])?$product_details['wholesale_price']:0.00;

                     $temp_product_arr->sku->$key1->maker_id = isset($temp_product_arr->sku->$key1->maker_id)?$temp_product_arr->sku->$key1->maker_id:0;

                     $temp_product_arr->sku->$key1->sku_no = isset($product_details['sku_no'])?$product_details['sku_no']:'';

                     $temp_product_arr->sku->$key1->shipping_type = isset($product_details['shipping_type'])?$product_details['shipping_type']:'';

                     $temp_product_arr->sku->$key1->minimum_amount_off = isset($product_details['minimum_amount_off'])?$product_details['minimum_amount_off']:0.00;

                     $temp_product_arr->sku->$key1->off_type_amount = isset($product_details['off_type_amount'])?$product_details['off_type_amount']:0.00;

                     $temp_product_arr->sku->$key1->shipping_charges = isset($product_details['shipping_charges'])?$product_details['shipping_charges']:0.00;

                     $temp_product_arr->sku->$key1->shipping_discount = isset($product_details['shipping_discount'])?$product_details['shipping_discount']:0.00;

                     $temp_product_arr->sku->$key1->prodduct_dis_type = isset($product_details['prodduct_dis_type'])?$product_details['prodduct_dis_type']:'';

                     $temp_product_arr->sku->$key1->product_dis_min_amt = isset($product_details['product_dis_min_amt'])?$product_details['product_dis_min_amt']:0.00;

                     $temp_product_arr->sku->$key1->product_discount_value = isset($product_details['product_discount_value'])?$product_details['product_discount_value']:0.00;

                     $temp_product_arr->sku->$key1->product_discount_amount = isset($product_details['product_dis amount'])?$product_details['product_dis amount']:0.00;
            
                } 

            }
            
        }       



        //then update it on temp bag table

        $bag_arr['product_data'] = json_encode($temp_product_arr);

        $update_data['product_data'] = $bag_arr['product_data'];
        //update in temp bag
        $update_cart_data = $this->TempBagModel->where('user_id', $loggedInUserId)->update($update_data);


      /*-------------------------------------------------------------------------------------*/



      
        // $this->arr_view_data['is_reorder']      = isset($bag_arr['is_reorder'])?$bag_arr['is_reorder']:0;
        $this->arr_view_data['is_reorder']      = 0;
        $this->arr_view_data['arr_final_data']   = $arr_final_data;
        $this->arr_view_data['product_data']    = isset($bag_arr['product_data'])?$bag_arr['product_data']:[];
        $this->arr_view_data['bag_id']          = isset($bag_arr['id'])?$bag_arr['id']:0;
        $this->arr_view_data['subtotal']        = $subtotal; 
        $this->arr_view_data['wholesale_subtotal'] = isset($wholesale_subtotal)?$wholesale_subtotal:"0"; 
        $this->arr_view_data['wholesale_total'] = isset($wholesale_subtotal)?$wholesale_subtotal:"0";    
        $this->arr_view_data['shipping_charges'] = 0;    
        $this->arr_view_data['shipping_discount'] = 0;
        $this->arr_view_data['product_discount'] = 0;    
        $this->arr_view_data['cart_product_arr']= $cart_product_arr;      
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'My '.$this->module_title;
         // dd($this->arr_view_data);
        return view($this->module_view_folder.'.customer_my_bag',$this->arr_view_data);
    }
    

    public function add_to_bag(Request $request)
    {

        $loggedInUserId = 0;
        $loggedInStatus = false;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
            $loggedInStatus = true;
        }

        if($request!=null)
        {
            $product_arr = $new_arr = [];
            $arr_rules = [  
                        'product_id'      => 'required',  
                        'item_qty'        => 'required'                            
                    ];

            $validator = Validator::make($request->all(),$arr_rules); 

            if($validator->fails())
            {        
            $response['status'] = 'warning';
            $response['description'] = 'Form validations failed, please check form fields.';
            return response()->json($response);
            }
    
            $product_id = $request->input('product_id',null);
            $product_id = base64_decode($product_id);

            $chk_is_vendor_product = "";
            $chk_is_vendor_product = $this->ProductsModel->where('user_id',$loggedInUserId)->where('id',$product_id)->count();


            if(isset($chk_is_vendor_product) && $chk_is_vendor_product > 0)
            {
               $response['status']      = 'warning';
               $response['description'] = "You cannot add your product into the cart.";
               return response()->json($response);
            }

            $item_qty   = $request->input('item_qty',null);
            $sku_no     = $request->input('sku_no',null);
            // $bag_count  = get_bag_count();
            $bag_count  = $this->MyCartService->total_items();

            //get retailer ip address
            $ip_address = $request->ip();
            $session_id = session()->getId();
        }

      
        //find is any product added from this ip if yes then push new data to same row
        try
        {
            DB::beginTransaction();
       /*     $exist_obj = $this->TempBagModel->where('ip_address',$ip_address)
                              ->where('user_session_id',$session_id)->first();*/

            // $exist_obj = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();                                          
            $exist_obj = $this->MyCartService->get_items();

            $product_obj = $this->ProductsModel->where('id',$product_id)->first();
            $new_arr = [];
            
            if($product_obj)
            {
                $product_arr = $product_obj->toArray();

                $new_arr['product_id']    = $product_id;           
                $new_arr['item_qty']      = $item_qty;    
                $new_arr['maker_id']      = $product_arr['user_id'];    
                $new_arr['sku_no']        = $sku_no;
                
                $new_arr['retail_price'] = $product_arr['retail_price'];                
                $new_arr['total_price']  = $item_qty * $product_arr['retail_price'];    

                $new_arr['wholesale_price'] = $product_arr['unit_wholsale_price'];
                $new_arr['total_wholesale_price'] = $item_qty * $product_arr['unit_wholsale_price']; 

                $new_arr['shipping_charges'] = isset($product_arr['shipping_charges'])?$product_arr['shipping_charges']:'';

                    //dd($new_arr);
                $new_arr['shipping_type'] = isset($product_arr['shipping_type'])?$product_arr['shipping_type']:'';


                $new_arr['minimum_amount_off'] = isset($product_arr['minimum_amount_off'])?$product_arr['minimum_amount_off']:'';

                $new_arr['off_type_amount'] = isset($product_arr['off_type_amount'])?$product_arr['off_type_amount']:'';

                $new_arr['shipping_discount'] = isset($product_arr['shipping_discount'])?$product_arr['shipping_discount']:'';

                $shipping_values = $this->calculate_customer_shipping_discount($new_arr['shipping_type'], $new_arr['total_price'] ,$new_arr['minimum_amount_off'],$new_arr['off_type_amount'],$new_arr['shipping_charges']);

                

                $new_arr['prodduct_dis_type'] = isset($product_arr['prodduct_dis_type'])?$product_arr['prodduct_dis_type']:'0';

                $new_arr['product_dis_min_amt'] = isset($product_arr['product_dis_min_amt'])?$product_arr['product_dis_min_amt']:'0';

                $new_arr['product_discount_value'] = isset($product_arr['product_discount'])?$product_arr['product_discount']:'0';

                if($new_arr['prodduct_dis_type']!='')
                {
                $product_dis_amount = $this->HelperService->calculate_product_discount($new_arr['prodduct_dis_type'],$new_arr['product_dis_min_amt'],$new_arr['product_discount_value'],$new_arr['total_price']);
                }
                else
                {
                    $product_dis_amount = 0;
                }
                
                
                // $new_arr['product_discount_amount'] = $product_dis_amount;
                // $new_arr['shipping_discount'] = isset($shipping_values['shipping_discount'])?$shipping_values['shipping_discount']:0;
                // $new_arr['shipping_charges'] = isset($shipping_values['shipping_charges'])?$shipping_values['shipping_charges']+$new_arr['shipping_discount']:0;


                $new_arr['product_discount_amount'] = $product_dis_amount;
                $new_arr['shipping_discount'] = isset($shipping_values['shipping_discount'])?$shipping_values['shipping_discount']:0;
                // $new_arr['shipping_charges'] = isset($shipping_values['shipping_charge'])?$shipping_values['shipping_charge']+$new_arr['shipping_discount']:0;
                $new_arr['shipping_charges'] = isset($shipping_values['shipping_charge'])?$shipping_values['shipping_charge']:0;

            }
            
            if($exist_obj)
            {
                $exist_arr = $exist_obj->toArray();
                $json_data = [];            
                $json_decoded_data = json_decode($exist_arr['product_data'],true);
                

                /* Update product details, if product are already available on cart */
                $data = isset($json_decoded_data['sku'][$new_arr['sku_no']])?$json_decoded_data['sku'][$new_arr['sku_no']]:false;

                if(isset($data) && !empty($data))
                {
                    $qty = $data['item_qty'] + $item_qty;
/*
                    if($qty > 20)
                    {
                        $response['status']  = 'warning';
                        $response['description'] = 'Only 20 product added into BAG';  
                        return response()->json($response);

                    }*/

                    $retail_price           = $qty * $product_arr['retail_price'];
                    $whole_sale_price       = $qty * $product_arr['unit_wholsale_price'];
                    
                    $new_arr['item_qty']    = $qty;
                    $new_arr['total_price'] = $retail_price;
                    $new_arr['total_wholesale_price'] = $whole_sale_price; 

                }
                
                /* -------------- End --------------------- */


                $json_decoded_data['sku'][$new_arr['sku_no']] = $new_arr;

                // array_push($json_decoded_data['sequence'],$sku_no);
                // dD($new_arr);
                // array_unshift($json_decoded_data['sku'][$new_arr['sku_no']],$new_arr);
                array_unshift($json_decoded_data['sequence'],$sku_no);

                $update_arr['product_data']            = json_encode($json_decoded_data,true);  
                // dd($update_arr);
                // $is_updated = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->update($update_arr);

                $is_updated = $this->MyCartService->update($update_arr);
                
                if($is_updated)
                {  
                    // Forget Customer Promotion Date session. If customer has applied promo code and trying to add new product then forget the session
                    Session::forget('customer_promotion_data');

                    DB::commit();
                    $response['status']      = 'SUCCESS';
                    $response['bag_count']   = $this->MyCartService->total_items();
                    $response['user_loggedIn_status'] = $loggedInStatus;

                    if($loggedInStatus == false)
                    {
                        $response['description'] = 'Product added to cart, please login as retailer go get wholesale price.';
                    }
                    else
                    {
                        $response['description'] = 'Product added to cart.';
                    }
                }
                else
                {
                    DB::rollback();
                    $response['status']      = 'FAILURE';
                    $response['description'] = 'Something went wrong, please try again.';                   
                }
            }
            else
            {
               
                //create
                $arr_cart_data = [];
                $arr_sequence  = [];
                $arr_final     = [];

                //change key product id to sku id
                $arr_cart_data[$new_arr['sku_no']] = $new_arr;
                $arr_sequence[0] = $sku_no;      

                $arr_final['sku']      = $arr_cart_data;                  
                $arr_final['sequence'] = $arr_sequence;   
                     
                // array_push($arr_cart_data, $new_arr);

                //dd($arr_final);
                $encoded_new_arr = json_encode($arr_final);

                //dd($encoded_new_arr);
                //dd($encoded_new_arr);
                $insert_arr['product_data']    = $encoded_new_arr;
                $insert_arr['ip_address']      = $ip_address;
                $insert_arr['user_session_id'] = $session_id;
                $insert_arr['user_id']         = $loggedInUserId;

               

                $is_created = $this->TempBagModel->create($insert_arr);
               
                if($is_created)
                {
                    // Forget Customer Promotion Date session. If customer has applied promo code and trying to add new product then forget the session
                    Session::forget('customer_promotion_data');

                    DB::commit();
                    $response['status']      = 'SUCCESS';
                    $response['bag_count']   = $bag_count;
                    $response['user_loggedIn_status'] = $loggedInStatus;
                    $response['description'] = 'Product added to cart.';
                }
                else
                {
                    DB::rollback();
                    $response['status']      = 'FAILURE';
                    $response['description'] = 'Something went wrong, please try again.';
                }
            }

        }catch(Exception $e)
        {
            DB::rollback();
            $response['status']      = 'FAILURE';
            $response['description'] = $e->getMessage();
        }

        
        return response()->json($response);
    }

public function reorder_add_to_bag($order_data=false)
{       
    $ip_addr = $order_data[0]['ip'];
    $session_id = session()->getId();

    $loggedInUserId = 0;
    $loggedInStatus = false;
    $user = Sentinel::check();
    if($user)
    {
        $loggedInUserId = $user->id;
        $loggedInStatus = true;
    }



   
    $cart_count = $this->MyCartService->total_items();                           

    
    if($cart_count>0)
    {
        /*$this->TempBagModel->where('ip_address',$ip_addr)
                              ->where('user_session_id',$session_id)->delete();*/

       $delete_items = $this->MyCartService->delete();                      
    }
    $response=[];

    foreach ($order_data as $key => $value)
    {
       
        $session_data = session()->all();
        $ip_address = $value['ip'];
        $session_id = session()->getId();
        
          

        $product_id = isset($value['product_id'])?$value['product_id']:null;
        //$product_id = base64_decode($product_id);
        $item_qty   = isset($value['item_qty'])?$value['item_qty']:null;
        $sku_no     =isset($value['sku_no'])?$value['sku_no']:null;
        $bag_count  = $this->MyCartService->total_items();
        //get retailer ip address
        $ip_address = $value['ip'];
        $session_id = session()->getId();
        //find is any product added from this ip if yes then push new data to same row
        try
        {
            DB::beginTransaction();
          /*  $exist_obj = $this->TempBagModel->where('ip_address',$ip_address)
                                  ->where('user_session_id',$session_id)->first();*/


            // $exist_obj = $this->TempBagModel->where('ip_address',$ip_addr)->where('user_session_id',$session_id)->first();                      

            $exist_obj = $this->MyCartService->get_items(); 

            $product_obj = $this->ProductsModel->where('id',$product_id)->first();
                
            $new_arr = [];
            if($product_obj)
            {
                $product_arr = $product_obj->toArray();

                
                $new_arr['product_id']    = $product_id;           
                $new_arr['item_qty']      = $item_qty;    
                $new_arr['maker_id']      = $product_arr['user_id'];    
                $new_arr['sku_no']        = $sku_no;
                $new_arr['retail_price']  = floatval($product_arr['retail_price']);                
                $new_arr['total_price']   = $item_qty * floatval($product_arr['retail_price']);    
                $new_arr['wholesale_price'] = floatval($product_arr['unit_wholsale_price']);
                $new_arr['total_wholesale_price'] = $item_qty * floatval($product_arr['unit_wholsale_price']); 
                $new_arr['shipping_charges'] = isset($product_arr['shipping_charges'])?floatval($product_arr['shipping_charges']):'';
                $new_arr['shipping_type'] = isset($product_arr['shipping_type'])?$product_arr['shipping_type']:'';
                $new_arr['minimum_amount_off'] = isset($product_arr['minimum_amount_off'])?floatval($product_arr['minimum_amount_off']):'';
                $new_arr['off_type_amount'] = isset($product_arr['off_type_amount'])?floatval($product_arr['off_type_amount']):'';
                $new_arr['shipping_discount'] = isset($product_arr['shipping_discount'])?floatval($product_arr['shipping_discount']):'';

                //dd($new_arr);

                $shipping_values = $this->calculate_customer_shipping_discount($new_arr['shipping_type'], $new_arr['total_price'] ,$new_arr['minimum_amount_off'],$new_arr['off_type_amount'],$new_arr['shipping_charges']);

               

                

                $new_arr['prodduct_dis_type'] = isset($product_arr['prodduct_dis_type'])?floatval($product_arr['prodduct_dis_type']):0;
                $new_arr['product_dis_min_amt'] = isset($product_arr['product_dis_min_amt'])?floatval($product_arr['product_dis_min_amt']):0;
                $new_arr['product_discount_value'] = isset($product_arr['product_discount'])?floatval($product_arr['product_discount']):0;
                //dd($new_arr);
                if($new_arr['prodduct_dis_type']!='')
                {
                    $product_dis_amount = floatval($this->HelperService->calculate_product_discount($new_arr['prodduct_dis_type'],$new_arr['product_dis_min_amt'],$new_arr['product_discount_value'],$new_arr['total_price']));

                }
                else
                {
                    $product_dis_amount = 0;
                }
                    
                $new_arr['product_discount_amount'] = floatval($product_dis_amount);
                $new_arr['shipping_discount'] = isset($shipping_values['shipping_discount'])?$shipping_values['shipping_discount']:0;
                $new_arr['shipping_charges'] = isset($shipping_values['shipping_charge'])?$shipping_values['shipping_charge']:0;
                }
                if($exist_obj)
                {//dd(123464654);
                    $exist_arr = $exist_obj->toArray();
                   
                    $json_data = [];            
                    $json_decoded_data = json_decode($exist_arr['product_data'],true);
                    /* Update product details, if product are already available on cart */
                   //dd($json_decoded_data['sku'][$new_arr['sku_no']]);

                    $data = isset($json_decoded_data['sku'][$new_arr['sku_no']])?$json_decoded_data['sku'][$new_arr['sku_no']]:false;

                    if(isset($data) && !empty($data))
                    {   
                        $qty = $data['item_qty'] + $item_qty;
                        $retail_price      = $qty * $product_arr['retail_price'];
                        $whole_sale_price  = $qty * $product_arr['unit_wholsale_price'];
                        $new_arr['item_qty'] = $qty;
                        $new_arr['total_price'] = floatval($retail_price);
                        $new_arr['total_wholesale_price'] = floatval($whole_sale_price); 
                    }
                    
                    
                    /* -------------- End --------------------- */
                    $json_decoded_data['sku'][$new_arr['sku_no']] = $new_arr;
                    array_push($json_decoded_data['sequence'],$sku_no);
                    $update_arr['product_data'] = json_encode($json_decoded_data,true); 


                   /* $is_updated = $this->TempBagModel->where('ip_address',$ip_address)
                                       ->where('user_session_id',$session_id)->update($update_arr);*/

                    // $is_updated = $this->TempBagModel->where('ip_address',$ip_addr)->where('user_session_id',$session_id)->update($update_arr);

                    $is_updated = $this->MyCartService->update($update_arr);
                                                       
                    if($is_updated)
                    {  
                        DB::commit();
                        $response['status']      = 'SUCCESS';
                        $response['bag_count']   = get_bag_count();
                        $response['user_loggedIn_status'] = $loggedInStatus;
                        $response['description'] = 'Product added to cart.';
                        //return $response;
                        $is_check = 1;
                    }
                    else
                    {
                        DB::rollback();
                        $response['status']      = 'FAILURE';
                        $response['description'] = 'Something went wrong, please try again.'; 
                       //return $response;
                         $is_check = 0;

                    }
                }
                else
                {
                    //create
                    $arr_cart_data = [];
                    $arr_sequence  = [];
                    $arr_final     = [];

                    //change key product id to sku id
                    $arr_cart_data[$new_arr['sku_no']] = $new_arr;
                    $arr_sequence[0] = $sku_no;      

                    $arr_final['sku']      = $arr_cart_data;                  
                    $arr_final['sequence'] = $arr_sequence;   
                         
                    // array_push($arr_cart_data, $new_arr);

                    //dd($arr_final);
                    $encoded_new_arr = json_encode($arr_final);

                    //dd($encoded_new_arr);
                    //dd($encoded_new_arr);
                    $insert_arr['product_data']    = $encoded_new_arr;
                    $insert_arr['ip_address']      = $ip_address;
                    $insert_arr['user_session_id'] = $session_id;
                    $insert_arr['user_id']         = $loggedInUserId;
                     /* add this for only */
                    $insert_arr['is_reorder']      = '1'; 
                   
                    $is_created = $this->TempBagModel->create($insert_arr);
                   
                    if($is_created)
                    {
                        DB::commit();
                        $response['status']      = 'SUCCESS';
                        $response['bag_count']   = $bag_count;
                        $response['user_loggedIn_status'] = $loggedInStatus;
                        $response['description'] = 'Product added to cart.';
                        //return $response;
                         $is_check = 1;
                        
                    }
                    else
                    {
                        DB::rollback();
                        $response['status']      = 'FAILURE';
                        $response['description'] = 'Something went wrong, please try again.';
                         $is_check = 0;
                        //return $response;

                       
                    }
                }
            }
                   
                catch(Exception $e)
                {
                    DB::rollback();
                    $response['status']      = 'FAILURE';
                    $response['description'] = $e->getMessage();
                }
    }
   
    if($is_check==1)
    {
        $response['status'] = "SUCCESS";
    }
    else
    {
        $response['status']      = 'FAILURE';
    }
    
    return $response;

}

 public function generate_reorder_data($order_no=false,$vendor_id=false,Request $request,$is_lead=false)
{
   
    if(isset($order_no))
    {
        $order_no = isset($order_no)?base64_decode($order_no):'';
        
        $vendor_id = isset($vendor_id)?base64_decode($vendor_id):'';



        if($is_lead=='lead')
        {
            $obj_order_details = $this->RepresentativeProductLeadsModel->with('product_details')->where('order_no',$order_no)
                                 ->where('maker_id',$vendor_id)
                                 ->get();
                                
            if(isset($obj_order_details))
            {
                $arr_order_details = $obj_order_details->toArray();
            }
             else
            {
                return Redirect::back()->with('message','No data available.');
            }
            $order_product_data = $arr_order_details;  
        }
        else
        {
             $obj_order_details = $this->CustomerQuotesModel->with('quotes_details.product_details')             ->where('order_no',$order_no)
                                                               ->where('maker_id',$vendor_id)
                                                               ->get();
             
             if(isset($obj_order_details))
                {
                    $arr_order_details = $obj_order_details->toArray();
                }
            else
            {
                return Redirect::back()->with('message','No data available.');
            }
            $order_product_data = array_column($arr_order_details, 'quotes_details');
        }
       
       


        //$order_product_data = array_column($arr_order_details, 'quotes_details');
       // dd($order_product_data);
        $order_products =[];
        $order_product_data_arr = []; 
        $order_product_data_arr['sku'] = [];
        $arr_final =[];
        $sku_arr =[];

            //dd($order_product_data);
        if($is_lead != 'lead')
        {
            foreach ($order_product_data[0] as $key => $value)
            {
              
            $prod_data=[];
            $product_data['product_id']= $value['product_id'];
            $product_data['item_qty']=$value['qty'];
            $product_data['maker_id'] = $value['product_details']['user_id'];
            $product_data['sku_no']  = $value['sku_no'];
            $product_data['ip'] = $request->ip();
                
            $request->product_id=$value['product_id'];
            $request->sku_no= $value['sku_no'];
            $request->retail_price = $value['retail_price'];
            $request->wholesale_price = $value['wholesale_price'];
            $request->item_qty=$value['qty'];

            array_push($order_product_data_arr['sku'],$product_data);
            }
        }
        else
        {
             foreach ($order_product_data as $key => $value)
            {
            $prod_data=[];
            $product_data['product_id']= $value['product_id'];
            $product_data['item_qty']=$value['qty'];
            $product_data['maker_id'] = $value['product_details']['user_id'];
            $product_data['sku_no']  = $value['sku'];
            $product_data['ip'] = $request->ip();
                
            $request->product_id=$value['product_id'];
            $request->sku_no= $value['sku'];
            $request->retail_price = $value['retail_price'];
            $request->wholesale_price = $value['wholesale_price'];
            $request->item_qty=$value['qty'];
            $request->shipping_charge=$value['product_shipping_charge'];
            $request->shipping_discount=$value['shipping_charges_discount'];

            array_push($order_product_data_arr['sku'],$product_data);
            }
        }
               
                $data = $this->reorder_add_to_bag($order_product_data_arr['sku']);
                //dd($data);
                if($data['status']=="SUCCESS")
                {
                     return redirect(url('/').'/customer_my_bag');
                }
                else
                {
                    return Redirect::back()->with('message','Operation failed,');

                }
            
        }
   
    }

    public function delete_product(Request $request,$enc_prod_id = 0)
    {
        $bag_arr    = $update_arr = [];
        $is_update  = false;
        //$product_id = base64_decode($enc_prod_id);
        $sku_no = base64_decode($enc_prod_id);

        $ip_address = $request->ip();
        $session_id = session()->getId();


        $loggedInUserId = 0;
        $user = Sentinel::check();
        if($user)
        {
            $loggedInUserId = $user->id;
        }

        /* $bag_obj    =  $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();*/

        // $bag_obj = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();

        $bag_obj = $this->MyCartService->get_items();

        if($bag_obj)
        {
            $cart_product_count = $this->MyCartService->total_items();
            if($cart_product_count==1)
            {
                $is_update = $this->MyCartService->delete();
            }
            else
            {
                $bag_arr = $bag_obj->toArray();         

                $product_bag_data = $bag_arr['product_data'];
                $product_bag_data = json_decode($product_bag_data,true);
                $product_sequence = $product_bag_data['sequence'];
                $product_data     = $product_bag_data['sku'];
                

                if(isset($product_data[$sku_no]))
                {
                    unset($product_data[$sku_no]);
                    if (($sku_key = array_search($sku_no, $product_sequence)) !== false) {
                        unset($product_sequence[$sku_key]);
                    }
                }

                // $update_arr['product_data'] = json_encode(array_values($product_data),true);
                $arr_sequence = [];
                $arr_sequence['sku']        = $product_data;    
                $arr_sequence['sequence']   = $product_sequence;    

                $update_arr['product_data'] = json_encode($arr_sequence,true);
                  
                // $is_update = $this->TempBagModel->where('ip_address',$ip_address)->update($update_arr);
                
                $is_update = $this->MyCartService->update($update_arr);

            }
            

            
            /*forget promotion session*/
            Session::forget('promo_shipping_charges');
            Session::forget('promotion_discount_amt');
            Session::forget('total_order_amout');
            Session::forget('customer_promotion_data');
            
        }

        if($is_update)
        {
            Flash::success('Product has been deleted from cart.');
        }
        else
        {
            Flash::error('Error occurred while deleting product from cart.');
        }

        return redirect()->back();
    }

    public function update_qty(Request $request)
    {
        $formData   = [];
        $formData   = $request->all(); 
        
        $bag_arr    = $update_arr = $product_data = [];
        $is_update  = false;
        
        $product_id = base64_decode($formData['pro_id']);
        $sku_no     = base64_decode($formData['sku_no']);
        $update_qty = isset($formData['qty'])?$formData['qty']:"";
           
        $ip_address = $request->ip();
        $session_id = session()->getId();




        $loggedInUserId = 0;
        $user = Sentinel::check();
        if($user)
        {
            $loggedInUserId = $user->id;
        }


        //check product inventory available or not
        $current_qty = $this->ProductInventoryModel->where('product_id',$product_id)
                                                   ->where('sku_no',$sku_no)
                                                   ->pluck('quantity')
                                                   ->first();

       /* if($update_qty > $current_qty)
        {
            $response['status']      = "warning";
            $response['description'] = "Product purchase limit is exceed. Available quantity is: '".$current_qty."'";

            return response()->json($response);
        }*/

        
        // $bag_obj    = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first(); 

        $bag_obj    = $this->MyCartService->get_items();                   
       
        if($bag_obj)
        {
            $bag_arr = $bag_obj->toArray();      
           
            $product_bag_data = json_decode($bag_arr['product_data'],true);
            $product_data     = $product_bag_data['sku'];
            $product_sequence = $product_bag_data['sequence'];

            if(isset($product_data) && sizeof($product_data)>0)
            {   
                foreach($product_data as $key=> $product)
                {//dd($product_data);
                    if($sku_no==$key)
                    {
                        //dd($product_id,$product_data);
                        $subtotal = 0;
                        unset($product_data[$key]);    
                         
                        $product_details = get_product_details($product_id);
                        

                        $retail_price    = isset($product_details['retail_price'])?$product_details['retail_price']:"";
                        $wholesale_price = isset($product_details['unit_wholsale_price'])?$product_details['unit_wholsale_price']:"";

                     
                        
                        $new_arr['product_id']    = $product['product_id'];           
                        $new_arr['item_qty']      = $update_qty;    
                        $new_arr['total_price']   = $update_qty * $retail_price;
                        $new_arr['total_wholesale_price'] = $update_qty * $wholesale_price;   

                        $new_arr['retail_price']    = $product['retail_price'];               
                        $new_arr['wholesale_price'] = $product['wholesale_price'];

                        $new_arr['maker_id']      = $product['maker_id']; 
                        $new_arr['sku_no']        = isset($product['sku_no'])?$product['sku_no']:"";   
                        
                        $new_arr['shipping_type'] = isset($product_details['shipping_type'])?$product_details['shipping_type']:0;


                       $new_arr['minimum_amount_off'] = isset($product_details['minimum_amount_off'])?$product_details['minimum_amount_off']:0;

                        $new_arr['off_type_amount'] = isset($product_details['off_type_amount'])?$product_details['off_type_amount']:0;

                        $new_arr['shipping_charges'] = isset($product_details['shipping_charges'])?$product_details['shipping_charges']:0;
                        
                        $new_arr['shipping_discount'] = isset($product['shipping_discount'])?$product['shipping_discount']:0;
                     
                     $shipping_arr = $this->calculate_customer_shipping_discount($new_arr['shipping_type'],$new_arr['total_price'] ,$new_arr['minimum_amount_off'],$new_arr['off_type_amount'],$new_arr['shipping_charges']);




                    $new_arr['prodduct_dis_type'] = isset($product['prodduct_dis_type'])?$product['prodduct_dis_type']:0;

                    $new_arr['product_dis_min_amt'] = isset($product['product_dis_min_amt'])?$product['product_dis_min_amt']:0;

                    $new_arr['product_discount_value'] = isset($product['product_discount_value'])?$product['product_discount_value']:'';
                   

                    if( $new_arr['prodduct_dis_type']!='')
                    {
                    $product_dis_amount = $this->HelperService->calculate_product_discount($new_arr['prodduct_dis_type'],$new_arr['product_dis_min_amt'],$new_arr['product_discount_value'],$new_arr['total_price']);
                        //dd($product_dis_amount);
                    }
                    else
                    {
                        $product_dis_amount = 0;
                    }
                    $new_arr['product_discount_amount'] = $product_dis_amount;
                    
                     $new_arr['shipping_charges'] = $shipping_arr['shipping_charge'];
                     $new_arr['shipping_discount'] = $shipping_arr['shipping_discount'];
                    // dd($shipping_arr);




                        $product_data[$sku_no]    =  $new_arr;

                        //----                        
                        $subtotal = array_sum((array_column($product_data,'total_price'))); 

                        $wholesale_subtotal = array_sum((array_column($product_data,'total_wholesale_price'))); 

                        $shipping_charges  = array_sum((array_column($product_data,'shipping_charges')));
                        $shipping_discount = array_sum((array_column($product_data,'shipping_discount')));
                       // dd($wholesale_subtotal+);
                        $wholesale_subtotal = $wholesale_subtotal+$shipping_charges-$shipping_discount;
                        //dd($wholesale_subtotal);
                         session::put('amount',$subtotal); 
                         session::put('total_amount',$subtotal);
                         session::put('shipping_charges',$shipping_charges);  
                         // dd(session::get('amount'));
                        
                        $arr_sequence = [];
                        $arr_sequence['sku'] = $product_data;    
                        $arr_sequence['sequence'] = $product_sequence;    

                        $update_arr['product_data'] = json_encode($arr_sequence,true);

                        $session_data = json_encode($product_data,true);
                        Session::forget('bag_data');

                        /*forget promotion session*/
                        // Session::forget('promo_shipping_charges');
                        Session::forget('promotion_discount_amt');
                        Session::forget('total_order_amout');
                        Session::forget('customer_promotion_data');

                        $is_update = Session::put('bag_data',$session_data);
                       
                        // $is_update = $this->TempBagModel->where('ip_address',$ip_address)->update($update_arr);

                        $is_update = $this->MyCartService->update($update_arr);

                        
                        if($is_update)
                        {
                            $response['subtotal'] = $subtotal;
                            $response['wholesale_subtotal'] = $wholesale_subtotal;
                            $response['total_price'] = isset($new_arr['total_price'])?$new_arr['total_price']:"";
                            $response['total_wholesale_price'] = isset($new_arr['total_wholesale_price'])?$new_arr['total_wholesale_price']:"";
                            $response['status'] = 'SUCCESS';
                        }

                    }    
                }
            }    
        }
        else
        {
            $response['status'] = 'FAILURE';    
        }

        return response()->json($response);
    }

    public function save_bag(Request $request)
    {  

        $loggedInUserId = 0;
        $user = Sentinel::check();


        if($user)
        {
            $loggedInUserId = $user->id;
        }


        $product_data = $request->input('product_data'); 

        $data_arr = json_decode($product_data,true);
            $product_data_arr = $data_arr['sku'];
           
            $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
            foreach($sku_ids_arr as $sku)
            {
                $product_qty_available = $this->ProductInventoryModel->where('sku_no',$sku)->select('quantity','product_id')->first()->toArray();
                if($product_qty_available['quantity']==0)
                {
                    $response['status'] = 'Out Of Stock';
                     return response()->json($response);
                }
            }
        $is_save = Session::put('bag_data',$product_data);


        if($user && $user->inRole('customer'))
        {   
            // dd($request->session()->all());
            $is_amount_set = $this->set_session($request);
    
            if($is_amount_set == 'SUCCESS')
            {
               $response['status']   = 'SUCCESS';
               $response['next_url'] = url('/customer_checkout'); 
            }
            else{
                $response['status']   = 'ERROR';
                $response['next_url'] = url('/');
            }

            return response()->json($response);        
        }
        
        if($user && !$user->inRole('admin'))
        {
            if($product_data)
            {

                Flash::error('Please login as a retailer or customer for checkout.');

                $response['status']   = 'SUCCESS';
                $response['next_url'] = url('/customer_my_bag');
            }
            else
            {
                $response['status'] = 'FAILURE';    
            }
        }
        else
        {
            Flash::error('Please login as a retailer or customer for checkout.');
            $response['status']   = 'SUCCESS';
            $response['next_url'] = url('/login');
        }                   
        
        return response()->json($response);
    }

          

    public function temp_save_bag(Request $request)
    {   
        $loggedInUserId = 0;
        $user = Sentinel::check();
        if($user)
        {
            $loggedInUserId = $user->id;
        }
        $product_data = $request->input('product_data');  
        //save product data to session 
        $is_save = Session::put('bag_data',$product_data);
        if($user && $user->inRole('retailer'))
        {
           $quotes_save =  $this->CustomerOrderService->apply_for_quotes();
           
           if($quotes_save['status'] == 'success')
           {
                Flash::success('You have successfully applied for quotes.');
                $response['status']   = 'SUCCESS';
                $response['next_url'] = url('/retailer/my_quote/view/'.base64_encode($quotes_save['quote_id']));                     
                return response()->json($response);
           }           
        }
        
        if($user && !$user->inRole('admin'))
        {
            if($product_data)
            {
                Flash::error('You are logged in with different user type, please login as a retailer for applying quotes.');
                /*return Redirect::back()->withErrors(['msg', 'The Message']);*/
                $response['status']   = 'SUCCESS';
                $response['next_url'] = url('/my_bag');
                //dd($response['next_url']);
            }
            else
            {
                $response['status'] = 'FAILURE';    
            }
        }
        else
        {
            Flash::error('Please login as a retailer for applying quotes.');
            $response['status']   = 'SUCCESS';
            $response['next_url'] = url('/login');
        }                   
        
        return response()->json($response);
    }
    


    public function empty_cart(Request $request)
    {
        $ip_addr    = $request->ip();
        $session_id = session()->getId();

        $loggedInUserId = 0;
        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

         // $is_delete  = $this->TempBagModel->where('ip_address',$ip_addr)->where('user_session_id',$session_id)->delete();
         $is_delete  = $this->MyCartService->delete();

        // Forget Customer Promotion Date session. If customer has applied promo code and trying to empty cart then forget the session
        Session::forget('customer_promotion_data');

        // $is_delete  = $this->TempBagModel->where('user_id',$loggedInUserId)->delete();

        if($is_delete)
        {
            $response['status']   = 'success';
            $response['next_url'] = url('/customer_my_bag');
        }
        else
        {
            $response['status'] = 'failure'; 
            $response['next_url'] = url('/customer_my_bag');   
        }               
        
        return response()->json($response);
      
    }

    public function set_session(Request $request)
    {
        $bag_arr = json_decode($request->product_data,true);
        $arr_product = $bag_arr['sku'];
        $wholesale_total_price = 0;
        $shipping_charges =0;
        $product_discount=0;

        foreach ($arr_product as $key => $value) 
        {   
      
            $wholesale_total_price+=isset($value['total_wholesale_price'])?$value['total_wholesale_price']:0;

            $wholesale_total_price+=isset($value['shipping_charges'])?(float)$value['shipping_charges']:0;
            $shipping_charges += isset($value['shipping_charges'])?(float)$value['shipping_charges']:0;
            $product_discount += isset($value['product_discount_amount'])?(float)$value['product_discount_amount']:0;
            
        }

        
        $bag_id          = isset($request->bag_id)?$request->bag_id:'';
        
        $wholesale_total_price = $wholesale_total_price;
        $total_price = $wholesale_total_price-$shipping_charges;

        $shipping_charges = $shipping_charges;
        $shipping_discount = Session::get('shipping_discount');
        $product_discount = Session::get('product_discount');
        $wholesale_total_price = $wholesale_total_price-$shipping_discount-$product_discount;

        
        if($wholesale_total_price > 0)
        {
           $bag_id = $request->input('bag_id');
           session::put('amount',$wholesale_total_price);
           session::put('total_amount',$total_price);
           session::put('shipping_charges',$shipping_charges);
           session::put('product_discount',$product_discount);

           session::put('bag_id',$bag_id);
        }

        if(session::has('amount'))
        {
            $response = 'SUCCESS';    
        }
        else
        {
            $response = 'FAILURE';
        }
    

        return $response;
    }

    public function apply_promo_code(Request $request)
    {
        // dd($request->all());
        // $promo_code_id = $vendor_id = $user_id =$promotion_id = 0;
        $promo_code_id = 0;
        $expiry_date   = '';
        $arr_promo_code = $arr_influencer_settings = [];

        $promo_code   = $request->input('promo_code');
        $subtotal_amt = $request->input('subtotal_amt');
        
        $tot_product_dis_amt      = isset($request->tot_product_dis_amt)?$request->tot_product_dis_amt: "0.00";
        $tot_ship_charges         = isset($request->tot_ship_charges)?$request->tot_ship_charges: "0.00";
        $tot_shipping_discount    = isset($request->tot_shipping_discount)?$request->tot_shipping_discount: "0.00";
        // $vendor_id               = $request->input('maker_id');

        $subtotal_amt = $subtotal_amt+ $tot_ship_charges - $tot_product_dis_amt - $tot_shipping_discount;

        // dd($subtotal_amt);
        // $total_shipping_charges = $total_shiping_discount=$total_product_discount=$final_total=$sub_total=0;

        $current_date = date('Y-m-d');

        /*$user = Sentinel::check();
         
        if($user)
        {
            $user_id = $user->id;
        }*/

        if ($promo_code == "") {
            $response['status']      = 'error';
            $response['description'] = 'Please enter promo code.';
            return response()->json($response);
        }

        // get  promo code details from that promo code
        $promo_code_details = $this->InfluencerPromoCodeModel
                                    ->with(['assigned_promo_code_details'])
                                    ->where('promo_code_name',$promo_code)
                                    ->where('is_active',1)
                                    ->first();

        if(isset($promo_code_details))
        {   
            $arr_promo_code = $promo_code_details->toArray();

            /*if ($arr_promo_code['vendor_id'] == $vendor_id) 
            {*/
               $promo_code_id = $arr_promo_code['id'];
               
               $expiry_date   = $arr_promo_code['assigned_promo_code_details']['expiry_date'];

               $influencer_id = isset($arr_promo_code['assigned_promo_code_details']['influencer_id'])?$arr_promo_code['assigned_promo_code_details']['influencer_id']:0;

               //get promotions from promo code id

               /*$promotion_details = $this->PromotionsModel->where('promo_code',$promo_code_id)->first();

               if(isset($promotion_details))
               {
                   $vendor_id = $promotion_details->maker_id;
                   $from_date = $promotion_details->from_date;
                   $todate    = $promotion_details->to_date;
                   $promotion_id = $promotion_details->id;
               }*/


               //get temp bag data

               // $temp_bag_data = $this->TempBagModel->where('user_id',$user_id)->first();

               // $cart_product_data  = json_decode($temp_bag_data->product_data);

                //check date of that promo code
                if($expiry_date >= $current_date)
                {

                    /*Get Admin Influencer Settings*/
                    $obj_influencer_settings = $this->InfluencerSettingModel->first();
                    // dd($obj_influencer_settings);

                    if(isset($obj_influencer_settings))
                    {
                        $arr_influencer_settings = $obj_influencer_settings->toArray();
                        $discount_percent  = isset($arr_influencer_settings['discount_on_promo_code'])?num_format($arr_influencer_settings['discount_on_promo_code']):0;

                        //check this promo code is already used or not for that logged in user
                        /*$count =0;
                        $count = $this->PromoCodeRetailerMappingModel->where('retailer_id',$user_id)->where('promo_code_id',$promo_code_id)->count();

                        if($count >0)
                        {
                           $response['status']      = 'error';
                           $response['description'] = 'This promo code is already used.';
                           return response()->json($response);
                        }*/
                        /*else
                        { */
                        // $final_total = [];  
                           //get all promotion type of that promotion
                            /*$promotion_offers_arr = $this->PromotionsOffersModel
                                                          ->with(['get_prmotion_type'])
                                                              ->where('promotion_id',$promotion_id)
                                                              ->get()
                                                              ->toArray();*/
                            

                           /*if(isset($promotion_offers_arr) && count($promotion_offers_arr)>0)
                           {*/

                               /*foreach($promotion_offers_arr as $key => $offers_types) 
                               {*/
                                   // if($total_amount >=$offers_types['minimum_ammount'])
                                   // {
                                       // $promotion_type = '';
                                       // $promotion_type = $offers_types['get_prmotion_type']['promotion_type_name'];
                                  
                                       
                                        /*if($promotion_type == '% Off')
                                        {
                                            // dd($total_amount);
                                           
                                           $discount = $offers_types['discount'];
                                           $get_discount_amt = ($discount*$total_amount/100);

                                           $final_total[$vendor_id]['total_wholesale_price'] = $total_amount-$get_discount_amt;
                                           $final_total[$vendor_id]['discount_amt'] = $get_discount_amt;
                                           $final_total[$vendor_id]['discount_percent'] = $discount;
                                           
                                        }
                                        if($promotion_type == 'Free Shipping')
                                        {
                                            
                                            $final_total[$vendor_id]['shipping_charges'] = 0;
                                        }*/

                                   //  }
                                   // else
                                   // {
                                   //     $response['status']      = 'error';
                                   //     $response['description'] = 'Promo code is not applicable for this order.';
                                   //     return response()->json($response); 
                                   // }
                               // }

    
                           /* $discount = $offers_types['discount'];
                            $get_discount_amt = ($discount*$total_amount/100);

                            $final_total[$vendor_id]['total_wholesale_price'] = $total_amount-$get_discount_amt;
                            $final_total[$vendor_id]['discount_amt'] = $get_discount_amt;
                            $final_total[$vendor_id]['discount_percent'] = $discount;*/


                            $get_discount_amt      = ($discount_percent*$subtotal_amt/100);
                            $total_wholesale_price = $subtotal_amt-$get_discount_amt;

                            $existing_promotion_data = Session::get('customer_promotion_data');

                            // dd($existing_promotion_data);

                            $existing_promotion_data = is_array($existing_promotion_data) ? $existing_promotion_data: [];

                            $existing_promotion_data['discount_amt']     = $get_discount_amt;
                            $existing_promotion_data['total_wholesale_price'] = $total_wholesale_price;
                            $existing_promotion_data['discount_percent'] = $discount_percent;
                            $existing_promotion_data['promo_code']       = $promo_code;
                            $existing_promotion_data['promo_codeId']     = $promo_code_id;
                            $existing_promotion_data['influencer_id'] = $influencer_id;

                            // dd($existing_promotion_data);

                           
                            Session::put('customer_promotion_data',$existing_promotion_data);
                            
                            $response['status']      = 'success';
                            // dd(Session::get('promotion_data'));
                            // dd(is_array(Session::get('promotion_data')));
                            return response()->json($response); 

                       // }
                    }
                    else
                    {
                        $response['status']      = 'error';
                        $response['description'] = 'Something went wrong, please contact to admin.';
                        return response()->json($response);
                    }

                   // }
                }
                else
                {
                   $response['status']      = 'error';
                   $response['description'] = 'This promo code has been expired.';
                   return response()->json($response);
                }
           
            // }
            /*else
            {

                $response['status']      = 'error';
                $response['description'] = 'Promo code is not applicable for this vendor.';
                return response()->json($response); 

            }*/
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Invalid promo code.';
            return response()->json($response);
        }
    
    }

    public function clear_promo_code(Request $request)
    {

        // $maker_id = $request['maker_id'];
        // if ($maker_id) {

            $getsessiondata = is_array(Session::get('customer_promotion_data')) ? Session::get('customer_promotion_data') : [];

            if (sizeof($getsessiondata)>0) 
            {
                // $products = session()->pull('customer_promotion_data', []); // Second argument is a default value       
                
                // dd(session()->pull('customer_promotion_data', []));

                // if(($key = array_search($getsessiondata[$maker_id], $products)) !== false) {

                   
                    // unset($getsessiondata[$key]);
                    // dd($getsessiondata);
                    // unset($getsessiondata);

                    Session::put('customer_promotion_data',[]);    


                    // dd(Session::get('customer_promotion_data'));               

                    $response['status']      = 'success';
                    $response['description'] = 'Promotion code has been removed.';
                    return response()->json($response);
                // }                
            }
            else
            {
                $response['status']      = 'error';
                $response['description'] = 'Something went wrong, please try again.';
                return response()->json($response);
            }

        // }
        // else
        // {
        //     $response['status']      = 'error';
        //     $response['description'] = 'Something went wrong, please try again.';
        //     return response()->json($response);
        // }
    }


    public function calculate_customer_shipping_discount($shipping_type,$retail_price,$minimum_amount_off,$off_type_amount,$shipping_charge)
    {
        if($shipping_type==1)
        {
          $shipping_discount = 0;
          if($retail_price < $minimum_amount_off)
          {
            $shipping_charge = $shipping_charge;

            $product_ship_charge = $shipping_charge;
          }
          else
          {
            $shipping_charge = 0;
            $product_ship_charge = 0;
          }
        }
        elseif($shipping_type==2)
        {
          if($retail_price>=$minimum_amount_off)
          {
            /*Discount = Original Price x Discount % / 100*/
           
            if(is_numeric($shipping_charge) && is_numeric($off_type_amount))
             { 
              $shipping_discount   =  $shipping_charge * $off_type_amount/ 100;
              $product_ship_charge = $shipping_charge;
             }
            else
             {
               $shipping_discount   =  0;
               $product_ship_charge = 0;
             }

        
          }
          else
          {
            $shipping_charge   = $shipping_charge;
            $shipping_discount = 0;

            $product_ship_charge = $shipping_charge;
          }
        }
        elseif($shipping_type==3)
        {
          if($retail_price>=$minimum_amount_off)
          {
            $shipping_discount   =  $off_type_amount;
            $product_ship_charge = $shipping_charge;
          }
        else
          {
            $shipping_charge     = $shipping_charge;
            $shipping_discount   = 0;
            $product_ship_charge = $shipping_charge;
          }
        }
        else
        {
            $shipping_charge     = 0;
            $shipping_discount   = 0;
            $product_ship_charge = 0;
        }

        $shipping_arr = [];
        $shipping_arr['shipping_charge']     = $shipping_charge;
        $shipping_arr['shipping_discount']   = $shipping_discount;
        
        $shipping_arr['product_ship_charge'] = $product_ship_charge;

      return $shipping_arr;
    }
}

