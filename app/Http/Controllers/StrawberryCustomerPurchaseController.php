<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStrawberryPurchaseRequest;
use App\Http\Requests\UpdateStrawberryPurchaseRequest;
use App\Models\StrawberryCustomerPurchase;

class StrawberryCustomerPurchaseController extends Controller
{
    public function store(StoreStrawberryPurchaseRequest $request)
    {
        $purchase = StrawberryCustomerPurchase::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة عملية الشراء بنجاح.',
            'redirect_url' => route(
                'strawberry-trucks.show',
                $purchase->strawberryTruckFruit->truck->id
            ),
        ]);
    }

    public function update(
        UpdateStrawberryPurchaseRequest $request,
        StrawberryCustomerPurchase $purchase
    ) {
        $purchase->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل عملية الشراء بنجاح.',
            'redirect_url' => route(
                'strawberry-trucks.show',
                $purchase->strawberryTruckFruit->truck->id
            ),
        ]);
    }

    public function destroy(StrawberryCustomerPurchase $purchase)
    {
        $purchase->delete();

        return redirect()->back();
    }
}
