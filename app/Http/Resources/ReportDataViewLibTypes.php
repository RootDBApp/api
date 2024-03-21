<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportDataViewLibTypes */
class ReportDataViewLibTypes extends JsonResource
{
    #[ArrayShape(['id' => "int", 'report_data_view_lib_id' => "int", 'label' => "string", 'name' => "string"])]
    public function toArray($request): array
    {
        return [
            'id'                              => $this->id,
            'report_data_view_lib_version_id' => $this->report_data_view_lib_version_id,
            'label'                           => $this->label,
            'name'                            => $this->name,
        ];
    }
}
