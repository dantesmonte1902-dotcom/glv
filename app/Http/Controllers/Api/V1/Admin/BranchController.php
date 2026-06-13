<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreBranchRequest;
use App\Http\Resources\RestaurantBranchResource;
use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [RestaurantBranch::class, $restaurant]);

        $query = $restaurant->branches()->with('city');

        if (($request->user()->role->value ?? $request->user()->role) === 'restaurant') {
            $query->where('city_id', $request->user()->city_id);
        }

        return RestaurantBranchResource::collection($query->paginate());
    }

    public function store(StoreBranchRequest $request, Restaurant $restaurant): RestaurantBranchResource
    {
        $this->authorize('create', [RestaurantBranch::class, $restaurant]);

        return new RestaurantBranchResource($restaurant->branches()->create($request->validated()));
    }

    public function update(StoreBranchRequest $request, RestaurantBranch $branch): RestaurantBranchResource
    {
        $this->authorize('update', $branch);

        $branch->update($request->validated());

        return new RestaurantBranchResource($branch->refresh()->load('city', 'restaurant'));
    }
}
