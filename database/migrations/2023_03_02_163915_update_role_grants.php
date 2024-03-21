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

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("INSERT INTO `rootdb-api`.role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound) VALUES (1, 'report-user-favorite', 'report-user-favorite', 1, 1, 1, 1,1, 0, 0);");
        DB::statement("INSERT INTO `rootdb-api`.role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound) VALUES (2, 'report-user-favorite', 'report-user-favorite', 1, 1, 1, 1,1, 0, 0);");
        DB::statement("INSERT INTO `rootdb-api`.role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound) VALUES (3, 'report-user-favorite', 'report-user-favorite', 1, 1, 1, 1,1, 0, 0);");
    }
};
