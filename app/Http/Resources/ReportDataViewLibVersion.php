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

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportDataViewLibVersion */
class ReportDataViewLibVersion extends JsonResource
{

    #[ArrayShape(['id' => "int", 'major_version' => "string", 'version' => "string", 'url_documentation' => "string", 'default' => "bool", 'report_data_view_lib' => "\Illuminate\Http\Resources\MissingValue|mixed"])]
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'major_version'        => $this->major_version,
            'version'              => $this->version,
            'name'                 => $this->reportDataViewLib->name . ' (v' . $this->version . ')',
            'url_documentation'    => $this->url_documentation,
            'default'              => $this->default,
            'report_data_view_lib' => $this->when(
                (int)$request->get('report_data_view_lib') === 1,
                function () {
                    return ReportDataViewLib::make($this->reportDataViewLib);
                }
            )
        ];
    }
}
