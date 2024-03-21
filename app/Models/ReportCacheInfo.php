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

use App\Enums\EnumCacheType;

class ReportCacheInfo
{
    public readonly bool $cached;
    public readonly \DateTime $cachedAt;
    public readonly EnumCacheType $cacheType;

    public function __construct(bool $cached, \DateTime $cachedAt, EnumCacheType $cacheType)
    {
        $this->cached = $cached;
        $this->cachedAt = $cachedAt;
        $this->cacheType = $cacheType;
    }
}
