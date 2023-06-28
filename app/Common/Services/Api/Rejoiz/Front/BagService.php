<?php

namespace App\Common\Services\Api\Rejoiz\Front;

use App\Common\Services\GeneralService;
use App\Common\Services\MyCartService;
use App\Common\Services\HelperService;
use App\Models\MakerModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductInventoryModel;
use App\Models\ProductsModel;
use App\Models\PromoCodeModel;
use App\Models\PromoCodeRetailerMappingModel;
use App\Models\PromotionsModel;
use App\Models\PromotionsOffersModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesModel;
use App\Models\TempBagModel;
use App\Models\UserModel;
use App\Models\CategoryModel;
use Illuminate\Pagination\LengthAwarePaginator;


use DB;
use Flash;
use Illuminate\Http\Request;
use Sentinel;
use Session;
use Validator;




class BagService {

	public function __construct(ProductsModel $ProductsModel,
	TempBagModel $TempBagModel,
	GeneralService $GeneralService,
	MyCartService $MyCartService,
	HelperService $HelperService,
	MakerModel $MakerModel,
	ProductDetailsModel $ProductDetailsModel,
	RetailerQuotesModel $RetailerQuotesModel,
	ProductInventoryModel $ProductInventoryModel,
	RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
	PromotionsModel $PromotionsModel,
	PromoCodeModel $PromoCodeModel,
	PromoCodeRetailerMappingModel $PromoCodeRetailerMappingModel,
	PromotionsOffersModel $PromotionsOffersModel,
	UserModel $UserModel,
	CategoryModel $CategoryModel
	) {
		$this->arr_view_data = [];
		$this->module_title = "Bag";
		$this->module_view_folder = 'front';
		$this->ProductsModel = $ProductsModel;
		$this->PromotionsModel = $PromotionsModel;
		$this->TempBagModel = $TempBagModel;
		$this->PromoCodeModel = $PromoCodeModel;
		$this->GeneralService = $GeneralService;
		$this->MyCartService = $MyCartService;
		$this->HelperService 	= $HelperService;
		$this->MakerModel = $MakerModel;
		$this->ProductDetailsModel = $ProductDetailsModel;
		$this->PromotionsOffersModel = $PromotionsOffersModel;
		$this->RetailerQuotesModel = $RetailerQuotesModel;
		$this->ProductInventoryModel = $ProductInventoryModel;
		$this->PromoCodeRetailerMappingModel = $PromoCodeRetailerMappingModel;
		$this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
		$this->UserModel = $UserModel;
		$this->CategoryModel = $CategoryModel;

	}

   	public function add($request=null) {

       try
		{
			DB::beginTransaction();
			/*     $exist_obj = $this->TempBagModel->where('ip_address',$ip_address)
                              ->where('user_session_id',$session_id)->first();*/
		$loggedInUserId = 0;
		$loggedInStatus = false;
		$form_data      = $request->all();
		$user = isset($form_data['auth_user'])?$form_data['auth_user']:'';


		if ($user) {
			$loggedInUserId = $user->id;
			$loggedInStatus = true;
		}

			$product_id = isset($form_data['product_id'])?$form_data['product_id']:'';

			$chk_is_vendor_product = "";
			$chk_is_vendor_product = $this->ProductsModel->where('user_id', $loggedInUserId)->where('id', $product_id)->count();

			if (isset($chk_is_vendor_product) && $chk_is_vendor_product > 0) {
				$response['status']  = 'failure';
				$response['message'] = 'You cannot add your product into the bag.';
				$response['data']    = '';
				return $response;
			}

			$item_qty  = isset($form_data['item_qty'])?$form_data['item_qty']:'';
			$sku_no    = isset($form_data['sku_no'])?$form_data['sku_no']:'';
			$bag_count = $this->MyCartService->total_items();
			//get retailer ip address
			$ip_address = $request->ip();
			$session_id = session()->getId();


		

			$exist_obj = $this->MyCartService->get_items();

			$product_obj = $this->ProductsModel->where('id', $product_id)->first();
			$new_arr     = [];


			if ($product_obj) {
				$product_arr = $product_obj->toArray();

				if ((isset($product_arr['is_active']) && $product_arr['is_active'] != 1) ||
					(isset($product_arr['product_status']) && $product_arr['product_status'] != 1)) {
					$response['status']  = 'failure';
					$response['message'] = "You cannot add your product into the bag.";
					$response['data']    = '';
					return $response;
					
				}

				$new_arr['product_id'] = $product_id;
				$new_arr['item_qty'] = $item_qty;
				$new_arr['maker_id'] = $product_arr['user_id'];
				$new_arr['sku_no'] = $sku_no;

				$new_arr['retail_price'] = $product_arr['retail_price'];
				$new_arr['total_price'] = $item_qty * $product_arr['retail_price'];

				$new_arr['wholesale_price'] = $product_arr['unit_wholsale_price'];
				$new_arr['total_wholesale_price'] = $item_qty * $product_arr['unit_wholsale_price'];

				$new_arr['shipping_charges'] = isset($product_arr['shipping_charges']) ? $product_arr['shipping_charges'] : '';

				$new_arr['shipping_type'] = isset($product_arr['shipping_type']) ? $product_arr['shipping_type'] : '';

				$new_arr['minimum_amount_off'] = isset($product_arr['minimum_amount_off']) ? $product_arr['minimum_amount_off'] : '';

				$new_arr['off_type_amount'] = isset($product_arr['off_type_amount']) ? $product_arr['off_type_amount'] : '';

				$new_arr['shipping_discount'] = isset($product_arr['shipping_discount']) ? $product_arr['shipping_discount'] : '';

				$shipping_values = $this->HelperService->calculate_shipping_discount($new_arr['shipping_type'], $new_arr['total_wholesale_price'], $new_arr['minimum_amount_off'], $new_arr['off_type_amount'], $new_arr['shipping_charges']);
				//dd($shipping_values);

				$new_arr['prodduct_dis_type'] = isset($product_arr['prodduct_dis_type']) ? $product_arr['prodduct_dis_type'] : '0';

				$new_arr['product_dis_min_amt'] = isset($product_arr['product_dis_min_amt']) ? $product_arr['product_dis_min_amt'] : '0';

				$new_arr['product_discount_value'] = isset($product_arr['product_discount']) ? $product_arr['product_discount'] : '0';


				if ($new_arr['prodduct_dis_type'] != '') {
					$product_dis_amount = $this->HelperService->calculate_product_discount($new_arr['prodduct_dis_type'], $new_arr['product_dis_min_amt'], $new_arr['product_discount_value'], $new_arr['total_wholesale_price']);
				} else {
					$product_dis_amount = 0;
				}

				$new_arr['product_discount_amount'] = $product_dis_amount;

				$new_arr['shipping_discount'] = isset($shipping_values['shipping_discount']) ? $shipping_values['shipping_discount'] : 0;

				$new_arr['shipping_charges'] = isset($shipping_values['shipping_charge']) ? $shipping_values['shipping_charge'] : 0;

			}



			if ($exist_obj) {
				$exist_arr = $exist_obj->toArray();
				$json_data = [];
				$json_decoded_data = json_decode($exist_arr['product_data'], true);

				/* Update product details, if product are already available on cart */
				$data = isset($json_decoded_data['sku'][$new_arr['sku_no']]) ? $json_decoded_data['sku'][$new_arr['sku_no']] : false;

				if (isset($data) && !empty($data)) {

					$qty = $data['item_qty'] + $item_qty;

					if ($qty > 1000) {
						$response['status']  = 'failure';
						$response['message'] = 'Only 1000 product added into BAG';
						$responsep['data']   = '';
						return $response;

					}

					$retail_price = $qty * $product_arr['retail_price'];
					$whole_sale_price = $qty * $product_arr['unit_wholsale_price'];

					$new_arr['item_qty'] = $qty;
					$new_arr['total_price'] = $retail_price;
					$new_arr['total_wholesale_price'] = $whole_sale_price;

				}

				/* -------------- End --------------------- */

				$json_decoded_data['sku'][$new_arr['sku_no']] = $new_arr;

				array_unshift($json_decoded_data['sequence'], $sku_no);

				$update_arr['product_data'] = json_encode($json_decoded_data, true);

				$is_updated = $this->MyCartService->update($update_arr);

				if ($is_updated) {

					DB::commit();
					$response['status']               = 'success';
				    $arr_data['bag_count']            = $this->MyCartService->total_items();
				    $arr_data['user_loggedIn_status'] = $loggedInStatus; 

					if ($loggedInStatus == false) {
						$response['message']     = 'Product added to bag, please login as customer to proceed.';
					} else {
						$response['message']     = 'Product added to bag.';
					}
                $response['data'] = isset($arr_data)?$arr_data:[];
                return $response;

				} else {
					DB::rollback();
					$response['status']  = 'failure';
					$response['message'] = 'Something went wrong, please try again.';
					$response['data']    = '';
				}
			} else {

				//create
				$arr_cart_data = [];
				$arr_sequence  = [];
				$arr_final     = [];

				//change key product id to sku id
				$arr_cart_data[$new_arr['sku_no']] = $new_arr;
				$arr_sequence[0] = $sku_no;

				$arr_final['sku'] = $arr_cart_data;
				$arr_final['sequence'] = $arr_sequence;

				$encoded_new_arr = json_encode($arr_final);

				$insert_arr['product_data'] = $encoded_new_arr;
				$insert_arr['ip_address'] = $ip_address;
				$insert_arr['user_session_id'] = $session_id;
				$insert_arr['user_id'] = $loggedInUserId;

				$is_created = $this->TempBagModel->create($insert_arr);

				if ($is_created) {
					DB::commit();
					$response['status']    = 'success';
					$response['message']   = 'Product added to bag.';
					$arr_data['bag_count'] = $bag_count;
					$arr_data['user_loggedIn_status'] = $loggedInStatus;
					$response['data']      = isset($arr_data)?$arr_data:[];
					return $response;

				} else {
					DB::rollback();
					$response['status']      = 'failure';
					$response['message']     = 'Something went wrong, please try again.';
					$response['data']        = '';
					return $response;
				}
			}

		} catch (Exception $e) {
			DB::rollback();
			$response['status']  = 'failure';
			$response['message'] = $e->getMessage();
			$response['data']    = '';
			return $response;
		}

	}


