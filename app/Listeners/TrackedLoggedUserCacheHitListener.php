<?php

namespace App\Listeners;

use App\Http\Middleware\TrackLoggedUser;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrackedLoggedUserCacheHitListener
{
    public function __construct()
    {
    }

    public function handle(CacheHit $event)
    {
        if (isset(auth()->user()->id)) {

            $cache_key = TrackLoggedUser::TRACKED_LOGGED_USER_CACHE_KEY_PREFIX . auth()->user()->id;
            if (preg_match('`.*' . $cache_key . '`', $event->key, $matches) > 0) {

                Log::debug('LogCacheHitListener:handle - update TTL', [$event->key]);
                Cache::store(config('cache.default'))->put($event->key, $event->value, TrackLoggedUser::TTL);
            }
        }
    }
}
