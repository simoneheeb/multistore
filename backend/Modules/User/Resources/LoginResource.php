<?php

namespace Modules\User\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'isActive' => (bool) $this->is_active,
            'isAdmin' => (bool) $this->is_admin,
        ];
    }
}
