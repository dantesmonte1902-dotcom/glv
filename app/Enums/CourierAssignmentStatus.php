<?php

namespace App\Enums;

enum CourierAssignmentStatus: string
{
    case SEARCHING = 'searching';
    case OFFERED = 'offered';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case TIMED_OUT = 'timed_out';
}
