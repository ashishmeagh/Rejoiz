<?php

use App\Models\SubCategoryModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\ProductsModel;
use App\Models\MakerModel;
use App\Models\BrandsModel;
use App\Models\TempBagModel;
use App\Models\CategoryModel;
use App\Models\LeadConversationModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\QuotesConversationModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\UserLastActiveModel;
use App\Models\ProductDetailsModel;
use App\Models\RepresentativeMakersModel;
use App\Models\ShopSettings;
use App\Models\GeneralSettingModel;
use App\Models\CountryModel;
use App\Models\SiteSettingModel;
use App\Models\UserModel;
use App\Models\RetailerModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\ProductInventoryModel;
use App\Models\SubCategoryTranslationModel;
use App\Models\ThirdSubCategoryTranslationModel;
use App\Models\FourthSubCategoryTranslationModel;
use App\Models\RepAreaModel;
use App\Models\NotificationsModel;
use App\Models\CategoryDivisionModel;
use App\Models\PromotionsOffersModel;
use App\Models\PromotionsModel;
use App\Models\PromoCodeModel;
use App\Models\OrderTrackDetailsModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\B2CPrivacySettingsModel;
use App\Models\RoleUsersModel;
use App\Models\MenuSettingModel;
use App\Models\ProductMultipleImagesModel;


use App\Models\ThirdSubCategoryModel;
use App\Models\FourthSubCategoryModel;

use App\Models\SizeModel;
use App\Models\ProductSizesModel;

function get_user_by_credentials($credentials=false){
  
  if($credentials==false){
    return false;
  }

  // $credentials = ['email' => $email];
  $user = Sentinel::findByCredentials($credentials);

  if($user)
  {
    return $user->toArray();
  }
  
  return false;
}

function get_slug($text=false)
{
  $slug = '-';
  if($text!=false){
    if(isset($text) && $text!=''){
      $slug = str_slug($text,'_');
    }
  }


  return $slug;
}


function get_admin_email()
{
  $email = "";
  $admin_role = Sentinel::findRoleBySlug('admin'); 
  $obj_admin = UserModel::where('id',$admin_role->id)->first();
  if($obj_admin)
  {
    $email = $obj_admin['email'];
  }
  return $email;
}


function get_user_email($user_id=false)
{
  $email = '';

  $email = UserModel::where('id',$user_id)->pluck('email')->first();

  return  $email;
}

function get_default_image($type =false)
{
  $image_path = url('/').'/assets/images/default_images/default.jpeg';
  
  if($type == "brand_image")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }

  if($type == "banner_image")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }

  if($type == "maker_detail")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }

  if($type == "find_rep")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }

  if($type == "product" || $type == "product_image")
  {
     $image_path = url('/').'/assets/images/default_images/default-product.png';
  }

  if($type == "front_banner")
  {
     $image_path = url('/').'/assets/images/default_images/no-banner-image.jpg';
  }
  if($type == "front_banner_small")
  {
     $image_path = url('/').'/assets/images/default_images/no-banner-image-small.jpg';
  }

  if($type == "shop_collection")
  {
     $image_path = url('/').'/assets/images/default_images/2-ban-img1.jpeg';
  }

  if($type == "shop_now")
  {
     $image_path = url('/').'/assets/images/default_images/3-ban-img1.jpeg';
  }

  if($type == "login_site_logo")
  {
     $site_setting_arr = get_site_settings();
     $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'';
     $image_path = $image_path = url('https://via.placeholder.com/160x53.png?text='.$site_name);
  }
  if($type == "shop_image")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }
  if($type == "catalog_image")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }
  if($type == "category_image")
  {
     $image_path = url('/').'/assets/images/no-product-img-found.jpg';
  }
  if($type == "user")
  {
    $image_path = url('/').'/assets/images/default_images/user-no-img.jpg';
  }
  if($type == "site_logo")
  {
    $image_path = url('/').'/assets/images/default_images/default.jpeg';
  }
  if($type == "fevicon")
  {
    $image_path = url('/').'/assets/images/default_images/default.jpeg';
  }
  if($type == "profile_image")
  {
    $image_path = url('/').'/assets/images/default_images/user-no-img.jpg';
  }



  return $image_path;
}

function get_role($id)
{
    $role = "";
    $role_array = RoleUsersModel::where('user_id',$id)->with(['role_name'])->first();

    if(isset($role_array))
    {
      $role = isset($role_array['role_name']['name'])?$role_array['role_name']['name']:''; 
    }
    return $role;                             
}

function get_area_type($area_id)
{
  $area_type = "";
  $area_type = RepAreaModel::where('id',$area_id)->pluck('area_type')->first();

  return $area_type; 
}

function get_order_data($order_id)
{
  $arr_order_data = [];

  $obj_order_data = RetailerQuotesModel::where('id',$order_id)->first();

  if($obj_order_data)
  {
    $arr_order_data = $obj_order_data->toArray();
  }
  return $arr_order_data;
}

function get_customer_order_data($order_id)
{
  $arr_order_data = [];

  $obj_order_data = CustomerQuotesModel::where('id',$order_id)->first();

  if($obj_order_data)
  {
    $arr_order_data = $obj_order_data->toArray();
  }
  return $arr_order_data;
}

function get_vendor_company_name()
{
  ini_set('max_execution_time', 0);
  $obj_brands = MakerModel::select(['company_name'])->orderBy('id','DESC')->limit(1)->get();
  $arr_company_data = $obj_brands->toArray();
  //dd($arr_company_data);
  $company_name = "Rejoiz-iStore001";
  if(!empty($arr_company_data)){
    $exp_company_name = explode("Rejoiz-iStore",$arr_company_data[0]['company_name']);
    //dd($exp_company_name);
    //echo "<pre>";print_r( $exp_company_name);die;
    if(!empty($exp_company_name) && isset( $exp_company_name[1])){
      $company_name_int = ($exp_company_name[1]) + 1;
      $company_name = 'Rejoiz-iStore'.sprintf("%'03d", $company_name_int);
      
    } else {
      $company_name = 'Rejoiz-iStore001';
    }
  }
  return $company_name;
}

