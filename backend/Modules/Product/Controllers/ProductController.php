<?php

namespace Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Requests\StoreProductRequest;
use Modules\Product\Requests\UpdateProductRequest;
use Modules\Product\Resources\ProductResource;

class ProductController extends Controller
{
    public function __construct(protected ProductRepository $repository) {}

    /**
     * Catalogue listing.
     *
     * Query parameters: all, paginate, perPage, page, latest, featured,
     * category_id, brand_id, is_new, search, sort.
     */
    public function index(Request $request): JsonResponse
    {
        // Shortcut endpoints used by the landing page.
        if ($request->boolean('latest')) {
            return response()->json([
                'data' => ProductResource::collection(
                    $this->repository->latestProducts($request->integer('limit') ?: 10)
                ),
                'status' => true,
            ]);
        }

        if ($request->boolean('featured')) {
            return response()->json([
                'data' => ProductResource::collection(
                    $this->repository->featuredProducts($request->integer('limit') ?: 8)
                ),
                'status' => true,
            ]);
        }

        $filters = [
            // Anonymous visitors only ever see published products; the admin
            // table needs the drafts too.
            'only_active' => ! ($request->user()?->is_admin),
            'category_id' => $request->query('category_id'),
            'brand_id' => $request->query('brand_id'),
            'is_new' => $request->boolean('is_new'),
            'search' => $request->query('search'),
            'sort' => $request->query('sort', 'latest'),
        ];

        $all = $request->boolean('all');
        $perPage = min($request->integer('perPage') ?: 15, 60);

        $products = $this->repository->findAll($all, $perPage, $filters);

        if ($all) {
            return response()->json([
                'data' => ProductResource::collection($products),
                'status' => true,
            ]);
        }

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'path' => $products->path(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
            'status' => true,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('featured_img')) {
            $data['featured_img'] = $request->file('featured_img')->store('products/featured', 'outside');
        }

        if ($request->hasFile('gallery')) {
            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn ($file) => $file->store('products/gallery', 'outside'))
                ->all();
        }

        $product = $this->repository->create($data);

        return response()->json([
            'message' => $product ? 'Product created successfully.' : 'The product could not be created.',
            'data' => $product ? new ProductResource($product->load('brand', 'category')) : null,
            'status' => (bool) $product,
        ], $product ? 201 : 422);
    }

    /**
     * Public product page: the product plus its related items, so the page
     * renders from a single request.
     */
    public function findBySlug(string $slug): JsonResponse
    {
        $product = $this->repository->findBySlug($slug);

        return response()->json([
            'data' => new ProductResource($product),
            'related' => ProductResource::collection($this->repository->related($product)),
            'status' => true,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json([
            'data' => new ProductResource($this->repository->findById($id)),
            'status' => true,
        ]);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $product = $this->repository->findById($id);

        $disk = Storage::disk('outside');

        // Replace the featured image, dropping the previous file so the disk
        // does not accumulate orphans.
        if ($request->hasFile('featured_img')) {
            if ($product->featured_img && $disk->exists($product->featured_img)) {
                $disk->delete($product->featured_img);
            }

            $data['featured_img'] = $request->file('featured_img')->store('products/featured', 'outside');
        }

        // The gallery is replaced wholesale, matching how the admin form
        // submits it.
        if ($request->hasFile('gallery')) {
            if (is_array($product->gallery) && $product->gallery) {
                $disk->delete($product->gallery);
            }

            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn ($file) => $file->store('products/gallery', 'outside'))
                ->all();
        }

        $updated = $this->repository->update($product, $data);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($updated->load('brand', 'category')),
            'status' => true,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        // The repository removes the media as part of the delete, so the
        // controller does not touch the disk itself.
        $deleted = $this->repository->delete($id);

        return response()->json([
            'message' => $deleted ? 'Product deleted.' : 'The product could not be deleted.',
            'status' => $deleted,
        ], $deleted ? 200 : 500);
    }
}
