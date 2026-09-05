<?php

namespace Modules\Category\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * Lightweight serialisation of a category tree node.
 *
 * Used by the navigation mega-menu and the admin tree picker, where only
 * label, slug and children matter - sending the full CategoryResource for
 * every node would triple the payload of the site header.
 */
class CategoryTreeResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'depth' => (int) $this->depth,
            'products_length' => (int) ($this->products_count ?? 0),
            'is_active' => (bool) $this->is_active,
            'children' => CategoryTreeResource::collection($this->whenLoaded('children')),
        ];

        if (Auth::check()) {
            $data['id'] = $this->id;
            $data['parent_id'] = $this->parent_id;
            $data['order'] = (int) $this->order;
        }

        return $data;
    }
}
