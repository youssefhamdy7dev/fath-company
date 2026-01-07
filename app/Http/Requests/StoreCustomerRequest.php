<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
        $locations = [
            'ميدان صقر',
            'الكويتية',
            'صقر',
            'العمومى بساتين',
            'البساتين',
            'سوق البساتين',
            'السد العالى',
            'أبو بريك',
            'المطبعة',
            'الجزيرة',
            'دار السلام',
            'البير',
            'المشير وأبو الوفا',
            'عبدالحميد مكى',
            'فايدة كامل',
            'حسنين الدسوقى',
            'المعادى',
            'أخرى'
        ];

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('customers')->where(fn($query) => $query->where('location', $this->location)),
            ],
            'phone' => ['nullable', 'string', 'max:11', 'min:11', 'unique:customers,phone'],
            'location' => ['required', Rule::in($locations)],
            'account' => ['required', 'integer', 'min:0'],
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
            'name.required' => 'يرجى إدخال إسم الزبون.',
            'name.unique' => 'هذا الزبون موجود بالفعل في نفس المنطقة.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل.',
            'phone.max' => 'رقم الهاتف غير صحيح',
            'phone.min' => 'رقم الهاتف غير صحيح',
            'location.required' => 'يجب اختيار المنطقة.',
            'location.in' => 'المنطقة المختارة غير صالحة.',
            'account.required' => 'يرجى إدخال حساب الزبون.',
            'account.integer' => 'يجب أن يكون الحساب رقم صحيح.',
            'account.min' => 'يجب أن يكون الحساب أكبر من أو يساوي 0.',
        ];
    }
}
