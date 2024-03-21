<?php

namespace App\Models;

use App\Tools\Tools;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CacheJobParameterSets
{

    public readonly CacheJob $cacheJob;
    /** @var CacheJobParameterSetConfig[] $cacheJobParameterSetConfigs */
    private array $cacheJobParameterSetConfigs = [];
    private array $parameterHashes = [];

    /**
     * @param CacheJob $cacheJob
     */
    public function __construct(CacheJob $cacheJob)
    {
        $this->cacheJob = $cacheJob;
    }

    public function addCacheJobParameterSetConfig(CacheJobParameterSetConfig $cacheJobParameterSetConfig): void
    {
        $this->cacheJobParameterSetConfigs[] = $cacheJobParameterSetConfig;
    }

    /**
     * @return ParameterSet[][]
     * @throws Exception
     */
    public function getAllParametersSets(): array
    {
        /** @var ParameterSet[] $allParameterSets */
        $allParameterSets = [];
        $parameters = [];

        foreach ($this->cacheJobParameterSetConfigs as $cacheJobParameterSetConfig) {

            $parameters[$cacheJobParameterSetConfig->parameter->variable_name] = $cacheJobParameterSetConfig->getValues();
        }

        foreach (Tools::array_cartesian_utm($parameters) as $combinationNumber => $parametersCombination) {

            $parameterSets = [];
            foreach ($parametersCombination as $parameter_name => $parameter_value) {

                $parameterSets[] = new ParameterSet($parameter_name, $parameter_value);
            }

            $allParameterSets[] = $parameterSets;
        }

        Log::debug("CacheJobParameterSets::getAllParametersSets - number of parameters sets to run: ", [count($allParameterSets)]);
        return $allParameterSets;
    }
}
