<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        DB::statement("
            ALTER TABLE `report_data_views`
            CHANGE `description_display_type`
                   `description_display_type` tinyint(3) unsigned COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 1 COMMENT '0: when no description, 1: overlay, 2: text';
        ");
    }
};
