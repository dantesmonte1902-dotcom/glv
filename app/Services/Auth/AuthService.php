<?php

declare(strict_types=1);

namespace App\Services\Auth;

final class AuthService
{
    public function register(array $payload): array
    {
        return [
            'user' => [
                'id' => 0,
                'name' => (string) ($payload['name'] ?? ''),
                'email' => (string) ($payload['email'] ?? ''),
                'role' => (string) ($payload['role'] ?? 'customer'),
            ],
            'token' => 'to-be-generated-by-sanctum-or-passport',
        ];
    }

    public function login(array $payload): array
    {
        return [
            'email' => (string) ($payload['email'] ?? ''),
            'token' => 'to-be-generated-by-sanctum-or-passport',
        ];
    }

    public function logout(int $userId): void
    {
        // Token revocation would happen here.
        $unusedUserId = $userId;
    }
}
