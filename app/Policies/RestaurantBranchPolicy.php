<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;

class RestaurantBranchPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return ($user->role->value ?? $user->role) === UserRole::ADMIN->value ? true : null;
    }

    public function viewAny(User $user, Restaurant $restaurant): bool
    {
        return $this->canManageRestaurant($user, $restaurant);
    }

    public function create(User $user, Restaurant $restaurant): bool
    {
        return $this->canManageRestaurant($user, $restaurant);
    }

    public function update(User $user, RestaurantBranch $branch): bool
    {
        return ($user->role->value ?? $user->role) === UserRole::RESTAURANT->value
            && $user->ownsRestaurantId($branch->restaurant_id)
            && $user->belongsToCity($branch->city_id);
    }

    private function canManageRestaurant(User $user, Restaurant $restaurant): bool
    {
        return ($user->role->value ?? $user->role) === UserRole::RESTAURANT->value
            && $user->ownsRestaurant($restaurant);
    }
}
