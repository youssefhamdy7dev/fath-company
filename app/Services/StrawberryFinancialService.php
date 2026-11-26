<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StrawberryFinancialService
{
    /**
     * Get Strawberry purchase, payment, and discount totals for customers.
     *
     * When $beforeDate is supplied, only transactions before that date
     * are included.
     */
    public function totalsByCustomer(
        $customerIds,
        ?string $beforeDate = null
    ): Collection {
        $customerIds = $this->normalizeCustomerIds($customerIds);

        if (empty($customerIds)) {
            return collect();
        }

        $purchaseQuery = DB::table('strawberry_customer_purchases as scp')
            ->leftJoin(
                'strawberry_truck_fruits as stf',
                'stf.id',
                '=',
                'scp.strawberry_truck_fruit_id'
            )
            ->whereIn('scp.customer_id', $customerIds);

        if ($beforeDate !== null) {
            $purchaseQuery->where('scp.date', '<', $beforeDate);
        }

        $purchaseTotals = $purchaseQuery
            ->select('scp.customer_id')
            ->selectRaw(
                'COALESCE(SUM(' . $this->purchaseTotalExpression() . '), 0)
                as purchase_total'
            )
            ->groupBy('scp.customer_id')
            ->get()
            ->keyBy('customer_id');

        $paymentQuery = DB::table('strawberry_customer_payments')
            ->whereIn('customer_id', $customerIds);

        if ($beforeDate !== null) {
            $paymentQuery->where('date', '<', $beforeDate);
        }

        $paymentTotals = $paymentQuery
            ->select('customer_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as payment_total')
            ->selectRaw('COALESCE(SUM(discount), 0) as discount_total')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        return collect($customerIds)->mapWithKeys(function ($customerId) use (
            $purchaseTotals,
            $paymentTotals
        ) {
            $purchases = $purchaseTotals->get($customerId);
            $payments = $paymentTotals->get($customerId);

            return [
                $customerId => (object) [
                    'purchase_total' => $purchases->purchase_total ?? 0,
                    'payment_total' => $payments->payment_total ?? 0,
                    'discount_total' => $payments->discount_total ?? 0,
                ],
            ];
        });
    }

    /**
     * Get daily Strawberry transaction totals for one customer.
     *
     * Results are ordered by date and can be used to calculate
     * historical balances cumulatively.
     */
    public function dailyTotalsForCustomer(
        int $customerId,
        ?string $beforeDate = null
    ): Collection {
        $purchaseQuery = DB::table('strawberry_customer_purchases as scp')
            ->leftJoin(
                'strawberry_truck_fruits as stf',
                'stf.id',
                '=',
                'scp.strawberry_truck_fruit_id'
            )
            ->where('scp.customer_id', $customerId);

        if ($beforeDate !== null) {
            $purchaseQuery->where('scp.date', '<', $beforeDate);
        }

        $purchaseTotals = $purchaseQuery
            ->selectRaw('DATE(scp.date) as transaction_date')
            ->selectRaw(
                'COALESCE(SUM(' . $this->purchaseTotalExpression() . '), 0)
                as purchase_total'
            )
            ->groupByRaw('DATE(scp.date)')
            ->get()
            ->keyBy('transaction_date');

        $paymentQuery = DB::table('strawberry_customer_payments')
            ->where('customer_id', $customerId);

        if ($beforeDate !== null) {
            $paymentQuery->where('date', '<', $beforeDate);
        }

        $paymentTotals = $paymentQuery
            ->selectRaw('DATE(date) as transaction_date')
            ->selectRaw('COALESCE(SUM(amount), 0) as payment_total')
            ->selectRaw('COALESCE(SUM(discount), 0) as discount_total')
            ->groupByRaw('DATE(date)')
            ->get()
            ->keyBy('transaction_date');

        $dates = $purchaseTotals->keys()
            ->merge($paymentTotals->keys())
            ->unique()
            ->sort()
            ->values();

        return $dates->mapWithKeys(function ($date) use (
            $purchaseTotals,
            $paymentTotals
        ) {
            $purchases = $purchaseTotals->get($date);
            $payments = $paymentTotals->get($date);

            return [
                $date => (object) [
                    'purchase_total' => $purchases->purchase_total ?? 0,
                    'payment_total' => $payments->payment_total ?? 0,
                    'discount_total' => $payments->discount_total ?? 0,
                ],
            ];
        });
    }

    /**
     * Get previous and selected-day Strawberry payment totals in one query.
     */
    public function paymentTotalsBeforeAndDuringDate(
        $customerIds,
        string $startDate,
        string $endDate
    ): Collection {
        $customerIds = $this->normalizeCustomerIds($customerIds);

        if (empty($customerIds)) {
            return collect();
        }

        return DB::table('strawberry_customer_payments')
            ->whereIn('customer_id', $customerIds)
            ->where('date', '<', $endDate)
            ->select('customer_id')
            ->selectRaw(
                'COALESCE(SUM(
                    CASE WHEN date < ? THEN amount ELSE 0 END
                ), 0) as previous_amount',
                [$startDate]
            )
            ->selectRaw(
                'COALESCE(SUM(
                    CASE WHEN date < ? THEN discount ELSE 0 END
                ), 0) as previous_discount',
                [$startDate]
            )
            ->selectRaw(
                'COALESCE(SUM(
                    CASE WHEN date >= ? AND date < ?
                    THEN amount ELSE 0 END
                ), 0) as amount',
                [$startDate, $endDate]
            )
            ->selectRaw(
                'COALESCE(SUM(
                    CASE WHEN date >= ? AND date < ?
                    THEN discount ELSE 0 END
                ), 0) as discount',
                [$startDate, $endDate]
            )
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');
    }

    /**
     * Get a customer's current Strawberry balance.
     *
     * Strawberry balance starts at zero and does not include
     * the customer's normal account balance.
     */
    public function getCurrentBalance($customer): int
    {
        $totals = $this->totalsByCustomer([$customer->id])
            ->get($customer->id);

        return $this->calculateBalance(
            $totals->purchase_total ?? 0,
            $totals->payment_total ?? 0,
            $totals->discount_total ?? 0
        );
    }

    /**
     * Get a customer's Strawberry balance before a given date.
     */
    public function getBalanceBefore($customer, $date): int
    {
        $cutoff = Carbon::parse($date)->format('Y-m-d');

        $totals = $this->totalsByCustomer(
            [$customer->id],
            $cutoff
        )->get($customer->id);

        return $this->calculateBalance(
            $totals->purchase_total ?? 0,
            $totals->payment_total ?? 0,
            $totals->discount_total ?? 0
        );
    }

    /**
     * Calculate a customer's Strawberry balance.
     *
     * Balance = Purchases - Payments - Discounts.
     */
    public function calculateBalance(
        $purchaseTotal,
        $paymentTotal,
        $discountTotal
    ): int {
        return (int) (
            $purchaseTotal
            - $paymentTotal
            - $discountTotal
        );
    }

    /**
     * Return the SQL expression matching StrawberryCustomerPurchase::computed_total.
     *
     * Each purchase is truncated individually before aggregation.
     */
    private function purchaseTotalExpression(): string
    {
        $effectiveUnitPrice = '
            COALESCE(
                scp.unique_unit_price,
                stf.unified_unit_price
            )
        ';

        $effectiveBoxPrice = '
            COALESCE(
                scp.unique_box_price,
                stf.unified_box_price
            )
        ';

        return "
            CASE
                -- A custom box price always takes priority.
                WHEN scp.unique_box_price IS NOT NULL
                THEN
                    CAST(
                        TRUNCATE(
                            scp.unique_box_price * scp.number_of_boxes,
                            0
                        ) AS SIGNED
                    )

                -- Special calculation for box-priced truck varieties:
                -- actual weight multiplied by the custom unit price.
                WHEN
                    stf.unified_box_price IS NOT NULL
                    AND scp.total_weight IS NOT NULL
                    AND scp.unique_unit_price IS NOT NULL
                THEN
                    CAST(
                        TRUNCATE(
                            scp.total_weight * scp.unique_unit_price,
                            0
                        ) AS SIGNED
                    )

                -- Normal unit-priced truck variety.
                WHEN stf.unified_unit_price IS NOT NULL
                THEN
                    COALESCE(
                        CAST(
                            TRUNCATE(
                                ($effectiveUnitPrice * scp.total_weight),
                                0
                            ) AS SIGNED
                        ),
                        0
                    )

                -- Normal or custom box-priced purchase.
                ELSE
                    COALESCE(
                        CAST(
                            TRUNCATE(
                                ($effectiveBoxPrice * scp.number_of_boxes),
                                0
                            ) AS SIGNED
                        ),
                        0
                    )
            END
        ";
    }

    /**
     * Normalize customer IDs before running aggregate queries.
     */
    private function normalizeCustomerIds($customerIds): array
    {
        return collect($customerIds)
            ->filter(fn($id) => $id !== null)
            ->unique()
            ->values()
            ->all();
    }
}
