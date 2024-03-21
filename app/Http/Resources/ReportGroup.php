<?php

namespace App\Http\Resources;

use App\Http\Resources\Group as GroupResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportGroup */
class ReportGroup extends JsonResource
{
    /**
     * @param $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'report_id'  => $this->when(
                $request->get('report-id') >= 1,
                function ()
                {
                    return $this->report_id;
                }
            ),
            'group_id'   => $this->group_id,
            'group'      => GroupResource::make($this->group),
        ];
    }
}
