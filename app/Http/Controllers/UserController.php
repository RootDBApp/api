<?php

namespace App\Http\Controllers;

use App\Events\APICacheReportsUpdated;
use App\Events\APICacheUsersUpdated;
use App\Http\Resources\OrganizationUser as OrganizationUserResource;
use App\Http\Resources\User as UserResource;
use App\Models\ApiModel;
use App\Models\Draft;
use App\Models\OrganizationUser;
use App\Models\OrganizationUserRole;
use App\Models\Report;
use App\Models\RoleGrants;
use App\Models\User;
use App\Models\UserPreferences;
use App\Tools\CommonTranslation;
use App\Tools\TrackLoggedUserTools;
use App\Tools\UserTools;
use Arr;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends ApiController
{
    public function changeOrganizationUser(Request $request): JsonResponse
    {
        $organizationUserCheck = User::changeCurrentOrganizationUserLogged(auth()->user(), $request);
        if ($organizationUserCheck->result === false) {
            return $this->errorResponse(CommonTranslation::accessDenied, CommonTranslation::accessDenied, [], 401);
        }

        TrackLoggedUserTools::flushCacheUserFromUser(auth()->user());
        TrackLoggedUserTools::updateCacheUserFromUser(auth()->user());

        $request->request->add(
            [
                'organization'        => 1,
                'organization-users'  => 1,
                'conf-connectors'     => 1,
                'groups'              => 1,
                'roles'               => 1,
                'do-not-display-user' => 1,
                'ui-grants'           => 1,
                'user-preferences'    => 1
            ]);

        return response()->json(new OrganizationUserResource($organizationUserCheck->organizationUser));
    }

    public function resetPassword(Request $request, User $user): JsonResponse|Response
    {
        // @todo understand why it's not using UserPolicy::update() method and neither CommonPolicy::update()
        $this->genericAuthorize($request, $user, false, 'update');
        if (auth()->user()->id !== $user->id) {

            return Response::deny(CommonTranslation::unableToUpdateResource, 401);
        }

        if ($request->exists('password') && mb_strlen($request->input('password')) >= 8) {

            $updateFields['password'] = Hash::make($request->input('password'));
            $updateFields['reset_password'] = 0;
            $user->update($updateFields);
        } else {

            return Response::deny(CommonTranslation::unableToUpdateResource, 401);
        }

        return $this->successResponse(new UserResource($user), 'The user has been updated.');
    }

    public function testDevUser(Request $request): JsonResponse
    {
        return $this->successResponse(
            ['exists' => OrganizationUserRole::where('role_id', '=', 2)
                ->where('organization_user_id', '!=', 1)
                ->exists()
            ]
        );
    }

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new User(), false);

        return UserTools::getUserCollection();
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->genericAuthorize($request, $user);

        // We can't remove the super-admin User
        // We put the check here instead in UserPolicy::destroy because of CommonPolicy::before that allow everything for super-admin User.
        if ($user->id === 1) {

            abort(401, CommonTranslation::accessDenied . ' - ' . Response::deny(CommonTranslation::unableToDeleteResource));
        }


        $api_response_message_part_1 = $api_response_message_part_2 = '';
        // First, we remove this User from the current OrganizationUser.
        $organizationUser = $user->organizationUsers->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->first();
        if (is_a($organizationUser, 'App\Models\OrganizationUser')) {

            $api_response_message_part_1 = 'User removed from this organization.';
            $organizationUser->delete();
        }

        // Change Report owner.
        if ((int)$request->input('new-user-dev-id') >= 1 && (int)$request->input('new-user-dev-id') != $user->id) {

            Report::where('user_id', '=', $user->id)
                ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->update(['user_id' => $request->input('new-user-dev-id')]);

            Draft::where('user_id', '=', $user->id)
                ->update(['user_id' => $request->input('new-user-dev-id')]);

            $api_response_message_part_2 = ' Reports and dataViews\'s owner changed.';
        } // Delete User Reports.
        else {

            Report::where('user_id', '=', $user->id)
                ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->delete();
        }

        // And if this User is not in another Organization, we can completely remove it.
        if (OrganizationUser::where('user_id', '=', $user->id)->count() === 0) {

            $api_response_message_part_1 = 'User deleted.';
            $user->delete();
        }

        if (App::environment() !== 'testing') {

            APICacheUsersUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
            APICacheReportsUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
        }

        return $this->successResponse(null, $api_response_message_part_1 . $api_response_message_part_2);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->genericAuthorize($request, $user);

        // To display Role, Organization, Group in OrganizationUser resource.
        $request->request->add(['organization-users' => 1, 'roles' => 1, 'groups' => 1, 'do-not-display-user' => true]);

        return response()->json(new UserResource($user));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, User::make($request->toArray()));

        $validator = $this->_userValidate($request);
        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the user.', $validator->getMessageBag(), 422);
        }

        // Add into table `users`.
        $user = User::create(
            [
                'name'           => $request->input('name'),
                'email'          => $request->input('email'),
                'firstname'      => $request->input('firstname'),
                'lastname'       => $request->input('lastname'),
                'password'       => Hash::make($request->input('password')),
                'reset_password' => $request->input('reset_password'),
                'is_active'      => $request->input('is_active'),
            ]
        );

        // Add into table `organization_user`.
        $user->organizations()->attach($request->input('organization_id'));

        // Below, using first, because we _create_ a User, so there's only one OrganizationUser.
        // Add into table `organization_user_group`
        $user->organizationUsers()->first()->groups()->sync($request->input('group_ids'));

        // Add into table `organization_user_role`
        $user->organizationUsers()->first()->roles()->sync($request->input('role_ids'));

        if (App::environment() !== 'testing') {

            APICacheUsersUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
        }

        $user = $user->refresh();

        UserPreferences::create(
            [
                'organization_user_id' => $user->organizationUsers->firstOrFail()->id,
                'lang'                 => 'en',
                'theme'                => 'saga-blue'
            ]
        );

        $request->request->add(
            [
                'organization'        => 1,
                'organization-users'  => 1,
                'conf-connectors'     => 1,
                'groups'              => 1,
                'roles'               => 1,
                'do-not-display-user' => 1,
                'user-preferences'    => 1
            ]);

        return $this->successResponse(new UserResource($user), 'The user has been created.');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $userForGrantsCheck = User::make($request->toArray());
        $userForGrantsCheck->id = $user->id;
        $this->genericAuthorize($request, $userForGrantsCheck);

        // Only super-admin can update the super-admin User.
        // We can't change the name of the super-admin User.
        // We put the check here instead in UserPolicy::update because of CommonPolicy::before that allow everything for super-admin User.
        if (($user->id === 1 && auth()->user()->id !== 1) ||
            ($user->id === 1 && auth()->user()->id === 1 && $userForGrantsCheck->name !== 'super-admin')) {

            abort(401, CommonTranslation::accessDenied . ' - ' . Response::deny(CommonTranslation::unableToUpdateResource));
        }

        unset($userForGrantsCheck);

        $validator = $this->_userValidate($request, $user);
        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the user.', $validator->getMessageBag(), 422);
        }

        // Update table `users` - user profile
        $updateFields = [
            'email'     => $request->input('email'),
            'firstname' => $request->input('firstname'),
            'lastname'  => $request->input('lastname'),
        ];

        // Password change ?
        if ($request->exists('password') && mb_strlen($request->input('password')) >= 5) {

            $updateFields['password'] = Hash::make($request->input('password'));
        }

        if ($user->id === auth()->user()->id) {

            $user->update($updateFields);

        } // Update table `users` - admin
        else {

            $updateFields['name'] = $request->input('name');
            $updateFields['is_active'] = $request->input('is_active');
            $updateFields['reset_password'] = $request->input('reset_password');
            $user->update($updateFields);

            // Get the OrganizationUser
            $organizationUser = $user->organizationUsers()->where('organization_id', '=', $request->input('organization_id'))->first();
            if (is_null($organizationUser)) {

                return $this->errorResponse('Request error', 'Unable to update the user.', $validator->getMessageBag(), 422);
            }

            // We do not touch super-admin groups & roles, because it should always have all Roles, ang about Group, we
            // don't care, but just in case.
            if ($user->id !== 1) {

                // Update table `organization_user_group`.
                $organizationUser->groups()->sync($request->input('group_ids'));

                // Update table `organization_user_role`
                $organizationUser->roles()->sync($request->input('role_ids'));
            }
        }

        $user->refresh();

        if (App::environment() !== 'testing'
            && User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_USER)
        ) {

            APICacheUsersUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
        }

        $request->request->add(
            [
                'organization-users'  => 1,
                'conf-connectors'     => 1,
                'groups'              => 1,
                'roles'               => 1,
                'do-not-display-user' => 1,
                'user-preferences'    => 1
            ]);

        // When use is updating his own data, we have to make sure we add email and other stuff in the update response
        // because it will be used to update user session storage.
        if (auth()->user()->id === $user->id) {

            $request->request->add(
                [
                    'from-profil-form'    => 1,
                    'organization'        => 1,
                    'organization-user'   => 1,
                    'organization-id'     => auth()->user()->currentOrganizationLoggedUser->organization_id,
                ]
            );
        }

        return $this->successResponse(new UserResource($user), 'The user has been updated.');
    }

    private function _userValidate(Request $request, User $user = null): \Illuminate\Contracts\Validation\Validator
    {
        $rules = User::$rules;

        // Add attribute and check if :
        // - new user
        // - we are updating the username.
        if (is_null($user)
            || ($request->input('name') !== $user->name)) {

            $rules = Arr::add($rules, 'name', 'required|unique:users|between:2,255');
        }

        // Check password change.
        if (!is_null($user) && (!$request->exists('password') || mb_strlen($request->input('password')) <= 2)) {

            unset($rules['password']);
        }


        return Validator::make(
            $request->all(),
            $rules,
            ApiModel::$rule_messages
        );
    }
}
