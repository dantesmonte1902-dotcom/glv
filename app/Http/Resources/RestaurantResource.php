<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_slug' => $this->brand_slug,
            'is_active' => $this->is_active,
            'branches' => RestaurantBranchResource::collection($this->whenLoaded('branches')),
        ];
    }
}
