<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomerPurchase extends Pivot
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relationship: The customer who bought the fruit.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship: The specific fruit + box type belonging to a truck.
     * truck_fruit_id → truck_fruit table (pivot row)
     */
    public function truckFruit()
    {
        return $this->belongsTo(TruckFruit::class, 'truck_fruit_id');
    }

    public function getFruitNameAttribute()
    {
        return $this->truckFruit->fruit->name;
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer->name;
    }

    public function getBoxTypeNameAttribute()
    {
        $box_types = [
            'big_box' => 'برنيكة كبيرة',
            'normal_box' => 'برنيكة صغيرة',
            'small_box' => 'برنيكة 10 كيلو',
            'small_net' => 'برنيكة شبك',
        ];
        return $box_types[$this->truckFruit->box_type]
            ?? $this->truckFruit->box_type;
    }

    public function getComputedUnitPriceAttribute()
    {
        return $this->unique_unit_price ?? $this->truckFruit->unified_unit_price;
    }

    public function getComputedBoxPriceAttribute()
    {
        return $this->unique_box_price ?? $this->truckFruit->unified_box_price;
    }

    public function getComputedTotalAttribute()
    {
        $unitPrice = $this->computed_unit_price;
        $boxPrice  = $this->computed_box_price;

        if ($this->total_weight && $unitPrice) {
            return intval($unitPrice * $this->total_weight);
        }

        return intval($boxPrice * $this->number_of_boxes);
    }

    public function getComputedClassNameAttribute()
    {
        return match ($this->box_class) {
            'first'  => 'فاخر',
            'second' => 'نمرة 2',
            'third'  => 'نمرة 3',
            default  => '—',
        };
    }
}
