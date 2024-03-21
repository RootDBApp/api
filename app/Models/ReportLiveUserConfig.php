<?php

namespace App\Models;

class ReportLiveUserConfig
{
    public readonly int $report_id;
    public readonly string $report_id_and_instance_id;
    private bool $auto_refresh_activated;

    public function __construct(int $report_id, string $report_id_and_instance_id, bool $auto_refresh_activated = false)
    {
        $this->report_id = $report_id;
        $this->report_id_and_instance_id = $report_id_and_instance_id;
        $this->auto_refresh_activated = $auto_refresh_activated;
    }

    public function isAutoRefreshActivated(): bool
    {
        return $this->auto_refresh_activated;
    }

    public function setAutoRefreshActivated(bool $auto_refresh_activated): void
    {
        $this->auto_refresh_activated = $auto_refresh_activated;
    }

}
