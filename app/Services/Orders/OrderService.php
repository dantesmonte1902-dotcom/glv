<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\Orders\OrderApproved;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderRejected;
use App\Jobs\Orders\AssignCourierJob;
use App\Models\CourierAssignment;
use App\Models\Order;
use App\Models\RestaurantBranch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(User $customer, array $payload): Order
    {
        $branch = RestaurantBranch::query()->findOrFail($payload['branch_id']);

        if ((int) $branch->city_id !== (int) $payload['city_id']) {
            throw ValidationException::withMessages([
                'branch_id' => 'The selected branch does not belong to the provided city.',
            ]);
        }

        $subtotal = collect($payload['items'])
            ->sum(fn (array $item): float => (float) $item['quantity'] * (float) $item['unit_price']);
        $deliveryFee = $this->calculateDeliveryFee($payload['domain_type'], $branch->service_radius_km);

        $order = DB::transaction(function () use ($customer, $payload, $subtotal, $deliveryFee): Order {
            $order = Order::query()->create([
                'city_id' => $payload['city_id'],
                'customer_id' => $customer->id,
                'branch_id' => $payload['branch_id'],
                'domain_type' => $payload['domain_type'],
                'status' => OrderStatus::PENDING_RESTAURANT_APPROVAL,
                'subtotal_amount' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $subtotal + $deliveryFee,
                'delivery_address' => $payload['delivery_address'],
                'delivery_lat' => $payload['delivery_lat'] ?? null,
                'delivery_lng' => $payload['delivery_lng'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            $order->items()->createMany(collect($payload['items'])->map(fn (array $item): array => [
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => (float) $item['quantity'] * (float) $item['unit_price'],
            ])->all());

            return $order;
        });

        OrderCreated::dispatch($order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment'));

        return $order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment');
    }

    public function showForUser(User $user, Order $order): Order
    {
        $this->ensureUserCanAccessOrder($user, $order);

        return $order->load('city', 'branch.restaurant', 'items', 'courierAssignment.courier');
    }

    public function approve(User $actor, Order $order): Order
    {
        $this->ensureRestaurantOrAdmin($actor);

        if (($order->status->value ?? $order->status) !== OrderStatus::PENDING_RESTAURANT_APPROVAL->value) {
            throw ValidationException::withMessages([
                'status' => 'Only pending orders can be approved.',
            ]);
        }

        $order->forceFill([
            'status' => OrderStatus::SEARCHING_COURIER,
            'approved_at' => now(),
        ])->save();

        OrderApproved::dispatch($order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment'));

        return $order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment');
    }

    public function reject(User $actor, Order $order, ?string $reason = null): Order
    {
        $this->ensureRestaurantOrAdmin($actor);

        $order->forceFill([
            'status' => OrderStatus::REJECTED,
            'notes' => $reason ?: $order->notes,
        ])->save();

        OrderRejected::dispatch($order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment'));

        return $order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment');
    }

    public function dispatchAssignment(User $actor, Order $order): CourierAssignment
    {
        $this->ensureRestaurantOrAdmin($actor);

        $assignment = $order->courierAssignment()->firstOrCreate([], [
            'status' => 'searching',
            'search_started_at' => now(),
        ]);

        AssignCourierJob::dispatch($order->id);

        return $assignment->fresh();
    }

    public function deliver(User $actor, Order $order): Order
    {
        if (($actor->role->value ?? $actor->role) !== UserRole::ADMIN->value && $order->courierAssignment?->courier_id !== $actor->id) {
            abort(403, 'Only the assigned courier or admin can complete delivery.');
        }

        $order->forceFill([
            'status' => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ])->save();

        return $order->fresh()->load('city', 'branch.restaurant', 'items', 'courierAssignment');
    }

    private function ensureUserCanAccessOrder(User $user, Order $order): void
    {
        $role = $user->role->value ?? $user->role;

        $allowed = $role === UserRole::ADMIN->value
            || $role === UserRole::RESTAURANT->value
            || $order->customer_id === $user->id
            || $order->courierAssignment?->courier_id === $user->id;

        if (! $allowed) {
            abort(403, 'You are not allowed to access this order.');
        }
    }

    private function ensureRestaurantOrAdmin(User $actor): void
    {
        $role = $actor->role->value ?? $actor->role;

        if (! in_array($role, [UserRole::ADMIN->value, UserRole::RESTAURANT->value], true)) {
            abort(403, 'Only restaurant and admin users can perform this action.');
        }
    }

    private function calculateDeliveryFee(string $domainType, float $serviceRadiusKm): float
    {
        $baseFee = match ($domainType) {
            'market' => 5.5,
            'pharmacy' => 4.5,
            default => 3.5,
        };

        return round($baseFee + max($serviceRadiusKm - 3, 0) * 0.35, 2);
    }
}
