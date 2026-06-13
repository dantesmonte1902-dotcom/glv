<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreBranchRequest;
use App\Http\Resources\RestaurantBranchResource;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        return RestaurantBranchResource::collection($restaurant->branches()->with('city')->paginate());
    }

    public function store(StoreBranchRequest $request, Restaurant $restaurant): RestaurantBranchResource
    {
        return new RestaurantBranchResource($restaurant->branches()->create($request->validated()));
    }

    public function update(StoreBranchRequest $request, RestaurantBranch $branch): RestaurantBranchResource
    {
        $branch->update($request->validated());

        return new RestaurantBranchResource($branch->refresh()->load('city', 'restaurant'));
    }
}
