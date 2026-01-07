<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFruitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->user()) {
            return true;
        } else {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:fruits,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    /**
     * Customize validation messages
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'إسم الصنف مطلوب',
            'name.unique' => 'هذا الصنف مسجل بالفعل',
            'name.max' => 'الإسم طويل للغاية',
            'image.image' => 'يرجى إرفاق صورة صحيحة',
            'image.mimes' => 'الصورة يجب أن تكون بصيغة jpeg, png, jpg, webp',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت',
        ];
    }
}
