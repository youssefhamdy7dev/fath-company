<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'date'        => [
                'required',
                'date',
                Rule::unique('customer_payments')
                    ->where(fn($q) => $q->where('customer_id', $this->customer_id))
            ],
            'amount'      => ['required', 'integer', 'min:1'],
            'discount'    => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Customize validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'الزبون مطلوب.',
            'customer_id.exists'   => 'الزبون المحدد غير موجود.',

            'date.required'        => 'يرجى إدخال التاريخ.',
            'date.date'            => 'التاريخ غير صحيح.',
            'date.unique' => 'هذا الزبون لديه تحصيل مسجل بالفعل في هذا التاريخ.',

            'amount.required'      => 'المبلغ مطلوب.',
            'amount.integer'       => 'المبلغ غير صحيح.',
            'amount.min'           => 'المبلغ يجب أن يكون 1 على الأقل.',

            'discount.numeric'     => 'الخصم غير صحيح.',
            'discount.min'         => 'الخصم غير صحيح.',
        ];
    }

    /**
     * Customize failed validation to return JSON for AJAX
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
