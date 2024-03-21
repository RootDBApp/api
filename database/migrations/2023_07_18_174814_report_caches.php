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
        Schema::create('report_caches', function (Blueprint $table) {

            $table->unsignedInteger('id', true)->unique();

            $table->string('memcached_key')->nullable(false);

            $table->unsignedInteger('report_id')->nullable(false);
            $table->foreign('report_id', 'fk__rcfu_report_id')->references('id')->on('reports')->onDelete('cascade');

            $table->string('input_parameters_hash')->nullable(false);

            $table->unsignedInteger('report_data_view_id')->nullable(false);
            $table->foreign('report_data_view_id', 'fk__rcfu_report_data_view_id')->references('id')->on('report_data_views')->onDelete('cascade');

            $table->timestamps();
        });
    }
};
