<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrawberryTruckFruit extends Model
{
    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'unified_weight' => 'float',
    ];

    /**
     * Box type display names.
     */
    public const BOX_TYPE_NAMES = [
        'big_box' => 'برنيكة كبيرة',
        'normal_box' => 'برنيكة وسط',
        'small_box' => 'برنيكة صغيرة',
        'small_net' => 'برنيكة شبك',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the truck associated with this strawberry variety.
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(StrawberryTruck::class, 'truck_id');
    }

    /**
     * Get the client associated with this strawberry variety.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get customer purchases associated with this strawberry variety.
     */
    public function customerPurchases(): HasMany
    {
        return $this->hasMany(
            StrawberryCustomerPurchase::class,
            'strawberry_truck_fruit_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the strawberry fruit name.
     */
    public function getFruitNameAttribute(): string
    {
        return $this->fruit_type;
    }

    /**
     * Get the box type display name.
     */
    public function getBoxTypeNameAttribute(): string
    {
        return self::BOX_TYPE_NAMES[$this->box_type] ?? $this->box_type;
    }

    /**
     * Get the total number of purchased boxes.
     */
    public function getPurchasedBoxesAttribute(): int
    {
        return (int) $this->customerPurchases()->sum('number_of_boxes');
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
