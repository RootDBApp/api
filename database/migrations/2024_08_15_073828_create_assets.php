<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name', 255);

            $table->integer('organization_id')->unsigned()->nullable(false);
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            $table->enum('storage_type', ['database', 'filesystem', 'online'])->default('database')->nullable(false);
            $table->enum('data_type', ['file', 'string'])->nullable(true)->default('string')->comment('When storage type = database');
            $table->text('pathname')->nullable(true)->comment('When storage type = filesystem');
            $table->text('url')->nullable(true)->comment('When storage type = online');
            $table->timestamps();
        });

        DB::statement("
        insert into role_grants (role_id, route_name, route_label, `index`, store, `show`, `update`, destroy, ui_edit, organization_user_bound)
        values (1, 'asset', 'asset', 1, 0, 1, 0, 0, 1, 0),
               (2, 'asset', 'asset', 1, 1, 1, 1, 1, 1, 0),
               (3, 'asset', 'asset', 0, 0, 0, 0, 0, 1, 0)
        ");

        DB ::statement("alter table assets add column data longblob default null comment 'When storage type = database'");
    }
};
