<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
