<?php

namespace App\Http\Controllers;

use App\Events\APICacheReportUpdated;
use App\Http\Resources\ReportParameter as ReportParameterResource;
use App\Models\Report;
use App\Models\ReportParameter;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class ReportParameterController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new ReportParameter(), false);

        $queryBuilder = ReportParameter::query();

        if ((int)$request->input('report-id') >= 1) {

            $request->request->add(['parameter-default-value' => 1]);
            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));
        } else {

            // In any cases, display the report-id field.
            $request->request->add(['report-id' => 1]);
        }

        return ReportParameterResource::collection($queryBuilder->paginate(20));
    }

    public function destroy(Request $request, ReportParameter $reportParameter): JsonResponse
    {

        $this->genericAuthorize($request, $reportParameter);

        $reportParameter->delete();

        $this->_reportUpdate($request, $reportParameter);

        $request->request->add(['parameter-values' => 1]);

        return $this->successResponse(
            ReportParameterResource::collection(ReportParameter::where('report_id', '=', $reportParameter->report_id)->get()),
            'Parameter deleted.'
        );
    }

    public function show(Request $request, ReportParameter $reportParameter): JsonResponse
    {
        $this->genericAuthorize($request, $reportParameter);

        return response()->json(new ReportParameterResource($reportParameter));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, ReportParameter::make($request->toArray()));

        // DD/MM/YYYY
        //if ($reportParameter->parameterInput->parameter_input_type_id === 6
        //    && strlen($request->get('forced_default_value')) >= 10) {
        //
        //    $forcedDefaultValueDate = new DateTime($request->get('forced_default_value'));
        //    $request->request->set('forced_default_value', $forcedDefaultValueDate->format('Y-m-d'));
        //}

        $validator = Validator::make(
            $request->all(),
            ReportParameter::$rules,
            ReportParameter::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the parameter.', $validator->errors(), 422);
        }

        $reportParameter = ReportParameter::create($request->toArray());

        $this->_reportUpdate($request, $reportParameter);

        $request->request->add(['parameter-values' => 1, 'parameter-default-value' => 1]);

        return $this->successResponse(
            ReportParameterResource::collection(ReportParameter::where('report_id', '=', $reportParameter->report_id)->get()),
            'The parameter has been created.'
        );
    }

    public function update(Request $request, ReportParameter $reportParameter): JsonResponse
    {
        $this->genericAuthorize($request, $reportParameter);

        // DD/MM/YYYY
        if ($reportParameter->parameterInput->parameter_input_type_id === 6
            && strlen($request->get('forced_default_value')) >= 10) {

            $forcedDefaultValueDate = new DateTime((string)$request->get('forced_default_value'));
            $request->request->set('forced_default_value', $forcedDefaultValueDate->format('Y-m-d'));
        }

        $validator = Validator::make(
            $request->all(),
            ReportParameter::$rules,
            ReportParameter::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the parameter.', $validator->errors(), 422);
        }

        $reportParameter->update($request->toArray());

        $this->_reportUpdate($request, $reportParameter);

        $request->request->add(['parameter-values' => 1, 'parameter-default-value' => 1]);

        return $this->successResponse(
            ReportParameterResource::collection(ReportParameter::where('report_id', '=', $reportParameter->report_id)->get()),
            'The parameter has been updated.'
        );
    }

    private function _reportUpdate(Request $request, ReportParameter $reportParameter): void
    {

        $report = Report::findOrFail($reportParameter->report_id);
        if ($report) {

            $report->has_parameters = $report->parameters()->count() > 0;
            $report->update();
            APICacheReportUpdated::dispatch($report);
        }
    }
}
