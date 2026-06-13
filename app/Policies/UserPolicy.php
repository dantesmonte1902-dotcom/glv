<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return ($user->role->value ?? $user->role) === UserRole::ADMIN->value ? true : null;
    }

    public function updateActivation(User $user, User $courier): bool
    {
        return false;
    }
}
