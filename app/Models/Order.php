<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'customer_id',
        'branch_id',
        'domain_type',
        'status',
        'subtotal_amount',
        'delivery_fee',
        'total_amount',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'notes',
        'approved_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'delivery_lat' => 'float',
            'delivery_lng' => 'float',
            'approved_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function courierAssignment(): HasOne
    {
        return $this->hasOne(CourierAssignment::class);
    }
}
