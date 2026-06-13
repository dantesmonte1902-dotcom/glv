<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to access this resource.');
        }

        if (! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Your account is disabled.');
        }

        $role = $user->role->value ?? $user->role;

        if ($role === UserRole::ADMIN->value) {
            return $next($request);
        }

        if ($role === UserRole::RESTAURANT->value && ! $user->restaurant_id) {
            abort(Response::HTTP_FORBIDDEN, 'Your restaurant account is not linked to a restaurant.');
        }

        if (in_array($role, [UserRole::CUSTOMER->value, UserRole::COURIER->value, UserRole::RESTAURANT->value], true) && ! $user->city_id) {
            abort(Response::HTTP_FORBIDDEN, 'Your account is not linked to a city.');
        }

        $this->authorizeCity($user, $request->route('city'));
        $this->authorizeRestaurant($user, $request->route('restaurant'));
        $this->authorizeBranch($user, $request->route('branch'));
        $this->authorizeOrder($user, $request->route('order'));

        return $next($request);
    }

    private function authorizeCity($user, mixed $city): void
    {
        if (! $city instanceof City) {
            return;
        }

        if (($user->role->value ?? $user->role) !== UserRole::RESTAURANT->value
            && ! in_array($user->role->value ?? $user->role, [UserRole::CUSTOMER->value, UserRole::COURIER->value], true)) {
            return;
        }

        if (! $user->belongsToCity($city->id)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to access this city.');
        }
    }

    private function authorizeRestaurant($user, mixed $restaurant): void
    {
        if (! $restaurant instanceof Restaurant) {
            return;
        }

        if (($user->role->value ?? $user->role) !== UserRole::RESTAURANT->value) {
            return;
        }

        if (! $user->ownsRestaurant($restaurant)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to access this restaurant.');
        }
    }

    private function authorizeBranch($user, mixed $branch): void
    {
        if (! $branch instanceof RestaurantBranch) {
            return;
        }

        $role = $user->role->value ?? $user->role;

        if ($role === UserRole::RESTAURANT->value
            && (! $user->ownsRestaurantId($branch->restaurant_id) || ! $user->belongsToCity($branch->city_id))) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to access this branch.');
        }
    }

    private function authorizeOrder($user, mixed $order): void
    {
        if (! $order instanceof Order) {
            return;
        }

        $order->loadMissing('branch.restaurant', 'courierAssignment');

        $role = $user->role->value ?? $user->role;

        if ($role === UserRole::RESTAURANT->value
            && (! $user->ownsRestaurantId($order->branch->restaurant_id) || ! $user->belongsToCity($order->city_id))) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to access this order.');
        }

        if (in_array($role, [UserRole::CUSTOMER->value, UserRole::COURIER->value], true) && ! $user->belongsToCity($order->city_id)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to access this order.');
        }
    }
}
