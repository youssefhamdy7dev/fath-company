<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
        $driverId = $this->route('driver')->id ?? null;
        return [
            'name'  => 'required|string|max:255|unique:drivers,name,' . $driverId,
            'phone' => 'nullable|string|max:11|min:11|unique:drivers,phone,' . $driverId,
        ];
    }

    /**
     * Customize validation messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return (new StoreDriverRequest())->messages();
    }
}
