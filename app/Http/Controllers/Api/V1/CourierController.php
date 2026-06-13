<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Courier\UpdateCourierAvailabilityRequest;
use App\Http\Requests\Api\V1\Courier\UpdateCourierLocationRequest;
use App\Services\Courier\CourierAssignmentService;
use Illuminate\Http\JsonResponse;

class CourierController extends Controller
{
    public function __construct(private readonly CourierAssignmentService $courierAssignmentService) {}

    public function updateLocation(UpdateCourierLocationRequest $request): JsonResponse
    {
        $result = $this->courierAssignmentService->updateLocation($request->user(), $request->validated());

        return response()->json($result);
    }

    public function updateAvailability(UpdateCourierAvailabilityRequest $request): JsonResponse
    {
        $profile = $this->courierAssignmentService->updateAvailability($request->user(), $request->boolean('is_online'));

        return response()->json([
            'courier_id' => $request->user()->id,
            'is_online' => $profile->is_online,
        ]);
    }
}
