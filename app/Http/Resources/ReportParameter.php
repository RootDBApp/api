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

use App\Http\Resources\ReportParameterInput as ParameterInputResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportParameter */
class ReportParameter extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                   => $this->id,
            'report_id'                            => $this->report_id,
            'parameter_input_id'                   => $this->parameter_input_id,
            'name'                                 => $this->name,
            'variable_name'                        => $this->variable_name,
            'parameter_input'                      => ParameterInputResource::make($this->parameterInput),
            'following_parameter_next_to_this_one' => $this->following_parameter_next_to_this_one,
            'forced_default_value'                 => $this->forced_default_value,
            'available_public_access'              => $this->available_public_access
        ];
    }
}
