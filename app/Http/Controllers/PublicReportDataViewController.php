<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicReportDataView as ReportDataViewResource;
use App\Models\ReportDataView;
use App\Tools\CommonTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicReportDataViewController extends PublicApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if (!$request->get('sh')) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to view this report.', [], 401);
        }

        $queryBuilder = ReportDataView::query();

        if ((int)$request->input('report-id') >= 1) {


            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));
            $queryBuilder->where('is_visible', '=', 1);
            $queryBuilder->with('reportDataViewLibVersion');
            $queryBuilder->with('reportDataViewJs');

        } else {

            // In any cases, display the report-id field.
            $request->request->add(['report-id' => 0]);
        }

        return ReportDataViewResource::collection($queryBuilder->paginate(20));
    }
}
