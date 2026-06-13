<?php

namespace Tests\Feature;

use App\Enums\CourierAssignmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\Courier\CourierAssigned;
use App\Events\Courier\CourierLocationUpdated;
use App\Events\Orders\OrderStatusUpdated;
use App\Models\City;
use App\Models\CourierAssignment;
use App\Models\CourierProfile;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BroadcastingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_tracking_channel_respects_tenant_authorization(): void
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
        $order = Order::query()->create([
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
        $owner = User::factory()->create([
            'city_id' => $city->id,
            'restaurant_id' => $restaurant->id,
            'role' => UserRole::RESTAURANT,
        ]);
        $foreignOwner = User::factory()->create([
            'city_id' => $city->id,
            'restaurant_id' => null,
            'role' => UserRole::RESTAURANT,
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('track', $order));
        $this->assertFalse(Gate::forUser($foreignOwner)->allows('track', $order));
    }

    public function test_order_status_update_event_broadcasts_expected_payload(): void
    {
        $order = $this->createOrderGraph();
        $event = new OrderStatusUpdated($order);

        $this->assertSame('order.status.updated', $event->broadcastAs());
        $this->assertEquals([new PrivateChannel('orders.'.$order->id.'.tracking')], $event->broadcastOn());
        $this->assertSame($order->id, $event->broadcastWith()['order_id']);
        $this->assertSame(OrderStatus::PENDING_RESTAURANT_APPROVAL->value, $event->broadcastWith()['status']);
    }

    public function test_courier_events_include_expected_channels(): void
    {
        $order = $this->createOrderGraph(withAssignment: true);
        $courier = $order->courierAssignment->courier;
        $profile = CourierProfile::query()->create([
            'user_id' => $courier->id,
            'vehicle_type' => 'bike',
            'is_online' => true,
            'last_lat' => 43.8570,
            'last_lng' => 18.4120,
            'last_seen_at' => now(),
        ]);

        $assigned = new CourierAssigned($order);
        $locationUpdated = new CourierLocationUpdated($courier, $profile, [$order->id]);

        $this->assertSame('courier.assignment.updated', $assigned->broadcastAs());
        $this->assertCount(2, $assigned->broadcastOn());
        $this->assertSame('courier.location.updated', $locationUpdated->broadcastAs());
        $this->assertCount(2, $locationUpdated->broadcastOn());
    }

    private function createOrderGraph(bool $withAssignment = false): Order
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
        $order = Order::query()->create([
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

        if ($withAssignment) {
            $courier = User::factory()->create([
                'city_id' => $city->id,
                'role' => UserRole::COURIER,
            ]);

            CourierAssignment::query()->create([
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'status' => CourierAssignmentStatus::OFFERED,
                'assigned_at' => now(),
            ]);
        }

        return $order->fresh()->load('courierAssignment.courier');
    }
}
