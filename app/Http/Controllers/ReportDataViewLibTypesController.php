<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportDataViewLibTypes as ReportDataViewLibTypesResource;
use App\Models\ReportDataViewLibTypes;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportDataViewLibTypesController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return ReportDataViewLibTypesResource::collection((new ReportDataViewLibTypes)->paginate(2000));
    }
}
