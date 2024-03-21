<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `report_caches`
            ADD COLUMN `cache_job_id` INT(11) UNSIGNED DEFAULT NULL AFTER `id`;
        ");
        DB::statement("
            ALTER TABLE `report_caches`
            ADD CONSTRAINT `fk_rc_cache_job_id` FOREIGN KEY (`cache_job_id`) REFERENCES cache_jobs(`id`);
        ");
    }
};
