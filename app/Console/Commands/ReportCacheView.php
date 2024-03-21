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

namespace App\Console\Commands;

use App\Tools\CacheReportTools;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Event\Runtime\PHP;
use Symfony\Component\Console\Helper\TableSeparator;

class ReportCacheView extends Command
{
    protected $signature = 'report:view-cache {key : A cache key from memcached:report}';

    protected $description = 'Display cache content from a key';

    public function handle(): void
    {

        $this->info('Contents of the key ' . $this->argument('key'));
        $data_view_results = Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->get($this->argument('key'));

        if (!is_array($data_view_results)) {
            $this->warn('There\'s no results for this key :/');
        } else {

            $num_results = count($data_view_results);
            $rows = [];
            $line = 1;
            $separator = new TableSeparator;
            $num_fields = 0;


            foreach ($data_view_results as $data_view_result) {

                $values = '';
                if ($line === 1) {
                    $num_fields = count((array)$data_view_result);
                }

                $num_field = 1;
                foreach ($data_view_result as $key => $value) {

                    $values .= $key . '=' . $value;

                    if ($num_field < $num_fields) {
                        $values .= PHP_EOL;
                    }

                    $num_field++;
                }

                $rows[] = [$line++, $values];

                if ($line <= $num_results) {

                    $rows[] = $separator;
                }
            }

            $this->info($num_results . ' results for this data view:');
            $this->table(['Row', 'Fields\'s values'], $rows);
        }
    }

}

