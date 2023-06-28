<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // <<< this line was added
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],        
        'admin'=>[
            \App\Http\Middleware\Admin\AuthenticateMiddleware::class,
            \App\Http\Middleware\Admin\GeneralMiddleware::class,
        ],
        
        'maker'=>[
            \App\Http\Middleware\Maker\AuthenticateMiddleware::class,
            \App\Http\Middleware\Maker\MakerMiddleware::class
        ],
        'retailer'=>[
            \App\Http\Middleware\Retailer\AuthenticateMiddleware::class,
            \App\Http\Middleware\Retailer\RetailerMiddleware::class
        ],
        'representative'=>[
            \App\Http\Middleware\Representative\AuthenticateMiddleware::class,
            \App\Http\Middleware\Representative\RepresentativeMiddleware::class
        ],
        'front'=>[
            // \App\Http\Middleware\Front\AuthenticationMiddleware::class,
           \App\Http\Middleware\Front\FrontMiddleware::class
        ],
        'sales_manager'=>[
            \App\Http\Middleware\Sales_Manager\AuthenticateMiddleware::class,
            \App\Http\Middleware\Sales_Manager\SalesManagerMiddleware::class
        ],
        'customer'=>[
            \App\Http\Middleware\Customer\AuthenticateMiddleware::class,
            \App\Http\Middleware\Customer\CustomerMiddleware::class
        ],
        'influencer'=>[
            \App\Http\Middleware\Influencer\AuthenticateMiddleware::class,
            \App\Http\Middleware\Influencer\InfluencerMiddleware::class
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'api.auth' => \App\Http\Middleware\Api\Representative\AuthenticateMiddleware::class,
        'rejoiz_auth' => \App\Http\Middleware\Api\Rejoiz\Retailer\RejoizAuthenticateMiddleware::class

    ];

}
