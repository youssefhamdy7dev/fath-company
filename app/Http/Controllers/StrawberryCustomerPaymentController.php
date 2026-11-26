<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStrawberryPaymentRequest;
use App\Http\Requests\UpdateStrawberryPaymentRequest;
use App\Models\Customer;
use App\Models\StrawberryCustomerPayment;
use App\Services\StrawberryFinancialService;
use App\Support\CustomerLocations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StrawberryCustomerPaymentController extends Controller
{
    protected $locations;

    protected StrawberryFinancialService $financialService;

    public function __construct(StrawberryFinancialService $financialService)
    {
        $this->financialService = $financialService;
        $this->locations = CustomerLocations::all();
    }

    public function index()
    {
        $dates = StrawberryCustomerPayment::query()
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

        return view('pages.strawberry.payments.index', compact(
            'dates',
            'customers'
        ));
    }

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

        $places = CustomerLocations::places();

        $selectedPlaceKeys = $request->boolean('places_filter')
            ? array_values(array_intersect(
                (array) $request->input('places', []),
                array_keys($places)
            ))
            : array_keys($places);

        $locationToPlace = CustomerLocations::locationToPlace($selectedPlaceKeys);
        $selectedLocations = array_keys($locationToPlace);

        $query = StrawberryCustomerPayment::query()
            ->select('strawberry_customer_payments.*')
            ->leftJoin(
                'customers',
                'customers.id',
                '=',
                'strawberry_customer_payments.customer_id'
            )
            ->with('customer')
            ->where('strawberry_customer_payments.date', '>=', $carbonDate)
            ->where('strawberry_customer_payments.date', '<', $nextDate)
            ->where(function ($query) {
                $query->whereNull('strawberry_customer_payments.customer_id')
                    ->orWhereNotIn('customers.name', ['نقدية', 'المحل']);
            });

        if ($request->filled('name')) {
            $query->where(
                'customers.name',
                'like',
                '%' . $request->name . '%'
            );
        }

        if (empty($selectedLocations)) {
            $query->whereNull('strawberry_customer_payments.customer_id')
                ->whereRaw('1 = 0');
        } else {
            $query->where(function ($query) use ($selectedLocations) {
                $query->whereNull('strawberry_customer_payments.customer_id')
                    ->orWhereIn('customers.location', $selectedLocations);
            });
        }

        $payments = $query
            ->orderBy('customers.location')
            ->orderBy('customers.name')
            ->get();

        $grouped = $payments->groupBy(function ($payment) use ($locationToPlace) {
            if (!$payment->customer_id) {
                return 'نقدية';
            }

            return $locationToPlace[$payment->customer->location] ?? 'أخرى';
        });

        $customerIds = $payments
            ->pluck('customer_id')
            ->filter()
            ->unique()
            ->values();

        $financialTotals = $this->financialService->totalsByCustomer(
            $customerIds,
            $carbonDate
        );

        $balancesByCustomer = [];

        foreach ($payments->whereNotNull('customer_id')->unique('customer_id') as $payment) {
            $customerId = $payment->customer_id;
            $totals = $financialTotals->get($customerId);

            $balancesByCustomer[$customerId] = $this->financialService->calculateBalance(
                $totals->purchase_total ?? 0,
                $totals->payment_total ?? 0,
                $totals->discount_total ?? 0
            );
        }

        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        $displayDate = Carbon::parse($carbonDate);

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
                'pages.strawberry.payments.partials.payments-by-date-table',
                $data
            )->render();
        }

        return view('pages.strawberry.payments.payments-by-date', array_merge($data, [
            'locations' => $this->locations,
            'carbonDate' => $carbonDate,
        ]));
    }

    public function payment(StrawberryCustomerPayment $payment)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $payment->id,
                'customer_id' => $payment->customer_id,
                'amount' => $payment->amount,
                'discount' => $payment->discount,
                'date' => $payment->date->format('Y-m-d'),
                'notes' => $payment->notes,
            ],
        ]);
    }

    public function store(StoreStrawberryPaymentRequest $request)
    {
        StrawberryCustomerPayment::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة التحصيل بنجاح.',
            'redirect_url' => null,
        ]);
    }

    public function update(
        UpdateStrawberryPaymentRequest $request,
        StrawberryCustomerPayment $payment
    ) {
        $payment->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل التحصيل بنجاح.',
        ]);
    }

    public function destroy(StrawberryCustomerPayment $payment)
    {
        $payment->delete();

        return redirect()->back();
    }

    public function dailyCreate()
    {
        $allCustomers = Customer::query()
            ->whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        // Get all Strawberry financial totals in one operation.
        $customerIds = $allCustomers->pluck('id');

        $financialTotals = $this->financialService->totalsByCustomer(
            $customerIds
        );

        // Keep only customers who purchased Strawberry and still have a balance.
        $customers = $allCustomers
            ->filter(function ($customer) use ($financialTotals) {
                $totals = $financialTotals->get($customer->id);

                $purchaseTotal = (int) ($totals->purchase_total ?? 0);

                $balance = $this->financialService->calculateBalance(
                    $purchaseTotal,
                    $totals->payment_total ?? 0,
                    $totals->discount_total ?? 0
                );

                return $purchaseTotal > 0 && $balance != 0;
            })
            ->groupBy('location');

        $today = Carbon::now()->format('Y-m-d');
        $locations = $this->locations;
        $places = CustomerLocations::places();

        return view('pages.strawberry.payments.daily', compact(
            'customers',
            'today',
            'locations',
            'places'
        ));
    }

    public function storeDaily(Request $request)
    {
        $validated = $request->validate(
            [
                'date' => 'required|date',
            ],
            [
                'date.required' => 'يرجى إدخال التاريخ.',
                'date.date' => 'التاريخ غير صحيح',
            ]
        );

        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $payload = $request->input('payments', []);

        $toInsert = collect($payload)
            ->map(function ($row) use ($date) {
                return [
                    'customer_id' => (int) $row['customer_id'],
                    'amount' => (int) $row['amount'],
                    'discount' => ($row['discount'] ?? '') !== ''
                        ? (float) $row['discount']
                        : null,
                    'date' => $date,
                ];
            })
            ->filter(function ($row) {
                return $row['amount'] !== null && $row['amount'] != 0;
            })
            ->values()
            ->toArray();

        if (empty($toInsert)) {
            return back()->withErrors([
                'payments' => 'لم يتم إدخال أى مبالغ لحفظها.',
            ]);
        }

        $customerIds = array_column($toInsert, 'customer_id');

        $duplicates = StrawberryCustomerPayment::whereIn('customer_id', $customerIds)
            ->whereDate('date', $date)
            ->pluck('customer_id')
            ->toArray();

        if (!empty($duplicates)) {
            $names = Customer::whereIn('id', $duplicates)
                ->pluck('name')
                ->toArray();

            $message = 'لا يمكن إتمام التسجيل لأن بعض العملاء لديهم تحصيل مسجل في نفس التاريخ: '
                . implode(' - ', $names);

            return back()->withErrors([
                'duplicates' => $message,
            ])->withInput();
        }

        DB::transaction(function () use ($toInsert) {
            foreach ($toInsert as $row) {
                StrawberryCustomerPayment::create($row);
            }
        });

        return redirect()
            ->route('strawberry-customer-payments.daily.create')
            ->with('success', 'تم تسجيل التحصيل اليومي بنجاح.');
    }

    public function dailyBalance(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($validated['date']);
        $startDate = $date->copy()->startOfDay();

        $customers = Customer::query()
            ->whereNotIn('name', ['نقدية', 'المحل'])
            ->get(['id']);

        $customerIds = $customers->pluck('id');

        $financialTotals = $this->financialService->totalsByCustomer(
            $customerIds,
            $startDate->toDateString()
        );

        $balances = [];

        foreach ($customers as $customer) {
            $totals = $financialTotals->get($customer->id);

            $balance = $this->financialService->calculateBalance(
                $totals->purchase_total ?? 0,
                $totals->payment_total ?? 0,
                $totals->discount_total ?? 0
            );

            $balances[$customer->id] = transform_numeric_value($balance);
        }

        return response()->json([
            'success' => true,
            'balances' => $balances,
            'formatted' => transform_numbers($date->format('d-m-Y')),
        ]);
    }

    /**
     * Read-only Strawberry collections overview.
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

        // Missing/null places = all groups.
        // Empty array = no groups.
        $selectedPlaceKeys = $validated['places'] ?? array_keys($places);

        $startDate = Carbon::parse($date)->startOfDay();
        $nextDay = $startDate->copy()->addDay();

        /*
    |--------------------------------------------------------------------------
    | 1. Fetch customers in selected locations
    |--------------------------------------------------------------------------
    */

        $locationToPlace = CustomerLocations::locationToPlace($selectedPlaceKeys);
        $selectedLocations = array_keys($locationToPlace);

        $customers = collect();

        if (!empty($selectedLocations)) {
            $customers = Customer::query()
                ->whereNotIn('name', ['نقدية', 'المحل'])
                ->whereIn('location', $selectedLocations)
                ->whereHas('strawberryPurchases')
                ->orderBy('location')
                ->orderBy('name')
                ->get();
        }

        /*
    |--------------------------------------------------------------------------
    | 2. Group customers by place
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
    | 3. Aggregate Strawberry purchases before selected date
    |--------------------------------------------------------------------------
    */

        $financialTotals = $this->financialService->totalsByCustomer(
            $allCustomerIds,
            $startDate->toDateString()
        );

        /*
    |--------------------------------------------------------------------------
    | 4. Aggregate previous and selected-date Strawberry payments
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
    | 5. Calculate Strawberry balances
    |--------------------------------------------------------------------------
    */

        $balancesByCustomer = collect();

        foreach ($customers as $customer) {
            $totals = $financialTotals->get($customer->id);

            $purchaseTotal = (int) ($totals->purchase_total ?? 0);

            $payment = $paymentAggregates->get($customer->id);

            $previousPayments = (int) ($payment->previous_amount ?? 0);
            $previousDiscounts = (int) ($payment->previous_discount ?? 0);

            // Strawberry balance starts at zero; do not include customer.account.
            $balanceBefore = $purchaseTotal
                - $previousPayments
                - $previousDiscounts;

            $balancesByCustomer->put(
                $customer->id,
                $balanceBefore
            );
        }

        // Keep the same variable name used by the normal dashboard tables.
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
            return view('pages.strawberry.payments.partials.tables', $data);
        }

        return view('pages.strawberry.payments.payments-dashboard', $data);
    }
}
