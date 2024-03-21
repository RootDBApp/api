<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `reports`
            ADD COLUMN `num_parameter_sets_cached_by_jobs` INT(11) UNSIGNED DEFAULT NULL AFTER `has_job_cache`,
            ADD COLUMN `num_parameter_sets_cached_by_users` INT(11) UNSIGNED DEFAULT NULL AFTER `has_job_cache`
        ");
    }
};
