<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\CustomerPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class CustomerPaymentController extends Controller
{
    protected $locations = [
        'ميدان صقر',
        'الكويتية',
        'صقر',
        'العمومى بساتين',
        'البساتين',
        'سوق البساتين',
        'السد العالى',
        'ميدان أبو عبده',
        'على عبدالعزيز',
        'أبو بريك',
        'المطبعة',
        'الجزيرة',
        'دار السلام',
        'البير',
        'المشير وأبو الوفا',
        'عبدالحميد مكى',
        'فايدة كامل',
        'حسنين الدسوقى',
        'المعادى',
        'أخرى'
    ];

    /**
     * Index: list distinct payment dates (newest first).
     */
    public function index(Request $request)
    {
        // Get distinct dates as Carbon instances ordered desc
        $dates = CustomerPayment::query()
            ->select('date')
            ->groupBy('date')
            ->orderByDesc('date')
            ->paginate(10);

        $dates->getCollection()->transform(function ($item) {
            return $item->date;
        });

        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')->orderBy('name')->get();

        return view('pages.payments.index', compact('dates', 'customers'));
    }


    /**
     * Display payments for a specific date (page).
     * Accepts optional AJAX filter params: name, location
     */
    public function byDate(Request $request, $date)
    {
        try {
            $carbonDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            abort(404);
        }

        $nextDate = Carbon::parse($carbonDate)
            ->addDay()
            ->format('Y-m-d');

        /*
    |--------------------------------------------------------------------------
    | 1. Define place groups
    |--------------------------------------------------------------------------
    */

        $places = [
            'صقر والبساتين' => [
                'ميدان صقر',
                'الكويتية',
                'صقر',
                'العمومى بساتين',
                'البساتين',
                'سوق البساتين',
            ],
            'السد العالى' => [
                'السد العالى',
                'ميدان أبو عبده',
                'على عبدالعزيز',
                'أبو بريك',
            ],
            'الجزيرة ودار السلام' => [
                'المطبعة',
                'الجزيرة',
                'دار السلام',
            ],
            'فايدة كامل وحسنين الدسوقى' => [
                'البير',
                'المشير وأبو الوفا',
                'عبدالحميد مكى',
                'فايدة كامل',
                'حسنين الدسوقى',
                'المعادى',
            ],
            'أخرى' => [
                'أخرى',
            ],
        ];

        /*
    |--------------------------------------------------------------------------
    | 2. Get selected groups and their locations
    |--------------------------------------------------------------------------
    */

        // Without places_filter, show all groups by default.
        // With places_filter, use only the submitted groups.
        $selectedPlaceKeys = $request->boolean('places_filter')
            ? array_values(array_intersect(
                (array) $request->input('places', []),
                array_keys($places)
            ))
            : array_keys($places);

        $locationToPlace = [];

        foreach ($selectedPlaceKeys as $placeKey) {
            foreach ($places[$placeKey] as $location) {
                $locationToPlace[$location] = $placeKey;
            }
        }

        $selectedLocations = array_keys($locationToPlace);

        /*
    |--------------------------------------------------------------------------
    | 3. Fetch payments for selected date
    |--------------------------------------------------------------------------
    */

        $query = CustomerPayment::query()
            ->select('customer_payments.*')
            ->join('customers', 'customers.id', '=', 'customer_payments.customer_id')
            ->with('customer')
            ->where('customer_payments.date', '>=', $carbonDate)
            ->where('customer_payments.date', '<', $nextDate)
            ->whereNotIn('customers.name', ['نقدية', 'المحل']);

        // Filter by customer name.
        if ($request->filled('name')) {
            $query->where(
                'customers.name',
                'like',
                '%' . $request->name . '%'
            );
        }

        // Filter by selected place groups.
        if (empty($selectedLocations)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('customers.location', $selectedLocations);
        }

        $payments = $query
            ->orderBy('customers.location')
            ->orderBy('customers.name')
            ->get();

        // Group payments by the selected place-group names.
        $grouped = $payments->groupBy(
            fn($payment) => $locationToPlace[$payment->customer->location] ?? 'أخرى'
        );

        /*
    |--------------------------------------------------------------------------
    | 4. Get unique customer IDs
    |--------------------------------------------------------------------------
    */

        $customerIds = $payments
            ->pluck('customer_id')
            ->unique()
            ->values();

        $purchaseTotals = collect();
        $paymentTotals = collect();

        if ($customerIds->isNotEmpty()) {

            /*
        |--------------------------------------------------------------------------
        | 5. Aggregate purchases BEFORE selected date
        |--------------------------------------------------------------------------
        */

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

            $purchaseTotalExpression = "
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

            $purchaseTotals = DB::table('customer_purchase as cp')
                ->leftJoin('truck_fruit as tf', 'tf.id', '=', 'cp.truck_fruit_id')
                ->whereIn('cp.customer_id', $customerIds)
                ->where('cp.date', '<', $carbonDate)
                ->select('cp.customer_id')
                ->selectRaw(
                    "COALESCE(SUM($purchaseTotalExpression), 0) as total"
                )
                ->groupBy('cp.customer_id')
                ->get()
                ->keyBy('customer_id');

            /*
        |--------------------------------------------------------------------------
        | 6. Aggregate payments and discounts BEFORE selected date
        |--------------------------------------------------------------------------
        */

            $paymentTotals = DB::table('customer_payments')
                ->whereIn('customer_id', $customerIds)
                ->where('date', '<', $carbonDate)
                ->select('customer_id')
                ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
                ->selectRaw('COALESCE(SUM(discount), 0) as total_discount')
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id');
        }

        /*
    |--------------------------------------------------------------------------
    | 7. Calculate balances in memory
    |--------------------------------------------------------------------------
    */

        $balancesByCustomer = [];

        foreach ($payments->unique('customer_id') as $payment) {
            $customer = $payment->customer;
            $customerId = $customer->id;

            $purchases = (int) (
                $purchaseTotals->get($customerId)->total ?? 0
            );

            $previousPayments = $paymentTotals->get($customerId);

            $totalPayments = (float) (
                $previousPayments->total_amount ?? 0
            );

            $totalDiscounts = (float) (
                $previousPayments->total_discount ?? 0
            );

            $balancesByCustomer[$customerId] = (int) (
                $customer->account
                + $purchases
                - $totalPayments
                - $totalDiscounts
            );
        }

        /*
    |--------------------------------------------------------------------------
    | 8. Load customers for modals / selects
    |--------------------------------------------------------------------------
    */

        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        $displayDate = Carbon::parse($carbonDate);

        /*
    |--------------------------------------------------------------------------
    | 9. Return AJAX or full page
    |--------------------------------------------------------------------------
    */

        $data = [
            'grouped' => $grouped,
            'customers' => $customers,
            'displayDate' => $displayDate,
            'balancesByCustomer' => $balancesByCustomer,
            'places' => $places,
            'selectedPlaceKeys' => $selectedPlaceKeys,
        ];

        if ($request->ajax()) {
            return view(
                'pages.payments.partials.payments-by-date-table',
                $data
            )->render();
        }

        return view('pages.payments.payments-by-date', array_merge($data, [
            'locations' => $this->locations,
            'carbonDate' => $carbonDate,
        ]));
    }

    /**
     * Return JSON data for a single payment (used by edit modal).
     */
    public function payment(CustomerPayment $payment)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $payment->id,
                'customer_id' => $payment->customer_id,
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'date' => $payment->date->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Store single payment (AJAX) - used by Add Payment modal.
     * On success -> return JSON {status: 'success'} and client reloads index.
     */
    public function store(StorePaymentRequest $request)
    {
        CustomerPayment::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة التحصيل بنجاح.',
            'redirect_url' => NULL,
        ]);
    }

    /**
     * Update single payment (AJAX).
     */
    public function update(UpdatePaymentRequest $request, CustomerPayment $payment)
    {
        $payment->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل التحصيل بنجاح.',
        ]);
    }

    /**
     * Delete payment (non-AJAX).
     */
    public function destroy(CustomerPayment $payment)
    {
        $payment->delete();
        return redirect()->back();
    }

    /**
     * Daily create page (bulk daily add): list all customers grouped by location
     * with inputs for amount / discount / date (date default = today).
     */
    public function dailyCreate()
    {
        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')
            ->orderBy('name')
            ->get()
            ->groupBy('location');

        $today = Carbon::now()->format('Y-m-d');
        $locations = $this->locations;
        return view('pages.payments.daily', compact('customers', 'today', 'locations'));
    }

    /**
     * Store bulk daily payments.
     * New behavior:
     *    - Date comes once from $request->date (no per-row dates).
     *    - If ANY payment exists for ANY included customer on that date → block.
     */
    public function storeDaily(Request $request)
    {
        // Validate date only
        $validated = $request->validate(
            ['date' => 'required|date'],
            ['date.required' => 'يرجى إدخال التاريخ.', 'date.date' => 'التاريخ غير صحيح']
        );

        $date = Carbon::parse($validated['date'])->format('Y-m-d');

        // Incoming rows
        $payload = $request->input('payments', []);
        // Filter + normalize data
        $toInsert = collect($payload)->map(function ($row) use ($date) {
            return [
                'customer_id' => (int) $row['customer_id'],
                'amount'      => (int) $row['amount'],
                'discount'    => ($row['discount'] ?? '') !== '' ? (float) $row['discount'] : null,
                'date'        => $date,
            ];
        })->filter(function ($row) {
            // Keep only rows with amount != NULL and amount != 0
            return $row['amount'] != NULL && $row['amount'] != 0;
        })
            ->values()
            ->toArray();

        // No entries?
        if (empty($toInsert)) {
            return back()->withErrors(['payments' => 'لم يتم إدخال أى مبالغ لحفظها.']);
        }

        /**
         * - Extract ALL customer_ids in one array
         * - One SQL query to check duplicates
         */
        $customerIds = array_column($toInsert, 'customer_id');

        $duplicates = CustomerPayment::whereIn('customer_id', $customerIds)
            ->whereDate('date', $date)
            ->pluck('customer_id')
            ->toArray();

        if (!empty($duplicates)) {
            $names = Customer::whereIn('id', $duplicates)->pluck('name')->toArray();
            $msg = 'لا يمكن إتمام التسجيل لأن بعض العملاء لديهم تحصيل مسجل في نفس التاريخ: ' . implode(' - ', $names);
            return back()->withErrors(['duplicates' => $msg])->withInput();
        }

        // Insert all rows in a transaction
        DB::transaction(function () use ($toInsert) {
            foreach ($toInsert as $row) {
                CustomerPayment::create($row);
            }
        });

        return redirect()
            ->route('customer-payments.daily.create')
            ->with('success', 'تم تسجيل التحصيل اليومي بنجاح.');
    }

    // AJAX for updating balance of customer based on date

    public function dailyBalance(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($validated['date']);
        $startDate = $date->copy()->startOfDay();

        // Load only customers displayed on the daily payments page.
        $customers = Customer::query()
            ->whereNotIn('name', ['نقدية', 'المحل'])
            ->get(['id', 'account']);

        $customerIds = $customers->pluck('id');

        // Calculate purchase totals using the same logic as computed_total.
        $effectiveWeight = '
        COALESCE(
            cp.total_weight,
            tf.unified_weight * cp.number_of_boxes
        )
    ';

        $effectiveUnitPrice = '
        COALESCE(cp.unique_unit_price, tf.unified_unit_price)
    ';

        $effectiveBoxPrice = '
        COALESCE(cp.unique_box_price, tf.unified_box_price)
    ';

        $purchaseTotalExpression = "
        CASE
            WHEN COALESCE($effectiveWeight, 0) <> 0
                 AND COALESCE($effectiveUnitPrice, 0) <> 0
            THEN COALESCE(
                CAST(TRUNCATE(
                    ($effectiveUnitPrice * $effectiveWeight), 0
                ) AS SIGNED),
                0
            )
            ELSE COALESCE(
                CAST(TRUNCATE(
                    ($effectiveBoxPrice * cp.number_of_boxes), 0
                ) AS SIGNED),
                0
            )
        END
    ";

        $purchaseTotals = DB::table('customer_purchase as cp')
            ->leftJoin('truck_fruit as tf', 'tf.id', '=', 'cp.truck_fruit_id')
            ->whereIn('cp.customer_id', $customerIds)
            ->where('cp.date', '<', $startDate)
            ->select('cp.customer_id')
            ->selectRaw("SUM($purchaseTotalExpression) as purchase_total")
            ->groupBy('cp.customer_id')
            ->get()
            ->keyBy('customer_id');

        // Aggregate payments and discounts before the selected date.
        $paymentTotals = CustomerPayment::query()
            ->whereIn('customer_id', $customerIds)
            ->where('date', '<', $startDate)
            ->select('customer_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(discount), 0) as total_discount')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        // Calculate balances without loading individual relationships.
        $balances = [];

        foreach ($customers as $customer) {
            $purchaseTotal = $purchaseTotals->get($customer->id)->purchase_total ?? 0;

            $payment = $paymentTotals->get($customer->id);

            $totalPayments = $payment->total_amount ?? 0;
            $totalDiscounts = $payment->total_discount ?? 0;

            $balance = ($customer->account + $purchaseTotal)
                - ($totalPayments + $totalDiscounts);

            $balances[$customer->id] = transform_numeric_value($balance);
        }

        return response()->json([
            'success' => true,
            'balances' => $balances,
            'formatted' => transform_numbers($date->format('d-m-Y')),
        ]);
    }



    /**
     * Read-only collections overview.
     */

    public function paymentsDashboard(Request $request)
    {
        $places = [
            'صقر والبساتين' => [
                'ميدان صقر',
                'الكويتية',
                'صقر',
                'العمومى بساتين',
                'البساتين',
                'سوق البساتين',
            ],
            'السد العالى' => [
                'السد العالى',
                'ميدان أبو عبده',
                'على عبدالعزيز',
                'أبو بريك',
            ],
            'الجزيرة ودار السلام' => [
                'المطبعة',
                'الجزيرة',
                'دار السلام',
            ],
            'فايدة كامل وحسنين الدسوقى' => [
                'البير',
                'المشير وأبو الوفا',
                'عبدالحميد مكى',
                'فايدة كامل',
                'حسنين الدسوقى',
                'المعادى',
            ],
            'أخرى' => [
                'أخرى',
            ],
        ];

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'places' => ['nullable', 'array'],
            'places.*' => ['string', Rule::in(array_keys($places))],
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        $displayDate = Carbon::parse($date);

        // Preserve the original behavior:
        // Missing/null places = all groups.
        // Empty array = no groups.
        $selectedPlaceKeys = $validated['places'] ?? array_keys($places);

        $startDate = Carbon::parse($date)->startOfDay();
        $nextDay = $startDate->copy()->addDay();

        /*
    |--------------------------------------------------------------------------
    | 1. Fetch all selected customers in ONE query
    |--------------------------------------------------------------------------
    */

        $locationToPlace = [];

        foreach ($selectedPlaceKeys as $placeKey) {
            foreach ($places[$placeKey] as $location) {
                $locationToPlace[$location] = $placeKey;
            }
        }

        $selectedLocations = array_keys($locationToPlace);

        $customers = collect();

        if (!empty($selectedLocations)) {
            $customers = Customer::query()
                ->whereNotIn('name', ['نقدية', 'المحل'])
                ->whereIn('location', $selectedLocations)
                ->orderBy('location')
                ->orderBy('name')
                ->get();
        }

        /*
    |--------------------------------------------------------------------------
    | 2. Group customers without additional database queries
    |--------------------------------------------------------------------------
    */

        $groupedCustomers = $customers->groupBy(
            fn($customer) => $locationToPlace[$customer->location] ?? null
        );

        $customersByPlace = collect();

        foreach ($selectedPlaceKeys as $placeKey) {
            $customersByPlace->put(
                $placeKey,
                $groupedCustomers->get($placeKey, collect())->values()
            );
        }

        $allCustomerIds = $customers->pluck('id')->all();

        $purchaseTotals = collect();
        $paymentAggregates = collect();

        /*
    |--------------------------------------------------------------------------
    | 3. Aggregate previous purchases in ONE query
    |--------------------------------------------------------------------------
    |
    | Reproduces CustomerPurchase::computed_total:
    |
    | - unique_unit_price OR truck_fruit.unified_unit_price
    | - total_weight OR unified_weight * number_of_boxes
    | - If weight AND unit price are truthy:
    |       intval(unit_price * total_weight)
    | - Otherwise:
    |       intval(box_price * number_of_boxes)
    |
    */

        if (!empty($allCustomerIds)) {
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

            $purchaseTotalExpression = "
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

            $purchaseTotals = DB::table('customer_purchase as cp')
                ->leftJoin(
                    'truck_fruit as tf',
                    'tf.id',
                    '=',
                    'cp.truck_fruit_id'
                )
                ->whereIn('cp.customer_id', $allCustomerIds)
                ->where('cp.date', '<', $startDate)
                ->select('cp.customer_id')
                ->selectRaw(
                    "SUM($purchaseTotalExpression) as purchase_total"
                )
                ->groupBy('cp.customer_id')
                ->get()
                ->keyBy('customer_id');

            /*
        |--------------------------------------------------------------------------
        | 4. Aggregate previous and selected-date payments in ONE query
        |--------------------------------------------------------------------------
        */

            $paymentAggregates = CustomerPayment::query()
                ->whereIn('customer_id', $allCustomerIds)
                ->where('date', '<', $nextDay)
                ->select('customer_id')

                // Payments before the selected date
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

                // Payments on the selected date
                ->selectRaw(
                    'COALESCE(SUM(
                    CASE
                        WHEN date >= ? AND date < ?
                        THEN amount ELSE 0
                    END
                ), 0) as amount',
                    [$startDate, $nextDay]
                )
                ->selectRaw(
                    'COALESCE(SUM(
                    CASE
                        WHEN date >= ? AND date < ?
                        THEN discount ELSE 0
                    END
                ), 0) as discount',
                    [$startDate, $nextDay]
                )
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id');
        }

        /*
    |--------------------------------------------------------------------------
    | 5. Calculate balances using aggregated data
    |--------------------------------------------------------------------------
    */

        $balancesByCustomer = collect();

        foreach ($customers as $customer) {
            $purchaseTotal = (int) (
                $purchaseTotals->get($customer->id)->purchase_total ?? 0
            );

            $payment = $paymentAggregates->get($customer->id);

            $previousPayments = (int) ($payment->previous_amount ?? 0);
            $previousDiscounts = (int) ($payment->previous_discount ?? 0);

            $balanceBefore = (int) $customer->account
                + $purchaseTotal
                - $previousPayments
                - $previousDiscounts;

            $balancesByCustomer->put(
                $customer->id,
                $balanceBefore
            );
        }

        // Keep the existing Blade variable name.
        $paymentsByCustomer = $paymentAggregates;

        $data = compact(
            'places',
            'selectedPlaceKeys',
            'customersByPlace',
            'paymentsByCustomer',
            'balancesByCustomer',
            'displayDate',
            'date'
        );

        if ($request->ajax()) {
            return view('pages.payments.partials.tables', $data);
        }

        return view('pages.payments.payments-dashboard', $data);
    }
}
