<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'city_id' => $this->city_id,
            'name' => $this->name,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'service_radius_km' => $this->service_radius_km,
            'is_active' => $this->is_active,
            'city' => $this->whenLoaded('city', fn (): array => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ]),
        ];
    }
}
