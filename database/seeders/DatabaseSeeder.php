<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::query()->firstOrCreate([
            'name' => 'Sarajevo',
        ], [
            'country_code' => 'BA',
            'is_active' => true,
        ]);

        User::factory()->create([
            'city_id' => $city->id,
            'name' => 'GLV Admin',
            'email' => 'admin@glv.local',
            'phone' => '+38761000000',
            'role' => UserRole::ADMIN,
        ]);

        $restaurant = Restaurant::query()->firstOrCreate([
            'brand_slug' => 'demo-burger',
        ], [
            'name' => 'Demo Burger',
            'is_active' => true,
        ]);

        RestaurantBranch::query()->firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'city_id' => $city->id,
            'name' => 'Demo Burger Center',
        ], [
            'address' => 'Marshal Tito Street 1, Sarajevo',
            'lat' => 43.8563000,
            'lng' => 18.4131000,
            'service_radius_km' => 6,
            'is_active' => true,
        ]);
    }
}
