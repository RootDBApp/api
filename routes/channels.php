<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Public channels
|--------------------------------------------------------------------------
*/

// Used when a report is displayed from another host.
Broadcast::channel('public.user.{user_id}', function ($public_user_id) {

    return true;
},                 ['guards' => ['sanctum']]);

/*
|--------------------------------------------------------------------------
| Private channels
|--------------------------------------------------------------------------
*/

// * send results to view / reports with auto-refresh enabled.
// * send updated API data like groups, reports, view, parameters & co.
Broadcast::channel('organization.{organization_id}', function (User $user, int $organization_id) {

    return $user->currentOrganizationLoggedUser->organization_id === $organization_id;
},                 ['guards' => ['sanctum']]);

// * send results to views
// * send results to SQL console (for dev only)
Broadcast::channel('user.{web_socket_session_id}', function (User $user, string $web_socket_session_id) {


    if (session()->get('currentOrganizationLoggedUser') !== null) {

        return unserialize(session()->get('currentOrganizationLoggedUser'))->web_socket_session_id === $web_socket_session_id;
    }

    return false;
},                 ['guards' => ['sanctum']]);
