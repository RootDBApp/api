<?php

namespace App\Providers;

use App\Models\ServiceMessage;
use App\Models\ServiceMessageRole;
use App\Policies\ServiceMessagePolicy;
use App\Policies\ServiceMessageRolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ServiceMessage::class     => ServiceMessagePolicy::class,
        ServiceMessageRole::class => ServiceMessageRolePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
