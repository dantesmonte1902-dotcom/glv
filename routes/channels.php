<?php

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('couriers.{courierId}', function (User $user, int $courierId): bool {
    return $user->is_active
        && (($user->role->value ?? $user->role) === UserRole::ADMIN->value
            || (($user->role->value ?? $user->role) === UserRole::COURIER->value
                && $user->id === $courierId));
});

Broadcast::channel('orders.{orderId}.tracking', function (User $user, int $orderId): bool {
    $order = Order::query()->with('branch.restaurant', 'courierAssignment')->find($orderId);

    if (! $order) {
        return false;
    }

    return $user->is_active && Gate::forUser($user)->allows('track', $order);
});
