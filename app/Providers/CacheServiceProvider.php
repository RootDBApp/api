<?php

namespace App\Providers;

use App\Services\CacheService;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            'App\Services\CacheService',
            function () {
                return new CacheService;
            }
        );
    }

    public function boot()
    {
    }
}
