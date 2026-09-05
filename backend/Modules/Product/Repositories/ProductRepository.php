<?php

namespace Modules\Product\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\Product;

/**
 * Cached data access for products.
 *
 * Caching strategy: every key embeds a version counter bumped on any write,
 * so a single Cache::forever() call invalidates every cached read at once.
 * This replaces Cache::tags(), which the database and file cache stores do
 * not support.
 */
class ProductRepository
{
    protected const CACHE_TTL = 3600;

    protected const VERSION_KEY = 'products:cache_version';

    public function __construct(protected Product $model) {}

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
        return 'products:v'.$this->cacheVersion().':'.$suffix;
    }

    // ----------------------------------------------------------------- reads

    /**
     * Listing with optional filters. `$all` returns the full set (used by the
     * sitemap and admin selects), otherwise a paginated slice.
     *
     * @param  array<string, mixed>  $filters
     */
    public function findAll(bool $all, ?int $perPage = 15, array $filters = []): Collection|LengthAwarePaginator
    {
        // Filters are part of the cache key so two different filtered views
        // never share an entry.
        $fingerprint = md5((string) json_encode($filters));

        if ($all) {
            return Cache::remember($this->key("all:{$fingerprint}"), self::CACHE_TTL, function () use ($filters) {
                return $this->query($filters)->get();
            });
        }

        $page = request()->integer('page', 1);

        return Cache::remember(
            $this->key("page:{$page}:per:{$perPage}:{$fingerprint}"),
            self::CACHE_TTL,
            fn () => $this->query($filters)->paginate($perPage)
        );
    }

    /**
     * Shared query builder for every listing. Keeping the filter logic in one
     * place means the public catalogue, the admin table and the sitemap all
     * agree on what "active" and "search" mean.
     *
     * @param  array<string, mixed>  $filters
     */
    protected function query(array $filters = []): Builder
    {
        $query = $this->model->newQuery()
            ->with(['brand:id,name,slug,logo', 'category:id,name,slug']);

        if (! empty($filters['only_active'])) {
            $query->where('is_active', true);
        }

        if (! empty($filters['category_id'])) {
            $query->whereIn('category_id', (array) $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['is_new'])) {
            $query->where('is_new', true);
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);

            // Grouped so the search never widens an existing filter.
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('pid', 'like', "%{$term}%");
            });
        }

        return match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->orderBy('created_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * Newest active products for the landing page.
     */
    public function latestProducts(int $limit = 10): Collection
    {
        return Cache::remember($this->key("latest:{$limit}"), self::CACHE_TTL, function () use ($limit) {
            return $this->model->newQuery()
                ->with(['brand:id,name,slug,logo', 'category:id,name,slug'])
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Products flagged as "new", used by the featured strip on the landing
     * page. Falls back to the newest products so the section is never empty
     * on a fresh install.
     */
    public function featuredProducts(int $limit = 8): Collection
    {
        return Cache::remember($this->key("featured:{$limit}"), self::CACHE_TTL, function () use ($limit) {
            $featured = $this->model->newQuery()
                ->with(['brand:id,name,slug,logo', 'category:id,name,slug'])
                ->where('is_active', true)
                ->where('is_new', true)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return $featured->isNotEmpty() ? $featured : $this->latestProducts($limit);
        });
    }

    /**
     * Other active products in the same category, excluding the one being
     * viewed. Powers the "related products" strip on a product page.
     */
    public function related(Product $product, int $limit = 8): Collection
    {
        return Cache::remember($this->key("related:{$product->id}:{$limit}"), self::CACHE_TTL, function () use ($product, $limit) {
            return $this->model->newQuery()
                ->with(['brand:id,name,slug,logo', 'category:id,name,slug'])
                ->where('is_active', true)
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Uncached on purpose: backs the admin edit form, which must see the
     * current row rather than a possibly stale copy.
     */
    public function findById(string $id): Product
    {
        return $this->model->newQuery()->with('brand', 'category')->findOrFail($id);
    }

    public function findBySlug(string $slug): Product
    {
        return Cache::remember($this->key("slug:{$slug}"), self::CACHE_TTL, function () use ($slug) {
            return $this->model->newQuery()
                ->with(['category:id,name,slug,parent_id', 'brand:id,name,slug,logo'])
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    // ---------------------------------------------------------------- writes

    public function create(array $payload): ?Product
    {
        try {
            $product = $this->model->create($payload);
            $this->bumpCacheVersion();

            return $product;
        } catch (\Throwable $th) {
            Log::error('Create product failed: '.$th->getMessage());

            return null;
        }
    }

    public function update(Product $product, array $payload): Product
    {
        $product->update($payload);
        $this->bumpCacheVersion();

        return $product->refresh();
    }

    /**
     * Removes the row first, then its media: if the delete fails we have not
     * already destroyed files that the surviving record still points at.
     */
    public function delete(string $id): bool
    {
        try {
            $product = $this->model->newQuery()->find($id);

            if (! $product) {
                return false;
            }

            $featured = $product->featured_img;
            $gallery = is_array($product->gallery) ? $product->gallery : [];

            $deleted = $product->delete();

            if (! $deleted) {
                return false;
            }

            $disk = Storage::disk('outside');

            if ($featured) {
                $disk->delete($featured);
            }

            if ($gallery) {
                $disk->delete($gallery);
            }

            $this->bumpCacheVersion();

            return true;
        } catch (\Throwable $th) {
            Log::error('Delete product failed: '.$th->getMessage());

            return false;
        }
    }

    public function flush(): void
    {
        $this->bumpCacheVersion();
    }
}
