<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTruckRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // Truck fields
            'date' => ['required', 'date'],
            'total_boxes' => ['required', 'integer'],
            'freight' => ['required', 'integer'],
            'driver_id' => ['nullable', 'exists:drivers,id'],

            // Fruits array
            'fruits' => ['required', 'array', 'min:1'],
            'fruits.*.fruit_id' => ['required', 'exists:fruits,id'],
            'fruits.*.client_id' => ['nullable', 'exists:clients,id'],
            'fruits.*.box_type' => ['required', 'in:normal_box,big_box,small_box,small_net'],
            'fruits.*.number_of_boxes' => ['required', 'integer'],
            'fruits.*.second_class_boxes' => ['nullable', 'integer'],
            'fruits.*.third_class_boxes' => ['nullable', 'integer'],
            'fruits.*.unified_weight' => ['nullable', 'numeric'],
            'fruits.*.unified_unit_price' => ['nullable', 'numeric'],
            'fruits.*.unified_box_price' => ['nullable', 'integer'],
        ];
    }

    /**
     * Custom validation rule
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fruits = $this->input('fruits', []);
            $totalBoxes = (int) $this->input('total_boxes', 0);

            /**
             * 1️⃣ Validate sum of number_of_boxes equals total_boxes
             */
            $sum = collect($fruits)->sum(
                fn($f) =>
                isset($f['number_of_boxes']) ? (int) $f['number_of_boxes'] : 0
            );

            if ($sum !== $totalBoxes) {
                $validator->errors()->add(
                    'total_boxes',
                    'مجموع عدد البرانيك في الأصناف يجب أن يساوي إجمالي عدد البرانيك للعربة'
                );
            }

            $combinations = [];

            foreach ($fruits as $index => $fruit) {
                /**
                 * 2️⃣ Validate unique (fruit_id + box_type) pairs
                 *    This mirrors the DB constraint: unique(truck_id, fruit_id, box_type)
                 */
                if (!isset($fruit['fruit_id'], $fruit['box_type'])) {
                    continue;
                }
                $key = $fruit['fruit_id'] . '-' . $fruit['box_type'];
                if (isset($combinations[$key])) {
                    $validator->errors()->add(
                        "fruits.$index.fruit_id",
                        'لا يمكن تكرار نفس الصنف مع نفس نوع البرنيكة مرتين.'
                    );
                }
                $combinations[$key] = true;

                /**
                 * Validate unified prices for the fruits.
                 */
                $unit = $fruit['unified_unit_price'] ?? null;
                $box  = $fruit['unified_box_price'] ?? null;

                if (empty($unit) && empty($box)) {
                    $validator->errors()->add(
                        "fruits.$index.unified_unit_price",
                        'يجب إدخال سعر الكيلو أو سعر البرنيكة.'
                    );
                    $validator->errors()->add(
                        "fruits.$index.unified_box_price",
                        'يجب إدخال سعر الكيلو أو سعر البرنيكة.'
                    );
                }

                if (!empty($unit) && !empty($box)) {
                    $validator->errors()->add(
                        "fruits.$index.unified_unit_price",
                        'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                    );
                    $validator->errors()->add(
                        "fruits.$index.unified_box_price",
                        'لا يمكن إدخال سعر الكيلو وسعر البرنيكة معًا.'
                    );
                }
            }
        });
    }

    /**
     * Use the same messages as the Store request
     */
    public function messages(): array
    {
        return (new StoreTruckRequest())->messages();
    }
}
