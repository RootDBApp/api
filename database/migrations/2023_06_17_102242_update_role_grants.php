<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
        insert into role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound)
        values (1, 'cache-job', 'cache-job', 1, 0, 1, 0, 0, 0, 0),
               (2, 'cache-job', 'cache-job', 1, 1, 1, 1, 1, 1, 0),
               (3, 'cache-job', 'cache-job', 1, 0, 1, 0, 0, 0, 0)
        ");
    }
};
