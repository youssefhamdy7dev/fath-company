<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTruckRequest;
use App\Http\Requests\UpdateTruckRequest;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Fruit;
use App\Models\Truck;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class TruckController extends Controller
{
    /**
     * Display trucks in separate finished and unfinished lists.
     */
    public function index()
    {
        $filters = [
            'date' => request()->query('date'),
            'driver_ids' => array_values(array_unique(array_filter(
                array_map('intval', (array) request()->query('driver_ids', []))
            ))),
            'fruit_ids' => array_values(array_unique(array_filter(
                array_map('intval', (array) request()->query('fruit_ids', []))
            ))),
        ];

        $hasFilters = filled($filters['date'])
            || !empty($filters['driver_ids'])
            || !empty($filters['fruit_ids']);

        $unfinishedQuery = $this->truckQueryByPurchaseStatus(false, $filters)
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $finishedQuery = $this->truckQueryByPurchaseStatus(true, $filters)
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($hasFilters) {
            // Return every matching truck without pagination.
            $unfinishedTrucks = $unfinishedQuery->get();
            $finishedTrucks = $finishedQuery->get();
        } else {
            // Keep the existing fast, paginated default listing.
            $unfinishedTrucks = $unfinishedQuery
                ->paginate(10, ['*'], 'unfinished_page');

            $finishedTrucks = $finishedQuery
                ->paginate(5, ['*'], 'finished_page');
        }

        $drivers = Driver::orderBy('name')->get(['id', 'name']);
        $fruits = Fruit::orderBy('name')->get(['id', 'name']);

        return view('pages.trucks.index', compact(
            'finishedTrucks',
            'unfinishedTrucks',
            'drivers',
            'fruits',
            'filters',
            'hasFilters'
        ));
    }

    /**
     * Build a truck query filtered by its purchase completion status
     * and the selected truck filters.
     */
    private function truckQueryByPurchaseStatus(
        bool $finished,
        array $filters = []
    ): Builder {
        $boughtBoxesSubquery = DB::table('truck_fruit')
            ->join(
                'customer_purchase',
                'customer_purchase.truck_fruit_id',
                '=',
                'truck_fruit.id'
            )
            ->whereColumn('truck_fruit.truck_id', 'trucks.id')
            ->selectRaw('COALESCE(SUM(customer_purchase.number_of_boxes), 0)');

        $operator = $finished ? '>=' : '<';

        $query = Truck::query()
            ->select('trucks.*')
            ->selectSub(clone $boughtBoxesSubquery, 'bought_boxes')
            ->whereRaw(
                '(' . $boughtBoxesSubquery->toSql() . ") {$operator} trucks.total_boxes"
            )
            ->with([
                'bill',
                'driver',
                'truckFruits.client',
                'truckFruits.fruit',
            ]);

        if (filled($filters['date'] ?? null)) {
            $query->whereDate('trucks.date', $filters['date']);
        }

        if (!empty($filters['driver_ids'])) {
            $query->whereIn('trucks.driver_id', $filters['driver_ids']);
        }

        if (!empty($filters['fruit_ids'])) {
            $query->whereHas('truckFruits', function (Builder $fruitQuery) use ($filters) {
                $fruitQuery->whereIn('fruit_id', $filters['fruit_ids']);
            });
        }

        return $query;
    }

    /**
     * Show the form for creating a truck.
     */
    public function create()
    {
        $drivers = Driver::get();
        $fruits = Fruit::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('pages.trucks.create', compact(
            'drivers',
            'fruits',
            'clients'
        ));
    }

    /**
     * Store a new truck and its associated fruits.
     */
    public function store(StoreTruckRequest $request)
    {
        DB::transaction(function () use ($request) {
            $truck = Truck::create(
                $request->only(
                    'date',
                    'total_boxes',
                    'freight',
                    'driver_id'
                )
            );

            foreach ($request->fruits as $fruit) {
                $truck->truckFruits()->create([
                    'fruit_id' => $fruit['fruit_id'],
                    'client_id' => $fruit['client_id'] ?? null,
                    'box_type' => $fruit['box_type'],
                    'number_of_boxes' => $fruit['number_of_boxes'],
                    'second_class_boxes' => $fruit['second_class_boxes'] ?? null,
                    'third_class_boxes' => $fruit['third_class_boxes'] ?? null,
                    'unified_weight' => $fruit['unified_weight'] ?? null,
                    'unified_unit_price' => $fruit['unified_unit_price'] ?? null,
                    'unified_box_price' => $fruit['unified_box_price'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('trucks.index')
            ->with('success', 'تم إضافة العربة بنجاح.');
    }

    /**
     * Display the specified truck.
     */
    public function show(Truck $truck)
    {
        $boxTypes = [
            'big_box' => 'برنيكة كبيرة',
            'normal_box' => 'برنيكة وسط',
            'small_box' => 'برنيكة صغيرة',
            'small_net' => 'برنيكة شبك',
        ];

        $truck->load([
            'bill',
            'bill.items',
            'driver',
            'truckFruits',
            'truckFruits.client',
            'truckFruits.fruit',
            'truckFruits.customerPurchases' => function ($query) {
                $query->orderBy('date', 'asc');
            },
            'truckFruits.customerPurchases.customer',
            'truckFruits.customerPurchases.truckFruit.fruit',
        ]);

        $customers = Customer::select('id', 'name', 'location')
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        return view('pages.trucks.show', compact(
            'truck',
            'customers',
            'boxTypes'
        ));
    }

    /**
     * Show the form for editing the specified truck.
     */
    public function edit(Truck $truck)
    {
        $truck->load(['driver', 'truckFruits']);

        $drivers = Driver::get();
        $fruits = Fruit::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('pages.trucks.edit', compact(
            'truck',
            'drivers',
            'fruits',
            'clients'
        ));
    }

    /**
     * Update a truck and synchronize its associated fruits.
     */
    public function update(UpdateTruckRequest $request, Truck $truck)
    {
        DB::transaction(function () use ($request, $truck) {
            $truck->update(
                $request->only(
                    'date',
                    'total_boxes',
                    'freight',
                    'driver_id'
                )
            );

            $requestKeys = collect($request->fruits)
                ->map(fn($fruit) => $fruit['fruit_id'] . '-' . $fruit['box_type'])
                ->all();

            $truck->truckFruits()
                ->whereNotIn(
                    DB::raw("CONCAT(fruit_id,'-',box_type)"),
                    $requestKeys
                )
                ->delete();

            foreach ($request->fruits as $fruitData) {
                $truck->truckFruits()->updateOrCreate(
                    [
                        'fruit_id' => $fruitData['fruit_id'],
                        'box_type' => $fruitData['box_type'],
                    ],
                    [
                        'client_id' => $fruitData['client_id'] ?? null,
                        'number_of_boxes' => $fruitData['number_of_boxes'],
                        'second_class_boxes' => $fruitData['second_class_boxes'] ?? null,
                        'third_class_boxes' => $fruitData['third_class_boxes'] ?? null,
                        'unified_weight' => $fruitData['unified_weight'] ?? null,
                        'unified_unit_price' => $fruitData['unified_unit_price'] ?? null,
                        'unified_box_price' => $fruitData['unified_box_price'] ?? null,
                    ]
                );
            }
        });

        return redirect()
            ->route('trucks.show', $truck->id)
            ->with('success', 'تم تحديث العربة بنجاح.');
    }

    /**
     * Delete the specified truck.
     */
    public function destroy(Truck $truck)
    {
        $truck->delete();

        return redirect()
            ->route('trucks.index')
            ->with('success', 'تم حذف العربة بنجاح.');
    }
}
