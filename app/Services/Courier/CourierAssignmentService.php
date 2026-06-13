<?php

namespace App\Services\Courier;

use App\Enums\CourierAssignmentStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\Courier\CourierAssigned;
use App\Events\Courier\CourierLocationUpdated;
use App\Jobs\Orders\AssignCourierJob;
use App\Models\CourierAssignment;
use App\Models\CourierProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourierAssignmentService
{
    public function assignBestCourier(Order $order, ?int $excludedCourierId = null): CourierAssignment
    {
        $order->loadMissing('customer', 'branch', 'courierAssignment');

        return DB::transaction(function () use ($order, $excludedCourierId): CourierAssignment {
            $assignment = $order->courierAssignment()->firstOrCreate([], [
                'status' => CourierAssignmentStatus::SEARCHING,
                'search_started_at' => now(),
            ]);

            $query = CourierProfile::query()
                ->where('is_online', true)
                ->whereNotNull('last_lat')
                ->whereNotNull('last_lng')
                ->whereHas('user', function ($builder) use ($order, $excludedCourierId): void {
                    $builder->where('role', UserRole::COURIER)
                        ->where('is_active', true)
                        ->where('city_id', $order->city_id);

                    if ($excludedCourierId) {
                        $builder->whereKeyNot($excludedCourierId);
                    }
                })
                ->with('user');

            if ($order->delivery_lat !== null && $order->delivery_lng !== null) {
                $query->orderByRaw(
                    'POWER(last_lat - ?, 2) + POWER(last_lng - ?, 2) asc',
                    [$order->delivery_lat, $order->delivery_lng],
                );
            } else {
                $query->orderByDesc('last_seen_at');
            }

            $profile = $query->first();

            if (! $profile) {
                $assignment->forceFill([
                    'courier_id' => null,
                    'status' => CourierAssignmentStatus::SEARCHING,
                    'search_started_at' => $assignment->search_started_at ?? now(),
                ])->save();

                $order->forceFill([
                    'status' => OrderStatus::SEARCHING_COURIER,
                ])->save();

                return $assignment->fresh();
            }

            $assignment->forceFill([
                'courier_id' => $profile->user_id,
                'status' => CourierAssignmentStatus::OFFERED,
                'search_started_at' => $assignment->search_started_at ?? now(),
                'assigned_at' => now(),
                'last_offered_at' => now(),
            ])->save();

            $order->forceFill([
                'status' => OrderStatus::COURIER_ASSIGNED,
            ])->save();

            CourierAssigned::dispatch($order->fresh()->load('courierAssignment.courier'));

            return $assignment->fresh('courier');
        });
    }

    public function acceptOrder(User $courier, Order $order): CourierAssignment
    {
        $assignment = $this->getCourierAssignment($courier, $order, CourierAssignmentStatus::OFFERED);

        $assignment->forceFill([
            'status' => CourierAssignmentStatus::ACCEPTED,
            'accepted_at' => now(),
        ])->save();

        $order->forceFill([
            'status' => OrderStatus::OUT_FOR_DELIVERY,
        ])->save();

        return $assignment->fresh('courier');
    }

    public function rejectOrder(User $courier, Order $order): CourierAssignment
    {
        $assignment = $this->getCourierAssignment($courier, $order, CourierAssignmentStatus::OFFERED);
        $excludedCourierId = $courier->id;

        $assignment->forceFill([
            'status' => CourierAssignmentStatus::REJECTED,
            'last_rejected_at' => now(),
        ])->save();

        $order->forceFill([
            'status' => OrderStatus::SEARCHING_COURIER,
        ])->save();

        AssignCourierJob::dispatch($order->id, $excludedCourierId);

        return $assignment->fresh();
    }

    public function updateLocation(User $courier, array $payload): array
    {
        $profile = $courier->courierProfile()->firstOrCreate([], [
            'vehicle_type' => 'bike',
            'is_online' => false,
        ]);

        $profile->forceFill([
            'last_lat' => $payload['lat'],
            'last_lng' => $payload['lng'],
            'is_online' => $payload['is_online'] ?? $profile->is_online,
            'last_seen_at' => now(),
        ])->save();

        $activeOrderIds = $courier->courierAssignments()
            ->whereIn('status', [CourierAssignmentStatus::OFFERED, CourierAssignmentStatus::ACCEPTED])
            ->pluck('order_id')
            ->all();

        CourierLocationUpdated::dispatch($courier, $profile, $activeOrderIds);

        return [
            'courier_id' => $courier->id,
            'is_online' => $profile->is_online,
            'last_lat' => $profile->last_lat,
            'last_lng' => $profile->last_lng,
            'last_seen_at' => $profile->last_seen_at,
            'active_order_ids' => $activeOrderIds,
        ];
    }

    public function updateAvailability(User $courier, bool $isOnline): CourierProfile
    {
        $profile = $courier->courierProfile()->firstOrCreate([], [
            'vehicle_type' => 'bike',
            'is_online' => false,
        ]);

        $profile->forceFill([
            'is_online' => $isOnline,
            'last_seen_at' => now(),
        ])->save();

        return $profile;
    }

    private function getCourierAssignment(User $courier, Order $order, CourierAssignmentStatus $expectedStatus): CourierAssignment
    {
        $assignment = $order->courierAssignment;

        if (! $assignment || $assignment->courier_id !== $courier->id || ($assignment->status->value ?? $assignment->status) !== $expectedStatus->value) {
            throw ValidationException::withMessages([
                'order' => 'This order is not currently assigned to the courier in the expected state.',
            ]);
        }

        return $assignment;
    }
}
