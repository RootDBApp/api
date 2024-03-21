<?php

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
