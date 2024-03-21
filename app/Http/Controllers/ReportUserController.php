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

use App\Http\Resources\ReportUser as ReportUserResource;
use App\Models\ReportUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class ReportUserController extends ApiController
{

    public function index(Request $request): AnonymousResourceCollection
    {
        $queryBuilder = ReportUser::query();

        if ((int)$request->input('report-id') >= 1) {

            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));
        } else {

            // In any cases, display the report-id field.
            $request->request->add(['report-id' => 1]);
        }

        return ReportUserResource::collection($queryBuilder->paginate(20));
    }

    public function destroy(ReportUser $reportUser): JsonResponse
    {
        $reportUser->delete();

        return $this->successResponse(null, 'User not linked to the report anymore.');
    }

    public function show(ReportUser $reportUser): JsonResponse
    {
        return response()->json(new ReportUserResource($reportUser));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            ReportUser::$rules,
            ReportUser::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to link user to the report.', $validator->errors(), 422);
        }

        $reportUser = ReportUser::create($request->toArray());

        return $this->successResponse(new ReportUserResource($reportUser), 'User linked to the report.');
    }

    public function update(Request $request, ReportUser $reportUser): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            ReportUser::$rules,
            ReportUser::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the link.', $validator->errors(), 422);
        }

        $reportUser->update($request->toArray());

        return $this->successResponse(new ReportUserResource($reportUser), 'The link has been updated.');
    }
}
