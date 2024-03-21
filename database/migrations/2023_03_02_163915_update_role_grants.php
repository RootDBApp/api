<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("INSERT INTO `rootdb-api`.role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound) VALUES (1, 'report-user-favorite', 'report-user-favorite', 1, 1, 1, 1,1, 0, 0);");
        DB::statement("INSERT INTO `rootdb-api`.role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound) VALUES (2, 'report-user-favorite', 'report-user-favorite', 1, 1, 1, 1,1, 0, 0);");
        DB::statement("INSERT INTO `rootdb-api`.role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound) VALUES (3, 'report-user-favorite', 'report-user-favorite', 1, 1, 1, 1,1, 0, 0);");
    }
};
