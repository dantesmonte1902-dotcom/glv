<?php

declare(strict_types=1);

namespace App\Jobs\Orders;

use App\Services\Courier\CourierAssignmentService;

final class AssignCourierJob
{
    public function __construct(private int $orderId)
    {
    }

    public static function dispatch(int $orderId): string
    {
        $job = new self($orderId);
        $job->handle(new CourierAssignmentService());

        return 'courier-assignment-dispatched';
    }

    public function handle(CourierAssignmentService $assignmentService): void
    {
        $assignmentService->assignBestCourier($this->orderId);
    }
}
