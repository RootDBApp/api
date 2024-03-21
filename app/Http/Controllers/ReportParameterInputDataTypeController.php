<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportParameterInputDataType as ReportParameterInputDataTypeResource;
use App\Models\ReportParameterInputDataType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportParameterInputDataTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ReportParameterInputDataTypeResource::collection((new ReportParameterInputDataType)->orderBy('name')->paginate(20));
    }
}
