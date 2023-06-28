<?php

namespace App\Http\Middleware\Sales_Manager;
use\App\Models\SiteSettingModel;

use Closure;

class SalesManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
   
    public function handle($request, Closure $next)
    {
        $loggedInUserDetails = \Sentinel::check();

        if($loggedInUserDetails)
        {
            view()->share('loggedInUserDetails',$loggedInUserDetails->toArray());
        }


        $site_setting_obj = SiteSettingModel::first();
        
        if($site_setting_obj)
        {
            $site_setting_arr = $site_setting_obj->toArray();            
        }

        
        view()->share('site_setting_arr',$site_setting_arr);
        
        return $next($request);
    }

    
}
