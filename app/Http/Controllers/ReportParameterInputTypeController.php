<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportParameterInputType as ReportParameterInputTypeResource;
use App\Models\ReportParameterInputType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportParameterInputTypeController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        return ReportParameterInputTypeResource::collection((new ReportParameterInputType)->paginate(9999));
    }
}