function get_influencer_code()
{
  ini_set('max_execution_time', 0);
  $obj_influencer = UserModel::select(['influencer_code'])->where('influencer_code','!=','')->orderBy('id','DESC')->limit(1)->get();
  
  $arr_influencer_data = $obj_influencer->toArray();
  
  $influencer_code = "Rejoiz-influencer001";
  if(!empty($arr_influencer_data)){
    $exp_influencer_code = explode("Rejoiz-influencer",$arr_influencer_data[0]['influencer_code']);
    
    if(!empty($exp_influencer_code) && isset( $exp_influencer_code[1])){
      $influencer_code_int = ($exp_influencer_code[1]) + 1;
      $influencer_code = 'Rejoiz-influencer'.sprintf("%'03d", $influencer_code_int);
      
    } else {
      $influencer_code = 'Rejoiz-influencer001';
    }
  }
  return $influencer_code;
}

function get_retailer_dummy_store_name()
{
  ini_set('max_execution_time', 0);
  $obj_dummy_store_name = RetailerModel::select(['dummy_store_name'])->orderBy('id','DESC')->limit(1)->get();
  $arr_dummy_store_name = $obj_dummy_store_name->toArray();
 
  $dummy_store_name = "Rejoiz-customer-001";
  if(!empty($arr_dummy_store_name)){
    $exp_dummy_store_name = explode("Rejoiz-customer-",$arr_dummy_store_name[0]['dummy_store_name']);
   
    if(!empty($exp_dummy_store_name) && isset( $exp_dummy_store_name[1])){
      $dummy_store_name_int = ($exp_dummy_store_name[1]) + 1;
      $dummy_store_name = 'Rejoiz-customer-'.sprintf("%'03d", $dummy_store_name_int);
      
    } else {
      $dummy_store_name = 'Rejoiz-customer-001';
    }
  }
  return $dummy_store_name;
}

function get_lead_data($order_id)
{

  $arr_order_data = [];

  $obj_order_data = RepresentativeLeadsModel::where('id',$order_id)->first();

  if($obj_order_data)
  {
    $arr_order_data = $obj_order_data->toArray();
  }
  return $arr_order_data;
}


function get_brand_id($brand_name=false)
{
  $brand_id = '';
  $obj_brands = BrandsModel::select(['id'])->where('brand_name',$brand_name)->first();

  if($obj_brands)
  {
    $arr_brand = $obj_brands->toArray();
    $brand_id = $arr_brand['id'];
  }

  return $brand_id;

}

/*pending from*/
function get_maker_brands($maker_id=false)
{
  $arr_brands = [];
  if(isset($maker_id) && !empty($maker_id) && $maker_id != false)
  {
     $arr_brands = BrandsModel::where('user_id',$maker_id)
                            ->where('is_active','1')
                            ->get()
                            ->toArray();
  }
  return $arr_brands;
}

  function get_sku_count($sku_no = false)
  {
    $sku_count = $sku_data = ProductDetailsModel::where('sku',$sku_no)->where('is_deleted',0)->count();
    return $sku_count;
  }

  function get_sku($product_id=false)
  {
    $sku = 0;
    $sku_no_obj = ProductDetailsModel::where('product_id',$product_id)->where('is_deleted',0)->get();
    if(isset($sku_no_obj))
    {
      $sku_no_arr =  $sku_no_obj->toArray();
      if(!empty($sku_no_arr)){
        $sku = $sku_no_arr[0]['sku'];
      }
    } 
    return $sku;
  }

  function get_all_sku($product_id=false)
  {   
    $arr_sku = [];
    $sku_no_obj = ProductInventoryModel::where('product_id',$product_id)->where('is_deleted',0)->get();

    if(isset($sku_no_obj))
    {
      $arr_sku =  $sku_no_obj->toArray();
    } 
    return $arr_sku;
  }


function get_product_quantity($sku_no=false)
{
  $quantity = 0;
  if($sku_no != false)
  {
    $obj_product = ProductInventoryModel::where('sku_no',$sku_no)->where('is_deleted',0)->first();
    if(isset($obj_product))
    {
      $quantity = $obj_product['quantity'];
    }
  }
  return $quantity;
}

function get_total_sku_inventory($product_id = false)
{
  $invetory = $total_inventory = 0;
  if($product_id != false)
  {
    $product_arr = ProductInventoryModel::where('product_id',$product_id)->where('is_deleted',0)->get()->toArray();

    if(isset($product_arr) && count($product_arr)>0)
    {
        foreach ($product_arr as $key => $product) 
        { 
           $total_inventory+= $product['quantity'];
        }
    }
  }

  return $total_inventory;
}

function get_maker_company_name($maker_id=false)
{
  $company_name = "";
  $obj_maker = MakerModel::where('user_id',$maker_id)->first();
  if($obj_maker)
  {
    $arr_maker = $obj_maker->toArray();
    if(isset($arr_maker['company_name']) && !empty($arr_maker['company_name']))
    {
      $company_name = $arr_maker['company_name'];
    }
  }
  return $company_name;
}

