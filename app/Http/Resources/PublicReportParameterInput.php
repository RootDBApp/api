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

use App\Http\Resources\ReportParameterInputType as ParameterInputTypeResource;
use App\Http\Resources\PublicReportParameterInputDataType as ParameterInputDataTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportParameterInput */
class PublicReportParameterInput extends JsonResource
{
    #[ArrayShape(['id' => "int", 'name' => "string", 'default_value' => "string", 'custom_entry' => "int", 'values' => "\Illuminate\Http\Resources\MissingValue|mixed", 'parameter_input_type' => "\App\Http\Resources\ReportParameterInputType", 'parameter_input_data_type' => "\App\Http\Resources\ReportParameterInputDataType"])]
    public function toArray($request): array
    {
        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'default_value'             => $this->getDefaultValue(),
            'values'                    => $this->getParameterValues(),
            'parameter_input_type'      => ParameterInputTypeResource::make($this->parameterInputType),
            'parameter_input_data_type' => ParameterInputDataTypeResource::make($this->parameterInputDataType),
        ];
    }
}
