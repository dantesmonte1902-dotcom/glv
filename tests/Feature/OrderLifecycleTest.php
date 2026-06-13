<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\CourierProfile;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_and_restaurant_can_approve_order(): void
    {
        $city = City::query()->create([
            'name' => 'Sarajevo',
            'country_code' => 'BA',
            'is_active' => true,
        ]);

        $restaurant = Restaurant::query()->create([
            'name' => 'Demo Burger',
            'brand_slug' => 'demo-burger',
            'is_active' => true,
        ]);

        $branch = RestaurantBranch::query()->create([
            'restaurant_id' => $restaurant->id,
            'city_id' => $city->id,
            'name' => 'Center',
            'address' => 'Main street 1',
            'lat' => 43.8563,
            'lng' => 18.4131,
            'service_radius_km' => 5,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'city_id' => $city->id,
            'role' => UserRole::CUSTOMER,
        ]);
        $restaurantUser = User::factory()->create([
            'city_id' => $city->id,
            'restaurant_id' => $restaurant->id,
            'role' => UserRole::RESTAURANT,
        ]);
        $courier = User::factory()->create([
            'city_id' => $city->id,
            'role' => UserRole::COURIER,
        ]);

        CourierProfile::query()->create([
            'user_id' => $courier->id,
            'is_online' => true,
            'vehicle_type' => 'bike',
            'last_lat' => 43.8570,
            'last_lng' => 18.4120,
            'last_seen_at' => now(),
        ]);

        Sanctum::actingAs($customer);

        $createResponse = $this->postJson('/api/v1/orders', [
            'city_id' => $city->id,
            'branch_id' => $branch->id,
            'domain_type' => 'food',
            'delivery_address' => 'Customer street 2',
            'delivery_lat' => 43.8600,
            'delivery_lng' => 18.4200,
            'items' => [
                [
                    'item_name' => 'Burger',
                    'quantity' => 2,
                    'unit_price' => 8.5,
                ],
            ],
        ]);

        $orderId = $createResponse->json('id');
        $createResponse->assertCreated();

        Sanctum::actingAs($restaurantUser);

        $approveResponse = $this->postJson("/api/v1/orders/{$orderId}/approve");

        $approveResponse
            ->assertOk()
            ->assertJsonPath('status', 'searching_courier');
    }
}
