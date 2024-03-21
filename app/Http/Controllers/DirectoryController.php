<?php

namespace App\Http\Controllers;

use App\Events\APICacheDirectoriesUpdated;
use App\Events\APICachePrimeReactTreeDirectoriesUpdatedEvent;
use App\Events\APICacheReportsUpdated;
use App\Http\Resources\PrimeReactTree as PrimeReactTreeResource;
use App\Http\Resources\Directory as DirectoryResource;
use App\Models\Directory;
use App\Models\Report;
use App\Tools\PrimeReactTreeDirectoryTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Validator;

class DirectoryController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new Directory(), false);

        if ($request->exists('prime-react-tree')) {

            $primeReactTree = (new PrimeReactTreeDirectoryTools((new Directory)->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->get()))->getPrimeReactTree();
            return PrimeReactTreeResource::collection($primeReactTree->all());
        }

        return DirectoryResource::collection((new Directory)->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->paginate(1000));
    }

    public function destroy(Request $request, Directory $directory): JsonResponse
    {
        $this->genericAuthorize($request, $directory);

        $directory->delete();

        if (App::environment() !== 'testing') {

            APICacheDirectoriesUpdated::dispatch($directory->organization_id);
            APICachePrimeReactTreeDirectoriesUpdatedEvent::dispatch($directory->organization_id);
            APICacheReportsUpdated::dispatch($directory->organization_id);
        }

        return $this->successResponse(null, 'Directory deleted.');
    }

    public function moveReports(Request $request, Directory $directory): JsonResponse
    {
        $this->genericAuthorize($request, $directory, true, 'update');

        if ((int)$request->input('move-to-directory-id') >= 1 && (int)$request->input('move-to-directory-id') != $directory->id) {

            Report::where('directory_id', '=', $directory->id)
                ->update(['directory_id' => $request->get('move-to-directory-id')]);

            if (App::environment() !== 'testing') {

                APICacheReportsUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
                APICacheDirectoriesUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
            }

            return $this->successResponse(null, 'Reports moved');
        }

        return $this->errorResponse('Unable to move reports', 'Unable to move reports', [], 500);
    }

    public function show(Request $request, Directory $directory): JsonResponse
    {
        $this->genericAuthorize($request, $directory);

        return response()->json(new DirectoryResource($directory));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Directory::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            Directory::$rules,
            Directory::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the directory.', $validator->errors(), 422);
        }

        $directory = Directory::create($request->all());

        if (App::environment() !== 'testing') {

            APICacheDirectoriesUpdated::dispatch($directory->organization_id);
            APICachePrimeReactTreeDirectoriesUpdatedEvent::dispatch($directory->organization_id);
        }

        return $this->successResponse(new DirectoryResource($directory), 'The directory has been created.');
    }

    public function update(Request $request, Directory $directory): JsonResponse
    {
        $this->genericAuthorize($request, $directory);

        $validator = Validator::make(
            $request->all(),
            Directory::$rules,
            Directory::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the directory.', $validator->errors(), 422);
        }

        $directory->update($request->toArray());

        if (App::environment() !== 'testing') {

            APICacheDirectoriesUpdated::dispatch($directory->organization_id);
            APICachePrimeReactTreeDirectoriesUpdatedEvent::dispatch($directory->organization_id);
        }

        return $this->successResponse(new DirectoryResource($directory), 'The directory has been updated.');
    }
}
