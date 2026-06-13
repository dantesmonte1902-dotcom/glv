<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\City;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_create_order_for_another_city(): void
    {
        [$sarajevo, $mostar] = $this->createCities();
        $branch = $this->createBranch($mostar);
        $customer = User::factory()->create([
            'city_id' => $sarajevo->id,
            'role' => UserRole::CUSTOMER,
        ]);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/orders', [
            'city_id' => $mostar->id,
            'branch_id' => $branch->id,
            'domain_type' => 'food',
            'delivery_address' => 'Wrong city 1',
            'items' => [
                [
                    'item_name' => 'Pizza',
                    'quantity' => 1,
                    'unit_price' => 12,
                ],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('city_id');
    }

    public function test_restaurant_user_cannot_approve_order_for_other_restaurant(): void
    {
        [$city] = $this->createCities();
        $ownedBranch = $this->createBranch($city, 'Owned', 'owned');
        $foreignBranch = $this->createBranch($city, 'Foreign', 'foreign');
        $restaurantUser = User::factory()->create([
            'city_id' => $city->id,
            'restaurant_id' => $ownedBranch->restaurant_id,
            'role' => UserRole::RESTAURANT,
        ]);
        $order = $this->createOrder($foreignBranch, $city);

        Sanctum::actingAs($restaurantUser);

        $this->postJson("/api/v1/orders/{$order->id}/approve")
            ->assertForbidden();
    }

    public function test_restaurant_user_can_only_list_owned_restaurant_records(): void
    {
        [$city] = $this->createCities();
        $ownedBranch = $this->createBranch($city, 'Owned', 'owned');
        $foreignBranch = $this->createBranch($city, 'Foreign', 'foreign');
        $restaurantUser = User::factory()->create([
            'city_id' => $city->id,
            'restaurant_id' => $ownedBranch->restaurant_id,
            'role' => UserRole::RESTAURANT,
        ]);

        Sanctum::actingAs($restaurantUser);

        $this->getJson('/api/v1/admin/restaurants')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownedBranch->restaurant_id);

        $this->getJson("/api/v1/admin/restaurants/{$foreignBranch->restaurant_id}/branches")
            ->assertForbidden();
    }

    public function test_courier_cannot_view_order_from_different_city(): void
    {
        [$sarajevo, $mostar] = $this->createCities();
        $branch = $this->createBranch($sarajevo);
        $order = $this->createOrder($branch, $sarajevo);
        $courier = User::factory()->create([
            'city_id' => $mostar->id,
            'role' => UserRole::COURIER,
        ]);

        Sanctum::actingAs($courier);

        $this->getJson("/api/v1/orders/{$order->id}")
            ->assertForbidden();
    }

    public function test_inactive_courier_cannot_update_location(): void
    {
        [$city] = $this->createCities();
        $courier = User::factory()->create([
            'city_id' => $city->id,
            'role' => UserRole::COURIER,
            'is_active' => false,
        ]);

        Sanctum::actingAs($courier);

        $this->patchJson('/api/v1/courier/location', [
            'lat' => 43.8563,
            'lng' => 18.4131,
            'is_online' => true,
        ])->assertForbidden();
    }

    private function createCities(): array
    {
        return [
            City::query()->create([
                'name' => 'Sarajevo',
                'country_code' => 'BA',
                'is_active' => true,
            ]),
            City::query()->create([
                'name' => 'Mostar',
                'country_code' => 'BA',
                'is_active' => true,
            ]),
        ];
    }

    private function createBranch(City $city, string $name = 'Demo Burger', string $slug = 'demo-burger'): RestaurantBranch
    {
        $restaurant = Restaurant::query()->create([
            'name' => $name,
            'brand_slug' => $slug,
            'is_active' => true,
        ]);

        return RestaurantBranch::query()->create([
            'restaurant_id' => $restaurant->id,
            'city_id' => $city->id,
            'name' => $name.' Center',
            'address' => 'Main street 1',
            'lat' => 43.8563,
            'lng' => 18.4131,
            'service_radius_km' => 5,
            'is_active' => true,
        ]);
    }

    private function createOrder(RestaurantBranch $branch, City $city): Order
    {
        $customer = User::factory()->create([
            'city_id' => $city->id,
            'role' => UserRole::CUSTOMER,
        ]);

        return Order::query()->create([
            'city_id' => $city->id,
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'domain_type' => 'food',
            'status' => OrderStatus::PENDING_RESTAURANT_APPROVAL,
            'subtotal_amount' => 10,
            'delivery_fee' => 3.5,
            'total_amount' => 13.5,
            'delivery_address' => 'Customer street 2',
        ]);
    }
}
