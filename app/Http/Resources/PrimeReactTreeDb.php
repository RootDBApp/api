<?php

namespace App\Http\Resources;

use App\Http\Resources\PrimeReactTreeDb as PrimeReactTreeDbResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PrimeReactTreeDb */
class PrimeReactTreeDb extends JsonResource
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
                'column_type' => $this->when(
                    !is_null($this->column_type),
                    function () {
                        return $this->column_type;
                    }
                ),
                'label_type' => $this->label_type,

                'data' => $this->when(
                    !is_null($this->data),
                    function () {
                        return $this->data;
                    }
                ),

                'data_description' => $this->when(
                    !is_null($this->data_description),
                    function () {
                        return $this->data_description;
                    }
                ),

                'icon' => $this->icon,
                'children' => $this->when(
                    !is_null($this->children),
                    function () {
                        return PrimeReactTreeDbResource::collection($this->children);
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
