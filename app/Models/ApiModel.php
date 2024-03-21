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

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ApiModel
 *
 * @method static Builder|ApiModel newModelQuery()
 * @method static Builder|ApiModel newQuery()
 * @method static Builder|ApiModel query()
 * @mixin \Eloquent
 */
class ApiModel extends Model
{
    use Compoships;

    public static array $rule_messages = [
        'between'    => [
            'string'  => '\':attribute\' should contains between :min and :max characters.',
            'numeric' => '\':attribute\' value should be between :min and :max.',
        ],
        'boolean'    => '\':attribute\' field should be a boolean.',
        'email'      => '\':input\' is not a valid email.',
        'exists'     => '\':attribute\' with value \':input\' does not exists.',
        'in'         => '\':attribute\' should be one of these values: :values',
        'integer'    => '\':attribute\' field should be an integer.',
        'max'        => [
            'string'  => '\':attribute\' should not exceed :max characters.',
            'numeric' => '\':attribute\' should be <= :max.',
        ],
        'min'        => [
            'string'  => '\':attribute\' should contains at least :min characters.',
            'numeric' => '\':attribute\' should be >= :min.',
        ],
        'required'   => '\':attribute\' field is required.',
        'unique'     => '\':attribute\' with value \':input\' already exists.',
        'validation' => [
            'regex' => '\':attribute\' is not formatted as expected.',
        ],
    ];
}
