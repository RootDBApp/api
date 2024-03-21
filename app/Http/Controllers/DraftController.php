<?php

namespace App\Http\Controllers;

use App\Http\Resources\Draft as DraftResource;
use App\Models\ConfConnector;
use App\Models\DraftQueries;
use App\Models\Draft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DraftController extends ApiController
{
    public function index(Request $request, ConfConnector $confConnector): JsonResponse
    {
        $this->genericAuthorize($request, new Draft());

        // First check if we have a QuerieDraft for this OrganizationUser, and ConfConnector and if not...
        $draft = Draft::where('conf_connector_id', '=', $confConnector->id)
            ->where('user_id', '=', auth()->user()->id)
            ->with('draftQueries')
            ->first();

        // .. we create it now.
        if (is_null($draft)) {

            $draft = Draft::create(
                [
                    'user_id'           => auth()->user()->id,
                    'conf_connector_id' => $confConnector->id,
                    'name'              => 'Draft #1',
                ]);

            DraftQueries::create(
                [
                    'draft_id' => $draft->id,
                    'queries'   => '-- CTRL+D                                     - to comment line(s)
-- CTRL+ENTER  (CMD+ENTER @mac)               - run the queries
-- CTRL+SHIFT-ENTER  (CMD+SHIFT-ENTER @mac)   - run the selected queries
-- ALT+SHIFT+V                                - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )'
                ]);

            $draft->fresh(['draftQueries']);
        }


        return response()->json(new DraftResource($draft));
    }
}
