<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductsModel;
use App\Models\UserModel;
use App\Models\TempBagModel;
use App\Models\ShopSettings;
use App\Models\CustomerFavoriteModel;
use App\Models\CatlogsModel;
use App\Models\CatalogImageModel;
use App\Models\PromotionsModel;
use App\Models\CatalogPdfModel;
use App\Models\BrandsModel;
use App\Models\MakerModel;
use App\Models\CatalogPagesModel;
use App\Common\Services\ElasticSearchService;
/*use App\Models\FavoriteMakerModel;
use App\Models\FavoriteProductModel;*/



use Flash;
use Validator;
use Paginate;
use Sentinel;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class MakerProductController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 10 July 2019
    */
    public function __construct(ProductsModel $ProductsModel,
    							UserModel $UserModel,
                                TempBagModel $TempBagModel,
                                ShopSettings $ShopSettings,
                                CustomerFavoriteModel $CustomerFavoriteModel,
                                CatlogsModel $CatlogsModel,
                                CatalogImageModel $CatalogImageModel,
                                PromotionsModel $PromotionsModel,
                                BrandsModel $BrandsModel,
                                ElasticSearchService $ElasticSearchService,
                                MakerModel $MakerModel,
                                CatalogPagesModel $CatalogPagesModel,
                                CatalogPdfModel $CatalogPdfModel

    						   )
    {
    	$this->arr_view_data         = [];
    	$this->module_title          = "Vendors Products";
    	$this->module_view_folder    = 'front.maker'; 
    	$this->customer_panel_slug   = config('app.project.customer_panel_slug');
    	$this->module_url_path       = url($this->customer_panel_slug.'/maker-profile');      
      $this->ProductsModel         = $ProductsModel;
      $this->UserModel             = $UserModel;
      $this->CatlogsModel          = $CatlogsModel;
      $this->TempBagModel          = $TempBagModel;
      $this->ShopSettings          = $ShopSettings;
      $this->PromotionsModel       = $PromotionsModel;
      $this->CustomerFavoriteModel         = $CustomerFavoriteModel;
      $this->CatalogImageModel     = $CatalogImageModel;
      $this->BrandsModel           = $BrandsModel;
      $this->ElasticSearchService  = $ElasticSearchService;
      $this->MakerModel            = $MakerModel;
      $this->CatalogPagesModel     = $CatalogPagesModel;
      $this->CatalogPdfModel       = $CatalogPdfModel;

      $this->meta_logo_image    = url('/').config('app.project.img_path.meta_logo_image');
     
    }

    public function maker_details(Request $request)
    {   
        $isProductActive = '';
        $per_page         = 9;
        $pagination_links = $message = '';
        $arr_brands       = $product_arr = $maker_arr = $maker_product_arr = [];
        $fav_maker_details_arr = $fav_product_arr = $catalog_data  = $arr_meta_details =[];
        $curr_url         = str_replace('%20', '-', $request->fullUrl());

        /* ----- for only remove pop-up after page refreshing purpose */

        $ven_id          = isset($request->vendor_id)?$request->vendor_id:false;
        $new_url         = 'javascript:void(0)';
        $maker_id        = $request->vendor_id;
        $maker_id        = base64_decode($maker_id);
        $enc_brand_id    = $request->brand_id;
        $enc_product_id  = $request->product_id;
        $product_id      = base64_decode($enc_product_id);
        $brand_id        = base64_decode($enc_brand_id);

        /* this filed is only appered when user visit from vendor section */
        $enc_vendor_brand_id  = $request->brand;
        $vendor_brand_id      = base64_decode($enc_vendor_brand_id);
        $is_active_count = $this->UserModel->where('id',$maker_id)
                                           ->where('status','1')
                                           ->where('is_approved','1')
                                           ->count();


        /*check this product is active or not*/

        if(isset($product_id) && $product_id!='')
        {
            $isProductActive = $this->ProductsModel->where('id',$product_id)->pluck('is_active')->first();
 
            if($isProductActive !=1)
            { 
               $message = "This product is currently unavailable.";
            }
          
        }                                   
 
        if($is_active_count !=1)
        {

          Flash::error('Currently this vendor is not active.');

          return redirect()->back();
        }
        
        /* get product brand details */
        $get_product_details = get_product_details($product_id);
        $brand_d = isset($get_product_details['brand'])?base64_encode($get_product_details['brand']):false;

        if($ven_id)
        {
          $new_url = parse_url($curr_url, PHP_URL_PATH).'?vendor_id='.$ven_id.'&brand_id='.$brand_d;
          /*---------------------------------------------------------------------*/
        }

        if($request->has('page'))
        {
          $new_url = $curr_url;
        }


        $product_id       = $request->product_id;
        $product_id       = base64_decode($product_id);
        $user_id          = $fav_maker_count  =0;
        
        /*get current logged in user*/
        $user = Sentinel::check();
        if($user)
        {
            $user_id = $user->id;
        }
        
        $enc_category_id      = $request->input('category_id',null);
        $enc_sub_category_id  = $request->input('subcategory',null);

        $request->input('',null);

        $request_data['vendor_id'] = $request->vendor_id;

        if(!empty($enc_category_id))
        {
            $request_data['category_id'] = $enc_category_id;
        }
        if(!empty($enc_sub_category_id))
        {
            $request_data['subcategory'] = $enc_sub_category_id;
        }
        if(!empty($enc_brand_id))
        {
            $request_data['brand_id'] = $enc_brand_id;
        }

        $request_data['page']     = $request->page;
      /*  if($ven_name)
        $maker_id                 = $request->vendor_id;
        $maker_id                 = base64_decode($maker_id);*/

        $shop_obj = $this->ShopSettings->where('maker_id',$maker_id)
                                       ->first();
                                    
        if($shop_obj)
        {
            $shop_arr = $shop_obj->toArray();
        }
        else
        {
            $shop_arr='';
        }
        
        if($request->has('page'))
        {
            $pageStart = $request->input('page'); /* pageStart : Indicates from which page to start.*/
        }
        else
        {
            $pageStart = 1; /* pageStart : Indicates from which page to start.*/
        } 

        $category_id = "";
       
        $category_id     = base64_decode($enc_category_id);    
        $sub_category_id = base64_decode($enc_sub_category_id);  
        
        /*$product_arr = $this->ElasticSearchService->search($request,$per_page);*/
        if(isset($maker_id) && ($product_id == '' && empty($enc_sub_category_id) && empty($enc_category_id )))
        {
          if(!isset($brand_id) || $brand_id == '')
          {
               $arr_brands = $this->BrandsModel->where('user_id',$maker_id)
                                          ->where('is_active',1)
                                          ->get()
                                          ->toArray();

               $apppend_data['vendor_id'] = base64_encode($maker_id);

               $paginator = $this->get_pagination_data($arr_brands, $pageStart, $per_page, $request_data);

            

              if($paginator)
              {
                  $pagination_links    =  $paginator;  
                  $arr_data            =  $paginator->items(); 
              } 
              
              if(count($arr_brands) == 1 )
              {
                  $product_arr = $this->ElasticSearchService->search($request,$per_page);
              }
          }
          else
          {
           $product_arr = $this->ElasticSearchService->search($request,$per_page);
          }        
        }
        else
        {
          $product_arr = $this->ElasticSearchService->search($request,$per_page);
        }

      
        if(isset($product_arr) && count($product_arr) > 0)  
        {

          $product_arr['total_results'] = isset($product_arr['total_results']) ? $product_arr['total_results'] : 0;

          $product_arr['arr_data'] = array_map(function($tmp_data)
          {
              return $tmp_data['_source'];
          },  $product_arr['arr_data']);
                                              
                    

          $paginator = $this->get_pagination_data_maker_details($product_arr['arr_data'],$product_arr['total_results'],$per_page,$request_data);


          if($paginator)
          {
              $pagination_links    =  $paginator;  
              $arr_data            =  $paginator->items(); 
          }  
        }

        $cat_id_arr = $this->ProductsModel->where('user_id',$maker_id)
                                          ->whereHas('categoryDetails.translations',function($q1){
                                            return $q1->orderBy('category_slug','ASC');
                                          })
                                           ->with(['categoryDetails'=>function($query)
                                           {
                                              return $query->select('id');
                                                    

                                           }])
                                                                                   /*->whereHas('categoryDetails.get_cat_name',function($q){
                                              return $q->orderBy('category_slug','ASC');
                                           })*/
                                          ->with(['categoryDetails.get_cat_name'=>function($q){
                                              return $q->orderBy('category_slug','ASC');
                                           }])
                                           ->with('categoryDetails.subcategory_details')
                                           ->where('is_active',1)
                                           ->where('product_complete_status',4)
                                           ->where('category_id','!=',0)
                                           ->groupBy('category_id')

                                           ->get(['category_id'])->toArray();
        
        /* Sort by Alpha */ 
        usort($cat_id_arr, function($sort_base, $sort_compare) {
            return $sort_base['category_details']['category_name'] <=> $sort_compare['category_details']['category_name'];
        });                                     

        $maker_obj = $this->UserModel->with(['store_details','maker_details'])
                                     ->where('id',$maker_id)
                                     ->first();
        if($maker_obj)
        {
            $maker_arr = $maker_obj->toArray();
        }
           
        $fav_maker_count = $this->CustomerFavoriteModel->where('retailer_id',$user_id)
                                               ->where('maker_id',$maker_id)
                                               ->count();
   
        $fav_product_arr = $this->CustomerFavoriteModel->where('retailer_id',$user_id)
                                               ->where('type','product')
                                               ->get()
                                               ->toArray();

        $fav_product_id_arr = array_column($fav_product_arr,'product_id');
       
     
        // $catalog_data = $this->CatlogsModel->with(['catlog_details'=>function($q)                                 {
        //                                       $q->select('id','catalog_id','image','sequence','is_active');
        //                                       $q->where('is_active',1);
        //                                       $q->orderBy('sequence','ASC');
                                                
        //                                    }])
        //                                   ->where('is_active',1)
        //                                   ->where('maker_id',$maker_id)
        //                                   ->groupBy('id')
        //                                   ->get()
        //                                   ->toArray(); 


        $catalog_data = $this->CatlogsModel->with(['catalogPageDetails'=>function($q)                                  {
                                               $q->where('is_active',1);
                                               $q->orderBy('sequence','ASC');
                                                    
                                            },'catalogPageDetails.getCatalogImageData'])
                                            ->where('is_active',1)
                                            ->where('maker_id',$maker_id)
                                            //->groupBy('id')
                                            ->get()
                                            ->toArray();  


        /*get promotion details from promotion id*/
          $current_month = date('m');
          $promotion_arr = [];
        
  
          $promotion_arr = $this->PromotionsModel->with(['get_promotions_offer_details.get_prmotion_type','get_promo_code_details'])
                                                 ->where('maker_id',$maker_id)
                                                 ->where('is_active','1')
                                                 ->get()
                                                 ->toArray();
          



           /*get catalog pdf data*/
            $pdfData = [];
            $pdfData = $this->CatalogPdfModel->where('vendor_id',$maker_id)
                                             ->where('is_active',1)
                                             ->get()
                                             ->toArray();
            // $imagick = new \Imagick($pdfData[0]['pdf_file'][0]);          
           /*---------------------------*/



          $first_product_id = $request->input('product_id');

          if(isset($first_product_id))
          { 
            $first_product_id = intval(base64_decode($first_product_id));
            $arr_data         = [];
            $obj_data         = $this->ProductsModel->with(['productDetails.inventory_details',
                                                            'categoryDetails']) 
                                                    ->where('id',$first_product_id)
                                                    ->first();
            if($obj_data)
            {
              $first_product_arr = $obj_data->toArray();
              if (isset($first_product_arr['product_details'][0]['sku'])) {
                 $first_prod_sku    = $first_product_arr['product_details'][0]['sku']; 
               } 
                
            }
          
            if(isset($first_prod_sku))
            {                                    
              $first_pro_details = get_style_dimension($first_prod_sku);
              $first_pro_qty     = get_product_quantity($first_prod_sku);
            }
          } 
          

          /*Meta Details Start...*/ 
          $arr_meta_details = [];
          if($maker_id!="" && $brand_id=="" && $product_id=="")
          { 
              $arr_meta_brands = [];
              $obj_meta_brands = $this->BrandsModel->where('user_id',$maker_id)
                                          ->where('is_active',1)
                                          ->first();
              if($obj_meta_brands)
              {
                $arr_meta_brands = $obj_meta_brands->toArray();
              }                            
            
            $meta_brand_image = "";
            $brand_image = isset($arr_meta_brands['brand_image'])?$arr_meta_brands['brand_image']:"";
            $meta_brand_name = isset($arr_meta_brands['brand_name'])?$arr_meta_brands['brand_name']:"Vendor Brand";

            if($brand_image!="")
            {
              $meta_brand_image = url('/storage/app/'.$brand_image); 
              //$meta_brand_image = url('/storage/app/'.$brand_image); 
            }
            else
            {
              $meta_brand_image = url('/assets/images/no-product-img-found.jpg');
            }

            
            $arr_meta_details['meta_title'] = $meta_brand_name;
            $arr_meta_details['meta_large_image_content']  = 'vendor_large_image';
            $arr_meta_details['meta_image'] = $meta_brand_image; 
          

          }
          elseif($maker_id!="" && $brand_id!="" && $product_id=="") 
          {
              //get details od brand and 
              $arr_meta_brands = $this->BrandsModel->where('user_id',$maker_id)
                                          ->where('id',$brand_id)
                                          ->where('is_active',1)
                                          ->first()
                                          ->toArray();

              $arr_maker_details = $this->BrandsModel->where('user_id',$maker_id)
                                                    ->first()->toArray();                         
              
              $meta_vendor_name = isset($arr_maker_details['brand_name'])?$arr_maker_details['brand_name']:"";
              $meta_vender_brand_name = isset($arr_meta_brands['brand_name'])?$arr_meta_brands['brand_name']:"";

              $meta_product_name = isset($product_arr['arr_data'][0]['product_name'])?$product_arr['arr_data'][0]['product_name']:"";

                $meta_image = "";
                $brand_image = isset($arr_meta_brands['brand_image'])?$arr_meta_brands['brand_image']:"";

                if($brand_image!="")
                {
                  $meta_image = url('/storage/app/'.$brand_image); 
                }
                else
                {
                  $meta_image = url('/assets/images/no-product-img-found.jpg');
                }

              $arr_meta_details['meta_title']  =    $meta_vendor_name.'/'.$meta_vender_brand_name;
              $arr_meta_details['meta_large_image_content']  = 'product_large_image';
              $arr_meta_details['meta_image']  = $meta_image; 
              //dd($arr_meta_details);
          }
          elseif($maker_id!="" && $brand_id!="" && $product_id!="") 
          {
              
              $product_details = $this->ProductsModel->where('id',$product_id)
                                                    ->with(['brand_details'=>function($q){
                                                      $q->select('id','brand_name');
                                                    }])
                                                    ->select('id','brand','product_name','product_image')
                                                    ->first()->toArray();
              $product_name = isset($product_details['product_name'])?$product_details['product_name']:"";
              $product_image = isset($product_details['product_image'])?$product_details['product_image']:"";  
              $brand_name = isset($product_details['brand_details']['brand_name'])?$product_details['brand_details']['brand_name']:"";
              
                $meta_image = ""; 
                if($product_image!="")
                {

                  $meta_image = url('/storage/app/'.$product_image); 
                  $meta_image = image_resize($meta_image,230,230); 
                  
                }
                else
                {
                  $meta_image = url('/assets/images/no-product-img-found.jpg');
                }

              $arr_meta_details['meta_title']  = $brand_name.'/'.$product_name;
              $arr_meta_details['meta_large_image_content']  = 'product_large_image';
              $arr_meta_details['meta_image']  = $meta_image; 
             
          } 

          /*Meta Details Stop...*/  
           
          $this->arr_view_data['new_url']          = $new_url;
          
          $this->arr_view_data['message']          = isset($message)?$message:'';
          $this->arr_view_data['isProductActive']  = isset($isProductActive)?$isProductActive:'';

          $this->arr_view_data['catalog_data']      = $catalog_data;
          $this->arr_view_data['promotion_arr']     = $promotion_arr;
          $this->arr_view_data['pagination_links']  = $pagination_links;
          $this->arr_view_data['fav_product_arr']   = $fav_product_id_arr;
          $this->arr_view_data['fav_maker_count']   = $fav_maker_count;
          $this->arr_view_data['maker_product_arr'] = isset($paginator)?$paginator:false;
          $this->arr_view_data['product_arr']       = $product_arr;
          $this->arr_view_data['maker_arr']         = $maker_arr;
          $this->arr_view_data['shop_arr']          = $shop_arr;
          $this->arr_view_data['module_title']      = $this->module_title;
          $this->arr_view_data['page_title']        = 'Vendor Product';
          $this->arr_view_data['module_url_path']   = $this->module_url_path;
          $this->arr_view_data['cat_arr']           = $cat_id_arr;
          $this->arr_view_data['arr_brands']        = isset($paginator)?$paginator:false;
          $this->arr_view_data['request_values']    = $request->all();

          $this->arr_view_data['first_prod_arr']     = isset($first_product_arr)?$first_product_arr:''; 
          $this->arr_view_data['first_prod_details'] = isset($first_pro_details)?$first_pro_details:'';
          $this->arr_view_data['first_pro_qty']      = isset($first_pro_qty)?$first_pro_qty:'';
          $this->arr_view_data['maker_prod_arr']     = isset($maker_prod_arr)?$maker_prod_arr:'';
          $this->arr_view_data['meta_details']       = $arr_meta_details;
          

          $this->arr_view_data['pdf_arr']       = isset($pdfData)?$pdfData:[];
         
          
          if(isset($arr_brands) && count($arr_brands) > 1 )
          {
            return view($this->module_view_folder.'.brand_list',$this->arr_view_data);
          }
          else
          {
            return view($this->module_view_folder.'.maker_details',$this->arr_view_data);
          }

    }


    public function maker_details_new(Request $request)
    {


        $isProductActive = '';
        $per_page         = 9;
        $pagination_links = $message = '';
        $arr_brands       = $product_arr = $maker_arr = $maker_product_arr = [];
        $fav_maker_details_arr = $fav_product_arr = $catalog_data  = $arr_meta_details =[];
        $curr_url         = str_replace('%20', '-', $request->fullUrl());

        /* ----- for only remove pop-up after page refreshing purpose */

        $ven_id          = isset($request->vendor_id)?$request->vendor_id:false;
        $new_url         = 'javascript:void(0)';
        $maker_id        = $request->vendor_id;
        $maker_id        = base64_decode($maker_id);
        $enc_brand_id    = $request->brand_id;
        $enc_product_id  = $request->product_id;
        $product_id      = base64_decode($enc_product_id);
        $brand_id        = base64_decode($enc_brand_id);

        /* this filed is only appered when user visit from vendor section */
        $enc_vendor_brand_id  = $request->brand;
        $vendor_brand_id      = base64_decode($enc_vendor_brand_id);
        $is_active_count = $this->UserModel->where('id',$maker_id)
                                           ->where('status','1')
                                           ->where('is_approved','1')
                                           ->count();


        /*check this product is active or not*/

        if(isset($product_id) && $product_id!='')
        {
            $isProductActive = $this->ProductsModel->where('id',$product_id)->pluck('is_active')->first();
 
            if($isProductActive !=1)
            { 
               $message = "This product is currently unavailable.";
            }
          
        }                                   
 
        if($is_active_count !=1)
        {

          Flash::error('Currently this vendor is not active.');

          return redirect()->back();
        }
        
        /* get product brand details */
        $get_product_details = get_product_details($product_id);
        $brand_d = isset($get_product_details['brand'])?base64_encode($get_product_details['brand']):false;

        if($ven_id)
        {
          $new_url = parse_url($curr_url, PHP_URL_PATH).'?vendor_id='.$ven_id.'&brand_id='.$brand_d;
          /*---------------------------------------------------------------------*/
        }

        if($request->has('page'))
        {
          $new_url = $curr_url;
        }


        $product_id       = $request->product_id;
        $product_id       = base64_decode($product_id);
        $user_id          = $fav_maker_count  =0;
        
        /*get current logged in user*/
        $user = Sentinel::check();
        if($user)
        {
            $user_id = $user->id;
        }
        
        $enc_category_id      = $request->input('category_id',null);
        $enc_sub_category_id  = $request->input('subcategory',null);

        $request->input('',null);

        $request_data['vendor_id'] = $request->vendor_id;

        if(!empty($enc_category_id))
        {
            $request_data['category_id'] = $enc_category_id;
        }
        if(!empty($enc_sub_category_id))
        {
            $request_data['subcategory'] = $enc_sub_category_id;
        }
        if(!empty($enc_brand_id))
        {
            $request_data['brand_id'] = $enc_brand_id;
        }

        $request_data['page']     = $request->page;
      /*  if($ven_name)
        $maker_id                 = $request->vendor_id;
        $maker_id                 = base64_decode($maker_id);*/

        $shop_obj = $this->ShopSettings->where('maker_id',$maker_id)
                                       ->first();
                                    
        if($shop_obj)
        {
            $shop_arr = $shop_obj->toArray();
        }
        else
        {
            $shop_arr='';
        }
        
        if($request->has('page'))
        {
            $pageStart = $request->input('page'); /* pageStart : Indicates from which page to start.*/
        }
        else
        {
            $pageStart = 1; /* pageStart : Indicates from which page to start.*/
        } 

        $category_id = "";
       
        $category_id     = base64_decode($enc_category_id);    
        $sub_category_id = base64_decode($enc_sub_category_id);  
        
        /*$product_arr = $this->ElasticSearchService->search($request,$per_page);*/
        if(isset($maker_id) && ($product_id == '' && empty($enc_sub_category_id) && empty($enc_category_id )))
        {
          if(!isset($brand_id) || $brand_id == '')
          {
               $arr_brands = $this->BrandsModel->where('user_id',$maker_id)
                                          ->where('is_active',1)
                                          ->get()
                                          ->toArray();

               $apppend_data['vendor_id'] = base64_encode($maker_id);

               $paginator = $this->get_pagination_data($arr_brands, $pageStart, $per_page, $request_data);

            

              if($paginator)
              {
                  $pagination_links    =  $paginator;  
                  $arr_data            =  $paginator->items(); 
              } 
              
              if(count($arr_brands) == 1 )
              {
                  $product_arr = $this->ElasticSearchService->search($request,$per_page);
              }
          }
          else
          {
           $product_arr = $this->ElasticSearchService->search($request,$per_page);
          }        
        }
        else
        {
          $product_arr = $this->ElasticSearchService->search($request,$per_page);
        }

      
        if(isset($product_arr) && count($product_arr) > 0)  
        {

          $product_arr['total_results'] = isset($product_arr['total_results']) ? $product_arr['total_results'] : 0;

          $product_arr['arr_data'] = array_map(function($tmp_data)
          {
              return $tmp_data['_source'];
          },  $product_arr['arr_data']);
                                              
                    

          $paginator = $this->get_pagination_data_maker_details($product_arr['arr_data'],$product_arr['total_results'],$per_page,$request_data);


          if($paginator)
          {
              $pagination_links    =  $paginator;  
              $arr_data            =  $paginator->items(); 
          }  
        }

        $cat_id_arr = $this->ProductsModel->where('user_id',$maker_id)
                                          ->whereHas('categoryDetails.translations',function($q1){
                                            return $q1->orderBy('category_slug','ASC');
                                          })
                                           ->with(['categoryDetails'=>function($query)
                                           {
                                              return $query->select('id');
                                                    

                                           }])
                                                                                   /*->whereHas('categoryDetails.get_cat_name',function($q){
                                              return $q->orderBy('category_slug','ASC');
                                           })*/
                                          ->with(['categoryDetails.get_cat_name'=>function($q){
                                              return $q->orderBy('category_slug','ASC');
                                           }])
                                           ->with('categoryDetails.subcategory_details')
                                           ->where('is_active',1)
                                           ->where('product_complete_status',4)
                                           ->where('category_id','!=',0)
                                           ->groupBy('category_id')

                                           ->get(['category_id'])->toArray();
        
        /* Sort by Alpha */ 
        usort($cat_id_arr, function($sort_base, $sort_compare) {
            return $sort_base['category_details']['category_name'] <=> $sort_compare['category_details']['category_name'];
        });                                     

        $maker_obj = $this->UserModel->with(['store_details','maker_details'])
                                     ->where('id',$maker_id)
                                     ->first();
        if($maker_obj)
        {
            $maker_arr = $maker_obj->toArray();
        }
           
        $fav_maker_count = $this->CustomerFavoriteModel->where('retailer_id',$user_id)
                                               ->where('maker_id',$maker_id)
                                               ->count();
   
        $fav_product_arr = $this->CustomerFavoriteModel->where('retailer_id',$user_id)
                                               ->where('type','product')
                                               ->get()
                                               ->toArray();

        $fav_product_id_arr = array_column($fav_product_arr,'product_id');
       
     
        // $catalog_data = $this->CatlogsModel->with(['catlog_details'=>function($q)                                 {
        //                                       $q->select('id','catalog_id','image','sequence','is_active');
        //                                       $q->where('is_active',1);
        //                                       $q->orderBy('sequence','ASC');
                                                
        //                                    }])
        //                                   ->where('is_active',1)
        //                                   ->where('maker_id',$maker_id)
        //                                   ->groupBy('id')
        //                                   ->get()
        //                                   ->toArray(); 


        $catalog_data = $this->CatlogsModel->with(['catalogPageDetails'=>function($q)                                  {
                                               $q->where('is_active',1);
                                               $q->orderBy('sequence','ASC');
                                                    
                                            },'catalogPageDetails.getCatalogImageData'])
                                            ->where('is_active',1)
                                            ->where('maker_id',$maker_id)
                                            //->groupBy('id')
                                            ->get()
                                            ->toArray();  


        /*get promotion details from promotion id*/
          $current_month = date('m');
          $promotion_arr = [];
        
  
          $promotion_arr = $this->PromotionsModel->with(['get_promotions_offer_details.get_prmotion_type','get_promo_code_details'])
                                                 ->where('maker_id',$maker_id)
                                                 ->where('is_active','1')
                                                 ->get()
                                                 ->toArray();
          



           /*get catalog pdf data*/
            $pdfData = [];
            $pdfData = $this->CatalogPdfModel->where('vendor_id',$maker_id)
                                             ->where('is_active',1)
                                             ->get()
                                             ->toArray();
            // $imagick = new \Imagick($pdfData[0]['pdf_file'][0]);          
           /*---------------------------*/



          $first_product_id = $request->input('product_id');

          if(isset($first_product_id))
          { 
            $first_product_id = intval(base64_decode($first_product_id));
            $arr_data         = [];
            $obj_data         = $this->ProductsModel->with(['productDetails.inventory_details',
                                                            'categoryDetails']) 
                                                    ->where('id',$first_product_id)
                                                    ->first();
            if($obj_data)
            {
              $first_product_arr = $obj_data->toArray();
              if (isset($first_product_arr['product_details'][0]['sku'])) {
                 $first_prod_sku    = $first_product_arr['product_details'][0]['sku']; 
               } 
                
            }
          
            if(isset($first_prod_sku))
            {                                    
              $first_pro_details = get_style_dimension($first_prod_sku);
              $first_pro_qty     = get_product_quantity($first_prod_sku);
            }
          } 
          

          /*Meta Details Start...*/ 
          $arr_meta_details = [];
          if($maker_id!="" && $brand_id=="" && $product_id=="")
          { 
              $arr_meta_brands = [];
              $obj_meta_brands = $this->BrandsModel->where('user_id',$maker_id)
                                          ->where('is_active',1)
                                          ->first();
              if($obj_meta_brands)
              {
                $arr_meta_brands = $obj_meta_brands->toArray();
              }                            
            
            $meta_brand_image = "";
            $brand_image = isset($arr_meta_brands['brand_image'])?$arr_meta_brands['brand_image']:"";
            $meta_brand_name = isset($arr_meta_brands['brand_name'])?$arr_meta_brands['brand_name']:"Vendor Brand";

            if($brand_image!="")
            {
              $meta_brand_image = url('/storage/app/'.$brand_image); 
              //$meta_brand_image = url('/storage/app/'.$brand_image); 
            }
            else
            {
              $meta_brand_image = url('/assets/images/no-product-img-found.jpg');
            }

            
            $arr_meta_details['meta_title'] = $meta_brand_name;
            $arr_meta_details['meta_large_image_content']  = 'vendor_large_image';
            $arr_meta_details['meta_image'] = $meta_brand_image; 
          

          }
          elseif($maker_id!="" && $brand_id!="" && $product_id=="") 
          {
              //get details od brand and 
              $arr_meta_brands = $this->BrandsModel->where('user_id',$maker_id)
                                          ->where('id',$brand_id)
                                          ->where('is_active',1)
                                          ->first()
                                          ->toArray();

              $arr_maker_details = $this->BrandsModel->where('user_id',$maker_id)
                                                    ->first()->toArray();                         
              
              $meta_vendor_name = isset($arr_maker_details['brand_name'])?$arr_maker_details['brand_name']:"";
              $meta_vender_brand_name = isset($arr_meta_brands['brand_name'])?$arr_meta_brands['brand_name']:"";

              $meta_product_name = isset($product_arr['arr_data'][0]['product_name'])?$product_arr['arr_data'][0]['product_name']:"";

                $meta_image = "";
                $brand_image = isset($arr_meta_brands['brand_image'])?$arr_meta_brands['brand_image']:"";

                if($brand_image!="")
                {
                  $meta_image = url('/storage/app/'.$brand_image); 
                }
                else
                {
                  $meta_image = url('/assets/images/no-product-img-found.jpg');
                }

              $arr_meta_details['meta_title']  =    $meta_vendor_name.'/'.$meta_vender_brand_name;
              $arr_meta_details['meta_large_image_content']  = 'product_large_image';
              $arr_meta_details['meta_image']  = $meta_image; 
              //dd($arr_meta_details);
          }
          elseif($maker_id!="" && $brand_id!="" && $product_id!="") 
          {
              
              $product_details = $this->ProductsModel->where('id',$product_id)
                                                    ->with(['brand_details'=>function($q){
                                                      $q->select('id','brand_name');
                                                    }])
                                                    ->select('id','brand','product_name','product_image')
                                                    ->first()->toArray();
              $product_name = isset($product_details['product_name'])?$product_details['product_name']:"";
              $product_image = isset($product_details['product_image'])?$product_details['product_image']:"";  
              $brand_name = isset($product_details['brand_details']['brand_name'])?$product_details['brand_details']['brand_name']:"";
              
                $meta_image = ""; 
                if($product_image!="")
                {

                  $meta_image = url('/storage/app/'.$product_image); 
                  $meta_image = image_resize($meta_image,230,230); 
                  
                }
                else
                {
                  $meta_image = url('/assets/images/no-product-img-found.jpg');
                }

              $arr_meta_details['meta_title']  = $brand_name.'/'.$product_name;
              $arr_meta_details['meta_large_image_content']  = 'product_large_image';
              $arr_meta_details['meta_image']  = $meta_image; 
             
          } 

          /*Meta Details Stop...*/  
           
          $this->arr_view_data['new_url']          = $new_url;
          
          $this->arr_view_data['message']          = isset($message)?$message:'';
          $this->arr_view_data['isProductActive']  = isset($isProductActive)?$isProductActive:'';

          $this->arr_view_data['catalog_data']      = $catalog_data;
          $this->arr_view_data['promotion_arr']     = $promotion_arr;
          $this->arr_view_data['pagination_links']  = $pagination_links;
          $this->arr_view_data['fav_product_arr']   = $fav_product_id_arr;
          $this->arr_view_data['fav_maker_count']   = $fav_maker_count;
          $this->arr_view_data['maker_product_arr'] = isset($paginator)?$paginator:false;
          $this->arr_view_data['product_arr']       = $product_arr;
          $this->arr_view_data['maker_arr']         = $maker_arr;
          $this->arr_view_data['shop_arr']          = $shop_arr;
          $this->arr_view_data['module_title']      = $this->module_title;
          $this->arr_view_data['page_title']        = 'Vendor Product';
          $this->arr_view_data['module_url_path']   = $this->module_url_path;
          $this->arr_view_data['cat_arr']           = $cat_id_arr;
          $this->arr_view_data['arr_brands']        = isset($paginator)?$paginator:false;
          $this->arr_view_data['request_values']    = $request->all();

          $this->arr_view_data['first_prod_arr']     = isset($first_product_arr)?$first_product_arr:''; 
          $this->arr_view_data['first_prod_details'] = isset($first_pro_details)?$first_pro_details:'';
          $this->arr_view_data['first_pro_qty']      = isset($first_pro_qty)?$first_pro_qty:'';
          $this->arr_view_data['maker_prod_arr']     = isset($maker_prod_arr)?$maker_prod_arr:'';
          $this->arr_view_data['meta_details']       = $arr_meta_details;
          

          $this->arr_view_data['pdf_arr']       = isset($pdfData)?$pdfData:[];
         
          
          if(isset($arr_brands) && count($arr_brands) > 1 )
          {
            return view($this->module_view_folder.'.brand_list',$this->arr_view_data);
          }
          else
          {
            return view($this->module_view_folder.'.maker_details_old',$this->arr_view_data);
          }

    }


    public function get_pagination_data_maker_details($arr_data = [], $count = 1, $per_page = 0, $append_data = [])
    {
        /* Pagination to an Array() */
         $paginator =  new LengthAwarePaginator($arr_data, $count, $per_page,Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));     
         $paginator->appends($append_data); /* Appends all input parameter to Links */
         return $paginator;
    }

    public function get_pagination_data($arr_data = [], $pageStart = 1, $per_page = 0, $apppend_data = [])
    {
      
        $perPage  = $per_page; /* Indicates how many to Record to paginate */
        $offSet   = ($pageStart * $perPage) - $perPage; /* Start displaying Records from this No.;*/        
        $count    = count($arr_data);

        /* Get only the Records you need using array_slice */
        $itemsForCurrentPage = array_slice($arr_data, $offSet, $perPage, true);

        /* Pagination to an Array() */
        $paginator =  new LengthAwarePaginator($itemsForCurrentPage, $count, $perPage,Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));      
         
        $paginator->appends($apppend_data); /* Appends all input parameter to Links */

        return $paginator;
    }

    public function get_product_details(Request $request)
    {
       
        $form_data = $request->all();
        $product_id = base64_decode($form_data['product_id']);

        /*check this product is active or not*/

        if(isset($product_id) && $product_id!='')
        {
            $isProductActive = $this->ProductsModel->where('id',$product_id)->pluck('is_active')->first();

            if($isProductActive !=1)
            {
               $message                      = "This product is currently unavailable.";
               $response['message']          = $message;
               
            }
           
            $response['isProductActive'] = $isProductActive;
        }  

    
        if($product_id)
        {
            $arr_data = [];
            $obj_data = $this->ProductsModel->with(['productDetails.inventory_details','categoryDetails','brand_details'])
                             ->where('id',$product_id)
                             ->first();
            if($obj_data)
            {
                $product_arr = $obj_data->toArray();
                //dd($product_arr);
            }
            
            $this->arr_view_data['product_arr'] = $product_arr;
            $html = view($this->module_view_folder.'._image_gallery',$this->arr_view_data)->render();
            
            if(isset($product_arr) && sizeof($product_arr)>0)
            {
              $sku_id = isset($product_arr['product_details'][0]['sku'])?$product_arr['product_details'][0]['sku']:"";
              $pro_details = get_style_dimension($sku_id);



              /*Meta details start*/

              $arr_meta_details = [];
              $product_name = isset($product_arr['product_name'])?$product_arr['product_name']:"";
              $product_image = isset($product_arr['product_image'])?$product_arr['product_image']:"";  
              $brand_name = isset($product_arr['brand_details']['brand_name'])?$product_arr['brand_details']['brand_name']:"";
             
              //dd($product_arr,$product_name,$product_image,$brand_name);

              $meta_image = ""; 
              if($product_image!="")
              {

                $meta_image = url('/storage/app/'.$product_image); 
                
              }
              else
              {
                $meta_image = url('/assets/images/no-product-img-found.jpg');
              }

              $arr_meta_details['meta_title']  = $brand_name.'/'.$product_name;
              $arr_meta_details['meta_large_image_content']  = 'product_large_image';
              $arr_meta_details['meta_image']  = $meta_image; 

              /*Meta details stop*/

              $response['pro_details'] = isset($pro_details)?$pro_details:"";
              $response['sku_id']     = $sku_id;                  
              $response['html']       = $html;                
              $response['arr_data']   = $product_arr;
              $response['meta_details'] = $arr_meta_details;
              $response['status']     = "SUCCESS";
            }
            else
            {
              $response['arr_data']   = $product_arr;
              $response['status']     = "FAILURE"; 
            }
        }
        else
        {
          $response['status']     = "FAILURE"; 

        }
        return response()->json($response);
    }


  /* new Code*/

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
         $count = $this->CustomerFavoriteModel->where('customer_id',$user_id)->where('maker_id',$id)->count();
         if($count > 0)
         {
             $response['status']      = 'ERROR';
             $response['description'] = 'Vendor is already added into the favorite list.';
             return response()->json($response);
         }
      }
      else if($type == 'product')
      {
          $count = $this->CustomerFavoriteModel->where('customer_id',$user_id)->where('product_id',$id)->count(); 
          if($count > 0)
          {
             $response['status']      = 'ERROR';
             $response['description'] = 'Product is already added into the favorite list.';
             return response()->json($response);
          }
      }


      if($type == 'maker')
      {
         $data['customer_id'] = $user_id;
         $data['maker_id']    = $id;
         $data['product_id']  = 0;
         $data['type']        = 'maker';

         $result = $this->CustomerFavoriteModel->create($data);
      }
      else if($type == 'product')
      {
         $data['customer_id'] = $user_id;
         $data['maker_id']    = 0;
         $data['product_id']  = $id;
         $data['type']        = 'product';
         
         $result = $this->CustomerFavoriteModel->create($data);
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


  public function remove_from_favorite(Request $request)
  {   
      // dd($request);
        $user_id = 0;
        
        $form_data = $request->all();

        $user = Sentinel::check();
       
        if(isset($user))
        {
           $user_id = $user->id; 
        }

        $id     = base64_decode($form_data['id']);
        $type   = $form_data['type'];

        
        if($type == 'maker')
        {
           $result = $this->CustomerFavoriteModel->where('customer_id',$user_id)->where('maker_id',$id)->delete();
        }
        else if($type == 'product')
        {
           $result = $this->CustomerFavoriteModel->where('customer_id',$user_id)->where('product_id',$id)->delete();
        }
        

        if($result)
        { 
              if($type == 'maker')
              {
                 $response['status']      = 'SUCCESS';
                 $response['description'] = 'Vendor has been removed from favorite list.'; 
              }
              else if($type == 'product')
              {
                $response['status']      = 'SUCCESS';
                $response['description'] = 'Product has been removed from favorite list.';
              }
             
             return response()->json($response);
        }
        else
        {
              if($type == 'maker')
              {
                 $response['status']      = 'ERROR';
                 $response['description'] = 'Error occurred while removing vendor from favorite list.'; 
              }
              else if($type == 'product')
              {
                $response['status']      = 'ERROR';
                $response['description'] = 'Error occurred while removing product from favorite list.';
              }
             
             return response()->json($response);
        }
  }

  public function catlogs($catalog_id)
  { 
     /*get all catalog data */
    
      $catalog_id = base64_decode($catalog_id);
      

      //get catalog name

      $catalog_name = $this->CatlogsModel->where('id',$catalog_id)->pluck('catalog_name')->first();

      // $catalog_data_arr = $this->CatalogPagesModel->with(['getCatalogImageData.productDeta.productDetails'])
      //                                        ->where('is_active',1)
      //                                        ->where('catalog_id',$catalog_id)
      //                                        ->orderBy('sequence','ASC')
      //                                        ->get()
      //                                        ->toArray();
      $catalog_data_arr = $this->CatalogPdfModel
                                             // ->where('is_active',1)
                                             ->where('id',$catalog_id)
                                             ->first()->toArray();

    // $catalogData = array_chunk($catalog_data_arr, 2);


    $this->arr_view_data['catalog_data']  =  $catalog_data_arr;
    $this->arr_view_data['catalog_name']  =  isset($catalog_name)?$catalog_name:'';
     
    $this->arr_view_data['page_title']    =  "Catalogs";

    // dd($this->arr_view_data);

     return view('front.catlogs',$this->arr_view_data);
  }

  public function catlogs_new($catalog_id)
  { 
     /*get all catalog data */
    
      $catalog_id = base64_decode($catalog_id);
      

      //get catalog name

      $catalog_name = $this->CatlogsModel->where('id',$catalog_id)->pluck('catalog_name')->first();

      $catalog_data_arr = $this->CatalogPagesModel->with(['getCatalogImageData.productDeta.productDetails'])
                                             ->where('is_active',1)
                                             ->where('catalog_id',$catalog_id)
                                             ->orderBy('sequence','ASC')
                                             ->get()
                                             ->toArray();
      

    $catalogData = array_chunk($catalog_data_arr, 2);


    $this->arr_view_data['catalog_data']  =  $catalogData;
    $this->arr_view_data['catalog_name']  =  isset($catalog_name)?$catalog_name:'';
     
    $this->arr_view_data['page_title']    =  "Catalogs";

    // dd($this->arr_view_data);

     return view('front.catlogs_old',$this->arr_view_data);
  }



  // public function getCatalogPdf()
  // {
  //    $getPdfArr = $this->CatalogPdfModel->  
  // }
}