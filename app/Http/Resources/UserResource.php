<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value ?? $this->role,
            'is_active' => $this->is_active,
            'city' => $this->whenLoaded('city', fn (): array => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ]),
            'courier_profile' => $this->whenLoaded('courierProfile', fn (): ?array => $this->courierProfile ? [
                'is_online' => $this->courierProfile->is_online,
                'vehicle_type' => $this->courierProfile->vehicle_type,
                'last_lat' => $this->courierProfile->last_lat,
                'last_lng' => $this->courierProfile->last_lng,
                'last_seen_at' => $this->courierProfile->last_seen_at,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}
