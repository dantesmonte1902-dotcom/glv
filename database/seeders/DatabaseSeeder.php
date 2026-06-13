<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\CourierProfile;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sarajevo = City::query()->updateOrCreate([
            'name' => 'Sarajevo',
        ], [
            'country_code' => 'BA',
            'is_active' => true,
        ]);

        City::query()->updateOrCreate([
            'name' => 'Mostar',
        ], [
            'country_code' => 'BA',
            'is_active' => true,
        ]);

        City::query()->updateOrCreate([
            'name' => 'Tuzla',
        ], [
            'country_code' => 'BA',
            'is_active' => true,
        ]);

        $restaurant = Restaurant::query()->updateOrCreate([
            'brand_slug' => 'demo-burger',
        ], [
            'name' => 'Demo Burger',
            'is_active' => true,
        ]);

        RestaurantBranch::query()->updateOrCreate([
            'restaurant_id' => $restaurant->id,
            'city_id' => $sarajevo->id,
            'name' => 'Demo Burger Center',
        ], [
            'address' => 'Marshal Tito Street 1, Sarajevo',
            'lat' => 43.8563000,
            'lng' => 18.4131000,
            'service_radius_km' => 6,
            'is_active' => true,
        ]);

        User::query()->updateOrCreate([
            'email' => 'admin@glv.local',
        ], [
            'city_id' => $sarajevo->id,
            'restaurant_id' => null,
            'name' => 'GLV Admin',
            'phone' => '+38761000000',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->updateOrCreate([
            'email' => 'restaurant@glv.local',
        ], [
            'city_id' => $sarajevo->id,
            'restaurant_id' => $restaurant->id,
            'name' => 'Demo Restaurant Manager',
            'phone' => '+38761000001',
            'password' => Hash::make('password'),
            'role' => UserRole::RESTAURANT,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->updateOrCreate([
            'email' => 'customer@glv.local',
        ], [
            'city_id' => $sarajevo->id,
            'restaurant_id' => null,
            'name' => 'Demo Customer',
            'phone' => '+38761000002',
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $courier = User::query()->updateOrCreate([
            'email' => 'courier@glv.local',
        ], [
            'city_id' => $sarajevo->id,
            'restaurant_id' => null,
            'name' => 'Demo Courier',
            'phone' => '+38761000003',
            'password' => Hash::make('password'),
            'role' => UserRole::COURIER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        CourierProfile::query()->updateOrCreate([
            'user_id' => $courier->id,
        ], [
            'is_online' => true,
            'vehicle_type' => 'bike',
            'last_lat' => 43.8570000,
            'last_lng' => 18.4120000,
            'last_seen_at' => now(),
        ]);
    }
}