function get_catrgory_name($category_id=false)
{ 
  $category_name = false;
  if(isset($category_id) && $category_id !=false)
  {
    $obj_category = CategoryTranslationModel::where('category_id',$category_id)->first();
     if(isset($obj_category))
     {
      $category_name = $obj_category['category_name'];
     }
  }
  return $category_name;
}

  function get_subcategory_name($subcategory_id=false)
  { 
    $sub_category_name = "";
    if($subcategory_id != false)
    {
      $obj_subcategory = SubCategoryTranslationModel::where('subcategory_id',$subcategory_id)->first();
      if(isset($obj_subcategory))
      {
        $obj_subcategory = $obj_subcategory->toArray();
        $sub_category_name = $obj_subcategory['subcategory_name'];
      } 
    }
    return $sub_category_name;
  }


  function get_second_subcategory_name($sec_subcategory_id=false)
  { 
    $sec_sub_category_name = "";
    if($sec_subcategory_id != false)
    {
      $obj_sec_subcategory = ThirdSubCategoryTranslationModel::where('third_sub_category_id',$sec_subcategory_id)->first();
      if(isset($obj_sec_subcategory))
      {
        $obj_sec_subcategory = $obj_sec_subcategory->toArray();
        $sec_sub_category_name = $obj_sec_subcategory['third_sub_category_name'];
      } 
    }
    return $sec_sub_category_name;
  }

  function get_third_subcategory_name($third_subcategory_id=false)
  { 
    $third_sub_category_name = "";
    if($third_subcategory_id != false)
    {
      $obj_third_subcategory = FourthSubCategoryTranslationModel::where('fourth_sub_category_id',$third_subcategory_id)->first();
      if(isset($obj_third_subcategory))
      {
        $obj_third_subcategory = $obj_third_subcategory->toArray();
        $third_sub_category_name = $obj_third_subcategory['fourth_sub_category_name'];
      } 
    }
    return $third_sub_category_name;
  }
  
  function get_thirdsubcategory_name($thirdsubcategory_id=false)
  { 
    $thirdsub_category_name = "";
    if($thirdsubcategory_id != false)
    {
      $obj_thirdsubcategory = ThirdSubCategoryTranslationModel::where('third_sub_category_id',$thirdsubcategory_id)->first();
      if(isset($obj_thirdsubcategory))
      {
        $obj_thirdsubcategory = $obj_thirdsubcategory->toArray();
        $thirdsub_category_name = $obj_thirdsubcategory['third_sub_category_name'];
      } 
    }
    return $thirdsub_category_name;
  }

  function get_area_name($area_id=false)
  {
    $area_name = "";
    if($area_id != false)
    {
      $obj_area = RepAreaModel::where('id',$area_id)->first();

      if(isset($obj_area))
      {
        $area_name = $obj_area->area_name;
      }
    }    
    return $area_name; 
  }

  function get_maker_name($maker_id=false)
  {
    $user_name = "";
    if($maker_id != false)
    {
       $obj_user = UserModel::where('id',$maker_id)->first();
    
      if(isset($obj_user))
      {
        $user_name = $obj_user['first_name'].' '.$obj_user['last_name'];
      }
    }    
    return $user_name;  
  }

  function get_product_brand_name($brand_id=false)
  {
    $brand_name = "";
     $obj_brand_name = BrandsModel::where('id',$brand_id)->first();
    
     if(isset($obj_brand_name))
     {
      $brand_name = $obj_brand_name['brand_name'];
     }
    return $brand_name;
  }


function get_retailer_shop_name($retailer_id=false)
{
  $store_name = "";
   $obj_retailer = RetailerModel::where('user_id',$retailer_id)->first();
   if(isset($obj_retailer))
   {
    $store_name = $obj_retailer['store_name'];
   }
  return $store_name;
}

function get_retailer_dummy_shop_name($retailer_id=false)
{
  $store_name = "";
   $obj_retailer = RetailerModel::where('user_id',$retailer_id)->first();
   if(isset($obj_retailer))
   {
    $store_name = $obj_retailer['dummy_store_name'];
   }
  return $store_name;
}

function get_subcategory_id($product_id=false)
{
  $subcategory_id = "";
  $obj_subcat = ProductsSubCategoriesModel::where('product_id',$product_id)->first();
  if(isset($obj_subcat))
  {
    $subcategory_id = $obj_subcat['sub_category_id'];
  }
  return $subcategory_id;
}


function get_site_settings($arr_filter = [])
{
  $arr_site_setting_data = [];  
  $obj_site_setting_data = SiteSettingModel::first();
  if($obj_site_setting_data){
    $arr_site_setting_data = $obj_site_setting_data->toArray();
  }

  $arr_return = [];
  if(isset($arr_filter) && count($arr_filter)>0){
    foreach ($arr_filter as $key => $data) {

        $arr_return[$data] = isset($arr_site_setting_data[$data])&&$arr_site_setting_data[$data]!=""?$arr_site_setting_data[$data]:"-";
    }
  }
  else{
    $arr_return = $arr_site_setting_data;
  }

  return $arr_return;
}



function generateRandomPassword() 
{
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
 
    $pass = []; //remember to declare $pass as an array

    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

    for ($i = 0; $i < 6; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }

    return implode($pass); //turn the array into a string
}


function getProfileImage($image_name = false)
{
  $image = url('/').config('app.project.img_path.default_images').'user-no-img.jpg';
  //$image = url('/').'/assets/images/default.png';
  if(isset($image_name) && $image_name!="" && $image_name!=false)
  { 
    if(file_exists(base_path('/storage/app').'/'.$image_name)==true)
    { 
      $image = url('/storage/app').'/'.$image_name;
    }
  } 
  
  return $image; 
}

