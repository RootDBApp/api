<?php

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

