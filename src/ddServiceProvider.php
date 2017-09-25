<?php
namespace Woldy\ddsdk;
use Illuminate\Support\ServiceProvider;
class ddServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //$this->loadViewsFrom(__DIR__.'../../views', 'woldycms');
        $this->publishes([
            //__DIR__.'../../views' => base_path('resources/views/woldycms'),
            //__DIR__.'../../config/dd.php' => config_path('dd.php'),
        ]);
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['dd'] = $this->app->share(function ($app) {
            return new dd($app['config']);
        });

        $this->app['isv'] = $this->app->share(function ($app) {
            return new isv($app['config']);
        });
    }
}
