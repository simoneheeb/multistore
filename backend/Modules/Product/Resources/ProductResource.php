<?php

namespace Modules\Product\Resources;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Resources\BrandResource;
use Modules\Category\Resources\CategoryResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'pid' => $this->pid,

            'description' => $this->description,

            // SEO overrides fall back to the real content so no product page
            // ever ships an empty <title> or meta description.
            'meta_title' => $this->meta_title ?: $this->name,
            'meta_description' => $this->meta_description ?: mb_substr((string) $this->description, 0, 160),

            // Read through getAttribute() rather than $this->attributes:
            // "attributes" collides with Eloquent's own internal property, so
            // the explicit accessor is the only unambiguous way to get the
            // JSON column.
            'attributes' => $this->resource->getAttribute('attributes') ?: [],

            'featured_img' => $this->resolveUrl($this->featured_img),
            'gallery' => $this->galleryUrls(),

            'is_active' => (bool) $this->is_active,
            'is_new' => (bool) $this->is_new,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
        ];

        if (Auth::check()) {
            $data['id'] = $this->id;
            $data['category_id'] = $this->category_id;
            $data['brand_id'] = $this->brand_id;
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    protected function galleryUrls(): array
    {
        $gallery = $this->resource->getAttribute('gallery');

        if (empty($gallery) || ! is_iterable($gallery)) {
            return [];
        }

        return collect($gallery)
            ->map(fn ($path) => $this->resolveUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Media may be a path on the "outside" disk or an absolute/root-relative
     * URL entered by hand. Passing null to the disk would throw, so it is
     * filtered out here.
     */
    protected function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('outside');

        return $disk->url($path);
    }
}
