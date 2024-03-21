<?php

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
