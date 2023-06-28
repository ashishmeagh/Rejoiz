<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Sentinel;
use App\Models\SiteSettingModel;
use App\Models\UserLastActiveModel;


class AuthenticateMiddleware
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
        $arr_except = array();

        $admin_path = config('app.project.admin_panel_slug');

        $site_setting_arr = [];


        $arr_except[] =  $admin_path;
        $arr_except[] =  $admin_path.'/login';
        $arr_except[] =  $admin_path.'/process_login';
        $arr_except[] =  $admin_path.'/forgot_password';
        $arr_except[] =  $admin_path.'/process_forgot_password';
        $arr_except[] =  $admin_path.'/validate_admin_reset_password_link';
        $arr_except[] =  $admin_path.'/reset_password';
        

        /*-----------------------------------------------------------------
            Code for {enc_id} or {extra_code} in url
        ------------------------------------------------------------------*/
        $request_path = $request->route()->getCompiled()->getStaticPrefix();
        $request_path = substr($request_path,1,strlen($request_path));
        
        /*-----------------------------------------------------------------
                End
        -----------------------------------------------------------------*/        

        if(!in_array($request_path, $arr_except))
        {
            
            $user = Sentinel::check();

            if($user)
            {
                if($user->inRole('admin'))
                {
                    $user_id = $user->id;

                    if($user_id)
                    {

                        $obj_last_activity_time = UserLastActiveModel::where('user_id',$user_id)->first();

                        if($obj_last_activity_time)
                        {
                            $last_activity_time = strtotime($obj_last_activity_time->last_active_time);

                            $current_date_time = date('Y-m-d H:i:s');

                            $endTime = strtotime("+20 minutes",$last_activity_time);

                            if(strtotime($current_date_time) > $endTime)
                            {
                                Sentinel::logout();
                                return redirect('/admin');
                            }
                        }
                    }

                     $site_setting_obj = SiteSettingModel::first();
                    
                    if($site_setting_obj)
                    {
                        $site_setting_arr = $site_setting_obj->toArray();            
                    }

                    $request->attributes->add(['site_setting_arr' => $site_setting_arr]);
                    view()->share('site_setting_arr',$site_setting_arr);
                    return $next($request);    
                }
                else
                {

                    return redirect('/admin');
                }    
            }
            else
            {
                return redirect('/admin');
            }
            
        }
        else
        {
            return $next($request); 
        }
    }
}
