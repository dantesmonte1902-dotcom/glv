<?php

use App\Http\Controllers\Api\V1\Admin\BranchController;
use App\Http\Controllers\Api\V1\Admin\CityController;
use App\Http\Controllers\Api\V1\Admin\CourierController as AdminCourierController;
use App\Http\Controllers\Api\V1\Admin\RestaurantController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourierController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware(['auth:sanctum', 'tenant'])->group(function (): void {
        Route::post('orders', [OrderController::class, 'store'])->middleware('role:customer');
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->middleware('role:restaurant,admin');
        Route::post('orders/{order}/reject', [OrderController::class, 'reject'])->middleware('role:restaurant,admin');
        Route::post('orders/{order}/assign-courier', [OrderController::class, 'assignCourier'])->middleware('role:admin,restaurant');
        Route::post('orders/{order}/accept-courier', [OrderController::class, 'acceptCourier'])->middleware('role:courier');
        Route::post('orders/{order}/reject-courier', [OrderController::class, 'rejectCourier'])->middleware('role:courier');
        Route::post('orders/{order}/deliver', [OrderController::class, 'deliver'])->middleware('role:courier,admin');

        Route::patch('courier/location', [CourierController::class, 'updateLocation'])->middleware('role:courier');
        Route::patch('courier/availability', [CourierController::class, 'updateAvailability'])->middleware('role:courier');

        Route::prefix('admin')->group(function (): void {
            Route::get('cities', [CityController::class, 'index']);
            Route::post('cities', [CityController::class, 'store']);
            Route::patch('cities/{city}', [CityController::class, 'update']);

            Route::get('restaurants', [RestaurantController::class, 'index'])->middleware('role:admin,restaurant');
            Route::post('restaurants', [RestaurantController::class, 'store'])->middleware('role:admin');
            Route::patch('restaurants/{restaurant}', [RestaurantController::class, 'update'])->middleware('role:admin,restaurant');

            Route::get('restaurants/{restaurant}/branches', [BranchController::class, 'index'])->middleware('role:admin,restaurant');
            Route::post('restaurants/{restaurant}/branches', [BranchController::class, 'store'])->middleware('role:admin,restaurant');
            Route::patch('branches/{branch}', [BranchController::class, 'update'])->middleware('role:admin,restaurant');

            Route::patch('couriers/{courier}/activation', [AdminCourierController::class, 'updateActivation'])->middleware('role:admin');
        });
    });
});
