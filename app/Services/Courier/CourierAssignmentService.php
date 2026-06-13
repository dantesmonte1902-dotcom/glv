<?php

declare(strict_types=1);

namespace App\Services\Courier;

final class CourierAssignmentService
{
    public function assignBestCourier(int $orderId): array
    {
        return [
            'order_id' => $orderId,
            'courier_id' => null,
            'strategy' => 'nearest_available_in_same_city',
            'status' => 'queued',
        ];
    }
}
