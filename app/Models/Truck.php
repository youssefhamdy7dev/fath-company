<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Truck extends Model
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
     * Get the bill associated with this truck.
     */
    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    /**
     * Get the driver assigned to this truck.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the truck's fruit records, ordered by fruit name.
     *
     * The explicit select is important to preserve the truck_fruit
     * model's columns after joining the fruits table.
     */
    public function truckFruits(): HasMany
    {
        return $this->hasMany(TruckFruit::class, 'truck_id')
            ->join('fruits', 'fruits.id', '=', 'truck_fruit.fruit_id')
            ->orderBy('fruits.name', 'ASC')
            ->select('truck_fruit.*');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get fruit names associated with this truck.
     */
    public function getFruitNamesAttribute(): string
    {
        $this->loadMissing(['truckFruits.fruit']);

        return $this->truckFruits
            ->map(fn($truckFruit) => $truckFruit->fruit->name)
            ->join(' - ');
    }

    /**
     * Get client names associated with this truck.
     */
    public function getClientNamesAttribute(): string
    {
        $this->loadMissing(['truckFruits.client']);

        return $this->truckFruits
            ->map(fn($truckFruit) => $truckFruit->client->name ?? 'مشتروات')
            ->join(' - ');
    }

    /**
     * Get the total number of second-class boxes.
     */
    public function getTotalSecondClassBoxesAttribute(): int
    {
        return $this->truckFruits()->sum('second_class_boxes');
    }

    /**
     * Get the total number of third-class boxes.
     */
    public function getTotalThirdClassBoxesAttribute(): int
    {
        return $this->truckFruits()->sum('third_class_boxes');
    }

    /**
     * Get purchase totals grouped by box class and applied price.
     */
    public function getTotalsByBoxClassAttribute(): array
    {
        // Flatten all purchases on this truck.
        $allPurchases = $this->truckFruits
            ->flatMap(fn($truckFruit) => $truckFruit->customerPurchases);

        // Calculate the total amount for all purchases.
        $totalAmount = $allPurchases->sum('computed_total');

        // Group first by box class, then by applied price.
        $grouped = $allPurchases
            ->groupBy('box_class')
            ->map(function ($classGroup) {
                return $classGroup
                    ->groupBy(function ($purchase) {
                        if ($purchase->computed_unit_price !== null) {
                            return 'unit_' . number_format(
                                $purchase->computed_unit_price,
                                2,
                                '.',
                                ''
                            );
                        }

                        return 'box_' . number_format(
                            $purchase->computed_box_price,
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
     * Get the total number of purchased boxes across all truck fruits.
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
                    ->reject(fn($purchase) => $purchase->box_class == 'first')
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
     * Get the truck fruit ID when exactly one record remains open.
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
