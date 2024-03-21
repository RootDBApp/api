<?php

namespace App\Http\Controllers;

use App\Events\APICacheServiceMessagesUpdated;
use App\Http\Resources\ServiceMessage as ServiceMessageResource;
use App\Models\Organization;
use App\Models\ServiceMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Validator;

class ServiceMessagesController extends ApiController
{
    public function destroy(Request $request, ServiceMessage $serviceMessage)
    {
        $this->genericAuthorize($request, $serviceMessage);

        $serviceMessage->delete();

        if (App::environment() !== 'testing') {

            foreach ($serviceMessage->organizations as $organization) {

                APICacheServiceMessagesUpdated::dispatch($organization->id);
            }
        }

        return $this->successResponse(null, 'Service message deleted.');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new ServiceMessage(), false);

        /** @var int[] $organizations */
        $organizations = [];
        if (auth()->user()->is_super_admin) {

            $organizations = Organization::all()->map(
                function (Organization $organization) {
                    return $organization->id;
                })->all();
        } else {

            $organizations = (new Collection(auth()->user()->organizations))->map(
                function (Organization $organization) {
                    return $organization->id;
                })->all();

        }

        $query = ServiceMessage::with(['organizations'
                                       => $closure = function ($query) use ($organizations) {
                $query->whereIn('organization_id', $organizations);
            }])->whereHas('organizations', $closure

        );

        return ServiceMessageResource::collection(
            $query->orderBy('created_at')->paginate(5000)
        );
    }

    public function show(Request $request, ServiceMessage $serviceMessage)
    {
        $this->genericAuthorize($request, $serviceMessage);

        return response()->json(new ServiceMessageResource($serviceMessage));
    }

    public function store(Request $request)
    {
        $this->genericAuthorize($request, ServiceMessage::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ServiceMessage::$rules
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to store the service message.', $validator->errors(), 422);
        }

        $serviceMessage = ServiceMessage::create($request->all());
        $serviceMessage->organizations()->sync($request->get('organization_ids'));

        if (App::environment() !== 'testing') {

            foreach ($serviceMessage->organizations as $organization) {

                APICacheServiceMessagesUpdated::dispatch($organization->id);
            }
        }

        return $this->successResponse(new ServiceMessageResource($serviceMessage), 'The service message has been created.');
    }

    public function update(Request $request, ServiceMessage $serviceMessage)
    {
        $this->genericAuthorize($request, $serviceMessage);

        $validator = Validator::make(
            $request->all(),
            ServiceMessage::$rules,
            ServiceMessage::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to update the service message.', $validator->errors(), 422);
        }

        $serviceMessage->update($request->all());
        $serviceMessage->organizations()->sync($request->get('organization_ids'));

        if (App::environment() !== 'testing') {

            foreach ($serviceMessage->organizations as $organization) {

                APICacheServiceMessagesUpdated::dispatch($organization->id);
            }
        }

        return $this->successResponse(new ServiceMessageResource($serviceMessage), 'The service message has been updated.');
    }
}
