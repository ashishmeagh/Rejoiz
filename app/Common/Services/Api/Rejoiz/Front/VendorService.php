<?php
namespace App\Common\Services\Api\Rejoiz\Front;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\SearchService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\HelperService;
use App\Models\UserModel;
use App\Models\RepresentativeMakersModel;

use App\Models\FavoriteModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\ShopImagesModel;
use App\Models\ShopSettings;
use App\Models\ProductsModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\PromotionsModel;
use App\Models\BrandsModel;


use Session;
use Paginate;
use Sentinel;
use DB;


use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;


class VendorService {

    
 public function __construct(SearchService $SearchService,
                                ElasticSearchService $ElasticSearchService,
                                UserModel     $UserModel,
                                RepresentativeMakersModel $RepresentativeMakersModel,
                                FavoriteModel $FavoriteModel,
                                MakerModel $MakerModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                ShopImagesModel $ShopImagesModel,
                                ShopSettings $ShopSettings,
                                ProductsModel $ProductsModel,
                                ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                CategoryModel $CategoryModel,
                                SubCategoryModel $SubCategoryModel,
                                PromotionsModel $PromotionsModel,
                                BrandsModel $BrandsModel,
                                HelperService $HelperService

                                )
    {        
        $this->SearchService              = $SearchService;
        $this->ElasticSearchService       = $ElasticSearchService;   
        $this->UserModel                  = $UserModel;
        $this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
        $this->RepresentativeMakersModel  = $RepresentativeMakersModel; 
        $this->FavoriteModel              = $FavoriteModel;
        $this->MakerModel                 = $MakerModel;
        $this->RoleModel                  = $RoleModel;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->ShopImagesModel            = $ShopImagesModel;
        $this->ProductsModel              = $ProductsModel;
        $this->ShopSettings               = $ShopSettings;
        $this->CategoryModel              = $CategoryModel;
        $this->SubCategoryModel           = $SubCategoryModel;
        $this->PromotionsModel            = $PromotionsModel;   
        $this->BrandsModel                = $BrandsModel;
        $this->HelperService              = $HelperService;

        $this->module_title               = "Products";
        $this->module_view_folder         = 'front';  
        $this->arr_view_data              = [];
    }
 


