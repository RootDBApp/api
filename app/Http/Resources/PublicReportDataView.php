<?php

namespace App\Http\Resources;

use App\Http\Resources\ReportDataViewJs as ReportDataViewJsResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportDataView */
class PublicReportDataView extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'report_id'                => $this->when(
                $request->get('report-id') === 0,
                function () {
                    return $this->report_id;
                }
            ),
            'type'                     => (int)$this->type,
            'name'                     => $this->name,
            'title'                    => $this->title,
            'description'              => $this->description,
            'description_display_type' => (int)$this->description_display_type,
            'position'                 => $this->position,
            'report_data_view_js'      => $this->when(
                $request->get('report-id') >= 1,
                function () {
                    return ReportDataViewJsResource::make($this->reportDataViewJs);
                }
            ),
            'max_width'                => $this->max_width,
        ];
    }
}
