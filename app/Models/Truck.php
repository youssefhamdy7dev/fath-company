<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relationships
     */
    public function bill()
    {
        return $this->hasOne(Bill::class);
    }
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
    public function truckFruits(): HasMany
    {
        return $this->hasMany(TruckFruit::class, 'truck_id')
            ->join('fruits', 'fruits.id', '=', 'truck_fruit.fruit_id')
            ->orderBy('fruits.name', 'ASC')
            ->select('truck_fruit.*'); // VERY IMPORTANT
    }

    /**
     * Accessors
     */
    public function getFruitNamesAttribute(): string
    {
        $this->loadMissing(['truckFruits.fruit']);
        return $this->truckFruits
            ->map(fn($tf) => $tf->fruit->name)
            ->join(' - ');
    }

    public function getClientNamesAttribute(): string
    {
        $this->loadMissing(['truckFruits.client']);
        return $this->truckFruits
            ->map(fn($tf) => $tf->client->name ?? 'مشتروات')
            ->join(' - ');
    }

    public function getTotalSecondClassBoxesAttribute(): int
    {
        return $this->truckFruits()->sum('second_class_boxes');
    }

    public function getTotalThirdClassBoxesAttribute(): int
    {
        return $this->truckFruits()->sum('third_class_boxes');
    }

    public function getTotalsByBoxClassAttribute(): array
    {
        // Flatten all purchases on this truck
        $allPurchases = $this->truckFruits->flatMap(fn($tf) => $tf->customerPurchases);

        // Total amount for all purchases
        $totalAmount = $allPurchases->sum('computed_total');

        // Group first by box_class → then by applied price
        $grouped = $allPurchases
            ->groupBy('box_class')
            ->map(function ($classGroup) {
                return $classGroup
                    ->groupBy(function ($cp) {
                        if ($cp->computed_unit_price !== null) {
                            return 'unit_' . number_format($cp->computed_unit_price, 2, '.', '');
                        }

                        return 'box_' . number_format($cp->computed_box_price, 2, '.', '');
                    })
                    ->map(function ($priceGroup) {
                        return [
                            'unit_price'   => $priceGroup->first()->computed_unit_price,
                            'box_price'    => $priceGroup->first()->computed_box_price,
                            'total_boxes'  => $priceGroup->sum('number_of_boxes'),
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

    public function getNumberOfBoughtBoxesAttribute(): int
    {
        return $this->truckFruits
            ->flatMap(fn($tf) => $tf->customerPurchases)
            ->sum('number_of_boxes');
    }

    public function getNumberOfLowClassBoughtBoxesAttribute(): int
    {
        return $this->truckFruits
            ->flatMap(fn($tf) => $tf->customerPurchases->reject(fn($cp) => $cp->box_class == 'first'))
            ->sum('number_of_boxes');
    }

    public function getNumberOfLowClassBoxesAttribute(): int
    {
        return $this->truckFruits
            ->sum('second_class_boxes') + $this->truckFruits->sum('third_class_boxes');
    }
}
