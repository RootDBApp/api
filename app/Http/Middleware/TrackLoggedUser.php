<?php

namespace App\Http\Middleware;

use App\Tools\TrackLoggedUserTools;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackLoggedUser
{
    public const TRACKED_LOGGED_USER_CACHE_KEY_PREFIX = 'logged_user_';
    public const TRACKED_LOGGED_USER_CACHE_TAG = 'logged-user';
    public const TTL = 7200;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse|JsonResponse
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $response = $next($request);

        $uri = $request->route()->uri;

        //Log::info('TrackLoggedUser::handle - $uri', [$uri]);
        // Because we update Cache.
        $no_cache_hit_for_these_routes = [
            'api/user/change-organization-user',
            'api/login',
            'api/logout',
            'api/report/{report}',
            'api/report/{report}/close',
        ];

        // Hit Cache to sync User Cache TTL with User session TTL. (handled in TrackedLoggedUserCacheHitListener )
        if (!in_array($uri, $no_cache_hit_for_these_routes) && isset(auth()->user()->id)) {

            TrackLoggedUserTools::getCacheUserFromCacheKey(TrackLoggedUserTools::getCacheKeyFromUserId(auth()->user()->id));
        }

        return $response;
    }
}
