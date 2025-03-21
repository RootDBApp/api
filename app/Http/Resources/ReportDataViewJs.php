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

namespace App\Http\Resources;

use App\Models\RoleGrants;
use App\Models\User;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use JShrink\Minifier;

/** @mixin \App\Models\ReportDataViewJs */
class ReportDataViewJs extends JsonResource
{
    public function toArray($request): array
    {
        // Only the DEV can see the full JS.
        $loggedUserHasRoleDev = User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_REPORT_DATA_VIEW_JS);

        // Minify JS
        $json_form = $js_register = $js_code = $js_init = null;
        try {
            $json_form = (!$loggedUserHasRoleDev && !is_null($this->json_form)) ? base64_encode(gzcompress($this->json_form)) : $this->json_form;
            $js_register = (!$loggedUserHasRoleDev && !is_null($this->js_register)) ? base64_encode(gzcompress(Minifier::minify($this->js_register))) : $this->js_register;
            $js_code = (!$loggedUserHasRoleDev && !is_null($this->js_code)) ? base64_encode(gzcompress(Minifier::minify($this->js_code))) : $this->js_code;
            $js_init = (!$loggedUserHasRoleDev && !is_null($this->js_init)) ? base64_encode(gzcompress(Minifier::minify($this->js_init))) : $this->js_init;
            $json_runtime_configuration = (!$loggedUserHasRoleDev && !is_null($this->json_runtime_configuration)) ? base64_encode(gzcompress(Minifier::minify($this->json_runtime_configuration))) : $this->json_runtime_configuration;

        } catch (Exception $exception) {

            abort(500, $exception->getMessage());
        }

        return [
            'id'                                  => $this->id,
            'json_form'                           => $json_form,
            'json_form_minified'                  => !$loggedUserHasRoleDev,
            'json_runtime_configuration'          => $json_runtime_configuration,
            'json_runtime_configuration_minified' => !$loggedUserHasRoleDev,
            'js_register'                         => $js_register,
            'js_register_minified'                => !$loggedUserHasRoleDev,
            'js_code'                             => $js_code,
            'js_code_minified'                    => !$loggedUserHasRoleDev,
            'js_init'                             => $js_init,
            'js_init_minified'                    => !$loggedUserHasRoleDev,
            'report_data_view_id'                 => $this->report_data_view_id,
            'report_data_view_lib_version_id'     => $this->report_data_view_lib_version_id,
            'report_data_view'                    => $this->when(
                (int)$request->get('report_data_view') === 1,
                function () {
                    return ReportDataView::make($this->reportDataView);
                }
            ),
            'report_data_view_lib_version'        => $this->when(
                (int)$request->get('report_data_view_lib_version') === 1,
                function () {
                    return ReportDataViewLibVersion::make($this->reportDataViewLibVersion);
                }
            )
        ];
    }
}
