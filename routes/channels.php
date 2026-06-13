<?php

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('couriers.{courierId}', function (User $user, int $courierId): bool {
    return $user->id === $courierId || $user->role === UserRole::ADMIN;
});

Broadcast::channel('orders.{orderId}.tracking', function (User $user, int $orderId): bool {
    $order = Order::query()->with('courierAssignment')->find($orderId);

    if (! $order) {
        return false;
    }

    return $user->role === UserRole::ADMIN
        || $order->customer_id === $user->id
        || ($order->courierAssignment?->courier_id === $user->id)
        || $user->role === UserRole::RESTAURANT;
});
