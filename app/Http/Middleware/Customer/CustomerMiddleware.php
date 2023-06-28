<?php

namespace App\Http\Middleware\Customer;
use App\Models\UserModel;
use App\Models\SiteSettingModel;
use App\Models\CategoryModel;
use App\Models\RepAreaModel;
use App\Models\CategoryTranslationModel;
use App\Models\CategoryDivisionModel;

use Closure;

class CustomerMiddleware
{
    public function __construct(
                                  CategoryModel $CategoryModel,
                                  CategoryTranslationModel $CategoryTranslationModel,
                                  RepAreaModel $RepAreaModel,
                                  CategoryDivisionModel $CategoryDivisionModel
                               )
    {

      $this->arr_view_data                   =   [];
      $this->CategoryModel                   =   $CategoryModel;
      $this->CategoryTranslationModel        =   $CategoryTranslationModel;
      $this->RepAreaModel                    =   $RepAreaModel; 
      $this->CategoryDivisionModel           =   $CategoryDivisionModel;      
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        $retailer_arr = $area_category_arr = [];
        $loggedInUserDetails = \Sentinel::check();

        /* get retailer details from retailer */

        $retailer_details = UserModel::with(['retailer_details'])->where('id',$loggedInUserDetails->id)->first();

        if(isset($retailer_details))
        {
            $retailer_arr = $retailer_details->toArray(); 
        }

       
        /* site setting data */
        $obj_site_setting = SiteSettingModel::first(); 

        $site_setting_arr = [];
        
        if($obj_site_setting)
        {
            $site_setting_arr = $obj_site_setting->toArray();
        }

        
        /*category data */
        $arr_category = [];
        $arr_category = CategoryModel::with(['subcategory_details'])->where('is_active',1)->get()->sortBy(function($CategoryModel){return $CategoryModel->category_name;})->toArray();

        $arr_area = [];
        $arr_area =  RepAreaModel::where('status',1)->get()->toArray();

        
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
       
        if($loggedInUserDetails)
        {   

            view()->share('loggedInUserDetails',$loggedInUserDetails->toArray());
            view()->share('store_name',$retailer_arr['retailer_details']['store_name']);
            view()->share('arr_category',$arr_category);
            view()->share('site_setting_arr',$site_setting_arr);
            view()->share('arr_area',$arr_area);
            view()->share('area_category_arr',$area_category_arr);

        }

       
        return $next($request);
    }
}
