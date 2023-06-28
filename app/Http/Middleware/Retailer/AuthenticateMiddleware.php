<?php

namespace App\Http\Middleware\Retailer;

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

        

            // if($obj_last_activity_time)
            // {
            //     $last_activity_time = strtotime($obj_last_activity_time->last_active_time);

            //     $current_date_time = date('Y-m-d H:i:s');

            //     $endTime = strtotime("+20 minutes",$last_activity_time);



            //     if(strtotime($current_date_time) > $endTime)
            //     {
            //         return redirect('/logout');
            //     }
            // }

            if($user)
            {


                //check user active or not

                $user = Sentinel::check();

                if($user)
                {
                    $is_approved = UserModel::where('id',$user->id)->pluck('is_approved')->first();
   
                    if($is_approved == 0)
                    {
                        Session::flush();
                        Sentinel::logout();
                        return redirect('/login');
                    }  

                }
          

                $user_id = $user->id;

                $obj_last_activity_time = UserLastActiveModel::where('user_id',$user_id)->first();

                if($user->inRole('retailer'))
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
