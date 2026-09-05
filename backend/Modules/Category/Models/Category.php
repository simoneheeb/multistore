<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Brand\Models\Brand;
use Modules\Category\Database\Factories\CategoryFactory;
use Modules\Product\Models\Product;

/**
 * A node in the category tree.
 *
 * The tree is stored as a plain adjacency list (parent_id) with a cached
 * `depth`. Reads never recurse through the database: CategoryRepository
 * fetches the flat table once and assembles the tree in memory.
 */
class Category extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'categories';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'logo',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'brand_id',
        'parent_id',
        'depth',
        'is_active',
        'is_new',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'order' => 'integer',
            'depth' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::makeSlug($category->name);
            } elseif ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = static::makeSlug($category->name);
            }

            // Keep the cached depth in step with whatever parent is set.
            if ($category->isDirty('parent_id') || ! $category->exists) {
                $category->depth = $category->parent_id
                    ? (int) (static::query()->whereKey($category->parent_id)->value('depth') ?? 0) + 1
                    : 0;
            }
        });

        // Moving a node changes the depth of everything beneath it.
        static::updated(function (Category $category) {
            if ($category->wasChanged('parent_id')) {
                $category->refreshSubtreeDepth();
            }
        });
    }

    /**
     * Str::slug() drops non-latin characters entirely, which would leave a
     * Persian-only name with an empty slug. Fall back to a transliteration-
     * free slug that keeps unicode letters instead.
     */
    protected static function makeSlug(?string $name): string
    {
        $slug = Str::slug((string) $name);

        if ($slug !== '') {
            return $slug;
        }

        $fallback = preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $name);

        return trim((string) $fallback, '-') ?: (string) Str::uuid();
    }

    // ------------------------------------------------------------ relations

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    /**
     * Eager-loadable recursive children. Use sparingly - the repository's
     * in-memory tree builder is cheaper for anything wider than one branch.
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // --------------------------------------------------------------- scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // ----------------------------------------------------------- tree helpers

    /**
     * Every ancestor from the root down to the direct parent. Used for
     * breadcrumbs; bounded by a hard depth limit so a corrupted parent chain
     * can never spin forever.
     */
    public function ancestors(): Collection
    {
        $chain = new Collection;
        $node = $this;
        $guard = 0;

        while ($node->parent_id && $guard++ < 20) {
            $node = static::query()->find($node->parent_id);

            if (! $node) {
                break;
            }

            $chain->prepend($node);
        }

        return $chain;
    }

    /**
     * Ids of this node and everything below it. Used to list the products of
     * a branch, and to reject a parent_id that would create a cycle.
     *
     * @return list<string>
     */
    public function descendantIds(bool $includeSelf = true): array
    {
        $all = static::query()->get(['id', 'parent_id']);

        $byParent = [];
        foreach ($all as $row) {
            $byParent[$row->parent_id ?? '_root'][] = $row->id;
        }

        $ids = $includeSelf ? [$this->id] : [];
        $stack = [$this->id];

        while ($stack) {
            $current = array_pop($stack);

            foreach ($byParent[$current] ?? [] as $childId) {
                $ids[] = $childId;
                $stack[] = $childId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Recompute depth for the whole subtree after this node was moved.
     */
    public function refreshSubtreeDepth(): void
    {
        $queue = [[$this->id, (int) $this->depth]];

        while ($queue) {
            [$id, $depth] = array_shift($queue);

            $children = static::query()->where('parent_id', $id)->get(['id']);

            foreach ($children as $child) {
                static::query()->whereKey($child->id)->update(['depth' => $depth + 1]);
                $queue[] = [$child->id, $depth + 1];
            }
        }
    }

    /**
     * Number of products directly attached to this category. Prefer
     * withCount('products') in queries; this is the fallback for single
     * models loaded without the count.
     */
    public function productsCount(): int
    {
        return (int) ($this->products_count ?? $this->products()->count());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function newFactory()
    {
        return CategoryFactory::new();
    }
}
