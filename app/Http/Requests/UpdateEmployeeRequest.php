<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees', 'name')->ignore($this->employee->id),
            ],
            'payment' => ['required', 'integer', 'min:0'],
            'start_date' => ['required', 'date'],
            'over_withdrawal_limit' => ['numeric'],
            'remaining_withdrawal' => ['numeric'],
        ];
    }

    /**
     * Customize validation messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return (new StoreEmployeeRequest())->messages();
    }
}
