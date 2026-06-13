<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CityResource::collection(City::query()->latest()->paginate());
    }

    public function store(StoreCityRequest $request): CityResource
    {
        return new CityResource(City::query()->create($request->validated()));
    }

    public function update(StoreCityRequest $request, City $city): CityResource
    {
        $city->update($request->validated());

        return new CityResource($city->refresh());
    }
}
