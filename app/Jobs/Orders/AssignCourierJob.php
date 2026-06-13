<?php

namespace App\Jobs\Orders;

use App\Models\Order;
use App\Services\Courier\CourierAssignmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AssignCourierJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $orderId,
        private readonly ?int $excludedCourierId = null,
    ) {}

    public function handle(CourierAssignmentService $assignmentService): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order) {
            return;
        }

        $assignmentService->assignBestCourier($order, $this->excludedCourierId);
    }
}
