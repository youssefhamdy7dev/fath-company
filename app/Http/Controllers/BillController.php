<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Client;
use App\Models\Fruit;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::whereHas('truckFruit.truck.bill')
            ->orderBy('name')
            ->get();

        $fruits = Fruit::orderBy('name')->get();

        $groupedBills = $clients->map(function ($client) {
            $bills = Bill::whereHas('truck.truckFruits', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
                ->with([
                    'truck',
                    'truck.truckFruits.fruit',
                    'truck.truckFruits.client',
                ])
                ->orderBy('billing_date', 'desc')
                ->get();

            $bills->each(function ($bill) {
                // Keep the original bill price; JavaScript applies the display rounding
                // to each bill before calculating the filtered client total.
                $bill->setAttribute('index_bill_price', (float) $bill->bill_price);
            });

            return [
                'client' => $client,
                'bills' => $bills,
            ];
        });

        $clientOptions = $clients;

        return view('pages.bills.index', compact(
            'groupedBills',
            'clientOptions',
            'fruits'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect('/trucks');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillRequest $request)
    {
        DB::transaction(function () use ($request) {
            $bill = Bill::create($request->only(
                'billing_date',
                'percentage',
                'expenses',
                'grand_total',
                'truck_id',
                'totals',
                'notes'
            ));

            foreach ($request['items'] as $group) {
                foreach ($group as $item) {
                    $bill->items()->create([
                        'box_class' => $item['box_class'],
                        'price' => $item['price'],
                        'total_boxes' => $item['total_boxes'],
                        'total_weight' => $item['total_weight'],
                        'total_amount' => $item['total_amount'],
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'تم صرف الفاتورة بنجاح',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        $bill->load([
            'truck',
            'truck.truckFruits.fruit',
            'truck.truckFruits.client',
        ]);

        return view('pages.bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        $bill->load([
            'truck',
            'truck.truckFruits.fruit',
            'truck.truckFruits.client',
        ]);

        return view('pages.bills.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        DB::transaction(function () use ($bill, $request) {
            $bill->update($request->only(
                'billing_date',
                'percentage',
                'expenses',
                'grand_total',
                'totals',
                'notes'
            ));

            $itemsIds = collect($request['items'])
                ->pluck('id')
                ->filter()
                ->all();

            $bill->items()->whereNotIn('id', $itemsIds)->delete();

            foreach ($request['items'] as $item) {
                $bill->items()->updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'box_class' => $item['box_class'],
                        'price' => $item['price'],
                        'total_boxes' => $item['total_boxes'],
                        'total_weight' => $item['total_weight'],
                        'total_amount' => $item['total_amount'],
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'تم تحديث الفاتورة بنجاح',
            'redirect_url' => route('bills.show', $bill->id),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        $bill->delete();

        return redirect()->back();
    }
}
