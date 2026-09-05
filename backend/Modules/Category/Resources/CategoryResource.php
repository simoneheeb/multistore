<?php

namespace Modules\Category\Resources;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Resources\BrandResource;
use Modules\Product\Resources\ProductResource;

/**
 * Serialises one category node.
 *
 * Relations are emitted only when they were eager-loaded (whenLoaded), which
 * is what keeps a tree of a few hundred nodes to a single query instead of
 * one per node.
 */
class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('outside');

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->resolveLogo($disk),
            'description' => $this->description,

            // SEO: fall back to the real name/description so every category
            // page still renders a usable title and meta description.
            'meta_title' => $this->meta_title ?: $this->name,
            'meta_description' => $this->meta_description ?: mb_substr((string) $this->description, 0, 160),

            'depth' => (int) $this->depth,
            'order' => (int) $this->order,
            'is_active' => (bool) $this->is_active,
            'is_new' => (bool) $this->is_new,

            'products_length' => (int) ($this->products_count ?? 0),

            'parent' => $this->whenLoaded('parent', fn () => [
                'name' => $this->parent?->name,
                'slug' => $this->parent?->slug,
            ]),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];

        // Internal identifiers are exposed to signed-in admins only; the
        // public site navigates purely by slug.
        if (Auth::check()) {
            $data['id'] = $this->id;
            $data['parent_id'] = $this->parent_id;
            $data['brand_id'] = $this->brand_id;
        }

        return $data;
    }

    /**
     * Logos may be a path on the "outside" disk or an absolute URL that was
     * pasted in by hand; both have to keep working.
     */
    protected function resolveLogo(FilesystemAdapter $disk): ?string
    {
        if (empty($this->logo)) {
            return null;
        }

        if (str_starts_with((string) $this->logo, 'http') || str_starts_with((string) $this->logo, '/')) {
            return $this->logo;
        }

        return $disk->exists($this->logo) ? $disk->url($this->logo) : null;
    }
}
