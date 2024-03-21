<?php

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
