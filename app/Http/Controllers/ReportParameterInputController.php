<?php

namespace App\Http\Controllers;

use App\Events\APICacheReportParameterInputsUpdated;
use App\Http\Resources\ReportParameterInput as ParameterInputResource;
use App\Models\ReportParameterInput;
use App\Tools\CommonTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Validator;

class ReportParameterInputController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new ReportParameterInput(), false);

        // Generate 8 queries for 7 parameters.
        //return ParameterInputResource::collection(
        //    (new ReportParameterInput)
        //        ->with('confConnector')
        //        ->with('parameterInputType')
        //        ->with('parameterInputDataType')
        //        ->whereRelation('confConnector', 'organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
        //        ->paginate(5000)
        //);

        // Generate 7 queries for 7 parameters. 12 queries with pagination enable.
        // Generate 10 queries for 7 parameters, with default values
        // Generate 13 queries for 7 parameters, with default value, and values
        $builder = ReportParameterInput::query();
        $builder->with('confConnector');
        $builder->with('parameterInputType');
        $builder->with('parameterInputDataType');
        $builder->whereRelation('confConnector', 'organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id);
        $builder->orderBy('name');
        //$builder->paginate(5000);

        $collection = $builder->get();

        $request->request->add(['parameter-default-value' => 1]);
        return ParameterInputResource::collection($collection);
    }

    public function destroy(Request $request, ReportParameterInput $reportParameterInput): JsonResponse
    {
        $this->genericAuthorize($request, $reportParameterInput);

        $reportParameterInput->delete();

        if (App::environment() !== 'testing') {

            APICacheReportParameterInputsUpdated::dispatch($reportParameterInput->confConnector->organization_id);
        }

        return $this->successResponse(null, 'Input parameter deleted.');
    }

    public function show(Request $request, ReportParameterInput $reportParameterInput): JsonResponse
    {
        $this->genericAuthorize($request, $reportParameterInput);

        return response()->json(new ParameterInputResource($reportParameterInput));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, ReportParameterInput::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ReportParameterInput::$rules,
            ReportParameterInput::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the input parameter.', $validator->errors(), 422);
        }

        $reportParameterInput = ReportParameterInput::create($request->toArray());

        if (App::environment() !== 'testing') {

            APICacheReportParameterInputsUpdated::dispatch($reportParameterInput->confConnector->organization_id);
        }

        $request->request->add(['parameter-default-value' => 1]);
        return $this->successResponse(new ParameterInputResource($reportParameterInput), 'The input parameter has been created.');
    }

    public function update(Request $request, ReportParameterInput $reportParameterInput): JsonResponse
    {
        $this->genericAuthorize($request, $reportParameterInput);

        $validator = Validator::make(
            $request->all(),
            ReportParameterInput::$rules,
            ReportParameterInput::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the input parameter.', $validator->errors(), 422);
        }

        $reportParameterInput->update($request->toArray());

        if (App::environment() !== 'testing') {

            APICacheReportParameterInputsUpdated::dispatch($reportParameterInput->confConnector->organization_id);
        }

        $request->request->add(['parameter-default-value' => 1]);
        return $this->successResponse(new ParameterInputResource($reportParameterInput), 'The input parameter has been updated.');
    }

    public function test(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, new ReportParameterInput(), false, 'index');

        $request->request->add(
            [
                'parameter-default-value' => 1,
                'parameter-values'        => 1
            ]);

        // In demo mode, we do not take into account eventual changes made by the user.
        // 4 - it's the ID of the 'demo' role in demo webapp, in DB...
        if (config('app.demo') && in_array(4, auth()->user()->currentOrganizationLoggedUser->roles)) {

            $reportParameterInput = ReportParameterInput::findOrFail($request->id);
        } else {

            $reportParameterInput = ReportParameterInput::make($request->all());
        }

        return response()->json(new ParameterInputResource($reportParameterInput));
    }
}
