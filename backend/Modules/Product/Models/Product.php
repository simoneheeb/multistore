<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Brand\Models\Brand;
use Modules\Category\Models\Category;
use Modules\Product\Database\Factories\ProductFactory;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'products';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'pid',
        'is_active',
        'is_new',
        'category_id',
        'brand_id',
        'featured_img',
        'gallery',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'gallery' => 'array',
            'attributes' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::makeSlug($product->name);
            } elseif ($product->isDirty('name') && ! $product->isDirty('slug')) {
                $product->slug = static::makeSlug($product->name);
            }
        });

        static::creating(function (Product $product) {
            // Human-readable product identifier, derived from the category so
            // it stays meaningful in invoices and support conversations.
            if (empty($product->pid)) {
                $categorySlug = $product->category_id
                    ? Category::query()->whereKey($product->category_id)->value('slug')
                    : null;

                // Trim the separator so a truncated slug does not produce a
                // doubled dash such as "LEATHER--AB12CD".
                $prefix = rtrim(strtoupper(substr((string) ($categorySlug ?: 'GEN'), 0, 8)), '-');

                $product->pid = ($prefix ?: 'GEN').'-'.Str::upper(Str::random(6));
            }
        });
    }

    /**
     * Str::slug() strips non-latin characters, so a Persian-only name would
     * produce an empty slug; fall back to a unicode-preserving slug.
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // --------------------------------------------------------------- scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
