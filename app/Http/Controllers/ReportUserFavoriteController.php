<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportUserFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class ReportUserFavoriteController extends ApiController
{

    public function destroy(Request $request, Report $report): JsonResponse
    {

        $reportUserFavorites = ReportUserFavorite::get()->where('report_id', '=', $report->id)->where('user_id', '=', auth()->user()->id);

        if (!is_null($reportUserFavorites) && $reportUserFavorites->count() === 1) {

            $this->genericAuthorize($request, $reportUserFavorites->first());
            $reportUserFavorites->first()->delete();
            return $this->successResponse(null, 'Report removed from favorites.');
        }


        return $this->errorResponse('Request error', 'You cannot remove this favorite.', [], 401);
    }


    public function store(Request $request): JsonResponse
    {
        $request->request->add(['user_id' => auth()->user()->id]);
        $this->genericAuthorize($request, ReportUserFavorite::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ReportUserFavorite::$rules,
            ReportUserFavorite::$rule_messages
        );
        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to add to favorites the report.', $validator->errors(), 422);
        }

        $reportUserFavorite = ReportUserFavorite::create($request->toArray());

        return $this->successResponse($reportUserFavorite->toJson(), 'The report has been added to favorites.');
    }
}
