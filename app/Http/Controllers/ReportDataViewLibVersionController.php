<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportDataViewLibVersion as ReportDataViewLibVersionResource;
use App\Models\ReportDataViewLibVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportDataViewLibVersionController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // To display ReportDataViewLib resource.
        $request->request->add(['report_data_view_lib' => 1]);

        return ReportDataViewLibVersionResource::collection(
            ReportDataViewLibVersion::with('reportDataViewLib')
                ->paginate(2000)
        );
    }

    public function show(Request $request, ReportDataViewLibVersion $reportDataViewLibVersion): JsonResponse
    {
        // To display ReportDataViewLib resource.
        $request->request->add(['report_data_view_lib' => 1]);

        return response()->json(new ReportDataViewLibVersionResource($reportDataViewLibVersion));
    }
}
