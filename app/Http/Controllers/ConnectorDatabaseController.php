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

namespace App\Http\Controllers;

use App\Models\ConnectorDatabase;
use App\Http\Resources\ConnectorDatabase as ConnectorDatabaseResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConnectorDatabaseController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ConnectorDatabaseResource::collection(ConnectorDatabase::all()->each(function (ConnectorDatabase $connectorDatabase) {

            $connectorDatabase->available = false;
            if ($connectorDatabase->id === 1 && extension_loaded('pdo_mysql')) {

                $connectorDatabase->available = true;
            } else if ($connectorDatabase->id === 2 && extension_loaded('pdo_pgsql')) {

                $connectorDatabase->available = true;
            }
        }));
    }
}
