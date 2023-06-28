<?php

namespace App\Http\Middleware\Influencer;

use Closure;
use App\Models\UserModel;
use App\Models\SiteSettingModel;

class InfluencerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function __construct(){

        $this->arr_view_data  =  [];
    }

    public function handle($request, Closure $next)
    {
        $loggedInUserDetails = \Sentinel::check();

        /* Site setting data */
        $obj_site_setting = SiteSettingModel::first(); 

        $site_setting_arr = [];
        
        if($obj_site_setting){
            $site_setting_arr = $obj_site_setting->toArray();
        }

        if($loggedInUserDetails){   
            view()->share('loggedInUserDetails',$loggedInUserDetails->toArray());
            view()->share('site_setting_arr',$site_setting_arr);
        }

        return $next($request);
    }
}
