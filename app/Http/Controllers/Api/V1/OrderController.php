<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Jobs\Orders\AssignCourierJob;
use App\Services\Orders\OrderService;

final class OrderController
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function store(array $payload): array
    {
        return $this->orderService->createOrder($payload);
    }

    public function show(int $orderId): array
    {
        return $this->orderService->getOrder($orderId);
    }

    public function assignCourier(int $orderId): string
    {
        // In Laravel this would be dispatched via queue dispatcher.
        return AssignCourierJob::dispatch($orderId);
    }
}
