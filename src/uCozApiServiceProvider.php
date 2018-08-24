<?php

namespace EnnioSousa\uCozApi;

use Illuminate\Support\ServiceProvider;

class uCozApiServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ucoz-api.php' => config_path('ucoz-api.php')], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . '*.php') as $file) {
            require_once $file;
        }
        //require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'uAPI.php';
    }

}
