<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPurchase extends Model
{
    /**
     * Database table associated with the model.
     */
    protected $table = 'customer_purchase';

    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'date' => 'date',
        'total_weight' => 'float',
    ];

    /**
     * Box type display names.
     */
    private const BOX_TYPE_NAMES = [
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
     * Customer who made the purchase.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Truck fruit record associated with the purchase.
     */
    public function truckFruit(): BelongsTo
    {
        return $this->belongsTo(TruckFruit::class, 'truck_fruit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the purchased fruit name.
     */
    public function getFruitNameAttribute()
    {
        return $this->truckFruit->fruit->name;
    }

    /**
     * Get the customer name.
     */
    public function getCustomerNameAttribute()
    {
        return $this->customer->name ?? 'نقدية';
    }

    /**
     * Get the box type display name.
     */
    public function getBoxTypeNameAttribute()
    {
        $boxType = $this->truckFruit->box_type;

        return self::BOX_TYPE_NAMES[$boxType] ?? $boxType;
    }

    /**
     * Get the effective total weight.
     *
     * Priority:
     * 1. Manually entered total weight.
     * 2. Unified truck fruit weight multiplied by purchased boxes.
     * 3. Null when neither value is available.
     */
    public function getTotalWeightAttribute($value)
    {
        if (!is_null($value)) {
            return (float) $value;
        }

        if (!is_null($this->truckFruit?->unified_weight)) {
            return $this->truckFruit->unified_weight
                * $this->number_of_boxes;
        }

        return null;
    }

    /**
     * Get the effective unit price.
     */
    public function getComputedUnitPriceAttribute()
    {
        return $this->unique_unit_price
            ?? $this->truckFruit->unified_unit_price;
    }

    /**
     * Get the effective box price.
     */
    public function getComputedBoxPriceAttribute()
    {
        return $this->unique_box_price
            ?? $this->truckFruit->unified_box_price;
    }

    /**
     * Calculate the total purchase amount.
     *
     * Uses unit price and total weight when both are truthy.
     * Otherwise, falls back to box price and number of boxes.
     */
    public function getComputedTotalAttribute(): int
    {
        $unitPrice = $this->computed_unit_price;
        $boxPrice = $this->computed_box_price;

        if ($this->total_weight && $unitPrice) {
            return intval($unitPrice * $this->total_weight);
        }

        return intval($boxPrice * $this->number_of_boxes);
    }

    /**
     * Get the box class display name.
     */
    public function getComputedClassNameAttribute(): string
    {
        return match ($this->box_class) {
            'first' => 'فاخر',
            'second' => 'نمرة 2',
            'third' => 'نمرة 3',
            default => '—',
        };
    }
}
