<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'customer_id' => $this->customer_id,
            'branch_id' => $this->branch_id,
            'domain_type' => $this->domain_type,
            'status' => $this->status->value ?? $this->status,
            'subtotal_amount' => $this->subtotal_amount,
            'delivery_fee' => $this->delivery_fee,
            'total_amount' => $this->total_amount,
            'delivery_address' => $this->delivery_address,
            'delivery_lat' => $this->delivery_lat,
            'delivery_lng' => $this->delivery_lng,
            'notes' => $this->notes,
            'approved_at' => $this->approved_at,
            'delivered_at' => $this->delivered_at,
            'city' => $this->whenLoaded('city', fn (): array => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ]),
            'branch' => $this->whenLoaded('branch', fn (): array => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
                'address' => $this->branch->address,
                'restaurant' => [
                    'id' => $this->branch->restaurant?->id,
                    'name' => $this->branch->restaurant?->name,
                ],
            ]),
            'items' => $this->whenLoaded('items', fn (): array => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->all()),
            'courier_assignment' => $this->whenLoaded('courierAssignment', fn (): ?array => $this->courierAssignment ? [
                'courier_id' => $this->courierAssignment->courier_id,
                'status' => $this->courierAssignment->status->value ?? $this->courierAssignment->status,
                'assigned_at' => $this->courierAssignment->assigned_at,
                'accepted_at' => $this->courierAssignment->accepted_at,
            ] : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
