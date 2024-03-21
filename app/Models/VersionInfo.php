<?php

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
