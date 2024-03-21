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
