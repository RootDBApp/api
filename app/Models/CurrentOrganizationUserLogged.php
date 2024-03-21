<?php

namespace App\Models;

use Illuminate\Support\Collection;

class CurrentOrganizationUserLogged
{
    public readonly int $organization_user_id;
    public readonly int $user_id;
    public readonly int $organization_id;
    public readonly array $roles;
    public readonly array $groups;
    public readonly string $web_socket_session_id;
    private Collection $reportLiveConfigs;

    public function __construct(int $organization_user_id, int $user_id, int $organization_id, array $roles, array $groups)
    {
        $this->organization_user_id = $organization_user_id;
        $this->user_id = $user_id;
        $this->organization_id = $organization_id;
        $this->roles = $roles;
        $this->groups = $groups;
        $this->reportLiveConfigs = new Collection([]);
        $this->web_socket_session_id = $user_id . rand(100000, 999999);
    }

    public function addReportLiveUserConfig(ReportLiveUserConfig $reportLiveUserConfig): void
    {
        if (!$this->reportLiveConfigs->contains('report_id', '=', $reportLiveUserConfig->report_id_and_instance_id)) {

            $this->reportLiveConfigs->add($reportLiveUserConfig);
        }
    }

    public function removeReportLiveUserConfig(string $reportIdAndInstanceId)
    {
        /**
         * @var ReportLiveUserConfig $reportLiveConfig
         */
        foreach ($this->reportLiveConfigs as $key => $reportLiveConfig) {

            if ($reportLiveConfig->report_id_and_instance_id === $reportIdAndInstanceId) {

                unset($this->reportLiveConfigs[$key]);
                break;
            }
        }
    }

    public function getReportLiveConfigs(): Collection
    {
        return $this->reportLiveConfigs;
    }
}
