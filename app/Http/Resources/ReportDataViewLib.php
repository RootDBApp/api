<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportDataViewLib */
class ReportDataViewLib extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'url_website' => $this->url_website,
            'default' => $this->default,
            'report_data_view_lib_versions' => $this->when(
                (int)$request->get('report_data_view_lib_versions') === 1,
                function () {
                    return ReportDataViewLibVersion::collection($this->reportDataViewLibVersions);
                }
            )
        ];
    }
}
