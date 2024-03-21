<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_caches', function (Blueprint $table) {
            $table->renameColumn('memcached_key', 'cache_key');
            $table->enum('cache_type', ['user', 'job'])->nullable(false);
        });
    }
};