function getStoreImage($image_name = false, $type = "")
{
  if($type == 1)
    $image = url('/').config('app.project.img_path.default_images').'default-company-image.jpg';
  else  
    $image = url('/').config('app.project.img_path.default_images').'default-company-logo.jpg';
  
    //$image = url('/').'/assets/images/default.png';
  if(isset($image_name) && $image_name!="" && $image_name!=false)
  { 
    if(file_exists(base_path('/storage/app').'/'.$image_name)==true)
    { 
      $image = url('/storage/app').'/'.$image_name;
    }
  } 
  
  return $image; 
}

function get_sku_image($sku_id=null)
{
  $image = url('/').config('app.project.img_path.default_images').'product-default.jpg';
  if($sku_id)
  {

      $sku_data = ProductDetailsModel::where('sku',$sku_id)->where('is_deleted',0)->first(['image']);
      $image_name = isset($sku_data->image)?$sku_data->image:"";

     
      if(file_exists(base_path('/storage/app').'/'.$image_name)==true)
      { 
        $image = url('/storage/app').'/'.$image_name;
      }  
  }

  return $image;
}

function get_style_dimension($sku_id=null)
{
  
  if($sku_id)
  {

    $data = ProductDetailsModel::where('sku',$sku_id)->first(['sku','weight','length','width','height','option_type','option'])->toArray();
    if($data['option_type']==0)
    {
      $data['option_type']="Color";
    }
    elseif($data['option_type']==1)
    {
      $data['option_type']="Scent";
    }
    elseif($data['option_type']==2)
    {
      $data['option_type']="Size";
    }      
    elseif($data['option_type']==3) 
    {
      $data['option_type']="Material";
    }  
      
      return $data;
    }
}


function get_multiple_images($product_id=null)
{
  
  $arr_details = [];
  if($product_id)
  {

    $data = ProductMultipleImagesModel::where('product_id',base64_decode($product_id))
    ->select(['sku','product_id','product_detail_id','product_image'])
    ->where('is_deleted',0)
    ->get()
    ->toArray();
    
     if(!empty($data)){
        foreach($data as $det){
         $arr_details[$det['sku']][] = $det;
          
        }
    }
  }
 
   return $arr_details;
}


function get_category($category_id=null)
{
 
    $arr_category = [];
    if(isset($category_id) && $category_id!="")
    {
      $obj_category = CategoryModel::where('is_active',1)->where('id',$category_id)->first();
    }
    else
    {
      $obj_category = CategoryModel::where('is_active',1)->get();
    }   

    if($obj_category)
    {
      $arr_category = $obj_category->toArray();
    }

    return $arr_category;

}

function get_sub_category($subcategory_id=null)
{
    $arr_sub_category = [];
    if(isset($subcategory_id) && $subcategory_id!="")
    {
      $obj_sub_category = SubCategoryModel::where('is_active',1)->where('id',$subcategory_id)->first();
    }
    else
    {
      $obj_sub_category = SubCategoryModel::where('is_active',1)->get();
    }   

    if($obj_sub_category)
    {
      $arr_sub_category = $obj_sub_category->toArray();
    }

    return $arr_sub_category;
}




function get_subcategories($category_id = null)
{
    $arr_sub_category = [];
    if(isset($category_id) && $category_id!="")
    {
        $obj_sub_category = SubCategoryModel::where('category_id',$category_id)
                            ->where('is_active',1)
                            /*->select('id','subcategory_name')*/
                            ->get();
    }
    else
    {
        $obj_sub_category = SubCategoryModel::where('is_active',1)
                            /*->select('id','subcategory_name')*/
                            ->get();
    }

    if($obj_sub_category)
    {
      $arr_sub_category = $obj_sub_category->toArray();
    }

    return $arr_sub_category;
}

function get_user_name($user_id=false)
{
  $user_name = "-";
  if($user_id!=false)
  {
     $user_name = UserModel::where('id',$user_id)->first();
     if(isset($user_name))
     {
      $data = $user_name->toArray();
      $user_name = $data['first_name']." ".$data['last_name'];
     }
     else
     {
      $user_name = "-";
     }
  }
  return $user_name;
 
}


function get_maker_details($maker_id = false)
{
  $maker_data = [];
  if($maker_id != false)
  {
    $maker_id = base64_decode($maker_id);
 
    $maker_company_name = MakerModel::where('user_id',$maker_id)
                              ->select('company_name')
                              ->get()->toArray();
    $maker_data['company_name'] = $maker_company_name;

    $maker_brands = BrandsModel::where('user_id',$maker_id)
                             ->select(['brand_name','id'])
                             ->get()->toArray();

    $maker_data['maker_brands'] = $maker_brands;
  }

 return $maker_data;
}

function get_product_complete_status($product_id = null)
{
    $product_status ='';
    if(isset($product_id) && $product_id!="")
    {
        $obj_product_complete_status = ProductsModel::where('id',$product_id)
                            ->select('product_complete_status')
                            ->first();
    }
     
    if($obj_product_complete_status)
    {
      $arr_product_complete_status = $obj_product_complete_status->toArray();

      $product_status = $arr_product_complete_status['product_complete_status'];
    }

    return $product_status;
}

function format_date($date = false)
{
  return date('d-M-Y',strtotime($date));
}

function notification_format_date($date = false)
{
  return date('m-d-Y h:m:s',strtotime($date));
}

function us_date_format($date = false)
{
  return date('m-d-Y',strtotime($date));
}

function get_maker_quote_count($maker_id)
{
  $quote_count = 0;
  if(isset($maker_id) && $maker_id!="" && $maker_id!=0)
  {
    $quote_count = RetailerQuotesModel::where('maker_id',$maker_id)->where('order_cancel_status','!=',2)->count();
  }

  return $quote_count;

}

