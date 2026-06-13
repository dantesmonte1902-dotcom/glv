<?php

namespace App\Providers;

use App\Events\Orders\OrderApproved;
use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\NotifyRestaurantOnOrderCreated;
use App\Listeners\Orders\QueueCourierAssignment;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            NotifyRestaurantOnOrderCreated::class,
        ],
        OrderApproved::class => [
            QueueCourierAssignment::class,
        ],
    ];
}