	public function delete($request=null) {
		$bag_arr   = $update_arr = [];
		$form_data = $request->all();
		$is_update = false;
		//$product_id = base64_decode($enc_prod_id);
		//$sku_no = base64_decode($enc_prod_id);
		$sku_no   = isset($form_data['sku_no'])?$form_data['sku_no']:''; 

		$ip_address = $request->ip();
		$session_id = session()->getId();

		$loggedInUserId = 0;
		$user = isset($form_data['auth_user'])?$form_data['auth_user']:'';
		if ($user) {
			$loggedInUserId = $user->id;
		}

		/* $bag_obj    =  $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();*/

		$bag_obj = $this->get_items($loggedInUserId);

		if ($bag_obj) {
			$cart_product_count = $this->total_items($loggedInUserId);
			if ($cart_product_count == 1) {
				$is_update = $this->MyCartService->remove($loggedInUserId);
			} else {
				$bag_arr = $bag_obj->toArray();

				$product_bag_data = $bag_arr['product_data'];
				$product_bag_data = json_decode($product_bag_data, true);
				$product_sequence = $product_bag_data['sequence'];
				$product_data = $product_bag_data['sku'];

				if (isset($product_data[$sku_no])) {
					unset($product_data[$sku_no]);
				}
				// $update_arr['product_data'] = json_encode(array_values($product_data),true);
				$arr_sequence = [];
				$arr_sequence['sku'] = $product_data;
				$arr_sequence['sequence'] = $product_sequence;

				$update_arr['product_data'] = json_encode($arr_sequence, true);

				$is_update = $this->update($update_arr,$loggedInUserId);

			}
			// $is_update = $this->TempBagModel->where('user_id',$loggedInUserId)->update($update_arr);

			/*forget promotion session*/
			Session::forget('promo_shipping_charges');
			Session::forget('promotion_discount_amt');
			Session::forget('total_order_amout');
			Session::forget('promotion_data');

		}

		if ($is_update) {
				$response['status']    = 'success';
				$response['message']   = 'Product has been deleted from bag.';
				$response['data']      = '';
				return $response;
		} else {
			$response['status']      = 'failure';
			$response['message']     = 'Error occurred while deleting product from bag.';
			$response['data']        = '';
			return $response;
		}

	}


    public function get_items($user_id=null)
    {
            
    
      $user_id = isset($user_id)?$user_id:0;

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


     public  function total_items($user_id=null)
    {
      $bag_arr = [];
      $product_count = $user_id = 0;

      
      $user_id = isset($user_id)?$user_id:0;

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
        $product_count    = isset($product_data_arr['sku']) ? count($product_data_arr['sku']) : 0;
      }


      return $product_count;  
    }



