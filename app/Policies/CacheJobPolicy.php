<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class CacheJobPolicy extends CommonPolicy
{
    use HandlesAuthorization;
}
