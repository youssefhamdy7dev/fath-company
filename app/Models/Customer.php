<?php

namespace App\Models;

use App\Services\CustomerFinancialService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /**
     * Attributes available for mass assignment.
     */
    protected $guarded = [];

    /**
     * Attributes appended to the model's array and JSON representations.
     */
    protected $appends = [
        'current_balance',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the customer's payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    /**
     * Get the customer's purchases.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(CustomerPurchase::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the customer's current balance.
     *
     * Balance = Account + Purchases - Payments - Discounts.
     */
    public function getCurrentBalanceAttribute(): int
    {
        $this->loadMissing([
            'payments',
            'purchases',
            'purchases.truckFruit',
        ]);

        $totalPurchases = $this->purchases->sum(
            fn($purchase) => $purchase->computed_total
        );

        $totalPayments = $this->payments->sum('amount');
        $totalDiscounts = $this->payments->sum('discount');

        return app(CustomerFinancialService::class)->calculateBalance(
            $this->account,
            $totalPurchases,
            $totalPayments,
            $totalDiscounts
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Balance Calculations
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the customer's balance before a given date.
     *
     * Only purchases and payments dated before the supplied date
     * are included.
     */
    public function getBalanceBefore($date): int
    {
        $cutoff = Carbon::parse($date)->format('Y-m-d');

        $purchases = $this->purchases()
            ->whereDate('date', '<', $cutoff)
            ->with('truckFruit')
            ->get();

        $payments = $this->payments()
            ->whereDate('date', '<', $cutoff)
            ->get();

        $totalPurchases = $purchases->sum(function ($purchase) {
            return $purchase->computed_total;
        });

        $totalPayments = $payments->sum('amount');
        $totalDiscounts = $payments->sum('discount');

        return app(CustomerFinancialService::class)->calculateBalance(
            $this->account,
            $totalPurchases,
            $totalPayments,
            $totalDiscounts
        );
    }
}
