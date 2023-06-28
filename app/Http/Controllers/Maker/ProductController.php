<?php

namespace App\Http\Controllers\Maker;

use App\Common\Services\ElasticSearchService;
use App\Common\Services\HelperService;
use App\Common\Services\GeneralService;
use App\Common\Services\ProductService;
use App\Common\Services\UserService;
use App\Http\Controllers\Controller;
use App\Models\BrandsModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\ProductMultipleImagesModel;
use App\Models\ProductInventoryModel;
use App\Models\ProductsModel;
use App\Models\MakerModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\ProductUpdatedByVendorModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SubCategoryModel;
use App\Models\ThirdSubCategoryModel;
use App\Models\FourthSubCategoryModel;
use App\Models\SizeModel;
use App\Models\ProductSizesModel;

use App\Models\ThirdSubCategoryTranslationModel;
use App\Models\FourthSubCategoryTranslationModel;

use App\Models\SubCategoryTranslationModel;
use App\Models\UserModel;
use App\Models\SiteSettingModel;
use Datatables;
use DB;
use Excel;
use Flash;
use Illuminate\Http\Request;
use Image;
use Sentinel;
use Session;
use Validator;

class ProductController extends Controller {
	/*
		    | Author : Sagar B. Jadhav
		    | Date   : 27 June 2019
	*/

	public function __construct(CategoryModel $CategoryModel,
		SubCategoryTranslationModel $SubCategoryTranslationModel,
		ProductsModel $ProductsModel,
		ProductUpdatedByVendorModel $ProductUpdatedByVendorModel,
		BrandsModel $BrandsModel,
		MakerModel $MakerModel,
		FourthSubCategoryModel $FourthSubCategoryModel,
		ThirdSubCategoryModel $ThirdSubCategoryModel,

		
		FourthSubCategoryTranslationModel $FourthSubCategoryTranslationModel,
		ThirdSubCategoryTranslationModel $ThirdSubCategoryTranslationModel,

		ProductDetailsModel $ProductDetailsModel,
		ProductImagesModel $ProductImagesModel,
		ProductMultipleImagesModel $ProductMultipleImagesModel,
		CategoryTranslationModel $CategoryTranslationModel,
		ProductsSubCategoriesModel $ProductsSubCategoriesModel,
		UserModel $UserModel,
		RoleModel $RoleModel,
		RoleUsersModel $RoleUsersModel,
		SubCategoryModel $SubCategoryModel,
		ProductInventoryModel $ProductInventoryModel,
		GeneralService $GeneralService,
		UserService $UserService,
		ProductService $ProductService,
		ElasticSearchService $ElasticSearchService,
		HelperService $HelperService,
		SizeModel $SizeModel,
		ProductSizesModel $ProductSizesModel

	) {

		$this->locale = \App::getLocale();

		$this->UserModel 						= $UserModel;
		$this->RoleModel 						= $RoleModel;
		$this->BrandsModel 						= $BrandsModel;
		$this->RoleUsersModel 					= $RoleUsersModel;
		$this->SubCategoryModel 				= $SubCategoryModel;
		$this->ThirdSubCategoryModel			= $ThirdSubCategoryModel;
		$this->FourthSubCategoryModel			= $FourthSubCategoryModel;
		$this->CategoryModel 					= $CategoryModel;
		$this->SubCategoryTranslationModel 		= $SubCategoryTranslationModel;
		$this->ProductsModel 					= $ProductsModel;
		$this->ProductUpdatedByVendorModel 		= $ProductUpdatedByVendorModel;
		$this->BaseModel 						= $ProductsModel;
		$this->MakerModel 					    = $MakerModel;
		$this->ProductDetailsModel 				= $ProductDetailsModel;
		$this->ProductInventoryModel 			= $ProductInventoryModel;
		$this->ProductImagesModel 				= $ProductImagesModel;
		$this->ProductMultipleImagesModel 		= $ProductMultipleImagesModel;
		$this->CategoryTranslationModel 		= $CategoryTranslationModel;
		$this->ProductsSubCategoriesModel 		= $ProductsSubCategoriesModel;
		$this->ProductService 					= $ProductService;
		$this->GeneralService 					= $GeneralService;
		$this->UserService 						= $UserService;
		$this->ElasticSearchService 			= $ElasticSearchService;
		$this->HelperService					= $HelperService;
		$this->SizeModel					= $SizeModel;
		$this->ProductSizesModel			= $ProductSizesModel;

		$this->ThirdSubCategoryTranslationModel			= $ThirdSubCategoryTranslationModel;
		$this->FourthSubCategoryTranslationModel			= $FourthSubCategoryTranslationModel;

		$this->product_img = base_path() . '/storage/app/';
		$this->arr_view_data = [];
		$this->module_title = "Products";
		$this->module_view_folder = 'maker.product';
		$this->maker_panel_slug = config('app.project.maker_panel_slug');
		$this->module_url_path = url($this->maker_panel_slug . '/products');
		$this->product_default_img_path = config('app.project.img_path.product_default_images');

	}

	public function index() {

		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		$arr_brand = $this->BrandsModel->where('is_active', '1')
			->where('user_id', $loggedInUserId)
			->orderBy('brand_name', 'ASC')
			->get();

		$category_arr = $this->CategoryModel->where('is_active', 1)
			->whereTranslation('locale', $this->locale)
			->get()
			->toArray();

		/* Sort by Alpha */
		usort($category_arr, function ($sort_base, $sort_compare) {

			return $sort_base['category_name'] <=> $sort_compare['category_name'];
		});

		// Get vendor commission by Harshada kothmire on date 09 Dec 2020
		$vendor_commisssion_percent = "0";
		$get_vendor_commission = $this->MakerModel
								  ->select('admin_commission')
								  ->where('user_id',$loggedInUserId)
								  ->where('admin_commission','!=','0')
								  ->get()->toArray();

		// If vendor not having commission then took from site setting
		if(empty($get_vendor_commission)){
					$site_settings = SiteSettingModel::
												select('commission')
												->where('id','1')
												->get()->toArray();
					if(!empty($site_settings)){
						$vendor_commisssion_percent = $site_settings[0]['commission'];
					}
		} else {
			// Else took from maker table
			$vendor_commisssion_percent = $get_vendor_commission[0]['admin_commission'];
		}

		

		$this->arr_view_data['category_arr'] 				= $category_arr;
		$this->arr_view_data['vendor_commisssion_percent'] 	= $vendor_commisssion_percent;
		$this->arr_view_data['arr_brand'] 					= $arr_brand;
		$this->arr_view_data['module_title'] 				= 'Products';
		$this->arr_view_data['page_title'] 					= 'Products';
		$this->arr_view_data['module_url_path'] 			= $this->module_url_path;
		$this->arr_view_data['maker_panel_slug'] 			= $this->maker_panel_slug;

		// chmod(storage_path(), 0777);

		return view($this->module_view_folder . '.index', $this->arr_view_data);
	}

	public function create(Request $request) {

		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		$arr_brand = $this->BrandsModel->where('is_active', '1')
			->where('user_id', $loggedInUserId)
			->orderBy('brand_name', 'ASC')
			->get();

		$category_arr = $this->CategoryModel->where('is_active', 1)
			->whereTranslation('locale', $this->locale)
			->get()
			->toArray();

		/* Sort by Alpha */
		usort($category_arr, function ($sort_base, $sort_compare) {

			return $sort_base['category_name'] <=> $sort_compare['category_name'];
		});

		// Get vendor commission by Harshada kothmire on date 09 Dec 2020
		$vendor_commisssion_percent = "0";
		$get_vendor_commission = $this->MakerModel
								  ->select('admin_commission')
								  ->where('user_id',$loggedInUserId)
								  ->where('admin_commission','!=','0')
								  ->get()->toArray();

		// If vendor not having commission then took from site setting
		if(empty($get_vendor_commission)){
					$site_settings = SiteSettingModel::
												select('commission')
												->where('id','1')
												->get()->toArray();
					if(!empty($site_settings)){
						$vendor_commisssion_percent = $site_settings[0]['commission'];
					}
		} else {
			// Else took from maker table
			$vendor_commisssion_percent = $get_vendor_commission[0]['admin_commission'];
		}


		/* Check Get a quote is active or not */
		$get_quote_status = $this->MakerModel
								  ->select('is_get_a_quote','primary_category_id')
								  ->where('user_id',$loggedInUserId)								 
								  ->get()->toArray();

		$chk_quote_status = 1;
		if(!empty($get_quote_status)){
				$chk_quote_status = isset($get_quote_status[0]['is_get_a_quote']) ? $get_quote_status[0]['is_get_a_quote'] : 0;
		}




		

		$this->arr_view_data['category_arr'] 				= $category_arr;
		$this->arr_view_data['primary_category_id'] 	    = $get_quote_status[0]['primary_category_id'];
		
		$this->arr_view_data['vendor_commisssion_percent'] 	= $vendor_commisssion_percent;
		$this->arr_view_data['arr_brand'] 					= $arr_brand;

		$this->arr_view_data['module_title'] 				= 'Add Product';
		$this->arr_view_data['page_title'] 					= 'Add Product';
		$this->arr_view_data['module_url_path'] 			= $this->module_url_path;
		$this->arr_view_data['chk_quote_status'] 			= $chk_quote_status;
		$this->arr_view_data['maker_panel_slug'] 			= $this->maker_panel_slug;
		return view($this->module_view_folder . '.add', $this->arr_view_data);
	}

	public function edit($enc_id) {
		$product_id       = base64_decode($enc_id);
		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		$arr_brand = $this->BrandsModel->where('is_active', '1')
			->where('user_id', $loggedInUserId)
			->orderBy('brand_name', 'ASC')
			->get();

		$category_arr = $this->CategoryModel->where('is_active', 1)
			->whereTranslation('locale', $this->locale)
			->get()
			->toArray();

		/* Sort by Alpha */
		usort($category_arr, function ($sort_base, $sort_compare) {

			return $sort_base['category_name'] <=> $sort_compare['category_name'];
		});

		
		//dd($sub_category_arr);
		// Get vendor commission by Harshada kothmire on date 09 Dec 2020
		$vendor_commisssion_percent = "0";
		$get_vendor_commission = $this->MakerModel
								  ->select('admin_commission')
								  ->where('user_id',$loggedInUserId)
								  ->where('admin_commission','!=','0')
								  ->get()->toArray();


		/* Check Get a quote is active or not */
		$get_quote_status = $this->MakerModel
								  ->select('is_get_a_quote')
								  ->where('user_id',$loggedInUserId)								 
								  ->get()->toArray();

		$chk_quote_status = 1;
		if(!empty($get_quote_status)){
				$chk_quote_status = isset($get_quote_status[0]['is_get_a_quote']) ? $get_quote_status[0]['is_get_a_quote'] : 0;
		}								


		// If vendor not having commission then took from site setting
		if(empty($get_vendor_commission)){
					$site_settings = SiteSettingModel::
												select('commission')
												->where('id','1')
												->get()->toArray();
					if(!empty($site_settings)){
						$vendor_commisssion_percent = $site_settings[0]['commission'];
					}
		} else {
			// Else took from maker table
			$vendor_commisssion_percent = $get_vendor_commission[0]['admin_commission'];
		}

		$product_arr = [];
		
		$product_obj = $this->BaseModel->with([
			'productDetails.inventory_details',
			'productImages',
			'productDetails.productMultipleImages',
			'productSubCategories.SubcategoryDetails'])
			->with(['productDetails' => function($q){
				return $q->where('is_deleted',0);
			}])
			->where('id', $product_id)
			->first();
			
		if ($product_obj) {
			$product_arr = $product_obj->toArray();		
			// dd($product_arr);
			$subcategories_id_arr = [];
			$subcategories_id_arr = array_column($product_arr['product_sub_categories'], 'sub_category_id');
			$product_arr['subcat_id'] = json_encode($subcategories_id_arr);	
			$prod_cat_id = isset($product_arr['category_id']) ? $product_arr['category_id'] : 0;
			$sub_category_arr = $this->SubCategoryModel->where('is_active', 1)
												->whereTranslation('locale', $this->locale)
												->where('category_id',$prod_cat_id)
												->get()
												->toArray();

			$third_subcategories_id_arr = [];
			// $sub_subcategories_id_arr = [];
			// $sub_subcategories_id_arr = array_unique(array_column($product_arr['product_sub_categories'], 'sub_category_id'));
			$third_subcategories_id_arr = array_column($product_arr['product_sub_categories'], 'third_sub_category_id');
			$product_arr['third_subcat_id'] = json_encode($third_subcategories_id_arr);	

			$prod_sub_cat_id = isset($product_arr['product_sub_categories'][0]['category_id']) ? $product_arr['product_sub_categories'][0]['category_id'] : 0;
			$third_sub_category_arr = $this->ThirdSubCategoryModel->where('is_active', 1)
										->whereTranslation('locale', $this->locale)
										->where('category_id',$prod_cat_id)
										->whereIn('sub_category_id',$subcategories_id_arr)
										->get()
										->toArray();

			$fourth_subcategories_id_arr = [];
			$fourth_subcategories_id_arr = array_column($product_arr['product_sub_categories'], 'fourth_sub_category_id');
			$product_arr['fourth_subcat_id'] = json_encode($fourth_subcategories_id_arr);	
			$prod_sub4_cat_id = isset($product_arr['product_sub_categories'][0]['category_id']) ? $product_arr['product_sub_categories'][0]['category_id'] : 0;
			$fourth_sub_cat_arr = $this->FourthSubCategoryModel->where('is_active', 1)
										->whereTranslation('locale', $this->locale)
										->whereIn('third_sub_category_id',$third_subcategories_id_arr)
										->where('category_id',$prod_sub4_cat_id)
										->get()
										->toArray();
			
			/* Sort by Alpha */
			usort($sub_category_arr, function ($sort_base, $sort_compare) {

				return $sort_base['subcategory_name'] <=> $sort_compare['subcategory_name'];
			});	
		}

		$this->arr_view_data['category_arr'] 				= $category_arr;
		$this->arr_view_data['vendor_commisssion_percent'] 	= $vendor_commisssion_percent;
		$this->arr_view_data['arr_brand'] 					= $arr_brand;
		$this->arr_view_data['product_id'] 					= $product_id;
		$this->arr_view_data['product_arr'] 				= $product_arr;
		$this->arr_view_data['sub_cat_arr'] 				= $sub_category_arr;
		$this->arr_view_data['third_sub_cat_arr'] 			= $third_sub_category_arr;
		$this->arr_view_data['fourth_sub_cat_arr'] 			= $fourth_sub_cat_arr;
		$this->arr_view_data['module_title'] 				= 'Edit Product';
		$this->arr_view_data['page_title'] 					= 'Edit Product';
		$this->arr_view_data['module_url_path'] 			= $this->module_url_path;
		$this->arr_view_data['chk_quote_status'] 			= $chk_quote_status;
		$this->arr_view_data['maker_panel_slug'] 			= $this->maker_panel_slug;
		return view($this->module_view_folder . '.edit', $this->arr_view_data);
	}

	public function get_maker_products(Request $request) {

		$loggedInUserId = 0;
		$user = Sentinel::check();

		$arr_search_column = $request->input('column_filter');

		if ($user) {
			$loggedInUserId = $user->id;
		}
		$obj_products = $this->ProductService->get_product_list($loggedInUserId, $arr_search_column);

		// dd($obj_products->get()->toArray());

		//Calculate total by Harshada on date 09 Sep 2020
		// $total_amt_retail = $total_amt_whole = 0;
		$total_amt_whole = array_reduce($obj_products->get()->toArray(), function (&$res, $item) {
			return $res + $item->unit_wholsale_price;
		}, 0);

		// $total_amt_retail = array_reduce($obj_products->get()->toArray(), function (&$res, $item) {
		// 	return $res + $item->retail_price;
		// }, 0);

		$total_amt_shipping = array_reduce($obj_products->get()->toArray(), function (&$res, $item) {
			return $res + $item->shipping_charges;
		}, 0);
		$current_context = $this;

		$json_result = Datatables::of($obj_products);

		/* Modifying Columns */
		$json_result = $json_result->editColumn('product_image', function ($data) use ($current_context) {
			$product_img_path = "";
			$image_name = (isset($data->product_image)) ? $data->product_image : "";
			$image_type = "product";
			$is_resize = 0;
			$product_img_path = imagePath($image_name, $image_type, $is_resize);

			return $product_img_path;
		})

			->editColumn('enc_id', function ($data) use ($current_context) {
				return $id = isset($data->id) ? $data->id : '';
			})

			->editColumn('category_name', function ($data) use ($current_context) {
				return $category_name = isset($data->category_name) && $data->category_name != '' ? $data->category_name : 'N/A';
			})

			->editColumn('brand_name', function ($data) use ($current_context) {

				return $brand_name = isset($data->brand_name) ? $data->brand_name : 'N/A';
			})

			->editColumn('product_sku_status', function ($data) {

				return $count = get_product_sku_count($data->id);

			})

			->editColumn('total_inventory', function ($data) {
				$total_inventory = 0;

				return $total_inventory = get_total_sku_inventory($data->id);

			})

			->editColumn('quantity', function ($data) {
				//dd($data->quantity);
				$quantity = get_all_sku($data->id);
				return $quantity = isset($quantity) ? $quantity : 'N/A';

			})

			->editColumn('remark', function ($data) use ($current_context) {
				return $remark = isset($data->remark) ? $data->remark : 'N/A';
			})

			->editColumn('is_active', function ($data) {
				$status = '';

				if ($data->is_active == 0) {
					$status = '<span class="label label-success">Rejected</span>';
				}
				if ($data->is_active == 1) {
					$status = '<span class="label label-success">Approved</span>';
				}
				if ($data->is_active == 2) {
					$status = '<span class="label label-success">Pending</span>';
				}

				return $status;

			})

			->make(true);

		$build_result = $json_result->getData();
		$build_result->total_amt_whole = $total_amt_whole;
		// $build_result->total_amt_retail = $total_amt_retail;
		$build_result->total_amt_shipping = $total_amt_shipping;
		return response()->json($build_result);
	}

	public function get_export_maker_products(Request $request) {
		$type = 'csv';
		$data = $arr_products = $arrayResponseData = [];

		$loggedInUserId = 0;
		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		$arr_search_column = $request->all();
		$vendor_commission = isset($arr_search_column['vendor_commission']) ? $arr_search_column['vendor_commission'] : 0;
		$arr_products = $this->ProductService->get_product_list($loggedInUserId, $arr_search_column)->get()->toArray();

		if (count($arr_products) <= 0) {
			$response['status'] = 'error';
			$response['message'] = 'No data available for export';

			return response()->json($response);
		}

		foreach ($arr_products as $key => $value) {
			// $mp_arr=array('productDetails.inventory_details','productImages','productSubCategories.SubcategoryDetails','productSubCategories.categoryDetails','categoryDetails','userDetails','brand_details');

			// $product_arr = $this->ProductService->get_productDetails_from_productId($value->id,$mp_arr);
			// dd($product_arr['id']);
			$product_status = 'Active';
			if ($value->product_status == 0 || $value->product_status == null) {
				$product_status = 'Inactive';
			} else {
				$product_status = 'Active';
			}

			$product_complete_status = 'Completed';
			if ($value->product_complete_status == 4) {
				$product_complete_status = 'Completed';
			} else {
				$product_complete_status = 'Incomplete';
			}

			$admin_status = 'Approved';
			if ($value->is_active == 1) {
				$admin_status = 'Approved';
			} else if ($value->is_active == 0) {
				$admin_status = 'Rejected';
			} else {
				$admin_status = 'Pending';
			}


			$commission_wholesale_price       = 0;
            $commission_wholesale_price = ($value->unit_wholsale_price - ( $value->unit_wholsale_price * $vendor_commission / 100 ));

           	// $commission_retail_price       = 0;
            // $commission_retail_price = ($value->retail_price - ( $value->retail_price * $vendor_commission / 100 ));
          

			$arrayResponseData['Product Name'] = $value->product_name;
			$arrayResponseData['Category'] = $value->category_name;
			$arrayResponseData['Brand Name'] = $value->brand_name;
			$arrayResponseData['SKU'] = $value->sku;
			$arrayResponseData['Inventory'] = $value->quantity;
			$arrayResponseData['Admin Commission(%)'] = $vendor_commission;
			$arrayResponseData['Price'] = $value->unit_wholsale_price;
			$arrayResponseData['Commission Price'] = number_format($commission_wholesale_price,2);
			// $arrayResponseData['Price (Retail)'] = $value->retail_price;
			// $arrayResponseData['Commission Price (Retail)'] = number_format($commission_retail_price,2);
			$arrayResponseData['Shipping Charge'] = $value->shipping_charges;
			$arrayResponseData['Product Status'] = $product_complete_status;
			$arrayResponseData['Admin Status'] = $admin_status;
			$arrayResponseData['Status'] = $product_status;

			array_push($data, $arrayResponseData);
		}
		
		return Excel::create('Vendor Products', function ($excel) use ($data) {

			$excel->sheet('Vendor Products', function ($sheet) use ($data) {
				$sheet->fromArray($data);
				$sheet->freezeFirstRow();
				$sheet->cells("A1:M1", function ($cells) {
					$cells->setFont(array(
						'bold' => true,
					));

				});
			});
		})->download($type);

	}

	public function changeStatus(Request $request) {
		$product_status = $request->input('productStatus');
		$product_id = $request->input('product_id');
		$status_response = $this->ProductService->changeStatus($product_status, $product_id);
		return response()->json($status_response);

	}

