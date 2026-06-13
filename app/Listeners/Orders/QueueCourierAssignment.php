<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderApproved;
use App\Jobs\Orders\AssignCourierJob;

class QueueCourierAssignment
{
    public function handle(OrderApproved $event): void
    {
        AssignCourierJob::dispatch($event->order->id);
    }
}
