<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TruckFruit extends Pivot
{
    protected $guarded = [];

    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function customerPurchases(): HasMany
    {
        return $this->hasMany(CustomerPurchase::class, 'truck_fruit_id');
    }

    // Accessors
    public function getPurchasedBoxesAttribute(): int
    {
        return $this->customerPurchases()->sum('number_of_boxes');
    }

    public function getRemainingBoxesAttribute(): int
    {
        return max(0, $this->number_of_boxes - $this->purchased_boxes);
    }

    public function getIsFullyPurchasedAttribute(): bool
    {
        return $this->remaining_boxes === 0;
    }
}
