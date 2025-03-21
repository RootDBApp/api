<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

namespace App\Http\Controllers;

use App\Enums\EnumAssetSource;
use App\Enums\EnumStorageType;
use App\Events\APICacheAssetsUpdated;
use App\Http\Resources\Asset as AssetResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Validator;

class AssetController extends ApiController
{
    public function destroy(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset);

        if ($asset->pathname !== null) {

            Storage::delete($asset->pathname);
        }

        $asset->delete();

        if (App::environment() !== 'testing') {

            APICacheAssetsUpdated::dispatch($asset->organization_id);
        }

        return $this->successResponse(null, 'Asset deleted.');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new Asset(), false);

        return AssetResource::collection((new Asset())->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->paginate(1000));
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset);

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);
        return response()->json(new AssetResource($asset));
    }

    public function getJson(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset, true, 'show');

        if ($asset->storage_type === EnumStorageType::FILESYSTEM) {

            return response()->json(Storage::get($asset->pathname));
        }

        return response()->json($asset->data_content);
    }

    public function getCSV(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset, true, 'show');

        $csv = [];
        if ($asset->storage_type === EnumStorageType::FILESYSTEM) {

            $csv = file(Storage::path($asset->pathname));
        } else {

            $csv = explode("\n", $asset->data_content);
        }

        $col_headers = [];
        $output = [];

        foreach ($csv as $line_index => $line) {

            $col_values = explode(',', $line);

            if ($line_index > 0) {

                $new_line = [];
                foreach ($col_values as $col_index => $value) {

                    $new_line[$col_headers[$col_index]] = str_replace(["\n", "\r", "\r\n"], null, $value);
                }

                $output[] = $new_line;

            } // Get header columns value.
            else {

                foreach ($col_values as $col_index => $value) {

                    $col_headers[$col_index] = str_replace(["\n", "\r", "\r\n"], null, $value);
                }
            }
        }

        return response()->json(json_encode($output));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Asset::make($request->toArray()));

        $messageBag_or_bool = $this->checks($request);
        if (is_a($messageBag_or_bool, 'Illuminate\Support\MessageBag')) {

            return $this->errorResponse('Request error', 'Unable to create the asset.', $messageBag_or_bool, 422);
        }


        $asset = Asset::create($request->toArray());

        if (App::environment() !== 'testing') {

            APICacheAssetsUpdated::dispatch($asset->organization_id);
        }

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);

        return $this->successResponse(new AssetResource($asset), 'The asset has been created successfully.');
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset);

        $messageBag_or_bool = $this->checks($request);
        if (is_a($messageBag_or_bool, 'Illuminate\Support\MessageBag')) {

            return $this->errorResponse('Request error', 'Unable to update the asset.', $messageBag_or_bool, 422);
        }

        $asset->update($request->toArray());

        if (App::environment() !== 'testing') {

            APICacheAssetsUpdated::dispatch($asset->organization_id);
        }

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);

        return $this->successResponse(new AssetResource($asset), 'The asset has been updated successfully.');
    }

    public function upload(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset, true, 'show');

        if ($asset->storage_type === EnumStorageType::FILESYSTEM) {

            if ($asset->pathname !== null) {

                Storage::delete($asset->pathname);
            }

            $asset_path = $request->file('asset_file')->storeAs(
                'assets', $asset->id . '_' . $request->file('asset_file')->getClientOriginalName()
            );

            $asset->update(['pathname' => $asset_path]);
        } else if ($asset->storage_type === EnumStorageType::DATABASE) {

            $asset->update(
                [
                    'data_content' => $request->file('asset_file')->getContent()
                ]);
        }

        if (App::environment() !== 'testing') {

            APICacheAssetsUpdated::dispatch($asset->organization_id);
        }

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);

        return $this->successResponse(new AssetResource($asset), 'The asset has been uploaded successfully.');
    }

    public function download(Request $request, Asset $asset): StreamedResponse
    {
        $this->genericAuthorize($request, $asset, true, 'show');

        return Storage::download($asset->pathname);
    }

    private function checks(Request $request): MessageBag|bool
    {
        // First basic check.
        $validator = Validator::make(
            $request->all(),
            Asset::rules(),
            Asset::$rule_messages
        );

        if ($validator->fails()) {

            return $validator->errors();
        }

        // Checks depending on asset storage type.
        switch (EnumStorageType::from($request->get('storage_type'))) {

            case EnumStorageType::DATABASE:
            {
                if ($request->get('asset_source') === EnumAssetSource::STRING) {

                    $validator->addRules(
                        [
                            'data_content' => 'required',
                        ]);
                }

                break;
            }

            case EnumStorageType::FILESYSTEM:
                break;
            case EnumStorageType::ONLINE:
            default:
            {
                return new MessageBag(['Unable to recognize asset\'s storage type.']);
            }
        }

        if ($validator->fails()) {

            return $validator->errors();
        }

        return true;
    }
}
