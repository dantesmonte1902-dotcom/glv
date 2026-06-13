<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\CourierProfile;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $payload): array
    {
        $user = DB::transaction(function () use ($payload): User {
            $user = User::query()->create([
                'city_id' => $payload['city_id'] ?? null,
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'password' => $payload['password'],
                'role' => $payload['role'],
                'is_active' => true,
            ]);

            if (($user->role->value ?? $user->role) === UserRole::COURIER->value) {
                CourierProfile::query()->create([
                    'user_id' => $user->id,
                    'is_online' => false,
                    'vehicle_type' => 'bike',
                ]);
            }

            return $user->loadMissing('city', 'courierProfile');
        });

        return [
            'user' => $user,
            'token' => $user->createToken($payload['device_name'] ?? 'mobile')->plainTextToken,
        ];
    }

    public function login(array $payload): array
    {
        $user = User::query()->with('city', 'courierProfile')->where('email', $payload['email'])->first();

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (! $user->is_active) {
            throw new AuthenticationException('User account is disabled.');
        }

        return [
            'user' => $user,
            'token' => $user->createToken($payload['device_name'] ?? 'mobile')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
