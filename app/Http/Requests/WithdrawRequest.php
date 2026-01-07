<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WithdrawRequest extends FormRequest
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
        $employee = $this->route('employee'); // Route model binding
        $employeeId = $employee->id ?? null;

        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => [
                'required',
                'date',
                Rule::unique('employee_withdrawals', 'date')
                    ->where('employee_id', $employeeId),
            ],
            'notes' => ['nullable', 'string', 'max:255'],
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
            'amount.required' => 'المبلغ مطلوب.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقمًا.',
            'amount.min' => 'المبلغ يجب أن يكون على الأقل صفر.',
            'date.required' => 'التاريخ مطلوب.',
            'date.date' => 'يجب أن يكون التاريخ صالحًا.',
            'date.unique' => 'تم تسجيل سحب لهذا التاريخ بالفعل لهذا الموظف.',
            'notes.string' => 'الملاحظات يجب أن تكون نصية.',
            'notes.max' => 'الملاحظات لا يمكن أن تتجاوز 255 حرفًا.',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);

            throw new \Illuminate\Validation\ValidationException($validator, $response);
        }

        parent::failedValidation($validator);
    }
}
