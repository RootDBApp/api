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
use App\Models\ReportUserFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class ReportUserFavoriteController extends ApiController
{

    public function destroy(Request $request, Report $report): JsonResponse
    {

        $reportUserFavorites = ReportUserFavorite::get()->where('report_id', '=', $report->id)->where('user_id', '=', auth()->user()->id);

        if (!is_null($reportUserFavorites) && $reportUserFavorites->count() === 1) {

            $this->genericAuthorize($request, $reportUserFavorites->first());
            $reportUserFavorites->first()->delete();
            return $this->successResponse(null, 'Report removed from favorites.');
        }


        return $this->errorResponse('Request error', 'You cannot remove this favorite.', [], 401);
    }


    public function store(Request $request): JsonResponse
    {
        $request->request->add(['user_id' => auth()->user()->id]);
        $this->genericAuthorize($request, ReportUserFavorite::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ReportUserFavorite::$rules,
            ReportUserFavorite::$rule_messages
        );
        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to add to favorites the report.', $validator->errors(), 422);
        }

        $reportUserFavorite = ReportUserFavorite::create($request->toArray());

        return $this->successResponse($reportUserFavorite->toJson(), 'The report has been added to favorites.');
    }
}
