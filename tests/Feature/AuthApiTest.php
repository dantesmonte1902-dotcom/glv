<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receive_token(): void
    {
        $city = City::query()->create([
            'name' => 'Sarajevo',
            'country_code' => 'BA',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'city_id' => $city->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+38761111111',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::CUSTOMER->value,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonStructure(['token']);
    }
}
