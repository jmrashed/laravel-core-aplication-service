<?php

namespace Jmrashed\LaravelCoreService;

use Illuminate\Support\ServiceProvider;
use Jmrashed\LaravelCoreService\Console\Commands\MigrateStatusCommand;
use Jmrashed\LaravelCoreService\Middleware\ServiceMiddleware;

class LaravelCoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', ServiceMiddleware::class);
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'service');
        $this->loadViewsFrom(resource_path('/views/vendors/service'), 'service');
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/jmrashed'),
             __DIR__.'/../resources/views' => resource_path('views/vendors/service'),
        ], 'jmrashed');
        $this->commands([
            MigrateStatusCommand::class,
        ]);
    }
}

