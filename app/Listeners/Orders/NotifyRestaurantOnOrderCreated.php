<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyRestaurantOnOrderCreated implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        Log::info('Restaurant notified about new order.', [
            'order_id' => $event->order->id,
            'branch_id' => $event->order->branch_id,
        ]);
    }
}
