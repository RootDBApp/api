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

use App\Tools\CommonTranslation;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateController extends ApiController
{
    public function startUpdate(Request $request): JsonResponse|Response
    {
        if (auth()->user()->id !== 1) {

            return Response::deny(CommonTranslation::unableToExecuteThisAction, 401);
        }

        $output = $result_code = '';

        exec('nohup ' . 'bash ' . __DIR__ . '/../../../bash/update.sh -w -v \'' . $request->json()->get(0)['available_version'] . '\' > /dev/null 2>&1 &',
             $output,
             $result_code,
        );

        return response()->json(['status' => $output, 'result' => $result_code]);
    }
}
