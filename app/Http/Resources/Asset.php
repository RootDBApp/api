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

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Asset */
class Asset extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'storage_type' => $this->storage_type,
            'data_content'         => $this->when($request->get('complete_resource'),
                function () {
                    return $this->data_content;
                }
            ),
            'data_type'    => $this->when($request->get('complete_resource'),
                function () {
                    return $this->data_type;
                }
            ),
            'pathname'     => $this->when($request->get('complete_resource'),
                function () {
                    return $this->pathname;
                }
            ),
            'url'          => $this->when($request->get('complete_resource'),
                function () {
                    return $this->url;
                }
            ),
        ];
    }
}
