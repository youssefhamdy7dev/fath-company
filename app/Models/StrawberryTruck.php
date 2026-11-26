<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrawberryTruck extends Model
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
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the driver assigned to this truck.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the truck's strawberry variety records, ordered by fruit type.
     */
    public function truckFruits(): HasMany
    {
        return $this->hasMany(StrawberryTruckFruit::class, 'truck_id')
            ->orderBy('fruit_type', 'ASC');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get strawberry fruit names associated with this truck.
     */
    public function getFruitNamesAttribute(): string
    {
        return $this->truckFruits
            ->pluck('fruit_type')
            ->unique()
            ->join(' - ');
    }

    /**
     * Get client names associated with this truck.
     */
    public function getClientNamesAttribute(): string
    {
        $this->loadMissing('truckFruits.client');

        return $this->truckFruits
            ->map(fn($truckFruit) => $truckFruit->client->name ?? 'مشتروات')
            ->join(' - ');
    }

    /**
     * Get the total number of second-class boxes.
     */
    public function getTotalSecondClassBoxesAttribute(): int
    {
        return (int) $this->truckFruits()->sum('second_class_boxes');
    }

    /**
     * Get the total number of third-class boxes.
     */
    public function getTotalThirdClassBoxesAttribute(): int
    {
        return (int) $this->truckFruits()->sum('third_class_boxes');
    }

    /**
     * Get purchase totals grouped by box class and applied price.
     */
    public function getTotalsByBoxClassAttribute(): array
    {
        $allPurchases = $this->truckFruits
            ->flatMap(fn($truckFruit) => $truckFruit->customerPurchases);

        $totalAmount = $allPurchases->sum('computed_total');

        $grouped = $allPurchases
            ->groupBy('box_class')
            ->map(function ($classGroup) {
                return $classGroup
                    ->groupBy(function ($purchase) {
                        if (
                            $purchase->unique_box_price !== null
                            || $purchase->computed_unit_price === null
                        ) {
                            return 'box_' . number_format(
                                $purchase->computed_box_price,
                                2,
                                '.',
                                ''
                            );
                        }

                        return 'unit_' . number_format(
                            $purchase->computed_unit_price,
                            2,
                            '.',
                            ''
                        );
                    })
                    ->map(function ($priceGroup) {
                        return [
                            'unit_price' => $priceGroup->first()->computed_unit_price,
                            'box_price' => $priceGroup->first()->computed_box_price,
                            'total_boxes' => $priceGroup->sum('number_of_boxes'),
                            'total_weight' => $priceGroup->sum('total_weight'),
                            'total_amount' => $priceGroup->sum('computed_total'),
                        ];
                    });
            });

        return [
            'by_box_class' => $grouped,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Get the total number of purchased boxes across all truck varieties.
     */
    public function getNumberOfBoughtBoxesAttribute(): int
    {
        return $this->truckFruits
            ->flatMap(fn($truckFruit) => $truckFruit->customerPurchases)
            ->sum('number_of_boxes');
    }

    /**
     * Get the total number of purchased boxes that are not first class.
     */
    public function getNumberOfLowClassBoughtBoxesAttribute(): int
    {
        return $this->truckFruits
            ->flatMap(
                fn($truckFruit) => $truckFruit->customerPurchases
                    ->reject(fn($purchase) => $purchase->box_class === 'first')
            )
            ->sum('number_of_boxes');
    }

    /**
     * Get the total number of second- and third-class boxes.
     */
    public function getNumberOfLowClassBoxesAttribute(): int
    {
        return $this->truckFruits->sum('second_class_boxes')
            + $this->truckFruits->sum('third_class_boxes');
    }

    /**
     * Get the truck variety ID when exactly one record remains open.
     */
    public function getAutoSelectableTruckFruitIdAttribute(): ?int
    {
        $this->loadMissing('truckFruits.customerPurchases');

        $notCompleted = $this->truckFruits
            ->filter(fn($truckFruit) => ! $truckFruit->is_fully_purchased);

        return $notCompleted->count() === 1
            ? $notCompleted->first()->id
            : null;
    }
}
