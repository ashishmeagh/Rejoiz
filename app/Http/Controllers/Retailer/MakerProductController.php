<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductsModel;
use App\Models\UserModel;
use App\Models\TempBagModel;
use App\Models\ShopSettings;
use App\Models\FavoriteModel;
use App\Models\CustomerFavoriteModel;
use App\Models\CatlogsModel;
use App\Models\CatalogImageModel;
use App\Models\PromotionsModel;
use App\Models\CatalogPdfModel;
use App\Models\BrandsModel;
use App\Models\MakerModel;
use App\Models\CatalogPagesModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\GetQuoteModel;
use App\Models\SiteSettingModel;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;
use App\Models\SizeModel;
use App\Models\ProductSizesModel;
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
                                FavoriteModel $FavoriteModel,
                                CustomerFavoriteModel $CustomerFavoriteModel,
                                CatlogsModel $CatlogsModel,
                                CatalogImageModel $CatalogImageModel,
                                PromotionsModel $PromotionsModel,
                                BrandsModel $BrandsModel,
                                ElasticSearchService $ElasticSearchService,
                                MakerModel $MakerModel,
                                CatalogPagesModel $CatalogPagesModel,
                                CatalogPdfModel $CatalogPdfModel,
                                ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                GetQuoteModel $GetQuoteModel,
                                EmailService $EmailService,
                                GeneralService $GeneralService,
                                SiteSettingModel $SiteSettingModel,
                                HelperService $HelperService,
                                SizeModel $SizeModel,
                                ProductSizesModel $ProductSizesModel

    						   )
    {

    	$this->arr_view_data         = [];
    	$this->module_title          = "Vendors Products";
    	$this->module_view_folder    = 'front.maker'; 
    	$this->retailer_panel_slug   = config('app.project.retailer_panel_slug');
    	$this->module_url_path       = url($this->retailer_panel_slug.'/maker-profile');      
      $this->ProductsModel         = $ProductsModel;
      $this->UserModel             = $UserModel;
      $this->CatlogsModel          = $CatlogsModel;
      $this->TempBagModel          = $TempBagModel;
      $this->ShopSettings          = $ShopSettings;
      $this->PromotionsModel       = $PromotionsModel;
      $this->FavoriteModel         = $FavoriteModel;
      $this->CustomerFavoriteModel = $CustomerFavoriteModel;
      $this->CatalogImageModel     = $CatalogImageModel;
      $this->BrandsModel           = $BrandsModel;
      $this->ElasticSearchService  = $ElasticSearchService;
      $this->MakerModel            = $MakerModel;
      $this->CatalogPagesModel     = $CatalogPagesModel;
      $this->CatalogPdfModel       = $CatalogPdfModel;
      $this->ProductsSubCategoriesModel  = $ProductsSubCategoriesModel;
      $this->GetQuoteModel               = $GetQuoteModel;
      $this->EmailService                = $EmailService;
      $this->GeneralService              = $GeneralService;
      $this->SiteSettingModel            = $SiteSettingModel;
      $this->HelperService               = $HelperService;
      $this->SizeModel                  = $SizeModel;
      $this->ProductSizesModel          = $ProductSizesModel;

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
        

 

        $enc_category_id         = $request->input('category_id',null);
        $enc_sub_category_id     = $request->input('subcategory',null);
        
        $enc_price_low           = $request->input('price:low',null);
        $enc_price_high          = $request->input('price:high',null);

        $enc_vendor_minimum_low  = $request->input('vendor_minimum_low',null);
        $enc_vendor_minimum_high = $request->input('vendor_minimum_high',null);
        $enc_free_shipping       = $request->input('free_shipping',null);
        $enc_percent_of          = $request->input('percent_of',null);
        $enc_doller_of           = $request->input('doller_of',null);
        $enc_brand_id            = $request->input('brand_id',null);
        $enc_lead_time_min       = $request->input('lead_time_min',null);
        $enc_lead_time_max       = $request->input('lead_time_max',null);


        if($request->has('all_products'))
        {
          $enc_all_products ="all_products";
        }
        else
        {
          $enc_all_products = null;
        }
       

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

        if(!empty($enc_price_low) || $enc_price_low=="0")
        {
            $request_data['price:low'] = $enc_price_low;
           
        }

        if(!empty($enc_price_high))
        {
            $request_data['price:high'] = $enc_price_high;
        }

         if(!empty($enc_vendor_minimum_high))
        {
            $request_data['vendor_minimum_high'] = $enc_vendor_minimum_high;
        }

         if(!empty($enc_vendor_minimum_low) || $enc_vendor_minimum_low=="0")
        {
            $request_data['vendor_minimum_low'] = $enc_vendor_minimum_low;
        }

          if(!empty($enc_free_shipping))
        {
            $request_data['free_shipping'] = $enc_free_shipping;
        }

          if(!empty($enc_percent_of))
        {
            $request_data['percent_of'] = $enc_percent_of;
        }

          if(!empty($enc_doller_of))
        {
            $request_data['doller_of'] = $enc_doller_of;
        }

        if(!empty($enc_lead_time_min))
        {
          $request_data['lead_time_min'] = $enc_lead_time_min;
        }

        if(!empty($enc_lead_time_max))
        {
          $request_data['lead_time_max'] = $enc_lead_time_max;
        }

        if(!empty($enc_all_products))
        {
          $request_data['all_products'] = $enc_all_products;
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
              
              if($request->has("all_products"))
              {
                unset($request['brand_id']);
                $product_arr = $this->ElasticSearchService->search($request,$per_page);
                  
              }
          }
          else
          {
           if($request->has("all_products"))
              {
                unset($request['brand_id']);
                $product_arr = $this->ElasticSearchService->search($request,$per_page);
                  
              }
              else
              {
                $product_arr = $this->ElasticSearchService->search($request,$per_page);
              }
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

          //dd($request->all());
          
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
        
        $login_user = Sentinel::Check();
        if($login_user==true && $login_user->inRole('customer'))
        {
          $fav_maker_count = $this->CustomerFavoriteModel->where('customer_id',$user_id)
                                               ->where('maker_id',$maker_id)
                                               ->count();
   
          $fav_product_arr = $this->CustomerFavoriteModel->where('customer_id',$user_id)
                                               ->where('type','product')
                                               ->get()
                                               ->toArray();

        } 
        else{
          $fav_maker_count = $this->FavoriteModel->where('retailer_id',$user_id)
                                               ->where('maker_id',$maker_id)
                                               ->count();
   
          $fav_product_arr = $this->FavoriteModel->where('retailer_id',$user_id)
                                               ->where('type','product')
                                               ->get()
                                               ->toArray();

        } 
        

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
           //dd("ok12");
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
          $this->arr_view_data['search_value']  = $request->all();

          $this->arr_view_data['array_brands']  = $this->HelperService->get_maker_brands($maker_arr['maker_details']['user_id']);
          
         
          $is_all_products = $request->has('all_products');
          
          if($is_all_products!=false)
          { 
            return view($this->module_view_folder.'.maker_details',$this->arr_view_data);
          }
          
          if(isset($arr_brands) && count($arr_brands) > 1)
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
           
       
        $login_user = Sentinel::Check();
        if($login_user==true && $login_user->inRole('customer'))
        {
          $fav_maker_count = $this->CustomerFavoriteModel->where('customer_id',$user_id)
                                               ->where('maker_id',$maker_id)
                                               ->count();
   
          $fav_product_arr = $this->CustomerFavoriteModel->where('customer_id',$user_id)
                                               ->where('type','product')
                                               ->get()
                                               ->toArray();

        } 
        else{
          $fav_maker_count = $this->FavoriteModel->where('retailer_id',$user_id)
                                               ->where('maker_id',$maker_id)
                                               ->count();
   
          $fav_product_arr = $this->FavoriteModel->where('retailer_id',$user_id)
                                               ->where('type','product')
                                               ->get()
                                               ->toArray();

        } 

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
         $count = $this->FavoriteModel->where('retailer_id',$user_id)->where('maker_id',$id)->count();
         if($count > 0)
         {
             $response['status']      = 'ERROR';
             $response['description'] = 'Vendor is already added into the favorite list.';
             return response()->json($response);
         }
      }
      else if($type == 'product')
      {
          $count = $this->FavoriteModel->where('retailer_id',$user_id)->where('product_id',$id)->count(); 
          if($count > 0)
          {
             $response['status']      = 'ERROR';
             $response['description'] = 'Product is already added into the favorite list.';
             return response()->json($response);
          }
      }


      if($type == 'maker')
      {
         $data['retailer_id'] = $user_id;
         $data['maker_id']    = $id;
         $data['product_id']  = 0;
         $data['type']        = 'maker';

         $result = $this->FavoriteModel->create($data);
      }
      else if($type == 'product')
      {
         $data['retailer_id'] = $user_id;
         $data['maker_id']    = 0;
         $data['product_id']  = $id;
         $data['type']        = 'product';
         
         $result = $this->FavoriteModel->create($data);
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
           $result = $this->FavoriteModel->where('retailer_id',$user_id)->where('maker_id',$id)->delete();
        }
        else if($type == 'product')
        {
           $result = $this->FavoriteModel->where('retailer_id',$user_id)->where('product_id',$id)->delete();
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
     $catalog_data_arr = [];
    
      $catalog_id = base64_decode($catalog_id);
      

      //get catalog name

      $catalog_name = $this->CatlogsModel->where('id',$catalog_id)->pluck('catalog_name')->first();

      // $catalog_data_arr = $this->CatalogPagesModel->with(['getCatalogImageData.productDeta.productDetails'])
      //                                        ->where('is_active',1)
      //                                        ->where('catalog_id',$catalog_id)
      //                                        ->orderBy('sequence','ASC')
      //                                        ->get()
      //                                        ->toArray();
      $catalog_data_obj = $this->CatalogPdfModel
                                             // ->where('is_active',1)
                                             ->where('id',$catalog_id)
                                             ->first();

      if($catalog_data_obj)
      {
        $catalog_data_arr = $catalog_data_obj->toArray();        
      }

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


  public function new_product_detail(Request $request)
  { 
    
      
      $product_id = '';
      $maker_id = '';
      $product_arr = $maker_arr = [];
      $form_data = $request->all();      
      $size_arr = [];
      if(isset($form_data) && !empty($form_data) && isset($form_data['product_id']) && !empty($form_data['product_id']))
      {

        $product_id = base64_decode($form_data['product_id']);
      }
      else
      {
        return redirect()->back();
      }

      /*fetching site setting for max product size*/
      $arr_site_setting = [];
      $obj_site_setting = $this->SiteSettingModel->select('product_max_qty')->first();
      if(isset($obj_site_setting))
      {
        $arr_site_setting = $obj_site_setting->toArray();
      }

       /*check this product is active or not*/

        if(isset($product_id) && $product_id!='')
        {
            $isProductActive = $this->ProductsModel->where('id',$product_id)->pluck('is_active')->first();

            if($isProductActive !=1)
            {
               $message                      = "This product is currently unavailable.";
               $this->arr_view_data['message']          = $message;
               
            }
           
            $this->arr_view_data['isProductActive'] = $isProductActive;
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
          
              $this->arr_view_data['product_arr'] = $product_arr;
            }
            
            // $html = view($this->module_view_folder.'._image_gallery',$this->arr_view_data)->render();
            
            if(isset($product_arr) && sizeof($product_arr)>0)
            {
              $sku_id = isset($product_arr['product_details'][0]['sku'])?$product_arr['product_details'][0]['sku']:"";
              $pro_details = get_style_dimension($sku_id);



              /*Meta details start*/

              $arr_meta_details = [];
              $product_name = isset($product_arr['product_name'])?$product_arr['product_name']:"";
              $product_image = isset($product_arr['product_image'])?$product_arr['product_image']:"";  
              $brand_name = isset($product_arr['brand_details']['brand_name'])?$product_arr['brand_details']['brand_name']:"";
              $brand_id = isset($product_arr['brand_details']['id'])?$product_arr['brand_details']['id']:"";
             
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

              $this->arr_view_data['pro_details'] = isset($pro_details)?$pro_details:"";
              $this->arr_view_data['sku_id']     = $sku_id;                  
              // $this->arr_view_data['html']       = $html;                
              
              
              // $category_arr = $this->ElasticSearchService->activate_category_product('25');

              // dd($category_arr);


              

              /*get maker details*/
              if(isset($form_data['vendor_id']) && !empty($form_data['vendor_id']))
              {
                $maker_id = base64_decode($form_data['vendor_id']);
                
                $maker_obj = $this->UserModel->with(['store_details','maker_details'])
                                     ->where('id',$maker_id)
                                     ->first();
                if($maker_obj)
                {
                    $maker_arr = $maker_obj->toArray();
                }
              }                
              /*end*/



              /*get first product detail*/
              $first_product_id = $request->input('product_id');

              if(isset($first_product_id))
              { 

                $first_pro_details_mul_images = get_multiple_images($first_product_id);

                $first_product_id = intval(base64_decode($first_product_id));
                $arr_data         = [];
                $obj_data         = $this->ProductsModel->with(['productDetails.inventory_details'=>function($q){
                                                                  $q->where('is_deleted',0);
                                                                },
                                                                'categoryDetails']) 
                                                        ->where('id',$first_product_id)
                                                        ->first();
                if($obj_data)
                {
                  $first_product_arr = $obj_data->toArray();

                     
                  if (isset($first_product_arr['product_details'][0]['sku'])) {
                     $first_prod_sku    = $first_product_arr['product_details'][0]['sku']; 
                   } 

                   /*get related category product*/

                    $category_id = isset($first_product_arr['category_id'])?$first_product_arr['category_id']:"";

                    

                    $product_size = $this->ProductSizesModel->where('product_id',$first_product_id)->get()->toArray();

                    if(isset($product_size) && $product_size != null)
                    {
                        $size_arr = get_size_for_category($category_id);
                    }



                    $obj_subcategory =$this->ProductsSubCategoriesModel
                    ->where('product_id',$product_id)
                    ->first();

                    if($obj_subcategory)
                    {
                      $arr_subcategory = $obj_subcategory->toArray();


                      // $related_product_arr =$this->ProductsSubCategoriesModel
                      //                   ->where('sub_category_id',$arr_subcategory['sub_category_id'])
                      //                   ->with('productDetails')
                      //                   ->whereHas('productDetails',function($q) use($product_id){
                      //                                     $q->where('id','<>',$product_id);
                      //                                     $q->where('product_complete_status','4');
                      //                                     $q->where('is_active','1');
                      //                                     $q->where('is_deleted','0');
                      //                                     $q->orderBy('updated_at','DESC');
                      //                                    })
                      //                   ->limit(10)
                      //                   ->get()
                      //                   ->toArray();


                      // dd($related_product_arr);

                      $request['subcategory'] = $arr_subcategory['sub_category_id'];
                      $request['category'] = $category_id;
                      $product_id = isset($request['product_id'])?base64_decode($request['product_id']):0;
                      //dd($product_id);
                      //unset($request['brand_id']);
                      unset($request['vendor_id']);
                      unset($request['product_id']);


                      $request['subcategory'] =  isset($request['subcategory'])?base64_encode($request['subcategory']):0;
                      
                      $request['category_id'] = isset($request['category'])?base64_encode($request['category']):0;
                      unset($request['category']);

                     $temp_product_arr = $this->ElasticSearchService->search($request);
                     $related_product_arr = array();

                    

                      if(isset($temp_product_arr['arr_data']) && count($temp_product_arr['arr_data']>0))
                      {
                          foreach ($temp_product_arr['arr_data'] as $key => $value) {
                            if($key <= 7){
                              $temp_product_data = $value['_source'];
                              if(isset($temp_product_data) && $value['_id']!=$product_id)
                              {
                                
                                array_push($related_product_arr,$temp_product_data);
                              }
                            }
                          }
                      }

                    }

                    // dd($related_product_arr);

                     // $related_product_arr = $this->ProductsModel->where('is_active','1')
                     //                      ->where('is_deleted','0')                    
                     //                      ->where('product_complete_status',4)    
                     //                      ->where('id','<>',$product_id)    
                     //                      ->where('category_id',$category_id)    
                     //                      ->orderBy('updated_at','DESC')
                     //                      ->take(8)
                     //                      ->get()
                     //                      ->toArray();  

                    /*end related category*/                    
                }
              
                if(isset($first_prod_sku))
                {                                    
                  $first_pro_details = get_style_dimension($first_prod_sku);
                  
                  $first_pro_qty     = get_product_quantity($first_prod_sku);
                }
              }

            }
            else
            {
              $this->arr_view_data['arr_data']   = $product_arr;
              $this->arr_view_data['status']     = "FAILURE"; 
            }
        }
        else
        {
          $this->arr_view_data['status']     = "FAILURE"; 

        }
        // dd($first_product_arr);
      // / dd($first_pro_details_mul_images);
      $this->arr_view_data['page_title']    =  "Product Details";

      $this->arr_view_data['size_arr']     = isset($size_arr)?$size_arr:[];
      $this->arr_view_data['first_prod_arr']     = isset($first_product_arr)?$first_product_arr:[]; 
      $this->arr_view_data['first_prod_details'] = isset($first_pro_details)?$first_pro_details:[];
      $this->arr_view_data['first_prod_detail_mul_images'] = isset($first_pro_details_mul_images)?$first_pro_details_mul_images:[];
      $this->arr_view_data['first_pro_qty']      = isset($first_pro_qty)?$first_pro_qty:'';
      $this->arr_view_data['arr_data']           = isset($product_arr)?$product_arr:'';
      $this->arr_view_data['meta_details']       = isset($arr_meta_details)?$arr_meta_details:[];
      $this->arr_view_data['related_product_arr']       = isset($related_product_arr)?$related_product_arr:[];
      $this->arr_view_data['maker_arr']         = isset($maker_arr)?$maker_arr:[];
      $this->arr_view_data['request_values']    = $request->all();
      $this->arr_view_data['search_value']      = $request->all();
      $this->arr_view_data['arr_site_setting']  = $arr_site_setting;
      
     // dd($this->arr_view_data);
      return view($this->module_view_folder.'.product_detail',$this->arr_view_data);
  }


  // Send get a quote 
  public function send_get_a_quote(Request $request)
  {
    //dd($request);
      $user = Sentinel::check();
      if($user)
      {
        $loggedInUserId = $user->id;
      }
      else
      {
        $loggedInUserId = 0;
      }

      $data = [];
      $emailData = [];
      $product_id = '';
      $form_data = $request->all();
      $influencer_code = $form_data['influencer_code']; 
      
      if(isset($form_data['product_id']) && $form_data['product_id']!='')
      {
         $product_id = $form_data['product_id'];
      }
      elseif (isset($form_data['get_quote_product_id']) && $form_data['get_quote_product_id']!='')
      {
        $product_id = $form_data['get_quote_product_id'];
        
      }
      
      
       
      // Get product details
      $product_details = $this->ProductsModel->where('id',$product_id)
                                                    ->with(['brand_details'=>function($q){
                                                      $q->select('id','brand_name');
                                                    }])
                                                    ->select('id','brand','product_name','product_image','retail_price')
                                                    ->first()->toArray();
                                   
      $site_setting_arr = $request->attributes->get('site_setting_arr');   
      
      // Get admin details
      $admin_details = $this->UserModel->where('id',1)
                                           ->where('status','1')
                                           ->where('is_approved','1')
                                           ->first()->toArray();
      
      $admin_name =  $admin_details['first_name'];                                    
      $admin_email =  $admin_details['email'];      
      $admin_id =  $admin_details['id'];      
      
      // Calculate expected date
      $expected_date = date('Y-m-d',strtotime("".$form_data['quote_no_of_days']." days"));
      $expected_date_email = date("m-d-Y", strtotime($expected_date));
      
      if($form_data)
      {
         $data['product_id'] = $product_id;
         $data['quantity'] = $form_data['quote_quantity'];
         $data['name'] = $form_data['quote_name'];
         $data['email'] = $form_data['quote_email'];
         $data['contact_number'] = $form_data['quote_contact_no'];
         $data['additional_note'] = $form_data['quote_additional_notes'];
         $data['no_of_days_to_expected_delivery'] = (int)$form_data['quote_no_of_days'];
         $data['expected_delivery_date'] = $expected_date;
         $data['vendor_id'] = $form_data['vendor_id'];
         $data['influencer_code'] = $form_data['influencer_code'];
         //dd($data);
         //echo '<pre>'; var_dump($data); die;
         $result = $this->GetQuoteModel->create($data);
      }
      

      /*Get site setting data from helper*/

      $arr_site_setting = get_site_settings(['site_name','website_url']);

      $credentials = ['email' => $admin_email];
      
      $arr_user = get_user_by_credentials($credentials);

      if($result)
      { 
        // send mail to Admin     

        $arr_built_content = [
          'ADMIN_NAME'                  => $admin_name,
          'ADMIN_EMAIL'                 => $admin_email,
          'VENDOR_NAME'                 => $form_data['vendor_name'],
          'VENDOR_EMAIL'                => $form_data['vendor_email'],
          'QUOTE_USER_NAME'             => $data['name'],
          'QUOTE_USER_EMAIL'            => $data['email'],
          'QUOTE_USER_CONTACT_NO'       => $data['contact_number'],
          'QUOTE_USER_ADDITIONAL_NOTES' => $data['additional_note'],
          'QUOTE_USER_QUANTITY'         => $data['quantity'],
          'PRODUCT_NAME'                => $product_details['product_name'],
          'PRODUCT_IMAGE'               => $product_details['product_image'],
          'PRODUCT_BRAND_NAME'          => $product_details['brand_details']['brand_name'],
          'SITE_URL'                    => $site_setting_arr['site_name'],
          'EXPECTED_DAYS'               => $form_data['quote_no_of_days'].' Days',
          'EXPECTED_DELIVERY_DATE'      => $expected_date_email,
          'VENDOR_COMPANY'              => $form_data['company_name'],
          'PROJECT_NAME'                => $arr_site_setting['site_name']
        ];

        $arr_mail_data['email_template_id'] = '70';
        $arr_mail_data['arr_built_content'] = $arr_built_content;
        $arr_mail_data['arr_user']          = $arr_user;


        try 
        {
          
          $is_mail_sent = $this->EmailService->send_mail($arr_mail_data);
        }
        catch (\Exception $e) {
          $response['status']   = 'ERROR';
          $response['msg']      = $e->getMessage();

          return response()->json($response);
        }

        // send mail to Influencer     

        $arr_user_influencer = $this->UserModel->where('influencer_code',$influencer_code)->first()->toArray();
        $arr_built_content_influencer = [
          'INFLUENCER_NAME'             => $arr_user_influencer['first_name'],
          'ADMIN_EMAIL'                 => $admin_email,
          'VENDOR_NAME'                 => $form_data['vendor_name'],
          'VENDOR_EMAIL'                => $form_data['vendor_email'],
          'QUOTE_USER_NAME'             => $data['name'],
          'QUOTE_USER_EMAIL'            => $data['email'],
          'QUOTE_USER_CONTACT_NO'       => $data['contact_number'],
          'QUOTE_USER_ADDITIONAL_NOTES' => $data['additional_note'],
          'QUOTE_USER_QUANTITY'         => $data['quantity'],
          'PRODUCT_NAME'                => $product_details['product_name'],
          'PRODUCT_IMAGE'               => $product_details['product_image'],
          'PRODUCT_BRAND_NAME'          => $product_details['brand_details']['brand_name'],
          'SITE_URL'                    => $site_setting_arr['site_name'],
          'EXPECTED_DAYS'               => $form_data['quote_no_of_days'].' Days',
          'EXPECTED_DELIVERY_DATE'      => $expected_date_email,
          'VENDOR_COMPANY'              => $form_data['company_name'],
          'PROJECT_NAME'                => $arr_site_setting['site_name']
        ];
        
        $arr_mail_data_influencer['email_template_id'] = '88';
        $arr_mail_data_influencer['arr_built_content'] = $arr_built_content_influencer;
        $arr_mail_data_influencer['arr_user']          = $arr_user_influencer;

        
        try 
        {
          
          $is_mail_sent = $this->EmailService->send_mail($arr_mail_data_influencer);

        }
        catch (\Exception $e) {
          $response['status']   = 'ERROR';
          $response['msg']      = $e->getMessage();

          return response()->json($response);
        }

        // Send notificatons
        $vendor_view_href                 = url('/admin/quote_requests');
        $notification_arr                 = [];
        $notification_arr['from_user_id'] = $loggedInUserId;
        $notification_arr['to_user_id']   = $admin_id;

        $notification_arr['description']  = 'There is a Quote Request generate for '.$form_data['company_name'];

        $notification_arr['title']        = 'Get Quote Request';
        $notification_arr['type']         = 'admin';
        $notification_arr['link']         = $vendor_view_href;

        $this->GeneralService->save_notification($notification_arr);

        $response['status']      = 'SUCCESS';
        $response['description'] = 'Get a Quote inquiry submitted successfully. Our team will communicate with you shortly. Thank You for your interest.'; 
          
        return response()->json($response);
      }
      else
      {
          $response['status']      = 'ERROR';
          $response['description'] = 'Error occurred while submit get a quote.';             
             
          return response()->json($response);
      }
    
  }


}