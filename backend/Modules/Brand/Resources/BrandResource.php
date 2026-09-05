<?php

namespace Modules\Brand\Resources;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Category\Resources\CategoryTreeResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'name' => $this->name,
            'slug' => $this->slug,

            // Full text, not a truncated copy: the listing decides how much
            // of it to show, and truncating here would also truncate the
            // brand detail page.
            'description' => $this->description,

            'meta_title' => $this->meta_title ?: $this->name,
            'meta_description' => $this->meta_description ?: mb_substr((string) $this->description, 0, 160),

            'logo' => $this->resolveLogo(),

            'is_active' => (bool) $this->is_active,
            'is_new' => (bool) $this->is_new,
            'order' => (int) $this->order,

            'categories_length' => (int) ($this->categories_count ?? 0),
            'products_length' => (int) ($this->products_count ?? 0),

            'categories' => CategoryTreeResource::collection($this->whenLoaded('categories')),
            'root_categories' => CategoryTreeResource::collection($this->whenLoaded('rootCategories')),
        ];

        if (Auth::check()) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    protected function resolveLogo(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }

        if (str_starts_with((string) $this->logo, 'http') || str_starts_with((string) $this->logo, '/')) {
            return $this->logo;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('outside');

        return $disk->exists($this->logo) ? $disk->url($this->logo) : null;
    }
}
