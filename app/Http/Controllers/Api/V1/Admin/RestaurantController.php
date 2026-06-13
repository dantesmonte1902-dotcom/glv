<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreRestaurantRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RestaurantResource::collection(Restaurant::query()->with('branches.city')->latest()->paginate());
    }

    public function store(StoreRestaurantRequest $request): RestaurantResource
    {
        return new RestaurantResource(Restaurant::query()->create($request->validated()));
    }

    public function update(StoreRestaurantRequest $request, Restaurant $restaurant): RestaurantResource
    {
        $restaurant->update($request->validated());

        return new RestaurantResource($restaurant->refresh()->load('branches.city'));
    }
}
