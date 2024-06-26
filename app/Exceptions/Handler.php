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

namespace App\Exceptions;

use App\Events\APIExceptionFatalError;
use App\Traits\ApiResponser;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * @param Throwable $e
     *
     * @throws Throwable
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }


    /**
     * @param Request $request
     * @param Throwable $e
     *
     * @return JsonResponse
     */
    public function render($request, Throwable $e): JsonResponse
    {
        return $this->handleException($request, $e);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     *
     * @return JsonResponse
     */
    public function handleException(Request $request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof FatalError) {

            APIExceptionFatalError::dispatch(
                auth()->user()->currentOrganizationLoggedUser->organization_id,
                $exception->getMessage()
            );
            return $this->exceptionResponse('Request error', $exception->getMessage(), $exception->getTrace(), 500);
        }

        if ($exception instanceof TokenMismatchException) {
            return $this->exceptionResponse('Request error', $exception->getMessage(), $exception->getTrace(), 403);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->exceptionResponse('Request error', 'The specified method for the request is invalid', $exception->getTrace(), 405);
            //return $this->errorResponse($exception->getMessage(), $exception->getTrace(), 405);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->exceptionResponse('Request error', 'The specified URL cannot be found', $exception->getTrace(), 404);
            //return $this->errorResponse($exception->getMessage(), $exception->getTrace(), 404);
        }

        if ($exception instanceof HttpException) {
            return $this->exceptionResponse('Request error', $exception->getMessage(), $exception->getTrace(), $exception->getStatusCode());
        }

        return $this->exceptionResponse('Request error', $exception->getMessage(), $exception->getTrace(), 500);
    }
}
