<?php

namespace App\Http\Controllers;

use App\Models\SystemInfo;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SystemInfoController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new SystemInfo(), false, 'index');

        $system_info = [];
        foreach (DB::select('SHOW VARIABLES LIKE "%version%"') as $variable) {

            if (in_array($variable->Variable_name, ['version', 'tls_version', 'version_ssl_library'])) {
                $system_info['mariadb_' . $variable->Variable_name] = $variable->Value;
            }
        }

        $system_info['php_version'] = phpversion();
        $system_info['memcached_version'] = str_replace('memcached ', '', exec('memcached --version'));

        $appConfig = [
            'app_version'                                     => config('app.version'),
            //'broadcasting_connections_pusher_app_id'          => config('broadcasting.connections.pusher.app_id'),
            'broadcasting_connections_pusher_key'             => config('broadcasting.connections.pusher.key'),
            //'broadcasting_connections_pusher_secret'          => config('broadcasting.connections.pusher.secret'),
            'broadcasting_connections_pusher_options_cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'broadcasting_connections_pusher_options_useTLS'  => config('broadcasting.connections.pusher.options.useTLS'),
            'broadcasting_connections_pusher_options_scheme'  => config('broadcasting.connections.pusher.options.scheme'),
            'websockets_ssl_allow_self_signed'                => config('websockets.ssl.allow_self_signed'),
            'websockets_ssl_verify_peer'                      => config('websockets.ssl.verify_peer'),
            'websockets_ssl_local_cert'                       => config('websockets.ssl.local_cert'),
            'websockets_ssl_local_pk'                         => config('websockets.ssl.local_pk'),
            //'websockets_ssl_passphrase'                       => config('websockets.ssl.passphrase'),
            'cors_allowed_origin'                             => implode(',', config('cors.allowed_origins')),
            'cors_allowed_origins_pattern'                    => implode(',', config('cors.allowed_origins_patterns'))
        ];

        return response()->json(array_merge($system_info, $appConfig));
    }
}