    public function remove($user_id=null)
    {

      $user_id = isset($user_id)?$user_id:0;

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

    public function update($prduct_arr,$user_id=null)
    {
     
      $user_id = isset($user_id)?$user_id:0;

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


    public function empty($request=null) {
    	$form_data  = $request->all();
		$ip_addr    = $request->ip();
		$session_id = session()->getId();

		$loggedInUserId = 0;
		$user = isset($form_data['auth_user'])?$form_data['auth_user']:'';

		if ($user) {
			$loggedInUserId = $user->id;
		}

		$is_delete = $this->remove($loggedInUserId);

		// $is_delete  = $this->TempBagModel->where('user_id',$loggedInUserId)->delete();

		if ($is_delete) {
			$response['status']  = 'success';
			$response['message'] = 'All products has been deleted from cart successfully.';
			$response['data']    = '';
			return $response;
		} else {
			$response['status']  = 'failure';
			$response['message'] = 'Problem occured while deleting products from cart.';
			$response['data']    = '';
			return $response;
		}

	}


		public function my_bag($request=null) {

        try
        {
		$loggedInUserId = 0;
		$form_data = $request->all();
		$user      = isset($form_data['auth_user'])?$form_data['auth_user']:'';
	
		if ($user) {
			$loggedInUserId = $user->id;
			/*if ($user->inRole('customer') || $user->inRole('influencer')) {
				return redirect('/customer_my_bag');
			}*/
		}

		if ($loggedInUserId == 0) {
			//Session::flash('error',"Please login to access your cart.");
			Flash::error("Please login to access your bag.");

			return redirect('/login');
		}
		$bag_arr = $product_data_arr = $product_arr = $cart_product_arr = $maker_details_arr = [];
		$arr_final_data = [];
		$subtotal = 0;
		$wholesale_subtotal = 0;
		$ip_address = $request->ip();
		$session_id = session()->getId();

		// $bag_obj = $this->TempBagModel->where('ip_address',$ip_address)
		// ->where('user_session_id',$session_id)->first();

		$bag_obj = $this->get_items($loggedInUserId);
		// dd($bag_obj);
		// $bag_obj = $this->TempBagModel->where('user_id',$loggedInUserId)->first();

		/*empty card when logged in as a retailer*/
		if ($user->inRole('maker') || $user->inRole('admin') || $user->inRole('influencer')) {
			// $bag_obj  = $this->TempBagModel->where('user_session_id',$session_id)->where('user_id',0)->delete();
			$bag_obj = $this->remove($loggedInUserId);

			$response['status']  = 'failure';
			$response['message'] = 'You are not able to purchase any product, please login as  customer.';
			$response['data']    = '';
			return $response;
		}

		// dd(is_object($bag_obj));

		if (isset($bag_obj) && is_object($bag_obj)) {
			$bag_arr = $bag_obj->toArray();
			$data_arr = json_decode($bag_arr['product_data'], true);

			$product_data_arr = $data_arr['sku'];

			// $sku_ids_arr  = array_column($product_data_arr, 'sku_no');
			$sku_ids_arr = $data_arr['sequence'];

			foreach ($sku_ids_arr as $key => $sku) {

				$productData = $this->ProductDetailsModel->where('sku', $sku)
					->with('productDetails')
				/* Check product is active */
					->whereHas('productDetails', function ($q) {
						$q->where('is_active', '1');
						return $q->where('product_status', '1');
					})
				/* Check product category is active */
					->whereHas('productDetails.categoryDetails', function ($q) {
						return $q->where('is_active', '1');
					})
				/* Check product Sub-category is active */
					->whereHas('productDetails.categoryDetails.subcategory_details', function ($q) {
						return $q->orwhere('is_active', '1');
					})
				/* Check product vendor is active */
					->whereHas('productDetails.userDetails', function ($q) {
						return $q->where('is_approved', '1');
					})
					->first();

				if ($productData) {
					$sku_product_arr[] = $productData->toArray();
				}

			}

			/* get active items ids  */
			$active_items = isset($sku_product_arr) ? array_column($sku_product_arr, 'sku') : [];
			/* get deactive items ids */
			$arr_deactivated_item = array_diff($sku_ids_arr, $active_items);
			$arr_product_ids = $arr_product_names = [];

			if ($arr_deactivated_item) {
				foreach ($arr_deactivated_item as $key => $item_sku) {
					/* remove deactivated items from bag */
					unset($product_data_arr[$item_sku]);

					$arr_product_ids[] = $this->ProductDetailsModel->where('sku', $item_sku)->pluck('product_id')->first();
					
					$temp_arr['sku'] = $product_data_arr;
					$temp_arr['sequence'] = array_keys($temp_arr['sku']);
				}

				/* foreach($arr_product_ids as $product_id)
					                {
					                    $product_names          = $this->ProductsModel->where('id',$product_id)->select('product_name')->first()->toArray();
					                    $arr_product_names[]    = $product_names['product_name'];
					                }

					                  $cnt_unavailable_products = count($arr_product_ids);

					                  $tmp ='';
					                  $tmp.= '<br> The order you are trying to place, has '. $cnt_unavailable_products .' product(s) unavailable at the moment <br> Unavailable product(s) are as folllows: <br> <ul><li>'.implode('</li><li>',$arr_product_names).'</li></ul>';

				*/

				/* update temp bag data */

				$update_arr_data['product_data'] = json_encode($temp_arr);

				// $update_bag_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->update($update_arr_data);
				$update_bag_data = $this->MyCartService->update($update_arr_data);

				// $bag_obj_data = $this->TempBagModel->where('ip_address',$ip_address)->where('user_session_id',$session_id)->first();
				$bag_obj_data = $this->get_items($loggedInUserId);

				if ($bag_obj_data) {
					$bag_arr = $bag_obj_data->toArray();

					$data_arr = json_decode($bag_arr['product_data'], true);

					$product_data_arr = $data_arr['sku'];

					$sku_ids_arr = array_column($product_data_arr, 'sku_no');
				}
			}

			if (isset($sku_product_arr) && count($sku_product_arr) > 0) {

				foreach ($sku_product_arr as $key => $product) {

					$cart_product_arr[$key]['user_id'] = $product['product_details']['user_id'];
					$cart_product_arr[$key]['product_id'] = $product['product_id'];
					$cart_product_arr[$key]['product_name'] = $product['product_details']['product_name'];
					$cart_product_arr[$key]['product_image'] = $product['image'];
					$sku_no = "";

					$cart_product_arr[$key]['product_min_qty'] = $product['product_min_qty'];

					$sku_no = isset($product_data_arr[$product['sku']]['sku_no']) ? $product_data_arr[$product['sku']]['sku_no'] : '';

					/*$cart_product_arr[$key]['product_image']  = get_sku_image($sku_no);*/
					$cart_product_arr[$key]['total_price'] = isset($product_data_arr[$product['sku']]['total_price']) ? $product_data_arr[$product['sku']]['total_price'] : '';

					$cart_product_arr[$key]['shipping_type'] = isset($product['product_details']['shipping_type']) ? $product['product_details']['shipping_type'] : '';

					$cart_product_arr[$key]['minimum_amount_off'] = isset($product['product_details']['minimum_amount_off']) ? $product['product_details']['minimum_amount_off'] : '';

					$cart_product_arr[$key]['off_type_amount'] = isset($product['product_details']['off_type_amount']) ? $product['product_details']['off_type_amount'] : '';

					$cart_product_arr[$key]['product_weight'] = isset($product['weight']) ? $product['weight'] : '';

					$cart_product_arr[$key]['product_length'] = isset($product['length']) ? $product['length'] : '';

					$cart_product_arr[$key]['product_width'] = isset($product['width']) ? $product['width'] : '';

					$cart_product_arr[$key]['product_height'] = isset($product['height']) ? $product['height'] : '';

					$cart_product_arr[$key]['product_option_type'] = isset($product['option_type']) ? $product['option_type'] : 0;

					$cart_product_arr[$key]['product_option_value'] = isset($product['option']) ? $product['option'] : 0;

					$cart_product_arr[$key]['unit_retail_price'] = isset($product_data_arr[$product['sku']]['retail_price']) ? $product_data_arr[$product['sku']]['retail_price'] : 0;

					$cart_product_arr[$key]['unit_wholsale_price'] = isset($product_data_arr[$product['sku']]['unit_wholsale_price']) ? $product_data_arr[$product['sku']]['unit_wholsale_price'] : 0;

					//dd($product_data_arr[$product['sku']]);
					$cart_product_arr[$key]['total_wholesale_price'] = isset($product_data_arr[$product['sku']]['total_wholesale_price']) ? $product_data_arr[$product['sku']]['total_wholesale_price'] : 0;

					$cart_product_arr[$key]['prodduct_dis_type'] = isset($product['product_details']['prodduct_dis_type'])?$product['product_details']['prodduct_dis_type']:0;

					$cart_product_arr[$key]['product_dis_min_amt'] = isset($product['product_details']['product_dis_min_amt']) ? $product['product_details']['product_dis_min_amt'] : 0;

					$cart_product_arr[$key]['product_discount_value'] = isset($product['product_details']['product_discount']) ? $product['product_details']['product_discount'] : 0;

					//calculate product discount calculation

					if(isset($product['product_details']['prodduct_dis_type']) && $product['product_details']['prodduct_dis_type'] == 1)
					{
						if($cart_product_arr[$key]['total_wholesale_price'] >= $product['product_details']['product_dis_min_amt'])
						{
                           $product_discount_amt = $product_data_arr[$product['sku']]['total_wholesale_price']*$product['product_details']['product_discount']/100;
						}
						else
						{
						   $product_discount_amt = 0.00;
						}
						
					}
					elseif(isset($product['product_details']['prodduct_dis_type']) && $product['product_details']['prodduct_dis_type'] == 2)
					{
						if($cart_product_arr[$key]['total_wholesale_price'] >= $product['product_details']['product_dis_min_amt'])
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
					

					// $cart_product_arr[$key]['product_dis amount'] = isset($product_data_arr[$product['sku']]['product_discount_amount']) ? $product_data_arr[$product['sku']]['product_discount_amount'] : '0';


					$cart_product_arr[$key]['product_dis amount'] = isset($product_discount_amt)?$product_discount_amt:'0';




					if (isset($product['product_details']['shipping_charges']) && $product['product_details']['shipping_charges'] == 0) {
						$cart_product_arr[$key]['shipping_charges'] = 0;
					}

					if ($cart_product_arr[$key]['shipping_type'] == 2) {

						if ($cart_product_arr[$key]['total_wholesale_price'] >= $cart_product_arr[$key]['minimum_amount_off']) {

							$shipping_charges = isset($product['product_details']['shipping_charges']) ? $product['product_details']['shipping_charges'] : 0;



							if (is_numeric($shipping_charges) && is_numeric($cart_product_arr[$key]['off_type_amount'])) {

								$discount_amount = $shipping_charges * $cart_product_arr[$key]['off_type_amount'] / 100;


								//$shipping_charges = $shipping_charges - $discount_amount;

								$cart_product_arr[$key]['shipping_discount'] = $discount_amount;
								$cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
							} else {
								$cart_product_arr[$key]['shipping_discount'] = 0;
								$cart_product_arr[$key]['shipping_charges'] = 0;
							}

						} else {

							$shipping_charges = isset($product['product_details']['shipping_charges']) ? $product['product_details']['shipping_charges'] : 0;
							$cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
							$cart_product_arr[$key]['shipping_discount'] = 0;

						}
					}

					if ($cart_product_arr[$key]['shipping_type'] == 1) {
						if ($cart_product_arr[$key]['total_wholesale_price'] < $cart_product_arr[$key]['minimum_amount_off']) {
							$shipping_charges = isset($product['product_details']['shipping_charges']) ? $product['product_details']['shipping_charges'] : 0;

							$cart_product_arr[$key]['shipping_charges'] = isset($product['product_details']['shipping_charges']) ? $product['product_details']['shipping_charges'] : 0;
							// $cart_product_arr[$key]['total_wholesale_price'] = $cart_product_arr[$key]['total_wholesale_price'] + $shipping_charges;
							$cart_product_arr[$key]['shipping_discount'] = 0;
							//dd($cart_product_arr[$key]['shipping_charges']);

						} else {

							$cart_product_arr[$key]['shipping_discount'] = 0;
							$cart_product_arr[$key]['shipping_charges'] = 0;
							//dd($cart_product_arr[$key]['shipping_charges']);

						}
					}

					if ($cart_product_arr[$key]['shipping_type'] == 3) {


						if ($cart_product_arr[$key]['total_wholesale_price'] >= $cart_product_arr[$key]['minimum_amount_off'])
						{
							$shipping_charges = isset($product['product_details']['shipping_charges']) ? $product['product_details']['shipping_charges'] : 0;


							// $shipping_charges = $shipping_charges - $cart_product_arr[$key]['off_type_amount'];


							$cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
							$cart_product_arr[$key]['shipping_discount'] = $cart_product_arr[$key]['off_type_amount'];

						}
						else
						{
							$shipping_charges = isset($product['product_details']['shipping_charges']) ? $product['product_details']['shipping_charges'] : 0;
							$cart_product_arr[$key]['shipping_charges'] = $shipping_charges;
							$cart_product_arr[$key]['shipping_discount'] = 0;
						}


					}

					$cart_product_arr[$key]['wholesale_price'] = isset($product['product_details']['unit_wholsale_price']) ? $product['product_details']['unit_wholsale_price'] : 0;

					$cart_product_arr[$key]['item_qty'] = isset($product_data_arr[$product['sku']]['item_qty']) ? $product_data_arr[$product['sku']]['item_qty'] : 0;

					$cart_product_arr[$key]['sku_no'] = isset($product_data_arr[$product['sku']]['sku_no']) ? $product_data_arr[$product['sku']]['sku_no'] : 0;
					$cart_product_arr[$key]['brand_name'] = get_brand_name($product['product_details']['user_id']);
				}

				$subtotal = array_sum((array_column($cart_product_arr, 'total_price')));
				$wholesale_subtotal = array_sum((array_column($cart_product_arr, 'total_wholesale_price')));
				//dd($wholesale_subtotal);
				// dd($wholesale_subtotal-10);

			}

			$arr_prefetch_user_id = array_unique(array_column($cart_product_arr, 'user_id'));
			$arr_prefetch_user_ref = $this->MakerModel->with('shop_settings')->whereIn('user_id', $arr_prefetch_user_id)->get()->toArray();

			$arr_prefetch_user_ref = array_column($arr_prefetch_user_ref, null, 'user_id');

			$product_sequence = "";
			$arr_product_sequence = $arr_sequence = [];
			$arr_sequence = $data_arr['sequence'];

			if (isset($cart_product_arr) && sizeof($cart_product_arr) > 0) {
				foreach ($cart_product_arr as $key => $value) {

					$arr_final_data[$value['user_id']]['product_details'][$value['sku_no']] = $value;
					$arr_final_data[$value['user_id']]['maker_details'] = isset($arr_prefetch_user_ref[$value['user_id']]) ? $arr_prefetch_user_ref[$value['user_id']] : [];

					//$arr_product_sequence[$product_sequence] = $arr_final_data;
				}
			}

			/* Rearrange sequence */
			if (sizeof($arr_final_data) > 0) {
				foreach ($arr_final_data as $_key => $_data) {
					$arr_relavant_sequence = array_flip(array_intersect($arr_sequence, array_keys($_data['product_details'])));

					if (sizeof($arr_relavant_sequence) > 0) {
						foreach ($arr_relavant_sequence as $sequence_attrib => $sequence_tmp) {
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

	                     $temp_product_arr->sku->$key1->off_type_amount = isset($product_details['off_type_amount'])?$product_details['off_type_amount']:'';

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




		//developer -> priyanka
		//if product min qty is there so after login if moq value is there then calculation should be moq wise

		$temp_product_arr = isset($bag_arr['product_data']) ? json_decode($bag_arr['product_data']) : '';

		if (isset($arr_final_data) && count($arr_final_data) > 0) {
			foreach ($arr_final_data as $key => $product_data) {
				foreach ($product_data['product_details'] as $key1 => $product_details) {

					if (isset($product_details['product_min_qty']) && $product_details['product_min_qty'] != '' && isset($product_details['item_qty']) && $product_details['item_qty'] != '') {

						if ($product_details['item_qty'] < $product_details['product_min_qty']) {
							$arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] = $product_details['product_min_qty'] * $product_details['wholesale_price'];

							$arr_final_data[$key]['product_details'][$key1]['item_qty'] = $product_details['product_min_qty'];

							$temp_product_arr->sku->$key1->total_wholesale_price = $product_details['product_min_qty'] * $product_details['wholesale_price'];

							$temp_product_arr->sku->$key1->item_qty = $product_details['product_min_qty'];

							//shipping charges and discount calculation

							if (isset($product_details['shipping_charges']) && $product_details['shipping_charges'] == 0) {
								$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = 0;

								$temp_product_arr->sku->$key1->shipping_charges = 0;
							}

							if ($product_details['shipping_type'] == 2) {

								if ($arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] >= $product_details['minimum_amount_off']) {

									$shipping_charges = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									if (is_numeric($shipping_charges) && is_numeric($product_details['off_type_amount'])) {

										$discount_amount = $shipping_charges * $product_details['off_type_amount'] / 100;

										$shipping_charges = $shipping_charges - $discount_amount;

										$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = $discount_amount;
										$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = $shipping_charges;

										$temp_product_arr->sku->$key1->shipping_charges = $shipping_charges;
										$temp_product_arr->sku->$key1->shipping_discount = $discount_amount;

									} else {
										$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = 0;
										$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = 0;

										$temp_product_arr->sku->$key1->shipping_charges = 0;
										$temp_product_arr->sku->$key1->shipping_discount = 0;

									}

								} else {

									$shipping_charges = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = $shipping_charges;
									$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = 0;

									$temp_product_arr->sku->$key1->shipping_charges = $shipping_charges;
									$temp_product_arr->sku->$key1->shipping_discount = 0;

								}
							}

							if ($product_details['shipping_type'] == 1) {

								if ($arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] < $product_details['minimum_amount_off']) {
									$shipping_charges = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = 0;

									$temp_product_arr->sku->$key1->shipping_charges = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									$temp_product_arr->sku->$key1->shipping_discount = 0;

								} else {

									$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = 0;
									$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = 0;

									$temp_product_arr->sku->$key1->shipping_charges = 0;

									$temp_product_arr->sku->$key1->shipping_discount = 0;

								}
							}

							if ($product_details['shipping_type'] == 3) {
								if ($arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] >= $product_details['minimum_amount_off']) {
									$shipping_charges = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									$shipping_charges = $shipping_charges - $product_details['off_type_amount'];

									$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = $shipping_charges;

									$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = $product_details['off_type_amount'];

									$temp_product_arr->sku->$key1->shipping_charges = $shipping_charges;

									$temp_product_arr->sku->$key1->shipping_discount = $product_details['off_type_amount'];
								} else {
									$shipping_charges = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

									$arr_final_data[$key]['product_details'][$key1]['shipping_charges'] = $shipping_charges;
									$arr_final_data[$key]['product_details'][$key1]['shipping_discount'] = 0;

									$temp_product_arr->sku->$key1->shipping_charges = $shipping_charges;

									$temp_product_arr->sku->$key1->shipping_discount = 0;

								}

							}

							//calculate product discount amt

							if ($product_details['prodduct_dis_type'] == 1) {
								if ($arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] >= $product_details['product_dis_min_amt']) {
									$arr_final_data[$key]['product_details'][$key1]['product_dis amount'] = $arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] * $product_details['product_discount_value'] / 100;

									$temp_product_arr->sku->$key1->product_discount_amount = $arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] * $product_details['product_discount_value'] / 100;

								} else {
									$arr_final_data[$key]['product_details'][$key1]['product_dis amount'] = 0;

									$temp_product_arr->sku->$key1->product_discount_amount = 0;
								}

							}

							if ($product_details['prodduct_dis_type'] == 2) {

								if ($arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] >= $product_details['product_dis_min_amt']) {
									$arr_final_data[$key]['product_details'][$key1]['product_dis amount'] = $product_details['product_discount_value'];

									$temp_product_arr->sku->$key1->product_discount_amount = $product_details['product_discount_value'];
								} else {
									$arr_final_data[$key]['product_details'][$key1]['product_dis amount'] = 0;

									$temp_product_arr->sku->$key1->product_discount_amount = 0;
								}

							}

						} else {
							$arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] = $product_details['item_qty'] * $product_details['wholesale_price'];

							$arr_final_data[$key]['product_details'][$key1]['item_qty'] = $product_details['item_qty'];

							$temp_product_arr->sku->$key1->item_qty = $product_details['item_qty'];
							$temp_product_arr->sku->$key1->total_wholesale_price = $product_details['item_qty'] * $product_details['wholesale_price'];
						}

					} else {
						$arr_final_data[$key]['product_details'][$key1]['total_wholesale_price'] = $product_details['item_qty'] * $product_details['wholesale_price'];

						$arr_final_data[$key]['product_details'][$key1]['item_qty'] = $product_details['item_qty'];

						$temp_product_arr->sku->$key1->item_qty = $product_details['item_qty'];

						$temp_product_arr->sku->$key1->total_wholesale_price = $product_details['item_qty'] * $product_details['wholesale_price'];
					}
				}
			}
		}

		//then update it on temp bag table

		$bag_arr['product_data']     = json_encode($temp_product_arr);

		$update_data['product_data'] = $bag_arr['product_data'];

		//update in temp bag
		$update_cart_data = $this->TempBagModel->where('user_id', $loggedInUserId)->update($update_data);

		$bag_data['product_summary'] = $arr_prod_summary = $bag_data['pagination'] = [];



		 if(isset($arr_final_data) && !empty($arr_final_data))
         {

            foreach($arr_final_data as $key => $product_details)
             {
                 $company_names[]         = $product_details['maker_details']['company_name']; 

                    $product_summary = $product_details['product_details'];
			        $total           = count($product_summary);
					$per_page        = isset($form_data['per_page'])?$form_data['per_page']:5; // How many items do you want to display.
					$page            = isset($form_data['page'])?$form_data['page']:1; // The index page.
					$product_summary = new LengthAwarePaginator($product_summary, $total, $per_page, $page);

                    if($product_summary)
                    {	
						$product_summary                     = $product_summary->toArray();
						$bag_data['pagination']['last_page'] = $product_summary['last_page'];
						$bag_data['pagination']['total']     = $product_summary['total'];
				    }



                 if(isset($product_summary['data']) && count($product_summary['data'])>0) 
                 { 
	                  foreach($product_summary['data'] as $pro => $arr_product_data)
	                  {
	                  	 $item_qty = isset($arr_product_data['item_qty'])?$arr_product_data['item_qty']:0;
                                                
                         $unit_price = isset($arr_product_data['wholesale_price'])?$arr_product_data['wholesale_price']:0;

                         $sub_total = $item_qty*$unit_price;

	                     $arr_prod_summary['product_name']  = isset($arr_product_data['product_name'])?$arr_product_data['product_name']:'NA';
	                     $arr_prod_summary['sku_no']        = isset($arr_product_data['sku_no'])?$arr_product_data['sku_no']:'-';
	                     $arr_prod_summary['unit_price']    = isset($arr_product_data['wholesale_price'])?$arr_product_data['wholesale_price']:'-';
	                     $arr_prod_summary['item_qty']      = isset($arr_product_data['item_qty'])?$arr_product_data['item_qty']:'-';
	                     $arr_prod_summary['sub_total']     = isset($sub_total)?$sub_total:'-';

	                     $arr_prod_summary['shipping_charges']  = isset($arr_product_data['shipping_charges'])?num_format($arr_product_data['shipping_charges']):0.00;

	                     $arr_prod_summary['shipping_discount'] = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):0.00;

	                     $arr_prod_summary['product_discount_type']  = isset($arr_product_data['prodduct_dis_type'])?num_format($arr_product_data['prodduct_dis_type']):0.00;

	                     $arr_prod_summary['product_discount_value'] = isset($arr_product_data['product_discount_value'])?num_format($arr_product_data['product_discount_value']):0.00;

	                     $arr_prod_summary['shipping_type'] = isset($arr_product_data['shipping_type'])?num_format($arr_product_data['shipping_type']):0.00;

	                     $arr_prod_summary['off_type_amount'] = isset($arr_product_data['off_type_amount'])?num_format($arr_product_data['off_type_amount']):0.00;

	                     array_push($bag_data['product_summary'],$arr_prod_summary); 
	                  }
                 } 
             } 
         }



