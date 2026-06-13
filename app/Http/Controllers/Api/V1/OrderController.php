<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Orders\OrderDecisionRequest;
use App\Http\Requests\Api\V1\Orders\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Courier\CourierAssignmentService;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CourierAssignmentService $courierAssignmentService,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        return response()->json(
            (new OrderResource($this->orderService->create($request->user(), $request->validated())))->resolve(),
            201,
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json(
            (new OrderResource($this->orderService->showForUser($request->user(), $order)))->resolve(),
        );
    }

    public function approve(Request $request, Order $order): JsonResponse
    {
        $this->authorize('approve', $order);

        return response()->json(
            (new OrderResource($this->orderService->approve($request->user(), $order)))->resolve(),
        );
    }

    public function reject(OrderDecisionRequest $request, Order $order): JsonResponse
    {
        $this->authorize('reject', $order);

        return response()->json(
            (new OrderResource($this->orderService->reject($request->user(), $order, $request->validated('reason'))))->resolve(),
        );
    }

    public function assignCourier(Request $request, Order $order): JsonResponse
    {
        $this->authorize('assignCourier', $order);

        $assignment = $this->orderService->dispatchAssignment($request->user(), $order);

        return response()->json([
            'message' => 'Courier assignment dispatched.',
            'assignment' => [
                'id' => $assignment->id,
                'status' => $assignment->status->value,
                'courier_id' => $assignment->courier_id,
            ],
        ]);
    }

    public function acceptCourier(Request $request, Order $order): JsonResponse
    {
        $this->authorize('acceptCourier', $order);

        $this->courierAssignmentService->acceptOrder($request->user(), $order);

        return response()->json(
            (new OrderResource($this->orderService->showForUser($request->user(), $order->fresh())))->resolve(),
        );
    }

    public function rejectCourier(Request $request, Order $order): JsonResponse
    {
        $this->authorize('rejectCourier', $order);

        $this->courierAssignmentService->rejectOrder($request->user(), $order);

        return response()->json(
            (new OrderResource($this->orderService->showForUser($request->user(), $order->fresh())))->resolve(),
        );
    }

    public function deliver(Request $request, Order $order): JsonResponse
    {
        $this->authorize('deliver', $order);

        return response()->json(
            (new OrderResource($this->orderService->deliver($request->user(), $order)))->resolve(),
        );
    }
}
