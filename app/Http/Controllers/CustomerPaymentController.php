<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\CustomerPayment;
use App\Models\Customer;
use App\Services\CustomerFinancialService;
use App\Support\CustomerLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class CustomerPaymentController extends Controller
{
    protected $locations;

    protected CustomerFinancialService $financialService;

    public function __construct(CustomerFinancialService $financialService)
    {
        $this->financialService = $financialService;
        $this->locations = CustomerLocations::all();
    }

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
            ->orderBy('location')
            ->orderBy('name')
            ->get();

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

        $places = CustomerLocations::places();

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

        $locationToPlace = CustomerLocations::locationToPlace($selectedPlaceKeys);
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

        /*
        |--------------------------------------------------------------------------
        | 5. Aggregate previous financial totals
        |--------------------------------------------------------------------------
        */

        $financialTotals = $this->financialService->totalsByCustomer(
            $customerIds,
            $carbonDate
        );

        /*
        |--------------------------------------------------------------------------
        | 6. Calculate balances in memory
        |--------------------------------------------------------------------------
        */

        $balancesByCustomer = [];

        foreach ($payments->unique('customer_id') as $payment) {
            $customer = $payment->customer;
            $customerId = $customer->id;
            $totals = $financialTotals->get($customerId);

            $purchases = (int) ($totals->purchase_total ?? 0);
            $totalPayments = (float) ($totals->payment_total ?? 0);
            $totalDiscounts = (float) ($totals->discount_total ?? 0);

            // Keep the original final integer cast for this screen.
            $balancesByCustomer[$customerId] = (int) (
                $customer->account
                + $purchases
                - $totalPayments
                - $totalDiscounts
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Load customers for modals / selects
        |--------------------------------------------------------------------------
        */

        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        $displayDate = Carbon::parse($carbonDate);

        /*
        |--------------------------------------------------------------------------
        | 8. Return AJAX or full page
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
        $places = CustomerLocations::places();

        return view('pages.payments.daily', compact(
            'customers',
            'today',
            'locations',
            'places'
        ));
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

        // Aggregate financial totals before the selected date.
        $financialTotals = $this->financialService->totalsByCustomer(
            $customerIds,
            $startDate->toDateString()
        );

        // Calculate balances without loading individual relationships.
        $balances = [];

        foreach ($customers as $customer) {
            $totals = $financialTotals->get($customer->id);

            $purchaseTotal = $totals->purchase_total ?? 0;
            $totalPayments = $totals->payment_total ?? 0;
            $totalDiscounts = $totals->discount_total ?? 0;

            $balance = ($customer->account + $purchaseTotal)
                - ($totalPayments + $totalDiscounts);

            // Preserve the original number transformation for this response.
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
        $places = CustomerLocations::places();

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

        $locationToPlace = CustomerLocations::locationToPlace($selectedPlaceKeys);
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

        $allCustomerIds = $customers->pluck('id');

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

        $financialTotals = $this->financialService->totalsByCustomer(
            $allCustomerIds,
            $startDate->toDateString()
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Aggregate previous and selected-date payments in ONE query
        |--------------------------------------------------------------------------
        */

        $paymentAggregates = $this->financialService
            ->paymentTotalsBeforeAndDuringDate(
                $allCustomerIds,
                $startDate->toDateString(),
                $nextDay->toDateString()
            );

        /*
        |--------------------------------------------------------------------------
        | 5. Calculate balances using aggregated data
        |--------------------------------------------------------------------------
        */

        $balancesByCustomer = collect();

        foreach ($customers as $customer) {
            $totals = $financialTotals->get($customer->id);

            $purchaseTotal = (int) ($totals->purchase_total ?? 0);

            $payment = $paymentAggregates->get($customer->id);

            // Keep the original individual casts for previous payments and discounts.
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
