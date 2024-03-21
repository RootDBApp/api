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

use App\Http\Resources\Draft as DraftResource;
use App\Models\ConfConnector;
use App\Models\DraftQueries;
use App\Models\Draft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DraftController extends ApiController
{
    public function index(Request $request, ConfConnector $confConnector): JsonResponse
    {
        $this->genericAuthorize($request, new Draft());

        // First check if we have a QuerieDraft for this OrganizationUser, and ConfConnector and if not...
        $draft = Draft::where('conf_connector_id', '=', $confConnector->id)
            ->where('user_id', '=', auth()->user()->id)
            ->with('draftQueries')
            ->first();

        // .. we create it now.
        if (is_null($draft)) {

            $draft = Draft::create(
                [
                    'user_id'           => auth()->user()->id,
                    'conf_connector_id' => $confConnector->id,
                    'name'              => 'Draft #1',
                ]);

            DraftQueries::create(
                [
                    'draft_id' => $draft->id,
                    'queries'   => '-- CTRL+D                                     - to comment line(s)
-- CTRL+ENTER  (CMD+ENTER @mac)               - run the queries
-- CTRL+SHIFT-ENTER  (CMD+SHIFT-ENTER @mac)   - run the selected queries
-- ALT+SHIFT+V                                - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )'
                ]);

            $draft->fresh(['draftQueries']);
        }


        return response()->json(new DraftResource($draft));
    }
}