          $bag_data['product_data'] = array();

          $company_names       = array_unique($company_names);

          if(isset($company_names) && !empty($company_names))
          { 
             foreach($company_names as $comp_name)
             {  
                 $temp_data = array();
                 $temp_data['company_name'] = $comp_name;
                // $temp_data['minimum_order_amount'] = '';
                 $temp_data['products']     =  array();
                foreach($arr_final_data as $key => $product_details)
                {
                   if($comp_name==$product_details['maker_details']['company_name'])
                  {  

                  	$temp_data['first_order_minimum'] = isset($product_details['maker_details']['shop_settings']['first_order_minimum'])?$product_details['maker_details']['shop_settings']['first_order_minimum']:'No Minimum Limit';
                

                   if(is_array(Session::get('promotion_data')) && Session::get('promotion_data') != ""){

                    $session_promotion_data = Session::get('promotion_data');
                    
                    $promo_shipping_charges = isset($session_promotion_data[$key]['final_total'][$key]['shipping_charges'])?$session_promotion_data[$key]['final_total'][$key]['shipping_charges']:1;

                    $total_wholesale_price = isset($session_promotion_data[$key]['final_total'][$key]['total_wholesale_price'])?$session_promotion_data[$key]['final_total'][$key]['total_wholesale_price']:0;

                    $promotion_discount_amt = isset($session_promotion_data[$key]['final_total'][$key]['discount_amt'])?$session_promotion_data[$key]['final_total'][$key]['discount_amt']:0;
                    $promotion_discount_percent = isset($session_promotion_data[$key]['final_total'][$key]['discount_percent'])?$session_promotion_data[$key]['final_total'][$key]['discount_percent']:0;

                    $total_promotion_discount_amt += $promotion_discount_amt;

                  }

                  if(isset($session_promotion_data[$key]['final_total'][$key]['shipping_charges']))
                  {
                  	$temp_data['shipping_type'] = 'Free Shipping';
                  }

                  if(isset($session_promotion_data[$key]['final_total'][$key]['total_wholesale_price']) && isset($total_wholesale_price))
                  {

                  	$temp_data['promotion_discount'] = "(".$promotion_discount_percent ."%):". "$".num_format($promotion_discount_amt);

                  }

                  if(isset($product_details['product_details']) && count($product_details['product_details']>0))
                  { 
                    foreach($product_details['product_details'] as $details)
                    {
                    	$arr['product_image'] = imagePath($details['product_image'],'product',0);
                    	$arr['product_name']  = isset($details['product_name'])?ucfirst($details['product_name']):'';
                    	$arr['sku_no']        = isset($details['sku_no'])?$details['sku_no']:'';
                    	$arr['unit_price']    = isset($details['wholesale_price'])?num_format($details['wholesale_price']) :'';
                    	$arr['sub_total']     = $details['wholesale_price']*$details['item_qty'];
                    	$arr['product_dis_min_amt'] = isset($details['product_dis_min_amt'])?num_format($details['product_dis_min_amt']):'';
                    	$arr['product_discount_value'] = isset($details['product_discount_value'])?num_format($details['product_discount_value']):'';
                        $arr['product_discount_amount'] = isset($details['product_dis amount'])?num_format($details['product_dis amount']):0.00;
                        $arr['shipping_type']      = isset($details['shipping_type'])?$details['shipping_type']:'';
                        $arr['off_type_amount']    = isset($details['off_type_amount'])?num_format($details['off_type_amount']):0.00;
                        $arr['minimum_amount_off'] = isset($details['minimum_amount_off'])?num_format($details['minimum_amount_off']):0.00;
                        $arr['shipping_charges']   = isset($details['shipping_charges'])?num_format($details['shipping_charges']):0.00;
                        $arr['shipping_discount']  = isset($details['shipping_discount'])?num_format($details['shipping_discount']):0.00;
                        $arr['qty']                = isset($details['item_qty'])?num_format($details['item_qty']):0.00;
                        $arr['prod_dis amount']    = isset($details['product_dis amount'])? $details['product_dis amount']: 0;
                        $arr['total_wholesale_price'] =  isset($details['total_wholesale_price'])? $details['total_wholesale_price']: 0;

                         array_push($temp_data['products'],$arr);  

                    }
                  }

                   array_push($bag_data['product_data'],$temp_data);                   
                }  	
             }  
          }   
                     
          }    