    public function search($letter = false,$per_page = false)
    {   
        try
        {
              $searching_word = $letter;
              $per_page       = isset($per_page)?$per_page:8;
             
              $vendors_details_arr = [];
              /*get all vendors*/
          
              /*row query for get all vendors*/

              $role_slug = 'maker';
                  
              $user_table        =  $this->UserModel->getTable();
              $prefix_user_table = DB::getTablePrefix().$user_table;

              $role_table        =  $this->RoleModel->getTable();
              $prefix_role_table = DB::getTablePrefix().$role_table;

              $role_user_table        =  $this->RoleUsersModel->getTable();
              $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

              $shop_table        =  $this->ShopImagesModel->getTable();
              $prefix_shop_table = DB::getTablePrefix().$shop_table;

              $maker_table        = $this->MakerModel->getTable();
              $prefix_maker_table = DB::getTablePrefix().$maker_table;

              $shop_setting_table        = $this->ShopSettings->getTable();
              $prefix_shop_setting_table = DB::getTablePrefix().$shop_table;
              
              $obj_vendors_details = DB::table($maker_table)
                                      ->select(DB::raw($prefix_user_table.".id as id,".
                                                   $prefix_user_table.".email as email, ".
                                                   $prefix_user_table.".status, ".
                                                   $prefix_user_table.".contact_no as contact_no, ".
                                                   $shop_table.".store_profile_image, ".
                                                   $maker_table.".brand_name, ".
                                                   $maker_table.".company_name, ".
                                                   $prefix_user_table.".first_name, ".
                                                  
                                                   "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                            .$prefix_user_table.".last_name) as user_name"
                                                   ))
                                      ->leftJoin($user_table,$prefix_user_table.'.id','=',$maker_table.'.user_id')
                                      ->leftJoin($shop_table,$prefix_shop_table.'.maker_id','=',$maker_table.'.user_id')
                                      ->where($user_table.'.status',1)
                                      ->where($user_table.'.is_approved',1)
                                      ->whereNull($user_table.'.deleted_at')
                                      ->where($user_table.'.id','!=',1)
                                      ->where($maker_table.'.company_name','!=',"")
                                      //->orderBy($maker_table.'.company_name','ASC');
                                      ->orderBy($maker_table.'.listing_sequence_no','ASC');
                                      if($letter!= false)
                                      {  
                                          if($letter == "&")
                                          { 
                                              $obj_vendors_details = $obj_vendors_details->whereNotIn(DB::raw('substr(company_name, 1, 1)'),['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z']);
                                          }
                                          else
                                          {
                                             $obj_vendors_details = $obj_vendors_details->where('company_name','LIKE', $searching_word.'%');
                                          }
                                      }

                                  $obj_vendors_details = $obj_vendors_details->paginate($per_page);


             
             
              if(isset($obj_vendors_details))
              {
                  $vendors_details_arr  =  $obj_vendors_details->toArray();
                  if(isset($vendors_details_arr['data']) && !empty($vendors_details_arr['data']))
                  {
                    foreach($vendors_details_arr['data'] as $key => $val)
                    {  
                      $arr_data['vendors'][$key]['id']           = $val->id;

                      if (isset($val->store_profile_image) && !empty($val->store_profile_image) && file_exists(base_path().'/storage/app/'.$val->store_profile_image)) {
                        $arr_data['vendors'][$key]['image']      = url('/').'/storage/app/'.$val->store_profile_image;
                      }
                      else{
                        $arr_data['vendors'][$key]['image']      = url('/').'/assets/images/no-product-img-found.jpg';
                      } 
                      $arr_data['vendors'][$key]['company_name'] = $val->company_name;
                    }
                  }

                   else{
                      $arr_data['vendors']  = [];
                 }
              } 



                $arr_data['pagination']["current_page"]    = $vendors_details_arr['current_page'];
                $arr_data['pagination']["first_page_url"]  = $vendors_details_arr['first_page_url'];
                $arr_data['pagination']["from"]            = $vendors_details_arr['from'];
                $arr_data['pagination']["last_page"]       = $vendors_details_arr['last_page'];
                $arr_data['pagination']["last_page_url"]   = $vendors_details_arr['last_page_url'];
                $arr_data['pagination']["next_page_url"]   = $vendors_details_arr['next_page_url'];
                $arr_data['pagination']["path"]            = $vendors_details_arr['path'];
                $arr_data['pagination']["per_page"]        = $vendors_details_arr['per_page'];
                $arr_data['pagination']["prev_page_url"]   = $vendors_details_arr['prev_page_url'];
                $arr_data['pagination']["to"]              = $vendors_details_arr['to'];
                $arr_data['pagination']["total"]           = $vendors_details_arr['total'];

               $response['status']      = 'success';
               $response['message']     = 'Representative details get successfully.';
               $response['data']        = isset($arr_data)?$arr_data:[];

               return $response; 
       }   

