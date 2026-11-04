<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\CustomerPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        $query = CustomerPayment::query()
            ->select('customer_payments.*')
            ->join('customers', 'customers.id', '=', 'customer_payments.customer_id')
            ->with('customer')
            ->whereDate('customer_payments.date', $carbonDate)
            ->where('customers.name', '!=', 'نقدية')
            ->where('customers.name', '!=', 'المحل');

        // 🔎 filter by customer name
        if ($request->filled('name')) {
            $query->where('customers.name', 'like', '%' . $request->name . '%');
        }

        // 📍 filter by location
        if ($request->filled('location')) {
            $query->where('customers.location', $request->location);
        }

        // ✅ SORTING (IMPORTANT PART)
        $query
            ->orderBy('customers.location', 'ASC')
            ->orderBy('customers.name', 'ASC');

        $payments = $query->get();

        // ✅ Group AFTER sorting
        $grouped = $payments->groupBy(fn($p) => $p->customer->location);

        // needed for modals / selects
        $customers = Customer::whereNotIn('name', ['نقدية', 'المحل'])
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        $displayDate = Carbon::parse($carbonDate);

        if ($request->ajax()) {
            return view('pages.payments.partials.payments-by-date-table', [
                'grouped'     => $grouped,
                'customers'   => $customers,
                'displayDate' => $displayDate,
            ])->render();
        }

        return view('pages.payments.payments-by-date', [
            'locations'   => $this->locations,
            'grouped'     => $grouped,
            'displayDate' => $displayDate,
            'carbonDate'  => $carbonDate,
            'customers'   => $customers,
        ]);
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
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $request->date;

        // Load all customers with needed relations
        $customers = Customer::with([
            'payments',
            'purchases',
            'purchases.truckFruit',
        ])->get();

        $balances = [];

        foreach ($customers as $customer) {
            $balances[$customer->id] = transform_numeric_value($customer->getBalanceBefore($date));
        }

        return response()->json([
            'success' => true,
            'balances' => $balances,
            'formatted' => transform_numbers(Carbon::parse($date)->format('d-m-Y')),
        ]);
    }
}
