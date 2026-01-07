<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
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
        $paymentId = $this->route('payment')->id;

        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'date'        => [
                'required',
                'date',
                Rule::unique('customer_payments')
                    ->where(fn($q) => $q->where('customer_id', $this->customer_id))
                    ->ignore($paymentId)
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
        return (new StorePaymentRequest())->messages();
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
