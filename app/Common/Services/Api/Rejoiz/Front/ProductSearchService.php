<?php

namespace App\Common\Services\Api\Rejoiz\Front;

use Illuminate\Http\Request;

use App\Common\Services\SearchService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\HelperService;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;

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
use App\Models\SiteSettingModel;
use App\Models\GetQuoteModel;


use Session;
use Paginate;
use Sentinel;
use DB;


use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;


class ProductSearchService {

    
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
                                SiteSettingModel $SiteSettingModel,
                                GetQuoteModel $GetQuoteModel,
                                HelperService $HelperService,
                                EmailService $EmailService,
                                GeneralService $GeneralService

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
        $this->SiteSettingModel           = $SiteSettingModel; 
        $this->GetQuoteModel              = $GetQuoteModel;
        $this->HelperService              = $HelperService;
        $this->EmailService               = $EmailService;
        $this->GeneralService             = $GeneralService;

        $this->module_title               = "Products";
        $this->module_view_folder         = 'front';  
        $this->arr_view_data              = [];
    }


	public function search($request=null)
    { 
       if(Session::has('category')){Session::forget('category');}
            
        $request_all = $request->all();
        $category = isset($request_all['category'])?$request_all['category']:"";

        $active_cat_id   = array_column(get_category(),'id');
        $category_id_arr = [];

        $brands_arr = $this->HelperService->get_all_brands();

        if(count($brands_arr)==0)
        {
             $brands_arr = [];
        }

        $arr_data['search_value'] = $request->all();
        $arr_data['brands_arr']   = $brands_arr;

        if($category=="shop_now")            
        {   
            $arr_category = [];
            $arr_category = CategoryModel::with(['subcategory_details'=>function ($query){
              $query->where('is_active',1);
            }])->where('is_active',1)->get()->sortBy(function($CategoryModel){return $CategoryModel->category_name;})->toArray();

            

            if(isset($arr_category) && count($arr_category)>0){
              foreach ($arr_category as $k => $category) {
                if(isset($category['subcategory_details']) && count($category['subcategory_details'])>0){
                  $temp_arr = [];
                   foreach ($category['subcategory_details'] as $key => $sub_category) {
                      if(isset($sub_category['subcategory_slug']) && strtolower($sub_category['subcategory_slug']) == 'general'){
                        $temp_arr = $sub_category;
                        unset($arr_category[$k]['subcategory_details'][$key]);
                      }
                    } 

                    if(count($temp_arr) > 0)
                    {
                      array_push($arr_category[$k]['subcategory_details'],$temp_arr);  
                    }
                    
                }
              }
            }
           
            $arr_data['arr_category']   = $arr_category;
            //return view('front.search.new_arrivals_list',$this->arr_view_data);    
        }
    
        else
        { 
            $arr_data                 = $this->ElasticSearchService->search($request);

            $arr_data['brands_arr']   = $brands_arr; 


         /*if(isset($arr_data['response']) && sizeof($arr_data['response'])!=0)
            {
              // $arr_data['response'];
            }*/

        } 

      //dd($arr_data);
/*
       if(isset($arr_data['arr_data']) && !empty($arr_data['arr_data']))
       {
          foreach($arr_data['arr_data'] as $key => $val)
          {
             $prod_arr['product_id']     = $val['_source']['id'];
             $prod_arr['user_id']        = $val['_source']['user_id'];
             $prod_arr['category_id']    = $val['_source']['category_id'];
             $prod_arr['product_name']   = $val['_source']['product_name'];
             $prod_arr['wholesale_price']= $val['_source']['unit_wholsale_price'];
             $prod_arr['retail_price']   = $val['_source']['retail_price'];
             $prod_arr['available_qty']  = $val['_source']['available_qty'];
             $prod_arr['category_name']  = $val['_source']['category_name'];
             $prod_arr['brand_name']     = $val['_source']['brand_name'];
             $arr_data['product_arr'][]  = $prod_arr;
          }
       }
*/

        $response['status']     = 'success';
        $response['message']    = 'Product listing get successfully.';
        $response['data']       =  isset($arr_data)?$arr_data:[];

        return $response;   

    }


  public function product_details($request=null)
  { 
      
      $product_id = '';
      $maker_id = '';
      $product_arr = $maker_arr = [];
      $form_data = $request->all();      

      if(isset($form_data) && !empty($form_data) && isset($form_data['product_id']) && !empty($form_data['product_id']))
      {

        $product_id = base64_decode($form_data['product_id']);
      }
      else
      {
         $response['status']        = "failure";
         $response['message']       = "Something went wrong";
         $response['data']          = "";

         return $response;
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
            $arr_data['isProductActive'] = $isProductActive;



            if($isProductActive !=1)
            {
               $response['status']        = "failure";
               $response['message']       = "This product is currently unavailable.";
               $response['data']          = isset($arr_data)?$arr_data:[];

               return $response;
            }
           
        }  

        if($product_id)
        {
            $arr_data = [];
            $obj_data = $this->ProductsModel->with(['productDetails'=>function($product_details){$product_details->select('id','product_id','image','image_thumb','sku','sku_product_description','product_min_qty');},
                                                    'productDetails.inventory_details'=>function($inventory_details){$inventory_details->select('quantity','sku_no');},
                                                    'productDetails.productMultipleImages'=>function($multiple_sku_images){$multiple_sku_images->select('product_detail_id','product_image');},
                                                    'categoryDetails'=>function($category_details){$category_details->select('id');},
                                                    'brand_details'=>function($brand_details){$brand_details->select('id','brand_name','user_id');},
                                                    'maker_details'=>function($maker_details){$maker_details->select('id','user_id','is_get_a_quote','is_add_to_bag');}])
                             ->where('id',$product_id)
                             ->first();
            if($obj_data)
            {
                $product_arr = $obj_data->toArray();


                if(isset($product_arr['product_details']) && !empty($product_arr['product_details']))
                 {
                    foreach($product_arr['product_details'] as $key => $val)
                    {
                        $product_arr['product_details'][$key]['image']       = $this->imagePathProduct($val['image'], 'product', 0);
                        $product_arr['product_details'][$key]['image_thumb'] = $this->imagePathProduct($val['image_thumb'], 'product', 0);
                    }
                 }

                    if(isset($product_arr['product_details']) && !empty($product_arr['product_details']))
                   {
                      foreach($product_arr['product_details'] as $key => $val)
                      {
                         $arr_related_images[]     =  $this->imagePathProduct($val['image'], 'product', 0);
                      }
                   }

          
              $arr_data['id']                       = $product_arr['id'];
              $arr_data['product_name']             = $product_arr['product_name'];
              $arr_data['wholesale_price']          = $product_arr['unit_wholsale_price'];
              $arr_data['retail_price']             = $product_arr['retail_price'];
              $arr_data['available_qty']            = $product_arr['available_qty'];
              $arr_data['case_quantity']            = $product_arr['case_quantity'];
              $arr_data['about_product']            = $product_arr['description'];
              $arr_data['ingrediants']              = isset($product_arr['ingrediants'])?$product_arr['ingrediants']:'';
              $arr_data['product_image']            = imagePath($product_arr['product_image'],'product',0);
              $arr_data['product_image_thumb']      = imagePath($product_arr['product_image_thumb'],'product',0);
              $arr_data['shipping_type']            = $product_arr['shipping_type'];
              $arr_data['minimum_amount_off']       = $product_arr['minimum_amount_off'];
              $arr_data['off_type_amount']          = $product_arr['off_type_amount'];
              $arr_data['product_discount_type']    = $product_arr['prodduct_dis_type'];
              $arr_data['product_discount_min_amt'] = $product_arr['product_dis_min_amt'];
              $arr_data['product_discount']         = $product_arr['product_discount'];
              $arr_data['category_name']            = $product_arr['category_details']['category_name'];
              $arr_data['brand_name']               = $product_arr['brand_details']['brand_name'];
              $arr_data['is_get_a_quote']           = $product_arr['maker_details']['is_get_a_quote'];
              $arr_data['is_add_to_bag']            = $product_arr['maker_details']['is_add_to_bag'];
              $arr_data['multiple_sku_details']     = $product_arr['product_details'];

            }


              /*get maker details*/
              if(isset($form_data['vendor_id']) && !empty($form_data['vendor_id']))
              {
                $maker_id = base64_decode($form_data['vendor_id']);
                
                $maker_obj = $this->UserModel->with(['store_details','maker_details'])
                                     ->where('id',$maker_id)
                                     ->first();
                if($maker_obj)
                {
                    $maker_arr                = $maker_obj->toArray();
                    $minimum_order            = 0;

                    $get_minimum_order        = get_maker_shop_setting($maker_arr['maker_details']['user_id']);
                     if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum'] == 0){$minimum_order = 'No Minimum Limit';}
                     else if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum']!= 0)
                     {$minimum_order = '$'. num_format($get_minimum_order['first_order_minimum']).' Minimum';}

                    $arr_data['vendor_id']     = $maker_arr['id']; 
                    $arr_data['company_name']  = $maker_arr['maker_details']['company_name'];
                    $arr_data['vendor_name']   = $maker_arr['first_name']." ".$maker_arr['last_name'];
                    $arr_data['minimum']       = $minimum_order;  

                }
              }          
            
            
            if(isset($product_arr) && sizeof($product_arr)>0)
            {
              $sku_id             = isset($product_arr['product_details'][0]['sku'])?$product_arr['product_details'][0]['sku']:"";
              $pro_details        = get_style_dimension($sku_id);
              $pro_details['sku'] = $pro_details;


              /*Meta details start*/

              $arr_meta_details = [];
              $product_name     = isset($product_arr['product_name'])?$product_arr['product_name']:"";
              $product_image    = isset($product_arr['product_image'])?$product_arr['product_image']:"";  
              $brand_name       = isset($product_arr['brand_details']['brand_name'])?$product_arr['brand_details']['brand_name']:"";
              $brand_id         = isset($product_arr['brand_details']['id'])?$product_arr['brand_details']['id']:"";
             
              $meta_image = ""; 
              if($product_image!="")
              {
                $meta_image = url('/storage/app/'.$product_image); 
              }
              else
              {
                $meta_image = url('/assets/images/no-product-img-found.jpg');
              }

              $arr_data['meta_details']['meta_title']  = $brand_name.'/'.$product_name;
              $arr_data['meta_details']['meta_large_image_content']  = 'product_large_image';
              $arr_data['meta_details']['meta_image']  = $meta_image; 

              /*Meta details stop*/


              /*get first product detail*/
              $first_product_id = $request->input('product_id');

              if(isset($first_product_id))
              { 
                //$arr_data['multiple_images'] = get_multiple_images($first_product_id);

                $first_product_id = intval(base64_decode($first_product_id));
                $obj_data         = $this->ProductsModel->with(['productDetails.inventory_details',
                                                                'categoryDetails']) 
                                                        ->where('id',$first_product_id)
                                                        ->first();
                if($obj_data)
                {
                  $first_product_arr = $obj_data->toArray();
                  if (isset($first_product_arr['product_details'][0]['sku'])) {
                     $first_prod_sku = $first_product_arr['product_details'][0]['sku']; 
                   } 




                  if(isset($first_prod_sku))
                  {                                    
                    $arr_data['first_pro_qty']     = get_product_quantity($first_prod_sku);
                  }


                   /*get related category product*/

                    $category_id = isset($first_product_arr['category_id'])?$first_product_arr['category_id']:"";

                    $obj_subcategory =$this->ProductsSubCategoriesModel
                    ->where('product_id',$product_id)
                    ->first();

                    if($obj_subcategory)
                    {
                      $arr_subcategory = $obj_subcategory->toArray();

                      $request['subcategory'] = $arr_subcategory['sub_category_id'];
                      $request['category']    = $category_id;
                      $product_id             = isset($request['product_id'])?base64_decode($request['product_id']):0;
                      
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

                          $arr_data['related_product_arr'] = $related_product_arr;
                      }

                    }
                }
      
              }

              $response['status']  = 'success';
              $response['message'] = 'Product details get successfully.';
              $response['data']    = isset($arr_data)?$arr_data:[];
              return $response;

            }
            else
            {
              $response['status']  = 'failure';
              $response['message'] = 'Something went wrong';
              $response['data']    = '';
              return $response;
            }
        }
        else
        {
             $response['status']  = 'failure';
             $response['message'] = 'Something went wrong';
             $response['data']    = '';
             return $response;

        }

  }


 // Show image 
  public function imagePathProduct($image_name, $image_type, $is_resize)
  {
      $imagePath = "";
      $URL = url('/');
      $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;

      if($image_type == "product")
      {
        $isFileExixst = file_exists(base_path().'/storage/app/'.$image_name);
        
        if($isFileExixst && $is_resize == 0 && $image_name != "")
        {
          $imagePath = url('/storage/app/'.$image_name);
        }
        elseif($isFileExixst && $is_resize == 1 && $image_name != "")
        {
          $imagePath = image_resize(url('/storage/app/'.$image_name));
        }
        else
          $imagePath = $URL.config('app.project.img_path.product_default_images_app');
      }
      return $imagePath;
    }


  public function get_a_quote($form_data=null)
  {
      try
      {
            $user = isset($form_data['auth_user'])?$form_data['auth_user']:'';
            if($user)
            {
              $loggedInUserId = $user->id;
            }
            else
            {
              $loggedInUserId = 0;
            }

            $data      = [];
            $emailData = [];
            $form_data = isset($form_data)?$form_data:[]; 
            // Get product details
            $product_details = $this->ProductsModel->where('id',$form_data['product_id'])
                                                          ->with(['brand_details'=>function($q){
                                                            $q->select('id','brand_name');
                                                          }])
                                                          ->select('id','brand','product_name','product_image','retail_price')
                                                          ->first()->toArray();
                        
            //$site_setting_arr = $request->attributes->get('site_setting_arr');  

            // Get admin details
            $admin_details = $this->UserModel->where('id',1)
                                                 ->where('status','1')
                                                 ->where('is_approved','1')
                                                 ->first()->toArray();
            
            $admin_name  =  $admin_details['first_name'];                                    
            $admin_email =  $admin_details['email'];      
            $admin_id    =  $admin_details['id'];      
            
            // Calculate expected date
            $expected_date       = date('Y-m-d',strtotime("".$form_data['quote_no_of_days']." days"));
            $expected_date_email = date("m-d-Y", strtotime($expected_date));

            $vendor_details        = $this->UserModel->where('id',$form_data['vendor_id'])->select('email')->first()->toArray();
            $vendor_email          = $vendor_details['email'];
            
            if($form_data)
            {
               $data['product_id']      = $form_data['product_id'];
               $data['quantity']        = $form_data['quote_quantity'];
               $data['name']            = $form_data['quote_name'];
               $data['email']           = $form_data['quote_email'];
               $data['contact_number']  = $form_data['quote_contact_no'];
               $data['additional_note'] = $form_data['quote_additional_notes'];
               $data['no_of_days_to_expected_delivery'] = (int)$form_data['quote_no_of_days'];
               $data['expected_delivery_date']          = $expected_date;
               $data['vendor_id']       = $form_data['vendor_id'];
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
                'VENDOR_EMAIL'                => $vendor_email,
                'QUOTE_USER_NAME'             => $data['name'],
                'QUOTE_USER_EMAIL'            => $data['email'],
                'QUOTE_USER_CONTACT_NO'       => $data['contact_number'],
                'QUOTE_USER_ADDITIONAL_NOTES' => $data['additional_note'],
                'QUOTE_USER_QUANTITY'         => $data['quantity'],
                'PRODUCT_NAME'                => $product_details['product_name'],
                'PRODUCT_IMAGE'               => $product_details['product_image'],
                'PRODUCT_BRAND_NAME'          => $product_details['brand_details']['brand_name'],
                'SITE_URL'                    => $arr_site_setting['website_url'],
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
                $response['status']       = 'failure';
                $response['message']      = $e->getMessage();
                $response['data']         = '';  

                return $response;
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

              $response['status']      = 'success';
              $response['message']     = 'Get a Quote inquiry submitted successfully. Our team will communicate with you shortly. Thank You for your interest.'; 
              $response['data']        = '';  

                return $response;
                
            }
            else
            {
                $response['status']      = 'failure';
                $response['message']     = 'Error occurred while submit get a quote.';    
                $response['data']        = '';  

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



}

?>