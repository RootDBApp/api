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

namespace App\Tools;

class CommonTranslation
{
    public const accessDenied = 'Access denied';
    public const unableToCreateResource = 'You are not granted to create a resource.';
    public const unableToDeleteResource = 'You are not granted to delete this resource.';
    public const unableToExecuteThisAction= 'You are not granted to execute this action.';
    public const unableToListResources = 'You are not granted to list resources.';
    public const unableToUpdateResource = 'You are not granted to update this resource.';
    public const unableToViewResource = 'You are not granted to view this resource.';
}
