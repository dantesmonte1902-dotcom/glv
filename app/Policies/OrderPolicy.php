<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return ($user->role->value ?? $user->role) === UserRole::ADMIN->value ? true : null;
    }

    public function view(User $user, Order $order): bool
    {
        $order->loadMissing('branch.restaurant', 'courierAssignment');

        return match ($user->role->value ?? $user->role) {
            UserRole::CUSTOMER->value => $order->customer_id === $user->id && $user->belongsToCity($order->city_id),
            UserRole::COURIER->value => $order->courierAssignment?->courier_id === $user->id && $user->belongsToCity($order->city_id),
            UserRole::RESTAURANT->value => $user->ownsRestaurantId($order->branch->restaurant_id) && $user->belongsToCity($order->city_id),
            default => false,
        };
    }

    public function approve(User $user, Order $order): bool
    {
        return $this->manageRestaurantOrder($user, $order);
    }

    public function reject(User $user, Order $order): bool
    {
        return $this->manageRestaurantOrder($user, $order);
    }

    public function assignCourier(User $user, Order $order): bool
    {
        return $this->manageRestaurantOrder($user, $order);
    }

    public function acceptCourier(User $user, Order $order): bool
    {
        $order->loadMissing('courierAssignment');

        return ($user->role->value ?? $user->role) === UserRole::COURIER->value
            && $order->courierAssignment?->courier_id === $user->id
            && $user->belongsToCity($order->city_id);
    }

    public function rejectCourier(User $user, Order $order): bool
    {
        return $this->acceptCourier($user, $order);
    }

    public function deliver(User $user, Order $order): bool
    {
        return $this->acceptCourier($user, $order);
    }

    public function track(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    private function manageRestaurantOrder(User $user, Order $order): bool
    {
        $order->loadMissing('branch.restaurant');

        return ($user->role->value ?? $user->role) === UserRole::RESTAURANT->value
            && $user->ownsRestaurantId($order->branch->restaurant_id)
            && $user->belongsToCity($order->city_id);
    }
}
