<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        DB::statement("
        insert into role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound)
        values (1, 'system-info', 'system-info', 1, 0, 1, 0, 0, 1, 0),
               (2, 'system-info', 'system-info', 1, 0, 1, 0, 0, 1, 0),
               (3, 'system-info', 'system-info', 0, 0, 0, 0, 0, 1, 0)
        ");
    }
};
