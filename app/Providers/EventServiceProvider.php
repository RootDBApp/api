<?php

namespace App\Providers;

use App\Listeners\TrackedLoggedUserCacheHitListener;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CacheHit::class => [
            TrackedLoggedUserCacheHitListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // or, instead of using the $listen array :
        //Event::listen(function (PodcastProcessed $event) {
        //    //
        //});
        // or
        // Event::listen(queueable(function (PodcastProcessed $event) {
        //        //
        //    }));
        // or
        //Event::listen('event.*', function ($eventName, array $data) {
        //    //
        //});
    }
}
