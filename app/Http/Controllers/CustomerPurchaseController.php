<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\CustomerPurchase;

class CustomerPurchaseController extends Controller
{
    /**
     * Store new customer purchase.
     */
    public function store(StorePurchaseRequest $request)
    {
        $purchase = CustomerPurchase::create($request->validated());

        return response()->json([
            'status'       => 'success',
            'message'      => 'تمت إضافة عملية الشراء بنجاح.',
            'redirect_url' => route('trucks.show', $purchase->truckFruit->truck->id),
        ]);
    }

    /**
     * Update existing customer purchase.
     */
    public function update(UpdatePurchaseRequest $request, CustomerPurchase $purchase)
    {
        $purchase->update($request->validated());

        return response()->json([
            'status'       => 'success',
            'message'      => 'تم تعديل عملية الشراء بنجاح.',
            'redirect_url' => route('trucks.show', $purchase->truckFruit->truck->id),
        ]);
    }

    /**
     * Delete purchase.
     */
    public function destroy(CustomerPurchase $purchase)
    {
        $purchase->delete();
        return redirect()->back();
    }
}
