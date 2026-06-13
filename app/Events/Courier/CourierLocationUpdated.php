<?php

namespace App\Events\Courier;

use App\Models\CourierProfile;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $courier,
        public CourierProfile $profile,
        public array $activeOrderIds,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('couriers.'.$this->courier->id)];

        foreach ($this->activeOrderIds as $orderId) {
            $channels[] = new PrivateChannel('orders.'.$orderId.'.tracking');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'courier.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'courier_id' => $this->courier->id,
            'lat' => $this->profile->last_lat,
            'lng' => $this->profile->last_lng,
            'is_online' => $this->profile->is_online,
            'last_seen_at' => $this->profile->last_seen_at?->toIso8601String(),
        ];
    }
}
