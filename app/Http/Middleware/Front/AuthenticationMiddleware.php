<?php

namespace App\Http\Middleware\Front;

use Closure;
use Sentinel;
use App\Models\SiteSettingModel;


class AuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *ss
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    public function __construct(SiteSettingModel $SiteSettingModel)
    {
      $this->SiteSettingModel                =   $SiteSettingModel;
    }
    public function handle($request, Closure $next)
    {
        $arr_except   = array();
        $arr_except[] =  'process_login';
        $arr_except[] =  'process_forgot_password';
        $arr_except[] =  'validate_user_reset_password_link';
        $arr_except[] =  'reset_password';
        $arr_except[] =  'forgot_password';
        $arr_except[] =  'signup';
        $arr_except[] =  'process_signup';
        $arr_except[] =  'activation_complete';
        $arr_except[] =  'process_reset_password';
        $arr_except[] =  'login';
        $arr_except[] =  'logout';
     
        

        $obj_site_setting = SiteSettingModel::first(); 

        $site_setting_arr = [];
        
        if($obj_site_setting)
        {
            $site_setting_arr = $obj_site_setting->toArray();
        }

        /*-----------------------------------------------------------------
            Code for {enc_id} or {extra_code} in url
        ------------------------------------------------------------------*/
        $request_path = $request->route()->getCompiled()->getStaticPrefix();
        $request_path = substr($request_path,1,strlen($request_path));
        
        /*-----------------------------------------------------------------
         
        -----------------------------------------------------------------*/    

        if(!in_array($request_path, $arr_except))
        {
            $user = Sentinel::check();

        
            if($user)
            {
                if($user->inRole('maker') || $user->inRole('representative') || $user->inRole('retailer') || $user->inRole('sales_manager') || $user->inRole('customer'))
                {

                   $request->attributes->add(['site_setting_arr' => $site_setting_arr]);
                    view()->share('site_setting_arr',$site_setting_arr);
                    return $next($request);                       
                }
                else
                {    
                    return redirect('/');
                }    
            }
            else
            { 
               return redirect('/'); 
            }
            
        }
        else
        {

            $request->attributes->add(['site_setting_arr' => $site_setting_arr]);

            view()->share('site_setting_arr',$site_setting_arr);
            return $next($request); 
        } 

    }
}
