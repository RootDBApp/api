<?php

namespace App\Http\Resources;

use App\Http\Resources\OrganizationUser as OrganizationUserResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Role */
class Role extends JsonResource
{
    /**
     * @param $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'users' => $this->when(
                (int)$request->get('users') === 1,
                function () {
                    OrganizationUserResource::collection($this->users);
                }
            ),
        ];
    }
}
