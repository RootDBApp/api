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

use App\Enums\EnumStorageDataType;
use App\Enums\EnumStorageType;
use App\Http\Resources\Asset as AssetResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\MessageBag;
use Validator;

class AssetController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new Asset(), false);

        return AssetResource::collection((new Asset())->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->paginate(1000));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Asset::make($request->toArray()));

        $messageBag_or_bool = $this->checks($request);
        if (is_a($messageBag_or_bool, 'Illuminate\Support\MessageBag')) {

            return $this->errorResponse('Request error', 'Unable to create the asset.', $messageBag_or_bool, 422);
        }


        $asset = Asset::create($request->toArray());

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);

        return $this->successResponse(new AssetResource($asset), 'The asset has been created successfully.');
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset);

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);
        return response()->json(new AssetResource($asset));
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset);

        $messageBag_or_bool = $this->checks($request);
        if (is_a($messageBag_or_bool, 'Illuminate\Support\MessageBag')) {

            return $this->errorResponse('Request error', 'Unable to update the asset.', $messageBag_or_bool, 422);
        }

        $asset->update($request->toArray());

        // To get all data.
        $request->request->add(['complete_resource' => 1,]);

        return $this->successResponse(new AssetResource($asset), 'The asset has been updated successfully.');
    }

    public function destroy(Request $request, Asset $asset): JsonResponse
    {
        $this->genericAuthorize($request, $asset);

        $asset->delete();

        return $this->successResponse(null, 'Asset deleted.');
    }

    public function checks(Request $request): MessageBag|bool
    {
        // First basic check.
        $validator = Validator::make(
            $request->all(),
            Asset::rules(),
            Asset::$rule_messages
        );

        if ($validator->fails()) {

            $validator->errors();
        }

        // Checks depending on asset storage type.
        switch (EnumStorageType::from($request->get('storage_type'))) {

            case EnumStorageType::DATABASE:
            {
                if (EnumStorageDataType::from($request->get('data_type')) === EnumStorageDataType::STRING) {

                    $validator->addRules(
                        [
                            'data_content' => 'required',
                        ]);
                }

                break;
            }

            case EnumStorageType::FILESYSTEM:
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