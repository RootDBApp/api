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
        Schema::create('cache_jobs', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('report_id')->nullable(false);
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
            $table->enum('frequency', ['everyFifteenMinutes', 'everyThirtyMinutes', 'hourlyAt', 'dailyAt', 'weeklyOn', 'monthlyOn'])->default('hourlyAt')->nullable(false);
            $table->unsignedTinyInteger('at_minute')->default('0')->nullable();
            $table->time('at_time')->default('00:00:01')->nullable();
            $table->enum('at_weekday', [1, 2, 3, 4, 5, 6, 7])->default(1)->nullable();
            $table->unsignedTinyInteger('at_day')->default(1)->nullable();
            $table->unsignedInteger('ttl')->default(3600)->nullable(false)->comment('In seconds.');
            $table->dateTime('last_run')->nullable();
            $table->unsignedInteger('last_run_duration')->nullable()->comment('In seconds.');
            $table->timestamps();
        });
    }
};
