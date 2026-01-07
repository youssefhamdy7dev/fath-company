<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UpdateBillRequest extends FormRequest
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
            'billing_date' => ['required', 'date'],
            'percentage'   => ['required', 'numeric', 'between:0,100'],
            'expenses'   => ['nullable', 'numeric'],
            'grand_total' => ['required'],
            'truck_id'     => [
                'nullable',
                'integer',
                Rule::unique('bills', 'truck_id')->ignore($this->bill->id),
            ],
            'items' => ['required', 'array'],

            'items.*.box_class'    => ['required', 'string'],
            'items.*.price'        => ['required', 'numeric'],
            'items.*.total_boxes'  => ['required', 'integer', 'min:0'],
            'items.*.total_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Customize validation messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return [
            'billing_date.required' => 'التاريخ مطلوب.',
            'billing_date.date'     => 'صيغة التاريخ غير صحيحة.',

            'percentage.required' => 'العمولة مطلوبة.',
            'percentage.numeric'  => 'العمولة يجب أن تكون رقم.',
            'percentage.between'  => 'العمولة يجب أن تكون بين 0 و 100.',

            'expenses.numeric'  => 'المصاريف يجب أن تكون رقم.',

            'grand_total.required' => 'إجمالى الفاتورة غير مسجل.',

            'truck_id.unique'  => 'تم صرف فاتورة لهذه العربة بالفعل.',

            'items.required' => 'بيانات الفاتورة غير مكتملة.',
            'items.array'    => 'بيانات الفاتورة غير صحيحة.',

            'items.*.box_class.required' => 'الفئة مطلوبة.',
            'items.*.price.required'     => 'الفئة السعرية مطلوبة.',
            'items.*.total_boxes.required' => 'عدد الصناديق مطلوب.',
            'items.*.total_amount.required' => 'المبلغ الإجمالي مطلوب.',
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
