<?php
namespace Louis\LaravelLinepay;

use Illuminate\Support\ServiceProvider;

class LinepayServiceProvider extends ServiceProvider
{
    /**
     * Register services
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Linepay::class, function ($app) {
            return new Linepay($app['config']['linepay']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('linepay.php'),
        ], 'linepay');
    }
}
