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

namespace App\Http\Controllers;

use App\Models\Report;
use App\Tools\Tools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicApiController extends ApiController
{

    protected function checkPermission(Request $request, Report|null $report): bool
    {
        // 3 - check if hash from parameters = hash from database
        if (is_null($report) || $report->public_security_hash !== $request->get('sh')) {

            Log::warning('Hash from parameter URL ( sh=' . $request->get('sh') . ' ) does not match the one from database : ' . $report->public_security_hash);

            return false;
        }

        // 4 - if authorized referrers is set, check if hostname requesting the report is authorized.
        if (mb_strlen($report->public_authorized_referers) > 3) {

            $http_from = Tools::getHttpFrom($request);
            if ($http_from === false) {

                return false;
            }

            foreach (explode(',', $report->public_authorized_referers) as $authorized_host) {

                Log::debug('Testing "' . $http_from . '" against authorized hostname : "' . $authorized_host . '"');

                if (mb_strstr($http_from, $authorized_host)) {

                    return true;
                }
            }

            Log::warning('Unable to find an authorized hostname matching the requesting hostname.');

            return false;
        }

        Log::warning('No authorised hostname found.');
        return false;
    }
}
