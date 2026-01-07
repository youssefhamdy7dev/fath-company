<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    protected $locations = [
        'ميدان صقر',
        'الكويتية',
        'صقر',
        'العمومى بساتين',
        'البساتين',
        'سوق البساتين',
        'السد العالى',
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
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $locations = $this->locations;

    //     $query = Customer::query()
    //         ->whereNotIn('name', ['نقدية', 'المحل'])
    //         ->orderBy('location')->orderBy('name');

    //     // Filter by name
    //     if ($request->filled('name')) {
    //         $query->where('name', 'like', '%' . $request->name . '%');
    //     }

    //     // Filter by location
    //     if ($request->filled('location') && $request->location != '') {
    //         $query->where('location', $request->location);
    //     }

    //     // Custom ordering
    //     $query->orderByRaw("FIELD(location, '" . implode("','", $locations) . "')")
    //         ->orderBy('name');

    //     // Pagination
    //     // if ($request->filled('name') || ($request->filled('location') && $request->location != '')) {
    //     //     $customers = $query->paginate(50)->withQueryString();
    //     // } else {
    //     //     $customers = $query->paginate(20)->withQueryString();
    //     // }
    //     $customers = $query->get();
    //     if ($request->ajax()) {
    //         return view('pages.customers.partials.customers-table', compact('customers'))->render();
    //     }

    //     return view('pages.customers.index', compact('customers', 'locations'));
    // }
    public function index()
    {
        $locations = $this->locations;
        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderByRaw("FIELD(location, '" . implode("','", $locations) . "')")
            ->orderBy('name')
            ->get();
        return view('pages.customers.index', compact('customers', 'locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = $this->locations;
        return view('pages.customers.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return redirect()->route('customers.index')->with('success', 'تم إضافة الزبون بنجاح.');
        // return redirect()->back()->with('success', 'تم إضافة الزبون بنجاح.');
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
        $locations = $this->locations;
        return view('pages.customers.edit', compact('customer', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return redirect()->route('customers.index')->with('success', 'تم تحديث بيانات الزبون بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', "تم حذف الزبون: {$customer->name} بنجاح.");
    }

    /**
     * Display the purchases of a specific customer.
     */
    // public function purchasesOld(Customer $customer)
    // {
    //     $purchaseQuery = $this->basePurchaseQuery($customer);
    //     // Paginated unique dates
    //     $dates = $this->paginatePurchaseDates($customer);
    //     // Grouped purchases for the selected dates
    //     $groupedPurchases = $this->fetchGroupedPurchases($purchaseQuery, $dates);
    //     // Balances computed per date
    //     $balances = $this->computeBalances($customer, $groupedPurchases);
    //     return view('pages.customers.purchases', compact(
    //         'customer',
    //         'dates',
    //         'groupedPurchases',
    //         'balances'
    //     ));
    // }

    // ################# START OF NEW CONTENT #################
    public function purchases(Customer $customer)
    {
        $dates = $this->paginateLedgerDates($customer);

        $groupedPurchases = $this->fetchGroupedPurchasesNew($customer, $dates);
        $groupedPayments  = $this->fetchGroupedPayments($customer, $dates);

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
            ->groupBy(fn($p) => $p->date->format('Y-m-d'));
    }
    private function fetchGroupedPayments(Customer $customer, $dates)
    {
        $selectedDates = $dates->pluck('ledger_date');

        return $customer->payments()
            ->whereIn(DB::raw('DATE(date)'), $selectedDates)
            ->orderByDesc('date')
            ->get()
            ->groupBy(fn($p) => $p->date->format('Y-m-d'));
    }
    private function computeBalancesNew(Customer $customer, $ledgerDates, $groupedPurchases, $groupedPayments)
    {
        $balances = [];

        foreach ($ledgerDates as $row) {
            $dateKey = Carbon::parse($row->ledger_date)->format('Y-m-d');
            $carbonDate = Carbon::parse($dateKey);

            $purchases = $groupedPurchases[$dateKey] ?? collect();
            $payments  = $groupedPayments[$dateKey] ?? collect();

            $remaining = $customer->getBalanceBefore($carbonDate);

            $totalPurchases = $purchases->sum('computed_total');
            $paymentAmount  = $payments->sum('amount');
            $discountAmount = $payments->sum('discount');

            $final = ($remaining + $totalPurchases) - ($paymentAmount + $discountAmount);

            $balances[$dateKey] = [
                'date'           => $carbonDate,
                'remaining'      => (int) $remaining,
                'totalPurchases' => (int) $totalPurchases,
                'payment'        => (int) $paymentAmount,
                'discount'       => (int) $discountAmount,
                'final'          => (int) $final,
            ];
        }

        return $balances;
    }

    // ################## END OF NEW CONTENT ##################

    /**
     * Display the payments of a specific customer.
     */
    // public function payments(Customer $customer)
    // {
    //     $customer->load('payments');

    //     // Sort payments (newest first)
    //     $payments = $customer->payments()->with('customer')->orderByDesc('date')->paginate(10);

    //     return view('pages.customers.payments', compact('customer', 'payments'));
    // }

    /**
     * Base query with common eager loads.
     */
    // private function basePurchaseQuery(Customer $customer)
    // {
    //     return $customer->purchases()->with([
    //         'customer',
    //         'truckFruit',
    //         'truckFruit.fruit',
    //         'truckFruit.truck',
    //         'truckFruit.truck.truckFruits',
    //         'truckFruit.truck.truckFruits.fruit',
    //     ]);
    // }

    /**
     * Paginate distinct purchase dates.
     */
    // private function paginatePurchaseDates(Customer $customer)
    // {
    //     return $customer->purchases()
    //         ->selectRaw('DATE(date) AS purchase_date')
    //         ->groupBy('purchase_date')
    //         ->orderByDesc('purchase_date')
    //         ->paginate(5);
    // }

    /**
     * Fetch purchases grouped by date for the selected paginated dates.
     */
    // private function fetchGroupedPurchases($query, $dates)
    // {
    //     $selected = $dates->pluck('purchase_date');

    //     return $query->clone()
    //         ->whereIn(DB::raw('DATE(date)'), $selected)
    //         ->orderByDesc('date')
    //         ->get()
    //         ->groupBy(fn($p) => $p->date->format('Y-m-d'));
    // }

    /**
     * Compute running balances for grouped purchase dates.
     */
    // private function computeBalances(Customer $customer, $groupedPurchases)
    // {
    //     $balances  = [];
    //     $payments  = $customer->payments;

    //     foreach ($groupedPurchases->sortKeys() as $date => $rows) {
    //         $carbonDate      = Carbon::parse($date);
    //         $remaining       = $customer->getBalanceBefore($carbonDate) ?? 0;
    //         $totalPurchases  = $rows->sum('computed_total');
    //         $paymentAmount   = $payments->where('date', $carbonDate)->sum('amount');
    //         $discountAmount  = $payments->where('date', $carbonDate)->sum('discount');

    //         $final = ($remaining + $totalPurchases) - ($paymentAmount + $discountAmount);

    //         $balances[$date] = [
    //             'remaining'      => $remaining,
    //             'totalPurchases' => $totalPurchases,
    //             'payment'        => $paymentAmount,
    //             'discount'       => $discountAmount,
    //             'final'          => $final,
    //             'date'           => $carbonDate,
    //         ];
    //     }
    //     return $balances;
    // }

    /**
     * Display customers purchases report given a certain date
     */
    public function reports(Request $request)
    {
        // use provided date or today (Y-m-d)
        $date = $request->date
            ? Carbon::parse($request->date)->format('Y-m-d')
            : now()->format('Y-m-d');
        // Fetch customers who had purchases on that exact date, with required eager loads
        $customers = $this->fetchCustomersWithPurchasesOnDate($date);
        // Build per-customer report data for that date
        $reportData = $this->buildDailyReport($customers, $date);
        // pass date & reportData to view
        return view('pages.customers.daily-reports', compact('date', 'reportData'));
    }
    private function fetchCustomersWithPurchasesOnDate(string $date)
    {
        // whereHas ensures only customers that bought on $date are returned
        return Customer::whereNotIn('name', ['نقدية', 'المحل'])->whereHas('purchases', function ($q) use ($date) {
            $q->whereDate('date', $date);
        })
            ->with([
                // load purchases but we will filter on collection later — we still eager load truckFruit & fruit
                'purchases' => function ($q) use ($date) {
                    $q->whereDate('date', $date)
                        ->with(['truckFruit', 'truckFruit.fruit']);
                },
                // load payments (we'll inspect payments for same date via collection)
                'payments' => function ($q) use ($date) {
                    $q->where('date', $date);
                }
            ])
            ->orderBy('location')
            ->orderBy('name')
            ->get();
    }

    private function buildDailyReport($customers, string $date): array
    {
        $data = [];
        // parse once
        $carbonDate = Carbon::parse($date);
        foreach ($customers as $customer) {
            // purchases have been eager-loaded (only those on $date because of fetchCustomersWithPurchasesOnDate)
            $purchases = $customer->purchases->sortByDesc('id')->values();
            // payments collection (all) — sum those with same date
            $paymentAmount = $customer->payments->where('date', $carbonDate)->sum('amount');
            $discountAmount = $customer->payments->where('date', $carbonDate)->sum('discount');
            // total of purchases for this date (uses computed_total accessor)
            $totalPurchases = $purchases->sum(function ($p) {
                return $p->computed_total;
            });
            // remaining balance BEFORE this date (uses your model method)
            $remaining = $customer->getBalanceBefore($carbonDate);
            // final after today's sales & today's payments/discounts
            $final = ($remaining + $totalPurchases) - ($paymentAmount + $discountAmount);
            $data[] = [
                'customer'       => $customer,
                'purchases'      => $purchases,
                'totalPurchases' => (int)$totalPurchases,
                'remaining'      => (int)$remaining,
                'payment'        => (int)$paymentAmount,
                'discount'       => $discountAmount ? (float)$discountAmount : 0,
                'final'          => (int)$final,
            ];
        }

        return $data;
    }
}
