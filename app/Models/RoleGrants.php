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

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RoleGrants
 *
 * @property int $id
 * @property int $role_id
 * @property string $route_name
 * @property string $route_label
 * @property boolean $index
 * @property boolean $store
 * @property boolean $show
 * @property boolean $update
 * @property boolean $destroy
 * @property boolean $ui_edit
 * @property boolean $organization_user_bound
 * @method static Builder|RoleGrants newModelQuery()
 * @method static Builder|RoleGrants newQuery()
 * @method static Builder|RoleGrants query()
 * @method static Builder|RoleGrants whereDestroy($value)
 * @method static Builder|RoleGrants whereId($value)
 * @method static Builder|RoleGrants whereIndex($value)
 * @method static Builder|RoleGrants whereOrganizationUserBound($value)
 * @method static Builder|RoleGrants whereRoleId($value)
 * @method static Builder|RoleGrants whereRouteLabel($value)
 * @method static Builder|RoleGrants whereRouteName($value)
 * @method static Builder|RoleGrants whereShow($value)
 * @method static Builder|RoleGrants whereStore($value)
 * @method static Builder|RoleGrants whereUpdate($value)
 * @method static Builder|RoleGrants whereUiEdit($value)
 * @mixin Eloquent
 */
class RoleGrants extends Model
{
    public const PERMISSION_EDIT = 'edit';

    public const RESOURCE_CONF_CONNECTOR = 'conf_connector';
    public const RESOURCE_CACHE = 'cache';
    public const RESOURCE_CATEGORY = 'category';
    public const RESOURCE_DRAFT = 'draft';
    public const RESOURCE_DRAFT_QUERIES = 'draft_queries';
    public const RESOURCE_DIRECTORY = 'directory';
    public const RESOURCE_GROUP = 'group';
    public const RESOURCE_ORGANIZATION = 'organization';
    public const RESOURCE_REPORT = 'report';
    public const RESOURCE_REPORT_DATA_VIEW = 'report_data_view';
    public const RESOURCE_REPORT_DATA_VIEW_JS = 'report_data_view_js';
    public const RESOURCE_REPORT_PARAMETER_INPUT = 'report_parameter_input';
    public const RESOURCE_REPORT_PARAMETER = 'report_parameter';
    public const RESOURCE_USER = 'user';
    public const RESOURCE_USER_PREFERENCES = 'user_preferences';
    public const RESOURCE_SYSTEM_INFO = 'system_info';
    public const RESOURCE_SERVICE_MESSAGE = 'service_message';

    public $timestamps = false;

    protected $table = 'role_grants';

    protected $casts = [
        'index'                   => 'boolean',
        'store'                   => 'boolean',
        'show'                    => 'boolean',
        'update'                  => 'boolean',
        'destroy'                 => 'boolean',
        'organization_user_bound' => 'boolean',
        'ui_edit'                 => 'boolean',
    ];
}
