<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->boolean('has_user_cache')->default(false)->after('has_cache');
            $table->boolean('has_job_cache')->default(false)->after('has_user_cache');
        });
    }
};
