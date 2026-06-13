<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateCourierActivationRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class CourierController extends Controller
{
    public function updateActivation(UpdateCourierActivationRequest $request, User $courier): UserResource
    {
        $courier->update($request->validated());

        return new UserResource($courier->refresh()->loadMissing('city', 'courierProfile'));
    }
}
