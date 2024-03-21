<?php

namespace App\Http\Controllers;

use App\Events\APICacheConfConnectorsUpdated;
use App\Events\APICacheReportParameterInputsUpdated;
use App\Events\APICacheReportsUpdated;
use App\Events\SQLQueriesStart;
use App\Http\Resources\PrimeReactTreeDb as PrimeReactTreeResourceDb;
use App\Http\Resources\ConfConnector as ConfConnectorResource;
use App\Jobs\ProcessSQLQueriesJob;
use App\Models\ConfConnector;
use App\Models\Draft;
use App\Models\Report;
use App\Models\ReportParameterInput;
use App\Services\ConnectorService;
use App\Tools\CommonTranslation;
use Crypt;
use Database\Seeders\Common\ReportParameterInputsSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Validator;

class ConfConnectorController extends ApiController
{
    public function destroy(Request $request, ConfConnector $confConnector): JsonResponse
    {

        $this->genericAuthorize($request, $confConnector);

        $api_response_message_part_2 = '';
        // Change Connector for Report and ParameterInput
        if ((int)$request->input('new-connector-id') >= 1 && (int)$request->input('new-connector-id') != $confConnector->id) {

            Report::where('conf_connector_id', '=', $confConnector->id)
                ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->update(['conf_connector_id' => $request->input('new-connector-id')]);

            ReportParameterInput::where('conf_connector_id', '=', $confConnector->id)
                ->update(['conf_connector_id' => $request->input('new-connector-id')]);

            Draft::where('conf_connector_id', '=', $confConnector->id)
                ->update(['conf_connector_id' => $request->input('new-connector-id')]);


            $api_response_message_part_2 = ' Reports and ParameterInput connector changed.';
        }

        $api_response_message_part_1 = 'Connector deleted.';
        $confConnector->delete();

        if (App::environment() !== 'testing') {

            APICacheConfConnectorsUpdated::dispatch($confConnector->organization_id, $request);
            APICacheReportsUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
            APICacheReportParameterInputsUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
        }

        return $this->successResponse(null, $api_response_message_part_1 . $api_response_message_part_2);
    }

    public function execQueries(Request $request, ConfConnector $confConnector): JsonResponse
    {
        $this->genericAuthorize($request, $confConnector, true, 'index');

        // In demo mode we do not allow any query.
        // 4 - it's the ID of the 'demo' role in demo webapp, in DB...
        if (config('app.demo') && in_array(4, auth()->user()->currentOrganizationLoggedUser->roles)) {

            abort(401, CommonTranslation::unableToExecuteThisAction);
        }

        SQLQueriesStart::dispatch(auth()->user()->currentOrganizationLoggedUser->web_socket_session_id, $request->input('draft_query_id'));

        ProcessSQLQueriesJob::dispatch(
            $request->input('draft_query_id'),
            $request->input('queries'),
            $confConnector,
            auth()->user()->currentOrganizationLoggedUser->web_socket_session_id
        );

        return $this->successResponse('SQL sent.');
    }

    public function getApiServerIP(): JsonResponse
    {

        $externalIp = file_get_contents('https://myexternalip.com/raw');

        return response()->json(['ip' => $externalIp]);
    }

    public function getAceEditorAutoComplete(Request $request, ConfConnector $confConnector, ConnectorService $connectorService): string
    {
        $this->genericAuthorize($request, $confConnector, true, 'index');

        $connectorInstance = $connectorService->getInstance($confConnector);
        $wordsCollection = $connectorInstance->getAutoComplete();

        return $wordsCollection->toJson();
    }

