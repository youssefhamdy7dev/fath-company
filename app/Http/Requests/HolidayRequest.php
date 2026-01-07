<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
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
        $employeeId = $this->route('employee')->id ?? null;

        return [
            'date' => 'required|date|unique:employee_holidays,date,NULL,id,employee_id,' . $employeeId,
            'notes' => 'nullable|string|max:255',
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
            'date.required' => 'التاريخ مطلوب.',
            'date.date' => 'يجب أن يكون التاريخ صالحًا.',
            'date.unique' => 'هناك أجازة مسجلة لهذا التاريخ بالفعل لهذا الموظف.',
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
