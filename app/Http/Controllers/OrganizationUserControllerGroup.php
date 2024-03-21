<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUserGroup;
use Illuminate\Http\JsonResponse;

class OrganizationUserControllerGroup extends Controller
{

    public function index(): JsonResponse
    {
        return response()->json(
            OrganizationUserGroup::with('group')->with('organizationUser')->get()
        );
    }
}
