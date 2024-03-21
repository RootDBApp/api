<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportDataViewLibVersion */
class ReportDataViewLibVersion extends JsonResource
{

    #[ArrayShape(['id' => "int", 'major_version' => "string", 'version' => "string", 'url_documentation' => "string", 'default' => "bool", 'report_data_view_lib' => "\Illuminate\Http\Resources\MissingValue|mixed"])]
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'major_version'        => $this->major_version,
            'version'              => $this->version,
            'name'                 => $this->reportDataViewLib->name . ' (v' . $this->version . ')',
            'url_documentation'    => $this->url_documentation,
            'default'              => $this->default,
            'report_data_view_lib' => $this->when(
                (int)$request->get('report_data_view_lib') === 1,
                function () {
                    return ReportDataViewLib::make($this->reportDataViewLib);
                }
            )
        ];
    }
}