function get_retailer_quote_count($retailer_id=null,$maker_id=null)
{
  
  $quote_count = 0;
  if(isset($retailer_id) && $retailer_id!="" && $retailer_id!=0)
  {
    $quote_count = RetailerQuotesModel::where('retailer_id',$retailer_id)
                                        ->where('maker_id',$maker_id)
                                        ->where('order_cancel_status','!=',2)
                                        ->count();
  }

  return $quote_count;
}  

function get_maker_lead_count($maker_id)
{
  $lead_count = 0;
  if(isset($maker_id) && $maker_id!="" && $maker_id!=0)
  {
    $lead_count = RepresentativeLeadsModel::where('maker_id',$maker_id)->where('order_cancel_status','!=',2)->where('is_confirm',1)->orwhere('is_confirm',2)->count();
  }

  return $lead_count;

}

function get_maker_product_count($maker_id)
{
  $product_count = 0;
  if(isset($maker_id) && $maker_id!="" && $maker_id!=0)
  {
    $product_count = ProductsModel::where('user_id',$maker_id)->where('is_archive',0)->where('is_deleted',0)->count();
  }

  return $product_count;

}

function get_product_count()
{
  $product_count = 0;

    //$product_count = ProductsModel::where('is_archive',0)->where('is_deleted',0)->count();
    $product_count = ProductsModel::where('is_archive',0)
                     ->where('is_deleted',0)->where('product_complete_status',4)->count();

  return $product_count;

}

function get_product_details($product_id)
{//dd("olkjl");
    $arr_data = [];
    $obj_data = ProductsModel::where('id',$product_id)->first();
    if($obj_data)
    {
      $arr_data = $obj_data->toArray();
      
    }  

    return $arr_data;
}

function get_brand_name($user_id = null)
{
  $brand_name = '';

  $maker_obj = MakerModel::where('user_id',$user_id)->first();

  if($maker_obj)
  {
    $maker_arr  = $maker_obj->toArray();
    $brand_name = $maker_arr['brand_name'];
  }

  return $brand_name;
}

function get_brand_name_brandId($brand_id = null)
{
  $brand_name = '';

  $maker_obj = BrandsModel::where('id',$brand_id)->first();

  if($maker_obj)
  {
    $maker_arr  = $maker_obj->toArray();
    $brand_name = $maker_arr['brand_name'];
  }

  return $brand_name;
}

function get_bag_count()
{
  $bag_arr = [];
  
  $product_count = $loggedInUserId = 0;

  $user = \Sentinel::check();

  if($user)
  {
    $loggedInUserId = $user->id;
  }

  $ip_address = \Request::ip();
  $session_id = session()->getId();

  if ($loggedInUserId != 0) {   
    $bag_obj = TempBagModel::where('user_id',$loggedInUserId)->first();

  }
  else
  {
     $bag_obj = TempBagModel::where('user_session_id',$session_id)->where('user_id',0)->first();
  }

    if($bag_obj)
    {
      $bag_arr = $bag_obj->toArray();
          
      $product_data_arr = json_decode($bag_arr['product_data'],true);
      $product_data_arr = isset($product_data_arr['sku'])?$product_data_arr['sku']:"0";

      $product_count = count($product_data_arr);
    }  

  return $product_count;  
}


//get unread messages for leads
function get_lead_unread_messages_count($lead_id = 0, $panel = '')
{
    $loggedInUserId = 0;

    $user = \Sentinel::check();

    if($user)
    {
      $loggedInUserId = $user->id;
    }

    $unread_msg_count = LeadConversationModel::where('lead_id',$lead_id)
                                              ->where('sender_id','!=',$loggedInUserId);

    switch ($panel) {

      case 'admin':
        $unread_msg_count->where('is_admin_viewed',0);         
        break;

      case 'retailer':
        $unread_msg_count->where('is_retailer_viewed',0);         
        break;

      case 'representative':

        $unread_msg_count->where('is_representative_viewed',0);         
        break;

      case 'maker':
        $unread_msg_count->where('is_maker_viewed',0);         
        break;      
      
      default:
        
      break;
    }

    $unread_msg_count = $unread_msg_count->count();

    return $unread_msg_count;
}

//get unread messages for quotes
function get_quote_unread_messages_count($quote_id = 0, $panel = '')
{
    $loggedInUserId = 0;

    $user = \Sentinel::check();

    if($user)
    {
      $loggedInUserId = $user->id;
    }
    $unread_msg_count = QuotesConversationModel::where('quote_id',$quote_id)
                                                ->where('sender_id','!=',$loggedInUserId);;

    switch ($panel) {

      case 'admin':
        $unread_msg_count->where('is_admin_viewed',0);         
        break;

      case 'retailer':
        $unread_msg_count->where('is_retailer_viewed',0);         
        break;

      case 'representative':
        $unread_msg_count->where('is_representative_viewed',0);         
        break;

      case 'maker':
      $unread_msg_count->where('is_maker_viewed',0);         
      break; 

      case 'customer':
      $unread_msg_count->where('is_customer_viewed',0);         
      break;      
      
      default:
        
      break;
    }

    $unread_msg_count = $unread_msg_count->count();

    return $unread_msg_count;
}

//get quotes products
function get_quote_products($quote_id = 0)
{
  $product_arr = RetailerQuotesProductModel::where('retailer_quotes_id',$quote_id)
                                            ->with(['product_details'])
                                            ->get()->toArray();


  return $product_arr;
}

//get quotes products
function get_customer_quote_products($quote_id = 0)
{
  $product_arr = [];

  if($quote_id != 0)
  {
    $product_arr = CustomerQuotesProductModel::where('customer_quotes_id',$quote_id)
                                            ->with(['product_details'])
                                            ->get()->toArray();
  }

  return $product_arr;
}


