<?php

declare(strict_types=1);

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;

final class NotifyRestaurantOnOrderCreated
{
    public function handle(OrderCreated $event): void
    {
        // In production this would push websocket + notification dispatch.
        $unusedEvent = $event;
    }
}
