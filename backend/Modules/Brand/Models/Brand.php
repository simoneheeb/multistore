<?php

namespace Modules\Brand\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Brand\Database\Factories\BrandFactory;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;

class Brand extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'logo',
        'description',
        'meta_title',
        'meta_description',
        'slug',
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
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Brand $brand) {
            if (empty($brand->slug)) {
                $brand->slug = static::makeSlug($brand->name);
            } elseif ($brand->isDirty('name') && ! $brand->isDirty('slug')) {
                $brand->slug = static::makeSlug($brand->name);
            }
        });
    }

    /**
     * Keeps a usable slug for names that contain no latin characters, which
     * Str::slug() would otherwise reduce to an empty string.
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ------------------------------------------------------------ relations

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Only the top level of this brand's category tree; the rest hangs off
     * each root through Category::children.
     */
    public function rootCategories(): HasMany
    {
        return $this->categories()->whereNull('parent_id')->orderBy('order');
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderByDesc('created_at');
    }

    public static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }
}
