<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'name'  => 'required|string|max:255|unique:clients,name',
            'phone' => 'nullable|string|max:11|min:11|unique:clients,phone',
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
            'name.required' => 'يرجى إدخال الإسم.',
            'name.unique' => 'هذا الاسم مسجل مسبقًا.',
            'phone.unique' => 'هذا الرقم مسجل مسبقًا.',
            'phone.max' => 'رقم الهاتف غير صحيح',
            'phone.min' => 'رقم الهاتف غير صحيح',
        ];
    }
}
