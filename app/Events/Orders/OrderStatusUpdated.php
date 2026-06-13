<?php

namespace App\Events\Orders;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.'.$this->order->id.'.tracking'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing('courierAssignment');

        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status->value ?? $this->order->status,
            'city_id' => $this->order->city_id,
            'branch_id' => $this->order->branch_id,
            'courier_assignment' => $this->order->courierAssignment ? [
                'courier_id' => $this->order->courierAssignment->courier_id,
                'status' => $this->order->courierAssignment->status->value ?? $this->order->courierAssignment->status,
                'assigned_at' => $this->order->courierAssignment->assigned_at?->toIso8601String(),
                'accepted_at' => $this->order->courierAssignment->accepted_at?->toIso8601String(),
            ] : null,
            'approved_at' => $this->order->approved_at?->toIso8601String(),
            'delivered_at' => $this->order->delivered_at?->toIso8601String(),
            'updated_at' => $this->order->updated_at?->toIso8601String(),
        ];
    }
}
