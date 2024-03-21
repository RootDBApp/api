<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportDataViewLib as ReportDataViewLibResource;
use App\Models\ReportDataViewLib;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportDataViewLibController extends ApiController
{

    public function index(Request $request): AnonymousResourceCollection
    {
        // To display ReportDataViewLibVersions resource.
        $request->request->add(['report_data_view_lib_versions' => 1]);

        return ReportDataViewLibResource::collection(
           ReportDataViewLib::with('reportDataViewLibVersions')->paginate(2000)
        );
    }

    public function show(Request $request, ReportDataViewLib $reportDataViewLib): JsonResponse
    {
        // To display ReportDataViewLibVersions resource.
        $request->request->add(['report_data_view_lib_versions' => 1]);

        return response()->json(new ReportDataViewLibResource($reportDataViewLib));
    }
}
