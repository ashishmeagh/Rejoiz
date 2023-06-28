<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CategoryModel;
use App\Models\ProductsModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductUpdatedByVendorModel;
use App\Models\ProductImagesModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SubCategoryModel;
use App\Models\MakerModel;
use App\Models\BrandsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\ProductMultipleImagesModel;
use Sleimanx2\Plastic\Searchable;
use App\Common\Traits\MultiActionTrait;
use App\Common\Services\ProductService;
use App\Common\Services\GeneralService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\HelperService;
use App\Models\SizeModel;
use App\Models\ProductSizesModel;

use Validator;
use DB;
use Datatables;
use Sentinel;
use Image;
use Flash;
use Excel;

class ProductController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 24 June 2019
    */
    use MultiActionTrait;    
    public function __construct(CategoryModel $CategoryModel,
                                ProductsModel $ProductsModel,
                                ProductUpdatedByVendorModel $ProductUpdatedByVendorModel,
                                ProductDetailsModel $ProductDetailsModel,
                                ProductImagesModel $ProductImagesModel,
                                CategoryTranslationModel $CategoryTranslationModel,
                                ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                ProductMultipleImagesModel $ProductMultipleImagesModel,
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                SubCategoryModel $SubCategoryModel,
                                MakerModel $MakerModel,
                                BrandsModel $BrandsModel,
                                ProductService $ProductService,
                                GeneralService $GeneralService,
                                ElasticSearchService $ElasticSearchService,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                HelperService $HelperService,
                                SizeModel $SizeModel,
                                ProductSizesModel $ProductSizesModel
                              )
    {

      $this->UserModel            = $UserModel;
      $this->RoleModel            = $RoleModel;
      $this->RoleUsersModel       = $RoleUsersModel;
      $this->MakerModel           = $MakerModel;
      $this->BrandsModel          = $BrandsModel;
      $this->ProductUpdatedByVendorModel      = $ProductUpdatedByVendorModel;
      $this->ProductService       = $ProductService;
      $this->GeneralService       = $GeneralService;
      $this->ElasticSearchService = $ElasticSearchService;
      $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
      $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
      $this->ProductMultipleImagesModel     = $ProductMultipleImagesModel;
      $this->SubCategoryModel   = $SubCategoryModel;
      $this->CategoryModel      = $CategoryModel;
      $this->BaseModel          = $ProductsModel;
      $this->ProductDetailsModel= $ProductDetailsModel;
      $this->ProductImagesModel = $ProductImagesModel;
      $this->CategoryTranslationModel   = $CategoryTranslationModel;
      $this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
      $this->HelperService              = $HelperService;
      $this->SizeModel					= $SizeModel;
		  $this->ProductSizesModel			= $ProductSizesModel;   

      $this->arr_view_data      = [];
      $this->module_title       = "Products";
      $this->module_view_folder = "admin.products";    
      $this->admin_panel_slug   = config('app.project.admin_panel_slug');
      $this->module_url_path    = url($this->admin_panel_slug.'/products');
      $this->curr_panel_slug    =  config('app.project.admin_panel_slug');
      $this->product_img        = base_path().'storage/app/';
      $this->product_default_img_path = config('app.project.img_path.product_default_images');
    } 

    public function index()
    {
      $this->arr_view_data['page_title']        = str_plural($this->module_title);
      $this->arr_view_data['module_title']    = str_plural($this->module_title);
      $this->arr_view_data['module_url_path'] = $this->module_url_path;
      
      return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_all_products(Request $request)
    {
      $arr_search_column = $request->input('column_filter');
      $obj_products      = $this->ProductService->get_product_list('',$arr_search_column);

      $current_context = $this;

      $json_result  = Datatables::of($obj_products);
        // dd($json_result);

        /* Modifying Columns */
      $json_result =  $json_result->editColumn('enc_id',function($data) use ($current_context)
                        {
                            return  base64_encode(($data->id));
                        })

                        ->editColumn('product_image',function($data) use ($current_context)
                          { 
                              $image_name = (isset($data->product_image))? $data->product_image:"";
                              $image_type = "product";
                              $is_resize = 0;
                              $product_img_path = imagePath($image_name, $image_type, $is_resize);                              

                              return  $product_img_path;
                          })

                        ->editColumn('brand_name',function($data) use ($current_context)
                        {
                            return $brand_name = isset($data->brand_name) && $data->brand_name!=''?$data->brand_name:'N/A';
                        })

                        ->editColumn('company_name',function($data) use ($current_context)
                        {

                          return  $company_name = isset($data->company_name) && $data->company_name!=''?$data->company_name:'N/A';
                        })

                        ->editColumn('created_at',function($data) use ($current_context)
                        {
                            return  us_date_format($data->created_at);
                        })

                        ->editColumn('is_active',function($data) use($current_context){
                           $status = '';

                           if($data->is_active == 0)
                           {
                             $status = '<span class="label label-success">Rejected</span>';
                           }
                           if($data->is_active == 1)
                           {
                             $status = '<span class="label label-success">Approved</span>'; 
                           }
                           if($data->is_active == 2)
                           {
                             $status ='<span class="label label-success">Pending</span>';
                           }

                            return $status; 

                        })
                        
                        ->make(true);

        $build_result = $json_result->getData();
        return response()->json($build_result);
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
      if($prod_size_arr){
        foreach($prod_size_arr as $val){
          $html .= '<tr>
                      <td scope="col">'.$val->size.'</td>
                      <td scope="col">'.$val->size_inventory.'</td>
                    </tr>';
                  }
      }else {
          $html .= '<tr>
                        <td scope="col">No data</td>
                        <td scope="col">No data</td>
                      </tr>';
                    }
      $html .= "</table>";
                    $response['status']   = 'ERROR';
                    $response['msg']      = 'success';
                    $response['html']      = $html;
  
            return response()->json($response);
    }

    public function get_export_admin_product(Request $request)
    {
      $type  = 'csv'; 
      $data = $arr_products = $arrayResponseData = [];

      $arr_search_column = $request->all();
      $arr_products      = $this->ProductService->get_product_list('',$arr_search_column)->get()->toArray();

      if(count($arr_products) <= 0)
      {
          $response['status']      = 'error';
          $response['message']      = 'No data available for export';
      
          return response()->json($response);
      }
      
      foreach($arr_products as $key => $value)
      { 

        $vendor_status = 'Completed';
        if($value->product_complete_status == 4)
        {
          $vendor_status =  'Completed';
        }
        else
        {
           $vendor_status = 'Incomplete';
        }       

        $admin_status = 'Approved';
          if($value->is_active == 1)
          {
            $admin_status =  'Approved';
          }
          else if($value->is_active == 0)
          {
             $admin_status = 'Rejected';
          }
          else
          {
            $admin_status = 'Pending';
          }

        $arrayResponseData['Product Name']        = $value->product_name;
        $arrayResponseData['Vendor']              = $value->company_name;
        $arrayResponseData['Brand Name']          = $value->brand_name;
        $arrayResponseData['Created On']          = $value->created_at;
        $arrayResponseData['Vendor Status']       = $vendor_status;
        $arrayResponseData['Admin Status']        = $admin_status;
        
        array_push($data,$arrayResponseData);
      }

      return Excel::create('Admin Products', function($excel) use ($data) {
        
        $excel->sheet('Admin Products', function($sheet) use ($data)
        {
          $sheet->fromArray($data);
          $sheet->freezeFirstRow();  
          $sheet->cells("A1:M1", function($cells) {            
            $cells->setFont(array(              
              'bold'       =>  true
            ));

          });
        });
      })->download($type);

    }

    public function create()
    {
      $arr_makers = [];
      /* get all categories*/
      $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();
      
      /*Get all makers*/
      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $obj_user = DB::table($user_table)
            ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".wallet_address as wallet_address, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $role_table.".slug as slug, ".
                                     $role_table.".name as name, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->where($role_table.'.slug','=','maker')
            ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC')
                        ->get();
        if($obj_user)
        {
          $arr_makers = $obj_user->toArray();
        }

        
        $this->arr_view_data['arr_makers']      = $arr_makers; 
        $this->arr_view_data['categories_arr']  = $categories_arr;        
        $this->arr_view_data['page_title']      = "Create ".str_singular( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;        

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function edit($enc_product_id = false)
    {
      $product_arr = [];
      $arr_makers  = [];

      $product_id = base64_decode($enc_product_id);
      /* get all categories*/
      $categories_arr = $this->CategoryModel->where('is_active','1')->get()->toArray();
      $product_obj    = $this->BaseModel->where('id',$product_id)->first();
    
      if($product_obj)
      {
        $product_arr = $product_obj->toArray();
       /* dd($product_arr);*/
      }
      else
      {
        $response['status']            = 'failure';
              $response['description'] = 'Something went wrong, please try again.';            
              return response()->json($response);
      }

      /*Get all makers*/
        $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $obj_user = DB::table($user_table)
            ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".wallet_address as wallet_address, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $role_table.".slug as slug, ".
                                     $role_table.".name as name, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->where($role_table.'.slug','=','maker')
            ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC')
                        ->get();
        if($obj_user)
        {
          $arr_makers = $obj_user->toArray();
        }

        //Get Product sub categories
        $arr_sub_categories = [];
        $obj_sub_categories = $this->ProductsSubCategoriesModel->where('product_id',$product_id)->get();
        if($obj_sub_categories)
        {
          $arr_sub_categories  = $obj_sub_categories->toArray();
        }

        //Get all sub categories

        $product_cat_id = isset($product_arr['category_id'])?$product_arr['category_id']:'';
        $arr_all_sub_categories = [];
        $obj_all_sub_categories = $this->SubCategoryModel->where('is_active',1)
                                                          ->where('category_id',$product_cat_id) 
                                                          ->get();
        if($obj_all_sub_categories)
        {
          $arr_all_sub_categories  = $obj_all_sub_categories->toArray();
        } 

        $selected_sub_cat = [];
        $all_sub_cat      = [];

        $selected_sub_cat = array_column($arr_sub_categories, 'sub_category_id');
        $all_sub_cat      = array_column($arr_all_sub_categories, 'id');

        $this->arr_view_data['arr_sub_categories']      = $arr_sub_categories;  
        $this->arr_view_data['arr_all_sub_categories']  = $arr_all_sub_categories;    

        $this->arr_view_data['all_sub_cat']      = $all_sub_cat;  
        $this->arr_view_data['selected_sub_cat'] = $selected_sub_cat;
        $this->arr_view_data['arr_makers']      = $arr_makers; 
        $this->arr_view_data['product_arr']   = $product_arr;        
        $this->arr_view_data['categories_arr']  = $categories_arr;        
        $this->arr_view_data['page_title']      = "Update ".str_singular( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;        

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function storeProduct(Request $request)
    {
      try
      {
        $is_update = false;
        $arr_rules = [];        
        $loggedInUserId = 0;
        $form_data = $request->all(); 
        /*          dd($form_data);
        */          // dd($form_data );       
        $json_data = json_encode($form_data);
        if($request->has('product_id'))
          {
              $is_update = true;
          }

          $product_id = $request->input('product_id');
          $arr_rules = [  
                  // 'maker_id'           => 'required',  
                  'category_id'         => 'required',
                  'product_name'              => 'required',
                  'case_qty'                  => 'required',
                  'unit_wholesale_price'      => 'required',

                  //'retail_price'              => 'required',
                  'description'               => 'required',                  
                  'unit_wholesale_price'      => 'required',
                  'brand_name'                => 'required'
               
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
      $product_image = isset($form_data['product_image'])?$form_data['product_image']:null;
      
      if($product_image!=null)
      {
        //Validation for product image
        $file_extension = strtolower($product_image->getClientOriginalExtension());

              if(!in_array($file_extension,['jpg','png','jpeg']))
              {             
                  $arr_response['status']   = 'FAILURE';
          $arr_response['description']      = 'Invalid product image, please try again.';

          return response()->json($response);
              }

        $product_img_file_path = $product_image->store('product_image');

        //delete old image from directory in any available
        $old_product_img = isset($form_data['old_product_image'])?$form_data['old_product_image']:'';

        $unlink_old_img_path  = $this->product_img.$old_product_img;
                            
                if(file_exists($unlink_old_img_path))
                {
                    @unlink($unlink_old_img_path);  
                }
      }
      else
      {
        $product_img_file_path = $form_data['old_product_image'];
      }


      $product = $this->BaseModel->firstOrNew(['id' => $product_id]);  
      
      if($is_update=="true")
        {
          $product_complete_status = get_product_complete_status($product_id);
          /*$this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>$product_complete_status]);*/
         }
         else
         {
           /* $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>]);*/
           $product_complete_status = 1;
          }

      $product->category_id             = $form_data['category_id'];
      $product->product_name            = $form_data['product_name'];
      $product->available_qty           = $form_data['case_qty'];
      $product->unit_wholsale_price     = $form_data['unit_wholesale_price'];
      $product->retail_price            = $form_data['retail_price'];
      $product->description             = $form_data['description'];
      $product->product_image           = $product_img_file_path;
      $product->user_id                 = $form_data['maker_id'];         
      $product->is_active               = 1;
/*      $product->brand                   = $form_data['brand_name'];
*/      $product->product_complete_status = $product_complete_status;
    
      // $product->maker_id                 = $form_data['maker_id'];
          $is_store = $product->save(); 

      $is_exist_in_search = ProductsModel::search()->term('_id',$product_id)->get()->totalHits()['value'];
      if($is_exist_in_search==0)
      {
        $product_entry = ProductsModel::where('id',$product_id)->first();
        $product_entry->category_name = get_catrgory_name($product_entry->category_id);
        $product_entry->maker_name = get_maker_name($product_entry->user_id);
        $product_entry->document()->save(); 
      }
      else
      {
         $product_entry = ProductsModel::where('id',$product_id)->first();
        $product_entry->category_name = get_catrgory_name($product_entry->category_id);
        $product_entry->maker_name = get_maker_name($product_entry->user_id);
        $product_entry->document()->save(); 
      }




      if($is_store)
      { 
        if($is_update!="true")
        {
          $product_id = $product->id;
        //Store sub category
        $arr_sub_category = [];
        $arr_sub_category = isset($form_data['sub_category'])?$form_data['sub_category']:'';

        $this->ProductsSubCategoriesModel->where('product_id',$product_id)->delete();
       }
       else
       {
          $arr_sub_category = [];
          $arr_sub_category = isset($form_data['sub_category'])?$form_data['sub_category']:'';
          $this->ProductsSubCategoriesModel->where('product_id',$product_id)->delete();
       }

       if($product_id!=null)
        {
         $complete_status= get_product_complete_status($product_id);
        }

        if(isset($arr_sub_category) && sizeof($arr_sub_category)>0)
        {
          foreach ($form_data['sub_category'] as $key => $value) 
                {
                    $arr_data                    = [];
                    $arr_data['category_id']     = isset($form_data['category_id'])?$form_data['category_id']:'';
                    $arr_data['sub_category_id'] = $value;
                    $arr_data['product_id']      = $product_id;


                    $this->ProductsSubCategoriesModel->create($arr_data);
                } 
                if($product_id!=null)
              {
      
                if($complete_status==4)
                {
                  $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>4,'category_id'=>$form_data['category_id']]);   
                }
                else
                {
                  //dd($complete_status);
                  $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>$complete_status,'category_id'=>$form_data['category_id']]);  
                }  
              }
            } 

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

              $arr_event['MODULE_TITLE'] = $this->module_title;             
              $arr_event['USER_ID']      = $user->id;             

              $this->save_activity($arr_event);

              DB::commit();
        $response['status']      = 'success';
        
        if($is_update)
        {
          $response['description'] = str_singular($this->module_title).' has been updated, Click OK to proceed next.';
        }
        else
        {
          $response['description'] = str_singular($this->module_title).' has been added, Click OK to proceed next.';
        }       
              
              $response['next_url']      = $this->module_url_path.'/style_and_dimensions/'.base64_encode($product->id);

              return response()->json($response);
      }
      else
      { 
        DB::rollback();
        $response['status']            = 'failure';
              $response['description'] = 'Error occurred while adding '.str_singular($this->module_title);

              return response()->json($response);
      }

      }catch(Exception $e)
      {
        DB::rollback();
        
        $response['status']        = 'failure';
          $response['description'] = $e->getMessage();

          return response()->json($response);
      }
    }

    public function styleAndDimensions($enc_product_id)
    {
      $product_id = base64_decode($enc_product_id);

      $product_arr = $product_details_arr = [];
      $product_details_obj = $this->BaseModel->where('id',$product_id)->first();
      $product_details_arr = $this->ProductDetailsModel->where('product_id',$product_id)->get()->toArray();

      if($product_details_obj)
      {
        $product_arr = $product_details_obj->toArray();
      }
      else
      {
        $response['status']          = 'failure';
            $response['description'] = 'Something went wrong, please try again.';            
            return response()->json($response);
      }

      // dd($product_details_arr);

        $this->arr_view_data['product_arr']     = $product_arr;
        $this->arr_view_data['product_details_arr'] = $product_details_arr;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;  
        $this->arr_view_data['page_title']      = "Add ".str_singular( $this->module_title).' Styles and Dimensions';        
        $this->arr_view_data['module_title']    = str_plural($this->module_title);      

        return view($this->module_view_folder.'.styleAndDimensions',$this->arr_view_data);
    }

    public function storeProductStyleAndDiemensions(Request $request)
    {
      /*  dd($request->all());*/
      try{
        $is_update = false;

          $arr_rules = $db_rows_id_arr = $user_removed_id_arr = $user_row_id = [];        

          $form_data = $request->all();        
          $json_data = json_encode($form_data);        

          $arr_rules = [
                  'optionName' => 'required',
                  'product_id' => 'required'
              ];

          $validator = Validator::make($request->all(),$arr_rules); 

          if($validator->fails())
          {        
             $response['status']      = 'warning';
             $response['description'] = 'Form validations failed, please check all fields.';

            return response()->json($response);
          }  
          //dd($form_data['option']);
          DB::beginTransaction();         
          $product_id   = $form_data['product_id'];
          $optionName   = $form_data['optionName'];
          $old_option_arr = $request->input('old_option');
          $new_option_arr = $request->input('new_option');

          //delete those records that removed from user
          //get all rows id 
          $db_rows_id_arr = $this->ProductDetailsModel->select('id')
                      ->where('product_id',$product_id)
                      ->get()->toArray();

          $db_rows_id_arr = array_column($db_rows_id_arr,'id');
          
          //get user selected rows id
          if(isset($old_option_arr) && count($old_option_arr)>0)
          {
            $user_row_id = array_keys($old_option_arr);
            $user_removed_id_arr = array_diff($db_rows_id_arr,$user_row_id);  
          }       
          
          //delete removed records from db
          $this->ProductDetailsModel->whereIn('id',$user_removed_id_arr)->delete();

          

          //update old records
          if(isset($old_option_arr) && count($old_option_arr)>0)
          {
            foreach ($old_option_arr as $old_key => $value) 
            {
              $old_product_arr = [];
              //upload product image and get path   
          $old_product_img_file_path = '';
          
          $old_product_image = isset($form_data['old_product_image'][$old_key])?$form_data['old_product_image'][$old_key]:null;

          
          

          if($old_product_image!=null)
          {
            //Validation for product image
             $product_image_thumb = Image::make($old_product_image)->resize(800,800);
          
            $thumb_file_extension = strtolower( $form_data['old_product_image'][$old_key]->getClientOriginalExtension());

            $file_extension = strtolower($old_product_image->getClientOriginalExtension());

                  if(!in_array($file_extension,['jpg','png','jpeg']))
                  {             
                      $arr_response['status']  = 'FAILURE';
              $arr_response['description']     = 'Invalid product image, please try again.';
              return response()->json($response);
                  }

           
            $old_product_img_file_path = $old_product_image->store('product_image');
            
            $path = storage_path('app/product_image/product_img_thumb');
            $img_name = date('mdYHis').uniqid().'.'.$file_extension;
              
            $product_img_thumb_file = $product_image_thumb->save($path.'/'.$img_name);
            
            

           /* $old_product_arr = array_push_assoc($old_product_arr, 'image_thumb', $img_name);*/
           $th_img_path = $img_name;

          }
          else
          {
           
            $old_product_img_file_path = $form_data['db_product_image'][$old_key];
           
             if(isset($form_data['db_product_thumb_image'][$old_key])&& $form_data['db_product_thumb_image'][$old_key]!=null)
             {
              
              $th_img_path = $form_data['db_product_thumb_image'][$old_key];
             }
             else
             {
              $th_img_path = ''; 
             }
          }
        
          $old_product_arr = [
                    'option_type' => $optionName,
                    'product_id'  => $product_id,
                    'option'      => $form_data['old_option'][$old_key],
                    'sku'         => $form_data['old_sku'][$old_key],
                    'weight'      => $form_data['old_weight'][$old_key],
                    'length'      => $form_data['old_length'][$old_key],
                    'width'       => $form_data['old_width'][$old_key],
                    'height'      => $form_data['old_height'][$old_key],
                    'image'       => $old_product_img_file_path,
                    'image_thumb' => $th_img_path
                      ]; 
          /*if(isset($img_name) && $img_name!='')
          { 
            $old_product_arr = array_push_assoc($old_product_arr, 'image_thumb', $img_name);
          }
          else
          {
             $old_product_arr = array_push_assoc($old_product_arr, 'image_thumb','');
          }*/
          /*dd($old_product_arr);  
      */
             $is_old_product_store = $this->ProductDetailsModel->where('id',$old_key)
                                          ->update($old_product_arr);

              $arr_event    = [];

              $user = Sentinel::check();

           
              $arr_event['ACTION']       = 'EDIT';
              $arr_event['MODULE_ID']    =  $old_key;             
              $arr_event['MODULE_TITLE'] = $this->module_title;             
              $arr_event['USER_ID']      = $user->id;             

              $this->save_activity($arr_event);


            } 

          }         
          
          //insert new records
          if(isset($new_option_arr) && count($new_option_arr)>0)
          {
            foreach ($new_option_arr as $key => $value) 
            {
              $product_arr = [];
              //upload product image and get path   
          $new_product_img_file_path = '';
          
          $product_image = $form_data['new_product_image'][$key];
          $product_image_thumb = Image::make($product_image)->resize(800,800);
          $thumb_file_extension = strtolower( $form_data['new_product_image'][$key]->getClientOriginalExtension());


          if($product_image!=null)
          {
            //Validation for product image
            $file_extension = strtolower($product_image->getClientOriginalExtension());

                  if(!in_array($file_extension,['jpg','png','jpeg']))
                  {             
                      $arr_response['status']     = 'FAILURE';
              $arr_response['description']  = 'Invalid product image,please try again.';
              return response()->json($response);
                  }

            $new_product_img_file_path = $product_image->store('product_image');
            
            $path = storage_path('app/product_image/product_img_thumb');
            $img_name = date('mdYHis').uniqid().'.'.$file_extension;
              
            $product_img_thumb_file = $product_image_thumb->save($path.'/'.$img_name);
          }

          $product_arr = [
                    'option_type' => $optionName,
                    'product_id'  => $product_id,
                    'option'      => $form_data['new_option'][$key],
                    'sku'         => $form_data['new_sku'][$key],
                    'weight'      => $form_data['new_weight'][$key],
                    'length'      => $form_data['new_length'][$key],
                    'width'       => $form_data['new_width'][$key],
                    'height'      => $form_data['new_height'][$key],
                    'image'       => $new_product_img_file_path,
                    'image_thumb' => $img_name
                      ];  

                    
                      
              $is_new_product_store = $this->ProductDetailsModel->create($product_arr);

              $arr_event                 = [];           
              $arr_event['ACTION']       = 'ADD';
              $arr_event['MODULE_ID']    =  $is_new_product_store->id;              
              $arr_event['MODULE_TITLE'] = $this->module_title; 
              $arr_event['USER_ID']      = $user->id;             

              $this->save_activity($arr_event);


            }           
          }



          

          //update product complete status                

          if($product_id!=null&&$product_id!="")
      {
      /*      dd($product_id);
      */
           $complete_status= get_product_complete_status($product_id);
           if($complete_status==4)
           {
            $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>4]);
           }
           else
           {
            $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>2]);
           }
        }   
      if(isset($is_new_product_store))
      {
              DB::commit();
        $response['status']            = 'success';
              $response['description'] = str_singular($this->module_title).' details has been added, Click Ok to proceed next.';

              $response['next_url']      = $this->module_url_path.'/additional_images/'.base64_encode($product_id);

              return response()->json($response);
      }
      elseif(isset($is_old_product_store))
      {
              DB::commit();
              $response['status']      = 'success';
              $response['description'] = str_singular($this->module_title).' details has been updated, Click Ok to proceed next';

              $response['next_url']    = $this->module_url_path.'/additional_images/'.base64_encode($product_id);

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

    public function additionalImages($enc_product_id)
    {
      $product_id = base64_decode($enc_product_id);
      $product_details_arr = $product_image_arr = [];
      $product_details_obj = $this->BaseModel->where('id',$product_id)->first();
      $product_image_obj   = $this->ProductImagesModel->where('product_id',$product_id)->first();

      if($product_details_obj)
      {
        $product_details_arr = $product_details_obj->toArray();
      }
      else
      {
        $response['status']          = 'failure';
            $response['description'] = 'Something went wrong, please try again.';            
            return response()->json($response);
      }

      if($product_image_obj)
      {
        $product_image_arr = $product_image_obj->toArray();
      }

        $this->arr_view_data['product_id']          = $product_id;
        $this->arr_view_data['product_details_arr'] = $product_details_arr;
        $this->arr_view_data['product_image_arr']   = $product_image_arr;
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']     = $this->curr_panel_slug;  
        $this->arr_view_data['page_title']          = "Add ".str_singular( $this->module_title).' Additional Images';        
        $this->arr_view_data['module_title']        = str_plural($this->module_title);      

        return view($this->module_view_folder.'.additionalImages',$this->arr_view_data);
    }

    public function view($enc_product_id = false)
    {
      $product_id = base64_decode($enc_product_id);
      $product_arr = [];
      $mp_arr=array('productDetails','productImages','productSubCategories.SubcategoryDetails','productSubCategories.ThirdSubcategoryDetails' , 'productSubCategories.FourthSubcategoryDetails' ,'categoryDetails','userDetails','brand_details','maker_details',
    'productDetails.productMultipleImages','productDetails.inventory_details');

      $product_arr = $this->ProductService->get_productDetails_from_productId($product_id,$mp_arr); 

      if(empty($product_arr))
      {
        $response['status']      = 'failure';
        $response['description'] = 'Something went wrong,please try again.';            
        return response()->json($response);
      }     

        /* --------------------------------------------------------------------------------                
                To get updated columns in admin login 
                By Harshada Kothmire
                On date 07 Sept 202
        ---------------------------------------------------------------------------------*/         
        $res_already_updated_by_vendor = $this->ProductUpdatedByVendorModel->where('product_id',$product_id)->get()->toArray();
        $updatedColumnsArray = $updatedColumnsDetArray = $updatedColumnsSubCatArray = $updatedColumnsThirdSubCatArray = $updatedColumnsFourthSubCatArray = array();
        if(!empty($res_already_updated_by_vendor)){
          if($res_already_updated_by_vendor[0]['update_columns']){
              $updatedColumnsArray = json_decode($res_already_updated_by_vendor[0]['update_columns'], true);
          }
          if($res_already_updated_by_vendor[0]['update_productDetails_columns']){
               $updatedColumnsDetArray = json_decode($res_already_updated_by_vendor[0]['update_productDetails_columns'], true);
          }
           if($res_already_updated_by_vendor[0]['update_subcategories_columns']){
               $updatedColumnsSubCatArray = json_decode($res_already_updated_by_vendor[0]['update_subcategories_columns'], true);
          }
          if($res_already_updated_by_vendor[0]['update_third_subcategories_columns']){
               $updatedColumnsThirdSubCatArray = json_decode($res_already_updated_by_vendor[0]['update_third_subcategories_columns'], true);
          }
          if($res_already_updated_by_vendor[0]['update_fourth_subcategories_columns']){
               $updatedColumnsFourthSubCatArray = json_decode($res_already_updated_by_vendor[0]['update_fourth_subcategories_columns'], true);
          }
        }      
        //echo "<pre>";print_r($updatedColumnsArray);die;
        $this->arr_view_data['updatedColumnsArray']         = $updatedColumnsArray;
        $this->arr_view_data['updatedColumnsDetArray']      = $updatedColumnsDetArray;
        $this->arr_view_data['updatedColumnsSubCatArray']   = $updatedColumnsSubCatArray;
        $this->arr_view_data['updatedColumnsThirdSubCatArray']   = $updatedColumnsThirdSubCatArray;
        $this->arr_view_data['updatedColumnsFourthSubCatArray']   = $updatedColumnsFourthSubCatArray;
        $this->arr_view_data['product_arr']                 = $product_arr;
        $this->arr_view_data['module_url_path']             = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']             = $this->curr_panel_slug;  
        $this->arr_view_data['page_title']                  = "View ".str_singular( $this->module_title).' Details';        
        $this->arr_view_data['module_title']                = str_plural($this->module_title);      
        //  dd($this->arr_view_data);
        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

    public function storeProductAdditionalImages(Request $request)
    {
      $form_data = $request->all();       
     
      $product_img_file_path = $product_life_style_image_path = $product_packaging_image_path= '';
      $images_arr = [];

      $is_update          = false;
      $product_id         = $form_data['product_id'];       
      $product_image      = isset($form_data['product_image'])?$form_data['product_image']:null;
      $life_style_image   = isset($form_data['life_style_image'])?$form_data['life_style_image']:null;
      $packaging_image    = isset($form_data['packaging_image'])?$form_data['packaging_image']:null;
      
      try
      {
        DB::beginTransaction();   

        if($product_image!=null)
      {
        //Validation for product image
        $file_extension = strtolower($product_image->getClientOriginalExtension());

              if(!in_array($file_extension,['jpg','png','jpeg']))
              {             
                  $arr_response['status']     = 'FAILURE';
          $arr_response['description']  = 'Invalid product image, please try again.';

          return response()->json($response);
              }

        $product_img_file_path = $product_image->store('product_image');      
        /*$store_iamge = */
      }
      else
      {
        $product_img_file_path = isset($form_data['old_product_image'])?$form_data['old_product_image']:null;
      }

      if($life_style_image!=null)
      {
        //Validation for product image
        $file_extension = strtolower($life_style_image->getClientOriginalExtension());

        if(!in_array($file_extension,['jpg','png','jpeg']))
        {             
            $arr_response['status']         = 'FAILURE';
            $arr_response['description']    = 'Invalid product image, please try again.';

            return response()->json($response);
        }

        $product_life_style_image_path = $life_style_image->store('product_image');
      }
      else
      {
        $product_life_style_image_path = isset($form_data['old_lifestyle_image'])?$form_data['old_lifestyle_image']:null;
      }

      if($packaging_image!=null)
      {
        //Validation for product image
        $file_extension = strtolower($packaging_image->getClientOriginalExtension());

        if(!in_array($file_extension,['jpg','png','jpeg']))
        {             
            $arr_response['status']       = 'FAILURE';
            $arr_response['description']  = 'Invalid product image, please try again.';

            return response()->json($response);
        }

        $product_packaging_image_path = $packaging_image->store('product_image');
      }
      else
      {
        $product_packaging_image_path = isset($form_data['old_packaging_image'])?$form_data['old_packaging_image']:null;
      }

      //check is product image already uploaded or not
      $is_images_exist = $this->ProductImagesModel->where('product_id',$product_id)
                            ->count()>0;
      
      if($is_images_exist)
      {
        $is_update = true;
      }

      $product_images = $this->ProductImagesModel->firstOrNew(['product_id' => $product_id]);

     

      //storing product in elastic search
     
      $is_exist_in_search = ProductsModel::search()->term('_id',$product_id)->get()->totalHits()['value'];
      if($is_exist_in_search==0)
      {
        $product_entry = ProductsModel::where('id',$product_id)->first();
        $product_entry->category_name = get_catrgory_name($product_entry->category_id);
        $product_entry->maker_name = get_maker_name($product_entry->user_id);
        $product_entry->document()->save(); 
      }
      else
      {
        $product_entry = ProductsModel::where('id',$product_id)->first();
        $product_entry->category_name = get_catrgory_name($product_entry->category_id);
        $product_entry->maker_name = get_maker_name($product_entry->user_id);
        $product_entry->document()->update(); 
      }
      

      $product_images->product_id      = $product_id;
      $product_images->product_image   = $product_img_file_path;
      $product_images->lifestyle_image = $product_life_style_image_path;
      $product_images->packaging_image = $product_packaging_image_path;   

      $is_store = $product_images->save(); 

      if($is_store)
      { 
          //update product complete status
          if($product_id!=null&&$product_id!='')
          {
      

             $complete_status= get_product_complete_status($product_id);
             if($complete_status==4)
             {
              $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>4]);
             }
             else
             {
              $this->BaseModel->where('id',$product_id)->update(['product_complete_status'=>4]);
             }
          }   

          $arr_event    = [];

          $user = Sentinel::check();

          if($is_update == false)
          {
              $arr_event['ACTION']       = 'ADD';
              $arr_event['MODULE_ID']    =  $product_images->id;
          }
          else
          {
              $arr_event['ACTION']   = 'EDIT';
              $arr_event['MODULE_ID'] = $product_id;
          }

          $arr_event['MODULE_TITLE'] = $this->module_title;
          $arr_event['USER_ID']      = $user->id;
        

          $this->save_activity($arr_event);



          DB::commit();
          $arr_response['status']       = 'SUCCESS';
          $arr_response['description']  = str_singular($this->module_title). ' additional images has been added.';
          $arr_response['next_url']     = $this->module_url_path.'/view/'.base64_encode($product_id);

           return response()->json($arr_response);
      }
      else
      {
        DB::rollback();  
        $arr_response['status']       = 'FAILURE';
        $arr_response['description']  = 'Something went wrong, please try again.';

        return response()->json($arr_response);
      }
      }catch(Exception $e)
      {
          DB::rollback();       
          $response['status']      = 'failure';
          $response['description'] = $e->getMessage();

          return response()->json($response);
      }     
    }

    //-------Done
    public function changeStatus(Request $request)
    {
      $product_status = $request->input('productStatus');
      $product_id     = $request->input('product_id');

      $complete_status= get_product_complete_status($product_id);
      if($complete_status!=4)
      {
        $response['status']  = 'WARNING';
        $response['message'] = $this->module_title.' status incomplete.';
        return response()->json($response);
      }

      /*------------check the product is perchased or not---------*/
        //first check in rep_lead_details & retailer_transaction_details table
        $count_in_lead_rep = $count_in_retailer_transaction = 0;

        $count_in_lead_rep = $this->RepresentativeProductLeadsModel->where('product_id',$product_id)->count();

        $count_in_retailer_transaction = $this->RetailerQuotesProductModel->where('product_id',$product_id)->count();  

        
        if($count_in_retailer_transaction >0 || $count_in_lead_rep >0)
        {
            $response['status']  = 'WARNING';
            $response['message'] = "Product can't deactivated, because this product already purchased.";
            return response()->json($response);
        }
        else
        {
           $status_response = $this->ProductService->changeStatus($product_status,$product_id);
           return response()->json($status_response); 
        }
      /*----------------------------------------------------------*/

      
    }

    public function get_second_level_categories($enc_id)
    {
      $arr_sub_category = [];
      $category_id = base64_decode($enc_id);
      $arr_sub_category = $this->HelperService->get_subcategories($category_id);
      
      $response['status']  = 'SUCCESS';
        $response['second_level_category_arr'] = $arr_sub_category;     
      return response()->json($response);
    }

    public function get_maker_data($enc_id)
    {
      $arr_maker_data = [];
      $maker_id = base64_decode($enc_id);
      $maker_data =  get_maker_details($enc_id);
     

      $maker_data = $maker_data;
      
      $response['status']  = 'SUCCESS';
      $response['maker_arr'] = $maker_data;     
      return response()->json($response);
    }
    //-------Done
    public function does_exists(Request $request,$param,$pro_id=false)
    { 
      $form_data = $request->all();
      $is_sku_exists = $this->ProductService->does_exists($form_data,$param,$pro_id);
      
      if ($is_sku_exists == 'success') {

        return response()->json(['exists'=>'true']);             
      } 

      elseif ($is_sku_exists == 'failed') {

        return response()->json(['exists'=>'true'],404);          
      }
      
      
    }
    //-------Done
    public function does_exists_edit(Request $request,$param,$pro_id=false)
    { 
      $form_data = $request->all();

      $is_sku_exists = $this->ProductService->does_exists_edit($form_data,$param,$pro_id); 

      if ($is_sku_exists == 'success') {

        return response()->json(['exists'=>'true']);             
      } 

      elseif ($is_sku_exists == 'failed') {

        return response()->json(['exists'=>'true'],404);          
      }   
    }

     

    public function product_confirmation(Request $request)
    {
      $inserted_arr = [];
      $form_data    = $request->all();
      $maker_id = $product_id = 0;

      $user = Sentinel::check();

      if($user)
      {
        $loggedInUserId = $user->id;
      }

      $product_id = $form_data['product_id'];


      /*find maker id from product_id*/

      $maker = $this->BaseModel->where('id',$product_id)->select('user_id','product_name')->first();

      if($maker)
      {
         $maker_id = $maker->user_id; 
      }

      $inserted_arr['is_active'] = isset($form_data['status'])?$form_data['status']:'';
      $inserted_arr['remark']    = $form_data['remark'];

      /*For update product previous status*/

      if ($form_data['status'] == 1) {

        $inserted_arr['previous_category_status'] = '1';
      }
      elseif($form_data['status'] == 0){

        $inserted_arr['previous_category_status'] = '0';
      }


      $inserted_arr['is_remark_checked'] = 0;  

      $result = $this->BaseModel->where('id',$product_id)->update($inserted_arr);

      /* update table of product_updated_by_vendor */
      $ProductUpdatedArr = array();
      $ProductUpdatedArr['update_productDetails_columns'] = '';                 
      $ProductUpdatedArr['update_columns'] = '';
      $ProductUpdatedArr['update_subcategories_columns'] = '';
      $ProductUpdatedArr['updated_at'] = date("Y-m-d H:i:s");
      $this->ProductUpdatedByVendorModel->where('product_id', $product_id)->update($ProductUpdatedArr);
       // saving product in elastic search document end
       
   
      if($result)
      {
        $msg= $view_product_href='';
        /* Send notification to admin*/

        if($form_data['status'] == 1)
        {
          $msg ='Your product '.'"'.$maker->product_name.'"'.' approved by admin.';
        }
        else if($form_data['status'] == 0)
        {
            $msg ='Your product '.'"'.$maker->product_name.'"'.' rejected by admin.';
        }

            $view_product_href = url('/').'/vendor/products/view/'.base64_encode($product_id);

            $notification_arr                 = [];
            $notification_arr['from_user_id'] = $loggedInUserId;
            $notification_arr['to_user_id']   = $maker_id;
            $notification_arr['description']  = $msg;
            $notification_arr['title']        = 'Product Confirmation';
            $notification_arr['type']         = 'maker';   
            $notification_arr['link']         = $view_product_href;   
        

        $this->GeneralService->save_notification($notification_arr);

        if($form_data['status'] == 1)  
        {
          $response['status']      = 'success';
          $response['description'] = 'Product has been approved.';
          $this->ElasticSearchService->initiate_index_product($product_id);

          return response()->json($response);
        }
        elseif ($form_data['status'] == 0)
        {
          $response['status']      = 'success';
          $response['description'] = 'Product has been rejected.';

          return response()->json($response);
        }

          
      }
      else
      {
        $response['status']      = 'error';
        $response['description'] = 'Error occurred while product confirmation.';

        return response()->json($response);
       
      }
  }

  public function resync_product_index(){

    $arr_product = ProductsModel::where('product_complete_status',4)->get();

    if(sizeof($arr_product) > 0)
    {
      foreach ($arr_product as $key => $product_entry) 
      {
        try
        {
          $product_entry->category_name = get_catrgory_name($product_entry->category_id);
          $product_entry->maker_name = get_maker_name($product_entry->user_id);
          $product_entry->company_name = get_maker_company_name($product_entry->user_id);
          $product_entry->subcategory_id = get_subcategory_id($product_entry->id);
          $product_entry->brand_name = get_product_brand_name($product_entry->brand);

          $product_entry->document()->update();     
        }
        catch(\Exception $e)
        {
          if('Elasticsearch\Common\Exceptions\Missing404Exception' == get_class($e))
          {
            $product_entry->document()->save(); 
          }
        }
        
      }
      
    }

    echo "Total Product(s) : ".sizeof($arr_product);
  }



  public function activate($enc_id = FALSE)
  {
    if(!$enc_id)
    {
        return redirect()->back();
    }


    if($this->perform_activate(base64_decode($enc_id),$product_count=false))
    {
        Flash::success($product_count.' '.$this->module_title. ' has been approved.');
    }
   
       
    else
    {
        Flash::error('Error occurred while '.$this->module_title.' approvation.');
    }


    return redirect()->back();
  }

  public function deactivate($enc_id = FALSE)
  {   
      if(!$enc_id)
      {
        return redirect()->back();
      }
      
      else if($this->perform_deactivate(base64_decode($enc_id)) == TRUE)
      { 
          Flash::success($this->module_title. ' has been rejected.');
          return redirect()->back();
      }
      else
      {
         Flash::error('Error occurred while '.str_plural($this->module_title).' rejection.');
         return redirect()->back();
      }

      
  }



  public function perform_activate($id)
  {   
      $static_page = $this->BaseModel->where('id',$id)->first();
        
      if($static_page)
      {
          if($this->BaseModel->getTable()=="products")
          {
              $is_category_active =  $this->CategoryModel->select('is_active')->where('id',$static_page->category_id)->first();


              $sub_cat_arr = $this->ProductsSubCategoriesModel->select('sub_category_id')->where('product_id',$static_page->id)->get()->toArray();

              $sub_cat_arr = array_column($sub_cat_arr,'sub_category_id');

              $count_obj = $this->SubCategoryModel->whereIn('id',$sub_cat_arr)
                                               ->where('is_active',0)
                                               ->first();

              if(isset($count_obj))
              { 
                $count_arr = $count_obj->toArray();
                Flash::error("Products Sub-category ".$count_arr['subcategory_name']." is deactivated can not perform status update operations.");
                return redirect()->back();
              }
               
              if(isset($is_category_active))
              {
                  $is_category_active = $is_category_active->toArray();
              }
              if($is_category_active['is_active']=='1')
              {

                 /* $this->ElasticSearchService->activate_product($id,1);
                       
                  return $this->BaseModel->where('id',$id)->update(['is_active'=>'1','previous_category_status' =>'1']);
*/

                   $updateProduct = $this->BaseModel->where('id',$id)->update(['is_active'=>'1','previous_category_status' =>'1']);

                  $this->ElasticSearchService->activate_product($id,1);
                  $this->ElasticSearchService->index_product($id);
                  

                  return $updateProduct;
              }
              else
              {
                Flash::error("Products category is deactivated can not perform status update operations.");
                return redirect()->back();
              }   
          }
       
      }

      return FALSE;
  }

  public function perform_deactivate($id)
  {  

      $static_page = $this->BaseModel->where('id',$id)->first();

      if($static_page)
      {
          if($this->BaseModel->getTable()=="products")
          {
              $is_category_active =  $this->CategoryModel->select('is_active')->where('id',$static_page->category_id)->first();
       
              $sub_cat_arr = $this->ProductsSubCategoriesModel->select('sub_category_id')->where('product_id',$static_page->id)->get()->toArray();

              $sub_cat_arr = array_column($sub_cat_arr,'sub_category_id');

              $count_obj = $this->SubCategoryModel->whereIn('id',$sub_cat_arr)
                                       ->where('is_active',0)
                                       ->first();

              if(isset($count_obj))
              { 
                $count_arr = $count_obj->toArray();
                Flash::error("Products Sub-category ".$count_arr['subcategory_name']." is deactivated can not perform status update operations.");
                  return redirect()->back();
              }


              if(isset($is_category_active))
              {
                $is_category_active = $is_category_active->toArray();
              }
              if($is_category_active['is_active']=='1')
              {
                $this->ElasticSearchService->decactivate_product($id,0);
                return $this->BaseModel->where('id',$id)->update(['is_active'=>'0','previous_category_status' => '0']);
              }
              else
              {
                Flash::error("Products category is deactivated can not perform status update operations.");
                return redirect()->back();
              }   
          }
          
      }
      else
      {
        return FALSE;
      }
  }

  public function multi_action(Request $request)
  { 
        $arr_rules = array();
        $arr_rules['multi_action'] = "required";
        $arr_rules['checked_record'] = "required";

        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Flash::error('Please select '.$this->module_title.' to perform multi actions.');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $multi_action   = $request->input('multi_action');
        $checked_record = $request->input('checked_record');

        /* Check if array is supplied*/
        if(is_array($checked_record) && sizeof($checked_record)<=0)
        {
            Flash::error('Error occurred while doing multi action.');
            return redirect()->back();
        }

        $product_count = count($checked_record);

        foreach ($checked_record as $key => $record_id) 
        {  
          
            if($multi_action=="delete")
            {  
               $this->perform_delete(base64_decode($record_id));    
               Flash::success($product_count.' product(s) have been deleted.'); 
               
            } 
            elseif($multi_action=="activate")
            {
               $this->perform_activate(base64_decode($record_id),$product_count); 
               Flash::success($product_count.' '.'product(s) have been approved.');
               
            }
            elseif($multi_action=="deactivate")
            {
               $this->perform_deactivate(base64_decode($record_id),$product_count);    
               Flash::success($product_count.' '.'product(s) have been rejected.'); 
               
            }

        }

        return redirect()->back(); 
        
  }

    

}