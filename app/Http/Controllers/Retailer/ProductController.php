<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CategoryModel;
use App\Models\ProductsModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SubCategoryModel;
use App\Models\FavoriteProductModel;


use Validator;
use DB;
use Datatables;
use Sentinel;

class ProductController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 27 June 2019
    */

    public function __construct(CategoryModel $CategoryModel,
    							ProductsModel $ProductsModel,
    							ProductDetailsModel $ProductDetailsModel,
    							ProductImagesModel $ProductImagesModel,
    							CategoryTranslationModel $CategoryTranslationModel,
    							ProductsSubCategoriesModel $ProductsSubCategoriesModel,
    							UserModel $UserModel,
    							RoleModel $RoleModel,
    							RoleUsersModel $RoleUsersModel,
    							SubCategoryModel $SubCategoryModel,
                                FavoriteProductModel $FavoriteProductModel 
                                )
    {
    	$this->UserModel    	  = $UserModel;
  		$this->RoleModel          = $RoleModel;
    	$this->RoleUsersModel     = $RoleUsersModel;
    	$this->SubCategoryModel   = $SubCategoryModel;
    	$this->CategoryModel      = $CategoryModel;
    	$this->BaseModel          = $ProductsModel;
    	$this->ProductDetailsModel= $ProductDetailsModel;
    	$this->ProductImagesModel = $ProductImagesModel;
    	$this->CategoryTranslationModel = $CategoryTranslationModel;
    	$this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
        $this->FavoriteProductModel = $FavoriteProductModel;
	    $this->product_img        = base_path().'storage/app/';
    	$this->arr_view_data      = [];
    	$this->module_title       = "Products";
    	$this->module_view_folder = 'maker.product';
        $this->maker_panel_slug   = config('app.project.maker_panel_slug');
        $this->module_url_path    = url($this->maker_panel_slug.'/products');
    
    }

    public function index()
    {   
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Products List';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['is_search_box_visible']     =  'yes';

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_maker_products(Request $request)
    {
      $product_tbl_name 	= $this->BaseModel->getTable();        
      $prefixed_product_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

      $category__tbl 		= $this->CategoryModel->getTable();
      $prefix_category_tbl = DB::getTablePrefix().$this->CategoryModel->getTable();

      $category_trans_tbl_name  	= $this->CategoryTranslationModel->getTable();        
      $prefixed_category_trans_tbl  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

       $user_table =  $this->UserModel->getTable();
	   $prefix_user_table = DB::getTablePrefix().$user_table;

      $obj_products = DB::table($product_tbl_name)
                                ->select(DB::raw($prefixed_product_tbl.".id,".  
                                                 $prefixed_product_tbl.'.is_active,'.
                                                 $prefixed_product_tbl.'.product_name,'.
                                                 $prefix_user_table.'.first_name,'.
                                                 $prefixed_category_trans_tbl.'.category_name,'.
                                                 $prefixed_product_tbl.".created_at,".
                                                 $prefixed_product_tbl.".product_image,".
                                                 $prefixed_product_tbl.".unit_wholsale_price,".
                                                 $prefixed_product_tbl.".retail_price"	

                                               ))
                                ->leftjoin($category__tbl,$prefix_category_tbl.'.id','=',$prefixed_product_tbl.'.category_id')
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_product_tbl.'.maker_id')
                                ->leftjoin($category_trans_tbl_name,$category__tbl.'.id','=',$prefixed_category_trans_tbl.'.category_id');
                                
         
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_category_name']) && $arr_search_column['q_category_name']!="")
        {
            $search_term      = $arr_search_column['q_category_name'];
            $obj_products = $obj_products->where($prefixed_category_trans_tbl.'.category_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_unit_wholsale_price']) && $arr_search_column['q_unit_wholsale_price']!="")
        {
            $search_term      = $arr_search_column['q_unit_wholsale_price'];
            $obj_products = $obj_products->where($prefixed_product_tbl.'.unit_wholsale_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_retail_price']) && $arr_search_column['q_retail_price']!="")
        {
            $search_term      = $arr_search_column['q_retail_price'];
            $obj_products = $obj_products->where($prefixed_product_tbl.'.retail_price','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_product_name']) && $arr_search_column['q_product_name']!="")
        {
            $search_term  = $arr_search_column['q_product_name'];
            $obj_products = $obj_products->where($prefixed_product_tbl.'.product_name','LIKE', '%'.$search_term.'%');
        }        

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            
            $obj_products     = $obj_products->where($prefixed_product_tbl.'.is_active',$search_term);
        }
  

        $current_context = $this;

        $json_result  = Datatables::of($obj_products);

        /* Modifying Columns */
        $json_result =  $json_result->editColumn('product_image',function($data) use ($current_context)
                        {

                            if($data->product_image)
                            {
                                $product_img_path = url('/storage/app/'.$data->product_image);
                            }
                            else
                            {
                                $product_img_path = url('/assets/images/default-product.png');
                            }

                            return  $product_img_path;
                        })
                        
                        ->make(true);

        $build_result = $json_result->getData();

        return response()->json($build_result);
    }

    function changeStatus(Request $request)
    {
        $product_status = $request->input('productStatus');
        $product_id     = $request->input('product_id');

        if($product_status=='1')
        {
            $this->BaseModel->where('id',$product_id)->update(['is_active'=>1]);
            
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been activated.';

        }
        elseif($product_status=='0')
        {
            $this->BaseModel->where('id',$product_id)->update(['is_active'=>0]);

            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been deactivated.';
        }
        else
        {
            $response['status']  = 'ERROR';
            $response['message'] = 'Something went wrong, please try again.';
        }
            return response()->json($response); 
    }

    public function storeProduct(Request $request)
    {
      
        try
        {
            $is_update = false;
            $arr_rules = [];        
            $loggedInUserId = 0;
            $form_data = $request->all();        
            $json_data = json_encode($form_data);

            if($request->has('product_id'))
            {
                $is_update = true;
            }

            $product_id = $request->input('product_id');
            $arr_rules = [  
                            'product_name'            => 'required',  
                            'case_qty'                => 'required',
                            'unit_wholsale_price'     => 'required',
                            'retail_price'            => 'required',
                            'product_description'     => 'required'                           
                        ];

            $validator = Validator::make($request->all(),$arr_rules); 

            if($validator->fails())
            {        
               $response['status']      = 'warning';
               $response['description'] = 'Form validations failed, please check all fields.';

              return response()->json($response);
            }  

            DB::beginTransaction();

            $user = Sentinel::check();

            if($user)
            {
                $loggedInUserId = $user->id;
            }

            /* Check if product already exists with given name*/
            $is_duplicate = $this->BaseModel->where('product_name',$form_data['product_name']);

            if($is_update)
            {
                $is_duplicate->where('id','<>',$product_id);
            }
            
            $is_duplicate = $is_duplicate->count()>0;        
           
            if($is_duplicate)
            {  
               $response['status']      = 'warning';
               $response['description'] = str_singular($this->module_title).' already exist with '.$form_data['product_name'].' name';

               return response()->json($response);
            } 

            //upload product image and get path                 
            $product_img_file_path = '';            
            $product_image = isset($form_data['product_primary_image'])?$form_data['product_primary_image']:null;
            
            if($product_image!=null)
            {
                //Validation for product image
                $file_extension = strtolower($product_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid product image, please try again.';

                    return response()->json($response);
                }

                $product_img_file_path            = $product_image->store('product_image');

                //delete old image from directory in any available
                $old_product_img                  = isset($form_data['old_product_image'])?$form_data['old_product_image']:'';

                $unlink_old_img_path              = $this->product_img.$old_product_img;
                            
                if(file_exists($unlink_old_img_path))
                {
                    @unlink($unlink_old_img_path);  
                }
            }
            else
            {
                $product_img_file_path = $form_data['old_product_image'];
            }

            if(isset($form_data['is_best_seller']))
            {
                $is_best_seller = $form_data['is_best_seller'];
            }
            else
            {
                $is_best_seller = 0;
            }

            if(isset($form_data['is_tester_available']))
            {
                $is_tester_available = $form_data['is_tester_available'];
            }
            else
            {
                $is_tester_available = 0;
            }

            $product = $this->BaseModel->firstOrNew(['id' => $product_id]);  

            $product->product_name            = $form_data['product_name'];
            $product->available_qty           = $form_data['case_qty'];
            $product->unit_wholsale_price     = $form_data['unit_wholsale_price'];
            $product->retail_price            = $form_data['retail_price'];
            $product->description             = $form_data['product_description'];
            $product->product_image           = $product_img_file_path;
            $product->user_id                 = $loggedInUserId;                        
            $product->is_active               = 1;
            $product->is_best_seller          = $is_best_seller;
            $product->is_tester_available     = $is_tester_available;
            $product->product_complete_status = 1;
            $product->maker_id                = $loggedInUserId;
            $is_store = $product->save();   

            if($is_store)
            {   
                $arr_event    = [];
                if($is_update == false)
                {
                    $arr_event['ACTION']       = 'ADD';
                    $arr_event['MODULE_ID']    =  $product->id;
                }
                else
                {
                    $arr_event['ACTION']    = 'EDIT';
                    $arr_event['MODULE_ID'] = $product_id;
                }

                $arr_event['MODULE_TITLE']  = $this->module_title;  
                $arr_event['USER_ID']       = $loggedInUserId;             

                $this->save_activity($arr_event);

                DB::commit();
                $response['status']      = 'success';
                
                if($is_update)
                {
                    $response['description'] = str_singular($this->module_title).'has been updated.';
                }
                else
                {
                    $response['description'] = str_singular($this->module_title).'has been added.';
                }               

                return response()->json($response);
            }
            else
            {   
                DB::rollback();
                $response['status']      = 'failure';
                $response['description'] = 'Error occurred while adding '.str_singular($this->module_title);

                return response()->json($response);
            }

        }catch(Exception $e)
        {
            DB::rollback();
            
            $response['status']      = 'failure';
            $response['description'] = $e->getMessage();

            return response()->json($response);
        }
    }



}
