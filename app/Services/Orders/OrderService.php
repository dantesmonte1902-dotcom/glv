<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Events\Orders\OrderCreated;

final class OrderService
{
    public function createOrder(array $payload): array
    {
        $order = [
            'id' => 0,
            'customer_id' => (int) ($payload['customer_id'] ?? 0),
            'branch_id' => (int) ($payload['branch_id'] ?? 0),
            'city_id' => (int) ($payload['city_id'] ?? 0),
            'domain_type' => (string) ($payload['domain_type'] ?? 'food'),
            'status' => 'pending',
        ];

        OrderCreated::dispatch($order);

        return $order;
    }

    public function getOrder(int $orderId): array
    {
        return [
            'id' => $orderId,
            'status' => 'pending',
        ];
    }
}
