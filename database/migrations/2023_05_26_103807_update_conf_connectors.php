<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('conf_connectors', function (Blueprint $table) {
            $table->addColumn('boolean', 'mysql_ssl_verify_server_cert')->default(false);
        });

        DB::statement("ALTER TABLE conf_connectors ADD COLUMN pgsql_ssl_mode ENUM('disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full') NOT NULL DEFAULT 'disable'");
    }
};
