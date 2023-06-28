<?php

namespace App\Http\Controllers\Maker;

use App\Common\Services\ElasticSearchService;
//use Illuminate\Http\File;
use App\Common\Traits\MultiActionTrait;
use App\Http\Controllers\Controller;
use App\Models\CatalogImageModel;
use App\Models\CatlogsModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductMultipleImagesModel;
use App\Models\ZipExtractionModel;
use Carbon\Carbon;
use DB;
use Exception;
use File;
use Illuminate\Http\Request;
use Image;
use Sentinel;
use Storage;
use Validator;
use ZipArchive;

class ProductImageController extends Controller {
	use MultiActionTrait;

	public function __construct(CatalogImageModel $CatalogImageModel,
		CatlogsModel $CatlogsModel,
		ElasticSearchService $ElasticSearchService,
		ProductDetailsModel $ProductDetailsModel,
		ProductMultipleImagesModel $ProductMultipleImagesModel,
		ZipExtractionModel $ZipExtractionModel

	) {
		$this->arr_view_data = [];
		$this->CatalogImageModel = $CatalogImageModel;
		$this->CatlogsModel = $CatlogsModel;
		$this->BaseModel = $this->CatalogImageModel;
		$this->ZipExtractionModel = $ZipExtractionModel;
		$this->ElasticSearchService = $ElasticSearchService;
		$this->ProductDetailsModel = $ProductDetailsModel;
		$this->ProductMultipleImagesModel 			= $ProductMultipleImagesModel;
		$this->module_title = "Upload Images";
		$this->module_view_folder = 'maker.product_images';
		$this->maker_panel_slug = config('app.project.maker_panel_slug');
		$this->module_url_path = url($this->maker_panel_slug . '/product_images');
	}

	public function index() {

		$this->arr_view_data['module_title'] = str_plural($this->module_title);
		$this->arr_view_data['page_title'] = str_plural($this->module_title);
		$this->arr_view_data['module_url_path'] = $this->module_url_path;
		$this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;

		return view($this->module_view_folder . '.index', $this->arr_view_data);
	}

