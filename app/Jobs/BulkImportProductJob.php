<?php

namespace App\Jobs;

use App\Common\Services\ElasticSearchService;
use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\ProductInventoryModel;
use App\Models\ProductsModel;
use App\Models\ProductsSubCategoriesModel;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkImportProductJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	protected $arr_config = [];
	protected $ElasticSearchService;

	public function __construct($arr_config = [], ElasticSearchService $ElasticSearchService) {
		$this->arr_config = $arr_config;
		$this->ElasticSearchService = $ElasticSearchService;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {

		set_time_limit(0);

		$arr_data = $this->arr_config['arr_data'];
		if (sizeof($arr_data) <= 0) {
			dump("Cannot Process Empty Product List");
			return false;
		}


		$arr_already_exists_sku = $this->arr_config['arr_already_exists_sku'];
		$arr_product_sku_mapping = $this->arr_config['arr_product_sku_mapping'];
		$arr_category_mapping = $this->arr_config['arr_category_mapping'];
		$arr_subcategory_mapping = $this->arr_config['arr_subcategory_mapping'];
		$arr_subcategory_category_mapping = $this->arr_config['arr_subcategory_category_mapping'];

		$arr_third_category_mapping = $this->arr_config['arr_thirdsubcategory_mapping'];
		$arr_thirdsubcategory_category_mapping = $this->arr_config['arr_thirdsubcategory_category_mapping'];

		$arr_fourth_category_mapping = $this->arr_config['arr_fourthsubcategory_mapping'];
		$arr_fourthsubcategory_category_mapping = $this->arr_config['arr_fourthsubcategory_category_mapping'];
		 //dd($arr_fourth_category_mapping);
		$arr_brand_mapping = $this->arr_config['arr_brand_mapping'];

		$override_params = $this->arr_config['override_params'];
		$user_id = $this->arr_config['user_id'];

		

		//flags
		$product_name_flag = 0;
		$brand_flag = 0;
		$case_qty_flag = 0;
		$unit_wholsale_price_flag = 0;
		$unit_retail_price_flag = 0;
		$sku_flag = 0;
		$available_qty_flag = 0;
		$category_flag = 0;
		$shipping_charges_flag = 0;
		$shipping_dis_type_flag = 0;
		$product_min_qty_flag = 0;

		try
		{
			DB::beginTransaction();
			$cat_sub_cat_mapping_arr = [];

			foreach ($arr_data as $key => $value) {



				// --------------check all column empty or not--------------------
				
				//only check required fields


				if (isset($value['product_name']) && $value['product_name'] == null && $value['product_name'] == '') {
					$product_name_flag = 1;
					//continue;

				}

				if (isset($value['category']) && $value['category'] == null && $value['category'] == '') {
					$category_flag = 1;
					//continue;
				}

				if (isset($value['restock_days']) && $value['restock_days'] == null && $value['restock_days'] == '') {
					$restock_flag = 1;
					//continue;
				}

				if (isset($value['brand']) && $value['brand'] == null && $value['brand'] == '') {
				}

				if (isset($value['case_quantity']) && $value['case_quantity'] == null && $value['case_quantity'] == '') {
					$case_qty_flag = 1;
					//continue;
				}

				if (isset($value['unit_price']) && $value['unit_price'] == null && $value['unit_price'] == '') {
					$unit_wholsale_price_flag = 1;
					//continue;
				}

				if (isset($value['sku']) && $value['sku'] == null && $value['sku'] == '') {
					$sku_flag = 1;
					//continue;

				}

				if (isset($value['available_qty']) && $value['available_qty'] == null && $value['available_qty'] == '') {
					$available_qty_flag = 1;
					//continue;
				}

				if (isset($value['shipping_charges']) && $value['shipping_charges'] == null && $value['shipping_charges'] == '') {
					$shipping_charges_flag = 1;
					//continue;
				}

				if (isset($value['discount_type_on_shipping']) && $value['discount_type_on_shipping'] == null && $value['discount_type_on_shipping'] == '') {
					$shipping_dis_type_flag = 1;
					//continue;
				}

				if (isset($value['product_min_qty']) && $value['product_min_qty'] == null && $value['product_min_qty'] == '') {
					$product_min_qty_flag = 1;
				}

				//To Handle SKU parsed as number into String

				$value['sku'] = is_numeric($value['sku']) ? intval($value['sku']) : $value['sku'];
				$value['sku'] = "" . $value['sku'] . "";

				if (in_array($value['sku'], $arr_already_exists_sku)) {
					continue;
				}

				/*$category_id = 0;
					                                                    $category_id = $this->CategoryTranslationModel->where('category_name',$value['category'])
					                                                                                                  ->pluck('category_id')
				*/

				$category_id = isset($arr_category_mapping[strtoupper($value['category'])]) ? $arr_category_mapping[strtoupper($value['category'])] : 0;
				// $subcategory_id = isset($arr_subcategory_mapping[strtoupper($value['subcategory'])]) ? $arr_subcategory_mapping[strtoupper($value['subcategory'])] : 0;

				// $category_id = isset($arr_category_mapping[strtoupper($value['category'])]) ? $arr_category_mapping[strtoupper($value['category'])] : 0;

				$get_subcategory_id = strtoupper($value['subcategory']).'###'.$category_id;
				
				
				$subcategory_id = isset($arr_subcategory_mapping[$get_subcategory_id]) ? $arr_subcategory_mapping[$get_subcategory_id] : 0;

				$get_thirdcategory_id = strtoupper($value['third_level_categories']).'###'.$subcategory_id;

				$third_category_id = isset($arr_third_category_mapping[$get_thirdcategory_id]) ? $arr_third_category_mapping[$get_thirdcategory_id] : 0;

				$get_fourthcategory_id = strtoupper($value['fourth_level_categories']).'###'.$third_category_id;

				$fourth_category_id = isset($arr_fourth_category_mapping[$get_fourthcategory_id]) ? $arr_fourth_category_mapping[$get_fourthcategory_id] : 0;

				 // dd($fourth_category_id);

				$is_option_type = isset($value['option_type']) ? gettype($value['option_type']) : '';

				$is_shipping_type = isset($value['discount_type_on_shipping']) ? gettype($value['discount_type_on_shipping']) : '';

				$is_product_discount_type = isset($value['product_discount_type']) ? gettype($value['product_discount_type']) : '';

				/*  if($is_option_type=="NULL")
						                {
						                    Flash::error('Option type field is required.');
						                    return redirect()->back();
					                    }
				*/

				//check available quantity data

				/*  if($is_shipping_type=="NULL")
					{
					    Flash::error('Discount type on shipping field is required.');
					    return redirect()->back();
					}
				*/
				if (isset($value['discount_type_on_shipping']) && $value['discount_type_on_shipping'] == 'Free Shipping') {
					$shipping_type = 1;
				} elseif (isset($value['discount_type_on_shipping']) && $value['discount_type_on_shipping'] == '% Off') {
					$shipping_type = 2;
				} elseif (isset($value['discount_type_on_shipping']) && $value['discount_type_on_shipping'] == '$ Off') {
					$shipping_type = 3;
				} else {
					$shipping_type = 0;
				}

				if (isset($value['option_type']) && $value['option_type'] == "color") {
					$option_type = 0;
				} elseif (isset($value['option_type']) && $value['option_type'] == "scent") {
					$option_type = 1;
				} elseif (isset($value['option_type']) && $value['option_type'] == "size") {
					$option_type = 2;
				} elseif (isset($value['option_type']) && $value['option_type'] == "material") {
					$option_type = 3;
				} else {
					$option_type = 0;
				}

				if (isset($value['product_discount_type']) && $value['product_discount_type'] == '% Off') {
					$product_discount_type = 1;

				} elseif (isset($value['product_discount_type']) && $value['product_discount_type'] == '$ Off') {
					$product_discount_type = 2;

				} else {
					$product_discount_type = 0;
				}

				if (isset($value['brand']) && $value['brand'] != "") {
					/*$brand_id = isset($value['brand'])?get_brand_id($value['brand']):'';   */
					$brand_id = isset($arr_brand_mapping[strtoupper($value['brand'])]) ? $arr_brand_mapping[strtoupper($value['brand'])] : 0;
				} else {
					$brand_id = $override_params['brand_id'] !== false ? $override_params['brand_id'] : '';
				}

				$product_arr = [
					'user_id' => isset($user_id) ? $user_id : 0,

					'category_id' => isset($category_id) ? $category_id : 0,

					'product_name' => isset($value['product_name']) && $value['product_name'] != null ? $value['product_name'] : '',

					'restock_days' => isset($value['restock_days']) && $value['restock_days'] != null ? $value['restock_days'] : '',

					/*'brand'       => $override_params['brand_id'] !== false ? $override_params['brand_id'] : $brand_id ,//isset($brand_id)?$brand_id:0,*/

					'brand' => isset($brand_id) ? $brand_id : '',

					'description' => isset($value['description']) && $value['description'] != null ? $value['description'] : '',

					'ingrediants' => isset($value['product_ingrediants']) && $value['product_ingrediants'] != null ? $value['product_ingrediants'] : '',

					'case_quantity' => isset($value['case_quantity']) && $value['case_quantity'] != null ? $value['case_quantity'] : '',

					'unit_wholsale_price' => isset($value['unit_price']) && $value['unit_price'] != null ? $value['unit_price'] : 0.00,

					'retail_price' => isset($value['retailer_price']) && $value['retailer_price'] != null ? $value['retailer_price'] : 0.00,

					'available_qty' => isset($value['available_qty']) && $value['available_qty'] != null ? $value['available_qty'] : 0,

					'sku_product_description' => isset($value['sku_description']) && $value['sku_description'] != null ? $value['sku_description'] : 0,

					'shipping_charges' => isset($value['shipping_charges']) && $value['shipping_charges'] != null ? $value['shipping_charges'] : 0.00,

					'shipping_type' => isset($shipping_type) ? $shipping_type : '',

					'minimum_amount_off' => isset($value['min_order_amount_for_shipping']) && $value['min_order_amount_for_shipping'] != null ? $value['min_order_amount_for_shipping'] : 0.00,

					'off_type_amount' => isset($value['off_discount_on_shipping']) && $value['off_discount_on_shipping'] != null ? $value['off_discount_on_shipping'] : 0.00,

					'prodduct_dis_type' => isset($product_discount_type) && $product_discount_type != null ? $product_discount_type : '',

					'product_dis_min_amt' => isset($value['min_order_amount_to_get_product_discount']) && $value['min_order_amount_to_get_product_discount'] != null ? $value['min_order_amount_to_get_product_discount'] : 0.00,

					'product_discount' => isset($value['off_discount_on_product']) && $value['off_discount_on_product'] != null ? $value['off_discount_on_product'] : 0.00,

					'is_active' => '2',
				];
				
				if (isset($product_arr['unit_wholsale_price']) && $product_arr['unit_wholsale_price'] != "") {
					$unit_wholsale_price = isset($product_arr['unit_wholsale_price']) ? is_numeric($product_arr['unit_wholsale_price']) : false;

					if ($unit_wholsale_price == false) {
						$unit_wholsale_price = 0;
					}
				}

				if (isset($product_arr['restock_days']) && $product_arr['restock_days'] != "") {
					$restock_days = isset($product_arr['restock_days']) ? is_numeric($product_arr['restock_days']) : false;

					if ($restock_days == false) {
						$restock_days = 0;
					}
				}

				if (isset($product_arr['retailer_price']) && $product_arr['retailer_price'] != "") {
					$retailer_price = isset($product_arr['retailer_price']) ? is_numeric($product_arr['retailer_price']) : false;

					if ($retailer_price == false) {
						$retailer_price = 0;
					}
				}

				if (isset($product_arr['available_qty']) && $product_arr['available_qty'] != "") {
					$available_qty = isset($product_arr['available_qty']) ? is_numeric($product_arr['available_qty']) : false;

					if ($available_qty == false) {
						Flash::error('Product with name ' . $product_arr['product_name'] . ' has invalid quantity');
						return redirect()->back();
					}

				}

				if (isset($product_arr['case_quantity']) && $product_arr['case_quantity'] != "") {
					$case_quantity = isset($product_arr['case_quantity']) ? is_numeric($product_arr['case_quantity']) : false;

					if ($case_quantity == false) {
						Flash::error('Product with name ' . $product_arr['product_name'] . ' has invalid case quantity');
						return redirect()->back();
					}
				}

				if (isset($product_arr['shipping_charges']) && $product_arr['shipping_charges'] != "") {
					$shipping_charges = isset($product_arr['shipping_charges']) ? is_numeric($product_arr['shipping_charges']) : false;

					if ($shipping_charges == false) {
						$shipping_charges = 0;
					}
				}

				if (isset($product_arr['minimum_amount_off']) && $product_arr['minimum_amount_off'] != "") {
					$minimum_amount_off = isset($product_arr['minimum_amount_off']) ? is_numeric($product_arr['minimum_amount_off']) : false;

					if ($minimum_amount_off == false) {
						$minimum_amount_off = 0;
					}
				}

				if (isset($product_arr['off_type_amount']) && $product_arr['off_type_amount'] != "") {
					$off_type_amount = isset($product_arr['off_type_amount']) ? is_numeric($product_arr['off_type_amount']) : false;

					if ($off_type_amount == false) {
						$off_type_amount = 0;
					}
				}

				$product_dis_min_amt = isset($product_arr['product_dis_min_amt']) ? is_numeric($product_arr['product_dis_min_amt']) : false;

				if ($product_dis_min_amt == false) {
					$product_dis_min_amt = 0;
				}

				$product_discount = isset($product_arr['product_discount']) ? is_numeric($product_arr['product_discount']) : false;

				if ($product_discount == false) {
					$product_discount = 0;
				}

				/*if (isset($product_arr) && empty($product_arr)) {
					Flash::error('Fill all the fields in csv.');
					return redirect()->back();
				}*/

				// PLEASE UNCOMMENT After Meeting 14-12-2019 is DONE
				$duplicate_products = [];
				$product_name_count = 0;
				$tmp_product_sku_slug = strtoupper($value['product_name']) . "::" . $value['sku'];

				if (isset($value['product_name']) && $value['product_name'] != '' && isset($value['sku']) && $value['sku'] != '') {

					/*
						                        $product_name_count = isset($value['product_name'])?get_product_name_count($value['product_name'],$value['sku']):'';
						                        if($product_name_count>0)
						                        {
						                            $duplicate_products[] = $value['product_name'];
						                            continue;
						                        }

					*/

					if (isset($arr_product_sku_mapping[$tmp_product_sku_slug])) {
						$duplicate_products[] = $value['product_name'];
						continue;
					}
				}

				/*try
					                                                  {
				*/
				/*if product name duplicate then insert into product detail table*/

				$finalized_product_id = 0;

				if (isset($arr_product_sku_mapping[$tmp_product_sku_slug])) {
					// $duplicate_products[] = $value['product_name'];
					// $product_id = $this->BaseModel->where('product_name',$value['product_name'])->pluck('id')->first();

					$product_id = $arr_product_sku_mapping[$tmp_product_sku_slug];
					$finalized_product_id = $product_id;

					/* Product SKU Details */

					$value['option_value'] = isset($value['option_value']) ? $value['option_value'] : '';
					$value['weight'] = isset($value['weight']) ? $value['weight'] : '';
					$value['length'] = isset($value['length']) ? $value['length'] : '';
					$value['width'] = isset($value['width']) ? $value['width'] : '';
					$value['height'] = isset($value['height']) ? $value['height'] : '';

					$value['shipping_weight'] = isset($value['shipping_weight']) ? $value['shipping_weight'] : '';
					$value['shipping_length'] = isset($value['shipping_length']) ? $value['shipping_length'] : '';
					$value['shipping_width'] = isset($value['shipping_width']) ? $value['shipping_width'] : '';
					$value['shipping_height'] = isset($value['shipping_height']) ? $value['shipping_height'] : '';

					/*Default producy_min_qty set as 1*/
					$product_min_qty = 1;
					if(isset($value['product_min_qty']) && $value['product_min_qty'] != null && $value['product_min_qty'] != "" && $value['product_min_qty']!=0){
						$product_min_qty = $value['product_min_qty'];
					}

					$product_details_arr = [
						'option_type' => $override_params['option_type'] !== false ? $override_params['option_type'] : $option_type,
						'product_id' => $product_id,
						'option' => $override_params['option_value'] !== false ? $override_params['option_value'] : $value['option_value'],
						'sku' => isset($value['sku']) ? $value['sku'] : '',
						'weight' => $override_params['weight'] !== false ? $override_params['weight'] : $value['weight'],
						'length' => $override_params['length'] !== false ? $override_params['length'] : $value['length'],
						'width' => $override_params['width'] !== false ? $override_params['width'] : $value['width'],
						'height' => $override_params['height'] !== false ? $override_params['height'] : $value['height'],

						'shipping_weight' => $override_params['shipping_weight'] !== false ? $override_params['shipping_weight'] : $value['shipping_weight'],
						'shipping_length' => $override_params['shipping_length'] !== false ? $override_params['shipping_length'] : $value['shipping_length'],
						'shipping_width' => $override_params['shipping_width'] !== false ? $override_params['shipping_width'] : $value['shipping_width'],
						'shipping_height' => $override_params['shipping_height'] !== false ? $override_params['shipping_height'] : $value['shipping_height'],

						'sku_product_description' => isset($value['sku_description']) ? $value['sku_description'] : '',
						'product_min_qty' => isset($product_min_qty) ? $product_min_qty : 1
					];

					//echo "<pre>";print_r($product_details_arr);die;
					$product_details_arr['image'] = 'product_image/default-product.png';
					$product_details_arr['image_thumb'] = 'product_image/default-product.png';

					$is_new_product_store = ProductDetailsModel::create($product_details_arr);

					$sku_no = $value['sku'];
					$quantity = $override_params['available_qty'] !== false ? $override_params['available_qty'] : $value['available_qty'];

					$product_inventory_arr =
						[
						'product_id' => $product_id,
						'sku_no' => $sku_no,
						'quantity' => $quantity,
						'user_id' => $user_id,
					];

				} else {

					$product_res = ProductsModel::create($product_arr);
					$finalized_product_id = $product_res->id;
					// $pro_model= $this->BaseModel::latest()->first();

					/* Product SKU Details */

					$value['option_value'] = isset($value['option_value']) ? $value['option_value'] : '';
					$value['weight'] = isset($value['weight']) ? $value['weight'] : '';
					$value['length'] = isset($value['length']) ? $value['length'] : '';
					$value['width'] = isset($value['width']) ? $value['width'] : '';
					$value['height'] = isset($value['height']) ? $value['height'] : '';
					$value['shipping_weight'] = isset($value['shipping_weight']) ? $value['shipping_weight'] : '';
					$value['shipping_length'] = isset($value['shipping_length']) ? $value['shipping_length'] : '';
					$value['shipping_width'] = isset($value['shipping_width']) ? $value['shipping_width'] : '';
					$value['shipping_height'] = isset($value['shipping_height']) ? $value['shipping_height'] : '';

					/*Default producy_min_qty set as 1*/
					$product_min_qty = 1;
					if(isset($value['product_min_qty']) && $value['product_min_qty'] != null && $value['product_min_qty'] != "" && $value['product_min_qty']!=0){
						$product_min_qty = $value['product_min_qty'];
					}

					$product_details_arr = [
						'option_type' => $override_params['option_type'] !== false ? $override_params['option_type'] : $option_type,
						'product_id' => $product_res->id,
						'option' => $override_params['option_value'] !== false ? $override_params['option_value'] : $value['option_value'],
						'sku' => isset($value['sku']) ? $value['sku'] : '',
						'weight' => $override_params['weight'] !== false ? $override_params['weight'] : $value['weight'],
						'length' => $override_params['length'] !== false ? $override_params['length'] : $value['length'],
						'width' => $override_params['width'] !== false ? $override_params['width'] : $value['width'],
						'height' => $override_params['height'] !== false ? $override_params['height'] : $value['height'],

						'shipping_weight' => $override_params['shipping_weight'] !== false ? $override_params['shipping_weight'] : $value['shipping_weight'],
						'shipping_length' => $override_params['shipping_length'] !== false ? $override_params['shipping_length'] : $value['shipping_length'],
						'shipping_width' => $override_params['shipping_width'] !== false ? $override_params['shipping_width'] : $value['shipping_width'],
						'shipping_height' => $override_params['shipping_height'] !== false ? $override_params['shipping_height'] : $value['shipping_height'],
						'sku_product_description' => isset($value['sku_description']) ? $value['sku_description'] : '',
						'product_min_qty' => isset($product_min_qty) ? $product_min_qty : 1,
					];

					$product_details_arr['image'] = 'product_image/default-product.png';
					$product_details_arr['image_thumb'] = 'product_image/default-product.png';

					$is_new_product_store = ProductDetailsModel::create($product_details_arr);

					$sku_no = isset($value['sku']) ? $value['sku'] : '';
					$quantity = isset($override_params['available_qty']) && $override_params['available_qty'] !== false ? $override_params['available_qty'] : isset($value['available_qty']) ? $value['available_qty'] : '';

					$product_inventory_arr =
						[
						'product_id' => $product_res->id,
						'sku_no' => $sku_no,
						'quantity' => $quantity,
						'user_id' => $user_id,
					];

					if ($override_params['sub_category_id'] === false) {
						/*$sub_cat_arr = get_subcategories();
						$sub_cat_id = get_sub_cat_id($value['subcategory']);*/

						$sub_cat_id = isset($subcategory_id) ? $subcategory_id : 0;
						$category_id = isset($category_id) ? $category_id : 0;

						/*$valid_sub_cat = is_valid_subcat($category_id,$sub_cat_id);

							if($valid_sub_cat!=1)
							{
							  Flash::error('product ' .$value['product_name'].' category and sub-category are invalid.');
							  return redirect()->back();
						*/
					}

					$arr_cat_data = [];
					$arr_cat_data['category_id'] = $override_params['category_id'] !== false ? $override_params['category_id'] : $category_id;
					$arr_cat_data['sub_category_id'] = $override_params['sub_category_id'] !== false ? $override_params['sub_category_id'] : $sub_cat_id;
					$arr_cat_data['product_id'] = $product_res->id;


					$arr_cat_data['third_sub_category_id'] = $override_params['third_category_id'] !== false ? $override_params['third_category_id'] : $third_category_id;

					$arr_cat_data['fourth_sub_category_id'] = $override_params['fourth_category_id'] !== false ? $override_params['fourth_category_id'] : $fourth_category_id;
					 // dd($arr_cat_data['fourth_sub_category_id']);

					ProductsSubCategoriesModel::create($arr_cat_data);

					//check category and sub category mapping
					//dd($value['category'],$value['subcategory']);
					if (isset($value['category']) && $value['category'] != "" && isset($value['subcategory']) && $value['subcategory'] != "") {
						/*  $category_id     = get_category_id($value['category']);
							    $sub_category_id = get_sub_cat_id($value['subcategory']);

						*/

						$is_map_cat_sub_cat = false;
						if (isset($arr_subcategory_category_mapping[$subcategory_id]) && $arr_subcategory_category_mapping[$subcategory_id] == $category_id) {
							$is_map_cat_sub_cat = true;
						}

						if ($is_map_cat_sub_cat == false) {
							$update_result = ProductsSubCategoriesModel::
								where('product_id', $product_res->id)
								->where('category_id', $category_id)
								->update(['sub_category_id' => 0]);

							array_push($cat_sub_cat_mapping_arr, $product_res->id);
						}

					}

				}

				/*-----------------------------------------------------------------*/

				/* Product Inventory */

				$create_inventory_data = ProductInventoryModel::create($product_inventory_arr);

				//add default images in product image table

				$img_arr = [];
				/*
					                if (isset($product_id))
					                {
					                    $productId = $product_id;
									} else
					                {
									    $productId = $product_res->id;
					                }
				*/

				$img_arr['product_id'] = $finalized_product_id;

				$img_arr['product_image'] = 'product_image/default-product.png';
				$img_arr['lifestyle_image'] = 'product_image/default-product.png';
				$img_arr['packaging_image'] = 'product_image/default-product.png';

				ProductImagesModel::create($img_arr);

				// DB::commit();
				// $successful_insertion++;
				// $product_name_successful_insertion++;

				// add product into elastic search

				$this->ElasticSearchService->index_product($finalized_product_id);

				/*} catch (Exception $error) {
					                    DB::rollback();
					                    Flash::error('Something went wrong, please try again.');
					                    return redirect()->back();
					                }

				*/

			}

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
		}

	}

	public function failed($exception) {

		dd($exception->getMessage());
	}
}
