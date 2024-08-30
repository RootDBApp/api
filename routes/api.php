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

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (loaded by RouteServiceProvider)
|--------------------------------------------------------------------------
| Loaded by the RouteServiceProvider within a group which is assigned the
| "api" middleware group.
*/

//
// Public routes
//
Route::get('/public/report/{report}', 'App\Http\Controllers\PublicReportController@show')
    ->name('public.report')
    ->middleware('web');

Route::get('/public/report/{report}/parameters-sets-in-cache', 'App\Http\Controllers\PublicReportController@parametersSetsInCache')
    ->name('public.parameters-sets-in-cache')
    ->middleware('web');

Route::post('/public/report/{report}/run', 'App\Http\Controllers\PublicReportController@run')
    ->name('public.report.run')
    ->middleware('web');

Route::get('/public/report-data-view', 'App\Http\Controllers\PublicReportDataViewController@index')
    ->name('public.report-data-view')
    ->middleware('web');

Route::get('/theme/{theme}', 'App\Http\Controllers\ThemeController@index')
    ->name('theme')
    ->middleware('web');

Route::get('/theme/fonts/{fonts}', 'App\Http\Controllers\ThemeController@fonts')
    ->name('theme.fonts')
    ->middleware('web');

//
// Private routes
//
Route::get('fetch-latest-version', 'App\Http\Controllers\ApiController@fetchLatestVersion')
    ->name('api.fetch-latest-version')
    ->middleware(['auth:sanctum']);

Route::get('test-web-socket-server', 'App\Http\Controllers\ApiController@testWebSocketServer')
    ->name('api.test-web-socket-server')
    ->middleware(['auth:sanctum']);

Route::apiResource('asset', App\Http\Controllers\AssetController::class)->middleware('auth:sanctum');
Route::post('asset/{asset}/upload', 'App\Http\Controllers\AssetController@upload')
    ->name('asset-upload')
    ->middleware(['auth:sanctum']);

Route::get('cache', 'App\Http\Controllers\CacheController@index')
    ->name('cache')
    ->middleware(['auth:sanctum']);

Route::apiResource('cache-job', App\Http\Controllers\CacheJobController::class)->middleware('auth:sanctum');
Route::post('cache-job/{cacheJob}/delete-cache-report', 'App\Http\Controllers\CacheJobController@deleteCache')
    ->name('cache-job.delete-cache-report')
    ->middleware('auth:sanctum');
Route::post('cache-job/{cacheJob}/run', 'App\Http\Controllers\CacheJobController@run')
    ->name('cache-job.run')
    ->middleware('auth:sanctum');

Route::apiResource('category', App\Http\Controllers\CategoryController::class)->middleware('auth:sanctum');
Route::post('category/{category}/move-reports', 'App\Http\Controllers\CategoryController@moveReports')
    ->name('category.move-reports')
    ->middleware('auth:sanctum');

Route::post('conf-connector/{conf_connector}/exec-queries', 'App\Http\Controllers\ConfConnectorController@execQueries')
    ->name('conf-connector.exec-queries')
    ->middleware('auth:sanctum');

Route::get('conf-connector/{conf_connector}/get-ace-editor-auto-complete', 'App\Http\Controllers\ConfConnectorController@getAceEditorAutoComplete')
    ->name('conf-connector.get-ace-editor-auto-complete')
    ->middleware('auth:sanctum');

Route::get('conf-connector/get-api-server-ip', 'App\Http\Controllers\ConfConnectorController@getApiServerIP')
    ->name('conf-connector.get-api-server-ip')
    ->middleware('auth:sanctum');

Route::get('conf-connector/{conf_connector}/get-prime-react-tree-db', 'App\Http\Controllers\ConfConnectorController@getPrimeReactTreeDB')
    ->name('conf-connector./get-prime-react-tree-db')
    ->middleware('auth:sanctum');

Route::post('conf-connector/{conf_connector}/test-existing-connector', 'App\Http\Controllers\ConfConnectorController@testExistingConnector')
    ->name('conf-connector.test-existing-connector')
    ->middleware('auth:sanctum');

Route::post('conf-connector/test-new-connector', 'App\Http\Controllers\ConfConnectorController@testNewConnector')
    ->name('conf-connector.test-new-connector')
    ->middleware('auth:sanctum');

Route::apiResource('conf-connector', App\Http\Controllers\ConfConnectorController::class)->middleware('auth:sanctum');

Route::get('connector-database', 'App\Http\Controllers\ConnectorDatabaseController@index')
    ->name('connector-database')
    ->middleware('auth:sanctum');

Route::apiResource('directory', App\Http\Controllers\DirectoryController::class)->middleware('auth:sanctum');
Route::post('directory/{directory}/move-reports', 'App\Http\Controllers\DirectoryController@moveReports')
    ->name('directory.move-reports')
    ->middleware('auth:sanctum');

Route::get('draft/{conf_connector}', 'App\Http\Controllers\DraftController@index')
    ->name('draft.index')
    ->middleware('auth:sanctum');

Route::apiResource('draft-queries', App\Http\Controllers\DraftQueriesController::class)
    ->except(['index', 'show'])
    ->middleware('auth:sanctum');

Route::apiResource('group', App\Http\Controllers\GroupController::class)->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/group-organization-users', 'App\Http\Controllers\OrganizationUserControllerGroup@index');

Route::apiResource('license', App\Http\Controllers\LicensesController::class)
    ->middleware('auth:sanctum');

Route::post('login', 'App\Http\Controllers\AuthController@login');

Route::post('logout', 'App\Http\Controllers\AuthController@logout');

Route::apiResource('organization', App\Http\Controllers\OrganizationController::class)->middleware('auth:sanctum');