	public function uploadZip(Request $request) {

		$product_img_file = '';
		try
		{
			$form_data = $request->all();
			// dd($form_data);
			$arr_rules = [
				'upload_bulk_data' => 'required',
			];

			$validator = Validator::make($request->all(), $arr_rules);

			if ($validator->fails()) {
				// Flash::error('Form validation failed, please check file.');
				// return redirect()->back();

				$response['message'] = 'Form validation failed, please check file';
				$response['status'] = 'warning';
				return response()->json($response);

			}

			$vendor_id = 0;
			$login_user = Sentinel::check();

			if ($login_user == true) {
				$vendor_id = $login_user->id;
			}

			Storage::disk('local')->makeDirectory('app/product_zip/' . $vendor_id . '/');
			Storage::disk('local')->makeDirectory('app/product_zip/' . $vendor_id . '/all_extracted_zip');

			if (isset($form_data['upload_bulk_data'])) {
				$size = $request->file('upload_bulk_data')->getSize();

				/* Zip should not be more than 250 mb */
				if ($size > 250000000) {
					/*Flash::error('File size too large, please try again.');
                    return redirect()->back();*/

					$response['message'] = 'File size too large, please try again.';
					$response['status'] = 'warning';
					return response()->json($response);
				}

				$product_zip_file_path = '';
				$img = isset($form_data['upload_bulk_data']) ? $form_data['upload_bulk_data'] : null;

				$file_extension = strtolower($form_data['upload_bulk_data']->getClientOriginalExtension());

				if (!in_array($file_extension, ['zip'])) {
					/*Flash::error('Only zip file is allowed, please try again.');
                    return redirect()->back();*/

					$response['message'] = 'Only zip file is allowed, please try again.';
					$response['status'] = 'warning';
					return response()->json($response);
				}

				// $path                  = storage_path('app/product_zip');
				$zip_file_name = date('mdYHis') . uniqid() . '.' . $file_extension;
				$product_img_file = $img->store('product_zip/' . $vendor_id);
				$product_img_file_path = 'product_zip/' . $vendor_id . '/' . $zip_file_name;

				$product_img_path = '';
				if ($request->hasFile('upload_bulk_data')) {
					$product_img = $request->file('upload_bulk_data');
					$original_file_name = $request->file('upload_bulk_data')->getClientOriginalName();

					if ($product_img != null) {
						$product_img_path = $product_img->store('product_zip/' . $vendor_id);
					}

					//$product_img_file = pathinfo($profile_file_path,PATHINFO_FILENAME);
					$product_img_file = basename($product_img_path);
				}

				if (isset($product_img_file) && $product_img_file != '') {
					$current = Carbon::now();
					// add 3 days to the current time
					$ZipDeleteDate = $current->addDays(3);

					$arr_update = [];
					$arr_update['zip_name'] = isset($product_img_file) ? $product_img_file : "";
					$arr_update['user_id'] = $vendor_id;
					$arr_update['zip_status'] = '0';
					$arr_update['delete_date'] = $ZipDeleteDate;
					$arr_update['is_deleted'] = 0;

					$store = $this->ZipExtractionModel->create($arr_update);

					$extractFolderResult = $this->extractZipFile($vendor_id, $product_img_file, $original_file_name);

					if (isset($extractFolderResult['status']) == "error") {

						$errorMessage = isset($extractFolderResult['msg']) ? $extractFolderResult['msg'] : "Error occured while uploading file.";

						$arr_update['zip_status'] = '2';
						$arr_update['zip_extraction_log'] = $errorMessage;
						//$arr_update['deleted_at'] = $ZipDeleteDate;

						$store = $this->ZipExtractionModel->where('zip_name', $product_img_file)->update($arr_update);

						/*Flash::error($extractFolderResult['msg']);
                        return redirect()->back();*/

						$response['message'] = $extractFolderResult['msg'];
						$response['status'] = 'warning';
						return response()->json($response);
					} else {
						$arr_update['zip_status'] = '1';

						$store = $this->ZipExtractionModel->where('zip_name', $product_img_file)->update($arr_update);

						\Artisan::call("cache:clear");

						/*
							Temporarily Removed for Demo 26-11-2020 
						if ($extractFolderResult['img_sku_count'] != $extractFolderResult['product_count']) {

							if (isset($extractFolderResult['invalid_images']) && count($extractFolderResult['invalid_images'])) {

								$response['message'] = "File has been uploaded, but some file wasn't in proper format. Invalid Images: " . implode(', ', $extractFolderResult['invalid_images']);
								$response['status'] = 'success';
								return response()->json($response);
							} else {

								$response['message'] = "File has been uploaded, but some file wasn't in proper format.";
								$response['status'] = 'success';
								return response()->json($response);
							}
						} else {

							$response['message'] = 'File has been uploaded.';
							$response['status'] = 'success';
							return response()->json($response);
						}*/

						$response['message'] = 'File has been uploaded.';
						$response['status'] = 'success';
						return response()->json($response);

					}
				} else {
					/*Flash::error('Error occured while uploading file.');
                  return redirect()->back();*/

					$response['message'] = 'Error occured while uploading file.';
					$response['status'] = 'error';
					return response()->json($response);
				}
			} else {

				/*Flash::error('Invalid file, please try again.');
                return redirect()->back(); */
				$response['message'] = 'Invalid file, please try again.';
				$response['status'] = 'warning';
				return response()->json($response);

			}

		} catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
			/* Flash::error('File size too large,please try again.');
            return redirect()->back();*/

			$response['message'] = 'File size too large, please try again.';
			$response['status'] = 'warning';
			return response()->json($response);

		}

	}

	public function extractZipFile($vendor_id, $zipFileName, $originalFileName) {

		$response = [];
		$filename = $moveImg = false;
		ini_set('max_execution_time', 0);

		try
		{

			$zip = new ZipArchive;
			$zip_extraction_status = $zip->open(storage_path('app/product_zip/' . $vendor_id . '/' . $zipFileName));
			
			//$res = $zip->open(storage_path('app/product_zip/'.$zip_file_name));
			if ($zip_extraction_status === true) {

				$filename = $zip->getNameIndex(1);


				$root_filename = str_replace("/", "", $filename);
				$files = explode('/', $filename);

				$filename = isset($files[0]) ? $files[0] : '';
				$root_filename = $filename;				

				$zip->extractTo(storage_path('app/product_zip/' . $vendor_id . '/all_extracted_zip'));
				$zip->close();

				if ($filename != false) {
					$file_path = storage_path('app/product_zip/' . $vendor_id . '/all_extracted_zip/' . $root_filename);
					

					// $is_sub_directories = glob($file_path . '/*', GLOB_ONLYDIR);

					$is_sub_directories = "";
					if(!is_dir($file_path)){ 
						$is_sub_directories = glob($file_path . '/*', GLOB_ONLYDIR);
						
					} else {
						
						$files = explode(".",$originalFileName);						
						
						$filename = isset($files[0]) ? $files[0] : '';
						
						$file_path = storage_path('app/product_zip/' . $vendor_id . '/all_extracted_zip/' . $root_filename);

						$is_sub_directories = glob($file_path . '/*/*', GLOB_ONLYDIR);
						
					}

					if (!empty($is_sub_directories)) {
						$response['status'] = 'error';
						$response['msg'] = 'Subfolder(s) are not allowed in zip.';

						$deleteFlag = 'true';
						if ($deleteFlag == true) {
							$deleteFolder = Storage::deleteDirectory('product_zip/' . $vendor_id . '/all_extracted_zip/' . $root_filename);
						}
						return $response;
					}
					
					$moveImg = $this->moveExtractedImage($vendor_id, $root_filename);				

					if ($moveImg == null) {
						$update_img_path = $this->update_img_path();

					} else {
						return $moveImg;
					}

					return true;
				} else {
					$response['status'] = 'error';
					$response['msg'] = 'Invalid zip file, please try agian.';
					return $response;
				}

			} else {
				$response['status'] = 'error';
				$response['msg'] = 'Something went wrong, please try again later.';
				return $response;
			}
		} catch (Exception $e) {
			$response['status'] = 'error';
			$response['msg'] = $e->getMessage();
			return $response;
		}
	}

	
	/*Move extracted files into product_images folder*/
	public function moveExtractedImage($vendor_id, $originalFileName) {
		
		$response = $invalidImages = $productSku = [];
		try
		{
			$files = Storage::allfiles('product_zip/' . $vendor_id . '/all_extracted_zip/' . $originalFileName);	


			/* Build SKU => User id */
			// dd($files);
			$arr_uploaded_sku = [];
			if (isset($files) == false || (isset($files) && sizeof($files) <= 0)) {
				$response['status'] = 'error';
				$response['msg'] = 'Please select valid zip file.';
				return $response;
			}
			
			foreach ($files as $file) {
				$exp = explode("/", $file);
				// dd($exp);				
				if(!empty($exp) && isset($exp[5])){					
					if(isset($exp[5])){						
						$key = 4;
						foreach($exp as $imgs){
							if($key >= 4){
								$sku_name = isset($exp[$key]) ? $exp[$key] : "";
								if($sku_name != ""){
									$arr_uploaded_sku[] = $sku_name;
								}
								$key++;
							}
						}
					}
				} else {					
					$sku_name = pathinfo($file, PATHINFO_FILENAME);
					$arr_uploaded_sku[] = $sku_name;
				}
				
			}

			$arr_valid_sku = DB::table('vw_products_basic_details')
				->whereIn('sku', $arr_uploaded_sku)
				->where('user_id', $vendor_id)
				->where('is_deleted', 0)
				->select(['sku'])
				->get()
				->toArray();

			$arr_valid_sku = array_column($arr_valid_sku, 'sku', 'sku');

			if (count($arr_valid_sku) == 0) {
				$response['status'] = 'error';
				$response['msg'] = "Please check product SKU's and image dimension.";
				return $response;
			}


			//echo "<pre>";print_r($files);
			$arr_check_cnt = [];
			$arr_check_cnt_new = [];
			if (isset($files) && sizeof($files) > 0) {

				$deleteFlag = false;
				$arr_to_be_inserted = [];
				$img_cnt = 0;
				foreach ($files as $k=>$file) {
					$exp_array = explode("/", $file);	
					
					if(!empty($exp_array) && isset($exp_array[5])){			
						
						if(isset($exp_array[5])){	

							$key = 4;
							
							/*Added limit of multiple images as 5*/
							
							foreach($exp_array as $imgs){
								if($key >= 4 && $img_cnt < 5){
									$ext_sku = isset($exp_array[$key]) ? $exp_array[$key] : "";
									if($ext_sku != ""){
										$extracted_sku = pathinfo($exp_array[$key], PATHINFO_FILENAME);
									}
									$key++;
									$img_cnt++;
									/*dump($img_cnt); */
								}
							}						
							
						}
					} else {					
						$extracted_sku = pathinfo($file, PATHINFO_FILENAME);
					}	

					

					/*$isAccesible = $this->is_vendor_acceesible($fileName);*/
					// if (isset($arr_valid_sku[$extracted_sku]) == false) {
					// 	continue;
					// }

					$fullPathSource = Storage::getDriver()->getAdapter()->applyPathPrefix($file);
					$ext = pathinfo($fullPathSource, PATHINFO_EXTENSION);
					$ext = strtolower($ext);
					try
					{
						$image_size = getimagesize($fullPathSource);
						$width = $image_size[0];
						$height = $image_size[1];	
					}
					catch(\Exception $e)
					{
						continue;
					}


					if ($ext == "") {
						$invalidImages[] = basename($file);
						continue;
					}

					/*if($ext!="" && ($ext=="jpg" || $ext=="jpeg" || "png") && $width >= 407 && $height >= 500)*/
					
						
					if(isset($exp_array[5])){						
							
						if(isset($exp_array[5])){

							Storage::disk('local')->makeDirectory('product_multiple_image');
							/*Storage::disk('local')->makeDirectory('product_multiple_image/product_multiple_img_thumb');*/


							$fullPathDest1 = Storage::disk('local')->getDriver()->getAdapter()
							->applyPathPrefix('product_multiple_image/' . basename($file));

							$copyImagePath1 = Storage::disk('local')->getDriver()->getAdapter()
						     ->applyPathPrefix('product_multiple_image/product_img_thumb/' . basename($file));

							// $copyImagePath = Storage::disk('local')->getDriver()->getAdapter()
							// ->applyPathPrefix('product_multiple_image/product_multiple_img_thumb/' . basename($file));

							$product_name = 'product_multiple_image/' . basename($file);
							
							if(isset($exp_array[4])){
									$cb_products_details = DB::table('products_details')
													->leftJoin('products', 'products.id', '=', 'products_details.product_id')
													->where('products.user_id', $vendor_id)
													->where('products_details.sku', $exp_array[4])
													->where('products_details.is_deleted', '=', 0)
													->select(['products_details.sku','products_details.product_id','products_details.id'])
													->first();
													//echo "<pre>";print_r($cb_products_details);die;
													if(!empty($cb_products_details)){
														$productMultipleImageUpdateArr['product_id'] = $cb_products_details->product_id;
														$productMultipleImageUpdateArr['product_detail_id'] = $cb_products_details->id;
														$productMultipleImageUpdateArr['sku'] = $cb_products_details->sku;
														$productMultipleImageUpdateArr['product_image'] = $product_name;				
														

														$arr_sku_name = isset($cb_products_details->sku) ? $cb_products_details->sku : "";
														
														$arr_check_cnt[$arr_sku_name][] = 1; 
														if (array_key_exists($arr_sku_name,$arr_check_cnt))
														  {
														  		/*$arr_check_cnt_new[$arr_sku_name][] = count($arr_check_cnt[$arr_sku_name]);*/
														  		$arr_img_cnt  = count($arr_check_cnt[$arr_sku_name]);

														  		/*Condition for adding number of multiple images*/
														  		if($arr_img_cnt < 6){
														  				$store_multi_image = $this->ProductMultipleImagesModel->create($productMultipleImageUpdateArr);


														  				if (\File::copy($fullPathSource, $copyImagePath1)) {

																			$productSku[] = pathinfo($fullPathSource, PATHINFO_FILENAME);
																			
																			$fileMove = File::move($fullPathSource, $fullPathDest1);

																			if ($fileMove == true) {

																				$deleteFlag = true;
																			}
																		}
														  		}

														  }
													}

								
							}
						}
					} else {		
						
 						
					$fullPathDest = Storage::disk('local')->getDriver()->getAdapter()
						->applyPathPrefix('product_image/' . basename($file));

					$copyImagePath = Storage::disk('local')->getDriver()->getAdapter()
						->applyPathPrefix('product_image/product_img_thumb/' . basename($file));

					if (\File::copy($fullPathSource, $copyImagePath)) {

						$productSku[] = pathinfo($fullPathSource, PATHINFO_FILENAME);
						
						$fileMove = File::move($fullPathSource, $fullPathDest);

						if ($fileMove == true) {

							$deleteFlag = true;
						}
					}
					}

					

				}
				//die;
				

				/*$vendor_id = 0;
					$login_user = Sentinel::check();

					if ($login_user == true) {
						$vendor_id = $login_user->id;
					}

					if ($vendor_id != 0) {
						$cb_products = DB::table('products')
							->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
							->where('products.user_id', $vendor_id)
							->whereIn('products_details.sku', $productSku)
							->where('products.is_deleted', '=', 0)
							->get()->toArray();

						if (count($cb_products) == 0) {

							$response['status'] = 'error';
							$response['msg'] = "Please check product SKU's and image dimension.";
							return $response;

						}

				*/

				//dump($arr_check_cnt,$arr_check_cnt_new);	

				$updateImagePath = $this->update_img_path($vendor_id, $arr_uploaded_sku);

				//Delete folder when files moves successfully
				if ($deleteFlag == true) {
					$deleteFolder = Storage::deleteDirectory('product_zip/' . $vendor_id . '/all_extracted_zip/' . $originalFileName);
				}

				$response['img_sku_count'] = (isset($arr_valid_sku)) ? count($arr_valid_sku) : "";
				$response['product_count'] = count($arr_uploaded_sku);
				$response['invalid_images'] = $invalidImages;
				return $response;

				} else {
				$response['status'] = 'error';
				$response['msg'] = 'Please select valid zip file.';
				return $response;
			}

		} catch (Exception $e) {
			$response['status'] = 'error';
			$response['msg'] = $e->getMessage();
			return $response;
		}

	}

	public function update_img_path($vendor_id, $skuArray) {

		set_time_limit(0);
		/*$vendor_id = 0;
			$login_user = Sentinel::check();
			if ($login_user == true) {
				$vendor_id = $login_user->id;
		*/

		if ($vendor_id == 0) {
			return false;
		}

		if ($vendor_id != 0) {
			$cb_products = DB::table('products')
				->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
				->where('products.user_id', $vendor_id)
				->whereIn('products_details.sku', $skuArray)
				->where('products.is_deleted', '=', 0)
				->get()->toArray();

			try
			{
				DB::beginTransaction();
				foreach ($cb_products as $cb_key => $cb_value) {

					$product_sku = $cb_value->sku;
					$product_id = $cb_value->product_id;

					$ext = pathinfo($cb_value->image, PATHINFO_EXTENSION);

					$file_extension_arr = array('0' => 'png', '1' => 'jpeg', '2' => 'jpg', '3' => 'JPG', '4' => 'PNG', '5' => 'JPEG');

					foreach ($file_extension_arr as $key => $ext) {
						// $ext = 'jpg';
						$product_image_path = "";
						$product_image_path = 'product_image/' . $product_sku . '.' . $ext;
						$productImageThumbPath = 'product_image/product_img_thumb/' . $product_sku . '.' . $ext;

						$file = basename($product_image_path);

						$exists = file_exists(base_path() . '/storage/app/product_image/' . $file);
						if ($exists == false) {
							continue;
						}

						$productUpdateArr = [];
						$productDetailsUpdateArr = [];
						$productImageUpdateArr = [];

						$productUpdateArr['product_image'] = $product_image_path;
						$productUpdateArr['product_image_thumb'] = $productImageThumbPath;

						$productImageUpdateArr['product_image'] = $product_image_path;

						$productDetailsUpdateArr['image'] = $productUpdateArr['product_image'];
						$productDetailsUpdateArr['image_thumb'] = $productUpdateArr['product_image_thumb'];

						/*$updateStatus = DB::table('products')->where('id', $product_id)
								->update($productUpdateArr);

							$a = DB::table('products_details')->where('sku', $product_sku)
								->update(array('image' => $product_image_path, 'image_thumb' => $productImageThumbPath));

						*/

						//if product having all data not any missing value then it will be complete
						/*--------comment(1) start---------------*/			
						// if ((isset($cb_value->product_name) && $cb_value->product_name != '') &&
						// 	(isset($cb_value->brand) && $cb_value->brand != '') &&
						// 	(isset($cb_value->unit_wholsale_price) && $cb_value->unit_wholsale_price != '') &&	
						// 	(isset($cb_value->retail_price) && $cb_value->retail_price != '') &&
						// 	(isset($cb_value->available_qty) && $cb_value->available_qty != '' && $cb_value->available_qty != 0) &&
						// 	(isset($cb_value->shipping_charges) && $cb_value->shipping_charges != '') &&
						// 	(isset($cb_value->sku) && $cb_value->sku != '') &&
						// 	(isset($cb_value->category_id) && $cb_value->category_id != '') &&
						// 	(isset($cb_value->case_quantity) && $cb_value->case_quantity != '' && $cb_value->case_quantity != 0) &&
						// 	(isset($cb_value->shipping_type) && $cb_value->shipping_type != '') &&
						// 	(isset($cb_value->product_min_qty) && $cb_value->product_min_qty != '' && $cb_value->product_min_qty != 0)
						// ) {
						// 	$productUpdateArr['is_active'] = '1';
						// 	$productUpdateArr['product_complete_status'] = 4;
						// } else {

						// 	//if product having all data not any missing value then it will be complete

						// 	if ((isset($cb_value->product_name) && $cb_value->product_name != '') &&
						// 		(isset($cb_value->brand) && $cb_value->brand != '') &&
						// 		(isset($cb_value->unit_wholsale_price) && $cb_value->unit_wholsale_price != '') &&
						// 		(isset($cb_value->retail_price) && $cb_value->retail_price != '') &&	
						// 		(isset($cb_value->available_qty) && $cb_value->available_qty != '') &&
						// 		(isset($cb_value->shipping_charges) && $cb_value->shipping_charges != '') &&
						// 		(isset($cb_value->sku) && $cb_value->sku != '') &&
						// 		(isset($cb_value->category_id) && $cb_value->category_id != '') &&
						// 		(isset($cb_value->case_quantity) && $cb_value->case_quantity != '') &&
						// 		(isset($cb_value->shipping_type) && $cb_value->shipping_type != '')
						// 	) {
						// 		$productUpdateArr['is_active'] = '1';
						// 		$productUpdateArr['product_complete_status'] = 4;
						// 	} else {
						// 		$productUpdateArr['is_active'] = '2';
						// 		$productUpdateArr['product_complete_status'] = 0;
						// 	}

						// }

						/*--------comment(1) end---------------*/
						/*
							New code for updating product complete status for new field and for avoiding require validation
							for other charges field 8-oct-21 

							uncomment the above comment(1)code and comment below code for previous functionality
						*/
						if ((isset($cb_value->product_name) && $cb_value->product_name != '') &&
							(isset($cb_value->brand) && $cb_value->brand != '') &&
							(isset($cb_value->unit_wholsale_price) && $cb_value->unit_wholsale_price != '') &&	
							
							(isset($cb_value->restock_days) && $cb_value->restock_days != '') &&
							(isset($cb_value->available_qty) && $cb_value->available_qty != '' && $cb_value->available_qty != 0) &&
							
							(isset($cb_value->sku) && $cb_value->sku != '') &&
							(isset($cb_value->category_id) && $cb_value->category_id != '') &&
							(isset($cb_value->case_quantity) && $cb_value->case_quantity != '' && $cb_value->case_quantity != 0) &&
							
							(isset($cb_value->product_min_qty) && $cb_value->product_min_qty != '' && $cb_value->product_min_qty != 0)
						) {
							$productUpdateArr['is_active'] = '1';
							$productUpdateArr['product_complete_status'] = 4;
						} else {

							//if product having all data not any missing value then it will be complete

							if ((isset($cb_value->product_name) && $cb_value->product_name != '') &&
								(isset($cb_value->brand) && $cb_value->brand != '') &&
								(isset($cb_value->unit_wholsale_price) && $cb_value->unit_wholsale_price != '') &&
								(isset($cb_value->restock_days) && $cb_value->restock_days != '') &&
								(isset($cb_value->available_qty) && $cb_value->available_qty != '') &&
								
								(isset($cb_value->sku) && $cb_value->sku != '') &&
								(isset($cb_value->category_id) && $cb_value->category_id != '') &&
								(isset($cb_value->case_quantity) && $cb_value->case_quantity != '') 
								
							) {
								$productUpdateArr['is_active'] = '1';
								$productUpdateArr['product_complete_status'] = 4;
							} else {
								$productUpdateArr['is_active'] = '2';
								$productUpdateArr['product_complete_status'] = 0;
							}

						}		



						DB::table('products')->where('id', $product_id)
							->update($productUpdateArr);

						DB::table('products_details')->where('sku', $product_sku)
							->update($productDetailsUpdateArr);

						DB::table('product_images')->where('product_id', $product_id)
							->update($productImageUpdateArr);

					}

					$this->ElasticSearchService->index_product($product_id);

				}

				DB::commit();

				return true;
			} catch (Exception $e) {
				DB::rollback();
				return false;
			}
		} else {
			return false;
		}
	}

	/* check the current login user can access this sku?  */
	public function is_vendor_acceesible($sku = '') {
		$isAccessible = false;
		$currentLoginUserId = 0;

		$loginUser = Sentinel::check();
		if ($loginUser == true) {
			$currentLoginUserId = $loginUser->id;
		}

		if ($currentLoginUserId != 0 && $sku != '') {
			$isAccessibleData = $this->ProductDetailsModel->where('sku', $sku)
				->whereHas('productDetails', function ($q) use ($currentLoginUserId) {
					return $q->where('user_id', $currentLoginUserId);
				})
				->count();
			if ($isAccessibleData > 0) {
				return $isAccessible = true;
			}
		}

		return $isAccessible;
	}

	/*public function update_img_path()
		    {

		        set_time_limit(0);

		        $vendorId = 0 ;

		        $loginUser = Sentinel::check();

		        if($loginUser == true)
		        {
		            $vendorId = $loginUser->id;
		        }

		        if($vendorId != 0)
		        {

		            $cbProducts = DB::table('products')
		            ->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
		            ->where('products.user_id',$vendorId)

		            ->get()->toArray();

		            try
		            {
		                foreach ($cbProducts as $cbKey => $cbValue)
		                {
		                    $productSku = $cbValue->sku;
		                    $productId = $cbValue->product_id;
		                    $productImagePath = 'product_image/'.$productSku.'.jpg';

		                    $file = basename($productImagePath);
		                    $exists = file_exists(base_path().'/storage/app/product_image/'.$file);

		                    if($exists=="true")
		                    {
		                        DB::table('products')->where('id',$productId)->update(array(
		                                     'product_image'=>$productImagePath,'product_image_thumb'=>$productImagePath,'is_active'=>'1','product_complete_status'=>4));

		                        DB::table('products_details')->where('sku',$productSku)->update(array(
		                                     'image'=>$productImagePath,'image_thumb'=>$productImagePath));

		                        DB::table('product_images')->where('product_id',$productId)->update(array(
		                                     'product_image'=>$productImagePath));
		                    }
		                }

		                return true;
		            }
		            catch(Exception $e)
		            {
		                return $e;
		            }
		        }
		        else
		        {
		            return false;
		        }

	*/

}
