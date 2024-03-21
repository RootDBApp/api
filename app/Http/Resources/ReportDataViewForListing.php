<?php

namespace App\Http\Resources;

use App\Http\Resources\Report as ReportResource;
use App\Http\Resources\ReportDataViewLibVersion as ReportDataViewLibVersionResource;
use App\Http\Resources\ReportDataViewJs as ReportDataViewJsResource;
use App\Models\RoleGrants;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportDataView */
class ReportDataViewForListing extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name
        ];
    }
}