Route::apiResource('organization-user', App\Http\Controllers\OrganizationUserController::class)->middleware('auth:sanctum');

Route::apiResource('report', App\Http\Controllers\ReportController::class)->middleware('auth:sanctum');
Route::put('/report/{report}/queries', 'App\Http\Controllers\ReportController@updateQueries')
    ->name('report.queries')
    ->middleware('auth:sanctum');

Route::post('/report/{report}/cache-results', 'App\Http\Controllers\ReportController@cacheResults')
    ->name('report.cache-results')
    ->middleware(['auth:sanctum']);

Route::post('/report/{report}/delete-user-cache', 'App\Http\Controllers\ReportController@deleteUserCache')
    ->name('report.delete-user-cache')
    ->middleware(['auth:sanctum']);

Route::get('/report/{report}/close', 'App\Http\Controllers\ReportController@close')
    ->name('report.close')
    ->middleware('auth:sanctum');

Route::get('/report/{report}/parameters-sets-in-cache', 'App\Http\Controllers\ReportController@parametersSetsInCache')
    ->name('report.parameters-sets-in-cache')
    ->middleware('auth:sanctum');

Route::post('/report/{report}/run', 'App\Http\Controllers\ReportController@run')
    ->name('report.run')
    ->middleware('auth:sanctum');

Route::put('/report/{reportId}/update-data-views-layout', 'App\Http\Controllers\ReportController@updateDataViewsLayout')
    ->name('report.update-data-views-layout')
    ->middleware('auth:sanctum');

Route::put('/report/{report}/update-visibility', 'App\Http\Controllers\ReportController@updateVisibility')
    ->name('report.update-visibility')
    ->middleware('auth:sanctum');

Route::apiResource('report-data-view', App\Http\Controllers\ReportDataViewController::class)->middleware('auth:sanctum');
Route::put('/report-data-view/{report_data_view}/query', 'App\Http\Controllers\ReportDataViewController@updateQuery')
    ->name('report-data-view.query')
    ->middleware('auth:sanctum');

Route::post('report-data-view/{report_data_view}/run', 'App\Http\Controllers\ReportDataViewController@run')
    ->name('report-data-view.run')
    ->middleware('auth:sanctum');


Route::apiResource('report-data-view-js', App\Http\Controllers\ReportDataViewJsController::class)
    ->parameters(['report-data-view-js' => 'report-data-view-js'])
    ->middleware('auth:sanctum');

Route::apiResource('report-data-view-lib', App\Http\Controllers\ReportDataViewLibController::class)
    ->except(['store', 'update', 'destroy'])
    ->middleware('auth:sanctum');

Route::apiResource('report-data-view-lib-type', App\Http\Controllers\ReportDataViewLibTypesController::class)
    ->except(['store', 'show', 'update', 'destroy'])
    ->middleware('auth:sanctum');

Route::apiResource('report-data-view-lib-version', App\Http\Controllers\ReportDataViewLibVersionController::class)
    ->except(['store', 'update', 'destroy'])
    ->middleware('auth:sanctum');

Route::apiResource('report-group', App\Http\Controllers\ReportGroupController::class)->middleware('auth:sanctum');

Route::apiResource('report-parameter', App\Http\Controllers\ReportParameterController::class)->middleware('auth:sanctum');

Route::apiResource('report-parameter-input', App\Http\Controllers\ReportParameterInputController::class)->middleware('auth:sanctum');
Route::post('report-parameter-input/test', 'App\Http\Controllers\ReportParameterInputController@test')
    ->name('report-parameter-input.test')
    ->middleware('auth:sanctum');

Route::apiResource('report-parameter-input-type', App\Http\Controllers\ReportParameterInputTypeController::class)->middleware('auth:sanctum');

Route::apiResource('report-parameter-input-data-type', App\Http\Controllers\ReportParameterInputDataTypeController::class)->middleware('auth:sanctum');

Route::apiResource('report-user', App\Http\Controllers\ReportUserController::class)->middleware('auth:sanctum');

Route::apiResource('report-user-favorite', App\Http\Controllers\ReportUserFavoriteController::class)
    ->except(['index', 'destroy', 'update', 'show'])
    ->middleware('auth:sanctum');

Route::delete('/report-user-favorite/{report}', 'App\Http\Controllers\ReportUserFavoriteController@destroy')
    ->name('report-user-favorite.destroy')
    ->middleware('auth:sanctum');

Route::apiResource('role', App\Http\Controllers\RoleController::class)->middleware('auth:sanctum');

Route::get('/user/change-organization-user', 'App\Http\Controllers\UserController@changeOrganizationUser')
    ->name('user.change-organization-user')
    ->middleware('auth:sanctum');

Route::put('/user/reset-password/{user}', 'App\Http\Controllers\UserController@resetPassword')
    ->name('user.reset-password')
    ->middleware('auth:sanctum');

Route::get('/user/test-dev-user', 'App\Http\Controllers\UserController@testDevUser')
    ->name('user.test-dev-user')
    ->middleware('auth:sanctum');

Route::apiResource('service-message', App\Http\Controllers\ServiceMessagesController::class)
    ->middleware('auth:sanctum');

Route::get('system-info', 'App\Http\Controllers\SystemInfoController@index')
    ->name('system-info')
    ->middleware(['auth:sanctum']);

Route::apiResource('user', App\Http\Controllers\UserController::class)->middleware('auth:sanctum');

Route::apiResource('user-preferences', App\Http\Controllers\UserPreferencesController::class)
    ->except(['index', 'store', 'destroy'])
    ->middleware('auth:sanctum');

Route::post('start-update', 'App\Http\Controllers\UpdateController@startUpdate')
    ->middleware('auth:sanctum');