    public function getPrimeReactTreeDB(Request $request, ConfConnector $confConnector, ConnectorService $connectorService): JsonResponse|AnonymousResourceCollection
    {
        $this->genericAuthorize($request, $confConnector, true, 'index');

        $connectorInstance = $connectorService->getInstance($confConnector);

        $primeReactTreeDB = $connectorInstance->getPrimeReactTreeDB($request);

        if ($primeReactTreeDB === false) {

            return $this->errorResponse('Request Error', "Unable to get the PrimeReact TreeCode's json for this connector.", [], 503);
        }

        return PrimeReactTreeResourceDb::collection($primeReactTreeDB->all());
    }

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new ConfConnector(), false);

        if ($request->exists('for-admin')) {

            $paginator = (new ConfConnector)
                ->with('connectorDatabase')
                //->with('reports')
                //->with('reportParameterInputs')
                ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->paginate(20);

            return ConfConnectorResource::collection($paginator);
        }

        return ConfConnectorResource::collection(
            (new ConfConnector())
                ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->paginate($request->exists('for-admin') ? 20 : 9999)
        );
    }

    public function show(Request $request, ConfConnector $confConnector): JsonResponse
    {
        $this->genericAuthorize($request, $confConnector);

        return response()->json(new ConfConnectorResource($confConnector));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, ConfConnector::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ConfConnector::$rules,
            ConfConnector::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the connector.', $validator->errors(), 422);
        }

        $request->request->set('password', Crypt::encrypt($request->input('password')));

        // If it's the first ConfConnector of this Organization, we need to create all defaults ReportParameterInputs
        // like : string, a day ago, a month ago, yes / no.
        // in a dedicated "All connectors" ConfConnector - for this Organization.
        $numConnectors = ConfConnector::where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->count();

        // Create the new fresh ConfConnector ...
        $confConnector = ConfConnector::create($request->toArray());

        // ... and add Organization default ReportParameterInputs, using a dedicated "All connectors" ConfConnector.
        if ($numConnectors === 0) {

            $AllConnectorsConfConnector = ConfConnector::create(
                [
                    'organization_id'              => auth()->user()->currentOrganizationLoggedUser->organization_id,
                    'name'                         => 'all connectors',
                    'connector_database_id'        => $confConnector->connector_database_id,
                    'host'                         => '',
                    'port'                         => 0,
                    'database'                     => '',
                    'username'                     => '',
                    'password'                     => Crypt::encrypt('az'),
                    'timeout'                      => 0,
                    'use_ssl'                      => 0,
                    'ssl_ca'                       => '',
                    'ssl_cert'                     => '',
                    'ssl_key'                      => '',
                    'global'                       => 1,
                    'mysql_ssl_verify_server_cert' => 0,
                    'pgsql_ssl_mode'               => 'disable',
                ]
            );

            (new ReportParameterInputsSeeder)->run($AllConnectorsConfConnector->id);
        }

        if (App::environment() !== 'testing') {

            APICacheConfConnectorsUpdated::dispatch($confConnector->organization_id);
        }

        return $this->successResponse(new ConfConnectorResource($confConnector), 'The connector has been created.');
    }

    public function testNewConnector(Request $request, ConnectorService $connectorService): JsonResponse
    {
        $this->genericAuthorize($request, new ConfConnector(), false, 'index');

        $confConnector = ConfConnector::make($request->all());
        // Because CommonConnectorService::init will decrypt it.
        $confConnector->password = Crypt::encrypt($confConnector->password);

        if ($confConnector->test($connectorService) === false) {

            return $this->errorResponse(
                'Error',
                'Unable to establish a connection.',
                $confConnector->getLastError(),
                417
            );
        }

        return $this->successResponse($confConnector, 'The ConfConnector has been tested OK, great.');
    }

    public function testExistingConnector(Request $request, ConnectorService $connectorService, ConfConnector $confConnector): JsonResponse
    {
        $this->genericAuthorize($request, $confConnector, false, 'index');

        if ($confConnector->test($connectorService, true) === false) {

            return $this->errorResponse(
                'Error',
                'Unable to establish a connection.',
                $confConnector->getLastError(),
                417
            );
        }

        $request->request->add(['for-admin' => 1]);
        return response()->json(new ConfConnectorResource($confConnector));
    }

    public function update(Request $request, ConfConnector $confConnector): JsonResponse
    {
        $this->genericAuthorize($request, $confConnector);

        //$request->request->add(['organization_id' => auth()->user()->currentOrganizationLoggedUser->organization_id]);

        $validator = Validator::make(
            $request->all(),
            ConfConnector::$rules,
            ConfConnector::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the connector.', $validator->errors(), 422);
        }


        $request->request->set('password', Crypt::encrypt($request->input('password')));
        $confConnector->update($request->toArray());

        if (App::environment() !== 'testing') {

            APICacheConfConnectorsUpdated::dispatch($confConnector->organization_id);
        }

        return $this->successResponse(new ConfConnectorResource($confConnector), 'The connector has been updated.');
    }

}
