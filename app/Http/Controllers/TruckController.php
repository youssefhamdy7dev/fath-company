<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Http\Requests\StoreTruckRequest;
use App\Http\Requests\UpdateTruckRequest;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Fruit;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TruckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1️⃣ Get all trucks paginated normally
        $trucks = Truck::with([
            'bill',
            'driver',
            'truckFruits',
            'truckFruits.client',
            'truckFruits.fruit',
            'truckFruits.customerPurchases',
            'truckFruits.customerPurchases.customer',
        ])
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        // 2️⃣ Split using accessor logic (PHP-side)
        $finished = $trucks->filter(
            fn($truck) =>
            $truck->numberOfBoughtBoxes >= $truck->total_boxes
        );

        $unfinished = $trucks->filter(
            fn($truck) =>
            $truck->numberOfBoughtBoxes < $truck->total_boxes
        );

        // 3️⃣ Manually paginate both collections
        $unfinishedTrucks = $this->paginateCollection($unfinished, 10, 'unfinished_page');
        $finishedTrucks   = $this->paginateCollection($finished, 5, 'finished_page');

        return view('pages.trucks.index', compact(
            'finishedTrucks',
            'unfinishedTrucks'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $drivers = Driver::get();
        $fruits = Fruit::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('pages.trucks.create', compact('drivers', 'fruits', 'clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTruckRequest $request)
    {
        DB::transaction(function () use ($request) {
            // 1️⃣ Create the main truck
            $truck = Truck::create($request->only('date', 'total_boxes', 'freight', 'driver_id'));

            // 2️⃣ Create each TruckFruit row manually
            foreach ($request->fruits as $fruit) {
                $truck->truckFruits()->create([
                    'fruit_id' => $fruit['fruit_id'],
                    'client_id' => $fruit['client_id'] ?? NULL,
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
        return redirect()->route('trucks.index')->with('success', 'تم إضافة العربة بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Truck $truck)
    {
        $boxTypes = [
            'big_box' => 'برنيكة كبيرة',
            'normal_box' => 'برنيكة صغيرة',
            'small_box' => 'برنيكة 10 كيلو',
            'small_net' => 'برنيكة شبك',
        ];

        // Load relationships
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

        $customers = Customer::select('id', 'name', 'location')->orderBy('location')->orderBy('name')->get();
        return view('pages.trucks.show', compact('truck', 'customers', 'boxTypes'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Truck $truck)
    {
        $truck->load(['driver', 'truckFruits']);
        $drivers = Driver::get();
        $fruits = Fruit::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        return view('pages.trucks.edit', compact('truck', 'drivers', 'fruits', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTruckRequest $request, Truck $truck)
    {
        DB::transaction(function () use ($request, $truck) {
            // 1️⃣ Update main truck data
            $truck->update($request->only('date', 'total_boxes', 'freight', 'driver_id'));
            // 2️⃣ Collect fruit_id + box_type from request
            $requestKeys = collect($request->fruits)
                ->map(fn($f) => $f['fruit_id'] . '-' . $f['box_type'])
                ->all();
            // 3️⃣ Delete truckFruits not included in request
            $truck->truckFruits()
                ->whereNotIn(DB::raw("CONCAT(fruit_id,'-',box_type)"), $requestKeys)
                ->delete();
            // 4️⃣ Update existing or create new
            foreach ($request->fruits as $fruitData) {
                $truck->truckFruits()->updateOrCreate(
                    [
                        'fruit_id' => $fruitData['fruit_id'],
                        'box_type' => $fruitData['box_type'],
                    ],
                    [
                        'client_id' => $fruitData['client_id'] ?? NULL,
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
        return redirect()->route('trucks.show', $truck->id)->with('success', 'تم تحديث العربة بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Truck $truck)
    {
        $truck->delete();
        return redirect()->route('trucks.index')->with('success', 'تم حذف العربة بنجاح.');
    }

    protected function paginateCollection(
        Collection $items,
        int $perPage = 10,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $page = request()->input($pageName, 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }
}
