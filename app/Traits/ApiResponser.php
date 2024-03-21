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

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

trait ApiResponser
{
    protected function successResponse(mixed $data, string $message = null, string $title = 'Operation finished', int $code = 200): JsonResponse
    {
        return response()->json(
            [
                'status'  => 'Success',
                'title'   => trans($title),
                'message' => trans($message),
                'data'    => $data,
            ],
            $code
        );
    }

    protected function errorResponse(string $title, string $message, mixed $errors, int $code): JsonResponse
    {
        return response()->json(
            [
                'status'  => 'Error',
                'title'   => trans($title),
                'message' => trans($message),
                'errors'  => $errors,
            ],
            $code
        );
    }

    protected function exceptionResponse(string $title, string $message, mixed $trace, int $code): JsonResponse
    {
        return response()->json(
            [
                'status'  => 'Error',
                'title'   => trans($title),
                'message' => trans($message),
                'trace'   => config('app.debug') ? $trace : null,
            ],
            $code
        );
    }
}