	public function storeProduct(Request $request) {
		// dd($request->all());
		try
		{
			$is_update = false;
			$arr_rules = [];
			$loggedInUserId = 0;
			$form_data = $request->all();
			$json_data = json_encode($form_data);
			$changes = [];

			$is_click_on_storeProduct = isset($form_data['is_click_on_storeProduct']) ? $form_data['is_click_on_storeProduct'] : 0;
			$is_click_on_update_style_and_dimension = isset($form_data['is_click_on_update_style_and_dimension']) ? $form_data['is_click_on_update_style_and_dimension'] : 0;
			$is_click_on_store_additional_images = isset($form_data['is_click_on_store_additional_images']) ? $form_data['is_click_on_store_additional_images'] : 0;
			$is_click_on_update_product_dategory = isset($form_data['is_click_on_update_product_dategory']) ? $form_data['is_click_on_update_product_dategory'] : 0;

			if ($request->has('product_id') && !empty($request->input('product_id'))) {
				$is_update = true;
				$product_id = $request->input('product_id', null);
				
			}

			$product_id = $request->input('product_id', null);

			$arr_rules = [
				'product_name' => 'required',
				'brand' => 'required',
				//'retail_price'            => 'required',
				'case_qty' => 'required',
				'unit_wholsale_price' => 'required',
				'restock_days' => 'required'

			];

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';
				$response['description'] = 'Form validations failed, please check form fields.';

				return response()->json($response);
			}

			/* Check Get a quote is active or not */
			$get_quote_status = $this->MakerModel
						  ->select('is_get_a_quote')
						  ->where('user_id',$loggedInUserId)								 
						  ->get()->toArray();

			$chk_quote_status = 1;
			if(!empty($get_quote_status)){
				$chk_quote_status = isset($get_quote_status[0]['is_get_a_quote']) ? $get_quote_status[0]['is_get_a_quote'] : 0;
			}			



			if($chk_quote_status == 0){
				if ($form_data['unit_wholsale_price'] != "" && $form_data['unit_wholsale_price'] == 0) {
					$response['status'] = 'warning';
					$response['description'] = 'price should not be 0.';
					return response()->json($response);
				}
			}

			/*  if($form_data['retail_price']!="" && $form_data['retail_price']==0)
				            {
				               $response['status']      = 'warning';
				               $response['description'] = 'Unit retail price should not be 0.';
				               return response()->json($response);
			*/

			// if (isset($form_data['retail_price']) && $form_data['retail_price'] != "") {
			// 	if ($form_data['unit_wholsale_price'] > $form_data['retail_price']) {
			// 		$response['status'] = 'warning';
			// 		$response['description'] = 'Unit wholesale price should be less than unit retail price ';
			// 		return response()->json($response);
			// 	}
			// }

			DB::beginTransaction();

			$user = Sentinel::check();

			if ($user) {
				$loggedInUserId = $user->id;
			}

			/* Check if product already exists with given name*/
			$is_duplicate = $this->BaseModel->where('product_name', $form_data['product_name'])
				->where('is_deleted', '=', 0);

			if ($is_update) {
				$is_duplicate->where('id', '<>', $product_id);
				$complete_status = get_product_complete_status($product_id);
			}

			$is_duplicate = $is_duplicate->count() > 0;

		/*	if ($is_duplicate) {
				$response['status'] = 'warning';
				$response['description'] = 'Product already exist with ' . $form_data['product_name'] . ' name.';

				return response()->json($response);
			}*/

			//upload product image and get path
			if (isset($form_data['product_primary_image'])) {
				$product_img_file_path = '';
				$img = isset($form_data['product_primary_image']) ? $form_data['product_primary_image'] : null;

				//Validation for product image
				$file_extension = strtolower($form_data['product_primary_image']->getClientOriginalExtension());

				if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
					$arr_response['status'] = 'FAILURE';

					$arr_response['description'] = 'Invalid product image, please try again.';

					return response()->json($response);
				}

				// $product_image = Image::make($img)->resize(230, 230);

				$product_image = Image::make($img);


				$path = storage_path('app/product_image/product_img_thumb');
				$img_name = date('mdYHis') . uniqid() . '.' . $file_extension;
				$product_img_file = $product_image->save($path . '/' . $img_name);

				$original_product_img_path = $img->store('product_image');

				$product_img_file_path = 'product_image/' . $img_name;

				$arr = [];
				$thumb_img_name = "";
				//unlink old image from directory in any available
				if (isset($form_data['old_product_image']) && isset($form_data['old_product_image_thumb'])) {
					$old_product_img = isset($form_data['old_product_image']) ? $form_data['old_product_image'] : '';
					$old_product_img_thumb = isset($form_data['old_product_image_thumb']) ? $form_data['old_product_image_thumb'] : '';

					$unlink_old_img_path = $this->product_img . $old_product_img;
					$unlink_old_thumb_img_path = $this->product_img . $old_product_img_thumb;

					if (file_exists($unlink_old_img_path)) {
						$unlink_old_img = $this->GeneralService->unlink_old_image($unlink_old_img_path);
						$unlink_old_thumb_img = $this->GeneralService->unlink_old_image($unlink_old_thumb_img_path);
					}

					//For unlinking image from img_thumb folder
					$arr = explode('/', $unlink_old_thumb_img_path);
					$thumb_img_name = $arr[7];
					$unlink_thumb_img = $this->GeneralService->unlink_old_image($this->product_img . '/product_image/product_img_thumb/' . $thumb_img_name);
				}
			} else {
				$original_product_img_path = $form_data['old_product_image'];

				$product_img_file_path = $form_data['old_product_image_thumb'];
			}

			if (isset($form_data['is_best_seller'])) {
				$is_best_seller = $form_data['is_best_seller'];
			} else {
				$is_best_seller = 0;
			}

			if (isset($form_data['is_tester_available'])) {
				$is_tester_available = $form_data['is_tester_available'];
			} else {
				$is_tester_available = 0;
			}

			$product = $this->BaseModel->firstOrNew(['id' => $product_id]);

			$product->product_name = isset($form_data['product_name']) ? $form_data['product_name'] : '';
			$product->ingrediants = isset($form_data['product_ingrediants']) ? $form_data['product_ingrediants'] : '';
			$product->brand = isset($form_data['brand']) ? $form_data['brand'] : '';
			$product->available_qty = isset($form_data['case_qty']) ? $form_data['case_qty'] : '';
			$product->unit_wholsale_price = isset($form_data['unit_wholsale_price']) ? $form_data['unit_wholsale_price'] : '';
			// $product->retail_price = isset($form_data['retail_price']) ? $form_data['retail_price'] : '0.00';
			$product->description = isset($form_data['product_description']) ? $form_data['product_description'] : '';
			$product->product_image_thumb = isset($product_img_file_path) ? $product_img_file_path : '';
			$product->product_image = isset($original_product_img_path) ? $original_product_img_path : '';
			$product->user_id = isset($loggedInUserId) ? $loggedInUserId : 0;
			$product->is_best_seller = $is_best_seller;
			$product->is_tester_available = $is_tester_available;

			$product->case_quantity = isset($form_data['case_qty']) ? $form_data['case_qty'] : '';
			$product->restock_days = isset($form_data['restock_days']) ? $form_data['restock_days'] : '';

			if ($is_update == true) {
				$changes = $product->getDirty();

				$product->is_remark_checked = 1;

				// $complete_status =1;

				if ($product->product_complete_status == 4) {

					if (isset($changes) && count($changes) >= 1) {
						$product->is_remark_checked = 1;
						$product->is_active = 2;
						$product->previous_category_status = '0';
						$product->remark = null;

					} else if ($form_data['is_active'] == '1' && count($changes) == '0') {
						$product->is_active = 1;
						$product->previous_category_status = '1';
						$product->is_remark_checked = 0;
						$product->remark = null;

					} else {
						$product->is_remark_checked = 0;
						$product->is_active = 0;
						$product->previous_category_status = '1';
					}
				}

			}

			if (isset($complete_status)) {
				if ($complete_status <= 1) {
					$product->product_complete_status = '1';

				} else {
					$product->product_complete_status = $complete_status;
				}
			} else {
				$product->product_complete_status = '1';

			}
			if (isset($form_data['items_is_draft']) && $form_data['items_is_draft'] == 'yes') {
				$product->is_draft = 1;
			}

			/* --------------------------------------------------------------------------------
				                Code to insert updated column in table - product_updated_by_vendor
				                To see updated columns in admin login
				                By Harshada Kothmire
				                On date 07 Sept 202
			*/
			if ($is_update == true) {
				$res_already_updated_by_vendor = $this->ProductUpdatedByVendorModel->where('product_id', $product_id)->get()->toArray();

				$updatedColumnsArray = array();
				$updateNewColumnsArray = array();
				$ProductUpdatedArr = array();
				$updateNewColumns = "";

				if (!empty($res_already_updated_by_vendor)) {
					$updatedColumnsArray = json_decode($res_already_updated_by_vendor[0]['update_columns'], true);

					if (isset($updatedColumnsArray) && count($updatedColumnsArray) > 0) {
						foreach ($updatedColumnsArray as $det) {
							array_push($updateNewColumnsArray, $det);
						}
					}

				}
				/* ------------------ Form 1 Columns------------------------------------------------*/
				$res_product_data = $this->ProductsModel->where('id', $product_id)->get()->toArray();
				if ($res_product_data[0]['product_name'] != $form_data['product_name']) {
					array_push($updateNewColumnsArray, 'product_name');
				}
				if ($res_product_data[0]['brand'] != $form_data['brand']) {
					array_push($updateNewColumnsArray, 'brand');
				}
				if ($res_product_data[0]['available_qty'] != $form_data['case_qty']) {
					array_push($updateNewColumnsArray, 'available_qty');
				}
				if ($res_product_data[0]['unit_wholsale_price'] != $form_data['unit_wholsale_price']) {
					array_push($updateNewColumnsArray, 'unit_wholsale_price');
				}
				// if ($res_product_data[0]['retail_price'] != $form_data['retail_price']) {
				// 	array_push($updateNewColumnsArray, 'retail_price');
				// }
				if ($res_product_data[0]['ingrediants'] != $form_data['product_ingrediants']) {
					array_push($updateNewColumnsArray, 'ingrediants');
				}
				if ($res_product_data[0]['description'] != $form_data['product_description']) {
					array_push($updateNewColumnsArray, 'description');
				}
				if ($res_product_data[0]['product_image'] != $original_product_img_path) {
					array_push($updateNewColumnsArray, 'product_image');
				}
				if ($res_product_data[0]['restock_days'] != $form_data['restock_days']) {
					array_push($updateNewColumnsArray, 'restock_days');
				}

				/* ------------------ Form 1 columns------------------------------------------------*/
				if (!empty($updateNewColumnsArray)) {
					$updateNewColumns = json_encode(array_unique($updateNewColumnsArray));
				}

				if (!empty($res_already_updated_by_vendor)) {
						    $ProductUpdatedArr['update_columns'] = $updateNewColumns;							
							$ProductUpdatedArr['updated_at'] = date("Y-m-d H:i:s");
							$this->ProductUpdatedByVendorModel->where('product_id', $product_id)->update($ProductUpdatedArr);
				} else {
					$ProductUpdatedArr['product_id'] = $product_id;
					$ProductUpdatedArr['vendor_id'] = isset($loggedInUserId) ? $loggedInUserId : 0;
					$ProductUpdatedArr['update_columns'] = $updateNewColumns;
					$ProductUpdatedArr['created_at'] = date("Y-m-d H:i:s");
					if ($updateNewColumns) {
						$this->ProductUpdatedByVendorModel->create($ProductUpdatedArr);
					}
				}

			}

			$is_store = $product->save();
			
			$this->ElasticSearchService->initiate_index_product($product->id);
		

