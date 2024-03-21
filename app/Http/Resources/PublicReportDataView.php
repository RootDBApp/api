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

use App\Http\Resources\ReportDataViewJs as ReportDataViewJsResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportDataView */
class PublicReportDataView extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'report_id'                => $this->when(
                $request->get('report-id') === 0,
                function () {
                    return $this->report_id;
                }
            ),
            'type'                     => (int)$this->type,
            'name'                     => $this->name,
            'title'                    => $this->title,
            'description'              => $this->description,
            'description_display_type' => (int)$this->description_display_type,
            'position'                 => $this->position,
            'report_data_view_js'      => $this->when(
                $request->get('report-id') >= 1,
                function () {
                    return ReportDataViewJsResource::make($this->reportDataViewJs);
                }
            ),
            'max_width'                => $this->max_width,
        ];
    }
}
