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

use App\Http\Resources\DraftQueries as DraftQueriesResource;
use App\Models\DraftQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class DraftQueriesController extends ApiController
{
    // @todo - understand why model not loaded directory
    //public function destroy(Request $request, DraftQueries $draftQueries): JsonResponse
    public function destroy(Request $request, int $draftQueriesId): JsonResponse
    {
        $draftQueries = DraftQueries::where('id', '=', $draftQueriesId)
            ->with(['draft', 'draft.confConnector'])
            ->first();

        if (is_null($draftQueries)) {

            return $this->errorResponse('Request Error', 'Unable to find this DraftQueries.', [], 422);
        }

        $this->genericAuthorize($request, $draftQueries);

        $draftQueries->delete();

        return $this->successResponse(null, 'DraftQueries deleted.');
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, DraftQueries::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            DraftQueries::$rules,
            DraftQueries::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to store the DraftQueries.', $validator->errors(), 422);
        }

        $request->request->add(['queries' => '-- CTRL+D                                     - to comment line(s)
-- CTRL+ENTER  (CMD+ENTER @mac)               - run the queries
-- CTRL+SHIFT-ENTER  (CMD+SHIFT-ENTER @mac)   - run the selected queries
-- ALT+SHIFT+V                                - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )']);

        $ConsoleTab = DraftQueries::create($request->all());

        return $this->successResponse(new DraftQueriesResource($ConsoleTab), 'The DraftQueries has been created.');
    }

    // @todo - understand why model not loaded directory
    public function update(Request $request, int $draftQueriesId): JsonResponse
    {

        $draftQueries = DraftQueries::where('id', '=', $draftQueriesId)
            ->with(['draft', 'draft.confConnector'])
            ->first();

        if (is_null($draftQueries)) {

            return $this->errorResponse('Request Error', 'Unable to find this DraftQueries.', [], 422);
        }

        $this->genericAuthorize($request, $draftQueries);


        $validator = Validator::make(
            $request->all(),
            DraftQueries::$rules,
            DraftQueries::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to update the DraftQueries.', $validator->errors(), 422);
        }

        $draftQueries->update($request->all());

        return $this->successResponse(new DraftQueriesResource($draftQueries), 'The DraftQueries has been updated.');
    }
}
