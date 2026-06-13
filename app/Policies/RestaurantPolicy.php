<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return ($user->role->value ?? $user->role) === UserRole::ADMIN->value ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return ($user->role->value ?? $user->role) === UserRole::RESTAURANT->value;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $this->ownsRestaurant($user, $restaurant);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $this->ownsRestaurant($user, $restaurant);
    }

    private function ownsRestaurant(User $user, Restaurant $restaurant): bool
    {
        return ($user->role->value ?? $user->role) === UserRole::RESTAURANT->value
            && $user->ownsRestaurant($restaurant);
    }
}
