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

use App\Enums\EnumVersion;
use PHLAK\SemVer\Version;

class VersionInfo
{
    public EnumVersion $type = EnumVersion::API;
    public Version $version;
    public Version $update_version_available;
    public bool $update_available = false;
    public string $url_release_note = '';

    public function __construct(EnumVersion $type, Version $version)
    {
        $this->version = $version;
        $this->type = $type;
        $this->update_version_available = new Version('0.0.0');

    }

    public function toArray(): array
    {
        return [
            'type'              => $this->type->value,
            'version'           => (string)$this->version,
            'available_version' => (string)$this->update_version_available,
            'update_available'  => $this->update_available,
            'url_release_note'  => $this->url_release_note,
        ];
    }
}
