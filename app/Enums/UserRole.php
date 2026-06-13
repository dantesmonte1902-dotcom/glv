<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case COURIER = 'courier';
    case RESTAURANT = 'restaurant';
    case ADMIN = 'admin';
}
