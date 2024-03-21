<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportDataViewJs as ReportDataViewJsResource;
use App\Models\ReportDataView;
use App\Models\ReportDataViewLibTypes;
use App\Models\ReportDataViewJs;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Storage;
use Validator;

class ReportDataViewJsController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new ReportDataViewJs(), false);

        // To display ReportDataViewLibVersion, ReportDataView resource.
        $request->request->add(['report_data_view' => 1, 'report_data_view_lib_version' => 1]);

        return ReportDataViewJsResource::collection((new ReportDataViewJs)->paginate(2000));
    }

    public function destroy(Request $request, ReportDataViewJs $reportDataViewJs): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataViewJs);

        $reportDataViewJs->delete();

        return $this->successResponse(null, "Data view's javascript code deleted.");

    }

    public function show(Request $request, ReportDataViewJs $reportDataViewJs): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataViewJs);

        // To display ReportDataViewLibVersion, ReportDataView resource.
        $request->request->add(['report_data_view' => 1, 'report_data_view_lib_version' => 1]);

        return response()->json(new ReportDataViewJsResource($reportDataViewJs));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, ReportDataViewJs::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ReportDataViewJs::$rules,
            ReportDataViewJs::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', "Unable to store the data view's javascript code.", $validator->errors(), 422);
        }


        // Handle template files.
        if ($request->exists('report_data_view_lib_type_id') && $request->input('report_data_view_lib_version_id') != ReportDataView::TABLE) {

            Log::debug('We have a chart model to use', [$request->get('report_data_view_lib_type_id')]);

            $reportDataViewLibType = ReportDataViewLibTypes::find($request->get('report_data_view_lib_type_id'));
            if (is_a($reportDataViewLibType, '\App\Models\ReportDataViewLibTypes')) {

                Log::debug('Chart model', [$reportDataViewLibType->name]);

                $template_files = [
                    // db column name for ReportDataViewJS
                    'js_init'     => 'templates/data_view_lib_versions/' . $request->get('report_data_view_lib_version_id') . '/' . $reportDataViewLibType->label . '_init.js',
                    'js_code'     => 'templates/data_view_lib_versions/' . $request->get('report_data_view_lib_version_id') . '/' . $reportDataViewLibType->label . '_main.js',
                    'js_register' => 'templates/data_view_lib_versions/' . $request->get('report_data_view_lib_version_id') . '/' . $reportDataViewLibType->label . '_register.js',
                ];
                try {
                    foreach ($template_files as $column_name => $file_name) {

                        if (Storage::disk('local')->exists($file_name)) {

                            Log::debug('Using template', [$column_name, $file_name]);
                            $request->request->add([$column_name => str_replace('[DATA_VIEW_JS_ID]', $request->get('report_data_view_id'), Storage::disk('local')->get($file_name))]);
                        }
                    }
                } catch (Exception $exception) {

                    abort(500, $exception->getMessage());
                }
            }
            // 5: INFO Widget.
        } else if ($request->input('report_data_view_lib_version_id') === 5) {

            Log::debug('We have a info widget to setup', [$request->get('report_data_view_lib_type_id')]);
            $file_name = 'templates/report_data_view_lib_types/' . ReportDataView::INFO . '/default_json_form.js';
            try {
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($file_name)) {

                    Log::debug('Using template', [$file_name]);
                    $request->request->add(['json_form' => Storage::disk('local')->get($file_name)]);
                }
            } catch (Exception $exception) {

                abort(500, $exception->getMessage());
            }
        } else {

            Log::debug('No template, no JS code, it will be created by frontend');
            $request->request->add(
                [
                    'js_init'     => '{}',
                    'js_code'     => '{}',
                    'js_register' => '{}',
                    'json_form'   => '{}'
                ]);
        }

        $reportDataViewJs = ReportDataViewJs::create($request->toArray());

        return $this->successResponse(new ReportDataViewJsResource($reportDataViewJs), "The data view's javascript code have been created.");
    }

    public function update(Request $request, ReportDataViewJs $reportDataViewJs): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataViewJs);

        $validator = Validator::make(
            $request->all(),
            ReportDataViewJs::$rules,
            ReportDataViewJs::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', "Unable to update the data view's javascript code.", $validator->errors(), 422);
        }

        $reportDataViewJs->update($request->toArray());

        return $this->successResponse(new ReportDataViewJsResource($reportDataViewJs), "The data view's javascript code has been updated.");
    }
}
