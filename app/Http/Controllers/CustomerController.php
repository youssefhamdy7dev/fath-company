<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerFinancialService;
use App\Support\BusinessDate;
use App\Support\CustomerLocations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = CustomerLocations::all();
        $places = CustomerLocations::places();

        /*
        |--------------------------------------------------------------------------
        | 1. Fetch all customers
        |--------------------------------------------------------------------------
        */

        $customers = Customer::query()
            ->select([
                'id',
                'name',
                'phone',
                'location',
                'account',
            ])
            ->whereNotIn('name', ['نقدية', 'المحل'])
            ->orderByRaw(
                "FIELD(location, '" . implode("','", $locations) . "')"
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. Aggregate financial totals for all customers
        |--------------------------------------------------------------------------
        */

        $customerIds = $customers->pluck('id')->all();

        $financialTotals = app(CustomerFinancialService::class)
            ->totalsByCustomer($customerIds);

        /*
        |--------------------------------------------------------------------------
        | 3. Calculate balances in memory
        |--------------------------------------------------------------------------
        */

        $financialService = app(CustomerFinancialService::class);

        $balancesByCustomer = collect();

        foreach ($customers as $customer) {
            $totals = $financialTotals->get($customer->id);

            $balance = $financialService->calculateBalance(
                $customer->account,
                $totals->purchase_total ?? 0,
                $totals->payment_total ?? 0,
                $totals->discount_total ?? 0
            );

            $balancesByCustomer->put($customer->id, $balance);
        }

        return view('pages.customers.index', compact(
            'customers',
            'locations',
            'places',
            'balancesByCustomer',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = CustomerLocations::all();

        return view('pages.customers.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', 'تم إضافة الزبون بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('pages.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $locations = CustomerLocations::all();

        return view('pages.customers.edit', compact('customer', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', 'تم تحديث بيانات الزبون بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', "تم حذف الزبون: {$customer->name} بنجاح.");
    }

    /**
     * Display the purchases of a specific customer.
     */
    public function purchases(Customer $customer)
    {
        $dates = $this->paginateLedgerDates($customer);

        $groupedPurchases = $this->fetchGroupedPurchasesNew($customer, $dates);
        $groupedPayments = $this->fetchGroupedPayments($customer, $dates);

        $balances = $this->computeBalancesNew(
            $customer,
            $dates->items(),
            $groupedPurchases,
            $groupedPayments
        );

        return view('pages.customers.purchases', compact(
            'customer',
            'dates',
            'groupedPurchases',
            'groupedPayments',
            'balances'
        ));
    }

    /**
     * Paginate unique dates from the customer's purchase and payment ledger.
     */
    private function paginateLedgerDates(Customer $customer)
    {
        return DB::table(function ($query) use ($customer) {
            $query->selectRaw('DATE(date) as ledger_date')
                ->from('customer_purchase')
                ->where('customer_id', $customer->id)
                ->union(
                    DB::table('customer_payments')
                        ->selectRaw('DATE(date) as ledger_date')
                        ->where('customer_id', $customer->id)
                );
        }, 'ledger')
            ->groupBy('ledger_date')
            ->orderByDesc('ledger_date')
            ->paginate(5);
    }

    /**
     * Fetch purchases grouped by date for the selected paginated dates.
     */
    private function fetchGroupedPurchasesNew(Customer $customer, $dates)
    {
        $selectedDates = $dates->pluck('ledger_date');

        return $customer->purchases()
            ->whereIn(DB::raw('DATE(date)'), $selectedDates)
            ->with([
                'customer',
                'truckFruit',
                'truckFruit.fruit',
                'truckFruit.truck',
                'truckFruit.truck.truckFruits',
                'truckFruit.truck.truckFruits.fruit',
            ])
            ->orderByDesc('date')
            ->get()
            ->groupBy(fn($purchase) => $purchase->date->format('Y-m-d'));
    }

    /**
     * Fetch payments grouped by date for the selected paginated dates.
     */
    private function fetchGroupedPayments(Customer $customer, $dates)
    {
        $selectedDates = $dates->pluck('ledger_date');

        return $customer->payments()
            ->whereIn(DB::raw('DATE(date)'), $selectedDates)
            ->orderByDesc('date')
            ->get()
            ->groupBy(fn($payment) => $payment->date->format('Y-m-d'));
    }

    /**
     * Compute balances for the selected ledger dates.
     *
     * The balance before the earliest selected date is calculated once.
     * The selected dates are then processed chronologically in memory.
     */
    private function computeBalancesNew(
        Customer $customer,
        $ledgerDates,
        $groupedPurchases,
        $groupedPayments
    ) {
        $balances = [];

        if (empty($ledgerDates)) {
            return $balances;
        }

        $financialService = app(CustomerFinancialService::class);

        $sortedDates = collect($ledgerDates)
            ->sortBy('ledger_date')
            ->values();

        $earliestDate = Carbon::parse(
            $sortedDates->first()->ledger_date
        )->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | 1. Calculate the balance before the earliest selected date once
        |--------------------------------------------------------------------------
        */

        $previousTotals = $financialService->totalsByCustomer(
            [$customer->id],
            $earliestDate
        )->get($customer->id);

        $runningBalance = (float) $financialService->calculateBalance(
            $customer->account,
            $previousTotals->purchase_total ?? 0,
            $previousTotals->payment_total ?? 0,
            $previousTotals->discount_total ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Calculate each selected date chronologically
        |--------------------------------------------------------------------------
        */

        foreach ($sortedDates as $row) {
            $dateKey = Carbon::parse($row->ledger_date)->format('Y-m-d');
            $carbonDate = Carbon::parse($dateKey);

            $purchases = $groupedPurchases->get($dateKey, collect());
            $payments = $groupedPayments->get($dateKey, collect());

            // Preserve the original integer balance before the current date.
            $remaining = (int) $runningBalance;

            $totalPurchases = $purchases->sum('computed_total');
            $paymentAmount = $payments->sum('amount');
            $discountAmount = $payments->sum('discount');

            $final = ($remaining + $totalPurchases)
                - ($paymentAmount + $discountAmount);

            $balances[$dateKey] = [
                'date' => $carbonDate,
                'remaining' => $remaining,
                'totalPurchases' => (int) $totalPurchases,
                'payment' => (int) $paymentAmount,
                'discount' => (int) $discountAmount,
                'final' => (int) $final,
            ];

            /*
             * Keep the running balance untruncated between dates.
             *
             * getBalanceBefore() originally summed all previous transactions
             * before returning an integer. Keeping the raw running value here
             * preserves that behavior for the next selected date.
             */
            $runningBalance += $totalPurchases
                - $paymentAmount
                - $discountAmount;
        }

        return $balances;
    }

    /**
     * Display customers purchases report given a certain date.
     */
    public function reports(Request $request)
    {
        // Use provided date or today (Y-m-d).
        $date = $request->date
            ? Carbon::parse($request->date)->format('Y-m-d')
            : BusinessDate::toDateString();

        // Fetch customers who had purchases on that exact date.
        $customers = $this->fetchCustomersWithPurchasesOnDate($date);

        // Build per-customer report data for that date.
        $reportData = $this->buildDailyReport($customers, $date);

        // Pass date and reportData to the view.
        return view('pages.customers.daily-reports', compact('date', 'reportData'));
    }

    /**
     * Fetch customers who had purchases on the specified date.
     *
     * Only the purchases and payments for that date are eager-loaded.
     */
    private function fetchCustomersWithPurchasesOnDate(string $date)
    {
        return Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->whereHas('purchases', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->with([
                'purchases' => function ($query) use ($date) {
                    $query->whereDate('date', $date)
                        ->with(['truckFruit', 'truckFruit.fruit']);
                },
                'payments' => function ($query) use ($date) {
                    $query->whereDate('date', $date);
                },
            ])
            ->orderBy('location')
            ->orderBy('name')
            ->get();
    }

    /**
     * Build the daily purchase report.
     *
     * Previous balances are aggregated for all customers in one batch
     * instead of querying purchases and payments separately for each customer.
     */
    private function buildDailyReport($customers, string $date): array
    {
        $data = [];

        if ($customers->isEmpty()) {
            return $data;
        }

        $carbonDate = Carbon::parse($date);

        /*
        |--------------------------------------------------------------------------
        | 1. Fetch previous financial totals for all report customers
        |--------------------------------------------------------------------------
        */

        $customerIds = $customers->pluck('id')->all();

        $previousTotals = app(CustomerFinancialService::class)
            ->totalsByCustomer($customerIds, $date);

        /*
        |--------------------------------------------------------------------------
        | 2. Build each customer's report
        |--------------------------------------------------------------------------
        */

        $financialService = app(CustomerFinancialService::class);

        foreach ($customers as $customer) {
            // Purchases have been eager-loaded for the selected date.
            $purchases = $customer->purchases
                ->sortByDesc('id')
                ->values();

            // Payments have been eager-loaded for the selected date.
            $paymentAmount = $customer->payments->sum('amount');
            $discountAmount = $customer->payments->sum('discount');

            // Total of purchases for this date.
            $totalPurchases = $purchases->sum(function ($purchase) {
                return $purchase->computed_total;
            });

            // Calculate the remaining balance before this date.
            $totals = $previousTotals->get($customer->id);

            $remaining = $financialService->calculateBalance(
                $customer->account,
                $totals->purchase_total ?? 0,
                $totals->payment_total ?? 0,
                $totals->discount_total ?? 0
            );

            // Final balance after today's purchases and payments.
            $final = ($remaining + $totalPurchases)
                - ($paymentAmount + $discountAmount);

            $data[] = [
                'customer' => $customer,
                'purchases' => $purchases,
                'totalPurchases' => (int) $totalPurchases,
                'remaining' => (int) $remaining,
                'payment' => (int) $paymentAmount,
                'discount' => $discountAmount ? (float) $discountAmount : 0,
                'final' => (int) $final,
            ];
        }

        return $data;
    }
}
