<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'name' => 'required|string|unique:employees,name|max:255',
            'payment' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'over_withdrawal_limit' => 'numeric',
            'remaining_withdrawal' => 'numeric',
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
            'name.required' => 'اسم الموظف مطلوب.',
            'name.unique' => 'هذا الاسم موجود بالفعل.',
            'payment.required' => 'يومية الموظف مطلوبة.',
            'payment.integer' => 'اليومية يجب أن تكون رقم صحيح.',
            'payment.min' => 'اليومية لا يمكن أن تكون 0',
            'start_date.required' => 'تاريخ بدء العمل مطلوب.',
            'start_date.date' => 'التاريخ غير صالح.',
            'over_withdrawal_limit.numeric' => 'الرقم غير صحيح',
            'remaining_withdrawal.numeric' => 'الرقم غير صحيح',
        ];
    }
}