          $response['status']  = 'success';
          $response['message'] = 'Bag data get successfully.';  
          $response['data']    = isset($bag_data)?$bag_data:[];
          return $response;     

       }  
       
       catch(Exception $e)
       {
       	  $response['status']  = 'failure';
          $response['message'] = $e->message();  
          $response['data']    = '';
          return $response;     
       } 

		/*------------------------------------------------------------------------------------------------*/

		// $this->arr_view_data['is_reorder']      = isset($bag_arr['is_reorder'])?$bag_arr['is_reorder']:0;
/*		$this->arr_view_data['is_reorder'] = 0;
		$this->arr_view_data['arr_final_data'] = $arr_final_data;
		$this->arr_view_data['product_data'] = isset($bag_arr['product_data']) ? $bag_arr['product_data'] : [];
		$this->arr_view_data['bag_id'] = isset($bag_arr['id']) ? $bag_arr['id'] : 0;
		$this->arr_view_data['subtotal'] = $subtotal;
		$this->arr_view_data['wholesale_subtotal'] = isset($wholesale_subtotal) ? $wholesale_subtotal : "0";
		$this->arr_view_data['wholesale_total'] = isset($wholesale_subtotal) ? $wholesale_subtotal : "0";
		$this->arr_view_data['shipping_charges'] = 0;
		$this->arr_view_data['shipping_discount'] = 0;
		$this->arr_view_data['product_discount'] = 0;
		$this->arr_view_data['cart_product_arr'] = $cart_product_arr;
		$this->arr_view_data['module_title'] = $this->module_title;
		$this->arr_view_data['page_title'] = 'My ' . $this->module_title;
		//dd($this->arr_view_data);
		return view($this->module_view_folder . '.my_bag', $this->arr_view_data);*/
	}

    private function get_current_ip(){
      return \Request::ip();
    } 

    private function get_session_id(){
      return session()->getId();
    } 


    public function update_qty($request=null) {
		$response = [];
		$formData = [];
		$formData = $request->all();
		$bag_arr   = $update_arr = $product_data = [];
		$is_update = false;

		$product_id = isset($formData['product_id']) ? $formData['product_id'] : "";
		$sku_no     = isset($formData['sku_no']) ? $formData['sku_no'] : "";
		$update_qty = isset($formData['qty']) ? $formData['qty'] : "";

		$ip_address = $request->ip();
		$session_id = session()->getId();

		$loggedInUserId = 0;
		$user = isset($formData['auth_user'])?$formData['auth_user']:'';
		if ($user) {
			$loggedInUserId = $user->id;
		}


		$bag_obj = $this->get_items($loggedInUserId);

		if ($bag_obj) {
			$bag_arr = $bag_obj->toArray();

			$product_bag_data = json_decode($bag_arr['product_data'], true);
			$product_data     = $product_bag_data['sku'];
			$product_sequence = $product_bag_data['sequence'];

			if (isset($product_data) && sizeof($product_data) > 0) {
				foreach ($product_data as $key => $product) {
					if ($sku_no == $key) {
						$subtotal = 0;
						unset($product_data[$key]);

						$product_details = get_product_details($product_id);

						$retail_price = isset($product_details['retail_price']) ? $product_details['retail_price'] : "";
						$wholesale_price = isset($product_details['unit_wholsale_price']) ? $product_details['unit_wholsale_price'] : "";

						$new_arr['product_id']  = $product['product_id'];
						$new_arr['item_qty']    = $update_qty;
						$new_arr['total_price'] = $update_qty * $retail_price;
						$new_arr['total_wholesale_price'] = $update_qty * $wholesale_price;

						$new_arr['retail_price']    = $product['retail_price'];
						$new_arr['wholesale_price'] = $product['wholesale_price'];

						$new_arr['maker_id']        = $product['maker_id'];
						$new_arr['sku_no']          = isset($product['sku_no']) ? $product['sku_no'] : "";

						$new_arr['shipping_type']   = isset($product_details['shipping_type']) ? $product_details['shipping_type'] : 0;

						$new_arr['minimum_amount_off'] = isset($product_details['minimum_amount_off']) ? $product_details['minimum_amount_off'] : 0;

						$new_arr['off_type_amount'] = isset($product_details['off_type_amount']) ? $product_details['off_type_amount'] : 0;

						$new_arr['shipping_charges'] = isset($product_details['shipping_charges']) ? $product_details['shipping_charges'] : 0;

						$new_arr['shipping_discount'] = isset($product['shipping_discount']) ? $product['shipping_discount'] : 0;
						//dd($product);

						$shipping_arr = $this->HelperService->calculate_shipping_discount($new_arr['shipping_type'], $new_arr['total_wholesale_price'], $new_arr['minimum_amount_off'], $new_arr['off_type_amount'], $new_arr['shipping_charges']);

						$new_arr['prodduct_dis_type'] = isset($product['prodduct_dis_type']) ? $product['prodduct_dis_type'] : 0;

						$new_arr['product_dis_min_amt'] = isset($product['product_dis_min_amt']) ? $product['product_dis_min_amt'] : 0;

						$new_arr['product_discount_value'] = isset($product['product_discount_value']) ? $product['product_discount_value'] : '';

						if ($new_arr['prodduct_dis_type'] != '') {
							$product_dis_amount = $this->HelperService->calculate_product_discount($new_arr['prodduct_dis_type'], $new_arr['product_dis_min_amt'], $new_arr['product_discount_value'], $new_arr['total_wholesale_price']);
							//dd($product_dis_amount);
						} else {
							$product_dis_amount = 0;
						}
						$new_arr['product_discount_amount'] = $product_dis_amount;

						$new_arr['shipping_charges'] = isset($shipping_arr['shipping_charge']) ? $shipping_arr['shipping_charge'] : 0;
						$new_arr['shipping_discount'] = isset($shipping_arr['shipping_discount']) ? $shipping_arr['shipping_discount'] : 0;

						$product_data[$sku_no] = $new_arr;

						//----
						$subtotal = array_sum((array_column($product_data, 'total_price')));

						$wholesale_subtotal = array_sum((array_column($product_data, 'total_wholesale_price')));

						$shipping_charges = array_sum((array_column($product_data, 'shipping_charges')));
						$shipping_discount = array_sum((array_column($product_data, 'shipping_discount')));
						// dd($wholesale_subtotal+);
						$wholesale_subtotal = $wholesale_subtotal + $shipping_charges - $shipping_discount;
						//dd($wholesale_subtotal);
						session::put('amount', $wholesale_subtotal);
						session::put('total_amount', $subtotal);
						session::put('shipping_charges', $shipping_charges);

						$arr_sequence = [];
						$arr_sequence['sku'] = $product_data;
						$arr_sequence['sequence'] = $product_sequence;

						$update_arr['product_data'] = json_encode($arr_sequence, true);

						$session_data = json_encode($product_data, true);
						Session::forget('bag_data');
                        Session::forget('promotion_data');


						/*forget promotion session*/
						/*Session::forget('promo_shipping_charges');
							                        Session::forget('promotion_discount_amt');
							                        Session::forget('total_order_amout');
						*/

						$is_update = Session::put('bag_data', $session_data);

						$is_update = $this->update($update_arr,$loggedInUserId);

						// $is_update = $this->TempBagModel->where('user_id',$loggedInUserId)->update($update_arr);

						if ($is_update) {

							$arr_data['maker_id'] = isset($product['maker_id']) ? $product['maker_id'] : "";
							$arr_data['subtotal'] = $subtotal;
							$arr_data['wholesale_subtotal'] = $wholesale_subtotal;
							$arr_data['total_price'] = isset($new_arr['total_price']) ? $new_arr['total_price'] : "";
							$arr_data['total_wholesale_price'] = isset($new_arr['total_wholesale_price']) ? $new_arr['total_wholesale_price'] : "";

							$response['status']   = 'success';
							$response['message']  = 'Quantity updated successfully.';
							$response['data']     = isset($arr_data)?$arr_data:[];

							return $response;
						}

					}
				}
			}
		} else {
			$response['status']  = 'failure';
			$response['message'] = 'Something went wrong.';
			$response['data']    = '';
			return $response;
		}

	}


	 public function check_cart_data_while_login($loggedInUserId=null)
    {
      $user_id = isset($loggedInUserId)?$loggedInUserId:0;

     
      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      $arr_criteria = [
        'user_id'           => 0,
        'user_session_id'   => $session_id

      ];

      $bag_obj = $this->TempBagModel->where($arr_criteria)->orderBy('id','desc')->first();
      
      return $bag_obj; 

    }


    public function transfer_session_data_while_login($session_bag_arr,$user_id=null)
    {
     
      $user_id = isset($user_id)?$user_id:0;


      $ip_address = $this->get_current_ip();
      $session_id = $this->get_session_id();

      if($user_id!=0)
      {
     
        $obj_user_cart_data = $this->get_items($user_id);


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


    public  function get_cart_count($user_id=null)
    {
      try{	
		      $arr_data      = [];
		      $product_count = 0;

		      
		      $user_id = isset($user_id)?$user_id:0;

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
		        $product_data_arr         = json_decode($bag_obj->product_data,true);
		        $arr_data['cart_count']   = isset($product_data_arr['sku']) ? count($product_data_arr['sku']) : 0;
		      }


		      $response['status']  = 'success';
		      $response['message'] = 'Cart count get successfully.';
		      $response['data']    =  isset($arr_data['cart_count'])?$arr_data['cart_count']:[];

		      return $response;  
         }

         catch(Exception $e)
        {
       	  $response['status']  = 'failure';
          $response['message'] = $e->message();  
          $response['data']    = '';

          return $response;     
        }   
    }

}

?>