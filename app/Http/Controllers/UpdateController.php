<?php

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
