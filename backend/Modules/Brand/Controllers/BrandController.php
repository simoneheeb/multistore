<?php

namespace Modules\Brand\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Repositories\BrandRepository;
use Modules\Brand\Requests\StoreBrandRequest;
use Modules\Brand\Requests\UpdateBrandRequest;
use Modules\Brand\Resources\BrandResource;

class BrandController extends Controller
{
    public function __construct(protected BrandRepository $repository) {}

    /**
     * `all=true` returns every active brand in one list (navigation,
     * sitemap); otherwise a paginated slice for the admin table.
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return response()->json([
                'data' => BrandResource::collection($this->repository->findAll(all: true)),
                'status' => true,
            ]);
        }

        $perPage = min($request->integer('perPage') ?: 15, 60);
        $brands = $this->repository->findAll(perPage: $perPage);

        return response()->json([
            'data' => BrandResource::collection($brands),
            'meta' => [
                'current_page' => $brands->currentPage(),
                'from' => $brands->firstItem(),
                'to' => $brands->lastItem(),
                'last_page' => $brands->lastPage(),
                'per_page' => $brands->perPage(),
                'total' => $brands->total(),
                'path' => $brands->path(),
                'next_page_url' => $brands->nextPageUrl(),
                'prev_page_url' => $brands->previousPageUrl(),
            ],
            'status' => true,
        ]);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'outside');
        }

        $created = $this->repository->create($data);

        return response()->json([
            'message' => $created ? 'Brand created successfully.' : 'The brand could not be created.',
            'data' => $created ? new BrandResource($created) : null,
            'status' => (bool) $created,
        ], $created ? 201 : 500);
    }

    public function show(string $slug): JsonResponse
    {
        // A missing slug raises ModelNotFoundException, which the global
        // exception handler renders as a JSON 404 - no try/catch needed here.
        return response()->json([
            'data' => new BrandResource($this->repository->findBySlug($slug)),
            'status' => true,
        ]);
    }

    public function findById(string $id): JsonResponse
    {
        return response()->json([
            'data' => new BrandResource($this->repository->findById($id)),
            'status' => true,
        ]);
    }

    public function update(UpdateBrandRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $brand = $this->repository->findById($id);

        if ($request->hasFile('logo')) {
            if (! empty($brand->logo)) {
                Storage::disk('outside')->delete($brand->logo);
            }

            $data['logo'] = $request->file('logo')->store('brands', 'outside');
        }

        $updated = $this->repository->update($data, $brand);

        return response()->json([
            'message' => $updated ? 'Brand updated successfully.' : 'The brand could not be updated.',
            'status' => $updated,
        ], $updated ? 200 : 422);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->repository->delete($id);

            return response()->json([
                'message' => $deleted ? 'Brand deleted.' : 'Brand not found.',
                'status' => $deleted,
            ], $deleted ? 200 : 404);
        } catch (\Throwable $th) {
            Log::error("Brand deletion failed for id {$id}: ".$th->getMessage());

            return response()->json([
                'message' => 'Something went wrong. Please try again.',
                'status' => false,
            ], 500);
        }
    }
}
