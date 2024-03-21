<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportGroup as ReportGroupResource;
use App\Models\ReportGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class ReportGroupController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $queryBuilder = ReportGroup::query();

        if ((int)$request->input('report-id') >= 1) {

            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));
        } else {

            // In any cases, display the report-id field.
            $request->request->add(['report-id' => 1]);
        }

        return ReportGroupResource::collection($queryBuilder->paginate(20));
    }


    public function destroy(ReportGroup $reportGroup): JsonResponse
    {
        $reportGroup->delete();

        return $this->successResponse(null, 'Report is not linked anymore to the group.');
    }

    public function show(ReportGroup $reportGroup): JsonResponse
    {
        return response()->json(new ReportGroupResource($reportGroup));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            ReportGroup::$rules,
            ReportGroup::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to link report to the group.', $validator->errors(), 422);
        }

        $reportGroup = ReportGroup::create($request->toArray());

        return $this->successResponse(new ReportGroupResource($reportGroup), 'Report linked to the group.');
    }


    public function update(Request $request, ReportGroup $reportGroup): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            ReportGroup::$rules,
            ReportGroup::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the link.', $validator->errors(), 422);
        }

        $reportGroup->update($request->toArray());

        return $this->successResponse(new ReportGroupResource($reportGroup), 'The link has been updated.');
    }
}
