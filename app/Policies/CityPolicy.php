<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\User;

class CityPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return ($user->role->value ?? $user->role) === UserRole::ADMIN->value ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, City $city): bool
    {
        return false;
    }
}
