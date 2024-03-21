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

namespace App\Enums;

enum EnumFrequency: string
{
    case EVERY_FIFTEEN_MINUTES = 'everyFifteenMinutes';
    case EVERY_THIRTY_MINUTES = 'everyThirtyMinutes';
    case HOURLY_AT = 'hourlyAt';
    case DAILY_AT = 'dailyAt';
    case WEEKLY_ON = 'weeklyOn';
    case MONTHLY_ON = 'monthlyOn';
}
