<?php

namespace Nicolaskuster\ApiDoc\Providers;

use Illuminate\Support\ServiceProvider;
use Nicolaskuster\ApiDoc\Console\Commands\ApiDoc;

class ApiDocServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ApiDoc::class
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $config = __DIR__.'/../../config/apiDoc.php';

        $this->publishes([
            $config => config_path('apiDoc.php'),
        ]);

        $this->mergeConfigFrom(
            $config, 'apiDoc'
        );
    }
}
