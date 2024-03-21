<?php

namespace App\Http\Resources;

use App\Http\Resources\Report as ReportResource;
use App\Http\Resources\ReportDataViewLibVersion as ReportDataViewLibVersionResource;
use App\Http\Resources\ReportDataViewJs as ReportDataViewJsResource;
use App\Models\RoleGrants;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportDataView */
class ReportDataView extends JsonResource
{
    public function toArray($request): array
    {
        $loggedUserHasRoleDev = User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_REPORT_DATA_VIEW);

        return [
            'id'                              => $this->id,
            'by_chunk'                        => $this->by_chunk,
            'chunk_size'                      => $this->chunk_size,
            'created_at'                      => $this->created_at,
            'name'                            => $this->name,
            'description'                     => $this->description,
            'description_display_type'        => (int)$this->description_display_type,
            'is_visible'                      => $this->is_visible,
            'on_queue'                        => $this->on_queue,
            'query'                           => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->query;
                }
            ),
            'max_width'                       => $this->max_width,
            'position'                        => $this->position,
            'report'                          => $this->when(
                $request->get('report-id') === 0 && $loggedUserHasRoleDev,
                function () {
                    return ReportResource::make($this->report);
                }
            ),
            'report_id'                       => $this->when(
                $request->get('report-id') === 0,
                function () {
                    return $this->report_id;
                }
            ),
            'report_data_view_js'             => $this->when(
                $request->get('report-id') >= 1,
                function () {
                    return ReportDataViewJsResource::make($this->reportDataViewJs);
                }
            ),
            'report_data_view_js_id'          => $this->when(
                $request->get('report-id') >= 1 && $loggedUserHasRoleDev,
                function () {
                    return $this->reportDataViewJs->id;
                }
            ),
            'report_data_view_lib_version'    => $this->when(
                $request->get('report-id') >= 1 && $loggedUserHasRoleDev,
                function () {
                    return ReportDataViewLibVersionResource::make($this->reportDataViewLibVersion);
                }
            ),
            'report_data_view_lib_version_id' => $this->report_data_view_lib_version_id,
            'title'                           => $this->title,
            'type'                            => (int)$this->type,
            'updated_at'                      => $this->updated_at,
        ];
    }
}
