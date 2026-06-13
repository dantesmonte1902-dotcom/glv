<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_RESTAURANT_APPROVAL = 'pending_restaurant_approval';
    case SEARCHING_COURIER = 'searching_courier';
    case COURIER_ASSIGNED = 'courier_assigned';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
