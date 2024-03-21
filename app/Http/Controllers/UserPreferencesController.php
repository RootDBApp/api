<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserPreferences as UserPreferencesResource;
use App\Models\UserPreferences;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class UserPreferencesController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->exceptionResponse('Request error', "User's preferences listing is not authorized.", null, 401);
    }

    public function destroy(): JsonResponse
    {
        return $this->exceptionResponse('Request error', "User's preferences deletion is not authorized.", null, 401);
    }

    public function show(Request $request, int $userPreferencesId): UserPreferencesResource
    {
        $userPreferences = UserPreferences::findOrFail($userPreferencesId);
        $this->genericAuthorize($request, $userPreferences);

        return UserPreferencesResource::make($userPreferences);
    }

    public function store(): JsonResponse
    {
        return $this->exceptionResponse('Request error', "User's preferences direct creation is not authorized.", null, 401);
    }

    public function update(Request $request, int $userPreferencesId): JsonResponse
    {
        $userPreferences = UserPreferences::findOrFail($userPreferencesId);

        $this->genericAuthorize($request, $userPreferences);

        $validator = Validator::make(
            $request->all(),
            UserPreferences::$rules,
            UserPreferences::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the user\'s preferences.', $validator->errors(), 422);
        }

        $userPreferences->update($request->all());

        session()->put('locale', $userPreferences->lang);
        App::setLocale($userPreferences->lang);

        return $this->successResponse(new UserPreferencesResource($userPreferences), "The user's preferences has been updated.");
    }
}
