<?php

namespace Modules\Category\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Category\Models\Category;

/**
 * Cached data access for the category tree.
 *
 * Caching strategy: every cache key embeds a version counter that is bumped
 * on any write. Old entries are orphaned instantly and expire on their own,
 * which gives tag-like invalidation on the database/file cache stores that do
 * not support Cache::tags().
 */
class CategoryRepository
{
    protected const CACHE_TTL = 3600;

    protected const VERSION_KEY = 'categories:cache_version';

    public function __construct(protected Category $model) {}

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
        return 'categories:v'.$this->cacheVersion().':'.$suffix;
    }

    // ----------------------------------------------------------------- reads

    /**
     * Flat listing. `$all` returns every row; otherwise a paginated slice for
     * the admin table.
     */
    public function findAll(bool $all, ?int $perPage = 15): Collection|LengthAwarePaginator
    {
        if ($all) {
            return Cache::remember($this->key('all'), self::CACHE_TTL, function () {
                return $this->model->newQuery()
                    ->withCount('products')
                    ->with(['parent:id,name,slug', 'brand:id,name,slug'])
                    ->ordered()
                    ->get();
            });
        }

        $page = request()->integer('page', 1);

        return Cache::remember($this->key("page:{$page}:per:{$perPage}"), self::CACHE_TTL, function () use ($perPage) {
            return $this->model->newQuery()
                ->withCount('products')
                ->with(['parent:id,name,slug', 'brand:id,name,slug'])
                ->ordered()
                ->paginate($perPage);
        });
    }

    /**
     * The whole category tree, assembled in memory from a single flat query.
     *
     * Each node gets a `children` relation populated with its descendants, so
     * the resource can serialise the tree recursively without touching the
     * database again.
     */
    public function tree(bool $onlyActive = true, ?string $brandId = null): Collection
    {
        $cacheKey = $this->key('tree:'.($onlyActive ? 'active' : 'all').':brand:'.($brandId ?? '_'));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($onlyActive, $brandId) {
            $query = $this->model->newQuery()->withCount('products')->ordered();

            if ($onlyActive) {
                $query->active();
            }

            if ($brandId) {
                $query->where('brand_id', $brandId);
            }

            return $this->buildTree($query->get());
        });
    }

    /**
     * Turn a flat collection into a nested one.
     *
     * A node whose parent is missing from the set (inactive, or filtered out
     * by brand) is promoted to a root so its branch is never orphaned.
     *
     * @param  Collection<int, Category>  $flat
     * @return Collection<int, Category>
     */
    protected function buildTree(Collection $flat): Collection
    {
        $byId = $flat->keyBy('id');
        $roots = new Collection;

        // Start every node with an empty children collection so the relation
        // is considered "loaded" and serialises as [] instead of triggering a
        // lazy query.
        foreach ($flat as $node) {
            $node->setRelation('children', new Collection);
        }

        foreach ($flat as $node) {
            $parent = $node->parent_id ? $byId->get($node->parent_id) : null;

            if ($parent) {
                $parent->children->push($node);
            } else {
                $roots->push($node);
            }
        }

        return $roots->values();
    }

    /**
     * Options for a <select> in the admin: one flat list with the depth kept
     * so the frontend can indent it. Optionally excludes a subtree, which is
     * what stops an admin from re-parenting a category under its own child.
     *
     * @return list<array{id: string, name: string: string, depth: int}>
     */
    public function options(?string $excludeSubtreeOf = null): array
    {
        $excluded = [];

        if ($excludeSubtreeOf) {
            $node = $this->model->newQuery()->find($excludeSubtreeOf);
            $excluded = $node ? $node->descendantIds() : [];
        }

        $flatten = function (Collection $nodes) use (&$flatten, $excluded): array {
            $out = [];

            foreach ($nodes as $node) {
                if (in_array($node->id, $excluded, true)) {
                    continue;
                }

                $out[] = [
                    'id' => $node->id,
                    'name' => $node->name,
                    'slug' => $node->slug,
                    'depth' => (int) $node->depth,
                ];

                $out = array_merge($out, $flatten($node->children));
            }

            return $out;
        };

        return $flatten($this->tree(false));
    }

    /**
     * Single category by id. Deliberately uncached: it backs the admin edit
     * form, which must always see the current row.
     */
    public function findById(string $id): Category
    {
        return $this->model->newQuery()
            ->with([
                'parent:id,name,slug,logo',
                'brand:id,name,slug,logo',
                'children:id,parent_id,name,slug',
            ])
            ->withCount('products')
            ->findOrFail($id);
    }

    /**
     * Public lookup. `$childSlug` resolves a direct child of `$slug`, which
     * is what makes /categories/football/leather work as tree navigation.
     */
    public function findBySlug(string $slug, ?string $childSlug = null): Category
    {
        $key = $this->key("slug:{$slug}:child:".($childSlug ?? '_'));

        return Cache::remember($key, self::CACHE_TTL, function () use ($slug, $childSlug) {
            $query = $this->model->newQuery()
                ->with([
                    'parent:id,name,slug',
                    'brand:id,name,slug,logo',
                    'children' => fn ($q) => $q->active()->ordered()->withCount('products'),
                ])
                ->withCount('products');

            if ($childSlug) {
                return $query->where('slug', $childSlug)
                    ->whereHas('parent', fn ($q) => $q->where('slug', $slug))
                    ->firstOrFail();
            }

            return $query->where('slug', $slug)->firstOrFail();
        });
    }

    /**
     * Products of a category *and every category below it*.
     *
     * Opening a parent node should list the whole branch, not an empty page,
     * so the descendant ids are resolved first and used as a single
     * whereIn - one query regardless of how deep the branch goes.
     *
     * @return Collection<int, \Modules\Product\Models\Product>
     */
    public function branchProducts(Category $category, int $limit = 24): Collection
    {
        $key = $this->key("branch-products:{$category->id}:limit:{$limit}");

        return Cache::remember($key, self::CACHE_TTL, function () use ($category, $limit) {
            $ids = $category->descendantIds();

            return \Modules\Product\Models\Product::query()
                ->whereIn('category_id', $ids)
                ->where('is_active', true)
                ->with(['brand:id,name,slug,logo', 'category:id,name,slug'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Ancestor chain of a category, cached alongside the node itself.
     *
     * @return Collection<int, Category>
     */
    public function breadcrumb(Category $category): Collection
    {
        return Cache::remember(
            $this->key('breadcrumb:'.$category->id),
            self::CACHE_TTL,
            fn () => $category->ancestors()
        );
    }

    // ---------------------------------------------------------------- writes

    public function create(array $data): ?Category
    {
        try {
            $category = $this->model->create($data);
            $this->bumpCacheVersion();

            return $category;
        } catch (\Throwable $th) {
            Log::error('Create category failed: '.$th->getMessage());

            return null;
        }
    }

    public function update(array $data, string $id): bool
    {
        try {
            $category = $this->model->newQuery()->findOrFail($id);

            // Guard against a move that would detach a branch from the tree
            // by making a node its own ancestor.
            if (! empty($data['parent_id']) && in_array($data['parent_id'], $category->descendantIds(), true)) {
                return false;
            }

            $updated = $category->update($data);

            if ($updated) {
                $this->bumpCacheVersion();
            }

            return $updated;
        } catch (\Throwable $th) {
            Log::error('Update category failed: '.$th->getMessage());

            return false;
        }
    }

    public function delete(Category $category): bool
    {
        $deleted = $category->delete();

        if ($deleted) {
            $this->bumpCacheVersion();
        }

        return $deleted;
    }

    /**
     * Exposed so other modules (products) can invalidate category caches when
     * a write on their side changes a product count.
     */
    public function flush(): void
    {
        $this->bumpCacheVersion();
    }
}
