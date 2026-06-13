<?php

namespace App\Events\Courier;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('orders.'.$this->order->id.'.tracking'),
        ];

        if ($this->order->courierAssignment?->courier_id) {
            $channels[] = new PrivateChannel('couriers.'.$this->order->courierAssignment->courier_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'courier.assignment.updated';
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing('courierAssignment.courier');

        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status->value ?? $this->order->status,
            'courier_assignment' => [
                'courier_id' => $this->order->courierAssignment?->courier_id,
                'status' => $this->order->courierAssignment?->status->value ?? $this->order->courierAssignment?->status,
                'assigned_at' => $this->order->courierAssignment?->assigned_at?->toIso8601String(),
            ],
        ];
    }
}
