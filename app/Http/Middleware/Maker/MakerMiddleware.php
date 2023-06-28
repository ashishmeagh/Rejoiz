<?php

namespace App\Http\Middleware\Maker;

use App\Models\MakerModel;
use App\Models\SiteSettingModel;
use Closure;

class MakerMiddleware
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
        $maker_arr = [];
        $loggedInUserDetails = \Sentinel::check();

        if($loggedInUserDetails)
        {
            view()->share('loggedInUserDetails',$loggedInUserDetails->toArray());
        }

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

        $request->attributes->add(['site_setting_arr' => $site_setting_arr]);

        /*get makers details by user_id*/

        $maker_details = MakerModel::where('user_id',$loggedInUserDetails->id)->first();

        if(isset($maker_details))
        {
           $maker_arr = $maker_details->toArray(); 
           view()->share('maker_data',$maker_arr);
        }

        view()->share('site_setting_arr',$site_setting_arr);



        return $next($request);
    }
}


