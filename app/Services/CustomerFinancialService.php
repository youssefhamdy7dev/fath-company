<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerFinancialService
{
    /**
     * Get purchase, payment, and discount totals for customers.
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

        $purchaseQuery = DB::table('customer_purchase as cp')
            ->leftJoin(
                'truck_fruit as tf',
                'tf.id',
                '=',
                'cp.truck_fruit_id'
            )
            ->whereIn('cp.customer_id', $customerIds);

        if ($beforeDate !== null) {
            $purchaseQuery->where('cp.date', '<', $beforeDate);
        }

        $purchaseTotals = $purchaseQuery
            ->select('cp.customer_id')
            ->selectRaw(
                'COALESCE(SUM(' . $this->purchaseTotalExpression() . '), 0)
                as purchase_total'
            )
            ->groupBy('cp.customer_id')
            ->get()
            ->keyBy('customer_id');

        $paymentQuery = DB::table('customer_payments')
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
     * Get daily transaction totals for one customer.
     *
     * Results are ordered by date and can be used to calculate
     * historical balances cumulatively.
     */
    public function dailyTotalsForCustomer(
        int $customerId,
        ?string $beforeDate = null
    ): Collection {
        $purchaseQuery = DB::table('customer_purchase as cp')
            ->leftJoin(
                'truck_fruit as tf',
                'tf.id',
                '=',
                'cp.truck_fruit_id'
            )
            ->where('cp.customer_id', $customerId);

        if ($beforeDate !== null) {
            $purchaseQuery->where('cp.date', '<', $beforeDate);
        }

        $purchaseTotals = $purchaseQuery
            ->selectRaw('DATE(cp.date) as transaction_date')
            ->selectRaw(
                'COALESCE(SUM(' . $this->purchaseTotalExpression() . '), 0)
                as purchase_total'
            )
            ->groupByRaw('DATE(cp.date)')
            ->get()
            ->keyBy('transaction_date');

        $paymentQuery = DB::table('customer_payments')
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
     * Get previous and selected-day payment totals in one query.
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

        return DB::table('customer_payments')
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
     * Calculate a customer's balance using the shared formula.
     *
     * Integer conversion happens after the calculation.
     */
    public function calculateBalance(
        $account,
        $purchaseTotal,
        $paymentTotal,
        $discountTotal
    ): int {
        return (int) (
            $account
            + $purchaseTotal
            - $paymentTotal
            - $discountTotal
        );
    }

    /**
     * Return the SQL expression matching CustomerPurchase::computed_total.
     *
     * Each purchase is truncated before aggregation.
     */
    private function purchaseTotalExpression(): string
    {
        $effectiveWeight = '
            COALESCE(
                cp.total_weight,
                tf.unified_weight * cp.number_of_boxes
            )
        ';

        $effectiveUnitPrice = '
            COALESCE(
                cp.unique_unit_price,
                tf.unified_unit_price
            )
        ';

        $effectiveBoxPrice = '
            COALESCE(
                cp.unique_box_price,
                tf.unified_box_price
            )
        ';

        return "
            CASE
                WHEN
                    COALESCE($effectiveWeight, 0) <> 0
                    AND COALESCE($effectiveUnitPrice, 0) <> 0
                THEN
                    COALESCE(
                        CAST(
                            TRUNCATE(
                                ($effectiveUnitPrice * $effectiveWeight),
                                0
                            ) AS SIGNED
                        ),
                        0
                    )
                ELSE
                    COALESCE(
                        CAST(
                            TRUNCATE(
                                ($effectiveBoxPrice * cp.number_of_boxes),
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
