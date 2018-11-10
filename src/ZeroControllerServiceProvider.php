<?php

namespace ZeroController;

use Illuminate\Support\ServiceProvider;

class ZeroControllerServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__ . '/routes.php';
    }
}