<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'billing_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the truck associated with this bill.
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * Get the items belonging to this bill.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the bill price after deducting freight,
     * percentage value, and expenses from the grand total.
     */
    public function getBillPriceAttribute()
    {
        $grandTotal = $this->grand_total;

        $percentageValue = intval(
            intval(($this->percentage / 100) * $grandTotal)
        );

        $expensesValue = intval($this->expenses ?? 0);
        $freight = intval($this->truck->freight ?? 0);

        return $grandTotal - (
            $freight + $percentageValue + $expensesValue
        );
    }
}
