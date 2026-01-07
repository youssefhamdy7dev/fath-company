<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $guarded = [];

    protected $appends = [
        'current_balance',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CustomerPurchase::class);
    }

    public function getCurrentBalanceAttribute(): int
    {
        // N+1 queries avoidance
        $this->load(['payments', 'purchases', 'purchases.truckFruit']);

        // total purchases
        $totalPurchases = $this->purchases->sum(function ($purchase) {
            return $purchase->computed_total;
        });

        // total payments & discounts
        $totalPayments  = $this->payments->sum('amount');
        $totalDiscounts = $this->payments->sum('discount');

        // final balace
        $balance = ($this->account + $totalPurchases) - ($totalPayments + $totalDiscounts);

        // your final equation
        return $balance;
    }

    public function getBalanceBefore($date): int
    {
        // N+1 queries avoidance
        $this->load(['payments', 'purchases', 'purchases.truckFruit']);

        $cutoff = Carbon::parse($date)->format('Y-m-d');

        // Load only the needed purchases & payments BEFORE the given date
        $purchases = $this->purchases()
            ->whereDate('date', '<', $cutoff)
            ->with('truckFruit')
            ->get();

        $payments = $this->payments()
            ->whereDate('date', '<', $cutoff)
            ->get();

        // Sum purchases (using the computed_total accessor)
        $totalPurchases = $purchases->sum(function ($purchase) {
            return $purchase->computed_total;
        });

        // Sum payments
        $totalPayments  = $payments->sum('amount');
        $totalDiscounts = $payments->sum('discount');

        // Same formula as current_balance
        $balance = ($this->account + $totalPurchases) - ($totalPayments + $totalDiscounts);

        return $balance;
    }
}
