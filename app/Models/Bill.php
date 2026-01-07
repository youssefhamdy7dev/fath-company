<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $guarded = [];

    protected $casts = [
        'billing_date' => 'date',
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function getBillPriceAttribute()
    {
        $grandTotal = $this->grand_total;
        $percentageValue = intval(intval(($this->percentage / 100) * $grandTotal));
        $expensesValue = intval($this->expenses ?? 0);
        $freight = intval($this->truck->freight ?? 0);

        return $grandTotal - ($freight + $percentageValue + $expensesValue);
    }
}