      catch(Exception $e){
           $response = [
            'status'  => 'failure',
            'message' => 'Something went wrong.',
            'data'    => ''
          ];

        return $response;
       }      

    }

    public function details($vendor_id=null)
    {
       try{
                 $arr_data  = [];
                 $vendor_id = isset($vendor_id)?$vendor_id:'';
                 $maker_obj = $this->UserModel->with(['store_details'=>function($store_details)
                                                                      {$store_details->select('maker_id','store_profile_image','store_cover_image');}
                                                                      ,'maker_details'=>function($maker_details)
                                                                      {$maker_details->select('user_id','company_name','brand_name');},'shop_settings'=>function($shop_settings)
                                                                      {$shop_settings->select('id','maker_id','first_order_minimum','shop_lead_time');}])
                                               ->where('id',$vendor_id)
                                               ->select('id','email','first_name','last_name','profile_image')
                                               ->first();
                  if($maker_obj)
                  {
                      $maker_data = $maker_obj->toArray();
                      $maker_name = "";
                      $maker_name = isset($maker_data['first_name'])?$maker_data['first_name']:''." ".isset($maker_data['last_name'])?$maker_data['last_name']:'';
                      $arr_data['id']            = isset($maker_data['shop_settings']['maker_id'])?$maker_data['shop_settings']['maker_id']:'';
                      $arr_data['name']          = $maker_name;
                      $arr_data['email']         = isset($maker_data['email'])?$maker_data['email']:'';
                      $arr_data['profile_image'] = isset($maker_data['profile_image'])?imagePath($maker_data['profile_image'],'user',0):'';
                      $arr_data['company_name']  = isset($maker_data['maker_details']['company_name'])?$maker_data['maker_details']['company_name']:'';
                      $arr_data['vendor_minimum']= isset($maker_data['shop_settings']['first_order_minimum'])?$maker_data['shop_settings']['first_order_minimum']:'';

                      if (isset($maker_data['store_details']['store_profile_image']) && !empty($maker_data['store_details']['store_profile_image']) && file_exists(base_path().'/storage/app/'.$maker_data['store_details']['store_profile_image'])) {
                                  $arr_data['store_profile_image']= url('/').'/storage/app/'.$maker_data['store_details']['store_profile_image'];
                                }
                      else{
                       $arr_data['store_profile_image']  = url('/').'/assets/images/no-product-img-found.jpg';
                      } 


                        if (isset($maker_data['store_details']['store_cover_image']) && !empty($maker_data['store_details']['store_cover_image']) && file_exists(base_path().'/storage/app/'.$maker_data['store_details']['store_cover_image'])) {
                                  $arr_data['store_cover_image']= url('/').'/storage/app/'.$maker_data['store_details']['store_cover_image'];
                                }
                      else{
                       $arr_data['store_cover_image']   = url('/').'/assets/images/no-banner-image-desktop.jpg';
                      } 

                      $arr_data['shop_lead_time']       = isset($maker_data['shop_settings']['shop_lead_time'])?$maker_data['shop_settings']['shop_lead_time'].' days':'No Lead time';
                      
                      $response['status']        = 'success';
                      $response['message']       = 'Vendor details get successfully.';
                      $response['data']          = isset($arr_data)?$arr_data:[];

                      return $response;
                    }  
             }


           catch(Exception $e){
           $response = [
            'status'  => 'failure',
            'message' => 'Something went wrong.',
            'data'    => ''
          ];

           return $response;
          }   
    }


    public function categories($vendor_id=null,$per_page=null)
    {
      try{
            $vendor_id  = isset($vendor_id)?$vendor_id:'';
            $per_page   = isset($per_page)?$per_page:'';
            $arr_cat    = [];
            $obj_categories = $this->ProductsModel->where('user_id',$vendor_id)
                                                ->whereHas('categoryDetails.translations',function($q1){
                                                  return $q1->orderBy('category_slug','ASC');
                                                })
                                                 ->with(['categoryDetails'=>function($query)
                                                 {
                                                    return $query->select('id');
                                                          
                                                 }])
                                                 ->where('is_active',1)
                                                 ->where('product_complete_status',4)
                                                 ->where('category_id','!=',0)
                                                 ->groupBy('category_id')->get();
              
            if($obj_categories)
            {  
              $categories_arr = $obj_categories->toArray();
            }
          
            if(isset($categories_arr) &&  !empty($categories_arr))
            {
              foreach($categories_arr as $key => $val)
              {
                 $arr_cat[] = $val['category_details']['id']; 
              }
            }


            $per_page         = isset($per_page)?$per_page:12; 
            $obj_categories   = $this->CategoryModel->where('is_active',1)
                                                    ->whereIn('id',$arr_cat)
                                                    ->orderBy('id','ASC');

            $obj_categories    = $obj_categories->paginate($per_page);

            if($obj_categories)
            {  
              $arr_categories    = $obj_categories->toArray();
            }


          $arr_data['categories_data'] = array();

          if(isset($arr_categories['data']) && !empty($arr_categories['data']))
          {
            foreach($arr_categories['data'] as $category)
            {
                 $temp_data = array();
                 $temp_data['category_id']    = $category['id'];
                 $temp_data['category_name']  = $category['category_name'];
                 $temp_data['category_image'] = imagePath($category['category_image'],'category',0);
                 $temp_data['subcategories']  =  array();

                 $obj_subcategories = $this->SubCategoryModel->where('is_active',1)->where('category_id',$category['id'])->get(); 
                 if($obj_subcategories)
                 {
                  $arr_subcategories = $obj_subcategories->toArray();
                 }

                if(isset($arr_subcategories) && !empty($arr_subcategories))
                { 
                    foreach($arr_subcategories as $key => $val)
                    {
                       $arr['subcategory_id']    = $val['id'];    
                       $arr['subcategory_name']  = $val['subcategory_name'];  
                       $arr['subcategory_slug']  = $val['subcategory_slug'];  

                       array_push($temp_data['subcategories'],$arr);  
                    }
               }
               array_push($arr_data['categories_data'],$temp_data);  
            }
          }  

            $arr_data['pagination']["current_page"]    = $arr_categories['current_page'];
            $arr_data['pagination']["first_page_url"]  = $arr_categories['first_page_url'];
            $arr_data['pagination']["from"]            = $arr_categories['from'];
            $arr_data['pagination']["last_page"]       = $arr_categories['last_page'];
            $arr_data['pagination']["last_page_url"]   = $arr_categories['last_page_url'];
            $arr_data['pagination']["next_page_url"]   = $arr_categories['next_page_url'];
            $arr_data['pagination']["path"]            = $arr_categories['path'];
            $arr_data['pagination']["per_page"]        = $arr_categories['per_page'];
            $arr_data['pagination']["prev_page_url"]   = $arr_categories['prev_page_url'];
            $arr_data['pagination']["to"]              = $arr_categories['to'];
            $arr_data['pagination']["total"]           = $arr_categories['total'];

           $response = [
          'status'  => 'success',
          'message' => 'Categories get successfully.',
          'data'    => isset($arr_data)?$arr_data:[]
        ];

          return $response; 

         } 

         catch(Exception $e){
           $response = [
            'status'  => 'failure',
            'message' => 'Something went wrong.',
            'data'    => ''
          ];

           return $response;
          } 

    }

    public function promotions($vendor_id=null,$per_page=null)
    {
      try{
            $vendor_id      = isset($vendor_id)?$vendor_id:'';
            $per_page       = isset($per_page)?$per_page:'';
            $promotion_arr  = $arr_data = [];
            $obj_promotions = $this->PromotionsModel->with(['get_promotions_offer_details.get_prmotion_type','get_promo_code_details'])
                                                    ->where('maker_id',$vendor_id)
                                                    ->where('is_active','1');
            if($obj_promotions)
            {    
              $obj_promotions = $obj_promotions->paginate($per_page);                                  
              $promotion_arr = $obj_promotions->toArray();     
            } 

            if(isset($promotion_arr['data']) && !empty($promotion_arr['data']))
            {
              foreach($promotion_arr['data'] as $key => $val)
              {
                $arr_data[$key]['title']      = $val['title'];
                $arr_data[$key]['from_date']  = $val['from_date'];
                $arr_data[$key]['to_date']    = $val['to_date'];
                $arr_data[$key]['promo_code'] = $val['get_promo_code_details']['promo_code_name'];
                if(isset($val['get_promotions_offer_details']) && count($val['get_promotions_offer_details'])>0)
                {  
                   foreach($val['get_promotions_offer_details'] as $k=>$promotions_offer)
                   { 
                     if($promotions_offer['promotion_type_id'] == 1)
                      { 
                         $arr_data[$key]['offer_type'][] = "Orders of " ."$".$promotions_offer['minimum_ammount'] ." receive free shipping";
                      }
                     elseif($promotions_offer['promotion_type_id'] == 2)
                      { 
                         $arr_data[$key]['offer_type'][] = "Orders of " ."$".$promotions_offer['minimum_ammount'] ." receive " .$promotions_offer['discount'] ."% off";
                      }
                  }
                }
              }
            }  

            else
            {
              $arr_data  = [];
            }

             $response   = [
              'status'  => 'success',
              'message' => 'Promotions get successfully.',
              'data'    => isset($arr_data)?$arr_data:[]
            ];

          return $response; 

                                   
         }

      catch(Exception $e){
           $response = [
            'status'  => 'failure',
            'message' => 'Something went wrong.',
            'data'    => ''
          ];

           return $response;
          }                                            
    }

    public function products($request=null)
    {
       try{

        $request_data = $request->all();
        $maker_id     = $request->vendor_id;
        $product_id   = $request->product_id;
        $per_page     = $request->per_page;



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
            $request_data['brand_id']   = $enc_brand_id;
        }

        $request_data['page']           = $request->page;
    

       
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
     }  

      catch(Exception $e){
         $response = [
          'status'  => 'failure',
          'message' => 'Something went wrong.',
          'data'    => ''
        ];

         return $response;
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


}

?>