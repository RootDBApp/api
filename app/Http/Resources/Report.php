<?php

namespace App\Http\Resources;

use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Directory as DirectoryResource;
use App\Http\Resources\Organization as OrganizationResource;
use App\Http\Resources\ConfConnector as ConfConnectorResource;
use App\Http\Resources\ReportGroup as ReportGroupResource;
use App\Http\Resources\ReportUser as ReportUserResource;
use App\Http\Resources\ReportParameter as ReportParameterResource;
use App\Http\Resources\User as UserResource;
use App\Models\RoleGrants;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Report */
class Report extends JsonResource
{
    public function toArray($request): array
    {
        $loggedUserHasRoleDev = User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_REPORT);

        return [
            'id'                                 => $this->id,
            'instance_id'                        => $request->get('instanceId'),
            'allowed_groups'                     => $this->when($loggedUserHasRoleDev,
                function () {
                    return ReportGroupResource::collection($this->allowedGroups);
                }
            ),
            'allowed_users'                      => $this->when($loggedUserHasRoleDev,
                function () {
                    return ReportUserResource::collection($this->allowedUsers);
                }
            ),
            'category'                           => $this->when($loggedUserHasRoleDev,
                function () use ($request) {

                    return CategoryResource::make($this->category);
                }
            ),
            'conf_connector'                     => $this->when($loggedUserHasRoleDev,
                function () {
                    return ConfConnectorResource::make($this->confConnector);
                }
            ),
            'conf_connector_id'                  => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->conf_connector_id;
                }
            ),
            'created_at'                         => $this->created_at,
            'description'                        => $this->description,
            'description_listing'                => $this->description_listing,
            'directory'                          => $this->when($loggedUserHasRoleDev,
                function () {
                    return DirectoryResource::make($this->directory);
                }
            ),
            'favorite'                           => count($this->favoriteUsers) > 0,
            'has_cache'                          => $this->has_cache,
            'has_job_cache'                      => $this->has_job_cache,
            'has_user_cache'                     => $this->has_user_cache,
            'has_data_views'                     => $this->has_data_views,
            'has_parameters'                     => $this->has_parameters,
            'is_visible'                         => $this->is_visible,
            'on_queue'                           => $this->on_queue,
            'name'                               => $this->name,
            'num_parameter_sets_cached_by_jobs'  => $this->num_parameter_sets_cached_by_jobs,
            'num_parameter_sets_cached_by_users' => $this->num_parameter_sets_cached_by_users,
            'organization'                       => $this->when($loggedUserHasRoleDev,
                function () {
                    return OrganizationResource::make($this->organization);
                }
            ),
            'organization_id'                    => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->organization_id;
                }
            ),
            'parameters'                         => $this->when(
                (int)$request->get('parameters') === 1,
                function () {
                    return ReportParameterResource::collection($this->parameters);
                }
            ),
            'public_access'                      => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->public_access;
                }
            ),
            'public_authorized_referers'         => $this->when($loggedUserHasRoleDev,
                function () {
                    return (mb_strlen($this->public_authorized_referers) > 3) ? explode(',', $this->public_authorized_referers) : [];
                }
            ),
            'public_security_hash'               => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->public_security_hash;
                }
            ),
            'query_cleanup'                      => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->query_cleanup;
                }
            ),
            'query_init'                         => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->query_init;
                }
            ),
            'title'                              => $this->title,
            'updated_at'                         => $this->updated_at,
            'user'                               => UserResource::make($this->user),
            'user_id'                            => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->user_id;
                }
            ),
        ];
    }
}
