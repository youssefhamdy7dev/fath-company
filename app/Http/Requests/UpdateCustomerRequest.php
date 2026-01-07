<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
                Rule::unique('customers')
                    ->ignore($this->customer->id)
                    ->where(fn($query) => $query->where('location', $this->location)),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:11',
                'min:11',
                Rule::unique('customers', 'phone')->ignore($this->customer->id)
            ],
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
        return (new StoreCustomerRequest())->messages();
    }
}
