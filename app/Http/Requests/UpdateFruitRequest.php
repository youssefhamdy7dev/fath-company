<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFruitRequest extends FormRequest
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
        $fruitId = $this->route('fruit')->id;
        return [
            'name' => 'required|string|max:255|unique:fruits,name,' . $fruitId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // max 2MB
        ];
    }

    /**
     * Customize validation messages.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return (new StoreFruitRequest())->messages();
    }
}
