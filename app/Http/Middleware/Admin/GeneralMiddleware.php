<?php

namespace App\Http\Middleware\Admin;

use App\Models\SiteSettingModel;
use Closure;
use Session;

class GeneralMiddleware
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
        $site_setting_arr = [];
        Session::put('locale','en');
        view()->share('admin_panel_slug',config('app.project.admin_panel_slug'));

        $site_setting_obj = SiteSettingModel::first();
        
        if($site_setting_obj)
        {
            $site_setting_arr = $site_setting_obj->toArray();            
        }

        $request->attributes->add(['site_setting_arr' => $site_setting_arr]);
        view()->share('site_setting_arr',$site_setting_arr);
        
        return $next($request);
    }
}
