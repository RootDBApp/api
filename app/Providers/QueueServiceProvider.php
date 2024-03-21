<?php

namespace App\Providers;

use App\Services\QueueService;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            'App\Services\QueueService',
            function ()
            {
                return new QueueService();
            }
        );
    }

    public function boot()
    {
        //
    }
}
