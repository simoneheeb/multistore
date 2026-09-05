<?php

namespace Modules\Category\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Category\Repositories\CategoryRepository;
use Modules\Category\Requests\StoreCategoryRequest;
use Modules\Category\Requests\UpdateCategoryRequest;
use Modules\Category\Resources\CategoryResource;
use Modules\Category\Resources\CategoryTreeResource;
use Modules\Product\Resources\ProductResource;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepository $repository) {}

    /**
     * Flat listing. `all=true` returns everything (admin selects, sitemaps),
     * otherwise a paginated slice.
     */
    public function index(Request $request): JsonResponse
    {
        $all = $request->boolean('all');
        $perPage = $request->integer('perPage') ?: 15;

        $categories = $this->repository->findAll($all, $perPage);

        if ($all) {
            return response()->json([
                'data' => CategoryResource::collection($categories),
                'status' => true,
            ]);
        }

        return response()->json([
            'data' => CategoryResource::collection($categories),
            'meta' => $this->paginationMeta($categories),
            'status' => true,
        ]);
    }

    /**
     * The nested category tree used by the site navigation and by the admin
     * parent picker.
     */
    public function tree(Request $request): JsonResponse
    {
        // Admins can ask for inactive nodes too; the public site never does.
        $onlyActive = ! ($request->user()?->is_admin && $request->boolean('withInactive'));

        $tree = $this->repository->tree($onlyActive, $request->query('brand_id'));

        return response()->json([
            'data' => CategoryTreeResource::collection($tree),
            'status' => true,
        ]);
    }

    /**
     * Flat, depth-annotated options for a <select>. `exclude` removes a
     * subtree so a category cannot be re-parented under its own descendant.
     */
    public function options(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->repository->options($request->query('exclude')),
            'status' => true,
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if ($request->hasFile('logo')) {
            $payload['logo'] = $request->file('logo')->store('categories', 'outside');
        }

        $created = $this->repository->create($payload);

        return response()->json([
            'message' => $created ? 'Category created successfully.' : 'The category could not be created.',
            'data' => $created ? new CategoryResource($created) : null,
            'status' => (bool) $created,
        ], $created ? 201 : 500);
    }

    /**
     * Public detail view. `{slug}` is the parent (or a standalone category)
     * and `{childSlug}` an optional direct child, so /categories/a/b resolves
     * "b" nested under "a".
     *
     * The response also carries the ancestor chain (breadcrumb) and the
     * products of the whole branch, which is what a visitor expects when
     * opening a parent node.
     */
    public function show(Request $request, string $slug, ?string $childSlug = null): JsonResponse
    {
        $category = $this->repository->findBySlug($slug, $childSlug);

        $breadcrumb = $this->repository->breadcrumb($category);

        return response()->json([
            'data' => new CategoryResource($category),
            'breadcrumb' => CategoryTreeResource::collection($breadcrumb),
            'products' => ProductResource::collection(
                $this->repository->branchProducts($category, $request->integer('limit') ?: 24)
            ),
            'status' => true,
        ]);
    }

    public function findById(string $id): JsonResponse
    {
        return response()->json([
            'data' => new CategoryResource($this->repository->findById($id)),
            'status' => true,
        ]);
    }

    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        $payload = $request->validated();
        $category = $this->repository->findById($id);

        if ($request->hasFile('logo')) {
            // Replace rather than accumulate: drop the previous file first.
            if (! empty($category->logo)) {
                Storage::disk('outside')->delete($category->logo);
            }

            $payload['logo'] = $request->file('logo')->store('categories', 'outside');
        }

        $updated = $this->repository->update($payload, $id);

        return response()->json([
            'message' => $updated ? 'Category updated successfully.' : 'The category could not be updated.',
            'status' => $updated,
        ], $updated ? 200 : 422);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $category = $this->repository->findById($id);

            if ($category->logo) {
                Storage::disk('outside')->delete($category->logo);
            }

            $deleted = $this->repository->delete($category);

            return response()->json([
                'message' => $deleted ? 'Category deleted.' : 'The category could not be deleted.',
                'status' => $deleted,
            ], $deleted ? 200 : 500);
        } catch (\Throwable $th) {
            Log::error("Category deletion failed for id {$id}: ".$th->getMessage());

            return response()->json([
                'message' => 'Something went wrong. Please try again.',
                'status' => false,
            ], 500);
        }
    }

    /**
     * Pagination envelope shared by every paginated listing in this module.
     */
    protected function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'path' => $paginator->path(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }
}
