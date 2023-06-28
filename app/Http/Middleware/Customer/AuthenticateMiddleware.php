<?php

namespace App\Http\Middleware\Customer;

use Closure;
use Request;
use Session;
use Sentinel;
use App\Models\UserLastActiveModel;
use App\Models\UserModel;



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
        $arr_except   =  [];
        
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
            $user = \Sentinel::check();

            if($user)
            {

                //check user active or not 

                $is_approved = UserModel::where('id',$user->id)->pluck('is_approved')->first();
   
                if($is_approved == 0)
                {
                    Session::flush();
                    Sentinel::logout();
                    return redirect('/login');
                }  



                $user_id = $user->id;

                $obj_last_activity_time = UserLastActiveModel::where('user_id',$user_id)->first();

                if($user->inRole('customer'))
                {
                    return $next($request); 
                                   
                }
                else
                {    
                    return redirect('/');
                }    
            }
            else
            { 
               return redirect('/login'); 
            }
            
        }
        else
        {
            return $next($request); 
        }
    }
}