function get_lead_products($lead_id=0,$order_no = false)
{ 
  $product_arr = [];
  if($lead_id != '' && $order_no !='')
  {
    $product_arr = RepresentativeProductLeadsModel::where('order_no',$order_no)
                                              ->where('representative_leads_id',$lead_id)
                                            ->with(['product_details'])
                                            ->get()
                                            ->toArray();
  }                                            

  return $product_arr; 
}

//this function for when order is save in this case order will merge and all product will show in product details popup
function get_rep_sales_leads_products($order_no)
{
    $product_arr = RepresentativeProductLeadsModel::where('order_no',$order_no)
                                                  ->with(['product_details'])
                                                  ->get()
                                                  ->toArray();
                                            

    return $product_arr; 
}

/*need to switch*/
function get_maker_total_amount($maker_id,$order_no = false)
{
  $amount = RepresentativeProductLeadsModel::where('order_no',$order_no)
                                              ->where('maker_id',$maker_id)
                                              ->sum('wholesale_price');
                                              
  $data['total_wholesale_price'] = num_format($amount);

  $update_price = RepresentativeLeadsModel::where('order_no',$order_no)
                                            ->where('maker_id',$maker_id)
                                            ->update($data);

/*  $response['amount'] = $amount;
  $response['update_price'] = $update_price;
return $response;*/
}


//check is log in user online or not if online then return true
function check_is_user_online($user_id = 0)
{
  // $current_date_time = date('d-m-y H:i:s');
  $current_date_time = date('Y-m-d H:i:s');
  
  $user_active_obj = UserLastActiveModel::where('user_id',$user_id)->first();

  if($user_active_obj)
  {
    $user_active_arr  = $user_active_obj->toArray();
    $last_active_time = date('Y-m-d H:i:s', strtotime($user_active_arr['last_active_time']));
  }
  else
  {
    return false;
  }

   $date_diff =  date_diff(date_create($current_date_time), date_create($last_active_time));
   
   if($date_diff->y == 0 && 
      $date_diff->m == 0 && 
      $date_diff->d == 0 && 
      $date_diff->h == 0 && 
      $date_diff->i == 0 && 
      $date_diff->s <= 10)
   {  
    return true;
   }
   else
   {
      return false;
   }
}

function get_product_sku_count($product_id = null)
{
    $status = "-";
    if($product_id)
    {
        $arr_product = [];  
        $obj_product = ProductDetailsModel::where('product_id',$product_id)->groupBy('sku')->where('is_deleted',0)->get();
        if($obj_product)
        {
            $arr_product = $obj_product->toArray();
            if(count($arr_product)>1)
            {
               $status = 'Multiple';
            }
            else
            {
              $status = isset($arr_product[0]['sku'])?$arr_product[0]['sku']:"-"; 
            }
          
        }
        else
        {
            $status = "-";
        }
    }

    return $status;
}

function get_maker_representative($maker_id,$table_data=null)
{
  $arr_data = [];
  $obj_data = RepresentativeMakersModel::where('maker_id',$maker_id)
                ->with(['representative_details'=>function($query)
                {
                    $query->select('id','first_name','last_name');
                }])
                ->get();

  if($table_data!="true")
  {
    if($obj_data)
    {
      $arr_data = $obj_data->toArray();
    }
    
    return $arr_data;
  }
  else
  {
    return $obj_data;
  }
}

function get_maker_shop_setting($maker_id=null)
{
    $arr_data = [];
    if($maker_id!=null)
    {
      $obj_data = ShopSettings::where('maker_id',$maker_id)->first();
      if($obj_data)
      {
          $arr_data = $obj_data->toArray();
      }
    }

    return $arr_data;
}

function num_format($number)
{
  $num = '';
  if($number || $number == '0')
  {
    $num = number_format((float)$number, 2, '.', '');
  }

  return $num ;
}
function card_number_masking($number, $maskingCharacter = 'X') 
{
    return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 14) . substr($number, -4);
}

function get_admin_stripe_key()
{

 /* $secret_key = "";
  $stripe_secret_obj = GeneralSettingModel::where('data_id','STRIPE_SECRET_KEY')->where('type','admin')->first();
   return $secret_key = isset($stripe_secret_obj->data_value)?$stripe_secret_obj->data_value:'';*/

  $secretKey = "";

  $userId = get_admin_id();

  $stripeKeyData = UserStripeAccountDetailsModel::where('user_id',$userId)
                                             ->where('is_active','1')
                                             ->first();

  if(isset($stripeKeyData) && $stripeKeyData != null)
  {
    $secretKey = $stripeKeyData->secret_key;
  }
  return $secretKey;

}

function get_transaction_status($status)
{
    $transaction_status = "";
     switch ($status) 
     {
      case '0':
        $transaction_status = "Processing";
        break;

      case '1':
         $transaction_status = "Authorised";  
        break;

      case '2':
         $transaction_status = "Success";    
        break;

      case '3':
        $transaction_status = "Failed";         
      break;      
      
      default:
        
      break;
    }

    return $transaction_status;

}

function get_order_status($status)
{
    $order_status = "";
    switch ($status) 
    {
      case '0':
        $order_status = "Pending";
        break;

      case '1':
         $order_status = "Shipped";  
        break;

      case '2':
         $order_status = "Failed";  
        break;
      
      default:
        
      break;
    }

    return $order_status;
}

function get_order_cancel_status($status)
{
    $order_status = "";
    switch ($status) 
    {
      case '0':
        $order_status = "Not Cancelled";
        break;

      case '1':
         $order_status = "Pending";  
        break;

      case '2':
         $order_status = "Cancelled";  
        break;
      
      default:
        
      break;
    }

    return $order_status; 
}

