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
        DB::statement("INSERT INTO `rootdb-api`.report_data_view_libs (id,type,name,url_website,`default`) VALUES (8,2,'Apache ECharts','https://echarts.apache.org',0);");
        DB::statement("INSERT INTO `rootdb-api`.report_data_view_lib_versions (id,report_data_view_lib_id,major_version,version,url_documentation) VALUES (9,8,'5.x', '5.5.1', 'https://echarts.apache.org/handbook/en/get-started/');");
        DB::statement("INSERT INTO `rootdb-api`.report_data_view_lib_types (id,report_data_view_lib_version_id,label,name) VALUES (32,9,'bar', 'Bar');");
    }
};
