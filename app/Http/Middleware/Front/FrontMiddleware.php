<?php

namespace App\Http\Middleware\Front;
use Illuminate\Http\Response;

use App\Models\StaticPageModel;
use App\Models\SiteSettingModel;
use App\Models\CountryModel;
use App\Models\CategoryModel; 
use App\Models\ProductsModel;
use App\Models\RepAreaModel;
use App\Models\CategoryTranslationModel;
use App\Models\ThirdSubCategoryModel;
use App\Models\CategoryDivisionModel;
use App\Models\UserModel;

use App\Models\SubCategoryModel;
use App\Models\SubCategoryTranslationModel;


use App\Models\ThirdSubCategoryTranslationModel;

use App\Models\FourthSubCategoryModel;
use App\Models\FourthSubCategoryTranslationModel;



use Closure;
use Request;
use Sentinel;
use Session;
use DB;

class FrontMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function __construct(

                               CategoryModel $CategoryModel,
                               CategoryTranslationModel $CategoryTranslationModel,
                               ThirdSubCategoryModel $ThirdSubCategoryModel,
                               RepAreaModel $RepAreaModel,
                               CategoryDivisionModel $CategoryDivisionModel,
                               UserModel $UserModel,
                               SubCategoryModel $SubCategoryModel,
                               SubCategoryTranslationModel $SubCategoryTranslationModel,
                              
                               ThirdSubCategoryTranslationModel $ThirdSubCategoryTranslationModel,
                               FourthSubCategoryModel $FourthSubCategoryModel,
                               FourthSubCategoryTranslationModel $FourthSubCategoryTranslationModel
                               )
    {
      $this->locale                          =   \App::getLocale();   
      $this->arr_view_data                   =   [];    
      $this->CategoryModel                   =   $CategoryModel;
      $this->CategoryTranslationModel        =   $CategoryTranslationModel;
      $this->ThirdSubCategoryModel           =   $ThirdSubCategoryModel;
      $this->RepAreaModel                    =   $RepAreaModel;  
      $this->CategoryDivisionModel           =   $CategoryDivisionModel; 
      $this->UserModel                       =   $UserModel;

      $this->SubCategoryModel                   =   $SubCategoryModel;
      $this->SubCategoryTranslationModel        =   $SubCategoryTranslationModel;

      $this->ThirdSubCategoryModel                   =   $ThirdSubCategoryModel;
      $this->ThirdSubCategoryTranslationModel        =   $ThirdSubCategoryTranslationModel;

       $this->FourthSubCategoryModel                   =   $FourthSubCategoryModel;
      $this->FourthSubCategoryTranslationModel        =   $FourthSubCategoryTranslationModel;

      $this->locale = \App::getLocale();   
    }

    public function handle($request, Closure $next)
    {     
        $loggedInUserDetails =  $area_category_arr = [];

        //check user active or not

        $user = Sentinel::check();

        if($user)
        {
            $is_approved = UserModel::where('id',$user->id)->pluck('is_approved')->first();

            if($is_approved == 0)
            {
                Sentinel::logout();
                Session::flush();
                return redirect('/login');
            } 
        }
       

        $arr_active_user  = Sentinel::check();

        
        if(isset($arr_active_user) && $arr_active_user!=false)
        {
            view()->share('arr_active_user',$arr_active_user);
        }   

        $cms_pages_arr = StaticPageModel::where('is_active','1')->get()->toArray(); 

        $obj_site_setting = SiteSettingModel::first(); 

        $site_setting_arr = [];
        
        if($obj_site_setting)
        {
            $site_setting_arr = $obj_site_setting->toArray();
        }

        $site_status = isset($site_setting_arr['site_status'])?$site_setting_arr['site_status']:'';

        if($site_status == '0')
        {
            return response(view('errors.503'));
        }

        //Site logo
        $site_logo = "";
        $site_base_img = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : '';
        //$site_image_path = url('/storage/app/'.$site_base_img);
        $site_image_path = url('/').config('app.project.img_path.meta_logo_image');

        $arr_country = [];
        $arr_country = CountryModel::where('is_active',1)->get()->toArray();
        

        $arr_category = [];

        $arr_category = CategoryModel::with(['subcategory_details'=>function ($query){
                                            $query->with(['second_subcategory_details'=>function($sub_query){
                                              $sub_query->with(['third_subcategory_details'=>function($sec_sub_query){
                                                $sec_sub_query->where('is_active',1);
                                              }]);
                                              $sub_query->where('is_active',1);
                                            }]);
                                            $query->where('is_active',1);
                                      }])
                                      ->where('is_active',1)
                                      ->orderBy('priority','ASC')
                                      /* ->sortBy(function($CategoryModel)
                                                {return $CategoryModel->category_name;}
                                              ) */
                                              ->get()  
                                      ->toArray();



         $arr_subcategory_data = SubCategoryModel::with(['second_subcategory_details'=>function ($query){
          $query->where('is_active',1);
        }])->where('is_active',1)->get()->sortBy(function($SubCategoryModel){return $SubCategoryModel->subcategory_name;});


        $thirdsub_categories_arr = ThirdSubCategoryModel::where('is_active',1)
                                            ->whereTranslation('locale',$this->locale)
                                            ->get()->toArray();

        $fourthsub_categories_arr = FourthSubCategoryModel::where('is_active',1)
                                            ->whereTranslation('locale',$this->locale)
                                            ->get()->toArray();


        if(isset($arr_category) && count($arr_category)>0){
          foreach ($arr_category as $k => $category) {
            if(isset($category['subcategory_details']) && count($category['subcategory_details'])>0){
              $temp_arr = [];
               /* Sort by Alpha */ 
                usort($category['subcategory_details'], function($sort_base, $sort_compare) {
                    return $sort_base['subcategory_slug'] <=> $sort_compare['subcategory_slug'];
                });  
               
                $arr_category[$k]['subcategory_details'] = $category['subcategory_details'];
               
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

        $third_sub_categories_arr = ThirdSubCategoryModel::where('is_active',1)
                                                ->whereTranslation('locale',$this->locale)
                                                ->get()
                                                ->toArray();
       
        $arr_product = [];
        $arr_product = ProductsModel::where('is_active',1)
                                    ->take(10)
                                    ->with(['userDetails'=>function($query)
                                    {
                                        $query->select('id','tax_id','email','first_name','last_name','country_id','address','post_code','profile_image');
                                    },'categoryDetails'])
                                    ->get()->toArray();


        
        $arr_area = [];
        $arr_area =  RepAreaModel::where('status',1)->orderBy("area_name")->get()->toArray();

      
       $trans_category_details_arr = $this->CategoryTranslationModel->get()->toArray();

        if(isset($arr_area) && count($arr_area)>0)
        { 
            foreach ($arr_area as $key => $area)
            {  
                $category_id_arr = json_decode($area['category_id']);
               
                if(isset($category_id_arr) && count($category_id_arr)>0)
                { 
                    $cat_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->get()->toArray();
                    $area_category_arr[$key]['category_arr']  = $cat_arr; 
                }

                $area_category_arr[$key]['area_id']    = $area['id'];
                $area_category_arr[$key]['area_name']  = $area['area_name'];
            }
        }


        $obj_data          = Sentinel::getUser();
        $user_id           = 0;
        $is_login          = '';

        if($obj_data)
        {
           $user_id  = $obj_data->id;
           $is_login = $obj_data->is_login;
        }
         //accesss array in controller
         $request->attributes->add(['site_setting_arr' => $site_setting_arr]);

          $segement = Request::segment(1);
          /*if promotion is set and user not on mybag or checkout page then forget the promotion session*/

          if($segement == 'my_bag' || $segement=='search' || $segement=='checkout' || $segement=='save_bag' || $segement=='buy' || $segement=='net_payment' || $segement == 'get_card' || $segement == 'daily_popup')
          {

            Session::get('promotion_data');
      
          }
          else
          {

            Session::forget('promotion_data');
            Session::forget('promo_shipping_charges');
            Session::forget('promotion_discount_amt');
            Session::forget('total_order_amout');

            Session::forget('representative_order_id');
            Session::forget('order_id');
            Session::forget('payment_type');
          }
        
        /*-------------------------------------------------------------------------*/    


      /* dd($thirdsub_categories_arr);*/

       $unique_sub_id = array();
         if(isset($thirdsub_categories_arr))
         {
            foreach($thirdsub_categories_arr as $val)
            {
                    $unique_sub_id[] =  $val['sub_category_id'];
            }
         }
        
        $unique_sub_id_arr = array_unique($unique_sub_id);

        $unique_thirdsub_id = array();
        if(isset($fourthsub_categories_arr))
        {
           foreach($fourthsub_categories_arr as $res)
           {
                   $unique_thirdsub_id[] =  $res['third_sub_category_id'];
           }
        }
       
       $unique_thirdsub_id_arr = array_unique($unique_thirdsub_id);
       
        view()->share('arr_product',$arr_product);
        view()->share('is_login',$is_login);

        view()->share('arr_area',$arr_area);
        view()->share('area_category_arr',$area_category_arr);
        view()->share('arr_country',$arr_country);
        view()->share('arr_category',$arr_category);
        //Session::put('category_arr',$arr_category);
        view()->share('cms_pages_arr',$cms_pages_arr);
        view()->share('site_setting_arr',$site_setting_arr);
        view()->share('site_logo',$site_image_path);
        view()->share('thirdsub_categories_arr',$thirdsub_categories_arr);
        view()->share('fourthsub_categories_arr',$fourthsub_categories_arr);
        view()->share('unique_sub_id',$unique_sub_id_arr);

        view()->share('unique_thirdsub_id',$unique_thirdsub_id_arr);


         

        return $next($request);
    }

    
}
