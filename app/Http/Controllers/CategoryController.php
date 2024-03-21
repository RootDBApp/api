<?php

namespace App\Http\Controllers;

use App\Events\APICacheCategoriesUpdated;
use App\Events\APICacheReportsUpdated;
use App\Http\Resources\Category as CategoryResource;
use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Validator;

class CategoryController extends ApiController
{
    public function destroy(Request $request, Category $category): JsonResponse
    {
        $this->genericAuthorize($request, $category);

        $category->delete();

        if (App::environment() !== 'testing') {

            APICacheCategoriesUpdated::dispatch($category->organization_id);
            APICacheReportsUpdated::dispatch($category->organization_id);
        }

        return $this->successResponse(null, 'Category deleted.');
    }

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new Category(), false);

        return CategoryResource::collection(
            (new Category())
                ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->paginate(5000)
        );
    }

    public function moveReports(Request $request, Category $category): JsonResponse
    {
        $this->genericAuthorize($request, $category, true, 'update');

        if ((int)$request->input('move-to-category-id') >= 1 && (int)$request->input('move-to-category-id') != $category->id) {

            Report::where('category_id', '=', $category->id)
                ->update(['category_id' => $request->get('move-to-category-id')]);

            if (App::environment() !== 'testing') {

                APICacheReportsUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
                APICacheCategoriesUpdated::dispatch(auth()->user()->currentOrganizationLoggedUser->organization_id, $request);
            }

            return $this->successResponse(null, 'Reports moved');
        }

        return $this->errorResponse('Unable to move reports', 'Unable to move reports', [], 500);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        $this->genericAuthorize($request, $category);

        return response()->json(new CategoryResource($category));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Category::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            Category::$rules
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to store the category.', $validator->errors(), 422);
        }

        $category = Category::create($request->all());

        if (App::environment() !== 'testing') {

            APICacheCategoriesUpdated::dispatch($category->organization_id);
        }

        return $this->successResponse(new CategoryResource($category), 'The category has been created.');
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->genericAuthorize($request, $category);

        $validator = Validator::make(
            $request->all(),
            Category::$rules,
            Category::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to update the category.', $validator->errors(), 422);
        }

        $category->update($request->all());

        if (App::environment() !== 'testing') {

            APICacheCategoriesUpdated::dispatch($category->organization_id);
        }

        return $this->successResponse(new CategoryResource($category), 'The category has been updated.');
    }
}
