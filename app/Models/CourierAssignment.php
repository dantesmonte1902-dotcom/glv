<?php

namespace App\Models;

use App\Enums\CourierAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_id',
        'status',
        'search_started_at',
        'assigned_at',
        'accepted_at',
        'last_offered_at',
        'last_rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourierAssignmentStatus::class,
            'search_started_at' => 'datetime',
            'assigned_at' => 'datetime',
            'accepted_at' => 'datetime',
            'last_offered_at' => 'datetime',
            'last_rejected_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
