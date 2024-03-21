<?php

namespace App\Providers;

use App\Services\MySQLConnectorService;
use Illuminate\Support\ServiceProvider;
use App\Services\PostgreSQLConnectorService;

class ConnectorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            'App\Services\MySQLConnectorService',
            function () {
                return new MySQLConnectorService;
            }
        );

        $this->app->singleton(
            'App\Services\PostgreSQLConnectorService',
            function () {
                return new PostgreSQLConnectorService;
            }
        );
    }


    public function boot()
    {
        //
    }
}
