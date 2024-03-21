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

    public function up()
    {
        DB::statement("
            ALTER TABLE `report_data_views`
            CHANGE `type`
                   `type` enum('1','2','3','4','5','6') NOT NULL COMMENT '1: table, 2: graph, 3: cron, 4: metric, 5: trend, 6: text';
        ");
        DB::statement("
            ALTER TABLE `report_data_view_libs`
            CHANGE `type`
                   `type` enum('1','2','3','4','5','6') NOT NULL COMMENT '1: table, 2: graph, 3: cron, 4: metric, 5: trend, 6: text';
        ");
        DB::statement("
            INSERT INTO `report_data_view_libs` (`type`, `name`, `url_website`, `default`)
            VALUES (6, 'RootDB', 'https://www.rootdb.fr/', 1);
        ");
        DB::statement("
            INSERT INTO `report_data_view_lib_versions` (`report_data_view_lib_id`, `major_version`, `version`, `url_documentation`, `default`)
            SELECT `id`, '1.x', '9.21.0', 'https://documentation.rootdb.fr', 0
            FROM `report_data_view_libs` where `type` = 6;
        ");
    }
};

