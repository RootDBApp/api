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
                   `type` enum('1','2','3','4','5') NOT NULL COMMENT '1: table, 2: graph, 3: cron, 4: metric, 5: trend';
        ");
        DB::statement("
            ALTER TABLE `report_data_view_libs`
            CHANGE `type`
                   `type` enum('1','2','3','4','5') NOT NULL COMMENT '1: table, 2: graph, 3: cron, 4: metric, 5: trend';
        ");
    }
};

