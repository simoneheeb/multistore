<?php

namespace Modules\Brand\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Models\Brand;

/**
 * Cached data access for brands. Uses the same version-counter invalidation
 * as the product and category repositories - see ProductRepository for why
 * Cache::tags() is not an option on the database/file stores.
 */
class BrandRepository
{
    protected const CACHE_TTL = 3600;

    protected const VERSION_KEY = 'brands:cache_version';

    public function __construct(protected Brand $model) {}

    protected function cacheVersion(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    protected function bumpCacheVersion(): void
    {
        Cache::forever(self::VERSION_KEY, $this->cacheVersion() + 1);
    }

    protected function key(string $suffix): string
    {
        return 'brands:v'.$this->cacheVersion().':'.$suffix;
    }

    // ----------------------------------------------------------------- reads

    /**
     * `$all` returns every active brand (site navigation, sitemap); otherwise
     * a paginated slice for the admin table, which also includes inactive
     * brands.
     */
    public function findAll(bool $all = false, int $perPage = 15): Collection|LengthAwarePaginator
    {
        if ($all) {
            return Cache::remember($this->key('active'), self::CACHE_TTL, function () {
                return $this->model->newQuery()
                    ->active()
                    ->withCount(['categories', 'products'])
                    ->ordered()
                    ->get();
            });
        }

        $page = request()->integer('page', 1);

        return Cache::remember($this->key("page:{$page}:per:{$perPage}"), self::CACHE_TTL, function () use ($perPage) {
            return $this->model->newQuery()
                ->withCount(['categories', 'products'])
                ->ordered()
                ->paginate($perPage);
        });
    }

    /**
     * Uncached: backs the admin edit form, which must see the current row.
     */
    public function findById(string $id): Brand
    {
        return $this->model->newQuery()
            ->withCount(['categories', 'products'])
            ->findOrFail($id);
    }

    /**
     * Public brand page: the brand plus the top level of its category tree.
     */
    public function findBySlug(string $slug): Brand
    {
        return Cache::remember($this->key("slug:{$slug}"), self::CACHE_TTL, function () use ($slug) {
            return $this->model->newQuery()
                ->with([
                    'rootCategories' => fn ($q) => $q->active()->withCount('products')
                        ->with(['children' => fn ($c) => $c->active()->withCount('products')]),
                ])
                ->withCount(['categories', 'products'])
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    // ---------------------------------------------------------------- writes

    public function create(array $data): ?Brand
    {
        try {
            $brand = $this->model->create($data);
            $this->bumpCacheVersion();

            return $brand;
        } catch (\Throwable $th) {
            Log::error('Create brand failed: '.$th->getMessage());

            return null;
        }
    }

    public function update(array $data, Brand $brand): bool
    {
        $updated = $brand->update($data);

        if ($updated) {
            $this->bumpCacheVersion();
        }

        return $updated;
    }

    /**
     * Deletes the row first, then the logo: a failed delete must not leave a
     * surviving record pointing at a missing file.
     */
    public function delete(string $id): bool
    {
        try {
            $brand = $this->model->newQuery()->find($id);

            if (! $brand) {
                return false;
            }

            $logo = $brand->logo;
            $deleted = $brand->delete();

            if (! $deleted) {
                return false;
            }

            if ($logo) {
                Storage::disk('outside')->delete($logo);
            }

            $this->bumpCacheVersion();

            return true;
        } catch (\Throwable $th) {
            Log::error("Delete brand failed for id {$id}: ".$th->getMessage());

            return false;
        }
    }

    public function flush(): void
    {
        $this->bumpCacheVersion();
    }
}
