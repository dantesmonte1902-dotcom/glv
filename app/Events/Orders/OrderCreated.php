<?php

declare(strict_types=1);

namespace App\Events\Orders;

final class OrderCreated
{
    public function __construct(public array $order)
    {
    }

    public static function dispatch(array $order): self
    {
        return new self($order);
    }
}
