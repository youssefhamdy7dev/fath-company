<?php

namespace App\Http\Requests;

class UpdateStrawberryPaymentRequest extends StoreStrawberryPaymentRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'amount' => ['required', 'integer', 'min:1'],
        ]);
    }
}
