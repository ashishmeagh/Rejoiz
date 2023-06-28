<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Auth0\Login\Contract\Auth0UserRepository as Auth0Contract;
use Auth0\Login\Repository\Auth0UserRepository as UserRepo;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductsModel;
use App\Observers\ProductsObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
  /*  public function boot()
    {
        Schema::defaultStringLength(191);

        \URL::forceScheme(env('APP_HTTP_SCHEMA','https'));
        //ProductsModel::observe(ProductsObserver::class);
    }*/

    public function boot()
    {
        Schema::defaultStringLength(191);

       \URL::forceScheme(env('APP_HTTP_SCHEMA','https'));
        //ProductsModel::observe(ProductsObserver::class);
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
       /* $this->app->bind(
            Auth0Contract::class,
            UserRepo::class
        );*/
       /* $this->app->bind(App\Repository\ProductsRepository::class, function ($app) {
            // This is useful in case we want to turn-off our
            // search cluster or when deploying the search
            // to a live, running application at first.
            if (! config('services.search.enabled')) {
                return new Articles\EloquentRepository();
            }
            return new App\Repository\ProductsRepository(
                $app->make(Client::class)
            );
        });*/
    }
   /* private function bindSearchClient()
    {
        $this->app->bind(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts($app['config']->get('services.search.hosts'))
                ->build();
        });
    }*/
}
