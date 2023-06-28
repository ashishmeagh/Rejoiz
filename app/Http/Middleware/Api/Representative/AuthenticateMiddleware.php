<?php

namespace App\Http\Middleware\Api\Representative;

use App\Common\Services\Api\Common\JWTService;


use Closure;

class AuthenticateMiddleware
{

     public function __construct ( JWTService $JWTService ) {
        $this->JWTService          = $JWTService;
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
        $token = $request->header('Authorization');

        if ($token == null || $token == "") {

            $response = [

                'status'    => 'failure',
                'message'   => 'token not found.',
                'data'      => ''
            ];
            
            return response()->json($response);
        }

        $user_response = $this->JWTService->getUser($token);

        if ($user_response['status'] == "failure" ) {

            $response = [

                'status'    => 'failure',
                'message'   => 'Invalid token.',
                'data'      => ''
            ];

            return response()->json($response);
        }

        $request->request->add(['auth_user'=> $user_response['data']]);     // Object send through Middleware

        return $next($request);
    }
}
