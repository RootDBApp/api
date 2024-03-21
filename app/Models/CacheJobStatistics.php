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

use Illuminate\Contracts\Support\Arrayable;

class CacheJobStatistics implements Arrayable
{
    public readonly int $num_parameter_sets;
    public readonly int $cache_size_b;

    public function __construct(int $num_parameter_sets, string $cache_size_b)
    {
        $this->num_parameter_sets = $num_parameter_sets;
        $this->cache_size_b = $cache_size_b;
    }

    public function toArray(): array
    {
        return [
            'num_parameter_sets' => $this->num_parameter_sets,
            'cache_size_b'       => $this->cache_size_b,
        ];
    }
}