function get_payment_status($status)
{
    $payment_status = "";

    switch ($status) 
    {
      case '1':
        $payment_status = "Pending";
        break;

      case '2':
         $payment_status = "Paid";  
        break;

      case '3':
         $payment_status = "Failed";  
        break;
      
      default:
        
        
      break;
    }

    return $payment_status;
}

function get_admin_id()
{
  $admin_role = Sentinel::findRoleBySlug('admin');        
  $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();
  $admin_id   = 0;

  if($admin_obj)
  {
    $admin_id = $admin_obj->user_id;            
  }

  return $admin_id;
}


function get_country($country_id=false)
{
    $country_data = [];
    $country_name = "NA";
    $get_country_details = CountryModel::where('id',$country_id)->first();

    // dd($get_country_details);

    if(isset($get_country_details))
    {
       $country_data = $get_country_details->toArray(); 
       $country_name = $country_data['name'];          
    }

    return $country_name;
}

function generateAlphabetArray() 
{
    $alphabet = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,W,X,Y,Z";

   return explode(',', $alphabet); //turn the string into a array
}

function get_notification_count($role)
{
  $count = 0;

  $check_login = \Sentinel::check();

  $user_id = false;

  if($check_login){
    $user_id = \Sentinel::getUser();
  }

  if($role && $user_id)
  {
    $count = NotificationsModel::where('type',$role)
                                ->where('is_read','0')
                                ->where('to_user_id',$user_id->id)
                                ->count();                              
  }

  return $count;
}

function get_contact_no($contact_no="")
{
  if($contact_no!="" && strlen($contact_no) >= 10)
  {
    return substr($contact_no,0,3).'-'.substr($contact_no,3,3).'-'.substr($contact_no,6,strlen($contact_no));
  }
  else
  { 
     return $contact_no;
  }  
}

function check_product_quantity($sku_no="")
{
  $product_quantity = 0;

  if($sku_no!="")
  {
      $product_quantity = ProductInventoryModel::where('sku_no',$sku_no)->select('quantity')->where('is_deleted',0)->first();

      if($product_quantity)
      {
        $product_quantity = $product_quantity->toArray();
      }
      
      return $product_quantity['quantity'];
  }  

  return $product_quantity;
}


  function getGrandTotal($qty,$unitPrice,$shippingCharges,$shippingDiscount,$productDiscount)
  {
    $subTotal = (float)$qty * (float)$unitPrice;

    $GrandTotal = $subTotal - (float)$shippingCharges - (float)$shippingDiscount  - (float)$productDiscount;

    return $GrandTotal;
  }
 

  function get_maker_payment_term($makerId)
  {
    $userPaymentStatus = MakerModel::where('user_id',$makerId)
                                    ->pluck('is_direct_payment')
                                    ->first();

    return $userPaymentStatus;
  }


  function get_b2c_privacy_settings_detail()
  {
      $arr_data = [];
      $obj_data =  B2CPrivacySettingsModel::first();

      if($obj_data){
        $arr_data = $obj_data->toArray();
      }

      return $arr_data;
  }

  function get_invoice_logo($site_base_img,$site_name)
  {
      $site_img = false;
      $site_image_base_path = base_path('storage/app/'.$site_base_img);    
      $site_default_image   = url('http://via.placeholder.com/160x53.png?text='.$site_name);
      $site_img = image_resize($site_image_base_path,160,53,$site_default_image);

      $response = [
                    'site_default_image'=>$site_default_image,
                    'site_img'          =>$site_img
                  ];
      return $response;
  }


  function is_promocode_freeshipping($promoCode = false)
  {
    $is_freeshipping = false;

    if($promoCode)
    {
      $promo_code_details = PromoCodeModel::where('promo_code_name',$promoCode)->first();

      if($promo_code_details)
      {
        $promo_code_id = isset($promo_code_details->id)?$promo_code_details->id:0;

        $promotion_details = PromotionsModel::where('promo_code',$promo_code_id)->first();

        if($promotion_details)
        {
         $promotion_id =  $promotion_details->id;
        }
        //get all promotion type of that promotion
        $promotion_id = isset($promotion_id)?$promotion_id:0;
        
        $promotion_offers_arr = PromotionsOffersModel::with(['get_prmotion_type'])
                                          ->where('promotion_id',$promotion_id)
                                          ->get()
                                          ->toArray();

        foreach($promotion_offers_arr as $promoCode)
        {
          if($promoCode['get_prmotion_type']['promotion_type_name'] == 'Free Shipping')
          {
            $is_freeshipping = true;
          }
        }
      }
    }

    return $is_freeshipping;
  }

function get_maker_all_details($maker_id=false)
{
  $arr_data = MakerModel::where('user_id',$maker_id)->first();
  
  return $arr_data;
}


function get_menu_detail()
{
  $arr_data = [];
  $obj_data =  MenuSettingModel::select(['menu_slug'])->where('menu_status',1)->get();

  if($obj_data){
    $arr_data = $obj_data->toArray();
  }

  return $arr_data;
}

function check_is_single_vendor(){  
  $obj_active_vendor =   UserModel::select(DB::raw('users'.'.id as id'))
                               ->join('makers','makers'.'.user_id','=','users'.'.id')
                               ->where('users'.'.status',1)
                               ->where('users'.'.is_approved',1)                                 
                               ->get()
                               ->toArray();  
  
  return $obj_active_vendor;
}


    /*get stripe account details using stripe_key_id (get data from table 'stripe_account_details')*/
