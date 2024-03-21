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

class CacheJobParameterNameValues
{
    public readonly string $variable_name;
    /** @var int[]|string[] $values */
    public readonly array $values;

    /**
     * @param string $variable_name
     * @param int[]|string[] $values
     */
    public function __construct(string $variable_name, array $values)
    {
        $this->variable_name = $variable_name;
        $this->values = $values;
    }
}
