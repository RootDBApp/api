<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportUser */
class ReportUser extends JsonResource
{
    /**
     * @param $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'report_id' => $this->when(
                $request->get('report-id') >= 1,
                function ()
                {
                    return $this->report_id;
                }
            ),
            'user_id'   => $this->user_id,
            'user'      => UserResource::make($this->user),
        ];
    }
}
