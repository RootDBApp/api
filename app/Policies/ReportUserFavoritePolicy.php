<?php

namespace App\Policies;

use App\Models\ApiModel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class ReportUserFavoritePolicy extends CommonPolicy
{
    use HandlesAuthorization;

}
