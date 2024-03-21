<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
