<?php

namespace App\Events\Courier;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }
}
