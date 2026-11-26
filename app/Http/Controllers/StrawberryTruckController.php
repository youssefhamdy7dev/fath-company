<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStrawberryTruckRequest;
use App\Http\Requests\UpdateStrawberryTruckRequest;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\StrawberryTruck;
use App\Models\StrawberryTruckFruit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StrawberryTruckController extends Controller
{
    private const FRUIT_TYPES = [
        'فلوريدا',
        'إستفالة',
        'فرتونة',
        'فراولة',
    ];

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
            'fruit_types' => array_values(array_unique(array_filter(
                (array) request()->query('fruit_types', [])
            ))),
        ];

        $hasFilters = filled($filters['date'])
            || !empty($filters['driver_ids'])
            || !empty($filters['fruit_types']);

        $unfinishedQuery = $this->truckQueryByPurchaseStatus(false, $filters)
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $finishedQuery = $this->truckQueryByPurchaseStatus(true, $filters)
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($hasFilters) {
            $unfinishedTrucks = $unfinishedQuery->get();
            $finishedTrucks = $finishedQuery->get();
        } else {
            $unfinishedTrucks = $unfinishedQuery
                ->paginate(10, ['*'], 'unfinished_page');

            $finishedTrucks = $finishedQuery
                ->paginate(5, ['*'], 'finished_page');
        }

        $drivers = Driver::orderBy('name')->get(['id', 'name']);
        $fruitTypes = self::FRUIT_TYPES;

        return view('pages.strawberry.trucks.index', compact(
            'finishedTrucks',
            'unfinishedTrucks',
            'drivers',
            'fruitTypes',
            'filters',
            'hasFilters'
        ));
    }

    /**
     * Build a truck query filtered by purchase completion status.
     */
    private function truckQueryByPurchaseStatus(
        bool $finished,
        array $filters = []
    ): Builder {
        $boughtBoxesSubquery = DB::table('strawberry_truck_fruits')
            ->join(
                'strawberry_customer_purchases',
                'strawberry_customer_purchases.strawberry_truck_fruit_id',
                '=',
                'strawberry_truck_fruits.id'
            )
            ->whereColumn(
                'strawberry_truck_fruits.truck_id',
                'strawberry_trucks.id'
            )
            ->selectRaw(
                'COALESCE(SUM(strawberry_customer_purchases.number_of_boxes), 0)'
            );

        $operator = $finished ? '>=' : '<';

        $query = StrawberryTruck::query()
            ->select('strawberry_trucks.*')
            ->selectSub(clone $boughtBoxesSubquery, 'bought_boxes')
            ->whereRaw(
                '(' . $boughtBoxesSubquery->toSql() . ") {$operator} strawberry_trucks.total_boxes"
            )
            ->with([
                'driver',
                'truckFruits.client',
            ]);

        if (filled($filters['date'] ?? null)) {
            $query->whereDate('strawberry_trucks.date', $filters['date']);
        }

        if (!empty($filters['driver_ids'])) {
            $query->whereIn(
                'strawberry_trucks.driver_id',
                $filters['driver_ids']
            );
        }

        if (!empty($filters['fruit_types'])) {
            $query->whereHas('truckFruits', function (Builder $fruitQuery) use ($filters) {
                $fruitQuery->whereIn('fruit_type', $filters['fruit_types']);
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
        $clients = Client::orderBy('name')->get();
        $fruitTypes = self::FRUIT_TYPES;
        $boxTypes = StrawberryTruckFruit::BOX_TYPE_NAMES;

        return view('pages.strawberry.trucks.create', compact(
            'drivers',
            'clients',
            'fruitTypes',
            'boxTypes'
        ));
    }

    /**
     * Store a new truck and its associated varieties.
     */
    public function store(StoreStrawberryTruckRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $truck = StrawberryTruck::create([
                'date' => $validated['date'],
                'total_boxes' => $validated['total_boxes'],
                'freight' => $validated['freight'],
                'driver_id' => $validated['driver_id'] ?? null,
            ]);

            foreach ($validated['fruits'] as $fruitData) {
                $truck->truckFruits()->create(
                    $this->fruitAttributes($fruitData)
                );
            }
        });

        return redirect()
            ->route('strawberry-trucks.index')
            ->with('success', 'تم إضافة عربة الفراولة بنجاح.');
    }

    /**
     * Display the specified truck.
     */
    public function show(StrawberryTruck $truck)
    {
        $boxTypes = StrawberryTruckFruit::BOX_TYPE_NAMES;

        $truck->load([
            'driver',
            'truckFruits.client',
            'truckFruits.customerPurchases.customer',
            'truckFruits.customerPurchases.truckFruit',
        ]);

        $customers = Customer::orderBy('location')
            ->orderBy('name')
            ->get(['id', 'name', 'location']);

        return view('pages.strawberry.trucks.show', compact(
            'truck',
            'customers',
            'boxTypes'
        ));
    }

    /**
     * Show the form for editing the specified truck.
     */
    public function edit(StrawberryTruck $truck)
    {
        $truck->load(['driver', 'truckFruits']);

        $drivers = Driver::get();
        $clients = Client::orderBy('name')->get();
        $fruitTypes = self::FRUIT_TYPES;
        $boxTypes = StrawberryTruckFruit::BOX_TYPE_NAMES;

        return view('pages.strawberry.trucks.edit', compact(
            'truck',
            'drivers',
            'clients',
            'fruitTypes',
            'boxTypes'
        ));
    }

    /**
     * Update a truck and synchronize its varieties.
     *
     * Customer purchases are never modified here.
     * Purchases without manual overrides continue using the
     * current defaults from their associated truck fruit.
     */
    public function update(
        UpdateStrawberryTruckRequest $request,
        StrawberryTruck $truck
    ) {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $truck) {
            // Lock the truck while synchronizing its fruit rows.
            $truck = StrawberryTruck::whereKey($truck->id)
                ->lockForUpdate()
                ->firstOrFail();

            $truck->update([
                'date' => $validated['date'],
                'total_boxes' => $validated['total_boxes'],
                'freight' => $validated['freight'],
                'driver_id' => $validated['driver_id'] ?? null,
            ]);

            $existingFruits = $truck->truckFruits()
                ->with('customerPurchases')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $submittedIds = [];
            $submittedCombinations = [];

            // Validate duplicate fruit type / box type combinations.
            foreach ($validated['fruits'] as $fruitData) {
                $combination = $fruitData['fruit_type']
                    . '|'
                    . $fruitData['box_type'];

                if (in_array($combination, $submittedCombinations, true)) {
                    throw ValidationException::withMessages([
                        'fruits' => 'لا يمكن إضافة نفس الصنف ونوع الصندوق أكثر من مرة.',
                    ]);
                }

                $submittedCombinations[] = $combination;

                if (!empty($fruitData['id'])) {
                    $fruitId = (int) $fruitData['id'];

                    if (in_array($fruitId, $submittedIds, true)) {
                        throw ValidationException::withMessages([
                            'fruits' => 'تم إرسال نفس الصنف أكثر من مرة.',
                        ]);
                    }

                    $submittedIds[] = $fruitId;

                    if (!$existingFruits->has($fruitId)) {
                        throw ValidationException::withMessages([
                            'fruits' => 'بيانات الصنف غير صحيحة أو لا تتبع هذه العربة.',
                        ]);
                    }
                }
            }

            // Do not allow the truck's total boxes to become
            // lower than the quantity already purchased.
            $totalPurchasedBoxes = $existingFruits
                ->sum(function ($fruit) {
                    return $fruit->customerPurchases
                        ->sum('number_of_boxes');
                });

            if ($validated['total_boxes'] < $totalPurchasedBoxes) {
                throw ValidationException::withMessages([
                    'total_boxes' => 'لا يمكن تقليل إجمالي صناديق العربة عن الكمية التي تم شراؤها بالفعل.',
                ]);
            }

            foreach ($validated['fruits'] as $fruitData) {
                $fruitId = !empty($fruitData['id'])
                    ? (int) $fruitData['id']
                    : null;

                $attributes = $this->fruitAttributes($fruitData);

                if ($fruitId !== null) {
                    // Update the existing row by its stable ID.
                    // This keeps its customer purchases attached.
                    $existingFruit = $existingFruits->get($fruitId);

                    $purchasedBoxes = $existingFruit->customerPurchases
                        ->sum('number_of_boxes');

                    if ($fruitData['number_of_boxes'] < $purchasedBoxes) {
                        throw ValidationException::withMessages([
                            'fruits' => 'لا يمكن تقليل عدد الصناديق عن الكمية التي تم شراؤها بالفعل.',
                        ]);
                    }

                    // Update only the truck fruit defaults and details.
                    // Never update or copy values into customer purchases.
                    $existingFruit->update($attributes);

                    $existingFruits->forget($fruitId);
                } else {
                    // Create a new fruit row when no existing ID was submitted.
                    $truck->truckFruits()->create($attributes);
                }
            }

            // Any existing rows left here were omitted from the form.
            // Delete them only if they have no customer purchases.
            foreach ($existingFruits as $existingFruit) {
                if ($existingFruit->customerPurchases->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'fruits' => 'لا يمكن حذف صنف تم تسجيل مشتريات عليه.',
                    ]);
                }

                $existingFruit->delete();
            }
        });

        return redirect()
            ->route('strawberry-trucks.show', $truck->id)
            ->with('success', 'تم تحديث عربة الفراولة بنجاح.');
    }

    /**
     * Prepare the allowed attributes for a truck fruit row.
     */
    private function fruitAttributes(array $fruitData): array
    {
        return [
            'fruit_type' => $fruitData['fruit_type'],
            'client_id' => $fruitData['client_id'] ?? null,
            'box_type' => $fruitData['box_type'],
            'number_of_boxes' => $fruitData['number_of_boxes'],
            'second_class_boxes' => $fruitData['second_class_boxes'] ?? null,
            'third_class_boxes' => $fruitData['third_class_boxes'] ?? null,
            'unified_weight' => $fruitData['unified_weight'] ?? null,
            'unified_unit_price' => $fruitData['unified_unit_price'] ?? null,
            'unified_box_price' => $fruitData['unified_box_price'] ?? null,
        ];
    }

    /**
     * Delete the specified truck.
     */
    public function destroy(StrawberryTruck $truck)
    {
        $hasPurchases = $truck->truckFruits()
            ->whereHas('customerPurchases')
            ->exists();

        if ($hasPurchases) {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن حذف العربة لوجود مشتريات مسجلة عليها.');
        }

        $truck->delete();

        return redirect()
            ->route('strawberry-trucks.index')
            ->with('success', 'تم حذف العربة بنجاح.');
    }
}
