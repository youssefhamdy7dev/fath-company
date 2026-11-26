<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrawberryCustomerPurchase extends Model
{
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
     * Strawberry truck variety associated with the purchase.
     */
    public function truckFruit(): BelongsTo
    {
        return $this->belongsTo(
            StrawberryTruckFruit::class,
            'strawberry_truck_fruit_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the purchased strawberry name.
     */
    public function getFruitNameAttribute(): ?string
    {
        return $this->truckFruit?->fruit_type;
    }

    /**
     * Get the customer name.
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->customer->name ?? 'نقدية';
    }

    /**
     * Get the box type display name.
     */
    public function getBoxTypeNameAttribute(): ?string
    {
        $boxType = $this->truckFruit?->box_type;

        if ($boxType === null) {
            return null;
        }

        return self::BOX_TYPE_NAMES[$boxType] ?? $boxType;
    }

    /**
     * Get the effective total weight.
     *
     * Priority:
     * 1. Manually entered total weight.
     * 2. Unified truck variety weight multiplied by purchased boxes.
     * 3. Null when neither value is available.
     */
    public function getTotalWeightAttribute($value): ?float
    {
        if (! is_null($value)) {
            return (float) $value;
        }

        if (! is_null($this->truckFruit?->unified_weight)) {
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
            ?? $this->truckFruit?->unified_unit_price;
    }

    /**
     * Get the effective box price.
     */
    public function getComputedBoxPriceAttribute()
    {
        return $this->unique_box_price
            ?? $this->truckFruit?->unified_box_price;
    }

    /**
     * Determine whether this purchase uses the special weight calculation.
     *
     * This applies only to box-priced truck varieties when an actual
     * manual weight and a custom unit price have both been supplied.
     */
    public function getIsSpecialCalculationAttribute(): bool
    {
        return $this->truckFruit?->unified_box_price !== null
            && $this->unique_unit_price !== null
            && $this->unique_box_price === null
            && ! is_null($this->attributes['total_weight'] ?? null);
    }

    /**
     * Calculate the total purchase amount.
     *
     * Priority:
     * 1. Custom box price multiplied by number of boxes.
     * 2. Special calculation: actual weight multiplied by custom unit price.
     * 3. Normal unit price multiplied by total weight.
     * 4. Normal box price multiplied by number of boxes.
     */
    public function getComputedTotalAttribute(): int
    {
        if ($this->unique_box_price !== null) {
            return intval(
                $this->unique_box_price * $this->number_of_boxes
            );
        }

        if ($this->is_special_calculation) {
            return intval(
                $this->total_weight * $this->unique_unit_price
            );
        }

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
