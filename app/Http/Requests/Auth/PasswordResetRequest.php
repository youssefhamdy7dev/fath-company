<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
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
            'secretKey' => 'required',
            'password' => 'required|min:8|same:password_confirmation',
        ];
    }

    /**
     * Customize validation rules messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return [
            'secretKey.required' => 'يرجى إدخال المفتاح السرى',
            'password.required' => 'يرجى إدخال كلمة المرور',
            'password.min' => 'كلمة المرور لا تقل عن 8 أحرف',
            'password.same' => 'كلمة السر غير متطابقة',
        ];
    }
}
