<?php

namespace App\Models;

use App\Enums\EnumCacheType;
use App\Tools\ReportTools;

class ExecReportInfo
{
    public readonly CurrentOrganizationUserLogged|null $currentOrganizationLoggedUser;
    public readonly string $instanceId;
    public readonly string|null $websocketPublicUserId;
    public CacheJob|null $cacheJob;
    public readonly bool $async;
    public readonly bool $useCache;
    /** @var ReportCacheInfo */
    public ReportCacheInfo $reportCacheInfo;
    /** @var string[] $dataViewKeys , indexed by data_views.id */
    public array $dataViewKeys = [];

    private array $inputParameters;
    private array $rawInputParameters;

    /**
     * @param array $inputParameters
     * @param int $instanceId If 0, it means we are running from CLI. (cron task)
     * @param CurrentOrganizationUserLogged|null $currentOrganizationLoggedUser
     * @param string|null $websocketPublicUserId
     * @param CacheJob|null $cacheJob
     * @param bool $useCache
     * @param bool $async
     */
    public function __construct(
        array                          $inputParameters,
        int                            $instanceId,
        ?CurrentOrganizationUserLogged $currentOrganizationLoggedUser,
        ?string                        $websocketPublicUserId,
        ?CacheJob                      $cacheJob,
        bool                           $useCache = true,
        bool                           $async = true,

    )
    {
        $this->inputParameters = $inputParameters;
        $this->rawInputParameters = $inputParameters;
        $this->instanceId = $instanceId;
        $this->currentOrganizationLoggedUser = $currentOrganizationLoggedUser;
        $this->websocketPublicUserId = $websocketPublicUserId;
        $this->async = $async;
        $this->useCache = $useCache;
        $this->cacheJob = $cacheJob;
        $this->reportCacheInfo = new ReportCacheInfo(false, new \DateTime(), EnumCacheType::JOB);

        // We order the inputParameters because for Connector which does not support proper SQL variables initialization,
        // we simply replace in the SQL query "@<input_parameter_name>" by the <input_parameter_value>
        // So we have to make sure we do not replace a "@variable_name_xyz" with "@variable_name".
        // And it's also useful for cache key generation, where we need to always have the parameters in the same order.
        ReportTools::orderInputParameters($this->inputParameters);
    }

    public function inputParameters(): array
    {
        return $this->inputParameters;
    }

    public function rawInputParameters(): array
    {
        return $this->rawInputParameters;
    }

    /**
     * Parameter's values will be ordered depending on their type. ( for instance, multi-select parameters values will be ordered ASC)
     * Because, with the cache system, we need to have all parameters values in the same order, because if not, generated hash for the cache key won't be the same if
     * parameters are not coming in the same order everywhere.
     *
     * @param Report $report
     * @return void
     */
    public function orderInputParametersValues(Report $report): void
    {
        $this->inputParameters = ReportTools::orderInputParametersValues($report->parameters(), $this->inputParameters);
    }

    public function inputParametersFlattened(): string
    {
        $input_parameters_flattened = '';
        foreach ($this->inputParameters as $parameter) {

            $input_parameters_flattened .= $parameter['name'] . ': ' . $parameter['value'] . ' | ';
        }

        return $input_parameters_flattened;
    }

    public function isAuthenticatedUser(): bool
    {
        return !is_null($this->currentOrganizationLoggedUser);
    }
}
