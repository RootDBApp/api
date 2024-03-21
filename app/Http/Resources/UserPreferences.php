<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\UserPreferences */
class UserPreferences extends JsonResource
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
            'lang'  => $this->lang,
            'theme' => $this->theme,
        ];
    }
}
