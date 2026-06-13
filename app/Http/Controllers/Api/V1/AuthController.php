<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\Auth\AuthService;

final class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(array $payload): array
    {
        return $this->authService->register($payload);
    }

    public function login(array $payload): array
    {
        return $this->authService->login($payload);
    }

    public function logout(int $userId): void
    {
        $this->authService->logout($userId);
    }
}
