<?php

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
