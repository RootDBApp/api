<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportUser as ReportUserResource;
use App\Models\ReportUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class ReportUserController extends ApiController
{

    public function index(Request $request): AnonymousResourceCollection
    {
        $queryBuilder = ReportUser::query();

        if ((int)$request->input('report-id') >= 1) {

            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));
        } else {

            // In any cases, display the report-id field.
            $request->request->add(['report-id' => 1]);
        }

        return ReportUserResource::collection($queryBuilder->paginate(20));
    }

    public function destroy(ReportUser $reportUser): JsonResponse
    {
        $reportUser->delete();

        return $this->successResponse(null, 'User not linked to the report anymore.');
    }

    public function show(ReportUser $reportUser): JsonResponse
    {
        return response()->json(new ReportUserResource($reportUser));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            ReportUser::$rules,
            ReportUser::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to link user to the report.', $validator->errors(), 422);
        }

        $reportUser = ReportUser::create($request->toArray());

        return $this->successResponse(new ReportUserResource($reportUser), 'User linked to the report.');
    }

    public function update(Request $request, ReportUser $reportUser): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            ReportUser::$rules,
            ReportUser::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the link.', $validator->errors(), 422);
        }

        $reportUser->update($request->toArray());

        return $this->successResponse(new ReportUserResource($reportUser), 'The link has been updated.');
    }
}
