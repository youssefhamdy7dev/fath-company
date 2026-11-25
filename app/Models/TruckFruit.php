<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TruckFruit extends Pivot
{
    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the fruit associated with this truck record.
     */
    public function fruit(): BelongsTo
    {
        return $this->belongsTo(Fruit::class);
    }

    /**
     * Get the truck associated with this fruit record.
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * Get the client associated with this fruit record.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get customer purchases associated with this truck fruit.
     */
    public function customerPurchases(): HasMany
    {
        return $this->hasMany(CustomerPurchase::class, 'truck_fruit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the total number of purchased boxes.
     */
    public function getPurchasedBoxesAttribute(): int
    {
        return $this->customerPurchases()->sum('number_of_boxes');
    }

    /**
     * Get the number of boxes remaining.
     */
    public function getRemainingBoxesAttribute(): int
    {
        return max(0, $this->number_of_boxes - $this->purchased_boxes);
    }

    /**
     * Determine whether all boxes have been purchased.
     */
    public function getIsFullyPurchasedAttribute(): bool
    {
        return $this->remaining_boxes === 0;
    }
}