function get_stripe_account_details($stipe_key_id)
{
  // $stipe_key_id = 1;
  $arr_stripe_account_details = [];
  if(isset($stipe_key_id) && !empty($stipe_key_id))
    {
        $obj_stripe_account_details = UserStripeAccountDetailsModel::where('id',$stipe_key_id)
                                        ->first();


        if($obj_stripe_account_details)
        {
            $arr_stripe_account_details = $obj_stripe_account_details->toArray();
        }
    }
    return $arr_stripe_account_details;
}

    /*end*/


    /*function for get representative commission*/
    function get_representative_commission($user_id=false)
  {
      $representative_commission = 0;

      if($user_id)
      {
        $representative_commission =  UserModel::where('id',$user_id)->select(['commission'])->first();

        if(isset($representative_commission))
        {
           $representative_commission =  $representative_commission->toArray();
        }
        
        $representative_commission = isset($representative_commission['commission'])?$representative_commission['commission']:0;  

        if($representative_commission == 0)  
        {
          /*Get site setting data from helper*/
            $representative_commission = get_site_settings(['site_name','representative_commission']);
          
            $representative_commission = isset($representative_commission['representative_commission'])?$representative_commission['representative_commission']:0;
        }
      }
      else
      {
        /*Get site setting data from helper*/
          $representative_commission = get_site_settings(['site_name','representative_commission']);
      
        $representative_commission = isset($representative_commission['representative_commission'])?$representative_commission['representative_commission']:0;
      }

      return $representative_commission;
  }

  /*function for get sales manager commission*/
  function get_sales_manager_commission($user_id=false)
  {
    $salesmanager_commision = 0;

    if($user_id)
    {
      $obj_salesmanager_commision =  UserModel::where('id',$user_id)->first(['commission']);

      if($obj_salesmanager_commision)
      {
        $salesmanager_commision = $obj_salesmanager_commision->toArray();
      }
      
      $salesmanager_commision = isset($salesmanager_commision['commission'])?$salesmanager_commision['commission']:0;  

     if($salesmanager_commision == 0)  
      {
        /*Get site setting data from helper*/
          $salesmanager_commision = get_site_settings(['site_name','salesmanager_commission']);
    
          $salesmanager_commision = isset($salesmanager_commision['salesmanager_commission'])?$salesmanager_commision['salesmanager_commission']:0;
      }  
    }
    else
    {
      /*Get site setting data from helper*/
      $salesmanager_commision = get_site_settings(['site_name','salesmanager_commission']);
    
      $salesmanager_commision = isset($salesmanager_commision['salesmanager_commission'])?$salesmanager_commision['salesmanager_commission']:0;
    }

    return $salesmanager_commision;
  }



  //this function will check inventory is greter or  not than moq
  function check_moq_inventory($product_id)
  {    
        $product_moq_arr = $product_inventory_arr = $valid_sku = [];

        $product_moq_arr = ProductDetailsModel::
                                where('product_id',$product_id)
                                ->select('product_min_qty','sku')
                                ->get()
                                ->toArray();

 

        if(isset($product_moq_arr) && count($product_moq_arr)>0)
        {
            
            foreach($product_moq_arr as $key => $moq_arr)
            {   

                $product_inventory_obj = ProductInventoryModel::
                                         where('product_id',$product_id)
                                        ->where('sku_no',$moq_arr['sku'])
                                        ->select('quantity','sku_no')
                                        ->first();

                if(isset($product_inventory_obj))
                {
                  $product_inventory_arr = $product_inventory_obj->toArray();
                }


                if(isset($product_inventory_arr['quantity']) && $product_inventory_arr['quantity'] >= $moq_arr['product_min_qty'])
                {
                    $valid_sku[] = $moq_arr['product_min_qty'];

                }
              
            }

        }            

        if(count($valid_sku) > 0)
        {
            return true;
        }
        else
        {
           return false;
        }
  }

  /*Get Influencer's Order Status Count*/
  function get_influencer_order_count($influencer_code){

    $completed_order_count = 0;
    $cancelled_order_count = 0;
    $pending_order_count = 0;

    $completed_order_count = RetailerQuotesModel::where('influencer_code',$influencer_code)
                                                    ->where('order_cancel_status',0)
                                                    ->where('ship_status',1)
                                                    ->where('maker_confirmation',1)
                                                    ->count();
  
    $cancelled_order_count = RetailerQuotesModel::where('influencer_code',$influencer_code)->where('order_cancel_status',2)                                              ->count();
    
    $pending_order_count = RetailerQuotesModel::where('influencer_code',$influencer_code)
                                                    ->where('order_cancel_status','!=',2)
                                                    ->where('maker_confirmation','!=',1)
                                                   // ->orWhereNull('maker_confirmation')
                                                    ->where('ship_status',0)
                                                    // ->where('maker_confirmation',0) 
                                                    // ->orWhere('maker_confirmation',null)
                                                    ->count();
                                          // dd($influencer_code);                                                 
    $data['complete_order'] = isset($completed_order_count)?$completed_order_count : 0 ;
    $data['pending_order'] = isset($pending_order_count)?$pending_order_count : 0 ;
    $data['cancelled_order'] = isset($cancelled_order_count)?$cancelled_order_count : 0 ;
  
    return $data;
  }

  function get_size_details($product_id,$sku_no)
  {
    $product_size_arr = [];

    $product_size_arr = ProductSizesModel::
                        select('id','size_id','size_inventory')
                        ->where('product_id',$product_id)
                        ->where('sku_no',$sku_no)
                        ->get()
                        ->toArray();

     return $product_size_arr;
  }

  function get_size_for_category($cat_id)
  {
    $size_arr = [];
    $size_arr = SizeModel::where('category_id',$cat_id)
                ->get()
                ->toArray();

    return $size_arr;
  }

  function get_size_from_id($size_id)
  {
    $size = "";
    $size_arr = [];
    $size_arr = SizeModel::where('id',$size_id)
                ->first();
    if($size_arr)
    {
      $size = $size_arr['size'];
    }

    return $size;
  }

  
  
