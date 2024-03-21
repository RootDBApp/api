<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `report_caches`
            drop CONSTRAINT fk_rc_cache_job_id;
        ");

        DB::statement("
            ALTER TABLE `report_caches`
            ADD CONSTRAINT `fk_rc_cache_job_id` FOREIGN KEY (`cache_job_id`) REFERENCES cache_jobs(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
        ");
    }
};
