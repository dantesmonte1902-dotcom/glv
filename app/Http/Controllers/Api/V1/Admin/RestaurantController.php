<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreRestaurantRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Restaurant::class);

        $query = Restaurant::query()->with('branches.city')->latest();

        if (($request->user()->role->value ?? $request->user()->role) !== 'admin') {
            $query->whereKey($request->user()->restaurant_id);
        }

        return RestaurantResource::collection($query->paginate());
    }

    public function store(StoreRestaurantRequest $request): RestaurantResource
    {
        $this->authorize('create', Restaurant::class);

        return new RestaurantResource(Restaurant::query()->create($request->validated()));
    }

    public function update(StoreRestaurantRequest $request, Restaurant $restaurant): RestaurantResource
    {
        $this->authorize('update', $restaurant);

        $restaurant->update($request->validated());

        return new RestaurantResource($restaurant->refresh()->load('branches.city'));
    }
}