			if ($is_store) {

				// dd($product->id);
				/* Insert blank entry into product details*/
				$is_entry_exist = $this->ProductDetailsModel->where('product_id', $product->id)->count();
				$product_arr = [
						'option_type' 			=> null,
						'product_id' 			=> $product->id,
						'option' 				=>  null,
						'sku' 					=>  '',
						'weight' 				=>  '0',
						'length' 				=>  '0',
						'width' 				=>  '0',
						'height' 				=>  '0',
						'image_thumb' 			=> '',
						'image' 				=> '',
						'sku_product_description' =>  '',
						'product_min_qty' 		=> '0',

					];
				if($is_entry_exist == 0){
					$this->ProductDetailsModel->create($product_arr);
				}
				/* Ends */



				$arr_event = [];

				if ($is_update == false) {
					$arr_event['ACTION'] = 'ADD';
					$arr_event['MODULE_ID'] = $product->id;

				}
				else
				{
					$arr_event['ACTION'] = 'EDIT';
					$arr_event['MODULE_ID'] = $product_id;

					/* Send notification to admin after edit*/

					//Get admin id

					$admin_id = get_admin_id();

					//Get maker name

					$first_name = isset($user->first_name) ? $user->first_name : "";
					$last_name = isset($user->last_name) ? $user->last_name : "";
					$product_name = isset($form_data['product_name']) ? $form_data['product_name'] : '';

					$notification_arr = [];
					$notification_arr['from_user_id'] = $loggedInUserId;
					$notification_arr['to_user_id'] = $admin_id;

					$product_detail_url = url('/admin/products/view/' . base64_encode($form_data['product_id']));

					//$product_url =    '<a href="'.$product_detail_url.'">'.$form_data['product_name'].'</a>';

					/* $notification_arr['description']  = 'Product '.'"'.$product_url.'"'.' updated by '.$first_name.' '.$last_name;*/

					$notification_arr['description'] = 'Product ' . '"' . $form_data['product_name'] . '"' . ' updated by ' . $first_name . ' ' . $last_name;

					$notification_arr['title'] = 'Updated Product';
					$notification_arr['type'] = 'admin';
					$notification_arr['link'] = $product_detail_url;

					$product_detail_url = url('/admin/products/view/' . base64_encode($form_data['product_id']));

					$notification_arr['description'] = 'Product ' . '"' . $form_data['product_name'] . '"' . ' updated by ' . $first_name . ' ' . $last_name;

				}


                $arr_event['USER_ID'] = $loggedInUserId;
				$arr_event['MODULE_TITLE'] = $this->module_title;

				$this->save_activity($arr_event);

				DB::commit();
				$response['status'] = 'success';

				/* if($is_update)
					            {
					              $this->GeneralService->save_notification($notification_arr);
					              $response['description'] = 'Item details has been updated.';
					              // $response['description'] = str_singular($this->module_title).' updated successfully';

					            }
					            else
					            {
					              $this->GeneralService->save_notification($notification_arr);
				*/

				if ($is_update) {
					$this->GeneralService->save_notification($notification_arr);
					$response['description'] = 'Item details has been updated.';
				} 
				else
				{
					//$this->GeneralService->save_notification($notification_arr);
					$response['description'] = 'Item details has been added.';
				}

				$response['product_id'] = $product->id;
				//dd($this->module_url_path.'/edit'.base64_encode($product->id));
				$response['next_url'] = $this->module_url_path.'/edit/'.base64_encode($product->id);
				return response()->json($response);

				$response['description'] = 'Item details has been added.';

				// }

				$response['product_id'] = $product->id;
				$this->ElasticSearchService->initiate_index_product($product->id);
				return response()->json($response);

			} else {
				DB::rollback();
				$response['status'] = 'failure';
				$response['description'] = 'Error occurred while adding ' . str_singular($this->module_title);

				return response()->json($response);
			}
		} catch (Exception $e) {
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function storeStyleAndDiemensions(Request $request) {
		
		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		//dd($request->all());
		try {
			$is_update = false;

			$arr_rules = $db_rows_id_arr = $user_removed_id_arr = $user_row_id = [];

			$form_data = $request->all();
			$json_data = json_encode($form_data);

			$arr_rules = [
				// 'optionName'       => 'required',
				'product_id' => 'required',
			];

			/*$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';

				$response['description'] = 'Form validations failed, please check form fields.';

				return response()->json($response);
			}*/

			$optionName = '';
			if (isset($form_data['optionName']) && !empty($form_data['optionName'])) {
				$optionName = $form_data['optionName'];
			}

			DB::beginTransaction();
			$product_id = $form_data['product_id'];
			$optionName = $optionName;
			$is_up = $form_data['is_up'];
			$option_arr = $request->input('option');
			$sku_arr = $form_data['sku'];
			$color_arr = $form_data['color'];
			$product_multiple_image = isset($form_data['product_multiple_image'])  ? $form_data['product_multiple_image'] : "";

			
			//dd($sku_arr);
			//insert new records
			if (isset($sku_arr) && count($sku_arr) > 0) {
				foreach ($sku_arr as $key => $value) {
					$product_arr = [];
					//upload product image and get path
					$new_product_img_file_path = '';

					/*                    $product_image = $form_data['product_image'][$key];
            */
					$img = $form_data['product_image'][$key];
					// $product_image = Image::make($img)->resize(800, 800);

					$product_image = Image::make($img);


					if ($form_data['product_image'][$key] != null) {
						//Validation for product image
						$file_extension = strtolower($form_data['product_image'][$key]->getClientOriginalExtension());

						if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
							$arr_response['status'] = 'FAILURE';
							$arr_response['description'] = 'Invalid product image, please try again.';
							return response()->json($response);
						}

						/* $new_product_img_file_path = $product_image->store('product_image');*/
						$path = storage_path('app/product_image/product_img_thumb');
						$img_name = date('mdYHis') . uniqid() . '.' . $file_extension;
						/*dd($img_name);*/
						$product_img_file = $product_image->save($path . '/' . $img_name);

						$original_product_img_path = $img->store('product_image');

						$new_product_img_file_path = 'product_image/' . $img_name;

					}

					$product_arr = [
						'option_type' => isset($optionName) ? $optionName : null,
						'product_id' => $product_id,
						'option' => isset($form_data['option'][$key]) ? $form_data['option'][$key] : null,
						'sku' => isset($form_data['sku'][$key]) ? $form_data['sku'][$key] : '0',
						'color' => isset($form_data['color'][$key]) ? $form_data['color'][$key] : '',
						'weight' => isset($form_data['weight'][$key]) ? $form_data['weight'][$key] : '0',
						'length' => isset($form_data['length'][$key]) ? $form_data['length'][$key] : '0',
						'width' => isset($form_data['width'][$key]) ? $form_data['width'][$key] : '0',
						'height' => isset($form_data['height'][$key]) ? $form_data['height'][$key] : '0',
						'image_thumb' => $img_name,
						'image' => $original_product_img_path,
						'sku_product_description' => isset($form_data['sku_product_description'][$key]) ? $form_data['sku_product_description'][$key] : '',
						'product_min_qty' => isset($form_data['min_quantity'][$key]) ? $form_data['min_quantity'][$key] : '0',

					];

					$sku_no = $form_data['sku'][$key];
					$color = $form_data['color'][$key];
					$quantity = $form_data['quantity'][$key];

					$product_inventory_arr =
						[
						'product_id' => $product_id,
						'sku_no' => $sku_no,
						'quantity' => $quantity,
						'user_id' => $loggedInUserId,

					];

					

					//dd($is_up);
					if ($is_up != "true") {

						$final_size_arr = array();
						$size_id_arr = isset($form_data['size_id'.$key]) ? $form_data['size_id'.$key] : array();
						$size_inventory = isset($form_data['size_inventory'.$key]) ? $form_data['size_inventory'.$key] : array();

						if(count($size_id_arr)>0){
							foreach ($size_id_arr as $size => $val) {
									if($size_id_arr[$size] && $size_inventory[$size]){
										$product_sizes_arr = [
															'product_id' => $product_id,
															'sku_no' => $sku_no,
															'size_id' => $size_id_arr[$size],
															'size_inventory' => $size_inventory[$size],
														];
										$create_product_size_data = $this->ProductSizesModel->create($product_sizes_arr);
									}
							
								}
							
						}

						$is_new_product_store = $this->ProductDetailsModel->create($product_arr);

						$last_inserted_product_detail_id = $is_new_product_store->id;

						
						$create_inventory_data = $this->ProductInventoryModel->create($product_inventory_arr);

					



						/* Insert multiple image to product_multiple_image table by Harshada on date 22 Dec 2020 */					
						
						$original_product_img_path_mul = "";
					
						if (isset($product_multiple_image[$key]) && count($product_multiple_image[$key]) > 0) {

						foreach ($product_multiple_image[$key] as $mul_img_key => $mul_img_value) {

							if (isset($product_multiple_image[$key][$mul_img_key]) && $product_multiple_image[$key][$mul_img_key] != null) {								

								// Create directory if not exists
								if (!file_exists(storage_path('app/product_multiple_image'))) {
								    mkdir(storage_path('app/product_multiple_image'), 0777, true);
								}

								$img_mul = $product_multiple_image[$key][$mul_img_key];

								// $product_image_mul = Image::make($img_mul)->resize(800, 800);

								$product_image_mul = Image::make($img_mul);

							
								$file_extension_mul = strtolower($product_multiple_image[$key][$mul_img_key]->getClientOriginalExtension());	

								// Create directory if not exists
								if (!file_exists(storage_path('app/product_multiple_image/product_img_thumb'))) {
								    mkdir(storage_path('app/product_multiple_image/product_img_thumb'), 0777, true);
								}

								$path_mul = storage_path('app/product_multiple_image');

								$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
								
								$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

								$original_product_img_path_mul = $img_mul->store('product_multiple_image');

								$new_product_img_file_path_mul = 'product_multiple_image/' . $img_name_mul;

							}

						$product_mul_img_arr = [						
								'product_id' 		=> $product_id,	
								'product_detail_id' => $last_inserted_product_detail_id,						
								'sku' 				=> isset($form_data['sku'][$key]) ? $form_data['sku'][$key] : '0',	
								'color' 				=> isset($form_data['color'][$key]) ? $form_data['color'][$key] : '',
								'product_image' 			=> $original_product_img_path_mul
						];

						
						$is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);
						}
					}

						


						$arr_event = [];
						$arr_event['ACTION'] = 'ADD';
						$arr_event['MODULE_ID'] = $is_new_product_store->id;
						$arr_event['MODULE_TITLE'] = $this->module_title;
						$arr_event['USER_ID'] = $loggedInUserId;

						$this->save_activity($arr_event);
					} else {
						$is_new_product_store = $this->ProductDetailsModel->where('product_id', $product_id)->update($product_arr);
					}
				}
			}

			$update_arr = [];

			if (isset($form_data['style_is_draft']) && $form_data['style_is_draft'] == 'yes') {
				$is_draft = 1;
				$update_arr['is_draft'] = $is_draft;
			}
			// else
			// {
			//     $is_draft = 0;
			// }

			//delete empty entry records from product_details table
          	$this->ProductDetailsModel->where('product_id',$product_id)->Where('sku','=','')->delete();


			$update_arr['product_complete_status'] = 2;


			//update product complete status
			$this->BaseModel->where('id', $product_id)->update($update_arr);

			if (isset($is_new_product_store)) {
				DB::commit();
				$response['status'] = 'success';

				$response['description'] = str_singular($this->module_title) . ' style and dimensions details has been added.';

				$response['product_id'] = $product_id;

				return response()->json($response);
			} else {
				DB::rollback();
				$response['status'] = 'failure';
				$response['description'] = 'Error occurred while adding ' . str_singular($this->module_title) . ' style and dimensions details.';

				return response()->json($response);
			}

		} catch (Exception $e) {
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function storeAdditionalImages(Request $request) {

		$form_data      = $request->all();
		$loggedInUserId = 0;

		$user = Sentinel::check();
		if($user)
		{	
		 $loggedInUserId = $user->id;
	    }
		

		$product_img_file_path = $product_life_style_image_path = $product_packaging_image_path = '';
		$images_arr = [];

		$is_update = false;
		$product_id = $form_data['product_id'];
		$complete_status = get_product_complete_status($product_id);
		// dd($complete_status);
		$product_image = isset($form_data['product_image']) ? $form_data['product_image'] : null;
		$life_style_image = isset($form_data['lifestyle_image']) ? $form_data['lifestyle_image'] : null;
		$packaging_image = isset($form_data['packaging_image']) ? $form_data['packaging_image'] : null;


		$is_click_on_storeProduct = isset($form_data['is_click_on_storeProduct']) ? $form_data['is_click_on_storeProduct'] : 0;
			$is_click_on_update_style_and_dimension = isset($form_data['is_click_on_update_style_and_dimension']) ? $form_data['is_click_on_update_style_and_dimension'] : 0;
			$is_click_on_store_additional_images = isset($form_data['is_click_on_store_additional_images']) ? $form_data['is_click_on_store_additional_images'] : 0;
			$is_click_on_update_product_dategory = isset($form_data['is_click_on_update_product_dategory']) ? $form_data['is_click_on_update_product_dategory'] : 0;

		/* if(!isset($form_data['old_additional_prod_image']) && $form_data['old_additional_prod_image'] == null && $form_data['old_additional_prod_image'] =='')
			        {
			            $arr_response['status']       = 'FAILURE';
			            $arr_response['description']  = 'Form validations fails, Please check all fields';

			            return response()->json($arr_response);
			        }

			        if(!isset($form_data['old_lifestyle_image']) && $form_data['old_lifestyle_image'] == null && $form_data['old_lifestyle_image'] =='')
			        {
			            $arr_response['status']       = 'FAILURE';
			            $arr_response['description']  = 'Form validations fails, Please check all fields';

			            return response()->json($arr_response);
			        }

			        if(!isset($form_data['old_packaging_image']) && $form_data['old_packaging_image'] == null && $form_data['old_packaging_image'] =='')
			        {
			            $arr_response['status']       = 'FAILURE';
			            $arr_response['description']  = 'Form validations fails, Please check all fields';

			            return response()->json($arr_response);
		*/

		try
		{
			DB::beginTransaction();

			if ($product_image != null) {
				//Validation for product image
				$file_extension = strtolower($product_image->getClientOriginalExtension());

				if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
					$arr_response['status'] = 'FAILURE';

					$arr_response['description'] = 'Invalid product image, please try again.';

					return response()->json($response);
				}

				$product_img_file_path = $product_image->store('product_image');
				/*$store_iamge = */
			} else {
				$product_img_file_path = isset($form_data['old_product_image']) ? $form_data['old_product_image'] : null;
			}

			if ($life_style_image != null) {
				//Validation for product image
				$file_extension = strtolower($life_style_image->getClientOriginalExtension());

				if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
					$arr_response['status'] = 'FAILURE';

					$arr_response['description'] = 'Invalid product image, please try again.';

					return response()->json($response);
				}

				$product_life_style_image_path = $life_style_image->store('product_image');
			} else {
				$product_life_style_image_path = isset($form_data['old_lifestyle_image']) ? $form_data['old_lifestyle_image'] : null;
			}

			if ($packaging_image != null) {
				//Validation for product image
				$file_extension = strtolower($packaging_image->getClientOriginalExtension());

				if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
					$arr_response['status'] = 'FAILURE';

					$arr_response['description'] = 'Invalid product image, please try again.';

					return response()->json($response);
				}

				$product_packaging_image_path = $packaging_image->store('product_image');
			} else {
				$product_packaging_image_path = isset($form_data['old_packaging_image']) ? $form_data['old_packaging_image'] : null;
			}

			//check is product image already uploaded or not
			$is_images_exist = $this->ProductImagesModel->where('product_id', $product_id)
				->count() > 0;

			if ($is_images_exist) {
				$is_update = true;
			}

			/* --------------------------------------------------------------------------------
				                Code to insert updated column in table - product_updated_by_vendor
				                To see updated columns in admin login
				                By Harshada Kothmire
				                On date 07 Sept 202
			*/
			if ($is_update == true) {
				$res_already_updated_by_vendor = $this->ProductUpdatedByVendorModel->where('product_id', $product_id)->get()->toArray();

				$updatedColumnsArray = array();
				$updateNewColumnsArray = array();
				$ProductUpdatedArr = array();
				$updateNewColumns = "";

				if (!empty($res_already_updated_by_vendor)) {

					$updatedColumnsArray = isset($res_already_updated_by_vendor[0]['update_columns']) ? json_decode($res_already_updated_by_vendor[0]['update_columns'], true) : [];

					if (isset($updatedColumnsArray) && count($updatedColumnsArray) > 0) {
						foreach ($updatedColumnsArray as $det) {
							array_push($updateNewColumnsArray, $det);
						}
					}

				}
				/* ------------------ Form 1 Columns------------------------------------------------*/
				$res_product_data = $this->ProductImagesModel->where('product_id', $product_id)->get()->toArray();

				// $update_main_image = ($res_product_data[0]['product_image'] != $product_img_file_path && !in_array('product_main_image', $updatedColumnsArray)) ? array_push($updateNewColumnsArray,'product_main_image') : (in_array('product_main_image', $updatedColumnsArray)) ? array_push($updateNewColumnsArray,'product_main_image') : "";

				// $update_lifestyle_image = ($res_product_data[0]['lifestyle_image'] != $product_life_style_image_path && !in_array('lifestyle_image', $updatedColumnsArray)) ? array_push($updateNewColumnsArray,'lifestyle_image') : (in_array('lifestyle_image', $updatedColumnsArray)) ? array_push($updateNewColumnsArray,'lifestyle_image') : "";

				// $update_packaging_image = ($res_product_data[0]['packaging_image'] != $product_packaging_image_path && !in_array('packaging_image', $updatedColumnsArray)) ? array_push($updateNewColumnsArray,'packaging_image') : (in_array('packaging_image', $updatedColumnsArray)) ? array_push($updateNewColumnsArray,'packaging_image') : "";

				if ($res_product_data[0]['product_image'] != $product_img_file_path) {
					array_push($updateNewColumnsArray, 'product_main_image');
				}
				if ($res_product_data[0]['lifestyle_image'] != $product_life_style_image_path) {
					array_push($updateNewColumnsArray, 'lifestyle_image');
				}
				if ($res_product_data[0]['packaging_image'] != $product_packaging_image_path) {
					array_push($updateNewColumnsArray, 'packaging_image');
				}

				/* ------------------ Form 1 columns------------------------------------------------*/

				if (!empty($updateNewColumnsArray)) {
					$updateNewColumns = json_encode(array_unique($updateNewColumnsArray));
				}

				if (!empty($res_already_updated_by_vendor)) {
					$ProductUpdatedArr['update_columns'] = $updateNewColumns;
					$ProductUpdatedArr['updated_at'] = date("Y-m-d H:i:s");
					$this->ProductUpdatedByVendorModel->where('product_id', $product_id)->update($ProductUpdatedArr);
				} else {
					$ProductUpdatedArr['product_id'] = $product_id;
					$ProductUpdatedArr['vendor_id'] = isset($loggedInUserId) ? $loggedInUserId : 0;
					$ProductUpdatedArr['update_columns'] = $updateNewColumns;
					$ProductUpdatedArr['created_at'] = date("Y-m-d H:i:s");
					if ($updateNewColumns) {
						$this->ProductUpdatedByVendorModel->create($ProductUpdatedArr);
					}
				}

			}

			$product_images = $this->ProductImagesModel->firstOrNew(['product_id' => $product_id]);

			$product_images->product_id = $product_id;
			$product_images->product_image = $product_img_file_path;
			$product_images->lifestyle_image = $product_life_style_image_path;
			$product_images->packaging_image = $product_packaging_image_path;

			$changes = $product_images->getDirty();

			$is_store = $product_images->save();
			if ($is_store) {
				$update_arr = [];

				if (isset($form_data['images_is_draft']) && $form_data['images_is_draft'] == 'yes') {
					$is_draft = 1;
					$update_arr['is_draft'] = $is_draft;
				}
				// else
				// {
				//     $is_draft = 0;
				// }

				//update product complete status

				if (count($changes) == 0) {
					$this->BaseModel->where('id', $product_id)->update($update_arr);
					$this->ElasticSearchService->initiate_index_product($product_id);

				} else {
					$update_arr['is_active'] = 2;
					$update_arr['remark'] = Null;

					$this->BaseModel->where('id', $product_id)->update($update_arr);
					$this->ElasticSearchService->initiate_index_product($product_id);
				}

				$arr_event = [];
				if ($is_update == false) {
					$arr_event['ACTION'] = 'ADD';
					$arr_event['MODULE_ID'] = $product_images->id;
				} else {
					$arr_event['ACTION'] = 'EDIT';
					$arr_event['MODULE_ID'] = $product_id;
				}

				$arr_event['MODULE_TITLE'] = $this->module_title;
				$arr_event['USER_ID']      = $loggedInUserId;

				// $arr_event['MODULE_DATA'] = $json_data;

				$this->save_activity($arr_event);
				DB::commit();
				$arr_response['status'] = 'SUCCESS';
				$arr_response['description'] = str_singular($this->module_title) . ' additional images has been added.';
				$arr_response['product_id'] = $product_id;

				return response()->json($arr_response);
			} else {
				DB::rollback();
				$arr_response['status'] = 'FAILURE';

				$arr_response['description'] = 'Something went wrong, please try again.';

				return response()->json($arr_response);
			}
		} catch (Exception $e) {
			DB::rollback();
			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function storeCategories(Request $request) {

		$form_data = $request->all();

		
		$loggedInUserId = 0;

		$user = Sentinel::check();

		if($user)
		{	
		  $loggedInUserId = $user->id;
	    }

		try
		{
			$arr_rules = [
				           'category_product_id' => 'required',
				           'category_id' => 'required',
				
			            ];

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';

				$response['description'] = 'Form validations failed, please check form fields.';

				return response()->json($response);
			}

			DB::beginTransaction();
			$product_id = $form_data['category_product_id'];

			if (isset($form_data['sub_category']) && count($form_data['sub_category']) > 0) {
				foreach ($form_data['sub_category'] as $key => $value) {
					$arr_data = [];

					$arr_data['category_id'] = $form_data['category_id'];
					$arr_data['sub_category_id'] = $value;
					$arr_data['product_id'] = $product_id;

					$this->ProductsSubCategoriesModel->create($arr_data);
				}
			}
			if (isset($form_data['sub_category3']) && $form_data['sub_category3'] && count($form_data['sub_category3']) > 0) 
			{
				foreach ($form_data['sub_category3'] as $key => $value) 
				{
					$arr_data['category_id'] = isset($form_data['category_id']) ? $form_data['category_id'] : '';
					$arr_data['third_sub_category_id'] = $value;
					$arr_data['product_id'] = $product_id;

					$data = $this->ProductsSubCategoriesModel->create($arr_data);
					
				}
			}
			if (isset($form_data['sub_category4']) && $form_data['sub_category4'] && count($form_data['sub_category4']) > 0) 
			{
				foreach ($form_data['sub_category4'] as $key => $value) 
				{
					$arr_data['category_id'] = isset($form_data['category_id']) ? $form_data['category_id'] : '';
					$arr_data['fourth_sub_category_id'] = $value;
					$arr_data['product_id'] = $product_id;

					$data = $this->ProductsSubCategoriesModel->create($arr_data);
				}
			}

			// else
			// {
			// 	$arr_data['category_id'] = $form_data['category_id'];
			// 	$arr_data['product_id'] = $product_id;
			// 	$is_created = $this->ProductsSubCategoriesModel->create($arr_data);
			// }

			$update_arr = [];

			$update_arr['category_id'] = $form_data['category_id'];

			if (isset($form_data['category_is_draft']) && $form_data['category_is_draft'] == 'yes') {
				$is_draft = 1;
				$update_arr['is_draft'] = $is_draft;
				$update_arr['category_id'] = $form_data['category_id'];

				// dd($update_arr);
			}
			// else
			// {
			//     $is_draft = 0;
			// }
// dd($form_data);
			$update_arr['product_complete_status'] = 4;
			$update_arr['is_active'] = 2;

			//update product complete status

			$this->BaseModel->where('id', $product_id)->update($update_arr);
			$arr_data = [];

			/*------------commented from front add product form for shipping and other charges start---------*/

			// $arr_data['shipping_charges'] = $form_data['shipping_charges'];
			// if ($form_data['shipping_charges'] && $form_data['shipping_type']) {

			// 	$arr_data['shipping_charges'] = $form_data['shipping_charges'];

			// 	if ($form_data['shipping_type'] == '1') {

			// 		$arr_data['shipping_type'] = $form_data['shipping_type'];
			// 		$arr_data['minimum_amount_off'] = $form_data['free_ship_min_amount'];

			// 	} elseif ($form_data['shipping_type'] == '2') {

			// 		$arr_data['shipping_type'] = $form_data['shipping_type'];
			// 		$arr_data['minimum_amount_off'] = $form_data['free_ship_min_amount'];
			// 		$arr_data['off_type_amount'] = $form_data['%_off'];

			// 	} elseif ($form_data['shipping_type'] == '3') {

			// 		$arr_data['shipping_type'] = $form_data['shipping_type'];
			// 		$arr_data['minimum_amount_off'] = $form_data['free_ship_min_amount'];
			// 		$arr_data['off_type_amount'] = $form_data['$_off'];

			// 	}

			// }

			$this->BaseModel->where('id', $product_id)->update($arr_data);

			// if (isset($form_data['product_discount_type'])) {
			// 	$arr_data = [];
			// 	$arr_data['prodduct_dis_type'] = $form_data['product_discount_type'];
			// 	$arr_data['product_dis_min_amt'] = $form_data['free_product_dis_min_amount'];

			// 	if (isset($form_data['product_discount_type']) && $form_data['product_discount_type'] == '1') {
			// 		$arr_data['product_discount'] = $form_data['product_%_off'];

			// 	} elseif (isset($form_data['product_discount_type']) && $form_data['product_discount_type'] == '2') {
			// 		$arr_data['product_discount'] = $form_data['product_$_off'];
			// 	}

			// 	$this->BaseModel->where('id', $product_id)->update($arr_data);
			// }

			/*------------commented from front add product form for shipping and other charges end---------*/

			DB::commit();
			$response['status']      = 'success';
			// $response['description'] = str_singular($this->module_title) . ' categories and shipping charges added successfully';
			$response['description'] = str_singular($this->module_title) . ' categories added successfully';


            /* Send notification to admin for product add */

		    $admin_id      = get_admin_id();

		    //get name of product
		    $prouct_name = $this->ProductsModel->where('id',$product_id)->pluck('product_name')->first();


			$first_name   = isset($user->first_name) ? $user->first_name : "";
			$last_name    = isset($user->last_name) ? $user->last_name : "";
			$product_name = isset($prouct_name) ? $prouct_name : '';

			$product_detail_url = url('/admin/products/view/' . base64_encode($product_id));

			$notification_arr                  = [];
			$notification_arr['from_user_id']  = $loggedInUserId;
			$notification_arr['to_user_id']    = $admin_id;
			$notification_arr['description']   = 'New Product ' . '"' . $product_name . '"' . ' added by ' . $first_name . ' ' . $last_name;
			$notification_arr['title']         = 'New Product';
			$notification_arr['type']          = 'admin';
			$notification_arr['link']          = $product_detail_url;

			$this->GeneralService->save_notification($notification_arr);


         	$response['next_url'] = $this->module_url_path;

			return response()->json($response);

		}
		catch (Exception $e)
		{
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}


	}

	public function view($enc_product_id = false) {
		$product_id = base64_decode($enc_product_id);

		$product_arr = [];
		$third_sub_categories_arr = [];
		$complete_status = get_product_complete_status($product_id);
		$mp_arr = array('productDetails.inventory_details', 'productImages', 'productSubCategories.SubcategoryDetails', 'productSubCategories.ThirdSubcategoryDetails' , 'productSubCategories.FourthSubcategoryDetails' ,'productSubCategories.categoryDetails',
		'productDetails.productMultipleImages', 'categoryDetails', 'userDetails', 'brand_details');
		
		$product_arr = $this->ProductService->get_productDetails_from_productId($product_id, $mp_arr);
		
		$product_sub_cat_arr = $this->ProductsSubCategoriesModel->where('category_id', $product_arr['category_id'])
																->where('product_id', $product_arr['id'])
																->get();
		$sub_categories_arr = [];
		$third_sub_category_arr = [];
		$fourth_sub_categories_arr = [];
		
		foreach($product_sub_cat_arr as $categories_arr)
		{
			
			if($categories_arr['fourth_sub_category_id'] != 0)
			{
				$fourth_sub_categories_arr[] = $this->FourthSubCategoryModel->where('is_active',1)
																		->where('id',$categories_arr['fourth_sub_category_id'])
																		->whereTranslation('locale',$this->locale)
																		->get()
																		->toArray();
			}
		    if($categories_arr['third_sub_category_id'] != 0)
			{
				$third_sub_category_arr[] = $this->ThirdSubCategoryModel->where('is_active', 1)
																		->whereTranslation('locale', $this->locale)
																		->where('id',$categories_arr['third_sub_category_id'])
																		->get()
																		->toArray();
										
				
			}
			if($categories_arr['sub_category_id'] != 0){
				// dd("fdgfgfgfg");
				$sub_categories_arr[] = $this->SubCategoryModel->where('is_active',1)
																->where('id',$categories_arr['sub_category_id'])
																->whereTranslation('locale',$this->locale)
																->get()				
																->toArray();

			}
		}
		if (empty($product_arr)) {
			Flash::error('Something went wrong,please try again.');
			return redirect()->back();
		}
		// dd($product_arr);
		$this->arr_view_data['product_arr'] = $product_arr;
		$this->arr_view_data['sub_categories_arr'] = isset($sub_categories_arr)?$sub_categories_arr : '';
		$this->arr_view_data['third_sub_category_arr'] = isset($third_sub_category_arr)?$third_sub_category_arr : '';
		$this->arr_view_data['fourth_sub_categories_arr'] = isset($fourth_sub_categories_arr)?$fourth_sub_categories_arr : '';
		$this->arr_view_data['module_url_path'] = $this->module_url_path;
		$this->arr_view_data['page_title'] = "View " . str_singular($this->module_title) . ' Details';
		$this->arr_view_data['module_title'] = str_plural($this->module_title);
		$this->arr_view_data['product_default_img_path'] = $this->product_default_img_path;

		// dd($this->arr_view_data);
		return view($this->module_view_folder . '.view', $this->arr_view_data);
	}

	public function product_details($enc_product_id = false) {
		$product_id = base64_decode($enc_product_id);

		$product_arr = [];

		$product_obj = $this->BaseModel->with([
			'productDetails.inventory_details',
			'productImages',
			'productSubCategories.SubcategoryDetails'])->where('id', $product_id)
			->first();

		if ($product_obj) {
			$product_arr = $product_obj->toArray();
			// dd($product_arr);
			$subcategories_id_arr = [];

			$subcategories_id_arr = array_column($product_arr['product_sub_categories'], 'sub_category_id');

			$product_arr['subcat_id'] = json_encode($subcategories_id_arr);

			$response['status'] = 'success';
			$response['description'] = str_singular($this->module_title) . ' has been got.';
			$response['product_arr'] = $product_arr;

			return response()->json($response);
		} else {
			$response['status'] = 'failure';
			$response['description'] = 'Something went wrong, please try again.';

			return response()->json($response);
		}
	}

	public function updateProduct(Request $request) {

		try
		{
			$is_update = false;
			$arr_rules = [];
			$loggedInUserId = 0;
			$form_data = $request->all();
			$json_data = json_encode($form_data);

			if ($request->has('product_id')) {
				$is_update = true;
			}

			$product_id = $request->input('product_id');
			$complete_status = get_product_complete_status($product_id);
			$arr_rules = [
				'product_name' => 'required',
				'case_qty' => 'required',
				'unit_wholsale_price' => 'required',
				//'retail_price'            => 'required',
				'product_description' => 'required',
				'restock_days' => 'required'
			];

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';
				$response['description'] = 'Form validation failed, please check form fields.';
				return response()->json($response);
			}


			/* Check Get a quote is active or not */
			$get_quote_status = $this->MakerModel
						  ->select('is_get_a_quote')
						  ->where('user_id',$loggedInUserId)								 
						  ->get()->toArray();

			$chk_quote_status = 1;
			if(!empty($get_quote_status)){
				$chk_quote_status = isset($get_quote_status[0]['is_get_a_quote']) ? $get_quote_status[0]['is_get_a_quote'] : 0;
			}							

			if($chk_quote_status == 0){
				if ($form_data['unit_wholsale_price'] != "" && $form_data['unit_wholsale_price'] == 0) {
					$response['status'] = 'warning';
					$response['description'] = 'price should not be 0.';
					return response()->json($response);
				}
			}

			/*  if($form_data['unit_retail_price']!="" && $form_data['unit_retail_price']==0)
				            {
				               $response['status']      = 'warning';
				               $response['description'] = 'Unit retail price should not be 0.';
				               return response()->json($response);
			*/

			// if (isset($form_data['retail_price']) && $form_data['retail_price'] != "") {
			// 	if ($form_data['unit_wholsale_price'] > $form_data['retail_price']) {
			// 		$response['status'] = 'warning';
			// 		$response['description'] = 'Unit wholesale price should be less than unit retail price ';
			// 		return response()->json($response);
			// 	}
			// }

			DB::beginTransaction();

			$user = Sentinel::check();

			if ($user) {
				$loggedInUserId = $user->id;
			}

			/* Check if product already exists with given name*/
			$is_duplicate = $this->BaseModel->where('product_name', $form_data['product_name']);

			if ($is_update) {
				$is_duplicate->where('id', '<>', $product_id);
			}

			$is_duplicate = $is_duplicate->count() > 0;

		/*	if ($is_duplicate) {
				$response['status'] = 'warning';
				$response['description'] = str_singular($this->module_title) . ' already exist with ' . $form_data['product_name'] . ' name';

				return response()->json($response);
			}
*/
			//upload product image and get path
			$product_img_file_path = '';
			$product_image = isset($form_data['product_primary_image']) ? $form_data['product_primary_image'] : null;

			if ($product_image != null) {
				//Validation for product image
				$file_extension = strtolower($product_image->getClientOriginalExtension());

				if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
					$arr_response['status'] = 'FAILURE';
					$arr_response['description'] = 'Invalid product image, please try again.';

					return response()->json($response);
				}

				$product_img_file_path = $product_image->store('product_image');

				//delete old image from directory in any available
				$old_product_img = isset($form_data['old_product_image']) ? $form_data['old_product_image'] : '';

				$unlink_old_img_path = $this->product_img . $old_product_img;

				if (file_exists($unlink_old_img_path)) {
					@unlink($unlink_old_img_path);
				}
			} else {
				$product_img_file_path = $form_data['old_product_image'];
			}

			if (isset($form_data['is_best_seller'])) {
				$is_best_seller = $form_data['is_best_seller'];
			} else {
				$is_best_seller = 0;
			}

			if (isset($form_data['is_tester_available'])) {
				$is_tester_available = $form_data['is_tester_available'];
			} else {
				$is_tester_available = 0;
			}

			$product = $this->BaseModel->firstOrNew(['id' => $product_id]);
			/*dd($product_img_file_path);
                */
			$product->product_name = $form_data['product_name'];
			$product->available_qty = $form_data['case_qty'];
			$product->unit_wholsale_price = $form_data['unit_wholsale_price'];
			// $product->retail_price = $form_data['retail_price'];
			$product->description = $form_data['product_description'];
			$product->product_image = $product_img_file_path;
			$product->user_id = $loggedInUserId;
			// $product->is_active               = 1;
			$product->is_active = 2;
			$product->is_best_seller = $is_best_seller;
			$product->is_tester_available = $is_tester_available;
			$product->previous_category_status = '0';
			$product->case_quantity = $form_data['case_qty'];
			$product->restock_days = $form_data['restock_days'];

			if ($complete_status < 1) {
				$product->product_complete_status = 1;
			} else {
				$product->product_complete_status = $complete_status;
			}
			$is_store = $product->save();

			if ($is_store) {
				$arr_event = [];
				if ($is_update == false) {
					$arr_event['ACTION'] = 'ADD';
					$arr_event['MODULE_ID'] = $product->id;
				} else {
					$arr_event['ACTION'] = 'EDIT';
					$arr_event['MODULE_ID'] = $product_id;
				}

				$arr_event['MODULE_TITLE'] = $this->module_title;
				$arr_event['USER_ID']      = $loggedInUserId;

				$this->save_activity($arr_event);

				DB::commit();
				$response['status'] = 'success';

				if ($is_update) {
					$response['description'] = str_singular($this->module_title) . ' has been updated.';
				} else {
					$response['description'] = str_singular($this->module_title) . ' has been added.';
				}

				$response['product_id'] = $product->id;
				return response()->json($response);
			} else {
				DB::rollback();
				$response['status'] = 'failure';
				$response['description'] = 'Error occurred while adding ' . str_singular($this->module_title);

				return response()->json($response);
			}

		} catch (Exception $e) {
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function updateStyleAndDiemensions_bk(Request $request) {

		$inventory_remove_id_arr = '';
		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		try {
			$is_update = false;

			$arr_rules = $db_rows_id_arr = $user_removed_id_arr = $user_row_id = [];

			$form_data = $request->all();
			//dd($form_data);
			//echo "<pre>";print_r($form_data);die;
			$json_data = json_encode($form_data);

			$is_click_on_storeProduct = isset($form_data['is_click_on_storeProduct']) ? $form_data['is_click_on_storeProduct'] : 0;
			$is_click_on_update_style_and_dimension = isset($form_data['is_click_on_update_style_and_dimension']) ? $form_data['is_click_on_update_style_and_dimension'] : 0;
			$is_click_on_store_additional_images = isset($form_data['is_click_on_store_additional_images']) ? $form_data['is_click_on_store_additional_images'] : 0;
			$is_click_on_update_product_dategory = isset($form_data['is_click_on_update_product_dategory']) ? $form_data['is_click_on_update_product_dategory'] : 0;

			$arr_rules = [
				// 'optionName' => 'required',
				'product_id' => 'required',
			];

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';

				$response['description'] = 'Form validations failed, please check form fields.';

				return response()->json($response);
			}

			$optionName = isset($form_data['optionName']) ? $form_data['optionName'] : '';

			DB::beginTransaction();
			$product_id = $form_data['product_id'];
			$complete_status = get_product_complete_status($product_id);
			$optionName = $optionName;
			// $old_option_arr = $request->input('old_option');
			// $new_option_arr = $request->input('new_option');
			$old_sku_arr = $request->input('old_sku');
			$new_sku_arr = $request->input('new_sku');

			//delete those records that removed from user
			//get all rows id
			$db_rows_id_arr = $this->ProductDetailsModel
				->select('id')
				->where('product_id', $product_id)
				->get()->toArray();

			$inventory_rows_id_arr = $this->ProductInventoryModel
				->select('sku_no')
				->where('product_id', $product_id)
				->get()->toArray();

			$db_rows_id_arr = array_column($db_rows_id_arr, 'id');
			$inventory_rows_id_arr = array_column($inventory_rows_id_arr, 'sku_no');

			//get user selected rows id
			if (isset($old_sku_arr) && count($old_sku_arr) > 0) {
				$user_row_id = array_keys($old_sku_arr);

				$user_removed_id_arr = array_diff($db_rows_id_arr, $user_row_id);

				$removed_sku_arr = array_values($old_sku_arr);

				$inventory_remove_id_arr = array_diff($inventory_rows_id_arr, $removed_sku_arr);

				$this->ProductDetailsModel->whereIn('id', $user_removed_id_arr)->delete();

				$this->ProductInventoryModel->whereIn('sku_no', $inventory_remove_id_arr)->delete();

			}

			//update old records
			$updatedColumnsArray = array();
			$updateNewColumnsArray = array();
			$ProductUpdatedArr = array();
			$updateNewColumns = "";

			/* --------------------------------------------------------------------------------
				                Code to insert updated column in table - product_updated_by_vendor
				                To see updated columns in admin login
				                By Harshada Kothmire
				                On date 07 Sept 202
			*/
			$res_already_updated_by_vendor = $this->ProductUpdatedByVendorModel->where('product_id', $product_id)->get()->toArray();

			if (!empty($res_already_updated_by_vendor)) {
				$updatedColumnsArray = json_decode($res_already_updated_by_vendor[0]['update_productDetails_columns'], true);
				//echo "<pre>";print_r( $updatedColumnsArray);die;

				if (isset($updatedColumnsArray)) {
					foreach ($updatedColumnsArray as $k => $det) {
						//array_push($updateNewColumnsArray,$det);
						$updateNewColumnsArray[$k] = $det;
					}
				}
			}

			DB::beginTransaction();
			/* Delete product images using product id */
			$this->ProductMultipleImagesModel->where('product_id', $product_id)->delete();	
			//echo "<pre>";print_r( $old_sku_arr);die;

			$arr_key = 0;
			$is_update_multiple_image = 0;
			if (isset($old_sku_arr) && count($old_sku_arr) > 0) {

				foreach ($old_sku_arr as $old_key => $value) {

					$old_product_arr = [];
					//upload product image and get path
					$old_product_img_file_path = '';

					$old_product_image = isset($form_data['old_product_image'][$old_key]) ? $form_data['old_product_image'][$old_key] : null;

					if ($old_product_image != null) {
						//Validation for product image
						$file_extension = strtolower($old_product_image->getClientOriginalExtension());

						if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
							$arr_response['status'] = 'FAILURE';
							$arr_response['description'] = 'Invalid product image, please try again.';
							return response()->json($response);
						}

						$old_product_img_file_path = $old_product_image->store('product_image');

						$img = $form_data['old_product_image'][$old_key];
						// $product_image = Image::make($img)->resize(800, 800);

						$product_image = Image::make($img);


						$path = storage_path('app/product_image/product_img_thumb');
						$img_name = date('mdYHis') . uniqid() . '.' . $file_extension;
						$product_img_thumb_file = $product_image->save($path . '/' . $img_name);

					} else {
						$parts = explode("/", $form_data['db_product_image'][$old_key]);
						$img_name = end($parts);
						$old_product_img_file_path = $form_data['db_product_image_original'][$old_key];
					}

					$old_product_arr = [
						'option_type' => isset($optionName) ? $optionName : null,

						'product_id' => $product_id,

						'option' => isset($form_data['old_option'][$old_key]) ? $form_data['old_option'][$old_key] : null,

						'sku' => isset($form_data['old_sku'][$old_key]) ? $form_data['old_sku'][$old_key] : '0',

						'weight' => isset($form_data['old_weight'][$old_key]) ? $form_data['old_weight'][$old_key] : '0',

						'length' => isset($form_data['old_length'][$old_key]) ? $form_data['old_length'][$old_key] : '0',

						'width' => isset($form_data['old_width'][$old_key]) ? $form_data['old_width'][$old_key] : '0',

						'height' => isset($form_data['old_height'][$old_key]) ? $form_data['old_height'][$old_key] : '0',

						'sku_product_description' => isset($form_data['old_sku_product_description'][$old_key]) ? $form_data['old_sku_product_description'][$old_key] : Null,

						'image' => $old_product_img_file_path,

						'image_thumb' => $img_name,

						'product_min_qty' => isset($form_data['old_min_quantity'][$old_key]) ? $form_data['old_min_quantity'][$old_key] : '0',
					];

					$OldProductDetailsModel = $this->ProductDetailsModel->firstOrNew(['id' => $old_key])->toArray();
					/* --------------------------------------------------------------------------------
						                        Code to created array which grid is updated by vendor - product_updated_by_vendor
						                        By Harshada Kothmire
						                        On date 08 Sept 202
					*/

					if (!empty($OldProductDetailsModel)) {
						if ($OldProductDetailsModel['sku'] != $form_data['old_sku'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'sku';
						}
						if ($OldProductDetailsModel['sku_product_description'] != $form_data['old_sku_product_description'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'sku_product_description';
						}

						if ($OldProductDetailsModel['product_min_qty'] != $form_data['old_min_quantity'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'product_min_qty';
						}

						if ($OldProductDetailsModel['image'] != $old_product_img_file_path) {
							$updateNewColumnsArray[$old_key][] = 'product_detail_image';
						}
					}

					$is_old_product_store = $this->ProductDetailsModel->where('id', $old_key)
						->update($old_product_arr);

					$NewProductDetailsModel = $this->ProductDetailsModel->firstOrNew(['id' => $old_key])->toArray();

					unset($OldProductDetailsModel['created_at']);
					unset($OldProductDetailsModel['updated_at']);
					unset($NewProductDetailsModel['created_at']);
					unset($NewProductDetailsModel['updated_at']);

					$arraysAreEqual = ($OldProductDetailsModel === $NewProductDetailsModel);
					//dd($form_data);
					$product_inventory_arr = [
						'product_id' => $product_id,
						'sku_no' => $form_data['old_sku'][$old_key],
						'quantity' => $form_data['old_quantity'][$old_key],
						'user_id' => $loggedInUserId,
					];

					/* --------------------------------------------------------------------------------
						                        Code to created array which grid is updated by vendor - product_updated_by_vendor
						                        By Harshada Kothmire
						                        On date 08 Sept 2020
					*/
					$res_inventory_rows_id_arr = $this->ProductInventoryModel
						->where('sku_no', $form_data['old_sku'][$old_key])
						->where('product_id', $product_id)
						->get()->toArray();

					if (!empty($res_inventory_rows_id_arr)) {
						if ($res_inventory_rows_id_arr[0]['quantity'] != $form_data['old_quantity'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'quantity';
						}
					}

					$pro_quantity = $this->ProductInventoryModel->where('sku_no', $form_data['old_sku'][$old_key])->update($product_inventory_arr);

					if ($pro_quantity == 0) {

						$pro_quantity = $this->ProductInventoryModel->where('sku_no', $form_data['old_sku'][$old_key])->create($product_inventory_arr);

						$res_inventory_rows_id_arr = $this->ProductInventoryModel
							->where('sku_no', $form_data['old_sku'][$old_key])
							->where('product_id', $product_id)
							->get()->toArray();
						//echo "<pre>";print_r( $res_inventory_rows_id_arr );die;
						if (!empty($res_inventory_rows_id_arr)) {
							//$updateNewColumnsArray[$old_key][] = 'quantity';
						}
					}

					if (!$arraysAreEqual) {
						$this->BaseModel->where('id', $product_id)->update(['is_active' => 2, 'remark' => Null]);
						
					}

					
					if (count($user_removed_id_arr) != 0 || count($inventory_remove_id_arr) != 0 || count($new_sku_arr) != 0) {

						$this->BaseModel->where('id', $product_id)->update(['is_active' => 2, 'remark' => Null]);
						
					}



						/* update multiple image to product_multiple_image table by Harshada on date 22 Dec 2020 */						
						$original_product_img_path_mul = "";
						$product_multiple_image = isset($form_data['old_product_multiple_image']) ? $form_data['old_product_multiple_image'] : "";	

						try
						{
							
							/* For existing images */							
							if (isset($form_data['db_old_product_multiple_image'][$old_key]) && count($form_data['db_old_product_multiple_image'][$old_key]) > 0) 
							{
								foreach ($form_data['db_old_product_multiple_image'][$old_key] as $already_added_mul_img_key => $already_added_mul_img_value) 
								{
										$old_product_img_file_path = $form_data['db_old_product_multiple_image'][$old_key][$already_added_mul_img_key];

										/*New uploaded image*/
										$old_product_multiple_image = isset($form_data['db_old_product_multiple_image'][$old_key][$already_added_mul_img_value]) ? $form_data['db_old_product_multiple_image'][$old_key][$already_added_mul_img_value] : "";


										if ($old_product_multiple_image != null) 
										{								

											// Create directory if not exists
											if (!file_exists(storage_path('app/product_multiple_image'))) 
											{
											    mkdir(storage_path('app/product_multiple_image'), 0777, true);
											}

											$img_mul = $product_multiple_image[$old_key][$already_added_mul_img_value];

											// $product_image_mul = Image::make($img_mul)->resize(800, 800);

											$product_image_mul = Image::make($img_mul);

										
											$file_extension_mul = strtolower($product_multiple_image[$old_key][$already_added_mul_img_value]->getClientOriginalExtension());	

											
											$path_mul = storage_path('app/product_multiple_image');

											$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
											
											$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

											$old_product_img_file_path = $img_mul->store('product_multiple_image');

											$new_product_img_file_path_mul = $img_name_mul;

										} 



										$product_mul_img_arr = [						
												'product_id' 		=> $product_id,	
												'product_detail_id' => $old_key,						
												'sku' 				=> isset($form_data['old_sku'][$old_key]) ? $form_data['old_sku'][$old_key] : '0',
												'product_image' 	=> $old_product_img_file_path
										];
							
										 /* add new images */
										  $is_update_multiple_image = 1;
							             $is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);
								}
							}

							/*  For only new added images  */
							// dd($product_multiple_image[$old_key]);
							$product_multiple_image_old_new = isset($form_data['old_product_multiple_image']) ? $form_data['old_product_multiple_image'] : "";	

							//dd($product_multiple_image_old_new);
							if (isset($product_multiple_image_old_new[$old_key]) && count($product_multiple_image_old_new[$old_key]) > 0) 
							{

									foreach ($product_multiple_image_old_new[$old_key] as $mul_img_key => $mul_img_value) 
									{

										$old_product_multiple_image = isset($form_data['old_product_multiple_image'][$old_key][$mul_img_key]) ? $form_data['old_product_multiple_image'][$old_key][$mul_img_key] : null;


										if ($old_product_multiple_image != null) 
										{								

											// Create directory if not exists
											if (!file_exists(storage_path('app/product_multiple_image'))) 
											{
											    mkdir(storage_path('app/product_multiple_image'), 0777, true);
											}

											$img_mul = $product_multiple_image_old_new[$old_key][$mul_img_key];

											// $product_image_mul = Image::make($img_mul)->resize(800, 800);

											$product_image_mul = Image::make($img_mul);

										
											$file_extension_mul = strtolower($product_multiple_image_old_new[$old_key][$mul_img_key]->getClientOriginalExtension());	

											/*// Create directory if not exists
											if (!file_exists(storage_path('app/product_multiple_image/product_img_thumb'))) 
											{
											    mkdir(storage_path('app/product_multiple_image/product_img_thumb'), 0777, true);
											}*/

											$path_mul = storage_path('app/product_multiple_image');

											$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
											
											$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

											$old_product_img_file_path = $img_mul->store('product_multiple_image');

											$new_product_img_file_path_mul = $img_name_mul;

										} 

										$product_mul_img_arr = [						
												'product_id' 		=> $product_id,	
												'product_detail_id' => $old_key,						
												'sku' 				=> isset($form_data['old_sku'][$old_key]) ? $form_data['old_sku'][$old_key] : '0',	
												'product_image' 			=> $old_product_img_file_path
										];
										//dump($product_mul_img_arr);
										 /* add new images */
										 $is_update_multiple_image = 1;
							             $is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);

									}
						    }
							
							DB::commit();
					}
						catch(Exception $e)
						{
							DB::rollback();
							$response['status'] = 'failure';
							$response['description'] = $e->getMessage();

							return response()->json($response);

						}
					




					$arr_key++;







					$arr_event = [];
					$arr_event['ACTION'] = 'EDIT';
					$arr_event['MODULE_ID'] = $old_key;
					$arr_event['MODULE_TITLE'] = $this->module_title;
					$arr_event['USER_ID']      = $loggedInUserId;


					$this->save_activity($arr_event);
				}
				//echo "<pre>";print_r($updatedColumnsArray);die;

			}

			//insert new records			
			if (isset($new_sku_arr) && count($new_sku_arr) > 0) {
				foreach ($new_sku_arr as $key => $value) {
					$product_arr = [];
					//upload product image and get path
					$new_product_img_file_path = '';

					$product_image = isset($form_data['new_product_image'][$key]) ? $form_data['new_product_image'][$key] : null;

					if ($product_image != null) {
						//Validation for product image
						$file_extension = strtolower($product_image->getClientOriginalExtension());

						if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
							$arr_response['status'] = 'FAILURE';
							$arr_response['description'] = 'Invalid product image, please try again.';
							return response()->json($response);
						}

						$new_product_img_file_path = $product_image->store('product_image');
						$img = isset($form_data['new_product_image'][$key]) ? $form_data['new_product_image'][$key] : null;

						// $product_image = Image::make($img)->resize(800, 800);

						$product_image = Image::make($img);


						$path = storage_path('app/product_image/product_img_thumb');
						$img_name = date('mdYHis') . uniqid() . '.' . $file_extension;
						$product_img_thumb_file = $product_image->save($path . '/' . $img_name);
					}

					$product_arr = [
						'option_type' => isset($optionName) ? $optionName : null,

						'product_id' => $product_id,

						'option' => isset($form_data['new_option'][$key]) ? $form_data['new_option'][$key] : Null,

						'sku' => isset($form_data['new_sku'][$key]) ? $form_data['new_sku'][$key] : '0',

						'weight' => isset($form_data['new_weight'][$key]) ? $form_data['new_weight'][$key] : '0',

						'length' => isset($form_data['new_length'][$key]) ? $form_data['new_length'][$key] : '0',

						'width' => isset($form_data['new_width'][$key]) ? $form_data['new_width'][$key] : '0',

						'height' => isset($form_data['new_height'][$key]) ? $form_data['new_height'][$key] : '0',

						'sku_product_description' => isset($form_data['new_sku_product_description'][$key]) ? $form_data['new_sku_product_description'][$key] : Null,

						'image' => $new_product_img_file_path,

						'image_thumb' => $img_name,

						'product_min_qty' => isset($form_data['new_min_quantity'][$key]) ? $form_data['new_min_quantity'][$key] : '0',
					];

					$new_product_inventory_arr = [
						'product_id' => $product_id,
						'sku_no' => $form_data['new_sku'][$key],
						'quantity' => $form_data['new_quantity'][$key],
						'user_id' => $loggedInUserId,
					];

					$pro_quantity = $this->ProductInventoryModel->create($new_product_inventory_arr);

					$is_new_product_store = $this->ProductDetailsModel->create($product_arr);
					//echo "<pre>".$is_new_product_store->id;die;

					$arr_event = [];
					$arr_event['ACTION'] = 'ADD';
					$arr_event['MODULE_ID'] = $is_new_product_store->id;
					$arr_event['MODULE_TITLE'] = $this->module_title;
					$arr_event['USER_ID']      = $loggedInUserId;


					$this->save_activity($arr_event);

					/* --------------------------------------------------------------------------------
						                        Code to created array which grid is updated by vendor - product_updated_by_vendor
						                        By Harshada Kothmire
						                        On date 08 Sept 202
					*/
					$last_inserted_det_id = 0;
					$res_last_inserted_id = $this->ProductDetailsModel
						->where('sku', $form_data['new_sku'][$key])
						->where('product_id', $product_id)
						->get()->toArray();
					if (!empty($res_last_inserted_id)) {
						$last_inserted_det_id = $res_last_inserted_id[0]['id'];
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'quantity';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'sku_product_description';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'product_min_qty';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'sku';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'product_detail_image';

					}




					/*  For only new added images  */
					$product_multiple_image_new = isset($form_data['old_product_multiple_image']) ? $form_data['old_product_multiple_image'] : "";	
					//echo "<pre>";print_r($product_multiple_image_new);die;
					
					if (isset($product_multiple_image_new[$arr_key]) && count($product_multiple_image_new[$arr_key]) > 0) 
					{
							 // echo 'in if';die;

							//echo "<pre>";print_r($product_multiple_image_new[$arr_key]);
							foreach ($product_multiple_image_new[$arr_key] as $mul_img_key => $mul_img_value) 
							{

								$old_product_multiple_image = isset($form_data['old_product_multiple_image'][$arr_key][$mul_img_key]) ? $form_data['old_product_multiple_image'][$arr_key][$mul_img_key] : null;


								if ($old_product_multiple_image != null) 
								{								

									// Create directory if not exists
									if (!file_exists(storage_path('app/product_multiple_image'))) 
									{
									    mkdir(storage_path('app/product_multiple_image'), 0777, true);
									}

									$img_mul = $product_multiple_image[$arr_key][$mul_img_key];

									// $product_image_mul = Image::make($img_mul)->resize(800, 800);

									$product_image_mul = Image::make($img_mul);

								
									$file_extension_mul = strtolower($product_multiple_image[$arr_key][$mul_img_key]->getClientOriginalExtension());	

									/*// Create directory if not exists
									if (!file_exists(storage_path('app/product_multiple_image/product_img_thumb'))) 
									{
									    mkdir(storage_path('app/product_multiple_image/product_img_thumb'), 0777, true);
									}*/

									$path_mul = storage_path('app/product_multiple_image');

									$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
									
									$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

									$old_product_img_file_path = $img_mul->store('product_multiple_image');

									$new_product_img_file_path_mul = $img_name_mul;

								} 

								$product_mul_img_arr = [						
										'product_id' 		=> $product_id,	
										'product_detail_id' => $last_inserted_det_id,	
										'sku' 				=> isset($form_data['new_sku'][$key]) ? $form_data['new_sku'][$key] : '0',	
										'product_image' 			=> $old_product_img_file_path
								];
									//dd($product_mul_img_arr);
								 /* add new images */
								 
					             $is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);
					            
							}// die;
				    }
				     $arr_key++;
				} //die;
			}
			
			if (!empty($updateNewColumnsArray)) {
				$updateNewColumns = json_encode($updateNewColumnsArray);
			}


			// dd($res_already_updated_by_vendor);
			if (!empty($res_already_updated_by_vendor)) {
				$ProductUpdatedArr['update_productDetails_columns'] = $updateNewColumns;
				$ProductUpdatedArr['updated_at'] = date("Y-m-d H:i:s");
				$this->ProductUpdatedByVendorModel->where('product_id', $product_id)->update($ProductUpdatedArr);

			} else {
				
				$ProductUpdatedArr['product_id'] = $product_id;
				$ProductUpdatedArr['vendor_id'] = isset($loggedInUserId) ? $loggedInUserId : 0;
				$ProductUpdatedArr['update_productDetails_columns'] = $updateNewColumns;
				$ProductUpdatedArr['created_at'] = date("Y-m-d H:i:s");
				if ($ProductUpdatedArr) {
					$insert_new = $this->ProductUpdatedByVendorModel->create($ProductUpdatedArr);				
				}
			}
			//update product complete status
			if ($complete_status <= 1) {
				// $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>2]);
				$this->BaseModel->where('id', $product_id)->update(['is_active' => 2, 'remark' => Null]);
			}

			if (isset($is_new_product_store)) {
				DB::commit();
				$response['status'] = 'success';
				$response['description'] = str_singular($this->module_title) . ' details has been added, click OK to proceed next.';

				$response['next_url'] = $this->module_url_path . '/additional_images/' . base64_encode($product_id);

				return response()->json($response);
			} elseif (isset($is_old_product_store)) {
				DB::commit();
				$response['status'] = 'success';
				$response['description'] = str_singular($this->module_title) . ' details has been updated, click OK to proceed next.';

				$response['next_url'] = $this->module_url_path . '/additional_images/' . base64_encode($product_id);

				return response()->json($response);
			} else {
				DB::rollback();
				$response['status'] = 'failure';
				$response['description'] = 'Error occurred while adding ' . str_singular($this->module_title);

				return response()->json($response);
			}

		} catch (Exception $e) {
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function updateStyleAndDiemensions(Request $request) {

		$inventory_remove_id_arr = '';
		$user = Sentinel::check();

		if ($user) {
			$loggedInUserId = $user->id;
		}

		try {
			$is_update = false;

			$arr_rules = $db_rows_id_arr = $user_removed_id_arr = $user_row_id = [];

			$form_data = $request->all();
			//dd($form_data);
			//echo "<pre>";print_r($form_data);die;
			$json_data = json_encode($form_data);

			$is_click_on_storeProduct = isset($form_data['is_click_on_storeProduct']) ? $form_data['is_click_on_storeProduct'] : 0;
			$is_click_on_update_style_and_dimension = isset($form_data['is_click_on_update_style_and_dimension']) ? $form_data['is_click_on_update_style_and_dimension'] : 0;
			$is_click_on_store_additional_images = isset($form_data['is_click_on_store_additional_images']) ? $form_data['is_click_on_store_additional_images'] : 0;
			$is_click_on_update_product_dategory = isset($form_data['is_click_on_update_product_dategory']) ? $form_data['is_click_on_update_product_dategory'] : 0;

			$arr_rules = [
				// 'optionName' => 'required',
				'product_id' => 'required',
			];

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';

				$response['description'] = 'Form validations failed, please check form fields.';

				return response()->json($response);
			}

			$optionName = isset($form_data['optionName']) ? $form_data['optionName'] : '';

			DB::beginTransaction();
			$product_id = $form_data['product_id'];
			$complete_status = get_product_complete_status($product_id);
			$optionName = $optionName;
			// $old_option_arr = $request->input('old_option');
			// $new_option_arr = $request->input('new_option');
			$old_sku_arr = $request->input('old_sku');
			$new_sku_arr = $request->input('new_sku');

			//delete those records that removed from user
			//get all rows id
			$db_rows_id_arr = $this->ProductDetailsModel
				->select('id')
				->where('product_id', $product_id)
				->get()->toArray();

			$inventory_rows_id_arr = $this->ProductInventoryModel
				->select('sku_no')
				->where('product_id', $product_id)
				->get()->toArray();

			$size_rows_id_arr = $this->ProductSizesModel
				->select('sku_no')
				->where('product_id', $product_id)
				->get()->toArray();

			$db_rows_id_arr = array_column($db_rows_id_arr, 'id');
			$inventory_rows_id_arr = array_column($inventory_rows_id_arr, 'sku_no');
			$size_rows_id_arr = array_column($size_rows_id_arr, 'sku_no');

			//get user selected rows id
			if (isset($old_sku_arr) && count($old_sku_arr) > 0) {
				$user_row_id = array_keys($old_sku_arr);

				$user_removed_id_arr = array_diff($db_rows_id_arr, $user_row_id);

				$removed_sku_arr = array_values($old_sku_arr);

				$inventory_remove_id_arr = array_diff($inventory_rows_id_arr, $removed_sku_arr);

				$size_remove_id_arr = array_diff($size_rows_id_arr, $removed_sku_arr);

				$this->ProductDetailsModel->whereIn('id', $user_removed_id_arr)->delete();

				$this->ProductInventoryModel->whereIn('sku_no', $inventory_remove_id_arr)->delete();
				
				$this->ProductSizesModel->whereIn('sku_no', $size_remove_id_arr)->delete();

			}



			//update old records
			$updatedColumnsArray = array();
			$updateNewColumnsArray = array();
			$ProductUpdatedArr = array();
			$updateNewColumns = "";

			/* --------------------------------------------------------------------------------
				                Code to insert updated column in table - product_updated_by_vendor
				                To see updated columns in admin login
				                By Harshada Kothmire
				                On date 07 Sept 202
			*/
			$res_already_updated_by_vendor = $this->ProductUpdatedByVendorModel->where('product_id', $product_id)->get()->toArray();

			if (!empty($res_already_updated_by_vendor)) {
				$updatedColumnsArray = json_decode($res_already_updated_by_vendor[0]['update_productDetails_columns'], true);
				//echo "<pre>";print_r( $updatedColumnsArray);die;

				if (isset($updatedColumnsArray)) {
					foreach ($updatedColumnsArray as $k => $det) {
						//array_push($updateNewColumnsArray,$det);
						$updateNewColumnsArray[$k] = $det;
					}
				}
			}

			DB::beginTransaction();
			/* Delete product images using product id */
			$this->ProductMultipleImagesModel->where('product_id', $product_id)->delete();	
			//echo "<pre>";print_r( $old_sku_arr);die;

			$arr_key = 0;
			$is_update_multiple_image = 0;
			if (isset($old_sku_arr) && count($old_sku_arr) > 0) {

				foreach ($old_sku_arr as $old_key => $value) {

					$old_product_arr = [];
					//upload product image and get path
					$old_product_img_file_path = '';

					$old_product_image = isset($form_data['old_product_image'][$old_key]) ? $form_data['old_product_image'][$old_key] : null;

					if ($old_product_image != null) {
						//Validation for product image
						$file_extension = strtolower($old_product_image->getClientOriginalExtension());

						if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
							$arr_response['status'] = 'FAILURE';
							$arr_response['description'] = 'Invalid product image, please try again.';
							return response()->json($response);
						}

						$old_product_img_file_path = $old_product_image->store('product_image');

						$img = $form_data['old_product_image'][$old_key];
						// $product_image = Image::make($img)->resize(800, 800);

						$product_image = Image::make($img);


						$path = storage_path('app/product_image/product_img_thumb');
						$img_name = date('mdYHis') . uniqid() . '.' . $file_extension;
						$product_img_thumb_file = $product_image->save($path . '/' . $img_name);

					} else {
						$parts = explode("/", $form_data['db_product_image'][$old_key]);
						$img_name = end($parts);
						$old_product_img_file_path = $form_data['db_product_image_original'][$old_key];
					}

					$old_product_arr = [
						'option_type' => isset($optionName) ? $optionName : null,

						'product_id' => $product_id,

						'option' => isset($form_data['old_option'][$old_key]) ? $form_data['old_option'][$old_key] : null,

						'sku' => isset($form_data['old_sku'][$old_key]) ? $form_data['old_sku'][$old_key] : '0',
						'color' => isset($form_data['old_color'][$old_key]) ? $form_data['old_color'][$old_key] : '',

						'weight' => isset($form_data['old_weight'][$old_key]) ? $form_data['old_weight'][$old_key] : '0',

						'length' => isset($form_data['old_length'][$old_key]) ? $form_data['old_length'][$old_key] : '0',

						'width' => isset($form_data['old_width'][$old_key]) ? $form_data['old_width'][$old_key] : '0',

						'height' => isset($form_data['old_height'][$old_key]) ? $form_data['old_height'][$old_key] : '0',

						'sku_product_description' => isset($form_data['old_sku_product_description'][$old_key]) ? $form_data['old_sku_product_description'][$old_key] : Null,

						'image' => $old_product_img_file_path,

						'image_thumb' => $img_name,

						'product_min_qty' => isset($form_data['old_min_quantity'][$old_key]) ? $form_data['old_min_quantity'][$old_key] : '0',
					];

					$OldProductDetailsModel = $this->ProductDetailsModel->firstOrNew(['id' => $old_key])->toArray();
					/* --------------------------------------------------------------------------------
						                        Code to created array which grid is updated by vendor - product_updated_by_vendor
						                        By Harshada Kothmire
						                        On date 08 Sept 202
					*/

					if (!empty($OldProductDetailsModel)) {
						if ($OldProductDetailsModel['sku'] != $form_data['old_sku'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'sku';
						}
						if ($OldProductDetailsModel['sku_product_description'] != $form_data['old_sku_product_description'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'sku_product_description';
						}

						if ($OldProductDetailsModel['product_min_qty'] != $form_data['old_min_quantity'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'product_min_qty';
						}

						if ($OldProductDetailsModel['image'] != $old_product_img_file_path) {
							$updateNewColumnsArray[$old_key][] = 'product_detail_image';
						}

						if ($OldProductDetailsModel['color'] != $form_data['old_color'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'color';
						}
					}

					$is_old_product_store = $this->ProductDetailsModel->where('id', $old_key)
						->update($old_product_arr);

					$NewProductDetailsModel = $this->ProductDetailsModel->firstOrNew(['id' => $old_key])->toArray();

					unset($OldProductDetailsModel['created_at']);
					unset($OldProductDetailsModel['updated_at']);
					unset($NewProductDetailsModel['created_at']);
					unset($NewProductDetailsModel['updated_at']);

					$arraysAreEqual = ($OldProductDetailsModel === $NewProductDetailsModel);
					//dd($form_data);
					$product_inventory_arr = [
						'product_id' => $product_id,
						'sku_no' => $form_data['old_sku'][$old_key],
						'quantity' => $form_data['old_quantity'][$old_key],
						'user_id' => $loggedInUserId,
					];


						/*Add Size Code Start*/
				$final_size_arr = array();
				$size_id_arr = isset($form_data['size_id'.$arr_key]) ? $form_data['size_id'.$arr_key] : array();
				$size_inventory = isset($form_data['size_inventory'.$arr_key]) ? $form_data['size_inventory'.$arr_key] : array();
				$product_size_id_arr = isset($form_data['product_size_id'.$arr_key]) ? $form_data['product_size_id'.$arr_key] : array();

				if(count($size_id_arr)>0){
					foreach ($size_id_arr as $size => $val) {
					if($size_id_arr[$size] && $size_inventory[$size]){
						$product_sizes_arr = [
												'product_id' => $product_id,
												'sku_no' => $form_data['old_sku'][$old_key],
												'size_id' => $size_id_arr[$size],
												'size_inventory' => $size_inventory[$size],
											];
						if($product_size_id_arr[$size] != ""){
							$update_product_size_data = $this->ProductSizesModel->where('id', $product_size_id_arr[$size])->update($product_sizes_arr);
						}else{
							$create_product_size_data = $this->ProductSizesModel->create($product_sizes_arr);
						}
					}
					
					}
					
				}

					/*Add Size Code End.*/

					/*-------Update Product Size Data Start -------------------*/
					/*$final_size_arr = array();
					$size_id_arr = isset($form_data['size_id'.$old_key]) ? $form_data['size_id'.$old_key] : array();
					$size_inventory = isset($form_data['size_inventory'.$old_key]) ? $form_data['size_inventory'.$old_key] : array();

					if(count($size_id_arr)>0){
						foreach ($size_id_arr as $size => $val) {
					
						$product_sizes_arr = [
												'product_id' => $product_id,
												'sku_no' => $sku_no,
												'size_id' => $size_id_arr[$size],
												'size_inventory' => $size_inventory[$size],
											];
							$create_product_size_data = $this->ProductSizesModel->create($product_sizes_arr);
							}
						
					}*/

					/*-------Update Product Size Data End -------------------*/

					/* --------------------------------------------------------------------------------
						                        Code to created array which grid is updated by vendor - product_updated_by_vendor
						                        By Harshada Kothmire
						                        On date 08 Sept 2020
					*/
					$res_inventory_rows_id_arr = $this->ProductInventoryModel
						->where('sku_no', $form_data['old_sku'][$old_key])
						->where('product_id', $product_id)
						->get()->toArray();

					if (!empty($res_inventory_rows_id_arr)) {
						if ($res_inventory_rows_id_arr[0]['quantity'] != $form_data['old_quantity'][$old_key]) {
							$updateNewColumnsArray[$old_key][] = 'quantity';
						}
					}

					$pro_quantity = $this->ProductInventoryModel->where('sku_no', $form_data['old_sku'][$old_key])->update($product_inventory_arr);

					if ($pro_quantity == 0) {

						$pro_quantity = $this->ProductInventoryModel->where('sku_no', $form_data['old_sku'][$old_key])->create($product_inventory_arr);

						$res_inventory_rows_id_arr = $this->ProductInventoryModel
							->where('sku_no', $form_data['old_sku'][$old_key])
							->where('product_id', $product_id)
							->get()->toArray();
						//echo "<pre>";print_r( $res_inventory_rows_id_arr );die;
						if (!empty($res_inventory_rows_id_arr)) {
							//$updateNewColumnsArray[$old_key][] = 'quantity';
						}
					}

				

					if (!$arraysAreEqual) {
						$this->BaseModel->where('id', $product_id)->update(['is_active' => 2, 'remark' => Null]);
						
					}

					
					if (count($user_removed_id_arr) != 0 || count($inventory_remove_id_arr) != 0 || count($new_sku_arr) != 0) {

						$this->BaseModel->where('id', $product_id)->update(['is_active' => 2, 'remark' => Null]);
						
					}



						/* update multiple image to product_multiple_image table by Harshada on date 22 Dec 2020 */						
						$original_product_img_path_mul = "";
						$product_multiple_image = isset($form_data['old_product_multiple_image']) ? $form_data['old_product_multiple_image'] : "";	

						try
						{
							
							/* For existing images */							
							if (isset($form_data['db_old_product_multiple_image'][$old_key]) && count($form_data['db_old_product_multiple_image'][$old_key]) > 0) 
							{
								foreach ($form_data['db_old_product_multiple_image'][$old_key] as $already_added_mul_img_key => $already_added_mul_img_value) 
								{
										$old_product_img_file_path = $form_data['db_old_product_multiple_image'][$old_key][$already_added_mul_img_key];

										/*New uploaded image*/
										$old_product_multiple_image = isset($form_data['db_old_product_multiple_image'][$old_key][$already_added_mul_img_value]) ? $form_data['db_old_product_multiple_image'][$old_key][$already_added_mul_img_value] : "";


										if ($old_product_multiple_image != null) 
										{								

											// Create directory if not exists
											if (!file_exists(storage_path('app/product_multiple_image'))) 
											{
											    mkdir(storage_path('app/product_multiple_image'), 0777, true);
											}

											$img_mul = $product_multiple_image[$old_key][$already_added_mul_img_value];

											// $product_image_mul = Image::make($img_mul)->resize(800, 800);

											$product_image_mul = Image::make($img_mul);

										
											$file_extension_mul = strtolower($product_multiple_image[$old_key][$already_added_mul_img_value]->getClientOriginalExtension());	

											
											$path_mul = storage_path('app/product_multiple_image');

											$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
											
											$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

											$old_product_img_file_path = $img_mul->store('product_multiple_image');

											$new_product_img_file_path_mul = $img_name_mul;

										} 



										$product_mul_img_arr = [						
												'product_id' 		=> $product_id,	
												'product_detail_id' => $old_key,						
												'sku' 				=> isset($form_data['old_sku'][$old_key]) ? $form_data['old_sku'][$old_key] : '0',
												'product_image' 	=> $old_product_img_file_path
										];
							
										 /* add new images */
										  $is_update_multiple_image = 1;
							             $is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);
								}
							}

							/*  For only new added images  */
							// dd($product_multiple_image[$old_key]);
							$product_multiple_image_old_new = isset($form_data['old_product_multiple_image']) ? $form_data['old_product_multiple_image'] : "";	

							//dd($product_multiple_image_old_new);
							if (isset($product_multiple_image_old_new[$old_key]) && count($product_multiple_image_old_new[$old_key]) > 0) 
							{

									foreach ($product_multiple_image_old_new[$old_key] as $mul_img_key => $mul_img_value) 
									{

										$old_product_multiple_image = isset($form_data['old_product_multiple_image'][$old_key][$mul_img_key]) ? $form_data['old_product_multiple_image'][$old_key][$mul_img_key] : null;


										if ($old_product_multiple_image != null) 
										{								

											// Create directory if not exists
											if (!file_exists(storage_path('app/product_multiple_image'))) 
											{
											    mkdir(storage_path('app/product_multiple_image'), 0777, true);
											}

											$img_mul = $product_multiple_image_old_new[$old_key][$mul_img_key];

											// $product_image_mul = Image::make($img_mul)->resize(800, 800);

											$product_image_mul = Image::make($img_mul);

										
											$file_extension_mul = strtolower($product_multiple_image_old_new[$old_key][$mul_img_key]->getClientOriginalExtension());	

											/*// Create directory if not exists
											if (!file_exists(storage_path('app/product_multiple_image/product_img_thumb'))) 
											{
											    mkdir(storage_path('app/product_multiple_image/product_img_thumb'), 0777, true);
											}*/

											$path_mul = storage_path('app/product_multiple_image');

											$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
											
											$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

											$old_product_img_file_path = $img_mul->store('product_multiple_image');

											$new_product_img_file_path_mul = $img_name_mul;

										} 

										$product_mul_img_arr = [						
												'product_id' 		=> $product_id,	
												'product_detail_id' => $old_key,						
												'sku' 				=> isset($form_data['old_sku'][$old_key]) ? $form_data['old_sku'][$old_key] : '0',	
												'product_image' 			=> $old_product_img_file_path
										];
										//dump($product_mul_img_arr);
										 /* add new images */
										 $is_update_multiple_image = 1;
							             $is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);

									}
						    }
							
							DB::commit();
					}
						catch(Exception $e)
						{
							DB::rollback();
							$response['status'] = 'failure';
							$response['description'] = $e->getMessage();

							return response()->json($response);

						}
					




					$arr_key++;







					$arr_event = [];
					$arr_event['ACTION'] = 'EDIT';
					$arr_event['MODULE_ID'] = $old_key;
					$arr_event['MODULE_TITLE'] = $this->module_title;
					$arr_event['USER_ID']      = $loggedInUserId;


					$this->save_activity($arr_event);
				}
				//echo "<pre>";print_r($updatedColumnsArray);die;

			}

			//insert new records			
			if (isset($new_sku_arr) && count($new_sku_arr) > 0) {
				foreach ($new_sku_arr as $key => $value) {
					$product_arr = [];
					//upload product image and get path
					$new_product_img_file_path = '';

					$product_image = isset($form_data['new_product_image'][$key]) ? $form_data['new_product_image'][$key] : null;

					if ($product_image != null) {
						//Validation for product image
						$file_extension = strtolower($product_image->getClientOriginalExtension());

						if (!in_array($file_extension, ['jpg', 'png', 'jpeg'])) {
							$arr_response['status'] = 'FAILURE';
							$arr_response['description'] = 'Invalid product image, please try again.';
							return response()->json($response);
						}

						$new_product_img_file_path = $product_image->store('product_image');
						$img = isset($form_data['new_product_image'][$key]) ? $form_data['new_product_image'][$key] : null;

						// $product_image = Image::make($img)->resize(800, 800);

						$product_image = Image::make($img);


						$path = storage_path('app/product_image/product_img_thumb');
						$img_name = date('mdYHis') . uniqid() . '.' . $file_extension;
						$product_img_thumb_file = $product_image->save($path . '/' . $img_name);
					}

					$product_arr = [
						'option_type' => isset($optionName) ? $optionName : null,

						'product_id' => $product_id,

						'option' => isset($form_data['new_option'][$key]) ? $form_data['new_option'][$key] : Null,

						'sku' => isset($form_data['new_sku'][$key]) ? $form_data['new_sku'][$key] : '0',

						'weight' => isset($form_data['new_weight'][$key]) ? $form_data['new_weight'][$key] : '0',

						'length' => isset($form_data['new_length'][$key]) ? $form_data['new_length'][$key] : '0',

						'width' => isset($form_data['new_width'][$key]) ? $form_data['new_width'][$key] : '0',

						'height' => isset($form_data['new_height'][$key]) ? $form_data['new_height'][$key] : '0',

						'sku_product_description' => isset($form_data['new_sku_product_description'][$key]) ? $form_data['new_sku_product_description'][$key] : Null,

						'image' => $new_product_img_file_path,

						'image_thumb' => $img_name,

						'product_min_qty' => isset($form_data['new_min_quantity'][$key]) ? $form_data['new_min_quantity'][$key] : '0',
					];



					$new_product_inventory_arr = [
						'product_id' => $product_id,
						'sku_no' => $form_data['new_sku'][$key],
						'quantity' => $form_data['new_quantity'][$key],
						'user_id' => $loggedInUserId,
					];

					$pro_quantity = $this->ProductInventoryModel->create($new_product_inventory_arr);

					$is_new_product_store = $this->ProductDetailsModel->create($product_arr);
					/*Add Size Code Start*/
					$final_size_arr = array();
						$size_id_arr = isset($form_data['size_id'.$key]) ? $form_data['size_id'.$key] : array();
						$size_inventory = isset($form_data['size_inventory'.$key]) ? $form_data['size_inventory'.$key] : array();

						if(count($size_id_arr)>0){
							foreach ($size_id_arr as $size => $val) {
							if($size_id_arr[$size] && $size_inventory[$size]){
								$product_sizes_arr = [
														'product_id' => $product_id,
														'sku_no' => $sku_no,
														'size_id' => $size_id_arr[$size],
														'size_inventory' => $size_inventory[$size],
													];
									$create_product_size_data = $this->ProductSizesModel->create($product_sizes_arr);
								}
							}
							
						}
						/*Add Size Code End.*/
					//echo "<pre>".$is_new_product_store->id;die;

					$arr_event = [];
					$arr_event['ACTION'] = 'ADD';
					$arr_event['MODULE_ID'] = $is_new_product_store->id;
					$arr_event['MODULE_TITLE'] = $this->module_title;
					$arr_event['USER_ID']      = $loggedInUserId;


					$this->save_activity($arr_event);

					/* --------------------------------------------------------------------------------
						                        Code to created array which grid is updated by vendor - product_updated_by_vendor
						                        By Harshada Kothmire
						                        On date 08 Sept 202
					*/
					$last_inserted_det_id = 0;
					$res_last_inserted_id = $this->ProductDetailsModel
						->where('sku', $form_data['new_sku'][$key])
						->where('product_id', $product_id)
						->get()->toArray();
					if (!empty($res_last_inserted_id)) {
						$last_inserted_det_id = $res_last_inserted_id[0]['id'];
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'quantity';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'sku_product_description';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'product_min_qty';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'sku';
						$updateNewColumnsArray[$res_last_inserted_id[0]['id']][] = 'product_detail_image';

					}




					/*  For only new added images  */
					$product_multiple_image_new = isset($form_data['old_product_multiple_image']) ? $form_data['old_product_multiple_image'] : "";	
					//echo "<pre>";print_r($product_multiple_image_new);die;
					
					if (isset($product_multiple_image_new[$arr_key]) && count($product_multiple_image_new[$arr_key]) > 0) 
					{
							 // echo 'in if';die;

							//echo "<pre>";print_r($product_multiple_image_new[$arr_key]);
							foreach ($product_multiple_image_new[$arr_key] as $mul_img_key => $mul_img_value) 
							{

								$old_product_multiple_image = isset($form_data['old_product_multiple_image'][$arr_key][$mul_img_key]) ? $form_data['old_product_multiple_image'][$arr_key][$mul_img_key] : null;


								if ($old_product_multiple_image != null) 
								{								

									// Create directory if not exists
									if (!file_exists(storage_path('app/product_multiple_image'))) 
									{
									    mkdir(storage_path('app/product_multiple_image'), 0777, true);
									}

									$img_mul = $product_multiple_image[$arr_key][$mul_img_key];

									// $product_image_mul = Image::make($img_mul)->resize(800, 800);

									$product_image_mul = Image::make($img_mul);

								
									$file_extension_mul = strtolower($product_multiple_image[$arr_key][$mul_img_key]->getClientOriginalExtension());	

									/*// Create directory if not exists
									if (!file_exists(storage_path('app/product_multiple_image/product_img_thumb'))) 
									{
									    mkdir(storage_path('app/product_multiple_image/product_img_thumb'), 0777, true);
									}*/

									$path_mul = storage_path('app/product_multiple_image');

									$img_name_mul = date('mdYHis') . uniqid() . '.' . $file_extension_mul;
									
									$product_img_file = $product_image_mul->save($path_mul . '/' . $img_name_mul);

									$old_product_img_file_path = $img_mul->store('product_multiple_image');

									$new_product_img_file_path_mul = $img_name_mul;

								} 

								$product_mul_img_arr = [						
										'product_id' 		=> $product_id,	
										'product_detail_id' => $last_inserted_det_id,	
										'sku' 				=> isset($form_data['new_sku'][$key]) ? $form_data['new_sku'][$key] : '0',	
										'product_image' 			=> $old_product_img_file_path
								];
									//dd($product_mul_img_arr);
								 /* add new images */
								 
					             $is_new_mul_product_store = $this->ProductMultipleImagesModel->create($product_mul_img_arr);
					            
							}// die;
				    }
				     $arr_key++;
				} //die;
			}
			
			if (!empty($updateNewColumnsArray)) {
				$updateNewColumns = json_encode($updateNewColumnsArray);
			}


			// dd($res_already_updated_by_vendor);
			if (!empty($res_already_updated_by_vendor)) {
				$ProductUpdatedArr['update_productDetails_columns'] = $updateNewColumns;
				$ProductUpdatedArr['updated_at'] = date("Y-m-d H:i:s");
				$this->ProductUpdatedByVendorModel->where('product_id', $product_id)->update($ProductUpdatedArr);

			} else {
				
				$ProductUpdatedArr['product_id'] = $product_id;
				$ProductUpdatedArr['vendor_id'] = isset($loggedInUserId) ? $loggedInUserId : 0;
				$ProductUpdatedArr['update_productDetails_columns'] = $updateNewColumns;
				$ProductUpdatedArr['created_at'] = date("Y-m-d H:i:s");
				if ($ProductUpdatedArr) {
					$insert_new = $this->ProductUpdatedByVendorModel->create($ProductUpdatedArr);				
				}
			}
			//update product complete status
			if ($complete_status <= 1) {
				// $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>2]);
				$this->BaseModel->where('id', $product_id)->update(['is_active' => 2, 'remark' => Null]);
			}

			if (isset($is_new_product_store)) {
				DB::commit();
				$response['status'] = 'success';
				$response['description'] = str_singular($this->module_title) . ' details has been added, click OK to proceed next.';

				$response['next_url'] = $this->module_url_path . '/additional_images/' . base64_encode($product_id);

				return response()->json($response);
			} elseif (isset($is_old_product_store)) {
				DB::commit();
				$response['status'] = 'success';
				$response['description'] = str_singular($this->module_title) . ' details has been updated, click OK to proceed next.';

				$response['next_url'] = $this->module_url_path . '/additional_images/' . base64_encode($product_id);

				return response()->json($response);
			} else {
				DB::rollback();
				$response['status'] = 'failure';
				$response['description'] = 'Error occurred while adding ' . str_singular($this->module_title);

				return response()->json($response);
			}

		} catch (Exception $e) {
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function update_product_category(Request $request) {

		$form_data = $request->all();
		//echo "<pre>";print_r($form_data);die;
		$updatedColumnsArray = array();
		$updateNewColumnsArray = array();
		$ProductUpdatedArr = array();
		$updateNewColumns = "";

		$is_click_on_storeProduct = isset($form_data['is_click_on_storeProduct']) ? $form_data['is_click_on_storeProduct'] : 0;
			$is_click_on_update_style_and_dimension = isset($form_data['is_click_on_update_style_and_dimension']) ? $form_data['is_click_on_update_style_and_dimension'] : 0;
			$is_click_on_store_additional_images = isset($form_data['is_click_on_store_additional_images']) ? $form_data['is_click_on_store_additional_images'] : 0;
			$is_click_on_update_product_dategory = isset($form_data['is_click_on_update_product_dategory']) ? $form_data['is_click_on_update_product_dategory'] : 0;
		try
		{
			$product_id = $form_data['product_id'];
			$complete_status = get_product_complete_status($product_id);
			$arr_rules = [
				'category_id' => 'required',
				'product_id' => 'required',
			];

			$old_product_arr = $this->BaseModel->where('id', $product_id)->first()->toArray();

			unset($old_product_arr['created_at']);
			unset($old_product_arr['updated_at']);

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				$response['status'] = 'warning';

				$response['description'] = 'Form validations failed, please check form fields.';

				return response()->json($response);
			}

			DB::beginTransaction();

			//delete old categories
			$this->ProductsSubCategoriesModel->where('product_id', $product_id)->delete();

			$res_product_data = $this->ProductsModel->where('id', $product_id)->get()->toArray();

			$InsertSubCatInTable = array();
			$InsertThirdSubCatInTable = array();
			$InsertFourthSubCatInTable = array();

			if (isset($form_data['sub_category']))
			{
				// dd($form_data);
				$arr_data = [];
				if($form_data['sub_category'] != "" && $form_data['sub_category'] != null)
				{
					
					foreach ($form_data['sub_category'] as $key => $value) {
						$arr_data['category_id'] = isset($form_data['category_id']) ? $form_data['category_id'] : '';
						$arr_data['sub_category_id'] = $value;
						$arr_data['product_id'] = $form_data['product_id'];

						$data = $this->ProductsSubCategoriesModel->create($arr_data);
						$this->BaseModel->where('id', $product_id)->update(['product_complete_status' => 4, 'category_id' => $form_data['category_id']]);

						// $this->BaseModel->where('id',$product_id)->update(['is_active'=>2]);
						array_push($InsertSubCatInTable, $value);
						array_push($updateNewColumnsArray, 'subcategory_id');
					}
					
				}
				if (isset($form_data['sub_category3']) && $form_data['sub_category3'] && count($form_data['sub_category3']) > 0) 
				{
					foreach ($form_data['sub_category3'] as $key => $value) 
					{
						$arr_data['category_id'] = isset($form_data['category_id']) ? $form_data['category_id'] : '';
						$arr_data['third_sub_category_id'] = $value;
						$arr_data['product_id'] = $form_data['product_id'];

						$data = $this->ProductsSubCategoriesModel->create($arr_data);
						$this->BaseModel->where('id', $product_id)->update(['product_complete_status' => 4, 'category_id' => $form_data['category_id']]);
						
						// $this->BaseModel->where('id',$product_id)->update(['is_active'=>2]);
	
						array_push($InsertThirdSubCatInTable, $value);
						array_push($updateNewColumnsArray, 'third_subcategory_id');
					}
				}
				if (isset($form_data['sub_category4']) && $form_data['sub_category4'] && count($form_data['sub_category4']) > 0) 
				{
					foreach ($form_data['sub_category4'] as $key => $value) 
					{
						$arr_data['category_id'] = isset($form_data['category_id']) ? $form_data['category_id'] : '';
						$arr_data['fourth_sub_category_id'] = $value;
						$arr_data['product_id'] = $form_data['product_id'];
	
						$data = $this->ProductsSubCategoriesModel->create($arr_data);
						$this->BaseModel->where('id', $product_id)->update(['product_complete_status' => 4, 'category_id' => $form_data['category_id']]);
						// $this->BaseModel->where('id',$product_id)->update(['is_active'=>2]);
	
						array_push($InsertFourthSubCatInTable, $value);
						array_push($updateNewColumnsArray, 'fourth_sub_category_id');
					}
				}
				
			}
			else
			{
				$arr_data['category_id'] = isset($form_data['category_id']) ? $form_data['category_id'] : '';
				$arr_data['product_id'] = $form_data['product_id'];

				$this->ProductsSubCategoriesModel->create($arr_data);
				$this->BaseModel->where('id', $product_id)
					                ->update([
					                	      'product_complete_status' => 4, 
					                	      'category_id' => $form_data['category_id']
					                	    ]);
			}

			/*------------commented from front edit product form for shipping and other charges start---------*/

			// if (isset($form_data['shipping_charges'])) {
			// 	$arr_data = [];
			// 	$arr_data['shipping_charges'] = $form_data['shipping_charges'];

			// 	// if (isset($form_data['shipping_type']) && $res_product_data[0]['shipping_type'] != $form_data['shipping_type']) {
			// 	// 	array_push($updateNewColumnsArray, 'shipping_type');
			// 	// }

			// 	// if ($res_product_data[0]['shipping_charges'] != $form_data['shipping_charges']) {
			// 	// 	array_push($updateNewColumnsArray, 'shipping_charges');
			// 	// }
			// 	if (isset($form_data['shipping_type']) && $form_data['shipping_type'] == '1') {

			// 		$arr_data['shipping_type'] = isset($form_data['shipping_type']) ? $form_data['shipping_type'] : '';
			// 		$arr_data['minimum_amount_off'] = isset($form_data['free_ship_min_amount']) ? $form_data['free_ship_min_amount'] : '';

			// 	} elseif ($form_data['shipping_type'] == '2') {

			// 		$arr_data['shipping_type'] = isset($form_data['shipping_type']) ? $form_data['shipping_type'] : '';
			// 		$arr_data['minimum_amount_off'] = isset($form_data['free_ship_min_amount']) ? $form_data['free_ship_min_amount'] : '';
			// 		$arr_data['off_type_amount'] = isset($form_data['old_%_off']) ? $form_data['old_%_off'] : '';

			// 	} elseif ($form_data['shipping_type'] == '3') {

			// 		$arr_data['shipping_type'] = isset($form_data['shipping_type']) ? $form_data['shipping_type'] : '';
			// 		$arr_data['minimum_amount_off'] = isset($form_data['free_ship_min_amount']) ? $form_data['free_ship_min_amount'] : '';
			// 		$arr_data['off_type_amount'] = isset($form_data['old_$_off']) ? $form_data['old_$_off'] : '';

			// 	}elseif($form_data['shipping_type'] == '')
			// 	{
			// 		$arr_data['shipping_type'] = isset($form_data['shipping_type']) ? $form_data['shipping_type'] : '';
			// 		$arr_data['minimum_amount_off'] = isset($form_data['free_ship_min_amount']) ? $form_data['free_ship_min_amount'] : 0;
			// 		$arr_data['off_type_amount'] = isset($form_data['old_$_off']) ? $form_data['old_$_off'] : '';
			// 		$form_data['free_ship_min_amount'] = $arr_data['minimum_amount_off'];
			// 	}

			// 	// if ($res_product_data[0]['minimum_amount_off'] != $form_data['free_ship_min_amount']) {
			// 	// 	array_push($updateNewColumnsArray, 'shipping_minimum_amount_off');
			// 	// }
			// 	//echo "<pre>";print_r($arr_data);die;
			// 	$this->BaseModel->where('id', $product_id)->update($arr_data);
			// }

		

			// if (isset($form_data['product_discount_type'])) {
			// 	$arr_data = [];
			// 	$arr_data['prodduct_dis_type'] = isset($form_data['product_discount_type']) ? $form_data['product_discount_type'] : '';
			// 	$arr_data['product_dis_min_amt'] = isset($form_data['free_product_dis_min_amount']) ? $form_data['free_product_dis_min_amount'] : '';

			// 	if (isset($form_data['product_discount_type']) && $form_data['product_discount_type'] == '1') {
			// 		$arr_data['product_discount'] = isset($form_data['product_old_percent_off']) ? $form_data['product_old_percent_off'] : '';

			// 	} elseif (isset($form_data['product_discount_type']) && $form_data['product_discount_type'] == '2') {
			// 		$arr_data['product_discount'] = isset($form_data['product_old_dollar_off']) ? $form_data['product_old_dollar_off'] : '';
			// 	}

			// 	if ($res_product_data[0]['product_dis_min_amt'] != $form_data['free_product_dis_min_amount']) {
			// 		array_push($updateNewColumnsArray, 'product_minimum_amount_off');
			// 	}

			// 	$this->BaseModel->where('id', $product_id)->update($arr_data);
			// }


			/*------------commented from front add product form for shipping and other charges end---------*/
			$productData = $this->BaseModel->where('id', $product_id)->first();

			$userData = $this->UserService->get_user_information($productData->user_id, 'admin');

			$arr_notify_data = [];
			$arr_notify_data['from_user_id'] = isset($productData->user_id) ? $productData->user_id : 0;
			$arr_notify_data['to_user_id'] = get_admin_id();

			$arr_notify_data['description'] = 'Product ' . $productData->product_name . ' is updated by vendor ' . $userData['first_name'] . ' ' . $userData['last_name'] . ', Please activate product.';

			$arr_notify_data['title'] = 'Product updated';
			$arr_notify_data['type'] = 'admin';
			$arr_notify_data['link'] = url('/admin/products/view/' . base64_encode($product_id));

			$saveNotification = $this->GeneralService->save_notification($arr_notify_data);
			
			DB::commit();
			$response['status'] = 'success';
			// $response['description'] = str_singular($this->module_title) . ' categories and shipping charges has been updated.';
			$response['description'] = str_singular($this->module_title) . ' categories has been updated.';

			$new_product_arr = $this->BaseModel->where('id', $product_id)->first()->toArray();

			unset($new_product_arr['created_at']);
			unset($new_product_arr['updated_at']);

			$arr_is_equal = ($old_product_arr === $new_product_arr);

			/* --------------------------------------------------------------------------------
				                Code to insert updated column in table - product_updated_by_vendor
				                To see updated columns in admin login
				                By Harshada Kothmire
				                On date 07 Sept 202
			*/
			// if($is_update == true)
			{
				$res_already_updated_by_vendor = $this->ProductUpdatedByVendorModel->where('product_id', $product_id)->get()->toArray();

				if (!empty($res_already_updated_by_vendor)) {
					$updatedColumnsArray = json_decode($res_already_updated_by_vendor[0]['update_columns'], true);
					if (isset($updatedColumnsArray) && count($updatedColumnsArray) > 0) {
						foreach ($updatedColumnsArray as $det) {
							array_push($updateNewColumnsArray, $det);
						}
					}
				}
				/* ------------------ Form 4 Columns------------------------------------------------*/
				//dd((int)$res_product_data[0]['minimum_amount_off'],(int)$form_data['free_ship_min_amount']);
				if ($res_product_data[0]['category_id'] != $form_data['category_id']) {
					array_push($updateNewColumnsArray, 'category_id');
				}

				/*---------other charges code start ------------------*/

				// if ((int) $res_product_data[0]['minimum_amount_off'] != (int) $form_data['free_ship_min_amount']) {
				// 	array_push($updateNewColumnsArray, 'shipping_minimum_amount_off');
				// }
				// if (isset($form_data['shipping_type']) && $res_product_data[0]['shipping_type'] != $form_data['shipping_type']) {
				// 	array_push($updateNewColumnsArray, 'shipping_type');
				// }
				// if ((int) $res_product_data[0]['shipping_charges'] != (int) $form_data['shipping_charges']) {
				// 	array_push($updateNewColumnsArray, 'shipping_charges');
				// }

				// if (isset($form_data['old_$_off'])) {
				// 	if ($res_product_data[0]['off_type_amount'] != $form_data['old_$_off']) {
				// 		array_push($updateNewColumnsArray, 'off_type_amount');
				// 	}
				// }

				// if (isset($form_data['product_discount_type'])) {
				// 	if ($res_product_data[0]['prodduct_dis_type'] != $form_data['product_discount_type']) {
				// 		array_push($updateNewColumnsArray, 'product_discount_type');
				// 	}
				// }
				// if (isset($form_data['product_old_percent_off']) || isset($form_data['product_old_dollar_off'])) {
				// 	if (isset($form_data['product_old_percent_off']) && $res_product_data[0]['product_discount'] != $form_data['product_old_percent_off']) {
				// 		array_push($updateNewColumnsArray, 'product_discount');
				// 	} elseif (isset($form_data['product_old_dollar_off']) && $res_product_data[0]['product_discount'] != $form_data['product_old_dollar_off']) {
				// 		array_push($updateNewColumnsArray, 'product_discount');
				// 	}
				// }

				/*--------------end------------------------*/
				// echo "<pre>";print_r($updateNewColumnsArray);die;
				/* ------------------ Form 4 columns------------------------------------------------*/
				if (!empty($updateNewColumnsArray)) {
					$updateNewColumns = json_encode(array_unique($updateNewColumnsArray));
				}
				$updateSubCategoryID = "";
				if (!empty($InsertSubCatInTable)) {
					$updateSubCategoryID = json_encode(array_unique($InsertSubCatInTable));
				}

				$updateThirdSubCategoryID = "";
				if (!empty($InsertThirdSubCatInTable)) {
					$updateThirdSubCategoryID = json_encode(array_unique($InsertThirdSubCatInTable));
				}

				$updateFourthSubCategoryID = "";
				if (!empty($InsertFourthSubCatInTable)) {
					$updateFourthSubCategoryID = json_encode(array_unique($InsertFourthSubCatInTable));
				}

				if (!empty($res_already_updated_by_vendor)) {
					$ProductUpdatedArr['update_columns'] = $updateNewColumns;
					$ProductUpdatedArr['update_subcategories_columns'] = $updateSubCategoryID;
					$ProductUpdatedArr['update_third_subcategories_columns'] = $updateThirdSubCategoryID;
					$ProductUpdatedArr['update_fourth_subcategories_columns'] = $updateFourthSubCategoryID;
					$ProductUpdatedArr['updated_at'] = date("Y-m-d H:i:s");
					$this->ProductUpdatedByVendorModel->where('product_id', $product_id)->update($ProductUpdatedArr);	
				} else {
					$ProductUpdatedArr['product_id'] = $product_id;
					$ProductUpdatedArr['vendor_id'] = isset($loggedInUserId) ? $loggedInUserId : 0;
					$ProductUpdatedArr['update_columns'] = $updateNewColumns;
					$ProductUpdatedArr['update_subcategories_columns'] = $updateSubCategoryID;
					$ProductUpdatedArr['update_third_subcategories_columns'] = $updateThirdSubCategoryID;
					$ProductUpdatedArr['update_fourth_subcategories_columns'] = $updateFourthSubCategoryID;
					$ProductUpdatedArr['created_at'] = date("Y-m-d H:i:s");
					if ($updateNewColumns) {
						$this->ProductUpdatedByVendorModel->create($ProductUpdatedArr);
					}
				}

			}

			if (!$arr_is_equal) {
				$arr_data['is_active'] = 2;
				$arr_data['remark'] = Null;
				$this->BaseModel->where('id', $product_id)->update($arr_data);

			}
			$this->ElasticSearchService->initiate_index_product($product_id);
			$response['next_url'] = $this->module_url_path;
		
			return response()->json($response);

		} catch (Exception $e) {
			DB::rollback();

			$response['status'] = 'failure';
			$response['description'] = $e->getMessage();

			return response()->json($response);
		}
	}

	public function does_exists(Request $request, $param, $pro_id = false) {
		$form_data = $request->all();
		$is_sku_exists = $this->ProductService->does_exists($form_data, $param, $pro_id);

		if ($is_sku_exists == 'success') {

			return response()->json(['exists' => 'true']);
		} elseif ($is_sku_exists == 'failed') {

			return response()->json(['exists' => 'true'], 404);
		}

	}
	//Done
	public function does_exists_edit(Request $request, $param, $pro_id = false) {
		// return 1;
		$form_data = $request->all();

		$is_sku_exists = $this->ProductService->does_exists_edit($form_data, $param, $pro_id);

		if ($is_sku_exists == 'success') {

			return response()->json(['exists' => 'true']);
		} elseif ($is_sku_exists == 'failed') {

			return response()->json(['exists' => 'true'], 404);
		}
	}

	public function export_sheet() {
		$user = Sentinel::check();
		$loggedInUserId = 0;
		if ($user) {
			$loggedInUserId = $user->id;
		}
		// $header = ['Product_Name(*)', 'Category(*)', 'Subcategory', 'Third Level Categories' , 'Fourth Level Categories' , 
		// 	'Brand(*)', 'Description', 'Product Ingrediants', 'Case Quantity(*)', 'Unit_price(*)',
		// 	'SKU(*)', 'Available Qty(*)', 'Product Min Qty(*)', 'SKU Description', 'Shipping Charges(*)',
		// 	'Discount type on shipping(*)',
		// 	'Min order amount for shipping',
		// 	'$ Off discount on shipping',
		// 	'% Off discount on shipping',
		// 	'Min order amount to get product discount',
		// 	'Product discount type',
		// 	'$ Off discount on product',
		// 	'% Off discount on product',
		// 	'Restock Days(*)'
		// ];

		$header = ['Product_Name(*)', 'Category(*)', 'Subcategory', 'Third Level Categories' , 'Fourth Level Categories' , 
			'Brand(*)', 'Description', 'Product Ingrediants', 'Case Quantity(*)', 'Unit_price(*)',
			'SKU(*)', 'Available Qty(*)', 'Product Min Qty(*)', 'SKU Description',
			'Restock Days(*)'
		];

		$header1 = [
			'Category',
			'First Subcategory',
		];

		$header2 = [
			'First Subcategory',
			'Second Subcategory',
		];

		$header3 = [
			'Second Subcategory',
			'Third Subcategory',
		];

		$header4 = [
			'Brand',
			'Discount type on shipping',
			'Product discount type',
		];

		$filename = 'Product file sample excel';
		\Excel::create($filename, function ($excel) use ($header, $header1, $header2, $header3, $header4, $loggedInUserId) {
			$excel->sheet('Product Details', function ($sheet) use ($header) {
				require_once (base_path() . "/vendor/phpoffice/phpexcel/Classes/PHPExcel/NamedRange.php");
				require_once (base_path() . "/vendor/phpoffice/phpexcel/Classes/PHPExcel/Cell/DataValidation.php");
				// $sheet->fromArray(array($commonVarible), null, 'A1', false, false);
				// $sheet->getRowDimension(1)->setVisible(false);
				$data = $header;
				$sheet->fromArray(array($data), null, 'A1', false, false);
				$sheet->cells('A2:I2', function ($cells) {
					$cells->setBorder('none', 'thick', 'none', 'none');
					$cells->setAlignment('left');
					$cells->setFontWeight('normal');

				});
				$sheet->row(1, function ($row) {
					$row->setFontWeight('bold');

				});

				$firstlevel = $this->CategoryModel->where('is_active', 1)->get()->toArray();

				$firstlevel = array_column($firstlevel, 'category_name');

				$firstlevelList = implode(',', $firstlevel);

				$user_id = 0;
				$user = Sentinel::check();
				if (isset($user)) {
					$user_id = $user->id;
				}

				$vendor_brands_arr = $this->HelperService->get_all_vendor_brands($user_id);
				$vendor_brands = implode(',', $vendor_brands_arr);

				$this->addDropdowninExcel('D', 2, $vendor_brands, 100, $sheet);

				$this->addDropdowninExcel('B', 2, $firstlevelList, 100, $sheet);

				$secondlevel = $this->SubCategoryModel->where('is_active', 1)
				->limit(85)
				->get()
				->toArray();

				$secondlevel = array_column($secondlevel, 'subcategory_name');

				$secondlevelList = implode(',', $secondlevel);

				$this->addDropdowninExcel('C', 2, $secondlevelList, 100, $sheet);

				$thirdlevel = $this->ThirdSubCategoryModel->where('is_active', 1)
				->limit(85)
				->get()
				->toArray();

				$thirdlevel = array_column($thirdlevel, 'third_sub_category_name');

				$thirdlevelList = implode(',', $thirdlevel);

				$this->addDropdowninExcel('D', 2, $thirdlevelList, 100, $sheet);

				$fourthlevel = $this->FourthSubCategoryModel->where('is_active', 1)
				->limit(85)
				->get()
				->toArray();

				$fourthlevel = array_column($fourthlevel, 'fourth_sub_category_name');

				$fourthlevelList = implode(',', $fourthlevel);

				$this->addDropdowninExcel('E', 2, $fourthlevelList, 100, $sheet);



				// $shipping_type = array('0' => 'Free Shipping', '1' => '% Off', '2' => '$ Off');

				// $shippingTypeList = implode(',', $shipping_type);

				// $this->addDropdowninExcel('P', 2, $shippingTypeList, 100, $sheet);

				// $product_dis_type = array('0' => '% Off', '1' => '$ Off');

				// $productTypeList = implode(',', $product_dis_type);

				// $this->addDropdowninExcel('U', 2, $productTypeList, 100, $sheet);

				// $product_option_type = array('0' => 'color', '1' => 'scent', '2' => 'size', '3' => 'material');

				// $productOptionList = implode(',', $product_option_type);

				// $this->addDropdowninExcel('H',2,$productOptionList,100,$sheet);

				// $sub_cat_arr = $this->HelperService->get_subcategories();

				//$subcategory_arr = [];

				// foreach ($sub_cat_arr as $key => $value) {
				// 	if (isset($value['subcategory_name'])) {
				// 		array_push($subcategory_arr, $value['subcategory_name']);
				// 	}

				// }

				// $subCatList = implode(',', $subcategory_arr);

				// $this->addDropdowninExcel('C', 2, $subCatList, 100, $sheet);

			});

			/*second sheet*/

			$cat_sub_cat_arr = $Category = [];

			$categories_arr = $this->CategoryModel->select('id')
				->with(['subcategory_details' => function ($q1) {
					$q1->where('is_active', 1);
				}])
				->where('is_active', 1)
				->get()
				->toArray();
			if (isset($categories_arr) && count($categories_arr) > 0) {

				foreach ($categories_arr as $key => $category) {
					if (isset($category['subcategory_details']) && count($category['subcategory_details']) > 0) {
						foreach ($category['subcategory_details'] as $key => $subcategory) {
							if (isset($subcategory['subcategory_name'])) {
								$cat_sub_cat_arr[$category['category_name']][] = $subcategory['subcategory_name'];
							}

						}

					} else {
						$cat_sub_cat_arr[$category['category_name']][] = '-';
					}
				}

			}

			$final_arr = [];
			$cnt = 0;

			// /*-----------------------------------------------------------------------*/

			foreach ($cat_sub_cat_arr as $key => $subcategory) {

				foreach ($subcategory as $key1 => $value) {
					if ($key1 > 0) {
						$final_arr[$cnt]['Category'] = '';

					} else {
						$final_arr[$cnt]['Category'] = $key;

					}

					$final_arr[$cnt]['Subcategory'] = $value;

					$cnt++;
				}
			}

			// /*-----------------------------------------------------------------------*/

			$excel->sheet('Category & Subcategory Table', function ($sheet) use ($header1, $final_arr) {
				$sheet->cell('A1', function ($cell) {
					$cell->sheet->getStyle('A1:A100')->applyFromArray([
						'font' => [
							'bold' => true,
						],
					]);

				});

				$cell_count = count($final_arr) + 1;

				$sheet->cells('A1:B1', function ($cells) {
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->cells('A2:B' . $cell_count, function ($cells) {
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->fromArray($final_arr, null, 'A1', true, true);

				//$sheet->fromArray([$final_arr,$header1]);

				$sheet->row(1, function ($row) {
					$row->setFontWeight('bold');

				});

			});

			// /*-------------------------3rd sheet ----------------------------------*/

			/*third sheet*/

			$third_sub_cat_arr = $Category = [];

			$sub_categories_arr = $this->SubCategoryModel->select('id')
														->with(['second_subcategory_details' => function ($q1) {
															$q1->where('is_active', 1);
														}])
														->where('is_active', 1)
														->get()
														->toArray();

			if (isset($sub_categories_arr) && count($sub_categories_arr) > 0) 
			{
				foreach ($sub_categories_arr as $key => $sub_category) 
				{
					if (isset($sub_category['second_subcategory_details']) && count($sub_category['second_subcategory_details']) > 0) 
					{
						foreach ($sub_category['second_subcategory_details'] as $key => $subcategory) 
						{
							if (isset($subcategory['third_sub_category_name'])) 
							{
								$third_sub_cat_arr[$sub_category['subcategory_name']][] = $subcategory['third_sub_category_name'];
							}
						}
					} else {
						//$third_sub_cat_arr[$sub_category['subcategory_name']][] = '-';
					}
				}
			}
			$final_arr = [];
			$cnt = 0;

			// /*-----------------------------------------------------------------------*/

			foreach ($third_sub_cat_arr as $key => $subcategory) {

				foreach ($subcategory as $key1 => $value) {
					if ($key1 > 0) {
						$final_arr[$cnt]['Sub Category'] = '';

					} else {
						$final_arr[$cnt]['Sub Category'] = $key;

					}

					$final_arr[$cnt]['Second Sub Category'] = $value;

					$cnt++;
				}
			}

			// /*-----------------------------------------------------------------------*/

			$excel->sheet('SubCategory & 2Subcategory', function ($sheet) use ($header1, $final_arr) {
				$sheet->cell('A1', function ($cell) {
					$cell->sheet->getStyle('A1:A100')->applyFromArray([
						'font' => [
							'bold' => true,
						],
					]);

				});

				$cell_count = count($final_arr) + 1;

				$sheet->cells('A1:B1', function ($cells) {
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->cells('A2:B' . $cell_count, function ($cells) {
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->fromArray($final_arr, null, 'A1', true, true);

				//$sheet->fromArray([$final_arr,$header1]);

				$sheet->row(1, function ($row) {
					$row->setFontWeight('bold');

				});

			});

			// /*-------------------------4th sheet ----------------------------------*/

			$fourth_sub_cat_arr = $Category = [];

			$second_sub_categories_arr = $this->ThirdSubCategoryModel->select('id')
														->with(['third_subcategory_details' => function ($q1) {
															$q1->where('is_active', 1);
														}])
														->where('is_active', 1)
														->get()
														->toArray();
			
			if (isset($second_sub_categories_arr) && count($second_sub_categories_arr) > 0) 
			{
				foreach ($second_sub_categories_arr as $key => $second_sub_category) 
				{
					if (isset($second_sub_category['third_subcategory_details']) && count($second_sub_category['third_subcategory_details']) > 0) 
					{
						foreach ($second_sub_category['third_subcategory_details'] as $key => $subcategory) 
						{
							if (isset($subcategory['fourth_sub_category_name'])) 
							{
								$fourth_sub_cat_arr[$second_sub_category['third_sub_category_name']][] = $subcategory['fourth_sub_category_name'];
							}
						}
					} else {
						$fourth_sub_cat_arr[$second_sub_category['third_sub_category_name']][] = '-';
					}
				}
			}
			$final_arr = [];
			$cnt = 0;
			
			// /*-----------------------------------------------------------------------*/

			foreach ($fourth_sub_cat_arr as $key => $subcategory) {

				foreach ($subcategory as $key1 => $value) {
					if ($key1 > 0) {
						$final_arr[$cnt]['Second Sub Category'] = '';

					} else {
						$final_arr[$cnt]['Second Sub Category'] = $key;

					}

					$final_arr[$cnt]['Third Sub Category'] = $value;

					$cnt++;
				}
			}

			// /*-----------------------------------------------------------------------*/

			$excel->sheet('2SubCategory & 3Subcategory', function ($sheet) use ($header1, $final_arr) {
				$sheet->cell('A1', function ($cell) {
					$cell->sheet->getStyle('A1:A100')->applyFromArray([
						'font' => [
							'bold' => true,
						],
					]);

				});

				$cell_count = count($final_arr) + 1;

				$sheet->cells('A1:B1', function ($cells) {
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->cells('A2:B' . $cell_count, function ($cells) {
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->fromArray($final_arr, null, 'A1', true, true);

				//$sheet->fromArray([$final_arr,$header1]);

				$sheet->row(1, function ($row) {
					$row->setFontWeight('bold');

				});

			});

			/*-------------------------5th sheet ----------------------------------*/


			//get brnads
			$brand_arr = [];

			$brand_arr = $this->BrandsModel->where('is_active', 1)->where('user_id', $loggedInUserId)->select('brand_name as Brand')->get()->toArray();

			$excel->sheet('Brand', function ($sheet) use ($header2, $brand_arr) {
				$sheet->cell('A1', function ($cell) {
					$cell->sheet->getStyle('A1:A100')->applyFromArray(['font' => ['bold' => false]]);
				});

				$cell_count = count($brand_arr) + 1;

				$sheet->fromArray($brand_arr, null, 'A1', true, true);

				$sheet->row(1, function ($row) {
					$row->setFontWeight('bold');
				});

				/*       $sheet->cell('B1', function ($cell)
					                  {
					                      $cell->sheet->getStyle('B1')->applyFromArray(['font' =>['bold' => true]]);
					                      $cell->setValue('Option Type');
					                  });

					                  $sheet->cell('B2', function ($cell)
					                  {
					                     $cell->setValue('color');
					                  });

					                  $sheet->cell('B3', function ($cell)
					                  {
					                    $cell->setValue('scent');
					                  });

					                  $sheet->cell('B4', function ($cell)
					                  {
					                    $cell->setValue('size');
					                  });

					                  $sheet->cell('B5', function ($cell)
					                  {
					                    $cell->setValue('material');
				*/

				// $sheet->cell('C1', function ($cell) {
				// 	$cell->sheet->getStyle('C1')->applyFromArray(['font' => ['bold' => true]]);
				// 	$cell->setValue('Discount type on shipping');
				// });

				// $sheet->cell('C2', function ($cell) {
				// 	$cell->setValue('Free Shipping');
				// });

				// $sheet->cell('C3', function ($cell) {
				// 	$cell->setValue('% Off');
				// });

				// $sheet->cell('C4', function ($cell) {
				// 	$cell->setValue('$ Off');
				// });

				// $sheet->cell('D1', function ($cell) {
				// 	$cell->sheet->getStyle('D1')->applyFromArray(['font' => ['bold' => true]]);
				// 	$cell->setValue('Product discount type');
				// });

				// $sheet->cell('D2', function ($cell) {
				// 	$cell->setValue('% Off');
				// });

				// $sheet->cell('D3', function ($cell) {
				// 	$cell->setValue('$ Off');
				// });

			});

			/*-------------------------------------------------------------------------------------------*/

			/* set 1st sheeet by default open*/
			$excel->setActiveSheetIndex(0);

		})->download('xlsx');

	}

	public function addDropdowninExcel($col, $row, $json, $limit, $sheet) {
		for ($i = 0; $i <= $limit; $i++) {
			$objValidation2 = $sheet->getCell($col . $row)->getDataValidation();
			$objValidation2->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST);
			$objValidation2->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
			$objValidation2->setAllowBlank(false);
			$objValidation2->setShowInputMessage(true);
			$objValidation2->setShowDropDown(true);
			$objValidation2->setPromptTitle('Pick from list');
			$objValidation2->setPrompt('Please pick a value from the drop-down list.');
			$objValidation2->setErrorTitle('Input error');
			$objValidation2->setError('Value is not in list');
			$objValidation2->setFormula1('"' . $json . '"');
			$row++;
		}

	}

	public function importExcel(Request $request) {

		$importExcelStartTime = microtime(true);

		set_time_limit(0);

		$user_id = 0;
		$product_arr = [];
		$shipping_discount_amt = $product_discount_amt = 0;

		$override_params = [

			'option_type' => false, //$option_type,
			'option_value' => false,
			'brand_id' => false,
			'option' => false, //isset($value['option_value'])?$value['option_value']:'',
			'weight' => false, //isset($value['weight'])?$value['weight']:'',
			'length' => false, //isset($value['length'])?$value['length']:'',
			'width' => false, //isset($value['width'])?$value['width']:'',
			'height' => false, //isset($value['height'])?$value['height']:''
			'category_id' => false,
			'sub_category_id' => false,
			'available_qty' => false,
			'shipping_weight' => false,
			'shipping_length' => false,
			'shipping_width' => false,
			'shipping_height' => false,
			'product_min_qty' => false,

			'third_category_id' => false,
			'fourth_category_id' => false
		];

		$user = Sentinel::check();

		if (isset($user)) {
			$user_id = $user->id;
		}

		/* Check if override brand is set and then get first brand of that user _id */

		$tmp_brand = $this->BrandsModel->where('user_id', $user_id)->first();

		if (isset($tmp_brand['id'])) {
			$override_params['brand_id'] = $tmp_brand['id'];
		}

		$request->validate([
			'import_bulk_data' => 'required',
		]);

		$path = $request->file('import_bulk_data')->getRealPath();
		$extension = $request->file('import_bulk_data')->getClientOriginalExtension();

		/* Check Excel Validation */
		if ($extension != "xlsx") {
			Flash::error('Please upload xlsx format only.');
			return redirect()->back();
		}
		// dd("hifdfdfdfdfdf");
		$data = Excel::selectSheetsByIndex(0)->load($path)->get()->toArray();
		// dd($data);
		
		//echo "<pre>";print_r($data);die;
		$data = array_column($data, null, 'sku');

		/* Removing null data entires */
		$tmp_arr = [];
		if(isset($data) && count($data)>0){
			foreach($data as $key => $value){
				if($key!="" && $key!=null){
					array_push($tmp_arr,$value);
				}
			}
		}

		$data = $tmp_arr;

		if (sizeof($data) <= 0) {
			Flash::error('File is empty.');
			return redirect()->back();
		}

		//Product SKU  duplication
		$arr_sku = array_column($data, 'sku');
		$arr_sku = array_filter($arr_sku);
		$arr_sku = array_map(function ($sku) {

			$sku = is_numeric($sku) ? intval($sku) : $sku;
			$sku = "" . $sku . "";
			return $sku;
		}, $arr_sku);

		$arr_already_exists_sku = ProductDetailsModel::whereIn('sku', $arr_sku)->where('is_deleted', 0)->get(['sku'])->toArray();
		$arr_already_exists_sku = array_column($arr_already_exists_sku, 'sku');

		$successful_insertion = 0;

		/*if(sizeof($arr_sku) != 0 && sizeof($arr_already_exists_sku) != 0)
			    {
			        if(sizeof($arr_sku) == sizeof($arr_already_exists_sku))
			        {
			          Flash::error('No product(s) added, duplicate sku(s) list: '.implode(" , ",$arr_already_exists_sku));
			          return redirect()->back();
			        }

		*/

		/* Build Product - SKU Mapping From View vw_products_basic_details
			        Format:
			                product_name::sku => product_id
			                product_name::sku => product_id
		*/
		$arr_product_sku_mapping = DB::table('vw_products_basic_details')
			->selectRaw("CONCAT(UPPER(product_name),'::',sku) as product_name_sku, id")
			->where("is_deleted", 0)
			->get()
			->toArray();

		$arr_product_sku_mapping = array_column($arr_product_sku_mapping, 'id', 'product_name_sku');

		/* Build Category Mapping
			       Format:
			       CATEGORY_NAME => Category Id
		*/

		$arr_category_mapping = $this->CategoryTranslationModel
			->selectRaw("category_id, UPPER(category_name) as category_name")
			->get()
			->toArray();

		$arr_category_mapping = array_column($arr_category_mapping, 'category_id', 'category_name');

		/* Build Sub Category Mapping
			       Format:
			       Sub CATEGORY_NAME => Sub Category Id
		*/

		// $arr_subcategory_mapping = $this->SubCategoryTranslationModel
		// 	->selectRaw("subcategory_id, UPPER(subcategory_name) as subcategory_name")
		// 	->get()
		// 	->toArray();

		$arr_subcategory_mapping = $this->SubCategoryTranslationModel
			->selectRaw("subcategory_translation.subcategory_id, CONCAT(UPPER(subcategory_translation.subcategory_name),'###',subcategory.category_id) as subcategory_name")
			->leftJoin('subcategory','subcategory_translation'.'.subcategory_id','=','subcategory'.'.id')
			->get()
			->toArray();

			//dd($arr_subcategory_mapping);
			
		$arr_subcategory_mapping = array_column($arr_subcategory_mapping, 'subcategory_id', 'subcategory_name');


		//echo "<pre>";print_r($arr_subcategory_mapping);die;
		/* Build Sub Category id - Category id Mapping
			       Format:
			       Sub category_id =>  Category Id
		*/

		$arr_subcategory_category_mapping = SubCategoryModel::where('is_active', 1)
			->get(['category_id', 'id'])
			->toArray();

		$arr_subcategory_category_mapping = array_column($arr_subcategory_category_mapping, 'category_id', 'id');


		//dd($arr_subcategory_category_mapping);
		/* Build Brand Mapping
			         Format:
			       BRAND_NAME => brand Id
		*/
/*third category start*/
$arr_thirdsubcategory_mapping = $this->ThirdSubCategoryTranslationModel
			->selectRaw("third_sub_category_translation.third_sub_category_id, CONCAT(UPPER(third_sub_category_translation.third_sub_category_name),'###',third_sub_category.sub_category_id) as thirdsubcategory_name")
			->leftJoin('third_sub_category','third_sub_category_translation'.'.third_sub_category_id','=','third_sub_category'.'.id')
			->get()
			->toArray();


$arr_thirdsubcategory_mapping = array_column($arr_thirdsubcategory_mapping, 'third_sub_category_id', 'thirdsubcategory_name');


$arr_thirdsubcategory_category_mapping = ThirdSubCategoryModel::where('is_active', 1)
			->get(['category_id','sub_category_id', 'id'])
			->toArray();

			

			/*third category end*/

			/*fourth category start*/
$arr_fourthsubcategory_mapping = $this->FourthSubCategoryTranslationModel
			->selectRaw("fourth_sub_category_translation.fourth_sub_category_id, CONCAT(UPPER(fourth_sub_category_translation.fourth_sub_category_name),'###',fourth_sub_category.third_sub_category_id	) as fourthsubcategory_name")
			->leftJoin('fourth_sub_category','fourth_sub_category_translation'.'.fourth_sub_category_id','=','fourth_sub_category'.'.id')
			->get()
			->toArray();

			


$arr_fourthsubcategory_mapping = array_column($arr_fourthsubcategory_mapping, 'fourth_sub_category_id', 'fourthsubcategory_name');


$arr_fourthsubcategory_category_mapping = FourthSubCategoryModel::where('is_active', 1)
			->get(['category_id','second_sub_category_id', 'id','third_sub_category_id'])
			->toArray();

			

			/*fourth category end*/

		$arr_brand_mapping = $this->BrandsModel
			->selectRaw("id, UPPER(brand_name) as brand_name")
			->get()->toArray();
		$arr_brand_mapping = array_column($arr_brand_mapping, 'id', 'brand_name');

		$product_name_insertion = 0;
		$product_name_successful_insertion = 0;

		//flags
		$product_name_flag = 0;
		$brand_flag = 0;
		$case_qty_flag = 0;
		$unit_wholsale_price_flag = 0;
		// $unit_retail_price_flag = 0;
		$sku_flag = 0;
		$available_qty_flag = 0;
		$category_flag = 0;
		$shipping_charges_flag = 0;
		$shipping_dis_type_flag = 0;
		$product_min_qty_flag = 0;

		$duplicate_products = $cat_sub_cat_mapping_arr = [];

		try
		{
			$arr_chunked_data = array_chunk($data, 200);

			//dd($arr_chunked_data);
			
			foreach ($arr_chunked_data as $key => $chunked_data) {
				$arr_config = [
					'arr_data' => $chunked_data,
					'arr_already_exists_sku' => $arr_already_exists_sku,
					'arr_product_sku_mapping' => $arr_product_sku_mapping,
					'arr_category_mapping' => $arr_category_mapping,

					'arr_subcategory_mapping' => $arr_subcategory_mapping,
					'arr_subcategory_category_mapping' => $arr_subcategory_category_mapping,

					'arr_thirdsubcategory_mapping' => $arr_thirdsubcategory_mapping,
					'arr_thirdsubcategory_category_mapping' => $arr_thirdsubcategory_category_mapping,

					'arr_fourthsubcategory_mapping' => $arr_fourthsubcategory_mapping,
					'arr_fourthsubcategory_category_mapping' => $arr_fourthsubcategory_category_mapping,

					'arr_brand_mapping' => $arr_brand_mapping,
					'override_params' => $override_params,
					'user_id' => $user_id,
				];

				

				 dispatch(new \App\Jobs\BulkImportProductJob($arr_config, $this->ElasticSearchService));

			}

		} catch (Exception $e) {
			Flash::error('Something went wrong, please try again.');
			return redirect()->back();
		}

		$importExcelEndTime = microtime(true);
		$importExcelExecutionTime = ($importExcelEndTime - $importExcelStartTime);

		// dump($importExcelExecutionTime, "importExcelExecutionTime");

		$duplicate_sku_count = sizeof($arr_already_exists_sku);
		//$duplicate_count_product_name = sizeof($duplicate_products);

		/*if ($duplicate_sku_count >= 0 && $successful_insertion <= 0) {
				Flash::error('All products already exists.');
				return redirect()->back();
			}

			if ($successful_insertion <= 0) {
				Flash::error('Something went wrong, please try again.');
				return redirect()->back();
		*/

		// $tmp = $successful_insertion . ' Product(s) has been added.';
		$cnt_all_data 				= sizeof($data);
		$cnt_already_exists_data 	= sizeof($arr_already_exists_sku);
		$total_products 			= $cnt_all_data - $cnt_already_exists_data;

		if($total_products != 0){
			$tmp = ($total_products). ' Product(s) has been queued, will be reflected shortly.';
		} else {
			$tmp = 'Product(s) already exists';
		}

		// if ($duplicate_sku_count > 0) {
		// 	$tmp .= '<br> Excluding ' . $duplicate_sku_count . ' sku(s)';
		// 	$tmp .= '<br> Duplicate sku(s) are as folllows: <br> <ul><li>' . implode("</li><li>", $arr_already_exists_sku) . "</li></ul>";
		// }

		/*  if($duplicate_count_product_name > 0)
				      {
				        $tmp.= '<br> Excluding '.$duplicate_count_product_name.' product(s)';
				        $tmp.= '<br> Duplicate product(s) are as folllows: <br> <ul><li>'.implode("</li><li>",$duplicate_products)."</li></ul>";
			*/

		//check cat sub cat mapping flag

		if (isset($cat_sub_cat_mapping_arr) && count($cat_sub_cat_mapping_arr) > 0) {
			$tmp .= "Few products having missmatch category and sub category data, please update those products through update product form.";

		}

		//if required fields are missing then show this message
		if ($product_name_flag == 1 ||
			$category_flag == 1 ||
			$brand_flag == 1 ||
			$case_qty_flag == 1 ||
			$unit_wholsale_price_flag == 1 ||
			// $unit_retail_price_flag == 1 ||
			$sku_flag == 1 ||
			$available_qty_flag == 1 ||
			$shipping_charges_flag == 1 ||
			$shipping_dis_type_flag == 1
		) {
			$tmp .= "Few products having missing data, please fill all data through update product form.";
		}

		Flash::success($tmp);
		Session::flash('is_secure', false);

		return redirect()->back();

	}

	/* Auth : Jaydip
		    Date : 12 Dec 2019
		    Desc : manage inventory functionality (Do not use without permission)
	*/

	public function product_invetory_script() {
		$arr_data = [];

		$sku_no = $this->ProductInventoryModel->get(['sku_no'])->toArray();

		$arr_sku_no = array_column($sku_no, 'sku_no');

		$arr_product_details = $this->ProductDetailsModel->get()->toArray();

		foreach ($arr_product_details as $product) {
			$product_obj = get_product_details($product['product_id']);

			$arr_data['product_id'] = $product['product_id'];
			$arr_data['user_id'] = isset($product_obj['user_id']) ? $product_obj['user_id'] : 0;
			$arr_data['sku_no'] = $product['sku'];
			$arr_data['quantity'] = '100';

			$create_entry = $this->ProductInventoryModel->create($arr_data);
		}

	}

	public function changeProductStatus(Request $request) {
		$product_status = $request->input('productStatus');
		$product_id = $request->input('product_id');

		if ($product_status == '1') {

			$result = $this->BaseModel->where('id', $product_id)->update(['product_status' => 1]);

			if ($result) {
				$this->ElasticSearchService->initiate_index_product($product_id, "1");
			}

			$response['status'] = 'SUCCESS';
			$response['message'] = $this->module_title . ' has been activated.';

		} elseif ($product_status == '0') {
			$result = $this->BaseModel->where('id', $product_id)->update(['product_status' => 0]);

			if ($result) {
				$this->ElasticSearchService->initiate_index_product($product_id, "1");
			}

			$response['status'] = 'SUCCESS';
			$response['message'] = $this->module_title . ' has been deactivated.';
		} else {
			$response['status'] = 'ERROR';
			$response['message'] = 'Something went wrong, please try again.';
		}

		return response()->json($response);
	}

	public function multi_action(Request $request) {
		$arr_rules = array();
		$arr_rules['checked_record'] = "required";

		$validator = Validator::make($request->all(), $arr_rules);

		if ($validator->fails()) {
			Flash::error('Please select ' . $this->module_title . ' to perform multi actions.');
			return redirect()->back()->withErrors($validator)->withInput();
		}

		$multi_action = $request->input('multi_action');
		$checked_record = $request->input('checked_record');

		/* Check if array is supplied*/
		if (is_array($checked_record) && sizeof($checked_record) <= 0) {
			Flash::error('Problem occurred, while doing multi action.');
			return redirect()->back();
		}

		$product_count = count($checked_record);

		foreach ($checked_record as $key => $record_id) {
			if ($multi_action == "delete") {				
				$this->delete(base64_encode($record_id),$product_count);
				$this->ElasticSearchService->initiate_index_product($record_id);
				// Flash::success($product_count.' product(s) have been deleted.');
				Flash::success($product_count.' product(s) have been deleted.'); 
			} elseif ($multi_action == "activate") {
				$this->perform_activate(base64_encode($record_id));
				$this->ElasticSearchService->initiate_index_product($record_id);
				Flash::success($product_count. ' product(s) have been activated.');
			} elseif ($multi_action == "deactivate") {
				$this->perform_deactivate(base64_encode($record_id));
				$this->ElasticSearchService->initiate_index_product($record_id);
				// Flash::success(str_plural($product_count.' have been deactivated.');
				Flash::success($product_count.' '.'product(s) have been rejected.'); 
			} elseif ($multi_action == "block") {
				$this->perform_deactivate(base64_encode($record_id));
				$this->ElasticSearchService->initiate_index_product($record_id);
				// Flash::success(str_plural($product_count.' have been deactivated.');
				Flash::success($product_count.' '.'product(s) have been blocked.'); 
			}

		}

		return redirect()->back();
	}

	public function getSkuImage(Request $request) {
		$sku_id = $request->input('sku_id', null);
		$sku_image = ProductDetailsModel::where('sku', $sku_id)->first(['image_thumb']);

		if (isset($sku_image) && $sku_image != '' && file_exists(base_path() . '/storage/app/' . $sku_image)) {
			$product_img_path = url('/storage/app/' . $sku_image);
		} else {
			$product_img_path = url('/') . $this->product_default_img_path;
		}

		$response['sku_thumb_image'] = $product_img_path;

		return response()->json($response);
	}

	public function delete($id,$product_count =false) {
		$id = base64_decode($id);

		$id = isset($id) ? intval($id) : 0;

		try
		{
			DB::beginTransaction();

			ProductDetailsModel::where('product_id', $id)->update(['is_deleted' => 1]);
			ProductImagesModel::where('product_id', $id)->update(['is_deleted' => 1]);
			ProductInventoryModel::where('product_id', $id)->update(['is_deleted' => 1]);
			ProductsSubCategoriesModel::where('product_id', $id)->update(['is_deleted' => 1]);

			DB::commit();

			$delete = $this->BaseModel->where('id', $id)->update(['is_deleted' => 1]);

			if ($delete) {

				$this->ElasticSearchService->initiate_index_product($id);
				Flash::success($product_count .' '.str_singular($this->module_title) . '(s) have been deleted.');
				return redirect()->back();
			} else {
				Flash::error('Error occurred while ' . str_singular($this->module_title) . ' deletion.');
				return redirect()->back();
			}

		} catch (Exception $e) {
			DB::rollback();
			Flash::error($e->getMessage());
		}

	}

	public function perform_activate($id) {
		$id = base64_decode($id);

		$static_page = $this->ProductsModel->where('id', $id)->first();

		if ($static_page) {

			$data = [];
			$data['product_status'] = 1;

			$result = $this->ProductsModel->where('id', $id)->update(['product_status' => 1]);

			if ($result) {
				$this->ElasticSearchService->activate_product_status($id, 1);
			}

		} else {
			return FALSE;
		}

	}

	public function perform_deactivate($id) {
		$id = base64_decode($id);
		$static_page = $this->BaseModel->where('id', $id)->first();

		if ($static_page) {
			$data = [];
			$data['product_status'] = 0;

			$result = $this->ProductsModel->where('id', $id)->update(['product_status' => 0]);

			if ($result) {
				$this->ElasticSearchService->decactivate_product_status($id, 0);
			}

		} else {
			return FALSE;
		}

	}

	// Function to add new row
	public function addnewrow(Request $request){
		$form_data = $request->all();
		$row = $form_data['row_id']+1;		

		$category_arr = $this->CategoryModel->where('is_active', 1)
			->whereTranslation('locale', $this->locale)
			->get()
			->toArray();

		/* Sort by Alpha */
		usort($category_arr, function ($sort_base, $sort_compare) {

			return $sort_base['category_name'] <=> $sort_compare['category_name'];
		});
		// dd($category_arr);
		$this->arr_view_data['category_arr'] = $category_arr;
		$this->arr_view_data['row'] = $row;
		return view($this->module_view_folder . '.addnewrow', $this->arr_view_data);
	}

	// Function to add new edit row
	public function addneweditrow(Request $request){
		$form_data = $request->all();
		$row = $form_data['row_id']+1;		

		$category_arr = $this->CategoryModel->where('is_active', 1)
			->whereTranslation('locale', $this->locale)
			->get()
			->toArray();

		// Sort by Alpha 
		usort($category_arr, function ($sort_base, $sort_compare) {

			return $sort_base['category_name'] <=> $sort_compare['category_name'];
		});
		$this->arr_view_data['category_arr'] = $category_arr;
		
		$this->arr_view_data['row'] = $row;
		return view($this->module_view_folder . '.addneweditrow', $this->arr_view_data);
	}

	// Function to delete multiple rows
	public function delete_multi_image(Request $request){
		$form_data = $request->all();
		// dd($form_data);
		$product_mul_image_id = $form_data['multi_image_id'];		

		$result = $this->ProductMultipleImagesModel->where('id', $product_mul_image_id)->delete();
		
	}

	// Function to delete sku row
	public function deleterow(Request $request){
		$form_data = $request->all();
		$product_det_id = $form_data['prod_det_id'];
		$product_id = $form_data['product_id'];
		$SKU_no = $form_data['SKU_no'];

		if($product_det_id){
			$ProductDetUpdatedArr['is_deleted'] = 1;							
			$ProductDetUpdatedArr['updated_at'] = date("Y-m-d H:i:s");

			// Update is_deleted = 1 in productdetails
			$result_details = $this->ProductDetailsModel
						->where('id', $product_det_id)
						->where('product_id', $product_id)
						->update($ProductDetUpdatedArr);

			// Update is_deleted = 1 in product inventory
			$result_inventory = $this->ProductInventoryModel
						->where('sku_no', $SKU_no)						
						->update($ProductDetUpdatedArr);
			//dd($form_data);
		}
		return 1;
	}

    public function show_size(Request $request)
    {
    	//dd($this->module_view_folder);
    	// dd($request->all());
    	$row_id = $request->row_id;
    	$size_row_id = $request->size_row_id;
    	$cat_id = $request->category_id;

    	$category_arr = $this->CategoryModel->where('is_active', 1)
			->whereTranslation('locale', $this->locale)
			->get()
			->toArray();

		/* Sort by Alpha */
		usort($category_arr, function ($sort_base, $sort_compare) {

			return $sort_base['category_name'] <=> $sort_compare['category_name'];
		});

		$size_arr = $this->SizeModel->where('category_id',$cat_id)
		            ->get()
		            ->toArray();
		if(isset($size_arr) && count($size_arr) > 0)
		{

		$this->arr_view_data['category_arr'] = $category_arr;

		$this->arr_view_data['size_arr'] = $size_arr;
		$this->arr_view_data['cat_id'] = $cat_id;
		$this->arr_view_data['row_id'] = $row_id;
		$this->arr_view_data['size_row_id'] = $size_row_id;
    	return view($this->module_view_folder . '.size',$this->arr_view_data);
		} 
		else{
			return 0;
		}           
		// dd($category_arr);
		
    }
    public function size_inventory(Request $request){

		$product_sku_no = $request->sku;
		$color = $request->color;

		$prod_size_table        =  $this->ProductSizesModel->getTable();
		$prefix_prod_size_table = DB::getTablePrefix().$prod_size_table;

		$size_table        =  $this->SizeModel->getTable();
		$prefix_size_table = DB::getTablePrefix().$size_table;

		$prod_size_arr = DB::table($prod_size_table)
												->leftJoin($size_table,$prod_size_table.'.size_id','=',$size_table.'.id')
												->where($prod_size_table.'.sku_no','=',$product_sku_no)
												->get()->toArray();
		 
		$html = '';
		$this->arr_view_data['prod_size_arr'] = $prod_size_arr;
		$html = '
		<h4>Color : '.$color.'</h4>
		<table class="table table-sm table-bordered">
						 <thead>
						 <tr>
								<td scope="col">Size</td>
								<td scope="col">Inventory</td>
						 </tr>
						 </thead>';

						foreach($prod_size_arr as $val){
		$html .= '<tr>
								<td scope="col">'.$val->size.'</td>
								<td scope="col">'.$val->size_inventory.'</td>
							</tr>';
						}
									
		$html .= "</table>";
									$response['status']   = 'ERROR';
									$response['msg']      = 'success';
									$response['html']      = $html;

          return response()->json($response);
	}

}
