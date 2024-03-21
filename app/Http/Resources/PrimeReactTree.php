<?php

namespace App\Http\Resources;

use App\Http\Resources\PrimeReactTree as PrimeReactTreeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PrimeReactTree */
class PrimeReactTree extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return
            [
                'key' => $this->key,
                'label' => $this->label,
                'data' => $this->when(
                    !is_null($this->data),
                    function () {
                        return $this->data;
                    }
                ),
                'icon' => $this->icon,
                'children' => $this->when(
                    !is_null($this->children),
                    function () {
                        return PrimeReactTreeResource::collection($this->children);
                    }
                ),
                'style' => $this->when(
                    !is_null($this->style),
                    function () {
                        return $this->style;
                    }
                ),
                'className' => $this->when(
                    !is_null($this->className),
                    function () {
                        return $this->className;
                    }
                ),
                'draggable' => $this->when(
                    $this->draggable === true,
                    function () {
                        return $this->draggable;
                    }
                ),
                'droppable' => $this->when(
                    $this->draggable === true,
                    function () {
                        return $this->draggable;
                    }
                ),
                'selectable' => $this->when(
                    $this->selectable === true,
                    function () {
                        return $this->selectable;
                    }
                ),
                'leaf' => $this->when(
                    $this->leaf === true,
                    function () {
                        return $this->leaf;
                    }
                ),
            ];
    }
}
