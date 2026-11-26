<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StoreStrawberryPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $payment = $this->route('payment');
        $paymentId = $payment?->id;

        $dateRules = [
            'required',
            'date',
        ];

        if ($this->filled('customer_id')) {
            $uniqueRule = Rule::unique('strawberry_customer_payments')
                ->where(fn($query) => $query->where(
                    'customer_id',
                    $this->customer_id
                ));

            if ($paymentId !== null) {
                $uniqueRule->ignore($paymentId);
            }

            $dateRules[] = $uniqueRule;
        }

        return [
            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'date' => $dateRules,

            'amount' => [
                'required',
                'integer',
                'min:0',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'يرجى اختيار الزبون.',
            'customer_id.exists' => 'الزبون المحدد غير موجود.',

            'date.required' => 'يرجى إدخال التاريخ.',
            'date.date' => 'التاريخ غير صحيح.',
            'date.unique' => 'هذا الزبون لديه تحصيل فراولة مسجل بالفعل في هذا التاريخ.',

            'amount.required' => 'المبلغ مطلوب.',
            'amount.integer' => 'المبلغ غير صحيح.',
            'amount.min' => 'المبلغ غير صحيح.',

            'discount.numeric' => 'الخصم غير صحيح.',
            'discount.min' => 'الخصم غير صحيح.',

            'notes.string' => 'الملاحظات غير صحيحة.',
        ];
    }

    /**
     * Return JSON validation errors for AJAX requests.
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
