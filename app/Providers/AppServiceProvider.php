<?php

namespace App\Providers;

use App\Models\City;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use App\Models\User;
use App\Policies\CityPolicy;
use App\Policies\OrderPolicy;
use App\Policies\RestaurantBranchPolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(City::class, CityPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Restaurant::class, RestaurantPolicy::class);
        Gate::policy(RestaurantBranch::class, RestaurantBranchPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
