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

namespace App\Http\Controllers;

use App\Events\WebSocketServerIsWorkingEvent;
use App\Jobs\VersionsInfoJob;
use App\Models\ApiModel;
use App\Models\Cache;
use App\Models\User;
use App\Tools\CommonTranslation;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class ApiController extends Controller
{
    use ApiResponser;

    protected int $currentOrganizationId = 1;

    public function __construct(Request $request)
    {
        // Handle locale @todo should be in a middleware.
        App::setLocale(session('locale') ? session('locale') : 'en');
        session()->put('locale', App::getLocale());

        if ($request->exists('locale')) {

            if (!in_array($request->get('locale'), ['en', 'fr'])) {

                return $this->errorResponse(CommonTranslation::accessDenied, 'Unsupported language.', [], 500);
            }

            App::setLocale($request->get('locale'));
            session()->put('locale', $request->get('locale'));
        }
    }

    public function fetchLatestVersion(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, new Cache(), false, 'index');

        VersionsInfoJob::dispatch(auth()->user());
        //VersionsInfoJob::dispatchSync(auth()->user()->id);

        return $this->successResponse('', 'Success', 200);
    }

    public function testWebSocketServer(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, new Cache(), false, 'index');

        try {
            WebSocketServerIsWorkingEvent::dispatch(auth()->user());

        } catch (Exception $exception) {

            Log::error('Websocket server is not working.', [$exception]);
        }

        return $this->successResponse(null, null, 204);
    }

    protected function genericAuthorize(
        Request             $request,
        ApiModel|Cache|User $model,
        bool                $checkOrganizationUser = true,
        string|null         $ability = null,
    ): bool
    {
        try {

            if (is_null($ability)) {

                $ability = explode('.', Route::currentRouteName())[1];
            }

            // Log::info('ability', [$ability]);
            $this->authorize($ability, [$model, $request, $checkOrganizationUser]);

            // also works:
            //Gate::authorize(explode('.', Route::currentRouteName())[1], [$model, $request]);
            $this->currentOrganizationId = auth()->user()->currentOrganizationLoggedUser->organization_id;

            // When app is in demo mode, with 'DEMO' role , we don't allow any resource modification.
            // 4 - it's the ID of the 'demo' role in demo webapp, in DB...
            if (config('app.demo') && in_array(4, auth()->user()->currentOrganizationLoggedUser->roles)) {

                if (in_array($ability, ['create', 'update', 'delete'])) {

                    abort(401, CommonTranslation::unableToExecuteThisAction);
                }
            }
        } catch (AuthorizationException $error) {

            abort(401, CommonTranslation::accessDenied . ' - ' . $error->getMessage());
        }

        return true;
    }
}
