<?php

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

