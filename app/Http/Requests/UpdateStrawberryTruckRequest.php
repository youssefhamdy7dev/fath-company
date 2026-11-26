<?php

namespace App\Http\Requests;

class UpdateStrawberryTruckRequest extends StoreStrawberryTruckRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'fruits.*.id' => [
                'nullable',
                'integer',
                'exists:strawberry_truck_fruits,id',
            ],
        ]);
    }
}
