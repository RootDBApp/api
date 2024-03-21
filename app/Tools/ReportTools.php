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

namespace App\Tools;

use App\Http\Resources\LightReport as APICacheReportResource;
use App\Models\Report;
use App\Models\ReportParameter;
use App\Models\Role;
use App\Models\RoleGrants;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportTools
{
    /**
     * Check groups & users permissions
     *
     * @param Report $report
     * @param User $user
     * @return bool
     */
    public static function checkShowPermissionByRestrictionReport(Report $report, User $user): bool
    {
        //
        // Check groups restrictions
        //
        $gotGroupRestriction = false;
        $allowedByGroup = false;
        if (count($report->allowedGroups) > 0) {

            $gotGroupRestriction = true;
            $organizationUserGroupIds = $user->currentOrganizationLoggedUser->groups;

            foreach ($report->allowedGroups as $allowedReportGroup) {

                if (in_array($allowedReportGroup->group_id, $organizationUserGroupIds)) {

                    $allowedByGroup = true;
                    break;
                }
            }
        }

        //
        // Check users restrictions
        //
        $gotUserRestriction = false;
        $allowedByUser = false;
        if (count($report->allowedUsers) > 0) {

            $gotUserRestriction = true;
            foreach ($report->allowedUsers as $allowedUser) {

                if ($allowedUser->user_id === $user->id) {

                    $allowedByUser = true;
                    break;
                }
            }
        }

        //
        // Final checks
        //
        $allowed = false;

        if ($gotGroupRestriction === false && $gotUserRestriction === false) {
            $allowed = true;
        } elseif ($gotGroupRestriction === true && $allowedByGroup === true) {
            $allowed = true;
        } elseif ($gotUserRestriction === true && $allowedByUser === true) {
            $allowed = true;
        }

        return $allowed;
    }

    /**
     * Check if report visible for User, in the right Organization & co.
     *
     * @param Report $report
     * @param User $user
     * @return bool
     */
    public static function checkShowPermissionByReport(Report $report, User $user): bool
    {
        // 1 - check that we are asking a report from the current organization.
        if ($report->organization_id !== $user->currentOrganizationLoggedUser->organization_id) {

            return false;
        }

        // 2 - check if dev
        if (User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_REPORT)) {

            return true;
        }

        if ($report->is_visible == 0) {

            return false;
        }

        // 3 - check when there's no allowed users / groups.
        if ($report->allowedGroups->count() === 0 &&
            $report->allowedUsers->count() === 0
        ) {

            return true;
        }

        // 4 - check when there are allowed users.
        // 5 - check when there are allowed groups.
        return ReportTools::checkShowPermissionByRestrictionReport($report, $user);
    }

    public static function flattenInputParameters(array $inputParameters): string
    {
        $parameters_flattened = '';
        foreach ($inputParameters as $parameter) {

            if (is_object($parameter)) {

                $parameters_flattened .= $parameter->name . ': ' . $parameter->value . ' | ';
            } else {

                $parameters_flattened .= $parameter['name'] . ': ' . $parameter['value'] . ' | ';
            }
        }

        return substr($parameters_flattened, 0, strlen($parameters_flattened) - 3);
    }

    /**
     * @param Request $request
     *                - for-organization-id - to override currentOrganizationLoggedUser.organization_id
     * @param User $user
     * @return AnonymousResourceCollection
     */
    public static function getReportCollection(Request $request, User $user): AnonymousResourceCollection
    {
        $organizationId = $user->currentOrganizationLoggedUser->organization_id;
        if ($request->exists('for-organization-id')) {

            $organizationId = $request->input('for-organization-id');
        }

        $reportsCollection = Report::where('organization_id', '=', $organizationId)
            ->with('category')
            ->with('directory')
            ->with('allowedGroups')
            ->with('favoriteUsers')
            // Include resource for report's restrictions.
            ->when($user->currentOrganizationLoggedUser->roles,
                function (Builder $query) use ($request) {

                    // If user cannot edit report (not a dev), check report's restrictions.
                    if (!User::searchIfLoggedUserHasRole(Role::DEV)) {

                        return $query
                            ->where('is_visible', '=', '1')
                            ->with('allowedUsers');
                    }

                    return $query;
                })
            ->get();


        // If user can not edit report (not a dev), check report's restrictions.
        if (!User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_REPORT)) {

            return APICacheReportResource::collection($reportsCollection->filter(function (Report $report) use ($user) {

                return ReportTools::checkShowPermissionByRestrictionReport($report, $user);
            }));
        }

        return APICacheReportResource::collection($reportsCollection);
    }

    /**
     * Order by ascending input parameter names.
     *
     * @param array $inputParameters
     * @return void
     */
    public static function orderInputParameters(array &$inputParameters): void
    {
        array_multisort(array_column($inputParameters, 'name'), SORT_ASC, $inputParameters);
    }

    public static function orderInputParametersValues(HasMany $parameters, array $inputParameters): array
    {
        /** @var ReportParameter $parameter */
        foreach ($parameters->get() as $parameter) {

            $currentInputParameterIndex = 0;
            foreach ($inputParameters as $index => $inputParameter) {

                if ($inputParameter['name'] === $parameter->variable_name) {

                    $currentInputParameterIndex = $index;
                    break;
                }
            }

            switch ($parameter->parameterInput->parameterInputType->name) {

                case 'multi-select':

                    $inputParameterValues = explode(',', $inputParameters[$currentInputParameterIndex]['value']);
                    sort($inputParameterValues);
                    $inputParameters[$currentInputParameterIndex]['value'] = implode(',', $inputParameterValues);
                    break;

                default:
                    break;
            }
        }

        return $inputParameters;
    }

    public static function SQLQueryCommentCleanup(string $query): string|null
    {
        $query_cleaned_up = preg_replace('`--.*`m', null, $query);
        return preg_match("/[a-z]/i", $query_cleaned_up) ? $query_cleaned_up : null;
    }
}
